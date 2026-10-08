// شاشة المسح: الكاميرا الخلفية، وقراية الـ QR، وبعت الكود لـ POST /sessions/{id}/scan، وعرض النتيجة
// بلون وأيقونة واهتزاز وصوت. والجلسة من ?session= (من كارت الجلسة)، وإلا آخر جلسة مفتوحة.
// القراية: BarcodeDetector المدمج في المتصفح لو موجود (كروم أندرويد)، وإلا مكتبة jsQR (آيفون).
// التسجيل اليدوي في المهمة 13، والمسح من غير نت في المهام 21 لـ 23.
(() => {
  const { $, api, esc, C } = window.Attend;
  const body = document.body;
  const icon = id => `<svg class="icon"><use href="#${id}"/></svg>`;
  const fTime = new Intl.DateTimeFormat('ar-EG-u-nu-latn', { hour: 'numeric', minute: '2-digit' });
  const clock = s => fTime.format(new Date(s.replace(' ', 'T')));
  const toDay = s => { const [y, m, d] = s.split('-').map(Number); return new Date(y, m - 1, d); };
  const day = n => { const d = new Date(); d.setHours(0, 0, 0, 0); d.setDate(d.getDate() + n); return d; };
  const fDay = new Intl.DateTimeFormat('ar-EG-u-nu-latn', { weekday: 'long', day: 'numeric', month: 'long' });
  const rel = d => d.toDateString() === day(0).toDateString() ? 'النهاردة' : d.toDateString() === day(-1).toDateString() ? 'امبارح' : fDay.format(d);

  let session = null, open = [], total = 0, recent = [];

  // التسجيل اليدوي لسه جاي (المهمة 13)
  $('#openManual').style.display = 'none';

  // ---------- الجلسة ----------
  async function load() {
    try {
      const [list, members] = await Promise.all([api('sessions'), api('members?status=active')]);
      open = list.filter(s => s.status === 'open');
      total = members.length;
    } catch (x) { return show('error', '', x.message); }
    const want = +new URLSearchParams(location.search).get('session');
    // من غير اختيار: آخر جلسة اتفتحت
    session = open.find(s => s.id === want) || [...open].sort((a, b) => b.opened_at.localeCompare(a.opened_at))[0] || null;
    if (!session) {
      body.dataset.state = 'nosession';
      $('#sessName').textContent = 'مفيش جلسة مفتوحة';
      $('#cnt').textContent = '0';
      return show('nosession');
    }
    renderTitle();
    await loadRecords();
  }

  function renderTitle() {
    $('#sessName').textContent = `${session.kind_name} · ${rel(toDay(session.date))}`;
    $('.s-title strong:last-of-type').textContent = total;
    // تغيير الجلسة لو فيه أكتر من واحدة مفتوحة: قايمة المتصفح نفسها فوق العنوان
    let pick = $('#pick');
    if (open.length > 1 && !pick) {
      $('.s-title').insertAdjacentHTML('beforeend', '<select id="pick" aria-label="غيّر الجلسة" style="position:absolute;inset:0;opacity:0;cursor:pointer;font-size:16px"></select>');
      $('.s-title').style.position = 'relative';
      pick = $('#pick');
      pick.addEventListener('change', async () => {
        session = open.find(s => s.id === +pick.value);
        history.replaceState(null, '', `?session=${session.id}`);
        renderTitle();
        await loadRecords();
      });
      $('#sessName').insertAdjacentHTML('beforeend', ' ▾');
    }
    if (pick) {
      pick.innerHTML = open.map(s => `<option value="${s.id}"${s.id === session.id ? ' selected' : ''}>${esc(s.kind_name)} · ${rel(toDay(s.date))}</option>`).join('');
      $('#sessName').textContent = `${session.kind_name} · ${rel(toDay(session.date))} ▾`;
    }
  }

  async function loadRecords() {
    try { recent = await api(`sessions/${session.id}/records`); } catch { recent = []; }
    renderCount();
    renderRecent();
  }

  const renderCount = () => { $('#cnt').textContent = recent.filter(r => r.status === 'present').length; };

  function renderRecent() {
    const box = $('#recent');
    if (!recent.length) { box.innerHTML = '<p class="rec-empty">لسه محدش اتسجّل في الجلسة دي.</p>'; return; }
    box.innerHTML = recent.slice(0, 3).map((r, i) => {
      const tag = r.method === 'manual' ? '<span class="tag">يدوي</span>' : r.status === 'excused' ? '<span class="tag excuse">غاب بعذر</span>' : '';
      return `<div class="rec"><span class="av" aria-hidden="true">${esc(r.full_name.slice(0, 2))}</span><div><b>${esc(r.full_name)}${tag}</b><small>${clock(r.recorded_at)}</small></div>${i === 0 ? `<button class="textlink" type="button" data-undo aria-label="تراجع عن تسجيل ${esc(r.full_name)}">تراجع</button>` : ''}</div>`;
    }).join('');
  }
  $('#recent').addEventListener('click', async e => {
    if (!e.target.closest('[data-undo]')) return;
    const r = recent[0];
    try {
      await api(`sessions/${session.id}/records/${r.member_id}`, { method: 'DELETE' });
      recent.shift();
      renderCount(); renderRecent();
      show('undo', r.full_name);
    } catch (x) { show('error', '', x.message); }
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
    dup:       { ic: 'i-repeat', good: false, t: n => `اتسجّل قبل كده · ${n}`,    p: (n, at) => `حضوره متسجّل من الساعة ${at}، ومش هيتسجّل مرتين.` },
    revoked:   { ic: 'i-ban',    good: false, t: () => 'الكارت ده ملغي',           p: n => `اتعمل كارت جديد${n ? ' لـ' + n : ''}. اطلب منه الكارت الجديد.` },
    unknown:   { ic: 'i-q',      good: false, t: () => 'كارت مش معروف',            p: () => 'الكود ده مش لأي مخدوم في الخدمة.' },
    stopped:   { ic: 'i-ban',    good: false, t: n => `${n} موقوف`,                 p: () => 'رجّعه نشط من ملفه في المخدومين لو رجع الخدمة.' },
    nosession: { ic: 'i-cal-x',  good: false, t: () => 'مفيش جلسة مفتوحة',          p: () => 'افتح جلسة الأول علشان تقدر تسجّل الحضور.' },
    undo:      { ic: 'i-repeat', good: true,  t: n => `اتلغى تسجيل ${n}`,          p: () => 'رجع غير مسجّل في الجلسة دي.' },
    error:     { ic: 'i-q',      good: false, t: () => 'حصلت مشكلة',               p: (n, at) => at || 'جرّب تاني.' },
  };
  const LOOK = { stopped: 'revoked', undo: 'dup', error: 'unknown' }; // شكل الحالات الزيادة من الخمسة اللي في التصميم
  const result = $('#result'), finder = $('#finder');
  let hideT = 0;
  function show(kind, name = '', at = '') {
    const r = RES[kind];
    result.dataset.kind = LOOK[kind] || kind;
    $('#rIc').innerHTML = icon(r.ic);
    $('#rTitle').textContent = r.t(name);
    $('#rText').textContent = r.p(name, at);
    $('#rAct').innerHTML = kind === 'nosession' ? `<a class="btn btn-light" href="${C.base}sessions/">افتح جلسة</a>` : '';
    result.classList.add('show');
    finder.classList.remove('hit', 'miss');
    finder.classList.add(r.good ? 'hit' : 'miss');
    if (kind !== 'undo') { buzz(r.good); beep(r.good); }
    clearTimeout(hideT);
    if (kind !== 'nosession') hideT = setTimeout(hideResult, kind === 'ok' || kind === 'undo' ? 2000 : 3500);
  }
  function hideResult() { result.classList.remove('show'); finder.classList.remove('hit', 'miss'); }
  result.addEventListener('click', e => { if (!e.target.closest('a,button')) hideResult(); });

  // ---------- بعت الكود ----------
  let busy = false, lastCode = '', lastAt = 0;
  async function onCode(code) {
    // نفس الكارت قدام الكاميرا: مانبعتوش تاني قبل 3 ثواني
    if (busy || !session || (code === lastCode && Date.now() - lastAt < 3000)) return;
    lastCode = code; lastAt = Date.now(); busy = true;
    try {
      const r = await api(`sessions/${session.id}/scan`, { method: 'POST', body: { code } });
      const name = r.member ? r.member.full_name : '';
      if (r.result === 'ok') {
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
      show('error', '', x.status === 0 ? 'أنت بدون إنترنت. المسح من غير نت لسه مش شغال.' : x.message);
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

  // ---------- بدون إنترنت ----------
  const offTxt = $('.offline span');
  if (offTxt) offTxt.textContent = 'أنت بدون إنترنت. المسح محتاج اتصال لحد ما يبقى شغال من غير نت.';
  const net = () => { if (!navigator.onLine) body.dataset.state = 'offline'; else if (body.dataset.state === 'offline') body.dataset.state = session ? 'normal' : 'nosession'; };
  addEventListener('online', net);
  addEventListener('offline', net);
  $('.s-title .pend').style.display = 'none';

  load().then(() => { net(); startCam(); });
})();
