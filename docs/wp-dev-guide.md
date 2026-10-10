# مرجع تطوير `WordPress`: `theme` و `plugin` من الصفر

> مرجع عملي مبني على مشروع حقيقي: موقع كنيسة السيدة العذراء ومارمينا والبابا كيرلس السادس، وفيه `theme` مخصص للموقع العام اسمه `st-mina` و `plugin` مخصص لنظام الحضور بالكود اسمه `st-mina-attendance`.
>
> الملف بيتحدّث مع كل خطوة في بناء `theme` و`plugin`. كل باب فيه: عملنا إيه وليه، والمفهوم في `WordPress`، والكود الحقيقي مشروح، والبدائل، والأمان، والأخطاء اللي قابلتنا.
>
> آخر تحديث: يوم 7 أكتوبر 2026، في المهمة 20 (الاستيراد من Excel).

## الفهرس

- الباب 1: الصورة الكبيرة والمشروع متقسم إزاي
- الباب 2: البيئة و `WordPress` على الاستضافة
- الباب 3: `theme` وأقل ملفات يحتاجها
- الباب 4: ملف `functions.php` ونقاط `hooks` وتحميل الملفات
- الباب 5: `plugin` وأول مسار في `REST API`
- الباب 6: الرفع على `server` و `cache`
- الباب 7: ربط Claude مع `dashboard` بتاعة `WordPress`
- الباب 8: أخطاء قابلتنا وحلّها
- الباب 9: تحويل تصميم HTML لـ `theme`
- الباب 10: صفحة إعدادات بـ `Settings API`
- الباب 11: أنواع محتوى مخصّصة وتصنيفات
- الباب 12: الخانات بـ `ACF`
- الباب 13: الصفحات وأنواع محتوى مالهاش روابط
- الباب 14: جدول المواعيد، بيانات واحدة في كذا مكان
- الباب 15: المكتبة، محتوى نوعه بيتحدد لوحده
- الباب 16: الأخبار والمناسبات، نوع واحد بأكتر من شكل
- الباب 17: نظام الحضور، الجداول والصلاحيات و`REST`
- الباب 18: شاشات الخدام والدخول
- الباب 19: كارت المخدوم، صفحة عامة بكود سري
- الباب 20: الجلسات، قيد فريد ومسار حذف بشرط
- الباب 21: المسح بالكاميرا وسجل الحضور
- الباب 22: التسجيل اليدوي والتصحيح وإنهاء الجلسة
- الباب 23: الجلسات المنسية، مهام مجدولة بـ `WP-Cron`
- الباب 24: "حضوري"، حساب واحد لكل الشاشات
- الباب 25: الاستيراد من `Excel`، وحالة "الكارت اتبعت"
- الباب 26: الحجم الهادي، تعديل كل الشاشات من ملف واحد
- الباب 27: فقاعات الواجهة، ومقولة بتلف من قسم تاني
- الباب 28: الخروج، وشاشة "المزيد"، وتغيير كلمة السر
- الباب 29: لوحة الخادم، وتنزيل الجدول `Excel`
- الباب 30: تثبيت "حضوري" على الموبايل (`PWA`)
- الباب 31: دخول المخدوم بالموبايل والرقم السري
- الباب 32: إدارة الخدام، ودعوة برابط، وصلاحية المحرر
- الباب 33: المسح من غير نت، والطابور، والمزامنة
- الباب 34: مراجعة الجودة قبل الإطلاق
- الباب 35: أحداث الخدمة، أو ربط نوعين محتوى ببعض
- الباب 36: الحضور المؤقت في الجلسة المفتوحة
- الباب 37: الأبواب الجاية

---

## الباب 1: الصورة الكبيرة والمشروع متقسم إزاي

### نختار `theme` ولا `plugin`؟

دي القاعدة اللي بيمشي عليها مطوّرين `WordPress`:

- **دور `theme` هو الشكل:** إزاي الصفحات بتتعرض، يعني ملفات HTML وCSS و`themes`. لو غيّرت `theme`، الشكل يتغيّر والبيانات تفضل زي ما هي.
- **دور `plugin` هو الوظيفة:** البيانات والمنطق، يعني الجداول والصلاحيات والحسابات ومسارات `API`. لو غيّرت `theme`، `plugin` يفضل شغال.

علشان كده نظام الحضور (المخدومين، والجلسات، وتسجيل الحضور) معمول كـ `plugin` مش جوه `theme`. لو الكنيسة غيّرت شكل الموقع بعد سنة، بيانات الحضور ماتضيعش.

### بنية `repo`

| المسار | المحتوى |
|---|---|
| `design/` | نماذج HTML المعتمدة، وهي المرجع للشكل اللي هنحوّله لـ `theme` |
| `wordpress/themes/st-mina/` | `theme` |
| `wordpress/plugins/st-mina-attendance/` | `plugin` |
| `docs/design.md` | نظام التصميم وقواعد حساب الحضور |
| `docs/dev-setup.md` | التشغيل والرفع |

### البادئة

نظام `WordPress` بيشغّل كود كل `plugins` و`theme` في نفس المكان. لو اتنين `plugins` عرّفوا دالة بنفس الاسم، الموقع يقع بخطأ قاتل. علشان كده كل حاجة عندنا ليها بادئة ثابتة:

| النوع | البادئة |
|---|---|
| الدوال والإعدادات والجداول | `stmina_` |
| الثوابت | `STMINA_` |
| مسارات `API` | `stmina/v1` |

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/plugins/plugin-basics/best-practices/#prefix-everything
```

---

## الباب 2: البيئة و `WordPress` على الاستضافة

### ليه `Hostinger` مش `Vercel`؟

نظام `WordPress` محتاج لغة `PHP` شغالة على `server` طول الوقت، وقاعدة بيانات `MySQL`، ومكان تتحفظ فيه الملفات المرفوعة. أما `Vercel` فمبني للمواقع الثابتة وتطبيقات JavaScript، فمش بيشغّل `WordPress`. علشان كده نماذج التصميم على `Vercel`، والموقع الحقيقي على `Hostinger`.

### الدومين الفرعي المؤقت

- **الرابط:** عملنا دومين فرعي في فولدر لوحده.
```
st-mina.abanoubmaurice.com  →  public_html/st-mina
```
- **ليه مؤقت؟** الموقع بيتبني ويتجرّب هنا، وعند الإطلاق (المهمة 25) بينتقل لدومين الكنيسة بتحويل نوعه `301`، وده بيقول لـ `Google` والمتصفحات إن العنوان اتنقل نهائيًا.
- **ليه موقع نضيف؟** كان فيه موقع قديم عليه ثيم تجربة لمشروع تاني، بإعدادات إنجليزي ومن الشمال لليمين. البداية النضيفة أسهل وأأمن من تنضيف موقع قديم.

### تركيب `WordPress`

اتركّب من `hPanel`، من صفحة المُركِّب التلقائي. ودي ملاحظات مهمة:

- **كلمة سر المدير** بيختارها صاحب الموقع بنفسه، ومابتتبعتش لحد.
- **اسم المستخدم** الأفضل مايكونش `admin` ولا أول جزء من الإيميل، لأن دول أول حاجة بيجرّبها اللي بيحاولوا يخترقوا المواقع.
- **قاعدة البيانات** لكل موقع `WordPress` واحدة لوحده، وكلمة سرها مختلفة عن كلمة سر المدير.

### الإعدادات الأساسية بعد التركيب

| الإعداد | مكانه في `dashboard` | القيمة | ليه |
|---|---|---|---|
| اسم الموقع والوصف | `Settings → General` | الاسم العربي، و"عرب العيايدة، الجبل الأصفر" | `theme` بيعرضهم بدالة `bloginfo()`، فمش مكتوبين في الكود |
| لغة الموقع | `Settings → General → Site Language` | العربية | بتحمّل ملفات الترجمة، وبتخلّي الصفحات من اليمين للشمال |
| التوقيت | `Settings → General → Timezone` | القاهرة | مواعيد الجلسات والحضور بتتسجّل بتوقيت مصر |
| محركات البحث | `Settings → Reading` | مقفول | الموقع المؤقت مايظهرش في `Google` |

ولما محركات البحث بتتقفل، `WordPress` بيحفظ الإعداد ده بقيمة صفر، وبيضيف السطر ده في كل صفحة:
```html
<meta name="robots" content="noindex, nofollow">
```

### لغة الموقع غير لغة `dashboard`

نظام `WordPress` فيه لغتين منفصلتين:

| النوع | مكانها | بتأثر على |
|---|---|---|
| لغة الموقع | `Settings → General` | اللي الزوار بيشوفوه |
| لغة المستخدم | `Users → Profile → Language` | `dashboard` للمستخدم ده بس |

فينفع الموقع يبقى عربي، و `dashboard` إنجليزي للمطوّر، وعربي لمحرر المحتوى.

📖 التوثيق الرسمي:
```
https://wordpress.org/documentation/article/installing-wordpress-in-your-language/
```

---

## الباب 3: `theme` وأقل ملفات يحتاجها

### كل `theme` عبارة عن فولدر

أي `theme` فولدر جوه مسار `wp-content/themes/` على `server`، و `WordPress` بيدوّر جوّاه على الملفات اللي يعرض بيها الموقع. وده `theme` بتاعنا:

```
st-mina/
├── style.css      ← بطاقة تعريف القالب + التنسيق
├── index.php      ← الصفحة اللي بتتعرض
└── functions.php  ← إعدادات وكود القالب (الباب 4)
```

أقل حاجة يحتاجها `theme` ملفين بس: ملف `style.css` وملف `index.php`. ومن غير أي واحد منهم، `theme` مش هيظهر في صفحة `themes` في `dashboard`.

### ملف `style.css` كبطاقة تعريف

أول جزء في الملف تعليق عادي في CSS، لكن `WordPress` بيقراه علشان يعرف `theme`:

```css
/*
Theme Name: St Mina
Theme URI: https://github.com/Abanoub-git-hup/church-st-mina
Description: قالب كنيسة السيدة العذراء ومارمينا والبابا كيرلس السادس. ...
Author: Abanoub S. Maurice
Version: 0.1.0
Requires at least: 6.5
Requires PHP: 8.0
Text Domain: st-mina
*/
```

| السطر | إجباري؟ | فايدته |
|---|---|---|
| `Theme Name` | أيوه، الوحيد الإجباري | الاسم في صفحة `themes`، ومن غيره `WordPress` يتجاهل `theme` |
| `Version` | لا | رقم النسخة. بنزوّده مع كل تعديل، وبنستخدمه علشان المتصفح يحمّل الملف الجديد (الباب 4) |
| `Requires at least` | لا | أقل نسخة `WordPress`، ولو `server` أقدم مش هيسمح بالتفعيل |
| `Requires PHP` | لا | أقل نسخة من لغة PHP |
| `Text Domain` | لا | اسم الترجمة اللي بيتحط مع النصوص في الكود |

وبعد التعليق، الملف بيكمّل كود CSS عادي. الألوان متعرّفة كمتغيرات بنفس أسماء ملف `docs/design.md`، زي متغير `--umber-700`، علشان `theme` يفضل متناسق مع نظام التصميم.

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/themes/basics/main-stylesheet-style-css/
```

### ملف `index.php` وترتيب `themes`

لما زائر يفتح صفحة، `WordPress` بيدوّر على أنسب ملف ليها بترتيب اسمه `Template Hierarchy`. وده مثال لصفحتين:

| الصفحة | `WordPress` بيدوّر بالترتيب ده |
|---|---|
| الرئيسية | `front-page.php` ← `home.php` ← `index.php` |
| مقال | `single-post.php` ← `single.php` ← `singular.php` ← `index.php` |

يعني ملف `index.php` هو الاحتياطي الأخير لكل الصفحات، وعلشان كده إجباري. ودلوقتي `theme` بتاعنا مافيهوش غيره، فأي رابط بيعرض نفس الصفحة المؤقتة. وفي المهمة الثانية هنضيف ملف `front-page.php` للرئيسية، وملفات لكل نوع صفحة.

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/themes/basics/template-hierarchy/
```

### شرح ملف `index.php` جزء جزء

**أولًا: سطر الأمان**
```php
defined( 'ABSPATH' ) || exit;
```
الثابت `ABSPATH` بيعرّفه `WordPress` وهو شغال. ولو حد فتح الملف مباشرة من المتصفح، الثابت مش هيكون موجود، فالملف يقفل فورًا من غير ما يطلّع أي حاجة. والسطر ده موجود في أول كل ملف PHP عندنا.

**ثانيًا: لغة الصفحة واتجاهها**
```php
<html <?php language_attributes(); ?>>
```
دالة `language_attributes()` بتكتب لغة الصفحة واتجاهها من إعداد لغة الموقع، والنتيجة كده:
```html
<html lang="ar" dir="rtl">
```
علشان كده لما غيّرنا اللغة، الصفحة اتقلبت من اليمين للشمال من غير ما نلمس الكود. ومانكتبش الاتجاه بإيدنا أبدًا، نسيبه لـ `WordPress`.

**ثالثًا: الترميز وعرض الموبايل**
```php
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
```
السطر الأول بيحدد الترميز علشان العربي يظهر صح، والتاني بيخلّي الصفحة تتعرض صح على الموبايل.

**رابعًا: نقط `WordPress` في الصفحة**
```php
<?php wp_head(); ?>       // قبل </head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>  // بعد <body> مباشرة
...
<?php wp_footer(); ?>     // قبل </body>
```

| الدالة | فايدتها |
|---|---|
| `wp_head()` | المكان اللي `WordPress` و`plugins` بيحطوا فيه ملفات CSS وJS، وعنوان الصفحة، وسطر قفل محركات البحث |
| `wp_footer()` | زيها، بس في آخر الصفحة، وغالبًا لملفات JavaScript |
| `body_class()` | بتضيف أسماء كلاسات للصفحة حسب نوعها، وتنفع في التنسيق |
| `wp_body_open()` | نقطة بعد بداية الصفحة، بتستخدمها بعض `plugins` زي أكواد التتبع |

وأهم حاجة: دالة `wp_head()` ودالة `wp_footer()` أهم سطرين في أي `theme`. ولو شيلنا دالة `wp_head()`، ملف التنسيق نفسه مش هيتحمّل، والسبب في الباب الجاي.

**خامسًا: الاسم والوصف**
```php
<h1><?php bloginfo( 'name' ); ?></h1>
<p class="tag"><?php bloginfo( 'description' ); ?></p>
```
دالة `bloginfo()` بتطبع اسم الموقع ووصفه من الإعدادات. يعني النص مش مكتوب في الكود، وأي حد يقدر يغيّره من `dashboard`.

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/reference/functions/wp_head/
https://developer.wordpress.org/reference/functions/language_attributes/
https://developer.wordpress.org/reference/functions/bloginfo/
```

### نختار `classic theme` ولا `block theme`؟

نظام `WordPress` فيه نوعين من `themes`:

| النوع | مثال | الصفحات بتتبني إزاي |
|---|---|---|
| `theme` البلوكات `Block theme` | `theme` `WordPress` الافتراضي `Twenty Twenty-Five` | من بلوكات في محرر الموقع، بملفات `.html` |
| `theme` الكلاسيكي `Classic theme` | `theme` بتاعنا | ملفات PHP بنتحكّم فيها بالكامل |

واخترنا الكلاسيكي لأن التصميم معتمد بالتفصيل (زجاج، وحركات، ومكوّنات مخصّصة)، ومحتاجين نترجمه بالظبط. ومحرر المحتوى هيضيف عظات وأخبار من نماذج محددة، مش هيعدّل شكل الصفحات.

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/themes/getting-started/what-is-a-theme/
```

---

## الباب 4: ملف `functions.php` ونقاط `hooks` وتحميل الملفات

### ملف `functions.php`

ملف اختياري، و `WordPress` بيشغّله تلقائيًا مع كل صفحة طول ما `theme` مفعّل. وبنحط فيه إعدادات `theme` وكوده.

### نقاط `hooks` وهي قلب `WordPress`

نظام `WordPress` وهو بيبني الصفحة بيعدّي على نقط معروفة بالاسم، وفي كل نقطة بيسأل: حد عايز يعمل حاجة هنا؟ والنقط دي اسمها `hooks`، وليها نوعين:

| النوع | معناه | بنسجّل فيه بدالة |
|---|---|---|
| `Actions` | اعمل حاجة هنا | `add_action()` |
| `Filters` | عدّل القيمة دي قبل ما أستخدمها | `add_filter()` |

وليه كده؟ علشان مانعدّلش في ملفات `WordPress` نفسها أبدًا، لأن أي تحديث هيمسح تعديلاتنا. وبدل كده بنتعلّق في النقط دي.

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/plugins/hooks/
```

### شرح الكود جزء جزء

**أولًا: ثابت النسخة**
```php
define( 'STMINA_THEME_VERSION', '0.1.0' );
```
ثابت فيه نسخة `theme`، وهنستخدمه تحت علشان المتصفح يحمّل ملف التنسيق الجديد بعد كل تعديل.

**ثانيًا: مميزات `theme`**
```php
add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'html5', array( 'style', 'script' ) );
} );
```

| الجزء | معناه |
|---|---|
| `after_setup_theme` | نقطة بتشتغل بدري بعد ما `WordPress` يحمّل `theme`، وهي المكان الصح لإعلان مميزاته |
| `title-tag` | بنقول لـ `WordPress` إنه هو اللي يكتب عنوان الصفحة من خلال دالة `wp_head()` |
| `html5` | بيخلّي `WordPress` يطبع أكواد التنسيق والسكريبت بصيغة HTML5 النضيفة |

وبسبب ميزة `title-tag`، عنوان التاب في المتصفح بقى "كنيسة السيدة العذراء… – عرب العيايدة، الجبل الأصفر" من غير ما نكتبه.

**ثالثًا: تحميل الخط وملف التنسيق**
```php
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'stmina-fonts', 'https://fonts.googleapis.com/css2?family=Alexandria:wght@200;300;400&display=swap', array(), null );
	wp_enqueue_style( 'stmina', get_stylesheet_uri(), array( 'stmina-fonts' ), STMINA_THEME_VERSION );
} );
```

ليه مانكتبش رابط ملف التنسيق في ملف `index.php` على طول؟ لأن `WordPress` محتاج يعرف كل ملفات CSS وJS في الصفحة، علشان:
- مايتحمّلش ملف مرتين لو `plugin` تاني طلبه.
- يرتّبهم صح لو واحد بيعتمد على التاني.
- علشان `plugins` بتاعة `cache` والضغط تقدر تتعامل معاهم.

والنظام ده اسمه `Enqueue`، والنقطة الصح ليه في الصفحات العامة هي نقطة `wp_enqueue_scripts`، ورغم إن اسمها فيه كلمة سكريبت، بس هي بتاعة ملفات التنسيق كمان.

ودالة `wp_enqueue_style()` بتاخد أربع حاجات:

| الترتيب | القيمة عندنا | معناها |
|---|---|---|
| الاسم | `stmina` | معرّف فريد بالبادئة بتاعتنا |
| الرابط | `get_stylesheet_uri()` | رابط ملف `style.css` في `theme` الحالي |
| بيعتمد على | `array( 'stmina-fonts' )` | `WordPress` يحمّل الخط قبل ملف التنسيق |
| النسخة | `STMINA_THEME_VERSION` | بتتضاف لآخر الرابط، وتتشرح تحت |

**إزاي النسخة بتحل مشكلة المتصفح؟** الرابط بيطلع كده:
```
style.css?ver=0.1.0
```
ولما نغيّر النسخة، الرابط يتغيّر، فالمتصفح يحمّل الملف الجديد بدل القديم المحفوظ عنده. ولخط `Google` حطينا القيمة `null` علشان مايتضافش رقم نسخة لرابط مش بتاعنا.

**والملفات دي بتظهر في الصفحة فين؟** جوه دالة `wp_head()`. وعلشان كده لو شيلناها، التنسيق مش هيتحمّل.

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/themes/basics/including-css-javascript/
https://developer.wordpress.org/reference/functions/wp_enqueue_style/
https://developer.wordpress.org/reference/functions/add_theme_support/
```

---

## الباب 5: `plugin` وأول مسار في `REST API`

### كل `plugin` فولدر وملف رئيسي

أي `plugin` فولدر جوه مسار `wp-content/plugins/`، وفيه ملف PHP رئيسي غالبًا بنفس اسم الفولدر:
```
st-mina-attendance/st-mina-attendance.php
```

### بطاقة تعريف `plugin`

زي ملف `style.css` في `theme`، أول تعليق في الملف بيعرّف `plugin`:

```php
/**
 * Plugin Name: St Mina Attendance
 * Description: نظام الحضور بالـ QR لخدمة إعداد الخدام. ...
 * Version: 0.1.0
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Text Domain: st-mina-attendance
 */
```
والسطر الإجباري الوحيد هو `Plugin Name`، ومن غيره `plugin` مش هيظهر في صفحة `plugins`.

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/plugins/plugin-basics/header-requirements/
```

### الثوابت

```php
define( 'STMINA_ATT_VERSION', '0.1.0' );
define( 'STMINA_ATT_FILE', __FILE__ );
```

| الثابت | فايدته |
|---|---|
| `STMINA_ATT_VERSION` | نسخة `plugin`، وهنستخدمها بعدين علشان نعرف لو قاعدة البيانات محتاجة تتحدّث بعد تحديث `plugin` |
| `STMINA_ATT_FILE` | مسار الملف الرئيسي، ودوال كتير في `WordPress` محتاجاه |

### التفعيل

```php
register_activation_hook( __FILE__, function () {
	update_option( 'stmina_att_version', STMINA_ATT_VERSION );
} );
```

- **إمتى بتشتغل؟** مرة واحدة بس، لما حد يضغط تفعيل `plugin`، مش مع كل صفحة.
- **هنستخدمها في إيه؟** ده المكان اللي هنعمل فيه جداول قاعدة البيانات في المهمة الثامنة (المخدومين، والجلسات، والحضور).
- **بتعمل إيه دلوقتي؟** بتحفظ نسخة `plugin` في جدول الإعدادات.

ودوال الإعدادات تلاتة:

| الدالة | بتعمل إيه |
|---|---|
| `update_option()` | بتحفظ قيمة، وبتعملها لو مش موجودة |
| `get_option()` | بتقرا قيمة |
| `delete_option()` | بتمسح قيمة |

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/plugins/plugin-basics/activation-deactivation-hooks/
https://developer.wordpress.org/apis/options/
```

### أول مسار في `REST API`

```php
add_action( 'rest_api_init', function () {
	register_rest_route( 'stmina/v1', '/health', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => function () {
			return array(
				'plugin'  => 'st-mina-attendance',
				'version' => STMINA_ATT_VERSION,
			);
		},
	) );
} );
```

**يعني إيه `REST API`؟** طريقة `WordPress` الرسمية إن صفحة أو تطبيق يكلّم الموقع ويرجع بيانات بصيغة JSON. وشاشات الحضور كلها (المسح، والجلسات، والمخدومين) هتكلّم `plugin` بالطريقة دي.

والكود ده بيعمل الرابط ده:
```
/wp-json/stmina/v1/health
```

| الجزء | معناه |
|---|---|
| نقطة `rest_api_init` | المكان اللي بنسجّل فيه المسارات |
| الاسم `stmina/v1` | البادئة بتاعتنا ورقم نسخة. لو غيّرنا شكل `API` بعدين، نعمل نسخة تانية من غير ما نكسر القديمة |
| نوع الطلب `methods` | قيمة `GET` للقراية، وقيمة `POST` للإضافة زي تسجيل حضور |
| الدالة `callback` | اللي بترد، وبنرجّع فيها مصفوفة، و `WordPress` يحوّلها JSON لوحده |
| الصلاحية `permission_callback` | مين مسموحله، والخانة دي إجبارية من نسخة `WordPress` 5.5 |

**عن الصلاحية:** حطينا القيمة `__return_true` ومعناها أي حد مسموحله. وده مقبول هنا بس لأن المسار بيرجّع اسم `plugin` ونسخته. أما أي مسار فيه بيانات مخدومين، فهيبقى فيه فحص صلاحيات حقيقي في المهمة الثامنة، زي كده:
```php
current_user_can( 'stmina_manage_attendance' )
```

**فايدة المسار ده دلوقتي:** نقطة فحص نعرف منها إن `plugin` شغال على `server` من غير ما ندخل `dashboard`.

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
```

---

## الباب 6: الرفع على `server` و `cache`

### الرفع

الكود بيتكتب في `repo` على الجهاز، وبيترفع لـ `server` عن طريق ربط `Hostinger` في Claude، على تلات خطوات:

| الخطوة | العملية | بتعمل إيه |
|---|---|---|
| الأولى | `hosting_files_generate-upload-url` | بتدّي رابط رفع ومفاتيح مؤقتة للموقع |
| التانية | رفع كل ملف لوحده بطريقة `TUS` | بروتوكول رفع بيكمّل لو النت قطع، وكل ملف على خطوتين: طلب يعلن الحجم، وطلب يبعت المحتوى |
| التالتة | `wordpress_themes_deploy` أو `wordpress_plugins_deploy` | بتحط الملفات في مكانها النهائي بالاسم وتفعّلها |

والملفات بتترفع الأول لفولدر مؤقت زي ده:
```
wp-content/themes/st-mina-u1/
```

**البديل التقليدي:** ضغط الفولدر ورفعه من صفحة `themes` في `dashboard`، أو من مدير الملفات في `Hostinger`، أو ببرنامج FTP.

### مشكلة `cache`

بعد الرفع، الصفحة فضلت تظهر بالشكل القديم. والسبب إن `Hostinger` بيحفظ نسخة جاهزة من الصفحة علشان يردّ أسرع، فكان بيبعت النسخة المحفوظة.

- **الحل:** مسح `cache` بعد كل رفع بالعملية دي:
```
hosting_cache_clear-website
```
- **إزاي عرفنا؟** لما طلبنا الصفحة برابط فيه رقم عشوائي، `cache` ماكانش عنده نسخة منه، فظهر الشكل الجديد.

---

## الباب 7: ربط Claude مع `dashboard` بتاعة `WordPress`

استخدمنا `plugin` `royal-mcp`، وهو بيعرض أدوات `WordPress` (قراية وتعديل المحتوى والإعدادات) بطريقة اسمها `MCP`، ودي الطريقة اللي Claude بيتكلّم بيها مع الأدوات.

### نوعين ربط

| | الربط المحلي | الربط على الإنترنت |
|---|---|---|
| بيشتغل منين | برنامج على جهازك بيكلّم الموقع | Claude بيكلّم الموقع مباشرة |
| الدخول | غالبًا كلمة سر تطبيق محفوظة في ملف | موافقة مرة واحدة من صفحة الموقع بطريقة `OAuth` |
| متاح فين | الجهاز ده بس | أي مكان على حسابك |

واخترنا الربط على الإنترنت علشان مافيش كلمة سر بتتحفظ ولا بتتبعت.

### حدود `plugin`، وده أمان مقصود

- **تعديل الإعدادات مقفول افتراضيًا،** ومحتاج زرار في إعدادات `plugin` اسمه `Allow AI to write WordPress options`.
- **فيه قايمة مسموح بيها بس:** الاسم والوصف اتغيّروا، لكن التوقيت ومحركات البحث اترفضوا، فاتعملوا من `dashboard`.
- **إعدادات ممنوعة نهائيًا:** رابط الموقع، وإيميل المدير، والصلاحيات، وأي حاجة فيها كلمة سر أو مفتاح.
- **كل تعديل ليه تراجع** لمدة 72 ساعة، وبيتسجّل في سجل.

ودي فكرة مهمة لما نبني `plugin` بتاعنا: أقل صلاحيات ممكنة، والحاجات الحساسة مقفولة من الأساس.

---

## الباب 8: أخطاء قابلتنا وحلّها

| المشكلة | السبب | الحل |
|---|---|---|
| `Hostinger` رفض الاسم العربي وقت التركيب | خانة الاسم في المُركِّب بتقبل حروف لاتيني بس | اسم إنجليزي مؤقت، وبعدين الاسم العربي من الإعدادات |
| الموقع اتركّب إنجليزي | المُركِّب بيستخدم الإنجليزي افتراضيًا، وخطوة اللغة ماظهرتش | تغيير لغة الموقع من الإعدادات العامة |
| الموقع رجع إنجليزي بعد ما `dashboard` اتحوّلت إنجليزي | لغة الموقع اتغيّرت بدل لغة المستخدم | لغة الموقع عربي من الإعدادات العامة، ولغة `dashboard` من الملف الشخصي |
| قفل محركات البحث ماتحفظش أول مرة | العلامة ماكانتش متعلّمة وقت الحفظ | الرجوع لإعدادات القراية والتأكد منها، والتأكد من سطر القفل في الصفحة |
| الصفحة بالشكل القديم بعد رفع `theme` | كاش `server` | مسح `cache` بعد كل رفع |
| مش قادرين نفحص أخطاء PHP على الجهاز | لغة PHP مش متثبّتة | الفحص على `server`، وبرنامج LocalWP للتشغيل المحلي |
| سجل الأخطاء مش متاح | تسجيل الأخطاء مقفول في ملف `wp-config.php` | هنفتحه وقت التطوير لما نحتاجه، مع إخفاء الأخطاء عن الزوار |

والإعدادات اللي بتفتح سجل الأخطاء من غير ما تظهر للزوار:
```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

---

## الباب 9: تحويل تصميم HTML لـ `theme`

### الفكرة

التصميم المعتمد صفحة HTML كاملة في ملف `design/home.html`. ومحتاجين نحوّلها لـ `theme` من غير ما الشكل يتغيّر ولا حرف. وبدل النسخ واللصق بالإيد، كتبنا سكريبت بيعمل التحويل:
```
node tools/convert-home.js
```
وده بيضمن إن أي تعديل في التصميم يتنقل بنفس الطريقة، ومفيش حاجة تتنسي أو تتلخبط.

### تقسيم الصفحة لتلات ملفات

أي صفحة في الموقع ليها بداية ونهاية ثابتين، والنص بيتغيّر. علشان كده `WordPress` بيقسّم الصفحة كده:

| الملف | جواه إيه | بيتنادى بدالة |
|---|---|---|
| `header.php` | من `<!doctype>` لحد `<main>`: الـ `head`، والأيقونات، والشريط العائم، وقايمة الموبايل | `get_header()` |
| `front-page.php` | الـ 14 قسم بتوع الرئيسية | — |
| `footer.php` | الـ `footer`، وعارض الصور، ودالة `wp_footer()`، وقفل الصفحة | `get_footer()` |

وأي صفحة جديدة بتبدأ بدالة `get_header()` وتخلص بدالة `get_footer()`، وفي النص المحتوى بتاعها بس. يعني لو غيّرنا حاجة في `header.php`، بتتغيّر في كل الموقع مرة واحدة.

**ليه ملف `front-page.php` بالذات؟** لأنه أول ملف بيدوّر عليه `WordPress` للرئيسية في ترتيب القوالب (الباب 3)، وبيشتغل مهما كان اختيار الرئيسية في إعدادات القراية.

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/themes/basics/template-files/#template-partials
https://developer.wordpress.org/reference/functions/get_header/
```

### الصفحة الاحتياطية بقت بنفس الشكل

ملف `index.php` بقى بيستخدم نفس `header` و`footer`، ويعرض رسالة "الصفحة دي بتتجهّز" لأي صفحة لسه ماتعملتش. و`WordPress` نفسه بيرجّع كود `404` للصفحات اللي مش موجودة، فمحركات البحث مابتعتبرهاش صفحات حقيقية.

### الصور والروابط بدوال

في التصميم، الصور مكتوبة بمسار نسبي زي `../media/church/hero-prayer.png`، والروابط زي `worship.html`. وده مش هينفع في `WordPress`، لأن الصفحة ممكن تبقى على أي رابط. فعملنا دالتين في `functions.php`:

```php
function stmina_media( $path ) {
	echo esc_url( get_template_directory_uri() . '/assets/media/' . $path );
}

function stmina_link( $page, $hash = '' ) {
	echo esc_url( home_url( '/' . $page . '/' ) . $hash );
}
```

| الدالة | بتعمل إيه | مثال |
|---|---|---|
| `get_template_directory_uri()` | رابط فولدر الـ `theme` على الموقع | `https://…/wp-content/themes/st-mina` |
| `home_url()` | رابط الموقع نفسه، ولو الموقع اتنقل لدومين الكنيسة يتغيّر لوحده | `https://…/worship/` |
| `esc_url()` | بتأمّن الرابط قبل طباعته (الجزء الجاي) | — |

والسكريبت بيبدّل كل صورة ورابط في التصميم بالدوال دي، فالسطر ده:
```html
<img src="../media/church/hero-prayer.png">
```
بيبقى كده:
```php
<img src="<?php stmina_media( 'church/hero-prayer.png' ); ?>">
```

والصور المستخدمة في الرئيسية بس (47 صورة) اتنسخت لفولدر `assets/media` جوه الـ `theme`. وفي مهام المحتوى الجاية، صور العظات والأخبار هتترفع من مكتبة الوسائط في `dashboard` بدل ما تبقى جوه الكود.

### الأمان في العرض: `Escaping`

أي قيمة بتتطبع في الصفحة لازم تتأمّن **وقت الطباعة**، حتى لو إحنا اللي حاطينها. والسبب إن لو حد قدر يحط كود جوه نص (زي سكريبت)، التأمين بيحوّله لنص عادي مايشتغلش. والقاعدة: استخدم الدالة المناسبة لمكان الطباعة.

| الدالة | بتتستخدم فين | عندنا |
|---|---|---|
| `esc_html()` | نص جوه الصفحة | آية الواجهة والقول |
| `esc_attr()` | قيمة جوه خاصية HTML | قيمة خانة الإعدادات |
| `esc_url()` | أي رابط | الصور والروابط |

ومثال من الرئيسية:
```php
<h1 data-split><?php echo esc_html( stmina_home( 'hero_verse' ) ); ?> <em><?php echo esc_html( stmina_home( 'hero_highlight' ) ); ?></em></h1>
```

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/apis/security/escaping/
```

### تحميل السكريبتات: `wp_enqueue_script`

زي ملفات التنسيق (الباب 4)، السكريبتات بتتحمّل عن طريق `WordPress`:

```php
wp_enqueue_script( 'gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js', array(), null, true );
wp_enqueue_script( 'gsap-scrolltrigger', '…/ScrollTrigger.min.js', array( 'gsap' ), null, true );
wp_enqueue_script( 'stmina-site', $uri . 'site.js', array( 'gsap-scrolltrigger' ), $ver, true );
```

| الجزء | معناه |
|---|---|
| ترتيب الاعتماد | ملف `site.js` بيعتمد على مكتبة الحركة، فـ `WordPress` بيحمّلها الأول |
| آخر قيمة `true` | السكريبت يتحط في آخر الصفحة جوه دالة `wp_footer()`، فالصفحة تظهر أسرع |

### ملفات لصفحة واحدة بس: `is_front_page()`

ملف `home.css` وملف `home.js` بتوع الرئيسية بس، فبنحمّلهم بشرط:
```php
if ( is_front_page() ) {
	wp_enqueue_style( 'stmina-home', $uri . 'home.css', array( 'stmina-site' ), $ver );
	wp_enqueue_script( 'stmina-home', $uri . 'home.js', array( 'stmina-site' ), $ver, true );
}
```
ودوال الشروط دي اسمها `Conditional Tags`، ومنها كمان `is_page()` و`is_single()` و`is_404()`.

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/themes/basics/conditional-tags/
```

### بعت قيمة من PHP لـ JavaScript

ملف `site.js` بيعمل القوايم المنسدلة، وكان مكتوب فيه روابط زي `worship.html`. وفي `WordPress` الرابط بقى `/worship/`. فبنبعتله رابط الموقع من PHP بدالة `wp_add_inline_script()`:
```php
wp_add_inline_script( 'stmina-site', 'window.SITE_BASE = ' . wp_json_encode( home_url( '/' ) ) . ';', 'before' );
```
| الجزء | معناه |
|---|---|
| القيمة `before` | السطر يتحط قبل ملف `site.js`، فيكون جاهز وقت ما السكريبت يشتغل |
| دالة `wp_json_encode()` | بتحوّل القيمة لصيغة JavaScript آمنة |

وجوه ملف `site.js`، لو المتغيّر `SITE_BASE` موجود، الروابط بتتحوّل لشكل `WordPress`، ولو مش موجود (في نماذج التصميم) بتفضل زي ما هي. كده نفس الملف شغال في المكانين.

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/reference/functions/wp_add_inline_script/
```

---

## الباب 10: صفحة إعدادات بـ `Settings API`

### ليه صفحة إعدادات؟

آية الواجهة، والقول اللي في الدايرة، وآية التأمل نصوص بتتغيّر كل فترة. ومحرر المحتوى لازم يغيّرها من `dashboard` من غير ما يلمس الكود. فعملنا صفحة "إعدادات الرئيسية" في القايمة الجانبية، في ملف `inc/home-settings.php`.

**البدائل اللي كانت قدامنا:**

| الطريقة | ميزتها | عيبها |
|---|---|---|
| أداة `Customizer` | موجودة في `WordPress` ومعاينة فورية | أقل تحكّم في شكل الصفحة |
| بلجن `ACF` | سهل جدًا في الحقول الكتير | الموقع بيعتمد على `plugin` خارجي |
| **صفحة بـ `Settings API`** (اخترناها) | تحكّم كامل، ومافيش اعتماد على حد، وأحسن للمذاكرة | كود أكتر |

### الأجزاء الأربعة لأي صفحة إعدادات

**أولًا: تعريف الخانات في مكان واحد**
```php
function stmina_home_fields() {
	return array(
		'hero_verse' => array( 'آية الواجهة', 'فِي ٱلْعَالَمِ سَيَكُونُ لَكُمْ ضِيقٌ، وَلكِنْ ثِقُوا:', 'hero' ),
		// …
	);
}
```
كل خانة ليها مفتاح، وعنوان، والنص الافتراضي من التصميم، والقسم اللي هتظهر فيه. ولو حبينا نضيف خانة، بنضيف سطر هنا بس.

**ثانيًا: تسجيل الإعداد في نقطة `admin_init`**
```php
register_setting( 'stmina_home', 'stmina_home', array(
	'type'              => 'array',
	'sanitize_callback' => 'stmina_home_sanitize',
	'default'           => array(),
) );
```
| الجزء | معناه |
|---|---|
| الاسم الأول | اسم مجموعة الإعدادات اللي الفورم بيبعتها |
| الاسم التاني | اسم الإعداد في جدول `wp_options`. كل الخانات بتتحفظ فيه كمصفوفة واحدة بدل 7 إعدادات منفصلة |
| دالة `sanitize_callback` | بتنضّف القيم قبل الحفظ (الجزء الجاي) |

وبعدها الأقسام والخانات:
```php
add_settings_section( 'hero', 'آية الواجهة', '__return_false', 'stmina-home' );
add_settings_field( $key, $f[0], 'stmina_home_field', 'stmina-home', $f[2], array( 'key' => $key, 'label_for' => 'stmina-' . $key ) );
```
والخاصية `label_for` بتربط عنوان الخانة بالخانة نفسها، فلما تضغط على العنوان المؤشر يروح للخانة، وده مهم لقارئ الشاشة.

**ثالثًا: تنضيف المدخلات: `Sanitizing`**
```php
function stmina_home_sanitize( $input ) {
	$clean = array();
	foreach ( array_keys( stmina_home_fields() ) as $key ) {
		$clean[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : '';
	}
	return $clean;
}
```
- **بنحفظ المفاتيح المعروفة بس:** لو حد بعت مفتاح زيادة في الطلب، بيتشال.
- **دالة `sanitize_text_field()`:** بتشيل أي كود HTML والمسافات الزيادة، وبتسيب النص العادي.
- **القاعدة الذهبية:** نضّف وقت الحفظ (`sanitize`)، وأمّن وقت الطباعة (`escape`). الاتنين مع بعض، مش واحد بدل التاني.

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/apis/security/sanitizing/
```

**رابعًا: الصفحة نفسها والصلاحيات**
```php
add_menu_page( 'إعدادات الرئيسية', 'إعدادات الرئيسية', 'edit_theme_options', 'stmina-home', 'stmina_home_page', 'dashicons-admin-home', 3 );
```
| الجزء | معناه |
|---|---|
| الصلاحية `edit_theme_options` | اللي معاه الصلاحية دي بس يشوف الصفحة. المدير معاه، وبعدين هنديها لمحرر المحتوى (المهمة 24) |
| الأيقونة `dashicons-admin-home` | أيقونة البيت من أيقونات `WordPress` |
| الرقم 3 | مكانها في القايمة، تحت `Dashboard` على طول |

وجوه الصفحة:
```php
<form action="options.php" method="post">
	<?php
	settings_fields( 'stmina_home' );
	do_settings_sections( 'stmina-home' );
	submit_button( 'حفظ' );
	?>
</form>
```
| الدالة | بتعمل إيه |
|---|---|
| ملف `options.php` | ملف من `WordPress` بيستقبل الفورم ويحفظ |
| دالة `settings_fields()` | بتضيف رمز حماية `nonce` بيتأكد إن الطلب جاي من الصفحة دي فعلًا، مش من موقع تاني |
| دالة `do_settings_sections()` | بترسم كل الأقسام والخانات اللي سجّلناها |

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/plugins/settings/settings-api/
```

### قراية القيمة في الرئيسية

```php
function stmina_home( $key ) {
	$saved  = get_option( 'stmina_home', array() );
	$fields = stmina_home_fields();
	if ( ! empty( $saved[ $key ] ) ) {
		return $saved[ $key ];
	}
	return isset( $fields[ $key ] ) ? $fields[ $key ][1] : '';
}
```
لو الخانة فاضية، بيرجع النص الافتراضي من التصميم. فالرئيسية عمرها ماتظهر فاضية، حتى قبل ما حد يدخل الإعدادات.

---

## الباب 11: أنواع محتوى مخصّصة وتصنيفات

### المشكلة

الخدمات ليها شكل ثابت: اسم، وصورة، ومجموعة، وموعد، ومكان، ومسؤولين. لو حطيناها كمقالات عادية في `Posts`، هتتلخبط مع الأخبار، ومش هيبقى ليها صفحة خاصة بيها ولا خانات خاصة بيها. والحل في `WordPress` اسمه نوع محتوى مخصّص، أو `Custom Post Type`.

### ليه في `plugin` مش في الـ `theme`؟

عملنا `plugin` جديد اسمه `st-mina-content`، وفيه كل أنواع المحتوى. والسبب القاعدة اللي في الباب الأول: البيانات مكانها `plugin`. ولو نوع المحتوى اتسجّل جوه الـ `theme`، وحد غيّر الـ `theme`، الخدمات تختفي من `dashboard` لحد ما حد يكتبها تاني، مع إنها لسه موجودة في قاعدة البيانات.

وعملناه منفصل عن `plugin` الحضور، لأن محتوى الموقع العام ونظام الحضور حاجتين مختلفتين، وكل واحد ممكن يتحدّث لوحده.

وفي أول الملف سطر بيقول إن الـ `plugin` محتاج `ACF`:
```php
 * Requires Plugins: advanced-custom-fields
```
وده بيمنع تفعيله لو `ACF` مش موجود، وموجود من نسخة `WordPress` 6.5.

### تسجيل نوع المحتوى: `register_post_type()`

```php
register_post_type( 'stmina_service', array(
	'labels'        => array( 'name' => 'الخدمات', 'singular_name' => 'خدمة', /* … */ ),
	'public'        => true,
	'show_in_rest'  => true,
	'menu_icon'     => 'dashicons-groups',
	'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
	'has_archive'   => 'services',
	'rewrite'       => array( 'slug' => 'service', 'with_front' => false ),
) );
```

| الخاصية | معناها |
|---|---|
| الاسم `stmina_service` | الاسم الداخلي، بالبادئة بتاعتنا، وأقصى طول 20 حرف |
| الخاصية `labels` | الكلام اللي بيظهر في `dashboard`: "الخدمات"، و"إضافة خدمة"، وغيرها |
| الخاصية `public` | الخدمات بتظهر للزوار وليها روابط |
| الخاصية `show_in_rest` | لازمة علشان محرر البلوكات يشتغل، وعلشان الخدمات تبان في `REST API` |
| الخاصية `supports` | الخانات اللي في المحرر: العنوان، والمحتوى، والمقتطف، والصورة البارزة، والترتيب |
| الخاصية `has_archive` | صفحة فيها كل الخدمات على الرابط `/services/` |
| الخاصية `rewrite` | رابط الخدمة الواحدة يبقى `/service/اسمها/` |

والتسجيل بيحصل في نقطة `init`، لأن `WordPress` محتاج يعرف أنواع المحتوى بدري، قبل ما يقرا الرابط ويعرف الزائر طالب أنهي صفحة.

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/plugins/post-types/registering-custom-post-types/
```

### التصنيف: `register_taxonomy()`

المجموعات الست تصنيف مخصّص للخدمات، زي ما `Categories` تصنيف للمقالات:
```php
register_taxonomy( 'stmina_service_group', 'stmina_service', array(
	'hierarchical'      => true,
	'show_in_rest'      => true,
	'show_admin_column' => true,
	'rewrite'           => array( 'slug' => 'service-group', 'with_front' => false ),
) );
```

| الخاصية | معناها |
|---|---|
| الخاصية `hierarchical` | لو `true` بيتصرّف زي `Categories` بمربعات اختيار، ولو `false` بيتصرّف زي `Tags` بخانة كتابة |
| الخاصية `show_admin_column` | عمود "المجموعة" في جدول الخدمات في `dashboard` |

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/plugins/taxonomies/working-with-custom-taxonomies/
```

### الروابط وتحديثها: `flush_rewrite_rules()`

`WordPress` بيحفظ قواعد الروابط (زي إن `/services/` معناها صفحة الخدمات) في قاعدة البيانات. ولما نسجّل نوع محتوى جديد، القواعد دي لازم تتحدّث، وإلا الروابط الجديدة هتطلع "الصفحة غير موجودة". والتحديث ده تقيل، فمايتعملش أبدًا مع كل صفحة، بس مرة واحدة بعد التفعيل.

**الطريقة اللي استقرينا عليها:** التفعيل بيحط علامة بس، والتحديث بيحصل في أول طلب بعده:
```php
register_activation_hook( __FILE__, function () {
	stmina_register_services();
	stmina_seed_services();
	update_option( 'stmina_flush_rewrite', 1 ); // علامة بس
} );

add_action( 'init', function () {
	if ( get_option( 'stmina_flush_rewrite' ) ) {
		delete_option( 'stmina_flush_rewrite' );
		flush_rewrite_rules(); // دلوقتي كل الأنواع متسجّلة
	}
}, 99 );
```
**ليه مش `flush_rewrite_rules()` جوه التفعيل على طول؟** وقت التفعيل، نقطة `init` بتكون عدّت خلاص، فأنواع المحتوى اللي بتتسجّل عليها مابتتسجّلش. ولو حدّثنا الروابط ساعتها، بتتبني من غيرهم وروابطهم بتتمسح.

**ده حصل فعلًا في المهمة السابعة:** أول نسخة كانت بتسجّل الخدمات بس جوه التفعيل وتحدّث على طول. ومع كل رفع، `Hostinger` بيلغي تفعيل الـ `plugin` ويفعّله تاني، فروابط العظات والأخبار (`/sermon/...` و`/news/...`) بقت كلها `404`. والحل كان العلامة والأولوية 99 على `init`، علشان التحديث يحصل بعد ما كل الأنواع تتسجّل. **والدرس:** بعد أي رفع، افتح رابط من كل نوع محتوى، مش الصفحات الرئيسية بس.

**ولو الروابط بقت "غير موجودة" في أي وقت:** ادخل `Settings → Permalinks` واضغط `Save Changes` من غير ما تغيّر حاجة، وده بيعمل نفس التحديث.

### اختيار الملف: ترتيب القوالب تاني

`WordPress` بيختار الملف حسب نوع المحتوى (الباب 3):

| الصفحة | `WordPress` بيدوّر بالترتيب ده |
|---|---|
| كل الخدمات | `archive-stmina_service.php` ← `archive.php` ← `index.php` |
| خدمة واحدة | `single-stmina_service.php` ← `single.php` ← `singular.php` ← `index.php` |

فبمجرد ما عملنا الملفين بالأسماء دي، `WordPress` بقى يستخدمهم لوحده من غير أي تسجيل.

### جلب الخدمات: `get_posts()` و`tax_query`

```php
function stmina_services( $group = null, $exclude = array() ) {
	$args = array(
		'post_type'      => 'stmina_service',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		'post__not_in'   => $exclude,
	);
	if ( $group ) {
		$args['tax_query'] = array( array(
			'taxonomy' => 'stmina_service_group',
			'terms'    => $group->term_id,
		) );
	}
	return get_posts( $args );
}
```

| الخاصية | معناها |
|---|---|
| القيمة `-1` في `posts_per_page` | كل الخدمات من غير تقسيم صفحات |
| الترتيب بـ `menu_order` | خانة `Order` في المحرر، وبيها محرر المحتوى يرتّب الخدمات |
| الخاصية `tax_query` | خدمات مجموعة معينة بس |
| الخاصية `post__not_in` | استبعاد خدمات، زي الخدمة المفتوحة في قسم "خدمات أخرى" |

ودالة `get_posts()` بترجّع مصفوفة خدمات جاهزة. وتحتها كلاس `WP_Query`، وده اللي بنستخدمه لما نحتاج تقسيم صفحات أو `The Loop`.

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/reference/classes/wp_query/
```

### أجزاء بتتكرر: `get_template_part()` مع `$args`

كارت الخدمة بيظهر في تلات أماكن: الرئيسية، وصفحة الخدمات، و"خدمات أخرى". فاتكتب مرة واحدة في ملف `template-parts/service-card.php`، وبيتنادى كده:
```php
get_template_part( 'template-parts/service-card', null, array( 'post' => $service ) );
```
والقيمة التالتة بتوصل للملف كمتغيّر اسمه `$args`، وده موجود من نسخة `WordPress` 5.5. وبنفس الطريقة عملنا:

| الملف | فيه إيه |
|---|---|
| `template-parts/hero-top.php` | رأس الصفحة: الشعار والقايمة، والقسم الحالي بيتعلّم |
| `template-parts/page-hero.php` | واجهة أي صفحة داخلية: الصورة، ومسار التنقل، والعنوان |
| `template-parts/service-card.php` | كارت الخدمة |

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/reference/functions/get_template_part/
```

### صور الخدمة من غير خانة زيادة

صور أنشطة الخدمة بتيجي من الصور المرفوعة جوه الخدمة نفسها، من غير الصورة البارزة:
```php
$photos = get_attached_media( 'image', $service );
unset( $photos[ get_post_thumbnail_id( $service ) ] );
```
فمحرر المحتوى يرفع الصور وهو بيعدّل الخدمة، وهي بتظهر لوحدها. ولو مفيش صور، القسم مابيظهرش.

---

## الباب 12: الخانات بـ `ACF`

### ليه `ACF`؟

الخانات الإضافية (الموعد، والمكان، والمسؤولين وأرقامهم) ممكن تتعمل بإيدنا بدالة `add_meta_box()`. بس `ACF` بيوفّر واجهة جاهزة ونضيفة، وأنواع خانات كتير (نص، وصورة، واختيار، وزرار تشغيل وإطفاء)، وبيحفظ القيم في `post meta` العادي بتاع `WordPress`.

### الخانات في الكود مش من اللوحة

الطريقة المعتادة إن الخانات تتعمل من لوحة `ACF`. وإحنا عرّفناها في الكود بدالة `acf_add_local_field_group()`:
```php
add_action( 'acf/include_fields', function () {
	acf_add_local_field_group( array(
		'key'      => 'group_stmina_service',
		'title'    => 'بيانات الخدمة',
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'stmina_service' ) ) ),
		'fields'   => array(
			array( 'key' => 'field_stmina_schedule', 'name' => 'schedule', 'label' => 'الموعد بالتفصيل', 'type' => 'text' ),
			// …
		),
	) );
} );
```
**ليه؟** علشان الخانات تبقى محفوظة في `GitHub` مع الكود، وتتنقل لأي موقع مع الـ `plugin` من غير ما حد يعملها بالإيد، وماحدش يقدر يمسحها بالغلط من اللوحة.

| الجزء | معناه |
|---|---|
| الخاصية `key` | معرّف ثابت للخانة، لازم يبدأ بـ `field_` ويبقى فريد |
| الخاصية `name` | الاسم اللي القيمة بتتحفظ بيه في قاعدة البيانات |
| الخاصية `location` | الخانات تظهر فين، وهنا في الخدمات بس |
| نقطة `acf/include_fields` | النقطة اللي `ACF` بيقرا فيها الخانات المتعرّفة في الكود |

📖 التوثيق الرسمي:
```
https://www.advancedcustomfields.com/resources/register-fields-via-php/
```

### قراية القيمة بأمان

```php
function stmina_field( $name, $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	return function_exists( 'get_field' ) ? get_field( $name, $post->ID ) : get_post_meta( $post->ID, $name, true );
}
```
لو `ACF` اتقفل بالغلط، الموقع مايقعش، والقيم تتقري من `post meta` مباشرة. ولو اتقفل كمان، بتظهر رسالة حمرا في `dashboard` بدل ما الخانات تختفي من غير سبب.

### إزاي `ACF` بيحفظ القيمة

كل خانة ليها سطرين في جدول `wp_postmeta`:

| الاسم | القيمة | فايدته |
|---|---|---|
| `schedule` | كل جمعة 6:00 مساءً | القيمة نفسها |
| `_schedule` | `field_stmina_schedule` | بيربط القيمة بالخانة، علشان `ACF` يعرف نوعها وهو بيقرا |

### الإدخال التلقائي

الـ 17 خدمة والمجموعات الست اتدخلوا مرة واحدة وقت التفعيل، من ملف `inc/seed.php`:
- **مرة واحدة بس:** بعد الإدخال بيتحفظ إعداد اسمه `stmina_content_seeded`، فلو الـ `plugin` اتقفل واتفعّل تاني مايتكررش.
- **الصور:** بتتنسخ من فولدر الـ `theme` لمكتبة الوسائط بدوال `wp_upload_bits()` و`wp_insert_attachment()` و`wp_generate_attachment_metadata()`، فبيتعمل منها كل المقاسات، ومحرر المحتوى يقدر يغيّرها من `dashboard`.
- **القيم:** بتتحفظ بالسطرين اللي فوق مباشرة، مش بدالة `update_field()`.

**مشكلة قابلتنا هنا:** وقت التفعيل، نقطة `acf/include_fields` بتكون عدّت قبل ما الـ `plugin` بتاعنا يتحمّل، فالخانات لسه مش متسجّلة. ولو استخدمنا دالة `update_field()` بمعرّف الخانة، كانت هتحفظ القيمة باسم غلط. فبنحفظ بالطريقة المباشرة.

---

## الباب 13: الصفحات وأنواع محتوى مالهاش روابط

### صفحة بملف خاص بيها: `page-{slug}.php`

صفحات الكنيسة الأربع صفحات عادية من نوع `Page`، اتعملت من `dashboard`. والجديد إن كل صفحة ليها ملف في الـ `theme` باسم رابطها:

| الصفحة | رابطها | الملف |
|---|---|---|
| النشأة | `/church-history/` | `page-church-history.php` |
| الآباء | `/church-fathers/` | `page-church-fathers.php` |
| الصور | `/church-gallery/` | `page-church-gallery.php` |
| الموقع | `/church-location/` | `page-church-location.php` |

وده من ترتيب القوالب (الباب 3): لأي صفحة، `WordPress` بيدوّر بالترتيب ده:
```
page-{slug}.php ← page-{id}.php ← page.php ← singular.php ← index.php
```
فبمجرد ما اسم الملف مطابق لرابط الصفحة، بيتختار لوحده. **وخلي بالك:** لو حد غيّر رابط الصفحة من `dashboard`، الملف مش هيتختار، والصفحة هتظهر بالشكل الاحتياطي.

والبديل اسمه `Page Template`: ملف فيه سطر `Template Name` في أوله، والمحرر يختاره من قايمة في الصفحة. وده أنسب لو نفس الشكل هيتستخدم في كذا صفحة. وإحنا كل صفحة ليها شكل لوحدها، فالاسم بالرابط أبسط.

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/themes/template-files-section/page-template-files/
```

### الصفحة نفسها مصدر بياناتها

كل صفحة واجهتها جاية من بياناتها، بدالة `stmina_page_hero()` في ملف `functions.php`:

| الجزء في الواجهة | جاي منين |
|---|---|
| الصورة | الصورة البارزة للصفحة |
| الكلمة الصغيرة، والعنوان، والجزء الدهبي | خانات `ACF` اسمها "واجهة الصفحة" في جنب المحرر |
| سطر الوصف | مقتطف الصفحة `Excerpt` |
| آخر جزء في مسار التنقل | اسم الصفحة |

والصفحات العادية مالهاش مقتطف، فضفناه بسطر واحد:
```php
add_post_type_support( 'page', 'excerpt' );
```

والنص الطويل (قصة الكنيسة، و"إزاي توصل") بيتكتب في محرر الصفحة نفسه، وبيظهر بدالة `the_content()` جوه كلاس `prose` اللي متنسّق في `site.css`.

### أنواع محتوى من غير روابط

الآباء، ومحطات التاريخ، والألبومات أنواع محتوى زي الخدمات، بس **مالهاش صفحات لوحدها**. بتظهر جوه صفحات الكنيسة بس. والفرق في خاصيتين:
```php
'public'             => false, // مالهاش روابط ولا بتظهر في البحث
'show_ui'            => true,  // بس بتظهر في dashboard علشان تتعدّل
'publicly_queryable' => false,
```
ولو كانت `public`، كان هيبقى لكل أب ولكل محطة رابط فاضي ملوش لازمة، ومحركات البحث ممكن تلاقيه.

### الترتيب من المحرر: `menu_order`

الآباء والمحطات والألبومات بتظهر بالترتيب اللي المحرر يحطه في خانة `Order`، لأن ترتيبهم مش أبجدي ولا بالتاريخ. مثلًا المحطات اللي سنتها لسه مش معروفة (0000) ليها مكان محدد في القصة. والخانة دي بتظهر لما نوع المحتوى يدعم `page-attributes`:
```php
'supports' => array( 'title', 'excerpt', 'page-attributes' ),
```

### الصور المرفوعة جوه المحتوى: `post_parent`

ملصقات أقوال كل أب، وصور كل ألبوم، مش في خانات. هي صور مرفوعة **جوه** الأب أو الألبوم. وفي `WordPress` كل صورة ليها خانة اسمها `post_parent` بتقول هي مرفوعة جوه أنهي محتوى، وبنجيبها كده:
```php
get_children( array(
	'post_parent'    => $post->ID,
	'post_type'      => 'attachment',
	'post_mime_type' => 'image',
	'orderby'        => array( 'menu_order' => 'ASC', 'ID' => 'ASC' ),
) );
```
فمحرر المحتوى يفتح الألبوم، ويرفع الصور من زرار `Add Media`، وهي تظهر في المعرض لوحدها. ولو الصورة موجودة في المكتبة قبل كده، يفتحها من `Media Library` ويختار `Attach` للألبوم.

**وده كان البديل لخانة معرض صور:** خانة `Gallery` في `ACF` موجودة في النسخة المدفوعة بس، فاستخدمنا طريقة `WordPress` نفسها.

### المعرض: الصورة بتاخد تصنيف ألبومها

صفحة الصور بتلف على كل الألبومات بالترتيب، وكل صورة بتاخد تصنيف الألبوم بتاعها في الخاصية `data-c`. والفلتر الموجود في `site.js` من التصميم بيشتغل عليها من غير أي تعديل:
```php
<button class="g" data-c="<?php echo esc_attr( $term->slug ); ?>" data-lb data-cap="…">
```
وأزرار الفلتر نفسها بتظهر للتصنيفات اللي فيها صور بس، فلو اتعمل تصنيف جديد فاضي مش هيظهر زرار من غير صور.

### الإدخال من غير تفعيل: نقطة `wp_loaded`

الـ `plugin` كان متفعّل من المهمة اللي فاتت، فإدخال محتوى الكنيسة مش هيشتغل في التفعيل. فبيشتغل مع أول طلب للموقع بعد التحديث، ومرة واحدة بس:
```php
add_action( 'wp_loaded', function () {
	if ( get_option( 'stmina_church_seeded' ) ) {
		return;
	}
	if ( ! add_option( 'stmina_church_seeding', time(), '', false ) ) {
		return;
	}
	stmina_seed_church();
	update_option( 'stmina_church_seeded', STMINA_CONTENT_VERSION );
	// …
} );
```
**ليه سطر `add_option` ده؟** لو طلبين وصلوا للموقع في نفس اللحظة، الاتنين هيلاقوا الإعداد مش موجود ويدخّلوا المحتوى مرتين. ودالة `add_option()` مابتضيفش لو الإعداد موجود وبترجّع `false`، فأول طلب بس هو اللي يكمّل. وده اسمه قفل `lock`.

### الصور مابتتكرّرش

نفس الصورة مستخدمة في خدمة وفي ألبوم. فقبل ما أي صورة تتنسخ للمكتبة، بندوّر عليها باسم ملفها:
```php
get_posts( array(
	'post_type'  => 'attachment',
	'meta_query' => array( array( 'key' => '_wp_attached_file', 'value' => '/' . basename( $path ), 'compare' => 'LIKE' ) ),
) );
```
والخانة `_wp_attached_file` فيها مسار الملف جوه فولدر `uploads`. والنتيجة: 38 صورة في المكتبة من غير ولا صورة متكرّرة.

---

## الباب 14: جدول المواعيد، بيانات واحدة في كذا مكان

### المشكلة

جدول القداسات والاجتماعات بيظهر في تلات أماكن: صفحة العبادة، وقسم المواعيد في الرئيسية، ودايرة "القداس القادم" اللي بتحسب أقرب قداس بـ JavaScript. ولو الجدول مكتوب في كل مكان لوحده، أي تعديل لازم يتعمل تلات مرات، ومسيرهم يختلفوا عن بعض.

**الحل:** كل موعد محتوى لوحده من نوع `stmina_slot`، والتلات أماكن بتقرا من نفس البيانات.

### نوع المحتوى "موعد"

| الخانة | نوعها في `ACF` | مثال |
|---|---|---|
| النوع | `select` | قداس، أو اجتماع ثابت |
| اليوم | `select` بأيام الأسبوع | الجمعة (رقمها 5) |
| من الساعة ولحد الساعة | `time_picker` | `07:00:00` و`09:00:00` |
| ملاحظة | `text` | التربية الكنسية |

**ليه اليوم رقم مش اسم؟** علشان الترتيب، وعلشان JavaScript بيعرف اليوم برقم: دالة `getDay()` بترجّع صفر للأحد. فبنحفظ الرقم، وبنحوّله لاسم وقت العرض بس.

**وخانة الوقت `time_picker`:** المحرر بيختار الساعة من قايمة، فمستحيل يكتب وقت بشكل غلط. وبتتحفظ بصيغة `H:i:s` اللي بتترتّب صح كنص عادي.

### الترتيب

```php
usort( $out, function ( $a, $b ) {
	return array( $a['day'], $a['start'], $a['end'] ) <=> array( $b['day'], $b['start'], $b['end'] );
} );
```
علامة `<=>` اسمها `spaceship operator`، وبتقارن قيمتين وترجّع سالب أو صفر أو موجب. ولما بتقارن مصفوفتين، بتقارن أول عنصر، ولو اتساووا التاني، وهكذا. فالترتيب بقى: باليوم، وبعدين ساعة البداية، ولو قداسين بدأوا مع بعض، اللي بيخلص الأول.

**مشكلة قابلتنا:** أول نسخة كانت بتقارن اليوم والبداية بس، فقداسين الجمعة الساعة 7 ظهروا بعكس التصميم. والحل كان إضافة ساعة النهاية للمقارنة.

### كتابة الوقت بالمصري

دالة `stmina_slot_time()` بتحوّل `07:00:00` و`10:00:00` لـ "7:00 – 10:00 ص":

| الساعة | المختصر | الطويل |
|---|---|---|
| قبل 12 | ص | صباحًا |
| 12 بالظبط | ظ | ظهرًا |
| بعد 12 | م | مساءً |

والشكل الطويل بيظهر في دايرة "القداس القادم". ولو البداية والنهاية فترتين مختلفتين، بيكتب الاتنين: "10:00 ص – 12:00 ظهرًا".

### عنوان الموعد بيتكتب لوحده: نقطة `acf/save_post`

كل محتوى في `WordPress` ليه عنوان، والعنوان بيظهر في جدول المواعيد في `dashboard`. فبدل ما المحرر يكتب عنوان، بنكتبه إحنا بعد الحفظ:
```php
add_action( 'acf/save_post', function ( $post_id ) {
	// …
	wp_update_post( array( 'ID' => $post_id, 'post_title' => $title ) );
}, 20 );
```
**ليه الرقم 20؟** نقطة `acf/save_post` بيشتغل عليها `ACF` نفسه برقم 10 وهو بيحفظ القيم. والرقم الأكبر معناه إن دالتنا تشتغل بعده، فتقرا القيم الجديدة. والرقم ده اسمه أولوية `priority`، والافتراضي 10.

📖 التوثيق الرسمي:
```
https://www.advancedcustomfields.com/resources/acf-save_post/
```

### جزء واحد بتلات أشكال

ملف `template-parts/schedule-days.php` بيرسم الجدول، وبيستقبل نوع المواعيد:
```php
get_template_part( 'template-parts/schedule-days' );                                   // الرئيسية: الكل
get_template_part( 'template-parts/schedule-days', null, array( 'kind' => 'mass' ) );    // العبادة: القداسات
get_template_part( 'template-parts/schedule-days', null, array( 'kind' => 'meeting' ) ); // العبادة: الاجتماعات
```
وتعليم يوم النهارده في الجدول شغّال من غير أي تعديل، لأن ملف `site.js` بيدوّر على `data-day` بنفس رقم اليوم.

### من PHP لدايرة "القداس القادم"

الدايرة بتحسب أقرب قداس في المتصفح، لأن الوقت عند الزائر بيتغيّر كل دقيقة. فبنبعت القداسات من PHP لـ JavaScript بنفس طريقة الباب 9:
```php
$masses = array_map( function ( $slot ) {
	return array( 'd' => $slot['day'], 'h' => (int) $slot['start'], 't' => stmina_slot_time( $slot, true ) );
}, stmina_slots( 'mass' ) );
wp_add_inline_script( 'stmina-home', 'window.STMINA_MASSES = ' . wp_json_encode( $masses ) . ';', 'before' );
```
وملف `home.js` بيقرا `window.STMINA_MASSES`. و`(int) '07:00:00'` بيدّي 7، لأن PHP بياخد الأرقام اللي في أول النص.

### التعديل بيظهر على طول؟

قبول المهمة بيقول إن الجدول يتحدّث "ويظهر فورًا". والخطر هنا الـ `cache`: لو السيرفر حافظ نسخة من الصفحة، التعديل مش هيبان. فاتأكدنا من رد السيرفر نفسه:
```
x-hcdn-cache-status: DYNAMIC
```
ومعناها إن الصفحة بتتبني من جديد مع كل زيارة، فأي تعديل بيظهر على طول. ولما نفعّل الـ `cache` قبل الإطلاق علشان السرعة، هنحتاج نتأكد إنه بيتمسح لوحده مع أي تعديل.

---

## الباب 15: المكتبة، محتوى نوعه بيتحدد لوحده

### عملنا إيه

صفحة "المكتبة" (`/library/`) بتلات تبويبات: العظات، والنشرات، والترانيم. ولكل عظة صفحة (`/sermon/اسمها/`)، وأحدث تلات عظات بيظهروا في الرئيسية. والعظات اللي اتدخلت 6 فيديوهات حقيقية من قناة القمص كيرلس روماني على `YouTube`.

| النوع | الاسم الداخلي | ليه رابط؟ | الخانات |
|---|---|---|---|
| عظة | `stmina_sermon` | أيوه: `/sermon/...` | المتحدث، ورابط الفيديو، وملف صوت، وملف `PDF`، والآية |
| موضوع | `stmina_topic` (تصنيف) | لأ، الفلترة جوه المكتبة | — |
| نشرة | `stmina_bulletin` | لأ، بتفتح ملف `PDF` على طول | الملف، ورقم العدد |
| ترنيمة | `stmina_hymn` | لأ | ملف الصوت، والفريق، والكلمات |

الملفات: `inc/library.php` و`inc/seed-library.php` في الـ `plugin`، و`page-library.php` و`single-stmina_sermon.php` و`template-parts/sermon-card.php` في الـ `theme`.

### عظة من غير صفحة أرشيف: `has_archive => false`

الخدمات كان ليها صفحة أرشيف لوحدها (`/services/`). لكن العظات بتظهر في صفحة فيها النشرات والترانيم كمان، فعملنا صفحة عادية اسمها `library` بملف `page-library.php`، وقفلنا الأرشيف:
```php
'has_archive' => false,
'rewrite'     => array( 'slug' => 'sermon', 'with_front' => false ),
```
**ليه مش أرشيف وجواه النشرات؟** ملف الأرشيف معمول لنوع محتوى واحد، والاستعلام الرئيسي فيه بيجيب عظات بس. وصفحة عادية بتدّينا كمان صورة وعنوان ووصف بنعدّلهم من `dashboard` بخانات "واجهة الصفحة" اللي عملناها في الباب 13.

### قايمة فرعية تحت نوع تاني: `show_in_menu`

النشرات والترانيم صغيرين، فبدل ما يبقى ليهم مكانين في القايمة الجانبية، بيظهروا تحت "العظات":
```php
'show_in_menu' => 'edit.php?post_type=stmina_sermon',
```
القيمة دي هي رابط صفحة العظات في `dashboard`، و`WordPress` بيحط النوع الجديد كبند فرعي تحتها.

### نوع العظة من الخانة اللي فيها قيمة

بدل خانة "النوع" يختارها المحرر، النوع بيتحدد لوحده:
```php
function stmina_sermon_kind( $post ) {
	if ( get_post_meta( $post->ID, 'video_url', true ) ) return 'video';
	if ( get_post_meta( $post->ID, 'audio', true ) )     return 'audio';
	if ( get_post_meta( $post->ID, 'pdf', true ) )       return 'pdf';
	return '';
}
```
**ليه؟** لو فيه خانة نوع، ممكن المحرر يختار "صوت" ويحط رابط فيديو، فالصفحة تبوظ. هنا مفيش حاجة تتلخبط: اللي اتحط هو اللي بيظهر. ودي قاعدة عامة: **ماتطلبش من المستخدم معلومة تقدر تستنتجها.**

### المتحدث: علاقة بين محتويين، خانة `post_object`

المتحدث يا أب من "الآباء الكهنة" اللي عملناهم في الباب 13، يا ضيف. فاستخدمنا خانة `post_object` في `ACF`، وهي قايمة بتختار منها محتوى تاني:
```php
array( 'name' => 'speaker', 'type' => 'post_object', 'post_type' => array( 'stmina_priest' ), 'return_format' => 'id', 'allow_null' => 1 ),
array( 'name' => 'guest_name', 'type' => 'text' ),
```
القيمة اللي بتتحفظ رقم الأب، مش اسمه. ففايدتين:
- لو اسم الأب اتعدّل في صفحة الآباء، بيتعدّل في كل العظات لوحده.
- نقدر نجيب "عظات تانية لنفس الأب" بفلتر على الرقم ده (تحت).

**البديل اللي سبناه:** تصنيف للمتحدثين. كان هيبقى فيه اسم الأب مرتين، مرة في الآباء ومرة في التصنيف.

### البحث بخانة: `meta_query`

في صفحة العظة، قسم "عظات أخرى" بيجيب عظات نفس الأب:
```php
stmina_sermons( array(
	'numberposts'  => 3,
	'post__not_in' => array( $sermon->ID ),   // من غير العظة المفتوحة
	'meta_query'   => array( array( 'key' => 'speaker', 'value' => $speaker['priest'] ) ),
) );
```
خيار `meta_query` بيفلتر بقيمة خانة، زي ما `tax_query` بيفلتر بتصنيف (الباب 11). وخلي بالك إن البحث بالخانات أبطأ من التصنيفات في المواقع الكبيرة، لأن جدول `wp_postmeta` مش معمول للبحث. ولعدد عظات كنيسة، ده مش مشكلة.

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/reference/classes/wp_query/#custom-field-post-meta-parameters
```

### تاريخ العظة هو تاريخ النشر

مافيش خانة تاريخ. كل محتوى في `WordPress` ليه تاريخ نشر، والمحرر يقدر يغيّره لأي يوم قديم من خانة `Publish`. وفي الإدخال حطينا تاريخ رفع كل فيديو على القناة:
```php
wp_insert_post( array( 'post_type' => 'stmina_sermon', 'post_title' => $s[0], 'post_date' => '2023-01-19 15:21:31' ) );
```
**الفايدة:** الترتيب من الأحدث للأقدم شغّال لوحده (`orderby => date`)، ودالة `get_the_date( 'j F Y' )` بتكتب التاريخ بالعربي، لأن لغة الموقع عربي: "19 يناير 2023".

### الفلاتر: من البيانات، وبتختفي لو مالهاش لازمة

صفوف الفلتر (المتحدث، والموضوع، والنوع) بتتبني من العظات الموجودة، مش ثابتة في الكود. وأي صف فيه اختيار واحد بس بيختفي:
```php
$rows = array_filter( $rows, function ( $row ) {
	return count( $row[2] ) > 1;
} );
```
فدلوقتي، والعظات كلها فيديو لأبونا كيرلس، صف "المتحدث" وصف "النوع" مش ظاهرين، وصف "الموضوع" بس ظاهر. ولما تتضاف عظة صوتية، صف النوع بيظهر لوحده.

والفلترة نفسها في المتصفح (ملف `library.js`): كل كارت عليه `data-sp` و`data-t` و`data-k`، والكارت بيظهر لو بيطابق كل الصفوف مع بعض. وده كفاية لحد كام مية عظة. ولو العدد كبر جدًا، الحل يبقى تقسيم صفحات وفلترة من السيرفر بكلاس `WP_Query`.

### فيديو `YouTube` بيتحمّل لما الزائر يدوس

لو حطينا `iframe` بتاع `YouTube` على طول، الصفحة بتحمّل حوالي 1 ميجا كود من `YouTube` حتى لو محدش شغّل الفيديو. فبنعرض صورة الفيديو وزرار تشغيل بس:
```php
<div class="player" data-yt="<?php echo esc_attr( $yt ); ?>">
	<img src="<?php echo esc_url( 'https://i.ytimg.com/vi/' . $yt . '/hqdefault.jpg' ); ?>" alt="">
	<button class="ph" type="button">…</button>
</div>
```
ولما الزائر يدوس، ملف `sermon.js` بيحط مكانها `iframe` من `youtube-nocookie.com`، وده نفس `YouTube` بس مابيحطش `cookies` غير لما الفيديو يشتغل.

ورقم الفيديو بيطلع من أي شكل للرابط بدالة `stmina_youtube_id()` بتعبير منتظم (`regex`)، فالمحرر يقدر يلصق رابط `watch?v=` أو `youtu.be` أو `shorts`.

**ولو الرابط مش `YouTube`** (زي فيسبوك)، بنستخدم دالة `wp_oembed_get()`، ودي بتسأل الموقع التاني عن كود التضمين بطريقة اسمها `oEmbed`:
```php
$embed = wp_oembed_get( $video );
```
ولو الموقع مابيدعمهاش، بيظهر زرار بيفتح الرابط في تاب جديد.

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/reference/functions/wp_oembed_get/
```

### الملفات المرفوعة: خانة `file` ومعلومات الملف

خانات الصوت و`PDF` نوعها `file`، ومحدد فيها الامتدادات المسموحة:
```php
array( 'name' => 'pdf', 'type' => 'file', 'return_format' => 'id', 'mime_types' => 'pdf' ),
```
وبرقم الملف بنجيب كل حاجة عنه:

| الدالة | بترجّع |
|---|---|
| `wp_get_attachment_url( $id )` | رابط الملف |
| `get_attached_file( $id )` | مسار الملف على السيرفر، علشان نحسب حجمه بـ `filesize()` |
| `size_format( $bytes, 1 )` | الحجم مكتوب: "2.4 MB" |
| `wp_get_attachment_metadata( $id )` | للصوت: المدة جاهزة في `length_formatted`، زي "4:12" |

`WordPress` بيقرا مدة ملف الصوت لوحده وقت الرفع، فمش محتاجين خانة للمدة.

### الحالات الفاضية

النشرات والترانيم لسه مالهاش ملفات، فكل تبويب بيقول "هتنزل هنا قريب" لحد ما يترفع أول ملف. وده أحسن من أمثلة وهمية، لأن الزائر مش هيدوس على حاجة ويلاقيها مش شغّالة.

### الأمان

- **كل نص من المحرر بيتعرض بدالة `esc_html()`،** وكل رابط بدالة `esc_url()`، زي الباب 9. وكلمات الترنيمة بتتعرض بدالة `nl2br( esc_html( $lyrics ) )`: الأول بنأمّن النص، وبعدين نحوّل السطور الجديدة لـ `<br>`. **الترتيب مهم:** لو عكسناه، دالة `esc_html()` هتأمّن الـ `<br>` نفسها وتظهر كنص.
- **كود `wp_oembed_get()` بيتطبع من غير تأمين،** لأنه `HTML` جاي من `WordPress` نفسه بعد ما فلتره، ومن مواقع في قايمة مسموحة بس.
- **روابط الملفات اللي بتفتح في تاب جديد** عليها `rel="noopener"`، علشان الصفحة الجديدة ماتقدرش تتحكم في صفحتنا.

### مشكلة قابلتنا: علامة `data-yt` اتكررت

أول رفع، صفحة العظة كان فيها خطأ في المتصفح، والقايمة وزرار الشمعة وقفوا. السبب إن ملف `site.js` المشترك بيدوّر على أي عنصر عليه `data-yt` علشان مشغّل الرئيسية اللي بأجزاء، وإحنا حطينا نفس العلامة على مشغّل العظة. فـ `site.js` دوّر جوه المشغّل على أجزاء مش موجودة، ووقع، والكود اللي بعده ماشتغلش.

**الحل:** غيّرنا اسم العلامة لـ `data-video`. **والدرس:** قبل ما تختار اسم علامة أو كلاس، دوّر عليه في الملفات المشتركة الأول. والخطأ ده مابيبانش غير في المتصفح، فلازم نفحص رسائل `console` بعد كل رفع.

وكمان أي تشغيل فيديو أو صوت بيبعت إشارة `site:media`، و`site.js` بيسمعها ويوقّف ترنيمة الخلفية، علشان مايشتغلوش الاتنين مع بعض.

### الإدخال

بنفس طريقة الباب 13 (نقطة `wp_loaded` بقفل)، بس بأولوية 20:
```php
add_action( 'wp_loaded', function () { … }, 20 );
```
**ليه 20؟** العظات بتتربط بالأب كيرلس، والأب بيتدخل في إدخال الكنيسة اللي على نفس النقطة بالأولوية العادية 10. فالأولوية الأكبر بتضمن إن الأب يكون موجود الأول. والأب بنلاقيه باسمه:
```php
get_posts( array( 'post_type' => 'stmina_priest', 'title' => 'القمص كيرلس روماني', 'fields' => 'ids' ) );
```

---

## الباب 16: الأخبار والمناسبات، نوع واحد بأكتر من شكل

### عملنا إيه

صفحة "الأخبار" (`/news/`)، ولكل خبر صفحة (`/news/اسمه/`). وكل الأخبار والإعلانات والمناسبات نوع محتوى واحد اسمه `stmina_news`، والفرق بينهم خانات:

| الخانة | نوعها | بتعمل إيه |
|---|---|---|
| النوع | `select`: إعلان أو خبر | الفلتر في الصفحة بيفصل بينهم |
| الميعاد المكتوب | `text` | "7 – 22 أغسطس" على الكارت. لو فاضية بيظهر تاريخ النشر |
| الصورة ملصق | `true_false` | الملصق بيبان من فوق ومن غير فلتر الألوان |
| مثبّت فوق | `true_false` | الخبر بيظهر في الدواير اللي فوق |
| مناسبة موسمية | `true_false` | بتظهر خانات زيادة، والصفحة بتتعرض بشكل تاني |

وأماكن ظهور الأخبار:

| المكان | بيعرض إيه |
|---|---|
| الرئيسية | أقرب مناسبة بالعد التنازلي، و3 دواير مثبّتة، وأحدث 3 أخبار |
| صفحة الأخبار | أقرب مناسبة، والدواير، وكل الأخبار. أول 6 ظاهرين والباقي ورا "عرض المزيد" |
| صفحة العبادة | أحدث 3 إعلانات |
| صفحة الخبر | الخبر، وأحدث 3 أخبار تانية |

### ليه نوع واحد مش تلاتة؟

المهمة بتقول إن المناسبة "خبر مميز". ولو عملناها نوع لوحده، المحرر هيحتار يضيفها فين، والرئيسية هتحتاج تجيب من نوعين. فبقى نوع واحد، والمناسبة علامة عليه. وده بيورّينا ميزة مهمة في `ACF`:

### خانات بتظهر بشرط: `conditional_logic`

خانات المناسبة (الميعاد، والآية، ومواعيد الموسم) مابتظهرش للمحرر غير لما يعلّم "مناسبة موسمية":
```php
$season_only = array( array( array( 'field' => 'field_stmina_news_season', 'operator' => '==', 'value' => '1' ) ) );
array( 'name' => 'event_at', 'type' => 'date_time_picker', 'conditional_logic' => $season_only, … ),
```
**ليه 3 مصفوفات جوه بعض؟** الطبقة الخارجية معناها "أو"، واللي جواها "و". فالقاعدة دي معناها: (الخانة دي = 1). ولو عايز "(أ و ب) أو ج" بتكتب مجموعتين في الطبقة الخارجية.

📖 التوثيق الرسمي:
```
https://www.advancedcustomfields.com/resources/conditional-logic/
```

### استبعاد بخانة ممكن ماتكونش موجودة: `NOT EXISTS`

قايمة الأخبار لازم تستبعد المناسبات. والمشكلة إن الخبر اللي اتعمل قبل خانة "مناسبة" مالوش القيمة دي خالص في قاعدة البيانات. فلو كتبنا `is_season != 1` بس، الخبر ده مش هيظهر، لأن المقارنة بتحتاج الخانة تكون موجودة. فبنقول: "الخانة مش موجودة، أو قيمتها مش 1":
```php
$args['meta_query'][] = array(
	'relation' => 'OR',
	array( 'key' => 'is_season', 'compare' => 'NOT EXISTS' ),
	array( 'key' => 'is_season', 'value' => '1', 'compare' => '!=' ),
);
```

### أقرب مناسبة جاية: الترتيب بخانة

```php
get_posts( array(
	'post_type'  => 'stmina_news',
	'meta_key'   => 'event_at',
	'orderby'    => 'meta_value',
	'order'      => 'ASC',
	'meta_query' => array(
		array( 'key' => 'is_season', 'value' => '1' ),
		array( 'key' => 'event_at', 'value' => current_time( 'mysql' ), 'compare' => '>=' ),
	),
) );
```
- `orderby => meta_value` مع `meta_key` بيرتّب بقيمة الخانة بدل تاريخ النشر.
- خانة `date_time_picker` بتتخزّن دايمًا `Y-m-d H:i:s`، والشكل ده بيترتّب ويتقارن صح كنص عادي، زي الساعة في الباب 14.
- دالة `current_time( 'mysql' )` بترجّع الوقت دلوقتي بتوقيت الموقع (القاهرة) بنفس الشكل.

**النتيجة:** بعد ليلة العيد، المناسبة بتختفي من الرئيسية لوحدها، ولو فيه مناسبة تانية جاية بتظهر مكانها. ومحدش محتاج يفتكر يشيلها.

### العد التنازلي: الوقت بتوقيت الموقع

العد التنازلي في `site.js` بيقرا الميعاد من `data-date`. ولازم الميعاد يبقى فيه فرق التوقيت، وإلا متصفح زائر في أوروبا هيحسبه بتوقيته هو:
```php
( new DateTimeImmutable( $at, wp_timezone() ) )->format( 'c' ); // 2027-01-06T23:00:00+02:00
```
دالة `wp_timezone()` بترجّع توقيت الموقع من الإعدادات، و`'c'` هو شكل `ISO 8601` اللي JavaScript بيفهمه.

### قايمة من غير `Repeater`: سطر لكل ميعاد

مواعيد الموسم قايمة (صوم الميلاد، وتسبحة كيهك، وليلة العيد). وخانة `Repeater` اللي بتعمل قوايم في `ACF` موجودة في النسخة المدفوعة بس. فاستخدمنا خانة نص، وكل سطر فيها ميعاد، والأجزاء بينها `|`:
```
صوم الميلاد | يبدأ 25 نوفمبر | 43 يومًا
```
ودالة `stmina_season_rows()` بتقسم النص لسطور، وكل سطر لأجزاء.

### مشكلة قابلتنا: `\R` بتقطّع الحروف العربي

أول نسخة كانت بتقسم السطور كده:
```php
preg_split( '/\R/', $text );
```
والنتيجة إن أول سطر طلع حتت: "يلاد"، و"بر". **السبب:** `\R` معناها "أي نهاية سطر"، ومنها رمز قديم رقمه `0x85`. ومن غير علامة `u` في آخر النمط، PHP بيقرا النص بايت بايت مش حرف حرف. والحروف العربي في `UTF-8` كل حرف منها بايتين، وحروف كتير زي "م" البايت التاني فيها هو `0x85` بالظبط. فـ PHP اعتبر نص الحرف نهاية سطر.

**الحل:** علامة `u` بتقول لـ PHP إن النص `UTF-8`، فبيقرا حرف حرف:
```php
preg_split( '/\R/u', $text );
```
**القاعدة:** أي `regex` على نص عربي في PHP لازم يبقى فيه `u`.

### المشاركة على واتساب وفيسبوك

روابط المشاركة بتاخد الرابط والعنوان في الـ `URL`، فلازم يتحوّلوا لشكل آمن للروابط الأول بدالة `rawurlencode()`. ومن غيرها أي مسافة أو `&` في العنوان هيكسر الرابط:
```php
$share = rawurlencode( get_permalink() );
'https://wa.me/?text=' . rawurlencode( get_the_title() . ' ' ) . $share
```
وبعدين الرابط كله بيتطبع بدالة `esc_url()` زي أي رابط.

### نفس صفحة الخبر بشكلين

ملف `single-stmina_news.php` واحد، وجواه شرط:
```php
if ( $is_season ) :
	// العد التنازلي، والآية بالشمعة، ومواعيد الموسم
else :
	// الملصق الكبير، والتفاصيل، والمشاركة
endif;
```
**البديل:** `WordPress` بيسمح بـ `Page Template` يختاره المحرر، بس ده للصفحات مش لأنواع المحتوى المخصّصة بسهولة، وكمان كان المحرر هيحتاج يختار حاجتين (العلامة والقالب). هنا العلامة لوحدها كفاية.

والملصق جوه زرار عليه `data-lb`، فبيفتح في عارض الصور اللي في `footer.php` من غير أي كود زيادة، لأن `site.js` بيدوّر على أي عنصر عليه العلامة دي.

### الإدخال

- صفحة "الأخبار"، والـ 10 أخبار وإعلانات اللي في التصميم بصورهم، ومناسبة عيد الميلاد 2027.
- **التواريخ الحقيقية مش معروفة،** فكل خبر اتحط أقدم من اللي قبله بدقيقة علشان الترتيب يفضل زي التصميم. والتاريخ ده مابيظهرش، لأن كل خبر ليه "ميعاد مكتوب". وده أحسن من اختراع تواريخ.
- **الوقت بدالة `wp_date()`:** بتاخد وقت عالمي من `time()` وبتحوّله لتوقيت الموقع. والغلط الشائع إنك تجمع فرق التوقيت بإيدك مرتين.

---

## الباب 17: نظام الحضور، الجداول والصلاحيات و`REST`

### عملنا إيه

أول خطوة في `plugin` اسمه `st-mina-attendance`: جداول المخدومين، ودور "خادم"، ومسارات `REST` لإضافة المخدومين وتعديلهم وإيقافهم وكتابة ملاحظات عليهم، واختبارات آلية بتجرّب كل ده بأدوار مختلفة. والشاشات نفسها في الخطوة الجاية.

| الملف | فيه إيه |
|---|---|
| `inc/schema.php` | الجداول، والخدمات، والدور والصلاحية، والتحديث لوحده |
| `inc/members.php` | التحقق من الاسم والموبايل، والإضافة، والتعديل، والقراية، والملاحظات |
| `inc/rest.php` | المسارات وفحص الصلاحية |
| `tests/rest/members.test.mjs` | الاختبارات |

### ليه جداول مخصّصة مش أنواع محتوى؟

في الموقع العام استخدمنا أنواع محتوى (الأبواب 11 لـ 16). لكن الحضور مختلف:

| | نوع محتوى | جدول مخصّص |
|---|---|---|
| عدد السجلات | مناسب لمئات | مناسب لآلاف وأكتر (كل مخدوم في كل جلسة) |
| "الموبايل فريد" | بالكود بس، وممكن يتكسر لو طلبين جم مع بعض | قيد `UNIQUE` في قاعدة البيانات نفسها |
| البحث بأكتر من خانة | بطيء في `wp_postmeta` | سريع بفهارس `KEY` |
| شاشة في `dashboard` | جاهزة | مش محتاجينها، الخدام ليهم شاشات لوحدهم |

### الجداول بدالة `dbDelta()`

| الجدول | فيه إيه |
|---|---|
| `wp_stmina_services` | الخدمات: "إعداد الخدام"، و"اختبار" المستخبية |
| `wp_stmina_members` | الاسم، والموبايل (فريد)، وكود `QR` (فريد)، والحالة، وتاريخ التسجيل |
| `wp_stmina_member_service` | ربط المخدوم بخدمة أو أكتر |
| `wp_stmina_notes` | الملاحظة، وكاتبها، ووقتها |

```php
dbDelta( 'CREATE TABLE ' . stmina_att_table( 'members' ) . " (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  phone varchar(20) NOT NULL,
  …
  PRIMARY KEY  (id),
  UNIQUE KEY phone (phone)
) $charset;" );
```
دالة `dbDelta()` بتقارن الجدول المكتوب بالموجود في قاعدة البيانات، وبتضيف العمود أو الفهرس الناقص **من غير ما تمسح بيانات**. فنفس الكود بيعمل الجدول أول مرة، وبيحدّثه بعدين. بس ليها قواعد كتابة صارمة، ولو اتكسرت بتفشل من غير رسالة:
- كل عمود في سطر لوحده.
- مسافتين بالظبط بعد `PRIMARY KEY`.
- من غير علامة `` ` `` حوالين الأسماء.
- `KEY` مش `INDEX`.

**البادئة:** اسم الجدول بيبدأ بـ `$wpdb->prefix` (غالبًا `wp_`)، لأن كل موقع ممكن تبقى بادئته مختلفة. ودالة `stmina_att_table()` بتعمل ده في مكان واحد.

**ليه مخدوم واحد في أكتر من خدمة؟** المهمة بتقول "النموذج يدعم عدة خدمات من البداية". فبدل عمود `service_id` في جدول المخدومين، فيه جدول ربط. ولو شاب في "إعداد الخدام" واجتماع الشباب، بيبقى سجل واحد برقم موبايل واحد.

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/plugins/creating-tables-with-plugins/
```

### التحديث من غير تفعيل: رقم نسخة الجداول

نقطة `register_activation_hook` مابتشتغلش لما الـ `plugin` يتحدّث، بس لما يتفعّل. فلو ضفنا عمود بعدين، مش هيتضاف. والحل رقم نسخة في الكود، ونسخة محفوظة في قاعدة البيانات:
```php
define( 'STMINA_ATT_DB_VERSION', 1 );

add_action( 'init', function () {
	if ( (int) get_option( 'stmina_att_db_version' ) < STMINA_ATT_DB_VERSION ) {
		stmina_att_install();
	}
}, 5 );
```
فأي تغيير في جدول: نعدّل الكود، ونزوّد الرقم، والتحديث بيحصل لوحده في أول طلب. والفحص ده رخيص، لأن `get_option` بتقرا من ذاكرة محمّلة أصلًا.

### الصلاحيات: الصلاحية مش الدور

```php
add_role( 'stmina_servant', 'خادم', array( 'read' => true, 'stmina_attend' => true ) );
get_role( 'administrator' )->add_cap( 'stmina_attend' );
```
وفي الكود بنسأل دايمًا عن **الصلاحية** `current_user_can( 'stmina_attend' )`، مش عن اسم الدور. ليه؟ لأن المدير مش "خادم"، بس لازم يقدر يدخل. ولو بعدين عملنا دور "أمين خدمة"، بنديله نفس الصلاحية من غير ما نلمس أي فحص.

**وخلي بالك:** الأدوار والصلاحيات بتتحفظ في قاعدة البيانات، مش بتتعرّف مع كل طلب. فدالة `add_role()` بتتنده مرة في التثبيت بس.

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/plugins/users/roles-and-capabilities/
```

### فحص الصلاحية في كل مسار: `permission_callback`

```php
function stmina_att_can() {
	if ( ! is_user_logged_in() ) {
		return new WP_Error( 'rest_not_logged_in', 'لازم تسجّل دخول كخادم.', array( 'status' => 401 ) );
	}
	if ( ! current_user_can( 'stmina_attend' ) ) {
		return new WP_Error( 'rest_forbidden', 'الحساب ده مالوش صلاحية الخدام.', array( 'status' => 403 ) );
	}
	return true;
}
register_rest_route( 'stmina/v1', '/members', array(
	'methods'             => 'GET',
	'permission_callback' => 'stmina_att_can',
	'callback'            => …,
) );
```
- `WordPress` بينده `permission_callback` **قبل** الدالة الأساسية، ولو رجّعت خطأ مابيوصلش للبيانات خالص.
- **الفرق بين 401 و403:** الأول "إنت مين؟" (مش داخل)، والتاني "عارفينك، بس مالكش حق".
- **ليه مش كفاية نخفي الأزرار؟** أي حد يقدر يبعت طلب لرابط `REST` من غير الشاشة. فالحماية الحقيقية في السيرفر، والشاشة بتخفي الأزرار للشكل بس. وده قرار مكتوب في المشروع من الأول.
- **الإيقاف بدل الحذف:** المخدوم بيتوقف بتغيير الحالة، وسجله بيفضل. وده لسه الافتراضي والمنصوح، لكن المسح النهائي بقى متاح لأي خادم في كل الحالات (تحت: "المسح النهائي").


### المسح النهائي (بدأ بشرطين، وبقى متاح في كل الحالات)

**اللي حصل أول مرة:** المستخدم كان ضايف نفسه في التجربة برقم مختلف، وبعدين استورد ملف الكنيسة وهو فيه. فاسمه بقى موجود مرتين، والنظام مافيهوش أي طريقة يشيل التسجيل الغلط. فاتعمل مسار `DELETE /members/<رقم>` بشرطين: للمدير بس، وللمخدوم اللي مالوش أي حضور.

**التغيير (10 أكتوبر 2026، بموافقة المستخدم):** الخادم كان محتار ليه زرار المسح بيبان عند بعض المخدومين وبيختفي عند التانيين (السبب إنه كان بيظهر للي مالوش حضور بس). فاتقرّر إن المسح يبقى **متاح لأي خادم وفي كل الحالات**، ولو المخدوم عنده حضور بيتمسح هو وكل سجلاته معاه.

**الصلاحية بقت زي باقي المسارات:**
```php
'methods'             => 'DELETE',
'permission_callback' => 'stmina_att_can', // خادم بس (زائر 401، ومشترك عادي 403)
```
قبل كده كانت دالة بتفحص `manage_options` كمان علشان تقصر المسح على المدير. دلوقتي بقت نفس `stmina_att_can` بتاعة كل المسارات.

**المسح بينضّف كل اللي يخص المخدوم** علشان مايفضلش سجل حضور "يتيم" (سجل في جدول `records` لمخدوم اتمسح). السجل اليتيم خطر لأنه بيختفي من قايمة الجلسة (الاستعلام `JOIN` مع جدول `members`)، لكنه بيفضل متحسوب في عدّادات الإحصائيات. علشان كده بنمسح السجلات بنفسنا:
```php
$wpdb->delete( stmina_att_table( 'records' ), array( 'member_id' => $member_id ) );
$wpdb->delete( stmina_att_table( 'member_service' ), array( 'member_id' => $member_id ) );
$wpdb->delete( stmina_att_table( 'notes' ), array( 'member_id' => $member_id ) );
$wpdb->delete( stmina_att_table( 'revoked' ), array( 'member_id' => $member_id ) );
$wpdb->delete( stmina_att_table( 'members' ), array( 'id' => $member_id ) );
```
**ملحوظة عن النسب:** لأن سجلات الحضور بتتشال بالكامل، نسب الجلسات القديمة بتتحسب كإن المخدوم ده ماكانش موجود — الأرقام بتتغيّر فعلًا، ومفيش رجوع. ده اللي بنحذّر منه في الشاشة.

**والشاشة:** بيانات المخدوم فيها `can_delete` (بقت `true` دايمًا) و`has_records` (عنده حضور ولا لأ). الزرار بيظهر دايمًا، والتحذير بيتغيّر: لو `has_records` بنقول إن حضوره هيتمسح معاه وإن نسب الجلسات هتتغيّر، وبنقترح الإيقاف بدل المسح لو مش متأكد. والحماية الحقيقية في السيرفر زي كل حاجة.

**ونفس المسار متسجّل مرتين:** `/members/(?P<id>\d+)` متسجّل للقراية والتعديل، وبعدين تاني للحذف. و`register_rest_route()` بتضيف الطرق الجديدة جنب القديمة، مش مكانها. فده عادي، والفصل بيخلّي كل طريقة ليها إعداداتها.

### الأمان في الاستعلامات: `$wpdb->prepare()`

أي قيمة جاية من بره بتعدّي على `prepare()`:
```php
$wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . stmina_att_table( 'services' ) . ' WHERE slug = %s', $slug ) );
```
علامة `%s` للنص و`%d` للرقم، و`prepare()` بتحط القيمة بأمان. ولو كتبنا القيمة في الجملة مباشرة، حد ممكن يبعت نص فيه أوامر `SQL` (ده اسمه `SQL injection`).

**ولـ `LIKE` خطوة زيادة:** علامتي `%` و`_` ليهم معنى خاص في البحث، فبنأمّنهم الأول بدالة `$wpdb->esc_like()`، وبعدين نضيف `%` بتاعتنا:
```php
$args[] = '%' . $wpdb->esc_like( $search ) . '%';
```

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/reference/classes/wpdb/prepare/
```

### التحقق من البيانات

| الخانة | القاعدة | الرسالة |
|---|---|---|
| الاسم | إلزامي، وكلمتين على الأقل | "اكتب الاسم بالكامل، اسمين على الأقل." |
| الموبايل | إلزامي، ومصري 11 رقم بيبدأ بـ 010 أو 011 أو 012 أو 015 | "رقم الموبايل لازم يكون مصري 11 رقم" |
| الموبايل | مش متسجّل لحد تاني | "الرقم ده متسجّل قبل كده لـ (الاسم)." |

ودالة `stmina_att_phone()` بتظبط الرقم قبل التحقق: الأرقام العربي، و`+20`، والمسافات، والصفر اللي `Excel` بيشيله. فالمخدوم بيتحفظ دايمًا بشكل واحد، والبحث والتكرار بيشتغلوا صح.

**والأخطاء بترجع كلها مرة واحدة، كل خطأ باسم خانته:**
```php
return new WP_Error( 'stmina_invalid', 'فيه بيانات محتاجة تتصلّح.', array( 'status' => 400, 'fields' => $errors ) );
```
فالشاشة تقدر تكتب كل رسالة تحت خانتها، والخادم يصلّح كل حاجة مرة واحدة.

**وفوق التحقق، قيد `UNIQUE` في الجدول:** لو خادمين ضافوا نفس الرقم في نفس اللحظة، التحقق ممكن يعدّي الاتنين، بس قاعدة البيانات هترفض التاني. فالكود بيرجّع 409 بدل ما يحفظ مخدوم مكرر.

### كود `QR`: عشوائي وطويل

```php
substr( rtrim( strtr( base64_encode( random_bytes( 24 ) ), '+/', '-_' ), '=' ), 0, 32 );
```
- دالة `random_bytes()` عشوائية آمنة للتشفير، مش زي `rand()` اللي ممكن تتخمّن.
- و`base64` بحروف آمنة في الروابط (`-` و`_` بدل `+` و`/`)، فالكود يتحط في رابط من غير مشاكل.
- 32 حرف معناها أكتر من 190 `bit` عشوائية، فمستحيل حد يخمّن كود مخدوم تاني.

### الاختبارات على `REST`

المهمة طالبة إن كل ده يتجرّب **بأدوار مختلفة**. فعملنا اختبارات بأداة `node:test` المدمجة في `Node.js` (من غير تثبيت أي حاجة)، وبتكلم الموقع المؤقت نفسه:
```bash
node --test tests/rest/*.test.mjs
```

**الدخول في الاختبارات بـ `Application Passwords`:** دي كلمات سر خاصة بالبرامج، بتتعمل من صفحة الحساب في `dashboard`، وبتتبعت في هيدر `Authorization`. ومميزاتها:
- مش هي كلمة سر الدخول، فلو اتسربت مش بتدخّل `dashboard`.
- تقدر تلغيها لوحدها في أي وقت.
- بتشتغل مع `REST` بس.

وكلمات السر في ملف `tests/.env` على الجهاز، والملف ده في `.gitignore` فمابيترفعش على `GitHub`.

**بيانات الاختبار مابتلوّثش البيانات الحقيقية:** كل طلبات الاختبار فيها `service=test`، فالمخدومين بيتضافوا لخدمة "اختبار" المستخبية. وده بيجرّب كمان إن المخدوم في خدمة مش بيظهر في خدمة تانية.

| الاختبار | المتوقع |
|---|---|
| زائر من غير دخول على كل مسار | 401 |
| حساب `Subscriber` على كل مسار | 403 |
| كود `QR` في الرابط أو الهيدر | مرفوض |
| إضافة من غير اسم وموبايل | 400 بخطأ في الخانتين |
| تاريخ التسجيل والكود | بيتعملوا لوحدهم |
| الأرقام العربي و`+20` | بتتحفظ `01…` |
| رقم متكرر | 400 "متسجّل قبل كده" |
| الإيقاف | الحالة `stopped`، والسجل لسه موجود |
| حذف | 404، المسار مش موجود |
| الملاحظات | للخادم بس، وباسم كاتبها |

---

## الباب 18: شاشات الخدام والدخول

### عملنا إيه

شاشات الخدام بقت شغالة على `/attend/`: صفحة الدخول، وقايمة المخدومين بالإضافة والبحث، وملف المخدوم بالتعديل والإيقاف والملاحظات. وكل البيانات بتيجي من مسارات `REST` اللي في الباب 17.

| الرابط | الشاشة |
|---|---|
| `/attend/login/` (و`/attend-login/` من قايمة الموقع) | الدخول |
| `/attend/` و`/attend/members/` | المخدومين |
| `/attend/members/12/` | ملف المخدوم رقم 12 |
| أي شاشة تانية زي `/attend/scan/` | "الشاشة دي جاية قريب" لحد ما مهمتها تتعمل |

| الملف | فيه إيه |
|---|---|
| `inc/app.php` | الروابط، وعرض الشاشة، والدخول، وحد المحاولات، وخانة موبايل الخادم |
| `screens/*.php` | شكل كل شاشة، متولّد من التصميم |
| `assets/attend-app.js` | المشترك: طلبات `REST`، والتنبيه، وتأمين النص |
| `assets/login.js` و`members.js` و`member.js` | سكريبت كل شاشة |
| `tools/convert-attend.js` | أداة التحويل من التصميم |

### صفحة لكل شاشة، والبيانات من `REST`

كان الاتفاق الأول "تطبيق صفحة واحدة". لكن كل شاشة في التصميم ليها تنسيق خاص بيها بأسماء كلاسات متكررة (زي `.card` و`.confirm`)، ولو اتجمعوا في صفحة واحدة هيتضاربوا. فبقت **صفحة لكل شاشة، والبيانات كلها من `REST`** بموافقة المستخدم. والميزة اللي كنا عايزينها من الصفحة الواحدة لسه موجودة: المسح من غير إنترنت في المهام 21 لـ 23 هيشتغل بـ `Service Worker` بيحفظ الصفحات والعمليات على الجهاز.

### روابط مخصّصة: `add_rewrite_rule()`

الشاشات مش صفحات في `WordPress` ولا أنواع محتوى. فبنقول لـ `WordPress` إن الرابط ده معناه متغيّر في الطلب:
```php
add_rewrite_rule( '^attend/members/([0-9]+)/?$', 'index.php?stmina_screen=member&stmina_id=$matches[1]', 'top' );

add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'stmina_screen';
	$vars[] = 'stmina_id';
	return $vars;
} );
```
- الجزء الأول `regex` على الرابط، و`$matches[1]` هو اللي جوه أول قوسين (رقم المخدوم).
- `'top'` معناها القاعدة دي تتجرّب قبل قواعد `WordPress` نفسه.
- فلتر `query_vars` لازم، وإلا `WordPress` بيتجاهل أي متغيّر مايعرفوش.
- **ولازم الروابط تتحدّث** بعد إضافة القاعدة، بنفس طريقة الباب 11: علامة في التثبيت، والتحديث على `init` بأولوية 99. وزوّدنا رقم نسخة الجداول لـ 2 علشان التحديث يحصل لوحده بعد الرفع.

### عرض الشاشة: نقطة `template_redirect`

```php
add_action( 'template_redirect', function () {
	$screen = get_query_var( 'stmina_screen' );
	if ( ! $screen ) {
		return; // مش شاشة من شاشاتنا، كمّل عادي
	}
	nocache_headers();
	header( 'X-Robots-Tag: noindex, nofollow', true );
	…
	include STMINA_ATT_DIR . 'screens/' . sanitize_key( $screen ) . '.php';
	exit;
} );
```
- نقطة `template_redirect` بتشتغل قبل ما `WordPress` يختار ملف من الـ `theme`. فبنعرض الشاشة ونقفل الطلب بـ `exit`، والـ `theme` مابيشتغلش خالص.
- `sanitize_key()` على اسم الشاشة قبل ما يبقى اسم ملف، علشان محدش يبعت `../` ويفتح ملف تاني.
- `nocache_headers()` علشان بيانات الخدام ماتتحفظش في `cache`. و`X-Robots-Tag` علشان محركات البحث ماتأرشفش الشاشات، وده من الحمايات المطلوبة في المشروع.
- **التحويل لصفحة الدخول** لو الزائر مش خادم: ده للراحة بس. والحماية الحقيقية لسه في `permission_callback` بتاع كل مسار.

### الدخول بالبريد أو الموبايل

مسار `POST /stmina/v1/login` بيدوّر على الحساب، وبعدين بيدخّله بدالة `wp_signon()`:
```php
$user = stmina_att_find_user( $req['who'] ); // بريد، أو موبايل من usermeta، أو اسم مستخدم
$ok   = $user ? wp_signon( array(
	'user_login'    => $user->user_login,
	'user_password' => $req['password'],
	'remember'      => (bool) $req['remember'],
), is_ssl() ) : null;
```
- دالة `wp_signon()` بتتأكد من كلمة السر، وبتحط كوكي الدخول في المتصفح، زي صفحة الدخول العادية بالظبط.
- `remember` هي خانة "خليك مسجّل على الجهاز ده": لو متعلّمة الكوكي بيعيش أسبوعين، ولو لأ بيتمسح لما المتصفح يتقفل.
- **الموبايل** متخزّن للخادم في `usermeta` باسم `stmina_phone`. واتضافت خانة "موبايل الخادم" في صفحة الحساب في `dashboard` بنقطتي `show_user_profile` و`edit_user_profile`، وبتتحفظ بنقطتي `personal_options_update` و`edit_user_profile_update`.

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/reference/functions/wp_signon/
```

### حد المحاولات بـ `transient`

```php
$key   = 'stmina_login_' . md5( $ip );
$fails = (int) get_transient( $key );
if ( $fails >= 5 ) {
	return new WP_Error( 'stmina_too_many', 'محاولات غلط كتير. استنى ربع ساعة وجرّب تاني.', array( 'status' => 429 ) );
}
…
set_transient( $key, $fails + 1, 15 * MINUTE_IN_SECONDS );
```
خاصية `transient` قيمة متخزّنة ليها مدة، وبتتمسح لوحدها لما المدة تخلص. فمش محتاجين جدول ولا مهمة تمسح العدّاد. وكود الحالة 429 معناه "طلبات كتير".

**رسالة واحدة لكل الأخطاء:** لو الحساب مش موجود أو كلمة السر غلط، الرسالة واحدة. لأن لو قلنا "الحساب ده مش موجود"، حد ممكن يجرّب أرقام لحد ما يعرف أرقام الخدام.

### رمز `nonce` بين الصفحة و`REST`

الطلبات من المتصفح بتتحسب باسم الخادم الداخل عن طريق الكوكي. بس الكوكي لوحده خطر: أي موقع تاني ممكن يخلّي متصفحك يبعت طلب لموقعنا والكوكي معاه (ده اسمه `CSRF`). فـ `WordPress` بيطلب مع الكوكي رمز `nonce` في هيدر `X-WP-Nonce`، والرمز ده مايعرفوش غير صفحاتنا:
```php
'nonce' => wp_create_nonce( 'wp_rest' ),
```
```js
headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': C.nonce },
```
ولو الطلب جه بالكوكي من غير الرمز، `WordPress` بيعامله كزائر مش داخل، فبياخد 401.

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/
```

### الأمان في JavaScript: تأمين النص قبل `innerHTML`

القايمة بتتبني بـ `innerHTML`، وفيها أسماء كتبها خدام. ولو حد كتب اسم فيه `<script>`، هيتنفّذ عند كل الخدام. فأي نص من المستخدم بيعدّي على دالة `esc()` الأول:
```js
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
`<span class="m-name">${esc(m.full_name)}</span>`
```
ودي نفس فكرة `esc_html()` في PHP (الباب 9)، بس في المتصفح.

### التحويل من التصميم بأداة

بنفس فكرة الباب 9، أداة `tools/convert-attend.js` بتاخد ملف التصميم زي ما هو، وبتشيل حاجات النموذج (لافتة المراجعة، والبيانات الوهمية، وسكريبت النموذج)، وبتحط مكانها سكريبت حقيقي. فأي تعديل في الشكل يتعمل في التصميم، وبعدين:
```bash
node tools/convert-attend.js
```
والأداة بترفض تكمّل لو لقت أي حاجة من النموذج لسه موجودة.

### مشكلة قابلتنا: `hidden` مابيخفيش

علشان نخفي زرار "ابعتله الكارت على واتساب" لحد المهمة 09، كتبنا `element.hidden = true`، والزرار فضل ظاهر. السبب إن خاصية `hidden` بتشتغل بتنسيق جوه المتصفح نفسه (`display: none`)، وأي تنسيق من الموقع بيغلبه. والزرار ده عليه `.btn{display:flex}`. **الحل:** `element.style.display = 'none'`، لأن التنسيق المكتوب على العنصر نفسه أقوى.

### الحاجات المستنية مهام تانية

| الحاجة | المهمة |
|---|---|
| نسبة الحضور وآخر الجلسات في ملف المخدوم | 14 |
| فلتر "محتاج افتقاد" | 18 |
| الاستيراد من `Excel` | 20 |

### الاختبارات

اتضافت 3 حالات للاختبارات (بقوا 19، ونجحوا كلهم):
- كلمة سر غلط بترجع 401 برسالة واحدة. ومحاولة واحدة بس، علشان حد المحاولات مايقفلش الجهاز.
- كل شاشات الخدام بتحوّل الزائر لصفحة الدخول (302).
- صفحة الدخول مفتوحة، وعليها `noindex`.

---

## الباب 19: كارت المخدوم، صفحة عامة بكود سري

### عملنا إيه

| الحاجة | التفاصيل |
|---|---|
| صفحة الكارت | `/me/<الكود>/`: الاسم، وكود `QR`، وزرار "احفظ الكارت كصورة". من غير دخول ومن غير أي تعديل |
| الـ `QR` | جواه رابط الكارت نفسه، فلو المخدوم مسحه بكاميرا موبايله بيفتح صفحته |
| "أرسل الكارت" | في ملف المخدوم وبعد الإضافة: بيفتح واتساب برسالة جاهزة فيها الرابط |
| إعادة الإصدار | كود جديد مكان القديم، فالرابط القديم بيبطل فورًا ويظهر "الرابط ده مش شغال" |
| المسارات | `GET /card/<الكود>` (عام)، و`POST /members/<رقم>/reissue` (للخدام) |

### صفحة عامة من غير كلمة سر: الكود هو المفتاح

المخدوم مالوش حساب. فالرابط نفسه هو اللي بيعرّف صفحته، والحماية إن الكود عشوائي وطويل (32 حرف، الباب 17)، فمستحيل حد يخمّن كود حد تاني. وده نفس فكرة روابط المشاركة في `Google Drive`.

**بس الكود ده مش دخول:** الصفحة بتعرض الاسم والكارت بس، وطلبات `REST` من غير حساب خادم بتاخد 401 حتى بعد فتح الكارت. وده قرار مكتوب في المشروع: "الخادم والمسؤول لا يدخلان بالـ QR أبدًا".

```php
// عامة: مفيش فحص صلاحية
register_rest_route( $ns, '/card/(?P<token>[A-Za-z0-9_-]{32})', array(
	'permission_callback' => '__return_true',
	'callback'            => function ( WP_REST_Request $req ) {
		$m = stmina_att_member_by_token( $req['token'] );
		…
		return array( 'full_name' => $m->full_name, 'card_url' => stmina_att_card_url( $m->qr_token ) );
	},
) );
```
**أقل بيانات ممكنة:** الرد فيه الاسم والرابط بس، من غير الموبايل ولا رقم المخدوم. والاختبارات بتتأكد إن المفاتيح هما الاتنين دول بالظبط، وإن الموبايل مش في الصفحة.

**وخلي بالك:** `permission_callback` لازم تتكتب دايمًا، حتى للمسار العام. لو اتشالت، `WordPress` بيكتب تحذير، لأنه عايزك تقرر عن قصد إن المسار مفتوح.

### صفحة الكارت والكود الغلط

```php
add_rewrite_rule( '^me/([^/]+)/?$', 'index.php?stmina_screen=card&stmina_token=$matches[1]', 'top' );
```
القاعدة بتقبل أي حاجة بعد `/me/`، مش الكود الصح بس. ليه؟ علشان الرابط القديم أو الغلط يوصل لصفحتنا ويظهر "الرابط ده مش شغال، اطلب الرابط الجديد"، بدل صفحة 404 عامة تلخبط المخدوم.

والبحث بالكود بيتأكد من شكله الأول قبل ما يكلم قاعدة البيانات:
```php
if ( ! preg_match( '/^[A-Za-z0-9_-]{32}$/', $token ) ) {
	return null;
}
```

### بيانات من PHP لـ JavaScript من غير `REST`

صفحة الكارت بتاخد الاسم والرابط مع الصفحة نفسها، مش بطلب منفصل، علشان تفتح أسرع على نت ضعيف:
```php
echo '<script>window.STMINA_ATT = ' . wp_json_encode( $config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . ";</script>\n";
```
- `JSON_UNESCAPED_UNICODE`: من غيرها العربي بيتكتب `مي…`. ده شغال برضه، بس الصفحة بتبقى أكبر وصعب تتقري.
- `JSON_HEX_TAG`: بتحوّل `<` و`>` لرموز، فلو حد كتب اسم فيه `</script>` مايقفلش الوسم ويحقن كود. **ودي مهمة جدًا أول ما شلنا تحويل العربي،** لأن الأسماء مكتوبة بإيد الخدام.
- **ومفيش رمز `nonce` في صفحة الكارت:** شلناه، لأن الصفحة عامة ومش محتاجة تكلم مسارات الخدام.

### رسم الـ `QR` في المتصفح

مكتبة `qrcode-generator` من `cdnjs` (زي `GSAP` في الـ `theme`) بتحوّل النص لمصفوفة مربعات، وإحنا بنرسمها `SVG` بنفس شكل التصميم:
```js
const q = qrcode(0, 'M'); // 0 = الحجم يتحدد لوحده، وM = تصحيح أخطاء متوسط (15%)
q.addData(text);
q.make();
q.isDark(y, x); // true = مربع غامق
```
**مستوى التصحيح `M`** معناه إن الكود يتقري حتى لو 15% منه مش واضح (شاشة مكسورة أو انعكاس نور).

**وحفظ الكارت كصورة** بيرسم نفس الكارت على `canvas` بمقاس 1080×1920 (مقاس شاشة الموبايل)، وبعدين `toDataURL('image/png')` وتنزيل.

### شكل الكارت: صورة الصلاة، والـ `QR` في شعاع النور

بطلب المستخدم، الكارت بقى بخلفية صورة الصلاة في ضوء الشباك بدل الكريمي. والمشكلة إن الـ `QR` محتاج تباين عالي علشان الكاميرا تقراه، والصورة فيها نور وضلمة. فالحل:
- **الـ `QR` فاضل على مربع أبيض** بهامش حواليه (`padding`)، لأن قارئ الـ `QR` محتاج "منطقة هادية" فاتحة حوالين الكود. والمربع متحط في نص شعاع النور، بإطار دهبي رفيع ونور خفيف حواليه، فشكله كأن النور واقع عليه.
- **الصورة عليها ضلمة متدرجة** من فوق ومن تحت بس، علشان النص يتقري، والنص في النص يفضل منوّر.
- **الاسم على زجاج خفيف** (`backdrop-filter`)، وطبقة الزجاج على `::before`، زي القاعدة في التصميم: مفيش عنصر `backdrop-filter` جوه عنصر تاني بنفس الخاصية.

**وفي الصورة المحفوظة:** `canvas` مافيهوش `backdrop-filter`، فبنعمل الزجاج بإيدنا: بنرسم الخلفية تاني جوه مستطيل الزجاج بفلتر تمويه، وفوقها لون كريمي شفاف:
```js
x.save(); x.beginPath(); x.roundRect(px, py, pw, ph, pr); x.clip();
cover(bg, .5, .3, 'blur(40px) saturate(1.4)'); // نفس الصورة متموّهة جوه المستطيل بس
x.fillStyle = 'rgba(255,244,228,.09)'; x.fillRect(px, py, pw, ph);
x.restore();
```
ورابط الصورة بناخده من التنسيق نفسه بدالة `getComputedStyle()`، علشان لو الصورة اتغيّرت في التصميم، الصورة المحفوظة تتغيّر معاها.

**ملحوظة:** دقة الصورة 384 في 672 بس، فعلى الشاشة شكلها كويس، بس في الصورة المحفوظة (1080 في 1920) بتبقى ناعمة شوية. ولو فيه نسخة أعلى دقة، الكارت المحفوظ هيبقى أوضح.

### مشكلة قابلتنا: كلاس `.svc` اتضارب

كلمة "إعداد الخدام" تحت في الكارت ظهرت جوه كبسولة غامقة، مع إن التنسيق بتاعها نص بس. **السبب:** ملف `site.css` المشترك فيه كلاس `.svc` لكارت الخدمة في الموقع العام، وشاشات الحضور بتحمّل `site.css`. فاتطبّق تنسيق كارت الخدمة على النص. **الحل:** اسم جديد `.c-svc`. **والدرس** نفس درس الباب 15: قبل أي اسم كلاس جديد، دوّر عليه في `site.css` الأول.

### مشكلة قابلتنا: "احفظ الكارت كصورة" مابيشتغلش على الموبايل

على اللابتوب الزرار كان بينزّل الصورة، وعلى الموبايل مابيعملش حاجة. **السبب:** الطريقة القديمة كانت رابط بخاصية `download` وجواه الصورة كنص طويل (`data:image/png…`). ومتصفحات الموبايل، خصوصًا آيفون، كتير بتتجاهل `download`، ومابتحبش الروابط الطويلة دي.

**الحل:** قايمة المشاركة بتاعة الموبايل نفسه (`Web Share API`)، وفيها "حفظ الصورة" اللي بتنزّلها في المعرض على طول:
```js
const file = new File([blob], name, { type: 'image/png' });
if (navigator.canShare?.({ files: [file] })) {
  await navigator.share({ files: [file], title: `كارت ${card.name}` });
}
```
ولو المتصفح مابيدعمهاش (زي اللابتوب)، تنزيل عادي، بس من `URL.createObjectURL(blob)` بدل النص الطويل.

**وحتة مهمة:** الموبايل بيسمح بفتح قايمة المشاركة بس على طول بعد ما المستخدم يدوس. ورسم الكارت بياخد وقت، فلو بدأناه مع الضغطة، القايمة ممكن ماتفتحش. فبقينا نرسم الكارت أول ما الصفحة تفتح، ونحفظ النتيجة، والضغطة بتشاركها بس.

### رسالة واتساب: رابط `wa.me`

```js
`https://wa.me/2${m.phone}?text=${encodeURIComponent(رسالة)}`
```
- الرقم بصيغة دولية من غير `+`: كود مصر 2، وبعده الرقم بالصفر (`201012345678`).
- `encodeURIComponent()` بتحوّل الرسالة (عربي وسطور جديدة) لشكل آمن في الرابط.
- الإرسال **يدوي**: الزرار بيفتح واتساب والرسالة جاهزة، والخادم يدوس إرسال. وده قرار المشروع (مفيش إرسال تلقائي).

### إعادة الإصدار

```php
function stmina_att_reissue( $member_id ) {
	$token = stmina_att_new_token();
	$wpdb->update( stmina_att_table( 'members' ), array( 'qr_token' => $token, … ), array( 'id' => $member_id ) );
	return $token;
}
```
مفيش جدول للأكواد القديمة: الكود الجديد بيتكتب مكان القديم، فالقديم مابقاش موجود في أي مكان، وبيبطل في نفس اللحظة. وفي الشاشة، الخادم بيأكد الأول، لأن المخدوم لازم ياخد الكارت الجديد.

### الاختبارات

اتضافت 7 حالات (بقوا 26، ونجحوا كلهم): رابط الكارت صح، والكارت العام بيرجّع الاسم والرابط بس، وصفحة الكارت مافيهاش الموبايل ولا `nonce`، والكود مايدخّلش الخدام، وغير الخدام مايعيدوش الإصدار، والرابط القديم بيبطل فورًا، والكود الغلط بيظهر "الرابط ده مش شغال".

---

## الباب 20: الجلسات، قيد فريد ومسار حذف بشرط

### عملنا إيه

الخادم بقى يفتح جلسة: يختار نوع النشاط (قداس، أو اجتماع، أو نشاط، أو خدمة) واليوم، والجلسة بتتحفظ "مفتوحة". وشاشة الجلسات بقت أول شاشة على `/attend/`.

| الحاجة | التفاصيل |
|---|---|
| الجدول | `wp_stmina_sessions`: الخدمة، والنوع، واليوم، والحالة، ومين فتحها وإمتى، وإمتى خلصت |
| أكتر من جلسة مفتوحة | ينفع (قداس الصبح واجتماع بالليل)، بس مش جلستين من نفس النوع في نفس اليوم |
| "زي آخر جلسة" | بيملا النوع بس، والتاريخ بيفضل زي ما هو |
| الحذف | للجلسة المفتوحة بس، علشان لو اتفتحت بالغلط. المنتهية مابتتمسحش |

| المسار | بيعمل إيه |
|---|---|
| `GET /sessions` | الجلسات، الأحدث الأول |
| `POST /sessions` | فتح جلسة `{ kind, date }` |
| `GET /sessions/last` | نوع آخر جلسة والخدمة، من غير التاريخ |
| `GET /sessions/<رقم>` | جلسة واحدة |
| `DELETE /sessions/<رقم>` | حذف جلسة مفتوحة |

### قيد فريد على أكتر من عمود

```sql
UNIQUE KEY one_per_day (service_id,kind,session_date)
```
القيد ده معناه إن قاعدة البيانات نفسها بترفض جلستين لنفس الخدمة من نفس النوع في نفس اليوم. وزي الموبايل في الباب 17، فيه فحص في الكود الأول علشان رسالة واضحة، والقيد ورا الكود علشان لو خادمين داسوا "افتح" في نفس اللحظة. والرسالة بتفرّق بين حالتين:
- "فيه جلسة قداس مفتوحة في اليوم ده فعلًا"، لو الجلسة لسه مفتوحة.
- "جلسة قداس اليوم ده اتعملت وخلصت"، لو انتهت.

وكود الحالة 409 معناه "تعارض": الطلب سليم، بس بيتعارض مع حاجة موجودة.

### التاريخ: من غير ساعة، وبتوقيت الموقع

اليوم بيتخزّن في عمود نوعه `date` (من غير ساعة)، وبيتحقق منه كده:
```php
$d = DateTimeImmutable::createFromFormat( '!Y-m-d', (string) $date, wp_timezone() );
if ( ! $d || $d->format( 'Y-m-d' ) !== $date ) { … 'اختار التاريخ.' }
elseif ( $date > current_time( 'Y-m-d' ) ) { … 'التاريخ ده لسه ماجاش.' }
```
- علامة `!` في أول الصيغة بتصفّر الساعة، فمايتلخبطش اليوم.
- المقارنة `format() !== $date` بترفض تواريخ زي `2026-02-31`، لأن PHP بيحوّلها لوحده لـ 3 مارس، فالنص بيطلع مختلف.
- `current_time( 'Y-m-d' )` هو النهارده بتوقيت القاهرة، مش بتوقيت السيرفر.

**وفي JavaScript نفس الحكاية:** `new Date('2026-10-08')` بيتقري كوقت عالمي، وفي مصر ممكن يبقى اليوم اللي قبله في بعض الساعات. فبنبني التاريخ من الأجزاء:
```js
const toDay = s => { const [y, m, d] = s.split('-').map(Number); return new Date(y, m - 1, d); };
```

### مسار `DELETE` بشرط

```php
function stmina_att_delete_session( $session ) {
	if ( 'open' !== $session->status ) {
		return new WP_Error( 'stmina_closed', 'الجلسة دي خلصت ومينفعش تتمسح.', array( 'status' => 409 ) );
	}
	$wpdb->delete( stmina_att_table( 'sessions' ), array( 'id' => $session->id ) );
	return true;
}
```
في الباب 17 قلنا "مفيش `DELETE` للمخدوم". هنا فيه، بس بشرط: الجلسة المفتوحة بس. ليه؟ الجلسة المنتهية داخلة في نسب الحضور، ومسحها يغيّر نسب كل المخدومين. أما المفتوحة لسه ماتحسبتش، فمسحها مابيأثرش على حد.

### ترتيب المسارات: `/sessions/last` و`/sessions/<رقم>`

المسارين شبه بعض. بس مسار الرقم مكتوب `(?P<id>\d+)`، يعني أرقام بس، فكلمة `last` مش هتتقري على إنها رقم. **لو كان المسار `(?P<id>[^/]+)`،** كان `last` هيتاخد كرقم جلسة، ويرجع "الجلسة دي مش موجودة".

### الشاشة: الأحداث على القسم كله

الجلسات المفتوحة بتتبني من جديد مع كل تغيير بـ `innerHTML`، فلو ربطنا الأزرار بـ `addEventListener` على كل زرار، الربط بيروح مع كل رسم. فبنربط مرة واحدة على القسم اللي شايلهم، ونشوف اتداس على إيه:
```js
$('#current').addEventListener('click', async e => {
  const art = e.target.closest('.cur');
  if (e.target.closest('[data-del]')) { … }
});
```
والطريقة دي اسمها `event delegation`.

### الاختبارات والتكرار

الاختبارات بتفتح جلسة كل مرة بتشتغل. ولأن مينفعش جلستين من نفس النوع في نفس اليوم، أول تشغيل في اليوم كان هينجح والتاني يفشل. **الحل:** كل اختبار بيختار يوم قديم عشوائي، وفي الآخر بيمسح الجلسة. وده درس عام: **الاختبار لازم ينجح لو اتشغّل مرتين ورا بعض.**

اتضافت 8 حالات (بقوا 34): الرفض لغير الخدام، والفتح بالحالة "مفتوحة"، ومنع التكرار، والتاريخ اللي لسه ماجاش، والقايمة، و"زي آخر جلسة" من غير تاريخ، والجلسة مش ظاهرة في خدمة تانية، والحذف.

---

## الباب 21: المسح بالكاميرا وسجل الحضور

### عملنا إيه

الخادم بيفتح شاشة المسح من كارت الجلسة ("ابدأ المسح")، والكاميرا الخلفية بتشتغل، وأول ما كارت يبان قدامها بيتسجّل حضور صاحبه، والنتيجة بتظهر بلون وأيقونة واهتزاز وصوت. والمهمتين 11 و12 اتعملوا مع بعض بموافقة المستخدم.

| النتيجة | معناها | بيتسجّل؟ |
|---|---|---|
| `ok` (تم) | اتسجّل حضوره دلوقتي | أيوه |
| `dup` (اتسجّل قبل كده) | متسجّل في الجلسة دي، وبيظهر الساعة | لأ |
| `revoked` (الكارت ده ملغي) | كارت قديم اتعمله إعادة إصدار | لأ |
| `unknown` (كارت مش معروف) | كود مش لأي مخدوم في الخدمة | لأ |
| `stopped` (موقوف) | المخدوم متوقف | لأ |
| `nosession` (مفيش جلسة مفتوحة) | الجلسة خلصت أو اتمسحت | لأ |

| المسار | بيعمل إيه |
|---|---|
| `POST /sessions/<رقم>/scan` | المسح `{ code }`، والكود ممكن يبقى رابط الكارت كامل |
| `GET /sessions/<رقم>/records` | اللي اتسجّلوا، الأحدث الأول |
| `DELETE /sessions/<رقم>/records/<رقم المخدوم>` | التراجع عن تسجيل، في الجلسة المفتوحة بس |

### الجداول

| الجدول | فيه إيه |
|---|---|
| `wp_stmina_records` | الجلسة، والمخدوم، والحالة (`present` أو `excused` أو `absent`)، والطريقة (`scan` أو `manual` أو `auto`)، ومين سجّل، وإمتى. وقيد `UNIQUE (session_id, member_id)` |
| `wp_stmina_revoked` | الأكواد القديمة بعد إعادة الإصدار، علشان نفرّق بين "ملغي" و"مش معروف" |

**ليه جدول للأكواد الملغية؟** في الباب 19، إعادة الإصدار كانت بتكتب الكود الجديد مكان القديم، فالقديم بيختفي خالص. والمهمة 12 بتطلب رسالتين مختلفتين: "الكارت ده ملغي" (المخدوم معروف، بس معاه كارت قديم) و"كارت مش معروف". فدلوقتي إعادة الإصدار بتحفظ الكود القديم الأول بدالة `$wpdb->replace()`، وده زي `insert`، بس لو الكود موجود بيكتب فوقه.

### "مخدوم واحد في الجلسة" على مستوى البيانات

المهمة بتطلب إن التفرد يبقى "على مستوى البيانات"، مش في الكود بس. والفرق بيبان لما خادمين يمسحوا نفس الكارت في نفس اللحظة:

1. الطلبين بيدوّروا على سجل للمخدوم ده، والاتنين مش لاقيين.
2. الاتنين بيحاولوا يضيفوا سجل.
3. **من غير القيد:** سجلين. **مع القيد:** قاعدة البيانات بتقبل الأول وترفض التاني.

والكود بيتعامل مع الرفض ده على إنه "اتسجّل قبل كده":
```php
$quiet = $wpdb->suppress_errors( true );
$ok    = $wpdb->insert( stmina_att_table( 'records' ), array( … ) );
$wpdb->suppress_errors( $quiet );
if ( $ok ) {
	return array( 'result' => 'ok', … );
}
// القيد رفض: حد سجّله في نفس اللحظة
```
**وليه `suppress_errors`؟** لو `WP_DEBUG` شغال، `$wpdb` بيطبع خطأ قاعدة البيانات في الصفحة، فالرد يبقى `HTML` قبل الـ `JSON` ويبوظ. وهنا الخطأ متوقع، فبنسكّته في الحتة دي بس، وبنرجّع الإعداد زي ما كان.

والاختبارات بتجرّب ده فعلًا: بتبعت مسحتين لنفس الكارت في نفس اللحظة بـ `Promise.all`، وبتتأكد إن واحدة `ok` والتانية `dup`، وإن فيه سجل واحد بس.

### النتيجة دايمًا 200

كل نتايج المسح بترجع بكود 200 وجواها `result`، حتى الرفض. ليه مش 404 للكارت المش معروف؟ لأن المسح نجح كعملية: الخادم مسح، والسيرفر رد بالحالة. وده بيسهّل الشاشة، وبيسهّل كمان طابور المسح من غير نت في المهام 21 لـ 23، لأن كل عملية ليها نتيجة واحدة بنفس الشكل. والأخطاء الحقيقية بس (مش داخل، أو مالكش صلاحية) هي اللي بترجع 401 و403.

### الكود من الـ `QR`

الـ `QR` فيه رابط الكارت كامل (الباب 19)، فالسيرفر بياخد الكود من آخره:
```php
if ( preg_match( '~/me/([A-Za-z0-9_-]{32})/?(?:[?#].*)?$~', $raw, $m ) ) {
	return $m[1];
}
```
علامة `~` بدل `/` حوالين النمط، علشان الرابط نفسه فيه `/` كتير.

### قراية الـ `QR` من الكاميرا

| المتصفح | الطريقة |
|---|---|
| كروم على أندرويد | `BarcodeDetector` المدمج في المتصفح (سريع ومن غير مكتبة) |
| سفاري على آيفون | مكتبة `jsQR` من `jsdelivr`، لأنها مش موجودة على `cdnjs` |

```js
if ('BarcodeDetector' in window) {
  if ((await BarcodeDetector.getSupportedFormats()).includes('qr_code')) detector = new BarcodeDetector({ formats: ['qr_code'] });
}
```
وكل 200 ملي ثانية بناخد صورة من الكاميرا ونقراها. ومع `jsQR` بنصغّر الصورة لعرض 480 الأول، لأن قراية صورة كبيرة على موبايل قديم بطيئة.

**وحمايات صغيرة مهمة:**
- **نفس الكارت قدام الكاميرا** مابيتبعتش تاني قبل 3 ثواني، وإلا كان هيطلع "اتسجّل قبل كده" عشر مرات.
- **طلب واحد في المرة** (`busy`)، فمفيش مسح جديد قبل ما رد اللي قبله يوصل.
- **الكاميرا بتقفل لما الصفحة تستخبى** (`visibilitychange`)، علشان البطارية.
- **الكاميرا محتاجة `HTTPS`:** المتصفحات مابتدّيش الكاميرا لصفحة من غير تشفير، وده من شروط المهمة.

### اختيار الجلسة

"ابدأ المسح" من كارت الجلسة بيفتح `/attend/scan/?session=<رقم>`. ولو الخادم دخل المسح من شريط التنقل، بياخد آخر جلسة اتفتحت. ولو فيه أكتر من جلسة مفتوحة، فوق العنوان قايمة `select` شفافة، فلما الخادم يدوس على اسم الجلسة بتظهر قايمة المتصفح نفسها ويختار.

### الاختبارات

اتضافت 8 حالات (بقوا 42): الرفض لغير الخدام، والمسح بالرابط كامل وحفظ الطريقة والخادم والوقت، والمسح مرتين، والمسحتين في نفس اللحظة، والملغي والمش معروف برسالتين ومن غير تسجيل، والموقوف، والتراجع، والمسح بعد مسح الجلسة.

---

## الباب 22: التسجيل اليدوي والتصحيح وإنهاء الجلسة

### عملنا إيه

المهمتين 13 و14 اتعملوا مع بعض بموافقة المستخدم، لأن شاشة "تفاصيل الجلسة" في التصميم فيها الاتنين.

| الحاجة | فين | المسار |
|---|---|---|
| التسجيل اليدوي بالبحث بالاسم | شاشة المسح، زرار "تسجيل يدوي" | `PUT /sessions/<رقم>/records/<رقم المخدوم>` |
| حضر، أو غاب بعذر، أو غاب | نفس المسار، بـ `{ status }` | |
| تصحيح سجل | تفاصيل الجلسة: تدوس على المخدوم وتختار | نفس المسار |
| كل مخدومين الجلسة بحالاتهم | تفاصيل الجلسة، والتسجيل اليدوي | `GET /sessions/<رقم>/roster` |
| إنهاء الجلسة | تفاصيل الجلسة، ضغط مطوّل ثانية ونص | `POST /sessions/<رقم>/close` |

### مسار واحد للتسجيل اليدوي والتصحيح: `PUT`

`PUT` معناها "خلّي الحاجة دي كده"، سواء كانت موجودة أو لأ. فمسار واحد بيعمل الاتنين:
- **المخدوم مالوش سجل:** سجل جديد بطريقة `manual` باسم الخادم. ولازم الجلسة تبقى مفتوحة والمخدوم نشط.
- **المخدوم ليه سجل:** الحالة بتتغيّر، وبيتحفظ مين عدّل (`updated_by`) وإمتى (`updated_at`). والطريقة الأصلية (مسح أو يدوي) مابتتغيّرش، علشان نفضل عارفين اتسجّل إزاي أول مرة.

**وده الفرق بين `PUT` و`POST`:** لو بعتّ نفس طلب `PUT` مرتين، النتيجة واحدة (المخدوم حالته "حضر"). أما `POST` لفتح جلسة، كل مرة بيحاول يعمل جلسة جديدة. والخاصية دي اسمها `idempotent`، وهتفرق جدًا في المسح من غير نت، لأن الطلب ممكن يتبعت مرتين لو النت قطع في النص.

### عمودين جداد في جدول موجود

اتضاف `updated_by` و`updated_at` لجدول السجلات. وكل اللي عملناه:
1. ضفنا السطرين في `CREATE TABLE` جوه `stmina_att_install()`.
2. زوّدنا `STMINA_ATT_DB_VERSION` من 5 لـ 6.

ودالة `dbDelta()` (الباب 17) لقت العمودين ناقصين وضافتهم، من غير ما تلمس السجلات اللي موجودة. وده بالظبط ليه عملنا رقم النسخة من الأول.

### إنهاء الجلسة: استعلام واحد بيعمل كل الغياب

```sql
INSERT IGNORE INTO wp_stmina_records (session_id, member_id, status, method, recorded_by, recorded_at)
SELECT <الجلسة>, m.id, 'absent', 'auto', 0, <دلوقتي>
FROM wp_stmina_members m
JOIN wp_stmina_member_service ms ON ms.member_id = m.id AND ms.service_id = <الخدمة>
WHERE m.status = 'active' AND DATE(m.registered_at) <= <يوم الجلسة>
  AND NOT EXISTS ( SELECT 1 FROM wp_stmina_records x WHERE x.session_id = <الجلسة> AND x.member_id = m.id )
```
- **`INSERT … SELECT`:** بدل ما نجيب المخدومين في PHP ونضيف كل واحد في طلب، قاعدة البيانات بتعمل الكل في خطوة واحدة. فلو الخدمة فيها 200 مخدوم، ده استعلام واحد مش 200.
- **الشروط هي شروط المهمة بالظبط:** نشط، ومتسجّل في الخدمة يوم الجلسة أو قبلها، ومالوش سجل.
- **`INSERT IGNORE`:** لو حد مسح كارت في نفس لحظة الإنهاء، القيد الفريد بيرفض السجل المكرر بهدوء، من غير ما الاستعلام كله يفشل.
- **`recorded_by = 0`:** معناها "النظام"، والشاشة بتكتب "اتسجّل غاب تلقائي لما الجلسة انتهت".
- **الإنهاء مرة تانية** مابيعملش حاجة، لأن أول سطر في الدالة بيرجع لو الجلسة مش مفتوحة. والقيد الفريد حماية تانية.

و`$wpdb->query()` بترجّع عدد الصفوف اللي اتضافت، فبنرجّعه للشاشة: "الجلسة انتهت، واتسجّل 7 غياب".

### "يوم الجلسة أو قبلها"

المهمة بتقول "مسجّل قبل تاريخ الجلسة". واخترنا "يوم الجلسة أو قبلها" (`<=`)، علشان المخدوم الجديد اللي اتضاف في نفس يوم الاجتماع يبقى من ضمن الجلسة. و`DATE(m.registered_at)` بتشيل الساعة من تاريخ التسجيل، فالمقارنة تبقى يوم بيوم.

### الإنهاء بالضغط المطوّل

الإنهاء مالوش تراجع، فالتصميم بيطلب تأكيدين: زرار "إنهاء الجلسة"، وبعدين ضغطة مطوّلة ثانية ونص. والكود بيبدأ عدّاد مع `pointerdown`، وبيلغيه مع أي `pointerup` أو `pointerleave`:
```js
hold.addEventListener('pointerdown', startHold);
['pointerup', 'pointerleave', 'pointercancel', 'keyup', 'blur'].forEach(t => hold.addEventListener(t, stopHold));
```
و`pointer` بيشتغل مع اللمس والماوس مع بعض. ومن الكيبورد: `Space` أو `Enter` مضغوطين.

### بيانات الاختبار في خدمة "اختبار"

الجلسة المنتهية مابتتمسحش، لأنها داخلة في النسب. بس الاختبارات بتعمل وبتنهي جلسات كل مرة، فكانت هتتراكم. **الحل:** الحذف مسموح للجلسة المنتهية في الخدمة المستخبية بس (`is_hidden = 1`). والخدمات الحقيقية لسه محمية، والاختبار بيتأكد إن آخر خطوة بتمسح جلساتها.

### الاختبارات

اتضافت 10 حالات (بقوا 52): الرفض لغير الخدام، واليدوي بعلامته واسم الخادم، والغياب بعذر، والموقوف مش في القايمة، والتصحيح بمين عدّل، والإنهاء بيسجّل "غاب" للي ماتسجّلوش بس، والإنهاء مرتين، والمسح والتسجيل الجديد مقفولين بعد الإنهاء والتصحيح شغال، واللي اتسجّل بعد يوم الجلسة مايتحسبش، والتنضيف.

---

## الباب 23: الجلسات المنسية، مهام مجدولة بـ `WP-Cron`

### عملنا إيه

لو خادم نسي ينهي جلسة، بتخلص لوحدها بعد ما يومها يعدّي (بتوقيت القاهرة)، بنفس نتيجة الإنهاء اليدوي بالظبط: "غاب" لكل اللي ماتسجّلوش. والمستخدم وافق على ده، لأن المهمة كانت بتقول إنه اقتراح محتاج تأكيد.

والجزء التاني من المهمة (حذف الجلسة المفتوحة بالغلط) كان اتعمل أصلًا مع المهمة 10، واتضاف دلوقتي اختبار إنه مابيسيبش أي سجل.

### إزاي `WordPress` بيشغّل حاجة كل ساعة: `WP-Cron`

```php
add_action( 'stmina_att_autoclose', 'stmina_att_autoclose' );
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'stmina_att_autoclose' ) ) {
		wp_schedule_event( time(), 'hourly', 'stmina_att_autoclose' );
	}
} );
```
- `wp_schedule_event()` بتسجّل "حدث" بيتكرر (كل ساعة هنا). والحدث ده اسمه نقطة `hook` عادية، فبنربط بيها الدالة بـ `add_action` زي أي نقطة.
- `wp_next_scheduled()` بتتأكد إن الحدث مش متسجّل قبل كده، وإلا كل طلب كان هيسجّله تاني.
- الحدث بيتحفظ في قاعدة البيانات (`option` اسمه `cron`)، مش في الكود.

**الحتة المهمة: `WP-Cron` مش ساعة حقيقية.** هو مابيشتغلش لوحده في الخلفية. مع كل زيارة للموقع، `WordPress` بيبص: "فيه حدث وقته عدّى؟" ولو أيوه بيشغّله. فلو الموقع مافيهوش زيارات بالليل، المهمة بتتأخر لحد أول زيارة الصبح.

**وعلشان كده عملنا طبقة تانية:** قايمة الجلسات والمسح بيعملوا نفس الفحص قبل ما يردّوا:
```php
stmina_att_autoclose(); // الجلسات المنسية تخلص قبل ما القايمة تتعرض
```
فالخادم عمره ما هيشوف جلسة امبارح مفتوحة، حتى لو `WP-Cron` اتأخر. والفحص رخيص: استعلام واحد بيدوّر على جلسات مفتوحة يومها قبل النهارده، وغالبًا مابيلاقيش حاجة.

**ولو الموقع كبر** واحتجنا مواعيد دقيقة، الحل إن `Hostinger` نفسه يشغّل `wp-cron.php` كل كام دقيقة (`Cron Jobs` في `hPanel`)، ونقفل التشغيل مع الزيارات بالسطر `define( 'DISABLE_WP_CRON', true );` في `wp-config.php`.

📖 التوثيق الرسمي:
```
https://developer.wordpress.org/plugins/cron/
```

### نفس الدالة للإنهاء اليدوي والتلقائي

```php
function stmina_att_autoclose() {
	$stale = … WHERE status = 'open' AND session_date < النهارده …;
	foreach ( $stale as $s ) {
		stmina_att_close_session( $s );
	}
}
```
الإنهاء التلقائي مابيكتبش منطق جديد، بينده نفس `stmina_att_close_session()` اللي زرار الإنهاء بينده (الباب 22). فالمهمة بتطلب "يعطي نفس نتيجة الإنهاء اليدوي"، والطريقة الوحيدة المضمونة لده إنه يبقى نفس الكود فعلًا.

### إلغاء الحدث مع إلغاء التفعيل

```php
register_deactivation_hook( __FILE__, function () {
	wp_clear_scheduled_hook( 'stmina_att_autoclose' );
} );
```
لو الـ `plugin` اتلغى تفعيله والحدث فضل متسجّل، `WP-Cron` هيفضل يحاول يشغّله كل ساعة، والدالة مش موجودة. فبنشيله.

### الاختبارات اتأثرت: الجلسة اللي لازم تفضل مفتوحة لازم تبقى النهارده

اختبارات المهام 10 لـ 14 كانت بتفتح جلسات بأيام قديمة عشوائية، علشان تتكرر من غير تعارض. بس دلوقتي أي جلسة مفتوحة من يوم فات بتخلص أول ما حد يفتح القايمة أو يمسح. فالاختبارات اللي محتاجة جلسة مفتوحة بقت بتستخدم النهارده بتوقيت القاهرة:
```js
const todayCairo = () => new Intl.DateTimeFormat('en-CA', { timeZone: 'Africa/Cairo' }).format(new Date());
```
`en-CA` بيكتب التاريخ بصيغة `2026-10-08`، وده نفس شكل قاعدة البيانات.

**وفي أول الاختبارات تنضيف:** لو تشغيل قبل كده وقف في النص، ممكن يسيب جلسة النهارده، فتمنع جلسة من نفس النوع (الباب 20). فقبل أي حاجة، كل جلسات خدمة "اختبار" بتتمسح.

**ودرس صغير:** كان فيه اختبار اسمه "الجلسة المنتهية في خدمة حقيقية مابتتمسحش"، بس كان بيجرّب جلسة مش موجودة أصلًا، فاسمه أكبر من اللي بيعمله. اتشال، لأن اختبار اسمه بيقول حاجة ومابيجرّبهاش أسوأ من مفيش اختبار.

اتضافت 3 حالات (بقوا 55): الجلسة المنسية بتخلص لوحدها والسجل الموجود مابيتغيّرش، وجلسة النهارده مابتخلصش، وحذف الجلسة المفتوحة مابيسيبش سجلات.

---

## الباب 24: "حضوري"، حساب واحد لكل الشاشات

### عملنا إيه

صفحة المخدوم (`/me/<الكود>/`) بقى فيها تبويبين: "حضوري" و"كارتي". و"حضوري" فيها:

| الجزء | فيه إيه |
|---|---|
| نسبة حضورك | دايرة بالنسبة، وفلتر نوع النشاط (الكل، قداس، اجتماع، نشاط، خدمة)، و"حضرت كام من كام" |
| ورا بعض | شمعة مولّعة لكل حضور ورا بعض، وشمعة مطفية مستنية الجلسة الجاية |
| آخر 6 شهور | عمود لكل شهر بنسبته، والحالي على اليمين |
| آخر 10 جلسات | النوع والتاريخ والحالة |

ونفس الأرقام اتحطت عند الخادم: دايرة النسبة في قايمة المخدومين، وقسم الحضور في ملف المخدوم (ومعاه "اتسجّل يدوي كام مرة" و"غاب كام مرة ورا بعض").

رسالة الخدام والتثبيت على الموبايل مستخبيين لحد المهمة 19.

### القاعدة: حساب واحد في مكان واحد

المهمة 17 بتقول: "الأرقام في لوحة الخادم تطابق ما يراه المخدوم نفسه". والطريقة الوحيدة المضمونة لده إن الحساب يبقى دالة واحدة، والكل ينادي عليها. فملف `inc/stats.php` فيه:

| الدالة | بترجّع |
|---|---|
| `stmina_att_member_log()` | سجلات المخدوم في الجلسات المنتهية، الأحدث الأول |
| `stmina_att_stats( $log, $kind )` | حضر، وبعذر، والجلسات، والمقام، والنسبة |
| `stmina_att_streak( $log )` | الحضور ورا بعض (الشموع) |
| `stmina_att_away( $log )` | الغياب ورا بعض (للافتقاد في المهمة 18) |
| `stmina_att_months( $log )` | نسبة كل شهر في آخر 6 شهور |
| `stmina_att_my_page()` | كل اللي صفحة "حضوري" محتاجاه |

**وده متجرّب:** اختبار بيجيب أرقام المخدوم من صفحته ومن شاشة الخادم، وبيتأكد إنهم متطابقين بالظبط (`assert.deepEqual`).

### المعادلة

```
النسبة = حضر ÷ (الجلسات − الغياب بعذر)
```
```php
$base = $total - $excused;
'pct' => $base > 0 ? (int) round( $present / $base * 100 ) : null,
```
- **"الجلسات"** هي الجلسات المنتهية اللي للمخدوم فيها سجل. وده نفسه "الجلسات المنتهية بعد تسجيله"، لأن الإنهاء (الباب 22) بيعمل "غاب" لكل اللي كانوا في الخدمة يومها بس. فالمخدوم الجديد مالوش سجل في الجلسات القديمة، فمابتتحسبش عليه.
- **الجلسة المفتوحة** مابتتحسبش لسه، لأن "لسه ماتسجّلش" مش غياب.
- **`null` مش صفر:** لو كل الجلسات بعذر، المقام صفر، والقسمة مستحيلة. والنسبة صفر كانت هتقول "مابيحضرش خالص"، وده غلط. فبنرجّع `null`، والشاشة بتكتب "–".

### الشموع والافتقاد: نفس الفكرة بالعكس

```php
function stmina_att_streak( $log ) {
	$n = 0;
	foreach ( $log as $x ) {
		if ( 'absent' === $x->status ) break;   // الغياب بيقطع
		if ( 'present' === $x->status ) $n++;   // الحضور بيتعدّ، والعذر بيتعدّى
	}
	return $n;
}
```
ودالة `stmina_att_away()` هي نفس الحلقة بالعكس: بتعدّ الغياب لحد أول حضور. وفي الاتنين، الغياب بعذر "بيتعدّى": مابيتحسبش ومابيقطعش. وده قرار مكتوب في التصميم (القسم 5.1).

### بيانات المخدوم: أقل حاجة ممكنة

صفحة "حضوري" عامة بالكود، فالرد بتاعها متصمّم يبقى فيه حاجات المخدوم بس:
- **مفيش:** الموبايل، ولا ملاحظات الخدام، ولا طريقة التسجيل (يدوي أو مسح)، ولا الغياب ورا بعض، ولا أي بيانات عن حد تاني.
- **ورد الخادم** (`/members/<رقم>/stats`) هو نفس الرد، وبيتضاف عليه `away` و`manual` بس.

والاختبار بيتأكد من ده بالمفاتيح نفسها: آخر جلسة لازم يبقى فيها `date` و`kind` و`kind_name` و`status` بالظبط، ومفيش كلمة `method` في الرد كله.

### مشكلة قابلتنا: "حضوري" أعرض من الموبايل

المستخدم فتح الصفحة على موبايل عرضه 393، والكروت كانت طالعة برا الشاشة من الشمال. والقياس قال إن قسم "حضوري" عرضه 411.

**السبب:** القسم معمول `display: grid`، وجواه صف أزرار أنواع النشاط معمول يتسحب بالعرض (`overflow-x: auto`). بس عمود الـ `grid` بيتمد لحد أعرض حاجة جواه، فالصف اللي كان المفروض يتسحب جوه نفسه، مدّ القسم كله.

**الحل:** سطر واحد بيقول إن العمود مايعديش عرض الشاشة:
```css
grid-template-columns: minmax(0, 1fr);
```
`minmax(0, 1fr)` معناها "أصغر حاجة صفر، وأكبر حاجة المساحة المتاحة"، فالعمود مابيتمدش علشان المحتوى. ونفس المشكلة بتحصل مع `flex`، وحلها هناك `min-width: 0`.

**وكمان:** على الشاشات الضيقة (أقل من 420)، آخر زرار في صف الأنواع كان بيبان مقصوص، ومش واضح إن الصف بيتسحب. فبقى ينزل سطر تاني بدل ما يتسحب.

**والدرس:** القياس اللي كنا بنعمله (`scrollWidth` للصفحة كلها) ماكشفش المشكلة، لأن الصفحة عليها `overflow-x: hidden`، فالزيادة بتتقص ومش بتبان في القياس. القياس الصح: عرض كل عنصر بـ `getBoundingClientRect()` مقارنة بعرض الشاشة.

### الاختبارات بأرقام معروفة

علشان نختبر معادلة، لازم نعرف النتيجة الصح من الأول. فالاختبار بيعمل 4 جلسات النهارده بحالات محددة، وجلسة قديمة قبل تسجيل المخدوم:

| الجلسة | الحالة |
|---|---|
| قداس | حضر |
| اجتماع | غاب |
| نشاط | بعذر |
| خدمة | حضر |
| اجتماع قديم | قبل التسجيل، مالوش سجل |

والمتوقع: حضر 2، وبعذر 1، والجلسات 4، والمقام 3، والنسبة 67%. والقداس 100%، والاجتماع 0%، والنشاط "–"، و"ورا بعض" = 1 (الخدمة حضر، والنشاط بعذر بيتعدّى، والاجتماع غاب فبيقطع).

اتضافت 8 حالات (بقوا 63).

---

## الباب 25: الاستيراد من `Excel`، وحالة "الكارت اتبعت"

### عملنا إيه

المستخدم بعت ملف حقيقي فيه 61 مخدوم، فعملنا المهمة 20 قبل 17 علشان يستورده بنفسه من الشاشة.

| الخطوة | اللي بيحصل |
|---|---|
| 1. اختيار الملف | زرار "اختار ملف Excel"، و"نزّل ملف جاهز تملاه" |
| 2. المعاينة | علامة لكل صف (فاتحة هتتضاف، وبرتقالي فيها مشكلة)، والصفوف المرفوضة برقم الصف في الملف وسببها، والأرقام اللي اتظبطت لوحدها |
| 3. بعد الإضافة | قايمة اللي اتضافوا، وزرار واتساب لكل واحد، وعدّاد "اتبعت كام من كام" |

ومعاها الإضافة اللي المستخدم طلبها في المهمة دي: كل مخدوم ليه حالة "الكارت اتبعت ولا لأ"، وفي قايمة المخدومين فلتر "لسه مااتبعتلوش كارت".

### مين بيقرا الملف؟ المتصفح، ومين بيحكم؟ السيرفر

| الجزء | فين | ليه |
|---|---|---|
| فتح ملف `xlsx` | المتصفح، بمكتبة `SheetJS` | قراية `Excel` في PHP محتاجة مكتبة كبيرة على السيرفر. وفي المتصفح المكتبة بتتحمّل بس لما الخادم يختار ملف |
| مين الاسم ومين الموبايل | المتصفح | بيتعرفوا من المحتوى (تحت) |
| هل الصف سليم؟ | السيرفر | نفس قواعد "إضافة مخدوم" بالظبط، ومعاها الأرقام المتسجّلة قبل كده في قاعدة البيانات |

**القاعدة:** أي فحص في المتصفح للراحة بس، والحكم النهائي في السيرفر. لو حد عدّل السكريبت وبعت صفوف غلط، السيرفر هيرفضها.

والمسار واحد بخيار:
```
POST /members/import  { rows: [ { line, name, phone }, … ], commit: false }   ← معاينة
POST /members/import  { rows: [ … ], commit: true }                          ← إضافة
```
والمعاينة بترجع نفس الرد بالظبط، من غير ما تضيف حاجة. فاللي الخادم شافه في المعاينة هو اللي هيحصل.

### الأعمدة من المحتوى، مش من المكان

التصميم كان مفترض إن العمود الأول الاسم والتاني الموبايل. بس ملف المستخدم الحقيقي كان فيه عمود رقم مسلسل الأول. فبقى كل صف بيتقري كده:
```js
const isMobile = v => digitsOf(v).length >= 9;                   // فيه 9 أرقام أو أكتر
const isName   = v => /\p{L}/u.test(String(v ?? '')) && !isMobile(v); // فيه حروف (أي لغة)
const phone = cells.find(isMobile) || '';
const name  = cells.find(isName) || '';
```
- الرقم المسلسل (1، 2، 3) مش حروف ومش 9 أرقام، فبيتجاهل لوحده.
- لو الاسم والموبايل متبدّلين، برضه شغال.
- `\p{L}` معناها "أي حرف في أي لغة"، ومحتاجة علامة `u`، زي درس الباب 16.

**ودرس مهم:** جرّبنا المنطق ده على ملف المستخدم على الجهاز قبل الرفع، وطبعنا أعداد بس (61 صف، كلهم سليمين، و54 رقم ناقصهم الصفر)، من غير أي اسم أو رقم، لأنها بيانات ناس حقيقية. والملف نفسه مادخلش المشروع ولا `GitHub`.

### أسباب الرفض

| السبب | مثال |
|---|---|
| من غير اسم | |
| من غير موبايل | |
| الاسم كلمة واحدة | "مينا" |
| مش رقم موبايل مصري | "12345" |
| مكرر جوه الملف | "الرقم مكرر، نفس رقم صف 2" |
| متسجّل قبل كده | "الرقم ده متسجّل قبل كده لـ (الاسم)" |

والأرقام اللي بتتظبط لوحدها (الصفر اللي `Excel` بيشيله، و`+20`، والأرقام العربي) بتتعلّم `fixed`، والشاشة بتقول "ظبطنا 54 رقم تلقائيًا".

**ليه `Excel` بيشيل الصفر؟** لأنه بيشوف `01012345678` رقم، والرقم مالوش صفر على الشمال. وعلشان كده "الملف الجاهز" اللي بننزّله بيخلّي عمود الموبايل نص (`z: '@'`)، فالصفر يفضل.

### "الكارت اتبعت": عمود، ومسار، وفلتر

| الحاجة | التفاصيل |
|---|---|
| العمود | `card_sent_at` في جدول المخدومين (نسخة الجداول 7) |
| المسار | `POST /members/<رقم>/card-sent` |
| إمتى بيتعلّم | لما الخادم يدوس زرار واتساب: بعد الإضافة، أو في ملف المخدوم، أو في شاشة الاستيراد |
| إمتى بيتلغي | مع إعادة إصدار الكارت، لأن الكارت الجديد لسه مااتبعتش |
| الفلتر | "لسه مااتبعتلوش كارت" في قايمة المخدومين، بيظهر بس لو فيه حد |

**وإحنا مش عارفين الرسالة وصلت فعلًا:** الزرار بيفتح واتساب بالرسالة جاهزة، والخادم هو اللي بيدوس إرسال. فالعلامة معناها "الخادم فتح الرسالة"، وده أقرب حاجة نقدر نعرفها من غير إرسال تلقائي. والكود بيعلّم في نفس لحظة الضغط، من غير ما يمنع الرابط إنه يفتح.

### الاختبارات

اتضافت 4 حالات (بقوا 67): الرفض لغير الخدام، والمعاينة بكل أسباب الرفض ومن غير إضافة، والإضافة بالسليم بس والاستيراد مرة تانية مابيكررش، وعلامة الكارت وإلغاؤها مع إعادة الإصدار.

---

## الباب 26: الحجم الهادي، تعديل كل الشاشات من ملف واحد

### عملنا إيه

المستخدم طلب شاشات الحضور تبقى أهدى: هوامش أوسع على الجنبين، ومحتوى أصغر بحوالي 10%، ومايملاش الشاشة. التعديل كله اتعمل في آخر ملف `design/assets/attend.css` المشترك، ومن غير ما نلمس أي شاشة لوحدها. وبعدين أداة `tools/convert-attend.js` نسخت الملف لـ `plugin` الحضور، ورفعنا ملفين بس، ونسخة الـ `plugin` بقت 0.10.4.

```css
html{font-size:90%}
:root{--gut:24px;--s-3:11px;--s-4:14px;--s-5:18px;--s-6:22px;--s-8:28px;--s-10:36px}
@media (min-width:768px){:root{--gut:32px}}
body :is(.wrap-c,.wrap-d,.wrap-f,.wrap-i,.wrap-m,.wrap-p,.wrap-s,.wrap-x){max-width:468px}
body .login{max-width:380px}
```

### ليه بالشكل ده

| السطر | بيعمل إيه |
|---|---|
| خاصية `font-size` على `html` | كل الخطوط مكتوبة بوحدة `rem`، ودي نسبة من خط `html`. فسطر واحد صغّر كل الخطوط 10% |
| المتغيرات `--s-*` | المسافات مكتوبة بالـ `px`، فمابتصغرش مع الخط. علشان كده غيّرنا قيمها بإيدنا، وكل الشاشات بتقراها من نفس المتغيرات |
| المتغير `--gut` | هامش الجنبين. اتغيّر من 16 لـ 24 على الموبايل، وعلى الشاشات الكبيرة فضل 32 |
| المحدد `body :is(...)` | كل شاشة ليها تنسيق جوه الصفحة نفسها، وبييجي بعد ملف `attend.css`. لو كتبنا `.wrap-c` لوحدها، تنسيق الشاشة هيكسب لأنه جه في الآخر. وكلمة `body` قبلها بتخلي المحدد أقوى، فيكسب مهما كان الترتيب |

**الأزرار مااتصغّرتش:** ارتفاعها بالـ `px` (52)، فهي لسه مريحة للمس حتى بعد تصغير الخط.

**ده للحضور بس:** ملف `attend.css` مابيتحمّلش غير في شاشات `/attend/`، فالموقع العام ماتأثّرش.

### المفهوم: ترتيب التنسيق وقوة المحدد

لما قاعدتين بيغيّروا نفس الخاصية، المتصفح بيختار:
1. **الأقوى في التحديد:** كلاس واحد أضعف من عنصر وكلاس (`body .x`).
2. **ولو متساويين:** اللي جاي في الآخر هو اللي بيكسب.

ودالة `:is()` بتاخد قوة أقوى حاجة جواها، فـ `body :is(.a,.b)` قوته زي `body .a` بالظبط.

### القياس

اتقاسوا كل الشاشات على عرض 393 و360 جوه `iframe` مخفي: الهامش 24 في كل شاشة، ومفيش عنصر طالع برا عرض الصفحة. والعنصر الوحيد اللي ظهر هو شريط الفلاتر في "تفاصيل الجلسة"، وده بيتسحب بالعرض جوه نفسه بخاصية `overflow-x: auto`، فمش مشكلة.

**فخ في القياس:** في الصفحات اللي من اليمين للشمال، شريط التمرير بيبقى على الشمال. فلو قارنت بـ `clientWidth` بس، كل حاجة هتبان طالعة برا. القياس الصح بالمقارنة مع `getBoundingClientRect()` بتاع `html` نفسه.

**التوثيق:** [MDN: Specificity](https://developer.mozilla.org/en-US/docs/Web/CSS/Specificity) و[MDN: rem](https://developer.mozilla.org/en-US/docs/Web/CSS/length#rem).

---

## الباب 27: فقاعات الواجهة، ومقولة بتلف من قسم تاني

### عملنا إيه

بطلب المستخدم، علشان الواجهة كانت ضعيفة في المحتوى:

| التغيير | فين |
|---|---|
| الآية أصغر: أقصاها `3.2rem` بدل `4.3rem` | ملف `assets/home.css` |
| الزرارين اتشالوا، والفقاعات طلعت مكانهم في آخر الواجهة، وبقوا 5 | ملف `front-page.php` |
| فقاعة جديدة لـ"اجتماع الشباب" بصورته (وفقاعة "إعداد الخدام" اتجرّبت واتشالت بطلب المستخدم) | نفس الملف |
| فقاعتي القداس القادم والمقولة أصغر، ومسافة 32 بين زرار "آية اليوم" والآية | ملف `assets/home.css` |
| انتقال ناعم بين صورة الواجهة والقسم اللي بعدها | نفس الملف |
| فقاعة القداس القادم بقت زجاج فضي بدل الكريمي | نفس الملف |
| الهيدر فضي شفاف: الصفحة الحالية في القايمة فضي بدل الدهبي، والزجاج فضي محايد، والإطار اتشال من القايمة والشريط العائم والأيقونات | آخر ملف `assets/site.css` |
| فقاعة المقولة بقت زجاج، وبتلف على أقوال الآباء كل 6 ثواني | ملف `assets/home.js` |
| كروت أقوال الآباء بقت زجاج خفيف بإطار فضي | ملف `assets/home.css` |
| قداس الافتتاح بيبدأ بالجزء 3، في الرئيسية وصفحة النشأة | ملف `template-parts/opening-mass.php` والنسخة اللي في الرئيسية |

ونسخة الـ `theme` بقت 0.7.5. **تعديلات الهيدر في آخر `site.css` بتأثّر على كل الصفحات،** لأن الهيدر واحد في الموقع كله.

### رابط خدمة بعنوانها

الخدمات نوع محتوى، وروابطها عربي ومش ثابتة في الكود. فبندوّر على الخدمة بعنوانها:

```php
$stmina_service_link = function ( $title, $group ) {
	$ids = get_posts( array( 'post_type' => 'stmina_service', 'title' => $title, 'numberposts' => 1, 'fields' => 'ids' ) );
	return $ids ? get_permalink( $ids[0] ) : home_url( '/services/#' . $group );
};
```

- **خانة `title`** في دالة `get_posts()` بتدوّر على العنوان بالظبط.
- **خانة `fields => ids`** بترجّع الأرقام بس، من غير ما تحمّل الموضوع كله. أخف.
- **دالة `get_permalink()`** بتبني الرابط الصح حتى لو اتغيّر شكل الروابط بعدين.
- **ولو الخدمة اتمسحت أو اتغيّر اسمها،** الرابط بيروح لصفحة الخدمات بدل ما يبقى مكسور.

**البديل:** نحط رقم الموضوع ثابت في الكود، بس الرقم بيختلف بين الموقع المؤقت والحقيقي.

### المقولة: مصدر واحد للنص

الأقوال مكتوبة مرة واحدة في قسم "أقوال الآباء". كل كارت عليه علامة `data-short` بنسخة قصيرة من نفس الكلام، وعلامة `data-cap` باسم الأب. وملف `home.js` بيقرا العلامتين دول ويبدّل الفقاعة:

- **التبديل:** بيضيف كلاس `fade` (شفافية صفر بحركة نص ثانية)، يغيّر النص، ويشيله.
- **لما الصفحة تستخبى** (تاب تاني)، التبديل بيقف بحدث `visibilitychange`، علشان مايشتغلش على الفاضي.
- **مفيش `aria-live`:** لو حطيناها، قارئ الشاشة هيقاطع الزائر كل 6 ثواني.
- **من غير `JavaScript`:** بيظهر القول اللي في "إعدادات الرئيسية" زي الأول.

**النسخة القصيرة** من نفس كلام الأب بالظبط، من غير أي إضافة، علشان تتقري جوه دايرة صغيرة.

### التوزيع العشوائي من غير `position: absolute`

الفقاعات في صف `flex` بيلف، وكل واحدة بعرض مختلف (`--w`) ومسافة من فوق مختلفة (`margin-top`). كده بتبان متبعترة، وفي نفس الوقت الصفحة بتتظبط لوحدها على أي عرض. والطفو من حركة `float` الموجودة أصلًا في ملف `site.css`. وعلى الموبايل اتحسبت العروض بالـ `vw`، وخاصية `order` بتخلي القداس والمقولة في الصف الأول، والصور التلاتة تحتهم.

### الآية في النص، والفقاعات تحت

الآية وزرارها في مجموعة لوحدها اسمها `.hero-verse`، عليها `margin-block: auto`. وجوه عمود `flex`، المسافة الفاضية بتتقسم فوقها وتحتها بالتساوي، فالآية بتتوسّط والفقاعات بتنزل لآخر الواجهة لوحدها، على أي ارتفاع شاشة.

### الخط اللي كان بين الواجهة والقسم اللي بعدها

غلاف الموقع صورة ثابتة ورا كل الأقسام، وفوقها طبقة تعتيم بتغمق مع النزول. الطبقة دي كان فيها `backdrop-filter: blur(3px)`. والمتصفح كان بيطبّق الـ `blur` تحت القسم اللي بعد الواجهة بس، فكان فيه خط واضح بين صورة مموّهة وصورة واضحة.

**اكتشفناه إزاي:** أخفينا الطبقات واحدة واحدة، وصوّرنا الشاشة بعد كل مرة، لحد ما الخط اختفى. ولما شلنا التمويه بس، الخط اختفى.

**الحل:** في ملف `home.css`، شلنا التمويه من الطبقة دي، وغمّقناها شوية بداله. وكمان التلاشي في آخر صورة الواجهة بقى على 8 درجات بدل خط مستقيم، فبيبدأ بدري وبيخلص بهدوء.

**الدرس:** أي `backdrop-filter` على طبقة ثابتة ورا محتوى طويل ممكن يتقطّع عند حدود الأقسام. والأأمن نستخدم تعتيم بلون شفاف بس.

### القياس

- **على 1366×768:** الآية 51px، وكل الفقاعات جوه أول شاشة.
- **على 375:** مفيش عنصر طالع برا عرض الصفحة، والفقاعات بتخلص قبل آخر أول شاشة.

**التوثيق:** [get_posts()](https://developer.wordpress.org/reference/functions/get_posts/) و[get_permalink()](https://developer.wordpress.org/reference/functions/get_permalink/).

---

## الباب 28: الخروج، وشاشة "المزيد"، وتغيير كلمة السر

**عملنا إيه:** أيقونة خروج صغيرة فوق في كل شاشات الحضور اللي ورا الدخول، وشاشة "المزيد" (`/attend/more/`) فيها لينك الاستيراد، وكارت "موقع الكنيسة"، ولوح "حسابك" بتغيير كلمة السر والخروج. ولينك الرجوع للموقع في شاشة الدخول كان موجود من الأول.

### الخروج: لينك بيشتغل حتى لو `JavaScript` وقع

الزرار في التصميم لينك عادي لصفحة الدخول، وعليه علامة `data-logout`:

```html
<a class="glass out" href="attend-login.html" data-logout aria-label="خروج من الحساب" title="خروج">…</a>
```

وملف `attend-app.js` (المشترك بين كل الشاشات) بيسمع الضغط على الصفحة كلها مرة واحدة:

```js
document.addEventListener('click', async e => {
  const el = e.target.closest('[data-logout]');
  if (!el) return;
  e.preventDefault();
  if (el.getAttribute('aria-busy')) return;
  el.setAttribute('aria-busy', 'true');
  const r = await api('logout', { method: 'POST' }).catch(() => null);
  location.href = r ? r.redirect : C.base + 'login/';
});
```

- **مستمع واحد على `document`** بدل مستمع لكل زرار. اسمه `event delegation`، وبيشتغل كمان مع أي زرار يتضاف للصفحة بعدين.
- **دالة `closest()`** علشان الضغط ممكن ييجي على الأيقونة اللي جوه اللينك، مش اللينك نفسه.
- **دالة `preventDefault()`** بتمنع اللينك يفتح، علشان نطلب الخروج الأول.
- **علامة `aria-busy`** بتمنع الضغطة التانية لو الإيد اتهزّت.
- **الخروج نفسه** مسار `POST /logout` بيستدعي دالة `wp_logout()`، اللي بتمسح كوكي الدخول وبتقفل الجلسة على السيرفر.

**ليه `POST` مش لينك `GET`؟** لو الخروج بلينك عادي، أي موقع تاني يقدر يحط صورة رابطها هو رابط الخروج ويطلّع الخادم من غير ما يحس. طلب `POST` معاه رمز `X-WP-Nonce` مايتعملش غير من صفحتنا. ودي نفس فكرة `wp_logout_url()` في `WordPress`، اللي بتحط `nonce` في الرابط.

**ليه اللينك رايح لصفحة الدخول؟** لو السكريبت ماتحمّلش، الضغطة بتفتح صفحة الدخول، وهي بترجّع الخادم الداخل لشاشة الجلسات. يعني مفيش حاجة بتبوظ، بس مفيش خروج.

### مكان الزرار في نوعين من الشاشات

| الشاشة | العنوان | مكان الزرار |
|---|---|---|
| الجلسات، والمخدومين، والمزيد | عنوان كبير `.top` | جنب الشارة في مجموعة `.top-end` |
| ملف المخدوم، والاستيراد، والمسح، وتفاصيل الجلسة | شريط زجاج فوق | آخر الشريط بشكل زرار `.ibtn` |

التنسيق المشترك في ملف `attend.css` (كلاس `.out`)، وكل شاشة بتاخد الشكل المناسب من الكلاس اللي معاه.

### التعديل من التصميم، مش من ملفات `PHP`

الشاشات متولّدة من `design/attend-*.html` بأداة `tools/convert-attend.js`. فبدل ما نعدّل 9 ملفات `PHP` بإيدنا، عدّلنا ملفات التصميم بسكريبت صغير، وشغّلنا الأداة. قبلها اتأكدنا إن الأداة لما تشتغل مابتغيّرش أي ملف (يعني الشاشات متطابقة مع التصميم)، علشان مانمسحش تعديل يدوي.

### شاشة "المزيد": أجزاء مهامها لسه ماتعملتش

التصميم فيه ألواح لمهام جاية: رسالة الخدام (19)، والافتقاد (18)، والخدام (24)، وكارت "لوحة الخادم" (17). الأداة بتشيلهم وقت التحويل بقايمة اسمها `LATER`:

```js
const LATER = {
  more: [
    /[ \t]*<!-- رسالة الخدام -->\n[ \t]*<section class="glass pane" data-pane="msg">[\s\S]*?<\/section>\n\n/,
    // ...
  ],
};
```

ولو أي نمط مالقاش حاجة، الأداة بتقف بخطأ، علشان لو التصميم اتغيّر مانعرضش جزء مش شغال من غير ما نحس. ولما مهمة تخلص، بنشيل سطرها من القايمة.

**تبويب مؤجّل:** الافتقاد (المهمة 18) اتأجّل بطلب المستخدم. فالأداة بتشيل سطر التبويب من شريط التنقل في كل الشاشات بنمط واحد بعلامة `g` (يعني كل مرة يلاقيه، مش أول مرة بس). وشريط التنقل كان `grid-template-columns: repeat(5, 1fr)`، يعني 5 أعمدة ثابتة، فلو التبويبات بقت 4 هيفضل عمود فاضي. بقى `grid-auto-flow: column` مع `grid-auto-columns: minmax(0, 1fr)`: كل تبويب بيعمل عمود لوحده، والأعمدة بتتقسم بالتساوي على أي عدد.

**غلطة قابلتنا:** نمط شيل "لافتة المراجعة" كان مفترض إن اللافتة كذا سطر. في "المزيد" كانت سطر واحد، فالنمط فضل يدوّر على أول سطر فيه `</div>` لوحده، وأكل نص الشاشة. الحل: النمط بقى بيقبل الشكلين `(?:[^\n]*<\/div>|[\s\S]*?\n[ \t]*<\/div>)`. **الدرس:** أي نمط فيه `[\s\S]*?` ممكن يعدّي حدود العنصر لو الشكل اتغيّر، فلازم نشوف الناتج بعد التحويل.

### تغيير كلمة السر: مسار `POST /password`

```php
register_rest_route( 'stmina/v1', '/password', array(
	'methods'             => 'POST',
	'permission_callback' => function () {
		return current_user_can( 'stmina_attend' );
	},
	'args'                => array(
		'current'  => array( 'type' => 'string', 'required' => true ),
		'password' => array( 'type' => 'string', 'required' => true ),
	),
	'callback'            => function ( WP_REST_Request $req ) { … },
) );
```

جوه دالة `callback` بالترتيب:

1. **حد المحاولات:** نفس فكرة الدخول، 5 غلط في ربع ساعة. بس العدّاد هنا لكل حساب (`stmina_pw_` ورقم المستخدم)، مش لكل `IP`، لأن اللي بيجرّب هنا داخل فعلًا.
2. **دالة `wp_check_password()`** بتقارن كلمة السر الحالية بالمتشفّرة في الجدول. **ليه نطلبها؟** لو حد لقى موبايل خادم مفتوح، مايقدرش يغيّر كلمة السر ويقفل الحساب على صاحبه.
3. **الغلط بيرجع `400` مش `401`،** لأن ملف `attend-app.js` بيرجّع أي `401` لصفحة الدخول. ومعاه `fields.current` علشان الرسالة تظهر تحت الخانة نفسها.
4. **الطول:** 8 على الأقل، بنفحصه هنا كمان مش في المتصفح بس، لأن أي حد يقدر يبعت الطلب من غير الشاشة.
5. **دالة `wp_update_user()`** بتشفّر كلمة السر الجديدة وتحفظها.

**إيه اللي بيحصل للدخول بعد التغيير؟** دالة `wp_update_user()` لما المستخدم يغيّر كلمة سره هو، بتعمل كوكي دخول جديدة للجهاز ده لوحده. والأجهزة التانية بتخرج، لأن كوكي `WordPress` جواها جزء من كلمة السر المتشفّرة، فالقديمة مابقتش صالحة. ودي ميزة: لو الخادم شاكك إن حد عرف كلمة سره، التغيير بيطلّعه من كل مكان.

**بس فيه مشكلة:** رمز `nonce` بتاع `REST` متربوط بجلسة الدخول. الجلسة اتغيّرت، فالرمز اللي في الصفحة مابقاش صالح، وأي طلب بعده هيترفض. **الحل:** الصفحة بتتحمّل تاني بعد النجاح، فتاخد رمز جديد. والرسالة "اتغيّرت كلمة السر" بتستنى في `sessionStorage` لحد ما الصفحة تفتح.

**كمان:** `WordPress` بيبعت إيميل لصاحب الحساب إن كلمة السر اتغيّرت. ده من الفلتر `send_password_change_email`، وسبناه شغال لأنه تنبيه أمان مفيد.

### البدائل

- **صفحة الحساب في `dashboard`** (`profile.php`): فيها تغيير كلمة السر جاهز، بس الخادم مش المفروض يدخل `dashboard` خالص.
- **مسار `POST /wp/v2/users/me`** الجاهز في `WordPress`: بيغيّر كلمة السر من غير ما يطلب الحالية، وبيفتح تعديل حاجات تانية في الحساب. مسارنا أضيق وأأمن.

**التوثيق:** [wp_logout()](https://developer.wordpress.org/reference/functions/wp_logout/) و[wp_check_password()](https://developer.wordpress.org/reference/functions/wp_check_password/) و[wp_update_user()](https://developer.wordpress.org/reference/functions/wp_update_user/) و[Cookie nonces في REST](https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/#cookie-authentication).

---

## الباب 29: لوحة الخادم، وتنزيل الجدول `Excel`

**عملنا إيه:** شاشة `/attend/dashboard/` (من كارت "لوحة الخادم" في "المزيد") فيها:

- **حضور الكل:** النسبة العامة، ومتوسط الحاضرين، وعمود لكل جلسة طوله على قد عدد اللي حضروا (القداس بلون مختلف)، ونسبة كل نوع نشاط.
- **كل مخدوم:** دايرة بنسبته، ومرتّبين من الأقل حضورًا، أو بالاسم، أو "يدوي كتير". ولما تفتح مخدوم يظهر نسبته في كل نوع، ورسم آخر 6 شهور، وعدد مرات التسجيل اليدوي.
- **الفترة:** "الشهر ده"، و"آخر 3 شهور" (الافتراضي)، و"من الأول".
- **زرار "نزّل جدول الحضور `Excel`":** صف لكل مخدوم، وعمود لكل جلسة، والنسبة في آخر عمود، بنفس الفترة المختارة.
- **الموقوفين برا الأرقام،** وفيه سطر تحت بعددهم.

### مسار واحد بكل اللي الشاشة محتاجاه: `GET /dashboard`

```php
register_rest_route( $ns, '/dashboard', array(
	'methods'             => 'GET',
	'permission_callback' => 'stmina_att_can',
	'args'                => array(
		'service' => array( 'type' => 'string', 'default' => 'i3dad', 'sanitize_callback' => 'sanitize_key' ),
		'period'  => array( 'type' => 'string', 'default' => '3m', 'enum' => array( 'month', '3m', 'all' ) ),
	),
	'callback'            => function ( WP_REST_Request $req ) { … },
) );
```

- **خانة `enum`** بتخلّي `WordPress` يرفض أي فترة تانية بخطأ `400` لوحده، من غير ما نكتب فحص.
- **دالة `stmina_att_can`** هي نفس فحص الصلاحية بتاع كل مسارات الخدام: الزائر `401`، والحساب العادي `403`.
- **الرد فيه:** الجلسات في الفترة (الأحدث الأول، ومع كل واحدة عدد الحاضرين)، والنسب العامة لكل نوع، والمخدومين النشطين بأرقامهم، وعدد الموقوفين، وحد "يدوي كتير" (3).

### نفس الحساب، بس باستعلامين بدل 60

الشرط الأساسي في المهمة: **الأرقام في اللوحة هي نفس اللي المخدوم بيشوفه في "حضوري"**. علشان كده الحساب نفسه مااتكتبش تاني:

| الرقم | الدالة | مستخدمة كمان في |
|---|---|---|
| النسبة لكل نوع | `stmina_att_stats()` | "حضوري"، وملف المخدوم، وقايمة المخدومين |
| رسم آخر 6 شهور | `stmina_att_months()` | "حضوري" |

**اللي اتغيّر هو جلب السجل بس.** لو استدعينا `stmina_att_member_log()` لكل مخدوم، هتبقى 60 استعلام لـ 60 مخدوم. فبدل كده استعلام واحد بيجيب سجل كل المخدومين في الجلسات المنتهية، **بنفس الترتيب بالظبط** (`ORDER BY s.session_date DESC, s.opened_at DESC, s.id DESC`)، وبعدين بنقسّمه على المخدومين في `PHP`:

```php
$logs = array();
foreach ( $rows as $x ) {
	$logs[ (int) $x->member_id ][] = $x;
}
```

وبعدها لكل مخدوم: السجل كله لرسم الشهور، والجزء اللي جوه الفترة للنسب:

```php
$in = $from ? array_values( array_filter( $log, function ( $x ) use ( $from ) { return $x->session_date >= $from; } ) ) : $log;
```

**مقارنة التاريخ كنص** (`'2026-10-08' >= '2026-07-08'`) صح، لأن شكل `Y-m-d` ترتيبه كنص هو نفس ترتيبه كتاريخ.

**النسبة العامة:** مجموع الحضور ÷ مجموع المقامات لكل المخدومين. مش متوسط النسب، لأن المتوسط بيدّي مخدوم حضر جلسة واحدة نفس وزن مخدوم حضر 20.

### فخ: `JSON` بيقلب الـ `array` لقايمة

حالة كل مخدوم في كل جلسة بتتبعت كـ `records`. لو استخدمنا رقم الجلسة كمفتاح، و`PHP` لقى المفاتيح 0 و1 و2 بالترتيب، دالة `json_encode()` هتطلّعها قايمة `[]` مش `{}`. والـ `array` الفاضي كمان بيطلع `[]`. **الحل:** حرف قبل الرقم (`'s' . $x->session_id`)، وتحويل لـ `object`:

```php
'records' => (object) $records,
```

### الفترة بتتحسب على السيرفر

```php
function stmina_att_period_from( $period ) {
	$today = new DateTimeImmutable( current_time( 'Y-m-d' ), wp_timezone() );
	if ( 'month' === $period ) {
		return $today->format( 'Y-m-01' );
	}
	if ( '3m' === $period ) {
		return $today->modify( '-3 months' )->format( 'Y-m-d' );
	}
	return '';
}
```

- **دالة `current_time()`** بترجّع النهارده بتوقيت الموقع (القاهرة)، مش توقيت السيرفر ولا جهاز الخادم.
- **دالة `wp_timezone()`** بتدّي المنطقة الزمنية من إعدادات `WordPress`.

### ملف `Excel` في المتصفح

مكتبة `SheetJS` كانت موجودة في الاستيراد، فاستخدمناها هنا كمان، وبتتحمّل أول ما الخادم يدوس الزرار بس. الملف بيتعمل من **نفس الرد** اللي الشاشة عارضاه، فمستحيل الأرقام تختلف:

```js
const S = D.sessions.slice().reverse(); // الأقدم الأول
const rows = [['الاسم', ...S.map(s => `${s.kind_name} ${dmy(s.date)}`), 'النسبة']];
D.members.slice().sort(byName).forEach(m => {
  const p = m.kinds.all.pct;
  rows.push([m.full_name, ...S.map(s => WORD[m.records['s' + s.id]] || ''), p === null ? '' : p / 100]);
});
const ws = X.utils.aoa_to_sheet(rows);
```

- **دالة `aoa_to_sheet()`** بتعمل شيت من مصفوفة صفوف.
- **النسبة رقم مش نص** (`0.67` بشكل `0%`)، علشان تتفرز وتتحسب جوه `Excel`.
- **الخانة الفاضية** معناها إن المخدوم ماكانش متسجّل في الخدمة يوم الجلسة، فمش محسوبة عليه.
- **إعداد `Views: [{ RTL: true }]`** بيفتح الشيت من اليمين للشمال.
- **دالة `writeFile()`** بتنزّل الملف على طول من غير سيرفر.

**الأمان:** البيانات جاية من مسار محمي، فغير الخادم مايقدرش يجيب الجدول أصلًا. والاختبار بيتأكد إن الزائر بياخد `401` والحساب العادي `403`.

**البديل:** نعمل الملف على السيرفر. ده محتاج كود `PHP` يبني ملف `xlsx` (هو ملف `zip` جواه ملفات `XML`)، أو مكتبة زي `PhpSpreadsheet` بتتركّب بـ `Composer`. الطريقة اللي اخترناها أبسط، ومافيهاش أي حاجة جديدة على السيرفر.

### الاختبارات (8 جداد، والمجموع 76)

مخدوم حضر قداس (تسجيل يدوي) وغاب اجتماع، ومخدوم تاني حضر وبعدين اتوقف، وجلسة من حوالي شهرين، وجلسة أقدم من 3 شهور. وبنتأكد من:

- **الزائر والحساب العادي** مرفوضين.
- **أرقام المخدوم في اللوحة** هي هي بتاعة ملفه و"حضوري"، بمقارنة `deepEqual` على كل الأنواع والشهور.
- **عدد التسجيل اليدوي،** وحالة كل جلسة في الجدول، وعدد الحاضرين في كل جلسة.
- **الموقوف** مش في القايمة ولا في عدد الحاضرين.
- **النسبة العامة** هي مجموع الحضور ÷ مجموع المقامات.
- **كل فترة** بتجيب الجلسات الصح، والفترة الغلط بترجع `400`.

### اختبار الشاشة من غير دخول

المتصفح المدمج مش داخل بحساب خادم. فعملنا صفحة تجربة محلية: نفس ملف الشاشة المتولّد، ونفس `dashboard.js`، بس دالة `fetch` بترجّع بيانات وهمية بنفس شكل رد `/dashboard`. وجرّبنا منها الفلاتر، وفتح مخدوم، وتغيير الفترة، وملف `Excel` (بدّلنا `writeFile` بدالة بتمسك الملف بدل ما تنزّله). وبعد التجربة مسحنا الملفات دي.

**التوثيق:** [current_time()](https://developer.wordpress.org/reference/functions/current_time/) و[wp_timezone()](https://developer.wordpress.org/reference/functions/wp_timezone/) و[Schema في REST: enum](https://developer.wordpress.org/rest-api/extending-the-rest-api/schema/#enum) و[SheetJS](https://docs.sheetjs.com/).

---

## الباب 30: تثبيت "حضوري" على الموبايل (`PWA`)

**عملنا إيه:** صفحة المخدوم (`/me/<الكود>/`) بقت تتثبّت على شاشة الموبايل كتطبيق اسمه "حضوري" بأيقونة لوجو الكنيسة، وبتفتح من غير شريط المتصفح. ولو مفيش نت (جوه الكنيسة مثلًا)، بتفتح آخر نسخة اتشافت، فالكارت والـ QR موجودين. ورسالة الخدام اتأجّلت بطلب المستخدم.

**المفهوم:** تطبيق الويب اللي يتثبّت (`PWA`) محتاج 3 حاجات:

| الحاجة | بتعمل إيه | عندنا |
|---|---|---|
| ملف `manifest` | الاسم، والأيقونة، ورابط البداية، وشكل الفتح | `/me/<الكود>/manifest.webmanifest` |
| سكريبت `service worker` | بيقف بين الصفحة والنت، ويقدر يرجّع نسخة محفوظة | `/me-sw.js` |
| بروتوكول `HTTPS` | شرط لأي `service worker` | الموقع كله `HTTPS` |

### ملف `manifest` لكل مخدوم

مافيش ملف ثابت، لأن **رابط البداية مختلف لكل مخدوم**: التطبيق لازم يفتح على صفحته هو. فعملنا رابط بدالة `add_rewrite_rule()`:

```php
add_rewrite_rule( '^me/([^/]+)/manifest\.webmanifest$', 'index.php?stmina_screen=manifest&stmina_token=$matches[1]', 'top' );
```

ودالة `stmina_att_manifest()` بتدوّر على المخدوم بالكود، وترجّع `JSON`:

```php
echo wp_json_encode( array(
	'id'         => $url,
	'name'       => 'حضوري',
	'start_url'  => $url,
	'scope'      => $url,
	'display'    => 'standalone',
	// ...
), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
```

- **خانة `display`** بقيمة `standalone`: التطبيق بيفتح في شاشة لوحده من غير شريط العنوان.
- **خانة `id` مختلفة لكل مخدوم:** لو اتنين إخوات على نفس الموبايل، كل واحد يقدر يثبّت صفحته.
- **علامة `JSON_UNESCAPED_UNICODE`:** الاسم العربي بيتكتب زي ما هو، مش رموز.
- **الكود الغلط** بيرجّع `404`، فمفيش تطبيق لرابط ملغي.
- **الأيقونة** معمولة من اللوجو على خلفية بلون الصفحة، بمقاسين 192 و512. واللوجو واخد 62% من العرض بس، علشان لو الموبايل قص الأيقونة دايرة (`purpose: maskable`) اللوجو مايتقصّش.

وفي `head` الصفحة بنحط الرابط، ووسوم آيفون، لأن سفاري مابيقراش أيقونات الـ `manifest`:

```php
echo '<link rel="manifest" href="' . esc_url( stmina_att_card_url( $m->qr_token ) . 'manifest.webmanifest' ) . "\">\n";
echo '<link rel="apple-touch-icon" href="' . esc_url( STMINA_ATT_URL . 'assets/icon-192.png' ) . "\">\n";
```

والسطر ده بيتحط في ملف `card.php` من أداة التحويل (قايمة `HEAD`)، علشان الشاشة متولّدة من التصميم.

### سكريبت `service worker` لازم يتقدّم من جذر الموقع

**مجال السكريبت** (الصفحات اللي يقدر يتحكم فيها) هو الفولدر اللي الملف فيه وتحته. لو قدّمناه من فولدر الـ `plugin`، مجاله هيبقى فولدر `assets` بس، ومش هيوصل لـ `/me/`. فعملنا رابط `/me-sw.js` بيقرا الملف ويبعته:

```php
add_rewrite_rule( '^me-sw\.js$', 'index.php?stmina_screen=sw', 'top' );

function stmina_att_service_worker() {
	status_header( 200 );
	header( 'Content-Type: application/javascript; charset=utf-8' );
	header( 'Cache-Control: no-cache' );
	readfile( STMINA_ATT_DIR . 'assets/me-sw.js' );
}
```

**قيمة `no-cache`** بتخلّي المتصفح يسأل السيرفر كل مرة، فلما نرفع نسخة جديدة توصل بسرعة.

### الطريقة: النت الأول، والنسخة المحفوظة لو مفيش نت

```js
self.addEventListener('fetch', e => {
  const req = e.request;
  if (req.method !== 'GET' || !/^https?:$/.test(new URL(req.url).protocol)) return;
  e.respondWith(
    fetch(req)
      .then(res => {
        if (res.ok || res.type === 'opaque') {
          const copy = res.clone();
          caches.open(CACHE).then(c => c.put(req, copy));
        }
        return res;
      })
      .catch(() => caches.match(req).then(r => r || Response.error()))
  );
});
```

- **كل طلب بيروح للنت الأول،** فالمخدوم بيشوف أحدث أرقام لو فيه نت.
- **نسخة من الرد بتتحفظ** بدالة `clone()`، لأن الرد بيتقري مرة واحدة بس.
- **ولو النت وقع** (جزء `catch`)، بيرجع آخر نسخة من `caches`.
- **الرد من نوع `opaque`:** ده رد من موقع تاني (الخطوط والمكتبات)، مش بنقدر نقراه، بس بنقدر نحفظه.
- **الأرقام جوه الصفحة نفسها** (في `STMINA_ATT.me` من `PHP`)، فنسخة الصفحة لوحدها كفاية، من غير أي طلب `REST`.

**اسم النسخة المحفوظة** فيه رقم نسخة الـ `plugin` (مثلًا `hodoury-0.13.1`)، لأن الرقم في رابط التسجيل. ولما الرقم يتغيّر، حدث `activate` بيمسح النسخ القديمة.

**البدائل:**
- **الحفظ الأول، والنت بعدين** (`cache first`): أسرع، بس المخدوم ممكن يشوف أرقام قديمة وهو معاه نت.
- **مكتبة `Workbox`:** بتعمل ده بإعدادات جاهزة، بس ملفنا 20 سطر ومش محتاج مكتبة.

### كارت "حط الصفحة على شاشة موبايلك"

- **في أندرويد** كروم بيبعت حدث `beforeinstallprompt`. بنمسكه، ونعرض زرار "ثبّتها"، ولما يدوس بنستدعي دالة `prompt()`.
- **في آيفون** مفيش الحدث ده، فبنكتب الخطوات بس: المشاركة، وبعدين "Add to Home Screen".
- **الكارت بيستخبى** لو التطبيق متثبّت (`display-mode: standalone`)، أو المخدوم داس "مش دلوقتي" (بتتحفظ في `localStorage`).

**غلطة:** كنا بنخبّي الكارت بعلامة `hidden`. بس تنسيق الكارت فيه `display: grid`، وتنسيق الزرار فيه `display: flex`، والاتنين بيغلبوا علامة `hidden`. فالكارت والزرار كانوا هيفضلوا ظاهرين. **الحل:** خاصية `style.display`.

### غلطة: السكريبت ورا تحويل

أول تجربة، التسجيل فشل برسالة إن الملف ورا تحويل (`behind a redirect`). السبب: `WordPress` بيزوّد `/` في آخر أي رابط بدالة `redirect_canonical()`، فالرابط `/me-sw.js` كان بيتحوّل لـ `/me-sw.js/`، والمتصفح بيرفض أي `service worker` وراه تحويل. **الحل:** فلتر بيقفل التحويل للملفين دول بس:

```php
add_filter( 'redirect_canonical', function ( $redirect ) {
	return in_array( get_query_var( 'stmina_screen' ), array( 'sw', 'manifest' ), true ) ? false : $redirect;
} );
```

**ومعاها درس تاني:** التحويل كان `301`، والمتصفح بيحفظه. فحتى بعد التصليح، نفس الرابط فضل بيتحوّل في المتصفح ده. وده سبب إن رقم النسخة في الرابط مفيد: زوّدناه لـ 0.13.1، فالرابط اتغيّر.

### تحديث الروابط من غير ما الجداول تتغيّر

قواعد الروابط الجديدة محتاجة دالة `flush_rewrite_rules()`. وقبل كده كانت بتتعمل بس لما نسخة الجداول تتغيّر. فزوّدنا رقم للقواعد نفسها:

```php
define( 'STMINA_ATT_RULES', '2' );

// في schema.php على init بأولوية 99
if ( get_option( 'stmina_att_flush' ) || STMINA_ATT_RULES !== get_option( 'stmina_att_rules' ) ) {
	update_option( 'stmina_att_rules', STMINA_ATT_RULES );
	flush_rewrite_rules();
}
```

أي قاعدة جديدة بعد كده: نزوّد الرقم، والروابط تتحدّث لوحدها مع أول طلب بعد الرفع.

### اتأكدنا إزاي

- **4 اختبارات جداد (والمجموع 80):** الصفحة فيها رابط الـ `manifest`، والـ `manifest` بيفتح على رابط المخدوم من غير تحويل، والكود الغلط بيرجّع `404`، والسكريبت بيتقدّم من الجذر من غير تحويل.
- **في المتصفح المدمج:** السكريبت اتفعّل ومجاله `/me/`. وبعد فتح الصفحة مرتين، اتحفظ 12 ملف: الصفحة بأرقامها، والتنسيق، والسكريبتات، ومكتبة الـ QR، والخطوط، والصور.

**التوثيق:** [Web App Manifest](https://developer.mozilla.org/en-US/docs/Web/Manifest) و[Service Worker API](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API) و[redirect_canonical()](https://developer.wordpress.org/reference/functions/redirect_canonical/) و[add_rewrite_rule()](https://developer.wordpress.org/reference/functions/add_rewrite_rule/).

---

## الباب 31: دخول المخدوم بالموبايل والرقم السري

**عملنا إيه:** المخدوم كان بيوصل لصفحته بالرابط اللي جاله على واتساب بس. دلوقتي:

1. **من صفحته** (الرابط)، فيه كارت "اعمل رقم سري": 4 أرقام مرتين.
2. **من صفحة الدخول،** تبويب "مخدوم": موبايله والرقم السري، فيفتح صفحته كلها ("حضوري" و"كارتي").
3. **لو نسي الرقم السري،** الخادم بيدوس "إعادة إصدار" الكارت: الرابط القديم بيبطل، والرقم السري بيتمسح، ويعمل رقم جديد من الرابط الجديد.

**ليه رقم سري مش الاسم والموبايل؟** الاسم والموبايل مش سر، وزمايله عارفينهم. وأي حد يفتح الصفحة ياخد الـ QR، والـ QR ممكن يسجّل حضور لحد مش موجود. والرقم السري المخدوم بس اللي عارفه.

### عمود جديد بـ `dbDelta()`

```php
  card_sent_at datetime DEFAULT NULL,
  pin_hash varchar(255) DEFAULT NULL,
```

ورقم نسخة الجداول بقى 8. ودالة `dbDelta()` بتقارن الجدول الموجود بالتعريف، وبتزوّد العمود الناقص من غير ما تمسح أي بيانات. والتحديث بيشتغل لوحده على `init` لما الرقم المتخزّن يبقى أقل (الباب 17).

**الطول 255** علشان شكل التشفير ممكن يتغيّر: `WordPress` من 6.8 بيستخدم `bcrypt`، وناتجه حوالي 60 حرف، وممكن يكبر بعدين.

### الرقم السري بيتحفظ متشفّر

```php
$wpdb->update( stmina_att_table( 'members' ), array( 'pin_hash' => wp_hash_password( $pin ) ), array( 'id' => $m->id ) );
```

- **دالة `wp_hash_password()`** هي نفس اللي بتشفّر كلمات سر `WordPress`. التشفير ده في اتجاه واحد: محدش يقدر يرجّع الرقم من الناتج، ولا حتى إحنا.
- **ودالة `wp_check_password()`** بتشفّر اللي اتكتب وتقارنه بالمتخزّن.
- **الرقم نفسه** مابيطلعش في أي رد. حتى الصفحة بتعرف "عنده رقم ولا لأ" بس (`has_pin`).

### مين يقدر يعمل الرقم السري؟

مسار `POST /me/<الكود>/pin` مفتوح من غير دخول (`__return_true`)، لأن **الكود اللي في الرابط هو الإثبات**، بنفس منطق صفحة "حضوري" نفسها: اللي معاه الرابط هو صاحب الصفحة.

**فخ `nonce`:** الطلب ده بيتبعت بـ `fetch` عادي مع `credentials: 'omit'`، مش بدالة `api()` المشتركة. السبب: لو خادم داخل بحسابه فتح صفحة مخدوم، المتصفح هيبعت كوكي الدخول. و`WordPress` لما يلاقي كوكي دخول في طلب `REST` من غير `nonce` صح، بيرفضه بـ `403`. ومن غير الكوكي، الطلب بيتعامل كزائر عادي، وده اللي محتاجينه.

### دخول المخدوم: `POST /member-login`

```php
if ( ! $m || empty( $m->pin_hash ) || '' === $pin || ! wp_check_password( $pin, $m->pin_hash ) ) {
	stmina_att_fail( $ip_key, 15 * MINUTE_IN_SECONDS );
	if ( $phone ) {
		stmina_att_fail( $ph_key, HOUR_IN_SECONDS );
	}
	return new WP_Error( 'stmina_bad_member_login', 'رقم الموبايل أو الرقم السري مش صح. راجعهم وحاول تاني.', array( 'status' => 401 ) );
}
return array( 'redirect' => stmina_att_card_url( $m->qr_token ) );
```

- **رسالة واحدة لكل الغلط:** الرقم مش متسجّل، أو مالوش رقم سري، أو الرقم السري غلط. كده محدش يعرف من الرسالة مين متسجّل في الخدمة.
- **مفيش كوكي ولا جلسة:** الرد رابط صفحته، والرابط نفسه هو المفتاح زي الأول.
- **حدّين للمحاولات:** 5 غلط في ربع ساعة لكل جهاز (`IP`)، و10 غلط في الساعة لكل رقم موبايل.

**ليه حد لكل موبايل كمان؟** 4 أرقام يعني 10,000 احتمال بس. حد الجهاز لوحده مايكفيش لو حد جرّب من كذا جهاز أو كذا شبكة. ومع حد الموبايل، أقصى حاجة 240 محاولة في اليوم، يعني محتاج أكتر من 40 يوم في المتوسط علشان يلاقي الرقم.

**والحدود بتتصفّر** لما الدخول ينجح (`delete_transient()`)، علشان المخدوم اللي غلط مرتين مايفضلش متعاقب.

### تبويبين في صفحة الدخول (نمط `tablist`)

```html
<div class="who" role="tablist" aria-label="إنت مين؟">
  <button type="button" role="tab" id="t-servant" aria-selected="true" aria-controls="p-servant">خادم</button>
  <button type="button" role="tab" id="t-member" aria-selected="false" aria-controls="p-member" tabindex="-1">مخدوم</button>
</div>
```

- **علامات `role` و`aria-selected`** بتخلّي قارئ الشاشة يقول "تبويب، 1 من 2، متحدد".
- **علامة `tabindex="-1"`** على التبويب المش متحدد، والأسهم بتنقل بينهم، زي أي `tablist`.
- **الرابط `/attend/login/#member`** بيفتح على المخدوم على طول، فينفع يتبعت لوحده.
- **حالة الصفحة** (`data-state` على `body`) واحدة، بس بتأثّر على الفورم الظاهر بس، لأن التاني جوه `tabpanel` مستخبي.

### الرجوع للموقع من صفحة المخدوم

- **فوق في صفحته** أيقونة بيت بتودّي لموقع الكنيسة، بنفس شكل أيقونة الخروج عند الخدام (كلاس `.out`). مفيش "خروج" للمخدوم، لأن مفيش جلسة دخول أصلًا.
- **ورسالة "الرابط ده مش شغال"** بقى تحتها زرار "ادخل بموبايلك والرقم السري" بيفتح `/attend/login/#member`، ولينك للموقع.

**أداة التحويل:** كانت بتحوّل `href="attend-login.html"` بس. فزوّدنا الجزء اللي بعد `#` لو موجود:

```js
h = h.replace(/href="attend-([a-z]+)\.html(#[a-z]+)?"/g, (_, n, hash) => hash
  ? `href="<?php echo esc_url( stmina_att_url( '${n}' ) . '${hash}' ); ?>"`
  : `href="${link(n)}"`);
```

### الكتابة المختلطة في خانة من الشمال لليمين

خانة الرقم السري `dir="ltr"` علشان الأرقام. والنص التوضيحي "4 أرقام" ظهر "أرقام 4"، لأن ترتيب الكلام العربي والأرقام بيتقلب جوه خانة `ltr`. **الحل:** النص التوضيحي بقى نقط (`••••`).

### الاختبارات (6 جداد، والمجموع 86)

- **من غير رقم سري** الدخول مرفوض.
- **الرقم السري** لازم 4 أرقام، والأرقام العربي مقبولة، والكود الغلط بيرجّع `404`.
- **الرقم الغلط** بيدّي نفس رسالة الموبايل المش متسجّل بالظبط.
- **الصح** بيرجّع رابط صفحته، ومفيش أي حاجة عن الرقم السري في الرد ولا في ملف المخدوم عند الخدام.
- **إعادة الإصدار** بتمسح الرقم السري.

**ماختبرناش حد المحاولات نفسه:** الاختبار كان هيقفل الدخول على جهاز المستخدم ربع ساعة.

**التوثيق:** [wp_hash_password()](https://developer.wordpress.org/reference/functions/wp_hash_password/) و[wp_check_password()](https://developer.wordpress.org/reference/functions/wp_check_password/) و[dbDelta()](https://developer.wordpress.org/reference/functions/dbdelta/) و[نمط Tabs في ARIA](https://www.w3.org/WAI/ARIA/apg/patterns/tabs/).

---

## الباب 32: إدارة الخدام، ودعوة برابط، وصلاحية المحرر

**عملنا إيه:** لوح "الخدام" في "المزيد":

- **القايمة لكل الخدام:** الاسم والموبايل، وعلامة "إنت" و"مدير الموقع" و"لسه ماقبلش الدعوة".
- **الإضافة وسحب الصلاحية لمدير الموقع بس** (قرار المستخدم). باقي الخدام بيشوفوا القايمة من غير الفورم ولا الأزرار.
- **إضافة خادم:** الاسم والموبايل، والرد رابط دعوة يتبعت على واتساب بزرار جاهز.
- **الدعوة:** الخادم الجديد بيفتح الرابط (`/attend/invite/<الكود>/`)، ويعمل كلمة السر بنفسه، ويدخل على طول. الرابط بيشتغل 3 أيام ومرة واحدة.
- **سحب الصلاحية:** بيمنعه فورًا، والحساب بيفضل علشان السجلات اللي سجّلها تفضل باسمه.

### الأدوار والصلاحيات: منفصلين من الأول

| الدور | الصلاحيات | يقدر يعمل إيه |
|---|---|---|
| الخادم (`stmina_servant`) | `read` و`stmina_attend` | شاشات الحضور بس |
| المحرر (`editor` من `WordPress`) | تعديل المحتوى، ومن غير `stmina_attend` | محتوى الموقع بس |
| مدير الموقع (`administrator`) | كل حاجة، ومعاها `stmina_attend` | الاتنين، وإدارة الخدام |

**صلاحية المحرر بتتدّي من `dashboard`:** من `Users`، تختار الحساب، وتغيّر `Role` لـ `Editor`. ولو حد محتاج الاتنين (خادم ومحرر)، ينفع يتدّي الدورين لنفس الحساب، بس من كود أو `plugin`، لأن شاشة الحساب في `WordPress` بتختار دور واحد.

**ليه مافيش كود جديد للفصل ده؟** لأن كل مسار حضور بيفحص `stmina_attend` في `permission_callback`، وكل تعديل محتوى `WordPress` بيفحص `edit_posts`. والدورين مافيهمش صلاحية التاني. والاختبارات بتتأكد من ده.

### مدير الموقع بس: دالة صلاحية فوق التانية

```php
function stmina_att_can_manage() {
	$can = stmina_att_can();
	if ( true !== $can ) {
		return $can;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return new WP_Error( 'rest_forbidden', 'إضافة الخدام وسحب الصلاحية لمدير الموقع بس.', array( 'status' => 403 ) );
	}
	return true;
}
```

- **بتستدعي `stmina_att_can()` الأول،** فالزائر بياخد `401` والحساب العادي `403` بنفس الرسايل.
- **وبعدين `manage_options`،** وهي صلاحية مدير الموقع في `WordPress`.
- **إخفاء الفورم في الشاشة راحة بس.** الحماية الحقيقية هنا.

### الخادم الجديد: حساب من غير كلمة سر حد يعرفها

```php
$id = wp_insert_user( array(
	'user_login'   => 'srv' . $phone,
	'user_pass'    => wp_generate_password( 32, true, true ),
	'display_name' => $name,
	'role'         => 'stmina_servant',
) );
update_user_meta( $id, 'stmina_phone', $phone );
```

- **دالة `wp_insert_user()`** بتعمل الحساب، والبريد مش إلزامي.
- **كلمة سر عشوائية طويلة** محدش بيشوفها، والدعوة بتغيّرها.
- **الموبايل في `stmina_phone`،** فبيدخل بيه من شاشة الدخول زي باقي الخدام (الباب 18).
- **لو الموبايل لخادم اتسحبت صلاحيته قبل كده،** نفس الحساب بيرجع (`add_role()`)، بدعوة جديدة، علشان سجلاته القديمة تفضل مربوطة بيه.

### الدعوة: الكود عند الخادم، والبصمة عندنا

```php
function stmina_att_new_invite( $user_id ) {
	$code = wp_generate_password( 32, false );
	update_user_meta( $user_id, 'stmina_invite', wp_hash( $code ) );
	update_user_meta( $user_id, 'stmina_invite_exp', time() + STMINA_ATT_INVITE_DAYS * DAY_IN_SECONDS );
	return stmina_att_url( 'invite' ) . $code . '/';
}
```

- **الكود نفسه** بيروح في الرابط بس، ومابيتخزّنش.
- **دالة `wp_hash()`** بتعمل بصمة بمفتاح سري من `wp-config.php`. فاللي يشوف قاعدة البيانات يلاقي البصمة، ومايقدرش يعمل منها رابط.
- **والدعوة بتشتغل** لو البصمة موجودة، والتاريخ ماعدّاش، والحساب لسه معاه الصلاحية.
- **ولما تتقبل** البصمة بتتمسح، فالرابط مايشتغلش تاني.

**ليه `wp_hash()` هنا و`wp_hash_password()` في الرقم السري؟** الكود هنا 32 حرف عشوائي، مستحيل يتخمّن، فبصمة سريعة كفاية، ونقدر ندوّر بيها في قاعدة البيانات مباشرة. والرقم السري 4 أرقام بس، فلازم تشفير بطيء (`bcrypt`) يصعّب التجريب.

### قبول الدعوة: كلمة السر والدخول في طلب واحد

```php
wp_set_password( $pass, $u->ID );
delete_user_meta( $u->ID, 'stmina_invite' );
delete_user_meta( $u->ID, 'stmina_invite_exp' );
wp_set_current_user( $u->ID );
wp_set_auth_cookie( $u->ID, true, is_ssl() );
```

- **دالة `wp_set_password()`** بتشفّر وتحفظ، وبتقفل أي جلسات قديمة.
- **دالة `wp_set_auth_cookie()`** بتحط كوكي الدخول في نفس الرد، فالخادم بيروح لشاشة الجلسات داخل على طول.

### سحب الصلاحية فورًا

```php
$u->remove_role( 'stmina_servant' );
$u->remove_cap( 'stmina_attend' );
WP_Session_Tokens::get_instance( $u->ID )->destroy_all();
```

- **دالة `remove_role()`** بتشيل الدور، فأي طلب جاي بيترفض من `permission_callback`.
- **كلاس `WP_Session_Tokens`** بيقفل كل جلسات الدخول المفتوحة على كل أجهزته. فحتى لو الصفحة مفتوحة عنده، أول طلب بيرجّعه لشاشة الدخول.
- **مينفعش تسحب صلاحية نفسك ولا مدير الموقع** (`400`)، علشان محدش يقفل النظام على نفسه بالغلط.

### شاشة جديدة من التصميم

شاشة الدعوة متولّدة من `design/attend-invite.html`، واتعملت من شاشة الدخول بنفس الشكل. وصفحتها عامة زي الكارت، لأن صاحب الدعوة لسه مالوش كلمة سر:

```php
if ( 'card' === $screen || 'invite' === $screen ) {
	// عامة: مفيش تحويل ولا فحص صلاحية
}
```

ورقم قواعد الروابط بقى 3 (`STMINA_ATT_RULES`، الباب 30)، علشان قاعدة `/attend/invite/<الكود>/` تتسجّل لوحدها بعد الرفع.

### الاختبارات (4 شغالين، و6 لمدير الموقع)

- **من غير حساب مدير:** القايمة للخدام بس، والخادم العادي مايقدرش يضيف ولا يسحب ولا يبعت دعوة، ومايقدرش يعمل مقال في الموقع، والدعوة بكود غلط بترجع `404`.
- **بحساب مدير** (`ADMIN_USER` و`ADMIN_APP_PASSWORD` في `tests/.env`): الإضافة، ورفض الموبايل المكرر، وقبول الدعوة مرة واحدة، والدخول بالموبايل، وصلاحيات الخادم والمحرر، وسحب الصلاحية، وتنضيف الحسابات.
- **من غير حساب المدير** المجموعة دي بتتخطى بعلامة `skip`، وماتفشلش.

**التوثيق:** [wp_insert_user()](https://developer.wordpress.org/reference/functions/wp_insert_user/) و[wp_hash()](https://developer.wordpress.org/reference/functions/wp_hash/) و[wp_set_auth_cookie()](https://developer.wordpress.org/reference/functions/wp_set_auth_cookie/) و[WP_Session_Tokens](https://developer.wordpress.org/reference/classes/wp_session_tokens/) و[Roles and Capabilities](https://developer.wordpress.org/plugins/users/roles-and-capabilities/).

---

## الباب 33: المسح من غير نت، والطابور، والمزامنة

**عملنا إيه (المهام 21 و22 و23):** شاشة المسح بقت تشتغل والنت واقع جوه الكنيسة.

1. **والنت شغال:** الخادم بيفتح شاشة المسح مرة، فبتتحفظ على موبايله "شنطة" الجلسة: المخدومين بالاسم وكود الكارت وحالة كل واحد. **من غير أي رقم موبايل.**
2. **والنت واقع:** المسح والتسجيل اليدوي بيشتغلوا من الشنطة. وكل عملية بتتحط في "طابور" على الموبايل بوقتها، وفيه عدّاد "N عمليات لسه على الموبايل".
3. **لما النت يرجع:** الطابور بيتبعت لوحده في طلب واحد، والعدّاد بيرجع صفر. واللي اترفض (كارت ملغي أو مش معروف) بيظهر للخادم بسببه.
4. **الإنهاء:** مقفول طول ما فيه عمليات للجلسة على الموبايل ده. ولو عملية من موبايل تاني وصلت بعد الإنهاء، "غاب" بيتحوّل "حضر" في نفس السجل.
5. **الشاشات نفسها بتفتح من غير نت:** تطبيق اسمه "إعداد الخدام" بيتثبّت على الموبايل، وبيفتح على شاشة المسح.

### الشنطة: `GET /sessions/{id}/pack`

```php
"SELECT m.id, m.full_name, m.qr_token, m.status AS member_status, r.status, r.recorded_at
 FROM $m m
 JOIN $ms ms ON ms.member_id = m.id AND ms.service_id = %d
 LEFT JOIN $r r ON r.member_id = m.id AND r.session_id = %d
 WHERE r.id IS NOT NULL OR ( m.status = 'active' AND DATE(m.registered_at) <= %s )"
```

- **نفس مخدومين الجلسة** اللي في `stmina_att_roster()`، ومعاهم كود الكارت علشان المسح يتعرف عليهم.
- **الموبايل مش في الاستعلام أصلًا،** فمستحيل يطلع. والاختبار بيتأكد إن الرد مافيهوش كلمة `phone` خالص.
- **الشنطة بتتحفظ في `localStorage`،** وآخر 5 جلسات بس، علشان الموبايل مايتمليش.

**ليه `localStorage` مش `IndexedDB`؟** البيانات صغيرة (عشرات الأسماء)، و`localStorage` أبسط بكتير: قراية وكتابة مباشرة من غير `Promise`. ولو المخدومين بقوا آلاف، `IndexedDB` هتبقى أحسن.

### الطابور على الموبايل

كل عملية بتتحفظ كده:

```js
{ id: 'رقم فريد', session_id: 12, type: 'scan', code: 'رابط الكارت', at: '2026-10-08 19:05:00' }
{ id: '...', session_id: 12, type: 'set', member_id: 7, status: 'excused', at: '...' }
```

- **الوقت بتوقيت القاهرة** بنفس شكل السيرفر، من `Intl.DateTimeFormat` بـ `timeZone: 'Africa/Cairo'`، مهما كان توقيت الموبايل.
- **الرقم الفريد** من `crypto.randomUUID()`، علشان كل نتيجة ترجع لعمليتها.
- **الشنطة بتتحدّث مع كل عملية،** فالعدّاد و"اتسجّل قبل كده" صح من غير نت.
- **التراجع** عن عملية لسه في الطابور: بتتشال منه، والحالة بترجع زي ما كانت. أما التراجع عن تسجيل اتزامن خلاص، فمحتاج نت.

**وكارت مش في الشنطة؟** ممكن كارت ملغي، أو مخدوم اتضاف من موبايل تاني بعد ما الشنطة اتحفظت. فبيتحط في الطابور بالكود، ويظهر "اتحفظ الكارت، هيتأكد لما النت يرجع". والسيرفر هو اللي بيقرر (قرار المستخدم).

### المزامنة: `POST /sync`

```php
function stmina_att_apply_op( $op, $service ) { … }
```

لكل عملية نفس منطق المسح والتسجيل اليدوي، ونفس النتايج (`ok` و`dup` و`revoked` و`unknown` و`stopped` و`nosession`)، بفرق تلاتة:

1. **وقت السجل هو وقت العملية الأصلي** (`at`)، مش وقت المزامنة. ولو الوقت في المستقبل (ساعة الموبايل غلط)، بيبقى "دلوقتي".
2. **الإرسال مرتين مابيعملش سجل مكرر:** القيد الفريد للمخدوم في الجلسة (الباب 21) بيرفض السجل التاني، فالنتيجة `dup`. ده اسمه **`idempotent`**: نفس الطلب أكتر من مرة نتيجته زي مرة واحدة. ومهم هنا لأن النت ممكن يقع والطلب وصل بس الرد ماوصلش، فالموبايل بيبعت تاني.
3. **العملية المتأخرة بعد الإنهاء:**

```php
if ( $existing && 'auto' === $existing->method && $status !== $existing->status ) {
	$wpdb->update( $table, $row, array( 'id' => $existing->id ) );
	return $out + array( 'result' => 'ok', /* … */ 'late' => true );
}
```

الإنهاء بيعمل "غاب" بطريقة `auto` للي ماتسجّلوش. فلو وصلت بعدها عملية من الطابور لنفس المخدوم، دي الحقيقة، فبتاخد مكان "غاب" في **نفس السجل**، بنفس الطريقة والوقت والخادم.

**ليه مافيش جدول للعمليات اللي اتعملت؟** القيد الفريد كفاية علشان مايبقاش فيه تكرار. وجدول زيادة كان هيكبر مع الوقت من غير فايدة.

**والموبايل بعد الرد:** بيشيل من الطابور كل العمليات اللي اتبعتت، حتى المرفوضة بعد ما يحفظ سببها. واللي اتضاف وإحنا بنزامن بيفضل للمرة الجاية.

**إمتى المزامنة بتحصل؟** أول ما الشاشة تفتح، ولما المتصفح يقول إن النت رجع (حدث `online`)، وكل 30 ثانية لو الطابور فيه حاجة. وده في `attend-app.js`، فبيشتغل من أي شاشة خدام.

### رمز `nonce` القديم

الشاشة اللي اتفتحت من النسخة المحفوظة من غير نت، رمز `nonce` بتاعها ممكن يكون عمره أكتر من يوم وباظ. فلو `WordPress` رفض الطلب بـ `rest_cookie_invalid_nonce`، بنجيب رمز جديد ونجرّب تاني مرة واحدة:

```js
const fresh = await fetch(C.ajax + '?action=rest-nonce', { credentials: 'same-origin' }).then(r => (r.ok ? r.text() : ''));
```

**الإجراء `rest-nonce`** موجود جاهز في `WordPress` (`wp_ajax_rest_nonce`)، وبيرجّع رمز جديد للحساب الداخل.

### تطبيق الخدام والـ `service worker`

نفس ملف `me-sw.js` (الباب 30) بيتقدّم من رابطين:

| الرابط | المجال | النسخة المحفوظة |
|---|---|---|
| `/me-sw.js` | `/me/` | `hodoury-…` |
| `/attend-sw.js` | `/attend/` | `attend-…` |

والملف بيعرف هو فين من `self.registration.scope`. **وطلبات `REST` مابتتحفظش فيه خالص** (`/wp-json/`)، لأن بيانات الخدام ليها مكانها في الشنطة، ولأن حفظ ردود فيها بيانات حساب جوه الكاش مش آمن. وملف `manifest` واحد للخدام (`/attend/manifest.webmanifest`) بيفتح على شاشة المسح.

**وفي الصفحات اللي ليها `?`** (زي `/attend/scan/?session=5`)، النسخة المحفوظة بتتدوّر من غير الجزء ده (`ignoreSearch`)، فأي نسخة من الشاشة تنفع.

### الخروج

- **لو فيه عمليات في الطابور،** الخروج بيترفض برسالة، لأنه كان هيضيّعها.
- **غير كده،** الشنطة والشاشات المحفوظة بتتمسح من الموبايل، علشان الأسماء والأكواد مايفضلوش على موبايل حد تاني.

### الإنهاء مقفول من الموبايل اللي فيه عمليات

في شاشة "تفاصيل الجلسة"، الإنهاء بيبص على الطابور: لو فيه عمليات للجلسة دي، بيظهر "فيه N عمليات على موبايلك لسه ماتزامنتش"، وزرار الضغط المطوّل بيتقفل، وبيحاول يزامن.

**الحد:** الموبايل ده بس اللي يعرف طابوره. لو خادم تاني عنده عمليات ماتزامنتش، السيرفر مايعرفش. وده سبب قاعدة "غاب" اللي بيتحوّل "حضر".

### الاختبارات (7 جداد، والمجموع 97)

- **الشنطة** للخدام بس، وفيها الكود، ومن غير موبايلات.
- **المزامنة** بتدّي كل عملية نتيجتها برقمها، ومعاها الكارت الملغي والمش معروف.
- **السجلات** بطريقة التسجيل واسم الخادم والوقت الأصلي.
- **نفس الدفعة مرتين** مابتعملش سجلات زيادة.
- **العملية المتأخرة بعد الإنهاء** بتحوّل "غاب" لـ"حضر" في نفس السجل.
- **الوقت اللي في المستقبل** بيبقى وقت المزامنة.

**ماتختبرش أوتوماتيك:** الموبايل نفسه من غير نت (الشنطة، والطابور، والعدّاد، وقفل الإنهاء). دول محتاجين موبايل حقيقي والنت مقفول.

**التوثيق:** [Window.localStorage](https://developer.mozilla.org/en-US/docs/Web/API/Window/localStorage) و[online/offline events](https://developer.mozilla.org/en-US/docs/Web/API/Navigator/onLine) و[crypto.randomUUID()](https://developer.mozilla.org/en-US/docs/Web/API/Crypto/randomUUID) و[Idempotence](https://developer.mozilla.org/en-US/docs/Glossary/Idempotent) و[REST API Authentication: nonces](https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/).

---

## الباب 34: مراجعة الجودة قبل الإطلاق

**عملنا إيه:** مراجعة عامة للموقع وتطبيق الحضور، والتقرير في `docs/bugs.md`. الباب ده عن طريقة المراجعة، والحاجات اللي اتصلّحت منها.

### طريقة المراجعة

- **زحف على الموقع:** سكريبت `node` بيبدأ من الرئيسية، ويفتح كل رابط داخلي، ويسجّل لكل صفحة: الحالة، والعنوان، وعدد `h1`، والوصف، والصور اللي من غير `alt`. وبعدين بيفتح كل صورة وملف. النتيجة: 43 صفحة و126 ملف.
- **القياس بالكود مش بالعين:** كل صفحة جوه `iframe` مخفي بعرض محدد (320 و375 و1366)، ونقيس كل عنصر بـ `getBoundingClientRect()`. والعنصر اللي جوه حاوية بتقص (`overflow: hidden`) أو ثابتة (`position: fixed`، زي قايمة الموبايل المقفولة) مايتحسبش، لأنه مش ظاهر برا.
- **شاشات الخدام من غير دخول:** نفس ملف الشاشة المتولّد، ونفس السكريبتات، ودالة `fetch` بترجّع بيانات تجريبية **بحجم الحقيقة** (61 مخدوم). وده اللي كشف المشكلة اللي تحت، لأن التصميم اتعمل ببيانات أصغر.

### العلامات اللي كانت بتطلع برا الكارت

كارت الجلسة المفتوحة فيه شرطة لكل مخدوم، في مجموعات من 5. وكانت في سطر واحد (`flex-wrap: nowrap`). مع 41 مخدوم (بيانات التصميم) كانت واخدة، ومع 61 (الحقيقة) بقت 359px في كارت جواه 290 بس. والكارت نفسه اتمدّد معاها لـ 397px على شاشة 375.

```css
.tally{display:flex;flex-wrap:wrap;align-items:flex-end;gap:6px;min-height:16px;min-width:0}
.stack{display:grid;grid-template-columns:minmax(0,1fr);gap:var(--s-4)}
```

- **`flex-wrap: wrap`:** الشرط بتنزل سطر تاني لو مش واخدة.
- **`minmax(0, 1fr)` في العمود:** عمود الـ `grid` من غيرها أقل عرض ليه هو أعرض حاجة جواه (`min-content`)، فبيتمدّد. بالقيمة دي العمود مايقدرش يبقى أعرض من مكانه. ودي نفس قاعدة "أي `grid` جواه صف بيتسحب" في فخاخ المشروع.

**الدرس:** التصميم بيتجرّب ببيانات تجريبية، والحقيقة دايمًا أكبر. أي عنصر عدده بيكبر مع البيانات (شرط، كروت، أسماء) لازم يتجرّب بأكبر عدد متوقع.

### وصف كل صفحة ومعاينة الرابط

ملف `inc/seo.php` في الـ `theme`، على نقطة `wp_head`:

```php
printf( '<meta name="description" content="%s">' . "\n", esc_attr( $m['description'] ) );
printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $m['image'] ) );
```

- **الوصف (`meta description`):** اللي بيظهر تحت العنوان في جوجل.
- **وسوم `og:` (Open Graph):** اللي واتساب وفيسبوك بيعملوا منها معاينة الرابط: العنوان والوصف والصورة.
- **منين الوصف:** صفحات الكنيسة الثابتة بنفس الوصف المعتمد في نماذج التصميم. والخدمة والخبر والعظة من أول 30 كلمة في محتواها (`wp_trim_words()`)، وإلا جملة بنفس صيغة التصميم.
- **منين الصورة:** صورة الموضوع (`get_the_post_thumbnail_url()`)، وإلا صورة الكنيسة من جوه.
- **دالة `wp_get_document_title()`:** نفس العنوان اللي `WordPress` بيحطه في التاب، فمفيش عنوانين مختلفين.

**البديل:** `plugin` زي `Yoast SEO`. بيعمل ده وأكتر، بس تقيل لموقع صغير، ومحتاج حد يملا الوصف لكل صفحة بإيده.

### فخ: المتغير جوه نص عربي في `PHP`

```php
$c = 'كنيسة …';
echo "$c، عرب العيايدة";   // ← بيطبع "، عرب العيايدة" من غير اسم الكنيسة
echo "{$c}، عرب العيايدة"; // ← صح
```

`PHP` بيقرا اسم المتغير جوه النص لحد أول حرف مش من حروف الأسماء. وحروف الأسماء عنده بتشمل أي بايت فوق 127، يعني أي حرف عربي. فـ `"$c،"` اتقرت كأنها متغير اسمه `$c،`، ومش موجود، فاتطبع فاضي من غير أي خطأ ظاهر. **الحل:** المتغير جوه أقواس `{$c}` دايمًا، لو بعده حرف عربي أو علامة ترقيم عربي.

### حسابات الاختبار مابتظهرش في قايمة الخدام

حساب الاختبار ظهر في قايمة الخدام، واتسحبت صلاحيته بالغلط، فوقفت الاختبارات. فبقى فيه خانة في صفحة الحساب في `dashboard`:

```php
<?php if ( current_user_can( 'manage_options' ) ) : ?>
	<label><input type="checkbox" name="stmina_test" value="1" <?php checked( (bool) get_user_meta( $user->ID, 'stmina_test', true ) ); ?>> الحساب ده للاختبارات الأوتوماتيك بس</label>
<?php endif; ?>
```

- **نقطتي `show_user_profile` و`edit_user_profile`:** بيزوّدوا خانات في صفحة الحساب، ونقطتي `personal_options_update` و`edit_user_profile_update` بيحفظوها (الباب 18).
- **دالة `checked()`:** بتكتب `checked="checked"` لو القيمة صح، بدل `if` في نص الـ `HTML`.
- **الحساب المتعلّم مابيظهرش في القايمة لحد غيره،** ومسار سحب الصلاحية بيرفضه. بس صاحبه بيشوف نفسه، علشان الاختبار اللي بيتأكد إن الخادم شايف نفسه يفضل شغال.

**التوثيق:** [wp_head](https://developer.wordpress.org/reference/hooks/wp_head/) و[wp_get_document_title()](https://developer.wordpress.org/reference/functions/wp_get_document_title/) و[The Open Graph protocol](https://ogp.me/) و[PHP: Variable parsing](https://www.php.net/manual/en/language.types.string.php#language.types.string.parsing) و[checked()](https://developer.wordpress.org/reference/functions/checked/).

---

## الباب 35: أحداث الخدمة، أو ربط نوعين محتوى ببعض

**عملنا إيه:** المستخدم عايز رحلة أديرة المنيا، ومؤتمر "عنبر 11"، وأمسية العبور يظهروا في صفحة اجتماع الشباب. التلاتة كانوا موجودين أصلًا كأخبار بصورهم، فبدل ما نعمل نوع محتوى جديد، **ربطنا الخبر بالخدمة**: خانة جديدة في الخبر اسمها "حدث لخدمة"، وصفحة الخدمة بتعرض الأخبار المربوطة بيها في قسم "أحداث الخدمة".

### الخانة: `post_object` في `ACF`

في `inc/fields.php` بتاع الـ `plugin` `st-mina-content`، جوه خانات الخبر:

```php
array(
	'key' => 'field_stmina_news_service', 'name' => 'service', 'label' => 'حدث لخدمة', 'type' => 'post_object',
	'post_type' => array( 'stmina_service' ), 'return_format' => 'id', 'allow_null' => 1, 'ui' => 1,
),
```

- **نوع `post_object`:** قايمة بتختار منها موضوع من نوع معيّن (هنا الخدمات). وبيتخزّن رقمه بس في `post meta` باسم `service`.
- **`return_format => id`:** الدالة `get_field()` بترجّع الرقم مش الموضوع كله، وده أخف.
- **`allow_null`:** الخانة اختيارية، فمعظم الأخبار مش تبع خدمة.
- **نفس الطريقة** اتعملت قبل كده مع "المتحدث" في العظة (الباب اللي فيه العظات).

### الاستعلام: كل الأخبار اللي الخانة فيها رقم الخدمة

في `inc/news.php`:

```php
function stmina_service_events( $service ) {
	return stmina_news( array(
		'meta_query' => array( array( 'key' => 'service', 'value' => (string) get_post( $service )->ID ) ),
	) );
}
```

- **`meta_query`:** بيدوّر في `post meta` بالمفتاح والقيمة. القيمة متخزّنة نص، فبنحوّل الرقم لنص.
- **بنستخدم `stmina_news()` الموجودة:** فبتستبعد المناسبات الموسمية وبترتّب الأحدث الأول من غير ما نكرر الكود.

### العرض في صفحة الخدمة

في `single-stmina_service.php`، قبل "خدمات أخرى"، القسم بيظهر بس لو فيه أحداث، وبيستخدم نفس كارت الخبر (`template-parts/news-card.php`). والكارت اتعلّم حاجة جديدة: **لو الخبر مالوش صورة وهو تبع خدمة، بياخد صورة الخدمة**، علشان الكارت مايبقاش فاضي.

`function_exists( 'stmina_service_events' )` قبل الاستدعاء: الـ `theme` مايقعش لو الـ `plugin` اتقفل.

### إزاي تضيف حدث جديد لأي خدمة

`dashboard` ← الأخبار ← إضافة خبر ← العنوان والصورة (`Featured image`) والميعاد المكتوب (مثلًا 2026) ← خانة "حدث لخدمة" اختار الخدمة ← `Publish`. الحدث بيظهر في صفحة الخدمة وفي صفحة الأخبار كمان.

### البدائل

- **نوع محتوى جديد "حدث":** أنضف لو الأحداث مختلفة جدًا عن الأخبار، بس كان هيكرر الكروت والصفحات، والأحداث هنا هي نفسها أخبار.
- **تصنيف (`taxonomy`) باسم الخدمة:** ينفع، بس لازم نعمل تصنيف لكل خدمة ونخلّيه متطابق معاها بإيدينا. الربط بالرقم بيفضل صح لو اسم الخدمة اتغيّر.
- **`Relationship` في `ACF`:** بتربط خبر بأكتر من خدمة. لو حدث بقى تبع خدمتين، دي الخطوة الجاية.

### فخ: الخانة المخفية `_service`

`ACF` بيحفظ مع كل خانة خانة مخفية (`_service`) فيها مفتاح الخانة. لما بنحط القيمة من بره (من `royal-mcp` مثلًا)، الخانة المخفية مابتتكتبش، و`royal-mcp` مابيسمحش يكتبها لأنها بتبدأ بـ `_` (`protected meta`). ده مش مشكلة هنا، لأن الخانات متعرّفة في الكود، و`ACF` بيلاقيها بالاسم. وأول ما حد يحفظ الخبر من `dashboard` بتتكتب لوحدها.

**التوثيق:** [ACF: Post Object](https://www.advancedcustomfields.com/resources/post-object/) و[WP_Query: Custom Field Parameters](https://developer.wordpress.org/reference/classes/wp_query/#custom-field-post-meta-parameters).

---

## الباب 36: الحضور المؤقت في الجلسة المفتوحة

**عملنا إيه:** قبل كده صفحة "حضوري" كانت بتقرا الجلسات المقفولة بس، فالمخدوم اللي اتسجّل حضوره مابيشوفش حاجة لحد ما الخادم يقفل الجلسة. دلوقتي الحضور والغياب بعذر بيظهروا على طول وبيتحسبوا في النسبة والشموع، وجنبهم كلمة "مؤقت". والصفحة بتتحدّث لوحدها وهي مفتوحة.

### التعديل كله في استعلام واحد

ملف `inc/stats.php` في الـ `plugin` `st-mina-attendance`:

```php
"SELECT s.id AS session_id, s.kind, s.session_date, r.status, r.method, IF( s.status = 'open', 1, 0 ) AS is_open
 FROM … r JOIN … s ON s.id = r.session_id
 WHERE r.member_id = %d AND s.service_id = %d
   AND ( s.status = 'closed' OR ( s.status = 'open' AND r.status IN ( 'present', 'excused' ) ) )"
```

- **الشرط الجديد:** الجلسة المقفولة زي ما هي، والمفتوحة بس لو السجل حضر أو غاب بعذر. ليه مش الغياب؟ لأن سجل "غاب" الحقيقي بيتعمل وقت القفل لكل اللي ماجوش. ولو الخادم سجّل حد غاب بإيده والجلسة مفتوحة، ممكن يجي بعدها ويتمسح، فمانعرضوش لحد القفل.
- **`IF()` في `MySQL`:** بترجّع 1 أو 0 في عمود جديد اسمه `is_open`، والرد بيحوّله لـ `true` أو `false` باسم `open`. ماسميناش العمود `open` لوحده، لأنها كلمة من أوامر `MySQL` نفسها. هي مش ممنوعة كاسم، بس الأأمن نبعد عن أي اسم زي كده.
- **ليه في الدالة دي:** الدالة `stmina_att_member_log()` هي اللي بتجيب السجل لـ "حضوري" وملف المخدوم وقايمة المخدومين. فتعديل واحد غيّرهم التلاتة مع بعض، والخادم بيشوف نفس اللي المخدوم شايفه. ده فايدة إن الحساب كله في مكان واحد (الباب اللي فيه حساب الحضور).
- **لوحة الخادم مااتغيّرتش:** دالة `stmina_att_dashboard()` ليها استعلام لوحدها على الجلسات المقفولة بس، لأنها إحصائيات للخدمة كلها، والجلسة لسه ماخلصتش.

### التحديث لوحده في الصفحة

ملف `assets/card.js`:

```js
async function refresh() {
  if (busy || document.hidden || !navigator.onLine) return;
  const res = await fetch(`${C.rest}me/${card.token}`, { credentials: 'omit', cache: 'no-store' });
  …
}
setInterval(refresh, 20000);
document.addEventListener('visibilitychange', refresh);
```

- **كل 20 ثانية:** علشان لو المخدوم فاتح الصفحة وهو واقف قدام الخادم، يشوف حضوره من غير ما يعيد فتحها.
- **`document.hidden`:** الصفحة مابتطلبش حاجة وهي في الخلفية، فمابتصرفش من باقة المخدوم ولا من بطاريته.
- **حدث `visibilitychange`:** أول ما يرجع للصفحة من تطبيق تاني، بتتحدّث على طول.
- **`credentials: 'omit'`:** نفس فكرة الرقم السري (الباب 31). الكود اللي في الرابط هو الإثبات، فمش محتاجين كوكي.
- **`cache: 'no-store'`:** ماياخدش نسخة قديمة من المتصفح.
- **بيرسم بس لو الرد اتغيّر:** بنقارن الرد الجديد بالقديم بـ `JSON.stringify`، فالصفحة ماتترسمش كل 20 ثانية من غير سبب.

### البدائل

- **إشعار (`Push Notification`):** المخدوم يعرف حتى والصفحة مقفولة. بس محتاج إنه يكون مثبّت التطبيق ويوافق على الإشعارات، ومفاتيح `VAPID`، وسيرفر يبعت. ومش مضمون على الآيفون. اترفض لأنه تقيل على الفايدة.
- **اتصال مفتوح (`WebSocket` أو `Server-Sent Events`):** التحديث بيوصل في نفس اللحظة. بس الاستضافة المشتركة مش مناسبة لاتصالات مفتوحة طول الوقت، و20 ثانية كفاية.
- **الحضور يظهر من غير ما يدخل في النسبة:** كانت الأأمن، لأن الأرقام ماتتغيّرش لو الخادم صحّح. المستخدم اختار إنه يدخل على طول.

### اللي لازم يتعرف

- **النسبة ممكن تبان أعلى لحد القفل،** لأن الغياب لسه ماتسجّلش.
- **المسح من غير نت:** المخدوم مش هيشوف حضوره غير لما طابور الخادم يتبعت (الباب 33).
- **قاعدة الحساب** اتحدّثت في `docs/design.md` في القسم 5.1.

**التوثيق:** [MySQL: IF()](https://dev.mysql.com/doc/refman/8.0/en/flow-control-functions.html#function_if) و[MySQL: Keywords and Reserved Words](https://dev.mysql.com/doc/refman/8.0/en/keywords.html) و[MDN: visibilitychange](https://developer.mozilla.org/en-US/docs/Web/API/Document/visibilitychange_event) و[MDN: fetch() cache](https://developer.mozilla.org/en-US/docs/Web/API/RequestInit#cache).

---

## الباب 37: الأبواب الجاية

| الباب | المهمة | المفاهيم |
|---|---|---|
| القوايم من لوحة التحكم | لما المحتوى يكتمل | القوايم بدالة `wp_nav_menu()` بدل الروابط الثابتة |
