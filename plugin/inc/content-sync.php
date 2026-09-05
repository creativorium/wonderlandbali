<?php
/**
 * Apply a page's block markup from a pasted copy of content/pages/<slug>.html.
 *
 * Exists because live has no SSH/WP-CLI, so `wp post update` is not available
 * there — this is the same operation through wp-admin instead. It matters that
 * it is not just "paste into the block editor": WordPress's default save path
 * runs `balanceTags` on `content_save_pre`, which repairs unbalanced HTML tags
 * and can rewrite what is otherwise a precise, authored copy of the block
 * comments. This tool disables that filter the same way the project's own
 * deploy tooling does, so what is pasted is what gets stored, byte for byte.
 *
 * Lives on the Wonderland Forms settings screen rather than a page of its own:
 * a standalone admin page under a brand-new slug hit "Sorry, you are not
 * allowed to access this page" for an account that is genuinely an
 * Administrator, on a site running two capability-restricting security plugins
 * (restrict-user-access, really-simple-ssl) — deactivating restrict-user-access
 * did not clear it, and the settings screen was confirmed reachable throughout,
 * so folding this into that already-working page sidesteps whatever the new
 * slug was tripping, rather than chasing it further.
 *
 * @package WonderlandBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The form, printed inside wonderland_forms_settings_page().
 */
function wonderland_content_sync_section() {
	?>
	<h2 class="title"><?php esc_html_e( 'Apply page content', 'wonderland-blocks' ); ?></h2>
	<p class="description" style="max-width:70ch">
		<?php
		esc_html_e(
			'Replaces one page\'s content wholesale with what is pasted below — the same effect as ' .
			'`wp post update <id> content/pages/<slug>.html`, for sites with no WP-CLI. Copy the full ' .
			'contents of the matching file from content/pages/ in the repository, exactly as it is — ' .
			'do not retype or reformat it.',
			'wonderland-blocks'
		);
		?>
	</p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="wonderland_content_sync" />
		<?php wp_nonce_field( 'wonderland_content_sync' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="wcs-slug"><?php esc_html_e( 'Page slug', 'wonderland-blocks' ); ?></label></th>
				<td>
					<input id="wcs-slug" name="slug" type="text" class="regular-text" placeholder="home" required />
					<p class="description">
						<?php esc_html_e( 'The filename without .html — "home" for content/pages/home.html, "portfolio" for content/pages/portfolio.html.', 'wonderland-blocks' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wcs-content"><?php esc_html_e( 'File contents', 'wonderland-blocks' ); ?></label></th>
				<td>
					<textarea id="wcs-content" name="content" rows="18" class="large-text code" required
						placeholder="<!-- wp:wonderland/hero {...} /-->"></textarea>
				</td>
			</tr>
		</table>
		<p>
			<label>
				<input type="checkbox" name="confirm" value="1" required />
				<?php esc_html_e( 'I understand this replaces the page\'s content immediately, live.', 'wonderland-blocks' ); ?>
			</label>
		</p>
		<?php
		// A native required checkbox rather than a JS confirm(): a page a
		// browser has already dismissed a few dialogs on can silently suppress
		// further window.confirm() calls (Chrome's "prevent additional dialogs"),
		// which makes the button look like it does nothing at all — exactly what
		// happened here while testing.
		submit_button( __( 'Apply', 'wonderland-blocks' ), 'secondary', 'submit', false );
		?>
	</form>
	<?php
}

add_action(
	'admin_post_wonderland_content_sync',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You cannot do that.', 'wonderland-blocks' ) );
		}
		check_admin_referer( 'wonderland_content_sync' );

		$result = wonderland_content_sync_apply();

		set_transient(
			'wonderland_content_sync_result',
			is_wp_error( $result )
				? array( 'ok' => false, 'message' => $result->get_error_message() )
				: array( 'ok' => true ) + $result,
			MINUTE_IN_SECONDS
		);

		wp_safe_redirect( admin_url( 'admin.php?page=wonderland-forms&wl_content_sync=1' ) );
		exit;
	}
);

add_action(
	'admin_notices',
	function () {
		if ( ! isset( $_GET['wl_content_sync'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		$result = get_transient( 'wonderland_content_sync_result' );
		if ( ! is_array( $result ) ) {
			return;
		}
		delete_transient( 'wonderland_content_sync_result' );

		if ( ! empty( $result['ok'] ) ) {
			$message = sprintf(
				/* translators: 1: page slug, 2: post ID, 3: byte count. */
				__( 'Applied to "%1$s" (post #%2$d) — %3$s bytes written.', 'wonderland-blocks' ),
				$result['slug'],
				(int) $result['post_id'],
				number_format_i18n( $result['bytes'] )
			);
		} else {
			$message = $result['message'] ?? __( 'Something went wrong.', 'wonderland-blocks' );
		}

		printf(
			'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
			! empty( $result['ok'] ) ? 'success' : 'error',
			esc_html( $message )
		);
	}
);

/**
 * Find the page by slug and overwrite its content, exactly as pasted.
 *
 * @return array{slug:string,post_id:int,bytes:int}|WP_Error
 */
function wonderland_content_sync_apply() {
	$slug    = isset( $_POST['slug'] ) ? sanitize_title( wp_unslash( $_POST['slug'] ) ) : '';
	$content = isset( $_POST['content'] ) ? wp_unslash( $_POST['content'] ) : '';

	if ( ! $slug ) {
		return new WP_Error( 'wonderland_content_sync', __( 'Enter a page slug.', 'wonderland-blocks' ) );
	}
	if ( '' === trim( $content ) ) {
		return new WP_Error( 'wonderland_content_sync', __( 'Paste the file\'s contents first.', 'wonderland-blocks' ) );
	}

	$page = get_page_by_path( $slug );
	if ( ! $page ) {
		return new WP_Error(
			'wonderland_content_sync',
			sprintf( /* translators: %s: page slug. */ __( 'No page found with the slug "%s".', 'wonderland-blocks' ), $slug )
		);
	}

	// Same guard as the project's own local apply script: without it, WordPress
	// HTML-escapes the block comments' own quotes (`"` becomes `&quot;`) unless
	// the saving user has unfiltered_html, and separately, balanceTags can
	// rewrite tag structure that was authored precisely on purpose. An admin
	// pasting into this dedicated tool should get an exact, unmodified copy —
	// that is the entire point of the tool existing.
	kses_remove_filters();
	remove_filter( 'content_save_pre', 'balanceTags', 50 );

	$result = wp_update_post(
		array(
			'ID'           => $page->ID,
			'post_content' => wp_slash( $content ),
		),
		true
	);

	if ( is_wp_error( $result ) ) {
		return $result;
	}

	$stored = get_post( $page->ID )->post_content;
	if ( $stored !== $content ) {
		return new WP_Error(
			'wonderland_content_sync',
			__( 'Saved, but the stored content does not match what was pasted exactly — check the page before trusting it.', 'wonderland-blocks' )
		);
	}

	return array(
		'slug'    => $slug,
		'post_id' => $page->ID,
		'bytes'   => strlen( $content ),
	);
}
