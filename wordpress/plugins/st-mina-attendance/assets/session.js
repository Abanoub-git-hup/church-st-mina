// تفاصيل الجلسة (/attend/session/<رقم>/): كل المخدومين بحالاتهم من GET /sessions/{id}/roster،
// وتصحيح أي سجل بـ PUT /sessions/{id}/records/{member}، وإنهاء الجلسة بضغط مطوّل ثانية ونص (POST /close).
// الحالات: data-state = open أو ended، زي التصميم.
(() => {
  const { $, api, toast, esc, C, Q, sync } = window.Attend;
  const body = document.body;
  const icon = id => `<svg class="icon"><use href="#${id}"/></svg>`;
  const fTime = new Intl.DateTimeFormat('ar-EG-u-nu-latn', { hour: 'numeric', minute: '2-digit' });
  const fFull = new Intl.DateTimeFormat('ar-EG-u-nu-latn', { weekday: 'long', day: 'numeric', month: 'long' });
  const clock = s => s ? fTime.format(new Date(s.replace(' ', 'T'))) : '';
  const toDay = s => { const [y, m, d] = s.split('-').map(Number); return new Date(y, m - 1, d); };
  const today = new Date(); today.setHours(0, 0, 0, 0);

  // حالات السجل في الشاشة: present ← here، excused ← excuse (زي أسماء التصميم)
  const UI = { present: 'here', excused: 'excuse', absent: 'absent' };
  const API = { here: 'present', excuse: 'excused', absent: 'absent' };
  const ST = { here: ['حضر', 'here', 'i-check'], excuse: ['غاب بعذر', 'excuse', 'i-note'], absent: ['غاب', 'absent', 'i-x'], null: ['لسه', 'none', 'i-dash'] };

  let session = null, people = [], filter = 'all', openId = null;
  const ended = () => body.dataset.state === 'ended';
  const count = st => people.filter(p => p.st === st).length;
  const rank = p => ({ here: 0, excuse: 1 })[p.st] ?? 2;

  const fromApi = r => ({
    id: r.member_id, name: r.full_name, st: r.status ? UI[r.status] : null,
    how: r.updated_at ? 'edit' : r.method, at: clock(r.updated_at || r.recorded_at),
    by: r.updated_by || r.recorded_by || 'النظام',
  });

  async function load() {
    try {
      const [s, roster] = await Promise.all([api(`sessions/${C.id}`), api(`sessions/${C.id}/roster`)]);
      session = s;
      people = roster.map(fromApi);
    } catch (x) {
      $('#main').innerHTML = `<div class="glass sum"><p>${esc(x.status === 404 ? 'الجلسة دي مش موجودة.' : x.message)}</p></div>`;
      $('#endBar').style.display = 'none';
      return;
    }
    body.dataset.state = session.status === 'open' ? 'open' : 'ended';
    $('.d-title b').firstChild.textContent = session.kind_name + ' ';
    $('#stateTxt').textContent = session.status === 'open' ? 'مفتوحة' : 'منتهية';
    const d = toDay(session.date);
    $('#dateTxt').textContent = (d.getTime() === today.getTime() ? 'النهاردة · ' : '') + fFull.format(d);
    $('.scan-link').href = `${C.base}scan/?session=${session.id}`;
    $('.scan-link').style.display = session.status === 'open' ? '' : 'none';
    render();
  }

  // ---------- العرض ----------
  function renderSum() {
    const here = count('here'), ex = count('excuse'), rest = ended() ? count('absent') : count(null), total = people.length;
    $('#nHere').textContent = here; $('#nEx').textContent = ex; $('#nRest').textContent = rest;
    $('#restLbl').textContent = ended() ? 'غاب' : 'لسه';
    // علامات العدّ: حضر، وبعدين بعذر، وبعدين الباقي
    const order = [...people].sort((a, b) => rank(a) - rank(b));
    let h = '';
    for (let g = 0; g < Math.ceil(total / 5); g++) {
      h += '<span class="grp">' + order.slice(g * 5, g * 5 + 5).map(p => p.st === 'here' ? '<i></i>' : p.st === 'excuse' ? '<i class="ex"></i>' : '<i class="no"></i>').join('') + '</span>';
    }
    $('#tally').innerHTML = `<div class="tally big" aria-hidden="true">${h}</div>`;
    $('#sumNote').textContent = ended()
      ? `الجلسة انتهت${session.closed_at ? ' الساعة ' + clock(session.closed_at) : ''}، وكل اللي ماتسجّلوش اتسجّلوا غياب. تقدر تصحّح أي سجل.`
      : `${rest} من ${total} لسه ماتسجّلوش. لو الجلسة انتهت دلوقتي هيتسجّلوا غياب.`;
  }

  function renderFilters() {
    const total = people.length;
    const F = ended()
      ? [['all', 'الكل', total], ['here', 'حضر', count('here')], ['absent', 'غاب', count('absent')], ['excuse', 'بعذر', count('excuse')]]
      : [['all', 'الكل', total], ['none', 'لسه', count(null)], ['here', 'حضر', count('here')], ['excuse', 'بعذر', count('excuse')]];
    if (!F.some(f => f[0] === filter)) filter = 'all';
    $('#filters').innerHTML = F.map(([k, t, n]) => `<button type="button" data-f="${k}" aria-pressed="${k === filter}">${t} <b>${n}</b></button>`).join('');
  }

  const how = h => h === 'manual' ? 'يدوي' : h === 'auto' ? 'بالإنهاء' : h === 'edit' ? 'اتعدّل' : 'مسح';
  const meta = p => !p.st ? 'لسه ماتسجّلش' : `${how(p.how)} · ${p.at}`;

  function renderList() {
    const term = $('#q').value.trim();
    const list = people
      .filter(p => (filter === 'all' || (filter === 'none' ? !p.st : p.st === filter)) && (!term || p.name.includes(term)))
      .sort((a, b) => ended() ? a.name.localeCompare(b.name, 'ar') : (!!b.st === !!a.st ? a.name.localeCompare(b.name, 'ar') : (a.st ? 1 : -1)));
    $('#list').innerHTML = list.length ? list.map(p => {
      const [label, cls, ic] = ST[p.st];
      const open = p.id === openId;
      const log = !p.st ? 'لسه محدش سجّله في الجلسة دي.'
        : p.how === 'auto' ? `اتسجّل <b>غاب</b> تلقائي لما الجلسة انتهت الساعة ${p.at}.`
        : `سجّله <b>${esc(p.by)}</b> ${p.how === 'manual' ? 'يدوي' : p.how === 'edit' ? 'بتعديل' : 'بالمسح'} الساعة ${p.at}.`;
      // قبل الإنهاء، اللي لسه ماتسجّلش ممكن يتسجّل حضر أو بعذر أو غاب يدوي. وبعد الإنهاء التصحيح للكل
      return `<div class="p-row${open ? ' open' : ''}" data-id="${p.id}">
        <button class="p-pick" type="button" aria-expanded="${open}">
          <span class="av" aria-hidden="true">${esc(p.name.slice(0, 2))}</span>
          <span><span class="p-name">${esc(p.name)}${p.how === 'manual' ? '<span class="tag">يدوي</span>' : ''}</span><span class="p-meta">${meta(p)}</span></span>
          <span class="badge ${cls}">${icon(ic)}${label}</span>
        </button>
        <div class="p-edit">
          <p class="p-log">${log}</p>
          <div class="p-set" role="group" aria-label="غيّر حالة ${esc(p.name)}">
            ${['here', 'excuse', 'absent'].map(s => `<button type="button" data-set="${s}" aria-pressed="${p.st === s}">${ST[s][0]}</button>`).join('')}
          </div>
        </div>
      </div>`;
    }).join('') : `<p class="empty">${term ? 'مفيش مخدوم بالاسم ده في الجلسة.' : filter === 'none' ? 'كله اتسجّل. تقدر تنهي الجلسة.' : 'مفيش حد هنا.'}</p>`;
  }

  function renderEnd() {
    const rest = count(null);
    $('#endInfo').innerHTML = rest ? `<span><b>${rest}</b> لسه ماتسجّلوش</span><span>هيتسجّلوا غياب لما تنهي</span>` : '<span>كله اتسجّل</span>';
    $('#confirmTxt').innerHTML = rest
      ? `هيتسجّل <b>${rest} مخدوم غياب</b>، والجلسة هتقفل للمسح. مفيش تراجع بعدها، بس تقدر تصحّح أي سجل بعدين.`
      : 'كل المخدومين اتسجّلوا. الجلسة هتقفل للمسح، ومفيش تراجع بعدها.';
    $('#doneTxt').innerHTML = `<b>الجلسة انتهت${session.closed_at ? ' ' + clock(session.closed_at) : ''}.</b> المسح اتقفل، والتصحيح لسه متاح.`;
  }

  function render() { renderSum(); renderFilters(); renderList(); renderEnd(); }

  // ---------- التفاعل ----------
  $('#filters').addEventListener('click', e => { const b = e.target.closest('[data-f]'); if (!b) return; filter = b.dataset.f; openId = null; renderFilters(); renderList(); });
  $('#q').addEventListener('input', renderList);
  $('#list').addEventListener('click', async e => {
    const row = e.target.closest('.p-row');
    if (!row) return;
    const id = +row.dataset.id;
    if (e.target.closest('.p-pick')) { openId = openId === id ? null : id; renderList(); $(`.p-row[data-id="${id}"] .p-pick`)?.focus(); return; }
    const set = e.target.closest('[data-set]');
    if (!set) return;
    const p = people.find(x => x.id === id);
    if (p.st === set.dataset.set) return;
    try {
      const r = await api(`sessions/${session.id}/records/${id}`, { method: 'PUT', body: { status: API[set.dataset.set] } });
      Object.assign(p, fromApi(r));
      openId = null;
      render();
      toast(`${p.name}: ${ST[p.st][0]}`);
    } catch (x) { toast(x.message); }
  });

  // ---------- الإنهاء: تأكيد في نفس الشريط، وبعدين ضغط مطوّل ثانية ونص ----------
  const bar = $('#endBar'), hold = $('#hold');
  // الإنهاء مقفول طول ما فيه عمليات للجلسة دي على الموبايل ده لسه ماتزامنتش (المهمة 23)،
  // لأن الإنهاء بيسجّل "غاب" للي ماتسجّلوش، وهما ممكن يكونوا في الطابور. ولو عملية من موبايل تاني
  // وصلت بعد الإنهاء، السيرفر بيحوّل "غاب" لحالتها (offline.php)
  function renderBlocked() {
    const n = Q.count(+C.id);
    $('.blocked').style.display = n ? 'flex' : 'none';
    $('.blocked span').innerHTML = `فيه <b>${n === 1 ? 'عملية واحدة' : n + ' عمليات'}</b> على موبايلك لسه ماتزامنتش. شغّل النت واستنى لحد ما تتزامن، وبعدها تقدر تنهي الجلسة.`;
    hold.disabled = n > 0;
    return n;
  }
  addEventListener('stmina-queue', renderBlocked);
  addEventListener('stmina-synced', () => { if (!renderBlocked()) load(); });
  $('#askEnd').addEventListener('click', () => { bar.classList.add('confirming'); renderEnd(); if (renderBlocked()) sync(); else hold.focus(); });
  $('#cancelEnd').addEventListener('click', () => { bar.classList.remove('confirming'); $('#askEnd').focus(); });
  let holdT = 0;
  const startHold = e => {
    if (hold.disabled || hold.classList.contains('pressing')) return;
    if (e.type === 'keydown' && e.key !== ' ' && e.key !== 'Enter') return;
    e.preventDefault();
    hold.classList.add('pressing');
    holdT = setTimeout(endSession, 1500);
  };
  const stopHold = () => { clearTimeout(holdT); hold.classList.remove('pressing'); };
  hold.addEventListener('pointerdown', startHold);
  hold.addEventListener('keydown', startHold);
  ['pointerup', 'pointerleave', 'pointercancel', 'keyup', 'blur'].forEach(t => hold.addEventListener(t, stopHold));
  hold.addEventListener('click', e => e.preventDefault());

  async function endSession() {
    stopHold();
    if (renderBlocked()) return;
    hold.disabled = true;
    try {
      const s = await api(`sessions/${session.id}/close`, { method: 'POST' });
      session = s;
      people = (await api(`sessions/${session.id}/roster`)).map(fromApi);
      body.dataset.state = 'ended';
      $('#stateTxt').textContent = 'منتهية';
      $('.scan-link').style.display = 'none';
      bar.classList.remove('confirming');
      render();
      try { navigator.vibrate?.(80); } catch {}
      toast(s.absent_created ? `الجلسة انتهت، واتسجّل ${s.absent_created} غياب.` : 'الجلسة انتهت.');
    } catch (x) {
      toast(x.message);
    } finally {
      hold.disabled = false;
      renderBlocked();
    }
  }

  load();
  renderBlocked();
})();
