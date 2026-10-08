<?php
/**
 * شاشة لسه ماتعملتش (الجلسات، والمسح، والافتقاد ...): رسالة "جاية قريب" وشريط التنقل.
 * كل شاشة بتتعمل في مهمتها، وساعتها بيبقى ليها ملف باسمها هنا.
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="robots" content="noindex, nofollow">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#2a1c12">
<title>قريب | إعداد الخدام</title>
<meta name="description" content="دخول الخدام لنظام حضور خدمة إعداد الخدام بكنيسة السيدة العذراء ومارمينا والبابا كيرلس السادس.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@200;300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo esc_url( get_template_directory_uri() . '/assets/site.css' ); ?>">
<link rel="stylesheet" href="<?php echo esc_url( STMINA_ATT_URL . 'assets/attend.css?ver=' . STMINA_ATT_VERSION ); ?>">
<style>
.soon{flex:1;width:100%;max-width:420px;margin-inline:auto;padding:var(--s-10) var(--gut);display:flex;flex-direction:column;justify-content:center}
.soon .panel{padding:var(--s-8) var(--s-5);text-align:center}
.soon h1{font-size:1.6rem;font-weight:300;margin-bottom:var(--s-3)}
.soon p{color:var(--on-glass-2);font-size:.92rem;line-height:1.8;margin-bottom:var(--s-6)}
</style>
</head>
<body class="has-nav">
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="i-calendar" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></symbol>
  <symbol id="i-users" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></symbol>
  <symbol id="i-scan" viewBox="0 0 24 24"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2"/><rect x="7" y="7" width="4" height="4" rx=".5"/><rect x="13" y="13" width="4" height="4" rx=".5"/><path d="M13 7h4v4M7 13v4h4"/></symbol>
  <symbol id="i-heart" viewBox="0 0 24 24"><path d="M19 14c1.5-1.5 3-3.2 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.8 0-3 .5-4.5 2-1.5-1.5-2.7-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4 3 5.5l7 7Z"/></symbol>
  <symbol id="i-more" viewBox="0 0 24 24"><circle cx="5" cy="12" r="1.2"/><circle cx="12" cy="12" r="1.2"/><circle cx="19" cy="12" r="1.2"/></symbol>
  <symbol id="i-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
  <symbol id="i-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></symbol>
  <symbol id="i-sheet" viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M4 9h16M4 15h16M10 3v18"/></symbol>
  <symbol id="i-wa" viewBox="0 0 24 24"><path d="M3 21l1.65-3.8A9 9 0 1 1 7.8 20.3L3 21Z"/><path d="M9 10a.5.5 0 0 0 1 0V9a.5.5 0 0 0-1 0v1a5 5 0 0 0 5 5h1a.5.5 0 0 0 0-1h-1a.5.5 0 0 0 0 1"/></symbol>
</svg>
<main class="soon" id="main">
  <div class="glass panel">
    <h1>الشاشة دي جاية قريب</h1>
    <p>لسه بنبنيها. لحد دلوقتي تقدر تضيف المخدومين وتعدّل بياناتهم.</p>
    <a class="btn btn-light" href="<?php echo esc_url( stmina_att_url( 'members' ) ); ?>">روح للمخدومين</a>
  </div>
</main>
<nav class="glass g-nav" aria-label="التنقل في نظام الحضور">
  <ul>
    <li><a href="<?php echo esc_url( stmina_att_url( 'sessions' ) ); ?>"><svg class="icon"><use href="#i-calendar"/></svg>الجلسات</a></li>
    <li><a href="<?php echo esc_url( stmina_att_url( 'members' ) ); ?>"><svg class="icon"><use href="#i-users"/></svg>المخدومون</a></li>
    <li><a href="<?php echo esc_url( stmina_att_url( 'scan' ) ); ?>"><svg class="icon"><use href="#i-scan"/></svg>المسح</a></li>
    <li><a href="<?php echo esc_url( stmina_att_url( 'followup' ) ); ?>"><svg class="icon"><use href="#i-heart"/></svg>الافتقاد</a></li>
    <li><a href="<?php echo esc_url( stmina_att_url( 'more' ) ); ?>"><svg class="icon"><use href="#i-more"/></svg>المزيد</a></li>
  </ul>
</nav>
</body>
</html>
