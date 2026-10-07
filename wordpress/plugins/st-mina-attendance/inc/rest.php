<?php
/**
 * مسارات REST للخدام تحت /wp-json/stmina/v1/.
 * كل مسار هنا بيفحص صلاحية stmina_attend في permission_callback قبل أي حاجة:
 * الزائر من غير دخول بياخد 401، والمستخدم اللي مالوش الصلاحية بياخد 403.
 * وكود QR بتاع المخدوم مش وسيلة دخول هنا خالص (هيبقى ليه مسار لوحده في المهمة 16).
 */

defined( 'ABSPATH' ) || exit;

/**
 * فحص الصلاحية لكل مسارات الخدام.
 */
function stmina_att_can() {
	if ( ! is_user_logged_in() ) {
		return new WP_Error( 'rest_not_logged_in', 'لازم تسجّل دخول كخادم.', array( 'status' => 401 ) );
	}
	if ( ! current_user_can( 'stmina_attend' ) ) {
		return new WP_Error( 'rest_forbidden', 'الحساب ده مالوش صلاحية الخدام.', array( 'status' => 403 ) );
	}
	return true;
}

/**
 * الخدمة من الطلب (?service=)، والافتراضي "إعداد الخدام".
 *
 * @return object|WP_Error
 */
function stmina_att_req_service( WP_REST_Request $req ) {
	$service = stmina_att_service( $req->get_param( 'service' ) ?: 'i3dad' );
	return $service ? $service : new WP_Error( 'stmina_no_service', 'الخدمة دي مش موجودة.', array( 'status' => 404 ) );
}

/**
 * شكل المخدوم في الرد. الأرقام أرقام، والكود للخدام بس لأن المسارات دي كلها للخدام.
 */
function stmina_att_member_json( $m ) {
	return array(
		'id'            => (int) $m->id,
		'full_name'     => $m->full_name,
		'phone'         => $m->phone,
		'status'        => $m->status,
		'registered_at' => $m->registered_at,
		'qr_token'      => $m->qr_token,
	);
}

add_action( 'rest_api_init', function () {
	$ns   = 'stmina/v1';
	$args = array(
		'service' => array( 'type' => 'string', 'default' => 'i3dad', 'sanitize_callback' => 'sanitize_key' ),
	);

	register_rest_route( $ns, '/members', array(
		array(
			'methods'             => 'GET',
			'permission_callback' => 'stmina_att_can',
			'args'                => $args + array(
				'status' => array( 'type' => 'string', 'enum' => array( '', 'active', 'stopped' ), 'default' => '' ),
				'search' => array( 'type' => 'string', 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ),
			),
			'callback'            => function ( WP_REST_Request $req ) {
				$service = stmina_att_req_service( $req );
				if ( is_wp_error( $service ) ) {
					return $service;
				}
				$list = stmina_att_list_members( $service, $req['status'], trim( $req['search'] ) );
				return array_map( 'stmina_att_member_json', $list );
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
				$id = stmina_att_create_member( array(
					'full_name' => $req->get_param( 'full_name' ),
					'phone'     => $req->get_param( 'phone' ),
				), $service );
				if ( is_wp_error( $id ) ) {
					return $id;
				}
				$res = rest_ensure_response( stmina_att_member_json( stmina_att_get_member( $id, $service ) ) );
				$res->set_status( 201 );
				return $res;
			},
		),
	) );

	register_rest_route( $ns, '/members/(?P<id>\d+)', array(
		array(
			'methods'             => 'GET',
			'permission_callback' => 'stmina_att_can',
			'args'                => $args,
			'callback'            => function ( WP_REST_Request $req ) {
				$m = stmina_att_req_member( $req );
				return is_wp_error( $m ) ? $m : stmina_att_member_json( $m );
			},
		),
		array(
			// التعديل والإيقاف: { full_name?, phone?, status? }. مفيش DELETE خالص
			'methods'             => 'PATCH',
			'permission_callback' => 'stmina_att_can',
			'args'                => $args,
			'callback'            => function ( WP_REST_Request $req ) {
				$m = stmina_att_req_member( $req );
				if ( is_wp_error( $m ) ) {
					return $m;
				}
				$data = array_intersect_key( $req->get_json_params() ?: $req->get_body_params(), array_flip( array( 'full_name', 'phone', 'status' ) ) );
				$done = stmina_att_update_member( (int) $m->id, $data );
				if ( is_wp_error( $done ) ) {
					return $done;
				}
				return stmina_att_member_json( stmina_att_req_member( $req ) );
			},
		),
	) );

	register_rest_route( $ns, '/members/(?P<id>\d+)/notes', array(
		array(
			'methods'             => 'GET',
			'permission_callback' => 'stmina_att_can',
			'args'                => $args,
			'callback'            => function ( WP_REST_Request $req ) {
				$m = stmina_att_req_member( $req );
				return is_wp_error( $m ) ? $m : stmina_att_notes( (int) $m->id );
			},
		),
		array(
			'methods'             => 'POST',
			'permission_callback' => 'stmina_att_can',
			'args'                => $args,
			'callback'            => function ( WP_REST_Request $req ) {
				$m = stmina_att_req_member( $req );
				if ( is_wp_error( $m ) ) {
					return $m;
				}
				$id = stmina_att_add_note( (int) $m->id, $req->get_param( 'body' ) );
				if ( is_wp_error( $id ) ) {
					return $id;
				}
				$res = rest_ensure_response( stmina_att_notes( (int) $m->id )[0] );
				$res->set_status( 201 );
				return $res;
			},
		),
	) );
} );

/**
 * المخدوم من رقم المسار، في الخدمة اللي في الطلب، أو 404.
 *
 * @return object|WP_Error
 */
function stmina_att_req_member( WP_REST_Request $req ) {
	$service = stmina_att_req_service( $req );
	if ( is_wp_error( $service ) ) {
		return $service;
	}
	$m = stmina_att_get_member( (int) $req['id'], $service );
	return $m ? $m : new WP_Error( 'stmina_not_found', 'المخدوم ده مش موجود.', array( 'status' => 404 ) );
}
