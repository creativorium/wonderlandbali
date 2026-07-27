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
 * Whether the current singular page opens with a full-bleed hero that the header
 * should sit transparently over (the wonderland/hero block, or a page-hero with a
 * background image / split layout).
 *
 * @return bool
 */
function wonderland_page_has_hero() {
	if ( is_front_page() ) {
		return true;
	}
	// The 404 screen is a full-bleed brand panel — treat it like a hero.
	if ( is_404() ) {
		return true;
	}
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_post();
	if ( ! $post ) {
		return false;
	}
	foreach ( parse_blocks( $post->post_content ) as $block ) {
		if ( empty( $block['blockName'] ) ) {
			continue; // skip empty/whitespace blocks
		}
		if ( 'wonderland/hero' === $block['blockName'] ) {
			return true;
		}
		if ( 'wonderland/page-hero' === $block['blockName'] ) {
			$attrs = $block['attrs'] ?? array();
			return ! empty( $attrs['backgroundUrl'] ) || ( ( $attrs['layout'] ?? '' ) === 'split' );
		}
		return false; // first real block is something else
	}
	return false;
}

/**
 * Flag hero pages on the body so the header can overlay them.
 */
add_filter(
	'body_class',
	function ( $classes ) {
		if ( wonderland_page_has_hero() ) {
			$classes[] = 'has-hero';
		}
		return $classes;
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
