// الاستيراد من Excel على 3 خطوات زي التصميم: اختيار الملف ← المعاينة ← بعد الإضافة (وبعت الكروت).
// المتصفح بيقرا الملف بمكتبة SheetJS (من cdnjs، بتتحمّل لما نحتاجها بس)، وبيبعت الصفوف لـ POST /members/import:
// الأول commit=false للمعاينة، وبعدين commit=true للإضافة. والسيرفر هو اللي بيحكم على كل صف.
(() => {
  const { $, api, esc, toast, fmtMobile, initials, waCard } = window.Attend;
  const body = document.body;
  let rows = [], sent = new Set(), added = [];

  // ---------- مكتبة Excel ----------
  let xlsx;
  const loadXlsx = () => xlsx || (xlsx = new Promise((ok, no) => {
    const s = document.createElement('script');
    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js';
    s.onload = () => ok(window.XLSX);
    s.onerror = () => { xlsx = null; no(); };
    document.head.appendChild(s);
  }));

  // ---------- من الجدول للصفوف: عمود الموبايل وعمود الاسم بيتعرفوا من محتواهم مش من مكانهم ----------
  // (فيه ملفات فيها عمود رقم مسلسل الأول، أو الاسم والموبايل متبدّلين)
  const digitsOf = v => String(v ?? '').replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d)).replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).replace(/\D/g, '');
  const isMobile = v => digitsOf(v).length >= 9;
  const isName = v => /\p{L}/u.test(String(v ?? '')) && !isMobile(v);
  const isHeader = r => r.some(c => /^(الاسم|اسم|name|الموبايل|موبايل|رقم|التليفون|تليفون|mobile|phone|م)$/i.test(String(c ?? '').trim()));
  function toRows(table) {
    const out = [];
    table.forEach((r, i) => {
      const cells = r.map(c => String(c ?? '').trim());
      if (!cells.some(Boolean)) return;
      const phone = cells.find(isMobile) || '';
      const name = cells.find(isName) || '';
      if (!phone && isHeader(cells)) return; // صف العناوين
      if (!phone && !name) return;
      out.push({ line: i + 1, name, phone });
    });
    return out;
  }

  // ---------- العرض ----------
  const plural = n => n === 1 ? 'مخدوم واحد' : n === 2 ? 'مخدومين اتنين' : n <= 10 ? `${n} مخدومين` : `${n} مخدوم`;
  const rowsN = n => n === 1 ? 'صف واحد' : n === 2 ? 'صفين' : n <= 10 ? `${n} صفوف` : `${n} صف`;
  const count = (n, one, two, many) => n === 1 ? `${one} واحد` : n === 2 ? two : n <= 10 ? `${n} ${many}` : `${n} ${one}`;
  function setStep(st) {
    body.dataset.step = st;
    $('#pickBox').classList.remove('is-reading');
    scrollTo(0, 0);
    const h = $(`#h-${st}`); if (h) { h.tabIndex = -1; h.focus({ preventScroll: true }); }
  }
  function showPreview(fileName, res) {
    rows = res.rows;
    const ok = rows.filter(r => r.ok), bad = rows.filter(r => !r.ok);
    $('#fileName').textContent = fileName;
    $('#h-preview').innerHTML = ok.length ? `هيتضاف <b>${plural(ok.length)}</b>` : 'مفيش ولا صف ينفع يتضاف';
    $('#pvLead').innerHTML = `في الملف <b>${rowsN(rows.length)}</b>${bad.length ? `، منهم <b>${rowsN(bad.length)}</b> فيهم مشكلة ومش هيتضافوا` : '، وكلهم سليمين'}.`;
    // علامة لكل صف بنفس ترتيب الملف، في مجموعات من 5
    let h = '';
    rows.forEach((r, i) => { if (i % 5 === 0) h += (i ? '</span>' : '') + '<span class="grp">'; h += `<i${r.ok ? '' : ' class="bad"'}></i>`; });
    $('#rowsTally').innerHTML = h + (rows.length ? '</span>' : '');
    const nFixed = ok.filter(r => r.fixed).length;
    $('#fixedNote').hidden = !nFixed;
    $('#fixedNote').textContent = nFixed ? `ظبطنا ${count(nFixed, 'رقم', 'رقمين', 'أرقام')} تلقائيًا: كان ناقصهم الصفر اللي في الأول (Excel بيشيله أحيانًا) أو مكتوبين بـ ‎+20 أو بأرقام عربي.` : '';
    $('#rej').hidden = !bad.length;
    $('#rejList').innerHTML = bad.map(r => `<div class="r-row"><span class="r-no">صف ${r.line}</span><span><span class="r-who${r.name ? '' : ' none'}">${r.name ? esc(r.name) : `<bdi dir="ltr">${esc(r.phone)}</bdi>`}</span><span class="r-why">${esc(r.why)}</span></span></div>`).join('');
    $('#okBox').hidden = !ok.length;
    $('#okTitle').textContent = `الأسماء اللي هتتضاف (${ok.length})`;
    $('#okList').innerHTML = ok.map(r => `<li><span>${esc(r.name)}</span><span>${fmtMobile(r.phone)}</span></li>`).join('');
    const btn = $('#doAdd');
    btn.disabled = !ok.length;
    btn.innerHTML = `<svg class="icon"><use href="#i-plus"/></svg>${ok.length ? `ضيف ${plural(ok.length)}` : 'مفيش حد يتضاف'}`;
    setStep('preview');
  }
  function showDone() {
    $('#h-done').innerHTML = `اتضاف <b>${plural(added.length)}</b>`;
    $('#waList').innerHTML = added.map(m => `<div class="w-row${sent.has(m.id) ? ' sent' : ''}" data-id="${m.id}">
      <span class="av" aria-hidden="true">${sent.has(m.id) ? '<svg class="icon" style="width:18px;height:18px"><use href="#i-check"/></svg>' : esc(initials(m.full_name))}</span>
      <span><span class="w-name">${esc(m.full_name)}</span><span class="w-mob">${fmtMobile(m.phone)}</span></span>
      <a class="wa" href="${waCard(m)}" target="_blank" rel="noopener" aria-label="ابعت الكارت لـ${esc(m.full_name)} على واتساب"><svg class="icon"><use href="#i-wa"/></svg>${sent.has(m.id) ? 'اتبعت' : 'ابعت الكارت'}</a>
    </div>`).join('');
    updateSent();
    setStep('done');
  }
  // "ابعت الكارت": الرابط بيفتح واتساب عادي، وإحنا بنعلّم إنه اتبعت (على الشاشة وعلى السيرفر)
  $('#waList').addEventListener('click', e => {
    const a = e.target.closest('.wa');
    if (!a) return;
    const row = a.closest('.w-row'), id = +row.dataset.id;
    sent.add(id);
    row.classList.add('sent');
    $('.av', row).innerHTML = '<svg class="icon" style="width:18px;height:18px"><use href="#i-check"/></svg>';
    a.lastChild.textContent = 'اتبعت';
    updateSent();
    api(`members/${id}/card-sent`, { method: 'POST' }).catch(() => {});
  });
  function updateSent() {
    $('#sentCount').textContent = `اتبعت ${sent.size} من ${added.length}`;
    $('#sentBar').style.width = (added.length ? sent.size / added.length * 100 : 0) + '%';
  }

  // ---------- قراية الملف والمعاينة ----------
  let pending = [], pendingName = '';
  $('#file').addEventListener('change', async e => {
    const f = e.target.files[0];
    e.target.value = '';
    if (!f) return;
    $('#pickBox').classList.add('is-reading');
    let table;
    try {
      const X = await loadXlsx();
      const wb = X.read(await f.arrayBuffer(), { type: 'array' });
      table = X.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]], { header: 1, raw: false, defval: '' });
    } catch {
      return showBad('الملف ده مش بيتفتح', 'اختار ملف Excel (بامتداد ‎.xlsx أو ‎.xls) أو CSV. لو الملف من Google Sheets نزّله الأول من File ثم Download ثم Microsoft Excel.');
    }
    pending = toRows(table);
    pendingName = f.name;
    if (!pending.length) return showBad('مش لاقيين أسماء في الملف ده', 'اتأكد إن الأسماء في أول صفحة في الملف، وإن فيه عمود للاسم وعمود للموبايل.');
    try {
      showPreview(f.name, await api('members/import', { method: 'POST', body: { rows: pending, commit: false } }));
    } catch (x) { showBad('حصلت مشكلة', x.message); }
  });
  function showBad(t, p) { $('#h-bad').textContent = t; $('#badLead').textContent = p; setStep('bad'); }
  const repick = () => { setStep('pick'); $('#file').click(); };
  $('#again').addEventListener('click', repick);
  $('#badAgain').addEventListener('click', repick);

  // ---------- الإضافة ----------
  $('#doAdd').addEventListener('click', async () => {
    const btn = $('#doAdd');
    btn.disabled = true;
    try {
      const res = await api('members/import', { method: 'POST', body: { rows: pending, commit: true } });
      added = res.added;
      sent = new Set();
      showDone();
    } catch (x) {
      toast(x.message);
      btn.disabled = false;
    }
  });

  // ---------- ملف جاهز: عمودين بعناوينهم، والموبايل نص علشان Excel مايشيلش الصفر ----------
  async function template() {
    try {
      const X = await loadXlsx();
      const ws = X.utils.aoa_to_sheet([['الاسم', 'الموبايل']]);
      ws['!cols'] = [{ wch: 28 }, { wch: 16 }];
      for (let r = 1; r < 300; r++) ws[X.utils.encode_cell({ r, c: 1 })] = { t: 's', v: '', z: '@' };
      ws['!ref'] = 'A1:B300';
      const wb = X.utils.book_new();
      wb.Workbook = { Views: [{ RTL: true }] };
      X.utils.book_append_sheet(wb, ws, 'المخدومين');
      X.writeFile(wb, 'مخدومين-إعداد-الخدام.xlsx');
    } catch { toast('مش قادرين ننزّل الملف دلوقتي. اتأكد من النت وجرّب تاني.'); }
  }
  $('#tpl').addEventListener('click', template);
  $('#tpl2').addEventListener('click', template);

  setStep('pick');
})();
