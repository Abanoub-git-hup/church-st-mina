-- قواعد الحضور الحساسة كدوال SQL، كل دالة بتشتغل كعملية واحدة (كاملة أو مابتحصلش خالص).
-- نفس منطق inc/records.php وinc/sessions.php في بلجن WordPress.
-- دوال Vercel بتناديها بـ service role. والتشغيل مقفول قدام المتصفح (revoke في آخر الملف)،
-- لأن Postgres بيدّي أي دالة جديدة لـ PUBLIC، وSupabase بيعرضها كمسار /rpc/ للزوار.

-- الوقت بنفس الشكل اللي الشاشات متعوّدة عليه من WordPress: 'YYYY-MM-DD HH24:MI:SS' بتوقيت القاهرة
create function cairo_ts(t timestamptz) returns text language sql immutable as $$
  select to_char(t at time zone 'Africa/Cairo', 'YYYY-MM-DD HH24:MI:SS')
$$;

-- الكود من اللي الكاميرا قرته: رابط الكارت كامل (أي دومين، القديم أو الجديد) أو الكود لوحده
create function code_from_scan(raw text) returns text language sql immutable as $$
  select coalesce(
    substring(trim(raw) from '/me/([A-Za-z0-9_-]{32})/?(?:[?#].*)?$'),
    case when trim(raw) ~ '^[A-Za-z0-9_-]{32}$' then trim(raw) end,
    ''
  )
$$;

-- إنهاء الجلسة: "غاب" لكل مخدوم نشط في الخدمة، اتسجّل يوم الجلسة أو قبله، ومالوش سجل فيها.
-- الإنهاء مرة تانية مابيعملش حاجة. بيرجّع عدد سجلات الغياب اللي اتعملت
create function close_session(p_session bigint) returns int language plpgsql as $$
declare
  s sessions;
  created int;
begin
  select * into s from sessions where id = p_session for update;
  if not found or s.status <> 'open' then
    return 0;
  end if;
  insert into records (session_id, member_id, status, method, recorded_by, recorded_at)
  select s.id, m.id, 'absent', 'auto', null, now()
  from members m
  join member_service ms on ms.member_id = m.id and ms.service_id = s.service_id
  where m.status = 'active'
    and (m.registered_at at time zone 'Africa/Cairo')::date <= s.session_date
  on conflict (session_id, member_id) do nothing;
  get diagnostics created = row_count;
  update sessions set status = 'closed', closed_at = now() where id = s.id;
  return created;
end $$;

-- أي جلسة مفتوحة يومها خلص (قبل النهارده بتوقيت القاهرة) بتخلص لوحدها، بنفس نتيجة الإنهاء اليدوي.
-- بيشغّلها Vercel Cron كل ساعة، وكمان قايمة الجلسات والمسح قبل ما يردّوا
create function autoclose() returns int language plpgsql as $$
declare
  sid bigint;
  n int := 0;
begin
  for sid in select id from sessions where status = 'open' and session_date < cairo_today() loop
    perform close_session(sid);
    n := n + 1;
  end loop;
  return n;
end $$;

-- عدد سجلات جلسة بحالة معيّنة
create function status_count(p_session bigint, p_status text) returns int language sql stable as $$
  select count(*)::int from records where session_id = p_session and status = p_status
$$;

-- مسح كارت في جلسة. النتيجة دايمًا في result:
-- ok، dup (متسجّل قبل كده)، revoked (كارت اتعمله إعادة إصدار)، unknown، stopped، nosession
create function scan(p_session bigint, p_code text, p_staff bigint, p_service bigint) returns jsonb language plpgsql as $$
declare
  s sessions;
  code text := code_from_scan(p_code);
  m members;
  old_member bigint;
  r records;
begin
  perform autoclose(); -- مسح بعد نص الليل في جلسة امبارح = "مفيش جلسة مفتوحة"
  select * into s from sessions where id = p_session and service_id = p_service;
  if not found or s.status <> 'open' then
    return jsonb_build_object('result', 'nosession');
  end if;

  if code <> '' then
    select * into m from members where qr_token = code;
  end if;
  if m.id is null and code <> '' then
    select member_id into old_member from revoked where token = code;
    if old_member is not null then
      return jsonb_build_object('result', 'revoked', 'member',
        jsonb_build_object('id', old_member, 'full_name', (select full_name from members where id = old_member)));
    end if;
  end if;
  -- المخدوم لازم يبقى في خدمة الجلسة، وإلا الكود "مش معروف" بالنسبة للجلسة دي
  if m.id is null or not exists (select 1 from member_service where member_id = m.id and service_id = s.service_id) then
    return jsonb_build_object('result', 'unknown');
  end if;
  if m.status <> 'active' then
    return jsonb_build_object('result', 'stopped', 'member', jsonb_build_object('id', m.id, 'full_name', m.full_name));
  end if;

  -- لو خادمين مسحوا نفس الكارت في نفس اللحظة، القيد الفريد بيرفض التاني فبيبقى "متسجّل قبل كده"
  insert into records (session_id, member_id, status, method, recorded_by, recorded_at)
  values (s.id, m.id, 'present', 'scan', p_staff, now())
  on conflict (session_id, member_id) do nothing
  returning * into r;
  if r.id is not null then
    return jsonb_build_object('result', 'ok', 'member', jsonb_build_object('id', m.id, 'full_name', m.full_name),
      'recorded_at', cairo_ts(r.recorded_at), 'present', status_count(s.id, 'present'));
  end if;
  select * into r from records where session_id = s.id and member_id = m.id;
  return jsonb_build_object('result', 'dup', 'member', jsonb_build_object('id', m.id, 'full_name', m.full_name),
    'recorded_at', coalesce(cairo_ts(r.recorded_at), ''), 'status', coalesce(r.status, ''));
end $$;

-- تسجيل يدوي أو تصحيح حالة مخدوم في جلسة.
-- - مالوش سجل: سجل جديد "يدوي"، ولازم الجلسة تبقى مفتوحة والمخدوم نشط.
-- - ليه سجل: الحالة بتتغيّر، ومين عدّل وإمتى بيتحفظوا، حتى بعد الإنهاء.
-- الخطأ بيرجع {error, status, message} علشان دالة Vercel تحوّله لنفس رد WordPress
create function set_record(p_session bigint, p_member bigint, p_status text, p_staff bigint) returns jsonb language plpgsql as $$
declare
  s sessions;
  m members;
  r records;
begin
  if p_status not in ('present', 'excused', 'absent') then
    return jsonb_build_object('error', 'stmina_invalid', 'status', 400, 'message', 'الحالة لازم تكون حضر أو غاب بعذر أو غاب.',
      'fields', jsonb_build_object('status', 'اختار الحالة.'));
  end if;
  select * into s from sessions where id = p_session;
  select m2.* into m from members m2
    join member_service ms on ms.member_id = m2.id and ms.service_id = s.service_id
    where m2.id = p_member;
  if m.id is null then
    return jsonb_build_object('error', 'stmina_not_found', 'status', 404, 'message', 'المخدوم ده مش في الخدمة دي.');
  end if;

  select * into r from records where session_id = s.id and member_id = m.id for update;
  if r.id is null then
    if s.status <> 'open' then
      return jsonb_build_object('error', 'stmina_closed', 'status', 409, 'message', 'الجلسة دي خلصت. التصحيح للي ليهم سجل بس.');
    end if;
    if m.status <> 'active' then
      return jsonb_build_object('error', 'stmina_stopped', 'status', 409, 'message', 'المخدوم ده موقوف.');
    end if;
    -- اتسجّل في نفس اللحظة من خادم تاني أو بالمسح: نصحّح الحالة بدل ما نضيف
    insert into records (session_id, member_id, status, method, recorded_by, recorded_at)
    values (s.id, m.id, p_status, 'manual', p_staff, now())
    on conflict (session_id, member_id) do update
      set status = excluded.status, updated_by = p_staff, updated_at = now()
      where records.status <> excluded.status;
  elsif r.status <> p_status then
    update records set status = p_status, updated_by = p_staff, updated_at = now() where id = r.id;
  end if;
  return record_json(s.id, m.id);
end $$;

-- شكل السجل في الرد، بالاسم واسم الخادم اللي سجّل (فاضي = النظام)
create function record_json(p_session bigint, p_member bigint) returns jsonb language sql stable as $$
  select jsonb_build_object(
    'member_id', r.member_id, 'full_name', m.full_name, 'status', r.status, 'method', r.method,
    'recorded_at', cairo_ts(r.recorded_at),
    'recorded_by', coalesce(b.display_name, ''),
    'updated_by', coalesce(u.display_name, ''),
    'updated_at', cairo_ts(r.updated_at))
  from records r
  join members m on m.id = r.member_id
  left join staff b on b.id = r.recorded_by
  left join staff u on u.id = r.updated_by
  where r.session_id = p_session and r.member_id = p_member
$$;

-- عملية واحدة من طابور المسح من غير نت (inc/offline.php). نفس نتايج scan.
-- - وقت السجل هو وقت العملية الأصلي على الموبايل (بتوقيت القاهرة). الغلط أو اللي بعد دلوقتي بـ 5 دقايق بيبقى "دلوقتي".
-- - "غاب" اللي الإنهاء عمله لوحده (auto): العملية المتأخرة هي الحقيقة، فبتاخد مكانه في نفس السجل.
-- - اليدوي من الطابور بيعدّل الحالة زي التصحيح، والمسح لسجل موجود بيبقى "متسجّل قبل كده".
create function apply_op(p_session bigint, p_service bigint, p_type text, p_code text, p_member bigint,
                         p_status text, p_at text, p_staff bigint) returns jsonb language plpgsql as $$
declare
  s sessions;
  m members;
  r records;
  old_member bigint;
  code text;
  st text;
  meth text;
  ts timestamptz := now();
  who jsonb;
begin
  select * into s from sessions where id = p_session and service_id = p_service;
  if not found then
    return jsonb_build_object('result', 'nosession'); -- الجلسة اتمسحت
  end if;
  if p_at ~ '^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$' then
    begin
      ts := (p_at::timestamp at time zone 'Africa/Cairo');
      if ts > now() + interval '5 minutes' then ts := now(); end if;
    exception when others then ts := now();
    end;
  end if;

  if p_type = 'scan' then
    code := code_from_scan(p_code);
    if code <> '' then
      select * into m from members where qr_token = code;
    end if;
    if m.id is null and code <> '' then
      select member_id into old_member from revoked where token = code;
      if old_member is not null then
        return jsonb_build_object('result', 'revoked', 'member',
          jsonb_build_object('id', old_member, 'full_name', (select full_name from members where id = old_member)));
      end if;
    end if;
    st := 'present';
    meth := 'scan';
  elsif p_type = 'set' then
    select * into m from members where id = p_member;
    if p_status not in ('present', 'excused', 'absent') then
      return jsonb_build_object('result', 'invalid');
    end if;
    st := p_status;
    meth := 'manual';
  else
    return jsonb_build_object('result', 'invalid');
  end if;

  if m.id is null or not exists (select 1 from member_service where member_id = m.id and service_id = s.service_id) then
    return jsonb_build_object('result', 'unknown');
  end if;
  who := jsonb_build_object('id', m.id, 'full_name', m.full_name);
  if m.status <> 'active' then
    return jsonb_build_object('result', 'stopped', 'member', who);
  end if;

  insert into records (session_id, member_id, status, method, recorded_by, recorded_at)
  values (s.id, m.id, st, meth, p_staff, ts)
  on conflict (session_id, member_id) do nothing
  returning * into r;
  if r.id is not null then
    return jsonb_build_object('result', 'ok', 'member', who, 'status', st, 'recorded_at', cairo_ts(ts), 'late', s.status <> 'open');
  end if;

  select * into r from records where session_id = s.id and member_id = m.id for update;
  if r.method = 'auto' and r.status <> st then
    update records set status = st, method = meth, recorded_by = p_staff, recorded_at = ts where id = r.id;
    return jsonb_build_object('result', 'ok', 'member', who, 'status', st, 'recorded_at', cairo_ts(ts), 'late', true);
  end if;
  if p_type = 'set' and r.status <> st then
    update records set status = st, updated_by = p_staff, updated_at = ts where id = r.id;
    return jsonb_build_object('result', 'ok', 'member', who, 'status', st, 'recorded_at', cairo_ts(r.recorded_at));
  end if;
  return jsonb_build_object('result', 'dup', 'member', who, 'status', coalesce(r.status, ''), 'recorded_at', coalesce(cairo_ts(r.recorded_at), ''));
end $$;

-- قفل التشغيل قدام المتصفح: الدوال دي لدوال Vercel بس (service role)
revoke execute on function cairo_ts(timestamptz), code_from_scan(text), close_session(bigint), autoclose(),
  status_count(bigint, text), scan(bigint, text, bigint, bigint), set_record(bigint, bigint, text, bigint),
  record_json(bigint, bigint), cairo_today(),
  apply_op(bigint, bigint, text, text, bigint, text, text, bigint)
  from public, anon, authenticated;
