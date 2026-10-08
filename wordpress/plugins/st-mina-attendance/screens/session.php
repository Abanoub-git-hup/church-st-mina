<?php
/**
 * شاشة "session" في نظام الحضور. متولّدة من design/attend-session.html بأداة tools/convert-attend.js،
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
<title>تفاصيل الجلسة | إعداد الخدام</title>
<meta name="description" content="حضور جلسة في خدمة إعداد الخدام: مين حضر ومين لسه، وتصحيح السجلات، وإنهاء الجلسة.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@200;300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo esc_url( get_template_directory_uri() . '/assets/site.css' ); ?>">
<link rel="stylesheet" href="<?php echo esc_url( STMINA_ATT_URL . 'assets/attend.css?ver=' . STMINA_ATT_VERSION ); ?>">
<style>
/* خاص بتفاصيل الجلسة */
.wrap-d{flex:1;width:100%;max-width:520px;margin-inline:auto;padding:var(--s-3) var(--gut) calc(170px + env(safe-area-inset-bottom))}
.d-top{position:sticky;top:var(--s-3);z-index:20;display:flex;align-items:center;gap:var(--s-2);padding:var(--s-2);border-radius:20px;margin-bottom:var(--s-4)}
.d-title{flex:1;min-width:0;line-height:1.35}
.d-title b{display:block;font-size:1rem;font-weight:500}
.d-title span{font-size:.8rem;color:var(--on-glass-2)}
.ibtn{width:44px;height:44px;flex:none;display:grid;place-items:center;border:0;border-radius:14px;background:var(--glass-2);color:var(--on-glass);cursor:pointer}
.ibtn:hover{background:var(--glass-3)}
.ibtn .icon{width:20px;height:20px}
.state{display:inline-flex;align-items:center;gap:6px;padding:2px 10px;border-radius:999px;background:var(--glass-2);font-size:.75rem;font-weight:500;vertical-align:middle;margin-inline-start:6px}
.state::before{content:"";width:6px;height:6px;border-radius:50%;background:var(--gold-soft)}
[data-state=ended] .state::before{background:rgba(255,248,238,.5)}

/* الملخص: 3 أرقام وعلامات عدّ ملوّنة بالحالة */
.sum{padding:var(--s-5)}
.nums{display:grid;grid-template-columns:repeat(3,1fr);gap:var(--s-2);margin-bottom:var(--s-4)}
.num{text-align:center}
.num strong{display:block;font-size:2.4rem;font-weight:200;line-height:1.05;font-variant-numeric:tabular-nums}
.num span{display:inline-flex;align-items:center;gap:6px;font-size:.8rem;color:var(--on-glass-2)}
.num span::before{content:"";width:8px;height:8px;border-radius:50%}
.num.here span::before{background:var(--on-glass)}
.num.excuse span::before{background:var(--excuse)}
.num.rest span::before{background:rgba(255,248,238,.3)}
[data-state=ended] .num.rest span::before{background:var(--bad)}
.tally i.ex{background:var(--excuse);height:16px}
.tally.big i.ex{height:28px}
[data-state=ended] .tally i.no{background:rgba(217,96,79,.55)}
.sum-note{margin-top:var(--s-3);font-size:.82rem;color:var(--on-glass-2);line-height:1.7}

/* الفلتر والبحث */
.tools{margin:var(--s-5) 0 var(--s-3)}
.filters{display:flex;gap:var(--s-2);overflow-x:auto;padding-bottom:4px;margin-bottom:var(--s-3);scrollbar-width:none}
.filters::-webkit-scrollbar{display:none}
.filters button{flex:none;display:inline-flex;align-items:center;gap:6px;min-height:44px;padding:0 16px;border-radius:999px;border:1px solid var(--glass-line-soft);background:var(--glass-2);color:var(--on-glass-2);font:inherit;font-size:.86rem;cursor:pointer;-webkit-backdrop-filter:blur(12px);backdrop-filter:blur(12px)}
.filters button b{font-weight:500;font-variant-numeric:tabular-nums}
.filters button[aria-pressed=true]{background:var(--btn-light);color:var(--ink);border-color:transparent}
.g-input .end .icon{width:20px;height:20px}

/* القائمة */
.list{padding:var(--s-2)}
.p-row{border-radius:16px}
.p-row + .p-row{margin-top:2px}
.p-row.open{background:var(--glass-2)}
.p-pick{display:grid;grid-template-columns:36px 1fr auto;align-items:center;gap:var(--s-3);width:100%;min-height:56px;padding:8px var(--s-2);border:0;border-radius:16px;background:none;color:var(--on-glass);font:inherit;text-align:start;cursor:pointer}
.p-pick:hover{background:var(--glass-2)}
.av{width:36px;height:36px;border-radius:50%;display:grid;place-items:center;background:var(--glass-3);font-size:.8rem;font-weight:500}
.p-name{display:block;font-size:.95rem;line-height:1.4}
.p-meta{display:block;font-size:.76rem;color:var(--on-glass-2)}
.badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:999px;font-size:.76rem;font-weight:500;white-space:nowrap}
.badge .icon{width:13px;height:13px;stroke-width:2.2}
.badge.here{background:rgba(155,208,127,.2);color:#C9EDB6}
.badge.excuse{background:rgba(127,166,191,.25);color:#CFE3F0}
.badge.absent{background:rgba(217,96,79,.25);color:var(--bad-text)}
.badge.none{background:rgba(255,248,238,.08);color:var(--on-glass-2)}
.tag{display:inline-block;margin-inline-start:6px;padding:0 8px;border-radius:999px;background:var(--glass-3);font-size:.75rem;line-height:1.7;vertical-align:middle}
.p-edit{display:none;padding:0 var(--s-2) var(--s-3)}
.p-row.open .p-edit{display:block}
.p-log{font-size:.8rem;line-height:1.8;color:var(--on-glass-2);padding:0 var(--s-1) var(--s-2)}
.p-log b{color:var(--on-glass);font-weight:500}
.p-set{display:grid;grid-template-columns:repeat(3,1fr);gap:var(--s-2)}
.p-set button{min-height:46px;border-radius:12px;border:1px solid var(--glass-line-soft);background:var(--glass-2);color:var(--on-glass);font:inherit;font-size:.86rem;cursor:pointer}
.p-set button[aria-pressed=true]{background:var(--btn-light);color:var(--ink);border-color:transparent}
.empty{padding:var(--s-6) var(--s-3);text-align:center;font-size:.9rem;color:var(--on-glass-2)}

/* شريط الإنهاء تحت */
.end-bar{position:fixed;inset-inline:var(--s-3);bottom:calc(var(--s-3) + env(safe-area-inset-bottom));z-index:30;max-width:520px;margin-inline:auto;padding:var(--s-3);border-radius:22px}
.end-info{display:flex;align-items:center;justify-content:space-between;gap:var(--s-3);padding:0 var(--s-2) var(--s-3);font-size:.84rem;color:var(--on-glass-2)}
.end-info b{color:var(--on-glass);font-weight:500}
.btn-end{background:rgba(180,68,58,.85);color:#fff}
.btn-end:hover{background:#B4443A}
.confirm{display:none;padding:var(--s-2) var(--s-2) 0}
.end-bar.confirming .confirm{display:block}
.end-bar.confirming .end-open{display:none}
.confirm p{font-size:.92rem;line-height:1.85;color:var(--on-glass-2);margin-bottom:var(--s-4)}
.confirm p b{color:var(--on-glass);font-weight:500}
.hold-row{display:flex;align-items:center;gap:var(--s-4)}
/* زر الضغط المطوّل: الدايرة بتكمل في ثانية ونص */
.hold{position:relative;flex:none;width:76px;height:76px;border:0;border-radius:50%;background:rgba(180,68,58,.85);color:#fff;font:inherit;font-size:.8rem;font-weight:500;line-height:1.3;cursor:pointer;touch-action:none;-webkit-user-select:none;user-select:none}
.hold svg{position:absolute;inset:-6px;width:88px;height:88px;rotate:-90deg;pointer-events:none}
.hold circle{fill:none;stroke-width:3}
.hold .track{stroke:rgba(255,248,238,.18)}
.hold .ring{stroke:#fff;stroke-linecap:round;stroke-dasharray:264;stroke-dashoffset:264}
.hold.pressing .ring{stroke-dashoffset:0;transition:stroke-dashoffset 1.5s linear}
.hold:disabled{background:rgba(255,248,238,.12);color:var(--on-glass-3);cursor:not-allowed}
.hold-help{font-size:.84rem;line-height:1.7;color:var(--on-glass-2)}
.hold-help .textlink{display:flex;margin-top:var(--s-1);min-height:44px;justify-content:flex-start}
.blocked{display:none;gap:var(--s-2);align-items:flex-start;margin-bottom:var(--s-4);padding:var(--s-3);border-radius:14px;background:rgba(242,179,90,.16);border:1px solid rgba(242,179,90,.4);font-size:.86rem;line-height:1.75;color:#FFE2B8}
.blocked .icon{width:18px;height:18px;flex:none;margin-top:3px}
[data-state=unsynced] .blocked{display:flex}
.done{display:none;align-items:center;gap:var(--s-3);padding:var(--s-2);font-size:.88rem;line-height:1.6;color:var(--on-glass-2)}
.done .icon{width:22px;height:22px;flex:none;color:var(--ok)}
.done b{color:var(--on-glass);font-weight:500}
[data-state=ended] .done{display:flex}
[data-state=ended] .end-open,[data-state=ended] .confirm{display:none !important}
[data-state=ended] .scan-link{display:none}
</style>
</head>
<body data-state="open">
<a class="skip" href="#main">تخطَّ إلى المحتوى</a>

<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="i-right" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></symbol>
  <symbol id="i-scan" viewBox="0 0 24 24"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2"/><rect x="7" y="7" width="4" height="4" rx=".5"/><rect x="13" y="13" width="4" height="4" rx=".5"/><path d="M13 7h4v4M7 13v4h4"/></symbol>
  <symbol id="i-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></symbol>
  <symbol id="i-check" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></symbol>
  <symbol id="i-x" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></symbol>
  <symbol id="i-dash" viewBox="0 0 24 24"><path d="M6 12h12"/></symbol>
  <symbol id="i-note" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></symbol>
  <symbol id="i-upload" viewBox="0 0 24 24"><path d="M12 16V4M7 9l5-5 5 5M4 20h16"/></symbol>
  <symbol id="i-lock" viewBox="0 0 24 24"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></symbol>
</svg>


<main class="wrap-d" id="main">
  <header class="glass d-top">
    <a class="ibtn" href="<?php echo esc_url( stmina_att_url( 'sessions' ) ); ?>" aria-label="رجوع للجلسات"><svg class="icon"><use href="#i-right"/></svg></a>
    <div class="d-title">
      <b>اجتماع <span class="state" id="stateTxt">مفتوحة</span></b>
      <span id="dateTxt">النهاردة</span>
    </div>
    <a class="ibtn scan-link" href="<?php echo esc_url( stmina_att_url( 'scan' ) ); ?>" aria-label="كمّل المسح"><svg class="icon"><use href="#i-scan"/></svg></a>
  </header>

  <section class="glass sum" aria-label="ملخص الحضور">
    <div class="nums">
      <div class="num here"><strong id="nHere">0</strong><span>حضر</span></div>
      <div class="num excuse"><strong id="nEx">0</strong><span>بعذر</span></div>
      <div class="num rest"><strong id="nRest">0</strong><span id="restLbl">لسه</span></div>
    </div>
    <div id="tally"></div>
    <p class="sum-note" id="sumNote"></p>
  </section>

  <div class="tools">
    <div class="filters" role="group" aria-label="اعرض" id="filters"></div>
    <div class="g-input has-end">
      <input type="search" id="q" placeholder="دوّر باسم المخدوم" aria-label="دوّر باسم المخدوم" autocomplete="off">
      <span class="end" aria-hidden="true"><svg class="icon"><use href="#i-search"/></svg></span>
    </div>
  </div>

  <section class="glass list" id="list" aria-label="المخدومون في الجلسة" aria-live="polite"></section>
</main>

<!-- إنهاء الجلسة -->
<section class="glass end-bar" id="endBar" aria-label="إنهاء الجلسة">
  <div class="end-open">
    <p class="end-info" id="endInfo"></p>
    <button class="btn btn-end" type="button" id="askEnd"><svg class="icon"><use href="#i-lock"/></svg>إنهاء الجلسة</button>
  </div>
  <div class="confirm" id="confirm">
    <p class="blocked" role="alert"><svg class="icon"><use href="#i-upload"/></svg><span>فيه <b>3 عمليات مسح</b> على موبايلك لسه ماتزامنتش. شغّل النت واستنى لحد ما تتزامن، وبعدها تقدر تنهي الجلسة.</span></p>
    <p id="confirmTxt"></p>
    <div class="hold-row">
      <button class="hold" type="button" id="hold" aria-describedby="holdHelp">
        <svg viewBox="0 0 88 88" aria-hidden="true"><circle class="track" cx="44" cy="44" r="42"/><circle class="ring" cx="44" cy="44" r="42"/></svg>
        اضغط<br>مطوّل
      </button>
      <div class="hold-help" id="holdHelp">
        اضغط على الزرار وسيبه بعد ما الدايرة تكمل (ثانية ونص). مفيش تراجع بعدها.
        <button class="textlink" type="button" id="cancelEnd">رجوع من غير ما أنهي</button>
      </div>
    </div>
  </div>
  <p class="done" role="status"><svg class="icon"><use href="#i-check"/></svg><span id="doneTxt"></span></p>
</section>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

<?php stmina_att_footer( 'session' ); ?>
</body>
</html>
