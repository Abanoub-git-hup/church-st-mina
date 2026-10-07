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
