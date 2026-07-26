<?php
/**
 * Front-end contact / request form handler.
 *
 * Forms POST to admin-post.php with action=wonderland_contact and a nonce.
 * On success we email the site and redirect back with #sent (or ?wl_sent=1).
 *
 * @package WonderlandBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Recipient address for form submissions. Filterable.
 */
function wonderland_form_recipient() {
	return apply_filters( 'wonderland_form_recipient', get_option( 'admin_email' ) );
}

function wonderland_handle_form() {
	$nonce = isset( $_POST['wl_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wl_nonce'] ) ) : '';
	$back  = isset( $_POST['wl_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['wl_redirect'] ) ) : home_url( '/' );

	if ( ! wp_verify_nonce( $nonce, 'wonderland_contact' ) ) {
		wp_safe_redirect( add_query_arg( 'wl_sent', 'error', $back ) );
		exit;
	}

	// Honeypot — bots fill this hidden field.
	if ( ! empty( $_POST['wl_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'wl_sent', '1', $back ) );
		exit;
	}

	$name    = isset( $_POST['wl_name'] ) ? sanitize_text_field( wp_unslash( $_POST['wl_name'] ) ) : '';
	$email   = isset( $_POST['wl_email'] ) ? sanitize_email( wp_unslash( $_POST['wl_email'] ) ) : '';
	$phone   = isset( $_POST['wl_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['wl_phone'] ) ) : '';
	$subject = isset( $_POST['wl_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['wl_subject'] ) ) : 'Website enquiry';
	$message = isset( $_POST['wl_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wl_message'] ) ) : '';

	$body  = "Name: $name\n";
	$body .= "Email: $email\n";
	$body .= "Phone: $phone\n\n";
	$body .= "Message:\n$message\n";

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
	if ( is_email( $email ) ) {
		$headers[] = 'Reply-To: ' . $name . ' <' . $email . '>';
	}

	wp_mail( wonderland_form_recipient(), '[Wonderland] ' . $subject, $body, $headers );

	wp_safe_redirect( add_query_arg( 'wl_sent', '1', $back ) . '#form' );
	exit;
}
add_action( 'admin_post_nopriv_wonderland_contact', 'wonderland_handle_form' );
add_action( 'admin_post_wonderland_contact', 'wonderland_handle_form' );

/**
 * Render a reusable form markup block.
 *
 * @param array $args heading, subject, button, message rows toggle.
 */
function wonderland_render_form( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'subject' => 'Website enquiry',
			'button'  => 'Send Message',
		)
	);
	$sent = isset( $_GET['wl_sent'] ) ? sanitize_text_field( wp_unslash( $_GET['wl_sent'] ) ) : '';
	ob_start();
	?>
	<form class="wl-form" id="form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php if ( '1' === $sent ) : ?>
			<p class="wl-form__notice is-success">Thank you! Your message has been sent — we'll be in touch soon.</p>
		<?php elseif ( 'error' === $sent ) : ?>
			<p class="wl-form__notice is-error">Sorry, something went wrong. Please try again.</p>
		<?php endif; ?>
		<input type="hidden" name="action" value="wonderland_contact" />
		<input type="hidden" name="wl_subject" value="<?php echo esc_attr( $args['subject'] ); ?>" />
		<input type="hidden" name="wl_redirect" value="<?php echo esc_url( get_permalink() ); ?>" />
		<?php wp_nonce_field( 'wonderland_contact', 'wl_nonce' ); ?>
		<p class="wl-form__hp"><label>Website<input type="text" name="wl_website" tabindex="-1" autocomplete="off" /></label></p>

		<div class="wl-form__row">
			<label>Name<input type="text" name="wl_name" required /></label>
			<label>Email<input type="email" name="wl_email" required /></label>
		</div>
		<label>Phone<input type="text" name="wl_phone" /></label>
		<label>Message<textarea name="wl_message" rows="5" required></textarea></label>
		<button type="submit" class="wl-form__submit"><?php echo esc_html( $args['button'] ); ?></button>
	</form>
	<?php
	return ob_get_clean();
}
