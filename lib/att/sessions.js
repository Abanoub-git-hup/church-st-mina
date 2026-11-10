// الجلسات والتسجيل والمسح والمسح من غير نت. نفس inc/sessions.php وinc/records.php وinc/offline.php.
// القواعد الحساسة (الإنهاء، والقفل التلقائي، والمسح، والتسجيل اليدوي) دوال SQL في 0002_attendance_rules.sql.
const { db, ApiError, invalid, notFound, KINDS, cairoToday, reqService } = require('./core');

const SESSION_COLS = db => db`s.id, s.kind, s.session_date::text as session_date, s.status, s.service_id,
  cairo_ts(s.opened_at) as opened_at, cairo_ts(s.closed_at) as closed_at`;

async function sessionJson(s) {
  const [c] = await db()`select
      count(*) filter (where status = 'present')::int as present,
      count(*) filter (where status = 'excused')::int as excused,
      count(*) filter (where status = 'absent')::int as absent
    from records where session_id = ${s.id}`;
  return { id: Number(s.id), kind: s.kind, kind_name: KINDS[s.kind] || s.kind, date: s.session_date, status: s.status,
    opened_at: s.opened_at, closed_at: s.closed_at, ...c };
}

const listSessions = service => db()`select ${SESSION_COLS(db())} from sessions s where service_id = ${service.id}
  order by s.session_date desc, s.opened_at desc, s.id desc`;

async function getSession(id, service) {
  const [s] = await db()`select ${SESSION_COLS(db())} from sessions s where id = ${Number(id) || 0} and service_id = ${service.id}`;
  if (!s) throw notFound('الجلسة دي مش موجودة.');
  return s;
}
const one = async ({ q, params: [id] }) => {
  const service = await reqService(q);
  return { service, s: await getSession(id, service) };
};

// فتح جلسة: اليوم مينفعش يكون لسه ماجاش، ومينفعش جلستين من نفس النوع في نفس اليوم
async function openSession(service, kind, date, staffId) {
  const errors = {};
  if (!KINDS[kind]) errors.kind = 'اختار نوع الجلسة الأول.';
  const valid = /^\d{4}-\d{2}-\d{2}$/.test(date) && new Date(date + 'T00:00:00Z').toISOString().slice(0, 10) === date;
  if (!valid) errors.date = 'اختار التاريخ.';
  else if (date > cairoToday()) errors.date = 'التاريخ ده لسه ماجاش. الجلسة بتتفتح يومها أو بعده.';
  if (Object.keys(errors).length) throw invalid(errors);

  const [same] = await db()`select id, status from sessions where service_id = ${service.id} and kind = ${kind} and session_date = ${date}`;
  if (same) {
    const msg = same.status === 'open' ? `فيه جلسة ${KINDS[kind]} مفتوحة في اليوم ده فعلًا.` : `جلسة ${KINDS[kind]} اليوم ده اتعملت وخلصت.`;
    throw new ApiError('stmina_duplicate', msg, 409, { session: Number(same.id) });
  }
  try {
    const [s] = await db()`insert into sessions (service_id, kind, session_date, opened_by) values (${service.id}, ${kind}, ${date}, ${staffId}) returning id`;
    return s.id;
  } catch (e) {
    // خادمين فتحوا نفس الجلسة في نفس اللحظة، والقيد الفريد رفض التاني
    if (e.code === '23505') throw new ApiError('stmina_duplicate', 'الجلسة دي اتفتحت لسه من خادم تاني.', 409);
    throw e;
  }
}

// كل مخدومين الجلسة: النشطين اللي اتسجّلوا في الخدمة يوم الجلسة أو قبله، وأي حد ليه سجل فيها
const rosterRows = s => db()`
  select m.id as member_id, m.full_name, m.qr_token, m.status as member_status, r.status, r.method,
    cairo_ts(r.recorded_at) as recorded_at, coalesce(b.display_name, '') as recorded_by,
    coalesce(u.display_name, '') as updated_by, cairo_ts(r.updated_at) as updated_at
  from members m
  join member_service ms on ms.member_id = m.id and ms.service_id = ${s.service_id}
  left join records r on r.member_id = m.id and r.session_id = ${s.id}
  left join staff b on b.id = r.recorded_by
  left join staff u on u.id = r.updated_by
  where r.id is not null or (m.status = 'active' and (m.registered_at at time zone 'Africa/Cairo')::date <= ${s.session_date}::date)
  order by m.full_name asc`;

// الشنطة: اللي شاشة المسح محتاجاه من غير نت. الاسم وكود الكارت بس، من غير أي موبايل
async function pack(s) {
  const rows = await rosterRows(s);
  return {
    session: await sessionJson(s),
    members: rows.map(x => ({ id: Number(x.member_id), full_name: x.full_name, token: x.qr_token, active: x.member_status === 'active', status: x.status, recorded_at: x.recorded_at })),
    saved_at: (await db()`select cairo_ts(now()) as t`)[0].t,
  };
}

// عملية واحدة من طابور المسح من غير نت. وقت السجل هو وقت العملية الأصلي على الموبايل
async function applyOp(op, service, staffId) {
  const out = { id: String(op?.id ?? '') };
  const [r] = await db()`select apply_op(${Number(op?.session_id) || 0}, ${service.id}, ${String(op?.type || '')},
    ${String(op?.code ?? '')}, ${Number(op?.member_id) || 0}, ${String(op?.status ?? '')}, ${String(op?.at ?? '')}, ${staffId}) as r`;
  return { ...out, ...r.r };
}

const routes = [
  ['GET', /^sessions$/, 'staff', async ({ q }) => {
    const service = await reqService(q);
    await db()`select autoclose()`; // الجلسات المنسية تخلص قبل ما القايمة تتعرض
    return Promise.all((await listSessions(service)).map(sessionJson));
  }],
  ['POST', /^sessions$/, 'staff', async ({ q, body, staff }) => {
    const service = await reqService(q);
    const id = await openSession(service, String(body.kind || ''), String(body.date || ''), staff.id);
    return [201, await sessionJson(await getSession(id, service))];
  }],
  // "زي آخر جلسة": الخدمة والنوع بس
  ['GET', /^sessions\/last$/, 'staff', async ({ q }) => {
    const service = await reqService(q);
    const [s] = await listSessions(service);
    return s ? { service: service.slug, kind: s.kind } : null;
  }],
  ['GET', /^sessions\/(\d+)$/, 'staff', async ctx => sessionJson((await one(ctx)).s)],
  // حذف جلسة مفتوحة اتفتحت بالغلط. المنتهية مابتتمسحش (داخلة في النسب)، إلا في خدمة مستخبية (الاختبارات)
  ['DELETE', /^sessions\/(\d+)$/, 'staff', async ctx => {
    const { service, s } = await one(ctx);
    if (s.status !== 'open' && !service.is_hidden) throw new ApiError('stmina_closed', 'الجلسة دي خلصت ومينفعش تتمسح.', 409);
    await db()`delete from sessions where id = ${s.id}`; // سجلاتها بتتمسح معاها (حذف متسلسل)
    return { deleted: true };
  }],

  // المسح: النتيجة دايمًا 200 وفيها result. الجلسة اللي اتمسحت أو مش موجودة = "مفيش جلسة مفتوحة"
  ['POST', /^sessions\/(\d+)\/scan$/, 'staff', async ({ q, body, params: [id], staff }) => {
    const service = await reqService(q);
    const [r] = await db()`select scan(${Number(id)}, ${String(body.code ?? '')}, ${staff.id}, ${service.id}) as r`;
    return r.r;
  }],
  // سجلات الجلسة، الأحدث الأول
  ['GET', /^sessions\/(\d+)\/records$/, 'staff', async ctx => {
    const { s } = await one(ctx);
    const rows = await db()`select record_json(r.session_id, r.member_id) as j from records r where r.session_id = ${s.id} order by r.recorded_at desc, r.id desc`;
    return rows.map(x => x.j);
  }],
  ['GET', /^sessions\/(\d+)\/roster$/, 'staff', async ctx => {
    const { s } = await one(ctx);
    return (await rosterRows(s)).map(x => ({ member_id: Number(x.member_id), full_name: x.full_name, status: x.status, method: x.method,
      recorded_at: x.recorded_at, recorded_by: x.recorded_by, updated_by: x.updated_by, updated_at: x.updated_at }));
  }],
  // إنهاء الجلسة وتسجيل الغياب
  ['POST', /^sessions\/(\d+)\/close$/, 'staff', async ctx => {
    const { service, s } = await one(ctx);
    const [r] = await db()`select close_session(${s.id}) as n`;
    return { ...(await sessionJson(await getSession(s.id, service))), absent_created: r.n };
  }],
  // التسجيل اليدوي والتصحيح
  ['PUT', /^sessions\/(\d+)\/records\/(\d+)$/, 'staff', async ({ q, body, params: [id, member], staff }) => {
    const service = await reqService(q);
    const s = await getSession(id, service);
    const [r] = await db()`select set_record(${s.id}, ${Number(member)}, ${String(body.status ?? '')}, ${staff.id}) as r`;
    if (r.r.error) throw new ApiError(r.r.error, r.r.message, r.r.status, r.r.fields ? { fields: r.r.fields } : {});
    return r.r;
  }],
  // التراجع عن تسجيل، في الجلسة المفتوحة بس
  ['DELETE', /^sessions\/(\d+)\/records\/(\d+)$/, 'staff', async ({ q, params: [id, member] }) => {
    const service = await reqService(q);
    const s = await getSession(id, service);
    if (s.status !== 'open') throw new ApiError('stmina_closed', 'الجلسة دي خلصت. التصحيح بيتعمل من تفاصيل الجلسة.', 409);
    const gone = await db()`delete from records where session_id = ${s.id} and member_id = ${Number(member)} returning id`;
    if (!gone.length) throw notFound('المخدوم ده مش متسجّل في الجلسة.');
    const [c] = await db()`select status_count(${s.id}, 'present') as n`;
    return { deleted: true, present: c.n };
  }],

  ['GET', /^sessions\/(\d+)\/pack$/, 'staff', async ctx => pack((await one(ctx)).s)],
  // المزامنة: الطابور كله في طلب واحد، وكل عملية بنتيجتها
  ['POST', /^sync$/, 'staff', async ({ q, body, staff }) => {
    const service = await reqService(q);
    const ops = (Array.isArray(body.ops) ? body.ops : []).slice(0, 500);
    const results = [];
    for (const op of ops) results.push(await applyOp(op && typeof op === 'object' ? op : {}, service, staff.id));
    return { results };
  }],
];

module.exports = { routes };
