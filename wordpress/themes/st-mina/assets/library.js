// خاص بالمكتبة: فلترة العظات بالمتحدث والموضوع والنوع مع بعض، وتشغيل الترانيم
(() => {
  const { $, $$, animate } = window.Site;

  // ---------- الفلاتر: كل صف ليه مفتاح (sp أو t أو k) بيقارن بـ data-sp أو data-t أو data-k في الكارت ----------
  const state = {}, cards = $$('#panel-sermons .sermon'), empty = $('#panel-sermons .empty');
  const apply = () => {
    let n = 0;
    cards.forEach(c => {
      const ok = Object.keys(state).every(key => state[key] === 'all' || c.dataset[key] === state[key]);
      c.classList.toggle('hide', !ok);
      if (ok) { n++; if (animate) gsap.fromTo(c, { opacity: 0, y: 16 }, { opacity: 1, y: 0, duration: .5, ease: 'power3.out', overwrite: true }); }
    });
    if (empty) empty.classList.toggle('show', n === 0);
    if (animate) ScrollTrigger.refresh();
  };
  $$('.frow').forEach(row => {
    const key = row.dataset.key;
    state[key] = 'all';
    $$('.chip', row).forEach(b => b.addEventListener('click', () => {
      state[key] = b.dataset.v;
      $$('.chip', row).forEach(x => x.setAttribute('aria-pressed', x === b));
      apply();
    }));
  });

  // ---------- الترانيم: ترنيمة واحدة بتشتغل في المرة، والشريط بيمشي مع الصوت ----------
  let current = null;
  const stop = btn => { btn.setAttribute('aria-pressed', 'false'); $('audio', btn.closest('.hymn')).pause(); };
  $$('.hymn').forEach(li => {
    const btn = $('.h-play', li), audio = $('audio', li), bar = $('.h-bar span', li), dur = $('.h-dur', li);
    if (!btn || !audio) return;
    btn.addEventListener('click', () => {
      if (current && current !== btn) stop(current);
      const on = btn.getAttribute('aria-pressed') !== 'true';
      btn.setAttribute('aria-pressed', on);
      if (on) { audio.play(); current = btn; dispatchEvent(new Event('site:media')); } else { audio.pause(); current = null; }
    });
    audio.addEventListener('timeupdate', () => { if (audio.duration) bar.style.width = (audio.currentTime / audio.duration * 100) + '%'; });
    audio.addEventListener('loadedmetadata', () => {
      if (dur && !dur.textContent.trim()) dur.textContent = Math.floor(audio.duration / 60) + ':' + String(Math.floor(audio.duration % 60)).padStart(2, '0');
    });
    audio.addEventListener('ended', () => { btn.setAttribute('aria-pressed', 'false'); bar.style.width = '0'; current = null; });
  });
})();
