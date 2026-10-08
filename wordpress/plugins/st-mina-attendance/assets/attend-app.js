// المشترك بين شاشات الحضور: طلبات REST برمز nonce، والتنبيه القصير، ودوال صغيرة للعرض.
// الإعدادات من PHP في window.STMINA_ATT (app.php: stmina_att_footer).
(() => {
  const C = window.STMINA_ATT;
  const $ = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => [...r.querySelectorAll(s)];

  // طلب لمسار تحت stmina/v1. بيرجّع البيانات، أو بيرمي خطأ فيه status والرسالة وأخطاء الخانات
  const offline = () => Object.assign(new Error('أنت بدون إنترنت.'), { status: 0 });
  async function api(path, { method = 'GET', body } = {}, retried = false) {
    if (!navigator.onLine) throw offline();
    let res;
    try {
      res = await fetch(C.rest + path, {
        method,
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': C.nonce },
        body: body ? JSON.stringify(body) : undefined,
      });
    } catch (e) {
      throw offline(); // الموبايل فاكر إن فيه نت، والطلب مابيوصلش (شبكة الكنيسة مثلًا)
    }
    const data = await res.json().catch(() => null);
    // رمز nonce بيبوظ بعد حوالي يوم. لو الشاشة اتفتحت من النسخة المحفوظة من غير نت، رمزها ممكن يكون قديم:
    // بنجيب رمز جديد من WordPress (admin-ajax: rest-nonce) ونجرّب تاني مرة واحدة
    if (res.status === 403 && data && data.code === 'rest_cookie_invalid_nonce' && !retried && C.ajax) {
      const fresh = await fetch(C.ajax + '?action=rest-nonce', { credentials: 'same-origin' }).then(r => (r.ok ? r.text() : '')).catch(() => '');
      if (/^[a-f0-9]{10}$/.test(fresh)) {
        C.nonce = fresh;
        return api(path, { method, body }, true);
      }
    }
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

  // ---------- المسح من غير نت: الشنطة والطابور (المهام 21 لـ 23) ----------
  // الكل في localStorage على موبايل الخادم:
  // - الشنطة (stmina_pack_<الجلسة>): الجلسة، والمخدومين بالاسم وكود الكارت وحالتهم. من غير أي موبايل.
  // - الطابور (stmina_queue): كل مسح أو تسجيل يدوي اتعمل من غير نت، بوقته الأصلي.
  // - المرفوض (stmina_rejected): اللي السيرفر رفضه في المزامنة بسببه (كارت ملغي أو مش معروف ...).
  const LS = {
    get(k, d) { try { const v = localStorage.getItem(k); return v ? JSON.parse(v) : d; } catch (e) { return d; } },
    set(k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} },
    del(k) { try { localStorage.removeItem(k); } catch (e) {} },
  };
  // دلوقتي بتوقيت القاهرة بنفس شكل السيرفر (Y-m-d H:i:s)
  const nowCairo = () => {
    const p = Object.fromEntries(new Intl.DateTimeFormat('en-CA', { timeZone: 'Africa/Cairo', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit', hourCycle: 'h23' })
      .formatToParts(new Date()).map(x => [x.type, x.value]));
    return `${p.year}-${p.month}-${p.day} ${p.hour}:${p.minute}:${p.second}`;
  };
  const uid = () => (crypto.randomUUID ? crypto.randomUUID() : Date.now().toString(36) + Math.random().toString(36).slice(2));
  const emit = () => dispatchEvent(new CustomEvent('stmina-queue'));

  const Q = {
    all: () => LS.get('stmina_queue', []),
    add(op) {
      const full = { id: uid(), at: nowCairo(), ...op };
      LS.set('stmina_queue', [...Q.all(), full]);
      emit();
      return full;
    },
    remove(id) { LS.set('stmina_queue', Q.all().filter(o => o.id !== id)); emit(); },
    count: sid => Q.all().filter(o => !sid || o.session_id === sid).length,
    rejected: () => LS.get('stmina_rejected', []),
    clearRejected() { LS.del('stmina_rejected'); emit(); },
  };

  // آخر 5 جلسات اتفتحت بس، علشان الموبايل مايتمليش
  const Pack = {
    get: id => LS.get('stmina_pack_' + id, null),
    save(p) {
      LS.set('stmina_pack_' + p.session.id, p);
      const ids = [p.session.id, ...LS.get('stmina_packs', []).filter(x => x !== p.session.id)];
      ids.slice(5).forEach(x => LS.del('stmina_pack_' + x));
      LS.set('stmina_packs', ids.slice(0, 5));
    },
    list: () => LS.get('stmina_packs', []).map(Pack.get).filter(Boolean),
    clear() { LS.get('stmina_packs', []).forEach(x => LS.del('stmina_pack_' + x)); LS.del('stmina_packs'); },
  };

  // المزامنة: الطابور كله في طلب واحد. اللي اتبعت بيتشال (حتى المرفوض، بعد ما يتحفظ سببه)،
  // واللي اتضاف وإحنا بنزامن بيفضل للمرة الجاية
  const BAD = ['revoked', 'unknown', 'stopped', 'nosession', 'invalid'];
  let syncing = null;
  function sync() {
    if (syncing) return syncing;
    const ops = Q.all();
    if (!ops.length || !navigator.onLine || C.screen === 'card' || C.screen === 'login' || C.screen === 'invite') return Promise.resolve(null);
    syncing = api('sync', { method: 'POST', body: { ops } })
      .then(r => {
        const sent = new Set(ops.map(o => o.id));
        const bad = r.results.filter(x => BAD.includes(x.result)).map(x => ({ ...x, at: (ops.find(o => o.id === x.id) || {}).at }));
        if (bad.length) LS.set('stmina_rejected', [...Q.rejected(), ...bad].slice(-30));
        LS.set('stmina_queue', Q.all().filter(o => !sent.has(o.id)));
        emit();
        dispatchEvent(new CustomEvent('stmina-synced', { detail: r.results }));
        return r.results;
      })
      .catch(() => null)
      .finally(() => { syncing = null; });
    return syncing;
  }
  addEventListener('online', () => sync());
  setInterval(() => { if (Q.all().length) sync(); }, 30000);
  setTimeout(() => sync(), 500);

  // تطبيق الخدام: الـ service worker بيحفظ الشاشات فتفتح من غير نت (me-sw.js)
  if (C.sw && 'serviceWorker' in navigator) navigator.serviceWorker.register(C.sw, { scope: C.swScope }).catch(() => {});

  // زرار الخروج: الأيقونة فوق في كل شاشة، وفي لوح "حسابك" في المزيد. أي عنصر عليه data-logout.
  // هو لينك لصفحة الدخول، فلو السكريبت ماشتغلش بيروح هناك عادي (والصفحة بترجّعه لو لسه داخل)
  document.addEventListener('click', async e => {
    const el = e.target.closest('[data-logout]');
    if (!el) return;
    e.preventDefault();
    if (el.getAttribute('aria-busy')) return;
    // عمليات لسه على الموبايل بس: الخروج كان هيضيّعها
    if (Q.count()) {
      toast(`فيه ${Q.count()} عمليات على الموبايل لسه ماتزامنتش. شغّل النت واستنى لحد ما تتزامن، وبعدين اخرج.`);
      sync();
      return;
    }
    el.setAttribute('aria-busy', 'true');
    // الأسماء والأكواد المحفوظة للمسح من غير نت والشاشات المحفوظة: مالهاش لازمة بعد الخروج
    Pack.clear();
    Q.clearRejected();
    try { (await caches.keys()).filter(k => k.startsWith('attend-')).forEach(k => caches.delete(k)); } catch (err) {}
    const r = await api('logout', { method: 'POST' }).catch(() => null);
    location.href = r ? r.redirect : C.base + 'login/';
  });

  window.Attend = { C, $, $$, api, toast, esc, fmtMobile, initials, toDate, fDate, digits, qrCells, qrSvg, waCard, Q, Pack, sync, nowCairo };
})();
