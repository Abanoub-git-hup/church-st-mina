<?php
/**
 * قالب St Mina: الإعدادات الأساسية، وتحميل الملفات، والدوال المساعدة.
 * الشكل المعتمد في design/ وdocs/design.md، والشرح في docs/wp-dev-guide.md.
 */

defined( 'ABSPATH' ) || exit;

define( 'STMINA_THEME_VERSION', '0.2.0' );

require_once get_template_directory() . '/inc/home-settings.php';

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
	}
} );

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
