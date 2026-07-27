<?php
/**
 * Legacy URL redirects.
 *
 * The Elementor-era site published a few pages under duplicate-suffixed slugs
 * (`contact-2`, `request-2`, `portfolio-lp`). The rebuild uses the clean slugs, so
 * 301 the old paths to keep the indexed URLs alive.
 *
 * WordPress' own `_wp_old_slug` redirect bails out for hierarchical post types
 * (see `wp_old_slug_redirect()`), so pages need this explicit map.
 *
 * @package Wonderland
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Map of legacy path => current path (both without leading/trailing slashes).
 *
 * @return array<string,string>
 */
function wonderland_legacy_redirects() {
	return array(
		'contact-2'     => 'contact',
		'request-2'     => 'request',
		'portfolio-lp'  => 'portfolio',
	);
}

add_action(
	'template_redirect',
	function () {
		if ( ! is_404() ) {
			return;
		}

		$path = wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
		if ( ! $path ) {
			return;
		}

		$slug = trim( wp_unslash( $path ), '/' );
		$map  = wonderland_legacy_redirects();

		if ( ! isset( $map[ $slug ] ) ) {
			return;
		}

		wp_safe_redirect( home_url( '/' . $map[ $slug ] . '/' ), 301 );
		exit;
	}
);
