<?php
/**
 * الاستيراد من Excel (المهمة 20)، وحالة "الكارت اتبعت ولا لأ".
 *
 * المتصفح بيقرا الملف (مكتبة SheetJS) وبيبعت الصفوف كـ { line, name, phone }، والسيرفر بيتحقق
 * من كل صف بنفس قواعد "إضافة مخدوم" (stmina_att_validate)، وبيرفض كمان الرقم المكرر جوه الملف.
 * - commit = false: معاينة بس، من غير ما حاجة تتضاف.
 * - commit = true: الصفوف السليمة بتتضاف، والرد فيه المخدومين الجداد بكود الكارت.
 */

defined( 'ABSPATH' ) || exit;

/**
 * فحص صفوف الاستيراد، وإضافتهم لو commit.
 *
 * @param object $service الخدمة.
 * @param array  $rows    كل صف: line (رقم الصف في الملف)، name، phone.
 * @param bool   $commit  تضيف ولا معاينة بس.
 * @return array rows (كل صف: line, name, phone, ok, why, fixed) و added (المخدومين اللي اتضافوا).
 */
function stmina_att_import( $service, $rows, $commit ) {
	$out   = array();
	$added = array();
	$seen  = array(); // الموبايل ← أول صف ظهر فيه، للمكرر جوه الملف
	foreach ( array_slice( (array) $rows, 0, 1000 ) as $i => $row ) {
		$line  = isset( $row['line'] ) ? (int) $row['line'] : $i + 1;
		$name  = stmina_att_name( isset( $row['name'] ) ? $row['name'] : '' );
		$raw   = isset( $row['phone'] ) ? (string) $row['phone'] : '';
		$phone = stmina_att_phone( $raw );
		$r     = array(
			'line'  => $line,
			'name'  => $name,
			'phone' => $phone ? $phone : stmina_att_digits( $raw ),
			'ok'    => false,
			'why'   => '',
			// اتظبط لوحده: الصفر اللي Excel شاله، أو +20، أو أرقام عربي
			'fixed' => $phone && $phone !== trim( $raw ),
		);
		if ( '' === $name && '' === trim( $raw ) ) {
			continue; // صف فاضي
		}
		if ( '' === $name ) {
			$r['why'] = 'من غير اسم';
		} elseif ( '' === trim( $raw ) ) {
			$r['why'] = 'من غير موبايل';
		} elseif ( count( explode( ' ', $name ) ) < 2 ) {
			$r['why'] = 'الاسم كلمة واحدة، اكتبه اسمين على الأقل';
		} elseif ( ! $phone ) {
			$r['why'] = 'الرقم ' . $raw . ' مش رقم موبايل مصري صحيح';
		} elseif ( isset( $seen[ $phone ] ) ) {
			$r['why'] = 'الرقم مكرر، نفس رقم صف ' . $seen[ $phone ];
		} else {
			$check = stmina_att_validate( array( 'full_name' => $name, 'phone' => $phone ), true );
			if ( is_wp_error( $check ) ) {
				$fields   = $check->get_error_data()['fields'];
				$r['why'] = rtrim( isset( $fields['phone'] ) ? $fields['phone'] : reset( $fields ), '.' );
			}
		}
		if ( ! $r['why'] ) {
			$seen[ $phone ] = $line;
			$r['ok']        = true;
			if ( $commit ) {
				$id = stmina_att_create_member( array( 'full_name' => $name, 'phone' => $phone ), $service );
				if ( is_wp_error( $id ) ) {
					$r['ok']  = false;
					$r['why'] = $id->get_error_message();
				} else {
					$added[] = stmina_att_member_json( stmina_att_get_member( $id, $service ) );
				}
			}
		}
		$out[] = $r;
	}
	return array( 'rows' => $out, 'added' => $added );
}

/**
 * علّم إن الكارت اتبعت للمخدوم (الخادم داس "ابعت الكارت" من أي مكان).
 */
function stmina_att_mark_card_sent( $member_id ) {
	global $wpdb;
	$wpdb->update( stmina_att_table( 'members' ), array( 'card_sent_at' => current_time( 'mysql' ) ), array( 'id' => $member_id ) );
}

add_action( 'rest_api_init', function () {
	$ns   = 'stmina/v1';
	$args = array( 'service' => array( 'type' => 'string', 'default' => 'i3dad', 'sanitize_callback' => 'sanitize_key' ) );

	register_rest_route( $ns, '/members/import', array(
		'methods'             => 'POST',
		'permission_callback' => 'stmina_att_can',
		'args'                => $args + array(
			'rows'   => array( 'type' => 'array', 'required' => true ),
			'commit' => array( 'type' => 'boolean', 'default' => false ),
		),
		'callback'            => function ( WP_REST_Request $req ) {
			$service = stmina_att_req_service( $req );
			if ( is_wp_error( $service ) ) {
				return $service;
			}
			return stmina_att_import( $service, $req['rows'], (bool) $req['commit'] );
		},
	) );

	register_rest_route( $ns, '/members/(?P<id>\d+)/card-sent', array(
		'methods'             => 'POST',
		'permission_callback' => 'stmina_att_can',
		'args'                => $args,
		'callback'            => function ( WP_REST_Request $req ) {
			$m = stmina_att_req_member( $req );
			if ( is_wp_error( $m ) ) {
				return $m;
			}
			stmina_att_mark_card_sent( (int) $m->id );
			return stmina_att_member_json( stmina_att_req_member( $req ) );
		},
	) );
} );
