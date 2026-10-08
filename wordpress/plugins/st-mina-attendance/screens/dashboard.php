<?php
/**
 * شاشة "dashboard" في نظام الحضور. متولّدة من design/attend-dashboard.html بأداة tools/convert-attend.js،
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
<title>لوحة الخادم | إعداد الخدام</title>
<meta name="description" content="حضور كل المخدومين، ونسبة كل نوع نشاط، ورسم لكل مخدوم، والتسجيل اليدوي.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@200;300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo esc_url( get_template_directory_uri() . '/assets/site.css' ); ?>">
<link rel="stylesheet" href="<?php echo esc_url( STMINA_ATT_URL . 'assets/attend.css?ver=' . STMINA_ATT_VERSION ); ?>">
<style>
/* خاص بلوحة الخادم */
.sr-only{position:absolute;width:1px;height:1px;overflow:hidden;clip-path:inset(50%);white-space:nowrap}
.wrap-d{flex:1;width:100%;max-width:520px;margin-inline:auto;padding:var(--s-3) var(--gut) 0;display:grid;gap:var(--s-4);align-content:start}
.p-top{position:sticky;top:var(--s-3);z-index:20;display:flex;align-items:center;gap:var(--s-2);padding:var(--s-2);border-radius:20px}
.p-top b{flex:1;font-size:1rem;font-weight:500}
.ibtn{width:44px;height:44px;flex:none;display:grid;place-items:center;border:0;border-radius:14px;background:var(--glass-2);color:var(--on-glass);cursor:pointer}
.ibtn:hover{background:var(--glass-3)}
.ibtn .icon{width:20px;height:20px}
.pane{padding:var(--s-5) var(--s-4)}
.pane h2{font-size:1rem;font-weight:500;margin-bottom:var(--s-1)}
.sub{font-size:.8rem;line-height:1.7;color:var(--on-glass-2)}

/* اختيار الفترة والترتيب: كبسولات زي فلاتر المخدومين */
.seg{display:flex;gap:var(--s-2);overflow-x:auto;scrollbar-width:none}
.seg::-webkit-scrollbar{display:none}
.seg button{flex:none;display:inline-flex;align-items:center;gap:6px;min-height:44px;padding:0 16px;border-radius:999px;border:1px solid var(--glass-line-soft);background:var(--glass-2);color:var(--on-glass-2);font:inherit;font-size:.86rem;cursor:pointer;-webkit-backdrop-filter:blur(12px);backdrop-filter:blur(12px)}
.seg button b{font-weight:500;font-variant-numeric:tabular-nums}
.seg button[aria-pressed=true]{background:var(--btn-light);color:var(--ink);border-color:transparent}

/* حضور الكل: جملة، وعمود لكل جلسة طوله على قد عدد اللي حضروا */
.headline{font-size:1.3rem;font-weight:300;line-height:1.5}
.headline b{font-weight:500;font-variant-numeric:tabular-nums}
.strip{display:flex;align-items:flex-end;gap:3px;height:96px;margin-top:var(--s-5);padding-bottom:2px;border-bottom:1px solid var(--glass-line-soft)}
.strip i{flex:1;max-width:22px;min-height:3px;border-radius:4px 4px 1px 1px;background:rgba(255,248,238,.55)}
.strip i.mass{background:var(--gold-soft)}
.strip-axis{display:flex;justify-content:space-between;margin-top:6px;font-size:.75rem;color:var(--on-glass-3)}
.key{display:flex;flex-wrap:wrap;gap:var(--s-2) var(--s-4);margin-top:var(--s-3);font-size:.78rem;color:var(--on-glass-2)}
.key span{display:inline-flex;align-items:center;gap:6px}
.key i{width:10px;height:10px;border-radius:3px;background:rgba(255,248,238,.55)}
.key i.mass{background:var(--gold-soft)}

/* نسبة كل نوع نشاط */
.types{display:grid;gap:var(--s-3);margin-top:var(--s-4)}
.t-row{display:grid;grid-template-columns:64px 1fr 44px;align-items:center;gap:var(--s-3);font-size:.88rem}
.t-row small{display:block;font-size:.75rem;color:var(--on-glass-3)}
.t-bar{height:8px;border-radius:4px;background:rgba(255,248,238,.12);overflow:hidden}
.t-bar span{display:block;height:100%;border-radius:4px;background:var(--on-glass)}
.t-row b{font-weight:500;font-variant-numeric:tabular-nums;text-align:left}
.t-row.none{color:var(--on-glass-3)}

/* القائمة */
.list-h{display:flex;align-items:baseline;justify-content:space-between;gap:var(--s-3);margin-bottom:var(--s-3)}
.list-h h2{margin:0}
.list{padding:var(--s-2)}
.m{border-radius:16px;transition:background var(--t-fast)}
.m + .m{margin-top:2px}
.m.open{background:var(--glass-2)}
.m-btn{display:grid;grid-template-columns:40px 1fr auto;align-items:center;gap:var(--s-3);width:100%;min-height:62px;padding:8px var(--s-2);border:0;border-radius:16px;background:none;color:inherit;font:inherit;text-align:start;cursor:pointer}
.m-btn:hover{background:var(--glass-2)}
.av{width:40px;height:40px;border-radius:50%;display:grid;place-items:center;background:var(--glass-3);font-size:.82rem;font-weight:500}
.m-name{display:block;font-size:.98rem;line-height:1.4}
.m-meta{display:flex;flex-wrap:wrap;align-items:center;gap:6px;font-size:.78rem;color:var(--on-glass-2);font-variant-numeric:tabular-nums}
.flag{display:inline-flex;align-items:center;gap:4px;padding:1px 9px;border-radius:999px;font-size:.75rem;font-weight:500;background:rgba(242,179,90,.2);color:#FFD9A0}
.ring{--p:0;position:relative;width:46px;height:46px;border-radius:50%;display:grid;place-items:center;background:conic-gradient(var(--on-glass) calc(var(--p) * 1%),rgba(255,248,238,.14) 0)}
.ring::before{content:"";position:absolute;inset:3px;border-radius:50%;background:rgba(40,27,18,.72)}
.ring span{position:relative;font-size:.78rem;font-weight:500;font-variant-numeric:tabular-nums}
.ring.low{background:conic-gradient(var(--warn) calc(var(--p) * 1%),rgba(255,248,238,.14) 0)}
.ring.na{background:rgba(255,248,238,.1)}

/* تفاصيل المخدوم: بتتفتح تحت اسمه */
.m-more{display:none;padding:var(--s-2) var(--s-3) var(--s-4);gap:var(--s-4)}
.m.open .m-more{display:grid}
.m-more h3{font-size:.82rem;font-weight:400;color:var(--on-glass-2);margin-bottom:var(--s-2)}
.mini{display:grid;grid-template-columns:repeat(4,1fr);gap:var(--s-2)}
.mini div{padding:8px 4px;border-radius:12px;background:rgba(255,248,238,.07);text-align:center}
.mini b{display:block;font-size:1rem;font-weight:500;font-variant-numeric:tabular-nums}
.mini span{font-size:.75rem;color:var(--on-glass-2)}
.bars{display:grid;grid-template-columns:repeat(6,1fr);gap:var(--s-3);align-items:end;height:120px}
.bar{display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;gap:6px}
.bar i{display:block;width:100%;max-width:30px;border-radius:7px 7px 3px 3px;background:rgba(255,248,238,.55);min-height:4px}
.bar.now i{background:var(--gold-soft)}
.bar em{font-style:normal;font-size:.75rem;font-variant-numeric:tabular-nums;color:var(--on-glass-2)}
.bar span{font-size:.75rem;color:var(--on-glass-2);white-space:nowrap}
.bar.none i{background:rgba(255,248,238,.1)}
.manual-t{font-size:.86rem;line-height:1.75;color:var(--on-glass-2)}
.manual-t b{color:var(--on-glass);font-weight:500}
.m-acts{display:flex;flex-wrap:wrap;gap:var(--s-2)}
.wa{display:inline-flex;align-items:center;gap:6px;min-height:44px;padding:0 16px;border-radius:999px;background:#1F7A4D;color:#fff;font-size:.84rem;font-weight:500}
.wa:hover{background:#238a57}
.wa .icon,.go .icon{width:18px;height:18px}
.go{display:inline-flex;align-items:center;gap:6px;min-height:44px;padding:0 16px;border-radius:999px;background:var(--glass-2);border:1px solid var(--glass-line-soft);color:var(--on-glass);font-size:.84rem}
.go:hover{background:var(--glass-3)}
.no-match{padding:var(--s-6) var(--s-3);text-align:center;font-size:.9rem;line-height:1.8;color:var(--on-glass-2)}
.note-off{font-size:.78rem;color:var(--on-glass-3);text-align:center}

/* تنزيل الجدول: نفس الفترة المختارة */
.xl{height:48px;margin-top:var(--s-5);font-size:.92rem}
.xl .icon{width:18px;height:18px}
.xl:disabled{opacity:.6;cursor:wait}

/* مفيش جلسات في الفترة دي */
.empty-p{display:none}
[data-empty] .empty-p{display:block}
[data-empty] .has-data{display:none}
</style>
<?php stmina_att_servant_head(); ?>
</head>
<body class="has-nav">
<a class="skip" href="#main">تخطَّ إلى المحتوى</a>

<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="i-calendar" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></symbol>
  <symbol id="i-users" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></symbol>
  <symbol id="i-scan" viewBox="0 0 24 24"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2"/><rect x="7" y="7" width="4" height="4" rx=".5"/><rect x="13" y="13" width="4" height="4" rx=".5"/><path d="M13 7h4v4M7 13v4h4"/></symbol>
  <symbol id="i-heart" viewBox="0 0 24 24"><path d="M19 14c1.5-1.5 3-3.2 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.8 0-3 .5-4.5 2-1.5-1.5-2.7-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4 3 5.5l7 7Z"/></symbol>
  <symbol id="i-more" viewBox="0 0 24 24"><circle cx="5" cy="12" r="1.2"/><circle cx="12" cy="12" r="1.2"/><circle cx="19" cy="12" r="1.2"/></symbol>
  <symbol id="i-right" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></symbol>
  <symbol id="i-left" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></symbol>
  <symbol id="i-wa" viewBox="0 0 24 24"><path d="M3 21l1.65-3.8A9 9 0 1 1 7.8 20.3L3 21Z"/><path d="M9 10a.5.5 0 0 0 1 0V9a.5.5 0 0 0-1 0v1a5 5 0 0 0 5 5h1a.5.5 0 0 0 0-1h-1a.5.5 0 0 0 0 1"/></symbol>
  <symbol id="i-sheet" viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M4 9h16M4 15h16M10 3v18"/></symbol>
  <symbol id="i-out" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></symbol>
</svg>


<main class="wrap-d" id="main">
  <header class="glass p-top">
    <a class="ibtn" href="<?php echo esc_url( stmina_att_url( 'more' ) ); ?>" aria-label="رجوع للمزيد"><svg class="icon"><use href="#i-right"/></svg></a>
    <b>لوحة الخادم</b>
    <a class="ibtn out" href="<?php echo esc_url( stmina_att_url( 'login' ) ); ?>" data-logout aria-label="خروج من الحساب" title="خروج"><svg class="icon"><use href="#i-out"/></svg></a>
  </header>

  <div class="seg" role="group" aria-label="الفترة" id="period"></div>

  <section class="glass pane empty-p">
    <h2>مفيش جلسات في الفترة دي</h2>
    <p class="sub">الأرقام بتبدأ تظهر بعد أول جلسة تتقفل. افتح جلسة من شاشة الجلسات، أو اختار فترة أطول.</p>
  </section>

  <!-- حضور الكل -->
  <section class="glass pane has-data" aria-labelledby="h-all">
    <h2 id="h-all" class="sr-only">حضور الكل</h2>
    <p class="headline" id="headline"></p>
    <p class="sub" id="headSub"></p>
    <div class="strip" id="strip" role="img"></div>
    <div class="strip-axis" aria-hidden="true"><span id="axFrom"></span><span id="axTo"></span></div>
    <div class="key" aria-hidden="true"><span><i class="mass"></i>قداس</span><span><i></i>اجتماع ونشاط وخدمة</span></div>
    <div class="types" id="types"></div>
    <button class="btn btn-glass xl" type="button" id="xlsx"><svg class="icon"><use href="#i-sheet"/></svg>نزّل جدول الحضور Excel</button>
  </section>

  <!-- كل مخدوم -->
  <section class="has-data" aria-labelledby="h-list">
    <div class="list-h"><h2 id="h-list" style="font-size:1rem;font-weight:500">كل مخدوم</h2><span class="sub" id="listCount"></span></div>
    <div class="seg" role="group" aria-label="اعرض" id="filter" style="margin-bottom:var(--s-3)"></div>
    <div class="glass list" id="list" aria-live="polite"></div>
    <p class="note-off" id="noteOff" style="margin-top:var(--s-3)"></p>
  </section>
</main>

<nav class="glass g-nav" aria-label="التنقل في نظام الحضور">
  <ul>
    <li><a href="<?php echo esc_url( stmina_att_url( 'sessions' ) ); ?>"><svg class="icon"><use href="#i-calendar"/></svg>الجلسات</a></li>
    <li><a href="<?php echo esc_url( stmina_att_url( 'members' ) ); ?>"><svg class="icon"><use href="#i-users"/></svg>المخدومون</a></li>
    <li><a href="<?php echo esc_url( stmina_att_url( 'scan' ) ); ?>"><svg class="icon"><use href="#i-scan"/></svg>المسح</a></li>
    <li><a href="<?php echo esc_url( stmina_att_url( 'more' ) ); ?>" aria-current="page"><svg class="icon"><use href="#i-more"/></svg>المزيد</a></li>
  </ul>
</nav>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

<?php stmina_att_footer( 'dashboard' ); ?>
</body>
</html>
