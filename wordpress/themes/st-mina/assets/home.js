// خاص بالرئيسية: القداس القادم، وزر "كل الصور"، ومقدمة الواجهة الكبيرة
(() => {
  const { $, animate } = window.Site;

  // ---------- القداس القادم من الجدول (0 = الأحد) ----------
  // المواعيد من جدول WordPress (functions.php بيبعتها)، والقديمة احتياطي لو مش موجودة
  const masses = window.STMINA_MASSES || [
    { d: 0, h: 7, t: "7:00 – 10:00 صباحًا" }
  ];
  const days = ["الأحد","الإثنين","الثلاثاء","الأربعاء","الخميس","الجمعة","السبت"];
  const now = new Date(), key = now.getDay() * 24 + now.getHours() + now.getMinutes() / 60;
  const next = masses.find(m => m.d * 24 + m.h > key) || masses[0];
  const ahead = (next.d - now.getDay() + 7) % 7;
  $("#nextMass").textContent = (ahead === 0 ? "اليوم · " : ahead === 1 ? "غدًا · " : "") + days[next.d];
  $("#nextMassTime").textContent = next.t;

  $("#allPhotos").addEventListener("click", () => $(".gcl .g").click());

  // ---------- فقاعة المقولة: بتلف على أقوال الآباء من قسمها كل 6 ثواني ----------
  // النص القصير في data-short على كل قول، والاسم في data-cap. ولو الصفحة مستخبية بتقف
  const hq = $("#heroQuote"), hb = $("#heroQuoteBy");
  const sayings = [...document.querySelectorAll(".qs[data-short]")].map(q => ({ t: "«" + q.dataset.short + "»", by: q.dataset.cap }));
  if (hq && sayings.length) {
    let i = 0, timer = null;
    const put = () => { hq.textContent = sayings[i].t; hb.textContent = sayings[i].by; };
    const next = () => {
      hq.classList.add("fade"); hb.classList.add("fade");
      setTimeout(() => { i = (i + 1) % sayings.length; put(); hq.classList.remove("fade"); hb.classList.remove("fade"); }, 500);
    };
    const run = () => { clearInterval(timer); timer = document.hidden ? null : setInterval(next, 6000); };
    put(); run();
    document.addEventListener("visibilitychange", run);
  }

  if (!animate) return;

  // الواجهة: الضوء يدخل ثم يظهر النص
  gsap.timeline({ defaults: { ease: "power3.out" } })
    .to(".hero-bg img", { scale: 1, duration: 2.8, ease: "power2.out" }, 0)
    .to(".rays", { opacity: 1, duration: 2.4 }, .2)
    .to("#dust", { opacity: 1, duration: 2 }, .8)
    .to(".hero h1 .w > span", { y: 0, duration: 1.3, stagger: .1 }, .4)
    .to(".hero [data-intro]", { opacity: 1, duration: 1.1, stagger: .07, startAt: { y: 24 }, y: 0 }, .9)
    .to("[data-tile]", { opacity: 1, duration: 1.3, stagger: .14, startAt: { scale: .7 }, scale: 1, ease: "back.out(1.4)" }, 1.1);

  // زووم إن وآوت بطيء ومستمر لصورة الواجهة
  gsap.to(".hero-bg img", { scale: 1.1, duration: 16, ease: "sine.inOut", yoyo: true, repeat: -1, delay: 2.8 });

  // تزحزح بطيء لصورة الواجهة مع التمرير
  gsap.to(".hero-bg img", { yPercent: 8, ease: "none", scrollTrigger: { trigger: ".hero", start: "top top", end: "bottom top", scrub: true } });
})();
