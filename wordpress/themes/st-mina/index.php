<?php
/**
 * الاحتياطي الأخير لأي صفحة لسه مالهاش ملف خاص بيها.
 * صفحات الكنيسة والعبادة والخدمات والمكتبة والأخبار بتتعمل في المهام من 3 لـ 7.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="sec" style="min-height:70vh;display:grid;place-items:center;text-align:center">
	<div class="wrap">
		<span class="eyebrow">قريبًا</span>
		<h1 class="title">الصفحة دي <b>بتتجهّز</b></h1>
		<p class="lead">ارجع للرئيسية لحد ما تجهز.</p>
		<p style="margin-top:var(--s-8)"><a class="pill" href="<?php echo esc_url( home_url( '/' ) ); ?>">الرئيسية <span class="dot"><svg class="icon"><use href="#i-left"/></svg></span></a></p>
	</div>
</section>

<?php
get_footer();
