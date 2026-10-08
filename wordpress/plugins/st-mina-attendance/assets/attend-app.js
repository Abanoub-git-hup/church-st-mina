// المشترك بين شاشات الحضور: طلبات REST برمز nonce، والتنبيه القصير، ودوال صغيرة للعرض.
// الإعدادات من PHP في window.STMINA_ATT (app.php: stmina_att_footer).
(() => {
  const C = window.STMINA_ATT;
  const $ = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => [...r.querySelectorAll(s)];

  // طلب لمسار تحت stmina/v1. بيرجّع البيانات، أو بيرمي خطأ فيه status والرسالة وأخطاء الخانات
  async function api(path, { method = 'GET', body } = {}) {
    if (!navigator.onLine) throw Object.assign(new Error('أنت بدون إنترنت.'), { status: 0 });
    const res = await fetch(C.rest + path, {
      method,
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': C.nonce },
      body: body ? JSON.stringify(body) : undefined,
    });
    const data = await res.json().catch(() => null);
    if (!res.ok) {
      // الجلسة خلصت أو اتقفلت: نرجع لشاشة الدخول
      if (res.status === 401 && C.screen !== 'login') location.href = C.base + 'login/';
      throw Object.assign(new Error((data && data.message) || 'حصلت مشكلة. جرّب تاني.'), {
        status: res.status,
        fields: (data && data.data && data.data.fields) || {},
      });
    }
    return data;
  }

  // التنبيه القصير فوق شريط التنقل
  const toast = msg => {
    const t = $('#toast');
    if (!t) return;
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(toast.t);
    toast.t = setTimeout(() => t.classList.remove('show'), 2600);
  };

  // أي نص من المستخدم بيتحط في HTML بعد التأمين، علشان محدش يحقن كود
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  const fmtMobile = m => `${m.slice(0, 4)} ${m.slice(4, 7)} ${m.slice(7)}`;
  const initials = n => n.trim().slice(0, 2);
  // "2026-10-08 22:10:00" من السيرفر (توقيت القاهرة) ← تاريخ بالعربي
  const toDate = s => new Date(s.replace(' ', 'T'));
  const fDate = new Intl.DateTimeFormat('ar-EG-u-nu-latn', { day: 'numeric', month: 'long', year: 'numeric' });
  // الأرقام العربي ← إنجليزي، وأي حاجة غير الأرقام بتتشال (لخانة الموبايل)
  const digits = s => s.replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d)).replace(/\D/g, '');

  // الـ QR كمصفوفة (true = مربع غامق)، من مكتبة qrcode-generator. وجواه رابط الكارت نفسه
  const qrCells = text => {
    const q = qrcode(0, 'M');
    q.addData(text);
    q.make();
    const N = q.getModuleCount();
    return Array.from({ length: N }, (_, y) => Array.from({ length: N }, (_, x) => q.isDark(y, x)));
  };
  // الـ QR كـ SVG بنفس شكل التصميم
  const qrSvg = text => {
    const g = qrCells(text), N = g.length;
    let r = '';
    g.forEach((row, y) => row.forEach((on, x) => { if (on) r += `<rect x="${x}" y="${y}" width="1" height="1"/>`; }));
    return `<svg viewBox="0 0 ${N} ${N}" shape-rendering="crispEdges"><g fill="#1E140D">${r}</g></svg>`;
  };
  // رابط واتساب برسالة الكارت جاهزة (الرقم بصيغة مصر الدولية: 2 + 01…)
  const waCard = m => `https://wa.me/2${m.phone}?text=${encodeURIComponent(`سلام ومحبة يا ${m.full_name.split(' ')[0]}\nده كارت حضورك في خدمة ${C.svc}. افتحه من الرابط واحفظه صورة، وورّيه للخادم كل مرة علشان يتمسح:\n${m.card_url}`)}`;

  // زرار الخروج (من المزيد بعدين)، بيشتغل على أي عنصر عليه data-logout
  document.addEventListener('click', async e => {
    if (!e.target.closest('[data-logout]')) return;
    const r = await api('logout', { method: 'POST' }).catch(() => null);
    location.href = r ? r.redirect : C.base + 'login/';
  });

  window.Attend = { C, $, $$, api, toast, esc, fmtMobile, initials, toDate, fDate, digits, qrCells, qrSvg, waCard };
})();
