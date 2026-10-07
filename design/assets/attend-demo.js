// بيانات وهمية لنماذج نظام الحضور (المخدومون وملف المخدوم). لا تُستخدم في الموقع الحقيقي.
// 41 مخدومًا بأسماء وأرقام وهمية، و22 جلسة سابقة، وحضور ثابت لكل مخدوم (نفس النتيجة كل مرة).
(() => {
  const NAMES = ['مينا عادل','مريم سمير','بيشوي فادي','مارينا جرجس','كيرلس ناجي','دميانة عماد','أبانوب رأفت','فيرينا مجدي','يوسف صبحي','ماريا نبيل','بولا شريف','إيريني ماهر','جورج رامي','كاترين وجيه','أندرو هاني','مارتينا صفوت','ديفيد إيهاب','نانسي رمسيس','بيتر عاطف','ساندرا موريس','مايكل سامح','جوليا فايز','رامي ميلاد','سارة نصحي','فادي لطفي','هايدي ثروت','شنودة كمال','مونيكا عزيز','باسم وديع','ريتا منير','كريم أنور','نيفين بشرى','مارك زكريا','تريزا حليم','جون سليمان','لوسي يونان','إسحق فوزي','ميرنا رشدي','توماس عياد','إيفون حنا','ماثيو وليم'];
  const SESSIONS = 22;
  // رقم شبه عشوائي ثابت لكل مخدوم
  const rnd = seed => () => (seed = (seed * 9301 + 49297) % 233280) / 233280;
  // اللي محتاجين افتقاد (غابوا ورا بعض)، واللي موقوف
  const AWAY = { 26: 4, 33: 3, 38: 5 }, SUSPENDED = [40];

  const members = NAMES.map((name, i) => {
    const r = rnd(i + 7);
    const rate = .55 + r() * .43;
    // الأحدث أولًا: h حضر، e بعذر، a غاب
    const log = Array.from({ length: SESSIONS }, () => { const x = r(); return x < rate ? 'h' : x < rate + .06 ? 'e' : 'a'; });
    if (AWAY[i]) for (let k = 0; k < AWAY[i]; k++) log[k] = 'a';
    else if (log[0] === 'a') log[0] = 'h';
    const present = log.filter(x => x === 'h').length;
    const streak = log.findIndex(x => x !== 'a');
    const mobile = '01' + [0, 1, 2, 5][i % 4] + String(10000000 + Math.floor(r() * 89999999)).slice(0, 8);
    const joined = new Date(); joined.setDate(joined.getDate() - (40 + Math.floor(r() * 200)));
    return {
      id: i, name, mobile, joined, log, present,
      pct: Math.round(present / SESSIONS * 100),
      away: streak === -1 ? SESSIONS : streak,
      suspended: SUSPENDED.includes(i)
    };
  });

  // تواريخ الجلسات السابقة (الأحدث أولًا): جمعة كل أسبوع تقريبًا
  const sessionDates = Array.from({ length: SESSIONS }, (_, k) => { const d = new Date(); d.setDate(d.getDate() - 5 - k * 7); return d; });

  const fmtMobile = m => `${m.slice(0, 4)} ${m.slice(4, 7)} ${m.slice(7)}`;
  const initials = n => n.slice(0, 2);
  // رابط واتساب برسالة الكارت (الرقم بصيغة مصر الدولية)
  const cardLink = m => new URL(`attend-card.html?c=${(m.id * 7919 + 104729).toString(36)}`, location.href).href;
  const waCard = m => `https://wa.me/2${m.mobile}?text=${encodeURIComponent(`سلام ومحبة يا ${m.name.split(' ')[0]}\nده كارت حضورك في خدمة إعداد الخدام. افتحه من الرابط واحفظه صورة، وورّيه للخادم كل مرة علشان يتمسح:\n${cardLink(m)}`)}`;

  window.AttendDemo = { members, SESSIONS, sessionDates, fmtMobile, initials, waCard, cardLink };
})();
