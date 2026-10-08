// service worker واحد لتطبيقين، وبيتقدّم من جذر الموقع (app.php: stmina_att_service_worker):
// - /me-sw.js بمجال /me/: صفحة "حضوري" بتاعة المخدوم (المهمة 19).
// - /attend-sw.js بمجال /attend/: شاشات الخدام، وأهمها المسح من غير نت (المهمة 21).
// كل طلب بيروح للنت الأول، وبتتحفظ نسخة منه على الموبايل. ولو مفيش نت بيرجع آخر نسخة.
// طلبات REST مابتتحفظش هنا: بيانات المسح من غير نت ليها مكانها (الشنطة والطابور في attend-app.js).
const SCOPE = new URL(self.registration.scope).pathname.includes('/attend/') ? 'attend-' : 'hodoury-';
const CACHE = SCOPE + (new URL(self.location).searchParams.get('v') || '1');
const SKIP = /\/wp-json\/|admin-ajax\.php|\/wp-admin\//;

self.addEventListener('install', () => self.skipWaiting());

// نسخة جديدة من الـ plugin: النسخ القديمة بتاعة نفس التطبيق بتتمسح، والـ worker الجديد بيمسك الصفحات المفتوحة
self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys()
      .then(keys => Promise.all(keys.filter(k => k.startsWith(SCOPE) && k !== CACHE).map(k => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', e => {
  const req = e.request;
  if (req.method !== 'GET' || !/^https?:$/.test(new URL(req.url).protocol) || SKIP.test(req.url)) return;
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
      // الصفحات من غير الجزء اللي بعد ? (زي /attend/scan/?session=5)، فأي نسخة محفوظة من الشاشة تنفع
      .catch(() => caches.match(req, { ignoreSearch: req.mode === 'navigate' }).then(r => r || Response.error()))
  );
});
