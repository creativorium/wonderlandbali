<?php
/**
 * Brochure lead magnet.
 *
 * The brochure already existed as a plain download link, which gave the visitor
 * something and us nothing. It now opens a small dialog asking for a name and
 * email; on submit the address is stored with the other submissions and the file
 * downloads automatically.
 *
 * No AJAX: the form posts like every other form on the site and comes back to
 * the same page with ?wl_sent=1&wl_dl=brochure, which the front-end script uses
 * to start the download. One code path, and it still works if JavaScript fails
 * to load — the link falls back to opening the PDF directly.
 *
 * @package Wonderland
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URL of the brochure PDF.
 *
 * @return string
 */
function wonderland_brochure_url() {
	// New file rather than a replacement at the old path: a brochure sits behind
	// Cloudflare and a browser cache for months, and reusing the URL is how
	// people keep being handed last year's edition.
	return (string) apply_filters(
		'wonderland_brochure_url',
		content_url( '/uploads/2026/08/Wonderland-Brochure-2026-2027.pdf' )
	);
}

/**
 * The dialog markup, printed once per page.
 */
add_action(
	'wp_footer',
	function () {
		if ( ! function_exists( 'wonderland_render_form' ) ) {
			return; // blocks plugin inactive
		}
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only flags.
		$sent = isset( $_GET['wl_sent'] ) ? sanitize_text_field( wp_unslash( $_GET['wl_sent'] ) ) : '';
		$dl   = isset( $_GET['wl_dl'] ) ? sanitize_key( wp_unslash( $_GET['wl_dl'] ) ) : '';
		// phpcs:enable

		// They have already given us their details — asking again on the way out
		// reads like the send failed.
		$done = ( '1' === $sent && 'brochure' === $dl );
		?>
		<div class="wl-brochure" id="wl-brochure" hidden>
			<div class="wl-brochure__backdrop" data-brochure-close></div>
			<div class="wl-brochure__panel" role="dialog" aria-modal="true"
				aria-labelledby="wl-brochure-title">
				<button class="wl-brochure__close" data-brochure-close
					aria-label="<?php esc_attr_e( 'Close', 'wonderland' ); ?>">&times;</button>

				<?php if ( $done ) : ?>

					<h2 class="wl-brochure__title" id="wl-brochure-title">
						<?php esc_html_e( 'Thank you — your brochure is on the way', 'wonderland' ); ?>
					</h2>
					<p class="wl-brochure__text">
						<?php esc_html_e( 'The download starts on its own. We have your details and will be in touch within 24 hours.', 'wonderland' ); ?>
					</p>

					<?php
					// A browser can refuse a download it did not see a click for, so
					// there is always something to press. `data-brochure-file` keeps
					// it out of the interceptor that opens this dialog.
					?>
					<a class="wl-brochure__file" href="<?php echo esc_url( wonderland_brochure_url() ); ?>"
						download data-brochure-file>
						<?php esc_html_e( 'Download the brochure', 'wonderland' ); ?>
					</a>

				<?php else : ?>

					<h2 class="wl-brochure__title" id="wl-brochure-title">
						<?php esc_html_e( 'Get the 2026 brochure', 'wonderland' ); ?>
					</h2>
					<p class="wl-brochure__text">
						<?php esc_html_e( 'Packages, inclusions and real celebrations — tell us where to send it and it downloads straight away.', 'wonderland' ); ?>
					</p>

					<?php
					echo wonderland_render_form( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'preset'  => 'brochure',
							'subject' => 'Brochure download',
							'button'  => __( 'Send me the brochure', 'wonderland' ),
						)
					);
					?>

				<?php endif; ?>
			</div>
		</div>
		<?php
	},
	// Ahead of wp_print_footer_scripts (priority 20) so the dialog exists in the
	// DOM by the time main.js looks for it.
	5
);

/**
 * After a brochure submission, hand the front end the file to download.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only flags.
		$sent = isset( $_GET['wl_sent'] ) ? sanitize_text_field( wp_unslash( $_GET['wl_sent'] ) ) : '';
		$dl   = isset( $_GET['wl_dl'] ) ? sanitize_key( wp_unslash( $_GET['wl_dl'] ) ) : '';
		// phpcs:enable

		$form = isset( $_GET['wl_form'] ) ? sanitize_key( wp_unslash( $_GET['wl_form'] ) ) : '';

		// A brochure submission that failed — a missing field, the rate limit,
		// a captcha score — comes back without the download flag. Reopen the
		// dialog for it too, so the reason is on screen instead of hidden.
		if ( 'brochure' === $form && '1' !== $sent ) {
			wp_add_inline_script( 'wonderland-main', 'window.wlBrochureFailed = true;', 'before' );
			return;
		}

		if ( '1' !== $sent || 'brochure' !== $dl ) {
			return;
		}

		wp_add_inline_script(
			'wonderland-main',
			'window.wlBrochureDownload = ' . wp_json_encode( wonderland_brochure_url() ) . ';',
			'before'
		);
	},
	20
);

/**
 * Send brochure submissions back with the download flag attached.
 *
 * @param string $url    Redirect target chosen by the form handler.
 * @param string $preset Form preset.
 * @return string
 */
add_filter(
	'wonderland_form_redirect',
	function ( $url, $preset ) {
		if ( 'brochure' !== $preset ) {
			return $url;
		}
		return add_query_arg( 'wl_dl', 'brochure', $url );
	},
	10,
	2
);
