<?php
/**
 * Branded wp-login screen.
 *
 * Replaces the LoginPress plugin: same job (brand the login), a fraction of the
 * weight, and nothing to keep updated. Styles come from theme/build/login.css
 * (Vite target `login`); the logo is the theme's own SVG, passed in as a custom
 * property so the stylesheet needs no build-time knowledge of the URL.
 *
 * Deliberately not touched here: the login URL itself, which the
 * change-wp-admin-login plugin owns, and any authentication behaviour.
 *
 * @package Wonderland
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Brand styles for the login/lost-password/reset screens.
 */
add_action(
	'login_enqueue_scripts',
	function () {
		$fonts = wonderland_asset( '/assets/fonts/fonts.css' );
		if ( $fonts ) {
			wp_enqueue_style( 'wonderland-fonts', $fonts[0], array(), $fonts[1] );
		}

		$css = wonderland_asset( '/build/login.css' );
		if ( ! $css ) {
			return;
		}
		wp_enqueue_style( 'wonderland-login', $css[0], array( 'wonderland-fonts' ), $css[1] );

		// A custom logo set in the Customizer wins; otherwise the theme mark.
		$logo_id  = (int) get_theme_mod( 'custom_logo' );
		$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
		if ( ! $logo_url ) {
			$logo_url = WONDERLAND_URI . '/assets/img/logo.svg';
		}

		wp_add_inline_style(
			'wonderland-login',
			':root{--wl-login-logo:url(' . esc_url( $logo_url ) . ')}'
		);
	}
);

/**
 * Drop the login-URL plugin's own "login designer" template stylesheet.
 *
 * change-wp-admin-login ships cosmetic templates and defaults to one of them.
 * Its rules are scoped `body.login.aio-login__template-03 #loginform …`, so they
 * outrank anything we write without an equally contrived selector. We own the
 * login design now, so the sheet simply goes — the plugin's functional CSS (the
 * one-time-code flow) is left alone.
 */
add_action(
	'login_enqueue_scripts',
	function () {
		$styles = wp_styles();
		foreach ( (array) $styles->queue as $handle ) {
			$src = $styles->registered[ $handle ]->src ?? '';
			if ( is_string( $src ) && false !== strpos( $src, '/change-wp-admin-login/assets/css/templates/' ) ) {
				wp_dequeue_style( $handle );
			}
		}
	},
	100
);

/**
 * Mark the screen as ours, for anyone styling on top of this later.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
add_filter(
	'login_body_class',
	function ( $classes ) {
		$classes[] = 'wl-login';
		return $classes;
	}
);

/**
 * Point the login logo at the site, not wordpress.org.
 *
 * Via a closure, not `home_url` itself: the filter passes the default URL as the
 * first argument, which home_url() would treat as a path and append.
 */
add_filter(
	'login_headerurl',
	function () {
		return home_url( '/' );
	}
);

/**
 * ...and give the logo link an accessible name.
 *
 * @return string
 */
add_filter(
	'login_headertext',
	function () {
		return get_bloginfo( 'name', 'display' );
	}
);

/**
 * Keep failed logins vague.
 *
 * WordPress says "unknown username" vs "the password you entered is incorrect",
 * which confirms to anyone guessing whether an account exists. One message for
 * both — the same advice as hiding user enumeration.
 *
 * @param WP_Error $errors Login errors.
 * @return WP_Error
 */
add_filter(
	'wp_login_errors',
	function ( $errors ) {
		$codes = $errors->get_error_codes();
		foreach ( array( 'invalid_username', 'invalid_email', 'incorrect_password' ) as $code ) {
			if ( in_array( $code, $codes, true ) ) {
				return new WP_Error(
					'authentication_failed',
					__( '<strong>Error:</strong> That username or password is not correct.', 'wonderland' )
				);
			}
		}
		return $errors;
	}
);
