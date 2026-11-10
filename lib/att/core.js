// المشترك بين كل مسارات الحضور: الاتصال بقاعدة البيانات، وشكل الرد والأخطاء، وتنظيف البيانات.
// نفس قواعد inc/members.php وinc/sessions.php في بلجن WordPress.
const crypto = require('crypto');
const postgres = require('postgres');

// اتصال واحد لكل نسخة من الدالة. Supabase بيدّي رابط "pooler" بوضع transaction،
// وده مابيدعمش prepared statements، فـ prepare: false
let pool;
const db = () => pool || (pool = postgres(process.env.DATABASE_URL, { prepare: false, max: 1, idle_timeout: 20 }));

// خطأ بنفس شكل WP_Error في الرد: { code, message, data: { status, fields? } }، علشان attend-app.js مايتغيّرش
class ApiError extends Error {
  constructor(code, message, status = 400, extra = {}) {
    super(message);
    Object.assign(this, { code, status, extra });
  }
}
const invalid = (fields, message = 'فيه بيانات محتاجة تتصلّح.') => new ApiError('stmina_invalid', message, 400, { fields });
const notFound = (message, code = 'stmina_not_found') => new ApiError(code, message, 404);

// أنواع النشاط بأسمائها
const KINDS = { mass: 'قداس', meeting: 'اجتماع', activity: 'نشاط', service: 'خدمة' };

// الأرقام بس من النص، والأرقام العربي (٠١٢) بتتحوّل لإنجليزي
const digits = raw => String(raw ?? '').replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d)).replace(/\D/g, '');

// موبايل مصري بالشكل الموحّد 01xxxxxxxxx، أو '' لو مش صحيح.
// بيظبط لوحده: الأرقام العربي، و+20 أو 0020 أو 20 في الأول، والصفر اللي Excel بيشيله
const phone = raw => {
  let p = digits(raw).replace(/^(0020|20)(?=1\d{9}$)/, '');
  if (/^1\d{9}$/.test(p)) p = '0' + p;
  return /^01[0125]\d{8}$/.test(p) ? p : '';
};

// الاسم بعد شيل أي وسوم HTML وتنضيف المسافات (زي sanitize_text_field)
const name = raw => String(raw ?? '').replace(/<[^>]*>/g, '').replace(/[\u0000-\u001f]/g, ' ').replace(/\s+/g, ' ').trim();

// كود QR عشوائي: 32 حرف آمن في الروابط (أكتر من 190 bit عشوائية)
const newToken = () => crypto.randomBytes(24).toString('base64url').slice(0, 32);
const isToken = t => typeof t === 'string' && /^[A-Za-z0-9_-]{32}$/.test(t);

// الرقم السري: 4 أرقام بالظبط (والأرقام العربي بتتحوّل)، أو ''
const pin = raw => {
  const p = String(raw ?? '').replace(/\s+/g, '').replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));
  return /^\d{4}$/.test(p) ? p : '';
};

// أصل الموقع من الطلب (https://church-st-mina.vercel.app أو دومين الكنيسة بعدين)
const origin = req => process.env.SITE_URL || `https://${req.headers['x-forwarded-host'] || req.headers.host}`;
const cardUrl = (req, token) => `${origin(req)}/me/${encodeURIComponent(token)}/`;
const attUrl = (req, screen = '', id = 0) => `${origin(req)}/attend/${screen ? screen + '/' : ''}${id ? id + '/' : ''}`;

// اليوم الحالي بتوقيت القاهرة (Y-m-d)
const cairoToday = () => new Intl.DateTimeFormat('en-CA', { timeZone: 'Africa/Cairo', year: 'numeric', month: '2-digit', day: '2-digit' }).format(new Date());

// الخدمة من ?service= (الافتراضي "إعداد الخدام")
async function reqService(q) {
  const slug = String(q.service || 'i3dad').toLowerCase().replace(/[^a-z0-9_-]/g, '');
  const [s] = await db()`select * from services where slug = ${slug}`;
  if (!s) throw notFound('الخدمة دي مش موجودة.', 'stmina_no_service');
  return s;
}

module.exports = { db, ApiError, invalid, notFound, KINDS, digits, phone, name, newToken, isToken, pin, origin, cardUrl, attUrl, cairoToday, reqService };
