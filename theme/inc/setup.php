<?php
/**
 * Theme supports, menus and editor configuration.
 *
 * @package Wonderland
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'custom-logo' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'editor-styles' );

		// Load the fonts + compiled theme CSS inside the block editor so block
		// previews match the front end.
		$editor_styles = array();
		if ( file_exists( get_stylesheet_directory() . '/assets/fonts/fonts.css' ) ) {
			$editor_styles[] = 'assets/fonts/fonts.css';
		}
		if ( file_exists( get_stylesheet_directory() . '/build/main.css' ) ) {
			$editor_styles[] = 'build/main.css';
		}
		if ( $editor_styles ) {
			add_editor_style( $editor_styles );
		}

		register_nav_menus(
			array(
				'primary' => __( 'Primary Menu', 'wonderland' ),
				'footer'  => __( 'Footer Menu', 'wonderland' ),
			)
		);
	}
);

/**
 * Let the editor width match the front-end content width.
 */
add_action(
	'after_setup_theme',
	function () {
		if ( ! isset( $GLOBALS['content_width'] ) ) {
			$GLOBALS['content_width'] = 1200;
		}
	}
);
