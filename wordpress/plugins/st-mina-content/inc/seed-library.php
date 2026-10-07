<?php
/**
 * إدخال المكتبة مرة واحدة، بنفس طريقة seed-church.php (مع أول طلب، وبقفل):
 * صفحة "المكتبة"، والمواضيع، و6 عظات فيديو حقيقية من قناة القمص كيرلس روماني على YouTube.
 * النشرات والترانيم مالهاش إدخال: التبويب بيقول "هتنزل قريب" لحد ما حد يرفع أول ملف.
 */

defined( 'ABSPATH' ) || exit;

// أولوية 20: بعد إدخال الكنيسة، علشان الأب الكاهن يكون موجود قبل ما نربطه بالعظة
add_action( 'wp_loaded', function () {
	if ( get_option( 'stmina_library_seeded' ) || ! add_option( 'stmina_library_seeding', time(), '', false ) ) {
		return;
	}
	stmina_seed_library();
	update_option( 'stmina_library_seeded', STMINA_CONTENT_VERSION );
	delete_option( 'stmina_library_seeding' );
	flush_rewrite_rules(); // علشان روابط /sermon/... تشتغل
}, 20 );

function stmina_seed_library() {
	if ( ! get_page_by_path( 'library' ) ) {
		$id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => 'library',
			'post_title'   => 'المكتبة',
			'post_excerpt' => 'عظات الآباء، والنشرة الشهرية، وترانيم فرق الكنيسة في مكان واحد.',
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			stmina_seed_meta( 'post', $id, 'hero_eyebrow', 'المكتبة', 'field_stmina_page_eyebrow', $id );
			stmina_seed_meta( 'post', $id, 'hero_title', 'كلمة تبني', 'field_stmina_page_title', $id );
			stmina_seed_meta( 'post', $id, 'hero_highlight', 'تبني', 'field_stmina_page_highlight', $id );
			$hero = stmina_seed_image( 'services/counseling/counseling-course-2026-1.jpeg' );
			if ( $hero ) {
				set_post_thumbnail( $id, $hero );
			}
		}
	}

	// المواضيع: الاسم والرابط الداخلي. استنتجناها من أسماء السلاسل على القناة، ووافق عليها المستخدم
	$topics = array();
	foreach ( array( 'family' => 'الأسرة', 'nativity' => 'الميلاد', 'bible-characters' => 'شخصيات من الكتاب المقدس', 'speech' => 'اللسان والكلام' ) as $slug => $name ) {
		$term = term_exists( $slug, 'stmina_topic' );
		if ( ! $term ) {
			$term = wp_insert_term( $name, 'stmina_topic', array( 'slug' => $slug ) );
		}
		if ( ! is_wp_error( $term ) ) {
			$topics[ $slug ] = (int) $term['term_id'];
		}
	}

	$kyrillos = get_posts( array( 'post_type' => 'stmina_priest', 'title' => 'القمص كيرلس روماني', 'numberposts' => 1, 'fields' => 'ids' ) );
	$speaker  = $kyrillos ? $kyrillos[0] : 0;

	// العنوان، ورقم الفيديو، وتاريخ الرفع على القناة بتوقيت القاهرة، والموضوع
	$sermons = array(
		array( 'الأبوة وتأثيرها الإيجابي', 'YmfjBaM73ug', '2023-01-19 15:21:31', 'family' ),
		array( 'جاء ليبارك بلادنا (الجزء 5)', 'XfoNUodv7Pc', '2021-01-13 16:36:10', 'nativity' ),
		array( 'وصيتي للحماة (الجزء 1)', '5SFhhc93NpQ', '2020-12-15 17:57:14', 'family' ),
		array( 'زكا العشار من محبة المال للسخاء في العطاء (الجزء 4)', 'OAl10C4-iG8', '2020-09-08 18:45:48', 'bible-characters' ),
		array( 'شمشون والأسد (الجزء 1)', 'gy1jVKJw8Es', '2020-09-08 18:38:43', 'bible-characters' ),
		array( 'الكلام والصيام (الجزء 3)', 'b_vUjzKJAGA', '2020-08-23 11:50:45', 'speech' ),
	);
	foreach ( $sermons as $s ) {
		$id = wp_insert_post( array(
			'post_type'   => 'stmina_sermon',
			'post_status' => 'publish',
			'post_title'  => $s[0],
			'post_date'   => $s[2],
		) );
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		stmina_seed_meta( 'post', $id, 'video_url', 'https://www.youtube.com/watch?v=' . $s[1], 'field_stmina_sermon_video', $id );
		if ( $speaker ) {
			stmina_seed_meta( 'post', $id, 'speaker', $speaker, 'field_stmina_sermon_speaker', $id );
		}
		if ( isset( $topics[ $s[3] ] ) ) {
			wp_set_object_terms( $id, $topics[ $s[3] ], 'stmina_topic' );
		}
	}
}
