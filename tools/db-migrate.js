// يشغّل ملفات supabase/migrations/*.sql بالترتيب على قاعدة البيانات اللي في DATABASE_URL.
// كل ملف بيتشغّل مرة واحدة بس (متسجّل في جدول _migrations)، وجوه transaction: يا يتنفّذ كله يا مايتنفّذش.
// التشغيل: node --env-file=.env.local tools/db-migrate.js
const fs = require('fs');
const path = require('path');
const postgres = require('postgres');

const dir = path.resolve(__dirname, '../supabase/migrations');

(async () => {
  if (!process.env.DATABASE_URL) throw new Error('DATABASE_URL مش موجود. حطه في .env.local');
  const sql = postgres(process.env.DATABASE_URL, { prepare: false, max: 1, onnotice: () => {} });
  await sql`create table if not exists _migrations (name text primary key, applied_at timestamptz not null default now())`;
  await sql`alter table _migrations enable row level security`;
  const done = new Set((await sql`select name from _migrations`).map(r => r.name));
  for (const f of fs.readdirSync(dir).filter(f => f.endsWith('.sql')).sort()) {
    if (done.has(f)) { console.log(`اتعمل قبل كده: ${f}`); continue; }
    await sql.begin(async t => {
      await t.unsafe(fs.readFileSync(path.join(dir, f), 'utf8'));
      await t`insert into _migrations (name) values (${f})`;
    });
    console.log(`اتعمل: ${f}`);
  }
  await sql.end();
})().catch(e => { console.error(e.message); process.exit(1); });
