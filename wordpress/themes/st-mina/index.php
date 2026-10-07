<?php
/**
 * صفحة مؤقتة لحد ما الرئيسية تتبني في المهمة 02.
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<main class="stmina-soon">
	<h1><?php bloginfo( 'name' ); ?></h1>
	<p class="tag"><?php bloginfo( 'description' ); ?></p>
	<p>الموقع بيتجهّز، وقريب هيكون جاهز.</p>
</main>
<?php wp_footer(); ?>
</body>
</html>
