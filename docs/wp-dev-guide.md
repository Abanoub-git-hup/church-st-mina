# مرجع تطوير `WordPress`: `theme` و `plugin` من الصفر

> مرجع عملي مبني على مشروع حقيقي: موقع كنيسة السيدة العذراء ومارمينا والبابا كيرلس السادس، وفيه `theme` مخصص للموقع العام اسمه `st-mina` و `plugin` مخصص لنظام الحضور بالكود اسمه `st-mina-attendance`.
>
> الملف بيتحدّث مع كل خطوة في بناء `theme` و`plugin`. كل باب فيه: عملنا إيه وليه، والمفهوم في `WordPress`، والكود الحقيقي مشروح، والبدائل، والأمان، والأخطاء اللي قابلتنا.
>
> آخر تحديث: يوم 7 أكتوبر 2026، في المهمة العاشرة (فتح الجلسة).

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
- الباب 21: الأبواب الجاية

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
- **مفيش `DELETE`:** المخدوم مابيتمسحش، بيتوقف بتغيير الحالة. ولأن مفيش مسار حذف أصلًا، مفيش طريقة حد يمسح بالغلط.

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

## الباب 21: الأبواب الجاية

| الباب | المهمة | المفاهيم |
|---|---|---|
| القوايم من لوحة التحكم | لما المحتوى يكتمل | القوايم بدالة `wp_nav_menu()` بدل الروابط الثابتة |
| المسح | من الحادية عشر | قراية `QR` بالكاميرا، وجدول السجلات بقيد فريد لكل مخدوم في الجلسة |
| الشغل بدون إنترنت | من 21 لـ 23 | تطبيق ويب يشتغل من غير نت، وطابور عمليات ومزامنة |
