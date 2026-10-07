<?php
/**
 * كارت عظة: أيقونة النوع، والعنوان، والمتحدث، والموضوع والتاريخ.
 * بيتستخدم في الرئيسية وصفحة المكتبة وصفحة العظة ("عظات أخرى").
 * data-sp وdata-k وdata-t بيستخدمهم فلتر المكتبة (library.js).
 *
 * $args['post'] العظة (WP_Post).
 */

defined( 'ABSPATH' ) || exit;

$sermon  = $args['post'];
$kind    = stmina_sermon_kind( $sermon );
$speaker = stmina_sermon_speaker( $sermon );
$topic   = stmina_sermon_topic( $sermon );

// الأيقونة، واسم النوع، وكلمة الزرار
$kinds = array(
	'video' => array( 'i-play', 'فيديو', 'شاهد الآن' ),
	'audio' => array( 'i-headphones', 'عظة صوتية', 'استمع الآن' ),
	'pdf'   => array( 'i-file', 'ملف PDF', 'اقرأ الآن' ),
);
$k    = isset( $kinds[ $kind ] ) ? $kinds[ $kind ] : array( 'i-book', 'عظة', 'التفاصيل' );
$meta = array_filter( array( $topic ? $topic->name : '', get_the_date( 'j F Y', $sermon ) ) );
?>
<a class="sermon" data-sp="<?php echo esc_attr( $speaker['key'] ); ?>" data-k="<?php echo esc_attr( $kind ); ?>" data-t="<?php echo esc_attr( $topic ? $topic->slug : '' ); ?>" href="<?php echo esc_url( get_permalink( $sermon ) ); ?>" data-rise>
	<div class="top"><span class="mi"><svg class="icon"<?php echo 'i-play' === $k[0] ? ' style="fill:currentColor"' : ''; ?>><use href="#<?php echo esc_attr( $k[0] ); ?>"/></svg></span><span class="kind"><?php echo esc_html( $k[1] ); ?></span></div>
	<h3><?php echo esc_html( get_the_title( $sermon ) ); ?></h3>
	<ul><li><?php echo esc_html( $speaker['name'] ); ?></li><li><?php echo esc_html( implode( ' · ', $meta ) ); ?></li></ul>
	<div class="foot"><span><?php echo esc_html( $k[2] ); ?></span><svg class="icon"><use href="#i-nw"/></svg></div>
</a>
