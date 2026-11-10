// المخدومين والملاحظات والكارت والاستيراد من Excel. نفس inc/members.php وinc/rest.php وinc/import.php.
const { db, ApiError, invalid, notFound, digits, phone: cleanPhone, name: cleanName, newToken, reqService, cardUrl } = require('./core');
const { memberLogs, stats, away, myPage, memberByToken } = require('./stats');

// المخدوم بأوقات بتوقيت القاهرة زي WordPress
const MEMBER_COLS = db => db`m.id, m.full_name, m.phone, m.qr_token, m.status, m.card_sent_at, m.pin_hash,
  cairo_ts(m.registered_at) as registered_at`;

// شكل المخدوم في الرد. الكود للخدام بس لأن المسارات دي كلها للخدام
async function memberJson(req, m) {
  const [r] = await db()`select exists (select 1 from records where member_id = ${m.id}) as has`;
  return {
    id: Number(m.id), full_name: m.full_name, phone: m.phone, status: m.status, registered_at: m.registered_at,
    qr_token: m.qr_token, card_url: cardUrl(req, m.qr_token), card_sent: !!m.card_sent_at,
    // الحذف النهائي متاح دايمًا لأي خادم، وhas_records علشان الواجهة تنبّه (قرار المستخدم 10 أكتوبر 2026)
    can_delete: true, has_records: r.has,
  };
}

// مخدوم واحد في خدمة، أو 404
async function getMember(id, service) {
  const [m] = await db()`select ${MEMBER_COLS(db())} from members m
    join member_service ms on ms.member_id = m.id and ms.service_id = ${service.id} where m.id = ${Number(id) || 0}`;
  if (!m) throw notFound('المخدوم ده مش موجود.');
  return m;
}

// التحقق من الاسم والموبايل. required: الإضافة لازم الاتنين، والتعديل اللي اتبعت بس
async function validate(data, required, exceptId = 0) {
  const errors = {}, clean = {};
  if (required || 'full_name' in data) {
    const n = cleanName(data.full_name);
    if (!n) errors.full_name = 'اكتب الاسم.';
    else if (n.split(' ').length < 2) errors.full_name = 'اكتب الاسم بالكامل، اسمين على الأقل.';
    else clean.full_name = n;
  }
  if (required || 'phone' in data) {
    const raw = String(data.phone ?? ''), p = cleanPhone(raw);
    if (!raw.trim()) errors.phone = 'اكتب رقم الموبايل.';
    else if (!p) errors.phone = 'رقم الموبايل لازم يكون مصري 11 رقم، زي 01012345678.';
    else {
      const [other] = await db()`select full_name from members where phone = ${p} and id <> ${exceptId}`;
      if (other) errors.phone = `الرقم ده متسجّل قبل كده لـ ${other.full_name}.`;
      else clean.phone = p;
    }
  }
  if ('status' in data) {
    if (['active', 'stopped'].includes(data.status)) clean.status = data.status;
    else errors.status = 'الحالة لازم تكون active أو stopped.';
  }
  if (Object.keys(errors).length) throw invalid(errors);
  return clean;
}

// إضافة مخدوم لخدمة، والربط بالخدمة في نفس العملية
async function createMember(data, service, staffId) {
  const c = await validate(data, true);
  try {
    return await db().begin(async t => {
      const [m] = await t`insert into members (full_name, phone, qr_token, created_by) values (${c.full_name}, ${c.phone}, ${newToken()}, ${staffId}) returning id`;
      await t`insert into member_service (member_id, service_id) values (${m.id}, ${service.id})`;
      return m.id;
    });
  } catch (e) {
    // غالبًا طلبين في نفس اللحظة بنفس الرقم، والقيد الفريد رفض التاني
    if (e.code === '23505') throw new ApiError('stmina_db', 'ماقدرناش نحفظ المخدوم. جرّب تاني.', 409);
    throw e;
  }
}

// مخدومين الخدمة بالاسم، بفلتر الحالة والبحث (الاسم بالنص، والموبايل بالأرقام بس)
async function listMembers(service, status = '', search = '') {
  const d = digits(search);
  const esc = s => s.replace(/[\\%_]/g, c => '\\' + c);
  return db()`select ${MEMBER_COLS(db())} from members m
    join member_service ms on ms.member_id = m.id and ms.service_id = ${service.id}
    where true
      ${['active', 'stopped'].includes(status) ? db()`and m.status = ${status}` : db()``}
      ${search ? db()`and (m.full_name ilike ${'%' + esc(search) + '%'} or m.phone like ${d ? '%' + esc(d) + '%' : '-'})` : db()``}
    order by m.full_name asc`;
}

async function notes(memberId) {
  const rows = await db()`select n.id, n.body, coalesce(s.display_name, '') as author, cairo_ts(n.created_at) as created_at
    from notes n left join staff s on s.id = n.author_id where n.member_id = ${memberId} order by n.created_at desc, n.id desc`;
  return rows.map(n => ({ ...n, id: Number(n.id) }));
}

// الاستيراد: نفس فحص "إضافة مخدوم" لكل صف، والرقم المكرر جوه الملف كمان. commit = false معاينة بس
async function importRows(req, service, rows, commit, staffId) {
  const out = [], added = [], seen = {};
  for (const [i, row] of (Array.isArray(rows) ? rows : []).slice(0, 1000).entries()) {
    const line = Number(row?.line) || i + 1;
    const n = cleanName(row?.name), raw = String(row?.phone ?? ''), p = cleanPhone(raw);
    if (!n && !raw.trim()) continue; // صف فاضي
    // اتظبط لوحده: الصفر اللي Excel شاله، أو +20، أو أرقام عربي
    const r = { line, name: n, phone: p || digits(raw), ok: false, why: '', fixed: !!p && p !== raw.trim() };
    if (!n) r.why = 'من غير اسم';
    else if (!raw.trim()) r.why = 'من غير موبايل';
    else if (n.split(' ').length < 2) r.why = 'الاسم كلمة واحدة، اكتبه اسمين على الأقل';
    else if (!p) r.why = `الرقم ${raw} مش رقم موبايل مصري صحيح`;
    else if (seen[p]) r.why = `الرقم مكرر، نفس رقم صف ${seen[p]}`;
    else {
      try { await validate({ full_name: n, phone: p }, true); } catch (e) {
        const f = e.extra?.fields || {};
        r.why = String(f.phone || Object.values(f)[0] || e.message).replace(/\.$/, '');
      }
    }
    if (!r.why) {
      seen[p] = line;
      r.ok = true;
      if (commit) {
        try {
          const id = await createMember({ full_name: n, phone: p }, service, staffId);
          added.push(await memberJson(req, await getMember(id, service)));
        } catch (e) { r.ok = false; r.why = e.message; }
      }
    }
    out.push(r);
  }
  return { rows: out, added };
}

const one = async ({ req, q, params: [id] }) => {
  const service = await reqService(q);
  return { service, m: await getMember(id, service) };
};

const routes = [
  ['GET', /^members$/, 'staff', async ({ req, q }) => {
    const service = await reqService(q);
    const list = await listMembers(service, q.status || '', String(q.search || '').trim());
    // النسبة والغياب ورا بعض لكل مخدوم، من استعلام واحد لسجل الكل
    const logs = await memberLogs(service.id);
    return Promise.all(list.map(async m => {
      const log = logs.get(Number(m.id)) || [];
      return { ...(await memberJson(req, m)), pct: stats(log).pct, away: away(log) };
    }));
  }],
  ['POST', /^members$/, 'staff', async ({ req, q, body, staff }) => {
    const service = await reqService(q);
    const id = await createMember({ full_name: body.full_name, phone: body.phone }, service, staff.id);
    return [201, await memberJson(req, await getMember(id, service))];
  }],
  ['POST', /^members\/import$/, 'staff', async ({ req, q, body, staff }) =>
    importRows(req, await reqService(q), body.rows, !!body.commit, staff.id)],

  ['GET', /^members\/(\d+)$/, 'staff', async ctx => { const { m } = await one(ctx); return memberJson(ctx.req, m); }],
  // التعديل والإيقاف: { full_name?, phone?, status? }
  ['PATCH', /^members\/(\d+)$/, 'staff', async ctx => {
    const { service, m } = await one(ctx);
    const data = Object.fromEntries(Object.entries(ctx.body || {}).filter(([k]) => ['full_name', 'phone', 'status'].includes(k)));
    const c = await validate(data, false, m.id);
    if (Object.keys(c).length) await db()`update members set ${db()(c)}, updated_at = now() where id = ${m.id}`;
    return memberJson(ctx.req, await getMember(m.id, service));
  }],
  // الحذف النهائي لأي خادم، وكل اللي يخصه بيتمسح معاه (foreign keys بحذف متسلسل)
  ['DELETE', /^members\/(\d+)$/, 'staff', async ctx => {
    const { m } = await one(ctx);
    await db()`delete from members where id = ${m.id}`;
    return { deleted: true };
  }],

  ['GET', /^members\/(\d+)\/notes$/, 'staff', async ctx => { const { m } = await one(ctx); return notes(m.id); }],
  ['POST', /^members\/(\d+)\/notes$/, 'staff', async ctx => {
    const { m } = await one(ctx);
    const body = String(ctx.body?.body ?? '').replace(/<[^>]*>/g, '').trim();
    if (!body) throw invalid({ body: 'اكتب الملاحظة.' }, 'اكتب الملاحظة.');
    await db()`insert into notes (member_id, author_id, body) values (${m.id}, ${ctx.staff.id}, ${body})`;
    return [201, (await notes(m.id))[0]];
  }],

  // إعادة إصدار الكارت: كود جديد، والقديم بيتسجّل كملغي، والكارت الجديد لسه مااتبعتش، والرقم السري بيتمسح
  ['POST', /^members\/(\d+)\/reissue$/, 'staff', async ctx => {
    const { service, m } = await one(ctx);
    await db().begin(async t => {
      await t`insert into revoked (token, member_id) values (${m.qr_token}, ${m.id}) on conflict (token) do update set revoked_at = now()`;
      await t`update members set qr_token = ${newToken()}, card_sent_at = null, pin_hash = null, updated_at = now() where id = ${m.id}`;
    });
    return memberJson(ctx.req, await getMember(m.id, service));
  }],
  ['POST', /^members\/(\d+)\/card-sent$/, 'staff', async ctx => {
    const { service, m } = await one(ctx);
    await db()`update members set card_sent_at = now() where id = ${m.id}`;
    return memberJson(ctx.req, await getMember(m.id, service));
  }],

  // نفس أرقام "حضوري" للخادم في ملف المخدوم، ومعاها الغياب ورا بعض وعدد التسجيل اليدوي
  ['GET', /^members\/(\d+)\/stats$/, 'staff', async ctx => {
    const { service, m } = await one(ctx);
    const log = await memberLogs(service.id, m.id);
    return { ...(await myPage(m, service.id)), away: away(log), manual: log.filter(x => x.method === 'manual' && x.status === 'present').length };
  }],

  // الكارت العام: الاسم والرابط بس، ومابيدّيش أي صلاحية لأي مسار تاني
  ['GET', /^card\/([A-Za-z0-9_-]{32})$/, 'public', async ({ req, params: [token] }) => {
    const m = await memberByToken(token);
    if (!m) throw new ApiError('stmina_bad_card', 'الرابط ده مش شغال.', 404);
    return { full_name: m.full_name, card_url: cardUrl(req, m.qr_token) };
  }],
];

module.exports = { routes, getMember };
