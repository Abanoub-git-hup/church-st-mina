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

  // ---------- الخدام (المهمة 24) ----------
  // القايمة لكل الخدام. الإضافة وسحب الصلاحية لمدير الموقع بس، فلغيره الفورم والأزرار بيستخبوا
  // (والسيرفر بيرفض برضه: stmina_att_can_manage في servants.php)
  const { esc, fmtMobile, digits } = window.Attend;
  const first = n => n.replace(/^أ\.\s*/, '').split(' ')[0];
  const invite = (s, url) => `https://wa.me/2${s.phone}?text=${encodeURIComponent(`سلام ومحبة يا ${first(s.name)}\nاتضفت خادم في نظام حضور ${C.svc}. افتح الرابط ده واعمل كلمة السر بتاعتك (بيشتغل 3 أيام):\n${url}`)}`;
  let servants = [], manage = false;

  function renderSrv() {
    $('#srvList').innerHTML = servants.map(s => {
      const pill = s.me ? '<span class="pill-s">إنت</span>'
        : s.admin ? '<span class="pill-s">مدير الموقع</span>'
        : s.pending ? `<span class="pill-s wait">${s.expired ? 'الدعوة عدّت 3 أيام' : 'لسه ماقبلش الدعوة'}</span>` : '';
      const act = !manage || s.me || s.admin ? '<span></span>'
        : s.pending ? '<button class="textlink" type="button" data-reinvite>ابعت الدعوة تاني</button>'
        : '<button class="textlink" type="button" data-ask>اسحب الصلاحية</button>';
      return `<div class="srv" data-id="${s.id}">
        <span class="av" aria-hidden="true">${esc(first(s.name).slice(0, 2))}</span>
        <div><b>${esc(s.name)}${pill}</b><small dir="ltr" style="text-align:right">${s.phone ? fmtMobile(s.phone) : ''}</small></div>
        ${act}
        <div class="srv-confirm">
          هيتمنع <b>${esc(s.name)}</b> من الدخول فورًا. السجلات اللي سجّلها بتفضل محفوظة باسمه.
          <div class="row2"><button class="btn-del" type="button" data-revoke>اسحب الصلاحية</button><button class="textlink" type="button" data-keep>رجوع</button></div>
        </div>
      </div>`;
    }).join('');
    const wait = servants.filter(s => s.pending).length;
    $('#s-srv').textContent = `${servants.length} ${servants.length === 1 ? 'خادم' : 'خدام'}${wait ? ` · ${wait === 1 ? 'واحد لسه ماقبلش' : wait + ' لسه ماقبلوش'} الدعوة` : ''}`;
  }

  async function loadSrv() {
    try {
      const r = await api('servants');
      servants = r.servants;
      manage = r.can_manage;
      $('#addSrv').style.display = manage ? '' : 'none';
      renderSrv();
    } catch (err) {
      $('#s-srv').textContent = 'ماقدرناش نجيب القايمة';
    }
  }

  // ابعت الدعوة: زرار واتساب جاهز تحت الفورم بالرابط الجديد
  function showInvite(s, url) {
    $('#invName').textContent = s.name;
    $('#invWa').href = invite(s, url);
    $('#invited').classList.add('show');
    $('#invWa').focus();
  }

  $('#srvList').addEventListener('click', async e => {
    const row = e.target.closest('.srv');
    if (!row) return;
    const s = servants.find(x => x.id === +row.dataset.id);
    if (e.target.closest('[data-ask]')) { row.classList.add('confirming'); $('[data-revoke]', row).focus(); }
    if (e.target.closest('[data-keep]')) { row.classList.remove('confirming'); $('[data-ask]', row).focus(); }
    if (e.target.closest('[data-revoke]')) {
      try {
        await api(`servants/${s.id}`, { method: 'DELETE' });
        toast(`اتسحبت صلاحية ${s.name}.`);
        loadSrv();
      } catch (err) { toast(err.message); }
    }
    if (e.target.closest('[data-reinvite]')) {
      try {
        const r = await api(`servants/${s.id}/invite`, { method: 'POST' });
        showInvite(r, r.invite_url);
        loadSrv();
      } catch (err) { toast(err.message); }
    }
  });

  $('#sMob').addEventListener('input', e => { e.target.value = digits(e.target.value).slice(0, 11); });
  $('#addSrv').addEventListener('submit', async e => {
    e.preventDefault();
    const name = $('#sName').value.trim(), phone = $('#sMob').value.trim();
    $('#fsName').classList.toggle('bad', !name);
    if (!name) return $('#sName').focus();
    const btn = $('button[type=submit]', e.target);
    btn.disabled = true;
    try {
      const r = await api('servants', { method: 'POST', body: { name, phone } });
      $('#fsName').classList.remove('bad');
      $('#fsMob').classList.remove('bad');
      $('#sName').value = '';
      $('#sMob').value = '';
      showInvite(r, r.invite_url);
      loadSrv();
    } catch (err) {
      const f = err.fields || {};
      $('#fsName').classList.toggle('bad', !!f.name);
      $('#fsMob').classList.toggle('bad', !!f.phone);
      $('#sMobErr').textContent = f.phone || '';
      if (f.name) $('#sName').focus();
      else if (f.phone) $('#sMob').focus();
      else toast(err.message);
    }
    btn.disabled = false;
  });

  loadSrv();

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
