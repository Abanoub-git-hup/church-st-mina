<?php
/**
 * سجل الحضور: المسح، وقايمة اللي اتسجّلوا في الجلسة، والتراجع عن تسجيل.
 *
 * نتيجة المسح دايمًا بترجع 200 وفيها result، علشان الشاشة (وبعدين طابور المسح من غير نت) تتعامل
 * مع كل الحالات بنفس الطريقة:
 *   ok         اتسجّل دلوقتي
 *   dup        متسجّل قبل كده في الجلسة دي (ومابيتسجّلش تاني)
 *   revoked    كارت اتعمله إعادة إصدار
 *   unknown    كود مش لأي مخدوم في الخدمة
 *   stopped    المخدوم موقوف
 *   nosession  الجلسة مش مفتوحة (خلصت أو اتمسحت)
 */

defined( 'ABSPATH' ) || exit;

/**
 * الكود من اللي الكاميرا قرته: رابط الكارت كامل (https://…/me/<الكود>/) أو الكود لوحده.
 */
function stmina_att_code_from_scan( $raw ) {
	$raw = trim( (string) $raw );
	if ( preg_match( '~/me/([A-Za-z0-9_-]{32})/?(?:[?#].*)?$~', $raw, $m ) ) {
		return $m[1];
	}
	return preg_match( '/^[A-Za-z0-9_-]{32}$/', $raw ) ? $raw : '';
}

/**
 * عدد الحاضرين في جلسة.
 */
function stmina_att_present_count( $session_id ) {
	global $wpdb;
	return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . stmina_att_table( 'records' ) . " WHERE session_id = %d AND status = 'present'", $session_id ) );
}

/**
 * سجلات جلسة، الأحدث الأول، بالاسم واسم الخادم اللي سجّل.
 *
 * @return array[]
 */
function stmina_att_session_records( $session_id ) {
	global $wpdb;
	$rows = $wpdb->get_results( $wpdb->prepare(
		'SELECT r.*, m.full_name FROM ' . stmina_att_table( 'records' ) . ' r
		 JOIN ' . stmina_att_table( 'members' ) . ' m ON m.id = r.member_id
		 WHERE r.session_id = %d ORDER BY r.recorded_at DESC, r.id DESC',
		$session_id
	) );
	return array_map( 'stmina_att_record_json', $rows );
}

/**
 * شكل السجل في الرد.
 */
function stmina_att_record_json( $r ) {
	$by = get_userdata( $r->recorded_by );
	return array(
		'member_id'   => (int) $r->member_id,
		'full_name'   => $r->full_name,
		'status'      => $r->status,
		'method'      => $r->method,
		'recorded_at' => $r->recorded_at,
		'recorded_by' => $by ? $by->display_name : '',
	);
}

/**
 * مسح كارت في جلسة.
 *
 * @return array result، والمخدوم (الاسم والرقم) لو اتعرف، ووقت تسجيله لو متسجّل.
 */
function stmina_att_scan( $session, $raw ) {
	global $wpdb;
	if ( ! $session || 'open' !== $session->status ) {
		return array( 'result' => 'nosession' );
	}

	$code   = stmina_att_code_from_scan( $raw );
	$member = $code ? stmina_att_member_by_token( $code ) : null;
	if ( ! $member && $code ) {
		$old = $wpdb->get_var( $wpdb->prepare( 'SELECT member_id FROM ' . stmina_att_table( 'revoked' ) . ' WHERE token = %s', $code ) );
		if ( $old ) {
			$name = $wpdb->get_var( $wpdb->prepare( 'SELECT full_name FROM ' . stmina_att_table( 'members' ) . ' WHERE id = %d', $old ) );
			return array( 'result' => 'revoked', 'member' => array( 'id' => (int) $old, 'full_name' => $name ) );
		}
	}
	// المخدوم لازم يبقى في خدمة الجلسة، وإلا الكود "مش معروف" بالنسبة للجلسة دي
	$in_service = $member && $wpdb->get_var( $wpdb->prepare(
		'SELECT 1 FROM ' . stmina_att_table( 'member_service' ) . ' WHERE member_id = %d AND service_id = %d',
		$member->id, $session->service_id
	) );
	if ( ! $in_service ) {
		return array( 'result' => 'unknown' );
	}
	$who = array( 'id' => (int) $member->id, 'full_name' => $member->full_name );
	if ( 'active' !== $member->status ) {
		return array( 'result' => 'stopped', 'member' => $who );
	}

	$existing = $wpdb->get_row( $wpdb->prepare(
		'SELECT * FROM ' . stmina_att_table( 'records' ) . ' WHERE session_id = %d AND member_id = %d',
		$session->id, $member->id
	) );
	if ( ! $existing ) {
		$now = current_time( 'mysql' );
		// لو خادمين مسحوا نفس الكارت في نفس اللحظة، القيد الفريد بيرفض التاني فبيبقى "متسجّل قبل كده"
		// والخطأ ده متوقع، فمانطبعهوش (لو WP_DEBUG شغال كان هيتطبع ويبوّظ رد الـ JSON)
		$quiet = $wpdb->suppress_errors( true );
		$ok    = $wpdb->insert( stmina_att_table( 'records' ), array(
			'session_id'  => $session->id,
			'member_id'   => $member->id,
			'status'      => 'present',
			'method'      => 'scan',
			'recorded_by' => get_current_user_id(),
			'recorded_at' => $now,
		) );
		$wpdb->suppress_errors( $quiet );
		if ( $ok ) {
			return array( 'result' => 'ok', 'member' => $who, 'recorded_at' => $now, 'present' => stmina_att_present_count( $session->id ) );
		}
		$existing = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . stmina_att_table( 'records' ) . ' WHERE session_id = %d AND member_id = %d', $session->id, $member->id ) );
	}
	return array( 'result' => 'dup', 'member' => $who, 'recorded_at' => $existing ? $existing->recorded_at : '', 'status' => $existing ? $existing->status : '' );
}

add_action( 'rest_api_init', function () {
	$ns   = 'stmina/v1';
	$args = array( 'service' => array( 'type' => 'string', 'default' => 'i3dad', 'sanitize_callback' => 'sanitize_key' ) );

	register_rest_route( $ns, '/sessions/(?P<id>\d+)/scan', array(
		'methods'             => 'POST',
		'permission_callback' => 'stmina_att_can',
		'args'                => $args + array( 'code' => array( 'type' => 'string', 'required' => true ) ),
		'callback'            => function ( WP_REST_Request $req ) {
			$s = stmina_att_req_session( $req );
			// الجلسة اللي اتمسحت أو مش موجودة = "مفيش جلسة مفتوحة"، مش خطأ
			return stmina_att_scan( is_wp_error( $s ) ? null : $s, $req['code'] );
		},
	) );

	register_rest_route( $ns, '/sessions/(?P<id>\d+)/records', array(
		'methods'             => 'GET',
		'permission_callback' => 'stmina_att_can',
		'args'                => $args,
		'callback'            => function ( WP_REST_Request $req ) {
			$s = stmina_att_req_session( $req );
			return is_wp_error( $s ) ? $s : stmina_att_session_records( (int) $s->id );
		},
	) );

	// التراجع عن تسجيل (لما الخادم يمسح حد بالغلط). في الجلسة المفتوحة بس
	register_rest_route( $ns, '/sessions/(?P<id>\d+)/records/(?P<member>\d+)', array(
		'methods'             => 'DELETE',
		'permission_callback' => 'stmina_att_can',
		'args'                => $args,
		'callback'            => function ( WP_REST_Request $req ) {
			global $wpdb;
			$s = stmina_att_req_session( $req );
			if ( is_wp_error( $s ) ) {
				return $s;
			}
			if ( 'open' !== $s->status ) {
				return new WP_Error( 'stmina_closed', 'الجلسة دي خلصت. التصحيح بيتعمل من تفاصيل الجلسة.', array( 'status' => 409 ) );
			}
			$n = $wpdb->delete( stmina_att_table( 'records' ), array( 'session_id' => $s->id, 'member_id' => (int) $req['member'] ) );
			if ( ! $n ) {
				return new WP_Error( 'stmina_not_found', 'المخدوم ده مش متسجّل في الجلسة.', array( 'status' => 404 ) );
			}
			return array( 'deleted' => true, 'present' => stmina_att_present_count( $s->id ) );
		},
	) );
} );
