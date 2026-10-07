<?php
/**
 * صفحة الأخبار (/news/): أقرب مناسبة موسمية بالعد التنازلي، وبعدين الأخبار والإعلانات
 * بفلتر (site.js)، والمثبّت منها في دواير فوق. أول 6 كروت ظاهرين والباقي ورا "عرض المزيد" (news.js).
 * من design/news.html.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	stmina_page_hero( 'news' );

	$season = stmina_next_season();
	if ( $season ) {
		get_template_part( 'template-parts/season-event', null, array( 'post' => $season, 'link' => true, 'cover' => true ) );
	}

	list( $pinned, $rest ) = stmina_news_split();
	$shown = 6;
	?>

	<section class="sec <?php echo $season ? 'deep' : 'pool'; ?>"<?php echo $season ? '' : ' data-cover-start'; ?> aria-labelledby="t-news">
		<div class="wrap">
			<div class="head-row">
				<div>
					<span class="eyebrow" data-rise>الأخبار والإعلانات</span>
					<h2 class="title" id="t-news" data-split>آخر <b>الأخبار</b></h2>
				</div>
				<?php if ( $pinned || $rest ) : ?>
					<div class="tabs" role="group" aria-label="تصفية الأخبار" data-rise>
						<button class="chip" data-n="all" aria-pressed="true">الكل</button>
						<button class="chip" data-n="news" aria-pressed="false">أخبار</button>
						<button class="chip" data-n="ann" aria-pressed="false">إعلانات</button>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $pinned ) : ?>
				<div class="nbubs">
					<?php
					foreach ( $pinned as $n ) {
						get_template_part( 'template-parts/news-card', null, array( 'post' => $n, 'style' => 'bubble' ) );
					}
					?>
				</div>
			<?php endif; ?>

			<?php if ( $rest ) : ?>
				<div class="cards">
					<?php
					foreach ( $rest as $i => $n ) {
						get_template_part( 'template-parts/news-card', null, array( 'post' => $n, 'later' => $i >= $shown ) );
					}
					?>
				</div>
				<?php if ( count( $rest ) > $shown ) : ?>
					<div class="more-wrap"><button class="pill ghost" id="moreNews">عرض المزيد <span class="dot"><svg class="icon"><use href="#i-left"/></svg></span></button></div>
				<?php endif; ?>
			<?php elseif ( ! $pinned ) : ?>
				<p class="empty-note">الأخبار هتنزل هنا قريب.</p>
			<?php endif; ?>
		</div>
	</section>

	<?php
endwhile;

get_footer();
