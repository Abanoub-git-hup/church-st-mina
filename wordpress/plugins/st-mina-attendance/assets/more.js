// شاشة "المزيد": اسم الخادم، والألواح، وتغيير كلمة السر. الخروج في attend-app.js (data-logout).
(() => {
  const { C, $, $$, api, toast } = window.Attend;

  $('#meName').textContent = C.user;

  // بعد تغيير كلمة السر الصفحة بتتحمّل تاني (علشان رمز nonce الجديد)، فالرسالة بتستنى هنا
  try {
    if (sessionStorage.getItem('stmina_pw_done')) {
      sessionStorage.removeItem('stmina_pw_done');
      setTimeout(() => toast('اتغيّرت كلمة السر.'), 300);
    }
  } catch (e) {}

  // الألواح: واحد مفتوح في المرة
  $$('button.pane-h').forEach(h => h.addEventListener('click', () => {
    const pane = h.closest('.pane'), open = !pane.classList.contains('open');
    $$('.pane.open').forEach(p => { p.classList.remove('open'); $('.pane-h', p).setAttribute('aria-expanded', 'false'); });
    pane.classList.toggle('open', open);
    h.setAttribute('aria-expanded', open);
  }));

  // تغيير كلمة السر: نفس الشروط هنا وفي السيرفر (8 على الأقل، والتانية زي الأولى)
  const form = $('#pwForm');
  form.addEventListener('submit', async e => {
    e.preventDefault();
    const cur = $('#pw0').value, a = $('#pw1').value, b = $('#pw2').value;
    $('#pw0Err').textContent = 'اكتب كلمة السر الحالية.';
    $('#fpw0').classList.toggle('bad', !cur);
    $('#fpw1').classList.toggle('bad', !!cur && a.length < 8);
    $('#fpw2').classList.toggle('bad', !!cur && a.length >= 8 && a !== b);
    if (!cur) return $('#pw0').focus();
    if (a.length < 8) return $('#pw1').focus();
    if (a !== b) return $('#pw2').focus();

    const btn = $('button[type=submit]', form);
    btn.disabled = true;
    try {
      await api('password', { method: 'POST', body: { current: cur, password: a } });
      try { sessionStorage.setItem('stmina_pw_done', '1'); } catch (err) {}
      location.reload();
    } catch (err) {
      btn.disabled = false;
      if (err.fields.current) {
        $('#pw0Err').textContent = err.fields.current;
        $('#fpw0').classList.add('bad');
        $('#pw0').select();
      } else if (err.fields.password) {
        $('#fpw1').classList.add('bad');
        $('#pw1').focus();
      } else {
        toast(err.message);
      }
    }
  });
})();
