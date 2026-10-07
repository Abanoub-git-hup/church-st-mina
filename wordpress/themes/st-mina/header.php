<?php
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
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="i-cross" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/><circle cx="12" cy="3.2" r="1.4"/><circle cx="12" cy="20.8" r="1.4"/><circle cx="3.2" cy="12" r="1.4"/><circle cx="20.8" cy="12" r="1.4"/><circle cx="12" cy="12" r="2.2"/></symbol>
  <symbol id="i-nw" viewBox="0 0 24 24"><path d="M17 17 7 7M7 17V7h10"/></symbol>
  <symbol id="i-left" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></symbol>
  <symbol id="i-right" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></symbol>
  <symbol id="i-play" viewBox="0 0 24 24"><path d="M7 4.5v15l12.5-7.5L7 4.5Z"/></symbol>
  <symbol id="i-pin" viewBox="0 0 24 24"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></symbol>
  <symbol id="i-church" viewBox="0 0 24 24"><path d="M12 2v4M10 4h4M18 22V11l-6-5-6 5v11M2 22h20M6 14l-4 3v5M18 14l4 3v5M10 22v-4a2 2 0 0 1 4 0v4"/></symbol>
  <symbol id="i-calendar" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></symbol>
  <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></symbol>
  <symbol id="i-headphones" viewBox="0 0 24 24"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></symbol>
  <symbol id="i-file" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M8 13h8M8 17h5"/></symbol>
  <symbol id="i-book" viewBox="0 0 24 24"><path d="M2 4h6a4 4 0 0 1 4 4v13a3 3 0 0 0-3-3H2Z"/><path d="M22 4h-6a4 4 0 0 0-4 4v13a3 3 0 0 1 3-3h7Z"/></symbol>
  <symbol id="i-users" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></symbol>
  <symbol id="i-heart" viewBox="0 0 24 24"><path d="M19 14c1.5-1.5 3-3.2 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.8 0-3 .5-4.5 2-1.5-1.5-2.7-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4 3 5.5l7 7Z"/></symbol>
  <symbol id="i-candle" viewBox="0 0 24 24"><path d="M9 22V11h6v11M7 22h10M12 11V9M12 3c1.5 1.8 2 3 0 5-2-2-1.5-3.2 0-5Z"/></symbol>
  <symbol id="i-grad" viewBox="0 0 24 24"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c3 3 9 3 12 0v-5M22 10v6"/></symbol>
  <symbol id="i-mask" viewBox="0 0 24 24"><path d="M3 4h18v6a9 9 0 0 1-18 0Z"/><path d="M8 9h.01M16 9h.01M9 14a4 4 0 0 0 6 0"/></symbol>
  <symbol id="i-whatsapp" viewBox="0 0 24 24"><path d="M3 21l1.65-3.8A9 9 0 1 1 7.8 20.3L3 21Z"/><path d="M9 10a.5.5 0 0 0 1 0V9a.5.5 0 0 0-1 0v1a5 5 0 0 0 5 5h1a.5.5 0 0 0 0-1h-1a.5.5 0 0 0 0 1"/></symbol>
  <symbol id="i-facebook" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3Z"/></symbol>
  <symbol id="i-youtube" viewBox="0 0 24 24"><path d="M2.5 17a24 24 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.6 49.6 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24 24 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.6 49.6 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/></symbol>
  <symbol id="i-menu" viewBox="0 0 24 24"><path d="M4 8h16M8 16h12"/></symbol>
  <symbol id="i-x" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></symbol>
  <symbol id="i-lock" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></symbol>
  <symbol id="i-phone" viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2Z"/></symbol>
</svg>

<div class="site-cover" aria-hidden="true"><img src="<?php stmina_media( 'church/bg-light-beams.jpg' ); ?>" alt=""><span class="veil"></span></div>

<a class="skip" href="#main">تخطَّ إلى المحتوى</a>

<!-- شريط عائم يظهر بعد الواجهة -->
<div class="float-nav" id="floatNav">
  <a class="brand" href="#top"><img src="<?php stmina_media( 'brand/logo.png' ); ?>" alt="" width="38" height="38"><span><b><span class="nw">كنيسة العذراء ومارمينا</span> <span class="nw">والبابا كيرلس</span></b>الجبل الأصفر</span></a>
  <nav aria-label="القائمة">
    <ul><li><a href="<?php stmina_link( 'church-history' ); ?>">الكنيسة</a></li><li><a href="<?php stmina_link( 'worship' ); ?>">العبادة</a></li><li><a href="<?php stmina_link( 'services' ); ?>">الخدمات</a></li><li><a href="<?php stmina_link( 'library' ); ?>">المكتبة</a></li><li><a href="<?php stmina_link( 'news' ); ?>">الأخبار</a></li></ul>
  </nav>
  <button class="menu-btn" data-open-menu aria-label="فتح القائمة" aria-controls="drawer"><svg class="icon"><use href="#i-menu"/></svg></button>
</div>

<div class="drawer" id="drawer" role="dialog" aria-modal="true" aria-label="القائمة">
  <div class="drawer-top">
    <a class="brand" href="#top"><img src="<?php stmina_media( 'brand/logo.png' ); ?>" alt=""><span><b>كنيسة السيدة العذراء ومارمينا</b><span class="nw">والبابا كيرلس السادس</span> <span class="nw"><span class="sep">· </span>الجبل الأصفر</span></span></a>
    <button class="menu-btn" id="menuClose" aria-label="إغلاق القائمة"><svg class="icon"><use href="#i-x"/></svg></button>
  </div>
  <ul><li><a href="#top">الرئيسية</a></li><li><a href="<?php stmina_link( 'church-history' ); ?>">الكنيسة</a></li><li><a href="<?php stmina_link( 'worship' ); ?>">العبادة</a></li><li><a href="<?php stmina_link( 'services' ); ?>">الخدمات</a></li><li><a href="<?php stmina_link( 'library' ); ?>">المكتبة</a></li><li><a href="<?php stmina_link( 'news' ); ?>">الأخبار</a></li></ul>
  <div class="socials">
    <a class="circle" href="https://www.facebook.com/santmarychurch" target="_blank" rel="noopener" aria-label="فيسبوك"><svg class="icon"><use href="#i-facebook"/></svg></a>
    <a class="circle" href="https://www.youtube.com/@%D8%A7%D9%84%D9%82%D9%85%D8%B5%D9%83%D9%8A%D8%B1%D9%84%D8%B3%D8%B1%D9%88%D9%85%D8%A7%D9%86%D9%8A/" target="_blank" rel="noopener" aria-label="قناة يوتيوب"><svg class="icon"><use href="#i-youtube"/></svg></a>
    <a class="circle" href="https://wa.me/201227445837" target="_blank" rel="noopener" aria-label="واتساب الكنيسة"><svg class="icon"><use href="#i-whatsapp"/></svg></a>
  </div>
</div>

<main id="main">
