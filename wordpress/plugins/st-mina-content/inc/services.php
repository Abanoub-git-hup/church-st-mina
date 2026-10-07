<?php
/**
 * نوع المحتوى "خدمة" وتصنيف "مجموعة الخدمات".
 */

defined( 'ABSPATH' ) || exit;

function stmina_register_services() {
	register_post_type( 'stmina_service', array(
		'labels'        => array(
			'name'          => 'الخدمات',
			'singular_name' => 'خدمة',
			'add_new'       => 'إضافة خدمة',
			'add_new_item'  => 'إضافة خدمة جديدة',
			'edit_item'     => 'تعديل الخدمة',
			'all_items'     => 'كل الخدمات',
			'search_items'  => 'بحث في الخدمات',
			'not_found'     => 'مفيش خدمات',
		),
		'public'        => true,
		'show_in_rest'  => true, // علشان محرر البلوكات يشتغل
		'menu_icon'     => 'dashicons-groups',
		'menu_position' => 5,
		'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
		'has_archive'   => 'services',              // صفحة كل الخدمات: /services/
		'rewrite'       => array( 'slug' => 'service', 'with_front' => false ), // خدمة واحدة: /service/اسمها/
	) );

	register_taxonomy( 'stmina_service_group', 'stmina_service', array(
		'labels'            => array(
			'name'          => 'المجموعات',
			'singular_name' => 'مجموعة',
			'add_new_item'  => 'إضافة مجموعة',
			'edit_item'     => 'تعديل المجموعة',
			'all_items'     => 'كل المجموعات',
		),
		'hierarchical'      => true, // شكل الاختيار في المحرر مربعات زي التصنيفات
		'show_in_rest'      => true,
		'show_admin_column' => true, // عمود المجموعة في قايمة الخدمات
		'rewrite'           => array( 'slug' => 'service-group', 'with_front' => false ),
	) );
}
add_action( 'init', 'stmina_register_services' );

/**
 * المجموعات بترتيبها (من خانة "الترتيب" في المجموعة).
 *
 * @return WP_Term[]
 */
function stmina_service_groups() {
	$terms = get_terms( array(
		'taxonomy'   => 'stmina_service_group',
		'hide_empty' => false,
	) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	usort( $terms, function ( $a, $b ) {
		return (int) stmina_term_field( 'order', $a ) <=> (int) stmina_term_field( 'order', $b );
	} );
	return $terms;
}

/**
 * الخدمات في مجموعة، بترتيب "Order" اللي في المحرر.
 *
 * @param WP_Term|null $group لو null بيرجّع كل الخدمات.
 * @param int[]        $exclude خدمات مايتجابوش (زي الخدمة المفتوحة دلوقتي).
 * @return WP_Post[]
 */
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

/**
 * أول مجموعة للخدمة.
 */
function stmina_service_group( $post ) {
	$terms = get_the_terms( $post, 'stmina_service_group' );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
}
