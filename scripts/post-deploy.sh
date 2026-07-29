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
	echo "→ applying page content from content/pages/"
	for file in "$DEPLOY_PATH"/pages/*.html; do
		[ -e "$file" ] || continue
		slug="$(basename "$file" .html)"

		# home.html is the front page, whose slug is 'home'.
		id="$(wpc post list --post_type=page --post_status=any --name="$slug" --field=ID | head -n1)"
		if [ -z "$id" ]; then
			echo "  ! no page with slug '$slug' — skipped"
			continue
		fi

		wpc post update "$id" "$file" >/dev/null
		echo "  ✓ $slug (ID $id)"
	done
else
	echo "→ skipping content sync (SYNC_CONTENT=$SYNC_CONTENT)"
fi

# --- housekeeping -----------------------------------------------------------
# Rewrite rules can go stale when templates change (the blog archive especially).
wpc rewrite flush --hard >/dev/null 2>&1 || true
wpc litespeed-purge all >/dev/null 2>&1 || echo "→ (LiteSpeed purge unavailable — skipped)"
wpc cache flush >/dev/null 2>&1 || true

echo "→ done"
