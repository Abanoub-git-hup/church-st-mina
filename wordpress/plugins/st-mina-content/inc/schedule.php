<?php
/**
 * جدول القداسات والاجتماعات: نوع محتوى "موعد"، كل موعد لوحده (اليوم، ومن، ولحد، والنوع، وملاحظة).
 * الجدول بيتعرض في صفحة العبادة، وقسم المواعيد في الرئيسية، ودايرة "القداس القادم".
 */

defined( 'ABSPATH' ) || exit;

function stmina_days() {
	return array( 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت' );
}

add_action( 'init', function () {
	register_post_type( 'stmina_slot', array(
		'labels'             => array(
			'name'          => 'جدول المواعيد',
			'singular_name' => 'موعد',
			'add_new'       => 'إضافة موعد',
			'add_new_item'  => 'إضافة موعد جديد',
			'edit_item'     => 'تعديل الموعد',
			'all_items'     => 'كل المواعيد',
		),
		'public'             => false,
		'show_ui'            => true,
		'show_in_rest'       => true,
		'publicly_queryable' => false,
		'menu_icon'          => 'dashicons-calendar-alt',
		'menu_position'      => 4,
		'supports'           => array( 'title' ),
	) );
} );

/**
 * عنوان الموعد بيتكتب لوحده بعد الحفظ (زي: الجمعة 7:00 – 9:00 ص · التربية الكنسية)،
 * علشان قايمة المواعيد في dashboard تبقى مقروءة والمحرر مايكتبهوش بإيده.
 * acf/save_post بأولوية 20 يعني بعد ما ACF يحفظ القيم الجديدة.
 */
add_action( 'acf/save_post', function ( $post_id ) {
	if ( 'stmina_slot' !== get_post_type( $post_id ) ) {
		return;
	}
	$slot = array(
		'start' => (string) get_post_meta( $post_id, 'start', true ),
		'end'   => (string) get_post_meta( $post_id, 'end', true ),
	);
	if ( '' === $slot['start'] ) {
		return;
	}
	$days  = stmina_days();
	$title = $days[ (int) get_post_meta( $post_id, 'day', true ) ] . ' ' . stmina_slot_time( $slot );
	$note  = get_post_meta( $post_id, 'note', true );
	wp_update_post( array( 'ID' => $post_id, 'post_title' => $note ? $title . ' · ' . $note : $title ) );
}, 20 );

/**
 * المواعيد مرتّبة بالأسبوع والساعة.
 *
 * @param string $kind mass أو meeting أو '' للكل.
 * @return array كل موعد: id, day, start, end, kind, note
 */
function stmina_slots( $kind = '' ) {
	$out = array();
	foreach ( get_posts( array( 'post_type' => 'stmina_slot', 'posts_per_page' => -1 ) ) as $p ) {
		$slot = array(
			'id'    => $p->ID,
			'day'   => (int) get_post_meta( $p->ID, 'day', true ),
			'start' => (string) get_post_meta( $p->ID, 'start', true ),
			'end'   => (string) get_post_meta( $p->ID, 'end', true ),
			'kind'  => get_post_meta( $p->ID, 'kind', true ) ?: 'mass',
			'note'  => (string) get_post_meta( $p->ID, 'note', true ),
		);
		if ( '' === $slot['start'] || ( $kind && $kind !== $slot['kind'] ) ) {
			continue;
		}
		$out[] = $slot;
	}
	usort( $out, function ( $a, $b ) {
		// باليوم، وبعدين ساعة البداية، ولو بدأوا مع بعض اللي بيخلص الأول
		return array( $a['day'], $a['start'], $a['end'] ) <=> array( $b['day'], $b['start'], $b['end'] );
	} );
	return $out;
}

/**
 * المواعيد متجمّعة باليوم: array( رقم اليوم => array( المواعيد ) ).
 */
function stmina_slots_by_day( $kind = '' ) {
	$days = array();
	foreach ( stmina_slots( $kind ) as $slot ) {
		$days[ $slot['day'] ][] = $slot;
	}
	return $days;
}

/**
 * الوقت بالشكل المصري: 07:00 ← 7:00، و15:30 ← 3:30.
 */
function stmina_clock( $time ) {
	list( $h, $m ) = array_map( 'intval', explode( ':', $time . ':0' ) );
	$h12 = $h % 12 ?: 12;
	return $h12 . ':' . str_pad( (string) $m, 2, '0', STR_PAD_LEFT );
}

/**
 * ص أو ظ أو م (أو صباحًا وظهرًا ومساءً لو $long).
 */
function stmina_period( $time, $long = false ) {
	$h = (int) $time;
	if ( 12 === $h ) {
		return $long ? 'ظهرًا' : 'ظ';
	}
	return $h < 12 ? ( $long ? 'صباحًا' : 'ص' ) : ( $long ? 'مساءً' : 'م' );
}

/**
 * الموعد كنص: "7:00 – 10:00 ص"، أو "6:00 م" لو مالوش نهاية.
 * المختصر زي الجدول بيكتب الفترة مرة واحدة في الآخر. والطويل (دايرة القداس القادم) بيكتبها
 * للبداية كمان لو اختلفت: "10:00 ص – 12:00 ظهرًا".
 */
function stmina_slot_time( $slot, $long = false ) {
	$start = stmina_clock( $slot['start'] );
	if ( ! $slot['end'] ) {
		return $start . ' ' . stmina_period( $slot['start'], $long );
	}
	$end = stmina_clock( $slot['end'] ) . ' ' . stmina_period( $slot['end'], $long );
	if ( $long && stmina_period( $slot['start'] ) !== stmina_period( $slot['end'] ) ) {
		$start .= ' ' . stmina_period( $slot['start'] );
	}
	return $start . ' – ' . $end;
}
