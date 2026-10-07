<?php
/**
 * إدخال الجدول وصفحة العبادة مرة واحدة، بنفس طريقة seed-church.php (مع أول طلب، وبقفل).
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_loaded', function () {
	if ( get_option( 'stmina_worship_seeded' ) || ! add_option( 'stmina_worship_seeding', time(), '', false ) ) {
		return;
	}
	stmina_seed_worship();
	update_option( 'stmina_worship_seeded', STMINA_CONTENT_VERSION );
	delete_option( 'stmina_worship_seeding' );
	flush_rewrite_rules();
} );

function stmina_seed_worship() {
	// اليوم (0 = الأحد)، ومن، ولحد، والنوع، والملاحظة. من design/worship.html
	$slots = array(
		array( 0, '07:00:00', '10:00:00', 'mass', '' ),
		array( 0, '09:00:00', '11:00:00', 'mass', '' ),
		array( 2, '08:00:00', '09:30:00', 'mass', '' ),
		array( 3, '08:00:00', '10:00:00', 'mass', '' ),
		array( 5, '07:00:00', '09:00:00', 'mass', 'التربية الكنسية' ),
		array( 5, '07:00:00', '09:30:00', 'mass', 'الشعب' ),
		array( 5, '10:00:00', '12:00:00', 'mass', '' ),
		array( 6, '08:00:00', '10:00:00', 'mass', '' ),
		array( 5, '15:00:00', '17:00:00', 'meeting', 'اجتماع الصلاة ودراسة الكتاب المقدس · الدور الأرضي' ),
		array( 5, '18:00:00', '', 'meeting', 'اجتماع الشباب · الكنيسة الكبيرة، الروف' ),
	);
	$days = stmina_days();
	foreach ( $slots as $s ) {
		$title = $days[ $s[0] ] . ' ' . stmina_slot_time( array( 'start' => $s[1], 'end' => $s[2] ) ) . ( $s[4] ? ' · ' . $s[4] : '' );
		$id    = wp_insert_post( array( 'post_type' => 'stmina_slot', 'post_status' => 'publish', 'post_title' => $title ) );
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		$map = array( 'day' => $s[0], 'start' => $s[1], 'end' => $s[2], 'kind' => $s[3], 'note' => $s[4] );
		foreach ( $map as $name => $value ) {
			stmina_seed_meta( 'post', $id, $name, $value, 'field_stmina_slot_' . $name, $id );
		}
	}

	if ( get_page_by_path( 'worship' ) ) {
		return;
	}
	$p = function ( $text ) {
		return "<!-- wp:paragraph -->\n<p>" . $text . "</p>\n<!-- /wp:paragraph -->\n\n";
	};
	$confession = $p( 'نص قصير عن سر التوبة والاعتراف يكتبه محرر المحتوى من لوحة التحكم: معنى التوبة، ودور أب الاعتراف، والاستعداد للتناول من الأسرار المقدسة.' )
		. $p( 'للاعتراف، تواصل مع أب اعترافك من آباء الكنيسة لتحديد موعد مناسب.' )
		. "<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\"><!-- wp:paragraph -->\n<p>«إِنِ ٱعْتَرَفْنَا بِخَطَايَانَا فَهُوَ أَمِينٌ وَعَادِلٌ، حَتَّى يَغْفِرَ لَنَا خَطَايَانَا وَيُطَهِّرَنَا مِنْ كُلِّ إِثْمٍ»</p>\n<!-- /wp:paragraph --><cite>(1 يوحنا 1: 9)</cite></blockquote>\n<!-- /wp:quote -->";
	$id = wp_insert_post( array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_name'    => 'worship',
		'post_title'   => 'العبادة',
		'post_excerpt' => 'مواعيد القداسات والاجتماعات طوال الأسبوع، وقراءات كل يوم، وسر التوبة والاعتراف.',
		'post_content' => $confession,
	) );
	if ( ! $id || is_wp_error( $id ) ) {
		return;
	}
	stmina_seed_meta( 'post', $id, 'hero_eyebrow', 'صلِّ معنا', 'field_stmina_page_eyebrow', $id );
	stmina_seed_meta( 'post', $id, 'hero_title', 'العبادة والقداسات', 'field_stmina_page_title', $id );
	stmina_seed_meta( 'post', $id, 'hero_highlight', 'والقداسات', 'field_stmina_page_highlight', $id );
	$hero = stmina_seed_image( 'worship/youth-night-liturgy-3.jpeg' );
	if ( $hero ) {
		set_post_thumbnail( $id, $hero );
	}
}
