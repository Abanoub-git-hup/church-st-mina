<?php
/**
 * وصف كل صفحة ومعاينة الرابط (مراجعة الـ QA، الملاحظة 3).
 *
 * - meta description: اللي بيظهر تحت العنوان في جوجل.
 * - وسوم Open Graph (og:): اللي واتساب وفيسبوك بيعملوا منها معاينة الرابط (العنوان والوصف والصورة).
 *
 * الوصف من مكانين:
 * - صفحات الكنيسة الثابتة: نفس الوصف المعتمد في نماذج التصميم (design/*.html)، بالظبط.
 * - الخدمة والخبر والعظة: أول كلام في المحتوى نفسه، وإلا جملة بنفس صيغة التصميم بالعنوان.
 */

defined( 'ABSPATH' ) || exit;

const STMINA_CHURCH = 'كنيسة السيدة العذراء ومارمينا والبابا كيرلس السادس';

/**
 * الوصف المعتمد للصفحات الثابتة، من نماذج التصميم.
 */
function stmina_fixed_descriptions() {
	$c     = STMINA_CHURCH;
	return array(
		'home'             => "{$c}، عرب العيايدة، ناحية الجبل الأصفر، مطرانية شبين القناطر وتوابعها.",
		'church-history'   => "نشأة وتاريخ {$c}، عرب العيايدة، ناحية الجبل الأصفر، مطرانية شبين القناطر وتوابعها.",
		'church-fathers'   => "آباء {$c} بالجبل الأصفر: القمص كيرلس روماني، والقس أرسانيوس عزت، والقس فام عبد المسيح.",
		'church-gallery'   => "صور من {$c} بالجبل الأصفر: العبادة، ومدارس الأحد، والشباب، والفرق والأنشطة.",
		'church-location'  => "موقع {$c}: عرب العيايدة، ناحية الجبل الأصفر، مطرانية شبين القناطر وتوابعها.",
		'worship'          => "مواعيد القداسات والاجتماعات في {$c} بالجبل الأصفر، والقراءات اليومية، وسر الاعتراف.",
		'library'          => "مكتبة {$c} بالجبل الأصفر: العظات، والنشرات الشهرية، والترانيم.",
		'news'             => "أخبار وإعلانات {$c} بالجبل الأصفر، والمناسبات الموسمية.",
		'services'         => "خدمات {$c} بالجبل الأصفر: مدارس الأحد، والاجتماعات، والخدام، والفرق والأنشطة، والحضانة، والأسرة والمجتمع.",
		'404'              => "الصفحة التي تبحث عنها غير موجودة في موقع {$c}.",
	);
}

/**
 * أول كلام في محتوى الموضوع، من غير وسوم، في حدود 30 كلمة. أو '' لو فاضي.
 */
function stmina_excerpt_text( $post ) {
	$text = has_excerpt( $post ) ? $post->post_excerpt : $post->post_content;
	$text = wp_strip_all_tags( strip_shortcodes( $text ), true );
	return $text ? wp_trim_words( $text, 30, '…' ) : '';
}

/**
 * الوصف والصورة للصفحة الحالية.
 *
 * @return array description، وimage (رابط)، وtype (website أو article).
 */
function stmina_page_meta() {
	$d     = stmina_fixed_descriptions();
	$c     = STMINA_CHURCH;
	$image = get_template_directory_uri() . '/assets/media/church/nave-real.jpeg';
	$type  = 'website';
	$desc  = $d['home'];

	if ( is_404() ) {
		$desc = $d['404'];
	} elseif ( is_post_type_archive( 'stmina_service' ) ) {
		$desc = $d['services'];
	} elseif ( is_page() ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		$desc = isset( $d[ $slug ] ) ? $d[ $slug ] : $d['home'];
	} elseif ( is_singular( array( 'stmina_service', 'stmina_news', 'stmina_sermon' ) ) ) {
		$post  = get_queried_object();
		$title = get_the_title( $post );
		$type  = 'article';
		$own   = stmina_excerpt_text( $post );
		$fall  = array(
			'stmina_service' => "$title في {$c} بالجبل الأصفر: الموعد والمكان والمسؤول.",
			'stmina_news'    => "$title في {$c} بالجبل الأصفر.",
			'stmina_sermon'  => "{$title}: عظة من مكتبة {$c} بالجبل الأصفر.",
		);
		$desc = $own ? $own : $fall[ $post->post_type ];
		if ( has_post_thumbnail( $post ) ) {
			$image = get_the_post_thumbnail_url( $post, 'large' );
		}
	}
	return array( 'description' => $desc, 'image' => $image, 'type' => $type );
}

/**
 * الوسوم في head كل صفحة. الموقع مش بيستخدم plugin للـ SEO، فده المكان الوحيد اللي بيطلّعها.
 */
add_action( 'wp_head', function () {
	$m     = stmina_page_meta();
	$title = wp_get_document_title();
	$url   = is_404() ? home_url( '/' ) : home_url( add_query_arg( array() ) );
	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $m['description'] ) );
	printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $m['type'] ) );
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( STMINA_CHURCH ) );
	printf( '<meta property="og:locale" content="ar_AR">' . "\n" );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $m['description'] ) );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
	printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $m['image'] ) );
	printf( '<meta name="twitter:card" content="summary_large_image">' . "\n" );
}, 2 );

