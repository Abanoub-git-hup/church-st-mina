<?php
/**
 * صفحة عظة واحدة (/sermon/اسمها/): الفيديو أو الصوت أو ملف PDF حسب اللي اتدخل،
 * والمتحدث والموضوع والتاريخ، ونبذة العظة وآيتها، وعظات تانية لنفس المتحدث.
 * من design/sermon.html.
 * فيديو YouTube بيظهر كصورة بزرار تشغيل، والـ iframe بيتحمّل لما الزائر يدوس بس (sermon.js)،
 * علشان الصفحة تفتح بسرعة ومايتحمّلش كود YouTube من غير لازمة.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$sermon  = get_post();
	$kind    = stmina_sermon_kind( $sermon );
	$speaker = stmina_sermon_speaker( $sermon );
	$topic   = stmina_sermon_topic( $sermon );
	$library = get_page_by_path( 'library' );
	$lib_url = home_url( '/library/' );

	// صورة الواجهة: صورة العظة لو موجودة، وإلا صورة صفحة المكتبة
	$image = get_the_post_thumbnail_url( $sermon, 'full' );
	if ( ! $image && $library ) {
		$image = get_the_post_thumbnail_url( $library, 'full' );
	}

	$words = explode( ' ', get_the_title() );
	get_template_part( 'template-parts/page-hero', null, array(
		'current'   => 'library',
		'image'     => $image,
		'crumbs'    => array( array( 'الرئيسية', home_url( '/' ) ), array( 'المكتبة', $lib_url ), array( get_the_title() ) ),
		'eyebrow'   => $speaker['name'],
		'title'     => get_the_title(),
		'highlight' => count( $words ) > 1 ? end( $words ) : '',
		'lead'      => has_excerpt() ? get_the_excerpt() : '',
	) );

	$video = get_post_meta( $sermon->ID, 'video_url', true );
	$yt    = stmina_youtube_id( $video );
	$file  = (int) get_post_meta( $sermon->ID, 'audio' === $kind ? 'audio' : 'pdf', true );
	?>

	<!-- الفيديو أو الصوت أو الملف -->
	<section class="sec pool" data-cover-start aria-labelledby="t-media">
		<div class="wrap">
			<h2 class="title" id="t-media" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)"><?php echo 'pdf' === $kind ? 'ملف العظة' : ( 'audio' === $kind ? 'تسجيل العظة' : 'فيديو العظة' ); ?></h2>

			<?php if ( 'video' === $kind && $yt ) : ?>
				<div class="player" data-clip data-video="<?php echo esc_attr( $yt ); ?>" data-title="<?php echo esc_attr( get_the_title() ); ?>">
					<img class="grade" src="<?php echo esc_url( 'https://i.ytimg.com/vi/' . $yt . '/hqdefault.jpg' ); ?>" alt="">
					<button class="ph" type="button" aria-label="<?php echo esc_attr( 'تشغيل فيديو ' . get_the_title() ); ?>"><span class="big"><svg class="icon"><use href="#i-play"/></svg></span><b>شاهد العظة</b><small>الفيديو من قناة YouTube</small></button>
				</div>
			<?php elseif ( 'video' === $kind ) : ?>
				<?php $embed = wp_oembed_get( $video ); // فيسبوك أو أي موقع بيدعم oEmbed ?>
				<div class="player embed" data-clip>
					<?php if ( $embed ) : ?>
						<?php echo $embed; // phpcs:ignore WordPress.Security.EscapeOutput -- كود التضمين من WordPress نفسه ?>
					<?php else : ?>
						<a class="ph" href="<?php echo esc_url( $video ); ?>" target="_blank" rel="noopener"><span class="big"><svg class="icon"><use href="#i-play"/></svg></span><b>شاهد العظة</b><small>تفتح في موقع خارجي</small></a>
					<?php endif; ?>
				</div>
			<?php elseif ( 'audio' === $kind ) : ?>
				<div class="alts one">
					<div class="audio-p" data-rise>
						<button class="big" type="button" aria-label="تشغيل العظة" aria-pressed="false"><svg class="icon i-p"><use href="#i-play"/></svg><span class="i-pause" aria-hidden="true"></span></button>
						<div class="tr"><b>عظة صوتية</b><div class="bar"><span></span></div><div class="t"><span class="cur">0:00</span><span class="dur"><?php $m = wp_get_attachment_metadata( $file ); echo esc_html( isset( $m['length_formatted'] ) ? $m['length_formatted'] : '' ); ?></span></div></div>
						<audio preload="metadata" src="<?php echo esc_url( wp_get_attachment_url( $file ) ); ?>"></audio>
					</div>
				</div>
			<?php elseif ( 'pdf' === $kind ) : ?>
				<?php $path = get_attached_file( $file ); ?>
				<div class="alts one">
					<div class="pdf-c" data-rise>
						<span class="ic"><svg class="icon"><use href="#i-file"/></svg></span>
						<div><b>ملف العظة</b><small>PDF<?php echo $path && file_exists( $path ) ? ' · ' . esc_html( size_format( filesize( $path ), 1 ) ) : ''; ?></small></div>
						<a class="link" href="<?php echo esc_url( wp_get_attachment_url( $file ) ); ?>" target="_blank" rel="noopener">اقرأ <svg class="icon"><use href="#i-nw"/></svg></a>
						<a class="link" href="<?php echo esc_url( wp_get_attachment_url( $file ) ); ?>" download>تحميل <svg class="icon"><use href="#i-file"/></svg></a>
					</div>
				</div>
			<?php endif; ?>

			<div class="s-meta" data-rise>
				<span><svg class="icon" aria-hidden="true"><use href="#i-users"/></svg><?php echo esc_html( $speaker['name'] ); ?></span>
				<?php if ( $topic ) : ?><span><svg class="icon" aria-hidden="true"><use href="#i-book"/></svg><?php echo esc_html( $topic->name ); ?></span><?php endif; ?>
				<span><svg class="icon" aria-hidden="true"><use href="#i-calendar"/></svg><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></span>
			</div>
		</div>
	</section>

	<?php
	// عن العظة: من المحرر، وآية العظة. القسم بيختفي لو الاتنين فاضيين
	$verse     = trim( (string) get_post_meta( $sermon->ID, 'verse', true ) );
	$verse_ref = trim( (string) get_post_meta( $sermon->ID, 'verse_ref', true ) );
	if ( trim( $sermon->post_content ) || $verse ) :
		?>
		<section class="sec deep" aria-labelledby="t-about">
			<div class="wrap">
				<article class="prose">
					<span class="eyebrow" data-rise>عن العظة</span>
					<h2 class="title" id="t-about" data-split style="margin-top:0">نقاط <b>العظة</b></h2>
					<?php the_content(); ?>
					<?php if ( $verse ) : ?>
						<blockquote data-rise><?php echo esc_html( $verse ); ?><?php if ( $verse_ref ) : ?><cite>(<?php echo esc_html( $verse_ref ); ?>)</cite><?php endif; ?></blockquote>
					<?php endif; ?>
				</article>
			</div>
		</section>
	<?php endif; ?>

	<?php
	// عظات تانية لنفس الأب، ولو المتحدث ضيف: أحدث العظات
	$more_args = array( 'numberposts' => 3, 'post__not_in' => array( $sermon->ID ) );
	if ( $speaker['priest'] ) {
		$more_args['meta_query'] = array( array( 'key' => 'speaker', 'value' => $speaker['priest'] ) );
	}
	$more = stmina_sermons( $more_args );
	if ( $more ) :
		?>
		<section class="sec <?php echo trim( $sermon->post_content ) || $verse ? 'pool-l' : 'deep'; ?>" aria-labelledby="t-more">
			<div class="wrap">
				<div class="more-head">
					<div><span class="eyebrow" data-rise><?php echo $speaker['priest'] ? 'للمتحدث نفسه' : 'من المكتبة'; ?></span><h2 class="title" id="t-more" data-split>عظات <b>أخرى</b></h2></div>
					<a class="link" href="<?php echo esc_url( $lib_url . '#panel-sermons' ); ?>" data-rise>كل العظات <svg class="icon"><use href="#i-nw"/></svg></a>
				</div>
				<div class="cards">
					<?php
					foreach ( $more as $s ) {
						get_template_part( 'template-parts/sermon-card', null, array( 'post' => $s ) );
					}
					?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
endwhile;

get_footer();
