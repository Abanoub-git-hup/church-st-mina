<?php
/**
 * الجداول المخصّصة والصلاحيات، وتحديثهم لما رقم النسخة يتغيّر.
 *
 * ليه جداول مخصّصة مش أنواع محتوى؟ الحضور هيبقى آلاف السجلات، وبنحتاج قيود زي "الموبايل فريد"
 * و"سجل واحد لكل مخدوم في الجلسة"، وده سهل وسريع في جدول، وصعب في wp_posts وwp_postmeta.
 */

defined( 'ABSPATH' ) || exit;

// رقم نسخة الجداول. أي تغيير في شكل جدول يزوّده، والتحديث بيشتغل لوحده
define( 'STMINA_ATT_DB_VERSION', 1 );

/**
 * أسماء الجداول بالبادئة بتاعة الموقع (wp_ أو غيرها).
 */
function stmina_att_table( $name ) {
	global $wpdb;
	return $wpdb->prefix . 'stmina_' . $name;
}

/**
 * إنشاء أو تحديث الجداول بدالة dbDelta: بتقارن الشكل المكتوب هنا باللي في قاعدة البيانات،
 * وبتضيف الناقص من غير ما تمسح بيانات. وليها قواعد كتابة صارمة: كل عمود في سطر،
 * ومسافتين بعد PRIMARY KEY، ومن غير علامات ` حوالين الأسماء.
 */
function stmina_att_install() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$charset = $wpdb->get_charset_collate();

	dbDelta( 'CREATE TABLE ' . stmina_att_table( 'services' ) . " (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  slug varchar(64) NOT NULL,
  name varchar(191) NOT NULL,
  is_hidden tinyint(1) NOT NULL DEFAULT 0,
  created_at datetime NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY slug (slug)
) $charset;" );

	dbDelta( 'CREATE TABLE ' . stmina_att_table( 'members' ) . " (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  full_name varchar(191) NOT NULL,
  phone varchar(20) NOT NULL,
  qr_token char(32) NOT NULL,
  status varchar(10) NOT NULL DEFAULT 'active',
  registered_at datetime NOT NULL,
  created_by bigint(20) unsigned NOT NULL DEFAULT 0,
  updated_at datetime NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY phone (phone),
  UNIQUE KEY qr_token (qr_token),
  KEY status (status)
) $charset;" );

	dbDelta( 'CREATE TABLE ' . stmina_att_table( 'member_service' ) . " (
  member_id bigint(20) unsigned NOT NULL,
  service_id bigint(20) unsigned NOT NULL,
  joined_at datetime NOT NULL,
  PRIMARY KEY  (member_id,service_id),
  KEY service_id (service_id)
) $charset;" );

	dbDelta( 'CREATE TABLE ' . stmina_att_table( 'notes' ) . " (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  member_id bigint(20) unsigned NOT NULL,
  author_id bigint(20) unsigned NOT NULL,
  body text NOT NULL,
  created_at datetime NOT NULL,
  PRIMARY KEY  (id),
  KEY member_id (member_id)
) $charset;" );

	// الخدمات: "إعداد الخدام" الظاهرة، و"اختبار" المستخبية لبيانات الاختبارات
	$services = array(
		array( 'i3dad', 'إعداد الخدام', 0 ),
		array( 'test', 'اختبار', 1 ),
	);
	foreach ( $services as $s ) {
		$exists = $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . stmina_att_table( 'services' ) . ' WHERE slug = %s', $s[0] ) );
		if ( ! $exists ) {
			$wpdb->insert( stmina_att_table( 'services' ), array(
				'slug'       => $s[0],
				'name'       => $s[1],
				'is_hidden'  => $s[2],
				'created_at' => current_time( 'mysql' ),
			) );
		}
	}

	stmina_att_roles();
	update_option( 'stmina_att_db_version', STMINA_ATT_DB_VERSION );
}

/**
 * الصلاحيات: دور "خادم" بصلاحية واحدة stmina_attend، والمدير بياخدها كمان.
 * الصلاحية دي هي اللي بتتفحص في كل مسار، مش اسم الدور.
 */
function stmina_att_roles() {
	if ( ! get_role( 'stmina_servant' ) ) {
		add_role( 'stmina_servant', 'خادم', array( 'read' => true, 'stmina_attend' => true ) );
	}
	$admin = get_role( 'administrator' );
	if ( $admin && ! $admin->has_cap( 'stmina_attend' ) ) {
		$admin->add_cap( 'stmina_attend' );
	}
}

/**
 * التحديث لوحده: لو رقم النسخة المحفوظ أقل من اللي في الكود، نشغّل التثبيت.
 * ده بيغطي الرفع من غير تفعيل، لأن register_activation_hook مابيشتغلش مع التحديث.
 */
add_action( 'init', function () {
	if ( (int) get_option( 'stmina_att_db_version' ) < STMINA_ATT_DB_VERSION ) {
		stmina_att_install();
	}
}, 5 );
