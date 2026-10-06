# Google Stitch Prompt

> Paste everything below into Google Stitch to generate the **remaining pages**. The home page is already built as `design/home.html` and is the visual reference. Generate in two passes if needed: **Pass A = public website inner pages**, **Pass B = attendance app (mobile)**. Both passes must use the Design System section exactly.

---

## Project Context

UI for the website of a Coptic Orthodox church in Egypt: **"كنيسة السيدة العذراء ومارمينا والبابا كيرلس السادس"** (Church of the Virgin Mary, St. Mina and Pope Kyrillos VI), Arab El-Ayayda, El-Gabal El-Asfar, Diocese of Shebin El-Qanater. The fathers: **القمص كيرلس روماني، القس أرسانيوس عزت، القس فام عبد المسيح**.

Two parts:
1. **Public church website** (no login): worship schedule, services, sermons, news, church history and priests.
2. **QR attendance app, mobile-first**, for the "إعداد الخدام" ministry. Servants log in, open sessions, scan QR cards, and follow up. Members open a read-only "حضوري" page from their card link.

**Arabic only, right-to-left (RTL)**. Mostly used on phones (from 360px wide).

**Mood: "morning light and candlelight inside a wooden church".** Warm amber-brown surfaces (never white, never black). Golden light enters as if through a window. Soft incense haze. Circles are the signature shape: photo circles and soft golden light orbs that float slowly. Thin, airy headlines. Calm, slow, reverent motion.

## Design System (use exactly)

**Colors**
- Page background `#2F2016`; deeper surfaces `#271A11`, `#1E140D`, `#170F09` (footer, dark quote circle); raised cards `#3B291C`.
- Gold `#E0B060` (eyebrows, icons, gold buttons, "today" marker); soft gold `#F3C987` (highlighted word in titles, gold text over photos); deep gold `#B8873F` (gold on light surfaces).
- Text cream `#F6ECDC`; secondary `#DCCAB1`; muted `#A8927A` (dates, hints); ink `#1E140D` (text on light surfaces).
- Lines `rgba(246,236,220,.09)`; strong lines `rgba(246,236,220,.2)`; section light pools `rgba(243,201,135,.16)`.
- Light orbs (circles without photos): radial gradient from pale cream to soft gold to transparent amber, opacity about 0.7.
- Status colors (always with an icon and a text label): present `#7FB069`, absent/error `#D9604F`, excused `#7FA6BF`, warning/unsynced `#E0A043`, live `#E5634F`.
- All photos get a warm grade (sepia 0.4, brightness 0.82). Photos with white backgrounds sit on a cream radial surface with multiply blending.
- A very subtle film grain over the whole page. Each section has a soft golden light pool in one corner.

**Typography**
- UI and headlines: **Alexandria**. Section titles are very light (weight 200), size clamp(2rem, 4.4vw, 3.7rem), and one highlighted word in weight 400 soft gold. Body text weight 300, line-height 1.9.
- Verses and quotes: **Amiri**.
- Eyebrow above each title: a 28px gold line, then a small gold label (0.85rem, weight 500).
- Western digits (0–9) for times and dates.

**Spacing and shape**
- 4px base: 4, 8, 12, 16, 20, 24, 32, 40, 48, 64, 80, 96, 128. Sections are 96px apart on mobile and 128px on desktop.
- Radius: 10, 16, 24, 32, full. Max content width 1280px; side gutter 16px on mobile, 32px from 768px.

**Buttons and links**
- Primary: cream pill, 52px tall, with a dark inner circle holding an arrow that rotates 45° on hover.
- Gold pill variant.
- Glass pill: translucent cream with a blurred backdrop.
- Text link: underlined, with a ↖ arrow.
- Ring button: a 150px circle with a thin gold border and a centered label.

**Motion**
- Easing cubic-bezier(.16,1,.3,1).
- Titles reveal word by word. Never split Arabic letters.
- Elements rise 40px and fade in, in staggered batches.
- Circles scale in from 0.6 with a slight overshoot, then float up and down 7px over 7–10s, each on a different timing.
- Big images reveal from bottom to top.
- Some sections have a fixed background photo that fades in and out as the section scrolls over it.
- Respect reduced motion.

## Pages to Generate

### PASS A — Public website inner pages (desktop 1440px AND mobile 390px)

Every inner page starts with a **short hero** (55–60% of viewport height):
- A warm-graded photo with soft window light rays and no smoke.
- A breadcrumb.
- A light Alexandria title with one gold word.
- Then the same footer as the home page: a dark rounded block with the big invitation "عندك مكان معنا في **بيت الله**", links, social circles, and a small "دخول الخدام" link.

**A1. العبادة (Worship)**
- Full schedule as clean rows with thin dividers: day name, then time slots. Today's row has a gold side line and "اليوم".
- Tabs: "الجدول المعتاد" / "جدول الصوم الكبير".
- Regular schedule:
  - الأحد: 7:00–10:00 ص، 9:00–11:00 ص.
  - الثلاثاء: 8:00–9:30 ص.
  - الأربعاء: 8:00–10:00 ص.
  - الجمعة: 7:00–9:00 ص (التربية الكنسية)، 7:00–9:30 ص (الشعب)، 10:00–12:00 ظ، and in gold 3:00–5:00 م (صلاة ودراسة الكتاب).
  - السبت: 8:00–10:00 ص.
- Live stream: a large photo circle with a gold play button and a pulsing ring, labeled "البث المباشر على صفحة الكنيسة".
- Daily readings external link: "القراءات اليومية ↖".
- Confession times block.

**A2. الخدمات (Services)**
- Big floating circles for the 6 groups (a text circle with the group name and count, next to a photo circle, with golden light orbs between them). Clicking a group filters the grid below.
- Groups: مدارس الأحد (3 مراحل)، الاجتماعات (5)، الخدام (2)، الفرق والأنشطة (4)، الحضانة، الأسرة والمجتمع (جديد).
- Then a grid of 4:5 photo cards: group label, service name, round arrow button. Some cards carry a dashed "صورة مؤقتة" tag; new ones carry a cream "جديد" tag.

**A3. صفحة الخدمة (Service detail)**
- Hero with the service photo and group label.
- Four info circles with icons: الوصف، المواعيد، المسؤول، التواصل (round call and WhatsApp buttons).
- A cluster of photo circles from the service.
- "خدمات أخرى في المجموعة".

**A4. الكنيسة: النشأة (History)**
- Reading column (max 720px) with arch-topped photos between paragraphs.
- A vertical timeline with gold dots.

**A5. الكنيسة: الآباء (Fathers)**
- One large photo circle per father, with name and title (كاهن الكنيسة).
- A small quotes slider for each: the center poster is large and its neighbors are smaller and dimmed.

**A6. الكنيسة: الصور (Gallery)**
- Photo-circle clusters grouped by event: مؤتمرات، رحلات، قداسات، أنشطة.
- Each cluster is one big center circle with smaller circles around it, with no overlaps.
- Full-screen dark lightbox.

**A7. الكنيسة: الموقع (Location)**
- A dark-styled map inside an arch-topped frame.
- Address: "عرب العيايدة، ناحية الجبل الأصفر".
- Button "افتح في الخرائط".

**A8. المكتبة: العظات (Sermons archive)**
- Filters: المتحدث / الموضوع / التاريخ. On mobile, a "تصفية" button opens a bottom sheet with an active-filter count.
- Sermon cards: a gold circle with the media type (audio, video, PDF), title, speaker, date. Pagination.

**A9. صفحة العظة (Sermon detail)**
- Audio player with a big gold play button, progress bar, speed and download, OR a 16:9 video, OR a PDF card.
- Description, then more sermons by the same speaker.

**A10. الأخبار (News)**
- Seasonal banner.
- Tabs: الكل / أخبار / إعلانات.
- The three top items as large photo circles with the title and date inside; the rest as 16:11 cards.
- Pagination.

**A11. صفحة الخبر (News detail)**
- The full poster or photo, the text, then related news.

**A12. 404**
- A short Amiri verse with a candle (CSS flame with a soft flickering glow) and the button "العودة للرئيسية".

### PASS B — Attendance app (mobile 390px first)

Same colors and fonts, but **no decorative motion**. Background `#271A11` with no photos. Primary actions are 56px tall in the thumb zone.

**Servant screens** share:
- A top bar: back button, title, and a sync indicator (green "متزامن" / amber "3 لم تُزامَن" / grey "بدون إنترنت").
- A bottom navigation: الجلسات، المخدومون، a raised gold circular **مسح** button in the middle، الافتقاد، المزيد.

- **B1. دخول الخدام:** logo, "البريد أو الموبايل", "كلمة السر" with a show toggle, cream "دخول" pill. Error state; rate-limit state "محاولات كثيرة، حاول بعد 15 دقيقة".
- **B2. الجلسات:** "فتح جلسة" pill; open-session cards (service, activity tag, date, pulsing gold dot "مفتوحة", "حضر 23"); finished sessions list.
- **B3. فتح جلسة:** service select (إعداد الخدام), segmented control قداس · اجتماع · نشاط · خدمة, date (today), link "نسخ من آخر جلسة", button "فتح الجلسة".
- **B4. المسح:** full-screen camera with a square frame with gold corners, counter "حضر 23 من 41" with a gold progress bar, buttons "تسجيل يدوي" and flashlight.
  - Five result sheets: ✓ green "تم تسجيل: مينا عادل"؛ ! amber "سُجل سابقًا"؛ × red "هذا الكارت ملغي"؛ × red "كارت غير معروف"؛ ! amber "لا توجد جلسة مفتوحة" with "افتح جلسة".
  - Offline amber banner.
- **B5. تسجيل يدوي:** auto-focused search, results with an initial avatar, and a bottom sheet with three big buttons حضر / غاب / غاب بعذر.
- **B6. تفاصيل الجلسة:** stat chips (حضر / بعذر / لم يُسجَّل), tabs, record rows (status badge, small "يدوي" badge, time, recorder), sticky "إنهاء الجلسة" button.
  - Confirmation dialog: "سيُسجَّل 18 مخدومًا غائبًا. لا يمكن التراجع."
  - Disabled state: "لا يمكن الإنهاء قبل مزامنة 3 عمليات".
- **B7. المخدومون:** search, chips نشط / متوقف, member rows with attendance %, floating "+" with "إضافة مخدوم" / "استيراد من Excel".
- **B8. إضافة مخدوم:** الاسم الكامل، رقم الموبايل، ملاحظات الخدام (lock icon, "تظهر للخدام فقط"); after saving, "أرسل الكارت الآن؟" with a WhatsApp button.
- **B9. ملف المخدوم:** status, join date, round actions (أرسل الكارت / اتصال / واتساب), percentages per activity, monthly gold bar chart, manual count, recent sessions, notes, and a danger zone (إعادة إصدار الكارت / إيقاف).
- **B10. استيراد Excel:** dashed gold dropzone, then a report "أُضيف 40 · رُفض 3" with the rejected rows and reasons.
- **B11. لوحة الخادم:** activity segmented control with "الكل", three stat tiles, a monthly chart, and a sortable member table.
- **B12. الافتقاد:** "من غاب 3 جلسات متتالية أو أكثر" with a change link; rows with name, red badge "غاب 4 مرات متتالية", "آخر حضور", and round call and WhatsApp buttons; empty state "لا أحد يحتاج افتقادًا الآن".
- **B13. المزيد:** servant message with a live preview, threshold stepper, servants management, logout.

**Member screens** (read-only, no edit controls anywhere):
- **B14. حضوري:**
  - Logo, name, and a warm servant-message banner.
  - A large gold ring "85%" with the segmented control الكل · قداس · اجتماع · نشاط · خدمة.
  - A candle icon with "6 جلسات متتالية".
  - A monthly chart and the last 10 sessions (no "يدوي" badge).
  - A "كارتي" button and the install card "ثبّت صفحتك على الشاشة الرئيسية".
- **B15. كارتي:** a 9:16 cream card (church logo, church name, member name, a large dark QR on cream with a quiet zone, "إعداد الخدام") and the button "احفظ الكارت كصورة".
- **B16. رابط غير صالح:** "هذا الرابط لم يعد صالحًا. اطلب كارتًا جديدًا من الخادم." Show no personal data.

## Responsive Requirements
- **Mobile (360–600px), the priority:**
  - Cards in two columns, with long descriptions hidden in small cards.
  - Photo-circle clusters keep their composition at a smaller scale; the smallest circles get slightly larger.
  - Filters open in bottom sheets.
  - No horizontal scroll at 360px.
- **Tablet (600–1000px):** two or three columns, and the menu button instead of the full navigation.
- **Desktop (1000px and up):** full horizontal navigation; a floating glass pill navigation appears after the hero; two-column sections (text on the right, visual on the left).

## Style References
- **The home page `design/home.html`:**
  - Hero with window light rays, incense smoke and slow zoom.
  - Four floating circles of different sizes under the hero.
  - A services circle cloud.
  - A gallery cluster (one big circle with 15 around it, text on the right).
  - A candle verse section.
  - Fixed background photos that fade with scroll.
- **Layout feeling:** a premium church landing page with a light, thin headline over golden window light (mirrored for RTL), and soft floating circles of photos and light orbs.
- **Avoid:**
  - Outlined numbers, icons inside squares, scrolling text marquees, flat white cards.
  - Pure white or pure black, cold blue tones.
  - Splitting Arabic words into letters.
