<?php
/**
 * صفحة المكتبة (/library/): تلات تبويبات بتتفتح من الرابط (#panel-sermons وغيره، من site.js).
 * العظات بفلاتر المتحدث والموضوع والنوع مع بعض (library.js)، والنشرات ملفات PDF بغلاف،
 * والترانيم صوت وكلمات. من design/library.html.
 * صف الفلتر بيظهر بس لو فيه اختيارين أو أكتر، علشان مايبقاش فيه زرار مالوش لازمة.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	stmina_page_hero( 'library' );

	$sermons   = stmina_sermons();
	$bulletins = stmina_bulletins();
	$hymns     = stmina_hymns();

	// قيم الفلاتر من العظات الموجودة: المفتاح => الاسم
	$speakers = array();
	$topics   = array();
	$kinds    = array();
	$names    = array( 'video' => 'فيديو', 'audio' => 'صوت', 'pdf' => 'PDF' );
	foreach ( $sermons as $s ) {
		$sp = stmina_sermon_speaker( $s );
		$speakers[ $sp['key'] ] = 'guest' === $sp['key'] ? 'ضيوف' : $sp['name'];
		$topic = stmina_sermon_topic( $s );
		if ( $topic ) {
			$topics[ $topic->slug ] = $topic->name;
		}
		$kind = stmina_sermon_kind( $s );
		if ( $kind ) {
			$kinds[ $kind ] = $names[ $kind ];
		}
	}
	// الضيوف آخر الصف، والأنواع بترتيب ثابت
	if ( isset( $speakers['guest'] ) ) {
		$guest = $speakers['guest'];
		unset( $speakers['guest'] );
		$speakers['guest'] = $guest;
	}
	$kinds = array_intersect_key( $names, $kinds );

	$rows = array_filter( array(
		array( 'sp', 'المتحدث', $speakers ),
		array( 't', 'الموضوع', $topics ),
		array( 'k', 'النوع', $kinds ),
	), function ( $row ) {
		return count( $row[2] ) > 1;
	} );
	?>

	<section class="sec pool" data-cover-start aria-label="محتوى المكتبة">
		<div class="wrap">
			<div class="lib-tabs" data-rise>
				<div class="tabs" role="tablist" aria-label="أقسام المكتبة">
					<button class="chip" role="tab" id="tab-sermons" aria-controls="panel-sermons" aria-selected="true" aria-pressed="true">العظات</button>
					<button class="chip" role="tab" id="tab-bulletins" aria-controls="panel-bulletins" aria-selected="false" aria-pressed="false" tabindex="-1">النشرات</button>
					<button class="chip" role="tab" id="tab-hymns" aria-controls="panel-hymns" aria-selected="false" aria-pressed="false" tabindex="-1">الترانيم</button>
				</div>
			</div>

			<!-- العظات -->
			<div role="tabpanel" id="panel-sermons" aria-labelledby="tab-sermons">
				<?php if ( $sermons ) : ?>
					<?php if ( $rows ) : ?>
						<div class="filters" data-rise>
							<?php foreach ( $rows as $row ) : ?>
								<div class="frow" data-key="<?php echo esc_attr( $row[0] ); ?>" role="group" aria-label="<?php echo esc_attr( $row[1] ); ?>">
									<span><?php echo esc_html( $row[1] ); ?></span>
									<button class="chip" data-v="all" aria-pressed="true">الكل</button>
									<?php foreach ( $row[2] as $value => $label ) : ?>
										<button class="chip" data-v="<?php echo esc_attr( $value ); ?>" aria-pressed="false"><?php echo esc_html( $label ); ?></button>
									<?php endforeach; ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					<div class="cards">
						<?php
						foreach ( $sermons as $s ) {
							get_template_part( 'template-parts/sermon-card', null, array( 'post' => $s ) );
						}
						?>
					</div>
					<p class="empty">لا توجد عظات بهذا الاختيار حتى الآن.</p>
				<?php else : ?>
					<p class="empty show">العظات هتنزل هنا قريب.</p>
				<?php endif; ?>
			</div>

			<!-- النشرات -->
			<div role="tabpanel" id="panel-bulletins" aria-labelledby="tab-bulletins" hidden>
				<?php if ( $bulletins ) : ?>
					<div class="buls">
						<?php
						foreach ( $bulletins as $b ) :
							$pdf   = wp_get_attachment_url( (int) get_post_meta( $b->ID, 'pdf', true ) );
							$issue = get_post_meta( $b->ID, 'issue', true );
							$month = get_the_date( 'F Y', $b );
							?>
							<article class="bul" data-rise>
								<?php if ( has_post_thumbnail( $b ) ) : ?>
									<div class="bul-cover has-img" aria-hidden="true"><?php echo get_the_post_thumbnail( $b, 'medium_large', array( 'alt' => '', 'loading' => 'lazy' ) ); ?></div>
								<?php else : ?>
									<div class="bul-cover" aria-hidden="true">
										<img src="<?php stmina_media( 'brand/logo.png' ); ?>" alt=""><b>نشرة الكنيسة</b><span><?php echo esc_html( $month ); ?></span>
										<?php if ( $issue ) : ?><small>العدد <?php echo esc_html( $issue ); ?></small><?php endif; ?>
									</div>
								<?php endif; ?>
								<div class="bul-bd">
									<h3><?php echo esc_html( get_the_title( $b ) ); ?></h3>
									<?php if ( has_excerpt( $b ) ) : ?><p><?php echo esc_html( get_the_excerpt( $b ) ); ?></p><?php endif; ?>
									<?php if ( $pdf ) : ?>
										<div class="bul-act">
											<a class="link" href="<?php echo esc_url( $pdf ); ?>" target="_blank" rel="noopener">اقرأ <svg class="icon"><use href="#i-nw"/></svg></a>
											<a class="link" href="<?php echo esc_url( $pdf ); ?>" download>تحميل PDF <svg class="icon"><use href="#i-file"/></svg></a>
										</div>
									<?php endif; ?>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<p class="empty show">النشرة الشهرية هتنزل هنا قريب.</p>
				<?php endif; ?>
			</div>

			<!-- الترانيم -->
			<div role="tabpanel" id="panel-hymns" aria-labelledby="tab-hymns" hidden>
				<?php if ( $hymns ) : ?>
					<ul class="hymns">
						<?php
						foreach ( $hymns as $i => $h ) :
							$audio_id = (int) get_post_meta( $h->ID, 'audio', true );
							$src      = wp_get_attachment_url( $audio_id );
							$meta     = $audio_id ? wp_get_attachment_metadata( $audio_id ) : array();
							$team     = get_post_meta( $h->ID, 'team', true );
							$lyrics   = trim( (string) get_post_meta( $h->ID, 'lyrics', true ) );
							if ( ! $src ) {
								continue;
							}
							?>
							<li class="hymn" data-rise>
								<button class="h-play" aria-label="<?php echo esc_attr( 'تشغيل ترنيمة ' . get_the_title( $h ) ); ?>" aria-pressed="false"><svg class="icon i-p"><use href="#i-play"/></svg><span class="i-pause" aria-hidden="true"></span></button>
								<div class="h-main">
									<div class="h-top"><h3><?php echo esc_html( get_the_title( $h ) ); ?></h3><span class="h-dur"><?php echo esc_html( isset( $meta['length_formatted'] ) ? $meta['length_formatted'] : '' ); ?></span></div>
									<?php if ( $team ) : ?><small><?php echo esc_html( $team ); ?></small><?php endif; ?>
									<div class="h-bar" aria-hidden="true"><span></span></div>
									<audio preload="none" src="<?php echo esc_url( $src ); ?>"></audio>
									<?php if ( $lyrics ) : ?>
										<details class="h-lyrics"><summary>الكلمات</summary><p><?php echo nl2br( esc_html( $lyrics ) ); ?></p></details>
									<?php endif; ?>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p class="empty show">ترانيم فرق الكنيسة هتنزل هنا قريب.</p>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php
endwhile;

get_footer();
