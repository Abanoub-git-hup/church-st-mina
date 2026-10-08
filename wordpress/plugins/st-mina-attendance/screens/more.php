<?php
/**
 * شاشة "more" في نظام الحضور. متولّدة من design/attend-more.html بأداة tools/convert-attend.js،
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
<title>المزيد | إعداد الخدام</title>
<meta name="description" content="إعدادات خدمة إعداد الخدام: رسالة الخدام، والافتقاد، والخدام، وحسابك.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@200;300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo esc_url( get_template_directory_uri() . '/assets/site.css' ); ?>">
<link rel="stylesheet" href="<?php echo esc_url( STMINA_ATT_URL . 'assets/attend.css?ver=' . STMINA_ATT_VERSION ); ?>">
<style>
/* خاص بالمزيد */
.wrap-x{flex:1;width:100%;max-width:520px;margin-inline:auto;padding:var(--s-8) var(--gut) 0;display:grid;gap:var(--s-3);align-content:start}
.top{display:flex;align-items:flex-start;justify-content:space-between;gap:var(--s-3);margin-bottom:var(--s-2);text-shadow:0 2px 12px rgba(0,0,0,.45)}
.top small{display:block;font-size:.82rem;font-weight:500;color:var(--gold-soft)}
.top h1{font-size:2.1rem;font-weight:300;line-height:1.25}
.me{font-size:.86rem;color:var(--on-glass-2);margin-top:2px}
.me b{color:var(--on-glass);font-weight:500}

/* الألواح: عنوان + ملخص القيمة الحالية، وبتتفتح مكانها */
.pane{padding:var(--s-2)}
.pane-h{display:grid;grid-template-columns:40px 1fr 24px;gap:var(--s-3);align-items:center;width:100%;min-height:64px;padding:var(--s-2) var(--s-3);border:0;border-radius:18px;background:none;color:var(--on-glass);font:inherit;text-align:start;cursor:pointer}
.pane-h:hover{background:var(--glass-2)}
.pane-ic{width:40px;height:40px;border-radius:12px;display:grid;place-items:center;background:var(--glass-3)}
.pane-ic .icon{width:20px;height:20px}
.pane-h b{display:block;font-size:1rem;font-weight:500}
.pane-h small{display:block;font-size:.8rem;color:var(--on-glass-2);line-height:1.5;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical}
.chev{width:20px;height:20px;transition:rotate var(--t-base) var(--ease)}
.pane.open .chev{rotate:180deg}
.pane-b{display:none;padding:var(--s-2) var(--s-3) var(--s-3)}
.pane.open .pane-b{display:block}
.help{font-size:.86rem;line-height:1.8;color:var(--on-glass-2);margin-bottom:var(--s-4)}
.row2{display:flex;gap:var(--s-2);align-items:center;margin-top:var(--s-3)}
.row2 .btn{flex:1;height:48px}
.row2 .textlink{flex:none;padding-inline:var(--s-3)}
.meta{margin-top:var(--s-3);font-size:.78rem;color:var(--on-glass-3)}

/* رسالة الخدام */
textarea.t-in{width:100%;min-height:124px;field-sizing:content;overflow:hidden;padding:var(--s-3) var(--s-4);border:1px solid transparent;border-radius:14px;background:var(--glass-2);color:var(--on-glass);font:inherit;font-size:1rem;line-height:1.7;resize:vertical}
textarea.t-in::placeholder{color:var(--on-glass-3)}
textarea.t-in:focus{outline:none;background:var(--glass-3);border-color:rgba(255,244,228,.55)}
.counter{display:flex;justify-content:space-between;margin-top:6px;font-size:.78rem;color:var(--on-glass-3)}
.counter.over{color:var(--bad-text)}
.preview-l{margin:var(--s-4) 0 var(--s-2);font-size:.8rem;color:var(--on-glass-2)}
/* المعاينة: نفس شريط الرسالة الدافي اللي فوق صفحة "حضوري" */
.msg-strip{display:flex;gap:var(--s-3);align-items:flex-start;padding:var(--s-3) var(--s-4);border-radius:16px;background:linear-gradient(135deg,rgba(243,201,135,.28),rgba(243,201,135,.1));border:1px solid rgba(243,201,135,.35);font-size:.92rem;line-height:1.8;color:#FFF3DE}
.msg-strip .icon{width:20px;height:20px;flex:none;margin-top:4px;color:var(--gold-soft)}
.msg-strip small{display:block;font-size:.76rem;color:rgba(255,243,222,.7)}
.msg-strip.empty-m{background:var(--glass-2);border-color:var(--glass-line-soft);color:var(--on-glass-3)}

/* الافتقاد: + و − */
.stepper{display:flex;align-items:center;justify-content:center;gap:var(--s-5);margin-bottom:var(--s-3)}
.stepper button{width:52px;height:52px;border-radius:50%;border:1px solid var(--glass-line);background:var(--glass-2);color:var(--on-glass);font:inherit;font-size:1.5rem;line-height:1;cursor:pointer}
.stepper button:disabled{opacity:.35;cursor:not-allowed}
.stepper output{min-width:96px;text-align:center}
.stepper output strong{display:block;font-size:2.6rem;font-weight:200;line-height:1;font-variant-numeric:tabular-nums}
.stepper output span{font-size:.8rem;color:var(--on-glass-2)}
.effect{text-align:center;font-size:.88rem;color:var(--on-glass-2)}
.effect b{color:var(--on-glass);font-weight:500}

/* الخدام */
.srv{display:grid;grid-template-columns:40px 1fr auto;gap:var(--s-3);align-items:center;min-height:60px;padding:6px var(--s-2);border-radius:14px}
.srv + .srv{margin-top:2px}
.av{width:40px;height:40px;border-radius:50%;display:grid;place-items:center;background:var(--glass-3);font-size:.8rem;font-weight:500}
.srv b{display:block;font-size:.95rem;font-weight:400}
.srv small{display:block;font-size:.78rem;color:var(--on-glass-2);font-variant-numeric:tabular-nums}
.pill-s{display:inline-block;margin-inline-start:6px;padding:0 9px;border-radius:999px;background:var(--glass-3);font-size:.75rem;line-height:1.8;vertical-align:middle}
.pill-s.wait{background:rgba(242,179,90,.2);color:#FFD9A0}
.srv .textlink{font-size:.8rem}
.srv-confirm{grid-column:1/-1;display:none;padding:var(--s-3);border-radius:14px;background:rgba(217,96,79,.16);border:1px solid rgba(255,180,163,.35);font-size:.86rem;line-height:1.75;color:var(--on-glass-2)}
.srv.confirming .srv-confirm{display:block}
.srv.confirming{background:var(--glass-2)}
.btn-del{flex:1;height:46px;border:0;border-radius:12px;background:#B4443A;color:#fff;font:inherit;font-size:.9rem;font-weight:500;cursor:pointer}
.add-srv{margin-top:var(--s-4);padding-top:var(--s-4);border-top:1px solid var(--glass-line-soft)}
.add-srv h3{font-size:.95rem;font-weight:500;margin-bottom:var(--s-3)}
.g-input input[dir=ltr]{text-align:right}
.g-input input[dir=ltr]:not(:placeholder-shown){text-align:left}
.f-err{display:none;margin-top:var(--s-2);font-size:.84rem;color:var(--bad-text)}
.g-field.bad .f-err{display:block}
.g-field.bad input{border-color:rgba(255,180,163,.6)}
.invited{display:none;margin-top:var(--s-3);padding:var(--s-4);border-radius:16px;background:rgba(155,208,127,.12);border:1px solid rgba(155,208,127,.3);font-size:.88rem;line-height:1.8;color:var(--on-glass-2)}
.invited.show{display:block}
.invited b{color:var(--on-glass);font-weight:500}
.btn-wa{background:#1F7A4D;color:#fff;margin-top:var(--s-3)}
.btn-wa:hover{background:#238a57}
.note-role{margin-top:var(--s-4);font-size:.8rem;line-height:1.75;color:var(--on-glass-3)}

/* لينكات لشاشات تانية */
.tiles{display:grid;grid-template-columns:1fr 1fr;gap:var(--s-3)}
.tile{display:grid;gap:var(--s-2);padding:var(--s-4);border-radius:20px;color:inherit}
.tile:hover{background:rgba(255,244,228,.06)}
.tile .pane-ic{width:44px;height:44px}
.tile b{font-size:.95rem;font-weight:500}
.tile small{font-size:.78rem;line-height:1.6;color:var(--on-glass-2)}
.tile:only-child{grid-column:1/-1}

/* موقع الكنيسة: نفس شكل عنوان اللوح، بس لينك */
a.pane-h{color:inherit}

/* حسابك */
.logout{display:flex;align-items:center;justify-content:center;gap:var(--s-2);margin-top:var(--s-4);min-height:48px;border-radius:14px;border:1px solid rgba(255,180,163,.35);color:var(--bad-text);font-size:.92rem}
.logout:hover{background:rgba(217,96,79,.12)}
.logout .icon{width:18px;height:18px}
</style>
</head>
<body class="has-nav">
<a class="skip" href="#main">تخطَّ إلى المحتوى</a>

<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="i-calendar" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></symbol>
  <symbol id="i-users" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></symbol>
  <symbol id="i-scan" viewBox="0 0 24 24"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2"/><rect x="7" y="7" width="4" height="4" rx=".5"/><rect x="13" y="13" width="4" height="4" rx=".5"/><path d="M13 7h4v4M7 13v4h4"/></symbol>
  <symbol id="i-heart" viewBox="0 0 24 24"><path d="M19 14c1.5-1.5 3-3.2 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.8 0-3 .5-4.5 2-1.5-1.5-2.7-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4 3 5.5l7 7Z"/></symbol>
  <symbol id="i-more" viewBox="0 0 24 24"><circle cx="5" cy="12" r="1.2"/><circle cx="12" cy="12" r="1.2"/><circle cx="19" cy="12" r="1.2"/></symbol>
  <symbol id="i-chev" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></symbol>
  <symbol id="i-msg" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z"/></symbol>
  <symbol id="i-shield" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></symbol>
  <symbol id="i-sheet" viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M4 9h16M4 15h16M10 3v18"/></symbol>
  <symbol id="i-chart" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 15v3M12 10v8M17 6v12"/></symbol>
  <symbol id="i-user" viewBox="0 0 24 24"><circle cx="12" cy="8.5" r="3.5"/><path d="M5.5 19.5a6.5 6.5 0 0 1 13 0"/></symbol>
  <symbol id="i-home" viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1Z"/></symbol>
  <symbol id="i-left" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></symbol>
  <symbol id="i-out" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></symbol>
  <symbol id="i-wa" viewBox="0 0 24 24"><path d="M3 21l1.65-3.8A9 9 0 1 1 7.8 20.3L3 21Z"/><path d="M9 10a.5.5 0 0 0 1 0V9a.5.5 0 0 0-1 0v1a5 5 0 0 0 5 5h1a.5.5 0 0 0 0-1h-1a.5.5 0 0 0 0 1"/></symbol>
  <symbol id="i-heart-s" viewBox="0 0 24 24"><path d="M12 21s-7-4.5-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 5.5-7 10-7 10Z"/></symbol>
</svg>


<main class="wrap-x" id="main">
  <header class="top">
    <div><small>إعداد الخدام</small><h1>المزيد</h1><p class="me">داخل باسم <b id="meName">خادم تجريبي</b></p></div>
    <a class="glass out" href="<?php echo esc_url( stmina_att_url( 'login' ) ); ?>" data-logout aria-label="خروج من الحساب" title="خروج"><svg class="icon"><use href="#i-out"/></svg></a>
  </header>

  <!-- شاشات تانية -->
  <div class="tiles">
    <a class="glass tile" href="<?php echo esc_url( stmina_att_url( 'import' ) ); ?>">
      <span class="pane-ic"><svg class="icon"><use href="#i-sheet"/></svg></span>
      <b>استيراد من Excel</b><small>ضيف مخدومين كتير مرة واحدة</small>
    </a>
    <a class="glass tile" href="<?php echo esc_url( stmina_att_url( 'dashboard' ) ); ?>">
      <span class="pane-ic"><svg class="icon"><use href="#i-chart"/></svg></span>
      <b>لوحة الخادم</b><small>حضور الكل ونسب كل نوع نشاط</small>
    </a>
  </div>

  <!-- موقع الكنيسة -->
  <section class="glass pane">
    <a class="pane-h" href="<?php echo esc_url( home_url( '/' ) ); ?>">
      <span class="pane-ic"><svg class="icon"><use href="#i-home"/></svg></span>
      <span><b>موقع الكنيسة</b><small>الرئيسية والمواعيد والعظات</small></span>
      <svg class="icon chev"><use href="#i-left"/></svg>
    </a>
  </section>

  <!-- حسابك -->
  <section class="glass pane" data-pane="me">
    <button class="pane-h" type="button" aria-expanded="false" aria-controls="b-me">
      <span class="pane-ic"><svg class="icon"><use href="#i-user"/></svg></span>
      <span><b>حسابك</b><small>تغيير كلمة السر والخروج</small></span>
      <svg class="icon chev"><use href="#i-chev"/></svg>
    </button>
    <div class="pane-b" id="b-me">
      <form id="pwForm" novalidate>
        <div class="g-field" id="fpw0"><label for="pw0">كلمة السر الحالية</label><div class="g-input"><input id="pw0" type="password" dir="ltr" autocomplete="current-password"></div><p class="f-err" role="alert" id="pw0Err">اكتب كلمة السر الحالية.</p></div>
        <div class="g-field" id="fpw1"><label for="pw1">كلمة السر الجديدة</label><div class="g-input"><input id="pw1" type="password" dir="ltr" autocomplete="new-password"></div><p class="f-err" role="alert">8 حروف أو أرقام على الأقل.</p></div>
        <div class="g-field" id="fpw2"><label for="pw2">اكتبها تاني</label><div class="g-input"><input id="pw2" type="password" dir="ltr" autocomplete="new-password"></div><p class="f-err" role="alert">مش زي اللي فوقها.</p></div>
        <button class="btn btn-glass" type="submit">غيّر كلمة السر</button>
      </form>
      <a class="logout" href="<?php echo esc_url( stmina_att_url( 'login' ) ); ?>" data-logout><svg class="icon"><use href="#i-out"/></svg>خروج من الحساب</a>
    </div>
  </section>
</main>

<nav class="glass g-nav" aria-label="التنقل في نظام الحضور">
  <ul>
    <li><a href="<?php echo esc_url( stmina_att_url( 'sessions' ) ); ?>"><svg class="icon"><use href="#i-calendar"/></svg>الجلسات</a></li>
    <li><a href="<?php echo esc_url( stmina_att_url( 'members' ) ); ?>"><svg class="icon"><use href="#i-users"/></svg>المخدومون</a></li>
    <li><a href="<?php echo esc_url( stmina_att_url( 'scan' ) ); ?>"><svg class="icon"><use href="#i-scan"/></svg>المسح</a></li>
    <li><a href="<?php echo esc_url( stmina_att_url( 'followup' ) ); ?>"><svg class="icon"><use href="#i-heart"/></svg>الافتقاد</a></li>
    <li><a href="<?php echo esc_url( stmina_att_url( 'more' ) ); ?>" aria-current="page"><svg class="icon"><use href="#i-more"/></svg>المزيد</a></li>
  </ul>
</nav>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

<?php stmina_att_footer( 'more' ); ?>
</body>
</html>
