<?php
/**
 * صفحة خدمة واحدة: الواجهة بصورة الخدمة، ودواير المعلومات، وكروت التواصل،
 * ونبذة عن الخدمة، وصورها، وخدمات تانية في نفس المجموعة.
 * من design/service.html. اسم الملف single-{نوع المحتوى}.php.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$service = get_post();
	$group   = stmina_service_group( $service );
	$groups_url = get_post_type_archive_link( 'stmina_service' );

	// العنوان: آخر كلمة دهبي لو الاسم أكتر من كلمة (زي "اجتماع الشباب")
	$words     = explode( ' ', get_the_title() );
	$highlight = count( $words ) > 1 ? end( $words ) : '';

	$crumbs = array( array( 'الرئيسية', home_url( '/' ) ), array( 'الخدمات', $groups_url ) );
	if ( $group ) {
		$crumbs[] = array( $group->name, $groups_url . '#g-' . $group->slug );
	}
	$crumbs[] = array( get_the_title() );

	get_template_part( 'template-parts/page-hero', null, array(
		'current'   => 'services',
		'image'     => get_the_post_thumbnail_url( $service, 'full' ),
		'crumbs'    => $crumbs,
		'eyebrow'   => $group ? $group->name : '',
		'title'     => get_the_title(),
		'highlight' => $highlight,
		'lead'      => has_excerpt() ? get_the_excerpt() : '',
	) );

	// دواير المعلومات: اللي ليها قيمة بس
	$infos = array_filter( array(
		array( 'i-clock', 'الموعد', stmina_field( 'schedule' ) ),
		array( 'i-pin', 'المكان', stmina_field( 'place' ) ),
		array( 'i-church', 'الأب الكاهن', stmina_field( 'priest_name' ) ),
		array( 'i-users', 'الخادم', stmina_field( 'servant_name' ) ),
	), function ( $info ) {
		return '' !== $info[2] && null !== $info[2];
	} );
	$contacts = array_filter( array(
		array( 'الأب الكاهن', stmina_field( 'priest_name' ), stmina_field( 'priest_phone' ) ),
		array( 'الخادم', stmina_field( 'servant_name' ), stmina_field( 'servant_phone' ) ),
	), function ( $c ) {
		return $c[1] && $c[2];
	} );
	?>

	<!-- معلومات الخدمة -->
	<section class="sec pool" data-cover-start aria-labelledby="t-infos">
		<div class="wrap">
			<h2 class="sr" id="t-infos">معلومات الخدمة</h2>
			<?php if ( $infos ) : ?>
				<div class="infos">
					<?php foreach ( $infos as $info ) : ?>
						<div class="info" data-bub><span class="bi"><span class="info-tx"><svg class="icon" aria-hidden="true"><use href="#<?php echo esc_attr( $info[0] ); ?>"/></svg><small><?php echo esc_html( $info[1] ); ?></small><b><?php echo esc_html( $info[2] ); ?></b></span></span></div>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="lead" style="text-align:center">مواعيد الخدمة والمسؤول عنها بتتضاف قريبًا. للاستفسار كلّم الكنيسة على <a href="https://wa.me/201227445837" target="_blank" rel="noopener">واتساب</a>.</p>
			<?php endif; ?>
			<?php if ( $contacts ) : ?>
				<div class="contacts">
					<?php foreach ( $contacts as $c ) : ?>
						<div class="contact" data-rise>
							<div><small><?php echo esc_html( $c[0] ); ?></small><b><?php echo esc_html( $c[1] ); ?></b><a class="tel" href="tel:<?php echo esc_attr( stmina_phone_intl( $c[2] ) ); ?>" dir="ltr"><?php echo esc_html( stmina_phone_display( $c[2] ) ); ?></a></div>
							<a class="circle" href="<?php echo esc_url( 'https://wa.me/' . ltrim( stmina_phone_intl( $c[2] ), '+' ) ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( 'راسل ' . $c[1] . ' على واتساب' ); ?>"><svg class="icon"><use href="#i-whatsapp"/></svg></a>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( trim( get_the_content() ) ) : ?>
		<!-- عن الخدمة -->
		<section class="sec deep" aria-labelledby="t-about">
			<div class="wrap">
				<article class="prose">
					<span class="eyebrow" data-rise>عن الخدمة</span>
					<h2 class="title" id="t-about" data-split style="margin-top:0">مكانك <b>معنا</b></h2>
					<?php the_content(); ?>
				</article>
			</div>
		</section>
	<?php endif; ?>

	<?php
	// صور الخدمة: الصور المرفوعة جوه الخدمة نفسها، من غير الصورة الرئيسية
	$photos = get_attached_media( 'image', $service );
	unset( $photos[ get_post_thumbnail_id( $service ) ] );
	if ( $photos ) :
		$sizes = array( 'l', '', 's', '', 'l', '', 's' );
		?>
		<section class="sec pool-l" aria-labelledby="t-gal">
			<div class="wrap">
				<div style="text-align:center;margin-bottom:var(--s-12)">
					<span class="eyebrow" data-rise>من أنشطة الخدمة</span>
					<h2 class="title" id="t-gal" data-split>صور <b>الخدمة</b></h2>
				</div>
				<div class="gfield">
					<?php
					$n = 0;
					foreach ( $photos as $photo ) :
						$cap = wp_get_attachment_caption( $photo->ID ) ?: get_the_title( $photo );
						?>
						<button class="g <?php echo esc_attr( $sizes[ $n++ % count( $sizes ) ] ); ?>" data-lb data-cap="<?php echo esc_attr( $cap ); ?>" data-bub><span class="bi"><?php echo wp_get_attachment_image( $photo->ID, 'large', false, array( 'class' => 'grade', 'alt' => $cap, 'loading' => 'lazy' ) ); ?><span class="cap"><?php echo esc_html( $cap ); ?></span></span></button>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	// أحداث الخدمة: أخبار مربوطة بيها من خانة "حدث لخدمة"
	$events = function_exists( 'stmina_service_events' ) ? stmina_service_events( $service ) : array();
	if ( $events ) :
		?>
		<section class="sec pool" aria-labelledby="t-events">
			<div class="wrap">
				<div style="text-align:center;margin-bottom:var(--s-12)">
					<span class="eyebrow" data-rise>رحلات ومؤتمرات وأمسيات</span>
					<h2 class="title" id="t-events" data-split>أحداث <b>الخدمة</b></h2>
				</div>
				<div class="cards">
					<?php
					foreach ( $events as $event ) {
						get_template_part( 'template-parts/news-card', null, array( 'post' => $event ) );
					}
					?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	$others = $group ?stmina_services( $group, array( $service->ID ) ) : array();
	if ( $others ) :
		?>
		<!-- خدمات تانية في نفس المجموعة -->
		<section class="sec deep" aria-labelledby="t-more">
			<div class="wrap">
				<div class="more-head">
					<div>
						<span class="eyebrow" data-rise>في نفس المجموعة</span>
						<h2 class="title" id="t-more" data-split>خدمات <b>أخرى</b></h2>
					</div>
					<a class="link" href="<?php echo esc_url( $groups_url ); ?>" data-rise>كل الخدمات <svg class="icon"><use href="#i-nw"/></svg></a>
				</div>
				<div class="svc-grid" style="margin-top:0">
					<?php
					foreach ( $others as $other ) {
						get_template_part( 'template-parts/service-card', null, array( 'post' => $other ) );
					}
					?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
endwhile;

get_footer();
