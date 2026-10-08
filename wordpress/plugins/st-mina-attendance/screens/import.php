<?php
/**
 * شاشة "import" في نظام الحضور. متولّدة من design/attend-import.html بأداة tools/convert-attend.js،
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
<title>استيراد من Excel | إعداد الخدام</title>
<meta name="description" content="إضافة مخدومين كتير مرة واحدة من ملف Excel فيه عمودين: الاسم والموبايل.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@200;300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo esc_url( get_template_directory_uri() . '/assets/site.css' ); ?>">
<link rel="stylesheet" href="<?php echo esc_url( STMINA_ATT_URL . 'assets/attend.css?ver=' . STMINA_ATT_VERSION ); ?>">
<style>
/* خاص بشاشة الاستيراد */
.wrap-i{flex:1;width:100%;max-width:520px;margin-inline:auto;padding:var(--s-3) var(--gut) 0;display:grid;gap:var(--s-4);align-content:start}
.p-top{position:sticky;top:var(--s-3);z-index:20;display:flex;align-items:center;gap:var(--s-2);padding:var(--s-2);border-radius:20px}
.p-top b{flex:1;font-size:1rem;font-weight:500}
.ibtn{width:44px;height:44px;flex:none;display:grid;place-items:center;border:0;border-radius:14px;background:var(--glass-2);color:var(--on-glass);cursor:pointer}
.ibtn:hover{background:var(--glass-3)}
.ibtn .icon{width:20px;height:20px}
.pane{padding:var(--s-5) var(--s-4)}
.pane h1,.pane h2{font-size:1.35rem;font-weight:300;line-height:1.45;margin-bottom:var(--s-2)}
.pane h1 b,.pane h2 b{font-weight:500;font-variant-numeric:tabular-nums}
.lead{font-size:.9rem;line-height:1.85;color:var(--on-glass-2)}
.lead b{color:var(--on-glass);font-weight:500}
.step{display:none}
[data-step=pick] .s-pick,[data-step=preview] .s-preview,[data-step=done] .s-done,[data-step=bad] .s-bad{display:grid;gap:var(--s-4)}

/* الشرح: ورقة Excel صغيرة بالعمودين المطلوبين */
/* من اليمين زي Excel العربي: أرقام الصفوف يمين، وعمود A الاسم */
.sheet{display:grid;grid-template-columns:28px 1fr 1fr;border:1px solid var(--glass-line-soft);border-radius:14px;overflow:hidden;font-size:.82rem;background:rgba(255,244,228,.06)}
.sheet > *{min-height:36px;display:flex;align-items:center;padding:0 10px;border-bottom:1px solid var(--glass-line-soft);border-inline-start:1px solid var(--glass-line-soft)}
.sheet > :nth-child(3n+1){border-inline-start:0;justify-content:center;padding:0;color:var(--on-glass-3);font-size:.75rem;background:rgba(255,244,228,.05)}
.sheet .col{justify-content:center;color:var(--on-glass-3);font-size:.75rem;background:rgba(255,244,228,.05);min-height:26px}
.sheet .hd{font-weight:500;color:var(--ink);background:var(--btn-light)}
.sheet .mb{direction:ltr;justify-content:flex-end;font-variant-numeric:tabular-nums;color:var(--on-glass-2)}
.sheet > :nth-last-child(-n+3){border-bottom:0}
.rules{display:grid;gap:var(--s-2);font-size:.86rem;line-height:1.7;color:var(--on-glass-2)}
.rules li{display:flex;gap:var(--s-2)}
.rules li::before{content:"";flex:none;width:5px;height:5px;margin-top:.7em;border-radius:50%;background:var(--on-glass-3)}
.pick-btn{position:relative}
.pick-btn input{position:absolute;inset:0;opacity:0;cursor:pointer}
.pick-btn:focus-within{outline:2px solid var(--on-glass);outline-offset:3px}
.reading{display:none;align-items:center;justify-content:center;gap:var(--s-2);font-size:.88rem;color:var(--on-glass-2)}
.is-reading .reading{display:flex}
.is-reading .pick-btn{display:none}

/* المعاينة: كل صف في الملف علامة عدّ. الطويلة الفاتحة هتتضاف، والقصيرة الدافية فيها مشكلة */
.file{display:flex;align-items:center;gap:var(--s-2);font-size:.82rem;color:var(--on-glass-2);min-width:0}
.file .icon{width:18px;height:18px;flex:none}
.file span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;unicode-bidi:plaintext}
.rows-tally{flex-wrap:wrap;row-gap:10px;height:auto}
.rows-tally i.bad{height:10px;background:var(--warn)}
.legend{display:flex;flex-wrap:wrap;gap:var(--s-2) var(--s-4);font-size:.8rem;color:var(--on-glass-2)}
.legend span{display:inline-flex;align-items:center;gap:6px}
.legend i{display:inline-block;width:3px;height:14px;border-radius:1px;background:var(--on-glass)}
.legend i.bad{height:8px;background:var(--warn)}
.fixed-note{font-size:.8rem;line-height:1.7;color:var(--on-glass-3)}

/* الصفوف المرفوضة: رقم الصف في الملف + الاسم + السبب */
.rej h3,.ok-list summary{font-size:.95rem;font-weight:500;margin-bottom:var(--s-1)}
.rej > p{font-size:.82rem;line-height:1.75;color:var(--on-glass-2);margin-bottom:var(--s-3)}
.r-row{display:grid;grid-template-columns:52px 1fr;gap:var(--s-3);align-items:start;padding:10px 0;border-top:1px solid var(--glass-line-soft)}
.r-no{display:grid;place-items:center;height:30px;border-radius:9px;background:rgba(242,179,90,.16);color:#FFD9A0;font-size:.78rem;font-weight:500;font-variant-numeric:tabular-nums;white-space:nowrap}
.r-who{display:block;font-size:.92rem;line-height:1.5}
.r-who.none{color:var(--on-glass-3)}
.r-why{display:block;font-size:.8rem;line-height:1.6;color:#FFD9A0}
.ok-list summary{display:flex;align-items:center;justify-content:space-between;min-height:44px;cursor:pointer;list-style:none}
.ok-list summary::-webkit-details-marker{display:none}
.ok-list summary .icon{width:18px;height:18px;transition:rotate var(--t-fast)}
.ok-list[open] summary .icon{rotate:180deg}
.ok-list ol{display:grid;font-size:.88rem}
.ok-list li{display:flex;justify-content:space-between;gap:var(--s-3);padding:8px 0;border-top:1px solid var(--glass-line-soft)}
.ok-list li span:last-child{direction:ltr;color:var(--on-glass-2);font-variant-numeric:tabular-nums}
.actions{display:grid;gap:var(--s-1)}

/* بعد الإضافة: ابعت الكروت واحد واحد */
.sent-bar{height:6px;border-radius:3px;background:rgba(255,248,238,.14);overflow:hidden}
.sent-bar span{display:block;height:100%;width:0;border-radius:3px;background:var(--ok);transition:width var(--t-base) var(--ease)}
.sent-count{font-size:.82rem;color:var(--on-glass-2);font-variant-numeric:tabular-nums}
.w-row{display:grid;grid-template-columns:40px 1fr auto;align-items:center;gap:var(--s-3);min-height:60px;padding:8px 0;border-top:1px solid var(--glass-line-soft)}
.av{width:40px;height:40px;border-radius:50%;display:grid;place-items:center;background:var(--glass-3);font-size:.82rem;font-weight:500}
.w-name{display:block;font-size:.95rem;line-height:1.4}
.w-mob{display:block;font-size:.78rem;color:var(--on-glass-2);direction:ltr;text-align:right;font-variant-numeric:tabular-nums}
.wa{display:inline-flex;align-items:center;gap:6px;min-height:44px;padding:0 14px;border-radius:999px;background:#1F7A4D;color:#fff;font-size:.82rem;font-weight:500;white-space:nowrap}
.wa:hover{background:#238a57}
.wa .icon{width:18px;height:18px}
.w-row.sent .wa{background:var(--glass-2);color:var(--on-glass-2)}
.w-row.sent .av{background:rgba(155,208,127,.25);color:var(--ok)}

/* ملف مش مفهوم */
.s-bad .lead{margin-top:calc(var(--s-2) * -1)}
.bad-ic{display:grid;place-items:center;width:48px;height:48px;border-radius:50%;background:rgba(242,179,90,.16);color:#FFD9A0}
.bad-ic .icon{width:24px;height:24px}
</style>
<?php stmina_att_servant_head(); ?>
</head>
<body class="has-nav" data-step="pick">
<a class="skip" href="#main">تخطَّ إلى المحتوى</a>

<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="i-calendar" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></symbol>
  <symbol id="i-users" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></symbol>
  <symbol id="i-scan" viewBox="0 0 24 24"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2"/><rect x="7" y="7" width="4" height="4" rx=".5"/><rect x="13" y="13" width="4" height="4" rx=".5"/><path d="M13 7h4v4M7 13v4h4"/></symbol>
  <symbol id="i-heart" viewBox="0 0 24 24"><path d="M19 14c1.5-1.5 3-3.2 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.8 0-3 .5-4.5 2-1.5-1.5-2.7-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4 3 5.5l7 7Z"/></symbol>
  <symbol id="i-more" viewBox="0 0 24 24"><circle cx="5" cy="12" r="1.2"/><circle cx="12" cy="12" r="1.2"/><circle cx="19" cy="12" r="1.2"/></symbol>
  <symbol id="i-right" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></symbol>
  <symbol id="i-sheet" viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M4 9h16M4 15h16M10 3v18"/></symbol>
  <symbol id="i-upload" viewBox="0 0 24 24"><path d="M12 15V3M7 8l5-5 5 5M4 15v4a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4"/></symbol>
  <symbol id="i-download" viewBox="0 0 24 24"><path d="M12 3v12M7 10l5 5 5-5M4 15v4a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4"/></symbol>
  <symbol id="i-down" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></symbol>
  <symbol id="i-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
  <symbol id="i-alert" viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></symbol>
  <symbol id="i-wa" viewBox="0 0 24 24"><path d="M3 21l1.65-3.8A9 9 0 1 1 7.8 20.3L3 21Z"/><path d="M9 10a.5.5 0 0 0 1 0V9a.5.5 0 0 0-1 0v1a5 5 0 0 0 5 5h1a.5.5 0 0 0 0-1h-1a.5.5 0 0 0 0 1"/></symbol>
  <symbol id="i-check" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></symbol>
  <symbol id="i-out" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></symbol>
</svg>


<main class="wrap-i" id="main">
  <header class="glass p-top">
    <a class="ibtn" href="<?php echo esc_url( stmina_att_url( 'members' ) ); ?>" aria-label="رجوع للمخدومين"><svg class="icon"><use href="#i-right"/></svg></a>
    <b>استيراد من Excel</b>
    <a class="ibtn out" href="<?php echo esc_url( stmina_att_url( 'login' ) ); ?>" data-logout aria-label="خروج من الحساب" title="خروج"><svg class="icon"><use href="#i-out"/></svg></a>
  </header>

  <!-- 1. اختيار الملف -->
  <section class="step s-pick" aria-labelledby="h-pick">
    <div class="glass pane" style="display:grid;gap:var(--s-4)">
      <div>
        <h1 id="h-pick">ضيف مخدومين كتير مرة واحدة</h1>
        <p class="lead">جهّز ملف Excel فيه <b>عمودين بس</b>: الاسم بالكامل، ورقم الموبايل. كل مخدوم في صف.</p>
      </div>
      <div class="sheet" role="img" aria-label="شكل الملف: العمود الأول الاسم، والتاني الموبايل، وكل مخدوم في صف">
        <span></span><span class="col">A</span><span class="col">B</span>
        <span>1</span><span class="hd">الاسم</span><span class="hd">الموبايل</span>
        <span>2</span><span class="nm">مينا عادل</span><span class="mb">01012345678</span>
        <span>3</span><span class="nm">مريم سمير</span><span class="mb">01198765432</span>
      </div>
      <ul class="rules">
        <li>أول صف ممكن يكون عناوين (الاسم، الموبايل) أو يبدأ بالأسماء على طول.</li>
        <li>اللي متسجّل قبل كده بنفس الرقم مش هيتضاف تاني.</li>
        <li>هتشوف الأسماء الأول قبل ما تتضاف.</li>
      </ul>
      <div class="actions" id="pickBox">
        <label class="btn btn-light pick-btn"><svg class="icon"><use href="#i-upload"/></svg>اختار ملف Excel
          <input type="file" id="file" accept=".xlsx,.xls,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,text/csv">
        </label>
        <p class="reading" role="status"><svg class="icon" style="width:18px;height:18px"><use href="#i-sheet"/></svg>بنقرا الملف...</p>
        <button class="textlink" type="button" id="tpl"><svg class="icon" style="width:18px;height:18px"><use href="#i-download"/></svg>نزّل ملف جاهز تملاه</button>
      </div>
    </div>
  </section>

  <!-- 2. المعاينة -->
  <section class="step s-preview" aria-labelledby="h-preview">
    <div class="glass pane" style="display:grid;gap:var(--s-4)">
      <p class="file"><svg class="icon"><use href="#i-sheet"/></svg><span id="fileName"></span></p>
      <div>
        <h1 id="h-preview"></h1>
        <p class="lead" id="pvLead"></p>
      </div>
      <div class="tally big rows-tally" id="rowsTally" aria-hidden="true"></div>
      <div class="legend" aria-hidden="true"><span><i></i>هيتضاف</span><span><i class="bad"></i>فيه مشكلة</span></div>
      <p class="fixed-note" id="fixedNote" hidden></p>
    </div>

    <div class="glass pane rej" id="rej">
      <h3>صفوف مش هتتضاف</h3>
      <p>صلّحها في الملف واستورده تاني. اللي اتضافوا المرة دي مش هيتكرروا.</p>
      <div id="rejList"></div>
    </div>

    <details class="glass pane ok-list" id="okBox">
      <summary><span id="okTitle"></span><svg class="icon"><use href="#i-down"/></svg></summary>
      <ol id="okList"></ol>
    </details>

    <div class="actions">
      <button class="btn btn-light" type="button" id="doAdd"></button>
      <button class="textlink" type="button" id="again">اختار ملف تاني</button>
    </div>
  </section>

  <!-- 3. بعد الإضافة -->
  <section class="step s-done" aria-labelledby="h-done">
    <div class="glass pane" style="display:grid;gap:var(--s-3)">
      <div>
        <h1 id="h-done"></h1>
        <p class="lead">ابعت لكل واحد الكارت بتاعه على واتساب علشان يتمسح من أول جلسة. اللي تبعتله بيتعلّم عليه.</p>
      </div>
      <div class="sent-bar" aria-hidden="true"><span id="sentBar"></span></div>
      <p class="sent-count" id="sentCount" role="status"></p>
    </div>
    <div class="glass pane" id="waList" style="padding-block:var(--s-2)" aria-label="ابعت الكروت"></div>
    <div class="actions">
      <a class="btn btn-glass" href="<?php echo esc_url( stmina_att_url( 'members' ) ); ?>"><svg class="icon"><use href="#i-users"/></svg>روح لقائمة المخدومين</a>
    </div>
  </section>

  <!-- ملف مش مفهوم -->
  <section class="step s-bad" aria-labelledby="h-bad">
    <div class="glass pane" style="display:grid;gap:var(--s-4)">
      <span class="bad-ic"><svg class="icon"><use href="#i-alert"/></svg></span>
      <h1 id="h-bad">مش لاقيين أسماء في الملف ده</h1>
      <p class="lead" id="badLead">اتأكد إن الملف Excel وفيه عمودين: الاسم في الأول والموبايل في التاني، وإن الأسماء في أول صفحة في الملف.</p>
      <div class="actions">
        <button class="btn btn-light" type="button" id="badAgain"><svg class="icon"><use href="#i-upload"/></svg>اختار ملف تاني</button>
        <button class="textlink" type="button" id="tpl2"><svg class="icon" style="width:18px;height:18px"><use href="#i-download"/></svg>نزّل ملف جاهز تملاه</button>
      </div>
    </div>
  </section>
</main>

<nav class="glass g-nav" aria-label="التنقل في نظام الحضور">
  <ul>
    <li><a href="<?php echo esc_url( stmina_att_url( 'sessions' ) ); ?>"><svg class="icon"><use href="#i-calendar"/></svg>الجلسات</a></li>
    <li><a href="<?php echo esc_url( stmina_att_url( 'members' ) ); ?>" aria-current="page"><svg class="icon"><use href="#i-users"/></svg>المخدومون</a></li>
    <li><a href="<?php echo esc_url( stmina_att_url( 'scan' ) ); ?>"><svg class="icon"><use href="#i-scan"/></svg>المسح</a></li>
    <li><a href="<?php echo esc_url( stmina_att_url( 'more' ) ); ?>"><svg class="icon"><use href="#i-more"/></svg>المزيد</a></li>
  </ul>
</nav>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

<?php stmina_att_footer( 'import' ); ?>
</body>
</html>
