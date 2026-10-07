<?php
/**
 * قالب St Mina: الإعدادات الأساسية.
 * الشكل المعتمد في design/ وdocs/design.md، والصفحات بتتبني من المهمة 02.
 */

defined( 'ABSPATH' ) || exit;

define( 'STMINA_THEME_VERSION', '0.1.0' );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'html5', array( 'style', 'script' ) );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'stmina-fonts', 'https://fonts.googleapis.com/css2?family=Alexandria:wght@200;300;400&display=swap', array(), null );
	wp_enqueue_style( 'stmina', get_stylesheet_uri(), array( 'stmina-fonts' ), STMINA_THEME_VERSION );
} );
