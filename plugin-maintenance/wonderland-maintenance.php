<?php
/**
 * Plugin Name:       Wonderland Maintenance
 * Plugin URI:        https://wonderlandbali.com
 * Description:       One-click maintenance mode with a branded holding page. Toggle it from the admin bar; logged-in admins always see the real site.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Wonderland Bali
 * Text Domain:       wonderland-maintenance
 *
 * @package WonderlandMaintenance
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WLM_VERSION', '1.0.0' );
define( 'WLM_DIR', plugin_dir_path( __FILE__ ) );
define( 'WLM_URL', plugin_dir_url( __FILE__ ) );
define( 'WLM_OPTION', 'wonderland_maintenance' );

/**
 * Stored settings, merged over defaults.
 *
 * @return array
 */
function wlm_settings() {
	$defaults = array(
		'enabled'    => 0,
		'eyebrow'    => 'Wonderland Bali',
		'heading'    => 'We are making wonders',
		'message'    => 'Our site is having a little refresh. We will be back very shortly — in the meantime, we would still love to hear about your day.',
		'email'      => 'info@wonderlandbali.com',
		'phone'      => '+62 878 6113 8090',
		'background' => '',
		'bypass_key' => '',
	);

	$saved = get_option( WLM_OPTION, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	return wp_parse_args( $saved, $defaults );
}

/**
 * Whether maintenance mode is switched on.
 *
 * @return bool
 */
function wlm_is_enabled() {
	$settings = wlm_settings();
	return ! empty( $settings['enabled'] );
}

/**
 * Whether the current visitor should be let through to the real site.
 *
 * Anyone who can manage options, plus a bypass-key holder, plus the requests
 * WordPress needs to keep working (login, admin, cron, REST, AJAX).
 *
 * @return bool
 */
function wlm_visitor_may_pass() {
	if ( current_user_can( 'manage_options' ) || current_user_can( 'edit_pages' ) ) {
		return true;
	}

	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return true;
	}

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return true;
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return true;
	}

	// The login/registration screens must stay reachable or you lock yourself out.
	$self = isset( $_SERVER['SCRIPT_NAME'] ) ? basename( sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_NAME'] ) ) ) : '';
	if ( in_array( $self, array( 'wp-login.php', 'wp-register.php' ), true ) ) {
		return true;
	}

	$settings = wlm_settings();
	$key      = $settings['bypass_key'];
	if ( $key ) {
		// A key in the URL sets a cookie so the rest of the browse works too.
		$given = isset( $_GET['wlm_key'] ) ? sanitize_text_field( wp_unslash( $_GET['wlm_key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $given && hash_equals( $key, $given ) ) {
			setcookie( 'wlm_bypass', $key, time() + WEEK_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
			return true;
		}
		if ( isset( $_COOKIE['wlm_bypass'] ) && hash_equals( $key, sanitize_text_field( wp_unslash( $_COOKIE['wlm_bypass'] ) ) ) ) {
			return true;
		}
	}

	return false;
}

require_once WLM_DIR . 'inc/screen.php';
require_once WLM_DIR . 'inc/admin.php';

/**
 * Intercept the front end and show the holding page.
 */
add_action(
	'template_redirect',
	function () {
		if ( ! wlm_is_enabled() || wlm_visitor_may_pass() ) {
			return;
		}
		wlm_render_screen();
		exit;
	},
	0
);

// Clean up the option if the plugin is removed entirely.
register_uninstall_hook( __FILE__, 'wlm_uninstall' );

/**
 * Remove stored settings on uninstall.
 */
function wlm_uninstall() {
	delete_option( WLM_OPTION );
}
