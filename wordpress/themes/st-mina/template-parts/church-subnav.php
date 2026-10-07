<?php
/**
 * شريط التنقل بين صفحات الكنيسة الأربع، تحت الواجهة على طول.
 */

defined( 'ABSPATH' ) || exit;

$pages = array(
	'church-history'  => 'النشأة',
	'church-fathers'  => 'الآباء',
	'church-gallery'  => 'الصور',
	'church-location' => 'الموقع',
);
$current = get_post_field( 'post_name', get_queried_object_id() );
?>
<nav class="subnav" aria-label="صفحات الكنيسة"><div class="wrap"><ul>
	<?php foreach ( $pages as $slug => $label ) : ?>
		<li><a href="<?php stmina_link( $slug ); ?>"<?php echo $slug === $current ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a></li>
	<?php endforeach; ?>
</ul></div></nav>
