<?php
/**
 * One-click updates for the theme and this plugin, from GitHub Releases.
 *
 * Neither is on wordpress.org, so WordPress has nowhere to look for a newer
 * version and every release had to be uploaded as a zip by hand — which is how
 * live, staging and the repository drifted apart. This teaches WordPress where
 * to look: the repo's latest release, whose assets are named for the versions
 * they carry (wonderland-0.2.1.zip, wonderland-blocks-0.2.4.zip).
 *
 * Both are handled here rather than one updater each, because the site does not
 * function with this plugin switched off, so there is no case where the theme
 * needs to check for itself. The trade is stated plainly: deactivate the plugin
 * and theme updates stop being offered.
 *
 * The release is fetched at most twice a day and cached; "Check again" on the
 * updates screen clears that cache, so a deliberate check is never stale.
 *
 * @package WonderlandBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const WONDERLAND_UPDATE_REPO      = 'creativorium/wonderlandbali';
const WONDERLAND_UPDATE_TRANSIENT = 'wonderland_release';
const WONDERLAND_UPDATE_TTL       = 12 * HOUR_IN_SECONDS;

/**
 * The latest release, as a version and download URL per component.
 *
 * @param bool $force Skip the cache.
 * @return array{url:string,notes:string,published:string,assets:array<string,array{version:string,package:string}>}
 */
function wonderland_update_release( $force = false ) {
	$empty = array(
		'url'       => '',
		'notes'     => '',
		'published' => '',
		'assets'    => array(),
	);

	if ( ! $force ) {
		$cached = get_site_transient( WONDERLAND_UPDATE_TRANSIENT );
		if ( is_array( $cached ) ) {
			return $cached;
		}
	}

	$response = wp_remote_get(
		'https://api.github.com/repos/' . WONDERLAND_UPDATE_REPO . '/releases/latest',
		array(
			'timeout' => 10,
			'headers' => array(
				'Accept'     => 'application/vnd.github+json',
				'User-Agent' => 'wonderland-updater',
			),
		)
	);

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		// A short cache on failure too: a rate-limited or offline check should
		// not hammer GitHub on every admin page load.
		set_site_transient( WONDERLAND_UPDATE_TRANSIENT, $empty, HOUR_IN_SECONDS );
		return $empty;
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $body ) ) {
		set_site_transient( WONDERLAND_UPDATE_TRANSIENT, $empty, HOUR_IN_SECONDS );
		return $empty;
	}

	$release = array(
		'url'       => (string) ( $body['html_url'] ?? '' ),
		'notes'     => (string) ( $body['body'] ?? '' ),
		'published' => (string) ( $body['published_at'] ?? '' ),
		'assets'    => array(),
	);

	foreach ( (array) ( $body['assets'] ?? array() ) as $asset ) {
		$name = (string) ( $asset['name'] ?? '' );

		// wonderland-0.2.1.zip -> theme, wonderland-blocks-0.2.4.zip -> plugin.
		// The version lives in the filename so one release can carry both at
		// their own version numbers.
		if ( preg_match( '/^(wonderland|wonderland-blocks)-(\d+\.\d+\.\d+)\.zip$/', $name, $m ) ) {
			$release['assets'][ $m[1] ] = array(
				'version' => $m[2],
				'package' => (string) ( $asset['browser_download_url'] ?? '' ),
			);
		}
	}

	set_site_transient( WONDERLAND_UPDATE_TRANSIENT, $release, WONDERLAND_UPDATE_TTL );

	return $release;
}

/**
 * This plugin's file, relative to the plugins directory.
 *
 * @return string
 */
function wonderland_update_plugin_basename() {
	return plugin_basename( WONDERLAND_BLOCKS_DIR . 'wonderland-blocks.php' );
}

/**
 * Offer the plugin update.
 */
add_filter(
	'pre_set_site_transient_update_plugins',
	function ( $transient ) {
		if ( ! is_object( $transient ) ) {
			return $transient;
		}

		$release = wonderland_update_release();
		$asset   = $release['assets']['wonderland-blocks'] ?? null;
		$file    = wonderland_update_plugin_basename();

		if ( ! $asset || ! $asset['package'] ) {
			return $transient;
		}

		$update = (object) array(
			'slug'        => 'wonderland-blocks',
			'plugin'      => $file,
			'new_version' => $asset['version'],
			'url'         => $release['url'],
			'package'     => $asset['package'],
			'tested'      => get_bloginfo( 'version' ),
		);

		if ( version_compare( WONDERLAND_BLOCKS_VERSION, $asset['version'], '<' ) ) {
			$transient->response[ $file ] = $update;
		} else {
			// Listing it as "no update" is what puts the version and the "View
			// details" link on the plugins screen rather than nothing at all.
			$transient->no_update[ $file ] = $update;
		}

		return $transient;
	}
);

/**
 * Offer the theme update.
 */
add_filter(
	'pre_set_site_transient_update_themes',
	function ( $transient ) {
		if ( ! is_object( $transient ) ) {
			return $transient;
		}

		$release = wonderland_update_release();
		$asset   = $release['assets']['wonderland'] ?? null;
		if ( ! $asset || ! $asset['package'] ) {
			return $transient;
		}

		$theme = wp_get_theme( 'wonderland' );
		if ( ! $theme->exists() ) {
			return $transient;
		}

		$update = array(
			'theme'       => 'wonderland',
			'new_version' => $asset['version'],
			'url'         => $release['url'],
			'package'     => $asset['package'],
		);

		if ( version_compare( (string) $theme->get( 'Version' ), $asset['version'], '<' ) ) {
			$transient->response['wonderland'] = $update;
		} else {
			$transient->no_update['wonderland'] = $update;
		}

		return $transient;
	}
);

/**
 * The "View details" panel for the plugin.
 */
add_filter(
	'plugins_api',
	function ( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || 'wonderland-blocks' !== $args->slug ) {
			return $result;
		}

		$release = wonderland_update_release();
		$asset   = $release['assets']['wonderland-blocks'] ?? null;

		return (object) array(
			'name'          => 'Wonderland Blocks',
			'slug'          => 'wonderland-blocks',
			'version'       => $asset['version'] ?? WONDERLAND_BLOCKS_VERSION,
			'author'        => '<a href="https://github.com/creativorium">Creativorium</a>',
			'homepage'      => $release['url'],
			'download_link' => $asset['package'] ?? '',
			'last_updated'  => $release['published'],
			'sections'      => array(
				'description' => 'Native Gutenberg blocks for the Wonderland Bali theme, server-rendered and built with Vite.',
				'changelog'   => $release['notes'] ? wpautop( wp_kses_post( $release['notes'] ) ) : 'See the release on GitHub.',
			),
		);
	},
	10,
	3
);

/**
 * A deliberate check should be a real one.
 *
 * "Check again" on the updates screen clears WordPress's own transients; ours
 * would otherwise keep answering from up to twelve hours ago.
 */
add_action(
	'load-update-core.php',
	function () {
		if ( isset( $_GET['force-check'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			delete_site_transient( WONDERLAND_UPDATE_TRANSIENT );
		}
	}
);

/**
 * After an update runs, the cached release is stale by definition.
 */
add_action(
	'upgrader_process_complete',
	function () {
		delete_site_transient( WONDERLAND_UPDATE_TRANSIENT );
	},
	10,
	0
);
