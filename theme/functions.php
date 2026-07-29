<?php
/**
 * Wonderland theme bootstrap.
 *
 * @package Wonderland
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WONDERLAND_VERSION', '0.1.0' );
define( 'WONDERLAND_DIR', get_stylesheet_directory() );
define( 'WONDERLAND_URI', get_stylesheet_directory_uri() );

require_once WONDERLAND_DIR . '/inc/setup.php';
require_once WONDERLAND_DIR . '/inc/enqueue.php';
require_once WONDERLAND_DIR . '/inc/redirects.php';
require_once WONDERLAND_DIR . '/inc/login.php';
require_once WONDERLAND_DIR . '/inc/contact-cta.php';
require_once WONDERLAND_DIR . '/inc/analytics.php';
require_once WONDERLAND_DIR . '/inc/brochure.php';
