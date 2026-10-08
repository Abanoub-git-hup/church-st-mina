// تحويل شاشات الحضور من design/attend-*.html لملفات PHP في st-mina-attendance/screens/.
// الشكل (HTML والتنسيق الخاص بكل شاشة) زي التصميم بالظبط، وبنشيل: لافتة المراجعة، والبيانات الوهمية،
// وسكريبت النموذج. وبنحط مكانهم سكريبت حقيقي بيكلم REST (assets/<الشاشة>.js).
// التشغيل: node tools/convert-attend.js
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..');
const OUT = path.join(ROOT, 'wordpress/plugins/st-mina-attendance/screens');
const SCREENS = { login: 'attend-login.html', members: 'attend-members.html', member: 'attend-member.html', card: 'attend-card.html' };

// اسم الشاشة في التصميم ← رابطها في WordPress
const link = name => `<?php echo esc_url( stmina_att_url( '${name}' ) ); ?>`;

fs.mkdirSync(OUT, { recursive: true });
for (const [key, file] of Object.entries(SCREENS)) {
  let h = fs.readFileSync(path.join(ROOT, 'design', file), 'utf8');

  // لافتة المراجعة (للنموذج بس) والتعليق اللي قبلها
  h = h.replace(/[ \t]*<!-- لافتة المراجعة[^\n]*\n[ \t]*<div class="review"[\s\S]*?\n[ \t]*<\/div>\n/, '');
  // البيانات الوهمية وسكريبت النموذج في آخر الصفحة
  h = h.replace(/[ \t]*<script src="assets\/attend-demo\.js"><\/script>\n/, '');
  h = h.replace(/<script>\n\(\(\) => \{[\s\S]*?<\/script>\n(?=<\/body>)/, () => `<?php stmina_att_footer( '${key}' ); ?>\n`);
  // ملفات التنسيق
  h = h.replace('href="assets/site.css"', () => 'href="<?php echo esc_url( get_template_directory_uri() . \'/assets/site.css\' ); ?>"');
  h = h.replace('href="assets/attend.css"', () => 'href="<?php echo esc_url( STMINA_ATT_URL . \'assets/attend.css?ver=\' . STMINA_ATT_VERSION ); ?>"');
  // الصور من فولدر الـ theme (الشعار)
  h = h.replace(/src="\.\.\/media\/([^"]+)"/g, (_, f) => `src="<?php echo esc_url( get_template_directory_uri() . '/assets/media/${f}' ); ?>"`);
  // الصور جوه التنسيق الخاص بالشاشة (خلفية الكارت)
  h = h.replace(/url\("\.\.\/media\/([^"]+)"\)/g, (_, f) => `url("<?php echo esc_url( get_template_directory_uri() . '/assets/media/${f}' ); ?>")`);
  // الروابط
  h = h.replace(/href="home\.html"/g, () => 'href="<?php echo esc_url( home_url( \'/\' ) ); ?>"');
  h = h.replace(/href="attend-([a-z]+)\.html"/g, (_, n) => `href="${link(n)}"`);
  // ممنوع الأرشفة، زيادة على الهيدر X-Robots-Tag
  h = h.replace('<meta charset="utf-8">', () => '<meta charset="utf-8">\n<meta name="robots" content="noindex, nofollow">');

  if (/attend-[a-z]+\.html|assets\/attend-demo|class="review"/.test(h)) throw new Error(`${file}: لسه فيه حاجة من النموذج`);
  const head = `<?php\n/**\n * شاشة "${key}" في نظام الحضور. متولّدة من design/${file} بأداة tools/convert-attend.js،\n * فأي تعديل في الشكل يتعمل في التصميم وبعدين تتشغّل الأداة تاني.\n */\n\ndefined( 'ABSPATH' ) || exit;\n?>\n`;
  fs.writeFileSync(path.join(OUT, key + '.php'), head + h);
  console.log('✓', key + '.php');
}

// التنسيق المشترك وصورة الخلفية (جوه الـ plugin علشان يبقى مستقل عن الـ theme)
const ASSETS = path.join(ROOT, 'wordpress/plugins/st-mina-attendance/assets');
fs.mkdirSync(ASSETS, { recursive: true });
fs.writeFileSync(path.join(ASSETS, 'attend.css'),
  fs.readFileSync(path.join(ROOT, 'design/assets/attend.css'), 'utf8').replace('../../media/church/bg-praying-light.jpg', 'bg-praying-light.jpg'));
fs.copyFileSync(path.join(ROOT, 'media/church/bg-praying-light.jpg'), path.join(ASSETS, 'bg-praying-light.jpg'));
console.log('✓ assets/attend.css و bg-praying-light.jpg');
