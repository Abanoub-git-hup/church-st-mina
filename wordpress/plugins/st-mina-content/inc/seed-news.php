<?php
/**
 * إدخال الأخبار مرة واحدة، بنفس طريقة seed-church.php (مع أول طلب، وبقفل):
 * صفحة "الأخبار"، والـ 10 أخبار وإعلانات اللي في design/news.html (من ملصقات وصور الكنيسة الحقيقية)،
 * ومناسبة عيد الميلاد 2027 من design/season.html.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_loaded', function () {
	if ( get_option( 'stmina_news_seeded' ) || ! add_option( 'stmina_news_seeding', time(), '', false ) ) {
		return;
	}
	stmina_seed_news();
	update_option( 'stmina_news_seeded', STMINA_CONTENT_VERSION );
	delete_option( 'stmina_news_seeding' );
	flush_rewrite_rules(); // علشان روابط /news/... تشتغل
}, 20 );

function stmina_seed_news() {
	if ( ! get_page_by_path( 'news' ) ) {
		$id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => 'news',
			'post_title'   => 'الأخبار',
			'post_excerpt' => 'آخر الأخبار والإعلانات، والمناسبات القادمة.',
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			stmina_seed_meta( 'post', $id, 'hero_eyebrow', 'الأخبار', 'field_stmina_page_eyebrow', $id );
			stmina_seed_meta( 'post', $id, 'hero_title', 'أخبار الكنيسة', 'field_stmina_page_title', $id );
			stmina_seed_meta( 'post', $id, 'hero_highlight', 'الكنيسة', 'field_stmina_page_highlight', $id );
			$hero = stmina_seed_image( 'church/bg-window-rays.jpg' );
			if ( $hero ) {
				set_post_thumbnail( $id, $hero );
			}
		}
	}

	// العنوان، والنوع، والميعاد المكتوب، والنبذة، والصورة، وملصق؟، ومثبّت؟ بترتيب التصميم
	$items = array(
		array( 'نهضة السيدة العذراء مريم', 'ann', '7 – 22 أغسطس', 'برنامج نهضة السيدة العذراء 2026 بالكنيسة.', 'events/virgin-mary-revival-2026.jpg', 1, 1 ),
		array( 'الحفل السنوي "أم للبيع"', 'ann', 'الجمعة 28 أغسطس', 'الحفل السنوي للكنيسة.', 'events/annual-party-umm-lilbay.jpg', 1, 1 ),
		array( 'رحلة أديرة المنيا', 'news', 'الشباب · 2026', 'رحلة الشباب لزيارة أديرة المنيا.', 'services/youth/minya-monasteries-trip-2026-1.jpeg', 0, 1 ),
		array( 'كورس المشورة للمقبلين على الزواج', 'ann', 'يبدأ 14 يونيو 2026', 'شهادة اجتياز ضمن مسوغات الزواج، ويقبل الشباب الجامعي والطلبة.', 'services/counseling/counseling-course-poster.jpg', 1, 0 ),
		array( 'تسليم ميداليات مسابقة كرة القدم لمرحلة ابتدائي', 'news', '2026', 'بحضور آباء الكنيسة والخدام وأولياء الأمور.', 'services/sunday-school/primary-football-1.jpg', 0, 0 ),
		array( 'نهضة البابا كيرلس السادس', 'ann', '6 – 9 مارس', 'زفة وتطييب وكلمة روحية 6–8 م، والقداس الإثنين 9 مارس على مذبح البابا كيرلس.', 'events/pope-kyrillos-revival.jpg', 1, 0 ),
		array( 'جدول قداسات الصوم الكبير', 'ann', 'الصوم الكبير 2026', 'قداسات يومية صباحًا ومساءً طوال أيام الصوم.', 'worship/schedule-great-lent-2026.jpg', 1, 0 ),
		array( 'مؤتمر "عنبر 11" للشباب', 'news', 'الشباب · 2026', 'مؤتمر الشباب لعام 2026.', 'services/youth/youth-conference-anbar11-2026.jpeg', 0, 0 ),
		array( 'قافلة تخصصات عيادات البتول', 'ann', '17 أكتوبر 2025', 'قافلة طبية متعددة التخصصات.', 'services/clinics/batool-clinics-convoy.jpg', 1, 0 ),
		array( 'أمسية العبور', 'news', 'الشباب · 2025', 'أمسية روحية للشباب.', 'services/youth/obour-evening-2025-1.jpeg', 0, 0 ),
	);
	// التواريخ الحقيقية مش معروفة، فبنخلّي كل خبر أقدم من اللي قبله بدقيقة علشان الترتيب يفضل زي التصميم.
	// والتاريخ مابيظهرش، لأن كل خبر ليه "ميعاد مكتوب"
	$now = time();
	foreach ( $items as $i => $n ) {
		$id = wp_insert_post( array(
			'post_type'    => 'stmina_news',
			'post_status'  => 'publish',
			'post_title'   => $n[0],
			'post_excerpt' => $n[3],
			'post_date'    => wp_date( 'Y-m-d H:i:s', $now - ( $i + 1 ) * 60 ), // wp_date بتحوّل لتوقيت القاهرة
		) );
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		$meta = array( 'kind' => $n[1], 'when_label' => $n[2], 'is_poster' => $n[5], 'pinned' => $n[6], 'is_season' => 0 );
		$keys = array( 'kind' => 'kind', 'when_label' => 'when', 'is_poster' => 'poster', 'pinned' => 'pinned', 'is_season' => 'season' );
		foreach ( $meta as $name => $value ) {
			stmina_seed_meta( 'post', $id, $name, $value, 'field_stmina_news_' . $keys[ $name ], $id );
		}
		$img = stmina_seed_image( $n[4] );
		if ( $img ) {
			set_post_thumbnail( $id, $img );
		}
	}

	// مناسبة عيد الميلاد. المواعيد اللي لسه ماتحددتش مكتوب عليها "يُعلن"
	$id = wp_insert_post( array(
		'post_type'    => 'stmina_news',
		'post_status'  => 'publish',
		'post_title'   => 'عيد الميلاد المجيد',
		'post_excerpt' => 'كل عام وأنتم بخير. مواعيد صوم الميلاد وتسبحة كيهك وقداس العيد.',
		'post_content' => "<!-- wp:paragraph -->\n<p>نص اختياري يكتبه محرر المحتوى: كلمة روحية عن الميلاد، أو برنامج الاحتفال.</p>\n<!-- /wp:paragraph -->",
	) );
	if ( ! $id || is_wp_error( $id ) ) {
		return;
	}
	$season = array(
		'kind'           => array( 'ann', 'kind' ),
		'when_label'     => array( 'ليلة 6 يناير 2027', 'when' ),
		'place'          => array( 'الكنيسة الكبيرة', 'place' ),
		'highlight'      => array( 'الميلاد المجيد', 'highlight' ),
		'is_poster'      => array( 0, 'poster' ),
		'pinned'         => array( 0, 'pinned' ),
		'is_season'      => array( 1, 'season' ),
		'event_at'       => array( '2027-01-06 23:00:00', 'event_at' ),
		'time_note'      => array( 'موعد القداس يُعلن قريبًا', 'time_note' ),
		'verse_label'    => array( 'بشارة الميلاد', 'verse_label' ),
		'verse'          => array( 'أَنَّهُ وُلِدَ لَكُمُ الْيَوْمَ فِي مَدِينَةِ دَاوُدَ مُخَلِّصٌ هُوَ الْمَسِيحُ الرَّبُّ', 'verse' ),
		'verse_ref'      => array( 'لوقا 2: 11', 'verse_ref' ),
		'rows_title'     => array( 'من صوم الميلاد إلى العيد', 'rows_title' ),
		'rows_highlight' => array( 'إلى العيد', 'rows_highlight' ),
		'season_rows'    => array( "صوم الميلاد | يبدأ 25 نوفمبر | 43 يومًا\nتسبحة كيهك | المواعيد تُعلن | طوال شهر كيهك\nليلة العيد | 6 يناير | موعد القداس يُعلن", 'rows' ),
	);
	foreach ( $season as $name => $v ) {
		stmina_seed_meta( 'post', $id, $name, $v[0], 'field_stmina_news_' . $v[1], $id );
	}
	$img = stmina_seed_image( 'worship/youth-night-liturgy-2.jpeg' );
	if ( $img ) {
		set_post_thumbnail( $id, $img );
	}
}
