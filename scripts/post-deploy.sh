#!/usr/bin/env bash
#
# Runs on the server after the files land. Makes sure the theme and plugins are
# active, optionally re-applies page content from the authoring records, and
# clears the cache.
#
# Expects: WP_PATH, DEPLOY_PATH, SYNC_CONTENT ("true" to apply content).
set -euo pipefail

WP_PATH="${WP_PATH:?WP_PATH not set}"
DEPLOY_PATH="${DEPLOY_PATH:?DEPLOY_PATH not set}"
SYNC_CONTENT="${SYNC_CONTENT:-false}"

# Bluehost does not always have wp-cli on PATH; fall back to a local copy.
if command -v wp >/dev/null 2>&1; then
	WP="wp"
else
	if [ ! -f "$DEPLOY_PATH/wp-cli.phar" ]; then
		curl -sSL -o "$DEPLOY_PATH/wp-cli.phar" \
			https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
	fi
	WP="php $DEPLOY_PATH/wp-cli.phar"
fi

wpc() { $WP --path="$WP_PATH" "$@"; }

echo "→ WordPress $(wpc core version)"

# --- make sure our code is switched on -------------------------------------
if [ "$(wpc theme get wonderland --field=status 2>/dev/null || echo missing)" != "active" ]; then
	echo "→ activating the Wonderland theme"
	wpc theme activate wonderland
fi

for plugin in wonderland-blocks wonderland-maintenance; do
	if ! wpc plugin is-active "$plugin" >/dev/null 2>&1; then
		echo "→ activating $plugin"
		wpc plugin activate "$plugin"
	fi
done

# --- page content -----------------------------------------------------------
# Off by default: this overwrites whatever is in the database, so it should be a
# deliberate choice rather than a side effect of every code deploy.
if [ "$SYNC_CONTENT" = "true" ]; then
	# Pages carried over from the Elementor era can still name a page template
	# that no longer exists (elementor_header_footer). wp_update_post() warns
	# about it and WP-CLI exits non-zero, which would abort the whole sync — so
	# clear the stale value first. Harmless when there is nothing to clear.
	echo "→ clearing page templates the theme no longer has"
	wpc eval '
		foreach ( get_posts( array( "post_type" => "page", "numberposts" => -1, "post_status" => "any" ) ) as $p ) {
			$t = get_post_meta( $p->ID, "_wp_page_template", true );
			if ( $t && "default" !== $t && ! locate_template( $t ) ) {
				update_post_meta( $p->ID, "_wp_page_template", "default" );
				echo "  reset " . $p->post_name . " (was " . $t . ")\n";
			}
		}
	'

	echo "→ applying page content from content/pages/"
	failed=0
	for file in "$DEPLOY_PATH"/pages/*.html; do
		[ -e "$file" ] || continue
		slug="$(basename "$file" .html)"

		# home.html is the front page, whose slug is 'home'.
		id="$(wpc post list --post_type=page --post_status=any --name="$slug" --field=ID | head -n1)"
		if [ -z "$id" ]; then
			echo "  ! no page with slug '$slug' — skipped"
			continue
		fi

		# One bad page should report itself, not take the deploy down with it.
		if wpc post update "$id" "$file" >/dev/null 2>&1; then
			echo "  ✓ $slug (ID $id)"
		else
			echo "  ✗ $slug (ID $id) — update failed"
			failed=$((failed + 1))
		fi
	done

	if [ "$failed" -gt 0 ]; then
		echo "→ $failed page(s) failed to update"
		exit 1
	fi
else
	echo "→ skipping content sync (SYNC_CONTENT=$SYNC_CONTENT)"
fi

# --- housekeeping -----------------------------------------------------------
# Rewrite rules can go stale when templates change (the blog archive especially).
wpc rewrite flush --hard >/dev/null 2>&1 || true
wpc litespeed-purge all >/dev/null 2>&1 || echo "→ (LiteSpeed purge unavailable — skipped)"
wpc cache flush >/dev/null 2>&1 || true

echo "→ done"
