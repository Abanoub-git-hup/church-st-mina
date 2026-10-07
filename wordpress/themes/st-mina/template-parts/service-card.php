<?php
/**
 * كارت خدمة: الصورة، واسم المجموعة والموعد المختصر، واسم الخدمة.
 * بيتستخدم في الرئيسية وصفحة الخدمات وصفحة الخدمة ("في نفس المجموعة").
 *
 * $args['post'] الخدمة (WP_Post).
 */

defined( 'ABSPATH' ) || exit;

$service = $args['post'];
$group   = stmina_service_group( $service );
$fit     = stmina_field( 'image_fit', $service );
$short   = stmina_field( 'schedule_short', $service );
$label   = $group ? $group->name : '';
if ( $short ) {
	$label .= ' · ' . $short;
}
$classes = array( 'svc' );
if ( in_array( $fit, array( 'top', 'paper' ), true ) ) {
	$classes[] = $fit;
}
if ( stmina_field( 'temp_image', $service ) ) {
	$classes[] = 'temp';
}
// الصور العادية بيتعملها فلتر دافي (grade)، والملصقات واللوجوهات لأ
$img_class = 'cover' === $fit || ! $fit ? 'grade' : '';
?>
<a class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-g="<?php echo esc_attr( $group ? $group->slug : '' ); ?>" href="<?php echo esc_url( get_permalink( $service ) ); ?>" data-rise>
	<?php if ( stmina_field( 'is_new', $service ) ) : ?><span class="new">جديد</span><?php endif; ?>
	<?php echo get_the_post_thumbnail( $service, 'large', array( 'class' => $img_class, 'alt' => '', 'loading' => 'lazy' ) ); ?>
	<span class="arrow"><svg class="icon"><use href="#i-nw"/></svg></span>
	<?php if ( stmina_field( 'temp_image', $service ) ) : ?><em class="tmp">صورة مؤقتة</em><?php endif; ?>
	<small><?php echo esc_html( $label ); ?></small>
	<h3><?php echo esc_html( get_the_title( $service ) ); ?></h3>
</a>
