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

  test('مفيش مسار حذف', async () => {
    const r = await call('servant', 'DELETE', `/members/${member.id}`);
    assert.equal(r.status, 404);
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
    const date = pastDay();
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
    s = (await call('servant', 'POST', '/sessions', { kind: 'meeting', date: pastDay() })).data;
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
    const date = new Intl.DateTimeFormat('en-CA', { timeZone: 'Africa/Cairo' }).format(new Date()); // النهارده بتوقيت القاهرة زي الموقع
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
