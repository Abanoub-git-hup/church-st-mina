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
 *
 * والمهمتين 13 و14: التسجيل اليدوي وتصحيح السجل (PUT على سجل مخدوم)، وإنهاء الجلسة بتسجيل "غاب"
 * لكل مخدوم نشط ماتسجّلش.
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
 * عدد سجلات جلسة بحالة معيّنة.
 */
function stmina_att_status_count( $session_id, $status ) {
	global $wpdb;
	return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . stmina_att_table( 'records' ) . ' WHERE session_id = %d AND status = %s', $session_id, $status ) );
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
	$by  = get_userdata( $r->recorded_by );
	$upd = ! empty( $r->updated_by ) ? get_userdata( $r->updated_by ) : null;
	return array(
		'member_id'   => (int) $r->member_id,
		'full_name'   => $r->full_name,
		'status'      => $r->status,
		'method'      => $r->method,
		'recorded_at' => $r->recorded_at,
		'recorded_by' => $by ? $by->display_name : '', // فاضي = النظام (الإنهاء)
		'updated_by'  => $upd ? $upd->display_name : '',
		'updated_at'  => isset( $r->updated_at ) ? $r->updated_at : null,
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


/**
 * كل مخدومين الجلسة: النشطين اللي اتسجّلوا في الخدمة يوم الجلسة أو قبله، وأي حد ليه سجل فيها.
 * بيتستخدم في "تفاصيل الجلسة" وفي التسجيل اليدوي. اللي مالوش سجل حالته null ("لسه").
 *
 * @return array[]
 */
function stmina_att_roster( $session ) {
	global $wpdb;
	$m    = stmina_att_table( 'members' );
	$ms   = stmina_att_table( 'member_service' );
	$r    = stmina_att_table( 'records' );
	$rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT m.id AS member_id, m.full_name, r.status, r.method, r.recorded_by, r.recorded_at, r.updated_by, r.updated_at
		 FROM $m m
		 JOIN $ms ms ON ms.member_id = m.id AND ms.service_id = %d
		 LEFT JOIN $r r ON r.member_id = m.id AND r.session_id = %d
		 WHERE r.id IS NOT NULL OR ( m.status = 'active' AND DATE(m.registered_at) <= %s )
		 ORDER BY m.full_name ASC",
		$session->service_id,
		$session->id,
		$session->session_date
	) );
	return array_map( function ( $x ) {
		$by  = $x->recorded_by ? get_userdata( $x->recorded_by ) : null;
		$upd = $x->updated_by ? get_userdata( $x->updated_by ) : null;
		return array(
			'member_id'   => (int) $x->member_id,
			'full_name'   => $x->full_name,
			'status'      => $x->status, // null = لسه ماتسجّلش
			'method'      => $x->method,
			'recorded_at' => $x->recorded_at,
			'recorded_by' => $by ? $by->display_name : '',
			'updated_by'  => $upd ? $upd->display_name : '',
			'updated_at'  => $x->updated_at,
		);
	}, $rows );
}

/**
 * تسجيل يدوي أو تصحيح: حالة مخدوم في جلسة (present أو excused أو absent).
 * - مالوش سجل: سجل جديد بطريقة "يدوي" باسم الخادم. ولازم الجلسة تبقى مفتوحة والمخدوم نشط.
 * - ليه سجل: الحالة بتتغيّر، ومين عدّل وإمتى بيتحفظوا. وده مسموح حتى بعد الإنهاء (التصحيح).
 *
 * @return array|WP_Error السجل بعد الحفظ.
 */
function stmina_att_set_record( $session, $member_id, $status, $retry = true ) {
	global $wpdb;
	if ( ! in_array( $status, array( 'present', 'excused', 'absent' ), true ) ) {
		return new WP_Error( 'stmina_invalid', 'الحالة لازم تكون حضر أو غاب بعذر أو غاب.', array( 'status' => 400, 'fields' => array( 'status' => 'اختار الحالة.' ) ) );
	}
	$member = $wpdb->get_row( $wpdb->prepare(
		'SELECT m.* FROM ' . stmina_att_table( 'members' ) . ' m JOIN ' . stmina_att_table( 'member_service' ) . ' ms ON ms.member_id = m.id AND ms.service_id = %d WHERE m.id = %d',
		$session->service_id,
		$member_id
	) );
	if ( ! $member ) {
		return new WP_Error( 'stmina_not_found', 'المخدوم ده مش في الخدمة دي.', array( 'status' => 404 ) );
	}
	$table    = stmina_att_table( 'records' );
	$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE session_id = %d AND member_id = %d", $session->id, $member_id ) );
	$now      = current_time( 'mysql' );

	if ( $existing ) {
		if ( $existing->status !== $status ) {
			$wpdb->update( $table, array( 'status' => $status, 'updated_by' => get_current_user_id(), 'updated_at' => $now ), array( 'id' => $existing->id ) );
		}
	} else {
		if ( 'open' !== $session->status ) {
			return new WP_Error( 'stmina_closed', 'الجلسة دي خلصت. التصحيح للي ليهم سجل بس.', array( 'status' => 409 ) );
		}
		if ( 'active' !== $member->status ) {
			return new WP_Error( 'stmina_stopped', 'المخدوم ده موقوف.', array( 'status' => 409 ) );
		}
		$quiet = $wpdb->suppress_errors( true );
		$ok    = $wpdb->insert( $table, array(
			'session_id'  => $session->id,
			'member_id'   => $member_id,
			'status'      => $status,
			'method'      => 'manual',
			'recorded_by' => get_current_user_id(),
			'recorded_at' => $now,
		) );
		$wpdb->suppress_errors( $quiet );
		if ( ! $ok && $retry ) {
			// اتسجّل في نفس اللحظة من خادم تاني أو بالمسح: نصحّح الحالة بدل ما نضيف
			return stmina_att_set_record( $session, $member_id, $status, false );
		}
	}
	$row = $wpdb->get_row( $wpdb->prepare(
		"SELECT r.*, m.full_name FROM $table r JOIN " . stmina_att_table( 'members' ) . ' m ON m.id = r.member_id WHERE r.session_id = %d AND r.member_id = %d',
		$session->id,
		$member_id
	) );
	return stmina_att_record_json( $row );
}

/**
 * إنهاء الجلسة: "غاب" لكل مخدوم نشط في الخدمة، اتسجّل يوم الجلسة أو قبله، ومالوش سجل فيها.
 * الموقوف واللي اتسجّل بعد الجلسة واللي ليه سجل (حضر أو بعذر) مابيتحسبوش.
 * الإنهاء مرة تانية مابيعملش حاجة، فمفيش سجلات مكررة.
 *
 * @return int عدد سجلات الغياب اللي اتعملت.
 */
function stmina_att_close_session( $session ) {
	global $wpdb;
	if ( 'open' !== $session->status ) {
		return 0;
	}
	$m  = stmina_att_table( 'members' );
	$ms = stmina_att_table( 'member_service' );
	$r  = stmina_att_table( 'records' );
	// INSERT IGNORE مع القيد الفريد: حتى لو حد مسح في نفس اللحظة، مفيش سجل مكرر
	$created = (int) $wpdb->query( $wpdb->prepare(
		"INSERT IGNORE INTO $r (session_id, member_id, status, method, recorded_by, recorded_at)
		 SELECT %d, m.id, 'absent', 'auto', 0, %s
		 FROM $m m
		 JOIN $ms ms ON ms.member_id = m.id AND ms.service_id = %d
		 WHERE m.status = 'active' AND DATE(m.registered_at) <= %s
		   AND NOT EXISTS ( SELECT 1 FROM $r x WHERE x.session_id = %d AND x.member_id = m.id )",
		$session->id,
		current_time( 'mysql' ),
		$session->service_id,
		$session->session_date,
		$session->id
	) );
	$wpdb->update( stmina_att_table( 'sessions' ), array( 'status' => 'closed', 'closed_at' => current_time( 'mysql' ) ), array( 'id' => $session->id ) );
	return $created;
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

	// كل مخدومين الجلسة بحالاتهم (تفاصيل الجلسة والتسجيل اليدوي)
	register_rest_route( $ns, '/sessions/(?P<id>\d+)/roster', array(
		'methods'             => 'GET',
		'permission_callback' => 'stmina_att_can',
		'args'                => $args,
		'callback'            => function ( WP_REST_Request $req ) {
			$s = stmina_att_req_session( $req );
			return is_wp_error( $s ) ? $s : stmina_att_roster( $s );
		},
	) );

	// إنهاء الجلسة وتسجيل الغياب
	register_rest_route( $ns, '/sessions/(?P<id>\d+)/close', array(
		'methods'             => 'POST',
		'permission_callback' => 'stmina_att_can',
		'args'                => $args,
		'callback'            => function ( WP_REST_Request $req ) {
			$s = stmina_att_req_session( $req );
			if ( is_wp_error( $s ) ) {
				return $s;
			}
			$created               = stmina_att_close_session( $s );
			$out                   = stmina_att_session_json( stmina_att_req_session( $req ) );
			$out['absent_created'] = $created;
			return $out;
		},
	) );

	// التسجيل اليدوي والتصحيح (PUT { status })، والتراجع عن تسجيل (DELETE، في الجلسة المفتوحة بس)
	register_rest_route( $ns, '/sessions/(?P<id>\d+)/records/(?P<member>\d+)', array(
		array(
			'methods'             => 'PUT',
			'permission_callback' => 'stmina_att_can',
			'args'                => $args,
			'callback'            => function ( WP_REST_Request $req ) {
				$s = stmina_att_req_session( $req );
				return is_wp_error( $s ) ? $s : stmina_att_set_record( $s, (int) $req['member'], (string) $req->get_param( 'status' ) );
			},
		),
		array(
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
		),
	) );
} );
