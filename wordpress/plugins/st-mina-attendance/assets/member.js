// ملف المخدوم: البيانات من GET /members/{id}، والتعديل والإيقاف بـ PATCH، والملاحظات من /notes.
// الحضور (المهمة 14) لسه ماتعملش، فقسمه بيقول كده. والكارت وإعادة إصداره من /members/{id}/reissue.
(() => {
  const { $, $$, api, toast, esc, fmtMobile, initials, toDate, fDate, digits, qrSvg, waCard, C } = window.Attend;
  const body = document.body;
  let m, notes = [];

  async function load() {
    try {
      [m, notes] = await Promise.all([api(`members/${C.id}`), api(`members/${C.id}/notes`)]);
    } catch (x) {
      $('#main').innerHTML = `<div class="glass card"><h1 style="font-size:1.3rem;font-weight:400">${esc(x.status === 404 ? 'المخدوم ده مش موجود.' : x.message)}</h1></div>`;
      return;
    }
    render();
  }

  function render() {
    const off = m.status === 'stopped';
    body.classList.toggle('is-off', off);
    document.title = `${m.full_name} | إعداد الخدام`;
    $('#mAv').textContent = initials(m.full_name);
    $('#mName').textContent = m.full_name;
    $('#mMob').textContent = fmtMobile(m.phone);
    $('#mStatus').textContent = off ? 'موقوف' : 'نشط';
    $('#mJoined').textContent = `في الخدمة من ${fDate.format(toDate(m.registered_at))}`;
    $('#callBtn').href = `tel:${m.phone}`;
    $('#chatBtn').href = `https://wa.me/2${m.phone}`;
    $('#susName').textContent = m.full_name;
    // الكارت: الـ QR فيه رابط الكارت، والإرسال برسالة جاهزة على واتساب
    $('#cardVer').textContent = 'الكارت الحالي';
    $('#qr').innerHTML = qrSvg(m.card_url);
    $('#sendCard').href = waCard(m);
    $('#viewCard').href = m.card_url;
    $('#notes').innerHTML = notes.length
      ? notes.map(n => `<div class="note">${esc(n.body)}<small>${esc(n.author)} · ${fDate.format(toDate(n.created_at))}</small></div>`).join('')
      : '<p class="no-notes">مفيش ملاحظات لسه.</p>';
  }

  // الحضور: لسه مفيش جلسات
  const att = $('#attH').closest('section');
  $('.c-h small', att).textContent = '';
  $('.att', att).innerHTML = '<p class="no-notes">الحضور هيظهر هنا أول ما الجلسات تبدأ.</p>';
  $('.last', att).style.display = 'none';

  // ---------- تعديل البيانات ----------
  const who = $('#whoCard');
  const fieldErr = (id, msg) => { $(id).classList.toggle('bad', !!msg); if (msg) $('.f-err', $(id)).textContent = msg; };
  $('#editBtn').addEventListener('click', () => {
    const on = !who.classList.contains('editing');
    who.classList.toggle('editing', on);
    $('#editBtn').setAttribute('aria-expanded', on);
    if (on) { $('#eNameIn').value = m.full_name; $('#eMobIn').value = m.phone; fieldErr('#eName', ''); fieldErr('#eMob', ''); $('#eNameIn').focus(); }
  });
  $('#editCancel').addEventListener('click', () => { who.classList.remove('editing'); $('#editBtn').setAttribute('aria-expanded', false); $('#editBtn').focus(); });
  $('#eMobIn').addEventListener('input', e => { e.target.value = digits(e.target.value).slice(0, 11); });
  $('#editForm').addEventListener('submit', async e => {
    e.preventDefault();
    const name = $('#eNameIn').value.trim().replace(/\s+/g, ' '), mob = $('#eMobIn').value.trim();
    const okN = name.split(' ').length >= 2, okM = /^01[0125]\d{8}$/.test(mob);
    fieldErr('#eName', okN ? '' : 'اكتب الاسم بالكامل (اسمين على الأقل).');
    fieldErr('#eMob', okM ? '' : 'رقم الموبايل لازم يكون 11 رقم ويبدأ بـ 010 أو 011 أو 012 أو 015.');
    if (!okN) return $('#eNameIn').focus();
    if (!okM) return $('#eMobIn').focus();
    try {
      m = await api(`members/${m.id}`, { method: 'PATCH', body: { full_name: name, phone: mob } });
      who.classList.remove('editing');
      render();
      toast('اتحفظ التعديل.');
    } catch (x) {
      fieldErr('#eName', x.fields.full_name || '');
      fieldErr('#eMob', x.fields.phone || '');
      if (!x.fields.full_name && !x.fields.phone) toast(x.message);
    }
  });

  // ---------- إعادة إصدار الكارت: كود جديد، والرابط القديم بيبطل فورًا ----------
  const cardBox = $('#cardBox');
  $('#askReissue').addEventListener('click', () => { cardBox.classList.add('show-confirm'); $('#reissue').focus(); });
  $('#keepCard').addEventListener('click', () => { cardBox.classList.remove('show-confirm'); $('#askReissue').focus(); });
  $('#reissue').addEventListener('click', async () => {
    try {
      m = await api(`members/${m.id}/reissue`, { method: 'POST' });
      cardBox.classList.remove('show-confirm');
      render();
      $('#sendCard').focus();
      toast('اتعمل كارت جديد والقديم اتلغى. ابعته للمخدوم.');
    } catch (x) { toast(x.message); }
  });

  // ---------- الملاحظات ----------
  $('#addNote').addEventListener('click', async () => {
    const t = $('#noteIn').value.trim();
    if (!t) return $('#noteIn').focus();
    try {
      const n = await api(`members/${m.id}/notes`, { method: 'POST', body: { body: t } });
      notes.unshift(n);
      $('#noteIn').value = '';
      render();
      toast('اتحفظت الملاحظة.');
    } catch (x) { toast(x.message); }
  });

  // ---------- الإيقاف والرجوع ----------
  const sus = $('.suspend');
  const setStatus = async (status, msg) => {
    try {
      m = await api(`members/${m.id}`, { method: 'PATCH', body: { status } });
      sus.classList.remove('show-confirm');
      render();
      toast(msg);
    } catch (x) { toast(x.message); }
  };
  $('#askSuspend').addEventListener('click', () => { sus.classList.add('show-confirm'); $('#suspend').focus(); });
  $('#keepActive').addEventListener('click', () => { sus.classList.remove('show-confirm'); $('#askSuspend').focus(); });
  $('#suspend').addEventListener('click', async () => { await setStatus('stopped', `اتوقف ${m.full_name}.`); scrollTo({ top: 0, behavior: 'smooth' }); });
  $('#unsuspend').addEventListener('click', () => setStatus('active', `${m.full_name} رجع نشط.`));

  load();
})();
