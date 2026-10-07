<?php
/**
 * قداس افتتاح الكنيسة (29 ديسمبر 2007): مشغّل يوتيوب بتلات أجزاء، أزرارها شموع طولها على قد مدة الجزء.
 * الفيديو بيتحمّل لما الزائر يضغط بس (السلوك في site.js).
 *
 * $args['story_link'] يظهر زرار "قصة الكنيسة" (في الرئيسية) ولا لأ (في صفحة النشأة نفسها).
 */

defined( 'ABSPATH' ) || exit;

$parts = array(
	array( '3oN42iWOHGY', '1:32:01', 64 ),
	array( 'S-1k0WgHzNA', '1:28:36', 62 ),
	array( '848eJp9_nbI', '48:27', 34 ),
);
?>
<section class="sec pool" id="opening" aria-labelledby="t-open">
	<div class="wrap opening-grid">
		<div>
			<span class="eyebrow" data-rise>حدث تاريخي</span>
			<h2 class="title" id="t-open" data-split>قداس <b>افتتاح الكنيسة</b></h2>
			<p class="lead" data-rise>قداس افتتاح كنيستنا مسجّلًا كاملًا في ثلاثة أجزاء، ذكرى لنا وللأجيال القادمة.</p>
			<ul class="ev-meta" data-rise style="margin-top:var(--s-8)">
				<li><svg class="icon" aria-hidden="true"><use href="#i-calendar"/></svg>29 ديسمبر 2007</li>
				<li><svg class="icon" aria-hidden="true"><use href="#i-users"/></svg>أبونا كيرلس روماني، وأبونا مقار ماهر، وأبونا بطرس حليم</li>
			</ul>
			<div style="display:flex;flex-wrap:wrap;gap:var(--s-6);align-items:center" data-rise>
				<?php if ( ! empty( $args['story_link'] ) ) : ?>
					<a class="pill" href="<?php stmina_link( 'church-history', '#opening' ); ?>">قصة الكنيسة <span class="dot"><svg class="icon"><use href="#i-left"/></svg></span></a>
				<?php endif; ?>
				<a class="link" href="https://www.youtube.com/watch?v=3oN42iWOHGY" target="_blank" rel="noopener">شاهد على يوتيوب <svg class="icon"><use href="#i-nw"/></svg></a>
			</div>
		</div>
		<div class="yt" data-yt data-clip>
			<div class="yt-frame">
				<img class="grade" src="https://i.ytimg.com/vi/3oN42iWOHGY/hqdefault.jpg" alt="" loading="lazy">
				<button class="yt-play" aria-label="تشغيل الجزء 1"><svg class="icon"><use href="#i-play"/></svg></button>
				<span class="yt-cap">الجزء 1 · 1:32:01</span>
			</div>
			<div class="yt-parts" role="group" aria-label="أجزاء القداس">
				<?php foreach ( $parts as $i => $part ) : $n = $i + 1; ?>
					<button class="yt-part" data-id="<?php echo esc_attr( $part[0] ); ?>" data-title="<?php echo esc_attr( 'قداس افتتاح كنيسة العذراء ومارمينا والبابا كيرلس الجبل الأصفر ' . $n ); ?>" data-thumb="<?php echo esc_url( 'https://i.ytimg.com/vi/' . $part[0] . '/hqdefault.jpg' ); ?>" aria-pressed="<?php echo 0 === $i ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( 'الجزء ' . $n . '، ' . $part[1] ); ?>"><span class="yc" style="--h:<?php echo (int) $part[2]; ?>px" aria-hidden="true"><span class="glow"></span></span><b>الجزء <?php echo (int) $n; ?></b><small><?php echo esc_html( $part[1] ); ?></small></button>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
