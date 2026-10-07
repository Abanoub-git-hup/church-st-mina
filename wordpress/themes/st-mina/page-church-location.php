<?php
/**
 * صفحة الموقع (/church-location/): العنوان وأزرار التواصل، وخريطة جوجل في إطار مقوّس،
 * و"إزاي توصل" من محرر الصفحة.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	stmina_page_hero( 'church-history', array( 'الكنيسة', home_url( '/church-history/' ) ) );
	get_template_part( 'template-parts/church-subnav' );
	?>

	<section class="sec pool" data-cover-start aria-labelledby="t-map">
		<div class="wrap split">
			<div>
				<span class="eyebrow" data-rise>العنوان</span>
				<h2 class="title" id="t-map" data-split>أهلًا بك <b>في بيت الله</b></h2>
				<ul class="ev-meta" data-rise style="margin-top:var(--s-8)">
					<li><svg class="icon" aria-hidden="true"><use href="#i-pin"/></svg>عرب العيايدة، ناحية الجبل الأصفر</li>
					<li><svg class="icon" aria-hidden="true"><use href="#i-church"/></svg>مطرانية شبين القناطر وتوابعها</li>
					<li><svg class="icon" aria-hidden="true"><use href="#i-clock"/></svg><a class="link" href="<?php stmina_link( 'worship' ); ?>">مواعيد القداسات</a></li>
					<li><svg class="icon" aria-hidden="true"><use href="#i-phone"/></svg><a class="link" href="tel:+201008025261" dir="ltr">010 0802 5261</a></li>
				</ul>
				<div class="loc-actions" data-rise>
					<a class="pill gold" href="https://maps.app.goo.gl/642e7FN8UX4WFBpt6" target="_blank" rel="noopener">افتح في الخرائط <span class="dot"><svg class="icon"><use href="#i-nw"/></svg></span></a>
					<a class="pill ghost" href="https://wa.me/201227445837" target="_blank" rel="noopener">واتساب <span class="dot"><svg class="icon"><use href="#i-whatsapp"/></svg></span></a>
					<a class="pill ghost" href="https://www.facebook.com/santmarychurch" target="_blank" rel="noopener">صفحة الكنيسة <span class="dot"><svg class="icon"><use href="#i-facebook"/></svg></span></a>
				</div>
			</div>
			<div class="map-arch" data-clip><iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d6896.3595605500095!2d31.353344399999997!3d30.203415900000003!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x145813b9cb09b3f7%3A0xc1d589dd2b68e60f!2z2YPZhtmK2LPYqSDYp9mE2LnYsNix2KfYoSDZiNin2YTYtNmH2YrYryDYp9mE2LnYuNmK2YUg2YXYp9ix2YXZitmG2Kcg2Ygg2KfZhNio2KfYqNinINmD2YrYsdmE2LMg2KfZhNiz2KfYr9izINio2LnYsdioINin2YTYudmK2KfZitiv2Ycg2KfZhNis2KjZhCDYp9mE2KPYtdmB2LEg2KXZitio2KfYsdi02YrYqSDYtNio2YrZhiDYp9mE2YLZhtin2LfYsSDZiCDYqtmI2KfYqNi52YfYpw!5e0!3m2!1sar!2seg!4v1791311207237!5m2!1sar!2seg" title="موقع الكنيسة على الخريطة" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div>
		</div>
	</section>

	<?php if ( trim( get_the_content() ) ) : ?>
		<!-- طريقة الوصول: من محرر الصفحة -->
		<section class="sec deep" aria-labelledby="t-way">
			<div class="wrap">
				<article class="prose">
					<span class="eyebrow" data-rise>طريقة الوصول</span>
					<h2 class="title" id="t-way" data-split style="margin-top:0">إزاي <b>توصل</b></h2>
					<?php the_content(); ?>
				</article>
			</div>
		</section>
	<?php endif; ?>

	<?php
endwhile;

get_footer();
