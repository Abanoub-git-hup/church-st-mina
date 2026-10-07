<?php
/**
 * صفحة النشأة (/church-history/): قصة الكنيسة من محرر الصفحة، والشفعاء التلاتة،
 * ومحطات التاريخ من نوع المحتوى "محطة"، وقداس الافتتاح، وكروت باقي صفحات الكنيسة.
 * اسم الملف page-{رابط الصفحة}.php فـ WordPress بيختاره لوحده للصفحة دي.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	stmina_page_hero( 'church-history', array( 'الكنيسة', home_url( '/church-history/' ) ) );
	get_template_part( 'template-parts/church-subnav' );
	?>

	<!-- قصة الكنيسة: من محرر الصفحة -->
	<section class="sec pool" data-cover-start aria-labelledby="t-story">
		<div class="wrap">
			<article class="prose">
				<span class="eyebrow" data-rise>قصة الكنيسة</span>
				<h2 class="title" id="t-story" data-split style="margin-top:0">كيف <b>بدأت</b></h2>
				<?php the_content(); ?>
			</article>
		</div>
	</section>

	<!-- شفعاء الكنيسة (المعلومات من موقع الأنبا تكلا هيمانوت) -->
	<section class="sec deep" aria-labelledby="t-saints">
		<div class="wrap">
			<div style="text-align:center;margin-bottom:var(--s-12)">
				<span class="eyebrow" data-rise>شفعاء الكنيسة</span>
				<h2 class="title" id="t-saints" data-split>بشفاعة <b>القديسين</b></h2>
				<p class="lead" data-rise style="margin-inline:auto">ثلاثة شفعاء تجمعهم قصة واحدة: مارمينا وُلد بعد صلاة أمه في عيد للسيدة العذراء، والبابا كيرلس السادس ترهّب باسم مينا، وفي عهده بُني دير مارمينا بمريوط من جديد.</p>
			</div>
			<div class="saints">
				<article class="saint" data-rise>
					<figure class="saint-im" data-clip><img src="<?php stmina_media( 'patrons/virgin-mary.jpg' ); ?>" alt="أيقونة السيدة العذراء مريم" loading="lazy"></figure>
					<span class="eyebrow">والدة الإله</span>
					<h3>السيدة العذراء مريم</h3>
					<p>أم ربنا يسوع المسيح، من سبط يهوذا. باركت أرض مصر مع العائلة المقدسة، وتكرّمها الكنيسة القبطية فوق جميع القديسين وتطلب شفاعتها في كل صلاة.</p>
					<ul class="saint-facts"><li>أعيادها كثيرة على مدار السنة</li><li>صوم العذراء: من 1 إلى 16 مسرى</li></ul>
					<a class="link" href="https://st-takla.org/Full-Free-Coptic-Books/FreeCopticBooks-002-Holy-Arabic-Bible-Dictionary/24_M/M_128.html" target="_blank" rel="noopener">اقرأ عنها في قاموس الكتاب المقدس <svg class="icon"><use href="#i-nw"/></svg></a>
				</article>
				<article class="saint" data-rise>
					<figure class="saint-im" data-clip><img src="<?php stmina_media( 'patrons/saint-mina.jpg' ); ?>" alt="أيقونة الشهيد مارمينا العجايبي" loading="lazy"></figure>
					<span class="eyebrow">الشهيد العظيم</span>
					<h3>مارمينا العجايبي</h3>
					<p>أشهر الشهداء المصريين، واسمه يعني «آمين». كان ضابطًا في الجيش، فتركه سنة 303 م ليتعبد في البرية، ثم نال إكليل الشهادة وهو في الرابعة والعشرين. لُقّب بالعجايبي لكثرة العجائب التي يصنعها الرب بصلواته.</p>
					<ul class="saint-facts"><li>عيد استشهاده: 15 هاتور</li><li>تذكار كنيسته بمريوط: 15 بؤونة</li><li>تدشين أول كنيسة باسمه: 1 أبيب</li></ul>
					<a class="link" href="https://st-takla.org/Saints/Coptic-Orthodox-Saints-Biography/Coptic-Saints-Story_1773.html" target="_blank" rel="noopener">اقرأ سيرته <svg class="icon"><use href="#i-nw"/></svg></a>
				</article>
				<article class="saint" data-rise>
					<figure class="saint-im" data-clip><img src="<?php stmina_media( 'patrons/pope-kyrillos-vi.jpg' ); ?>" alt="أيقونة البابا كيرلس السادس" loading="lazy"></figure>
					<span class="eyebrow">البطريرك 116</span>
					<h3>البابا كيرلس السادس</h3>
					<p>وُلد باسم عازر يوسف عطا في طوخ النصارى بدمنهور، وترهّب في دير البراموس باسم «الراهب مينا». اختارته القرعة الهيكلية بطريركًا، وفي عهده بُني دير مارمينا الحالي بمريوط وعاد إليه جزء من رفات الشهيد.</p>
					<ul class="saint-facts"><li>وُلد: 2 أغسطس 1902</li><li>رُسم راهبًا: 25 فبراير 1928</li><li>القرعة الهيكلية: 19 أبريل 1959</li></ul>
					<a class="link" href="https://st-takla.org/Pope-Kyrellos-1_.html" target="_blank" rel="noopener">اقرأ سيرته <svg class="icon"><use href="#i-nw"/></svg></a>
				</article>
			</div>
			<p class="src-note" data-rise>المعلومات والأيقونات من <a href="https://st-takla.org/" target="_blank" rel="noopener">موقع الأنبا تكلا هيمانوت</a>.</p>
		</div>
	</section>

	<?php
	$milestones = function_exists( 'stmina_ordered' ) ? stmina_ordered( 'stmina_milestone' ) : array();
	if ( $milestones ) :
		?>
		<!-- محطات التاريخ: من نوع المحتوى "محطة" -->
		<section class="sec pool-l" aria-labelledby="t-tl">
			<div class="wrap">
				<div style="text-align:center;margin-bottom:var(--s-12)">
					<span class="eyebrow" data-rise>خط زمني</span>
					<h2 class="title" id="t-tl" data-split>محطات <b>في تاريخ الكنيسة</b></h2>
				</div>
				<div class="timeline">
					<?php foreach ( $milestones as $m ) : ?>
						<div class="tl" data-rise>
							<span class="yr"><?php echo esc_html( get_post_meta( $m->ID, 'year', true ) ); ?></span>
							<h3><?php echo esc_html( get_the_title( $m ) ); ?></h3>
							<p><?php echo esc_html( get_the_excerpt( $m ) ); ?><?php if ( str_contains( get_the_title( $m ), 'افتتاح' ) ) : ?> <a class="link" href="#opening">شاهد القداس</a><?php endif; ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/opening-mass' ); ?>

	<!-- باقي صفحات الكنيسة -->
	<section class="sec deep" aria-labelledby="t-more">
		<div class="wrap">
			<div class="more-head"><div><span class="eyebrow" data-rise>تعرّف أكثر</span><h2 class="title" id="t-more" data-split>كنيستنا</h2></div></div>
			<div class="disc">
				<a class="dcard paper" href="<?php stmina_link( 'church-fathers' ); ?>" data-rise><img src="<?php stmina_media( 'priests/fathers-group.png' ); ?>" alt="" loading="lazy"><span class="go"><svg class="icon"><use href="#i-nw"/></svg></span><h3>الآباء الكهنة</h3><p>آباء الكنيسة الثلاثة وأقوالهم.</p></a>
				<a class="dcard" href="<?php stmina_link( 'church-gallery' ); ?>" data-rise><img class="grade" src="<?php stmina_media( 'worship/youth-liturgy.jpeg' ); ?>" alt="" loading="lazy"><span class="go"><svg class="icon"><use href="#i-nw"/></svg></span><h3>صور الكنيسة</h3><p>العبادة والخدمات والأنشطة.</p></a>
				<a class="dcard" href="<?php stmina_link( 'church-location' ); ?>" data-rise><img class="grade" src="<?php stmina_media( 'church/bg-window-rays.jpg' ); ?>" alt="" loading="lazy"><span class="go"><svg class="icon"><use href="#i-nw"/></svg></span><h3>موقع الكنيسة</h3><p>العنوان وطريقة الوصول.</p></a>
			</div>
		</div>
	</section>

	<?php
endwhile;

get_footer();
