// يبني نسخة المعاينة العامة في dist/ من design/home.html.
// ينسخ فقط الصور التي تستخدمها الصفحة، ويستبدل أي صورة مدرجة في SENSITIVE،
// ويمنع فهرسة محركات البحث. يفشل البناء لو بقيت أي صورة من القائمة.
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const dist = path.join(root, 'dist');

// صور لا تُنشر، وبديل كل واحدة. فارغة حاليًا: المستخدم وافق على نشر كل الصور (2026-10-06).
// مثال: 'services/sunday-school/primary-football-1.jpg': 'church/bg-light-beams.jpg'
const SENSITIVE = {};
const NEUTRAL_CAP = 'من داخل الكنيسة';

let html = fs.readFileSync(path.join(root, 'design', 'home.html'), 'utf8');

// أي عنصر في المعرض يعرض صورة حساسة: تبديل الصورة وجعل الوصف محايدًا
html = html.replace(/<button class="g[^"]*"[^>]*>[\s\S]*?<\/button>/g, el => {
  const hit = Object.keys(SENSITIVE).find(k => el.includes(k));
  if (!hit) return el;
  return el
    .replace(/data-cap="[^"]*"/, `data-cap="${NEUTRAL_CAP}"`)
    .replace(/alt="[^"]*"/, `alt="${NEUTRAL_CAP}"`)
    .replace(/<span class="cap">[\s\S]*?<\/span><\/span>/, `<span class="cap">${NEUTRAL_CAP}</span></span>`);
});

// تبديل باقي الإشارات (كروت الخدمات ودوائر المجموعات والأخبار)
for (const [from, to] of Object.entries(SENSITIVE)) html = html.split(from).join(to);

// المسارات: الصفحة تصبح في جذر الموقع
html = html.split('../media/').join('media/');

// منع الفهرسة
html = html.replace('<meta charset="utf-8">', '<meta charset="utf-8">\n<meta name="robots" content="noindex, nofollow">');

// تحقق: لا صور حساسة
const leaked = Object.keys(SENSITIVE).filter(k => html.includes(k));
if (leaked.length) { console.error('صور حساسة ما زالت في الصفحة:', leaked); process.exit(1); }

// نسخ الصور المستخدمة فقط
fs.rmSync(dist, { recursive: true, force: true });
const used = [...new Set([...html.matchAll(/media\/[^"')\s]+/g)].map(m => decodeURI(m[0])))];
let bytes = 0;
for (const rel of used) {
  if (Object.keys(SENSITIVE).some(k => rel.endsWith(k))) { console.error('محاولة نسخ صورة حساسة:', rel); process.exit(1); }
  const src = path.join(root, rel);
  if (!fs.existsSync(src)) { console.error('صورة غير موجودة:', rel); process.exit(1); }
  const dest = path.join(dist, rel);
  fs.mkdirSync(path.dirname(dest), { recursive: true });
  fs.copyFileSync(src, dest);
  bytes += fs.statSync(src).size;
}

fs.writeFileSync(path.join(dist, 'index.html'), html);
fs.writeFileSync(path.join(dist, 'robots.txt'), 'User-agent: *\nDisallow: /\n');
fs.writeFileSync(path.join(dist, '_headers'), '/*\n  X-Robots-Tag: noindex, nofollow\n');

console.log(`تم: ${used.length} صورة (${(bytes / 1048576).toFixed(1)} MB) في dist/`);
