// service worker لصفحة "حضوري" (/me/<الكود>/)، وبيتقدّم من /me-sw.js (app.php: stmina_att_service_worker).
// كل طلب بيروح للنت الأول، وبتتحفظ نسخة منه على الموبايل. ولو مفيش نت بيرجع آخر نسخة،
// فالكارت والـ QR وآخر أرقام اتشافت بيظهروا جوه الكنيسة حتى لو النت واقع.
// الأرقام نفسها جوه الصفحة (STMINA_ATT.me)، فنسخة الصفحة لوحدها كفاية من غير أي طلب REST.
const CACHE = 'hodoury-' + (new URL(self.location).searchParams.get('v') || '1');

self.addEventListener('install', () => self.skipWaiting());

// نسخة جديدة من الـ plugin: النسخ القديمة بتتمسح، والـ worker الجديد بيمسك الصفحات المفتوحة على طول
self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys()
      .then(keys => Promise.all(keys.filter(k => k.startsWith('hodoury-') && k !== CACHE).map(k => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', e => {
  const req = e.request;
  if (req.method !== 'GET' || !/^https?:$/.test(new URL(req.url).protocol)) return;
  e.respondWith(
    fetch(req)
      .then(res => {
        // ok للملفات العادية، وopaque للخطوط والمكتبات من مواقع تانية
        if (res.ok || res.type === 'opaque') {
          const copy = res.clone();
          caches.open(CACHE).then(c => c.put(req, copy));
        }
        return res;
      })
      .catch(() => caches.match(req).then(r => r || Response.error()))
  );
});
