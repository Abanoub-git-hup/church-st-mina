<?php
/**
 * إدخال المجموعات الست والخدمات الـ 17 مرة واحدة، من بيانات التصميم المعتمد.
 * بيشتغل عند تفعيل الـ plugin، ومابيتكررش (الإعداد stmina_content_seeded).
 * الصور بتتنسخ من فولدر الـ theme لمكتبة الوسائط، فمحرر المحتوى يقدر يغيّرها من اللوحة.
 */

defined( 'ABSPATH' ) || exit;

function stmina_seed_data() {
	$groups = array(
		// slug => الاسم، والجزء الدهبي، والكلمة الصغيرة، والوصف، والصورة، ولوجو، والترتيب
		'sunday'   => array( 'مدارس الأحد', 'الأحد', '3 مراحل', 'تربية كنسية للأطفال والفتيان في ثلاث مراحل: الابتدائية والإعدادية والثانوية.', 'services/sunday-school/primary-conference-2026-1.jpg', false, 1 ),
		'meet'     => array( 'الاجتماعات', '', '5 اجتماعات', 'اجتماعات أسبوعية للشباب والشعب والسيدات، وللصلاة ودراسة الكتاب المقدس، ولتعلّم الألحان.', 'services/youth/youth-meeting-2026.jpeg', false, 2 ),
		'servants' => array( 'الخدام', '', 'اجتماعان', 'اجتماع أسبوعي لخدام الكنيسة، وإعداد الخدام الجدد للخدمة.', 'services/counseling/counseling-course-2026-1.jpeg', false, 3 ),
		'teams'    => array( 'الفرق والأنشطة', 'والأنشطة', '4 فرق', 'فريق المسرح، والكورال، والكشافة، والأوبريت الجديد.', 'services/theater/theater-ismuh-yasou.jpg', false, 4 ),
		'nursery'  => array( 'الحضانة', '', 'للأطفال الصغار', 'خدمة الأطفال الصغار في الكنيسة.', 'services/sunday-school/sunday-school-art.jpg', true, 5 ),
		'family'   => array( 'الأسرة والمجتمع', 'والمجتمع', 'خدمتان جديدتان', 'خدمات جديدة للأسرة والمجتمع: كورس المشورة للمقبلين على الزواج، وعيادات البتول التخصصية.', 'services/counseling/counseling-course-2026-2.jpeg', false, 6 ),
	);

	// الاسم، والمجموعة، والصورة، وشكل الصورة، ومؤقتة، وجديدة، وخانات إضافية
	$services = array(
		array( 'المرحلة الابتدائية', 'sunday', 'services/sunday-school/primary-conference-2026-1.jpg', 'cover', false, false ),
		array( 'المرحلة الإعدادية', 'sunday', 'services/sunday-school/primary-conference-2026-3.jpg', 'cover', true, false ),
		array( 'المرحلة الثانوية', 'sunday', 'services/sunday-school/secondary-meeting.jpg', 'cover', false, false ),
		array( 'اجتماع الشباب', 'meet', 'services/youth/youth-meeting-2026.jpeg', 'cover', false, false, array(
			'schedule_short' => 'الجمعة 6 م',
			'schedule'       => 'كل جمعة 6:00 مساءً',
			'place'          => 'الكنيسة الكبيرة، الروف',
			'priest_name'    => 'أبونا أرسانيوس عزت',
			'priest_phone'   => '01552921322',
			'servant_name'   => 'أ. بيشوي القس',
			'servant_phone'  => '01273144313',
		) ),
		array( 'صلاة ودراسة الكتاب المقدس', 'meet', 'services/bible-study/prayer-bible-study-poster.jpg', 'top', false, false, array(
			'schedule_short' => 'الجمعة 3–5 م',
			'schedule'       => 'كل جمعة 3:00 – 5:00 مساءً',
		) ),
		array( 'اجتماع الألحان', 'meet', 'worship/deacons.jpeg', 'cover', false, false ),
		array( 'اجتماع الشعب', 'meet', 'church/nave-real.jpeg', 'cover', true, false ),
		array( 'اجتماع السيدات', 'meet', 'worship/youth-night-liturgy-3.jpeg', 'cover', true, false ),
		array( 'اجتماع الخدام', 'servants', 'services/counseling/counseling-course-2026-2.jpeg', 'cover', true, false ),
		array( 'إعداد الخدام', 'servants', 'church/bg-stained-window.webp', 'cover', true, false ),
		array( 'مسرح الأنبا موسى الأسود', 'teams', 'services/theater/theater-ismuh-yasou.jpg', 'cover', false, false ),
		array( 'كورال ترينتي', 'teams', 'services/choir/trinity-choir.jpg', 'cover', false, false ),
		array( 'فريق الكشافة', 'teams', 'services/scouts/scouts.jpg', 'cover', false, false ),
		array( 'أوبريت الأنبا بيشوي والأنبا كاراس', 'teams', 'services/theater/theater-stage.jpg', 'cover', true, true ),
		array( 'حضانة الكنيسة', 'nursery', 'services/sunday-school/sunday-school-art.jpg', 'paper', false, false ),
		array( 'كورس المشورة للمقبلين على الزواج', 'family', 'services/counseling/counseling-course-2026-1.jpeg', 'cover', false, true ),
		array( 'عيادات البتول التخصصية', 'family', 'services/clinics/batool-clinics-logo.jpg', 'paper', false, true ),
	);

	return array( $groups, $services );
}

function stmina_seed_services() {
	if ( get_option( 'stmina_content_seeded' ) ) {
		return;
	}
	list( $groups, $services ) = stmina_seed_data();
	$term_ids = array();

	foreach ( $groups as $slug => $g ) {
		$found = term_exists( $slug, 'stmina_service_group' );
		$term  = $found ? $found : wp_insert_term( $g[0], 'stmina_service_group', array( 'slug' => $slug, 'description' => $g[3] ) );
		if ( is_wp_error( $term ) ) {
			continue;
		}
		$tid               = (int) $term['term_id'];
		$term_ids[ $slug ] = $tid;
		$ref               = 'stmina_service_group_' . $tid; // طريقة ACF في حفظ خانات التصنيفات
		stmina_seed_meta( 'term', $tid, 'label', $g[2], 'field_stmina_group_label', $ref );
		stmina_seed_meta( 'term', $tid, 'highlight', $g[1], 'field_stmina_group_highlight', $ref );
		stmina_seed_meta( 'term', $tid, 'image', stmina_seed_image( $g[4] ), 'field_stmina_group_image', $ref );
		stmina_seed_meta( 'term', $tid, 'paper', $g[5] ? 1 : 0, 'field_stmina_group_paper', $ref );
		stmina_seed_meta( 'term', $tid, 'order', $g[6], 'field_stmina_group_order', $ref );
	}

	foreach ( $services as $i => $s ) {
		$id = wp_insert_post( array(
			'post_type'   => 'stmina_service',
			'post_status' => 'publish',
			'post_title'  => $s[0],
			'menu_order'  => $i + 1,
		) );
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		if ( isset( $term_ids[ $s[1] ] ) ) {
			wp_set_object_terms( $id, array( $term_ids[ $s[1] ] ), 'stmina_service_group' );
		}
		$img = stmina_seed_image( $s[2] );
		if ( $img ) {
			set_post_thumbnail( $id, $img );
		}
		$fields = array_merge( array(
			'image_fit'  => $s[3],
			'temp_image' => $s[4] ? 1 : 0,
			'is_new'     => $s[5] ? 1 : 0,
		), isset( $s[6] ) ? $s[6] : array() );
		foreach ( $fields as $name => $value ) {
			stmina_seed_meta( 'post', $id, $name, $value, 'field_stmina_' . $name, $id );
		}
	}

	update_option( 'stmina_content_seeded', STMINA_CONTENT_VERSION );
}

/**
 * حفظ قيمة خانة بنفس الشكل اللي ACF بيحفظ بيه: القيمة نفسها، وسطر تاني بيربطها بالخانة (_name => field key).
 * ACF محتاج السطر التاني علشان يعرف نوع الخانة وهو بيقرا.
 * مابنستخدمش update_field() هنا، لأن وقت تفعيل الـ plugin خانات ACF بتاعتنا لسه مااتسجّلتش.
 */
function stmina_seed_meta( $type, $id, $name, $value, $key, $acf_ref ) {
	$fn = 'term' === $type ? 'update_term_meta' : 'update_post_meta';
	$fn( $id, $name, $value );
	$fn( $id, '_' . $name, $key );
}

/**
 * نسخ صورة من فولدر الـ theme لمكتبة الوسائط. الصورة الواحدة بتتنسخ مرة واحدة بس:
 * لو اتنسخت قبل كده (حتى في مرة إدخال تانية) بنرجّع نفس الصورة.
 *
 * @param string $path    المسار جوه assets/media في الـ theme.
 * @param int    $parent  المحتوى اللي الصورة "مرفوعة جواه" (attached). الملصقات جوه الأب، وصور الألبوم جوه الألبوم.
 * @param string $caption تعليق الصورة.
 * @return int رقم الصورة في المكتبة، أو 0.
 */
function stmina_seed_image( $path, $parent = 0, $caption = '' ) {
	static $done = array();
	if ( ! isset( $done[ $path ] ) ) {
		$done[ $path ] = stmina_seed_find_image( $path ) ?: stmina_seed_upload_image( $path );
	}
	$id = $done[ $path ];
	if ( $id && ( $parent || $caption ) ) {
		$update = array( 'ID' => $id );
		if ( $parent && ! wp_get_post_parent_id( $id ) ) {
			$update['post_parent'] = $parent;
		}
		if ( $caption ) {
			$update['post_excerpt'] = $caption; // تعليق الصورة في المكتبة
		}
		wp_update_post( $update );
	}
	return $id;
}

/**
 * صورة اتنسخت قبل كده من نفس الملف (بنقارن باسم الملف المحفوظ).
 */
function stmina_seed_find_image( $path ) {
	$found = get_posts( array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => array( array(
			'key'     => '_wp_attached_file',
			'value'   => '/' . basename( $path ),
			'compare' => 'LIKE',
		) ),
	) );
	return $found ? (int) $found[0] : 0;
}

function stmina_seed_upload_image( $path ) {
	$file = get_template_directory() . '/assets/media/' . $path;
	if ( ! file_exists( $file ) ) {
		return 0;
	}
	$upload = wp_upload_bits( basename( $file ), null, file_get_contents( $file ) );
	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}
	$type = wp_check_filetype( $upload['file'] );
	$id   = wp_insert_attachment( array(
		'post_mime_type' => $type['type'],
		'post_title'     => pathinfo( $file, PATHINFO_FILENAME ),
		'post_status'    => 'inherit',
	), $upload['file'] );
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
	return $id;
}
