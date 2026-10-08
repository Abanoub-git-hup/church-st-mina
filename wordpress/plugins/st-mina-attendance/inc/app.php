<?php
/**
 * شاشات الخدام على /attend/: صفحة لكل شاشة، والبيانات كلها من REST (rest.php) بسكريبت كل شاشة.
 *
 *   /attend/login        الدخول (بالبريد أو الموبايل)
 *   /attend/ و/attend/sessions  الجلسات (أول شاشة)
 *   /attend/members      المخدومين
 *   /attend/members/12   ملف مخدوم
 *   /attend/more         المزيد: الاستيراد، وموقع الكنيسة، وكلمة السر والخروج
 *   /attend/dashboard    لوحة الخادم: حضور الكل وكل مخدوم، وتنزيل الجدول Excel
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
	// دعوة خادم جديد (servants.php): /attend/invite/<الكود>/
	add_rewrite_rule( '^attend/invite/([A-Za-z0-9]+)/?$', 'index.php?stmina_screen=invite&stmina_token=$matches[1]', 'top' );
	add_rewrite_rule( '^attend/([a-z]+)/?$', 'index.php?stmina_screen=$matches[1]', 'top' );
	// كارت المخدوم. أي حاجة بعد /me/ بتوصل للشاشة، والكود الغلط بيظهر "الرابط ده مش شغال"
	add_rewrite_rule( '^me/([^/]+)/?$', 'index.php?stmina_screen=card&stmina_token=$matches[1]', 'top' );
	// تثبيت "حضوري" على الموبايل (المهمة 19): ملف manifest لكل مخدوم (لأن بداية التطبيق رابطه هو)،
	// وservice worker واحد من جذر الموقع علشان يقدر يتحكم في كل اللي تحت /me/
	add_rewrite_rule( '^me/([^/]+)/manifest\.webmanifest$', 'index.php?stmina_screen=manifest&stmina_token=$matches[1]', 'top' );
	add_rewrite_rule( '^me-sw\.js$', 'index.php?stmina_screen=sw', 'top' );
	// تطبيق الخدام "إعداد الخدام" (المهمة 21): نفس ملف الـ service worker بمجال /attend/، وmanifest واحد للكل
	add_rewrite_rule( '^attend-sw\.js$', 'index.php?stmina_screen=sw', 'top' );
	add_rewrite_rule( '^attend/manifest\.webmanifest$', 'index.php?stmina_screen=attmanifest', 'top' );
	// القايمة في site.js بتودّي لـ /attend-login/ (من اسم ملف التصميم)
	add_rewrite_rule( '^attend-login/?$', 'index.php?stmina_screen=login', 'top' );
} );

// WordPress بيزوّد / في آخر أي رابط (redirect_canonical)، فـ /me-sw.js بتتحوّل لـ /me-sw.js/.
// والمتصفح بيرفض أي service worker وراه تحويل، فبنقفل التحويل للملفين دول بس
add_filter( 'redirect_canonical', function ( $redirect ) {
	return in_array( get_query_var( 'stmina_screen' ), array( 'sw', 'manifest', 'attmanifest' ), true ) ? false : $redirect;
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
	if ( 'manifest' === $screen ) {
		stmina_att_manifest();
		exit;
	}
	if ( 'attmanifest' === $screen ) {
		stmina_att_servant_manifest();
		exit;
	}
	if ( 'sw' === $screen ) {
		stmina_att_service_worker();
		exit;
	}
	nocache_headers();
	header( 'X-Robots-Tag: noindex, nofollow', true );
	if ( ! is_ssl() && wp_is_using_https() ) {
		wp_safe_redirect( set_url_scheme( home_url( add_query_arg( array() ) ), 'https' ), 301 );
		exit;
	}

	$can = current_user_can( 'stmina_attend' );
	if ( 'card' === $screen || 'invite' === $screen ) {
		// عامة: مفيش تحويل ولا فحص صلاحية. الكارت فيه الاسم والكارت بس، والدعوة فيها اسم الخادم بس
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
		'ajax'   => admin_url( 'admin-ajax.php' ), // لرمز nonce جديد لو القديم باظ (attend-app.js)
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
		// والرقم السري: موجود ولا لأ بس (الرقم نفسه متشفّر ومابيطلعش)
		$config['card'] = $m ? array( 'name' => $m->full_name, 'url' => stmina_att_card_url( $m->qr_token ), 'token' => $m->qr_token, 'has_pin' => ! empty( $m->pin_hash ) ) : null;
		// "حضوري": نفس رد /me/<الكود> (stats.php)، مع الصفحة نفسها علشان تفتح أسرع
		$config['me']   = $m ? stmina_att_my_page( $m, stmina_att_member_service_id( $m->id ) ) : null;
		// الـ service worker ومجاله (/me/). رقم النسخة في الرابط هو اسم النسخة المحفوظة على الموبايل
		$config['sw']      = add_query_arg( 'v', STMINA_ATT_VERSION, home_url( '/me-sw.js' ) );
		$config['swScope'] = wp_parse_url( home_url( '/me/' ), PHP_URL_PATH );
		unset( $config['nonce'], $config['user'] );
	}
	if ( 'invite' === $screen ) {
		// اسم صاحب الدعوة لو لسه شغالة، أو null. والكود نفسه السكريبت بياخده من الرابط
		$u                = stmina_att_invite_user( get_query_var( 'stmina_token' ) );
		$config['invite'] = $u ? array( 'name' => $u->display_name, 'code' => get_query_var( 'stmina_token' ) ) : null;
		unset( $config['user'] );
	}
	if ( ! in_array( $screen, array( 'card', 'login', 'invite' ), true ) ) {
		$config['sw']      = add_query_arg( 'v', STMINA_ATT_VERSION, home_url( '/attend-sw.js' ) );
		$config['swScope'] = wp_parse_url( stmina_att_url(), PHP_URL_PATH );
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
		<?php if ( current_user_can( 'manage_options' ) ) : ?>
		<tr>
			<th>حساب اختبار</th>
			<td>
				<label><input type="checkbox" name="stmina_test" value="1" <?php checked( (bool) get_user_meta( $user->ID, 'stmina_test', true ) ); ?>> الحساب ده للاختبارات الأوتوماتيك بس</label>
				<p class="description">بيستخبى من قايمة الخدام في "المزيد"، علشان صلاحيته ماتتسحبش بالغلط. والاختبارات بتفضل شغالة بيه عادي.</p>
			</td>
		</tr>
		<?php endif; ?>
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
	// علامة حساب الاختبار: مدير الموقع بس (servants.php بيخبّي الحسابات دي من القايمة)
	if ( current_user_can( 'manage_options' ) ) {
		if ( ! empty( $_POST['stmina_test'] ) ) {
			update_user_meta( $user_id, 'stmina_test', 1 );
		} else {
			delete_user_meta( $user_id, 'stmina_test' );
		}
	}
}
add_action( 'personal_options_update', 'stmina_att_save_phone' );
add_action( 'edit_user_profile_update', 'stmina_att_save_phone' );

// ---------------------------------------------------------------- تثبيت "حضوري" على الموبايل (PWA)

/**
 * رقم قواعد الروابط. أي تغيير في add_rewrite_rule فوق يزوّده، فالروابط تتحدّث لوحدها (schema.php).
 */
define( 'STMINA_ATT_RULES', '4' ); // 3: دعوة الخادم، و4: تطبيق الخدام من غير نت

/**
 * وسوم التثبيت في head صفحة "حضوري". بتتحط من أداة التحويل في screens/card.php.
 * من غير كود صح مفيش manifest، فالرابط الغلط مايتثبّتش.
 */
function stmina_att_card_head() {
	$m = stmina_att_member_by_token( get_query_var( 'stmina_token' ) );
	if ( ! $m ) {
		return;
	}
	echo '<link rel="manifest" href="' . esc_url( stmina_att_card_url( $m->qr_token ) . 'manifest.webmanifest' ) . "\">\n";
	// آيفون مابيقراش أيقونات الـ manifest، فبياخد دي
	echo '<link rel="apple-touch-icon" href="' . esc_url( STMINA_ATT_URL . 'assets/icon-192.png' ) . "\">\n";
	echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
	echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
	echo '<meta name="apple-mobile-web-app-title" content="حضوري">' . "\n";
	echo '<meta name="apple-mobile-web-app-status-bar-style" content="black">' . "\n";
}

/**
 * ملف manifest لكل مخدوم: التطبيق بيفتح على رابطه هو. وid مختلف لكل مخدوم،
 * فلو اتنين إخوات على نفس الموبايل، كل واحد يقدر يثبّت صفحته.
 */
function stmina_att_manifest() {
	$m = stmina_att_member_by_token( get_query_var( 'stmina_token' ) );
	if ( ! $m ) {
		status_header( 404 );
		return;
	}
	$url  = stmina_att_card_url( $m->qr_token );
	$icon = STMINA_ATT_URL . 'assets/icon-';
	status_header( 200 );
	nocache_headers();
	header( 'Content-Type: application/manifest+json; charset=utf-8' );
	header( 'X-Robots-Tag: noindex, nofollow', true );
	echo wp_json_encode( array(
		'id'               => $url,
		'name'             => 'حضوري',
		'short_name'       => 'حضوري',
		'description'      => 'حضورك وكارتك في خدمة إعداد الخدام',
		'lang'             => 'ar',
		'dir'              => 'rtl',
		'start_url'        => $url,
		'scope'            => $url,
		'display'          => 'standalone',
		'orientation'      => 'portrait',
		'background_color' => '#2a1c12',
		'theme_color'      => '#2a1c12',
		'icons'            => array(
			array( 'src' => $icon . '192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any' ),
			array( 'src' => $icon . '512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any' ),
			// اللوجو في النص بمسافة حواليه، فينفع الموبايل يقصّه دايرة أو مربع بحواف
			array( 'src' => $icon . '512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable' ),
		),
	), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
}

/**
 * الـ service worker من جذر الموقع (/me-sw.js)، علشان مجاله يغطي /me/.
 * لو اتقدّم من فولدر الـ plugin، مجاله هيبقى الفولدر ده بس. والملف نفسه في assets/me-sw.js.
 */
function stmina_att_service_worker() {
	status_header( 200 );
	header( 'Content-Type: application/javascript; charset=utf-8' );
	header( 'Cache-Control: no-cache' ); // المتصفح يسأل كل مرة، فالنسخة الجديدة توصل بسرعة
	header( 'X-Robots-Tag: noindex, nofollow', true );
	readfile( STMINA_ATT_DIR . 'assets/me-sw.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- ملف ثابت من الـ plugin
}

// ---------------------------------------------------------------- دخول المخدوم بالموبايل والرقم السري

/**
 * الرقم السري: 4 أرقام بالظبط (والأرقام العربي بتتحوّل). أو '' لو الشكل غلط.
 */
function stmina_att_pin( $raw ) {
	$ar  = array( '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9' );
	$pin = strtr( preg_replace( '/\s+/u', '', (string) $raw ), $ar );
	return preg_match( '/^\d{4}$/', $pin ) ? $pin : '';
}

/**
 * عدّاد محاولات غلط في transient. بيرجّع true لو الحد اتعدّى.
 */
function stmina_att_too_many( $key, $max ) {
	return (int) get_transient( $key ) >= $max;
}
function stmina_att_fail( $key, $ttl ) {
	set_transient( $key, (int) get_transient( $key ) + 1, $ttl );
}

add_action( 'rest_api_init', function () {
	$ns = 'stmina/v1';

	// المخدوم بيعمل رقمه السري من صفحته. الكود اللي في الرابط هو الإثبات إنه صاحبها،
	// زي ما هو الإثبات لشوفة الصفحة نفسها. والرقم بيتحفظ متشفّر زي كلمات سر WordPress
	register_rest_route( $ns, '/me/(?P<token>[A-Za-z0-9_-]{32})/pin', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'args'                => array( 'pin' => array( 'type' => 'string', 'required' => true ) ),
		'callback'            => function ( WP_REST_Request $req ) {
			global $wpdb;
			$m = stmina_att_member_by_token( $req['token'] );
			if ( ! $m ) {
				return new WP_Error( 'stmina_bad_card', 'الرابط ده مش شغال.', array( 'status' => 404 ) );
			}
			$pin = stmina_att_pin( $req['pin'] );
			if ( '' === $pin ) {
				return new WP_Error( 'stmina_bad_pin', 'الرقم السري 4 أرقام.', array( 'status' => 400, 'fields' => array( 'pin' => 'الرقم السري 4 أرقام.' ) ) );
			}
			$wpdb->update( stmina_att_table( 'members' ), array( 'pin_hash' => wp_hash_password( $pin ) ), array( 'id' => $m->id ) );
			return array( 'has_pin' => true );
		},
	) );

	// دخول المخدوم: الموبايل والرقم السري ← رابط صفحته. مفيش كوكي ولا جلسة، الرابط نفسه هو المفتاح.
	// الحد: 5 غلط في ربع ساعة لكل IP، و10 غلط في الساعة لكل موبايل (4 أرقام سهل تتجرّب من كذا جهاز)
	register_rest_route( $ns, '/member-login', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'args'                => array(
			'phone' => array( 'type' => 'string', 'required' => true ),
			'pin'   => array( 'type' => 'string', 'required' => true ),
		),
		'callback'            => function ( WP_REST_Request $req ) {
			global $wpdb;
			$ip_key = 'stmina_mlogin_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
			$phone  = stmina_att_phone( $req['phone'] );
			$ph_key = 'stmina_mphone_' . md5( (string) $phone );
			if ( stmina_att_too_many( $ip_key, 5 ) || ( $phone && stmina_att_too_many( $ph_key, 10 ) ) ) {
				return new WP_Error( 'stmina_too_many', 'محاولات غلط كتير. استنى شوية وجرّب تاني، أو اطلب الرابط من خادم الخدمة.', array( 'status' => 429 ) );
			}
			$m   = $phone ? $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . stmina_att_table( 'members' ) . ' WHERE phone = %s', $phone ) ) : null;
			$pin = stmina_att_pin( $req['pin'] );
			// نفس الرسالة لأي غلط: الرقم مش متسجّل، أو مالوش رقم سري، أو الرقم السري غلط
			if ( ! $m || empty( $m->pin_hash ) || '' === $pin || ! wp_check_password( $pin, $m->pin_hash ) ) {
				stmina_att_fail( $ip_key, 15 * MINUTE_IN_SECONDS );
				if ( $phone ) {
					stmina_att_fail( $ph_key, HOUR_IN_SECONDS );
				}
				return new WP_Error( 'stmina_bad_member_login', 'رقم الموبايل أو الرقم السري مش صح. راجعهم وحاول تاني.', array( 'status' => 401 ) );
			}
			delete_transient( $ip_key );
			delete_transient( $ph_key );
			return array( 'redirect' => stmina_att_card_url( $m->qr_token ) );
		},
	) );
} );

// ---------------------------------------------------------------- تطبيق الخدام من غير نت (المهمة 21)

/**
 * وسوم التثبيت في head شاشات الخدام. بتتحط من أداة التحويل (قايمة HEAD).
 */
function stmina_att_servant_head() {
	echo '<link rel="manifest" href="' . esc_url( stmina_att_url() . 'manifest.webmanifest' ) . "\">\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( STMINA_ATT_URL . 'assets/icon-192.png' ) . "\">\n";
	echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
	echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
	echo '<meta name="apple-mobile-web-app-title" content="إعداد الخدام">' . "\n";
}

/**
 * manifest تطبيق الخدام: بيفتح على شاشة المسح، لأنها اللي بتشتغل من غير نت.
 */
function stmina_att_servant_manifest() {
	$icon = STMINA_ATT_URL . 'assets/icon-';
	status_header( 200 );
	nocache_headers();
	header( 'Content-Type: application/manifest+json; charset=utf-8' );
	header( 'X-Robots-Tag: noindex, nofollow', true );
	echo wp_json_encode( array(
		'id'               => stmina_att_url(),
		'name'             => 'إعداد الخدام',
		'short_name'       => 'إعداد الخدام',
		'description'      => 'جلسات الحضور والمسح لخدمة إعداد الخدام',
		'lang'             => 'ar',
		'dir'              => 'rtl',
		'start_url'        => stmina_att_url( 'scan' ),
		'scope'            => stmina_att_url(),
		'display'          => 'standalone',
		'orientation'      => 'portrait',
		'background_color' => '#2a1c12',
		'theme_color'      => '#2a1c12',
		'icons'            => array(
			array( 'src' => $icon . '192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any' ),
			array( 'src' => $icon . '512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any' ),
			array( 'src' => $icon . '512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable' ),
		),
	), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
}
