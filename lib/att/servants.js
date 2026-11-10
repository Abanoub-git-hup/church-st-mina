// إدارة الخدام (inc/servants.php): القايمة لكل الخدام، والإضافة وسحب الصلاحية لمدير الموقع بس.
// الخادم الجديد بيتعمل من غير كلمة سر، ومعاه رابط دعوة بيشتغل 3 أيام ومرة واحدة.
// سحب الصلاحية بيقفل كل جلسات دخوله فورًا (token_version)، والحساب بيفضل علشان السجلات تفضل باسمه.
const { db, ApiError, invalid, phone: cleanPhone, name: cleanName } = require('./core');
const { newInvite } = require('./auth');

const manageOnly = staff => {
  if (!staff.is_admin) throw new ApiError('rest_forbidden', 'إضافة الخدام وسحب الصلاحية لمدير الموقع بس.', 403);
};

// الدعوة المعلّقة: اتعمل ولسه ماعملش كلمة السر
const servantJson = (u, me) => ({
  id: Number(u.id), name: u.display_name, phone: u.phone || '', me: Number(u.id) === Number(me.id), admin: u.is_admin,
  pending: !!u.invite_hash, expired: !!u.invite_expires_at && new Date(u.invite_expires_at) < new Date(),
});

const routes = [
  // القايمة: المدير الأول وبعدين بالاسم. حسابات الاختبار مابتظهرش، فماتتسحبش صلاحيتها بالغلط
  ['GET', /^servants$/, 'staff', async ({ staff }) => {
    const rows = await db()`select * from staff where active and (not is_test or id = ${staff.id}) order by is_admin desc, display_name asc`;
    return { servants: rows.map(u => servantJson(u, staff)), can_manage: staff.is_admin };
  }],

  // إضافة خادم: الاسم والموبايل، والرد فيه رابط الدعوة
  ['POST', /^servants$/, 'staff', async ({ req, body, staff }) => {
    manageOnly(staff);
    const n = cleanName(body.name), p = cleanPhone(body.phone), fields = {};
    if (!n) fields.name = 'اكتب اسم الخادم.';
    if (!p) fields.phone = 'رقم الموبايل لازم يكون 11 رقم ويبدأ بـ 010 أو 011 أو 012 أو 015.';
    const [old] = p ? await db()`select * from staff where phone = ${p}` : [];
    if (old && old.active) fields.phone = `الرقم ده لـ${old.display_name}، وهو خادم بالفعل.`;
    if (Object.keys(fields).length) throw invalid(fields, Object.values(fields)[0]);
    // كان خادم واتسحبت صلاحيته: نفس الحساب يرجع (السجلات القديمة باسمه)، بدعوة جديدة
    const [u] = old
      ? await db()`update staff set active = true, display_name = ${n} where id = ${old.id} returning id`
      : await db()`insert into staff (display_name, phone) values (${n}, ${p}) returning id`;
    const url = await newInvite(req, u.id);
    const [fresh] = await db()`select * from staff where id = ${u.id}`;
    return [201, { ...servantJson(fresh, staff), invite_url: url }];
  }],

  // دعوة جديدة لخادم لسه ماقبلش. الدعوة القديمة بتبطل
  ['POST', /^servants\/(\d+)\/invite$/, 'staff', async ({ req, params: [id], staff }) => {
    manageOnly(staff);
    const [u] = await db()`select * from staff where id = ${Number(id)} and active and invite_hash is not null`;
    if (!u) throw new ApiError('stmina_no_invite', 'الخادم ده مش مستني دعوة.', 404);
    const url = await newInvite(req, u.id);
    const [fresh] = await db()`select * from staff where id = ${u.id}`;
    return { ...servantJson(fresh, staff), invite_url: url };
  }],

  // سحب الصلاحية: مش لنفسك ولا لمدير الموقع ولا حساب اختبار
  ['DELETE', /^servants\/(\d+)$/, 'staff', async ({ params: [id], staff }) => {
    manageOnly(staff);
    const [u] = await db()`select * from staff where id = ${Number(id)} and active`;
    if (!u) throw new ApiError('stmina_not_servant', 'الحساب ده مش خادم.', 404);
    if (Number(u.id) === Number(staff.id) || u.is_admin || u.is_test) {
      throw new ApiError('stmina_cant_revoke', 'مينفعش تسحب صلاحية نفسك ولا صلاحية مدير الموقع ولا حساب اختبار.', 400);
    }
    await db()`update staff set active = false, invite_hash = null, invite_expires_at = null, token_version = token_version + 1 where id = ${u.id}`;
    return { id: Number(u.id), revoked: true };
  }],
];

module.exports = { routes };
