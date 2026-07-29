<?php
/**
 * Media delivery: generate WebP sub-sizes.
 *
 * WordPress keeps the uploaded original as-is and generates the sized copies
 * that pages actually use. Emitting those copies as WebP cuts roughly a third
 * off image weight with no markup change — `srcset` simply points at `.webp`
 * files. The original JPEG/PNG stays on disk untouched, so nothing is lost and
 * the change is reversible by regenerating with this filter off.
 *
 * Requires GD (or Imagick) built with WebP support; without it WordPress falls
 * back to the source format on its own, so this is safe to leave enabled.
 *
 * @package WonderlandBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Emit resized JPEG/PNG copies as WebP.
 *
 * @param array $formats Map of source mime type => output mime type.
 * @return array
 */
add_filter(
	'image_editor_output_format',
	function ( $formats ) {
		/**
		 * Allow WebP output to be switched off wholesale.
		 *
		 * @param bool $enabled Whether to convert sub-sizes to WebP.
		 */
		if ( ! apply_filters( 'wonderland_webp_enabled', true ) ) {
			return $formats;
		}

		$formats['image/jpeg'] = 'image/webp';
		$formats['image/png']  = 'image/webp';

		return $formats;
	}
);

/**
 * Quality for the generated WebP copies.
 *
 * 82 matches the JPEG quality already configured for this site — high enough
 * that the difference is invisible at the sizes these are displayed.
 *
 * @param int    $quality Default quality.
 * @param string $mime    Mime type being written.
 * @return int
 */
add_filter(
	'wp_editor_set_quality',
	function ( $quality, $mime ) {
		return 'image/webp' === $mime ? 82 : $quality;
	},
	10,
	2
);
