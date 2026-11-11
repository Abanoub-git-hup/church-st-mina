// ياخد نسخة من صفحات الموقع الحي على WordPress ويحفظها في design/*.html وdesign/posts/*.html، علشان معاينة Vercel تبقى مطابقة ومستقلة بالكامل:
// - ملفات CSS وJS بتاعة القالب بتتنسخ من wordpress/themes/st-mina/assets لـ design/assets.
// - صور القالب بتتربط بفولدر media/، والصور المرفوعة من لوحة التحكم بتتنزّل في media/uploads/.
// - كل صفحات الخدمات والعظات والأخبار الفردية بتتنسخ بالكامل بروابطها الحقيقية.
// صفحات الحضور (attend-*) والقالب _inner.html ماتتلمسش.
// التشغيل: node tools/sync-from-wp.js   ثم   node tools/build-preview.js
const fs = require('fs');
const path = require('path');
const { optimize } = require('./perf-transform');

const BASE = 'https://st-mina.abanoubmaurice.com';
const root = path.resolve(__dirname, '..');
const design = path.join(root, 'design');
const postsDir = path.join(design, 'posts');
const themeAssets = path.join(root, 'wordpress/themes/st-mina/assets');

// الصفحات اللي ليها رابط ثابت: اسم الملف = اسم الصفحة في WordPress
const FIXED = ['church-history', 'church-fathers', 'church-gallery', 'church-location', 'worship', 'services', 'library', 'news'];
// الصفحات المفردة الأساسية اللي بتحافظ على أسماء ملفاتها الأصلية في جذر design/
const SERVICE = '/service/اجتماع-الشباب/';
const SEASON = '/news/عيد-الميلاد-المجيد/';

const enc = p => p.split('/').map(s => encodeURIComponent(decodeURI(s))).join('/');
const norm = p => '/' + decodeURI(p).replace(/^\/+|\/+$/g, '').toLowerCase() + '/';
const sleep = ms => new Promise(r => setTimeout(r, ms));

const get = async url => {
  for (let i = 0; i < 5; i++) {
    try {
      const res = await fetch(url, { redirect: 'follow' });
      if (res.ok || res.status === 404) return res.text();
    } catch {}
    await sleep(400 * (i + 1));
  }
  throw new Error(`فشل تحميل: ${url}`);
};

const getBuf = async url => {
  for (let i = 0; i < 5; i++) {
    try {
      const res = await fetch(url, { redirect: 'follow' });
      if (res.ok) return Buffer.from(await res.arrayBuffer());
    } catch {}
    await sleep(400 * (i + 1));
  }
  throw new Error(`فشل تحميل صورة: ${url}`);
};

// خريطة كل الروابط المفردة لأسماء ملفاتها في المعاينة
const routeMap = new Map();

const toPreview = p => {
  const slug = decodeURI(p).replace(/^\/|\/$/g, '');
  if (slug === '') return 'home.html';
  if (FIXED.includes(slug)) return `${slug}.html`;
  const key = norm(p);
  if (routeMap.has(key)) return routeMap.get(key);
  if (slug.startsWith('service/')) return 'service.html';
  if (slug.startsWith('sermon/')) return 'sermon.html';
  if (slug.startsWith('news/')) return 'news-item.html';
  return null;
};

// خريطة كل الصور الأصلية في media/ بالاسم علشان نستغني عن نسخ wp-content/uploads المكررة
const mediaMap = new Map([['cropped-logo.png', '../media/brand/logo.png']]);
(function walkMedia(d) {
  for (const f of fs.readdirSync(d, { withFileTypes: true })) {
    if (f.name === 'uploads' || f.name === 'design-refs') continue;
    const p = path.join(d, f.name);
    if (f.isDirectory()) walkMedia(p);
    else mediaMap.set(f.name.toLowerCase(), '../' + path.relative(root, p).replace(/\\/g, '/'));
  }
})(path.join(root, 'media'));

const resolveUpload = file => {
  const base = decodeURI(file).split('/').pop().replace(/-\d+x\d+(?=\.\w+$)/, '').toLowerCase();
  return mediaMap.get(base) || `../media/${base}`;
};

const transform = html => {
  const esc = s => s.replace(/[.*+?^${}()|[\]\\/]/g, '\\$&');
  const theme = `${BASE}/wp-content/themes/st-mina/assets/`;
  const uploads = `${BASE}/wp-content/uploads/`;
  return html
    // حاجات WordPress اللي مالهاش لازمة في نسخة ثابتة
    .replace(/<script type="speculationrules">[\s\S]*?<\/script>\s*/g, '')
    .replace(/<script id="wp-emoji-settings"[\s\S]*?<\/script>\s*/g, '')
    .replace(/<script type="module">[\s\S]*?wp-emoji[\s\S]*?<\/script>\s*/g, '')
    .replace(/<link rel=["'](?:https:\/\/api\.w\.org\/|EditURI|alternate|canonical|shortlink)["'][^>]*>\s*/g, '')
    .replace(/<link rel=["']stylesheet["'][^>]*wp-includes\/blocks[^>]*>\s*/g, '')
    .replace(/<meta name="generator"[^>]*>\s*/g, '')
    // SITE_BASE بيخلي site.js يحوّل الروابط لشكل WordPress، والمعاينة روابطها .html
    .replace(/<script id="stmina-site-js-before">[\s\S]*?<\/script>\s*/g, '')
    // روابط متهرّبة جوه JSON (https:\/\/...): بتتفك الأول علشان باقي التحويلات تشتغل عليها
    .replace(new RegExp(esc(BASE.replace(/\//g, '\\/')) + '(\\\\/[^"\\s]*)', 'g'), (_, rest) => BASE + rest.replace(/\\\//g, '/'))
    // شيل srcset وsizes بتاعة النسخ المصغرة في WordPress علشان نربط بالصورة الأصلية بس
    .replace(/\s+srcset="[^"]*\/wp-content\/uploads\/[^"]*"/g, '')
    .replace(/\s+sizes="[^"]*"/g, '')
    // صور القالب والمرفوعة ← media/ الأصلية، وملفات CSS وJS ← assets/
    .replace(new RegExp(esc(theme) + 'media/', 'g'), '../media/')
    .replace(new RegExp(esc(uploads) + '([^"\'\\s<>)]+)', 'g'), (_, f) => resolveUpload(f))
    .replace(new RegExp(esc(theme) + '([\\w.-]+?)(?:\\?ver=[\\w.]+)?(?=["\'\\s])', 'g'), 'assets/$1')
    // روابط الصفحات ← أسماء ملفات المعاينة
    .replace(new RegExp(`href="${esc(BASE)}(/[^"#?]*)?(#[^"]*)?"`, 'g'), (all, p = '/', hash = '') => {
      const file = toPreview(p);
      return file ? `href="${file}${hash}"` : all;
    });
};

(async () => {
  fs.mkdirSync(postsDir, { recursive: true });
  const rawHtml = new Map();
  const uploadFiles = new Set();

  // 1) تحميل الصفحات الرئيسية والثابتة لاكتشاف كل الروابط الفردية
  const basePages = [['home.html', '/'], ...FIXED.map(s => [`${s}.html`, `/${s}/`]), ['404.html', '/page-not-found-preview/']];
  for (const [file, p] of basePages) {
    const h = await get(BASE + enc(p));
    rawHtml.set(file, { path: p, dir: design, html: h });
    await sleep(60);
  }

  // 2) اكتشاف كل صفحات الخدمات والعظات والأخبار الفردية بالترتيب
  const found = { service: [], sermon: [], news: [] };
  const seen = new Set();
  for (const { html } of rawHtml.values()) {
    for (const m of html.matchAll(new RegExp(`href="${BASE}(/(service|sermon|news)/[^"#?]+)"`, 'g'))) {
      const k = norm(m[1]);
      if (!seen.has(k)) {
        seen.add(k);
        found[m[2]].push(k);
      }
    }
  }

  // تعيين أسماء الملفات: الصفحات الـ 4 الأساسية في design/، والباقي في design/posts/
  const mainService = norm(SERVICE);
  const mainSeason = norm(SEASON);
  const mainSermon = found.sermon[0];
  const mainNews = found.news.find(k => k !== mainSeason);

  routeMap.set(mainService, 'service.html');
  if (mainSermon) routeMap.set(mainSermon, 'sermon.html');
  routeMap.set(mainSeason, 'season.html');
  if (mainNews) routeMap.set(mainNews, 'news-item.html');

  let sIdx = 2, srIdx = 2, nIdx = 2;
  for (const k of found.service) if (!routeMap.has(k)) routeMap.set(k, `service-${sIdx++}.html`);
  for (const k of found.sermon) if (!routeMap.has(k)) routeMap.set(k, `sermon-${srIdx++}.html`);
  for (const k of found.news) if (!routeMap.has(k)) routeMap.set(k, `news-item-${nIdx++}.html`);

  // 3) تحميل كل الصفحات الفردية الـ 34
  const rootFiles = new Set(['service.html', 'sermon.html', 'season.html', 'news-item.html']);
  for (const [p, file] of routeMap.entries()) {
    const h = await get(BASE + enc(p));
    rawHtml.set(file, { path: p, dir: rootFiles.has(file) ? design : postsDir, html: h });
    await sleep(60);
  }

  // 4) تحويل وحفظ كل الصفحات
  for (const [file, { path: p, dir, html: raw }] of rawHtml.entries()) {
    const html = optimize(transform(raw));
    if (!html.includes('</html>')) throw new Error(`صفحة ناقصة: ${p}`);
    const note = `<!-- نسخة من ${decodeURI(BASE + p)} اتعملت بـ tools/sync-from-wp.js. ماتعدّلش هنا: عدّل القالب أو المحتوى، وشغّل السكربت تاني -->\n`;
    fs.writeFileSync(path.join(dir, file), html.replace(/^(<!doctype html>\s*)/i, `$1${note}`));
    console.log(`${file} ← ${decodeURI(p)}`);
  }

  // 6) ملفات القالب المشتركة والخاصة بكل صفحة
  for (const f of fs.readdirSync(themeAssets)) {
    if (/\.(css|js)$/.test(f)) fs.copyFileSync(path.join(themeAssets, f), path.join(design, 'assets', f));
  }
  console.log(`تم نسخ ${rawHtml.size} صفحة وCSS وJS بالكامل`);
})().catch(e => { console.error(e.message); process.exit(1); });
