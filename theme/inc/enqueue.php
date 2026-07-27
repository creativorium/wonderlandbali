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

		// Everything else Elementor ships: its frontend bundle, icon font, the
		// per-widget stylesheets, and the Google Fonts request it adds for
		// Roboto in every weight and italic. None of it renders anything here.
		wonderland_dequeue_elementor_assets();

		// Core block CSS, only when the page renders none of them.
		if ( ! wonderland_page_uses_core_blocks() ) {
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'wp-block-library-theme' );
			wp_dequeue_style( 'classic-theme-styles' );
			wp_dequeue_style( 'global-styles' );
		}

		// Smash Balloon's CSS is only needed where the feed actually appears.
		if ( ! wonderland_page_uses_instagram_feed() ) {
			wp_dequeue_style( 'sbi_styles' );
			wp_dequeue_style( 'sb-elementor-shared-style' );
		}

		/**
		 * Escape hatch for any other handle that should not load on our pages.
		 *
		 * @param string[] $handles Script handles.
		 */
		foreach ( apply_filters( 'wonderland_dequeue_scripts', array() ) as $handle ) {
			wp_dequeue_script( $handle );
		}
	},
	100
);

/**
 * Later passes.
 *
 * One sweep is not enough: Elementor registers its Google Fonts request after
 * wp_enqueue_scripts has run, and enqueues its frontend JS bundle later still,
 * during footer output. Each hook re-runs the same sweep.
 */
foreach ( array( 'wp_print_styles', 'wp_print_footer_scripts' ) as $wonderland_late_hook ) {
	add_action(
		$wonderland_late_hook,
		function () {
			if ( wonderland_page_uses_elementor() ) {
				return;
			}
			wonderland_dequeue_elementor_assets();
		},
		0
	);
}
unset( $wonderland_late_hook );

/**
 * Drop every Elementor-owned style and script from the current page.
 *
 * Handles are matched by source path as well as name, because the per-widget
 * stylesheets ("widget-heading", "widget-icon-list", …) are registered on the
 * fly and share no common prefix.
 */
function wonderland_dequeue_elementor_assets() {
	foreach ( array( wp_styles(), wp_scripts() ) as $collection ) {
		foreach ( (array) $collection->queue as $handle ) {
			$item = $collection->registered[ $handle ] ?? null;
			$src  = $item->src ?? '';

			$is_elementor = ( 0 === strpos( $handle, 'elementor' ) )
				|| ( 0 === strpos( $handle, 'widget-' ) );

			if ( ! $is_elementor && is_string( $src ) && $src ) {
				/**
				 * Plugin directories whose assets are Elementor-only.
				 *
				 * Matched by path because handles are inconsistent — the
				 * DynamicConditions script, for example, is not named after its
				 * plugin. All of this goes away when Elementor is removed.
				 *
				 * @param string[] $dirs Plugin directory names.
				 */
				$dirs = apply_filters(
					'wonderland_elementor_plugin_dirs',
					array( 'elementor', 'pro-elements', 'dynamicconditions' )
				);
				foreach ( $dirs as $dir ) {
					if ( false !== strpos( $src, '/plugins/' . $dir . '/' ) ) {
						$is_elementor = true;
						break;
					}
				}
			}

			if ( $is_elementor ) {
				$collection->dequeue( $handle );
			}
		}
	}
}

/**
 * Whether the current page renders any core (non-Wonderland) block.
 *
 * @return bool
 */
function wonderland_page_uses_core_blocks() {
	if ( ! is_singular() ) {
		return true; // be conservative off single pages
	}
	$post = get_post();
	if ( ! $post || ! has_blocks( $post ) ) {
		return false;
	}
	foreach ( parse_blocks( $post->post_content ) as $block ) {
		$name = $block['blockName'] ?? '';
		if ( $name && 0 !== strpos( $name, 'wonderland/' ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Whether the current page shows the Instagram feed.
 *
 * @return bool
 */
function wonderland_page_uses_instagram_feed() {
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_post();
	if ( ! $post ) {
		return false;
	}
	return has_shortcode( $post->post_content, 'instagram-feed' )
		|| false !== strpos( $post->post_content, 'wonderland/follow' );
}
