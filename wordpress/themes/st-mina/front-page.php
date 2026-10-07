<?php
/**
 * الصفحة الرئيسية: الـ 14 قسم من design/home.html.
 * النصوص اللي بتتعدّل من "إعدادات الرئيسية" بتيجي من دالة stmina_home().
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<!-- ===== الواجهة ===== -->
<section class="hero" id="top" aria-label="الترحيب">
  <div class="hero-bg"><img src="<?php stmina_media( 'church/hero-prayer.png' ); ?>" alt="شاب يصلي داخل الكنيسة"></div>
  <div class="rays" aria-hidden="true"><span class="window"></span><span class="ray"></span><span class="ray"></span><span class="ray"></span><span class="ray"></span><span class="ray"></span></div>
  <canvas id="dust" aria-hidden="true"></canvas>

  <header class="hero-top">
    <div class="wrap">
      <a class="brand" href="#top" data-intro><img src="<?php stmina_media( 'brand/logo.png' ); ?>" alt="شعار الكنيسة"><span><b>كنيسة السيدة العذراء ومارمينا</b><span class="nw">والبابا كيرلس السادس</span> <span class="nw"><span class="sep">· </span>الجبل الأصفر</span></span></a>
      <nav class="top-nav" aria-label="القائمة الرئيسية" data-intro>
        <ul><li><a href="#top" aria-current="page">الرئيسية</a></li><li><a href="<?php stmina_link( 'church-history' ); ?>">الكنيسة</a></li><li><a href="<?php stmina_link( 'worship' ); ?>">العبادة</a></li><li><a href="<?php stmina_link( 'services' ); ?>">الخدمات</a></li><li><a href="<?php stmina_link( 'library' ); ?>">المكتبة</a></li><li><a href="<?php stmina_link( 'news' ); ?>">الأخبار</a></li></ul>
      </nav>
      <div class="socials" data-intro>
        <a class="circle" href="https://www.facebook.com/santmarychurch" target="_blank" rel="noopener" aria-label="صفحة الكنيسة على فيسبوك"><svg class="icon"><use href="#i-facebook"/></svg></a>
        <a class="circle" href="https://www.youtube.com/@%D8%A7%D9%84%D9%82%D9%85%D8%B5%D9%83%D9%8A%D8%B1%D9%84%D8%B3%D8%B1%D9%88%D9%85%D8%A7%D9%86%D9%8A/" target="_blank" rel="noopener" aria-label="قناة يوتيوب"><svg class="icon"><use href="#i-youtube"/></svg></a>
        <a class="circle" href="https://wa.me/201227445837" target="_blank" rel="noopener" aria-label="واتساب الكنيسة"><svg class="icon"><use href="#i-whatsapp"/></svg></a>
      </div>
      <button class="menu-btn" data-open-menu aria-label="فتح القائمة" aria-controls="drawer"><svg class="icon"><use href="#i-menu"/></svg></button>
    </div>
  </header>

  <div class="hero-center">
    <div>
      <span class="hello" data-intro><svg class="icon"><use href="#i-cross"/></svg>آية اليوم</span>
      <h1 data-split><?php echo esc_html( stmina_home( 'hero_verse' ) ); ?> <em><?php echo esc_html( stmina_home( 'hero_highlight' ) ); ?></em></h1>
      <span class="ref" data-intro>(<?php echo esc_html( stmina_home( 'hero_ref' ) ); ?>)</span>
      <div class="hero-ctas" data-intro>
        <a class="pill" href="#schedule">مواعيد القداسات <span class="dot"><svg class="icon"><use href="#i-left"/></svg></span></a>
        <a class="pill ghost" href="#about">تعرّف على الكنيسة <span class="dot"><svg class="icon"><use href="#i-left"/></svg></span></a>
      </div>
    </div>
  </div>
</section>

<!-- ===== الدوائر الأربعة أسفل الواجهة ===== -->
<section class="hero-cards" aria-label="وصول سريع">
  <div class="wrap hcirc">
    <a class="hc next" href="<?php stmina_link( 'worship' ); ?>" data-tile>
      <span class="bi"><span class="hc-tx">
        <svg class="icon mark" aria-hidden="true"><use href="#i-cross"/></svg>
        <small>القداس القادم</small>
        <h3 id="nextMass">الأحد</h3>
        <p id="nextMassTime">7:00 – 10:00 صباحًا</p>
        <span class="link">كل المواعيد <svg class="icon"><use href="#i-nw"/></svg></span>
      </span></span>
    </a>
    <a class="hc photo live" href="https://st-takla.org/zJ/index.php/en-readings-katamaros?view=reading-arabic" target="_blank" rel="noopener" data-tile aria-label="القراءات اليومية على موقع الأنبا تكلا هيمانوت">
      <span class="bi"><img class="grade" src="<?php stmina_media( 'worship/youth-night-liturgy-2.jpeg' ); ?>" alt="">
        <span class="hc-tx"><span class="play book"><svg class="icon"><use href="#i-book"/></svg></span><span class="lbl read">القراءات اليومية</span></span>
      </span>
    </a>
    <a class="hc photo fathers" href="<?php stmina_link( 'church-fathers' ); ?>" data-tile>
      <span class="bi"><img class="grade" src="<?php stmina_media( 'worship/deacons.jpeg' ); ?>" alt="آباء الكنيسة مع الشمامسة أمام الهيكل">
        <span class="hc-tx"><small>كنيستنا</small><b>آباء الكنيسة<br>والشمامسة</b></span>
      </span>
    </a>
    <div class="hc quote" data-tile>
      <span class="bi"><span class="hc-tx">
        <span class="qm" aria-hidden="true">”</span>
        <b><?php echo esc_html( stmina_home( 'quote_text' ) ); ?></b>
        <small><?php echo esc_html( stmina_home( 'quote_author' ) ); ?></small>
      </span></span>
    </div>
  </div>
</section>

<!-- ===== مواعيد القداسات ===== -->
<section class="sec pool" id="schedule" data-cover-start aria-labelledby="t-sched">
  <div class="wrap sched">
    <div>
      <span class="eyebrow" data-rise>صلِّ معنا</span>
      <h2 class="title" id="t-sched" data-split>مواعيد <b>القداسات</b> والاجتماعات</h2>
      <div class="days">
        <div class="day" data-day="0" data-rise><div class="day-n">الأحد</div><div class="slots"><span>7:00 – 10:00 ص</span><span>9:00 – 11:00 ص</span></div></div>
        <div class="day" data-day="2" data-rise><div class="day-n">الثلاثاء</div><div class="slots"><span>8:00 – 9:30 ص</span></div></div>
        <div class="day" data-day="3" data-rise><div class="day-n">الأربعاء</div><div class="slots"><span>8:00 – 10:00 ص</span></div></div>
        <div class="day" data-day="5" data-rise><div class="day-n">الجمعة</div><div class="slots"><span>7:00 – 9:00 ص <em>التربية الكنسية</em></span><span>7:00 – 9:30 ص <em>الشعب</em></span><span>10:00 – 12:00 ظ</span><span class="meet">3:00 – 5:00 م <em>صلاة ودراسة الكتاب</em></span></div></div>
        <div class="day" data-day="6" data-rise><div class="day-n">السبت</div><div class="slots"><span>8:00 – 10:00 ص</span></div></div>
      </div>
      <div class="sched-foot" data-rise>
        <p>تتغير المواعيد في الأصوام والأعياد، وتُعلن في الأخبار.</p>
        <a class="pill" href="<?php stmina_link( 'worship' ); ?>">الجدول الكامل <span class="dot"><svg class="icon"><use href="#i-left"/></svg></span></a>
        <a class="link" href="https://st-takla.org/zJ/index.php/en-readings-katamaros?view=reading-arabic" target="_blank" rel="noopener">القراءات اليومية <svg class="icon"><use href="#i-nw"/></svg></a>
      </div>
    </div>
    <div class="sched-media" data-clip>
      <img class="grade" data-parallax src="<?php stmina_media( 'worship/youth-night-liturgy-1.jpeg' ); ?>" alt="أب كاهن يصلي عند المذبح">
      <div class="badge"><small>القداس الإلهي</small><b>الكنيسة الكبيرة والكنيسة الصغيرة</b></div>
    </div>
  </div>
</section>

<!-- ===== عن الكنيسة ===== -->
<section class="sec deep pool-l" id="about" aria-labelledby="t-about">
  <div class="wrap about">
    <div class="arch" data-clip><img src="<?php stmina_media( 'priests/fathers-group.png' ); ?>" alt="آباء الكنيسة الثلاثة"></div>
    <div>
      <span class="eyebrow" data-rise>عن كنيستنا</span>
      <h2 class="title" id="t-about" data-split>بيت الله <b>وباب السماء</b></h2>
      <p class="lead" data-rise>نبذة عن نشأة الكنيسة وتاريخها تظهر هنا، ويكتبها محرر المحتوى من لوحة التحكم.</p>
      <div class="meta" data-rise>
        <span><svg class="icon"><use href="#i-pin"/></svg>عرب العيايدة، ناحية الجبل الأصفر</span>
        <span><svg class="icon"><use href="#i-church"/></svg>مطرانية شبين القناطر وتوابعها</span>
      </div>
      <ul class="fathers" aria-label="آباء الكنيسة">
        <li data-rise><span class="fp" style="background-image:url('<?php stmina_media( 'priests/quote-kyrillos-3.jpg' ); ?>');background-size:265px auto;background-position:-32px -233px" role="img" aria-label="القمص كيرلس روماني"></span><span class="who"><b>القمص كيرلس روماني</b><span>كاهن الكنيسة</span></span></li>
        <li data-rise><span class="fp" style="background-image:url('<?php stmina_media( 'priests/quote-arsanios-1.jpg' ); ?>');background-size:252px auto;background-position:-49px -84px" role="img" aria-label="القس أرسانيوس عزت"></span><span class="who"><b>القس أرسانيوس عزت</b><span>كاهن الكنيسة</span></span></li>
        <li data-rise><span class="fp" style="background-image:url('<?php stmina_media( 'priests/quote-fam.jpg' ); ?>');background-size:301px auto;background-position:-5px -115px" role="img" aria-label="القس فام عبد المسيح"></span><span class="who"><b>القس فام عبد المسيح</b><span>كاهن الكنيسة</span></span></li>
      </ul>
      <div style="margin-top:var(--s-8)" data-rise><a class="pill" href="<?php stmina_link( 'church-history' ); ?>">نشأة الكنيسة <span class="dot"><svg class="icon"><use href="#i-left"/></svg></span></a></div>
    </div>
  </div>
</section>

<!-- ===== قداس افتتاح الكنيسة (يوتيوب: قناة القمص كيرلس روماني) ===== -->
<section class="sec pool" id="opening" aria-labelledby="t-open">
  <div class="wrap opening-grid">
    <div>
      <span class="eyebrow" data-rise>حدث تاريخي</span>
      <h2 class="title" id="t-open" data-split>قداس <b>افتتاح الكنيسة</b></h2>
      <p class="lead" data-rise>قداس افتتاح كنيستنا مسجّلًا كاملًا في ثلاثة أجزاء، ذكرى لنا وللأجيال القادمة.</p>
      <ul class="ev-meta" data-rise style="margin-top:var(--s-8)">
        <li><svg class="icon" aria-hidden="true"><use href="#i-calendar"/></svg>29 ديسمبر 2007</li>
        <li><svg class="icon" aria-hidden="true"><use href="#i-users"/></svg>أبونا كيرلس روماني، وأبونا مقار ماهر، وأبونا بطرس حليم</li>
      </ul>
      <div style="display:flex;flex-wrap:wrap;gap:var(--s-6);align-items:center" data-rise>
        <a class="pill" href="<?php stmina_link( 'church-history', '#opening' ); ?>">قصة الكنيسة <span class="dot"><svg class="icon"><use href="#i-left"/></svg></span></a>
        <a class="link" href="https://www.youtube.com/watch?v=3oN42iWOHGY" target="_blank" rel="noopener">شاهد على يوتيوب <svg class="icon"><use href="#i-nw"/></svg></a>
      </div>
    </div>
    <div class="yt" data-yt data-clip>
      <div class="yt-frame">
        <img class="grade" src="https://i.ytimg.com/vi/3oN42iWOHGY/hqdefault.jpg" alt="" loading="lazy">
        <button class="yt-play" aria-label="تشغيل الجزء 1"><svg class="icon"><use href="#i-play"/></svg></button>
        <span class="yt-cap">الجزء 1 · 1:32:01</span>
      </div>
      <div class="yt-parts" role="group" aria-label="أجزاء القداس">
        <button class="yt-part" data-id="3oN42iWOHGY" data-title="قداس افتتاح كنيسة العذراء ومارمينا والبابا كيرلس الجبل الأصفر 1" data-thumb="https://i.ytimg.com/vi/3oN42iWOHGY/hqdefault.jpg" aria-pressed="true" aria-label="الجزء 1، 1:32:01"><span class="yc" style="--h:64px" aria-hidden="true"><span class="glow"></span></span><b>الجزء 1</b><small>1:32:01</small></button>
        <button class="yt-part" data-id="S-1k0WgHzNA" data-title="قداس افتتاح كنيسة العذراء ومارمينا والبابا كيرلس الجبل الأصفر 2" data-thumb="https://i.ytimg.com/vi/S-1k0WgHzNA/hqdefault.jpg" aria-pressed="false" aria-label="الجزء 2، 1:28:36"><span class="yc" style="--h:62px" aria-hidden="true"><span class="glow"></span></span><b>الجزء 2</b><small>1:28:36</small></button>
        <button class="yt-part" data-id="848eJp9_nbI" data-title="قداس افتتاح كنيسة العذراء ومارمينا والبابا كيرلس الجبل الأصفر 3" data-thumb="https://i.ytimg.com/vi/848eJp9_nbI/hqdefault.jpg" aria-pressed="false" aria-label="الجزء 3، 48:27"><span class="yc" style="--h:34px" aria-hidden="true"><span class="glow"></span></span><b>الجزء 3</b><small>48:27</small></button>
      </div>
    </div>
  </div>
</section>

<!-- ===== اكتشف كنيستنا ===== -->
<section class="sec" aria-labelledby="t-disc">
  <div class="wrap">
    <div class="head-row">
      <div>
        <span class="eyebrow" data-rise>اكتشف</span>
        <h2 class="title" id="t-disc" data-split>كل ما تحتاج معرفته <b>عن كنيستنا</b></h2>
      </div>
    </div>
    <div class="disc">
      <a class="dcard" href="<?php stmina_link( 'worship' ); ?>" data-rise><img class="grade" src="<?php stmina_media( 'worship/youth-night-liturgy-3.jpeg' ); ?>" alt="" loading="lazy"><span class="go"><svg class="icon"><use href="#i-nw"/></svg></span><h3>جدول القداسات</h3><p>مواعيد القداسات والاجتماعات والاعتراف طوال الأسبوع.</p></a>
      <a class="dcard paper" href="<?php stmina_link( 'church-fathers' ); ?>" data-rise><img src="<?php stmina_media( 'priests/fathers-group.png' ); ?>" alt="" loading="lazy"><span class="go"><svg class="icon"><use href="#i-nw"/></svg></span><h3>الآباء الكهنة</h3><p>القمص كيرلس روماني، والقس أرسانيوس عزت، والقس فام عبد المسيح.</p></a>
      <a class="dcard" href="<?php stmina_link( 'church-history' ); ?>" data-rise><img class="grade" src="<?php stmina_media( 'church/history-founder.jpg' ); ?>" alt="" loading="lazy" style="object-position:center 20%"><span class="go"><svg class="icon"><use href="#i-nw"/></svg></span><h3>نشأة الكنيسة</h3><p>قصة كنيستنا منذ تأسيسها حتى اليوم.</p></a>
      <a class="dcard" href="<?php stmina_link( 'church-gallery' ); ?>" data-rise><img class="grade" src="<?php stmina_media( 'worship/youth-liturgy.jpeg' ); ?>" alt="" loading="lazy"><span class="go"><svg class="icon"><use href="#i-nw"/></svg></span><h3>صور الكنيسة</h3><p>صور من القداسات والخدمات والمؤتمرات والرحلات.</p></a>
    </div>
  </div>
</section>

<!-- ===== أقوال الآباء ===== -->
<section class="has-bg sec deep pool" aria-labelledby="t-quotes">
  <div class="sec-bg" aria-hidden="true"><img src="<?php stmina_media( 'church/bg-praying-light.jpg' ); ?>" alt="" loading="lazy"></div>
  <div class="wrap">
    <div class="head-row">
      <div>
        <span class="eyebrow" data-rise>كلمة روحية</span>
        <h2 class="title" id="t-quotes" data-split>من أقوال <b>آباء الكنيسة</b></h2>
      </div>
      <div class="rail-ctl" data-rise>
        <button data-dir="prev" aria-label="السابق"><svg class="icon"><use href="#i-right"/></svg></button>
        <button data-dir="next" aria-label="التالي"><svg class="icon"><use href="#i-left"/></svg></button>
      </div>
    </div>
    <div class="qslider" id="qslider" aria-roledescription="carousel" aria-label="أقوال الآباء" data-rise>
      <button class="qs" data-lb data-cap="القمص كيرلس روماني"><span class="qs-face" style="background-image:url('<?php stmina_media( 'priests/quote-kyrillos-3.jpg' ); ?>');background-size:336.7% auto;background-position:13.1% 77.6%" aria-hidden="true"></span><span class="qs-q long">«قف أمام الله وصلِّ قائلًا: اضبطني يا ضابط الكل، ولا تتركني لذاتي لئلا أهلك. يا رب إن كنت أنا عاجزًا عن ضبط نفسي فلا تتركني، وإن لزم الأمر أن تقودني بالعصا أو التأديب فلا تسمح أن أحيد عن طريقك. خلّصني بكل وسيلة.»</span><span class="qs-by">القمص كيرلس روماني</span><img class="qs-src" src="<?php stmina_media( 'priests/quote-kyrillos-3.jpg' ); ?>" alt="ملصق قول القمص كيرلس روماني" loading="lazy"></button>
      <button class="qs" data-lb data-cap="القس أرسانيوس عزت"><span class="qs-face" style="background-image:url('<?php stmina_media( 'priests/quote-arsanios-1.jpg' ); ?>');background-size:341.3% auto;background-position:24.9% 25.9%" aria-hidden="true"></span><span class="qs-q">«خادم مش مصلي.. غصنه ناشف. خادم مش صايم ولا بيقدّم ذبيحة حب.. غصنه ناشف. خادم مش بيقرأ في الكتاب المقدس بروح الشبع.. غصنه ناشف.»</span><span class="qs-by">القس أرسانيوس عزت</span><img class="qs-src" src="<?php stmina_media( 'priests/quote-arsanios-1.jpg' ); ?>" alt="ملصق قول القس أرسانيوس عزت" loading="lazy"></button>
      <button class="qs" data-lb data-cap="القس فام عبد المسيح"><span class="qs-face" style="background-image:url('<?php stmina_media( 'priests/quote-fam.jpg' ); ?>');background-size:408.7% auto;background-position:0.4% 63.6%" aria-hidden="true"></span><span class="qs-q">«الوصية مش قيد.. الوصية أمان لينا. ربنا ما بيمنعشي الفرح، ربنا بيحمي الفرح من إنه يتحول إلى وجع.»</span><span class="qs-by">القس فام عبد المسيح</span><img class="qs-src" src="<?php stmina_media( 'priests/quote-fam.jpg' ); ?>" alt="ملصق قول القس فام عبد المسيح" loading="lazy"></button>
      <button class="qs" data-lb data-cap="القمص كيرلس روماني"><span class="qs-face" style="background-image:url('<?php stmina_media( 'priests/quote-kyrillos-1.jpg' ); ?>');background-size:348.1% auto;background-position:93.6% 55.0%" aria-hidden="true"></span><span class="qs-q">«كل مؤمن في المسيح يستطيع أن يغادر القبر وبستان الأشواك، ويقول: مكاني ليس في القبور، بل في المسيح.»</span><span class="qs-by">القمص كيرلس روماني</span><img class="qs-src" src="<?php stmina_media( 'priests/quote-kyrillos-1.jpg' ); ?>" alt="ملصق قول القمص كيرلس روماني" loading="lazy"></button>
      <button class="qs" data-lb data-cap="القس أرسانيوس عزت"><span class="qs-face" style="background-image:url('<?php stmina_media( 'priests/quote-arsanios-2.jpg' ); ?>');background-size:447.6% auto;background-position:8.9% 37.5%" aria-hidden="true"></span><span class="qs-q">«المخدوع من سلطان هذا العالم يجري ولا يأخذ.»</span><span class="qs-by">القس أرسانيوس عزت</span><img class="qs-src" src="<?php stmina_media( 'priests/quote-arsanios-2.jpg' ); ?>" alt="ملصق قول القس أرسانيوس عزت" loading="lazy"></button>
      <button class="qs" data-lb data-cap="القمص كيرلس روماني"><span class="qs-face" style="background-image:url('<?php stmina_media( 'priests/quote-kyrillos-2.jpg' ); ?>');background-size:408.7% auto;background-position:88.3% 68.6%" aria-hidden="true"></span><span class="qs-q">«احترس من الشفقة التي تضيّع أبديتك، فحتى المشاعر النبيلة إن لم توزن بميزان الإرادة الإلهية قد تقودنا إلى الخطأ والهلاك.»</span><span class="qs-by">القمص كيرلس روماني</span><img class="qs-src" src="<?php stmina_media( 'priests/quote-kyrillos-2.jpg' ); ?>" alt="ملصق قول القمص كيرلس روماني" loading="lazy"></button>
      <div class="qs-dots" role="tablist" aria-label="اختر القول"></div>
    </div>
  </div>
</section>

<!-- ===== الخدمات ===== -->
<section class="sec pool-l" id="services" aria-labelledby="t-svc">
  <div class="wrap">
    <div class="head-row">
      <div>
        <span class="eyebrow" data-rise>خدماتنا</span>
        <h2 class="title" id="t-svc" data-split>خدمة <b>لكل عمر</b></h2>
        <p class="lead" data-rise>خدمات الكنيسة في ست مجموعات. اختر ما يناسبك وتواصل مع المسؤول مباشرة.</p>
      </div>
      <a class="pill" href="<?php stmina_link( 'services' ); ?>" data-rise>كل الخدمات <span class="dot"><svg class="icon"><use href="#i-left"/></svg></span></a>
    </div>
    <div class="bubbles" role="group" aria-label="اختر مجموعة الخدمات">
      <span class="bub grad" aria-hidden="true" data-bub><span class="bi"></span></span>
      <button class="bub text" data-f="sunday" aria-pressed="false" data-bub><span class="bi"><span><b>مدارس الأحد</b><small>3 مراحل</small></span></span></button>
      <button class="bub photo" data-f="sunday" aria-pressed="false" aria-label="مدارس الأحد" data-bub><span class="bi"><img class="grade" src="<?php stmina_media( 'services/sunday-school/primary-conference-2026-1.jpg' ); ?>" alt="" loading="lazy"></span></button>
      <button class="bub text" data-f="meet" aria-pressed="false" data-bub><span class="bi"><span><b>الاجتماعات</b><small>5 اجتماعات</small></span></span></button>
      <button class="bub photo" data-f="meet" aria-pressed="false" aria-label="الاجتماعات" data-bub><span class="bi"><img class="grade" src="<?php stmina_media( 'services/youth/youth-meeting-2026.jpeg' ); ?>" alt="" loading="lazy"></span></button>
      <span class="bub grad" aria-hidden="true" data-bub><span class="bi"></span></span>
      <button class="bub photo" data-f="servants" aria-pressed="false" aria-label="الخدام" data-bub><span class="bi"><img class="grade" src="<?php stmina_media( 'services/counseling/counseling-course-2026-1.jpeg' ); ?>" alt="" loading="lazy"></span></button>
      <button class="bub text" data-f="servants" aria-pressed="false" data-bub><span class="bi"><span><b>الخدام</b><small>اجتماعان</small></span></span></button>
      <span class="bub grad" aria-hidden="true" data-bub><span class="bi"></span></span>
      <button class="bub photo" data-f="teams" aria-pressed="false" aria-label="الفرق والأنشطة" data-bub><span class="bi"><img class="grade" src="<?php stmina_media( 'services/theater/theater-ismuh-yasou.jpg' ); ?>" alt="" loading="lazy"></span></button>
      <button class="bub text" data-f="teams" aria-pressed="false" data-bub><span class="bi"><span><b>الفرق والأنشطة</b><small>4 فرق</small></span></span></button>
      <span class="bub grad" aria-hidden="true" data-bub><span class="bi"></span></span>
      <span class="bub grad" aria-hidden="true" data-bub><span class="bi"></span></span>
      <button class="bub text" data-f="nursery" aria-pressed="false" data-bub><span class="bi"><span><b>الحضانة</b><small>للأطفال الصغار</small></span></span></button>
      <button class="bub photo paper" data-f="nursery" aria-pressed="false" aria-label="الحضانة" data-bub><span class="bi"><img src="<?php stmina_media( 'services/sunday-school/sunday-school-art.jpg' ); ?>" alt="" loading="lazy"></span></button>
      <button class="bub text" data-f="family" aria-pressed="false" data-bub><span class="bi"><span><b>الأسرة والمجتمع</b><small>خدمتان جديدتان</small></span></span></button>
      <button class="bub photo" data-f="family" aria-pressed="false" aria-label="الأسرة والمجتمع" data-bub><span class="bi"><img class="grade" src="<?php stmina_media( 'services/counseling/counseling-course-2026-2.jpeg' ); ?>" alt="" loading="lazy"></span></button>
      <span class="bub grad" aria-hidden="true" data-bub><span class="bi"></span></span>
    </div>
    <div class="svc-reset" data-rise><span>اضغط على دائرة لعرض خدماتها، أو</span><button class="chip" data-f="all" aria-pressed="true">اعرض كل الخدمات</button></div>
    <div class="svc-grid">
      <?php
      // الخدمات الحقيقية من الـ plugin، ولو مش متفعّل القسم بيفضل فاضي من غير ما الصفحة تقع
      if ( function_exists( "stmina_services" ) ) {
        foreach ( stmina_services() as $service ) {
          get_template_part( "template-parts/service-card", null, array( "post" => $service ) );
        }
      }
      ?>
    </div>
  </div>
</section>

<!-- ===== من حياة الكنيسة ===== -->
<section class="sec deep" id="gallery" aria-labelledby="t-gal">
  <div class="wrap gal">
    <div class="gal-text">
      <span class="eyebrow" data-rise>صور الكنيسة</span>
      <h2 class="title" id="t-gal" data-split>من <b>حياة الكنيسة</b></h2>
      <p class="lead" data-rise>لحظات من القداسات والمؤتمرات والرحلات والأنشطة.</p>
      <p class="lead" data-rise>كل صورة حكاية من حياة كنيستنا: صلاة وخدمة وفرح مع بعض، لكل الأعمار.</p>
      <button class="ring-btn" id="allPhotos" data-rise>كل الصور<svg class="icon"><use href="#i-nw"/></svg><small>16 صورة</small></button>
    </div>
    <div class="gcl" aria-label="صور من حياة الكنيسة">
      <button class="g big" style="--gl:15;--gt:17;--gs:38;--ml:13;--mt:8.5;--ms:54" data-lb data-cap="قداس الشباب" data-bub><span class="bi"><img class="grade" src="<?php stmina_media( 'worship/youth-liturgy.jpeg' ); ?>" alt="الشباب مع أب الكاهن داخل الكنيسة" loading="lazy"><span class="cap">قداس الشباب<small>العبادة</small></span></span></button>
      <button class="g big" style="--gl:57;--gt:53;--gs:30;--ml:39;--mt:64;--ms:48" data-lb data-cap="الآباء مع الشمامسة أمام الهيكل" data-bub><span class="bi"><img class="grade" src="<?php stmina_media( 'worship/deacons.jpeg' ); ?>" alt="الآباء مع الشمامسة أمام الهيكل" loading="lazy"><span class="cap">الآباء مع الشمامسة<small>العبادة</small></span></span></button>
      <button class="g" style="--gl:0.97;--gt:5.14;--gs:17;--ml:51.86;--mt:34.22;--ms:30" data-lb data-cap="مسابقة كرة القدم لمرحلة ابتدائي" data-bub><span class="bi"><img class="grade" src="<?php stmina_media( 'services/sunday-school/primary-football-1.jpg' ); ?>" alt="تسليم ميداليات المسابقة" loading="lazy"></span></button>
      <button class="g" style="--gl:25.19;--gt:83.13;--gs:15;--ml:73.83;--mt:16.06;--ms:25" data-lb data-cap="قداس ليلي للشباب" data-bub><span class="bi"><img class="grade" src="<?php stmina_media( 'worship/youth-night-liturgy-2.jpeg' ); ?>" alt="الشمامسة في الهيكل" loading="lazy"></span></button>
      <button class="g" style="--gl:6.41;--gt:78.51;--gs:14;--ml:0.67;--mt:34.35;--ms:22" data-lb data-cap="رحلة أديرة المنيا 2026" data-bub><span class="bi"><img class="grade" src="<?php stmina_media( 'services/youth/minya-monasteries-trip-2026-1.jpeg' ); ?>" alt="الشباب في رحلة أديرة المنيا" loading="lazy"></span></button>
      <button class="g" style="--gl:72;--gt:3.48;--gs:13;--ml:60.05;--mt:0.2;--ms:20" data-lb data-cap="مؤتمر عنبر 11 للشباب 2026" data-bub><span class="bi"><img class="grade" src="<?php stmina_media( 'services/youth/youth-conference-anbar11-2026.jpeg' ); ?>" alt="مؤتمر الشباب" loading="lazy"></span></button>
      <button class="g" style="--gl:37.43;--gt:1.78;--gs:12;--ml:4.15;--mt:2.5;--ms:18" data-lb data-cap="عرض اسمه يسوع" data-bub><span class="bi"><img class="grade" src="<?php stmina_media( 'services/theater/theater-ismuh-yasou.jpg' ); ?>" alt="عرض مسرحي داخل الكنيسة" loading="lazy"></span></button>
      <button class="g" style="--gl:80.35;--gt:28.37;--gs:12;--ml:82.55;--mt:5.83;--ms:16" data-lb data-cap="مؤتمر ابتدائي 2026" data-bub><span class="bi"><img class="grade" src="<?php stmina_media( 'services/sunday-school/primary-conference-2026-2.jpg' ); ?>" alt="مؤتمر ابتدائي" loading="lazy"></span></button>
      <button class="g" style="--gl:16.94;--gt:56.27;--gs:11;--ml:33.64;--mt:39.26;--ms:14" data-lb data-cap="زيارة دير ضمن رحلة أديرة المنيا 2026" data-bub><span class="bi"><img class="grade" src="<?php stmina_media( 'services/youth/minya-monasteries-trip-2026-2.jpeg' ); ?>" alt="الشباب داخل كنيسة أثرية" loading="lazy"></span></button>
      <button class="g" style="--gl:86.44;--gt:75.42;--gs:10;--ml:6.68;--mt:54.32;--ms:28" data-lb data-cap="أمسية العبور 2025" data-bub><span class="bi"><img class="grade" src="<?php stmina_media( 'services/youth/obour-evening-2025-1.jpeg' ); ?>" alt="أمسية العبور" loading="lazy"></span></button>
      <button class="g" style="--gl:53.48;--gt:86.75;--gs:10;--ml:6.55;--mt:87.48;--ms:24" data-lb data-cap="فريق المسرح" data-bub><span class="bi"><img class="grade" src="<?php stmina_media( 'services/theater/theater-team.jpg' ); ?>" alt="صورة جماعية لفريق المسرح" loading="lazy"></span></button>
      <button class="g" style="--gl:88.04;--gt:49.92;--gs:9;--ml:70.75;--mt:87.69;--ms:21" data-lb data-cap="أبطال مسابقة كرة القدم" data-bub><span class="bi"><img class="grade" src="<?php stmina_media( 'services/sunday-school/primary-football-2.jpg' ); ?>" alt="الأطفال بالميداليات" loading="lazy"></span></button>
      <button class="g" style="--gl:68.74;--gt:88.9;--gs:9;--ml:62.82;--mt:52.19;--ms:19" data-lb data-cap="مؤتمر ثانوي 2015" data-bub><span class="bi"><img class="grade" src="<?php stmina_media( 'services/sunday-school/secondary-conference-2015.jpeg' ); ?>" alt="مؤتمر ثانوي" loading="lazy"></span></button>
      <button class="g" style="--gl:39.56;--gt:59.1;--gs:8;--ml:8.26;--mt:71.52;--ms:17" data-lb data-cap="كورال ترينتي" data-bub><span class="bi"><img class="grade" src="<?php stmina_media( 'services/choir/trinity-choir.jpg' ); ?>" alt="كورال ترينتي على المسرح" loading="lazy"></span></button>
      <button class="g" style="--gl:58.02;--gt:1.32;--gs:8;--ml:43.2;--mt:52.67;--ms:15" data-lb data-cap="كلمة الأب في مؤتمر ابتدائي 2026" data-bub><span class="bi"><img class="grade" src="<?php stmina_media( 'services/sunday-school/primary-conference-2026-3.jpg' ); ?>" alt="أب كاهن يتحدث مع الأطفال" loading="lazy"></span></button>
      <button class="g" style="--gl:47.14;--gt:52.72;--gs:7;--ml:24.23;--mt:80.86;--ms:13" data-lb data-cap="أمسية العبور 2025" data-bub><span class="bi"><img class="grade" src="<?php stmina_media( 'services/youth/obour-evening-2025-2.jpeg' ); ?>" alt="صورة جماعية ليلية" loading="lazy"></span></button>
    </div>
  </div>
</section>

<!-- ===== آية اليوم: ضوء الشموع ===== -->
<section class="has-bg candle-sec" aria-label="كلمة للتأمل">
  <div class="sec-bg" aria-hidden="true"><img src="<?php stmina_media( 'church/bg-praying-light.jpg' ); ?>" alt="" loading="lazy"></div>
  <span class="flame-glow" aria-hidden="true"></span>
  <div class="wrap">
    <div class="candle" aria-hidden="true" data-rise></div>
    <span class="eyebrow" data-rise>كلمة للتأمل</span>
    <blockquote data-split><?php echo esc_html( stmina_home( 'verse_text' ) ); ?></blockquote>
    <cite data-rise>(<?php echo esc_html( stmina_home( 'verse_ref' ) ); ?>)</cite>
  </div>
</section>

<!-- ===== المناسبة القادمة ===== -->
<section class="sec pool" aria-labelledby="t-event">
  <div class="wrap event">
    <div class="event-media" data-clip><img class="grade" data-parallax src="<?php stmina_media( 'worship/youth-night-liturgy-2.jpeg' ); ?>" alt="الشمامسة في الهيكل أمام أيقونة المسيح"></div>
    <div>
      <span class="eyebrow" data-rise>المناسبة القادمة</span>
      <h2 class="title" id="t-event" data-split>عيد <b>الميلاد المجيد</b></h2>
      <div class="countdown" id="countdown" data-date="2027-01-06T23:00:00+02:00" data-rise>
        <div><b data-u="d">00</b><small>يوم</small></div><div><b data-u="h">00</b><small>ساعة</small></div><div><b data-u="m">00</b><small>دقيقة</small></div><div><b data-u="s">00</b><small>ثانية</small></div>
      </div>
      <ul class="ev-meta" data-rise>
        <li><svg class="icon"><use href="#i-calendar"/></svg>ليلة 6 يناير 2027</li>
        <li><svg class="icon"><use href="#i-clock"/></svg>موعد القداس يُعلن قريبًا</li>
        <li><svg class="icon"><use href="#i-pin"/></svg>الكنيسة الكبيرة</li>
      </ul>
      <div data-rise><a class="pill" href="<?php stmina_link( 'season' ); ?>">تفاصيل المناسبة <span class="dot"><svg class="icon"><use href="#i-left"/></svg></span></a></div>
    </div>
  </div>
</section>

<!-- ===== أحدث العظات ===== -->
<section class="sec deep" id="sermons" aria-labelledby="t-serm">
  <div class="wrap">
    <div class="head-row">
      <div>
        <span class="eyebrow" data-rise>المكتبة</span>
        <h2 class="title" id="t-serm" data-split>أحدث <b>العظات</b></h2>
      </div>
      <a class="link" href="<?php stmina_link( 'library' ); ?>" data-rise>كل العظات <svg class="icon"><use href="#i-nw"/></svg></a>
    </div>
    <div class="cards">
      <a class="sermon" href="<?php stmina_link( 'sermon' ); ?>" data-rise><div class="top"><span class="mi"><svg class="icon"><use href="#i-headphones"/></svg></span><span class="kind">عظة صوتية</span></div><h3>عنوان العظة يظهر هنا</h3><ul><li>اسم المتحدث</li><li>الموضوع · التاريخ</li></ul><div class="foot"><span>استمع الآن</span><svg class="icon"><use href="#i-nw"/></svg></div></a>
      <a class="sermon" href="<?php stmina_link( 'sermon' ); ?>" data-rise><div class="top"><span class="mi"><svg class="icon" style="fill:currentColor"><use href="#i-play"/></svg></span><span class="kind">فيديو</span></div><h3>عنوان العظة يظهر هنا</h3><ul><li>اسم المتحدث</li><li>الموضوع · التاريخ</li></ul><div class="foot"><span>شاهد الآن</span><svg class="icon"><use href="#i-nw"/></svg></div></a>
      <a class="sermon" href="<?php stmina_link( 'sermon' ); ?>" data-rise><div class="top"><span class="mi"><svg class="icon"><use href="#i-file"/></svg></span><span class="kind">ملف PDF</span></div><h3>عنوان العظة يظهر هنا</h3><ul><li>اسم المتحدث</li><li>الموضوع · التاريخ</li></ul><div class="foot"><span>اقرأ الآن</span><svg class="icon"><use href="#i-nw"/></svg></div></a>
    </div>
  </div>
</section>

<!-- ===== الأخبار ===== -->
<section class="has-bg sec pool-l" id="news" aria-labelledby="t-news">
  <div class="sec-bg" aria-hidden="true"><img src="<?php stmina_media( 'church/bg-window-rays.jpg' ); ?>" alt="" loading="lazy"></div>
  <div class="wrap">
    <div class="head-row">
      <div>
        <span class="eyebrow" data-rise>الأخبار</span>
        <h2 class="title" id="t-news" data-split>آخر <b>الأخبار والإعلانات</b></h2>
      </div>
      <div class="tabs" role="group" aria-label="تصفية الأخبار" data-rise>
        <button class="chip" data-n="all" aria-pressed="true">الكل</button>
        <button class="chip" data-n="news" aria-pressed="false">أخبار</button>
        <button class="chip" data-n="ann" aria-pressed="false">إعلانات</button>
      </div>
    </div>
    <div class="nbubs">
      <a class="nbub top" data-t="ann" href="<?php stmina_link( 'news-item' ); ?>" data-bub><span class="bi"><img src="<?php stmina_media( 'events/annual-party-umm-lilbay.jpg' ); ?>" alt="ملصق الحفل السنوي أم للبيع" loading="lazy"><span class="nb-txt"><small>الجمعة 28 أغسطس</small><b>الحفل السنوي<br>"أم للبيع"</b></span></span></a>
      <a class="nbub" data-t="ann" href="<?php stmina_link( 'news-item' ); ?>" data-bub><span class="bi"><img src="<?php stmina_media( 'events/virgin-mary-revival-2026.jpg' ); ?>" alt="برنامج نهضة السيدة العذراء 2026" loading="lazy"><span class="nb-txt"><small>7 – 22 أغسطس</small><b>نهضة السيدة<br>العذراء مريم</b></span></span></a>
      <a class="nbub" data-t="news" href="<?php stmina_link( 'news-item' ); ?>" data-bub><span class="bi"><img class="grade" src="<?php stmina_media( 'services/youth/minya-monasteries-trip-2026-1.jpeg' ); ?>" alt="الشباب في رحلة أديرة المنيا" loading="lazy"><span class="nb-txt"><small>الشباب · 2026</small><b>رحلة أديرة<br>المنيا</b></span></span></a>
    </div>
    <div class="cards">
      <a class="news" data-t="ann" href="<?php stmina_link( 'news-item' ); ?>" data-rise><div class="im top"><img src="<?php stmina_media( 'services/counseling/counseling-course-poster.jpg' ); ?>" alt="إعلان كورس المشورة" loading="lazy"></div><div class="bd"><div class="k"><span>إعلان</span><span>يبدأ 14 يونيو 2026</span></div><h3>كورس المشورة للمقبلين على الزواج</h3><p>شهادة اجتياز ضمن مسوغات الزواج، ويقبل الشباب الجامعي والطلبة.</p></div></a>
      <a class="news" data-t="news" href="<?php stmina_link( 'news-item' ); ?>" data-rise><div class="im"><img class="grade" src="<?php stmina_media( 'services/sunday-school/primary-football-1.jpg' ); ?>" alt="تسليم ميداليات المسابقة" loading="lazy"></div><div class="bd"><div class="k"><span>خبر</span><span>2026</span></div><h3>تسليم ميداليات مسابقة كرة القدم لمرحلة ابتدائي</h3><p>بحضور آباء الكنيسة والخدام وأولياء الأمور.</p></div></a>
      <a class="news" data-t="ann" href="<?php stmina_link( 'news-item' ); ?>" data-rise><div class="im top"><img src="<?php stmina_media( 'events/pope-kyrillos-revival.jpg' ); ?>" alt="إعلان نهضة البابا كيرلس السادس" loading="lazy"></div><div class="bd"><div class="k"><span>إعلان</span><span>6 – 9 مارس</span></div><h3>نهضة البابا كيرلس السادس</h3><p>زفة وتطييب وكلمة روحية 6–8 م، والقداس الإثنين 9 مارس على مذبح البابا كيرلس.</p></div></a>
    </div>
  </div>
</section>

<?php
get_footer();
