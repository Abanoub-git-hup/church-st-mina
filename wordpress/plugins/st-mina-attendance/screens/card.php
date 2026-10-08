<?php
/**
 * شاشة "card" في نظام الحضور. متولّدة من design/attend-card.html بأداة tools/convert-attend.js،
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
<meta name="robots" content="noindex, nofollow">
<title>حضوري | إعداد الخدام</title>
<meta name="description" content="صفحة حضورك في خدمة إعداد الخدام وكارتك.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@200;300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo esc_url( get_template_directory_uri() . '/assets/site.css' ); ?>">
<link rel="stylesheet" href="<?php echo esc_url( STMINA_ATT_URL . 'assets/attend.css?ver=' . STMINA_ATT_VERSION ); ?>">
<style>
/* صفحة المخدوم: "حضوري" و"كارتي". قراءة بس، ومفيهاش أي حاجة تخص مخدوم تاني */
.wrap-c{flex:1;width:100%;max-width:480px;margin-inline:auto;padding:var(--s-6) var(--gut) 0;display:grid;gap:var(--s-4);align-content:start}
.hello{display:flex;align-items:center;gap:var(--s-3);text-shadow:0 2px 12px rgba(0,0,0,.45)}
.hello img{width:44px;height:44px;border-radius:50%;box-shadow:0 0 0 1px rgba(255,244,228,.4)}
.hello small{display:block;font-size:.8rem;font-weight:500;color:var(--gold-soft)}
.hello h1{font-size:1.6rem;font-weight:300;line-height:1.3}
/* موقع الكنيسة: أيقونة فوق في آخر السطر، بنفس شكل أيقونة الخروج عند الخدام (.out في attend.css) */
.hello .out{margin-inline-start:auto}
[hidden]{display:none !important}

/* رسالة الخدام */
.msg-strip{display:flex;gap:var(--s-3);align-items:flex-start;padding:var(--s-3) var(--s-4);border-radius:18px;background:linear-gradient(135deg,rgba(243,201,135,.28),rgba(243,201,135,.1));border:1px solid rgba(243,201,135,.35);-webkit-backdrop-filter:blur(16px);backdrop-filter:blur(16px);font-size:.94rem;line-height:1.8;color:#FFF3DE}
.msg-strip .icon{width:20px;height:20px;flex:none;margin-top:4px;color:var(--gold-soft)}
.msg-strip small{display:block;font-size:.76rem;color:rgba(255,243,222,.7)}

.card{padding:var(--s-5)}
.c-h{display:flex;align-items:baseline;justify-content:space-between;gap:var(--s-3);margin-bottom:var(--s-4)}
.c-h h2{font-size:1.05rem;font-weight:500}
.c-h small{font-size:.78rem;color:var(--on-glass-2)}

/* النسبة + فلتر نوع النشاط */
.types{display:flex;gap:var(--s-2);overflow-x:auto;padding-bottom:4px;margin-bottom:var(--s-5);scrollbar-width:none}
.types::-webkit-scrollbar{display:none}
/* الشاشات الضيقة: الأنواع تنزل سطر تاني بدل ما آخر واحد يتقص ومايبانش إنه بيتسحب */
@media (max-width:420px){.types{flex-wrap:wrap;overflow-x:visible}}
.types button{flex:none;min-height:44px;padding:0 16px;border-radius:999px;border:1px solid var(--glass-line-soft);background:var(--glass-2);color:var(--on-glass-2);font:inherit;font-size:.86rem;cursor:pointer}
.types button[aria-pressed=true]{background:var(--btn-light);color:var(--ink);border-color:transparent}
.pct-row{display:grid;grid-template-columns:132px 1fr;gap:var(--s-5);align-items:center}
.ring{--p:0;position:relative;width:132px;height:132px;border-radius:50%;display:grid;place-items:center;background:conic-gradient(var(--gold-soft) calc(var(--p) * 1%),rgba(255,248,238,.14) 0);transition:--p .6s}
.ring::before{content:"";position:absolute;inset:6px;border-radius:50%;background:rgba(40,27,18,.7)}
.ring div{position:relative;text-align:center;line-height:1.05}
.ring strong{display:block;font-size:2.6rem;font-weight:200;font-variant-numeric:tabular-nums}
.ring span{font-size:.78rem;color:var(--on-glass-2)}
.pct-facts{display:grid;gap:var(--s-2);font-size:.88rem;line-height:1.6;color:var(--on-glass-2)}
.pct-facts b{color:var(--on-glass);font-weight:500;font-variant-numeric:tabular-nums}

/* الشموع: شمعة مولّعة لكل جلسة حضرها ورا بعض، وشمعة مطفية مستنياه */
.candles{display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;min-height:76px;padding:var(--s-2) var(--s-1) 0}
.cd{position:relative;width:10px;height:38px;border-radius:3px 3px 2px 2px;background:linear-gradient(90deg,#E9D9BE,#FFF6E6 45%,#D8C4A2)}
.cd::before{content:"";position:absolute;left:50%;bottom:100%;translate:-50% -3px;width:10px;height:18px;border-radius:50%/60% 60% 40% 40%;background:radial-gradient(ellipse at 50% 70%,#fff 0%,#FFE2A0 30%,#FF9E45 70%,transparent 72%);transform-origin:50% 90%;animation:flame 1.8s ease-in-out infinite}
.cd::after{content:"";position:absolute;left:50%;bottom:100%;translate:-50% -1px;width:2px;height:4px;background:#5a4636;border-radius:1px}
.cd:nth-child(2n)::before{animation-delay:-.6s}
.cd:nth-child(3n)::before{animation-delay:-1.1s}
.cd.lit{box-shadow:0 -14px 22px -8px rgba(255,170,80,.45)}
.cd.off{opacity:.45}
.cd.off::before{display:none}
.more-c{align-self:center;font-size:.86rem;color:var(--gold-soft)}
.streak-t{margin-top:var(--s-4);font-size:.95rem;line-height:1.7}
.streak-t b{font-size:1.3rem;font-weight:400;color:var(--gold-soft)}
.streak-t small{display:block;font-size:.8rem;color:var(--on-glass-2)}

/* الرسم الشهري */
.bars{display:grid;grid-template-columns:repeat(6,1fr);gap:var(--s-3);align-items:end;height:150px;padding-top:var(--s-5)}
.bar{display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;gap:6px}
.bar i{display:block;width:100%;max-width:34px;border-radius:8px 8px 4px 4px;background:rgba(255,248,238,.55);min-height:4px}
.bar.now i{background:var(--gold-soft)}
.bar em{font-style:normal;font-size:.76rem;font-variant-numeric:tabular-nums;color:var(--on-glass-2)}
.bar.now em{color:var(--gold-soft)}
.bar span{font-size:.75rem;color:var(--on-glass-2);white-space:nowrap}
.bar.none i{background:rgba(255,248,238,.1)}

/* آخر 10 جلسات */
.last{display:grid;gap:2px}
.ls{display:grid;grid-template-columns:1fr auto;gap:var(--s-3);align-items:center;min-height:52px;padding:6px var(--s-2);border-radius:12px}
.ls:nth-child(odd){background:rgba(255,244,228,.04)}
.ls b{display:block;font-size:.92rem;font-weight:400}
.ls small{font-size:.78rem;color:var(--on-glass-2)}
.badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:999px;font-size:.76rem;font-weight:500;white-space:nowrap}
.badge .icon{width:13px;height:13px;stroke-width:2.2}
.badge.h{background:rgba(155,208,127,.2);color:#C9EDB6}
.badge.e{background:rgba(127,166,191,.25);color:#CFE3F0}
.badge.a{background:rgba(217,96,79,.25);color:var(--bad-text)}

/* تثبيت الصفحة على الموبايل */
/* الرقم السري: نفس شكل كارت التثبيت، والفورم بيتفتح جواه */
.pin-form{grid-column:1/-1;display:grid;gap:var(--s-3)}
.pin-row{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:var(--s-2)}
.pin-row input{text-align:center;letter-spacing:.3em}
.pin-row input::placeholder{letter-spacing:0}
.pin-err{display:none;font-size:.84rem;color:var(--bad-text)}
.pin-err.show{display:block}
.pin-form .btn{height:46px}
.pin-change{grid-column:1/-1;justify-self:start}
#pinBox.done .pin-form{display:none}
#pinBox.done.edit .pin-form{display:grid}
#pinBox:not(.done) .pin-change,#pinBox.edit .pin-change{display:none}

.install{display:grid;grid-template-columns:44px 1fr;gap:var(--s-3);align-items:start;padding:var(--s-4)}
.install .ic{width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:var(--glass-3)}
.install b{display:block;font-size:.95rem;font-weight:500}
.install p{font-size:.84rem;line-height:1.75;color:var(--on-glass-2);margin-top:2px}
.install .row{grid-column:1/-1;display:flex;gap:var(--s-2);align-items:center}
.install .row .btn{flex:1;height:46px}

/* كارتي: لوحة كريمي 9:16 */
/* الكارت: صورة الصلاة في ضوء الشباك، والـ QR في نص شعاع النور على مربع أبيض علشان يتقري، والاسم على زجاج خفيف تحت */
.my-card{position:relative;width:min(100%,340px);aspect-ratio:9/16;margin:0 auto;padding:22px 20px 18px;border-radius:28px;overflow:hidden;isolation:isolate;color:#F6ECDC;display:flex;flex-direction:column;align-items:center;text-align:center;box-shadow:0 30px 60px rgba(10,6,3,.5),0 0 0 1px rgba(243,201,135,.28)}
.my-card::before{content:"";position:absolute;inset:0;z-index:-1;background:linear-gradient(180deg,rgba(20,12,6,.62) 0%,rgba(20,12,6,.08) 26%,rgba(20,12,6,0) 46%,rgba(20,12,6,.18) 66%,rgba(20,12,6,.86) 100%),url("<?php echo esc_url( get_template_directory_uri() . '/assets/media/church/bg-praying-light.jpg' ); ?>") center 30%/cover no-repeat}
.my-card img{width:52px;height:52px;border-radius:50%;box-shadow:0 0 0 1px rgba(243,201,135,.7),0 6px 18px rgba(0,0,0,.45)}
.my-card .ch{margin-top:8px;font-size:.72rem;line-height:1.5;color:rgba(246,236,220,.88);text-shadow:0 1px 8px rgba(0,0,0,.7)}
.my-card .qr{width:64%;aspect-ratio:1;margin-top:auto;padding:5.5%;background:#fff;border-radius:18px;box-shadow:0 0 0 1px rgba(243,201,135,.9),0 0 46px 6px rgba(255,214,150,.42),0 14px 30px rgba(10,6,3,.35)}
.my-card .qr svg{width:100%;height:100%;display:block}
.my-card .who{position:relative;width:100%;margin-top:auto;padding:12px 14px 10px;border-radius:18px}
.my-card .who::before{content:"";position:absolute;inset:0;z-index:-1;border-radius:inherit;background:rgba(255,244,228,.09);backdrop-filter:blur(18px) saturate(1.4);-webkit-backdrop-filter:blur(18px) saturate(1.4);border:1px solid transparent;background-clip:padding-box;box-shadow:inset 0 1px 0 rgba(243,201,135,.45),inset 0 0 0 1px rgba(246,236,220,.12)}
.my-card .c-svc{font-size:.76rem;font-weight:500;color:#F3C987}
.my-card .nm{font-size:1.4rem;font-weight:500;line-height:1.35;margin:2px 0 4px;text-shadow:0 2px 12px rgba(0,0,0,.55)}
.my-card .tip{font-size:.72rem;line-height:1.6;color:rgba(246,236,220,.78)}
.card-acts{display:grid;gap:var(--s-2);width:min(100%,340px);margin:var(--s-4) auto 0}
.card-help{text-align:center;font-size:.82rem;line-height:1.75;color:var(--on-glass-2);text-shadow:0 1px 8px rgba(0,0,0,.5)}

/* رابط غير صالح */
.bad-link{padding:var(--s-8) var(--s-5);text-align:center}
.bad-link .ic{width:64px;height:64px;margin:0 auto var(--s-4);border-radius:50%;display:grid;place-items:center;background:var(--glass-3)}
.bad-link .ic .icon{width:28px;height:28px}
.bad-link h1{font-size:1.3rem;font-weight:400;margin-bottom:var(--s-2)}
.bad-link p{font-size:.9rem;line-height:1.85;color:var(--on-glass-2)}
.bad-acts{display:grid;gap:var(--s-2);margin-top:var(--s-5)}
.bad-acts .btn{height:48px;font-size:.95rem}

/* التبويبين تحت */
.g-nav.two ul{grid-template-columns:repeat(2,1fr)}
.g-nav button{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;width:100%;height:100%;border:0;border-radius:16px;background:none;color:var(--on-glass-2);font:inherit;font-size:.8rem;cursor:pointer}
.g-nav button[aria-selected=true]{background:var(--glass-3);color:var(--on-glass)}
.g-nav button .icon{width:22px;height:22px}
</style>
<?php stmina_att_card_head(); ?>
</head>
<body class="has-nav">
<a class="skip" href="#main">تخطَّ إلى المحتوى</a>

<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="i-heart-s" viewBox="0 0 24 24"><path d="M12 21s-7-4.5-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 5.5-7 10-7 10Z"/></symbol>
  <symbol id="i-chart" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 15v3M12 10v8M17 6v12"/></symbol>
  <symbol id="i-qr" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM20 14v.01M14 20h.01M17 20h4M20 17v4"/></symbol>
  <symbol id="i-check" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></symbol>
  <symbol id="i-x" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></symbol>
  <symbol id="i-note" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></symbol>
  <symbol id="i-down" viewBox="0 0 24 24"><path d="M12 4v12M7 11l5 5 5-5M4 20h16"/></symbol>
  <symbol id="i-phone-add" viewBox="0 0 24 24"><rect x="6" y="2" width="12" height="20" rx="2"/><path d="M12 8v6M9 11h6"/></symbol>
  <symbol id="i-link-off" viewBox="0 0 24 24"><path d="M9 15l6-6M10 6l1-1a4.2 4.2 0 0 1 6 6l-1 1M14 18l-1 1a4.2 4.2 0 0 1-6-6l1-1M3 3l18 18"/></symbol>
  <symbol id="i-lock" viewBox="0 0 24 24"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></symbol>
  <symbol id="i-home" viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1Z"/></symbol>
</svg>


<main class="wrap-c" id="main">
  <!-- رابط غير صالح -->
  <section class="glass bad-link" id="badLink" hidden>
    <div class="ic"><svg class="icon"><use href="#i-link-off"/></svg></div>
    <h1>الرابط ده مش شغال</h1>
    <p>ممكن يكون اتعملك كارت جديد والقديم اتلغى. اطلب من خادم إعداد الخدام يبعتلك الرابط الجديد على واتساب.</p>
    <div class="bad-acts">
      <a class="btn btn-glass" href="<?php echo esc_url( stmina_att_url( 'login' ) . '#member' ); ?>">ادخل بموبايلك والرقم السري</a>
      <a class="textlink" href="<?php echo esc_url( home_url( '/' ) ); ?>">العودة إلى موقع الكنيسة</a>
    </div>
  </section>

  <div id="page" hidden>
    <header class="hello">
      <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/media/brand/logo.png' ); ?>" alt="" width="44" height="44" id="logo">
      <div><small>إعداد الخدام</small><h1 id="hi"></h1></div>
      <a class="glass out" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="موقع الكنيسة" title="موقع الكنيسة"><svg class="icon"><use href="#i-home"/></svg></a>
    </header>

    <!-- حضوري -->
    <div id="tab-me" role="tabpanel" aria-labelledby="t-me" style="display:grid;grid-template-columns:minmax(0,1fr);gap:var(--s-4);margin-top:var(--s-4)">
      <div class="msg-strip" id="msg"><svg class="icon"><use href="#i-heart-s"/></svg><div><span id="msgTxt"></span><small>من خدام إعداد الخدام</small></div></div>

      <section class="glass card" aria-labelledby="pctH">
        <div class="c-h"><h2 id="pctH">نسبة حضورك</h2><small id="since"></small></div>
        <div class="types" role="group" aria-label="نوع النشاط" id="types"></div>
        <div class="pct-row">
          <div class="ring" id="ring" role="img"><div><strong id="pct"></strong><span>حضور</span></div></div>
          <div class="pct-facts" id="facts"></div>
        </div>
      </section>

      <section class="glass card" aria-labelledby="stH">
        <div class="c-h"><h2 id="stH">ورا بعض</h2><small>الغياب بعذر مابيقطعش العدّ</small></div>
        <div class="candles" id="candles" aria-hidden="true"></div>
        <p class="streak-t" id="streakT"></p>
      </section>

      <section class="glass card" aria-labelledby="moH">
        <div class="c-h"><h2 id="moH">آخر 6 شهور</h2><small>نسبة كل شهر</small></div>
        <div class="bars" id="bars" role="img"></div>
      </section>

      <section class="glass card" aria-labelledby="lsH">
        <div class="c-h"><h2 id="lsH">آخر 10 جلسات</h2></div>
        <div class="last" id="last"></div>
      </section>

      <section class="glass install" id="pinBox" aria-labelledby="pinH">
        <span class="ic"><svg class="icon"><use href="#i-lock"/></svg></span>
        <div><b id="pinH">اعمل رقم سري</b><p id="pinP">علشان لو الرابط ده ضاع منك، تفتح صفحتك من "دخول المخدوم" بموبايلك والرقم السري.</p></div>
        <form class="pin-form" id="pinForm" novalidate>
          <div class="pin-row">
            <div class="g-input"><input id="pin1" type="password" inputmode="numeric" maxlength="4" dir="ltr" autocomplete="new-password" placeholder="••••" aria-label="الرقم السري"></div>
            <div class="g-input"><input id="pin2" type="password" inputmode="numeric" maxlength="4" dir="ltr" autocomplete="new-password" placeholder="اكتبه تاني" aria-label="اكتب الرقم السري تاني"></div>
          </div>
          <p class="pin-err" id="pinErr" role="alert"></p>
          <button class="btn btn-light" type="submit">احفظ الرقم السري</button>
        </form>
        <button class="textlink pin-change" type="button" id="pinChange">غيّر الرقم السري</button>
      </section>

      <section class="glass install" id="install" aria-labelledby="inH">
        <span class="ic"><svg class="icon"><use href="#i-phone-add"/></svg></span>
        <div><b id="inH">حط الصفحة على شاشة موبايلك</b><p id="inSteps"></p></div>
        <div class="row">
          <button class="btn btn-light" type="button" id="inBtn" hidden>ثبّتها</button>
          <button class="textlink" type="button" id="inHide">مش دلوقتي</button>
        </div>
      </section>
    </div>

    <!-- كارتي -->
    <div id="tab-card" role="tabpanel" aria-labelledby="t-card" hidden style="margin-top:var(--s-4)">
      <div class="my-card" id="myCard">
        <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/media/brand/logo.png' ); ?>" alt="">
        <p class="ch">كنيسة السيدة العذراء ومارمينا<br>والبابا كيرلس السادس · الجبل الأصفر</p>
        <div class="qr" id="qr" role="img" aria-label="كود الحضور"></div>
        <div class="who">
          <p class="c-svc">إعداد الخدام</p>
          <p class="nm" id="cardName"></p>
          <p class="tip">ورّي الكارت ده للخادم في كل جلسة</p>
        </div>
      </div>
      <div class="card-acts">
        <button class="btn btn-light" type="button" id="save"><svg class="icon"><use href="#i-down"/></svg>احفظ الكارت كصورة</button>
        <p class="card-help">الصورة بتتحفظ في الموبايل. لو الكاميرا مش قارياه، علّي سطوع الشاشة.</p>
      </div>
    </div>
  </div>
</main>

<nav class="glass g-nav two" id="tabs" aria-label="صفحتك" hidden>
  <ul role="tablist">
    <li><button type="button" role="tab" id="t-me" aria-controls="tab-me" aria-selected="true"><svg class="icon"><use href="#i-chart"/></svg>حضوري</button></li>
    <li><button type="button" role="tab" id="t-card" aria-controls="tab-card" aria-selected="false"><svg class="icon"><use href="#i-qr"/></svg>كارتي</button></li>
  </ul>
</nav>

<?php stmina_att_footer( 'card' ); ?>
</body>
</html>
