// شاشة الجلسات: الجلسات المفتوحة فوق، وفتح جلسة جديدة، والقديمة متقسّمة بالشهر.
// البيانات من GET /sessions، والعدد الكلي من GET /members (النشطين بس).
// عدد الحاضرين هيبقى ليه بيانات من المسح (المهمة 11)، فدلوقتي العلامات كلها فاضية.
(() => {
  const { $, $$, api, toast, esc, C } = window.Attend;
  const body = document.body;
  const ICONS = { mass: 'i-cross', meeting: 'i-book', activity: 'i-star', service: 'i-hands' };
  const KINDS = { mass: 'قداس', meeting: 'اجتماع', activity: 'نشاط', service: 'خدمة' };
  let sessions = [], total = 0;

  // التاريخ من السيرفر "2026-10-08" ← تاريخ محلي (من غير ساعة، علشان مايتزحلقش يوم)
  const toDay = s => { const [y, m, d] = s.split('-').map(Number); return new Date(y, m - 1, d); };
  const day = n => { const d = new Date(); d.setHours(0, 0, 0, 0); d.setDate(d.getDate() + n); return d; };
  const iso = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
  const fWd = new Intl.DateTimeFormat('ar-EG', { weekday: 'long' });
  const fMonth = new Intl.DateTimeFormat('ar-EG-u-nu-latn', { month: 'long', year: 'numeric' });
  const fFull = new Intl.DateTimeFormat('ar-EG-u-nu-latn', { weekday: 'long', day: 'numeric', month: 'long' });
  const same = (a, b) => a.toDateString() === b.toDateString();
  const rel = d => same(d, day(0)) ? 'النهاردة' : same(d, day(-1)) ? 'امبارح' : '';
  const icon = id => `<svg class="icon"><use href="#${id}"/></svg>`;

  // علامات العدّ: مجموعات من 5، الحاضرين الأول
  const tally = (n, big) => {
    let h = '';
    for (let g = 0; g < Math.ceil(total / 5); g++) {
      h += '<span class="grp">';
      for (let i = g * 5; i < Math.min(g * 5 + 5, total); i++) h += i < n ? '<i></i>' : '<i class="no"></i>';
      h += '</span>';
    }
    return `<div class="tally${big ? ' big' : ''}" aria-hidden="true">${h}</div>`;
  };

  async function load() {
    try {
      const [list, members] = await Promise.all([api('sessions'), api('members?status=active')]);
      sessions = list;
      total = members.length;
    } catch (x) { toast(x.message); }
    body.dataset.state = sessions.length ? 'normal' : 'empty';
    render();
  }

  // ---------- الجلسات المفتوحة ----------
  function renderCurrent() {
    const open = sessions.filter(s => s.status === 'open');
    $('#current').innerHTML = open.map(s => {
      const d = toDay(s.date), r = rel(d), present = s.present;
      return `
      <article class="glass cur" data-id="${s.id}">
        <div class="cur-top"><span class="live">مفتوحة الآن</span><span class="cur-date">${r ? r + ' · ' : ''}${fFull.format(d)}</span></div>
        <h2>${esc(s.kind_name)}</h2>
        <p class="count"><strong>${present}</strong><span>حضروا من ${total}</span></p>
        ${tally(present, true)}
        <div class="cur-actions">
          <a class="btn btn-light" href="${C.base}scan/?session=${s.id}">${icon('i-scan')}ابدأ المسح</a>
          <div class="cur-foot">
            <a class="textlink" href="${C.base}session/">تفاصيل الجلسة</a>
            <button class="textlink" type="button" data-ask-del>حذف الجلسة</button>
          </div>
        </div>
        <div class="confirm" role="group" aria-label="تأكيد الحذف">
          <p>هتتمسح جلسة <b>${esc(s.kind_name)} ${r || fFull.format(d)}</b>. الحذف للجلسة اللي اتفتحت بالغلط، ومش هيأثر على نسب حد.</p>
          <div class="confirm-row">
            <button class="btn-del" type="button" data-del>احذف الجلسة</button>
            <button class="textlink" type="button" data-keep>رجوع</button>
          </div>
        </div>
      </article>`;
    }).join('');
  }
  // الحذف والتأكيد (مرة واحدة على القسم كله، علشان العناصر بتتبني من جديد)
  $('#current').addEventListener('click', async e => {
    const art = e.target.closest('.cur');
    if (!art) return;
    if (e.target.closest('[data-ask-del]')) { art.classList.add('is-confirming'); $('[data-del]', art).focus(); }
    if (e.target.closest('[data-keep]')) { art.classList.remove('is-confirming'); $('[data-ask-del]', art).focus(); }
    if (e.target.closest('[data-del]')) {
      try {
        await api(`sessions/${art.dataset.id}`, { method: 'DELETE' });
        sessions = sessions.filter(s => s.id !== +art.dataset.id);
        body.dataset.state = sessions.length ? 'normal' : 'empty';
        render();
        toast('اتمسحت الجلسة.');
      } catch (x) { toast(x.message); }
    }
  });

  // ---------- الجلسات اللي خلصت، بالشهر ----------
  function renderHistory() {
    const months = new Map();
    sessions.filter(s => s.status !== 'open').forEach(s => {
      const k = fMonth.format(toDay(s.date));
      if (!months.has(k)) months.set(k, []);
      months.get(k).push(s);
    });
    $('#history').innerHTML = [...months].map(([m, list]) => `
      <h2 class="month">${m}</h2>
      <div class="glass list">${list.map(s => {
        const d = toDay(s.date);
        return `<a class="row" href="${C.base}session/" aria-label="${esc(s.kind_name)}، ${fFull.format(d)}">
          <span class="dtile"><b>${d.getDate()}</b><small>${rel(d) || fWd.format(d)}</small></span>
          <span><span class="row-head"><span>${esc(s.kind_name)}</span><em dir="ltr">${s.present} / ${total}</em></span>${tally(s.present)}</span>
        </a>`;
      }).join('')}</div>`).join('');
  }

  function render() { renderCurrent(); renderHistory(); }

  // ---------- فتح جلسة جديدة ----------
  const nb = $('#newbox'), dateIn = $('#date'), send = $('#composer button[type=submit]');
  $('#types').insertAdjacentHTML('beforeend', Object.entries(KINDS).map(([k, n]) => `<label><input type="radio" name="type" value="${k}">${icon(ICONS[k])}${n}</label>`).join(''));
  dateIn.max = iso(day(0)); // الجلسة بتتفتح يومها أو بعده

  const chosen = () => {
    const w = $('#dates input:checked').value;
    return w === 'other' ? dateIn.value : iso(day(+w));
  };
  const showErr = msg => { $('#cErr').textContent = msg; $('#cErr').classList.toggle('show', !!msg); };

  // "زي آخر جلسة": بيملا النوع بس، والتاريخ بيفضل زي ما هو
  let last = null;
  async function openComposer() {
    $('#composer').reset();
    $('#dateWrap').classList.remove('show');
    showErr('');
    nb.classList.add('is-open');
    $('#newStart').setAttribute('aria-expanded', 'true');
    $('#types input').focus();
    const cp = $('#copyLast');
    cp.hidden = true;
    last = await api('sessions/last').catch(() => null);
    if (last) { cp.hidden = false; cp.innerHTML = `${icon('i-copy')}زي آخر جلسة: ${KINDS[last.kind]}`; }
  }
  function closeComposer() { nb.classList.remove('is-open'); $('#newStart').setAttribute('aria-expanded', 'false'); }
  $('#newStart').addEventListener('click', openComposer);
  $('#cancel').addEventListener('click', () => { closeComposer(); $('#newStart').focus(); });
  $('#copyLast').addEventListener('click', () => { if (last) $(`#types input[value=${last.kind}]`).checked = true; showErr(''); });
  $('#types').addEventListener('change', () => showErr(''));
  $('#dates').addEventListener('change', () => {
    const other = $('#dates input:checked').value === 'other';
    $('#dateWrap').classList.toggle('show', other);
    if (other) { dateIn.value ||= iso(day(0)); dateIn.focus(); }
  });

  $('#composer').addEventListener('submit', async e => {
    e.preventDefault();
    const t = $('#types input:checked'), d = chosen();
    if (!t) return showErr('اختار نوع الجلسة الأول.');
    if (!d) return showErr('اختار التاريخ.');
    send.disabled = true;
    try {
      const s = await api('sessions', { method: 'POST', body: { kind: t.value, date: d } });
      sessions.unshift(s);
      body.dataset.state = 'normal';
      closeComposer();
      render();
      scrollTo({ top: 0, behavior: 'smooth' });
      toast(`اتفتحت جلسة ${s.kind_name}.`);
    } catch (x) {
      showErr(x.fields.kind || x.fields.date || x.message);
    } finally {
      send.disabled = false;
    }
  });

  // ---------- بدون إنترنت: فتح جلسة جديدة محتاج نت ----------
  const net = () => {
    const off = !navigator.onLine, sync = $('#sync');
    if (off) body.dataset.state = 'offline'; else if (body.dataset.state === 'offline') body.dataset.state = sessions.length ? 'normal' : 'empty';
    sync.dataset.sync = off ? 'offline' : 'ok';
    sync.textContent = off ? 'بدون إنترنت' : 'متزامن';
    $('#newStart').disabled = off;
    if (off) closeComposer();
  };
  addEventListener('online', net);
  addEventListener('offline', net);
  const offTxt = $('.offline span'); if (offTxt) offTxt.textContent = 'أنت بدون إنترنت. فتح جلسة جديدة محتاج اتصال.';

  load().then(net);
})();
