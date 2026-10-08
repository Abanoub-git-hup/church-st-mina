<?php
/**
 * المسح من غير نت (المهام 21 و22 و23).
 *
 * - "الشنطة" (GET /sessions/{id}/pack): اللي موبايل الخادم بيحفظه وهو فاتح الجلسة والنت شغال:
 *   الجلسة، والمخدومين بالاسم وكود الكارت بس (من غير موبايلات)، وحالة كل واحد في الجلسة.
 * - المزامنة (POST /sync): الطابور اللي اتعمل من غير نت بيتبعت مرة واحدة. كل عملية بنتيجتها،
 *   والإرسال مرتين مابيعملش سجل مكرر (القيد الفريد للمخدوم في الجلسة بيرجّع "dup").
 * - العملية المتأخرة بعد الإنهاء: سجل "غاب" اللي الإنهاء عمله (method = auto) بيتحوّل للحالة الجديدة
 *   بنفس السجل، ومن غير سجل تاني.
 * - وقت السجل هو وقت العملية الأصلي على الموبايل، مش وقت المزامنة.
 */

defined( 'ABSPATH' ) || exit;

/**
 * الشنطة: كل اللي شاشة المسح محتاجاه علشان تشتغل من غير نت.
 */
function stmina_att_pack( $session ) {
	global $wpdb;
	$m    = stmina_att_table( 'members' );
	$ms   = stmina_att_table( 'member_service' );
	$r    = stmina_att_table( 'records' );
	// نفس مخدومين stmina_att_roster، ومعاهم كود الكارت، والنشطين بس (الموقوف مابيتسجّلش)
	$rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT m.id, m.full_name, m.qr_token, m.status AS member_status, r.status, r.recorded_at
		 FROM $m m
		 JOIN $ms ms ON ms.member_id = m.id AND ms.service_id = %d
		 LEFT JOIN $r r ON r.member_id = m.id AND r.session_id = %d
		 WHERE r.id IS NOT NULL OR ( m.status = 'active' AND DATE(m.registered_at) <= %s )
		 ORDER BY m.full_name ASC",
		$session->service_id,
		$session->id,
		$session->session_date
	) );
	return array(
		'session' => stmina_att_session_json( $session ),
		'members' => array_map( function ( $x ) {
			return array(
				'id'          => (int) $x->id,
				'full_name'   => $x->full_name,
				'token'       => $x->qr_token,
				'active'      => 'active' === $x->member_status,
				'status'      => $x->status, // null = لسه
				'recorded_at' => $x->recorded_at,
			);
		}, $rows ),
		'saved_at' => current_time( 'mysql' ),
	);
}

/**
 * وقت العملية من الموبايل (توقيت القاهرة بصيغة Y-m-d H:i:s). الغلط أو اللي في المستقبل بيبقى "دلوقتي".
 */
function stmina_att_op_time( $at ) {
	$now = current_time( 'mysql' );
	if ( ! is_string( $at ) || ! preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $at ) ) {
		return $now;
	}
	return strtotime( $at ) > strtotime( $now ) + 5 * MINUTE_IN_SECONDS ? $now : $at;
}

/**
 * عملية واحدة من الطابور. نفس نتايج المسح (stmina_att_scan): ok وdup وrevoked وunknown وstopped وnosession.
 *
 * @param array $op id، وsession_id، وtype (scan أو set)، وcode للمسح، أو member_id وstatus لليدوي، وat.
 */
function stmina_att_apply_op( $op, $service ) {
	global $wpdb;
	$out     = array( 'id' => isset( $op['id'] ) ? (string) $op['id'] : '' );
	$session = stmina_att_get_session( isset( $op['session_id'] ) ? (int) $op['session_id'] : 0, $service );
	if ( ! $session ) {
		return $out + array( 'result' => 'nosession' ); // الجلسة اتمسحت
	}
	$type = isset( $op['type'] ) ? $op['type'] : '';
	$at   = stmina_att_op_time( isset( $op['at'] ) ? $op['at'] : '' );

	// مين المخدوم
	if ( 'scan' === $type ) {
		$code   = stmina_att_code_from_scan( isset( $op['code'] ) ? $op['code'] : '' );
		$member = $code ? stmina_att_member_by_token( $code ) : null;
		if ( ! $member && $code ) {
			$old = $wpdb->get_var( $wpdb->prepare( 'SELECT member_id FROM ' . stmina_att_table( 'revoked' ) . ' WHERE token = %s', $code ) );
			if ( $old ) {
				$name = $wpdb->get_var( $wpdb->prepare( 'SELECT full_name FROM ' . stmina_att_table( 'members' ) . ' WHERE id = %d', $old ) );
				return $out + array( 'result' => 'revoked', 'member' => array( 'id' => (int) $old, 'full_name' => $name ) );
			}
		}
		$status = 'present';
		$method = 'scan';
	} elseif ( 'set' === $type ) {
		$member = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . stmina_att_table( 'members' ) . ' WHERE id = %d', isset( $op['member_id'] ) ? (int) $op['member_id'] : 0 ) );
		$status = isset( $op['status'] ) ? $op['status'] : '';
		if ( ! in_array( $status, array( 'present', 'excused', 'absent' ), true ) ) {
			return $out + array( 'result' => 'invalid' );
		}
		$method = 'manual';
	} else {
		return $out + array( 'result' => 'invalid' );
	}

	$in_service = $member && $wpdb->get_var( $wpdb->prepare(
		'SELECT 1 FROM ' . stmina_att_table( 'member_service' ) . ' WHERE member_id = %d AND service_id = %d',
		$member->id, $session->service_id
	) );
	if ( ! $in_service ) {
		return $out + array( 'result' => 'unknown' );
	}
	$who = array( 'id' => (int) $member->id, 'full_name' => $member->full_name );
	if ( 'active' !== $member->status ) {
		return $out + array( 'result' => 'stopped', 'member' => $who );
	}

	$table    = stmina_att_table( 'records' );
	$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE session_id = %d AND member_id = %d", $session->id, $member->id ) );
	$row      = array(
		'status'      => $status,
		'method'      => $method,
		'recorded_by' => get_current_user_id(),
		'recorded_at' => $at,
	);

	if ( ! $existing ) {
		$quiet = $wpdb->suppress_errors( true );
		$ok    = $wpdb->insert( $table, $row + array( 'session_id' => $session->id, 'member_id' => $member->id ) );
		$wpdb->suppress_errors( $quiet );
		if ( $ok ) {
			return $out + array( 'result' => 'ok', 'member' => $who, 'status' => $status, 'recorded_at' => $at, 'late' => 'open' !== $session->status );
		}
		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE session_id = %d AND member_id = %d", $session->id, $member->id ) );
	}
	// "غاب" اللي الإنهاء عمله لوحده: العملية اللي اتأخرت في الطابور هي الحقيقة، فبتاخد مكانه في نفس السجل
	if ( $existing && 'auto' === $existing->method && $status !== $existing->status ) {
		$wpdb->update( $table, $row, array( 'id' => $existing->id ) );
		return $out + array( 'result' => 'ok', 'member' => $who, 'status' => $status, 'recorded_at' => $at, 'late' => true );
	}
	// اليدوي من الطابور بيعدّل الحالة زي التصحيح، والمسح بيبقى "متسجّل قبل كده"
	if ( $existing && 'set' === $type && $status !== $existing->status ) {
		$wpdb->update( $table, array( 'status' => $status, 'updated_by' => get_current_user_id(), 'updated_at' => $at ), array( 'id' => $existing->id ) );
		return $out + array( 'result' => 'ok', 'member' => $who, 'status' => $status, 'recorded_at' => $existing->recorded_at );
	}
	return $out + array( 'result' => 'dup', 'member' => $who, 'status' => $existing ? $existing->status : '', 'recorded_at' => $existing ? $existing->recorded_at : '' );
}

add_action( 'rest_api_init', function () {
	$ns   = 'stmina/v1';
	$args = array( 'service' => array( 'type' => 'string', 'default' => 'i3dad', 'sanitize_callback' => 'sanitize_key' ) );

	register_rest_route( $ns, '/sessions/(?P<id>\d+)/pack', array(
		'methods'             => 'GET',
		'permission_callback' => 'stmina_att_can',
		'args'                => $args,
		'callback'            => function ( WP_REST_Request $req ) {
			$s = stmina_att_req_session( $req );
			return is_wp_error( $s ) ? $s : stmina_att_pack( $s );
		},
	) );

	register_rest_route( $ns, '/sync', array(
		'methods'             => 'POST',
		'permission_callback' => 'stmina_att_can',
		'args'                => $args + array( 'ops' => array( 'type' => 'array', 'required' => true ) ),
		'callback'            => function ( WP_REST_Request $req ) {
			$service = stmina_att_req_service( $req );
			if ( is_wp_error( $service ) ) {
				return $service;
			}
			$ops = array_slice( (array) $req['ops'], 0, 500 ); // الطابور بتاع يوم خدمة مايوصلش لده
			return array(
				'results' => array_map( function ( $op ) use ( $service ) {
					return stmina_att_apply_op( is_array( $op ) ? $op : array(), $service );
				}, $ops ),
			);
		},
	) );
} );
