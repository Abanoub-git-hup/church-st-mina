<?php
/**
 * خانات ACF متعرّفة في الكود (acf_add_local_field_group) بدل ما تتعمل من لوحة ACF،
 * علشان تبقى في GitHub وتتنقل مع الـ plugin لأي موقع.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/include_fields', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	// ---------- خانات الخدمة ----------
	acf_add_local_field_group( array(
		'key'      => 'group_stmina_service',
		'title'    => 'بيانات الخدمة',
		'position' => 'acf_after_title',
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'stmina_service' ) ) ),
		'fields'   => array(
			array( 'key' => 'field_stmina_schedule_short', 'name' => 'schedule_short', 'label' => 'الموعد المختصر', 'type' => 'text', 'instructions' => 'بيظهر على الكارت جنب اسم المجموعة. مثال: الجمعة 6 م', 'wrapper' => array( 'width' => 50 ) ),
			array( 'key' => 'field_stmina_schedule', 'name' => 'schedule', 'label' => 'الموعد بالتفصيل', 'type' => 'text', 'instructions' => 'بيظهر في دايرة الموعد. مثال: كل جمعة 6:00 مساءً', 'wrapper' => array( 'width' => 50 ) ),
			array( 'key' => 'field_stmina_place', 'name' => 'place', 'label' => 'المكان', 'type' => 'text', 'instructions' => 'مثال: الكنيسة الكبيرة، الروف' ),
			array( 'key' => 'field_stmina_priest_name', 'name' => 'priest_name', 'label' => 'الأب الكاهن', 'type' => 'text', 'wrapper' => array( 'width' => 50 ) ),
			array( 'key' => 'field_stmina_priest_phone', 'name' => 'priest_phone', 'label' => 'موبايل الأب الكاهن', 'type' => 'text', 'instructions' => '11 رقم، مثال: 01552921322', 'wrapper' => array( 'width' => 50 ) ),
			array( 'key' => 'field_stmina_servant_name', 'name' => 'servant_name', 'label' => 'الخادم المسؤول', 'type' => 'text', 'wrapper' => array( 'width' => 50 ) ),
			array( 'key' => 'field_stmina_servant_phone', 'name' => 'servant_phone', 'label' => 'موبايل الخادم', 'type' => 'text', 'wrapper' => array( 'width' => 50 ) ),
			array( 'key' => 'field_stmina_is_new', 'name' => 'is_new', 'label' => 'خدمة جديدة', 'type' => 'true_false', 'ui' => 1, 'instructions' => 'بيظهر عليها شارة "جديد"', 'wrapper' => array( 'width' => 33 ) ),
			array( 'key' => 'field_stmina_temp_image', 'name' => 'temp_image', 'label' => 'الصورة مؤقتة', 'type' => 'true_false', 'ui' => 1, 'instructions' => 'بيظهر عليها "صورة مؤقتة" لحد ما تتغيّر', 'wrapper' => array( 'width' => 33 ) ),
			array(
				'key' => 'field_stmina_image_fit', 'name' => 'image_fit', 'label' => 'شكل الصورة', 'type' => 'select', 'wrapper' => array( 'width' => 34 ),
				'choices' => array( 'cover' => 'صورة عادية', 'top' => 'ملصق (يبان من فوق)', 'paper' => 'لوجو بخلفية بيضا' ),
				'default_value' => 'cover',
			),
		),
	) );

	// ---------- خانات المجموعة ----------
	acf_add_local_field_group( array(
		'key'      => 'group_stmina_service_group',
		'title'    => 'بيانات المجموعة',
		'location' => array( array( array( 'param' => 'taxonomy', 'operator' => '==', 'value' => 'stmina_service_group' ) ) ),
		'fields'   => array(
			array( 'key' => 'field_stmina_group_label', 'name' => 'label', 'label' => 'الكلمة الصغيرة فوق العنوان', 'type' => 'text', 'instructions' => 'مثال: 3 مراحل' ),
			array( 'key' => 'field_stmina_group_highlight', 'name' => 'highlight', 'label' => 'الجزء الدهبي من الاسم', 'type' => 'text', 'instructions' => 'لازم يكون آخر جزء في اسم المجموعة. مثال: في "مدارس الأحد" اكتب "الأحد". سيبه فاضي لو مش عايز لون' ),
			array( 'key' => 'field_stmina_group_image', 'name' => 'image', 'label' => 'صورة الدايرة', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail' ),
			array( 'key' => 'field_stmina_group_paper', 'name' => 'paper', 'label' => 'الصورة لوجو بخلفية بيضا', 'type' => 'true_false', 'ui' => 1 ),
			array( 'key' => 'field_stmina_group_order', 'name' => 'order', 'label' => 'الترتيب', 'type' => 'number', 'default_value' => 10 ),
		),
	) );

	// ---------- الأب الكاهن ----------
	acf_add_local_field_group( array(
		'key'          => 'group_stmina_priest',
		'title'        => 'بيانات الأب',
		'position'     => 'acf_after_title',
		'instructions' => '',
		'location'     => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'stmina_priest' ) ) ),
		'fields'       => array(
			array( 'key' => 'field_stmina_priest_rank', 'name' => 'rank', 'label' => 'الصفة', 'type' => 'text', 'default_value' => 'كاهن الكنيسة' ),
			array( 'key' => 'field_stmina_priest_face', 'name' => 'face_image', 'label' => 'صورة الوش', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => 'صورة شخصية واضحة. لو الصورة ملصق والوش جزء منه، اضبط القص من الخانات اللي تحت' ),
			array( 'key' => 'field_stmina_priest_fw', 'name' => 'face_w', 'label' => 'عرض الصورة في الدايرة', 'type' => 'number', 'instructions' => 'سيبه فاضي لو الصورة شخصية عادية', 'wrapper' => array( 'width' => 33 ) ),
			array( 'key' => 'field_stmina_priest_fx', 'name' => 'face_x', 'label' => 'إزاحة أفقية', 'type' => 'number', 'wrapper' => array( 'width' => 33 ) ),
			array( 'key' => 'field_stmina_priest_fy', 'name' => 'face_y', 'label' => 'إزاحة رأسية', 'type' => 'number', 'wrapper' => array( 'width' => 34 ) ),
			array( 'key' => 'field_stmina_priest_note', 'name' => '', 'label' => 'ملصقات الأقوال', 'type' => 'message', 'message' => 'ارفع ملصقات أقوال الأب من زرار "Add Media" جوه الصفحة دي، أو من مكتبة الوسائط واختار "Attach" للأب ده. بتظهر تحت "من أقواله" بالترتيب.' ),
		),
	) );

	// ---------- محطة التاريخ ----------
	acf_add_local_field_group( array(
		'key'      => 'group_stmina_milestone',
		'title'    => 'السنة',
		'position' => 'acf_after_title',
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'stmina_milestone' ) ) ),
		'fields'   => array(
			array( 'key' => 'field_stmina_milestone_year', 'name' => 'year', 'label' => 'السنة', 'type' => 'text', 'instructions' => 'اكتب 0000 لو لسه مش معروفة. والنبذة في خانة "Excerpt"' ),
		),
	) );

	// ---------- واجهة الصفحات ----------
	acf_add_local_field_group( array(
		'key'      => 'group_stmina_page_hero',
		'title'    => 'واجهة الصفحة',
		'position' => 'side',
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ) ) ),
		'fields'   => array(
			array( 'key' => 'field_stmina_page_eyebrow', 'name' => 'hero_eyebrow', 'label' => 'الكلمة الصغيرة فوق العنوان', 'type' => 'text' ),
			array( 'key' => 'field_stmina_page_title', 'name' => 'hero_title', 'label' => 'عنوان الواجهة', 'type' => 'text', 'instructions' => 'لو فاضي بيظهر اسم الصفحة' ),
			array( 'key' => 'field_stmina_page_highlight', 'name' => 'hero_highlight', 'label' => 'الجزء الدهبي', 'type' => 'text', 'instructions' => 'آخر جزء في العنوان' ),
		),
	) );
} );

/**
 * قراية خانة خدمة. لو ACF مش شغال بنقرا القيمة من post meta مباشرة،
 * فالموقع مايقعش.
 */
function stmina_field( $name, $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	return function_exists( 'get_field' ) ? get_field( $name, $post->ID ) : get_post_meta( $post->ID, $name, true );
}

/**
 * قراية خانة مجموعة.
 */
function stmina_term_field( $name, $term ) {
	return function_exists( 'get_field' ) ? get_field( $name, $term ) : get_term_meta( $term->term_id, $name, true );
}

/**
 * لو ACF مش متفعّل، رسالة في اللوحة بدل ما الخانات تختفي من غير سبب.
 */
add_action( 'admin_notices', function () {
	if ( function_exists( 'acf_add_local_field_group' ) || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	echo '<div class="notice notice-error"><p>بلجن <b>St Mina Content</b> محتاج بلجن <b>Advanced Custom Fields</b> يكون متفعّل، علشان خانات الخدمات تظهر.</p></div>';
} );
