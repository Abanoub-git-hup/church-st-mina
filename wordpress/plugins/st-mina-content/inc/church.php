<?php
/**
 * محتوى صفحات الكنيسة: الآباء الكهنة، ومحطات التاريخ، وألبومات الصور.
 * التلاتة مالهمش صفحات لوحدهم (publicly_queryable = false)، وبيظهروا جوه صفحات الكنيسة بس.
 */

defined( 'ABSPATH' ) || exit;

function stmina_register_church() {
	$common = array(
		'public'             => false, // مالهمش روابط ولا بيظهروا في البحث
		'show_ui'            => true,  // بس بيظهروا في dashboard
		'show_in_rest'       => true,
		'publicly_queryable' => false,
	);

	register_post_type( 'stmina_priest', $common + array(
		'labels'        => array(
			'name'          => 'الآباء الكهنة',
			'singular_name' => 'أب كاهن',
			'add_new_item'  => 'إضافة أب كاهن',
			'edit_item'     => 'تعديل',
			'all_items'     => 'كل الآباء',
		),
		'menu_icon'     => 'dashicons-businessman',
		'menu_position' => 6,
		'supports'      => array( 'title', 'excerpt', 'thumbnail', 'page-attributes' ),
	) );

	register_post_type( 'stmina_milestone', $common + array(
		'labels'        => array(
			'name'          => 'محطات التاريخ',
			'singular_name' => 'محطة',
			'add_new_item'  => 'إضافة محطة',
			'edit_item'     => 'تعديل المحطة',
			'all_items'     => 'كل المحطات',
		),
		'menu_icon'     => 'dashicons-backup',
		'menu_position' => 7,
		'supports'      => array( 'title', 'excerpt', 'page-attributes' ),
	) );

	register_post_type( 'stmina_album', $common + array(
		'labels'        => array(
			'name'          => 'ألبومات الصور',
			'singular_name' => 'ألبوم',
			'add_new_item'  => 'إضافة ألبوم',
			'edit_item'     => 'تعديل الألبوم',
			'all_items'     => 'كل الألبومات',
		),
		'menu_icon'     => 'dashicons-format-gallery',
		'menu_position' => 8,
		'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
	) );

	register_taxonomy( 'stmina_album_cat', 'stmina_album', array(
		'labels'            => array(
			'name'          => 'تصنيفات الصور',
			'singular_name' => 'تصنيف',
			'add_new_item'  => 'إضافة تصنيف',
		),
		'public'            => false,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
	) );

	// الصفحات العادية مالهاش مقتطف، وإحنا بنستخدمه سطر وصف تحت عنوان الواجهة
	add_post_type_support( 'page', 'excerpt' );
}
add_action( 'init', 'stmina_register_church' );

/**
 * محتوى من النوع ده بالترتيب (خانة Order في المحرر).
 *
 * @return WP_Post[]
 */
function stmina_ordered( $type ) {
	return get_posts( array(
		'post_type'      => $type,
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'ID' => 'ASC' ),
	) );
}

/**
 * الصور المرفوعة جوه محتوى معيّن، بترتيبها، من غير الصورة البارزة.
 * دي طريقة الملصقات عند الآباء، والصور جوه الألبومات.
 *
 * @return WP_Post[]
 */
function stmina_attached_images( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return array();
	}
	return get_children( array(
		'post_parent'    => $post->ID,
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'orderby'        => array( 'menu_order' => 'ASC', 'ID' => 'ASC' ),
		'exclude'        => get_post_thumbnail_id( $post ),
	) );
}
