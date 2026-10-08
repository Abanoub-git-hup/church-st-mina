<?php
/**
 * الجلسات: الفتح، والقايمة، وآخر جلسة (لزرار "زي آخر جلسة")، وحذف الجلسة المفتوحة بالغلط.
 * الإنهاء وتسجيل الغياب في المهمة 14، والمسح في المهمة 11.
 */

defined( 'ABSPATH' ) || exit;

/**
 * أنواع النشاط بأسمائها.
 */
function stmina_att_kinds() {
	return array(
		'mass'     => 'قداس',
		'meeting'  => 'اجتماع',
		'activity' => 'نشاط',
		'service'  => 'خدمة',
	);
}

/**
 * شكل الجلسة في الرد.
 */
function stmina_att_session_json( $s ) {
	$kinds = stmina_att_kinds();
	return array(
		'id'        => (int) $s->id,
		'kind'      => $s->kind,
		'kind_name' => isset( $kinds[ $s->kind ] ) ? $kinds[ $s->kind ] : $s->kind,
		'date'      => $s->session_date,
		'status'    => $s->status,
		'opened_at' => $s->opened_at,
		'closed_at' => $s->closed_at,
		'present'   => stmina_att_present_count( $s->id ),
		'excused'   => stmina_att_status_count( $s->id, 'excused' ),
		'absent'    => stmina_att_status_count( $s->id, 'absent' ),
	);
}

/**
 * جلسات خدمة، الأحدث الأول (باليوم، وبعدين بوقت الفتح).
 *
 * @return object[]
 */
function stmina_att_list_sessions( $service ) {
	global $wpdb;
	return $wpdb->get_results( $wpdb->prepare(
		'SELECT * FROM ' . stmina_att_table( 'sessions' ) . ' WHERE service_id = %d ORDER BY session_date DESC, opened_at DESC, id DESC',
		$service->id
	) );
}

/**
 * جلسة في خدمة، أو null.
 */
function stmina_att_get_session( $id, $service ) {
	global $wpdb;
	return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . stmina_att_table( 'sessions' ) . ' WHERE id = %d AND service_id = %d', $id, $service->id ) );
}

/**
 * فتح جلسة. اليوم بصيغة Y-m-d، ومينفعش يكون لسه ماجاش، ومينفعش جلستين من نفس النوع في نفس اليوم.
 *
 * @return int|WP_Error رقم الجلسة.
 */
function stmina_att_open_session( $service, $kind, $date ) {
	global $wpdb;
	$errors = array();
	if ( ! isset( stmina_att_kinds()[ $kind ] ) ) {
		$errors['kind'] = 'اختار نوع الجلسة الأول.';
	}
	$d = DateTimeImmutable::createFromFormat( '!Y-m-d', (string) $date, wp_timezone() );
	if ( ! $d || $d->format( 'Y-m-d' ) !== $date ) {
		$errors['date'] = 'اختار التاريخ.';
	} elseif ( $date > current_time( 'Y-m-d' ) ) {
		$errors['date'] = 'التاريخ ده لسه ماجاش. الجلسة بتتفتح يومها أو بعده.';
	}
	if ( $errors ) {
		return new WP_Error( 'stmina_invalid', 'فيه بيانات محتاجة تتصلّح.', array( 'status' => 400, 'fields' => $errors ) );
	}

	$same = $wpdb->get_row( $wpdb->prepare(
		'SELECT id, status FROM ' . stmina_att_table( 'sessions' ) . ' WHERE service_id = %d AND kind = %s AND session_date = %s',
		$service->id, $kind, $date
	) );
	if ( $same ) {
		$msg = 'open' === $same->status ? 'فيه جلسة ' . stmina_att_kinds()[ $kind ] . ' مفتوحة في اليوم ده فعلًا.' : 'جلسة ' . stmina_att_kinds()[ $kind ] . ' اليوم ده اتعملت وخلصت.';
		return new WP_Error( 'stmina_duplicate', $msg, array( 'status' => 409, 'session' => (int) $same->id ) );
	}

	$ok = $wpdb->insert( stmina_att_table( 'sessions' ), array(
		'service_id'   => $service->id,
		'kind'         => $kind,
		'session_date' => $date,
		'status'       => 'open',
		'opened_by'    => get_current_user_id(),
		'opened_at'    => current_time( 'mysql' ),
	) );
	if ( ! $ok ) {
		// خادمين فتحوا نفس الجلسة في نفس اللحظة، والقيد الفريد رفض التاني
		return new WP_Error( 'stmina_duplicate', 'الجلسة دي اتفتحت لسه من خادم تاني.', array( 'status' => 409 ) );
	}
	return (int) $wpdb->insert_id;
}

/**
 * حذف جلسة مفتوحة اتفتحت بالغلط. المنتهية مابتتمسحش، لأنها داخلة في النسب.
 *
 * @return true|WP_Error
 */
function stmina_att_delete_session( $session ) {
	global $wpdb;
	// المنتهية مابتتمسحش، إلا في خدمة مستخبية (خدمة "اختبار")، علشان بيانات الاختبارات ماتتراكمش
	$hidden = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT is_hidden FROM ' . stmina_att_table( 'services' ) . ' WHERE id = %d', $session->service_id ) );
	if ( 'open' !== $session->status && ! $hidden ) {
		return new WP_Error( 'stmina_closed', 'الجلسة دي خلصت ومينفعش تتمسح.', array( 'status' => 409 ) );
	}
	$wpdb->delete( stmina_att_table( 'records' ), array( 'session_id' => $session->id ) ); // التسجيلات اللي اتعملت فيها بالغلط
	$wpdb->delete( stmina_att_table( 'sessions' ), array( 'id' => $session->id ) );
	return true;
}

add_action( 'rest_api_init', function () {
	$ns   = 'stmina/v1';
	$args = array( 'service' => array( 'type' => 'string', 'default' => 'i3dad', 'sanitize_callback' => 'sanitize_key' ) );

	register_rest_route( $ns, '/sessions', array(
		array(
			'methods'             => 'GET',
			'permission_callback' => 'stmina_att_can',
			'args'                => $args,
			'callback'            => function ( WP_REST_Request $req ) {
				$service = stmina_att_req_service( $req );
				return is_wp_error( $service ) ? $service : array_map( 'stmina_att_session_json', stmina_att_list_sessions( $service ) );
			},
		),
		array(
			'methods'             => 'POST',
			'permission_callback' => 'stmina_att_can',
			'args'                => $args,
			'callback'            => function ( WP_REST_Request $req ) {
				$service = stmina_att_req_service( $req );
				if ( is_wp_error( $service ) ) {
					return $service;
				}
				$id = stmina_att_open_session( $service, (string) $req->get_param( 'kind' ), (string) $req->get_param( 'date' ) );
				if ( is_wp_error( $id ) ) {
					return $id;
				}
				$res = rest_ensure_response( stmina_att_session_json( stmina_att_get_session( $id, $service ) ) );
				$res->set_status( 201 );
				return $res;
			},
		),
	) );

	// "زي آخر جلسة": الخدمة والنوع بس، من غير التاريخ
	register_rest_route( $ns, '/sessions/last', array(
		'methods'             => 'GET',
		'permission_callback' => 'stmina_att_can',
		'args'                => $args,
		'callback'            => function ( WP_REST_Request $req ) {
			$service = stmina_att_req_service( $req );
			if ( is_wp_error( $service ) ) {
				return $service;
			}
			$list = stmina_att_list_sessions( $service );
			return $list ? array( 'service' => $service->slug, 'kind' => $list[0]->kind ) : null;
		},
	) );

	register_rest_route( $ns, '/sessions/(?P<id>\d+)', array(
		array(
			'methods'             => 'GET',
			'permission_callback' => 'stmina_att_can',
			'args'                => $args,
			'callback'            => function ( WP_REST_Request $req ) {
				$s = stmina_att_req_session( $req );
				return is_wp_error( $s ) ? $s : stmina_att_session_json( $s );
			},
		),
		array(
			'methods'             => 'DELETE',
			'permission_callback' => 'stmina_att_can',
			'args'                => $args,
			'callback'            => function ( WP_REST_Request $req ) {
				$s = stmina_att_req_session( $req );
				if ( is_wp_error( $s ) ) {
					return $s;
				}
				$done = stmina_att_delete_session( $s );
				return is_wp_error( $done ) ? $done : array( 'deleted' => true );
			},
		),
	) );
} );

/**
 * الجلسة من رقم المسار، في الخدمة اللي في الطلب، أو 404.
 *
 * @return object|WP_Error
 */
function stmina_att_req_session( WP_REST_Request $req ) {
	$service = stmina_att_req_service( $req );
	if ( is_wp_error( $service ) ) {
		return $service;
	}
	$s = stmina_att_get_session( (int) $req['id'], $service );
	return $s ? $s : new WP_Error( 'stmina_not_found', 'الجلسة دي مش موجودة.', array( 'status' => 404 ) );
}
