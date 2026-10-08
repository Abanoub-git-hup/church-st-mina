<?php
/**
 * شاشة "sessions" في نظام الحضور. متولّدة من design/attend-sessions.html بأداة tools/convert-attend.js،
 * فأي تعديل في الشكل يتعمل في التصميم وبعدين تتشغّل الأداة تاني.
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
<title>الجلسات | إعداد الخدام</title>
<meta name="description" content="جلسات حضور خدمة إعداد الخدام: الجلسة المفتوحة، وفتح جلسة جديدة، والجلسات السابقة.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@200;300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo esc_url( get_template_directory_uri() . '/assets/site.css' ); ?>">
<link rel="stylesheet" href="<?php echo esc_url( STMINA_ATT_URL . 'assets/attend.css?ver=' . STMINA_ATT_VERSION ); ?>">
<style>
/* خاص بشاشة الجلسات */
.wrap-s{flex:1;width:100%;max-width:520px;margin-inline:auto;padding:var(--s-8) var(--gut) 0}
.top{display:flex;align-items:flex-end;justify-content:space-between;gap:var(--s-3);margin-bottom:var(--s-5);text-shadow:0 2px 12px rgba(0,0,0,.45)}
.top small{display:block;font-size:.82rem;font-weight:500;color:var(--gold-soft)}
.top h1{font-size:2.1rem;font-weight:300;line-height:1.25}
.sync{display:inline-flex;align-items:center;gap:var(--s-2);padding:6px 14px;border-radius:999px;font-size:.78rem;color:var(--on-glass-2);text-shadow:none;white-space:nowrap}
.sync::before{content:"";width:7px;height:7px;border-radius:50%;background:var(--ok)}
.sync[data-sync=offline]::before{background:rgba(255,248,238,.5)}
.stack{display:grid;gap:var(--s-4)}

/* الجلسة المفتوحة */
.cur{padding:var(--s-5)}
.cur-top{display:flex;align-items:center;justify-content:space-between;gap:var(--s-3);flex-wrap:wrap}
.live{display:inline-flex;align-items:center;gap:var(--s-2);padding:4px 12px;border-radius:999px;background:var(--glass-2);font-size:.78rem;font-weight:500}
.live::before{content:"";width:7px;height:7px;border-radius:50%;background:var(--gold-soft);box-shadow:0 0 0 0 rgba(243,201,135,.7);animation:pulse 1.8s infinite}
@keyframes pulse{70%{box-shadow:0 0 0 7px rgba(243,201,135,0)}100%{box-shadow:0 0 0 0 rgba(243,201,135,0)}}
.cur-date{font-size:.82rem;color:var(--on-glass-2)}
.cur h2{font-size:1.5rem;font-weight:400;margin-top:var(--s-3)}
.count{display:flex;align-items:baseline;gap:var(--s-2);margin:var(--s-2) 0 var(--s-3)}
.count strong{font-size:3.2rem;font-weight:200;line-height:1;font-variant-numeric:tabular-nums}
.count span{font-size:.86rem;color:var(--on-glass-2)}
.pending{display:none;align-items:center;gap:var(--s-2);margin-top:var(--s-3);font-size:.82rem;color:var(--warn)}
.pending .icon{width:16px;height:16px}
[data-state=offline] .pending{display:flex}
.cur .btn-light{margin-top:var(--s-5)}
.cur-foot{display:flex;justify-content:space-between;margin-top:var(--s-1)}
.confirm{display:none;margin-top:var(--s-4);padding:var(--s-4);border-radius:16px;background:rgba(217,96,79,.18);border:1px solid rgba(255,180,163,.35)}
.confirm p{font-size:.9rem;line-height:1.85;color:var(--on-glass-2);margin-bottom:var(--s-3)}
.confirm p b{color:var(--on-glass);font-weight:500}
.confirm-row{display:flex;gap:var(--s-3);align-items:center}
.btn-del{flex:1;height:48px;border:0;border-radius:14px;background:#B4443A;color:#fff;font:inherit;font-size:.92rem;font-weight:500;cursor:pointer}
.cur.is-confirming .confirm{display:block}
.cur.is-confirming .cur-actions{display:none}

/* فتح جلسة جديدة: نفس اللوح بيتفتح في مكانه */
.newbox{padding:var(--s-2)}
.new-start{display:flex;align-items:center;gap:var(--s-3);width:100%;min-height:60px;padding:var(--s-2) var(--s-3);border:0;border-radius:18px;background:none;color:var(--on-glass);font:inherit;text-align:start;cursor:pointer}
.new-start:hover{background:var(--glass-2)}
.new-start .plus{display:grid;place-items:center;width:40px;height:40px;border-radius:12px;background:var(--btn-light);color:var(--ink);flex:none}
.new-start b{display:block;font-size:1rem;font-weight:500}
.new-start small{display:block;font-size:.8rem;color:var(--on-glass-2)}
.new-start:disabled{opacity:.5;cursor:not-allowed;background:none}
.new-off{display:none;padding:0 var(--s-3) var(--s-2);font-size:.8rem;color:var(--on-glass-2)}
[data-state=offline] .new-off{display:block}
.composer{display:none;padding:var(--s-4) var(--s-3) var(--s-3)}
.newbox.is-open .new-start{display:none}
.newbox.is-open .composer{display:block}
.composer h2{font-size:1.2rem;font-weight:400;margin-bottom:var(--s-4)}
.seg{display:grid;grid-template-columns:repeat(2,1fr);gap:var(--s-2);border:0;margin:0 0 var(--s-2);padding:0}
.seg.three{grid-template-columns:repeat(3,1fr)}
.seg legend{float:right;width:100%;margin-bottom:var(--s-2)}
.seg label{position:relative;display:flex;align-items:center;justify-content:center;gap:var(--s-2);min-height:50px;border-radius:14px;background:var(--glass-2);border:1px solid var(--glass-line-soft);font-size:.95rem;color:var(--on-glass-2);cursor:pointer;transition:background var(--t-fast),color var(--t-fast)}
.seg label .icon{width:18px;height:18px}
.seg input{position:absolute;opacity:0;pointer-events:none}
.seg label:hover{background:var(--glass-3)}
.seg label:has(input:checked){background:var(--btn-light);color:var(--ink);border-color:transparent}
.seg label:has(input:focus-visible){outline:2px solid var(--on-glass);outline-offset:2px}
.copy-last{margin-bottom:var(--s-3);font-size:.84rem}
.copy-last[hidden]{display:none}
.date-wrap{display:none;margin-top:var(--s-2)}
.date-wrap.show{display:block}
.date-wrap input{color-scheme:dark}
.c-err{display:none;margin-top:var(--s-3);font-size:.86rem;color:var(--bad-text)}
.c-err.show{display:block}
.composer .btn-light{margin-top:var(--s-5)}
.composer .textlink{display:flex;width:100%;margin-top:var(--s-1)}

/* الجلسات السابقة */
.month{margin:var(--s-4) var(--s-2) var(--s-2);font-size:.82rem;font-weight:500;color:var(--on-glass-2);text-shadow:0 1px 8px rgba(0,0,0,.5)}
.list{padding:var(--s-2)}
.row{display:grid;grid-template-columns:56px 1fr;gap:var(--s-3);align-items:center;padding:var(--s-3);border-radius:16px;color:inherit;transition:background var(--t-fast)}
.row + .row{margin-top:2px}
.row:hover{background:var(--glass-2)}
.dtile{display:grid;place-items:center;align-content:center;height:56px;border-radius:14px;background:var(--glass-2);line-height:1.15}
.dtile b{font-size:1.35rem;font-weight:300;font-variant-numeric:tabular-nums}
.dtile small{font-size:.75rem;color:var(--on-glass-2)}
.row-head{display:flex;justify-content:space-between;align-items:baseline;gap:var(--s-3);margin-bottom:var(--s-2)}
.row-head span{font-size:1rem;font-weight:400}
.row-head em{font-style:normal;font-size:.86rem;color:var(--on-glass-2);font-variant-numeric:tabular-nums}

/* فاضي */
.empty{display:none;padding:var(--s-6) var(--s-5);text-align:center}
.empty h2{font-size:1.25rem;font-weight:400;margin-bottom:var(--s-2)}
.empty p{font-size:.9rem;line-height:1.85;color:var(--on-glass-2)}
[data-state=empty] .empty{display:block}
[data-state=empty] #current,[data-state=empty] #history{display:none}
</style>
</head>
<body class="has-nav" data-state="normal">
<a class="skip" href="#main">تخطَّ إلى المحتوى</a>

<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="i-calendar" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></symbol>
  <symbol id="i-users" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></symbol>
  <symbol id="i-scan" viewBox="0 0 24 24"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2"/><rect x="7" y="7" width="4" height="4" rx=".5"/><rect x="13" y="13" width="4" height="4" rx=".5"/><path d="M13 7h4v4M7 13v4h4"/></symbol>
  <symbol id="i-heart" viewBox="0 0 24 24"><path d="M19 14c1.5-1.5 3-3.2 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.8 0-3 .5-4.5 2-1.5-1.5-2.7-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4 3 5.5l7 7Z"/></symbol>
  <symbol id="i-more" viewBox="0 0 24 24"><circle cx="5" cy="12" r="1.2"/><circle cx="12" cy="12" r="1.2"/><circle cx="19" cy="12" r="1.2"/></symbol>
  <symbol id="i-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
  <symbol id="i-copy" viewBox="0 0 24 24"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></symbol>
  <symbol id="i-wifi-off" viewBox="0 0 24 24"><path d="M3 3l18 18M8.5 16.4a5 5 0 0 1 7 0M5 12.8a10 10 0 0 1 4.4-2.5M14.6 10.3A10 10 0 0 1 19 12.8M2 8.8a15 15 0 0 1 4.2-2.6M10.7 5.1A15 15 0 0 1 22 8.8M12 20h.01"/></symbol>
  <symbol id="i-upload" viewBox="0 0 24 24"><path d="M12 16V4M7 9l5-5 5 5M4 20h16"/></symbol>
  <symbol id="i-cross" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/><circle cx="12" cy="3.2" r="1.4"/><circle cx="12" cy="20.8" r="1.4"/><circle cx="3.2" cy="12" r="1.4"/><circle cx="20.8" cy="12" r="1.4"/><circle cx="12" cy="12" r="2.2"/></symbol>
  <symbol id="i-book" viewBox="0 0 24 24"><path d="M2 4h6a4 4 0 0 1 4 4v13a3 3 0 0 0-3-3H2Z"/><path d="M22 4h-6a4 4 0 0 0-4 4v13a3 3 0 0 1 3-3h7Z"/></symbol>
  <symbol id="i-star" viewBox="0 0 24 24"><path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9Z"/></symbol>
  <symbol id="i-hands" viewBox="0 0 24 24"><path d="M12 21s-7-4.5-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 5.5-7 10-7 10Z"/><path d="M9 12h6"/></symbol>
  <symbol id="i-out" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></symbol>
</svg>


<div class="offline" role="status">
  <svg class="icon"><use href="#i-wifi-off"/></svg>
  <span>أنت بدون إنترنت، العمليات تُحفظ على جهازك (3)</span>
</div>

<main class="wrap-s" id="main">
  <header class="top">
    <div><small>إعداد الخدام</small><h1>الجلسات</h1></div>
    <div class="top-end"><span class="glass sync" id="sync" data-sync="ok">متزامن</span><a class="glass out" href="<?php echo esc_url( stmina_att_url( 'login' ) ); ?>" data-logout aria-label="خروج من الحساب" title="خروج"><svg class="icon"><use href="#i-out"/></svg></a></div>
  </header>

  <div class="stack">
    <section id="current" aria-label="الجلسة المفتوحة"></section>

    <div class="glass empty">
      <h2>مفيش جلسات لسه</h2>
      <p>افتح أول جلسة علشان تبدأ تسجّل حضور المخدومين بمسح الكارت.</p>
    </div>

    <!-- فتح جلسة جديدة -->
    <div class="glass newbox" id="newbox">
      <button class="new-start" type="button" id="newStart" aria-expanded="false" aria-controls="composer">
        <span class="plus"><svg class="icon"><use href="#i-plus"/></svg></span>
        <span><b>فتح جلسة جديدة</b><small>اختار نوع الجلسة والتاريخ</small></span>
      </button>
      <p class="new-off">فتح جلسة يحتاج إنترنت. الجلسة المفتوحة تقدر تكمّل فيها المسح عادي.</p>

      <form class="composer" id="composer" novalidate>
        <h2>جلسة جديدة · إعداد الخدام</h2>
        <fieldset class="seg" id="types">
          <legend class="g-label">نوع الجلسة</legend>
        </fieldset>
        <button class="textlink copy-last" type="button" id="copyLast"></button>

        <fieldset class="seg three" id="dates">
          <legend class="g-label">التاريخ</legend>
          <label><input type="radio" name="when" value="0" checked>النهاردة</label>
          <label><input type="radio" name="when" value="-1">امبارح</label>
          <label><input type="radio" name="when" value="other">تاريخ تاني</label>
        </fieldset>
        <div class="g-input date-wrap" id="dateWrap"><input type="date" id="date" aria-label="اختار التاريخ"></div>

        <p class="c-err" id="cErr" role="alert"></p>
        <button class="btn btn-light" type="submit"><svg class="icon"><use href="#i-scan"/></svg>افتح الجلسة وابدأ المسح</button>
        <button class="textlink" type="button" id="cancel">إلغاء</button>
      </form>
    </div>

    <div id="history"></div>
  </div>
</main>

<nav class="glass g-nav" aria-label="التنقل في نظام الحضور">
  <ul>
    <li><a href="<?php echo esc_url( stmina_att_url( 'sessions' ) ); ?>" aria-current="page"><svg class="icon"><use href="#i-calendar"/></svg>الجلسات</a></li>
    <li><a href="<?php echo esc_url( stmina_att_url( 'members' ) ); ?>"><svg class="icon"><use href="#i-users"/></svg>المخدومون</a></li>
    <li><a href="<?php echo esc_url( stmina_att_url( 'scan' ) ); ?>"><svg class="icon"><use href="#i-scan"/></svg>المسح</a></li>
    <li><a href="<?php echo esc_url( stmina_att_url( 'followup' ) ); ?>"><svg class="icon"><use href="#i-heart"/></svg>الافتقاد</a></li>
    <li><a href="<?php echo esc_url( stmina_att_url( 'more' ) ); ?>"><svg class="icon"><use href="#i-more"/></svg>المزيد</a></li>
  </ul>
</nav>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

<?php stmina_att_footer( 'sessions' ); ?>
</body>
</html>
