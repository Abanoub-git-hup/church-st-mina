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

	// ---------- موعد في الجدول ----------
	acf_add_local_field_group( array(
		'key'      => 'group_stmina_slot',
		'title'    => 'الموعد',
		'position' => 'acf_after_title',
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'stmina_slot' ) ) ),
		'fields'   => array(
			array( 'key' => 'field_stmina_slot_kind', 'name' => 'kind', 'label' => 'النوع', 'type' => 'select', 'choices' => array( 'mass' => 'قداس', 'meeting' => 'اجتماع ثابت' ), 'default_value' => 'mass', 'wrapper' => array( 'width' => 25 ) ),
			array( 'key' => 'field_stmina_slot_day', 'name' => 'day', 'label' => 'اليوم', 'type' => 'select', 'choices' => stmina_days(), 'wrapper' => array( 'width' => 25 ) ),
			array( 'key' => 'field_stmina_slot_start', 'name' => 'start', 'label' => 'من الساعة', 'type' => 'time_picker', 'display_format' => 'g:i a', 'return_format' => 'H:i:s', 'wrapper' => array( 'width' => 25 ) ),
			array( 'key' => 'field_stmina_slot_end', 'name' => 'end', 'label' => 'لحد الساعة', 'type' => 'time_picker', 'display_format' => 'g:i a', 'return_format' => 'H:i:s', 'instructions' => 'سيبها فاضية لو مالوش نهاية محددة', 'wrapper' => array( 'width' => 25 ) ),
			array( 'key' => 'field_stmina_slot_note', 'name' => 'note', 'label' => 'ملاحظة', 'type' => 'text', 'instructions' => 'بتظهر جنب الوقت. مثال: التربية الكنسية، أو اجتماع الشباب · الكنيسة الكبيرة، الروف' ),
		),
	) );

	// ---------- العظة ----------
	acf_add_local_field_group( array(
		'key'      => 'group_stmina_sermon',
		'title'    => 'بيانات العظة',
		'position' => 'acf_after_title',
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'stmina_sermon' ) ) ),
		'fields'   => array(
			array(
				'key' => 'field_stmina_sermon_speaker', 'name' => 'speaker', 'label' => 'المتحدث', 'type' => 'post_object',
				'post_type' => array( 'stmina_priest' ), 'return_format' => 'id', 'allow_null' => 1, 'ui' => 1,
				'instructions' => 'اختار أب من الآباء الكهنة. لو المتحدث ضيف سيبها فاضية واكتب اسمه في الخانة اللي جنبها',
				'wrapper' => array( 'width' => 50 ),
			),
			array( 'key' => 'field_stmina_sermon_guest', 'name' => 'guest_name', 'label' => 'اسم المتحدث الضيف', 'type' => 'text', 'instructions' => 'مثال: نيافة الأنبا ...', 'wrapper' => array( 'width' => 50 ) ),
			array( 'key' => 'field_stmina_sermon_note', 'name' => '', 'label' => 'تاريخ العظة', 'type' => 'message', 'message' => 'تاريخ العظة هو تاريخ النشر. غيّره من خانة "Publish" في الجنب. والموضوع من خانة "المواضيع".' ),
			array( 'key' => 'field_stmina_sermon_video', 'name' => 'video_url', 'label' => 'رابط الفيديو', 'type' => 'url', 'instructions' => 'رابط YouTube أو Facebook. لو موجود، العظة بتبقى فيديو' ),
			array( 'key' => 'field_stmina_sermon_audio', 'name' => 'audio', 'label' => 'ملف الصوت', 'type' => 'file', 'return_format' => 'id', 'mime_types' => 'mp3,m4a,ogg,wav', 'instructions' => 'لو مفيش فيديو، العظة بتبقى صوتية', 'wrapper' => array( 'width' => 50 ) ),
			array( 'key' => 'field_stmina_sermon_pdf', 'name' => 'pdf', 'label' => 'ملف PDF', 'type' => 'file', 'return_format' => 'id', 'mime_types' => 'pdf', 'instructions' => 'لو مفيش فيديو ولا صوت، العظة بتبقى ملف للقراية', 'wrapper' => array( 'width' => 50 ) ),
			array( 'key' => 'field_stmina_sermon_verse', 'name' => 'verse', 'label' => 'آية العظة', 'type' => 'textarea', 'rows' => 2, 'wrapper' => array( 'width' => 70 ) ),
			array( 'key' => 'field_stmina_sermon_verse_ref', 'name' => 'verse_ref', 'label' => 'الشاهد', 'type' => 'text', 'instructions' => 'مثال: يوحنا 3: 16', 'wrapper' => array( 'width' => 30 ) ),
		),
	) );

	// ---------- النشرة ----------
	acf_add_local_field_group( array(
		'key'      => 'group_stmina_bulletin',
		'title'    => 'ملف النشرة',
		'position' => 'acf_after_title',
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'stmina_bulletin' ) ) ),
		'fields'   => array(
			array( 'key' => 'field_stmina_bulletin_pdf', 'name' => 'pdf', 'label' => 'ملف PDF', 'type' => 'file', 'return_format' => 'id', 'mime_types' => 'pdf', 'required' => 1, 'wrapper' => array( 'width' => 60 ) ),
			array( 'key' => 'field_stmina_bulletin_issue', 'name' => 'issue', 'label' => 'رقم العدد', 'type' => 'number', 'wrapper' => array( 'width' => 40 ) ),
			array( 'key' => 'field_stmina_bulletin_note', 'name' => '', 'label' => 'ملاحظات', 'type' => 'message', 'message' => 'شهر النشرة هو تاريخ النشر. وعنوان المقال الرئيسي في خانة "Excerpt". ولو مفيش صورة غلاف (Featured image) بيظهر غلاف بالشعار.' ),
		),
	) );

	// ---------- الترنيمة ----------
	acf_add_local_field_group( array(
		'key'      => 'group_stmina_hymn',
		'title'    => 'الترنيمة',
		'position' => 'acf_after_title',
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'stmina_hymn' ) ) ),
		'fields'   => array(
			array( 'key' => 'field_stmina_hymn_audio', 'name' => 'audio', 'label' => 'ملف الصوت', 'type' => 'file', 'return_format' => 'id', 'mime_types' => 'mp3,m4a,ogg,wav', 'required' => 1, 'wrapper' => array( 'width' => 50 ) ),
			array( 'key' => 'field_stmina_hymn_team', 'name' => 'team', 'label' => 'الفريق', 'type' => 'text', 'instructions' => 'مثال: كورال ترينتي', 'wrapper' => array( 'width' => 50 ) ),
			array( 'key' => 'field_stmina_hymn_lyrics', 'name' => 'lyrics', 'label' => 'الكلمات', 'type' => 'textarea', 'rows' => 8, 'new_lines' => '' ),
		),
	) );

	// ---------- الخبر ----------
	$season_only = array( array( array( 'field' => 'field_stmina_news_season', 'operator' => '==', 'value' => '1' ) ) );
	acf_add_local_field_group( array(
		'key'      => 'group_stmina_news',
		'title'    => 'بيانات الخبر',
		'position' => 'acf_after_title',
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'stmina_news' ) ) ),
		'fields'   => array(
			array( 'key' => 'field_stmina_news_kind', 'name' => 'kind', 'label' => 'النوع', 'type' => 'select', 'choices' => array( 'ann' => 'إعلان', 'news' => 'خبر' ), 'default_value' => 'ann', 'instructions' => 'الإعلان عن حاجة جاية، والخبر عن حاجة حصلت', 'wrapper' => array( 'width' => 25 ) ),
			array( 'key' => 'field_stmina_news_when', 'name' => 'when_label', 'label' => 'الميعاد المكتوب', 'type' => 'text', 'instructions' => 'بيظهر على الكارت. مثال: 7 – 22 أغسطس، أو يبدأ 14 يونيو. لو فاضي بيظهر تاريخ النشر', 'wrapper' => array( 'width' => 40 ) ),
			array( 'key' => 'field_stmina_news_place', 'name' => 'place', 'label' => 'المكان', 'type' => 'text', 'instructions' => 'مثال: الكنيسة الكبيرة', 'wrapper' => array( 'width' => 35 ) ),
			array( 'key' => 'field_stmina_news_highlight', 'name' => 'highlight', 'label' => 'الجزء الدهبي من العنوان', 'type' => 'text', 'instructions' => 'لازم يكون آخر جزء في العنوان. لو فاضي بتبقى آخر كلمة', 'wrapper' => array( 'width' => 40 ) ),
			array( 'key' => 'field_stmina_news_poster', 'name' => 'is_poster', 'label' => 'الصورة ملصق', 'type' => 'true_false', 'ui' => 1, 'instructions' => 'الملصق بيبان من فوق ومن غير فلتر الألوان', 'wrapper' => array( 'width' => 20 ) ),
			array( 'key' => 'field_stmina_news_pinned', 'name' => 'pinned', 'label' => 'مثبّت فوق', 'type' => 'true_false', 'ui' => 1, 'instructions' => 'بيظهر في الدواير اللي فوق (أول 3 بس)', 'wrapper' => array( 'width' => 20 ) ),
			array( 'key' => 'field_stmina_news_season', 'name' => 'is_season', 'label' => 'مناسبة موسمية', 'type' => 'true_false', 'ui' => 1, 'instructions' => 'الميلاد، أو أسبوع الآلام، أو القيامة. بتظهر خانات زيادة', 'wrapper' => array( 'width' => 20 ) ),
			array( 'key' => 'field_stmina_news_note', 'name' => '', 'label' => 'الصورة والنبذة', 'type' => 'message', 'message' => 'الصورة من "Featured image"، والنبذة القصيرة اللي على الكارت من "Excerpt"، والتفاصيل في المحرر.' ),
			// خانات المناسبة بس
			array( 'key' => 'field_stmina_news_event_at', 'name' => 'event_at', 'label' => 'ميعاد المناسبة', 'type' => 'date_time_picker', 'display_format' => 'j F Y g:i a', 'return_format' => 'Y-m-d H:i:s', 'instructions' => 'العد التنازلي بيعدّ لحد الميعاد ده، وبعده المناسبة بتختفي من الرئيسية', 'conditional_logic' => $season_only, 'wrapper' => array( 'width' => 35 ) ),
			array( 'key' => 'field_stmina_news_time_note', 'name' => 'time_note', 'label' => 'سطر الوقت', 'type' => 'text', 'instructions' => 'مثال: القداس 10 مساءً، أو موعد القداس يُعلن قريبًا', 'conditional_logic' => $season_only, 'wrapper' => array( 'width' => 65 ) ),
			array( 'key' => 'field_stmina_news_verse_label', 'name' => 'verse_label', 'label' => 'الكلمة فوق الآية', 'type' => 'text', 'instructions' => 'مثال: بشارة الميلاد', 'conditional_logic' => $season_only, 'wrapper' => array( 'width' => 30 ) ),
			array( 'key' => 'field_stmina_news_verse', 'name' => 'verse', 'label' => 'الآية', 'type' => 'textarea', 'rows' => 2, 'conditional_logic' => $season_only, 'wrapper' => array( 'width' => 50 ) ),
			array( 'key' => 'field_stmina_news_verse_ref', 'name' => 'verse_ref', 'label' => 'الشاهد', 'type' => 'text', 'conditional_logic' => $season_only, 'wrapper' => array( 'width' => 20 ) ),
			array( 'key' => 'field_stmina_news_rows_title', 'name' => 'rows_title', 'label' => 'عنوان المواعيد', 'type' => 'text', 'instructions' => 'مثال: من صوم الميلاد إلى العيد', 'conditional_logic' => $season_only, 'wrapper' => array( 'width' => 50 ) ),
			array( 'key' => 'field_stmina_news_rows_highlight', 'name' => 'rows_highlight', 'label' => 'الجزء الدهبي منه', 'type' => 'text', 'instructions' => 'آخر جزء في العنوان. مثال: إلى العيد', 'conditional_logic' => $season_only, 'wrapper' => array( 'width' => 50 ) ),
			array( 'key' => 'field_stmina_news_rows', 'name' => 'season_rows', 'label' => 'مواعيد الموسم', 'type' => 'textarea', 'rows' => 5, 'new_lines' => '', 'instructions' => 'كل ميعاد في سطر: الاسم | الميعاد | ملاحظة. مثال: صوم الميلاد | يبدأ 25 نوفمبر | 43 يومًا', 'conditional_logic' => $season_only ),
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
