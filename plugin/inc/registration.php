<?php
/**
 * Registers every block under /src/blocks and wires up the Vite-built assets.
 *
 * Each block ships a block.json (metadata + "render": "file:./render.php").
 * The editor UI for ALL blocks is bundled into one file (build/editor.js) and
 * enqueued once; front-end block styles are bundled into build/frontend.css.
 *
 * @package WonderlandBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add a dedicated "Wonderland" category to the block inserter.
 */
add_filter(
	'block_categories_all',
	function ( $categories ) {
		array_unshift(
			$categories,
			array(
				'slug'  => 'wonderland',
				'title' => __( 'Wonderland', 'wonderland-blocks' ),
				'icon'  => null,
			)
		);
		return $categories;
	}
);

/**
 * Register blocks from their source directories (block.json drives render.php).
 */
add_action(
	'init',
	function () {
		$blocks_root = WONDERLAND_BLOCKS_DIR . 'src/blocks';
		if ( ! is_dir( $blocks_root ) ) {
			return;
		}
		foreach ( glob( $blocks_root . '/*', GLOB_ONLYDIR ) as $dir ) {
			if ( file_exists( $dir . '/block.json' ) ) {
				register_block_type( $dir );
			}
		}
	}
);

/**
 * Editor bundle: registers the block UIs. @wordpress/* live as wp.* globals,
 * so we declare them as script dependencies.
 */
add_action(
	'enqueue_block_editor_assets',
	function () {
		$js = WONDERLAND_BLOCKS_DIR . 'build/editor.js';
		if ( file_exists( $js ) ) {
			wp_enqueue_script(
				'wonderland-editor',
				WONDERLAND_BLOCKS_URL . 'build/editor.js',
				array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-data' ),
				filemtime( $js ),
				true
			);
		}

		$css = WONDERLAND_BLOCKS_DIR . 'build/editor.css';
		if ( file_exists( $css ) ) {
			wp_enqueue_style(
				'wonderland-editor',
				WONDERLAND_BLOCKS_URL . 'build/editor.css',
				array(),
				filemtime( $css )
			);
		}
	}
);

/**
 * Front-end block styles (single combined file — fewer requests, better gzip
 * than per-block files at this scale).
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		$css = WONDERLAND_BLOCKS_DIR . 'build/frontend.css';
		if ( file_exists( $css ) ) {
			wp_enqueue_style(
				'wonderland-blocks',
				WONDERLAND_BLOCKS_URL . 'build/frontend.css',
				array(),
				filemtime( $css )
			);
		}
	}
);
