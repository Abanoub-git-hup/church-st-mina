<?php
/**
 * قالب St Mina: الإعدادات الأساسية، وتحميل الملفات، والدوال المساعدة.
 * الشكل المعتمد في design/ وdocs/design.md، والشرح في docs/wp-dev-guide.md.
 */

defined( 'ABSPATH' ) || exit;

define( 'STMINA_THEME_VERSION', '0.7.8' );

require_once get_template_directory() . '/inc/home-settings.php';
require_once get_template_directory() . '/inc/seo.php'; // وصف كل صفحة ومعاينة الرابط على واتساب

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'html5', array( 'style', 'script' ) );
} );

/**
 * تحميل الخطوط والتنسيق والسكريبتات.
 * GSAP من cdnjs زي التصميم، والسكريبتات في آخر الصفحة (آخر قيمة true).
 */
add_action( 'wp_enqueue_scripts', function () {
	$uri = get_template_directory_uri() . '/assets/';
	$ver = STMINA_THEME_VERSION;

	wp_enqueue_style( 'stmina-fonts', 'https://fonts.googleapis.com/css2?family=Alexandria:wght@200;300;400;500;600&family=Amiri:ital,wght@0,400;0,700;1,400&display=swap', array(), null );
	wp_enqueue_style( 'stmina-site', $uri . 'site.css', array( 'stmina-fonts' ), $ver );

	wp_enqueue_script( 'gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js', array(), null, true );
	wp_enqueue_script( 'gsap-scrolltrigger', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js', array( 'gsap' ), null, true );
	wp_enqueue_script( 'stmina-site', $uri . 'site.js', array( 'gsap-scrolltrigger' ), $ver, true );
	// site.js بيحوّل روابط القوايم المنسدلة لروابط WordPress من الرابط ده
	wp_add_inline_script( 'stmina-site', 'window.SITE_BASE = ' . wp_json_encode( home_url( '/' ) ) . ';', 'before' );

	if ( is_front_page() ) {
		wp_enqueue_style( 'stmina-home', $uri . 'home.css', array( 'stmina-site' ), $ver );
		wp_enqueue_script( 'stmina-home', $uri . 'home.js', array( 'stmina-site' ), $ver, true );
		// القداسات لدايرة "القداس القادم": اليوم، والساعة، والوقت مكتوب
		if ( function_exists( 'stmina_slots' ) ) {
			$masses = array_map( function ( $slot ) {
				return array( 'd' => $slot['day'], 'h' => (int) $slot['start'], 't' => stmina_slot_time( $slot, true ) );
			}, stmina_slots( 'mass' ) );
			wp_add_inline_script( 'stmina-home', 'window.STMINA_MASSES = ' . wp_json_encode( $masses ) . ';', 'before' );
		}
	}
	if ( is_page( 'worship' ) ) {
		wp_enqueue_style( 'stmina-worship', $uri . 'worship.css', array( 'stmina-site' ), $ver );
		wp_enqueue_script( 'stmina-worship', $uri . 'worship.js', array( 'stmina-site' ), $ver, true );
	}
	if ( is_page( 'news' ) ) {
		wp_enqueue_style( 'stmina-news', $uri . 'news.css', array( 'stmina-site' ), $ver );
		wp_enqueue_script( 'stmina-news', $uri . 'news.js', array( 'stmina-site' ), $ver, true );
	}
	if ( is_singular( 'stmina_news' ) ) {
		wp_enqueue_style( 'stmina-news-item', $uri . 'news-item.css', array( 'stmina-site' ), $ver );
	}
	if ( is_page( 'library' ) ) {
		wp_enqueue_style( 'stmina-library', $uri . 'library.css', array( 'stmina-site' ), $ver );
		wp_enqueue_script( 'stmina-library', $uri . 'library.js', array( 'stmina-site' ), $ver, true );
	}
	if ( is_singular( 'stmina_sermon' ) ) {
		wp_enqueue_style( 'stmina-sermon', $uri . 'sermon.css', array( 'stmina-site' ), $ver );
		wp_enqueue_script( 'stmina-sermon', $uri . 'sermon.js', array( 'stmina-site' ), $ver, true );
	}
	if ( is_post_type_archive( 'stmina_service' ) ) {
		wp_enqueue_style( 'stmina-services', $uri . 'services.css', array( 'stmina-site' ), $ver );
	}
	if ( is_singular( 'stmina_service' ) ) {
		wp_enqueue_style( 'stmina-service', $uri . 'service.css', array( 'stmina-site' ), $ver );
	}
	// صفحات الكنيسة: كل صفحة ليها ملف تنسيق باسم رابطها لو موجود
	foreach ( array( 'church-history', 'church-fathers', 'church-location' ) as $slug ) {
		if ( is_page( $slug ) ) {
			wp_enqueue_style( 'stmina-' . $slug, $uri . $slug . '.css', array( 'stmina-site' ), $ver );
		}
	}
} );

/**
 * واجهة الصفحة الداخلية من بيانات الصفحة نفسها: الصورة البارزة، والمقتطف،
 * وخانات "واجهة الصفحة" (الكلمة الصغيرة، والعنوان، والجزء الدهبي).
 *
 * @param string $current القسم الحالي في القايمة.
 * @param array  $parent  صفحة أعلى في مسار التنقل: array( 'الاسم', 'الرابط' ).
 */
function stmina_page_hero( $current, $parent = array() ) {
	$id     = get_queried_object_id();
	$crumbs = array( array( 'الرئيسية', home_url( '/' ) ) );
	if ( $parent ) {
		$crumbs[] = $parent;
	}
	$crumbs[] = array( get_the_title( $id ) );
	$title    = get_post_meta( $id, 'hero_title', true );

	get_template_part( 'template-parts/page-hero', null, array(
		'current'   => $current,
		'image'     => get_the_post_thumbnail_url( $id, 'full' ),
		'crumbs'    => $crumbs,
		'eyebrow'   => get_post_meta( $id, 'hero_eyebrow', true ),
		'title'     => $title ? $title : get_the_title( $id ),
		'highlight' => get_post_meta( $id, 'hero_highlight', true ),
		'lead'      => has_excerpt( $id ) ? get_the_excerpt( $id ) : '',
	) );
}

/**
 * عنوان بجزء دهبي في آخره، زي "خدمة <b>لكل عمر</b>". النص كله متأمّن.
 *
 * @param string $title     العنوان كامل.
 * @param string $highlight الجزء الدهبي، ولازم يكون آخر العنوان.
 */
function stmina_title( $title, $highlight = '' ) {
	$highlight = trim( (string) $highlight );
	if ( $highlight && str_ends_with( $title, $highlight ) && $highlight !== $title ) {
		$start = trim( substr( $title, 0, -strlen( $highlight ) ) );
		echo esc_html( $start ) . ' <b>' . esc_html( $highlight ) . '</b>';
		return;
	}
	echo esc_html( $title );
}

/**
 * رقم موبايل مصري بالشكل الدولي: 01552921322 ← +201552921322
 */
function stmina_phone_intl( $phone ) {
	$digits = preg_replace( '/\D/', '', (string) $phone );
	return '+2' . ltrim( $digits, '2' );
}

/**
 * رقم موبايل للعرض: 01552921322 ← 0155 292 1322
 */
function stmina_phone_display( $phone ) {
	$d = preg_replace( '/\D/', '', (string) $phone );
	return 11 === strlen( $d ) ? substr( $d, 0, 4 ) . ' ' . substr( $d, 4, 3 ) . ' ' . substr( $d, 7 ) : $phone;
}

/**
 * رابط صورة من assets/media جوه القالب، جاهز للطباعة.
 *
 * @param string $path المسار جوه assets/media، زي church/hero-prayer.png
 */
function stmina_media( $path ) {
	echo esc_url( get_template_directory_uri() . '/assets/media/' . $path );
}

/**
 * رابط صفحة في الموقع من اسمها في التصميم، جاهز للطباعة.
 * worship.html في التصميم بتبقى /worship/ هنا. الصفحات نفسها بتتعمل في المهام من 3 لـ 7.
 *
 * @param string $page اسم الصفحة، زي worship
 * @param string $hash جزء بعد # في الرابط، زي #schedule
 */
function stmina_link( $page, $hash = '' ) {
	echo esc_url( home_url( '/' . $page . '/' ) . $hash );
}
