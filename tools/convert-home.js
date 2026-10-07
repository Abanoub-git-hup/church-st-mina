// تحويل design/home.html لملفات theme في wordpress/themes/st-mina (المهمة 02).
// بيقسم الصفحة header.php و front-page.php و footer.php، ويطلع الـ CSS والـ JS الخاصين بالرئيسية،
// وينقل site.css و site.js والصور المستخدمة بس. التشغيل: node tools/convert-home.js
const fs = require('fs'), path = require('path');
const ROOT = path.join(__dirname, '..'), DESIGN = path.join(ROOT, 'design'), THEME = path.join(ROOT, 'wordpress/themes/st-mina');
const html = fs.readFileSync(path.join(DESIGN, 'home.html'), 'utf8');
const media = new Set();

// الروابط: الصور من assets/media، وصفحات الموقع من دالة stmina_link
function php(s) {
  s = s.replace(/\.\.\/media\/([^"')\s]+)/g, (_, p) => { media.add(p); return `<?php stmina_media( '${p}' ); ?>`; });
  s = s.replace(/href="([a-z0-9-]+)\.html(#[^"]*)?"/g, (_, page, hash) => `href="<?php stmina_link( '${page}'${hash ? `, '${hash}'` : ''} ); ?>"`);
  return s;
}
const between = (a, b) => { const i = html.indexOf(a), j = html.indexOf(b, i); if (i < 0 || j < 0) throw new Error('marker: ' + a); return html.slice(i, j); };

// ---------- الـ CSS والـ JS الخاصين بالرئيسية ----------
const style = between('<style>', '</style>').replace('<style>', '').trim();
fs.mkdirSync(path.join(THEME, 'assets'), { recursive: true });
fs.writeFileSync(path.join(THEME, 'assets/home.css'), style.replace(/\.\.\/media\//g, 'media/') + '\n');
const homeJs = between('<script>\n// خاص بالرئيسية', '</script>\n</body>').replace('<script>\n', '');
fs.writeFileSync(path.join(THEME, 'assets/home.js'), homeJs.trim() + '\n');

// ---------- header.php ----------
const top = between('<svg width="0"', '<main id="main">');
fs.writeFileSync(path.join(THEME, 'header.php'), `<?php
/**
 * بداية كل صفحة: الـ head، والأيقونات، والشريط العائم، وقائمة الموبايل.
 * اتنقل من design/home.html بـ tools/convert-home.js
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>document.documentElement.classList.add('anim');</script>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
${php(top).trim()}

<main id="main">
`);

// ---------- front-page.php ----------
let body = php(between('<!-- ===== الواجهة', '</main>')).trim();
const R = [
  [/<h1 data-split>[\s\S]*?<\/h1>/, `<h1 data-split><?php echo esc_html( stmina_home( 'hero_verse' ) ); ?> <em><?php echo esc_html( stmina_home( 'hero_highlight' ) ); ?></em></h1>`],
  [/<span class="ref" data-intro>[\s\S]*?<\/span>/, `<span class="ref" data-intro>(<?php echo esc_html( stmina_home( 'hero_ref' ) ); ?>)</span>`],
  [/<b>الوصية مش قيد[\s\S]*?<\/b>\s*<small>[\s\S]*?<\/small>/, `<b><?php echo esc_html( stmina_home( 'quote_text' ) ); ?></b>\n        <small><?php echo esc_html( stmina_home( 'quote_author' ) ); ?></small>`],
  [/<blockquote data-split>[\s\S]*?<\/blockquote>/, `<blockquote data-split><?php echo esc_html( stmina_home( 'verse_text' ) ); ?></blockquote>`],
  [/<cite data-rise>[\s\S]*?<\/cite>/, `<cite data-rise>(<?php echo esc_html( stmina_home( 'verse_ref' ) ); ?>)</cite>`],
];
for (const [re, to] of R) { if (!re.test(body)) throw new Error('editable not found: ' + re); body = body.replace(re, to); }
fs.writeFileSync(path.join(THEME, 'front-page.php'), `<?php
/**
 * الصفحة الرئيسية: الـ 14 قسم من design/home.html.
 * النصوص اللي بتتعدّل من "إعدادات الرئيسية" بتيجي من دالة stmina_home().
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

${body}

<?php
get_footer();
`);

// ---------- footer.php ----------
let foot = php(between('</main>', '<script src="https://cdnjs')).replace('</main>', '').trim();
foot = foot.replace(/© 2026/, '© <?php echo esc_html( wp_date( \'Y\' ) ); ?>');
fs.writeFileSync(path.join(THEME, 'footer.php'), `<?php
/**
 * نهاية كل صفحة: الـ footer، وعارض الصور، و wp_footer.
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

${foot}

<?php wp_footer(); ?>
</body>
</html>
`);

// ---------- site.css و site.js ----------
const css = fs.readFileSync(path.join(DESIGN, 'assets/site.css'), 'utf8');
for (const m of css.matchAll(/\.\.\/\.\.\/media\/([^"')\s]+)/g)) media.add(m[1]);
fs.writeFileSync(path.join(THEME, 'assets/site.css'), css.replace(/\.\.\/\.\.\/media\//g, 'media/'));
fs.copyFileSync(path.join(DESIGN, 'assets/site.js'), path.join(THEME, 'assets/site.js'));

// ---------- الصور المستخدمة بس ----------
let bytes = 0;
for (const p of media) {
  const from = path.join(ROOT, 'media', p), to = path.join(THEME, 'assets/media', p);
  if (!fs.existsSync(from)) { console.warn('missing image:', p); continue; }
  fs.mkdirSync(path.dirname(to), { recursive: true }); fs.copyFileSync(from, to); bytes += fs.statSync(from).size;
}
console.log(`done: ${media.size} images, ${(bytes / 1048576).toFixed(1)} MB`);
