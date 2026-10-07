<?php
/**
 * رأس الصفحة جوه الواجهة: الشعار، والقايمة، والسوشيال، وزرار القايمة.
 * القوايم المنسدلة وزرار دخول الخدام بيتضافوا من site.js.
 *
 * $args['current'] اسم القسم الحالي علشان يتعلّم في القايمة: home أو church-history أو services ...
 */

defined( 'ABSPATH' ) || exit;

$current = isset( $args['current'] ) ? $args['current'] : '';
$items   = array(
	'home'           => 'الرئيسية',
	'church-history' => 'الكنيسة',
	'worship'        => 'العبادة',
	'services'       => 'الخدمات',
	'library'        => 'المكتبة',
	'news'           => 'الأخبار',
);
?>
<header class="hero-top">
	<div class="wrap">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" data-intro><img src="<?php stmina_media( 'brand/logo.png' ); ?>" alt="شعار الكنيسة"><span><b>كنيسة السيدة العذراء ومارمينا</b><span class="nw">والبابا كيرلس السادس</span> <span class="nw"><span class="sep">· </span>الجبل الأصفر</span></span></a>
		<nav class="top-nav" aria-label="القائمة الرئيسية" data-intro>
			<ul>
				<?php foreach ( $items as $slug => $label ) : ?>
					<li><a href="<?php echo esc_url( 'home' === $slug ? home_url( '/' ) : home_url( '/' . $slug . '/' ) ); ?>"<?php echo $slug === $current ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<div class="socials" data-intro>
			<a class="circle" href="https://www.facebook.com/santmarychurch" target="_blank" rel="noopener" aria-label="صفحة الكنيسة على فيسبوك"><svg class="icon"><use href="#i-facebook"/></svg></a>
			<a class="circle" href="https://www.youtube.com/@%D8%A7%D9%84%D9%82%D9%85%D8%B5%D9%83%D9%8A%D8%B1%D9%84%D8%B3%D8%B1%D9%88%D9%85%D8%A7%D9%86%D9%8A/" target="_blank" rel="noopener" aria-label="قناة يوتيوب"><svg class="icon"><use href="#i-youtube"/></svg></a>
			<a class="circle" href="https://wa.me/201227445837" target="_blank" rel="noopener" aria-label="واتساب الكنيسة"><svg class="icon"><use href="#i-whatsapp"/></svg></a>
		</div>
		<button class="menu-btn" data-open-menu aria-label="فتح القائمة" aria-controls="drawer"><svg class="icon"><use href="#i-menu"/></svg></button>
	</div>
</header>
