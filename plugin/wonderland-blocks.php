<?php
/**
 * Plugin Name:       Wonderland Blocks
 * Plugin URI:        https://github.com/creativorium/wonderlandbali
 * Description:       Native Gutenberg blocks for the Wonderland Bali theme. Built with Vite, rendered server-side (render.php) for a light front end.
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            Creativorium
 * Text Domain:       wonderland-blocks
 *
 * @package WonderlandBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WONDERLAND_BLOCKS_VERSION', '0.1.0' );
define( 'WONDERLAND_BLOCKS_DIR', plugin_dir_path( __FILE__ ) );
define( 'WONDERLAND_BLOCKS_URL', plugin_dir_url( __FILE__ ) );

require_once WONDERLAND_BLOCKS_DIR . 'inc/helpers.php';
require_once WONDERLAND_BLOCKS_DIR . 'inc/registration.php';
require_once WONDERLAND_BLOCKS_DIR . 'inc/forms-settings.php';
require_once WONDERLAND_BLOCKS_DIR . 'inc/forms-submissions.php';
require_once WONDERLAND_BLOCKS_DIR . 'inc/contact.php';
