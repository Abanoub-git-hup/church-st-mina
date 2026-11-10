// فتح شاشات الحضور (inc/app.php: template_redirect وstmina_att_footer وملفات manifest).
// الشاشة HTML جاهزة من screens.generated.js، والإعدادات اللي السكريبت محتاجها بتتحط في {{FOOTER}}.
// الحماية هنا راحة بس (تحويل لصفحة الدخول)، والحماية الحقيقية في كل مسار في api.
const { version: VER, screens } = require('./screens.generated');
const { cardUrl } = require('./core');
const { currentStaff, inviteStaff } = require('./auth');
const { myPage, memberServiceId, memberByToken } = require('./stats');

const SVC = 'إعداد الخدام';
const PUBLIC = ['card', 'invite'];

// العربي يفضل عربي، و< و> بيتحوّلوا لرموز علشان أي اسم مايقفلش وسم <script>
const json = v => JSON.stringify(v).replace(/</g, '\\u003c').replace(/>/g, '\\u003e');

const servantHead = () => [
  '<link rel="manifest" href="/attend/manifest.webmanifest">',
  '<link rel="apple-touch-icon" href="/attend-assets/icon-192.png">',
  '<meta name="apple-mobile-web-app-capable" content="yes">',
  '<meta name="mobile-web-app-capable" content="yes">',
  '<meta name="apple-mobile-web-app-title" content="إعداد الخدام">',
].join('\n');
// من غير كود صح مفيش manifest، فالرابط الغلط مايتثبّتش
const cardHead = (req, m) => !m ? '' : [
  `<link rel="manifest" href="${cardUrl(req, m.qr_token)}manifest.webmanifest">`,
  '<link rel="apple-touch-icon" href="/attend-assets/icon-192.png">',
  '<meta name="apple-mobile-web-app-capable" content="yes">',
  '<meta name="mobile-web-app-capable" content="yes">',
  '<meta name="apple-mobile-web-app-title" content="حضوري">',
  '<meta name="apple-mobile-web-app-status-bar-style" content="black">',
].join('\n');

async function footer(req, screen, { id, token, staff }) {
  const c = { rest: '/api/v1/', base: '/attend/', screen, id: id || 0, user: staff ? staff.display_name : '', svc: SVC, admin: !!(staff && staff.is_admin) };
  let member = null;
  if (screen === 'card') {
    member = await memberByToken(token);
    c.card = member ? { name: member.full_name, url: cardUrl(req, member.qr_token), token: member.qr_token, has_pin: !!member.pin_hash } : null;
    c.me = member ? await myPage(member, await memberServiceId(member.id)) : null;
    c.sw = `/me-sw.js?v=${VER}`;
    c.swScope = '/me/';
    delete c.user;
  }
  if (screen === 'invite') {
    const u = await inviteStaff(token);
    c.invite = u ? { name: u.display_name, code: token } : null;
    delete c.user;
  }
  if (!['card', 'login', 'invite'].includes(screen)) {
    c.sw = `/attend-sw.js?v=${VER}`;
    c.swScope = '/attend/';
  }
  const tags = [`<script>window.STMINA_ATT = ${json(c)};</script>`, `<script src="/attend-assets/attend-app.js?ver=${VER}"></script>`];
  // qrcode-generator لرسم الـ QR، وjsQR لقراية الكاميرا في الآيفون
  if (['card', 'member'].includes(screen)) tags.push('<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>');
  if (screen === 'scan') tags.push('<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>');
  tags.push(`<script src="/attend-assets/${screen}.js?ver=${VER}"></script>`);
  return { html: tags.join('\n'), member };
}

const noindex = res => {
  res.setHeader('X-Robots-Tag', 'noindex, nofollow');
  res.setHeader('Cache-Control', 'no-store, max-age=0');
};

// الرابط ← الشاشة. /attend/members/12 ملف مخدوم، و/me/<الكود>/ الكارت ...
function route(p) {
  let m;
  if (/^\/attend\/?$/.test(p)) return { screen: 'sessions' };
  if ((m = p.match(/^\/attend\/members\/(\d+)\/?$/))) return { screen: 'member', id: Number(m[1]) };
  if ((m = p.match(/^\/attend\/session\/(\d+)\/?$/))) return { screen: 'session', id: Number(m[1]) };
  if ((m = p.match(/^\/attend\/invite\/([A-Za-z0-9]+)\/?$/))) return { screen: 'invite', token: m[1] };
  if (/^\/attend\/manifest\.webmanifest$/.test(p)) return { manifest: 'servant' };
  if ((m = p.match(/^\/attend\/([a-z]+)\/?$/))) return { screen: m[1] };
  if (/^\/attend-login\/?$/.test(p)) return { screen: 'login' };
  if ((m = p.match(/^\/me\/([^/]+)\/manifest\.webmanifest$/))) return { manifest: 'card', token: m[1] };
  if ((m = p.match(/^\/me\/([^/]+)\/?$/))) return { screen: 'card', token: m[1] };
  return null;
}

const ICONS = [
  { src: '/attend-assets/icon-192.png', sizes: '192x192', type: 'image/png', purpose: 'any' },
  { src: '/attend-assets/icon-512.png', sizes: '512x512', type: 'image/png', purpose: 'any' },
  // اللوجو في النص بمسافة حواليه، فينفع الموبايل يقصّه دايرة أو مربع بحواف
  { src: '/attend-assets/icon-512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
];
const APP = { lang: 'ar', dir: 'rtl', display: 'standalone', orientation: 'portrait', background_color: '#2a1c12', theme_color: '#2a1c12', icons: ICONS };

async function serve(req, res, p) {
  const r = route(p);
  if (!r) return false;
  noindex(res);

  if (r.manifest) {
    res.setHeader('Content-Type', 'application/manifest+json; charset=utf-8');
    if (r.manifest === 'servant') {
      // تطبيق الخدام بيفتح على شاشة المسح، لأنها اللي بتشتغل من غير نت
      res.end(JSON.stringify({ id: '/attend/', name: SVC, short_name: SVC, description: 'جلسات الحضور والمسح لخدمة إعداد الخدام', start_url: '/attend/scan/', scope: '/attend/', ...APP }));
      return true;
    }
    // manifest لكل مخدوم: التطبيق بيفتح على رابطه هو، فإخوات على نفس الموبايل كل واحد يثبّت صفحته
    const m = await memberByToken(r.token);
    if (!m) { res.statusCode = 404; res.end('{}'); return true; }
    const url = cardUrl(req, m.qr_token);
    res.end(JSON.stringify({ id: url, name: 'حضوري', short_name: 'حضوري', description: 'حضورك وكارتك في خدمة إعداد الخدام', start_url: url, scope: url, ...APP }));
    return true;
  }

  const staff = await currentStaff(req);
  // العامة (الكارت والدعوة) من غير تحويل. الدخول بيرجّع الخادم الداخل للجلسات، والباقي بيرجّع الزائر للدخول
  if (!PUBLIC.includes(r.screen)) {
    if (r.screen === 'login' && staff) return redirect(res, '/attend/sessions/');
    if (r.screen !== 'login' && !staff) return redirect(res, '/attend/login/');
  }
  const name = screens[r.screen] ? r.screen : 'soon';
  const f = await footer(req, name, { ...r, staff });
  const head = name === 'card' ? cardHead(req, f.member) : servantHead();
  res.setHeader('Content-Type', 'text/html; charset=utf-8');
  res.end(screens[name].replace('{{HEAD}}', () => head).replace('{{FOOTER}}', () => f.html));
  return true;
}

function redirect(res, to) {
  res.statusCode = 302;
  res.setHeader('Location', to);
  res.end();
  return true;
}

module.exports = { serve };
