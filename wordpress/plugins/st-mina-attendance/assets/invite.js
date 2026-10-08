// شاشة قبول دعوة الخادم (/attend/invite/<الكود>/): بيعمل كلمة السر بنفسه، وبعدها بيدخل على طول.
// صاحب الدعوة من PHP في STMINA_ATT.invite، أو null لو الدعوة مش شغالة (عدّت 3 أيام أو اتستخدمت).
(() => {
  const { C, $, api } = window.Attend;
  const body = document.body;

  if (!C.invite) {
    $('#ok').hidden = true;
    $('#bad').hidden = false;
    return;
  }
  $('#hi').textContent = `أهلًا يا ${C.invite.name.replace(/^أ\.\s*/, '').split(' ')[0]}`;

  const submit = $('.submit'), err = $('.error span');
  const show = state => {
    body.dataset.state = state;
    submit.disabled = state === 'offline';
    submit.setAttribute('aria-busy', state === 'loading');
  };
  const net = () => show(navigator.onLine ? 'normal' : 'offline');
  addEventListener('online', net);
  addEventListener('offline', net);
  net();

  $('#invite').addEventListener('submit', async e => {
    e.preventDefault();
    if (body.dataset.state === 'loading' || submit.disabled) return;
    const a = $('#pw1').value, b = $('#pw2').value;
    const msg = a.length < 8 ? '8 حروف أو أرقام على الأقل.' : a !== b ? 'كلمتين السر مش زي بعض.' : '';
    $('#f1').classList.toggle('bad', a.length < 8);
    $('#f2').classList.toggle('bad', a.length >= 8 && a !== b);
    if (msg) {
      err.textContent = msg;
      show('error');
      return (a.length < 8 ? $('#pw1') : $('#pw2')).focus();
    }
    show('loading');
    try {
      const r = await api('invite', { method: 'POST', body: { code: C.invite.code, password: a } });
      location.href = r.redirect;
    } catch (x) {
      // الدعوة بطلت وهو فاتح الصفحة (اتسحبت أو اتعملت دعوة أجدد)
      if (x.status === 404) {
        $('#ok').hidden = true;
        $('#bad').hidden = false;
        return;
      }
      err.textContent = x.message;
      show(x.status === 0 ? 'offline' : 'error');
    }
  });

  // إظهار كلمة السر وإخفاؤها
  const eye = $('.eye'), pw = $('#pw1');
  eye.addEventListener('click', () => {
    const on = eye.getAttribute('aria-pressed') !== 'true';
    eye.setAttribute('aria-pressed', on);
    eye.setAttribute('aria-label', on ? 'إخفاء كلمة السر' : 'إظهار كلمة السر');
    pw.type = on ? 'text' : 'password';
  });
})();
