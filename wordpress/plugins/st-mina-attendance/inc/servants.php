<?php
/**
 * إدارة الخدام (المهمة 24): القايمة لكل الخدام، والإضافة وسحب الصلاحية لمدير الموقع بس (قرار المستخدم).
 *
 * - الخادم حساب WordPress عادي بدور stmina_servant (read + stmina_attend بس)، فمايقدرش يعدّل محتوى الموقع.
 *   وصلاحية "محرر" المحتوى (Editor) بتتدّي من dashboard، ومافيهاش stmina_attend. الاتنين منفصلين.
 * - الخادم الجديد بيتعمل من غير كلمة سر يعرفها حد، ومعاه رابط دعوة على واتساب بيشتغل 3 أيام ومرة واحدة،
 *   بيعمل منه كلمة السر بنفسه (/attend/invite/<الكود>/).
 * - سحب الصلاحية بيشيل الدور ويقفل كل جلسات دخوله فورًا، والحساب نفسه بيفضل علشان السجلات اللي سجّلها
 *   تفضل باسمه.
 */

defined( 'ABSPATH' ) || exit;

const STMINA_ATT_INVITE_DAYS = 3;

/**
 * فحص صلاحية الإدارة: مدير الموقع بس.
 */
function stmina_att_can_manage() {
	$can = stmina_att_can();
	if ( true !== $can ) {
		return $can;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return new WP_Error( 'rest_forbidden', 'إضافة الخدام وسحب الصلاحية لمدير الموقع بس.', array( 'status' => 403 ) );
	}
	return true;
}

/**
 * شكل الخادم في الرد. الدعوة المعلّقة: اتعمل ولسه ماعملش كلمة السر.
 */
function stmina_att_servant_json( WP_User $u ) {
	$exp = (int) get_user_meta( $u->ID, 'stmina_invite_exp', true );
	return array(
		'id'      => $u->ID,
		'name'    => $u->display_name,
		'phone'   => (string) get_user_meta( $u->ID, 'stmina_phone', true ),
		'me'      => get_current_user_id() === $u->ID,
		'admin'   => user_can( $u, 'manage_options' ),
		'pending' => (bool) get_user_meta( $u->ID, 'stmina_invite', true ),
		'expired' => $exp && $exp < time(),
	);
}

/**
 * كل اللي معاهم صلاحية الحضور، المدير الأول وبعدين بالاسم.
 *
 * @return WP_User[]
 */
function stmina_att_servants() {
	$users = get_users( array( 'capability' => 'stmina_attend', 'orderby' => 'display_name' ) );
	usort( $users, function ( $a, $b ) {
		return (int) user_can( $b, 'manage_options' ) - (int) user_can( $a, 'manage_options' );
	} );
	return $users;
}

/**
 * دعوة جديدة: كود عشوائي، ومتخزّن منه بصمة بس (wp_hash)، فاللي يشوف قاعدة البيانات مايقدرش يستخدمه.
 *
 * @return string رابط الدعوة.
 */
function stmina_att_new_invite( $user_id ) {
	$code = wp_generate_password( 32, false );
	update_user_meta( $user_id, 'stmina_invite', wp_hash( $code ) );
	update_user_meta( $user_id, 'stmina_invite_exp', time() + STMINA_ATT_INVITE_DAYS * DAY_IN_SECONDS );
	return stmina_att_url( 'invite' ) . $code . '/';
}

/**
 * صاحب الدعوة من الكود، لو لسه شغالة: مااتستخدمتش، ومعدّتش 3 أيام، ولسه معاه الصلاحية.
 */
function stmina_att_invite_user( $code ) {
	if ( ! is_string( $code ) || ! preg_match( '/^[A-Za-z0-9]{32}$/', $code ) ) {
		return null;
	}
	$found = get_users( array( 'meta_key' => 'stmina_invite', 'meta_value' => wp_hash( $code ), 'number' => 1 ) );
	if ( ! $found ) {
		return null;
	}
	$u = $found[0];
	if ( (int) get_user_meta( $u->ID, 'stmina_invite_exp', true ) < time() || ! user_can( $u, 'stmina_attend' ) ) {
		return null;
	}
	return $u;
}

/**
 * الحساب بالموبايل (stmina_phone)، بأي دور.
 */
function stmina_att_user_by_phone( $phone ) {
	$found = get_users( array( 'meta_key' => 'stmina_phone', 'meta_value' => $phone, 'number' => 1 ) );
	return $found ? $found[0] : null;
}

add_action( 'rest_api_init', function () {
	$ns = 'stmina/v1';

	register_rest_route( $ns, '/servants', array(
		// القايمة: لكل الخدام، ومعاها هل اللي فاتح يقدر يدير ولا لأ
		array(
			'methods'             => 'GET',
			'permission_callback' => 'stmina_att_can',
			'callback'            => function () {
				return array(
					'servants'   => array_map( 'stmina_att_servant_json', stmina_att_servants() ),
					'can_manage' => current_user_can( 'manage_options' ),
				);
			},
		),
		// إضافة خادم: الاسم والموبايل، والرد فيه رابط الدعوة
		array(
			'methods'             => 'POST',
			'permission_callback' => 'stmina_att_can_manage',
			'args'                => array(
				'name'  => array( 'type' => 'string', 'required' => true ),
				'phone' => array( 'type' => 'string', 'required' => true ),
			),
			'callback'            => function ( WP_REST_Request $req ) {
				$name   = stmina_att_name( $req['name'] );
				$phone  = stmina_att_phone( $req['phone'] );
				$fields = array();
				if ( '' === $name ) {
					$fields['name'] = 'اكتب اسم الخادم.';
				}
				if ( ! $phone ) {
					$fields['phone'] = 'رقم الموبايل لازم يكون 11 رقم ويبدأ بـ 010 أو 011 أو 012 أو 015.';
				}
				$old = $phone ? stmina_att_user_by_phone( $phone ) : null;
				if ( $old && user_can( $old, 'stmina_attend' ) ) {
					$fields['phone'] = 'الرقم ده لـ' . $old->display_name . '، وهو خادم بالفعل.';
				}
				if ( $fields ) {
					return new WP_Error( 'stmina_invalid', reset( $fields ), array( 'status' => 400, 'fields' => $fields ) );
				}

				if ( $old ) {
					// كان خادم واتسحبت صلاحيته: نفس الحساب يرجع (السجلات القديمة باسمه)، بدعوة جديدة
					$old->add_role( 'stmina_servant' );
					wp_update_user( array( 'ID' => $old->ID, 'display_name' => $name ) );
					$id = $old->ID;
				} else {
					$id = wp_insert_user( array(
						'user_login'   => 'srv' . $phone,
						'user_pass'    => wp_generate_password( 32, true, true ), // محدش يعرفها، والدعوة بتغيّرها
						'display_name' => $name,
						'nickname'     => $name,
						'role'         => 'stmina_servant',
					) );
					if ( is_wp_error( $id ) ) {
						return new WP_Error( 'stmina_servant_failed', 'ماقدرناش نضيف الخادم. جرّب تاني.', array( 'status' => 500 ) );
					}
					update_user_meta( $id, 'stmina_phone', $phone );
				}
				$url = stmina_att_new_invite( $id );
				return new WP_REST_Response( stmina_att_servant_json( get_userdata( $id ) ) + array( 'invite_url' => $url ), 201 );
			},
		),
	) );

	// دعوة جديدة لخادم لسه ماقبلش (الأولى ضاعت أو عدّت 3 أيام). الدعوة القديمة بتبطل
	register_rest_route( $ns, '/servants/(?P<id>\d+)/invite', array(
		'methods'             => 'POST',
		'permission_callback' => 'stmina_att_can_manage',
		'callback'            => function ( WP_REST_Request $req ) {
			$u = get_userdata( (int) $req['id'] );
			if ( ! $u || ! user_can( $u, 'stmina_attend' ) || ! get_user_meta( $u->ID, 'stmina_invite', true ) ) {
				return new WP_Error( 'stmina_no_invite', 'الخادم ده مش مستني دعوة.', array( 'status' => 404 ) );
			}
			return stmina_att_servant_json( $u ) + array( 'invite_url' => stmina_att_new_invite( $u->ID ) );
		},
	) );

	// سحب الصلاحية: الدور بيتشال، والدعوة بتبطل، وكل جلسات دخوله بتتقفل فورًا.
	// مش لنفسك ولا لمدير الموقع
	register_rest_route( $ns, '/servants/(?P<id>\d+)', array(
		'methods'             => 'DELETE',
		'permission_callback' => 'stmina_att_can_manage',
		'callback'            => function ( WP_REST_Request $req ) {
			$u = get_userdata( (int) $req['id'] );
			if ( ! $u || ! user_can( $u, 'stmina_attend' ) ) {
				return new WP_Error( 'stmina_not_servant', 'الحساب ده مش خادم.', array( 'status' => 404 ) );
			}
			if ( get_current_user_id() === $u->ID || user_can( $u, 'manage_options' ) ) {
				return new WP_Error( 'stmina_cant_revoke', 'مينفعش تسحب صلاحية نفسك ولا صلاحية مدير الموقع.', array( 'status' => 400 ) );
			}
			$u->remove_role( 'stmina_servant' );
			$u->remove_cap( 'stmina_attend' ); // لو كانت متدّية للحساب نفسه مش للدور
			delete_user_meta( $u->ID, 'stmina_invite' );
			delete_user_meta( $u->ID, 'stmina_invite_exp' );
			WP_Session_Tokens::get_instance( $u->ID )->destroy_all();
			return array( 'id' => $u->ID, 'revoked' => true );
		},
	) );

	// قبول الدعوة: كلمة السر (8 على الأقل)، وبعدها بيدخل على طول. مفتوح من غير دخول، والكود هو الإثبات.
	// حد: 10 محاولات غلط في ربع ساعة لكل جهاز
	register_rest_route( $ns, '/invite', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'args'                => array(
			'code'     => array( 'type' => 'string', 'required' => true ),
			'password' => array( 'type' => 'string', 'required' => true ),
		),
		'callback'            => function ( WP_REST_Request $req ) {
			$key = 'stmina_invite_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
			if ( (int) get_transient( $key ) >= 10 ) {
				return new WP_Error( 'stmina_too_many', 'محاولات كتير. استنى ربع ساعة وجرّب تاني.', array( 'status' => 429 ) );
			}
			$u = stmina_att_invite_user( $req['code'] );
			if ( ! $u ) {
				set_transient( $key, (int) get_transient( $key ) + 1, 15 * MINUTE_IN_SECONDS );
				return new WP_Error( 'stmina_bad_invite', 'الدعوة دي مش شغالة. اطلب من مدير الموقع يبعتلك دعوة جديدة.', array( 'status' => 404 ) );
			}
			$pass = (string) $req['password'];
			if ( mb_strlen( $pass ) < 8 ) {
				return new WP_Error( 'stmina_short_password', '8 حروف أو أرقام على الأقل.', array( 'status' => 400, 'fields' => array( 'password' => '8 حروف أو أرقام على الأقل.' ) ) );
			}
			wp_set_password( $pass, $u->ID ); // بتقفل أي جلسات قديمة كمان
			delete_user_meta( $u->ID, 'stmina_invite' );
			delete_user_meta( $u->ID, 'stmina_invite_exp' );
			wp_set_current_user( $u->ID );
			wp_set_auth_cookie( $u->ID, true, is_ssl() );
			return array( 'redirect' => stmina_att_url( 'sessions' ) );
		},
	) );
} );
