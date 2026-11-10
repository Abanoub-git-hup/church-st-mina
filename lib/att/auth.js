// الدخول: جلسة الخادم في cookie موقّعة، وكلمات السر والأرقام السرية bcrypt جوه Postgres (pgcrypto)،
// وحدود محاولات الدخول. نفس سلوك inc/app.php وinc/servants.php في WordPress.
const crypto = require('crypto');
const { db, ApiError, invalid, phone: cleanPhone, pin: cleanPin, isToken, attUrl, cardUrl } = require('./core');

const COOKIE = 'stmina_sess';
const SECRET = () => {
  if (!process.env.SESSION_SECRET || process.env.SESSION_SECRET.length < 32) throw new Error('SESSION_SECRET ناقص أو قصير');
  return process.env.SESSION_SECRET;
};
const hmac = s => crypto.createHmac('sha256', SECRET()).update(s).digest('base64url');
const sha = s => crypto.createHash('sha256').update(s).digest('hex');

// ---------------------------------------------------------------- الجلسة

// القيمة: بيانات الجلسة base64url، ونقطة، والتوقيع. token_version جواها، فلو زاد كل الجلسات القديمة تبطل
const sign = data => { const b = Buffer.from(JSON.stringify(data)).toString('base64url'); return `${b}.${hmac(b)}`; };
const unsign = v => {
  const [b, sig] = String(v || '').split('.');
  if (!b || !sig) return null;
  const good = hmac(b);
  if (sig.length !== good.length || !crypto.timingSafeEqual(Buffer.from(sig), Buffer.from(good))) return null;
  try { return JSON.parse(Buffer.from(b, 'base64url').toString()); } catch { return null; }
};
const readCookie = (req, n) => {
  const m = String(req.headers.cookie || '').match(new RegExp(`(?:^|;\\s*)${n}=([^;]*)`));
  return m ? decodeURIComponent(m[1]) : '';
};

// "افتكرني": 14 يوم زي WordPress، ومن غيرها يومين وcookie بتتمسح مع قفل المتصفح
function setSession(res, staff, remember = true) {
  const days = remember ? 14 : 2;
  const v = sign({ uid: Number(staff.id), v: staff.token_version, exp: Date.now() + days * 864e5 });
  const age = remember ? `; Max-Age=${days * 86400}` : '';
  res.setHeader('Set-Cookie', `${COOKIE}=${encodeURIComponent(v)}; Path=/; HttpOnly; Secure; SameSite=Lax${age}`);
}
const clearSession = res => res.setHeader('Set-Cookie', `${COOKIE}=; Path=/; HttpOnly; Secure; SameSite=Lax; Max-Age=0`);

// الخادم الداخل، أو null: الجلسة سليمة، ومخلصتش، والحساب لسه معاه الصلاحية، ورقم النسخة زي ما هو
async function currentStaff(req) {
  const s = unsign(readCookie(req, COOKIE));
  if (!s || !s.uid || s.exp < Date.now()) return null;
  const [u] = await db()`select * from staff where id = ${s.uid} and active and token_version = ${s.v}`;
  return u || null;
}

// ---------------------------------------------------------------- حدود المحاولات

const ip = req => String(req.headers['x-real-ip'] || req.headers['x-forwarded-for'] || '').split(',')[0].trim();
const ipBucket = (req, kind) => `${kind}:ip:${sha(ip(req) + SECRET()).slice(0, 32)}`;
async function tooMany(bucket, max, seconds) {
  const [r] = await db()`select count(*)::int as n from login_attempts where bucket = ${bucket} and at > now() - make_interval(secs => ${seconds})`;
  return r.n >= max;
}
const fail = bucket => db()`insert into login_attempts (bucket) values (${bucket})`;
const clear = bucket => db()`delete from login_attempts where bucket = ${bucket}`;
const tooManyErr = (msg = 'محاولات غلط كتير. استنى ربع ساعة وجرّب تاني.') => new ApiError('stmina_too_many', msg, 429);

// ---------------------------------------------------------------- الخادم

// الحساب من البريد أو الموبايل
async function findStaff(who) {
  who = String(who || '').trim();
  if (/^[^\s@]+@[^\s@]+$/.test(who)) return (await db()`select * from staff where lower(email) = lower(${who})`)[0];
  const p = cleanPhone(who);
  return p ? (await db()`select * from staff where phone = ${p}`)[0] : undefined;
}
const passwordOk = async (id, password) =>
  (await db()`select 1 from staff where id = ${id} and password_hash is not null and password_hash = extensions.crypt(${password}, password_hash)`).length > 0;
const setPassword = (id, password) =>
  db()`update staff set password_hash = extensions.crypt(${password}, extensions.gen_salt('bf', 10)), token_version = token_version + 1,
        invite_hash = null, invite_expires_at = null where id = ${id} returning *`;

// ---------------------------------------------------------------- الدعوات

const INVITE_DAYS = 3;
// كود عشوائي، والمتخزّن بصمته بس (sha256)، فاللي يشوف قاعدة البيانات مايقدرش يستخدمه
async function newInvite(req, staffId) {
  const ABC = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  const code = Array.from({ length: 32 }, () => ABC[crypto.randomInt(ABC.length)]).join('');
  await db()`update staff set invite_hash = ${sha(code)}, invite_expires_at = now() + make_interval(days => ${INVITE_DAYS}) where id = ${staffId}`;
  return `${attUrl(req, 'invite')}${code}/`;
}
// صاحب الدعوة لو لسه شغالة: مااتستخدمتش، ومعدّتش 3 أيام، ولسه معاه الصلاحية
async function inviteStaff(code) {
  if (typeof code !== 'string' || !/^[A-Za-z0-9]{32}$/.test(code)) return null;
  const [u] = await db()`select * from staff where invite_hash = ${sha(code)} and invite_expires_at > now() and active`;
  return u || null;
}

// ---------------------------------------------------------------- المسارات

const routes = [
  ['POST', /^login$/, 'public', async ({ req, res, body }) => {
    const bucket = ipBucket(req, 'login');
    if (await tooMany(bucket, 5, 900)) throw tooManyErr();
    const u = await findStaff(body.who);
    if (!u || !(await passwordOk(u.id, String(body.password || '')))) {
      await fail(bucket);
      // نفس الرسالة في كل الحالات، علشان محدش يعرف إذا كان الحساب موجود ولا لأ
      throw new ApiError('stmina_bad_login', 'البريد أو رقم الموبايل أو كلمة السر غير صحيحة. راجعها وحاول تاني.', 401);
    }
    if (!u.active) throw new ApiError('stmina_not_servant', 'الحساب ده مالوش صلاحية الخدام. كلّم مسؤول الخدمة.', 403);
    await clear(bucket);
    setSession(res, u, body.remember !== false);
    return { redirect: attUrl(req, 'sessions') };
  }],

  ['POST', /^logout$/, 'public', async ({ req, res }) => {
    clearSession(res);
    return { redirect: attUrl(req, 'login') };
  }],

  // تغيير كلمة السر: لازم الحالية، وحد المحاولات لكل حساب. 400 مش 401 علشان الشاشة ماترجعش للدخول
  ['POST', /^password$/, 'staff', async ({ res, body, staff }) => {
    const bucket = `pw:${staff.id}`;
    if (await tooMany(bucket, 5, 900)) throw tooManyErr();
    if (!(await passwordOk(staff.id, String(body.current || '')))) {
      await fail(bucket);
      throw new ApiError('stmina_bad_password', 'كلمة السر الحالية مش صح.', 400, { fields: { current: 'كلمة السر الحالية مش صح.' } });
    }
    const pw = String(body.password || '');
    if ([...pw].length < 8) throw new ApiError('stmina_short_password', '8 حروف أو أرقام على الأقل.', 400, { fields: { password: '8 حروف أو أرقام على الأقل.' } });
    await clear(bucket);
    // رقم النسخة بيزيد، فالأجهزة التانية بتخرج، والجهاز ده بياخد جلسة جديدة
    const [u] = await setPassword(staff.id, pw);
    setSession(res, u);
    return { ok: true };
  }],

  // قبول الدعوة: كلمة السر (8 على الأقل)، وبعدها بيدخل على طول. الكود هو الإثبات
  ['POST', /^invite$/, 'public', async ({ req, res, body }) => {
    const bucket = ipBucket(req, 'invite');
    if (await tooMany(bucket, 10, 900)) throw tooManyErr('محاولات كتير. استنى ربع ساعة وجرّب تاني.');
    const u = await inviteStaff(body.code);
    if (!u) {
      await fail(bucket);
      throw new ApiError('stmina_bad_invite', 'الدعوة دي مش شغالة. اطلب من مدير الموقع يبعتلك دعوة جديدة.', 404);
    }
    const pw = String(body.password || '');
    if ([...pw].length < 8) throw new ApiError('stmina_short_password', '8 حروف أو أرقام على الأقل.', 400, { fields: { password: '8 حروف أو أرقام على الأقل.' } });
    const [fresh] = await setPassword(u.id, pw);
    setSession(res, fresh);
    return { redirect: attUrl(req, 'sessions') };
  }],

  // المخدوم بيعمل رقمه السري من صفحته. الكود اللي في الرابط هو الإثبات إنه صاحبها
  ['POST', /^me\/([A-Za-z0-9_-]{32})\/pin$/, 'public', async ({ params: [token], body }) => {
    const p = cleanPin(body.pin);
    const [m] = await db()`select id from members where qr_token = ${token}`;
    if (!m) throw new ApiError('stmina_bad_card', 'الرابط ده مش شغال.', 404);
    if (!p) throw new ApiError('stmina_bad_pin', 'الرقم السري 4 أرقام.', 400, { fields: { pin: 'الرقم السري 4 أرقام.' } });
    await db()`update members set pin_hash = extensions.crypt(${p}, extensions.gen_salt('bf', 8)) where id = ${m.id}`;
    return { has_pin: true };
  }],

  // دخول المخدوم: الموبايل والرقم السري ← رابط صفحته. مفيش جلسة، الرابط نفسه هو المفتاح.
  // الحد: 5 غلط في ربع ساعة لكل جهاز، و10 في الساعة لكل موبايل (4 أرقام سهل تتجرّب من كذا جهاز)
  ['POST', /^member-login$/, 'public', async ({ req, body }) => {
    const ipB = ipBucket(req, 'mlogin');
    const p = cleanPhone(body.phone);
    const phB = `mphone:${p}`;
    if ((await tooMany(ipB, 5, 900)) || (p && (await tooMany(phB, 10, 3600)))) {
      throw tooManyErr('محاولات غلط كتير. استنى شوية وجرّب تاني، أو اطلب الرابط من خادم الخدمة.');
    }
    const code = cleanPin(body.pin);
    const [m] = p && code ? await db()`select qr_token from members where phone = ${p} and pin_hash is not null and pin_hash = extensions.crypt(${code}, pin_hash)` : [];
    if (!m) {
      await fail(ipB);
      if (p) await fail(phB);
      throw new ApiError('stmina_bad_member_login', 'رقم الموبايل أو الرقم السري مش صح. راجعهم وحاول تاني.', 401);
    }
    await clear(ipB);
    await clear(phB);
    return { redirect: cardUrl(req, m.qr_token) };
  }],
];

module.exports = { routes, currentStaff, newInvite, inviteStaff, isToken };
