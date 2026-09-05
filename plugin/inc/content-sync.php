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
 * @package WonderlandBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'wonderland-forms',
			__( 'Apply Page Content', 'wonderland-blocks' ),
			__( 'Apply Page Content', 'wonderland-blocks' ),
			'manage_options',
			'wonderland-content-sync',
			'wonderland_content_sync_page'
		);
	}
);

/**
 * The admin screen.
 */
function wonderland_content_sync_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You cannot access this page.', 'wonderland-blocks' ) );
	}

	$result = null;
	if ( isset( $_POST['wonderland_content_sync_nonce'] ) && check_admin_referer( 'wonderland_content_sync', 'wonderland_content_sync_nonce' ) ) {
		$result = wonderland_content_sync_apply();
	}

	$slug    = isset( $_POST['slug'] ) ? sanitize_title( wp_unslash( $_POST['slug'] ) ) : '';
	$content = isset( $_POST['content'] ) ? wp_unslash( $_POST['content'] ) : ''; // Raw on purpose — see wonderland_content_sync_apply().
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Apply Page Content', 'wonderland-blocks' ); ?></h1>
		<p class="description" style="max-width:70ch">
			<?php
			esc_html_e(
				'Replaces one page\'s content wholesale with what is pasted below — the same effect as ' .
				'`wp post update <id> content/pages/<slug>.html`, for the sites that have no WP-CLI. ' .
				'Copy the full contents of the matching file from content/pages/ in the repository, exactly ' .
				'as it is — do not retype or reformat it.',
				'wonderland-blocks'
			);
			?>
		</p>

		<?php if ( is_wp_error( $result ) ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $result->get_error_message() ); ?></p></div>
		<?php elseif ( is_array( $result ) ) : ?>
			<div class="notice notice-success">
				<p>
					<?php
					printf(
						/* translators: 1: page slug, 2: post ID, 3: byte count. */
						esc_html__( 'Applied to "%1$s" (post #%2$d) — %3$s bytes written.', 'wonderland-blocks' ),
						esc_html( $result['slug'] ),
						(int) $result['post_id'],
						esc_html( number_format_i18n( $result['bytes'] ) )
					);
					?>
					<a href="<?php echo esc_url( get_permalink( $result['post_id'] ) ); ?>" target="_blank" rel="noopener">
						<?php esc_html_e( 'View the page', 'wonderland-blocks' ); ?>
					</a>
				</p>
			</div>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'wonderland_content_sync', 'wonderland_content_sync_nonce' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="wcs-slug"><?php esc_html_e( 'Page slug', 'wonderland-blocks' ); ?></label></th>
					<td>
						<input id="wcs-slug" name="slug" type="text" class="regular-text" value="<?php echo esc_attr( $slug ); ?>"
							placeholder="home" required />
						<p class="description">
							<?php esc_html_e( 'The filename without .html — "home" for content/pages/home.html, "portfolio" for content/pages/portfolio.html.', 'wonderland-blocks' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wcs-content"><?php esc_html_e( 'File contents', 'wonderland-blocks' ); ?></label></th>
					<td>
						<textarea id="wcs-content" name="content" rows="18" class="large-text code" required
							placeholder="<!-- wp:wonderland/hero {...} /-->"><?php echo esc_textarea( $content ); ?></textarea>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Apply', 'wonderland-blocks' ), 'primary', 'submit', true, array( 'onclick' => "return confirm('" . esc_js( __( 'This replaces the page\'s content immediately, live. Continue?', 'wonderland-blocks' ) ) . "');" ) ); ?>
		</form>
	</div>
	<?php
}

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
