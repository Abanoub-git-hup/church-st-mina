// لوحة الخادم: حضور الكل ونسبة كل نوع، وكل مخدوم برسمه وعدد التسجيل اليدوي، وتنزيل الجدول Excel.
// كل الأرقام جاهزة من GET /dashboard (stats.php)، بنفس حساب "حضوري"، فالشاشة بتعرض بس ومابتحسبش نسب.
(() => {
  const { C, $, $$, api, toast, esc, initials, waCard } = window.Attend;
  const body = document.body;
  const fShort = new Intl.DateTimeFormat('ar-EG-u-nu-latn', { day: 'numeric', month: 'short' });
  const fMonth = new Intl.DateTimeFormat('ar-EG-u-nu-latn', { month: 'short' });
  const PERIODS = [['month', 'الشهر ده'], ['3m', 'آخر 3 شهور'], ['all', 'من الأول']];
  let period = '3m', filter = 'all', open = null, D = null;

  const day = s => new Date(s + 'T00:00:00');
  const sessN = n => n === 1 ? 'جلسة واحدة' : n === 2 ? 'جلستين' : n <= 10 ? `${n} جلسات` : `${n} جلسة`;
  const timesN = n => n === 1 ? 'مرة واحدة' : n === 2 ? 'مرتين' : n <= 10 ? `${n} مرات` : `${n} مرة`;
  const pctTxt = p => p === null ? '–' : p + '%';

  // لحد ما أول رد يوصل، مايظهرش "مفيش جلسات" ولا أرقام فاضية
  $$('.has-data').forEach(el => { el.style.visibility = 'hidden'; });

  async function load() {
    try {
      D = await api('dashboard?period=' + period);
    } catch (err) {
      toast(err.message);
      return;
    }
    $$('.has-data').forEach(el => { el.style.visibility = ''; });
    open = null;
    render();
  }

  // ---------- حضور الكل ----------
  function renderAll() {
    const S = D.sessions, n = D.members.length;
    $('#headline').innerHTML = `نسبة الحضور <b><bdi dir="ltr">${pctTxt(D.overall.all.pct)}</bdi></b>`;
    const avg = Math.round(S.reduce((a, s) => a + s.present, 0) / S.length);
    $('#headSub').textContent = `في ${sessN(S.length)}، بيحضر في المتوسط ${avg} من ${n} مخدوم.`;
    // الأحدث الأول، فبيبقى على اليمين زي رسم الشهور في "حضوري"
    const label = s => `${s.kind_name} ${fShort.format(day(s.date))}: ${s.present}`;
    $('#strip').innerHTML = S.map(s => `<i class="${s.kind === 'mass' ? 'mass' : ''}" style="height:${Math.max(3, n ? s.present / n * 100 : 0)}%" title="${esc(label(s))}"></i>`).join('');
    $('#strip').setAttribute('aria-label', 'عدد الحاضرين في كل جلسة: ' + S.map(label).join('، '));
    $('#axFrom').textContent = fShort.format(day(S[0].date));
    $('#axTo').textContent = S.length > 1 ? fShort.format(day(S[S.length - 1].date)) : '';
    $('#types').innerHTML = Object.entries(D.kind_names).map(([k, name]) => {
      const c = S.filter(s => s.kind === k).length, p = D.overall[k].pct;
      return `<div class="t-row${p === null ? ' none' : ''}"><span>${name}<small>${c ? sessN(c) : 'مفيش'}</small></span><span class="t-bar" aria-hidden="true"><span style="width:${p || 0}%"></span></span><b>${pctTxt(p)}</b></div>`;
    }).join('');
  }

  // ---------- كل مخدوم ----------
  const byName = (a, b) => a.full_name.localeCompare(b.full_name, 'ar');
  function renderList() {
    const all = D.members.slice(), min = D.manual_min;
    const many = all.filter(m => m.manual >= min);
    const F = [['all', 'الأقل حضورًا', null], ['name', 'بالاسم', null], ['manual', 'يدوي كتير', many.length]];
    $('#filter').innerHTML = F.map(([k, t, c]) => `<button type="button" data-f="${k}" aria-pressed="${k === filter}">${t}${c === null ? '' : ` <b>${c}</b>`}</button>`).join('');
    const L = filter === 'manual' ? many.sort((a, b) => b.manual - a.manual || byName(a, b))
      : filter === 'name' ? all.sort(byName)
      : all.sort((a, b) => (a.kinds.all.pct ?? 101) - (b.kinds.all.pct ?? 101) || byName(a, b));
    $('#listCount').textContent = `${all.length} مخدوم`;
    const off = D.stopped;
    $('#noteOff').textContent = off ? `${off === 1 ? 'مخدوم واحد موقوف مش' : off + ' موقوفين مش'} داخل في الأرقام.` : '';
    $('#list').innerHTML = L.length ? L.map(m => {
      const s = m.kinds.all, isOpen = open === m.id;
      const meta = s.base ? `حضر ${s.present} من ${s.base}` : 'مفيش جلسات له';
      const flag = m.manual >= min ? `<span class="flag">يدوي ${m.manual}</span>` : m.manual ? `<span>· يدوي ${m.manual}</span>` : '';
      return `<div class="m${isOpen ? ' open' : ''}">
        <button class="m-btn" type="button" data-id="${m.id}" aria-expanded="${isOpen}" aria-controls="mm-${m.id}">
          <span class="av" aria-hidden="true">${esc(initials(m.full_name))}</span>
          <span><span class="m-name">${esc(m.full_name)}</span><span class="m-meta"><span>${meta}</span>${flag}</span></span>
          <span class="ring${s.pct === null ? ' na' : s.pct < 60 ? ' low' : ''}" style="--p:${s.pct || 0}" aria-label="نسبة الحضور ${s.pct === null ? 'مفيش' : s.pct + '%'}"><span>${pctTxt(s.pct)}</span></span>
        </button>
        <div class="m-more" id="mm-${m.id}">${isOpen ? detail(m) : ''}</div>
      </div>`;
    }).join('') : `<p class="no-match">مفيش حد اتسجّل يدوي ${timesN(min)} أو أكتر في الفترة دي. الكروت شغالة.</p>`;
  }

  // تفاصيل مخدوم: نسبة كل نوع في الفترة، ورسم آخر 6 شهور (نفس "حضوري")، والتسجيل اليدوي
  function detail(m) {
    const types = Object.entries(D.kind_names).map(([k, name]) => `<div><b>${pctTxt(m.kinds[k].pct)}</b><span>${name}</span></div>`).join('');
    const mon = x => { const [y, mo] = x.month.split('-'); return fMonth.format(new Date(+y, mo - 1, 1)); };
    const bars = m.months.map((x, k) => `<div class="bar${k === 0 ? ' now' : ''}${x.pct === null ? ' none' : ''}"><em>${pctTxt(x.pct)}</em><i style="height:${x.pct === null ? 4 : Math.max(6, x.pct)}%"></i><span>${mon(x)}</span></div>`).join('');
    const label = 'نسبة كل شهر: ' + m.months.map(x => `${mon(x)} ${x.pct === null ? 'مفيش جلسات' : x.pct + '%'}`).join('، ');
    const many = m.manual >= D.manual_min;
    const manual = m.manual
      ? `اتسجّل يدوي <b>${timesN(m.manual)}</b> من ${m.kinds.all.present} حضور في الفترة دي.${many ? ' غالبًا الكارت مش معاه، ابعتهوله تاني.' : ''}`
      : 'كل حضوره اتسجّل بمسح الكارت.';
    return `<div><h3>نسبته في كل نوع</h3><div class="mini">${types}</div></div>
      <div><h3>آخر 6 شهور</h3><div class="bars" role="img" aria-label="${label}">${bars}</div></div>
      <p class="manual-t">${manual}</p>
      <div class="m-acts">
        ${many ? `<a class="wa" href="${esc(waCard(m))}" target="_blank" rel="noopener"><svg class="icon"><use href="#i-wa"/></svg>ابعتله الكارت تاني</a>` : ''}
        <a class="go" href="${esc(C.base + 'members/' + m.id + '/')}">افتح ملفه<svg class="icon"><use href="#i-left"/></svg></a>
      </div>`;
  }

  function render() {
    $('#period').innerHTML = PERIODS.map(([k, t]) => `<button type="button" data-p="${k}" aria-pressed="${k === period}">${t}</button>`).join('');
    if (!D) return;
    if (!D.sessions.length) { body.dataset.empty = ''; return; }
    delete body.dataset.empty;
    renderAll();
    renderList();
  }

  // ---------- تنزيل الجدول Excel ----------
  // نفس مكتبة SheetJS اللي في الاستيراد، بتتحمّل أول ما تدوس بس
  let xlsx;
  const loadXlsx = () => xlsx || (xlsx = new Promise((ok, no) => {
    const s = document.createElement('script');
    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js';
    s.onload = () => ok(window.XLSX);
    s.onerror = () => { xlsx = null; no(); };
    document.head.appendChild(s);
  }));
  const WORD = { present: 'حضر', absent: 'غاب', excused: 'بعذر' };
  const dmy = s => s.split('-').reverse().join('/');

  $('#xlsx').addEventListener('click', async e => {
    const btn = e.currentTarget;
    if (!D || !D.sessions.length) return;
    btn.disabled = true;
    try {
      const X = await loadXlsx();
      // الأعمدة من الأقدم للأحدث، والشيت من اليمين للشمال، فالأقدم بييجي جنب الاسم
      const S = D.sessions.slice().reverse();
      const rows = [['الاسم', ...S.map(s => `${s.kind_name} ${dmy(s.date)}`), 'النسبة']];
      D.members.slice().sort(byName).forEach(m => {
        const p = m.kinds.all.pct;
        rows.push([m.full_name, ...S.map(s => WORD[m.records['s' + s.id]] || ''), p === null ? '' : p / 100]);
      });
      const ws = X.utils.aoa_to_sheet(rows);
      // النسبة رقم بشكل % علشان تتحسب في Excel، ونفس رقم اللوحة بالظبط
      for (let r = 1; r < rows.length; r++) {
        const cell = ws[X.utils.encode_cell({ r, c: S.length + 1 })];
        if (cell && cell.t === 'n') cell.z = '0%';
      }
      ws['!cols'] = [{ wch: 26 }, ...S.map(() => ({ wch: 14 })), { wch: 8 }];
      const wb = X.utils.book_new();
      wb.Workbook = { Views: [{ RTL: true }] };
      X.utils.book_append_sheet(wb, ws, 'الحضور');
      const name = PERIODS.find(x => x[0] === period)[1];
      const today = new Intl.DateTimeFormat('en-CA', { timeZone: 'Africa/Cairo' }).format(new Date());
      X.writeFile(wb, `حضور ${C.svc} - ${name} - ${today}.xlsx`);
    } catch (err) {
      toast('ماقدرناش نعمل الملف. اتأكد من الإنترنت وجرّب تاني.');
    }
    btn.disabled = false;
  });

  $('#period').addEventListener('click', e => {
    const b = e.target.closest('[data-p]');
    if (!b || b.dataset.p === period) return;
    period = b.dataset.p;
    render();
    load();
  });
  $('#filter').addEventListener('click', e => {
    const b = e.target.closest('[data-f]');
    if (!b) return;
    filter = b.dataset.f;
    open = null;
    render();
  });
  $('#list').addEventListener('click', e => {
    const b = e.target.closest('.m-btn');
    if (!b) return;
    const id = +b.dataset.id;
    open = open === id ? null : id;
    render();
    $(`.m-btn[data-id="${id}"]`).focus();
  });

  render();
  load();
})();
