<?php
/**
 * صفحة العبادة (/worship/): جدول القداسات والاجتماعات من "جدول المواعيد"،
 * والقراءات اليومية (رابط لموقع الأنبا تكلا من غير نسخ محتواه)، وآية بالشمعة،
 * والاعتراف من محرر الصفحة، وإعلانات المواسم.
 * مفيش قسم بث مباشر، لأن الكنيسة مالهاش بث حاليًا.
 */

defined( 'ABSPATH' ) || exit;

$readings = 'https://st-takla.org/zJ/index.php/en-readings-katamaros?view=reading-arabic';

get_header();

while ( have_posts() ) :
	the_post();
	stmina_page_hero( 'worship' );
	?>

	<!-- مواعيد القداسات والاجتماعات -->
	<section class="sec pool" id="schedule" data-cover-start aria-labelledby="t-sched">
		<div class="wrap sched">
			<div>
				<span class="eyebrow" data-rise>الجدول المعتاد</span>
				<h2 class="title" id="t-sched" data-split>مواعيد <b>القداسات</b></h2>
				<?php get_template_part( 'template-parts/schedule-days', null, array( 'kind' => 'mass' ) ); ?>

				<h3 class="sub-h" id="meetings" data-rise>الاجتماعات الثابتة</h3>
				<?php get_template_part( 'template-parts/schedule-days', null, array( 'kind' => 'meeting' ) ); ?>

				<div class="sched-foot" data-rise>
					<p>تتغير المواعيد في الأصوام والأعياد، وتُعلن في الأخبار.</p>
					<a class="pill" href="<?php stmina_link( 'news' ); ?>">مواعيد المواسم في الأخبار <span class="dot"><svg class="icon"><use href="#i-left"/></svg></span></a>
				</div>
			</div>
			<div class="sched-media" data-clip>
				<img class="grade" data-parallax src="<?php stmina_media( 'worship/deacons.jpeg' ); ?>" alt="آباء الكنيسة مع الشمامسة أمام الهيكل" loading="lazy">
				<div class="badge"><small>أماكن الصلاة</small><b>الكنيسة الكبيرة والكنيسة الصغيرة</b></div>
			</div>
		</div>
	</section>

	<!-- القراءات اليومية: رابط خارجي، من غير نسخ المحتوى -->
	<section class="sec deep" id="readings" aria-labelledby="t-read">
		<div class="wrap split">
			<div>
				<span class="eyebrow" data-rise>كلمة الله كل يوم</span>
				<h2 class="title" id="t-read" data-split>القراءات <b>اليومية</b></h2>
				<p class="lead" data-rise>قراءات عشية وباكر والقداس والسنكسار حسب الكاتامارس القبطي، على موقع الأنبا تكلا هيمانوت.</p>
				<div class="read-actions" data-rise>
					<a class="pill gold" href="<?php echo esc_url( $readings ); ?>" target="_blank" rel="noopener">اقرأ قراءات اليوم <span class="dot"><svg class="icon"><use href="#i-nw"/></svg></span></a>
					<small>تفتح في موقع خارجي</small>
				</div>
			</div>
			<a class="read-circle" href="<?php echo esc_url( $readings ); ?>" target="_blank" rel="noopener" aria-label="قراءات اليوم على موقع الأنبا تكلا هيمانوت" data-bub>
				<span class="bi"><img class="grade" src="<?php stmina_media( 'worship/youth-night-liturgy-2.jpeg' ); ?>" alt="" loading="lazy">
					<span class="rc-tx">
						<span class="rc-ic"><svg class="icon" aria-hidden="true"><use href="#i-book"/></svg></span>
						<small>قراءات اليوم</small>
						<b id="readDate">الكاتامارس</b>
					</span>
				</span>
			</a>
		</div>
	</section>

	<!-- آية عن الصلاة -->
	<section class="has-bg candle-sec" aria-label="كلمة عن الصلاة">
		<div class="sec-bg" aria-hidden="true"><img src="<?php stmina_media( 'church/bg-praying-light.jpg' ); ?>" alt="" loading="lazy"></div>
		<span class="flame-glow" aria-hidden="true"></span>
		<div class="wrap">
			<div class="candle" aria-hidden="true" data-rise></div>
			<span class="eyebrow" data-rise>كلمة عن الصلاة</span>
			<blockquote data-split>اِسْهَرُوا وَصَلُّوا لِئَلاَّ تَدْخُلُوا فِي تَجْرِبَةٍ</blockquote>
			<cite data-rise>(متى 26: 41)</cite>
		</div>
	</section>

	<!-- الاعتراف: من محرر الصفحة -->
	<section class="sec pool-l" id="confession" aria-labelledby="t-conf">
		<div class="wrap">
			<article class="prose">
				<span class="eyebrow" data-rise>الاعتراف</span>
				<h2 class="title" id="t-conf" data-split style="margin-top:0">سر <b>التوبة</b> والاعتراف</h2>
				<?php the_content(); ?>
				<div data-rise><a class="pill" href="<?php stmina_link( 'church-fathers' ); ?>">آباء الكنيسة <span class="dot"><svg class="icon"><use href="#i-left"/></svg></span></a></div>
			</article>
		</div>
	</section>

	<!-- إعلانات المواسم: أحدث 3 إعلانات من الأخبار -->
	<section class="sec deep" aria-labelledby="t-more">
		<div class="wrap">
			<div class="more-head">
				<div>
					<span class="eyebrow" data-rise>المواسم والمناسبات</span>
					<h2 class="title" id="t-more" data-split>من <b>الأخبار</b></h2>
				</div>
				<a class="link" href="<?php stmina_link( 'news' ); ?>" data-rise>كل الأخبار <svg class="icon"><use href="#i-nw"/></svg></a>
			</div>
			<div class="cards">
				<?php
				// أحدث 3 إعلانات من الأخبار
				$anns = function_exists( 'stmina_news' ) ? stmina_news( array( 'numberposts' => 3, 'meta_query' => array( array( 'key' => 'kind', 'value' => 'ann' ) ) ) ) : array();
				foreach ( $anns as $n ) {
					get_template_part( 'template-parts/news-card', null, array( 'post' => $n ) );
				}
				?>
			</div>
		</div>
	</section>

	<?php
endwhile;

get_footer();
