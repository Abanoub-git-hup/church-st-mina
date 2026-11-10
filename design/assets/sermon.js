// خاص بصفحة العظة: فيديو YouTube بيتحمّل لما الزائر يدوس، ومشغّل الصوت
(() => {
  const { $ } = window.Site;

  // ---------- الفيديو: نبدّل الصورة بـ iframe من youtube-nocookie (من غير كوكيز لحد التشغيل) ----------
  const player = $('.player[data-video]');
  if (player) {
    $('.ph', player).addEventListener('click', () => {
      const f = document.createElement('iframe');
      f.src = 'https://www.youtube-nocookie.com/embed/' + player.dataset.video + '?autoplay=1&rel=0';
      f.title = player.dataset.title || 'فيديو العظة';
      f.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
      f.allowFullscreen = true;
      player.replaceChildren(f);
      f.focus();
      dispatchEvent(new Event('site:media')); // بيوقّف ترنيمة الخلفية (site.js)
    });
  }

  // ---------- الصوت: تشغيل وإيقاف، والشريط بيمشي، والضغط على الشريط بينقل لمكان ----------
  const box = $('.audio-p');
  const audio = box && $('audio', box);
  if (audio) {
    const btn = $('.big', box), bar = $('.bar', box), fill = $('.bar span', box), cur = $('.cur', box), dur = $('.dur', box);
    const clock = s => Math.floor(s / 60) + ':' + String(Math.floor(s % 60)).padStart(2, '0');
    btn.addEventListener('click', () => (audio.paused ? audio.play() : audio.pause()));
    audio.addEventListener('play', () => { btn.setAttribute('aria-pressed', 'true'); dispatchEvent(new Event('site:media')); });
    audio.addEventListener('pause', () => btn.setAttribute('aria-pressed', 'false'));
    audio.addEventListener('loadedmetadata', () => { if (!dur.textContent.trim()) dur.textContent = clock(audio.duration); });
    audio.addEventListener('timeupdate', () => {
      cur.textContent = clock(audio.currentTime);
      if (audio.duration) fill.style.width = (audio.currentTime / audio.duration * 100) + '%';
    });
    // الصفحة من اليمين للشمال، فبداية الشريط على اليمين
    bar.addEventListener('click', e => {
      const r = bar.getBoundingClientRect();
      if (audio.duration) audio.currentTime = (r.right - e.clientX) / r.width * audio.duration;
    });
  }
})();
