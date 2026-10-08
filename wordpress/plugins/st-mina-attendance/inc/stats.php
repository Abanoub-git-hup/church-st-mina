<?php
/**
 * حساب الحضور في مكان واحد، علشان "حضوري" وملف المخدوم ولوحة الخادم يطلعوا نفس الأرقام (docs/design.md 5.1):
 * - الجلسات اللي بتتحسب: الجلسات المنتهية بس، اللي للمخدوم فيها سجل (الإنهاء بيعمل "غاب" لكل اللي
 *   كانوا متسجّلين في الخدمة يومها، فده نفسه "الجلسات المنتهية بعد تسجيله").
 * - النسبة = حضر ÷ (الجلسات − الغياب بعذر).
 * - "ورا بعض" (الشموع): الحضور من الأحدث لحد أول غياب، والغياب بعذر بيتعدّى ومابيقطعش.
 * - الغياب ورا بعض (للافتقاد): الغياب من الأحدث لحد أول حضور، والعذر بيتعدّى برضه.
 */

defined( 'ABSPATH' ) || exit;

/**
 * سجل مخدوم في الجلسات المنتهية، الأحدث الأول.
 *
 * @return object[] كل واحد: kind, session_date, status, method
 */
function stmina_att_member_log( $member_id, $service_id ) {
	global $wpdb;
	return $wpdb->get_results( $wpdb->prepare(
		'SELECT s.id AS session_id, s.kind, s.session_date, r.status, r.method
		 FROM ' . stmina_att_table( 'records' ) . ' r
		 JOIN ' . stmina_att_table( 'sessions' ) . " s ON s.id = r.session_id
		 WHERE r.member_id = %d AND s.service_id = %d AND s.status = 'closed'
		 ORDER BY s.session_date DESC, s.opened_at DESC, s.id DESC",
		$member_id,
		$service_id
	) );
}

/**
 * الأرقام لنوع نشاط أو للكل.
 *
 * @param object[] $log  من stmina_att_member_log.
 * @param string   $kind mass أو meeting … أو all.
 * @return array present, excused, total (الجلسات)، base (المقام)، pct (رقم صحيح أو null لو مفيش جلسات)
 */
function stmina_att_stats( $log, $kind = 'all' ) {
	$rows    = 'all' === $kind ? $log : array_filter( $log, function ( $x ) use ( $kind ) { return $x->kind === $kind; } );
	$present = 0;
	$excused = 0;
	foreach ( $rows as $x ) {
		if ( 'present' === $x->status ) {
			$present++;
		} elseif ( 'excused' === $x->status ) {
			$excused++;
		}
	}
	$total = count( $rows );
	$base  = $total - $excused;
	return array(
		'present' => $present,
		'excused' => $excused,
		'total'   => $total,
		'base'    => $base,
		'pct'     => $base > 0 ? (int) round( $present / $base * 100 ) : null,
	);
}

/**
 * عدد مرات الحضور ورا بعض من الأحدث (الغياب بعذر بيتعدّى).
 */
function stmina_att_streak( $log ) {
	$n = 0;
	foreach ( $log as $x ) {
		if ( 'absent' === $x->status ) {
			break;
		}
		if ( 'present' === $x->status ) {
			$n++;
		}
	}
	return $n;
}

/**
 * عدد مرات الغياب ورا بعض من الأحدث (للافتقاد). الغياب بعذر بيتعدّى، والحضور بيوقف العدّ.
 */
function stmina_att_away( $log ) {
	$n = 0;
	foreach ( $log as $x ) {
		if ( 'present' === $x->status ) {
			break;
		}
		if ( 'absent' === $x->status ) {
			$n++;
		}
	}
	return $n;
}

/**
 * نسبة كل شهر في آخر 6 شهور، الحالي الأول. والشهر اللي مافيهوش جلسات نسبته null.
 *
 * @return array[] كل واحد: month (Y-m) و pct
 */
function stmina_att_months( $log ) {
	$out  = array();
	$base = new DateTimeImmutable( current_time( 'Y-m-01' ), wp_timezone() );
	for ( $k = 0; $k < 6; $k++ ) {
		$ym    = $base->modify( "-$k months" )->format( 'Y-m' );
		$rows  = array_filter( $log, function ( $x ) use ( $ym ) { return substr( $x->session_date, 0, 7 ) === $ym; } );
		$s     = stmina_att_stats( $rows );
		$out[] = array( 'month' => $ym, 'pct' => $s['pct'] );
	}
	return $out;
}

/**
 * كل اللي صفحة "حضوري" محتاجاه، من غير أي حاجة الخدام بس يشوفوها:
 * مفيش ملاحظات، ولا طريقة التسجيل (يدوي أو مسح)، ولا بيانات حد تاني.
 */
function stmina_att_my_page( $member, $service_id ) {
	$log   = stmina_att_member_log( $member->id, $service_id );
	$kinds = array( 'all' => stmina_att_stats( $log ) );
	foreach ( array_keys( stmina_att_kinds() ) as $k ) {
		$kinds[ $k ] = stmina_att_stats( $log, $k );
	}
	$names = stmina_att_kinds();
	return array(
		'full_name'     => $member->full_name,
		'registered_at' => $member->registered_at,
		'kinds'         => $kinds,
		'streak'        => stmina_att_streak( $log ),
		'months'        => stmina_att_months( $log ),
		'last'          => array_map( function ( $x ) use ( $names ) {
			return array( 'kind' => $x->kind, 'kind_name' => $names[ $x->kind ], 'date' => $x->session_date, 'status' => $x->status );
		}, array_slice( $log, 0, 10 ) ),
	);
}

/**
 * الخدمة الظاهرة الأولى اللي المخدوم فيها (لصفحة "حضوري"، لأن الرابط مافيهوش خدمة).
 */
function stmina_att_member_service_id( $member_id ) {
	global $wpdb;
	return (int) $wpdb->get_var( $wpdb->prepare(
		'SELECT ms.service_id FROM ' . stmina_att_table( 'member_service' ) . ' ms JOIN ' . stmina_att_table( 'services' ) . ' sv ON sv.id = ms.service_id
		 WHERE ms.member_id = %d ORDER BY sv.is_hidden ASC, sv.id ASC LIMIT 1',
		$member_id
	) );
}

add_action( 'rest_api_init', function () {
	$ns = 'stmina/v1';

	// "حضوري": عامة بالكود زي الكارت، وبتاعة المخدوم ده بس
	register_rest_route( $ns, '/me/(?P<token>[A-Za-z0-9_-]{32})', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => function ( WP_REST_Request $req ) {
			$m = stmina_att_member_by_token( $req['token'] );
			if ( ! $m ) {
				return new WP_Error( 'stmina_bad_card', 'الرابط ده مش شغال.', array( 'status' => 404 ) );
			}
			return stmina_att_my_page( $m, stmina_att_member_service_id( $m->id ) );
		},
	) );

	// نفس الأرقام للخادم في ملف المخدوم، ومعاها الغياب ورا بعض وعدد التسجيل اليدوي
	register_rest_route( $ns, '/members/(?P<id>\d+)/stats', array(
		'methods'             => 'GET',
		'permission_callback' => 'stmina_att_can',
		'args'                => array( 'service' => array( 'type' => 'string', 'default' => 'i3dad', 'sanitize_callback' => 'sanitize_key' ) ),
		'callback'            => function ( WP_REST_Request $req ) {
			$m = stmina_att_req_member( $req );
			if ( is_wp_error( $m ) ) {
				return $m;
			}
			$service = stmina_att_req_service( $req );
			$log     = stmina_att_member_log( $m->id, $service->id );
			$out     = stmina_att_my_page( $m, $service->id );
			$out['away']   = stmina_att_away( $log );
			$out['manual'] = count( array_filter( $log, function ( $x ) { return 'manual' === $x->method && 'present' === $x->status; } ) );
			return $out;
		},
	) );
} );
