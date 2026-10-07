<?php
/**
 * كارت خبر أو دايرة خبر مثبّت.
 * بيتستخدم في الرئيسية وصفحة الأخبار وصفحة الخبر وصفحة العبادة.
 * data-t (news أو ann) بيستخدمه فلتر الأخبار في site.js.
 *
 * $args['post']  الخبر (WP_Post).
 * $args['style'] card (الافتراضي) أو bubble.
 * $args['later'] true لو الكارت مستخبي لحد "عرض المزيد".
 */

defined( 'ABSPATH' ) || exit;

$news   = $args['post'];
$kind   = get_post_meta( $news->ID, 'kind', true ) === 'news' ? 'news' : 'ann';
$poster = stmina_news_flag( $news, 'is_poster' );
$when   = get_post_meta( $news->ID, 'when_label', true );
$when   = $when ? $when : get_the_date( 'j F Y', $news );
$title  = get_the_title( $news );
// الملصق بيبان من فوق ومن غير فلتر الألوان، والصورة العادية بفلتر دافي (grade)
$img = get_the_post_thumbnail( $news, 'large', array( 'class' => $poster ? '' : 'grade', 'alt' => $title, 'loading' => 'lazy' ) );

if ( isset( $args['style'] ) && 'bubble' === $args['style'] ) : ?>
	<a class="nbub<?php echo $poster ? ' top' : ''; ?>" data-t="<?php echo esc_attr( $kind ); ?>" href="<?php echo esc_url( get_permalink( $news ) ); ?>" data-bub><span class="bi"><?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput -- من get_the_post_thumbnail ?><span class="nb-txt"><small><?php echo esc_html( $when ); ?></small><b><?php echo esc_html( $title ); ?></b></span></span></a>
<?php else : ?>
	<a class="news<?php echo empty( $args['later'] ) ? '' : ' later'; ?>" data-t="<?php echo esc_attr( $kind ); ?>" href="<?php echo esc_url( get_permalink( $news ) ); ?>" data-rise>
		<div class="im<?php echo $poster ? ' top' : ''; ?>"><?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<div class="bd">
			<div class="k"><span><?php echo 'news' === $kind ? 'خبر' : 'إعلان'; ?></span><span><?php echo esc_html( $when ); ?></span></div>
			<h3><?php echo esc_html( $title ); ?></h3>
			<?php if ( has_excerpt( $news ) ) : ?><p><?php echo esc_html( get_the_excerpt( $news ) ); ?></p><?php endif; ?>
		</div>
	</a>
<?php
endif;
