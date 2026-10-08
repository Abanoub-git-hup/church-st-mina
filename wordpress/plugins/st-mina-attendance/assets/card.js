// صفحة المخدوم على /me/<الكود>/ بتبويبين: "حضوري" (النسب والشموع والرسم وآخر 10 جلسات) و"كارتي" (الـ QR وحفظه صورة).
// من غير دخول ومن غير أي تعديل. البيانات جاية من PHP في STMINA_ATT.card وSTMINA_ATT.me (stats.php)،
// أو null لو الكود غلط أو اتلغى. ومافيهاش ملاحظات الخدام ولا "يدوي" ولا بيانات حد تاني.
// رسالة الخدام والتثبيت على الموبايل في المهمة 19.
(() => {
  const { $, $$, esc, C, qrCells, qrSvg } = window.Attend;
  const card = C.card, me = C.me;
  const icon = id => `<svg class="icon"><use href="#${id}"/></svg>`;
  const fDay = new Intl.DateTimeFormat('ar-EG-u-nu-latn', { weekday: 'long', day: 'numeric', month: 'long' });
  const fMonth = new Intl.DateTimeFormat('ar-EG-u-nu-latn', { month: 'short' });
  const fDate = new Intl.DateTimeFormat('ar-EG-u-nu-latn', { day: 'numeric', month: 'long', year: 'numeric' });
  const toDay = s => { const [y, m, d] = s.split('-').map(Number); return new Date(y, m - 1, d || 1); };
  const KINDS = { mass: 'قداس', meeting: 'اجتماع', activity: 'نشاط', service: 'خدمة' };
  const ST = { present: ['حضر', 'i-check', 'h'], excused: ['غاب بعذر', 'i-note', 'e'], absent: ['غاب', 'i-x', 'a'] };

  // المهمة 19: رسالة الخدام والتثبيت
  $('#msg').style.display = 'none';
  $('#install').style.display = 'none';

  if (!card) {
    $('#tabs').style.display = 'none';
    document.body.classList.remove('has-nav');
    $('#badLink').hidden = false;
    document.title = 'الرابط ده مش شغال | ' + C.svc;
    return;
  }

  $('#page').hidden = false;
  $('#tabs').hidden = false;
  document.body.classList.add('has-nav');
  document.title = `حضوري · ${card.name}`;
  $('#hi').textContent = `أهلًا يا ${card.name.split(' ')[0]}`;
  $('#cardName').textContent = card.name;
  $('#qr').innerHTML = qrSvg(card.url);

  // ---------- حضوري ----------
  let type = 'all';
  $('#since').textContent = me.registered_at ? `من ${fDate.format(new Date(me.registered_at.replace(' ', 'T')))}` : '';
  function renderPct() {
    const s = me.kinds[type];
    $('#types').innerHTML = [['all', 'الكل'], ...Object.entries(KINDS)].map(([k, t]) => `<button type="button" data-t="${k}" aria-pressed="${k === type}">${t}</button>`).join('');
    const ring = $('#ring');
    ring.style.setProperty('--p', s.pct ?? 0);
    ring.classList.toggle('low', s.pct !== null && s.pct < 60);
    ring.setAttribute('aria-label', s.pct === null ? 'لسه مفيش جلسات' : `نسبة حضورك ${s.pct}%`);
    $('#pct').textContent = s.pct === null ? '–' : s.pct + '%';
    const tn = type === 'all' ? 'جلسة' : `جلسة ${KINDS[type]}`;
    $('#facts').innerHTML = s.total
      ? `<span>حضرت <b>${s.present}</b> من <b>${s.base}</b> ${tn}</span>${s.excused ? `<span>وغبت بعذر <b>${s.excused}</b> ${s.excused === 1 ? 'مرة' : 'مرات'}، ودول مابيتحسبوش</span>` : ''}`
      : `<span>لسه مفيش ${tn} من ساعة ما اتسجّلت.</span>`;
  }
  $('#types').addEventListener('click', e => { const b = e.target.closest('[data-t]'); if (b) { type = b.dataset.t; renderPct(); } });

  // الشموع: شمعة لكل حضور ورا بعض (لحد 12)، وشمعة مطفية مستنية الجلسة الجاية
  function renderStreak() {
    const n = me.streak, show = Math.min(n, 12);
    $('#candles').innerHTML = Array.from({ length: show }, () => '<i class="cd lit"></i>').join('') + (n > 12 ? `<span class="more-c">+${n - 12}</span>` : '') + '<i class="cd off"></i>';
    $('#streakT').innerHTML = n
      ? `<b>${n}</b> ${n === 1 ? 'جلسة' : n === 2 ? 'جلستين' : 'جلسات'} ورا بعض<small>الشمعة اللي جاية مستنياك الجلسة الجاية.</small>`
      : 'لسه مفيش شموع مولّعة<small>احضر الجلسة الجاية وولّع أول شمعة.</small>';
  }

  // نسبة كل شهر من آخر 6 شهور (الحالي على اليمين)
  function renderBars() {
    $('#bars').innerHTML = me.months.map((x, k) => `<div class="bar${k === 0 ? ' now' : ''}${x.pct === null ? ' none' : ''}"><em>${x.pct === null ? '–' : x.pct + '%'}</em><i style="height:${x.pct === null ? 4 : Math.max(6, x.pct)}%"></i><span>${fMonth.format(toDay(x.month))}</span></div>`).join('');
    $('#bars').setAttribute('aria-label', 'نسبة كل شهر: ' + me.months.map(x => `${fMonth.format(toDay(x.month))} ${x.pct === null ? 'مفيش جلسات' : x.pct + '%'}`).join('، '));
  }

  function renderLast() {
    $('#last').innerHTML = me.last.length
      ? me.last.map(x => `<div class="ls"><div><b>${esc(x.kind_name)}</b><small>${fDay.format(toDay(x.date))}</small></div><span class="badge ${ST[x.status][2]}">${icon(ST[x.status][1])}${ST[x.status][0]}</span></div>`).join('')
      : '<p style="color:var(--on-glass-2);font-size:.9rem;padding:var(--s-2) 0">لسه مفيش جلسات خلصت من ساعة ما اتسجّلت.</p>';
  }
  renderPct(); renderStreak(); renderBars(); renderLast();

  // ---------- التبويبين: حضوري وكارتي (#card في الرابط بيفتح الكارت على طول) ----------
  const tabs = $$('#tabs [role=tab]');
  function showTab(id) {
    tabs.forEach(t => t.setAttribute('aria-selected', t.id === id));
    $('#tab-me').style.display = id === 't-me' ? 'grid' : 'none';
    $('#tab-card').hidden = id !== 't-card';
    history.replaceState(null, '', id === 't-card' ? '#card' : location.pathname + location.search);
    scrollTo(0, 0);
  }
  tabs.forEach(t => t.addEventListener('click', () => showTab(t.id)));
  showTab(location.hash === '#card' ? 't-card' : 't-me');

  // احفظ الكارت: بنرسم نفس شكل الكارت على canvas بمقاس 1080×1920 (شاشة موبايل) وننزّله PNG.
  // الخلفية صورة الصلاة، والـ QR على مربع أبيض في نص شعاع النور، والاسم على زجاج خفيف تحت
  const loadImg = src => new Promise(ok => { const i = new Image(); i.onload = () => ok(i); i.onerror = () => ok(null); i.src = src; });
  // الرسمة بتتعمل أول ما الصفحة تفتح (مش مع الضغطة)، لأن الموبايل بيسمح بقايمة المشاركة بس على طول بعد الضغط
  async function drawCard() {
    await document.fonts.ready;
    const W = 1080, H = 1920, c = document.createElement('canvas');
    c.width = W; c.height = H;
    const x = c.getContext('2d');
    const bgUrl = (getComputedStyle($('#myCard'), '::before').backgroundImage.match(/url\("?([^")]+)"?\)/) || [])[1];
    const bg = bgUrl ? await loadImg(bgUrl) : null;

    // الخلفية: الصورة بتغطي الكارت كله (cover) ومركزها 30% من فوق، زي التنسيق
    const cover = (img, fx, fy, filter = 'none') => {
      const s = Math.max(W / img.width, H / img.height), w = img.width * s, h = img.height * s;
      x.filter = filter; x.drawImage(img, (W - w) * fx, (H - h) * fy, w, h); x.filter = 'none';
    };
    x.fillStyle = '#2A1C12'; x.fillRect(0, 0, W, H);
    if (bg) cover(bg, .5, .3);
    const g = x.createLinearGradient(0, 0, 0, H);
    [[0, .62], [.26, .08], [.46, 0], [.66, .18], [1, .86]].forEach(([p, a]) => g.addColorStop(p, `rgba(20,12,6,${a})`));
    x.fillStyle = g; x.fillRect(0, 0, W, H);

    // الشعار بإطار دهبي، واسم الكنيسة
    const logo = $('#myCard img');
    if (logo.complete && logo.naturalWidth) {
      x.save(); x.beginPath(); x.arc(W / 2, 160, 84, 0, Math.PI * 2); x.clip(); x.drawImage(logo, W / 2 - 84, 76, 168, 168); x.restore();
      x.strokeStyle = 'rgba(243,201,135,.75)'; x.lineWidth = 4; x.beginPath(); x.arc(W / 2, 160, 86, 0, Math.PI * 2); x.stroke();
    }
    x.direction = 'rtl'; x.textAlign = 'center';
    x.shadowColor = 'rgba(0,0,0,.7)'; x.shadowBlur = 24;
    x.fillStyle = 'rgba(246,236,220,.9)'; x.font = '300 38px Alexandria';
    x.fillText('كنيسة السيدة العذراء ومارمينا', W / 2, 320); x.fillText('والبابا كيرلس السادس · الجبل الأصفر', W / 2, 372);
    x.shadowBlur = 0;

    // الـ QR: مربع أبيض بإطار دهبي ونور حواليه، في نص المسافة بين الاسم والزجاج
    const q = 690, qx = (W - q) / 2, qy = 640;
    x.save(); x.shadowColor = 'rgba(255,214,150,.55)'; x.shadowBlur = 140;
    x.fillStyle = '#fff'; x.beginPath(); x.roundRect(qx, qy, q, q, 56); x.fill(); x.restore();
    x.strokeStyle = 'rgba(243,201,135,.9)'; x.lineWidth = 3; x.beginPath(); x.roundRect(qx, qy, q, q, 56); x.stroke();
    const cells = qrCells(card.url), N = cells.length, pad = 40, cs = (q - pad * 2) / N;
    x.fillStyle = '#1E140D';
    cells.forEach((row, yy) => row.forEach((on, xx) => { if (on) x.fillRect(qx + pad + xx * cs, qy + pad + yy * cs, Math.ceil(cs), Math.ceil(cs)); }));

    // الزجاج تحت: نفس الخلفية متموّهة جوه المستطيل، وفوقها لون كريمي شفاف وخط نور من فوق
    const px = 64, py = 1540, pw = W - 128, ph = 316, pr = 56;
    x.save(); x.beginPath(); x.roundRect(px, py, pw, ph, pr); x.clip();
    if (bg) { cover(bg, .5, .3, 'blur(40px) saturate(1.4)'); x.fillStyle = g; x.fillRect(0, 0, W, H); }
    x.fillStyle = 'rgba(255,244,228,.09)'; x.fillRect(px, py, pw, ph);
    x.restore();
    x.strokeStyle = 'rgba(246,236,220,.14)'; x.lineWidth = 3; x.beginPath(); x.roundRect(px, py, pw, ph, pr); x.stroke();
    const hl = x.createLinearGradient(px, 0, px + pw, 0);
    hl.addColorStop(0, 'rgba(243,201,135,0)'); hl.addColorStop(.5, 'rgba(243,201,135,.6)'); hl.addColorStop(1, 'rgba(243,201,135,0)');
    x.strokeStyle = hl; x.beginPath(); x.moveTo(px + pr, py + 1.5); x.lineTo(px + pw - pr, py + 1.5); x.stroke();

    x.fillStyle = '#F3C987'; x.font = '500 40px Alexandria'; x.fillText(C.svc, W / 2, py + 82);
    x.shadowColor = 'rgba(0,0,0,.55)'; x.shadowBlur = 30;
    x.fillStyle = '#F6ECDC'; x.font = '500 76px Alexandria'; x.fillText(card.name, W / 2, py + 186, pw - 80);
    x.shadowBlur = 0;
    x.fillStyle = 'rgba(246,236,220,.8)'; x.font = '300 36px Alexandria'; x.fillText('ورّي الكارت ده للخادم في كل جلسة', W / 2, py + 262);

    return new Promise(ok => c.toBlob(ok, 'image/png'));
  }
  let pngReady = null;
  const getPng = () => pngReady || (pngReady = drawCard());
  // نجهّزها بعد ما الصفحة تهدى، علشان ماتبطّأش الفتح
  addEventListener('load', () => setTimeout(getPng, 300));

  $('#save').addEventListener('click', async () => {
    // الحفظ: على الموبايل قايمة المشاركة بتاعة الموبايل نفسه (فيها "حفظ الصورة" اللي بتنزّلها في المعرض)،
    // لأن متصفحات الموبايل (خصوصًا آيفون) كتير بتتجاهل download. وعلى اللابتوب تنزيل عادي.
    const name = `كارت-${card.name}.png`;
    const blob = await getPng();
    const file = new File([blob], name, { type: 'image/png' });
    if (navigator.canShare?.({ files: [file] })) {
      try {
        await navigator.share({ files: [file], title: `كارت ${card.name}` });
        return;
      } catch (e) {
        if (e.name === 'AbortError') return; // المخدوم قفل القايمة بنفسه
      }
    }
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.download = name;
    a.href = url;
    document.body.append(a);
    a.click();
    a.remove();
    // آيفون قديم مابيدعمش ده كله: الصورة بتفتح في تاب، والمخدوم يضغط عليها مطوّل ويختار "حفظ"
    if (/iphone|ipad|ipod/i.test(navigator.userAgent) && !navigator.canShare) window.open(url, '_blank');
    setTimeout(() => URL.revokeObjectURL(url), 60000);
  });
})();
