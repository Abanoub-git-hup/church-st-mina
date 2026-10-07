<?php
/**
 * Plugin Name: St Mina Content
 * Plugin URI: https://github.com/Abanoub-git-hup/church-st-mina
 * Description: أنواع محتوى الموقع العام: الخدمات، وصفحات الكنيسة، وجدول المواعيد، والمكتبة، والأخبار. خاناتها بـ ACF ومتعرّفة في الكود.
 * Version: 0.5.2
 * Author: Abanoub S. Maurice
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Requires Plugins: advanced-custom-fields
 * Text Domain: st-mina-content
 */

defined( 'ABSPATH' ) || exit;

define( 'STMINA_CONTENT_VERSION', '0.5.2' );
define( 'STMINA_CONTENT_DIR', plugin_dir_path( __FILE__ ) );

require_once STMINA_CONTENT_DIR . 'inc/services.php';
require_once STMINA_CONTENT_DIR . 'inc/schedule.php';
require_once STMINA_CONTENT_DIR . 'inc/library.php';
require_once STMINA_CONTENT_DIR . 'inc/news.php';
require_once STMINA_CONTENT_DIR . 'inc/fields.php';
require_once STMINA_CONTENT_DIR . 'inc/seed.php';
require_once STMINA_CONTENT_DIR . 'inc/church.php';
require_once STMINA_CONTENT_DIR . 'inc/seed-church.php';
require_once STMINA_CONTENT_DIR . 'inc/seed-worship.php';
require_once STMINA_CONTENT_DIR . 'inc/seed-library.php';
require_once STMINA_CONTENT_DIR . 'inc/seed-news.php';

/**
 * عند التفعيل: ندخل الخدمات لو لسه ماتدخلتش، ونعلّم إن الروابط محتاجة تتحدّث.
 * التحديث نفسه مابيحصلش هنا، لأن وقت التفعيل أنواع المحتوى اللي متسجّلة على init (الأخبار والعظات)
 * لسه ماتسجّلتش، فـ flush_rewrite_rules هنا كانت بتمسح روابطهم وتطلّع 404.
 */
register_activation_hook( __FILE__, function () {
	stmina_register_services();
	stmina_seed_services();
	update_option( 'stmina_flush_rewrite', 1 );
} );

/**
 * أول طلب بعد التفعيل: كل الأنواع اتسجّلت على init، فنحدّث الروابط مرة واحدة.
 * الأولوية 99 علشان تشتغل بعد كل تسجيل على init.
 */
add_action( 'init', function () {
	if ( get_option( 'stmina_flush_rewrite' ) ) {
		delete_option( 'stmina_flush_rewrite' );
		flush_rewrite_rules();
	}
}, 99 );

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
