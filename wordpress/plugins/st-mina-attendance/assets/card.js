// كارت المخدوم على /me/<الكود>/: الاسم والـ QR وزرار "احفظ الكارت كصورة". من غير دخول ومن غير أي تعديل.
// البيانات جاية من PHP في STMINA_ATT.card (الاسم والرابط بس)، أو null لو الكود غلط أو اتلغى.
// تبويب "حضوري" هيتضاف في المهمة 16، فدلوقتي الصفحة بتفتح على الكارت على طول.
(() => {
  const { $, C, qrCells, qrSvg } = window.Attend;
  const card = C.card;

  // الأجزاء اللي لسه مستنية "حضوري": التبويبين، والتحية بالنسبة، وتبويب حضوري نفسه
  $('#tabs').style.display = 'none';
  $('#tab-me').style.display = 'none';
  document.body.classList.remove('has-nav');

  if (!card) {
    $('#badLink').hidden = false;
    document.title = 'الرابط ده مش شغال | ' + C.svc;
    return;
  }

  $('#page').hidden = false;
  $('#tab-card').hidden = false;
  document.title = `كارت ${card.name} | ${C.svc}`;
  $('#hi').textContent = `أهلًا يا ${card.name.split(' ')[0]}`;
  $('#cardName').textContent = card.name;
  $('#qr').innerHTML = qrSvg(card.url);

  // احفظ الكارت: بنرسم نفس شكل الكارت على canvas بمقاس 1080×1920 (شاشة موبايل) وننزّله PNG.
  // الخلفية صورة الصلاة، والـ QR على مربع أبيض في نص شعاع النور، والاسم على زجاج خفيف تحت
  const loadImg = src => new Promise(ok => { const i = new Image(); i.onload = () => ok(i); i.onerror = () => ok(null); i.src = src; });
  $('#save').addEventListener('click', async () => {
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

    const a = document.createElement('a');
    a.download = `كارت-${card.name}.png`;
    a.href = c.toDataURL('image/png');
    a.click();
  });
})();
