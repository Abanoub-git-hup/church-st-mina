<?php
/**
 * واجهة الصفحة الداخلية (80% من الشاشة): صورة، وأشعة، ورأس الصفحة، ومسار التنقل، والعنوان.
 * من design/_inner.html.
 *
 * $args:
 *   current   القسم الحالي في القايمة (زي services)
 *   image     رابط صورة الواجهة
 *   crumbs    مسار التنقل: array( array( 'الاسم', 'الرابط' ), ... ) والأخير من غير رابط
 *   eyebrow   الكلمة الصغيرة فوق العنوان
 *   title     العنوان
 *   highlight الجزء الدهبي في آخر العنوان (اختياري)
 *   lead      سطر الوصف (اختياري)
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args, array(
	'current'   => '',
	'image'     => '',
	'crumbs'    => array(),
	'eyebrow'   => '',
	'title'     => '',
	'highlight' => '',
	'lead'      => '',
) );
?>
<section class="page-hero" aria-labelledby="t-page">
	<?php if ( $args['image'] ) : ?>
		<div class="hero-bg"><img src="<?php echo esc_url( $args['image'] ); ?>" alt=""></div>
	<?php endif; ?>
	<div class="rays" aria-hidden="true"><span class="window"></span><span class="ray"></span><span class="ray"></span><span class="ray"></span><span class="ray"></span><span class="ray"></span></div>
	<canvas id="dust" aria-hidden="true"></canvas>

	<?php get_template_part( 'template-parts/hero-top', null, array( 'current' => $args['current'] ) ); ?>

	<div class="wrap page-head">
		<?php if ( $args['crumbs'] ) : ?>
			<nav class="crumbs" aria-label="مسار التنقل" data-intro>
				<ol>
					<?php foreach ( $args['crumbs'] as $crumb ) : ?>
						<li><?php if ( ! empty( $crumb[1] ) ) : ?><a href="<?php echo esc_url( $crumb[1] ); ?>"><?php echo esc_html( $crumb[0] ); ?></a><?php else : ?><span aria-current="page"><?php echo esc_html( $crumb[0] ); ?></span><?php endif; ?></li>
					<?php endforeach; ?>
				</ol>
			</nav>
		<?php endif; ?>
		<?php if ( $args['eyebrow'] ) : ?>
			<span class="eyebrow" data-intro><?php echo esc_html( $args['eyebrow'] ); ?></span>
		<?php endif; ?>
		<h1 class="title" id="t-page" data-split><?php stmina_title( $args['title'], $args['highlight'] ); ?></h1>
		<?php if ( $args['lead'] ) : ?>
			<p class="lead" data-intro><?php echo esc_html( $args['lead'] ); ?></p>
		<?php endif; ?>
	</div>
</section>
