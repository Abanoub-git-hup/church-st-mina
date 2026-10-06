# Google Stitch Prompt

> Paste everything below into Google Stitch. It can be run in two passes if needed: **Pass A = Public website**, **Pass B = Attendance app (mobile)**. Both passes must use the same Design System section.

---

## Project Context

Design the UI for the website of a Coptic Orthodox church in Egypt: **"كنيسة السيدة العذراء ومارمينا والبابا كيرلس"** (Church of the Virgin Mary, St. Mina and Pope Kyrillos). The product has two parts:
1. **A public church website** (no login) for parishioners and visitors: mass schedule, services/ministries, sermons library, news, church history and priests.
2. **A QR-based attendance app, mobile-first**, for one ministry ("إعداد الخدام" / Servants Preparation). **Servants** (staff) log in, open a session, scan members' QR cards with the phone camera, mark manual/excused attendance, end the session, and follow up on absent members. **Members** open a read-only "My Attendance" page from their QR card link, with no password.

The entire UI is **Arabic only, right-to-left (RTL)**. Audience: Egyptian Coptic families, youth and church servants, mostly on phones (minimum width 360px).

**Mood: "Candlelight".** Calm, warm, dark and reverent, like the inside of a wooden church lit by candles. **Dark warm backgrounds (never white)**, with **gold** as the light that falls only on what matters, and **warm wood** tones for depth. Elegant and modern, not flashy: no marquees, no counters, no busy animation.

## Design System

### Colors (use exactly)
- Page background: `#14100C` (warm espresso near-black)
- Surface 1 (alternate sections, footer, bottom nav): `#1D1712`
- Surface 2 (cards, inputs): `#271F17`
- Surface 3 (hover/selected): `#33281E`
- Gold primary (primary buttons, links, icons, eyebrows): `#C9A15B`
- Gold light (hover, highlighted word in a heading): `#E4C68A`
- Gold dark (pressed, quiet gold borders): `#9A7738`
- Wood dark (feature tiles, banners): `#7A5133`
- Wood mid (secondary badges, ornaments): `#A87A52`
- Wood light (text on wood): `#D9BFA0`
- Text primary (parchment): `#F3E9D8`
- Text secondary: `#C9B9A1`
- Text muted (dates, captions): `#94836D`
- Text on gold buttons: `#1A130C`
- Card border: 1px `rgba(228,198,138,0.10)`; input border: `rgba(228,198,138,0.28)`
- Inverted light surface "parchment" `#F3E9D8` with ink `#1A130C`: use ONLY for the member QR card and the "Next Mass" bento tile.
- Status colors (always paired with an icon + text label): Present `#7FB069` (check), Absent/Error `#D9604F` (×), Excused `#7FA6BF` (clock), Warning/Offline/Unsynced `#E0A043` (!), Live `#D9604F` (pulsing dot). Badges use the same color at 15% opacity as background.
- Liturgical red `#8E2A22`: ornament only (seasonal Holy Week banner), never for text.
- Rules: gold covers at most ~10% of any screen; one primary gold button per area; no pure white, no pure black; no drop shadows. Show elevation with a lighter surface plus a hairline gold border.

### Typography
- UI and headings: **IBM Plex Sans Arabic** (weights 300/400/500/600). Large headings use the **light 300 weight** for an elegant, airy look.
- Bible verses and quotes only: **Amiri** (Naskh), with the reference in small muted text.
- Scale (mobile → desktop): Display 40→72px/300; Verse 26→40px Amiri; H1 32→48px/300; H2 26→36px/400; H3 20→24px/500; Body 16px/400 with line-height 1.85; Label 14px/500; Eyebrow 13px/600 gold; Caption 12px; Stat numbers 40→56px/300.
- Use Western digits (0-9) for times, dates and percentages.

### Spacing, grid, shape
- 4px base unit: 4, 8, 12, 16, 24, 32, 48, 64, 96, 128.
- Grid: mobile 4 columns, 16px gutters and 16px side margins; tablet 8 columns, 24px gutters and 32px margins; desktop 12 columns, 24px gutters, max content width 1280px.
- Radius: 8 (badges), 14 (inputs, list rows), 22 (cards, images), 32 (bento container, bottom sheets), pill (buttons, chips, round social icons).
- Section spacing: 64px on mobile, 96–128px on desktop. Touch targets at least 44px; attendance primary actions 56px tall.

### Icons, imagery, motion
- Thin line icons (1.5px stroke, rounded) in gold.
- **Signature mark:** a small simplified **Coptic cross** glyph, used before every eyebrow label, as a section divider, and in the corner of the "Next Mass" tile.
- Photos: warm amber-lit church photography. Any text over a photo sits on a gradient from `#14100C` at 85% (text side) to transparent.
- Motion: subtle fade-up on scroll (16px, 400ms, once). Hover transitions 200ms.

## Pages to Generate

### PASS A — Public Website (generate desktop 1440px AND mobile 390px for each)

**A1. Home (الرئيسية)**
1. **Header**, transparent over the hero. Right side: circular logo + church name "كنيسة السيدة العذراء ومارمينا والبابا كيرلس". Center: nav "الرئيسية · الكنيسة ▾ · العبادة ▾ · الخدمات · المكتبة ▾ · الأخبار". Left side: round icon buttons for Facebook, YouTube, Instagram, WhatsApp, Phone. On mobile: logo + hamburger.
2. **Hero**, full-bleed warm photo of a young man praying in a candle-lit church. Text aligned right, placed in the lower third on mobile. Verse in Amiri: "فِي الْعَالَمِ سَيَكُونُ لَكُمْ ضِيقٌ، وَلكِنْ ثِقُوا: أَنَا قَدْ غَلَبْتُ الْعَالَمَ" with the reference "(يو 16: 33)". Two buttons: primary gold pill "مواعيد القداسات ←" and outline "تعرّف على الكنيسة".
3. **Bento strip**: 4 tiles joined edge to edge inside one rounded (32px) container, overlapping the bottom of the hero. (1) A church photo. (2) A dark tile with a gold quote mark and a short line: "كنيسة تجمعنا عائلةً واحدة في المسيح". (3) A photo tile with a round play button labeled "البث المباشر". (4) A **parchment** tile: "القداس القادم", "الأحد · 7:00 – 9:30 ص", the link "كل المواعيد ←", and a Coptic cross in the corner. On mobile the strip scrolls horizontally, with the "القداس القادم" tile first.
4. **Quick access**: eyebrow "اكتشف" + H2 "كنيستنا", then 4 cards with a gold line icon, title, one-line description and arrow: "جدول القداسات", "الآباء الكهنة", "نشأة الكنيسة", "صور الكنيسة".
5. **This week's schedule**: eyebrow "العبادة" + H2 "مواعيد هذا الأسبوع". Rows grouped by day (gold day name), each with time, type badge (قداس / اجتماع / اعتراف) and place. Link "الجدول الكامل ←".
6. **Our services**: eyebrow "الخدمات" + H2 "خدماتنا". Group chips "الكل · مدارس الأحد · الاجتماعات · الخدام · الفرق والأنشطة · الحضانة", then service cards (4:3 photo, name, group badge, clock + time). Sample services: مدارس الأحد – ابتدائي، فريق الكشافة، كورال ترينتي، فريق المسرح، اجتماع الشباب، الحضانة.
7. **Latest sermons**: eyebrow "المكتبة" + H2 "أحدث العظات". Three cards, each with a round gold media-type icon (audio/video/PDF), title, speaker "أبونا …", topic badge, date and duration.
8. **Verse band**: full-width wood-dark (`#7A5133`) band with a large centered Amiri verse.
9. **Latest news**: eyebrow "الأخبار" + H2 "آخر الأخبار". Three cards with photo, badge (خبر / إعلان), title, date and excerpt.
10. **Footer** on Surface 1: logo + short description; quick links; contact (phone, address); round social icons. A bottom bar with copyright and a small muted link "دخول الخدام".

**A2. Church – History (النشأة والتاريخ):** page title over a photo, a comfortable reading column (max 720px) with inline photos, and an optional simple timeline.
**A3. Church – Priests (الآباء الكهنة):** a grid of priest cards (1:1 photo with thin gold frame, name, title): 2 columns on mobile, 4 on desktop.
**A4. Church – Gallery (صور الكنيسة):** masonry gallery, plus a full-screen dark lightbox state.
**A5. Church – Location (الموقع):** a large dark-styled map card, the address, and a button "افتح في الخرائط".
**A6. Worship (العبادة):** a live-stream banner (pulsing "مباشر" badge, title, "شاهد الآن", or an embedded 16:9 video), the full schedule grouped by day (on mobile, one card per day) covering قداسات, اجتماعات and اعتراف, and an external-link card "القراءات اليومية" with a "يفتح في نافذة جديدة" icon.
**A7. Services (الخدمات):** group chips, then for each of the 5 groups a section header plus a grid of service cards. Services without a photo show a Surface-2 tile with a large gold line icon.
**A8. Service detail (صفحة الخدمة):** wide photo with the service name and group badge; an info block of 4 icon rows: الوصف, المواعيد, المسؤول, التواصل (with round Call and WhatsApp buttons); a longer description; "خدمات أخرى في المجموعة".
**A9. Sermons archive (العظات):** filter bar (المتحدث / الموضوع / التاريخ + "مسح التصفية"; on mobile a "تصفية" button opens a bottom sheet with an active-filter count), a results count, a sermon card list and pagination.
**A10. Sermon detail:** title, speaker and date; an audio player (big gold play button, progress bar, time, speed, download) OR a 16:9 video OR a PDF file card; description; "عظات أخرى لنفس المتحدث".
**A11. Media (الميديا)** with chips صور / فيديو / ترانيم; **A12. Bulletins (النشرات)** as a list of PDF file cards (icon, title, date, size, "تحميل").
**A13. News (الأخبار):** a seasonal feature banner (e.g. "أسبوع الآلام" with a thin liturgical-red ornament), chips الكل / أخبار / إعلانات, a news card grid and pagination. **A14. News detail.**
**A15. 404 page:** a short verse and the button "العودة للرئيسية".

### PASS B — Attendance App (mobile 390px first; same dark design system)

Common to all servant screens: a top app bar (back button, title, sync indicator on the left: green dot "متزامن" / amber dot with count "3 لم تُزامَن" / grey "بدون إنترنت"), and a bottom navigation with 5 items: "الجلسات", "المخدومون", a prominent raised round gold **"مسح"** button in the center, "الافتقاد", "المزيد".

**B1. Servant login (دخول الخدام):** logo at the top, title "دخول الخدام", fields "البريد أو الموبايل" and "كلمة السر" (with a show toggle), primary button "دخول". Also show an error state, and a rate-limit state with the message "محاولات كثيرة، حاول بعد 15 دقيقة".
**B2. Sessions (الجلسات):** primary button "فتح جلسة", open-session cards (service "إعداد الخدام", activity badge, date, a pulsing gold dot "مفتوحة", "حضر 23"), and a list of finished sessions (grey "منتهية").
**B3. Open session (فتح جلسة):** a service select (pre-selected "إعداد الخدام"), an activity-type segmented control "قداس · اجتماع · نشاط · خدمة", a date field (today), the link "نسخ من آخر جلسة", and primary button "فتح الجلسة".
**B4. Scanner (المسح):** full-screen camera with a centered square frame with gold corners; the session name on top; a live counter "حضر 23 من 41" with a gold progress bar; bottom buttons "تسجيل يدوي" and a flashlight icon. Show **5 result states** as a bottom card: ✓ green "تم تسجيل: مينا عادل"; ! amber "سُجل سابقًا: مينا عادل"; × red "هذا الكارت ملغي"; × red "كارت غير معروف"; ! amber "لا توجد جلسة مفتوحة" with the button "افتح جلسة". Also show an offline variant with an amber top banner: "أنت بدون إنترنت، العمليات تُحفظ على جهازك (3)".
**B5. Manual registration (تسجيل يدوي):** an auto-focused search field, a result list (initial avatar, name, a check if already recorded), and a bottom sheet with 3 large buttons "حضر" / "غاب" / "غاب بعذر".
**B6. Session detail:** header with service, activity, date and status; 3 stat chips (حضر / بعذر / لم يُسجَّل); tabs "المسجَّلون / لم يُسجَّلوا"; record rows (name, status badge, a small "يدوي" badge, time, recorder name); a sticky bottom button "إنهاء الجلسة"; a small red link "حذف الجلسة". Show the end-session confirmation dialog: "سيُسجَّل 18 مخدومًا غائبًا. لا يمكن التراجع." with a red "إنهاء الجلسة" button and "إلغاء". Also show the disabled state: "لا يمكن الإنهاء قبل مزامنة 3 عمليات".
**B7. Members (المخدومون):** a search field, chips "نشط / متوقف", member rows (avatar initial, name, attendance %), and a floating "+" button with the options "إضافة مخدوم" and "استيراد من Excel".
**B8. Add member:** fields "الاسم الكامل", "رقم الموبايل", and "ملاحظات الخدام" (with a lock icon and the hint "تظهر للخدام فقط"); a "حفظ" button; then a success sheet asking "أرسل الكارت الآن؟" with a WhatsApp-icon button.
**B9. Member profile:** name, status badge and join date; round action buttons (أرسل الكارت / اتصال / واتساب); per-activity percentages; a monthly bar chart; "تسجيلات يدوية: 2"; recent sessions; servant notes; a danger zone with "إعادة إصدار الكارت" and "إيقاف".
**B10. Excel import:** a dashed gold dropzone "اختر ملف Excel (عمودان: الاسم، الموبايل)" with the link "حمّل نموذجًا", then a report: "أُضيف 40 · رُفض 3" and a table of rejected rows with reasons.
**B11. Dashboard (اللوحة):** an activity segmented control with "الكل", 3 stat cards (متوسط الحضور 78%، النشطون 41، جلسات الشهر 8), a monthly gold bar chart, and a sortable member table with percentages.
**B12. Follow-up (الافتقاد):** the subtitle "من غاب 3 جلسات متتالية أو أكثر" with the link "تغيير", and rows showing name, red badge "غاب 4 مرات متتالية", "آخر حضور: 12 سبتمبر", and round Call and WhatsApp buttons. Include an empty state: "لا أحد يحتاج افتقادًا الآن".
**B13. More (المزيد):** "رسالة الخدام" textarea with a live preview; the follow-up threshold stepper (− 3 +); servants management (list, "إضافة خادم", "سحب الصلاحية"); "تسجيل الخروج".

**Member screens (no login, read-only, NO edit controls anywhere):**
**B14. My Attendance (حضوري):** logo and member name; a wood-toned servant message banner ("رسالة من الخدام: …"); a large gold progress ring "85%" with the segmented control "الكل · قداس · اجتماع · نشاط · خدمة"; a streak counter with a candle icon "6 جلسات متتالية"; a monthly bar chart; the last 10 sessions (date, type, status badge, no "يدوي" badge); a "كارتي" button; and an install card "ثبّت صفحتك على الشاشة الرئيسية" with "تثبيت" / "لاحقًا".
**B15. My Card (كارتي):** a 9:16 **parchment** card (designed to be saved as a phone image): church logo, church name, member name, a large dark QR code on a light background with a quiet zone, and "إعداد الخدام". Below the card, the button "احفظ الكارت كصورة".
**B16. Invalid link:** "هذا الرابط لم يعد صالحًا. اطلب كارتًا جديدًا من الخادم." Show no personal data.

## Reusable Components
Buttons (primary gold pill / outline gold / ghost / danger red; states: default, hover, pressed, focus with gold ring, disabled, loading); pill button with a round arrow circle on its left end; round icon buttons (40/48px); arrow text link "اعرف المزيد ←"; eyebrow (Coptic cross + gold label); section header (eyebrow + H2 + "عرض الكل ←"); badges (neutral, gold, wood, present, absent, excused, live, manual); filter chips; segmented control; text, phone, password and search inputs (default, focus, filled, error with message, disabled); select/date fields opening as bottom sheets on mobile; base card (Surface 2, 22px radius, hairline gold border, lighter on hover); dialog / bottom sheet; toast (success, error, warning, info); empty state; skeleton loader; pagination (RTL order); breadcrumb; stat card with progress ring; monthly bar chart; scan result card; sync indicator; offline banner; member row; record row; follow-up row; stepper; dropzone; QR member card.

## Responsive Requirements
- **Mobile (360–767px), the priority:** hamburger menu opening a full-height panel from the right; hero at 78% of the viewport height with the verse in the lower third and stacked full-width buttons; bento as a horizontal scroll strip; single-column cards (2 columns for priests and gallery); home services and sermons scroll horizontally; schedule shown as day cards; filters in a bottom sheet; attendance primary actions sticky in the thumb zone; **no horizontal page scroll at 360px**.
- **Tablet (768–1199px):** bento 2×2; 2–3 column grids.
- **Desktop (1200px and up):** full horizontal nav with dropdowns; hero at 88% of the viewport height; bento as one row of 4 overlapping the hero by 64px; 3–4 column grids. The attendance app uses a centered 560px column for scan and register screens, a full-width table for the dashboard and members, and the bottom nav becomes a right sidebar.

## Style References
- **Layout and mood:** a premium church landing page with a large light-weight headline over a golden, sun-lit church interior; a 4-tile bento row under the hero (photo / dark quote tile / photo with play button / light event tile with "Learn more ↗" and a star mark → **we replace the star with a Coptic cross**); round social icons in the top corner. Recreate this feeling, mirrored for RTL.
- **Section structure:** a modern church theme style: a small colored eyebrow line with an icon above each heading, pill buttons with a round arrow, generously rounded image corners, alternating sections. **Do not** copy its bright orange color, its scrolling text marquees or its animated counters.
- **Content origin:** the church's own previous homepage: a verse over a warm photo, then 4 white cards (نشأة الكنيسة، صور الكنيسة، الآباء الكهنة، جدول القداسات). Keep the idea; restyle it in the dark candlelight system.
- Overall: dark, warm, quiet, golden light. It should feel like evening prayer, not a marketing site.
