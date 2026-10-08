// شاشة الدخول: بتبعت البريد أو الموبايل وكلمة السر لمسار /login، والسيرفر بيحط كوكي الدخول.
// الحالات بتتعرض بـ data-state على body زي التصميم: normal أو error أو loading أو offline.
(() => {
  const { $, api } = window.Attend;
  const body = document.body, submit = $('.submit'), err = $('.error span');

  const show = state => {
    body.dataset.state = state;
    submit.disabled = state === 'offline';
    submit.setAttribute('aria-busy', state === 'loading');
  };
  const net = () => show(navigator.onLine ? 'normal' : 'offline');
  addEventListener('online', net);
  addEventListener('offline', net);
  net();

  $('#login').addEventListener('submit', async e => {
    e.preventDefault();
    if (body.dataset.state === 'loading' || submit.disabled) return;
    const who = $('#who').value.trim(), password = $('#pw').value;
    if (!who || !password) {
      err.textContent = 'اكتب البريد أو رقم الموبايل وكلمة السر.';
      show('error');
      return (who ? $('#pw') : $('#who')).focus();
    }
    show('loading');
    try {
      const r = await api('login', { method: 'POST', body: { who, password, remember: $('[name=remember]').checked } });
      location.href = r.redirect;
    } catch (x) {
      err.textContent = x.message;
      show(x.status === 0 ? 'offline' : 'error');
      $('#pw').select();
    }
  });

  // إظهار كلمة السر وإخفاؤها
  const eye = $('.eye'), pw = $('#pw');
  eye.addEventListener('click', () => {
    const on = eye.getAttribute('aria-pressed') !== 'true';
    eye.setAttribute('aria-pressed', on);
    eye.setAttribute('aria-label', on ? 'إخفاء كلمة السر' : 'إظهار كلمة السر');
    pw.type = on ? 'text' : 'password';
  });

  // "نسيت كلمة السر؟"
  const fBtn = $('.forgot button'), fNote = $('#forgot-note');
  fBtn.addEventListener('click', () => {
    const open = fNote.hidden;
    fNote.hidden = !open;
    fBtn.setAttribute('aria-expanded', open);
  });
})();
