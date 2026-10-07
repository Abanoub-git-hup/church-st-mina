<?php
/**
 * Plugin Name: St Mina Attendance
 * Plugin URI: https://github.com/Abanoub-git-hup/church-st-mina
 * Description: نظام الحضور بالـ QR لخدمة إعداد الخدام. الشاشات المعتمدة في design/attend-*.html، وقواعد الحساب في docs/design.md القسم 5.
 * Version: 0.1.0
 * Author: Abanoub S. Maurice
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Text Domain: st-mina-attendance
 */

defined( 'ABSPATH' ) || exit;

define( 'STMINA_ATT_VERSION', '0.1.0' );
define( 'STMINA_ATT_FILE', __FILE__ );

// بداية فاضية (المهمة 01). الجداول والصلاحيات وواجهة REST بتتعمل من المهمة 08

register_activation_hook( __FILE__, function () {
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
