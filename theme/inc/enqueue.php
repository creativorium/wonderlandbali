<?php
/**
 * Front-end asset loading. Vite writes fixed filenames into /build;
 * we cache-bust with filemtime() so no manifest is required.
 *
 * @package Wonderland
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build a [url, version] pair for a file under the theme's /build dir.
 *
 * @param string $rel Path relative to the theme root, e.g. '/build/main.css'.
 * @return array{0:string,1:(int|string)}|null
 */
function wonderland_asset( $rel ) {
	$file = WONDERLAND_DIR . $rel;
	if ( ! file_exists( $file ) ) {
		return null;
	}
	return array( WONDERLAND_URI . $rel, filemtime( $file ) );
}

add_action(
	'wp_enqueue_scripts',
	function () {
		// Web fonts first (static file — relative @font-face URLs).
		$fonts = wonderland_asset( '/assets/fonts/fonts.css' );
		if ( $fonts ) {
			wp_enqueue_style( 'wonderland-fonts', $fonts[0], array(), $fonts[1] );
		}

		$css = wonderland_asset( '/build/main.css' );
		if ( $css ) {
			wp_enqueue_style( 'wonderland-main', $css[0], array( 'wonderland-fonts' ), $css[1] );
		}

		$js = wonderland_asset( '/build/main.js' );
		if ( $js ) {
			wp_enqueue_script( 'wonderland-main', $js[0], array(), $js[1], true );
		}
	}
);

/**
 * Whether the current page is still rendered by Elementor.
 *
 * Rebuilt pages have `_elementor_edit_mode` cleared; the original data is kept
 * so any page can be reverted (see Backup & reversibility in doc/README.md).
 *
 * @return bool
 */
function wonderland_page_uses_elementor() {
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_post();
	if ( ! $post ) {
		return false;
	}
	return 'builder' === get_post_meta( $post->ID, '_elementor_edit_mode', true );
}

/**
 * Drop Elementor's global kit stylesheet on pages we render ourselves.
 *
 * The kit sets sitewide rules from the old Elementor design — notably
 * `.elementor-kit-9 a { color: #FFFFFF }`, which turns every link on the site
 * white. It matches our own `a` rules at equal specificity but loads later, so
 * it silently wins. Pages still on Elementor keep the kit so they render intact.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( wonderland_page_uses_elementor() ) {
			return;
		}

		$kit_id = (int) get_option( 'elementor_active_kit', 0 );
		if ( $kit_id ) {
			wp_dequeue_style( 'elementor-post-' . $kit_id );
		}
		// The kit id is 9 on this install; dequeue it explicitly as a fallback
		// in case the option is missing.
		wp_dequeue_style( 'elementor-post-9' );
	},
	100
);
