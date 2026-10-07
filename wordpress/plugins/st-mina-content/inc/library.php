<?php
/**
 * المكتبة: العظات (ولكل عظة صفحة)، والنشرات، والترانيم.
 * العظة نوعها بيتحدد لوحده من الخانة اللي فيها قيمة: رابط فيديو، أو ملف صوت، أو ملف PDF.
 * والمتحدث يا أب من "الآباء الكهنة"، يا اسم ضيف مكتوب.
 */

defined( 'ABSPATH' ) || exit;

function stmina_register_library() {
	register_post_type( 'stmina_sermon', array(
		'labels'        => array(
			'name'          => 'العظات',
			'singular_name' => 'عظة',
			'add_new'       => 'إضافة عظة',
			'add_new_item'  => 'إضافة عظة جديدة',
			'edit_item'     => 'تعديل العظة',
			'all_items'     => 'كل العظات',
			'search_items'  => 'بحث في العظات',
			'not_found'     => 'مفيش عظات',
		),
		'public'        => true,
		'show_in_rest'  => true,
		'menu_icon'     => 'dashicons-microphone',
		'menu_position' => 9,
		'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
		'has_archive'   => false, // كل العظات في صفحة "المكتبة" (/library/) مع النشرات والترانيم
		'rewrite'       => array( 'slug' => 'sermon', 'with_front' => false ), // /sermon/اسمها/
	) );

	register_taxonomy( 'stmina_topic', 'stmina_sermon', array(
		'labels'            => array(
			'name'          => 'المواضيع',
			'singular_name' => 'موضوع',
			'add_new_item'  => 'إضافة موضوع',
			'edit_item'     => 'تعديل الموضوع',
			'all_items'     => 'كل المواضيع',
		),
		'public'            => false, // الفلترة جوه صفحة المكتبة، فمش محتاجين صفحة لكل موضوع
		'show_ui'           => true,
		'show_in_rest'      => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
	) );

	$hidden = array(
		'public'             => false,
		'show_ui'            => true,
		'show_in_rest'       => true,
		'publicly_queryable' => false,
		'show_in_menu'       => 'edit.php?post_type=stmina_sermon', // تحت "العظات" في dashboard
	);

	register_post_type( 'stmina_bulletin', $hidden + array(
		'labels'   => array(
			'name'          => 'النشرات',
			'singular_name' => 'نشرة',
			'add_new_item'  => 'إضافة نشرة',
			'edit_item'     => 'تعديل النشرة',
			'all_items'     => 'النشرات',
		),
		'supports' => array( 'title', 'excerpt', 'thumbnail' ),
	) );

	register_post_type( 'stmina_hymn', $hidden + array(
		'labels'   => array(
			'name'          => 'الترانيم',
			'singular_name' => 'ترنيمة',
			'add_new_item'  => 'إضافة ترنيمة',
			'edit_item'     => 'تعديل الترنيمة',
			'all_items'     => 'الترانيم',
		),
		'supports' => array( 'title', 'page-attributes' ),
	) );
}
add_action( 'init', 'stmina_register_library' );

/**
 * العظات الأحدث الأول (تاريخ النشر هو تاريخ العظة).
 *
 * @param array $args إضافات لـ get_posts، زي numberposts أو meta_query.
 * @return WP_Post[]
 */
function stmina_sermons( $args = array() ) {
	return get_posts( $args + array(
		'post_type'   => 'stmina_sermon',
		'numberposts' => -1,
		'orderby'     => 'date',
		'order'       => 'DESC',
	) );
}

/**
 * نوع العظة من الخانات: video أو audio أو pdf، أو '' لو لسه مفيش ملف.
 */
function stmina_sermon_kind( $post ) {
	$post = get_post( $post );
	if ( get_post_meta( $post->ID, 'video_url', true ) ) {
		return 'video';
	}
	if ( get_post_meta( $post->ID, 'audio', true ) ) {
		return 'audio';
	}
	if ( get_post_meta( $post->ID, 'pdf', true ) ) {
		return 'pdf';
	}
	return '';
}

/**
 * المتحدث: الاسم، ومفتاح الفلتر (p + رقم الأب، أو guest)، ورقم الأب لو موجود.
 *
 * @return array{name:string,key:string,priest:int}
 */
function stmina_sermon_speaker( $post ) {
	$post   = get_post( $post );
	$priest = (int) get_post_meta( $post->ID, 'speaker', true );
	if ( $priest && 'stmina_priest' === get_post_type( $priest ) ) {
		return array( 'name' => get_the_title( $priest ), 'key' => 'p' . $priest, 'priest' => $priest );
	}
	$guest = trim( (string) get_post_meta( $post->ID, 'guest_name', true ) );
	return array( 'name' => $guest ? $guest : 'متحدث ضيف', 'key' => 'guest', 'priest' => 0 );
}

/**
 * أول موضوع للعظة.
 */
function stmina_sermon_topic( $post ) {
	$terms = get_the_terms( $post, 'stmina_topic' );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
}

/**
 * رقم فيديو يوتيوب من أي شكل للرابط (watch?v= أو youtu.be أو shorts أو embed)، أو ''.
 */
function stmina_youtube_id( $url ) {
	if ( preg_match( '~(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/))([\w-]{11})~', (string) $url, $m ) ) {
		return $m[1];
	}
	return '';
}

/**
 * النشرات، الأحدث الأول.
 *
 * @return WP_Post[]
 */
function stmina_bulletins() {
	return get_posts( array( 'post_type' => 'stmina_bulletin', 'numberposts' => -1, 'orderby' => 'date', 'order' => 'DESC' ) );
}

/**
 * الترانيم بالترتيب (خانة Order).
 *
 * @return WP_Post[]
 */
function stmina_hymns() {
	return stmina_ordered( 'stmina_hymn' );
}
