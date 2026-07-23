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
