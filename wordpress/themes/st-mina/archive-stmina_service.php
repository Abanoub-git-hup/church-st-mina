<?php
/**
 * صفحة كل الخدمات (/services/): فهرس المجموعات في دواير، وقسم لكل مجموعة بكروتها.
 * من design/services.html. اسم الملف archive-{نوع المحتوى}.php فـ WordPress بيختاره لوحده.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$groups  = stmina_service_groups();
$classes = array( 'pool', 'deep', 'pool-l', 'deep', 'pool', 'deep' ); // تبادل الخلفيات زي التصميم

get_template_part( 'template-parts/page-hero', null, array(
	'current'   => 'services',
	'image'     => get_template_directory_uri() . '/assets/media/services/sunday-school/primary-conference-2026-2.jpg',
	'crumbs'    => array( array( 'الرئيسية', home_url( '/' ) ), array( 'الخدمات' ) ),
	'eyebrow'   => 'خدماتنا',
	'title'     => 'خدمة لكل عمر',
	'highlight' => 'لكل عمر',
	'lead'      => 'خدمات الكنيسة في ست مجموعات. اختر مجموعتك، وافتح الخدمة لتعرف موعدها والمسؤول عنها.',
) );
?>

<!-- فهرس المجموعات -->
<section class="sec idx-sec" id="groups" aria-label="مجموعات الخدمات">
	<div class="wrap">
		<nav class="gidx" aria-label="انتقل إلى مجموعة">
			<?php foreach ( $groups as $group ) : ?>
				<?php $paper = stmina_term_field( 'paper', $group ); ?>
				<a class="gix<?php echo $paper ? ' paper' : ''; ?>" href="#g-<?php echo esc_attr( $group->slug ); ?>" data-bub><span class="bi">
					<?php echo wp_get_attachment_image( (int) stmina_term_field( 'image', $group ), 'medium_large', false, array( 'class' => $paper ? '' : 'grade', 'alt' => '', 'loading' => 'lazy' ) ); ?>
					<span class="gix-tx"><b><?php echo esc_html( $group->name ); ?></b><small><?php echo esc_html( stmina_term_field( 'label', $group ) ); ?></small></span>
				</span></a>
			<?php endforeach; ?>
		</nav>
	</div>
</section>

<!-- المجموعات -->
<?php foreach ( $groups as $i => $group ) : ?>
	<?php
	$services = stmina_services( $group );
	if ( ! $services ) {
		continue;
	}
	?>
	<section class="sec <?php echo esc_attr( $classes[ $i % count( $classes ) ] ); ?> grp" id="g-<?php echo esc_attr( $group->slug ); ?>" aria-labelledby="t-<?php echo esc_attr( $group->slug ); ?>"<?php echo 0 === $i ? ' data-cover-start' : ''; ?>>
		<div class="wrap">
			<div class="head-row">
				<div>
					<span class="eyebrow" data-rise><?php echo esc_html( stmina_term_field( 'label', $group ) ); ?></span>
					<h2 class="title" id="t-<?php echo esc_attr( $group->slug ); ?>" data-split><?php stmina_title( $group->name, stmina_term_field( 'highlight', $group ) ); ?></h2>
					<?php if ( $group->description ) : ?>
						<p class="lead" data-rise><?php echo esc_html( $group->description ); ?></p>
					<?php endif; ?>
				</div>
				<a class="link" href="#groups" data-rise>كل المجموعات <svg class="icon"><use href="#i-nw"/></svg></a>
			</div>
			<div class="svc-grid">
				<?php
				foreach ( $services as $service ) {
					get_template_part( 'template-parts/service-card', null, array( 'post' => $service ) );
				}
				?>
			</div>
		</div>
	</section>
<?php endforeach; ?>

<?php
get_footer();
