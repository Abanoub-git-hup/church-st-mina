<?php
/**
 * صفحة الصور (/church-gallery/): فلتر بتصنيفات الصور، وكل صور الألبومات في شبكة دواير.
 * كل صورة بتاخد تصنيف الألبوم بتاعها، والفلتر والعارض شغالين من site.js.
 */

defined( 'ABSPATH' ) || exit;

get_header();

// كل صور الألبومات بالترتيب، ومع كل صورة تصنيف ألبومها
$photos = array();
$used   = array();
$albums = function_exists( 'stmina_ordered' ) ? stmina_ordered( 'stmina_album' ) : array();
foreach ( $albums as $album ) {
	$terms = get_the_terms( $album, 'stmina_album_cat' );
	$term  = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
	foreach ( stmina_attached_images( $album ) as $img ) {
		$photos[] = array( $img, $term, $album );
		if ( $term ) {
			$used[ $term->slug ] = $term;
		}
	}
}

while ( have_posts() ) :
	the_post();
	stmina_page_hero( 'church-history', array( 'الكنيسة', home_url( '/church-history/' ) ) );
	get_template_part( 'template-parts/church-subnav' );
	?>

	<section class="sec pool" data-cover-start aria-label="الصور">
		<div class="wrap">
			<?php if ( $used ) : ?>
				<div class="gal-chips" role="group" aria-label="تصفية الصور" data-rise>
					<button class="chip" data-gf="all" aria-pressed="true">الكل <small>(<?php echo (int) count( $photos ); ?>)</small></button>
					<?php foreach ( $used as $term ) : ?>
						<button class="chip" data-gf="<?php echo esc_attr( $term->slug ); ?>" aria-pressed="false"><?php echo esc_html( $term->name ); ?></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<div class="gfield">
				<?php
				$sizes = array( 'l', '', 's', '', '', 's' ); // تبادل أحجام الدواير زي التصميم
				foreach ( $photos as $n => $item ) :
					list( $img, $term, $album ) = $item;
					$cap = wp_get_attachment_caption( $img->ID ) ?: get_the_title( $album );
					?>
					<button class="g <?php echo esc_attr( $sizes[ $n % count( $sizes ) ] ); ?>" data-c="<?php echo esc_attr( $term ? $term->slug : '' ); ?>" data-lb data-cap="<?php echo esc_attr( $cap ); ?>" data-bub><span class="bi"><?php echo wp_get_attachment_image( $img->ID, 'large', false, array( 'class' => 'grade', 'alt' => $cap, 'loading' => 'lazy' ) ); ?><span class="cap"><?php echo esc_html( $cap ); ?><?php if ( $term ) : ?><small><?php echo esc_html( $term->name ); ?></small><?php endif; ?></span></span></button>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php
endwhile;

get_footer();
