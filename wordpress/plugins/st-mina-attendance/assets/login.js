// شاشة الدخول بتبويبين:
// - "خادم": البريد أو الموبايل وكلمة السر لمسار /login، والسيرفر بيحط كوكي الدخول.
// - "مخدوم": الموبايل والرقم السري لمسار /member-login، والرد رابط صفحته (/me/<الكود>/). مفيش كوكي.
// الحالات بتتعرض بـ data-state على body زي التصميم: normal أو error أو loading أو offline،
// وبتأثّر على الفورم الظاهر بس (التاني مستخبي).
(() => {
  const { $, $$, api, digits } = window.Attend;
  const body = document.body;
  let panel = $('#p-servant');
  const submit = () => $('.submit', panel), err = () => $('.error span', panel);

  const show = state => {
    body.dataset.state = state;
    $$('.submit').forEach(b => {
      b.disabled = state === 'offline';
      b.setAttribute('aria-busy', state === 'loading');
    });
  };
  const net = () => show(navigator.onLine ? 'normal' : 'offline');
  addEventListener('online', net);
  addEventListener('offline', net);
  net();

  // ---------- خادم أو مخدوم ----------
  const TXT = {
    servant: ['دخول الخادم', 'ادخل بحسابك علشان تفتح الجلسات وتسجّل الحضور.'],
    member: ['دخول المخدوم', 'افتح صفحتك وكارتك بموبايلك والرقم السري.'],
  };
  function pick(k, focus) {
    $$('.who [role=tab]').forEach(t => {
      const on = t.id === 't-' + k;
      t.setAttribute('aria-selected', on);
      t.tabIndex = on ? 0 : -1;
    });
    $('#p-servant').hidden = k !== 'servant';
    $('#p-member').hidden = k !== 'member';
    panel = $('#p-' + k);
    $('#loginH').textContent = TXT[k][0];
    $('#loginP').textContent = TXT[k][1];
    document.title = `${TXT[k][0]} | إعداد الخدام`;
    history.replaceState(null, '', k === 'member' ? '#member' : location.pathname);
    net();
    if (focus) $('input', panel).focus();
  }
  $$('.who [role=tab]').forEach(t => t.addEventListener('click', () => pick(t.id.slice(2), true)));
  // الأسهم بين التبويبين (نمط tablist)
  $('.who').addEventListener('keydown', e => {
    if (!['ArrowLeft', 'ArrowRight'].includes(e.key)) return;
    const next = $('.who [aria-selected=false]');
    pick(next.id.slice(2));
    next.focus();
  });
  // رابط /attend/login/#member بيفتح على المخدوم على طول
  if (location.hash === '#member') pick('member');

  // ---------- الخادم ----------
  $('#login').addEventListener('submit', async e => {
    e.preventDefault();
    if (body.dataset.state === 'loading' || submit().disabled) return;
    const who = $('#who').value.trim(), password = $('#pw').value;
    if (!who || !password) {
      err().textContent = 'اكتب البريد أو رقم الموبايل وكلمة السر.';
      show('error');
      return (who ? $('#pw') : $('#who')).focus();
    }
    show('loading');
    try {
      const r = await api('login', { method: 'POST', body: { who, password, remember: $('[name=remember]').checked } });
      location.href = r.redirect;
    } catch (x) {
      err().textContent = x.message;
      show(x.status === 0 ? 'offline' : 'error');
      $('#pw').select();
    }
  });

  // ---------- المخدوم ----------
  $('#memberLogin').addEventListener('submit', async e => {
    e.preventDefault();
    if (body.dataset.state === 'loading' || submit().disabled) return;
    const phone = digits($('#mPhone').value), pin = digits($('#mPin').value);
    if (!phone || pin.length !== 4) {
      err().textContent = !phone ? 'اكتب رقم الموبايل اللي الخادم سجّله.' : 'الرقم السري 4 أرقام.';
      show('error');
      return (phone ? $('#mPin') : $('#mPhone')).focus();
    }
    show('loading');
    try {
      const r = await api('member-login', { method: 'POST', body: { phone, pin } });
      location.href = r.redirect;
    } catch (x) {
      err().textContent = x.message;
      show(x.status === 0 ? 'offline' : 'error');
      $('#mPin').select();
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

  // "نسيت كلمة السر؟" و"نسيت الرقم السري؟"
  $$('.forgot button').forEach(fBtn => fBtn.addEventListener('click', () => {
    const fNote = document.getElementById(fBtn.getAttribute('aria-controls'));
    const open = fNote.hidden;
    fNote.hidden = !open;
    fBtn.setAttribute('aria-expanded', open);
  }));
})();
