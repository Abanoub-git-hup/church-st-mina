<?php
/**
 * قسم "المناسبة القادمة": الصورة، والعنوان، والعد التنازلي (site.js بيقرا data-date)، والميعاد والمكان.
 * بيتستخدم في الرئيسية وأول صفحة الأخبار (بزرار التفاصيل)، وفي صفحة المناسبة نفسها (من غيره).
 *
 * $args['post'] المناسبة (WP_Post).
 * $args['link'] true علشان يظهر زرار "تفاصيل المناسبة".
 * $args['class'] كلاس القسم، والافتراضي "sec pool".
 * $args['cover'] true لو ده أول قسم بعد الواجهة (data-cover-start).
 */

defined( 'ABSPATH' ) || exit;

$season = $args['post'];
$iso    = stmina_season_iso( $season );
$lines  = array_filter( array(
	'i-calendar' => get_post_meta( $season->ID, 'when_label', true ),
	'i-clock'    => get_post_meta( $season->ID, 'time_note', true ),
	'i-pin'      => get_post_meta( $season->ID, 'place', true ),
) );
$words = explode( ' ', get_the_title( $season ) );
$hl    = get_post_meta( $season->ID, 'highlight', true );
$hl    = $hl ? $hl : ( count( $words ) > 1 ? end( $words ) : '' );
$class = isset( $args['class'] ) ? $args['class'] : 'sec pool';
?>
<section class="<?php echo esc_attr( $class ); ?>"<?php echo empty( $args['cover'] ) ? '' : ' data-cover-start'; ?> aria-labelledby="t-event">
	<div class="wrap event">
		<div class="event-media" data-clip><?php echo get_the_post_thumbnail( $season, 'large', array( 'class' => 'grade', 'data-parallax' => '', 'alt' => '' ) ); ?></div>
		<div>
			<span class="eyebrow" data-rise>المناسبة القادمة</span>
			<h2 class="title" id="t-event" data-split><?php stmina_title( get_the_title( $season ), $hl ); ?></h2>
			<?php if ( $iso ) : ?>
				<div class="countdown" id="countdown" data-date="<?php echo esc_attr( $iso ); ?>" data-rise>
					<div><b data-u="d">00</b><small>يوم</small></div><div><b data-u="h">00</b><small>ساعة</small></div><div><b data-u="m">00</b><small>دقيقة</small></div><div><b data-u="s">00</b><small>ثانية</small></div>
				</div>
			<?php endif; ?>
			<?php if ( $lines ) : ?>
				<ul class="ev-meta" data-rise>
					<?php foreach ( $lines as $icon => $text ) : ?>
						<li><svg class="icon"><use href="#<?php echo esc_attr( $icon ); ?>"/></svg><?php echo esc_html( $text ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<?php if ( ! empty( $args['link'] ) ) : ?>
				<div data-rise><a class="pill" href="<?php echo esc_url( get_permalink( $season ) ); ?>">تفاصيل المناسبة <span class="dot"><svg class="icon"><use href="#i-left"/></svg></span></a></div>
			<?php endif; ?>
		</div>
	</div>
</section>
