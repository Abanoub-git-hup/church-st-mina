// حساب الحضور في مكان واحد، علشان "حضوري" وملف المخدوم ولوحة الخادم يطلعوا نفس الأرقام (docs/design.md 5.1).
// نفس inc/stats.php:
// - الجلسات اللي بتتحسب: المنتهية اللي للمخدوم فيها سجل، ومعاها المفتوحة اللي اتسجّل فيها حاضر أو بعذر.
// - النسبة = حضر ÷ (الجلسات − الغياب بعذر).
// - "ورا بعض": الحضور من الأحدث لحد أول غياب، والعذر بيتعدّى. والغياب ورا بعض (للافتقاد) بالعكس.
const { db, KINDS, cairoToday, reqService, ApiError, isToken, cardUrl } = require('./core');


// سجل مخدوم واحد، أو كل مخدومين الخدمة لو memberId = null (متجمّعين بالمخدوم)
async function memberLogs(serviceId, memberId = null) {
  const rows = await db()`
    select r.member_id, s.id as session_id, s.kind, s.session_date::text as session_date, r.status, r.method, (s.status = 'open') as is_open
    from records r join sessions s on s.id = r.session_id
    where s.service_id = ${serviceId} ${memberId ? db()`and r.member_id = ${memberId}` : db()``}
      and (s.status = 'closed' or (s.status = 'open' and r.status in ('present', 'excused')))
    order by s.session_date desc, s.opened_at desc, s.id desc`;
  if (memberId) return rows;
  const by = new Map();
  for (const r of rows) (by.get(Number(r.member_id)) || by.set(Number(r.member_id), []).get(Number(r.member_id))).push(r);
  return by;
}

// الأرقام لنوع نشاط أو للكل
function stats(log, kind = 'all') {
  const rows = kind === 'all' ? log : log.filter(x => x.kind === kind);
  const present = rows.filter(x => x.status === 'present').length;
  const excused = rows.filter(x => x.status === 'excused').length;
  const total = rows.length, base = total - excused;
  return { present, excused, total, base, pct: base > 0 ? Math.round((present / base) * 100) : null };
}

// الحضور ورا بعض من الأحدث (الغياب بعذر بيتعدّى)
function streak(log) {
  let n = 0;
  for (const x of log) { if (x.status === 'absent') break; if (x.status === 'present') n++; }
  return n;
}
// الغياب ورا بعض من الأحدث (للافتقاد). العذر بيتعدّى، والحضور بيوقف العدّ
function away(log) {
  let n = 0;
  for (const x of log) { if (x.status === 'present') break; if (x.status === 'absent') n++; }
  return n;
}
// نسبة كل شهر في آخر 6 شهور، الحالي الأول
function months(log) {
  const [y, m] = cairoToday().split('-').map(Number);
  return Array.from({ length: 6 }, (_, k) => {
    const d = new Date(Date.UTC(y, m - 1 - k, 1));
    const ym = d.toISOString().slice(0, 7);
    return { month: ym, pct: stats(log.filter(x => x.session_date.slice(0, 7) === ym)).pct };
  });
}
const kindStats = log => Object.fromEntries([['all', stats(log)], ...Object.keys(KINDS).map(k => [k, stats(log, k)])]);

// كل اللي صفحة "حضوري" محتاجاه، من غير ملاحظات ولا طريقة التسجيل ولا بيانات حد تاني
async function myPage(member, serviceId) {
  const log = await memberLogs(serviceId, member.id);
  return {
    full_name: member.full_name,
    registered_at: member.registered_at,
    kinds: kindStats(log),
    streak: streak(log),
    months: months(log),
    last: log.slice(0, 10).map(x => ({ kind: x.kind, kind_name: KINDS[x.kind], date: x.session_date, status: x.status, open: x.is_open })),
  };
}

// الخدمة الظاهرة الأولى اللي المخدوم فيها (الرابط مافيهوش خدمة)
async function memberServiceId(memberId) {
  const [r] = await db()`select ms.service_id from member_service ms join services sv on sv.id = ms.service_id
    where ms.member_id = ${memberId} order by sv.is_hidden asc, sv.id asc limit 1`;
  return r ? Number(r.service_id) : 0;
}

// المخدوم بكود الكارت (بأوقات بتوقيت القاهرة زي WordPress)، أو null
async function memberByToken(token) {
  if (!isToken(token)) return null;
  const [m] = await db()`select *, cairo_ts(registered_at) as registered_at from members where qr_token = ${token}`;
  return m || null;
}

// أول يوم في الفترة: month من أول الشهر، و3m من نفس اليوم من 3 شهور، أو '' لـ "من الأول"
function periodFrom(period) {
  const t = cairoToday();
  if (period === 'month') return t.slice(0, 8) + '01';
  if (period === '3m') {
    const [y, m, d] = t.split('-').map(Number);
    const x = new Date(Date.UTC(y, m - 1 - 3, d));
    return x.toISOString().slice(0, 10);
  }
  return '';
}

// لوحة الخادم: الجلسات المنتهية بس، والمخدومين النشطين بس، والموقوفين عددهم بس
async function dashboard(req, service, period) {
  const from = periodFrom(period);
  const sessions = await db()`select id, kind, session_date::text as session_date from sessions s
    where service_id = ${service.id} and status = 'closed' ${from ? db()`and session_date >= ${from}` : db()``}
    order by s.session_date desc, s.opened_at desc, s.id desc`;
  // سجل الكل في كل الجلسات المنتهية (من غير فلتر الفترة، علشان رسم آخر 6 شهور)
  const rows = await db()`select r.member_id, s.id as session_id, s.kind, s.session_date::text as session_date, r.status, r.method
    from records r join sessions s on s.id = r.session_id where s.service_id = ${service.id} and s.status = 'closed' order by s.session_date desc, s.opened_at desc, s.id desc`;
  const logs = new Map();
  for (const r of rows) (logs.get(Number(r.member_id)) || logs.set(Number(r.member_id), []).get(Number(r.member_id))).push(r);

  const keys = ['all', ...Object.keys(KINDS)];
  const sum = Object.fromEntries(keys.map(k => [k, { present: 0, base: 0 }]));
  const present = Object.fromEntries(sessions.map(s => [s.id, 0]));
  const active = await db()`select m.id, m.full_name, m.phone, m.qr_token from members m
    join member_service ms on ms.member_id = m.id and ms.service_id = ${service.id} where m.status = 'active' order by m.full_name asc`;
  const members = active.map(m => {
    const log = logs.get(Number(m.id)) || [];
    const inRange = from ? log.filter(x => x.session_date >= from) : log;
    const k = kindStats(inRange);
    for (const key of keys) { sum[key].present += k[key].present; sum[key].base += k[key].base; }
    const records = {};
    let manual = 0;
    for (const x of inRange) {
      records['s' + x.session_id] = x.status; // حرف قبل الرقم علشان JSON يفضل object
      if (x.status === 'present') { present[x.session_id]++; if (x.method === 'manual') manual++; }
    }
    return { id: Number(m.id), full_name: m.full_name, phone: m.phone, card_url: cardUrl(req, m.qr_token), kinds: k, manual, months: months(log), records };
  });
  const [stopped] = await db()`select count(*)::int as n from members m join member_service ms on ms.member_id = m.id and ms.service_id = ${service.id} where m.status = 'stopped'`;
  return {
    period, from, kind_names: KINDS,
    sessions: sessions.map(s => ({ id: Number(s.id), kind: s.kind, kind_name: KINDS[s.kind], date: s.session_date, present: present[s.id] })),
    overall: Object.fromEntries(keys.map(k => [k, { ...sum[k], pct: sum[k].base > 0 ? Math.round((sum[k].present / sum[k].base) * 100) : null }])),
    members, stopped: stopped.n, manual_min: 3,
  };
}

const PERIODS = ['month', '3m', 'all'];
const routes = [
  ['GET', /^dashboard$/, 'staff', async ({ req, q }) => {
    const service = await reqService(q);
    return dashboard(req, service, PERIODS.includes(q.period) ? q.period : '3m');
  }],
  // "حضوري": عامة بالكود زي الكارت، وبتاعة المخدوم ده بس
  ['GET', /^me\/([A-Za-z0-9_-]{32})$/, 'public', async ({ params: [token] }) => {
    const m = await memberByToken(token);
    if (!m) throw new ApiError('stmina_bad_card', 'الرابط ده مش شغال.', 404);
    return myPage(m, await memberServiceId(m.id));
  }],
];

module.exports = { routes, memberLogs, stats, streak, away, months, myPage, memberServiceId, memberByToken, kindStats };
