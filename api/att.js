// دالة Vercel واحدة لنظام الحضور كله (الخطة المجانية فيها 12 دالة بالكتير، وعندنا 30 مسار):
// - /api/v1/<المسار>: نفس مسارات /wp-json/stmina/v1/ في بلجن WordPress، بنفس شكل الرد والأخطاء.
// - /attend/... و/me/...: الشاشات (lib/att/screens.js).
// - /api/cron/autoclose: الجلسات المنسية تخلص لوحدها (Vercel Cron كل ساعة، بدل WP-Cron).
// vercel.json بيحوّل الروابط دي هنا، والمسار الأصلي في ?__p=
const { ApiError, db } = require('../lib/att/core');
const auth = require('../lib/att/auth');
const { serve } = require('../lib/att/screens');

const ROUTES = [
  ...auth.routes,
  ...require('../lib/att/members').routes,
  ...require('../lib/att/sessions').routes,
  ...require('../lib/att/stats').routes,
  ...require('../lib/att/servants').routes,
  ['GET', /^health$/, 'public', async () => ({ plugin: 'st-mina-attendance', runtime: 'vercel' })],
];

const send = (res, status, data) => {
  res.statusCode = status;
  res.setHeader('Content-Type', 'application/json; charset=utf-8');
  res.setHeader('Cache-Control', 'no-store');
  res.setHeader('X-Robots-Tag', 'noindex, nofollow');
  res.end(JSON.stringify(data));
};
// نفس شكل WP_Error، علشان attend-app.js بيقرا message وdata.fields
const sendError = (res, e) => send(res, e.status, { code: e.code, message: e.message, data: { status: e.status, ...e.extra } });

async function api(req, res, path) {
  const method = req.method === 'HEAD' ? 'GET' : req.method;
  let params = null;
  const hit = ROUTES.find(([m, re]) => m === method && (params = path.match(re)));
  if (!hit) {
    const known = ROUTES.some(([, re]) => re.test(path));
    return sendError(res, known ? new ApiError('rest_no_route', 'الطريقة دي مش مسموحة.', 405) : new ApiError('rest_no_route', 'المسار ده مش موجود.', 404));
  }
  const [, , perm, handler] = hit;
  const ctx = { req, res, q: req.query || {}, body: req.body && typeof req.body === 'object' ? req.body : {}, params: params.slice(1) };

  if (perm === 'staff') {
    ctx.staff = await auth.currentStaff(req);
    if (!ctx.staff) throw new ApiError('rest_not_logged_in', 'لازم تسجّل دخول كخادم.', 401);
    // الحماية من CSRF: موقع تاني مايقدرش يبعت رأس مخصّص من غير إذن CORS، والـ cookie كمان SameSite=Lax
    if (method !== 'GET' && req.headers['x-stmina'] !== '1') throw new ApiError('rest_forbidden', 'الطلب ده مش من شاشات الحضور.', 403);
  }
  const out = await handler(ctx);
  // [201, بيانات] للإضافة، وأي حاجة تانية 200
  if (Array.isArray(out) && out.length === 2 && typeof out[0] === 'number') return send(res, out[0], out[1]);
  return send(res, 200, out === undefined ? null : out);
}

module.exports = async (req, res) => {
  const path = String((req.query && req.query.__p) || req.url.split('?')[0]);
  if (req.query) delete req.query.__p;
  try {
    if (path.startsWith('/api/cron/autoclose')) {
      // Vercel بيبعت CRON_SECRET في الرأس، فمحدش من بره يقدر يشغّلها
      if (!process.env.CRON_SECRET || req.headers.authorization !== `Bearer ${process.env.CRON_SECRET}`) return send(res, 401, { ok: false });
      const [r] = await db()`select autoclose() as closed`;
      await db()`delete from login_attempts where at < now() - interval '1 day'`; // محاولات الدخول القديمة مالهاش لازمة
      return send(res, 200, { ok: true, closed: r.closed });
    }
    if (path.startsWith('/api/v1/')) return await api(req, res, path.slice('/api/v1/'.length).replace(/\/$/, ''));
    if (await serve(req, res, path)) return;
    send(res, 404, { code: 'not_found', message: 'الصفحة دي مش موجودة.' });
  } catch (e) {
    if (e instanceof ApiError) return sendError(res, e);
    console.error(e);
    sendError(res, new ApiError('stmina_error', 'حصلت مشكلة. جرّب تاني.', 500));
  }
};
