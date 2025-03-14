<?php declare( strict_types = 1 ); ?>
<?php
/**
 * Startorg functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Startorg
 * @since Startorg 1.0
 */


if ( ! function_exists( 'startorg_support' ) ) :

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * @since Startorg 1.0
	 *
	 * @return void
	 */
	function startorg_support() {

		// Enqueue editor styles.
		add_editor_style( 'style.css' );

		// Make theme available for translation.
		load_theme_textdomain( 'startorg' );
	}

endif;

add_action( 'after_setup_theme', 'startorg_support' );

if ( ! function_exists( 'startorg_styles' ) ) :

	/**
	 * Enqueue styles.
	 *
	 * @since Startorg 1.0
	 *
	 * @return void
	 */
	function startorg_styles() {

		// Register theme stylesheet.
		wp_register_style(
			'startorg-style',
			get_stylesheet_directory_uri() . '/style.css',
			array(),
			wp_get_theme()->get( 'Version' )
		);

		// Enqueue theme stylesheet.
		wp_enqueue_style( 'startorg-style' );

	}

endif;

add_action( 'wp_enqueue_scripts', 'startorg_styles' );
