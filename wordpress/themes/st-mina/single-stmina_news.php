<?php
/**
 * صفحة خبر واحد (/news/اسمه/)، وليها شكلين:
 * - خبر أو إعلان: الملصق الكبير (بيكبر في عارض الصور)، والميعاد والمكان، والتفاصيل، والمشاركة. من design/news-item.html.
 * - مناسبة موسمية: العد التنازلي، والآية بالشمعة، ومواعيد الموسم. من design/season.html.
 * وفي الآخر أحدث 3 أخبار.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$news      = get_post();
	$is_season = stmina_news_flag( $news, 'is_season' );
	$kind      = get_post_meta( $news->ID, 'kind', true ) === 'news' ? 'خبر' : 'إعلان';
	$when      = get_post_meta( $news->ID, 'when_label', true );
	$place     = get_post_meta( $news->ID, 'place', true );
	$news_page = get_page_by_path( 'news' );
	$news_url  = home_url( '/news/' );

	$words = explode( ' ', get_the_title() );
	$hl    = get_post_meta( $news->ID, 'highlight', true );

	get_template_part( 'template-parts/page-hero', null, array(
		'current'   => 'news',
		'image'     => $news_page ? get_the_post_thumbnail_url( $news_page, 'full' ) : '',
		'crumbs'    => array( array( 'الرئيسية', home_url( '/' ) ), array( 'الأخبار', $news_url ), array( get_the_title() ) ),
		'eyebrow'   => $is_season ? 'مناسبة موسمية' : ( $when ? $kind . ' · ' . $when : $kind ),
		'title'     => get_the_title(),
		'highlight' => $hl ? $hl : ( count( $words ) > 1 ? end( $words ) : '' ),
		'lead'      => has_excerpt() ? get_the_excerpt() : '',
	) );

	if ( $is_season ) :
		get_template_part( 'template-parts/season-event', null, array( 'post' => $news, 'cover' => true ) );

		$verse = trim( (string) get_post_meta( $news->ID, 'verse', true ) );
		if ( $verse ) :
			$verse_ref = get_post_meta( $news->ID, 'verse_ref', true );
			$label     = get_post_meta( $news->ID, 'verse_label', true );
			?>
			<section class="has-bg candle-sec" aria-label="<?php echo esc_attr( $label ? $label : 'آية المناسبة' ); ?>">
				<div class="sec-bg" aria-hidden="true"><img src="<?php stmina_media( 'church/bg-praying-light.jpg' ); ?>" alt="" loading="lazy"></div>
				<span class="flame-glow" aria-hidden="true"></span>
				<div class="wrap">
					<div class="candle" aria-hidden="true" data-rise></div>
					<?php if ( $label ) : ?><span class="eyebrow" data-rise><?php echo esc_html( $label ); ?></span><?php endif; ?>
					<blockquote data-split><?php echo esc_html( $verse ); ?></blockquote>
					<?php if ( $verse_ref ) : ?><cite data-rise>(<?php echo esc_html( $verse_ref ); ?>)</cite><?php endif; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php
		$rows = stmina_season_rows( $news );
		if ( $rows || trim( $news->post_content ) ) :
			$rows_title = get_post_meta( $news->ID, 'rows_title', true );
			?>
			<section class="sec pool-l" aria-labelledby="t-sea">
				<div class="wrap" style="max-width:860px">
					<span class="eyebrow" data-rise>مواعيد الموسم</span>
					<h2 class="title" id="t-sea" data-split><?php stmina_title( $rows_title ? $rows_title : 'مواعيد الموسم', get_post_meta( $news->ID, 'rows_highlight', true ) ); ?></h2>
					<?php if ( $rows ) : ?>
						<div class="days">
							<?php foreach ( $rows as $row ) : ?>
								<div class="day" data-rise><div class="day-n"><?php echo esc_html( $row['name'] ); ?></div><div class="slots"><span><?php echo esc_html( $row['when'] ); ?><?php if ( $row['note'] ) : ?> <em><?php echo esc_html( $row['note'] ); ?></em><?php endif; ?></span></div></div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					<?php if ( trim( $news->post_content ) ) : ?>
						<div class="lead season-text" data-rise><?php the_content(); ?></div>
					<?php endif; ?>
				</div>
			</section>
		<?php endif; ?>

	<?php else : ?>

		<section class="sec pool" data-cover-start aria-label="<?php echo esc_attr( 'تفاصيل ال' . $kind ); ?>">
			<div class="wrap item<?php echo has_post_thumbnail() ? '' : ' no-img'; ?>">
				<?php if ( has_post_thumbnail() ) : ?>
					<button class="poster-big" data-lb data-cap="<?php echo esc_attr( get_the_title() ); ?>" data-clip><?php the_post_thumbnail( 'full', array( 'alt' => get_the_title() ) ); ?><span class="zoom"><svg class="icon"><use href="#i-nw"/></svg>كبّر الصورة</span></button>
				<?php endif; ?>
				<article class="prose">
					<span class="eyebrow" data-rise><?php echo esc_html( 'تفاصيل ال' . $kind ); ?></span>
					<?php if ( $when || $place ) : ?>
						<ul class="ev-meta" data-rise style="margin-top:var(--s-2)">
							<?php if ( $when ) : ?><li><svg class="icon"><use href="#i-calendar"/></svg><?php echo esc_html( $when ); ?></li><?php endif; ?>
							<?php if ( $place ) : ?><li><svg class="icon"><use href="#i-pin"/></svg><?php echo esc_html( $place ); ?></li><?php endif; ?>
						</ul>
					<?php endif; ?>
					<?php
					if ( trim( $news->post_content ) ) {
						the_content();
					} elseif ( has_excerpt() ) {
						echo '<p data-rise>' . esc_html( get_the_excerpt() ) . '</p>';
					}
					// روابط المشاركة: النص والرابط في الـ URL بعد rawurlencode
					$share = rawurlencode( get_permalink() );
					?>
					<div class="share" data-rise>
						<span><?php echo esc_html( 'شارك ال' . $kind ); ?></span>
						<a class="circle" href="<?php echo esc_url( 'https://wa.me/?text=' . rawurlencode( get_the_title() . ' ' ) . $share ); ?>" target="_blank" rel="noopener" aria-label="مشاركة على واتساب"><svg class="icon"><use href="#i-whatsapp"/></svg></a>
						<a class="circle" href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . $share ); ?>" target="_blank" rel="noopener" aria-label="مشاركة على فيسبوك"><svg class="icon"><use href="#i-facebook"/></svg></a>
					</div>
				</article>
			</div>
		</section>

	<?php endif; ?>

	<?php
	$more = stmina_news( array( 'numberposts' => 3, 'post__not_in' => array( $news->ID ) ) );
	if ( $more ) :
		?>
		<section class="sec deep" aria-labelledby="t-more">
			<div class="wrap">
				<div class="more-head">
					<div><span class="eyebrow" data-rise><?php echo $is_season ? 'الأخبار' : 'اقرأ أيضًا'; ?></span><h2 class="title" id="t-more" data-split><?php echo $is_season ? 'آخر <b>الأخبار</b>' : 'أخبار <b>أخرى</b>'; ?></h2></div>
					<a class="link" href="<?php echo esc_url( $news_url ); ?>" data-rise>كل الأخبار <svg class="icon"><use href="#i-nw"/></svg></a>
				</div>
				<div class="cards">
					<?php
					foreach ( $more as $n ) {
						get_template_part( 'template-parts/news-card', null, array( 'post' => $n ) );
					}
					?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
endwhile;

get_footer();
