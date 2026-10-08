// اختبارات مسارات المخدومين في st-mina-attendance على الموقع المؤقت.
// التشغيل: node --test tests/rest/*.test.mjs (اسم الفولدر لوحده مابيشتغلش على Windows)
// كل البيانات هنا في خدمة "test" المستخبية، فمابتظهرش في شاشات الخدام.
import { test, describe, before } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

// ---------- الإعداد: القيم من tests/.env ----------
const env = Object.fromEntries(
  readFileSync(new URL('../.env', import.meta.url), 'utf8')
    .split(/\r?\n/)
    .filter(l => l.trim() && !l.startsWith('#'))
    .map(l => [l.slice(0, l.indexOf('=')).trim(), l.slice(l.indexOf('=') + 1).trim()])
);
const BASE = env.STMINA_URL.replace(/\/$/, '') + '/wp-json/stmina/v1';
const auth = (user, pass) => 'Basic ' + Buffer.from(`${user}:${pass}`).toString('base64');
const AS = {
  guest: null,
  servant: auth(env.SERVANT_USER, env.SERVANT_APP_PASSWORD),
  subscriber: auth(env.SUBSCRIBER_USER, env.SUBSCRIBER_APP_PASSWORD),
  // مدير الموقع (اختياري): اختبارات إضافة الخدام وسحب الصلاحية بتتخطى من غيره
  admin: env.ADMIN_USER ? auth(env.ADMIN_USER, env.ADMIN_APP_PASSWORD) : null,
};

// طلب لمسار، بدور معيّن، والخدمة دايمًا "test"
async function call(role, method, path, body) {
  const url = BASE + path + (path.includes('?') ? '&' : '?') + 'service=test';
  const headers = { 'Content-Type': 'application/json' };
  if (AS[role]) headers.Authorization = AS[role];
  const res = await fetch(url, { method, headers, body: body ? JSON.stringify(body) : undefined });
  let data = null;
  try { data = await res.json(); } catch { /* رد فاضي */ }
  return { status: res.status, data };
}

// موبايل عشوائي في كل تشغيل، علشان الرقم فريد في الجدول
const phone = () => '0100' + String(Math.floor(Math.random() * 1e7)).padStart(7, '0');

let member; // مخدوم بيتعمل مرة ويتستخدم في باقي الاختبارات

// النهارده بتوقيت القاهرة زي الموقع. الجلسة اللي لازم تفضل مفتوحة لازم تبقى النهارده،
// لأن أي جلسة مفتوحة من يوم فات بتخلص لوحدها (المهمة 15)
const todayCairo = () => new Intl.DateTimeFormat('en-CA', { timeZone: 'Africa/Cairo' }).format(new Date());

// لو تشغيل قبل كده وقف في النص، جلساته ممكن تمنع جلسات النهارده (نوع واحد في اليوم). خدمة الاختبار بس
before(async () => {
  const list = await call('servant', 'GET', '/sessions');
  for (const x of list.data || []) await call('servant', 'DELETE', `/sessions/${x.id}`);
});

before(async () => {
  const r = await call('servant', 'POST', '/members', { full_name: 'مخدوم اختبار ' + Date.now(), phone: phone() });
  assert.equal(r.status, 201, 'الخادم لازم يقدر يضيف مخدوم: ' + JSON.stringify(r.data));
  member = r.data;
});

describe('الصلاحيات: غير الخدام مرفوضين من كل المسارات', () => {
  const routes = () => [
    ['GET', '/members'],
    ['POST', '/members', { full_name: 'حد تاني', phone: phone() }],
    ['GET', `/members/${member.id}`],
    ['PATCH', `/members/${member.id}`, { status: 'stopped' }],
    ['GET', `/members/${member.id}/notes`],
    ['POST', `/members/${member.id}/notes`, { body: 'ملاحظة' }],
  ];

  test('الزائر من غير دخول ياخد 401', async () => {
    for (const [m, p, b] of routes()) {
      const r = await call('guest', m, p, b);
      assert.equal(r.status, 401, `${m} ${p}`);
    }
  });

  test('المشترك العادي (Subscriber) ياخد 403', async () => {
    for (const [m, p, b] of routes()) {
      const r = await call('subscriber', m, p, b);
      assert.equal(r.status, 403, `${m} ${p}`);
    }
  });

  test('كود QR بتاع المخدوم مايدخّلش', async () => {
    const r = await call('guest', 'GET', `/members?c=${member.qr_token}`);
    assert.equal(r.status, 401);
    const r2 = await fetch(BASE + `/members/${member.id}/notes?service=test`, { headers: { Authorization: 'Bearer ' + member.qr_token } });
    assert.ok([401, 403].includes(r2.status), 'الكود في الهيدر برضه مرفوض');
  });
});

describe('الإضافة', () => {
  test('تاريخ التسجيل والكود بيتعملوا لوحدهم، والحالة نشط', () => {
    assert.match(member.registered_at, /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/);
    assert.match(member.qr_token, /^[A-Za-z0-9_-]{32}$/);
    assert.equal(member.status, 'active');
  });

  test('الاسم والموبايل إلزاميين', async () => {
    const r = await call('servant', 'POST', '/members', {});
    assert.equal(r.status, 400);
    assert.ok(r.data.data.fields.full_name);
    assert.ok(r.data.data.fields.phone);
  });

  test('الاسم لازم كلمتين على الأقل', async () => {
    const r = await call('servant', 'POST', '/members', { full_name: 'مينا', phone: phone() });
    assert.equal(r.status, 400);
    assert.ok(r.data.data.fields.full_name);
  });

  test('الموبايل لازم مصري 11 رقم', async () => {
    const r = await call('servant', 'POST', '/members', { full_name: 'مينا جرجس', phone: '12345' });
    assert.equal(r.status, 400);
    assert.ok(r.data.data.fields.phone);
  });

  test('الأرقام العربي و+20 بتتظبط لوحدها', async () => {
    const digits = phone().slice(1); // من غير الصفر
    const arabic = [...('+20' + digits)].map(c => '٠١٢٣٤٥٦٧٨٩'[c] ?? c).join('');
    const r = await call('servant', 'POST', '/members', { full_name: 'مينا جرجس اختبار', phone: arabic });
    assert.equal(r.status, 201, JSON.stringify(r.data));
    assert.equal(r.data.phone, '0' + digits);
  });

  test('الرقم المتكرر مرفوض', async () => {
    const r = await call('servant', 'POST', '/members', { full_name: 'حد تاني خالص', phone: member.phone });
    assert.equal(r.status, 400);
    assert.match(r.data.data.fields.phone, /متسجّل قبل كده/);
  });
});

describe('التعديل والإيقاف', () => {
  test('تعديل الاسم', async () => {
    const r = await call('servant', 'PATCH', `/members/${member.id}`, { full_name: member.full_name + ' معدّل' });
    assert.equal(r.status, 200);
    assert.ok(r.data.full_name.endsWith('معدّل'));
  });

  test('الإيقاف بيغيّر الحالة ومابيمسحش', async () => {
    const r = await call('servant', 'PATCH', `/members/${member.id}`, { status: 'stopped' });
    assert.equal(r.status, 200);
    assert.equal(r.data.status, 'stopped');
    const again = await call('servant', 'GET', `/members/${member.id}`);
    assert.equal(again.status, 200, 'السجل لسه موجود');
    assert.equal(again.data.status, 'stopped');
    const list = await call('servant', 'GET', '/members?status=stopped');
    assert.ok(list.data.some(m => m.id === member.id), 'بيظهر في فلتر الموقوفين');
  });

  test('المسح النهائي للمدير بس: الخادم العادي والزائر مرفوضين', async () => {
    assert.equal((await call('guest', 'DELETE', `/members/${member.id}`)).status, 401);
    assert.equal((await call('subscriber', 'DELETE', `/members/${member.id}`)).status, 403);
    const r = await call('servant', 'DELETE', `/members/${member.id}`);
    assert.equal(r.status, 403, 'حساب الخادم في الاختبارات مش مدير');
    assert.equal((await call('servant', 'GET', `/members/${member.id}`)).status, 200, 'لسه موجود');
  });

  test('بيانات المخدوم بتقول إذا كان ينفع يتمسح (مالوش حضور)', async () => {
    const r = await call('servant', 'GET', `/members/${member.id}`);
    assert.equal(typeof r.data.can_delete, 'boolean');
  });

  test('المخدوم مش ظاهر في خدمة تانية', async () => {
    const res = await fetch(BASE + `/members/${member.id}?service=i3dad`, { headers: { Authorization: AS.servant } });
    assert.equal(res.status, 404);
  });
});

describe('الملاحظات للخدام بس', () => {
  test('الخادم يكتب ملاحظة ويقراها باسمه', async () => {
    const r = await call('servant', 'POST', `/members/${member.id}/notes`, { body: 'اتصلت بيه وهو كويس' });
    assert.equal(r.status, 201);
    assert.equal(r.data.body, 'اتصلت بيه وهو كويس');
    assert.ok(r.data.author);
    const list = await call('servant', 'GET', `/members/${member.id}/notes`);
    assert.equal(list.data[0].body, 'اتصلت بيه وهو كويس');
  });

  test('الملاحظة الفاضية مرفوضة', async () => {
    const r = await call('servant', 'POST', `/members/${member.id}/notes`, { body: '   ' });
    assert.equal(r.status, 400);
  });

  test('الملاحظات مش جوه بيانات المخدوم نفسها', async () => {
    const r = await call('servant', 'GET', `/members/${member.id}`);
    assert.equal(r.data.notes, undefined);
  });
});

describe('الدخول والشاشات', () => {
  const SITE = env.STMINA_URL.replace(/\/$/, '');

  test('كلمة سر غلط: 401 برسالة واحدة مابتقولش الحساب موجود ولا لأ', async () => {
    // محاولة واحدة بس، علشان حد المحاولات (5 في ربع ساعة) مايقفلش الجهاز
    const res = await fetch(BASE + '/login', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ who: 'no-such-user-' + Date.now(), password: 'x' }) });
    assert.equal(res.status, 401);
    assert.match((await res.json()).message, /غير صحيحة/);
  });

  test('شاشات الخدام بتحوّل الزائر لصفحة الدخول', async () => {
    for (const p of ['/attend/', '/attend/members/', `/attend/members/${member.id}/`, '/attend/scan/']) {
      const res = await fetch(SITE + p, { redirect: 'manual' });
      assert.equal(res.status, 302, p);
      assert.match(res.headers.get('location'), /\/attend\/login\/$/, p);
    }
  });

  test('صفحة الدخول مفتوحة ومش متأرشفة', async () => {
    const res = await fetch(SITE + '/attend/login/');
    assert.equal(res.status, 200);
    assert.match(res.headers.get('x-robots-tag') || '', /noindex/);
    assert.match(await res.text(), /دخول الخادم/);
  });
});

describe('الكارت (المهمة 09)', () => {
  const SITE = env.STMINA_URL.replace(/\/$/, '');

  test('رابط الكارت في بيانات المخدوم هو /me/الكود/', () => {
    assert.equal(member.card_url, `${SITE}/me/${member.qr_token}/`);
  });

  test('الكارت العام بيرجّع الاسم والرابط بس، من غير دخول', async () => {
    const r = await call('guest', 'GET', `/card/${member.qr_token}`);
    assert.equal(r.status, 200);
    assert.deepEqual(Object.keys(r.data).sort(), ['card_url', 'full_name']);
  });

  test('صفحة الكارت مفتوحة ومش متأرشفة، وفيها الاسم ومافيهاش الموبايل', async () => {
    const m = (await call('servant', 'GET', `/members/${member.id}`)).data;
    const res = await fetch(m.card_url);
    assert.equal(res.status, 200);
    assert.match(res.headers.get('x-robots-tag') || '', /noindex/);
    const html = await res.text();
    assert.ok(html.includes(JSON.stringify(m.full_name).slice(1, -1)) || html.includes(m.full_name), 'الاسم في الصفحة');
    assert.ok(!html.includes(m.phone), 'الموبايل مش في الصفحة');
    assert.ok(!html.includes('"nonce"'), 'مفيش رمز nonce في صفحة المخدوم');
  });

  test('الكود مايدخّلش الخدام حتى بعد فتح الكارت', async () => {
    await fetch(member.card_url);
    const r = await call('guest', 'GET', `/members?c=${member.qr_token}`);
    assert.equal(r.status, 401);
  });

  test('غير الخدام مايقدروش يعيدوا الإصدار', async () => {
    assert.equal((await call('guest', 'POST', `/members/${member.id}/reissue`)).status, 401);
    assert.equal((await call('subscriber', 'POST', `/members/${member.id}/reissue`)).status, 403);
  });

  test('إعادة الإصدار بتلغي الرابط القديم فورًا', async () => {
    const old = (await call('servant', 'GET', `/members/${member.id}`)).data.qr_token;
    const r = await call('servant', 'POST', `/members/${member.id}/reissue`);
    assert.equal(r.status, 200);
    assert.notEqual(r.data.qr_token, old);
    assert.match(r.data.qr_token, /^[A-Za-z0-9_-]{32}$/);
    assert.equal((await call('guest', 'GET', `/card/${old}`)).status, 404, 'القديم بطل');
    assert.equal((await call('guest', 'GET', `/card/${r.data.qr_token}`)).status, 200, 'الجديد شغال');
    const page = await (await fetch(`${SITE}/me/${old}/`)).text();
    assert.match(page, /"card":null/, 'صفحة الرابط القديم بتقول إنه مش شغال');
  });

  test('كود بشكل غلط بيرجّع "الرابط ده مش شغال"', async () => {
    const res = await fetch(`${SITE}/me/not-a-real-code/`);
    assert.equal(res.status, 200);
    assert.match(await res.text(), /"card":null/);
  });
});

describe('الجلسات (المهمة 10)', () => {
  // يوم قديم عشوائي، علشان مينفعش جلستين من نفس النوع في نفس اليوم والاختبارات بتتكرر
  const pastDay = () => { const d = new Date(); d.setDate(d.getDate() - 30 - Math.floor(Math.random() * 3000)); return d.toISOString().slice(0, 10); };
  let s;

  test('غير الخدام مرفوضين', async () => {
    for (const [m, p, b] of [['GET', '/sessions'], ['POST', '/sessions', { kind: 'meeting', date: pastDay() }], ['GET', '/sessions/last'], ['DELETE', '/sessions/1']]) {
      assert.equal((await call('guest', m, p, b)).status, 401, `${m} ${p}`);
      assert.equal((await call('subscriber', m, p, b)).status, 403, `${m} ${p}`);
    }
  });

  test('فتح جلسة بيحفظ النوع والتاريخ والحالة "مفتوحة"', async () => {
    const date = todayCairo();
    const r = await call('servant', 'POST', '/sessions', { kind: 'mass', date });
    assert.equal(r.status, 201, JSON.stringify(r.data));
    s = r.data;
    assert.equal(s.kind, 'mass');
    assert.equal(s.kind_name, 'قداس');
    assert.equal(s.date, date);
    assert.equal(s.status, 'open');
  });

  test('مينفعش جلستين من نفس النوع في نفس اليوم', async () => {
    const r = await call('servant', 'POST', '/sessions', { kind: 'mass', date: s.date });
    assert.equal(r.status, 409);
    assert.match(r.data.message, /مفتوحة/);
  });

  test('النوع والتاريخ لازم يبقوا صح، والتاريخ مايبقاش لسه ماجاش', async () => {
    const bad = await call('servant', 'POST', '/sessions', { kind: 'party', date: 'x' });
    assert.equal(bad.status, 400);
    assert.ok(bad.data.data.fields.kind && bad.data.data.fields.date);
    const next = new Date(); next.setDate(next.getDate() + 2);
    const future = await call('servant', 'POST', '/sessions', { kind: 'meeting', date: next.toISOString().slice(0, 10) });
    assert.equal(future.status, 400);
    assert.match(future.data.data.fields.date, /لسه ماجاش/);
  });

  test('القايمة بتظهر الجلسة وحالتها', async () => {
    const r = await call('servant', 'GET', '/sessions');
    const found = r.data.find(x => x.id === s.id);
    assert.ok(found);
    assert.equal(found.status, 'open');
  });

  test('"زي آخر جلسة" بيرجّع الخدمة والنوع من غير التاريخ', async () => {
    const r = await call('servant', 'GET', '/sessions/last');
    assert.equal(r.status, 200);
    assert.deepEqual(Object.keys(r.data).sort(), ['kind', 'service']);
    assert.equal(r.data.service, 'test');
  });

  test('الجلسة مش ظاهرة في خدمة تانية', async () => {
    const res = await fetch(BASE + `/sessions/${s.id}?service=i3dad`, { headers: { Authorization: AS.servant } });
    assert.equal(res.status, 404);
  });

  test('حذف الجلسة المفتوحة بالغلط', async () => {
    const r = await call('servant', 'DELETE', `/sessions/${s.id}`);
    assert.equal(r.status, 200);
    assert.equal((await call('servant', 'GET', `/sessions/${s.id}`)).status, 404);
  });
});

describe('المسح (المهام 11 و12)', () => {
  const pastDay = () => { const d = new Date(); d.setDate(d.getDate() - 30 - Math.floor(Math.random() * 3000)); return d.toISOString().slice(0, 10); };
  const newMember = async name => (await call('servant', 'POST', '/members', { full_name: name, phone: phone() })).data;
  let s, a, b;

  before(async () => {
    s = (await call('servant', 'POST', '/sessions', { kind: 'meeting', date: todayCairo() })).data;
    a = await newMember('مخدوم مسح ' + Date.now());
    b = await newMember('مخدوم تاني ' + Date.now());
  });

  test('غير الخدام مرفوضين من المسح والسجلات', async () => {
    for (const [m, p, body] of [['POST', `/sessions/${s.id}/scan`, { code: a.qr_token }], ['GET', `/sessions/${s.id}/records`], ['DELETE', `/sessions/${s.id}/records/${a.id}`]]) {
      assert.equal((await call('guest', m, p, body)).status, 401, `${m} ${p}`);
      assert.equal((await call('subscriber', m, p, body)).status, 403, `${m} ${p}`);
    }
  });

  test('مسح كارت صالح بيسجّل "حضر" بالطريقة والخادم والوقت', async () => {
    const r = await call('servant', 'POST', `/sessions/${s.id}/scan`, { code: a.card_url }); // الـ QR فيه الرابط كامل
    assert.equal(r.data.result, 'ok', JSON.stringify(r.data));
    assert.equal(r.data.member.full_name, a.full_name);
    assert.equal(r.data.present, 1);
    const rec = (await call('servant', 'GET', `/sessions/${s.id}/records`)).data[0];
    assert.equal(rec.member_id, a.id);
    assert.equal(rec.status, 'present');
    assert.equal(rec.method, 'scan');
    assert.ok(rec.recorded_by, 'اسم الخادم');
    assert.match(rec.recorded_at, /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/);
  });

  test('المسح مرة تانية مابيعملش سجل تاني', async () => {
    const r = await call('servant', 'POST', `/sessions/${s.id}/scan`, { code: a.qr_token });
    assert.equal(r.data.result, 'dup');
    assert.ok(r.data.recorded_at);
    assert.equal((await call('servant', 'GET', `/sessions/${s.id}/records`)).data.length, 1);
  });

  test('مسحتين في نفس اللحظة لنفس الكارت: سجل واحد بس (القيد في قاعدة البيانات)', async () => {
    const both = await Promise.all([1, 2].map(() => call('servant', 'POST', `/sessions/${s.id}/scan`, { code: b.qr_token })));
    assert.deepEqual(both.map(r => r.data.result).sort(), ['dup', 'ok']);
    const recs = (await call('servant', 'GET', `/sessions/${s.id}/records`)).data.filter(r => r.member_id === b.id);
    assert.equal(recs.length, 1);
  });

  test('الكارت الملغي والكارت المش معروف برسالتين مختلفتين ومن غير تسجيل', async () => {
    const c = await newMember('مخدوم ملغي ' + Date.now());
    await call('servant', 'POST', `/members/${c.id}/reissue`);
    const before = (await call('servant', 'GET', `/sessions/${s.id}/records`)).data.length;
    const rv = await call('servant', 'POST', `/sessions/${s.id}/scan`, { code: c.qr_token });
    assert.equal(rv.data.result, 'revoked');
    assert.equal(rv.data.member.full_name, c.full_name);
    const un = await call('servant', 'POST', `/sessions/${s.id}/scan`, { code: 'x'.repeat(32) });
    assert.equal(un.data.result, 'unknown');
    const junk = await call('servant', 'POST', `/sessions/${s.id}/scan`, { code: 'https://example.com/hello' });
    assert.equal(junk.data.result, 'unknown');
    assert.equal((await call('servant', 'GET', `/sessions/${s.id}/records`)).data.length, before);
  });

  test('المخدوم الموقوف مابيتسجّلش', async () => {
    const d = await newMember('مخدوم موقوف ' + Date.now());
    await call('servant', 'PATCH', `/members/${d.id}`, { status: 'stopped' });
    const r = await call('servant', 'POST', `/sessions/${s.id}/scan`, { code: d.qr_token });
    assert.equal(r.data.result, 'stopped');
  });

  test('التراجع بيشيل التسجيل', async () => {
    const r = await call('servant', 'DELETE', `/sessions/${s.id}/records/${a.id}`);
    assert.equal(r.status, 200);
    assert.equal((await call('servant', 'DELETE', `/sessions/${s.id}/records/${a.id}`)).status, 404);
    const again = await call('servant', 'POST', `/sessions/${s.id}/scan`, { code: a.qr_token });
    assert.equal(again.data.result, 'ok', 'بعد التراجع يتسجّل تاني عادي');
  });

  test('المسح من غير جلسة مفتوحة مرفوض', async () => {
    await call('servant', 'DELETE', `/sessions/${s.id}`);
    const r = await call('servant', 'POST', `/sessions/${s.id}/scan`, { code: a.qr_token });
    assert.equal(r.data.result, 'nosession');
  });
});

describe('اليدوي والتصحيح والإنهاء (المهام 13 و14)', () => {
  const pastDay = () => { const d = new Date(); d.setDate(d.getDate() - 30 - Math.floor(Math.random() * 3000)); return d.toISOString().slice(0, 10); };
  const newMember = async name => (await call('servant', 'POST', '/members', { full_name: name, phone: phone() })).data;
  const recordOf = async (sid, mid) => (await call('servant', 'GET', `/sessions/${sid}/roster`)).data.find(r => r.member_id === mid);
  let today, past, a, b, c, d;

  before(async () => {
    a = await newMember('يدوي أول ' + Date.now());
    b = await newMember('يدوي تاني ' + Date.now());
    c = await newMember('يدوي تالت ' + Date.now());
    d = await newMember('يدوي موقوف ' + Date.now());
    await call('servant', 'PATCH', `/members/${d.id}`, { status: 'stopped' });
    // جلسة النهارده (علشان المخدومين الجداد يبقوا متسجّلين يومها). لو نوع اتعمل النهارده قبل كده، نجرّب اللي بعده
    const date = todayCairo();
    for (const kind of ['activity', 'service', 'meeting', 'mass']) {
      const r = await call('servant', 'POST', '/sessions', { kind, date });
      if (r.status === 201) { today = r.data; break; }
    }
    assert.ok(today, 'اتفتحت جلسة النهارده');
    past = (await call('servant', 'POST', '/sessions', { kind: 'meeting', date: pastDay() })).data;
  });

  test('غير الخدام مرفوضين', async () => {
    for (const [m, p, body] of [['GET', `/sessions/${today.id}/roster`], ['PUT', `/sessions/${today.id}/records/${a.id}`, { status: 'present' }], ['POST', `/sessions/${today.id}/close`]]) {
      assert.equal((await call('guest', m, p, body)).status, 401, `${m} ${p}`);
      assert.equal((await call('subscriber', m, p, body)).status, 403, `${m} ${p}`);
    }
  });

  test('التسجيل اليدوي بيتحفظ بعلامة "يدوي" واسم الخادم', async () => {
    const r = await call('servant', 'PUT', `/sessions/${today.id}/records/${a.id}`, { status: 'present' });
    assert.equal(r.status, 200, JSON.stringify(r.data));
    assert.equal(r.data.status, 'present');
    assert.equal(r.data.method, 'manual');
    assert.ok(r.data.recorded_by);
  });

  test('الغياب بعذر حالة مستقلة', async () => {
    const r = await call('servant', 'PUT', `/sessions/${today.id}/records/${b.id}`, { status: 'excused' });
    assert.equal(r.data.status, 'excused');
    assert.equal((await call('servant', 'PUT', `/sessions/${today.id}/records/${b.id}`, { status: 'late' })).status, 400);
  });

  test('القايمة فيها اللي لسه ماتسجّلش، ومافيهاش الموقوف', async () => {
    assert.equal((await recordOf(today.id, c.id)).status, null);
    assert.equal(await recordOf(today.id, d.id), undefined);
    assert.equal((await call('servant', 'PUT', `/sessions/${today.id}/records/${d.id}`, { status: 'present' })).status, 409, 'الموقوف مايتسجّلش');
  });

  test('التصحيح بيغيّر الحالة ويحفظ مين عدّل', async () => {
    const r = await call('servant', 'PUT', `/sessions/${today.id}/records/${a.id}`, { status: 'absent' });
    assert.equal(r.data.status, 'absent');
    assert.ok(r.data.updated_by);
    assert.ok(r.data.updated_at);
    assert.equal(r.data.method, 'manual', 'الطريقة الأصلية مابتتغيّرش');
  });

  test('الإنهاء بيسجّل "غاب" للي ماتسجّلوش بس، والجلسة بتبقى منتهية', async () => {
    const r = await call('servant', 'POST', `/sessions/${today.id}/close`);
    assert.equal(r.status, 200);
    assert.equal(r.data.status, 'closed');
    assert.ok(r.data.absent_created >= 1);
    const cRec = await recordOf(today.id, c.id);
    assert.equal(cRec.status, 'absent');
    assert.equal(cRec.method, 'auto');
    assert.equal((await recordOf(today.id, b.id)).status, 'excused', 'اللي ليه سجل مابيتغيّرش');
    assert.equal(await recordOf(today.id, d.id), undefined, 'الموقوف مايتحسبش غايب');
  });

  test('الإنهاء مرة تانية مابيعملش سجلات', async () => {
    const before = (await call('servant', 'GET', `/sessions/${today.id}/records`)).data.length;
    const r = await call('servant', 'POST', `/sessions/${today.id}/close`);
    assert.equal(r.data.absent_created, 0);
    assert.equal((await call('servant', 'GET', `/sessions/${today.id}/records`)).data.length, before);
  });

  test('بعد الإنهاء: المسح مقفول، والتصحيح شغال للي ليهم سجل بس', async () => {
    assert.equal((await call('servant', 'POST', `/sessions/${today.id}/scan`, { code: a.qr_token })).data.result, 'nosession');
    assert.equal((await call('servant', 'PUT', `/sessions/${today.id}/records/${c.id}`, { status: 'excused' })).data.status, 'excused');
    const e = await newMember('اتسجّل بعد الإنهاء ' + Date.now());
    assert.equal((await call('servant', 'PUT', `/sessions/${today.id}/records/${e.id}`, { status: 'present' })).status, 409);
  });

  test('اللي اتسجّل في الخدمة بعد يوم الجلسة مايتحسبش غايب', async () => {
    await call('servant', 'POST', `/sessions/${past.id}/close`);
    for (const m of [a, b, c]) assert.equal(await recordOf(past.id, m.id), undefined, m.full_name);
  });

  test('تنضيف: جلسات خدمة الاختبار المنتهية بتتمسح', async () => {
    assert.equal((await call('servant', 'DELETE', `/sessions/${today.id}`)).status, 200);
    assert.equal((await call('servant', 'DELETE', `/sessions/${past.id}`)).status, 200);
  });
});

describe('الجلسات المنسية والحذف (المهمة 15)', () => {
  const pastDay = () => { const d = new Date(); d.setDate(d.getDate() - 30 - Math.floor(Math.random() * 3000)); return d.toISOString().slice(0, 10); };

  test('الجلسة المفتوحة من يوم فات بتخلص لوحدها بنفس نتيجة الإنهاء', async () => {
    const old = (await call('servant', 'POST', '/sessions', { kind: 'activity', date: pastDay() })).data;
    const m = (await call('servant', 'POST', '/members', { full_name: 'جلسة منسية ' + Date.now(), phone: phone() })).data;
    await call('servant', 'PUT', `/sessions/${old.id}/records/${m.id}`, { status: 'excused' });
    const list = (await call('servant', 'GET', '/sessions')).data;
    const after = list.find(x => x.id === old.id);
    assert.equal(after.status, 'closed', 'خلصت لوحدها');
    assert.ok(after.closed_at);
    const rec = (await call('servant', 'GET', `/sessions/${old.id}/roster`)).data.find(r => r.member_id === m.id);
    assert.equal(rec.status, 'excused', 'السجل الموجود مابيتغيّرش');
    await call('servant', 'DELETE', `/sessions/${old.id}`);
  });

  test('جلسة النهارده المفتوحة مابتخلصش لوحدها', async () => {
    const t = (await call('servant', 'POST', '/sessions', { kind: 'service', date: todayCairo() })).data;
    const after = (await call('servant', 'GET', '/sessions')).data.find(x => x.id === t.id);
    assert.equal(after.status, 'open');
    await call('servant', 'DELETE', `/sessions/${t.id}`);
  });

  test('حذف الجلسة المفتوحة مابيسيبش أي سجل', async () => {
    const t = (await call('servant', 'POST', '/sessions', { kind: 'service', date: todayCairo() })).data;
    const m = (await call('servant', 'POST', '/members', { full_name: 'حذف جلسة ' + Date.now(), phone: phone() })).data;
    assert.equal((await call('servant', 'POST', `/sessions/${t.id}/scan`, { code: m.qr_token })).data.result, 'ok');
    assert.equal((await call('servant', 'DELETE', `/sessions/${t.id}`)).status, 200);
    assert.equal((await call('servant', 'GET', `/sessions/${t.id}/records`)).status, 404);
  });

});

describe('حضوري والنسب (المهمة 16)', () => {
  const pastDay = () => { const d = new Date(); d.setDate(d.getDate() - 30 - Math.floor(Math.random() * 3000)); return d.toISOString().slice(0, 10); };
  let m, sessions = [];

  before(async () => {
    m = (await call('servant', 'POST', '/members', { full_name: 'نسب حضوري ' + Date.now(), phone: phone() })).data;
    // 4 جلسات النهارده (نوع لكل واحدة): قداس حضر، اجتماع غاب، نشاط بعذر، خدمة حضر. بالترتيب ده
    const plan = [['mass', 'present'], ['meeting', 'absent'], ['activity', 'excused'], ['service', 'present']];
    for (const [kind, status] of plan) {
      const s = (await call('servant', 'POST', '/sessions', { kind, date: todayCairo() })).data;
      assert.ok(s && s.id, `اتفتحت جلسة ${kind}`);
      await call('servant', 'PUT', `/sessions/${s.id}/records/${m.id}`, { status });
      await call('servant', 'POST', `/sessions/${s.id}/close`);
      sessions.push(s);
    }
    // وجلسة قديمة قبل تسجيله: مالهاش دعوة بنسبته
    const old = (await call('servant', 'POST', '/sessions', { kind: 'meeting', date: pastDay() })).data;
    await call('servant', 'POST', `/sessions/${old.id}/close`);
    sessions.push(old);
  });

  test('النسبة = حضر ÷ (الجلسات − بعذر)، والجلسة اللي قبل التسجيل مش محسوبة', async () => {
    const r = await call('guest', 'GET', `/me/${m.qr_token}`);
    assert.equal(r.status, 200);
    const all = r.data.kinds.all;
    assert.deepEqual([all.present, all.excused, all.total, all.base, all.pct], [2, 1, 4, 3, 67]);
  });

  test('النسبة لكل نوع نشاط', async () => {
    const k = (await call('guest', 'GET', `/me/${m.qr_token}`)).data.kinds;
    assert.equal(k.mass.pct, 100);
    assert.equal(k.meeting.pct, 0);
    assert.equal(k.activity.pct, null, 'كله بعذر: مفيش نسبة');
    assert.equal(k.service.pct, 100);
  });

  test('ورا بعض: العذر مابيقطعش، والغياب بيقطع', async () => {
    assert.equal((await call('guest', 'GET', `/me/${m.qr_token}`)).data.streak, 1);
  });

  test('آخر الجلسات والشهور موجودين', async () => {
    const d = (await call('guest', 'GET', `/me/${m.qr_token}`)).data;
    assert.equal(d.last.length, 4);
    assert.equal(d.months.length, 6);
    assert.equal(d.months[0].pct, 67);
  });

  test('صفحة المخدوم مافيهاش "يدوي" ولا ملاحظات ولا موبايل ولا بيانات حد تاني', async () => {
    const d = (await call('guest', 'GET', `/me/${m.qr_token}`)).data;
    const text = JSON.stringify(d);
    assert.ok(!('manual' in d) && !('notes' in d) && !('phone' in d) && !('away' in d));
    assert.ok(!text.includes('"method"'), 'مفيش طريقة تسجيل');
    assert.ok(!text.includes(m.phone));
    assert.deepEqual(Object.keys(d.last[0]).sort(), ['date', 'kind', 'kind_name', 'status']);
  });

  test('الخادم بيشوف نفس الأرقام، ومعاها اليدوي والغياب ورا بعض', async () => {
    const s = (await call('servant', 'GET', `/members/${m.id}/stats`)).data;
    const me = (await call('guest', 'GET', `/me/${m.qr_token}`)).data;
    assert.deepEqual(s.kinds, me.kinds);
    assert.equal(s.manual, 2, 'الحضور اليدوي');
    assert.equal(s.away, 0);
    const list = (await call('servant', 'GET', '/members')).data.find(x => x.id === m.id);
    assert.equal(list.pct, 67, 'نفس النسبة في قايمة المخدومين');
  });

  test('غير الخدام مايشوفوش أرقام الخادم', async () => {
    assert.equal((await call('guest', 'GET', `/members/${m.id}/stats`)).status, 401);
    assert.equal((await call('subscriber', 'GET', `/members/${m.id}/stats`)).status, 403);
    assert.equal((await call('guest', 'GET', '/me/' + 'z'.repeat(32))).status, 404);
  });

  test('تنضيف', async () => {
    for (const s of sessions) assert.equal((await call('servant', 'DELETE', `/sessions/${s.id}`)).status, 200);
  });
});

describe('الاستيراد من Excel وحالة الكارت (المهمة 20)', () => {
  const p1 = phone(), p2 = phone(), p3 = phone();
  const rows = () => [
    { line: 2, name: 'مستورد أول ' + Date.now(), phone: p1.slice(1) },          // الصفر اللي Excel شاله
    { line: 3, name: 'مستورد تاني ' + Date.now(), phone: '+2' + p2 },             // +20
    { line: 4, name: '', phone: phone() },                                        // من غير اسم
    { line: 5, name: 'مستورد من غير موبايل', phone: '' },
    { line: 6, name: 'كلمة', phone: phone() },                                    // الاسم كلمة واحدة
    { line: 7, name: 'رقم غلط خالص', phone: '12345' },
    { line: 8, name: 'مكرر في الملف', phone: p1 },                                // نفس رقم صف 2
    { line: 9, name: 'متسجّل قبل كده', phone: member.phone },                     // موجود في النظام
    { line: 10, name: 'مستورد تالت ' + Date.now(), phone: p3 },
  ];
  let preview;

  test('غير الخدام مايقدروش يستوردوا', async () => {
    assert.equal((await call('guest', 'POST', '/members/import', { rows: rows() })).status, 401);
    assert.equal((await call('subscriber', 'POST', '/members/import', { rows: rows() })).status, 403);
  });

  test('المعاينة بتفحص كل صف وماتضيفش حاجة', async () => {
    const before = (await call('servant', 'GET', '/members')).data.length;
    preview = (await call('servant', 'POST', '/members/import', { rows: rows(), commit: false })).data;
    assert.equal((await call('servant', 'GET', '/members')).data.length, before);
    const by = Object.fromEntries(preview.rows.map(r => [r.line, r]));
    assert.ok(by[2].ok && by[2].fixed && by[2].phone === p1, 'الصفر رجع');
    assert.ok(by[3].ok && by[3].phone === p2, '+20 اتشال');
    assert.match(by[4].why, /من غير اسم/);
    assert.match(by[5].why, /من غير موبايل/);
    assert.match(by[6].why, /كلمة واحدة/);
    assert.match(by[7].why, /مش رقم موبايل/);
    assert.match(by[8].why, /مكرر، نفس رقم صف 2/);
    assert.match(by[9].why, /متسجّل قبل كده/);
    assert.ok(by[10].ok);
    assert.equal(preview.added.length, 0);
  });

  test('الإضافة بتعمل الصفوف السليمة بس، كل واحد بكوده', async () => {
    const r = (await call('servant', 'POST', '/members/import', { rows: rows(), commit: true })).data;
    assert.equal(r.added.length, 3);
    for (const m of r.added) {
      assert.match(m.qr_token, /^[A-Za-z0-9_-]{32}$/);
      assert.equal(m.card_sent, false);
    }
    const again = (await call('servant', 'POST', '/members/import', { rows: rows(), commit: true })).data;
    assert.equal(again.added.length, 0, 'الاستيراد مرة تانية مابيكررش حد');
  });

  test('"الكارت اتبعت" بيتعلّم، وإعادة الإصدار بتلغيه', async () => {
    const m = (await call('servant', 'GET', '/members')).data.find(x => x.phone === p3);
    assert.equal((await call('guest', 'POST', `/members/${m.id}/card-sent`)).status, 401);
    const r = await call('servant', 'POST', `/members/${m.id}/card-sent`);
    assert.equal(r.data.card_sent, true);
    const re = await call('servant', 'POST', `/members/${m.id}/reissue`);
    assert.equal(re.data.card_sent, false, 'الكارت الجديد لسه مااتبعتش');
  });
});

describe('لوحة الخادم (المهمة 17)', () => {
  // تاريخ من n يوم، بتوقيت القاهرة
  const daysAgo = n => { const d = new Date(Date.now() - n * 864e5); return new Intl.DateTimeFormat('en-CA', { timeZone: 'Africa/Cairo' }).format(d); };
  const rnd = (a, b) => a + Math.floor(Math.random() * (b - a));
  let a, b, mass, meeting, old2, old5;
  const ids = r => r.data.sessions.map(s => s.id);

  before(async () => {
    a = (await call('servant', 'POST', '/members', { full_name: 'لوحة الخادم ' + Date.now(), phone: phone() })).data;
    b = (await call('servant', 'POST', '/members', { full_name: 'لوحة موقوف ' + Date.now(), phone: phone() })).data;
    // النهارده: قداس حضر (يدوي)، واجتماع غاب
    mass = (await call('servant', 'POST', '/sessions', { kind: 'mass', date: todayCairo() })).data;
    await call('servant', 'PUT', `/sessions/${mass.id}/records/${a.id}`, { status: 'present' });
    await call('servant', 'PUT', `/sessions/${mass.id}/records/${b.id}`, { status: 'present' });
    await call('servant', 'POST', `/sessions/${mass.id}/close`);
    meeting = (await call('servant', 'POST', '/sessions', { kind: 'meeting', date: todayCairo() })).data;
    await call('servant', 'PUT', `/sessions/${meeting.id}/records/${a.id}`, { status: 'absent' });
    await call('servant', 'POST', `/sessions/${meeting.id}/close`);
    // جلسة من شهرين تقريبًا (جوه "آخر 3 شهور" وبرا "الشهر ده")، وجلسة أقدم من 3 شهور
    old2 = (await call('servant', 'POST', '/sessions', { kind: 'activity', date: daysAgo(rnd(40, 80)) })).data;
    await call('servant', 'POST', `/sessions/${old2.id}/close`);
    old5 = (await call('servant', 'POST', '/sessions', { kind: 'activity', date: daysAgo(rnd(120, 300)) })).data;
    await call('servant', 'POST', `/sessions/${old5.id}/close`);
    assert.ok(mass.id && meeting.id && old2.id && old5.id, 'كل الجلسات اتفتحت');
    // الموقوف بعد ما حضر: مايتحسبش في اللوحة
    await call('servant', 'PATCH', `/members/${b.id}`, { status: 'stopped' });
  });

  test('غير الخدام مايوصلوش للوحة ولا للجدول', async () => {
    assert.equal((await call('guest', 'GET', '/dashboard')).status, 401);
    assert.equal((await call('subscriber', 'GET', '/dashboard')).status, 403);
  });

  test('أرقام المخدوم في اللوحة هي نفس أرقام ملفه و"حضوري"', async () => {
    const d = (await call('servant', 'GET', '/dashboard?period=all')).data;
    const row = d.members.find(x => x.id === a.id);
    const s = (await call('servant', 'GET', `/members/${a.id}/stats`)).data;
    const me = (await call('guest', 'GET', `/me/${a.qr_token}`)).data;
    assert.deepEqual(row.kinds, s.kinds);
    assert.deepEqual(row.kinds, me.kinds);
    assert.deepEqual(row.months, me.months, 'نفس رسم آخر 6 شهور');
    assert.deepEqual([row.kinds.all.present, row.kinds.all.base, row.kinds.all.pct], [1, 2, 50]);
  });

  test('عدد التسجيل اليدوي لكل مخدوم', async () => {
    const d = (await call('servant', 'GET', '/dashboard?period=all')).data;
    assert.equal(d.members.find(x => x.id === a.id).manual, 1);
    assert.equal(d.manual_min, 3);
  });

  test('جدول Excel: حالة كل مخدوم في كل جلسة، وعدد الحاضرين في كل جلسة', async () => {
    const d = (await call('servant', 'GET', '/dashboard?period=all')).data;
    const row = d.members.find(x => x.id === a.id);
    assert.equal(row.records['s' + mass.id], 'present');
    assert.equal(row.records['s' + meeting.id], 'absent');
    assert.equal(row.records['s' + old2.id], undefined, 'الجلسة اللي قبل تسجيله مالهاش خانة');
    assert.equal(d.sessions.find(s => s.id === mass.id).present, 1, 'الموقوف مش محسوب في الحاضرين');
  });

  test('الموقوفين برا الأرقام، وعددهم بس ظاهر', async () => {
    const d = (await call('servant', 'GET', '/dashboard?period=all')).data;
    assert.ok(!d.members.some(x => x.id === b.id));
    assert.ok(d.stopped >= 1);
  });

  test('النسبة العامة = مجموع الحضور ÷ مجموع المقامات', async () => {
    const d = (await call('servant', 'GET', '/dashboard?period=all')).data;
    const p = d.members.reduce((t, m) => t + m.kinds.all.present, 0), base = d.members.reduce((t, m) => t + m.kinds.all.base, 0);
    assert.deepEqual([d.overall.all.present, d.overall.all.base], [p, base]);
    assert.equal(d.overall.all.pct, base ? Math.round(p / base * 100) : null);
  });

  test('الفترة بتحدد الجلسات: الشهر ده، وآخر 3 شهور، ومن الأول', async () => {
    const month = ids(await call('servant', 'GET', '/dashboard?period=month'));
    const m3 = ids(await call('servant', 'GET', '/dashboard?period=3m'));
    const all = ids(await call('servant', 'GET', '/dashboard?period=all'));
    assert.ok(month.includes(mass.id) && !month.includes(old2.id) && !month.includes(old5.id));
    assert.ok(m3.includes(mass.id) && m3.includes(old2.id) && !m3.includes(old5.id));
    assert.ok(all.includes(old2.id) && all.includes(old5.id));
    assert.equal((await call('servant', 'GET', '/dashboard?period=year')).status, 400);
  });

  test('تنضيف', async () => {
    for (const s of [mass, meeting, old2, old5]) assert.equal((await call('servant', 'DELETE', `/sessions/${s.id}`)).status, 200);
  });
});

describe('تثبيت "حضوري" على الموبايل (المهمة 19)', () => {
  const SITE = env.STMINA_URL.replace(/\/$/, '');
  let m;
  before(async () => {
    m = (await call('servant', 'POST', '/members', { full_name: 'تثبيت حضوري ' + Date.now(), phone: phone() })).data;
  });

  test('صفحة المخدوم فيها رابط manifest بتاعه', async () => {
    const html = await (await fetch(m.card_url)).text();
    assert.ok(html.includes(`href="${m.card_url}manifest.webmanifest"`));
    assert.ok(html.includes('apple-touch-icon'));
  });

  test('الـ manifest بيفتح على رابط المخدوم نفسه، من غير شريط المتصفح', async () => {
    const r = await fetch(m.card_url + 'manifest.webmanifest', { redirect: 'manual' });
    assert.equal(r.status, 200, 'من غير تحويل');
    assert.match(r.headers.get('content-type'), /application\/manifest\+json/);
    const j = await r.json();
    assert.equal(j.name, 'حضوري');
    assert.equal(j.start_url, m.card_url);
    assert.equal(j.scope, m.card_url);
    assert.equal(j.display, 'standalone');
    assert.ok(j.icons.some(i => i.sizes === '512x512'));
  });

  test('كود غلط: مفيش manifest', async () => {
    assert.equal((await fetch(`${SITE}/me/${'z'.repeat(32)}/manifest.webmanifest`)).status, 404);
  });

  test('الـ service worker بيتقدّم من جذر الموقع من غير تحويل', async () => {
    const r = await fetch(`${SITE}/me-sw.js?v=test${Date.now()}`, { redirect: 'manual' });
    assert.equal(r.status, 200);
    assert.match(r.headers.get('content-type'), /javascript/);
    assert.ok((await r.text()).includes("addEventListener('fetch'"));
  });
});

describe('دخول المخدوم بالموبايل والرقم السري', () => {
  let m;
  const login = (phone, pin) => call('guest', 'POST', '/member-login', { phone, pin });
  before(async () => {
    m = (await call('servant', 'POST', '/members', { full_name: 'رقم سري ' + Date.now(), phone: phone() })).data;
  });

  test('من غير رقم سري: الدخول مرفوض', async () => {
    const r = await login(m.phone, '1234');
    assert.equal(r.status, 401);
  });

  test('الرقم السري لازم 4 أرقام، وبالكود الصح بس', async () => {
    for (const pin of ['123', '12345', 'abcd', '']) {
      assert.equal((await call('guest', 'POST', `/me/${m.qr_token}/pin`, { pin })).status, 400, pin);
    }
    assert.equal((await call('guest', 'POST', `/me/${'z'.repeat(32)}/pin`, { pin: '1234' })).status, 404);
    const r = await call('guest', 'POST', `/me/${m.qr_token}/pin`, { pin: '٤٨٢٧' });
    assert.equal(r.status, 200, 'الأرقام العربي بتتقبل');
  });

  test('رقم سري غلط: نفس رسالة الرقم المش متسجّل', async () => {
    const wrong = await login(m.phone, '1111');
    const nobody = await login('01099999999', '4827');
    assert.equal(wrong.status, 401);
    assert.equal(wrong.data.message, nobody.data.message);
  });

  test('الموبايل والرقم السري الصح بيرجّعوا رابط صفحته', async () => {
    const r = await login(m.phone, '4827');
    assert.equal(r.status, 200);
    assert.equal(r.data.redirect, m.card_url);
    assert.ok(!JSON.stringify(r.data).includes('pin'), 'مفيش أي حاجة عن الرقم السري في الرد');
  });

  test('الرقم السري مابيظهرش للخدام', async () => {
    const r = await call('servant', 'GET', `/members/${m.id}`);
    assert.ok(!JSON.stringify(r.data).includes('pin'));
  });

  test('إعادة إصدار الكارت بتمسح الرقم السري', async () => {
    await call('servant', 'POST', `/members/${m.id}/reissue`);
    assert.equal((await login(m.phone, '4827')).status, 401);
  });
});

describe('إدارة الخدام (المهمة 24)', () => {
  const SITE = env.STMINA_URL.replace(/\/$/, '');
  const wp = (role, method, path, body) => {
    const headers = { 'Content-Type': 'application/json' };
    if (AS[role]) headers.Authorization = AS[role];
    return fetch(SITE + '/wp-json/wp/v2' + path, { method, headers, body: body ? JSON.stringify(body) : undefined })
      .then(async r => ({ status: r.status, data: await r.json().catch(() => null) }));
  };
  const noAdmin = !AS.admin && 'محتاج ADMIN_USER وADMIN_APP_PASSWORD في tests/.env';

  test('القايمة للخدام بس، والخادم العادي مايقدرش يدير', async () => {
    assert.equal((await call('guest', 'GET', '/servants')).status, 401);
    assert.equal((await call('subscriber', 'GET', '/servants')).status, 403);
    const r = await call('servant', 'GET', '/servants');
    assert.equal(r.status, 200);
    assert.equal(r.data.can_manage, false);
    assert.ok(r.data.servants.some(s => s.me), 'الخادم شايف نفسه في القايمة');
    assert.ok(!JSON.stringify(r.data).includes('invite"'), 'مفيش أكواد دعوات في القايمة');
  });

  test('الخادم العادي مايقدرش يضيف ولا يسحب ولا يبعت دعوة', async () => {
    assert.equal((await call('servant', 'POST', '/servants', { name: 'حد', phone: phone() })).status, 403);
    const me = (await call('servant', 'GET', '/servants')).data.servants.find(s => s.me);
    assert.equal((await call('servant', 'DELETE', `/servants/${me.id}`)).status, 403);
    assert.equal((await call('servant', 'POST', `/servants/${me.id}/invite`)).status, 403);
  });

  test('الخادم مايقدرش يعدّل محتوى الموقع', async () => {
    assert.equal((await wp('servant', 'POST', '/posts', { title: 'تجربة', status: 'draft' })).status, 403);
  });

  test('دعوة بكود غلط مابتشتغلش', async () => {
    assert.equal((await call('guest', 'POST', '/invite', { code: 'z'.repeat(32), password: '12345678' })).status, 404);
  });

  describe('مدير الموقع', { skip: noAdmin }, () => {
    const p = phone(), pass = 'Test-' + Math.random().toString(36).slice(2, 12);
    let s, code;

    test('إضافة خادم بالاسم والموبايل، ومعاه رابط دعوة', async () => {
      const bad = await call('admin', 'POST', '/servants', { name: '', phone: '123' });
      assert.equal(bad.status, 400);
      assert.ok(bad.data.data.fields.name && bad.data.data.fields.phone);
      const r = await call('admin', 'POST', '/servants', { name: 'خادم اختبار ' + Date.now(), phone: p });
      assert.equal(r.status, 201, JSON.stringify(r.data));
      s = r.data;
      assert.equal(s.pending, true);
      code = s.invite_url.match(/invite\/([A-Za-z0-9]{32})\//)[1];
      assert.equal((await call('admin', 'POST', '/servants', { name: 'تاني', phone: p })).status, 400, 'الرقم مكرر');
    });

    test('الخادم الجديد بيعمل كلمة السر من الدعوة، والدعوة بتشتغل مرة واحدة', async () => {
      assert.equal((await call('guest', 'POST', '/invite', { code, password: 'short' })).status, 400);
      const r = await call('guest', 'POST', '/invite', { code, password: pass });
      assert.equal(r.status, 200);
      assert.equal((await call('guest', 'POST', '/invite', { code, password: pass })).status, 404, 'مرة واحدة بس');
      const login = await call('guest', 'POST', '/login', { who: p, password: pass });
      assert.equal(login.status, 200, 'بيدخل بموبايله');
    });

    test('الخادم الجديد معاه صلاحية الحضور ومن غير صلاحية تعديل المحتوى', async () => {
      const u = (await wp('admin', 'GET', `/users/${s.id}?context=edit`)).data;
      assert.equal(u.capabilities.stmina_attend, true);
      assert.ok(!u.capabilities.edit_posts);
    });

    test('صلاحية المحرر مابتدّيش صلاحية الحضور', async () => {
      const r = await wp('admin', 'POST', '/users', { username: 'ed' + Date.now(), email: `ed${Date.now()}@example.com`, password: pass + 'x', roles: ['editor'] });
      assert.equal(r.status, 201);
      const caps = (await wp('admin', 'GET', `/users/${r.data.id}?context=edit`)).data.capabilities;
      assert.ok(caps.edit_posts && !caps.stmina_attend);
      const me = (await wp('admin', 'GET', '/users/me')).data;
      await wp('admin', 'DELETE', `/users/${r.data.id}?force=true&reassign=${me.id}`);
    });

    test('سحب الصلاحية بيمنعه فورًا، ومينفعش تسحب صلاحية نفسك', async () => {
      const me = (await call('admin', 'GET', '/servants')).data.servants.find(x => x.me);
      assert.equal((await call('admin', 'DELETE', `/servants/${me.id}`)).status, 400);
      assert.equal((await call('admin', 'DELETE', `/servants/${s.id}`)).status, 200);
      const login = await call('guest', 'POST', '/login', { who: p, password: pass });
      assert.equal(login.status, 403, 'الحساب موجود بس مالوش صلاحية');
      assert.ok(!(await call('admin', 'GET', '/servants')).data.servants.some(x => x.id === s.id));
    });

    test('تنضيف: حساب الاختبار بيتمسح', async () => {
      const me = (await wp('admin', 'GET', '/users/me')).data;
      assert.equal((await wp('admin', 'DELETE', `/users/${s.id}?force=true&reassign=${me.id}`)).status, 200);
    });
  });
});

describe('المسح من غير نت والمزامنة (المهام 21 لـ 23)', () => {
  const at = (h, m = 0) => `${todayCairo()} ${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:00`;
  const uid = () => Math.random().toString(36).slice(2) + Date.now();
  let a, b, c, d, s1, s2, oldToken;

  before(async () => {
    const add = async n => (await call('servant', 'POST', '/members', { full_name: n + ' ' + Date.now(), phone: phone() })).data;
    a = await add('طابور أ'); b = await add('طابور ب'); c = await add('طابور ج'); d = await add('طابور د');
    oldToken = c.qr_token;
    await call('servant', 'POST', `/members/${c.id}/reissue`); // كارت ج القديم بقى ملغي
    s1 = (await call('servant', 'POST', '/sessions', { kind: 'activity', date: todayCairo() })).data;
    s2 = (await call('servant', 'POST', '/sessions', { kind: 'service', date: todayCairo() })).data;
    assert.ok(s1.id && s2.id, 'الجلستين اتفتحوا');
  });

  test('الشنطة للخدام بس، وفيها الأسماء وأكواد الكروت من غير موبايلات', async () => {
    assert.equal((await call('guest', 'GET', `/sessions/${s1.id}/pack`)).status, 401);
    assert.equal((await call('subscriber', 'GET', `/sessions/${s1.id}/pack`)).status, 403);
    const p = (await call('servant', 'GET', `/sessions/${s1.id}/pack`)).data;
    const row = p.members.find(x => x.id === a.id);
    assert.equal(row.token, a.qr_token);
    assert.equal(row.status, null);
    const text = JSON.stringify(p);
    assert.ok(!text.includes('phone') && !text.includes(a.phone), 'مفيش أي موبايل');
  });

  const batch = () => [
    { id: 'op1-' + s1.id, session_id: s1.id, type: 'scan', code: a.card_url, at: at(10, 5) },
    { id: 'op2-' + s1.id, session_id: s1.id, type: 'set', member_id: b.id, status: 'excused', at: at(10, 6) },
    { id: 'op3-' + s1.id, session_id: s1.id, type: 'scan', code: oldToken, at: at(10, 7) },
    { id: 'op4-' + s1.id, session_id: s1.id, type: 'scan', code: 'z'.repeat(32), at: at(10, 8) },
  ];

  test('المزامنة: كل عملية بنتيجتها، والمرفوض بسببه', async () => {
    assert.equal((await call('guest', 'POST', '/sync', { ops: batch() })).status, 401);
    const r = await call('servant', 'POST', '/sync', { ops: batch() });
    assert.equal(r.status, 200);
    assert.deepEqual(r.data.results.map(x => x.result), ['ok', 'ok', 'revoked', 'unknown']);
    assert.deepEqual(r.data.results.map(x => x.id), batch().map(x => x.id), 'كل نتيجة برقم عمليتها');
    assert.equal(r.data.results[2].member.id, c.id, 'الكارت الملغي معروف لمين');
  });

  test('السجل بطريقة التسجيل واسم الخادم والوقت الأصلي', async () => {
    const recs = (await call('servant', 'GET', `/sessions/${s1.id}/records`)).data;
    const ra = recs.find(x => x.member_id === a.id), rb = recs.find(x => x.member_id === b.id);
    assert.deepEqual([ra.status, ra.method, ra.recorded_at], ['present', 'scan', at(10, 5)]);
    assert.deepEqual([rb.status, rb.method, rb.recorded_at], ['excused', 'manual', at(10, 6)]);
    assert.ok(ra.recorded_by, 'اسم الخادم');
  });

  test('نفس الدفعة مرتين مابتعملش سجلات مكررة', async () => {
    const r = await call('servant', 'POST', '/sync', { ops: batch() });
    assert.deepEqual(r.data.results.map(x => x.result), ['dup', 'dup', 'revoked', 'unknown']);
    const recs = (await call('servant', 'GET', `/sessions/${s1.id}/records`)).data;
    assert.equal(recs.filter(x => x.member_id === a.id).length, 1);
    assert.equal(recs.length, 2);
  });

  test('عملية متأخرة بعد الإنهاء: "غاب" بيتحوّل "حضر" في نفس السجل', async () => {
    await call('servant', 'POST', `/sessions/${s2.id}/close`);
    let roster = (await call('servant', 'GET', `/sessions/${s2.id}/roster`)).data;
    assert.equal(roster.find(x => x.member_id === d.id).status, 'absent', 'الإنهاء سجّله غايب');
    const r = await call('servant', 'POST', '/sync', { ops: [{ id: uid(), session_id: s2.id, type: 'scan', code: d.qr_token, at: at(9, 30) }] });
    assert.equal(r.data.results[0].result, 'ok');
    assert.equal(r.data.results[0].late, true);
    const recs = (await call('servant', 'GET', `/sessions/${s2.id}/records`)).data.filter(x => x.member_id === d.id);
    assert.equal(recs.length, 1, 'من غير سجل تاني');
    assert.deepEqual([recs[0].status, recs[0].method, recs[0].recorded_at], ['present', 'scan', at(9, 30)]);
  });

  test('الوقت اللي في المستقبل بيبقى وقت المزامنة', async () => {
    const r = await call('servant', 'POST', '/sync', { ops: [{ id: uid(), session_id: s1.id, type: 'scan', code: d.qr_token, at: '2099-01-01 10:00:00' }] });
    assert.equal(r.data.results[0].result, 'ok');
    assert.notEqual(r.data.results[0].recorded_at, '2099-01-01 10:00:00');
  });

  test('تنضيف', async () => {
    for (const s of [s1, s2]) assert.equal((await call('servant', 'DELETE', `/sessions/${s.id}`)).status, 200);
  });
});
