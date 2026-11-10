// ياخد نسخة من صفحات الموقع الحي على WordPress ويحفظها في design/*.html، علشان معاينة Vercel تبقى مطابقة له.
// - ملفات CSS وJS بتاعة القالب بتتنسخ من wordpress/themes/st-mina/assets لـ design/assets.
// - صور القالب بتتربط بفولدر media/ (نفس الملفات بالظبط)، والصور المرفوعة من لوحة التحكم بتتحمّل من سيرفر WordPress.
// - روابط الصفحات بتتحوّل لأسماء ملفات المعاينة: /worship/ تبقى worship.html، وأي /service/... تبقى service.html.
// صفحات الحضور (attend-*) والقالب _inner.html ماتتلمسش.
// التشغيل: node tools/sync-from-wp.js   ثم   node tools/build-preview.js
const fs = require('fs');
const path = require('path');

const BASE = 'https://st-mina.abanoubmaurice.com';
const root = path.resolve(__dirname, '..');
const design = path.join(root, 'design');
const themeAssets = path.join(root, 'wordpress/themes/st-mina/assets');

// الصفحات اللي ليها رابط ثابت: اسم الملف = اسم الصفحة في WordPress
const FIXED = ['church-history', 'church-fathers', 'church-gallery', 'church-location', 'worship', 'services', 'library', 'news'];
// الصفحات المفردة: الصفحة اللي بتمثّل كل نوع في المعاينة
const SERVICE = '/service/اجتماع-الشباب/';
const SEASON = '/news/عيد-الميلاد-المجيد/';

const enc = p => p.split('/').map(encodeURIComponent).join('/').toLowerCase();
const get = async url => {
  const res = await fetch(url, { redirect: 'follow' });
  if (!res.ok && res.status !== 404) throw new Error(`${res.status} ${url}`);
  return res.text();
};
// أول رابط من نوع معيّن في صفحة القايمة (أول عظة في المكتبة، وأول خبر مش موسمي في الأخبار)
const firstLink = (html, type, skip = []) => {
  for (const m of html.matchAll(new RegExp(`href="${BASE}(/${type}/[^"#]+)"`, 'g'))) {
    if (!skip.includes(decodeURI(m[1]).toLowerCase())) return m[1];
  }
  throw new Error(`مفيش رابط ${type}`);
};

// رابط صفحة على الموقع الحي ← اسم ملفها في المعاينة (أو null لو مالهاش ملف)
const seasonSlug = decodeURI(SEASON).replace(/\//g, '');
const toPreview = p => {
  const slug = decodeURI(p).replace(/^\/|\/$/g, '');
  if (slug === '') return 'home.html';
  if (FIXED.includes(slug)) return `${slug}.html`;
  if (slug.startsWith('service/')) return 'service.html';
  if (slug.startsWith('sermon/')) return 'sermon.html';
  if (slug.startsWith('news/')) return slug.replace(/\//g, '') === seasonSlug ? 'season.html' : 'news-item.html';
  return null;
};

const transform = html => {
  const esc = s => s.replace(/[.*+?^${}()|[\]\\/]/g, '\\$&');
  const theme = `${BASE}/wp-content/themes/st-mina/assets/`;
  return html
    // حاجات WordPress اللي مالهاش لازمة في نسخة ثابتة
    .replace(/<script type="speculationrules">[\s\S]*?<\/script>\s*/g, '')
    .replace(/<script id="wp-emoji-settings"[\s\S]*?<\/script>\s*/g, '')
    .replace(/<script type="module">[\s\S]*?wp-emoji[\s\S]*?<\/script>\s*/g, '')
    .replace(/<link rel=["'](?:https:\/\/api\.w\.org\/|EditURI|alternate|canonical|shortlink)["'][^>]*>\s*/g, '')
    .replace(/<meta name="generator"[^>]*>\s*/g, '')
    // SITE_BASE بيخلي site.js يحوّل الروابط لشكل WordPress، والمعاينة روابطها .html
    .replace(/<script id="stmina-site-js-before">[\s\S]*?<\/script>\s*/g, '')
    // روابط متهرّبة جوه JSON (https:\/\/...): بتتفك الأول علشان باقي التحويلات تشتغل عليها
    .replace(new RegExp(esc(BASE.replace(/\//g, '\\/')) + '(\\\\/[^"\\s]*)', 'g'), (_, rest) => BASE + rest.replace(/\\\//g, '/'))
    // صور القالب ← media/ (نفس الملفات)، وملفات CSS وJS ← assets/
    .replace(new RegExp(esc(theme) + 'media/', 'g'), '../media/')
    .replace(new RegExp(esc(theme) + '([\\w.-]+?)(?:\\?ver=[\\w.]+)?(?=["\'\\s])', 'g'), 'assets/$1')
    // روابط الصفحات ← أسماء ملفات المعاينة. الصور المرفوعة وأي رابط تاني يفضل على الموقع الحي
    .replace(new RegExp(`href="${esc(BASE)}(/[^"#?]*)?(#[^"]*)?"`, 'g'), (all, p = '/', hash = '') => {
      const file = toPreview(p);
      return file ? `href="${file}${hash}"` : all;
    });
};

(async () => {
  const pages = [['home.html', '/'], ...FIXED.map(s => [`${s}.html`, `/${s}/`])];
  const library = await get(`${BASE}/library/`), news = await get(`${BASE}/news/`);
  pages.push(
    ['service.html', enc(SERVICE)],
    ['sermon.html', firstLink(library, 'sermon')],
    ['season.html', enc(SEASON)],
    ['news-item.html', firstLink(news, 'news', [SEASON.toLowerCase()])],
    ['404.html', '/page-not-found-preview/']
  );

  for (const [file, p] of pages) {
    const html = transform(await get(BASE + p));
    if (!html.includes('</html>')) throw new Error(`صفحة ناقصة: ${p}`);
    const note = `<!-- نسخة من ${decodeURI(BASE + p)} اتعملت بـ tools/sync-from-wp.js. ماتعدّلش هنا: عدّل القالب أو المحتوى، وشغّل السكربت تاني -->\n`;
    fs.writeFileSync(path.join(design, file), html.replace(/^(<!doctype html>\s*)/i, `$1${note}`));
    console.log(`${file} ← ${decodeURI(p)}`);
  }

  // ملفات القالب المشتركة والخاصة بكل صفحة
  for (const f of fs.readdirSync(themeAssets)) {
    if (/\.(css|js)$/.test(f)) fs.copyFileSync(path.join(themeAssets, f), path.join(design, 'assets', f));
  }
  console.log('تم نسخ CSS وJS من القالب لـ design/assets');
})().catch(e => { console.error(e.message); process.exit(1); });
