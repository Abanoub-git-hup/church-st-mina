<?php
/**
 * صفحة الآباء (/church-fathers/): صورة الآباء مع بعض، وقسم لكل أب من نوع المحتوى "أب كاهن":
 * وشه في دايرة كبيرة، وصفته، ونبذته، وملصقات أقواله (الصور المرفوعة جواه).
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	stmina_page_hero( 'church-history', array( 'الكنيسة', home_url( '/church-history/' ) ) );
	get_template_part( 'template-parts/church-subnav' );
	?>

	<section class="sec pool-l" aria-label="الآباء معًا">
		<div class="wrap"><div class="arch" data-clip style="margin-inline:auto"><img src="<?php stmina_media( 'priests/fathers-group.png' ); ?>" alt="آباء الكنيسة الثلاثة"></div></div>
	</section>

	<?php
	$priests = function_exists( 'stmina_ordered' ) ? stmina_ordered( 'stmina_priest' ) : array();
	$bg      = array( 'pool', 'deep' );
	foreach ( $priests as $i => $priest ) :
		$name  = get_the_title( $priest );
		$words = explode( ' ', $name, 2 ); // "القس" عادي، والاسم دهبي
		$face  = (int) get_post_meta( $priest->ID, 'face_image', true );
		$w     = get_post_meta( $priest->ID, 'face_w', true );
		// الوش: لو فيه أرقام قصّ نستخدمها (الصورة ملصق)، ولو لأ الصورة تملا الدايرة
		$style = $face ? 'background-image:url(' . esc_url( wp_get_attachment_image_url( $face, 'full' ) ) . ');' : '';
		if ( $face && '' !== $w ) {
			$style .= '--w:' . (float) $w . ';--x:' . (float) get_post_meta( $priest->ID, 'face_x', true ) . ';--y:' . (float) get_post_meta( $priest->ID, 'face_y', true );
		} elseif ( $face ) {
			$style .= 'background-size:cover;background-position:center';
		}
		$posters = function_exists( 'stmina_attached_images' ) ? stmina_attached_images( $priest ) : array();
		?>
		<section class="sec <?php echo esc_attr( $bg[ $i % 2 ] ); ?>"<?php echo 0 === $i ? ' data-cover-start' : ''; ?> aria-labelledby="t-f<?php echo (int) $i; ?>">
			<div class="wrap split<?php echo $i % 2 ? ' rev' : ''; ?>">
				<div class="face-wrap" data-bub><span class="face" style="<?php echo esc_attr( $style ); ?>" role="img" aria-label="<?php echo esc_attr( $name ); ?>"></span></div>
				<div>
					<span class="eyebrow" data-rise><?php echo esc_html( get_post_meta( $priest->ID, 'rank', true ) ); ?></span>
					<h2 class="title" id="t-f<?php echo (int) $i; ?>" data-split><?php stmina_title( $name, isset( $words[1] ) ? $words[1] : '' ); ?></h2>
					<?php if ( has_excerpt( $priest ) ) : ?>
						<p class="lead" data-rise><?php echo esc_html( get_the_excerpt( $priest ) ); ?></p>
					<?php endif; ?>
					<?php if ( $posters ) : ?>
						<h3 class="sub-h" data-rise>من أقواله</h3>
						<div class="posters" data-rise>
							<?php foreach ( $posters as $poster ) : $cap = wp_get_attachment_caption( $poster->ID ) ?: $name; ?>
								<button class="poster" data-lb data-cap="<?php echo esc_attr( $cap . ' · ' . $name ); ?>"><?php echo wp_get_attachment_image( $poster->ID, 'large', false, array( 'alt' => $cap, 'loading' => 'lazy' ) ); ?><span class="zoom"><svg class="icon"><use href="#i-nw"/></svg></span></button>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>
	<?php endforeach; ?>

	<?php
endwhile;

get_footer();
