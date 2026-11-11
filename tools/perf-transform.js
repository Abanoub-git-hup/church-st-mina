// تحسينات الأداء على صفحات design/*.html (نتيجة فحص PageSpeed):
// - حذف أنماط Gutenberg الداخلية غير المستخدمة، و preconnect للخطوط ولـ cdnjs، و defer للسكربتات.
// - الصور الكبيرة بصيغة webp مع srcset، و lazy للصور تحت الشاشة، وشيل fetchpriority الغلط.
// بيتنادى من tools/sync-from-wp.js بعد التحويل، وينفع يتشغّل لوحده: node tools/perf-transform.js
const fs = require('fs');
const path = require('path');

// الصورة الأصلية ← عرض النسخة الكاملة والمصغّرة (webp). bg-light-beams ماعندهاش نسخة مصغّرة.
const IMAGES = {
  'church/hero-prayer.png': [1364, 720],
  'church/bg-light-beams.jpg': [512, null],
  'priests/fathers-group.png': [607, 360],
  'worship/deacons.jpeg': [960, 360],
  'worship/youth-night-liturgy-2.jpeg': [720, 320],
  'worship/youth-night-liturgy-3.jpeg': [960, 400],
  'services/youth/youth-meeting-2026.jpeg': [640, 320],
};

const PRECONNECT = [
  '<link rel="preconnect" href="https://fonts.googleapis.com">',
  '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>',
  '<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>',
].join('\n');

const optimize = html => {
  let out = html
    // أنماط Gutenberg اللي القالب مابيستخدمهاش
    .replace(/<style id=["'](?:wp-block-library|classic-theme-styles|global-styles|wp-emoji-styles)-inline-css["'][^>]*>[\s\S]*?<\/style>\s*/g, '')
    // defer للسكربتات الخارجية (الترتيب بيتحفظ مع defer)
    .replace(/<script id="(gsap-js|gsap-scrolltrigger-js|stmina-[\w-]+-js)" src=/g, '<script defer id="$1" src=');

  if (!out.includes('rel="preconnect"') && !out.includes("rel='preconnect'")) {
    out = out.replace(/(<meta name="viewport"[^>]*>\s*)/i, `$1${PRECONNECT}\n`);
  }

  out = out.replace(/<img\b[^>]*>/g, tag => {
    const m = tag.match(/src="((?:\.\.\/)+media\/)([^"]+)\.(png|jpe?g)"/);
    const key = m && `${m[2]}.${m[3]}`;
    const dims = key && IMAGES[key];
    if (!dims) return tag;
    const [full, sm] = dims;
    const base = `${m[1]}${m[2]}`;
    const hero = key === 'church/hero-prayer.png';
    let t = tag
      .replace(/\s(?:srcset|sizes)="[^"]*"/g, '')
      .replace(m[0], `src="${base}.webp"` + (sm ? ` srcset="${base}-sm.webp ${sm}w, ${base}.webp ${full}w" sizes="(max-width:${sm}px) 100vw, ${full}px"` : ''));
    if (hero) {
      t = t.replace(/\sfetchpriority="[^"]*"/, '').replace(/<img\b/, '<img fetchpriority="high" decoding="async"');
    } else {
      t = t.replace(/\sfetchpriority="[^"]*"/, '');
      if (!/\sloading=/.test(t)) t = t.replace(/<img\b/, '<img loading="lazy"');
      if (!/\sdecoding=/.test(t)) t = t.replace(/<img\b/, '<img decoding="async"');
    }
    return t;
  });
  return out;
};

module.exports = { optimize };

if (require.main === module) {
  const design = path.resolve(__dirname, '..', 'design');
  const files = [];
  for (const d of [design, path.join(design, 'posts')]) {
    if (fs.existsSync(d)) for (const f of fs.readdirSync(d)) if (f.endsWith('.html') && !f.startsWith('attend-') && !f.startsWith('_')) files.push(path.join(d, f));
  }
  let n = 0;
  for (const f of files) {
    const a = fs.readFileSync(f, 'utf8'), b = optimize(a);
    if (a !== b) { fs.writeFileSync(f, b); n++; }
  }
  console.log(`تم تحسين ${n} من ${files.length} صفحة`);
}
