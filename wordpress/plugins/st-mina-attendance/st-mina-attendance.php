<?php
/**
 * Plugin Name: St Mina Attendance
 * Plugin URI: https://github.com/Abanoub-git-hup/church-st-mina
 * Description: نظام الحضور بالـ QR لخدمة إعداد الخدام. الشاشات المعتمدة في design/attend-*.html، وقواعد الحساب في docs/design.md القسم 5.
 * Version: 0.10.3
 * Author: Abanoub S. Maurice
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Text Domain: st-mina-attendance
 */

defined( 'ABSPATH' ) || exit;

define( 'STMINA_ATT_VERSION', '0.10.3' );
define( 'STMINA_ATT_FILE', __FILE__ );

define( 'STMINA_ATT_DIR', plugin_dir_path( __FILE__ ) );
define( 'STMINA_ATT_URL', plugin_dir_url( __FILE__ ) );

require_once STMINA_ATT_DIR . 'inc/schema.php';  // الجداول والصلاحيات
require_once STMINA_ATT_DIR . 'inc/members.php'; // المخدومين والملاحظات
require_once STMINA_ATT_DIR . 'inc/rest.php';    // مسارات الخدام
require_once STMINA_ATT_DIR . 'inc/sessions.php'; // الجلسات
require_once STMINA_ATT_DIR . 'inc/records.php';  // المسح وسجل الحضور
require_once STMINA_ATT_DIR . 'inc/stats.php';    // النسب و"حضوري"
require_once STMINA_ATT_DIR . 'inc/import.php';   // الاستيراد من Excel والكارت اتبعت
require_once STMINA_ATT_DIR . 'inc/app.php';     // شاشات /attend/ والدخول

// التفعيل: الجداول والصلاحيات. والتحديثات من غير تفعيل بتتعمل على init (schema.php)
register_activation_hook( __FILE__, function () {
	stmina_att_install();
	update_option( 'stmina_att_version', STMINA_ATT_VERSION );
} );

// نقطة فحص بسيطة: /wp-json/stmina/v1/health بترجع إن البلجن شغال ونسخته
add_action( 'rest_api_init', function () {
	register_rest_route( 'stmina/v1', '/health', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => function () {
			return array(
				'plugin'  => 'st-mina-attendance',
				'version' => STMINA_ATT_VERSION,
			);
		},
	) );
} );

// عند إلغاء التفعيل: مهمة الإنهاء التلقائي تتشال، علشان WP-Cron مايندهش دالة مش موجودة
register_deactivation_hook( __FILE__, function () {
	wp_clear_scheduled_hook( 'stmina_att_autoclose' );
} );
