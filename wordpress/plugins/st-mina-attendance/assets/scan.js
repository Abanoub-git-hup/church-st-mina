// شاشة المسح: الكاميرا الخلفية، وقراية الـ QR، وتسجيل الحضور، بالنت أو من غيره.
// القراية: BarcodeDetector المدمج في المتصفح لو موجود (كروم أندرويد)، وإلا مكتبة jsQR (آيفون).
// والجلسة من ?session= (من كارت الجلسة)، وإلا آخر جلسة مفتوحة. والتسجيل اليدوي بالبحث بالاسم (المهمة 13).
//
// من غير نت (المهام 21 لـ 23): وإحنا فاتحين الجلسة بالنت، "الشنطة" بتتحفظ على الموبايل (Pack في attend-app.js):
// المخدومين بالاسم وكود الكارت وحالتهم. ولو النت وقع، المسح والتسجيل اليدوي بيشتغلوا منها، وكل عملية
// بتتحط في الطابور (Q) بوقتها، وبتتزامن لوحدها أول ما النت يرجع. والشنطة دايمًا فيها آخر حالة نعرفها،
// فالعدّاد و"اتسجّل قبل كده" صح بالنت أو من غيره.
(() => {
  const { $, api, esc, C, Q, Pack, sync } = window.Attend;
  const body = document.body;
  const icon = id => `<svg class="icon"><use href="#${id}"/></svg>`;
  const fTime = new Intl.DateTimeFormat('ar-EG-u-nu-latn', { hour: 'numeric', minute: '2-digit' });
  const clock = s => (s ? fTime.format(new Date(s.replace(' ', 'T'))) : '');
  const toDay = s => { const [y, m, d] = s.split('-').map(Number); return new Date(y, m - 1, d); };
  const day = n => { const d = new Date(); d.setHours(0, 0, 0, 0); d.setDate(d.getDate() + n); return d; };
  const fDay = new Intl.DateTimeFormat('ar-EG-u-nu-latn', { weekday: 'long', day: 'numeric', month: 'long' });
  const rel = d => d.toDateString() === day(0).toDateString() ? 'النهاردة' : d.toDateString() === day(-1).toDateString() ? 'امبارح' : fDay.format(d);
  // نفس stmina_att_code_from_scan في records.php
  const codeOf = raw => { raw = String(raw).trim(); const m = raw.match(/\/me\/([A-Za-z0-9_-]{32})\/?(?:[?#].*)?$/); return m ? m[1] : /^[A-Za-z0-9_-]{32}$/.test(raw) ? raw : ''; };
  const isOffline = x => x && x.status === 0;

  let session = null, open = [], pack = null, recent = [];

  // ---------- الشنطة: الحالة المحلية لكل مخدوم ----------
  const member = id => pack && pack.members.find(m => m.id === id);
  function setLocal(id, status, at) {
    const m = member(id);
    if (!m) return;
    m.status = status;
    m.recorded_at = status ? at : null;
    Pack.save(pack);
  }
  async function loadPack() {
    try {
      pack = await api(`sessions/${session.id}/pack`);
      Pack.save(pack);
    } catch (x) {
      pack = Pack.get(session.id) || pack;
    }
    // عمليات لسه في الطابور: الشنطة الجاية من السيرفر مافيهاش، فبنحطها فوقها
    Q.all().filter(o => o.session_id === session.id && o.type === 'set').forEach(o => setLocal(o.member_id, o.status, o.at));
    Q.all().filter(o => o.session_id === session.id && o.type === 'scan').forEach(o => {
      const m = pack && pack.members.find(x => x.token === codeOf(o.code));
      if (m && m.status !== 'present') setLocal(m.id, 'present', o.at);
    });
  }
  const total = () => (pack ? pack.members.filter(m => m.active).length : 0);

  // ---------- التسجيل اليدوي: البحث بالاسم، وبعدين حضر أو غاب بعذر أو غاب ----------
  const manual = $('#manual'), scrim = $('#scrim'), q = $('#q');
  let opener = null;
  const statusText = p => p.status === 'present' ? `<small class="here">حاضر${p.recorded_at ? ' · ' + clock(p.recorded_at) : ''}</small>` : p.status === 'excused' ? '<small>غاب بعذر</small>' : p.status === 'absent' ? '<small>غاب</small>' : '<small>مش متسجّل</small>';
  function renderManual() {
    const term = q.value.trim();
    const list = (pack ? pack.members : []).filter(p => p.active || p.status).filter(p => !term || p.full_name.includes(term))
      .sort((a, b) => (a.status === 'present') - (b.status === 'present') || a.full_name.localeCompare(b.full_name, 'ar'));
    $('#mList').innerHTML = list.length ? list.map(p => `
      <div class="m-row" data-id="${p.id}">
        <button class="m-pick" type="button" aria-expanded="false"><span class="av" aria-hidden="true">${esc(p.full_name.slice(0, 2))}</span><span>${esc(p.full_name)}</span>${statusText(p)}</button>
        <div class="m-choose" role="group" aria-label="تسجيل ${esc(p.full_name)}">
          <button type="button" data-set="present">حضر</button>
          <button type="button" data-set="excused">غاب بعذر</button>
          <button type="button" data-set="absent">غاب</button>
        </div>
      </div>`).join('') : '<p class="m-empty">مفيش مخدوم بالاسم ده في الخدمة.</p>';
  }
  $('#mList').addEventListener('click', async e => {
    const row = e.target.closest('.m-row');
    if (!row) return;
    if (e.target.closest('.m-pick')) {
      const isOpen = !row.classList.contains('open');
      document.querySelectorAll('.m-row.open').forEach(r => { r.classList.remove('open'); $('.m-pick', r).setAttribute('aria-expanded', 'false'); });
      row.classList.toggle('open', isOpen);
      $('.m-pick', row).setAttribute('aria-expanded', isOpen);
      return;
    }
    const set = e.target.closest('[data-set]');
    if (!set || !session) return;
    const id = +row.dataset.id, status = set.dataset.set, m = member(id), prev = m ? { status: m.status, at: m.recorded_at } : null;
    let rec;
    try {
      if (!navigator.onLine) throw { status: 0 };
      const r = await api(`sessions/${session.id}/records/${id}`, { method: 'PUT', body: { status } });
      setLocal(id, r.status, r.updated_at || r.recorded_at);
      rec = r;
    } catch (x) {
      if (!isOffline(x)) { closeManual(); return show('error', '', x.message); }
      const op = Q.add({ type: 'set', session_id: session.id, member_id: id, status });
      setLocal(id, status, op.at);
      rec = { member_id: id, full_name: m ? m.full_name : '', status, method: 'manual', recorded_at: op.at, pending: op.id, prev };
    }
    recent = recent.filter(x => x.member_id !== rec.member_id);
    recent.unshift(rec);
    renderCount(); renderRecent(); closeManual();
    if (rec.status === 'present') show(rec.pending ? 'saved' : 'ok', rec.full_name, clock(rec.updated_at || rec.recorded_at));
  });
  q.addEventListener('input', renderManual);
  async function openManual() {
    if (!session) return show('nosession');
    opener = document.activeElement;
    q.value = '';
    manual.classList.add('open');
    scrim.classList.add('show');
    setTimeout(() => q.focus(), 50);
    if (navigator.onLine) await loadPack(); // أحدث حالة لو فيه نت
    renderManual();
  }
  function closeManual() { manual.classList.remove('open'); scrim.classList.remove('show'); opener?.focus?.(); }
  $('#openManual').addEventListener('click', openManual);
  $('#closeManual').addEventListener('click', closeManual);
  scrim.addEventListener('click', closeManual);
  addEventListener('keydown', e => { if (e.key === 'Escape' && manual.classList.contains('open')) closeManual(); });

  // ---------- الجلسة ----------
  async function load() {
    await sync(); // لو فيه عمليات من مرة فاتت، تتزامن الأول علشان الأرقام تبقى صح
    try {
      open = (await api('sessions')).filter(s => s.status === 'open');
    } catch (x) {
      if (!isOffline(x)) return show('error', '', x.message);
      // من غير نت: الجلسات المفتوحة اللي شنطتها محفوظة
      open = Pack.list().map(p => p.session).filter(s => s.status === 'open');
    }
    const want = +new URLSearchParams(location.search).get('session');
    session = open.find(s => s.id === want) || [...open].sort((a, b) => b.opened_at.localeCompare(a.opened_at))[0] || null;
    if (!session) {
      body.dataset.state = navigator.onLine ? 'nosession' : 'offline';
      $('#sessName').textContent = navigator.onLine ? 'مفيش جلسة مفتوحة' : 'مفيش جلسة محفوظة على الموبايل';
      $('#cnt').textContent = '0';
      return show('nosession');
    }
    await loadPack();
    renderTitle();
    await loadRecords();
  }

  function renderTitle() {
    $('#sessName').textContent = `${session.kind_name} · ${rel(toDay(session.date))}`;
    $('.s-title strong:last-of-type').textContent = total();
    // تغيير الجلسة لو فيه أكتر من واحدة مفتوحة: قايمة المتصفح نفسها فوق العنوان
    let pick = $('#pick');
    if (open.length > 1 && !pick) {
      $('.s-title').insertAdjacentHTML('beforeend', '<select id="pick" aria-label="غيّر الجلسة" style="position:absolute;inset:0;opacity:0;cursor:pointer;font-size:16px"></select>');
      $('.s-title').style.position = 'relative';
      pick = $('#pick');
      pick.addEventListener('change', async () => {
        session = open.find(s => s.id === +pick.value);
        history.replaceState(null, '', `?session=${session.id}`);
        await loadPack();
        renderTitle();
        await loadRecords();
      });
    }
    if (pick) {
      pick.innerHTML = open.map(s => `<option value="${s.id}"${s.id === session.id ? ' selected' : ''}>${esc(s.kind_name)} · ${rel(toDay(s.date))}</option>`).join('');
      $('#sessName').textContent = `${session.kind_name} · ${rel(toDay(session.date))} ▾`;
    }
    renderPending();
  }

  // آخر التسجيلات: من السيرفر لو فيه نت، وإلا من الشنطة. والعمليات اللي في الطابور فوق
  async function loadRecords() {
    try {
      recent = await api(`sessions/${session.id}/records`);
    } catch (x) {
      recent = (pack ? pack.members : []).filter(m => m.status)
        .map(m => ({ member_id: m.id, full_name: m.full_name, status: m.status, method: '', recorded_at: m.recorded_at }))
        .sort((a, b) => String(b.recorded_at).localeCompare(String(a.recorded_at)));
    }
    const queued = Q.all().filter(o => o.session_id === session.id).reverse();
    queued.forEach(o => {
      const m = o.type === 'set' ? member(o.member_id) : pack && pack.members.find(x => x.token === codeOf(o.code));
      if (!m) return;
      recent = recent.filter(x => x.member_id !== m.id);
      recent.unshift({ member_id: m.id, full_name: m.full_name, status: o.type === 'set' ? o.status : 'present', method: o.type === 'set' ? 'manual' : 'scan', recorded_at: o.at, pending: o.id });
    });
    renderCount();
    renderRecent();
  }

  const renderCount = () => { $('#cnt').textContent = pack ? pack.members.filter(m => m.status === 'present').length : recent.filter(r => r.status === 'present').length; };

  function renderRecent() {
    const box = $('#recent');
    if (!recent.length) { box.innerHTML = '<p class="rec-empty">لسه محدش اتسجّل في الجلسة دي.</p>'; return; }
    box.innerHTML = recent.slice(0, 3).map((r, i) => {
      const tag = r.pending ? '<span class="tag">على الموبايل</span>' : r.method === 'manual' ? '<span class="tag">يدوي</span>' : r.status === 'excused' ? '<span class="tag excuse">غاب بعذر</span>' : '';
      return `<div class="rec"><span class="av" aria-hidden="true">${esc(r.full_name.slice(0, 2))}</span><div><b>${esc(r.full_name)}${tag}</b><small>${clock(r.recorded_at)}</small></div>${i === 0 ? `<button class="textlink" type="button" data-undo aria-label="تراجع عن تسجيل ${esc(r.full_name)}">تراجع</button>` : ''}</div>`;
    }).join('');
  }
  $('#recent').addEventListener('click', async e => {
    if (!e.target.closest('[data-undo]')) return;
    const r = recent[0];
    if (r.pending) {
      // لسه على الموبايل: بتتشال من الطابور، والحالة بترجع زي ما كانت
      Q.remove(r.pending);
      setLocal(r.member_id, r.prev ? r.prev.status : null, r.prev ? r.prev.at : null);
      recent.shift();
      renderCount(); renderRecent();
      return show('undo', r.full_name);
    }
    try {
      await api(`sessions/${session.id}/records/${r.member_id}`, { method: 'DELETE' });
      setLocal(r.member_id, null, null);
      recent.shift();
      renderCount(); renderRecent();
      show('undo', r.full_name);
    } catch (x) {
      show('error', '', isOffline(x) ? 'التراجع عن تسجيل اتزامن خلاص محتاج نت.' : x.message);
    }
  });

  // ---------- الاهتزاز والصوت (الصوت بيتحفظ على الجهاز) ----------
  const sound = $('#sound');
  let soundOn = true;
  try { soundOn = localStorage.getItem('scan-sound') !== 'off'; } catch {}
  const setSound = on => {
    soundOn = on;
    sound.setAttribute('aria-pressed', on);
    sound.setAttribute('aria-label', on ? 'الصوت شغال، اضغط لقفله' : 'الصوت مقفول، اضغط لتشغيله');
    try { localStorage.setItem('scan-sound', on ? 'on' : 'off'); } catch {}
  };
  setSound(soundOn);
  sound.addEventListener('click', () => setSound(!soundOn));
  let ac = null;
  function beep(good) {
    if (!soundOn) return;
    try {
      ac ||= new (window.AudioContext || window.webkitAudioContext)();
      const tones = good ? [[880, 0, .12]] : [[300, 0, .12], [300, .18, .12]];
      tones.forEach(([f, start, dur]) => {
        const o = ac.createOscillator(), g = ac.createGain(), t = ac.currentTime + start;
        o.frequency.value = f; o.type = 'sine';
        g.gain.setValueAtTime(.0001, t); g.gain.exponentialRampToValueAtTime(.25, t + .02); g.gain.exponentialRampToValueAtTime(.0001, t + dur);
        o.connect(g).connect(ac.destination); o.start(t); o.stop(t + dur + .02);
      });
    } catch {}
  }
  const buzz = good => { try { navigator.vibrate?.(good ? 60 : [80, 60, 80]); } catch {} };

  // ---------- نتيجة المسح ----------
  const RES = {
    ok:        { ic: 'i-check',  good: true,  t: n => `تم · ${n}`,               p: (n, at) => `اتسجّل حضوره ${at}` },
    saved:     { ic: 'i-check',  good: true,  t: n => `تم · ${n}`,               p: (n, at) => `اتسجّل ${at} على الموبايل، وهيتزامن لما النت يرجع.` },
    queued:    { ic: 'i-upload', good: true,  t: () => 'اتحفظ الكارت',            p: () => 'الكارت ده مش في القايمة اللي على الموبايل. هيتأكد لما النت يرجع.' },
    dup:       { ic: 'i-repeat', good: false, t: n => `اتسجّل قبل كده · ${n}`,    p: (n, at) => `حضوره متسجّل${at ? ' من الساعة ' + at : ''}، ومش هيتسجّل مرتين.` },
    revoked:   { ic: 'i-ban',    good: false, t: () => 'الكارت ده ملغي',           p: n => `اتعمل كارت جديد${n ? ' لـ' + n : ''}. اطلب منه الكارت الجديد.` },
    unknown:   { ic: 'i-q',      good: false, t: () => 'كارت مش معروف',            p: () => 'الكود ده مش لأي مخدوم في الخدمة.' },
    stopped:   { ic: 'i-ban',    good: false, t: n => `${n} موقوف`,                 p: () => 'رجّعه نشط من ملفه في المخدومين لو رجع الخدمة.' },
    nosession: { ic: 'i-cal-x',  good: false, t: () => 'مفيش جلسة مفتوحة',          p: () => (navigator.onLine ? 'افتح جلسة الأول علشان تقدر تسجّل الحضور.' : 'افتح الجلسة والنت شغال مرة، وبعدها المسح يشتغل من غيره.') },
    undo:      { ic: 'i-repeat', good: true,  t: n => `اتلغى تسجيل ${n}`,          p: () => 'رجع غير مسجّل في الجلسة دي.' },
    rejected:  { ic: 'i-q',      good: false, t: () => 'عمليات اترفضت في المزامنة', p: (n, at) => at },
    error:     { ic: 'i-q',      good: false, t: () => 'حصلت مشكلة',               p: (n, at) => at || 'جرّب تاني.' },
  };
  const LOOK = { saved: 'ok', queued: 'nosession', stopped: 'revoked', undo: 'dup', rejected: 'revoked', error: 'unknown' }; // شكل الحالات الزيادة من الخمسة اللي في التصميم
  const result = $('#result'), finder = $('#finder');
  let hideT = 0;
  function show(kind, name = '', at = '') {
    const r = RES[kind];
    result.dataset.kind = LOOK[kind] || kind;
    $('#rIc').innerHTML = icon(r.ic);
    $('#rTitle').textContent = r.t(name);
    $('#rText').textContent = r.p(name, at);
    $('#rAct').innerHTML = kind === 'nosession' && navigator.onLine ? `<a class="btn btn-light" href="${C.base}sessions/">افتح جلسة</a>` : '';
    result.classList.add('show');
    finder.classList.remove('hit', 'miss');
    finder.classList.add(r.good ? 'hit' : 'miss');
    if (kind !== 'undo') { buzz(r.good); beep(r.good); }
    clearTimeout(hideT);
    if (kind !== 'nosession') hideT = setTimeout(hideResult, kind === 'ok' || kind === 'saved' || kind === 'undo' ? 2000 : kind === 'rejected' ? 6000 : 3500);
  }
  function hideResult() { result.classList.remove('show'); finder.classList.remove('hit', 'miss'); }
  result.addEventListener('click', e => { if (!e.target.closest('a,button')) hideResult(); });

  // ---------- المسح من غير نت ----------
  function offlineScan(code) {
    const token = codeOf(code);
    const m = pack && pack.members.find(x => x.token === token);
    if (!m) {
      // ممكن كارت ملغي، أو مخدوم اتضاف من موبايل تاني بعد ما الشنطة اتحفظت: السيرفر هيقرر وقت المزامنة
      if (!token) return show('unknown');
      Q.add({ type: 'scan', session_id: session.id, code });
      return show('queued');
    }
    if (!m.active) return show('stopped', m.full_name);
    // زي المسح بالنت: أي سجل (حتى بعذر) = "اتسجّل قبل كده"، والتغيير من التسجيل اليدوي
    if (m.status) return show('dup', m.full_name, m.status === 'present' ? clock(m.recorded_at) : '');
    const prev = { status: m.status, at: m.recorded_at };
    const op = Q.add({ type: 'scan', session_id: session.id, code });
    setLocal(m.id, 'present', op.at);
    recent = recent.filter(x => x.member_id !== m.id);
    recent.unshift({ member_id: m.id, full_name: m.full_name, status: 'present', method: 'scan', recorded_at: op.at, pending: op.id, prev });
    renderCount(); renderRecent();
    show('saved', m.full_name, clock(op.at));
  }

  // ---------- بعت الكود ----------
  let busy = false, lastCode = '', lastAt = 0;
  async function onCode(code) {
    // نفس الكارت قدام الكاميرا: مانبعتوش تاني قبل 3 ثواني
    if (busy || !session || (code === lastCode && Date.now() - lastAt < 3000)) return;
    lastCode = code; lastAt = Date.now(); busy = true;
    try {
      if (!navigator.onLine) throw { status: 0 };
      const r = await api(`sessions/${session.id}/scan`, { method: 'POST', body: { code } });
      const name = r.member ? r.member.full_name : '';
      if (r.result === 'ok') {
        setLocal(r.member.id, 'present', r.recorded_at);
        recent = recent.filter(x => x.member_id !== r.member.id);
        recent.unshift({ member_id: r.member.id, full_name: name, status: 'present', method: 'scan', recorded_at: r.recorded_at });
        renderCount(); renderRecent();
        show('ok', name, clock(r.recorded_at));
      } else if (r.result === 'dup') {
        show('dup', name, r.recorded_at ? clock(r.recorded_at) : '');
      } else {
        if (r.result === 'nosession') { body.dataset.state = 'nosession'; session = null; }
        show(r.result, name);
      }
    } catch (x) {
      if (isOffline(x)) offlineScan(code);
      else show('error', '', x.message);
    } finally {
      busy = false;
    }
  }

  // ---------- الكاميرا الخلفية والقراية ----------
  const video = $('#cam'), camOff = $('#camOff'), torch = $('#torch');
  let stream = null, detector = null, canvas = null, ctx = null, loopT = 0;

  async function startCam() {
    if (!navigator.mediaDevices?.getUserMedia) return showCamOff('المتصفح ده مش بيدعم الكاميرا. افتح الصفحة من كروم أو سفاري.');
    try {
      stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false });
      video.srcObject = stream;
      camOff.hidden = true;
      const caps = stream.getVideoTracks()[0].getCapabilities?.() || {};
      torch.disabled = !caps.torch; // الكشاف بيشتغل على أندرويد كروم بس
      if ('BarcodeDetector' in window) {
        try { if ((await BarcodeDetector.getSupportedFormats()).includes('qr_code')) detector = new BarcodeDetector({ formats: ['qr_code'] }); } catch {}
      }
      scanLoop();
    } catch (e) {
      showCamOff(e.name === 'NotAllowedError'
        ? 'رفضت إذن الكاميرا. اسمح للموقع يستخدمها من إعدادات المتصفح، وبعدين جرّب تاني.'
        : 'مش لاقيين كاميرا على الجهاز ده.');
    }
  }
  function showCamOff(msg) { $('#camMsg').textContent = msg; camOff.hidden = false; torch.disabled = true; }
  $('#camRetry').addEventListener('click', startCam);
  torch.addEventListener('click', async () => {
    const on = torch.getAttribute('aria-pressed') !== 'true';
    try { await stream.getVideoTracks()[0].applyConstraints({ advanced: [{ torch: on }] }); torch.setAttribute('aria-pressed', on); } catch {}
  });

  // كل 200 ملي ثانية: صورة من الكاميرا ← كود
  async function readFrame() {
    if (video.readyState < 2) return '';
    if (detector) {
      const found = await detector.detect(video).catch(() => []);
      return found[0] ? found[0].rawValue : '';
    }
    if (!window.jsQR) return '';
    const w = 480, h = Math.round(video.videoHeight / video.videoWidth * w) || 640;
    canvas ||= document.createElement('canvas');
    if (canvas.width !== w) { canvas.width = w; canvas.height = h; ctx = canvas.getContext('2d', { willReadFrequently: true }); }
    ctx.drawImage(video, 0, 0, w, h);
    const img = ctx.getImageData(0, 0, w, h);
    const code = jsQR(img.data, w, h, { inversionAttempts: 'dontInvert' });
    return code ? code.data : '';
  }
  async function scanLoop() {
    clearTimeout(loopT);
    if (!stream) return;
    if (!document.hidden && session && !busy) {
      const code = await readFrame();
      if (code) await onCode(code);
    }
    loopT = setTimeout(scanLoop, 200);
  }
  // الكاميرا بتقفل لما الصفحة تستخبى (توفير بطارية)، وبترجع لما ترجع
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) { stream?.getTracks().forEach(t => t.stop()); stream = null; }
    else if (!stream) startCam();
  });

  // ---------- النت والطابور ----------
  // العدّاد: عمليات الجلسة دي اللي لسه على الموبايل. بيظهر تحت العنوان، وفي شريط "بدون إنترنت"
  function renderPending() {
    const n = session ? Q.count(session.id) : Q.count();
    $('#pendN').textContent = n;
    $('#pendTxt').textContent = n === 1 ? 'عملية واحدة لسه على الموبايل' : `${n} عمليات لسه على الموبايل`;
    $('.s-title .pend').style.display = n ? 'flex' : 'none';
  }
  addEventListener('stmina-queue', renderPending);
  // بعد المزامنة: الأرقام من السيرفر، والمرفوض بيظهر بسببه
  const WHY = { revoked: 'كارت ملغي', unknown: 'كارت مش معروف', stopped: 'موقوف', nosession: 'الجلسة اتمسحت', invalid: 'عملية مش صحيحة' };
  addEventListener('stmina-synced', async () => {
    const bad = Q.rejected();
    if (bad.length) {
      show('rejected', '', bad.map(x => `${x.member ? x.member.full_name + ': ' : ''}${WHY[x.result] || x.result}`).join('، ') + '.');
      Q.clearRejected();
    }
    if (session) { await loadPack(); renderTitle(); await loadRecords(); }
  });
  const net = () => {
    if (!navigator.onLine) body.dataset.state = 'offline';
    else if (body.dataset.state === 'offline') body.dataset.state = session ? 'normal' : 'nosession';
  };
  addEventListener('online', net);
  addEventListener('offline', net);

  load().then(() => { net(); renderPending(); startCam(); });
})();
