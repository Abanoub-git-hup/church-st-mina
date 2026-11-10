// يجهّز نظام الحضور لـ Vercel من بلجن WordPress نفسه (wordpress/plugins/st-mina-attendance)، من غير ما يغيّر فيه:
// - الشاشات (screens/*.php) ← lib/att/screens.generated.js: نفس HTML، و<?php ?> بتتحوّل لروابط ثابتة
//   أو علامات {{HEAD}} و{{FOOTER}} بتتملي وقت فتح الصفحة (lib/att/screens.js).
// - ملفات assets ← dist/attend-assets/، مع تعديلين لـ Vercel: رأس X-Stmina بدل X-WP-Nonce في attend-app.js،
//   والـ service worker بيتخطّى /api/ بدل /wp-json/.
// - الـ service worker من جذر الموقع: dist/me-sw.js وdist/attend-sw.js (المجال لازم يغطي /me/ و/attend/).
// بيشتغل بعد tools/build-preview.js، لأن ده بيمسح dist/ الأول.
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const plugin = path.join(root, 'wordpress/plugins/st-mina-attendance');
const dist = path.join(root, 'dist');
const VER = (process.env.VERCEL_GIT_COMMIT_SHA || Date.now().toString(36)).slice(0, 8);

// كل <?php ... ?> في الشاشات ← بديله. أي حاجة مش معروفة بتوقف البناء، علشان مافيش PHP يوصل للمتصفح
const php = (code, file) => {
  const c = code.trim();
  if (/^\/\*\*[\s\S]*defined\( 'ABSPATH' \)/.test(c)) return ''; // تعليق أول الملف
  let m;
  if ((m = c.match(/get_template_directory_uri\(\) \. '\/assets\/site\.css'/))) return '/assets/site.css';
  if ((m = c.match(/get_template_directory_uri\(\) \. '\/assets\/media\/([^']+)'/))) return `/media/${m[1]}`;
  if ((m = c.match(/STMINA_ATT_URL \. 'assets\/([\w.-]+)\?ver=' \. STMINA_ATT_VERSION/))) return `/attend-assets/${m[1]}?ver=${VER}`;
  if ((m = c.match(/stmina_att_url\( '([a-z]+)' \)(?: \. '(#\w+)')?/))) return `/attend/${m[1]}/${m[2] || ''}`;
  if (/home_url\( '\/' \)/.test(c)) return '/';
  if (/stmina_att_(servant|card)_head\(\);/.test(c)) return '{{HEAD}}';
  if (/stmina_att_footer\( '[a-z]+' \);/.test(c)) return '{{FOOTER}}';
  throw new Error(`PHP مش معروف في ${file}: ${c.slice(0, 80)}`);
};

const screens = {};
for (const f of fs.readdirSync(path.join(plugin, 'screens')).filter(f => f.endsWith('.php'))) {
  const src = fs.readFileSync(path.join(plugin, 'screens', f), 'utf8');
  screens[f.replace(/\.php$/, '')] = src.replace(/<\?php([\s\S]*?)\?>\n?/g, (all, code) => {
    const out = php(code, f);
    return out === '' ? '' : out + (all.endsWith('\n') ? '\n' : '');
  }).trimStart();
}
fs.writeFileSync(path.join(root, 'lib/att/screens.generated.js'),
  '// متولّد من wordpress/plugins/st-mina-attendance/screens بأداة tools/build-attend.js. ماتعدّلش هنا.\n' +
  `module.exports = ${JSON.stringify({ version: VER, screens })};\n`);

// ملفات الشاشات
const out = path.join(dist, 'attend-assets');
fs.mkdirSync(out, { recursive: true });
for (const f of fs.readdirSync(path.join(plugin, 'assets'))) {
  let data = fs.readFileSync(path.join(plugin, 'assets', f));
  if (f === 'attend-app.js') {
    const s = data.toString();
    const patched = s.replace("'X-WP-Nonce': C.nonce", "'X-Stmina': '1'");
    if (patched === s) throw new Error('attend-app.js: رأس X-WP-Nonce اتغيّر، راجع التعديل');
    data = Buffer.from(patched);
  }
  if (f === 'me-sw.js') {
    const s = data.toString();
    const patched = s.replace('const SKIP = /\\/wp-json\\/|admin-ajax\\.php|\\/wp-admin\\//;', 'const SKIP = /\\/api\\//;');
    if (patched === s) throw new Error('me-sw.js: سطر SKIP اتغيّر، راجع التعديل');
    for (const name of ['me-sw.js', 'attend-sw.js']) fs.writeFileSync(path.join(dist, name), patched);
    continue;
  }
  fs.writeFileSync(path.join(out, f), data);
}

// صور الشاشات من فولدر media (الكارت وصفحة الدخول)
for (const rel of ['church/bg-praying-light.jpg', 'brand/logo.png']) {
  const dest = path.join(dist, 'media', rel);
  fs.mkdirSync(path.dirname(dest), { recursive: true });
  fs.copyFileSync(path.join(root, 'media', rel), dest);
}

console.log(`نظام الحضور: ${Object.keys(screens).length} شاشة، ونسخة ${VER}`);
