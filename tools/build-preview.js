// يبني نسخة المعاينة العامة في dist/ من كل صفحات design/*.html والملفات المشتركة في design/assets/.
// home.html تصبح index.html، والقالب _inner.html يصبح inner.html (بدون الشرطة السفلية).
// ينسخ فقط الصور التي تستخدمها الصفحات، ويستبدل أي صورة مدرجة في SENSITIVE،
// ويمنع فهرسة محركات البحث. يفشل البناء لو بقيت أي صورة من القائمة.
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const design = path.join(root, 'design');
const dist = path.join(root, 'dist');

// صور لا تُنشر، وبديل كل واحدة. فارغة حاليًا: المستخدم وافق على نشر كل الصور (2026-10-06).
// مثال: 'services/sunday-school/primary-football-1.jpg': 'church/bg-light-beams.jpg'
const SENSITIVE = {};
const NEUTRAL_CAP = 'من داخل الكنيسة';

// اسم الصفحة في المعاينة
const outName = f => f === 'home.html' ? 'index.html' : f.replace(/^_/, '');
const pages = fs.readdirSync(design).filter(f => f.endsWith('.html'));

const build = file => {
  let html = fs.readFileSync(path.join(design, file), 'utf8');

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

  // المسارات: الصفحات تصبح في جذر الموقع
  html = html.split('../media/').join('media/');
  // الروابط بين الصفحات بأسمائها في المعاينة
  for (const p of pages) html = html.split(`href="${p}`).join(`href="${p === 'home.html' ? './' : outName(p)}`);

  // صفحة 404 تُعرض على أي مسار، فكل الروابط والملفات فيها تبدأ من جذر الموقع
  if (file === '404.html') {
    html = html.replace(/(src|href)="(media\/|assets\/)/g, '$1="/$2')
      .replace(/href="(?!https?:|#|\/|tel:|mailto:)([^"]+)"/g, 'href="/$1"');
  }

  // منع الفهرسة
  html = html.replace('<meta charset="utf-8">', '<meta charset="utf-8">\n<meta name="robots" content="noindex, nofollow">');

  // تحقق: لا صور حساسة
  const leaked = Object.keys(SENSITIVE).filter(k => html.includes(k));
  if (leaked.length) { console.error(`صور حساسة ما زالت في ${file}:`, leaked); process.exit(1); }
  return html;
};

fs.rmSync(dist, { recursive: true, force: true });
fs.mkdirSync(dist, { recursive: true });

// الصفحات
const used = new Set();
for (const file of pages) {
  const html = build(file);
  for (const m of html.matchAll(/media\/[^"')\s]+/g)) used.add(decodeURI(m[0]));
  fs.writeFileSync(path.join(dist, outName(file)), html);
}

// الملفات المشتركة (CSS وJS)
const assets = path.join(design, 'assets');
if (fs.existsSync(assets)) {
  fs.cpSync(assets, path.join(dist, 'assets'), { recursive: true });
  for (const f of fs.readdirSync(assets)) {
    for (const m of fs.readFileSync(path.join(assets, f), 'utf8').matchAll(/\.\.\/\.\.\/(media\/[^"')\s]+)/g)) used.add(decodeURI(m[1]));
  }
}

// نسخ الصور المستخدمة فقط
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

fs.writeFileSync(path.join(dist, 'robots.txt'), 'User-agent: *\nDisallow: /\n');
fs.writeFileSync(path.join(dist, '_headers'), '/*\n  X-Robots-Tag: noindex, nofollow\n');

console.log(`تم: ${pages.length} صفحة (${pages.map(outName).join('، ')})، و${used.size} صورة (${(bytes / 1048576).toFixed(1)} MB) في dist/`);
