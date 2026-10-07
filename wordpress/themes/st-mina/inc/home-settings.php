<?php
/**
 * صفحة "إعدادات الرئيسية" في لوحة التحكم، معمولة بـ Settings API.
 * بتحفظ النصوص اللي بتتغيّر في الرئيسية في إعداد واحد اسمه stmina_home.
 */

defined( 'ABSPATH' ) || exit;

/**
 * الخانات: المفتاح ← العنوان، والقيمة الافتراضية (نص التصميم المعتمد)، والقسم.
 */
function stmina_home_fields() {
	return array(
		'hero_verse'     => array( 'آية الواجهة', 'فِي ٱلْعَالَمِ سَيَكُونُ لَكُمْ ضِيقٌ، وَلكِنْ ثِقُوا:', 'hero' ),
		'hero_highlight' => array( 'الجزء الدهبي في آخر الآية', 'أَنَا قَدْ غَلَبْتُ ٱلْعَالَمَ', 'hero' ),
		'hero_ref'       => array( 'الشاهد', 'يوحنا 16: 33', 'hero' ),
		'quote_text'     => array( 'القول', 'الوصية مش قيد.. الوصية أمان لينا', 'quote' ),
		'quote_author'   => array( 'قائل القول', 'أبونا فام عبد المسيح', 'quote' ),
		'verse_text'     => array( 'آية التأمل', 'تَعَالَوْا إِلَيَّ يَا جَمِيعَ الْمُتْعَبِينَ وَالثَّقِيلِي الأَحْمَالِ، وَأَنَا أُرِيحُكُمْ', 'verse' ),
		'verse_ref'      => array( 'الشاهد', 'متى 11: 28', 'verse' ),
	);
}

/**
 * قيمة خانة: المحفوظة لو موجودة، وإلا النص الافتراضي.
 */
function stmina_home( $key ) {
	$saved  = get_option( 'stmina_home', array() );
	$fields = stmina_home_fields();
	if ( ! empty( $saved[ $key ] ) ) {
		return $saved[ $key ];
	}
	return isset( $fields[ $key ] ) ? $fields[ $key ][1] : '';
}

// تسجيل الإعداد والأقسام والخانات
add_action( 'admin_init', function () {
	register_setting( 'stmina_home', 'stmina_home', array(
		'type'              => 'array',
		'sanitize_callback' => 'stmina_home_sanitize',
		'default'           => array(),
	) );

	add_settings_section( 'hero', 'آية الواجهة', '__return_false', 'stmina-home' );
	add_settings_section( 'quote', 'دايرة القول (تحت الواجهة)', '__return_false', 'stmina-home' );
	add_settings_section( 'verse', 'كلمة للتأمل (قسم الشمعة)', '__return_false', 'stmina-home' );

	foreach ( stmina_home_fields() as $key => $f ) {
		add_settings_field( $key, $f[0], 'stmina_home_field', 'stmina-home', $f[2], array(
			'key'       => $key,
			'label_for' => 'stmina-' . $key,
		) );
	}
} );

/**
 * خانة نص واحدة. القيمة الافتراضية بتظهر كمثال لو الخانة فاضية.
 */
function stmina_home_field( $args ) {
	$key    = $args['key'];
	$saved  = get_option( 'stmina_home', array() );
	$fields = stmina_home_fields();
	printf(
		'<input type="text" class="large-text" id="%1$s" name="stmina_home[%2$s]" value="%3$s" placeholder="%4$s" dir="rtl">',
		esc_attr( $args['label_for'] ),
		esc_attr( $key ),
		esc_attr( isset( $saved[ $key ] ) ? $saved[ $key ] : '' ),
		esc_attr( $fields[ $key ][1] )
	);
}

/**
 * تنضيف القيم قبل الحفظ: نص عادي بس، ومفاتيح معروفة بس.
 */
function stmina_home_sanitize( $input ) {
	$clean = array();
	foreach ( array_keys( stmina_home_fields() ) as $key ) {
		$clean[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : '';
	}
	return $clean;
}

// الصفحة في القايمة الجانبية، للي معاه صلاحية edit_theme_options بس
add_action( 'admin_menu', function () {
	add_menu_page( 'إعدادات الرئيسية', 'إعدادات الرئيسية', 'edit_theme_options', 'stmina-home', 'stmina_home_page', 'dashicons-admin-home', 3 );
} );

function stmina_home_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	?>
	<div class="wrap" dir="rtl">
		<h1>إعدادات الرئيسية</h1>
		<p>النصوص دي بتظهر في الصفحة الرئيسية. لو سبت خانة فاضية، بيظهر النص الرمادي اللي جواها.</p>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'stmina_home' );
			do_settings_sections( 'stmina-home' );
			submit_button( 'حفظ' );
			?>
		</form>
	</div>
	<?php
}
