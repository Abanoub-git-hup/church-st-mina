<?php
/**
 * Plugin Name: St Mina Content
 * Plugin URI: https://github.com/Abanoub-git-hup/church-st-mina
 * Description: أنواع محتوى الموقع العام: الخدمات، وصفحات الكنيسة، وجدول المواعيد، والمكتبة (وبعدين الأخبار). خاناتها بـ ACF ومتعرّفة في الكود.
 * Version: 0.4.0
 * Author: Abanoub S. Maurice
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Requires Plugins: advanced-custom-fields
 * Text Domain: st-mina-content
 */

defined( 'ABSPATH' ) || exit;

define( 'STMINA_CONTENT_VERSION', '0.4.0' );
define( 'STMINA_CONTENT_DIR', plugin_dir_path( __FILE__ ) );

require_once STMINA_CONTENT_DIR . 'inc/services.php';
require_once STMINA_CONTENT_DIR . 'inc/schedule.php';
require_once STMINA_CONTENT_DIR . 'inc/library.php';
require_once STMINA_CONTENT_DIR . 'inc/fields.php';
require_once STMINA_CONTENT_DIR . 'inc/seed.php';
require_once STMINA_CONTENT_DIR . 'inc/church.php';
require_once STMINA_CONTENT_DIR . 'inc/seed-church.php';
require_once STMINA_CONTENT_DIR . 'inc/seed-worship.php';
require_once STMINA_CONTENT_DIR . 'inc/seed-library.php';

/**
 * عند التفعيل: نسجّل نوع المحتوى، وندخل الخدمات لو لسه ماتدخلتش، ونحدّث الروابط.
 * flush_rewrite_rules علشان روابط /services/ و /service/... تشتغل من أول مرة.
 */
register_activation_hook( __FILE__, function () {
	stmina_register_services();
	stmina_seed_services();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
