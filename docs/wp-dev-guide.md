# مرجع تطوير `WordPress`: `theme` و `plugin` من الصفر

> مرجع عملي مبني على مشروع حقيقي: موقع كنيسة السيدة العذراء ومارمينا والبابا كيرلس السادس، وفيه `theme` مخصص للموقع العام اسمه `st-mina` و `plugin` مخصص لنظام الحضور بالكود اسمه `st-mina-attendance`.
>
> الملف بيتحدّث مع كل خطوة في بناء `theme` و`plugin`. كل باب فيه: عملنا إيه وليه، والمفهوم في `WordPress`، والكود الحقيقي مشروح، والبدائل، والأمان، والأخطاء اللي قابلتنا.
>
> آخر تحديث: يوم 7 أكتوبر 2026، في المهمة الخامسة (صفحة العبادة).

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
- الباب 15: الأبواب الجاية

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

`WordPress` بيحفظ قواعد الروابط (زي إن `/services/` معناها صفحة الخدمات) في قاعدة البيانات. ولما نسجّل نوع محتوى جديد، القواعد دي لازم تتحدّث، وإلا الروابط الجديدة هتطلع "الصفحة غير موجودة". وعلشان كده بنحدّثها مرة واحدة وقت التفعيل:
```php
register_activation_hook( __FILE__, function () {
	stmina_register_services();
	stmina_seed_services();
	flush_rewrite_rules();
} );
```
ولازم نسجّل نوع المحتوى الأول جوه نفس الدالة، لأن وقت التفعيل نقطة `init` بتكون عدّت خلاص. والتحديث ده تقيل، فمايتعملش أبدًا مع كل صفحة.

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

## الباب 15: الأبواب الجاية

| الباب | المهمة | المفاهيم |
|---|---|---|
| الصفحات الداخلية | من الثالثة للسابعة | قالب الصفحة الداخلية من ملف `design/_inner.html`، وتقسيم الأجزاء المتكررة بدالة `get_template_part()` |
| العظات والأخبار | من الخامسة للسابعة | أنواع محتوى تانية بنفس طريقة الخدمات، وتقسيم الصفحات بكلاس `WP_Query`، وربط أقسام الرئيسية بالمحتوى الحقيقي |
| القوايم من لوحة التحكم | لما المحتوى يكتمل | القوايم بدالة `wp_nav_menu()` بدل الروابط الثابتة |
| دخول الخادم والمخدومين | الثامنة | جداول مخصّصة بدالة `dbDelta()`، والأدوار والصلاحيات، ومسارات بفحص صلاحيات، ورموز الحماية `nonces`، وتنضيف المدخلات |
| الحضور والمسح | من العاشرة للرابعة عشر | منطق `plugin`، واختبارات المسارات |
| الشغل بدون إنترنت | من 21 لـ 23 | تطبيق ويب يشتغل من غير نت، وطابور عمليات ومزامنة |
