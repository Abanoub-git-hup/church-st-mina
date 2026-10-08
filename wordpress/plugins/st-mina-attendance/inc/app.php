<?php
/**
 * شاشات الخدام على /attend/: صفحة لكل شاشة، والبيانات كلها من REST (rest.php) بسكريبت كل شاشة.
 *
 *   /attend/login        الدخول (بالبريد أو الموبايل)
 *   /attend/ و/attend/sessions  الجلسات (أول شاشة)
 *   /attend/members      المخدومين
 *   /attend/members/12   ملف مخدوم
 *   /attend/more         المزيد: الاستيراد، وموقع الكنيسة، وكلمة السر والخروج
 *   أي شاشة تانية (الجلسات، والمسح ...)   "الشاشة دي جاية قريب" لحد ما مهمتها تتعمل
 *   /me/<الكود>/         كارت المخدوم (عام من غير دخول، ومابيدّيش أي صلاحية)
 *
 * الحماية: كل الشاشات من غير أرشفة ومن غير cache، والشاشات غير الدخول بتحوّل لصفحة الدخول
 * لو الزائر مش خادم. ودي راحة بس، والحماية الحقيقية في permission_callback بتاع كل مسار.
 */

defined( 'ABSPATH' ) || exit;

/**
 * رابط شاشة: stmina_att_url( 'members' ) ← https://…/attend/members/
 */
function stmina_att_url( $screen = '', $id = 0 ) {
	$path = 'attend/' . ( $screen ? $screen . '/' : '' ) . ( $id ? (int) $id . '/' : '' );
	return home_url( '/' . $path );
}

add_action( 'init', function () {
	add_rewrite_rule( '^attend/?$', 'index.php?stmina_screen=sessions', 'top' );
	add_rewrite_rule( '^attend/members/([0-9]+)/?$', 'index.php?stmina_screen=member&stmina_id=$matches[1]', 'top' );
	add_rewrite_rule( '^attend/session/([0-9]+)/?$', 'index.php?stmina_screen=session&stmina_id=$matches[1]', 'top' );
	add_rewrite_rule( '^attend/([a-z]+)/?$', 'index.php?stmina_screen=$matches[1]', 'top' );
	// كارت المخدوم. أي حاجة بعد /me/ بتوصل للشاشة، والكود الغلط بيظهر "الرابط ده مش شغال"
	add_rewrite_rule( '^me/([^/]+)/?$', 'index.php?stmina_screen=card&stmina_token=$matches[1]', 'top' );
	// القايمة في site.js بتودّي لـ /attend-login/ (من اسم ملف التصميم)
	add_rewrite_rule( '^attend-login/?$', 'index.php?stmina_screen=login', 'top' );
} );

add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'stmina_screen';
	$vars[] = 'stmina_id';
	$vars[] = 'stmina_token';
	return $vars;
} );

add_action( 'template_redirect', function () {
	$screen = get_query_var( 'stmina_screen' );
	if ( ! $screen ) {
		return;
	}
	nocache_headers();
	header( 'X-Robots-Tag: noindex, nofollow', true );
	if ( ! is_ssl() && wp_is_using_https() ) {
		wp_safe_redirect( set_url_scheme( home_url( add_query_arg( array() ) ), 'https' ), 301 );
		exit;
	}

	$can = current_user_can( 'stmina_attend' );
	if ( 'card' === $screen ) {
		// عامة: مفيش تحويل ولا فحص صلاحية، والصفحة نفسها مافيهاش غير الاسم والكارت
	} elseif ( 'login' === $screen ) {
		if ( $can ) {
			wp_safe_redirect( stmina_att_url( 'sessions' ) );
			exit;
		}
	} elseif ( ! $can ) {
		wp_safe_redirect( stmina_att_url( 'login' ) );
		exit;
	}

	$file = STMINA_ATT_DIR . 'screens/' . sanitize_key( $screen ) . '.php';
	status_header( 200 );
	include is_file( $file ) ? $file : STMINA_ATT_DIR . 'screens/soon.php';
	exit;
} );

/**
 * آخر كل شاشة: الإعدادات اللي السكريبت محتاجها، والسكريبت المشترك، وسكريبت الشاشة.
 * رمز nonce هو اللي بيخلّي طلبات REST من المتصفح تتحسب باسم الخادم الداخل (حماية من CSRF).
 */
function stmina_att_footer( $screen ) {
	$config = array(
		'rest'   => esc_url_raw( rest_url( 'stmina/v1/' ) ),
		'nonce'  => wp_create_nonce( 'wp_rest' ),
		'base'   => stmina_att_url(),
		'screen' => $screen,
		'id'     => (int) get_query_var( 'stmina_id' ),
		'user'   => is_user_logged_in() ? wp_get_current_user()->display_name : '',
		'svc'    => 'إعداد الخدام',
		'admin'  => current_user_can( 'manage_options' ), // زرار المسح النهائي يظهر للمدير بس
	);
	if ( 'card' === $screen ) {
		// الكارت: الاسم والرابط بس، أو null لو الكود غلط أو اتلغى
		$m              = stmina_att_member_by_token( get_query_var( 'stmina_token' ) );
		$config['card'] = $m ? array( 'name' => $m->full_name, 'url' => stmina_att_card_url( $m->qr_token ) ) : null;
		// "حضوري": نفس رد /me/<الكود> (stats.php)، مع الصفحة نفسها علشان تفتح أسرع
		$config['me']   = $m ? stmina_att_my_page( $m, stmina_att_member_service_id( $m->id ) ) : null;
		unset( $config['nonce'], $config['user'] );
	}
	$url = STMINA_ATT_URL . 'assets/';
	$ver = STMINA_ATT_VERSION;
	// العربي يفضل عربي في الصفحة، و< و> بيتحوّلوا لرموز علشان أي اسم مايقفلش وسم <script>
	echo '<script>window.STMINA_ATT = ' . wp_json_encode( $config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . ";</script>\n";
	echo '<script src="' . esc_url( $url . 'attend-app.js?ver=' . $ver ) . "\"></script>\n";
	if ( in_array( $screen, array( 'card', 'member' ), true ) ) {
		// مكتبة qrcode-generator لرسم الـ QR في المتصفح (من cdnjs زي GSAP في الـ theme)
		echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>' . "\n";
	}
	if ( 'scan' === $screen ) {
		// قراية الـ QR من الكاميرا في المتصفحات اللي مافيهاش BarcodeDetector (آيفون). مش موجودة على cdnjs
		echo '<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>' . "\n";
	}
	echo '<script src="' . esc_url( $url . $screen . '.js?ver=' . $ver ) . "\"></script>\n";
}

// ---------------------------------------------------------------- الدخول

/**
 * الحساب من البريد أو الموبايل أو اسم المستخدم.
 * الموبايل متخزّن في usermeta باسم stmina_phone، بالشكل الموحّد 01xxxxxxxxx.
 */
function stmina_att_find_user( $who ) {
	$who = trim( (string) $who );
	if ( is_email( $who ) ) {
		return get_user_by( 'email', $who );
	}
	$phone = stmina_att_phone( $who );
	if ( $phone ) {
		$found = get_users( array( 'meta_key' => 'stmina_phone', 'meta_value' => $phone, 'number' => 1 ) );
		if ( $found ) {
			return $found[0];
		}
	}
	return get_user_by( 'login', $who );
}

/**
 * حد المحاولات: 5 محاولات غلط في 15 دقيقة لكل IP. العدّاد في transient بيتمسح لوحده.
 */
function stmina_att_login_key() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return 'stmina_login_' . md5( $ip );
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'stmina/v1', '/login', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true', // الدخول نفسه مفتوح، والحماية بحد المحاولات
		'args'                => array(
			'who'      => array( 'type' => 'string', 'required' => true ),
			'password' => array( 'type' => 'string', 'required' => true ),
			'remember' => array( 'type' => 'boolean', 'default' => true ),
		),
		'callback'            => function ( WP_REST_Request $req ) {
			$key   = stmina_att_login_key();
			$fails = (int) get_transient( $key );
			if ( $fails >= 5 ) {
				return new WP_Error( 'stmina_too_many', 'محاولات غلط كتير. استنى ربع ساعة وجرّب تاني.', array( 'status' => 429 ) );
			}
			$user = stmina_att_find_user( $req['who'] );
			$ok   = $user ? wp_signon( array(
				'user_login'    => $user->user_login,
				'user_password' => $req['password'],
				'remember'      => (bool) $req['remember'],
			), is_ssl() ) : null;

			if ( ! $ok || is_wp_error( $ok ) ) {
				set_transient( $key, $fails + 1, 15 * MINUTE_IN_SECONDS );
				// نفس الرسالة في كل الحالات، علشان محدش يعرف إذا كان الحساب موجود ولا لأ
				return new WP_Error( 'stmina_bad_login', 'البريد أو رقم الموبايل أو كلمة السر غير صحيحة. راجعها وحاول تاني.', array( 'status' => 401 ) );
			}
			if ( ! user_can( $ok, 'stmina_attend' ) ) {
				wp_logout();
				return new WP_Error( 'stmina_not_servant', 'الحساب ده مالوش صلاحية الخدام. كلّم مسؤول الخدمة.', array( 'status' => 403 ) );
			}
			delete_transient( $key );
			return array( 'redirect' => stmina_att_url( 'sessions' ) );
		},
	) );

	register_rest_route( 'stmina/v1', '/logout', array(
		'methods'             => 'POST',
		'permission_callback' => 'is_user_logged_in',
		'callback'            => function () {
			wp_logout();
			return array( 'redirect' => stmina_att_url( 'login' ) );
		},
	) );

	// تغيير كلمة السر من "حسابك" في المزيد. لازم كلمة السر الحالية، علشان لو حد لقى الموبايل مفتوح
	// مايقدرش يقفل الحساب على صاحبه. ونفس حد المحاولات بتاع الدخول، بس لكل حساب.
	register_rest_route( 'stmina/v1', '/password', array(
		'methods'             => 'POST',
		'permission_callback' => function () {
			return current_user_can( 'stmina_attend' );
		},
		'args'                => array(
			'current'  => array( 'type' => 'string', 'required' => true ),
			'password' => array( 'type' => 'string', 'required' => true ),
		),
		'callback'            => function ( WP_REST_Request $req ) {
			$user  = wp_get_current_user();
			$key   = 'stmina_pw_' . $user->ID;
			$fails = (int) get_transient( $key );
			if ( $fails >= 5 ) {
				return new WP_Error( 'stmina_too_many', 'محاولات غلط كتير. استنى ربع ساعة وجرّب تاني.', array( 'status' => 429 ) );
			}
			// 400 مش 401، علشان الشاشة ماترجعش لصفحة الدخول
			if ( ! wp_check_password( $req['current'], $user->user_pass, $user->ID ) ) {
				set_transient( $key, $fails + 1, 15 * MINUTE_IN_SECONDS );
				return new WP_Error( 'stmina_bad_password', 'كلمة السر الحالية مش صح.', array(
					'status' => 400,
					'fields' => array( 'current' => 'كلمة السر الحالية مش صح.' ),
				) );
			}
			$new = (string) $req['password'];
			if ( mb_strlen( $new ) < 8 ) {
				return new WP_Error( 'stmina_short_password', '8 حروف أو أرقام على الأقل.', array(
					'status' => 400,
					'fields' => array( 'password' => '8 حروف أو أرقام على الأقل.' ),
				) );
			}
			delete_transient( $key );
			// wp_update_user بيشفّر كلمة السر، وبيعمل كوكي دخول جديدة للجهاز ده،
			// والأجهزة التانية بتخرج لوحدها لأن الكوكي القديمة متربوطة بكلمة السر القديمة
			$done = wp_update_user( array( 'ID' => $user->ID, 'user_pass' => $new ) );
			if ( is_wp_error( $done ) ) {
				return new WP_Error( 'stmina_password_failed', 'ماقدرناش نغيّر كلمة السر. جرّب تاني.', array( 'status' => 500 ) );
			}
			return array( 'ok' => true );
		},
	) );
} );

// ---------------------------------------------------------------- موبايل الخادم في صفحة حسابه

/**
 * خانة "موبايل الخادم" في صفحة الحساب في dashboard، علشان يقدر يدخل بيه.
 */
function stmina_att_phone_field( $user ) {
	if ( ! user_can( $user, 'stmina_attend' ) ) {
		return;
	}
	?>
	<h2>نظام الحضور</h2>
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="stmina_phone">موبايل الخادم</label></th>
			<td>
				<input type="tel" name="stmina_phone" id="stmina_phone" dir="ltr" class="regular-text" value="<?php echo esc_attr( get_user_meta( $user->ID, 'stmina_phone', true ) ); ?>">
				<p class="description">علشان يدخل شاشات الحضور بالموبايل بدل البريد. مثال: 01012345678</p>
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'stmina_att_phone_field' );
add_action( 'edit_user_profile', 'stmina_att_phone_field' );

/**
 * حفظ الموبايل. WordPress بيفحص nonce الصفحة قبل النقطة دي، وإحنا بنفحص الصلاحية.
 */
function stmina_att_save_phone( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) || ! isset( $_POST['stmina_phone'] ) ) {
		return;
	}
	$phone = stmina_att_phone( wp_unslash( $_POST['stmina_phone'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- بيتنضّف في stmina_att_phone
	$taken = $phone ? get_users( array( 'meta_key' => 'stmina_phone', 'meta_value' => $phone, 'exclude' => array( $user_id ), 'fields' => 'ids' ) ) : array();
	if ( ! $taken ) {
		update_user_meta( $user_id, 'stmina_phone', $phone );
	}
}
add_action( 'personal_options_update', 'stmina_att_save_phone' );
add_action( 'edit_user_profile_update', 'stmina_att_save_phone' );
