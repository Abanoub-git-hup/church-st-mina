// تحويل شاشات الحضور من design/attend-*.html لملفات PHP في st-mina-attendance/screens/.
// الشكل (HTML والتنسيق الخاص بكل شاشة) زي التصميم بالظبط، وبنشيل: لافتة المراجعة، والبيانات الوهمية،
// وسكريبت النموذج. وبنحط مكانهم سكريبت حقيقي بيكلم REST (assets/<الشاشة>.js).
// التشغيل: node tools/convert-attend.js
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..');
const OUT = path.join(ROOT, 'wordpress/plugins/st-mina-attendance/screens');
const SCREENS = { login: 'attend-login.html', members: 'attend-members.html', member: 'attend-member.html', card: 'attend-card.html', sessions: 'attend-sessions.html', scan: 'attend-scan.html', session: 'attend-session.html', import: 'attend-import.html', more: 'attend-more.html', dashboard: 'attend-dashboard.html' };
// أجزاء في التصميم مهامها لسه ماتعملتش، فبتتشال من الشاشة لحد ما تتعمل:
// رسالة الخدام (19)، والافتقاد (18)، والخدام (24)
const LATER = {
  more: [
    /[ \t]*<!-- رسالة الخدام -->\n[ \t]*<section class="glass pane" data-pane="msg">[\s\S]*?<\/section>\n\n/,
    /[ \t]*<!-- الافتقاد -->\n[ \t]*<section class="glass pane" data-pane="fu">[\s\S]*?<\/section>\n\n/,
    /[ \t]*<!-- الخدام -->\n[ \t]*<section class="glass pane" data-pane="srv">[\s\S]*?<\/section>\n\n/,
  ],
};

// اسم الشاشة في التصميم ← رابطها في WordPress
const link = name => `<?php echo esc_url( stmina_att_url( '${name}' ) ); ?>`;

fs.mkdirSync(OUT, { recursive: true });
for (const [key, file] of Object.entries(SCREENS)) {
  let h = fs.readFileSync(path.join(ROOT, 'design', file), 'utf8');

  // لافتة المراجعة (للنموذج بس) والتعليق اللي قبلها
  // (اللافتة ممكن تبقى في سطر واحد زي "المزيد"، أو في كذا سطر بأزرار الحالات)
  h = h.replace(/[ \t]*<!-- لافتة المراجعة[^\n]*\n[ \t]*<div class="review"(?:[^\n]*<\/div>|[\s\S]*?\n[ \t]*<\/div>)\n/, '');
  // البيانات الوهمية وسكريبت النموذج في آخر الصفحة
  h = h.replace(/[ \t]*<script src="assets\/attend-demo\.js"><\/script>\n/, '');
  // الافتقاد (المهمة 18) مؤجّل بطلب المستخدم: التبويب بيتشال من شريط التنقل في كل الشاشات،
  // والتصميم فاضل زي ما هو لو رجعناله. لما يرجع، امسح السطر ده
  h = h.replace(/[ \t]*<li><a href="attend-followup\.html"[^\n]*<\/li>\n/g, '');
  for (const re of LATER[key] || []) {
    if (!re.test(h)) throw new Error(`${file}: مالقيتش ${re}`);
    h = h.replace(re, '');
  }
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
