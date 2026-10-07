<?php
/**
 * الأخبار والإعلانات والمناسبات الموسمية: نوع محتوى واحد "خبر" (/news/اسمه/).
 * - النوع (خبر أو إعلان) خانة، والفلتر في الصفحة بيفصل بينهم.
 * - "مثبّت" بيطلّع الخبر في الدواير اللي فوق في الرئيسية وصفحة الأخبار.
 * - "مناسبة موسمية" (الميلاد، أسبوع الآلام، القيامة ...) بتظهر لها خانات زيادة: ميعاد العد التنازلي،
 *   والآية، ومواعيد الموسم. وأقرب مناسبة جاية بتظهر لوحدها في الرئيسية وأول صفحة الأخبار.
 * كل الأخبار في صفحة "الأخبار" (/news/)، فمفيش صفحة أرشيف.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_post_type( 'stmina_news', array(
		'labels'        => array(
			'name'          => 'الأخبار',
			'singular_name' => 'خبر',
			'add_new'       => 'إضافة خبر',
			'add_new_item'  => 'إضافة خبر أو إعلان أو مناسبة',
			'edit_item'     => 'تعديل الخبر',
			'all_items'     => 'كل الأخبار',
			'search_items'  => 'بحث في الأخبار',
			'not_found'     => 'مفيش أخبار',
		),
		'public'        => true,
		'show_in_rest'  => true,
		'menu_icon'     => 'dashicons-megaphone',
		'menu_position' => 3,
		'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
		'has_archive'   => false, // صفحة "الأخبار" (/news/) عادية، علشان الواجهة بتاعتها تتعدّل من dashboard
		'rewrite'       => array( 'slug' => 'news', 'with_front' => false ),
	) );
} );

/**
 * علامة نعم/لا من خانة ACF.
 */
function stmina_news_flag( $post, $name ) {
	return (bool) get_post_meta( get_post( $post )->ID, $name, true );
}

/**
 * الأخبار العادية (من غير المناسبات)، الأحدث الأول.
 *
 * @param array $args إضافات لـ get_posts.
 * @return WP_Post[]
 */
function stmina_news( $args = array() ) {
	$args += array(
		'post_type'   => 'stmina_news',
		'numberposts' => -1,
		'orderby'     => 'date',
		'order'       => 'DESC',
		'meta_query'  => array(),
	);
	// المناسبة مالهاش مكان في قايمة الأخبار، ليها القسم بتاعها
	$args['meta_query'][] = array(
		'relation' => 'OR',
		array( 'key' => 'is_season', 'compare' => 'NOT EXISTS' ),
		array( 'key' => 'is_season', 'value' => '1', 'compare' => '!=' ),
	);
	return get_posts( $args );
}

/**
 * الأخبار المثبّتة (الدواير) والباقي، من غير تكرار.
 *
 * @param int $pins  عدد الدواير.
 * @param int $limit عدد الكروت (-1 للكل).
 * @return array{0:WP_Post[],1:WP_Post[]}
 */
function stmina_news_split( $pins = 3, $limit = -1 ) {
	$pinned = stmina_news( array(
		'numberposts' => $pins,
		'meta_query'  => array( array( 'key' => 'pinned', 'value' => '1' ) ),
	) );
	$rest = stmina_news( array(
		'numberposts'  => $limit,
		'post__not_in' => wp_list_pluck( $pinned, 'ID' ),
	) );
	return array( $pinned, $rest );
}

/**
 * أقرب مناسبة موسمية لسه ماجاتش، أو null.
 */
function stmina_next_season() {
	$found = get_posts( array(
		'post_type'   => 'stmina_news',
		'numberposts' => 1,
		'meta_key'    => 'event_at',
		'orderby'     => 'meta_value',
		'order'       => 'ASC',
		'meta_query'  => array(
			array( 'key' => 'is_season', 'value' => '1' ),
			// القيمة متخزّنة Y-m-d H:i:s، فالمقارنة كنص بتمشي صح
			array( 'key' => 'event_at', 'value' => current_time( 'mysql' ), 'compare' => '>=' ),
		),
	) );
	return $found ? $found[0] : null;
}

/**
 * ميعاد المناسبة بصيغة ISO بتوقيت الموقع، للعد التنازلي: 2027-01-06T23:00:00+02:00
 */
function stmina_season_iso( $post ) {
	$at = get_post_meta( get_post( $post )->ID, 'event_at', true );
	if ( ! $at ) {
		return '';
	}
	try {
		return ( new DateTimeImmutable( $at, wp_timezone() ) )->format( 'c' );
	} catch ( Exception $e ) {
		return '';
	}
}

/**
 * مواعيد الموسم من خانة النص: كل سطر "الاسم | الميعاد | ملاحظة".
 *
 * @return array كل صف: array( 'name', 'when', 'note' )
 */
function stmina_season_rows( $post ) {
	$rows = array();
	// u ضروري: من غيره \R بيعتبر البايت 0x85 سطر جديد، وده جزء من حروف عربي كتير زي "م"
	foreach ( preg_split( '/\R/u', (string) get_post_meta( get_post( $post )->ID, 'season_rows', true ) ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line ) );
		if ( '' === $parts[0] ) {
			continue;
		}
		$rows[] = array(
			'name' => $parts[0],
			'when' => isset( $parts[1] ) ? $parts[1] : '',
			'note' => isset( $parts[2] ) ? $parts[2] : '',
		);
	}
	return $rows;
}
