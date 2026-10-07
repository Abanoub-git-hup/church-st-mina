// بيانات وهمية لنماذج نظام الحضور (المخدومون وملف المخدوم). لا تُستخدم في الموقع الحقيقي.
// 41 مخدومًا بأسماء وأرقام وهمية، و22 جلسة سابقة، وحضور ثابت لكل مخدوم (نفس النتيجة كل مرة).
(() => {
  const NAMES = ['مينا عادل','مريم سمير','بيشوي فادي','مارينا جرجس','كيرلس ناجي','دميانة عماد','أبانوب رأفت','فيرينا مجدي','يوسف صبحي','ماريا نبيل','بولا شريف','إيريني ماهر','جورج رامي','كاترين وجيه','أندرو هاني','مارتينا صفوت','ديفيد إيهاب','نانسي رمسيس','بيتر عاطف','ساندرا موريس','مايكل سامح','جوليا فايز','رامي ميلاد','سارة نصحي','فادي لطفي','هايدي ثروت','شنودة كمال','مونيكا عزيز','باسم وديع','ريتا منير','كريم أنور','نيفين بشرى','مارك زكريا','تريزا حليم','جون سليمان','لوسي يونان','إسحق فوزي','ميرنا رشدي','توماس عياد','إيفون حنا','ماثيو وليم'];
  const SESSIONS = 22;
  // رقم شبه عشوائي ثابت لكل مخدوم
  const rnd = seed => () => (seed = (seed * 9301 + 49297) % 233280) / 233280;
  // اللي محتاجين افتقاد (غابوا ورا بعض)، واللي موقوف
  // 12: غاب 3 مرات وبينهم مرة بعذر (العذر مابيقطعش العدّ). 7: غاب مرتين بس فمش في الافتقاد
  const AWAY = { 26: 4, 33: 3, 38: 5, 12: ['a', 'e', 'a', 'a'], 7: 2 }, SUSPENDED = [40];
  // نسبة التسجيل اليدوي للي غالبًا الكارت مش معاهم
  const NOCARD = { 3: .75, 15: .6, 29: .5, 35: .4 };

  const members = NAMES.map((name, i) => {
    const r = rnd(i + 7);
    const rate = .55 + r() * .43;
    // الأحدث أولًا: h حضر، e بعذر، a غاب
    const log = Array.from({ length: SESSIONS }, () => { const x = r(); return x < rate ? 'h' : x < rate + .06 ? 'e' : 'a'; });
    if (Array.isArray(AWAY[i])) { AWAY[i].forEach((x, k) => { log[k] = x; }); log[AWAY[i].length] = 'h'; }
    else if (AWAY[i]) { for (let k = 0; k < AWAY[i]; k++) log[k] = 'a'; log[AWAY[i]] = 'h'; }
    else if (log[0] === 'a') log[0] = 'h';
    const present = log.filter(x => x === 'h').length, excused = log.filter(x => x === 'e').length;
    // التسجيل اليدوي: نادر عند الأغلب، وكتير عند اللي غالبًا مش معاهم الكارت
    const rm = rnd(i * 13 + 5), manualRate = NOCARD[i] || .03;
    const manual = log.map(x => x === 'h' && rm() < manualRate);
    // الغياب المتتالي من الأحدث: الغياب بعذر بيتعدّى (مابيتحسبش ومابيقطعش)، والحضور بيوقف العدّ
    let away = 0; for (const x of log) { if (x === 'h') break; if (x === 'a') away++; }
    const lastSeen = log.indexOf('h');
    const mobile = '01' + [0, 1, 2, 5][i % 4] + String(10000000 + Math.floor(r() * 89999999)).slice(0, 8);
    const joined = new Date(); joined.setDate(joined.getDate() - (40 + Math.floor(r() * 200)));
    return {
      id: i, name, mobile, joined, log, present, manual,
      // نفس حساب "حضوري": حضر ÷ (الجلسات − الغياب بعذر)
      pct: Math.round(present / (SESSIONS - excused) * 100),
      away, lastSeen,
      suspended: SUSPENDED.includes(i)
    };
  });

  // تواريخ الجلسات السابقة (الأحدث أولًا): جمعة كل أسبوع تقريبًا
  const sessionDates = Array.from({ length: SESSIONS }, (_, k) => { const d = new Date(); d.setDate(d.getDate() - 5 - k * 7); return d; });

  // نوع كل جلسة: اجتماع غالبًا، وقداس كل 3 جلسات، ونشاط وخدمة أحيانًا
  const sessionTypes = Array.from({ length: SESSIONS }, (_, k) => k % 3 === 1 ? 'mass' : k % 7 === 4 ? 'activity' : k % 9 === 6 ? 'service' : 'meeting');
  const TYPES = { mass: 'قداس', meeting: 'اجتماع', activity: 'نشاط', service: 'خدمة' };

  // QR وهمي ثابت لكل مخدوم وإصدار (شكل بس، مش بيتقري): مصفوفة 25×25، true = مربع غامق
  const qrCells = seed => {
    const N = 25, r = () => (seed = (seed * 9301 + 49297) % 233280) / 233280, g = [];
    const finder = (x, y) => { for (let i = 0; i < 7; i++) for (let j = 0; j < 7; j++) { const e = Math.max(Math.abs(i - 3), Math.abs(j - 3)); g[y + i][x + j] = e !== 2; } };
    for (let y = 0; y < N; y++) { g[y] = []; for (let x = 0; x < N; x++) g[y][x] = r() > .52; }
    for (let y = 0; y < 8; y++) for (let x = 0; x < 8; x++) { g[y][x] = false; g[y][N - 1 - x] = false; g[N - 1 - y][x] = false; }
    finder(0, 0); finder(N - 7, 0); finder(0, N - 7);
    return g;
  };
  // الرقم اللي في رابط الكارت، ومنه بنعرف المخدوم
  const cardCode = m => (m.id * 7919 + 104729).toString(36);
  const fromCode = c => { const n = (parseInt(c, 36) - 104729) / 7919; return members.find(m => m.id === n) || null; };

  const fmtMobile = m => `${m.slice(0, 4)} ${m.slice(4, 7)} ${m.slice(7)}`;
  const initials = n => n.slice(0, 2);
  // رابط واتساب برسالة الكارت (الرقم بصيغة مصر الدولية)
  const cardLink = m => new URL(`attend-card.html?c=${cardCode(m)}#card`, location.href).href;
  const waCard = m => `https://wa.me/2${m.mobile}?text=${encodeURIComponent(`سلام ومحبة يا ${m.name.split(' ')[0]}\nده كارت حضورك في خدمة إعداد الخدام. افتحه من الرابط واحفظه صورة، وورّيه للخادم كل مرة علشان يتمسح:\n${cardLink(m)}`)}`;

  window.AttendDemo = { members, SESSIONS, sessionDates, sessionTypes, TYPES, qrCells, cardCode, fromCode, fmtMobile, initials, waCard, cardLink };
})();
