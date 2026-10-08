// شاشة المخدومين: القايمة من GET /members، والبحث والفلتر في المتصفح، والإضافة بـ POST /members.
// نسبة الحضور و"محتاج افتقاد" هيبقوا ليهم بيانات من المهام 14 و18، فدلوقتي الدايرة فاضية والفلتر مش ظاهر.
(() => {
  const { $, $$, api, toast, esc, initials, toDate, fDate, digits, C } = window.Attend;
  const body = document.body;
  let members = [], filter = 'all';

  async function load() {
    try {
      members = await api('members');
    } catch (x) {
      toast(x.message);
    }
    body.dataset.state = members.length ? 'normal' : 'empty';
    render();
  }

  function renderFilters() {
    const active = members.filter(m => m.status === 'active').length, off = members.length - active;
    const F = [['all', 'الكل', active]];
    if (off) F.push(['off', 'موقوف', off]);
    if (filter === 'off' && !off) filter = 'all';
    $('#filters').innerHTML = F.length > 1
      ? F.map(([k, t, n]) => `<button type="button" data-f="${k}" aria-pressed="${k === filter}">${t} <b>${n}</b></button>`).join('')
      : '';
    $('#countChip').innerHTML = `<b>${active}</b> مخدوم`;
  }

  function renderList() {
    const term = $('#q').value.trim().replace(/\s/g, ''), num = digits(term);
    const L = members
      .filter(m => filter === 'off' ? m.status === 'stopped' : m.status === 'active')
      .filter(m => !term || m.full_name.replace(/\s/g, '').includes(term) || (num && m.phone.includes(num)))
      .sort((a, b) => a.full_name.localeCompare(b.full_name, 'ar'));
    $('#list').innerHTML = L.length ? L.map(m => {
      const off = m.status === 'stopped';
      return `<a class="m-row${off ? ' is-off' : ''}" href="${C.base}members/${m.id}/" aria-label="${esc(m.full_name)}${off ? '، موقوف' : ''}">
        <span class="av" aria-hidden="true">${esc(initials(m.full_name))}</span>
        <span><span class="m-name">${esc(m.full_name)}</span><span class="m-meta">${off ? '<span class="flag off">موقوف</span>' : 'في الخدمة من ' + fDate.format(toDate(m.registered_at))}</span></span>
        <span class="ring" style="--p:0" aria-hidden="true"><span>–</span></span>
      </a>`;
    }).join('') : `<p class="no-match">${term ? 'مفيش مخدوم بالاسم أو الرقم ده.' : 'مفيش حد هنا.'}</p>`;
  }

  const render = () => { renderFilters(); renderList(); };
  $('#filters').addEventListener('click', e => { const b = e.target.closest('[data-f]'); if (!b) return; filter = b.dataset.f; render(); });
  $('#q').addEventListener('input', renderList);

  // ---------- إضافة مخدوم ----------
  const box = $('#addbox'), form = $('#addForm'), nameIn = $('#name'), mobIn = $('#mob'), send = $('button[type=submit]', form);
  const setOpen = st => {
    box.classList.toggle('is-open', st === 'open');
    box.classList.toggle('is-done', st === 'done');
    $('#addStart').setAttribute('aria-expanded', st === 'open');
  };
  const fieldErr = (id, msg) => {
    $(id).classList.toggle('bad', !!msg);
    if (msg) $('.f-err', $(id)).textContent = msg;
  };
  $('#addStart').addEventListener('click', () => { form.reset(); fieldErr('#fName', ''); fieldErr('#fMob', ''); setOpen('open'); nameIn.focus(); });
  $('#addCancel').addEventListener('click', () => { setOpen(''); $('#addStart').focus(); });
  $('#addMore').addEventListener('click', () => $('#addStart').click());
  mobIn.addEventListener('input', () => { mobIn.value = digits(mobIn.value).slice(0, 11); });

  // استيراد Excel (المهمة 20) والكارت على واتساب (المهمة 09) لسه ماتعملوش
  const alt = $('.add-alt'); if (alt) alt.style.display = 'none';
  $('#addedWa').style.display = 'none'; // hidden مابيكفيش لأن .btn ليها display
  $('#added p').textContent = 'هيظهر في القايمة دلوقتي. وإرسال الكارت على واتساب هيشتغل أول ما شاشة الكارت تخلص.';

  form.addEventListener('submit', async e => {
    e.preventDefault();
    if (send.disabled) return;
    // نفس قواعد السيرفر، علشان الخطأ يبان على طول. والسيرفر بيتحقق تاني في كل الأحوال
    const name = nameIn.value.trim().replace(/\s+/g, ' '), mob = mobIn.value.trim();
    const nameMsg = name.split(' ').length >= 2 ? '' : 'اكتب الاسم بالكامل (اسمين على الأقل).';
    const mobMsg = /^01[0125]\d{8}$/.test(mob) ? '' : 'رقم الموبايل لازم يكون 11 رقم ويبدأ بـ 010 أو 011 أو 012 أو 015.';
    fieldErr('#fName', nameMsg); fieldErr('#fMob', mobMsg);
    if (nameMsg) return nameIn.focus();
    if (mobMsg) return mobIn.focus();

    send.disabled = true;
    try {
      const m = await api('members', { method: 'POST', body: { full_name: name, phone: mob } });
      members.push(m);
      filter = 'all';
      body.dataset.state = 'normal';
      render();
      $('#addedName').textContent = m.full_name;
      setOpen('done');
      $('#addMore').focus();
    } catch (x) {
      fieldErr('#fName', x.fields.full_name || '');
      fieldErr('#fMob', x.fields.phone || '');
      if (x.fields.full_name) nameIn.focus(); else if (x.fields.phone) mobIn.focus(); else toast(x.message);
    } finally {
      send.disabled = false;
    }
  });

  load();
})();
