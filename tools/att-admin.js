// أول حساب أدمن لنظام الحضور (أو حساب اختبار)، وبيطبع رابط دعوة يعمل منه كلمة السر.
// في WordPress الأدمن كان مدير الموقع، هنا هو خادم بعلامة is_admin.
// التشغيل: node --env-file=.env.local tools/att-admin.js "الاسم" 01xxxxxxxxx [--test]
const crypto = require('crypto');
const postgres = require('postgres');

const [name, rawPhone, flag] = process.argv.slice(2);
const site = process.env.SITE_URL || 'https://church-st-mina.vercel.app';

(async () => {
  if (!process.env.DATABASE_URL) throw new Error('DATABASE_URL مش موجود. حطه في .env.local');
  const { phone } = require('../lib/att/core');
  const p = phone(rawPhone);
  if (!name || !p) throw new Error('الاستخدام: node tools/att-admin.js "الاسم" 01xxxxxxxxx [--test]');
  const sql = postgres(process.env.DATABASE_URL, { prepare: false, max: 1 });
  const test = flag === '--test';
  const [u] = await sql`insert into staff (display_name, phone, is_admin, is_test) values (${name}, ${p}, ${!test}, ${test})
    on conflict (phone) do update set display_name = excluded.display_name, active = true returning id`;
  // نفس طريقة الدعوة في lib/att/auth.js: الكود في الرابط، والمتخزّن بصمته بس
  const ABC = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  const code = Array.from({ length: 32 }, () => ABC[crypto.randomInt(ABC.length)]).join('');
  const hash = crypto.createHash('sha256').update(code).digest('hex');
  await sql`update staff set invite_hash = ${hash}, invite_expires_at = now() + interval '3 days' where id = ${u.id}`;
  console.log(`${test ? 'حساب اختبار' : 'أدمن'}: ${name} (${p})`);
  console.log(`رابط الدعوة (3 أيام، مرة واحدة): ${site}/attend/invite/${code}/`);
  await sql.end();
})().catch(e => { console.error(e.message); process.exit(1); });
