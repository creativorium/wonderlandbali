<?php
/**
 * Site hardening.
 *
 * The things a WordPress install gives away or leaves open by default, closed
 * here in one file so there is one place to read them. Deliberately limited to
 * what a theme can honestly own: response headers, what the site tells an
 * anonymous visitor about its users, and the interfaces this site never uses.
 *
 * Not attempted here, because it belongs to the server or to Wordfence, and two
 * systems doing the same job badly is worse than one doing it well:
 *   - a firewall, malware scanning and login rate limiting (Wordfence)
 *   - the login URL itself (the change-wp-admin-login plugin)
 *   - TLS, backups and PHP/WordPress updates (the host)
 *
 * @package Wonderland
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Response headers.
 *
 * `send_headers` fires for every front-end response, including 404s and feeds.
 * Nothing here restricts what the page may load — a Content-Security-Policy
 * would, but the forms and the reCAPTCHA snippet run inline scripts, so a real
 * policy needs nonces on those first. These are the headers that cost nothing
 * and break nothing.
 */
add_action(
	'send_headers',
	function () {
		if ( is_admin() ) {
			return;
		}

		// Stop a browser second-guessing a declared content type — the trick
		// behind serving an "image" that a browser decides is JavaScript.
		header( 'X-Content-Type-Options: nosniff' );

		// Clickjacking: nothing here is meant to be framed by another site.
		header( 'X-Frame-Options: SAMEORIGIN' );

		// Send the full URL to ourselves, only the origin to anyone else — so an
		// enquiry page's query string does not leak into a third party's logs.
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );

		// Hardware this site never asks for. A compromised script cannot then
		// prompt for it either.
		header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()' );

		/**
		 * HSTS — off by default, and deliberately so.
		 *
		 * It tells browsers to refuse plain HTTP for this host for months, which
		 * is right for a live site on its own domain and painful on a shared
		 * staging host or a domain still being moved. Switch it on per site:
		 *
		 *     add_filter( 'wonderland_hsts', '__return_true' );
		 *
		 * @param bool $enabled Whether to send Strict-Transport-Security.
		 */
		if ( is_ssl() && apply_filters( 'wonderland_hsts', false ) ) {
			header( 'Strict-Transport-Security: max-age=15552000' ); // 180 days, this host only.
		}
	}
);

/**
 * Stop announcing the WordPress version.
 *
 * Not a vulnerability in itself; it is the shopping list an automated scanner
 * reads before choosing which exploit to try.
 */
add_action(
	'init',
	function () {
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'rsd_link' );
	}
);

add_filter( 'the_generator', '__return_empty_string' );

/**
 * XML-RPC: closed.
 *
 * This site has no Jetpack, no mobile app and no remote publishing. What the
 * endpoint does attract is credential stuffing, because `system.multicall` lets
 * one request try hundreds of passwords.
 */
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'pings_open', '__return_false' );

/**
 * The filter above only refuses the authenticated methods; the endpoint itself
 * still answers, so a brute-force script keeps its target. Turn the whole file
 * away instead.
 */
add_action(
	'init',
	function () {
		// xmlrpc.php defines this before it loads WordPress, which makes it the
		// reliable signal — SCRIPT_FILENAME depends on how the host maps requests.
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			status_header( 403 );
			exit;
		}
	},
	0
);

add_filter(
	'wp_headers',
	function ( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}
);

/**
 * User enumeration.
 *
 * `/?author=1` redirects to that author's archive and hands over their login
 * name, which is half of a credential. There are no author archives on this
 * site, so the query has no legitimate use.
 */
add_action(
	'template_redirect',
	function () {
		if ( is_admin() || is_user_logged_in() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- reading a public query var.
		if ( isset( $_GET['author'] ) || is_author() ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	}
);

/**
 * The REST users endpoint gives the same list away in JSON. Editors and above
 * still need it — the block editor's author controls read it — so it is closed
 * to everyone else rather than removed.
 */
add_filter(
	'rest_authentication_errors',
	function ( $result ) {
		if ( ! empty( $result ) ) {
			return $result;
		}

		$route = isset( $GLOBALS['wp']->query_vars['rest_route'] ) ? (string) $GLOBALS['wp']->query_vars['rest_route'] : '';
		if ( '' === $route ) {
			$route = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		}

		if ( preg_match( '#/wp/v2/users#', $route ) && ! current_user_can( 'list_users' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Not available.', 'wonderland' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return $result;
	}
);

/**
 * Login errors, made uninformative.
 *
 * WordPress says "unknown username" or "the password you entered is incorrect",
 * which confirms for an attacker which half they got right. One message for
 * both halves does not.
 */
add_filter(
	'login_errors',
	function () {
		return __( 'Those details are not right. Please try again.', 'wonderland' );
	}
);

/**
 * The plugin and theme editors, off.
 *
 * They turn a stolen administrator password into arbitrary PHP execution in two
 * clicks. All code here is deployed from the repository, so nobody should be
 * editing it in a browser. Defining it in wp-config.php is the usual advice and
 * still worth doing — this is the copy that survives a host migration where the
 * config gets rewritten.
 */
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}
