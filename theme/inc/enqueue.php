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
		$css = wonderland_asset( '/build/main.css' );
		if ( $css ) {
			wp_enqueue_style( 'wonderland-main', $css[0], array(), $css[1] );
		}

		$js = wonderland_asset( '/build/main.js' );
		if ( $js ) {
			wp_enqueue_script( 'wonderland-main', $js[0], array(), $js[1], true );
		}
	}
);
