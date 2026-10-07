// خاص بالأخبار: "عرض المزيد" بيظهّر باقي الكروت
(() => {
  const { $, $$, animate } = window.Site;
  const btn = $('#moreNews');
  if (!btn) return;
  btn.addEventListener('click', () => {
    $$('.news.later').forEach(c => { c.classList.remove('later'); if (animate) gsap.fromTo(c, { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: .6, ease: 'power3.out' }); });
    btn.parentElement.hidden = true;
    if (animate) ScrollTrigger.refresh();
  });
})();
