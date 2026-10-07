<?php
/**
 * جدول المواعيد بالأيام، من نوع المحتوى "موعد" في الـ plugin.
 * site.js بيعلّم على يوم النهارده لوحده (من data-day).
 *
 * $args['kind'] mass للقداسات بس، أو meeting للاجتماعات بس، أو فاضي للاتنين مع بعض (الرئيسية).
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'stmina_slots_by_day' ) ) {
	return;
}
$kind = isset( $args['kind'] ) ? $args['kind'] : '';
$days = stmina_days();
?>
<div class="days">
	<?php foreach ( stmina_slots_by_day( $kind ) as $day => $slots ) : ?>
		<div class="day"<?php echo 'meeting' !== $kind ? ' data-day="' . (int) $day . '"' : ''; ?> data-rise>
			<div class="day-n"><?php echo esc_html( $days[ $day ] ); ?></div>
			<div class="slots">
				<?php foreach ( $slots as $slot ) : ?>
					<span<?php echo 'meeting' === $slot['kind'] ? ' class="meet"' : ''; ?>><?php echo esc_html( stmina_slot_time( $slot ) ); ?><?php if ( $slot['note'] ) : ?> <em><?php echo esc_html( $slot['note'] ); ?></em><?php endif; ?></span>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
