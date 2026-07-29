<?php
/**
 * Persistent enquiry CTA — a floating WhatsApp button on every page.
 *
 * WhatsApp is the lowest-friction channel for a destination audience, and the
 * strategy review found the only route to it was a small footer icon. This puts
 * it one thumb-tap away everywhere, with a quiet radar pulse to draw the eye
 * without nagging.
 *
 * Numbers and copy are filterable so nothing here is hard-coded for good.
 *
 * @package Wonderland
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The WhatsApp number, digits only, in international format.
 *
 * @return string
 */
function wonderland_whatsapp_number() {
	return (string) apply_filters( 'wonderland_whatsapp_number', '6287861138090' );
}

/**
 * Whether to show the floating CTA on this request.
 *
 * Hidden on the request and contact pages: the visitor is already in the middle
 * of getting in touch, and a floating button over a form is just an obstacle.
 *
 * @return bool
 */
function wonderland_show_floating_cta() {
	$show = ! is_404() && ! is_page( array( 'request', 'contact' ) );

	/**
	 * Filters whether the floating WhatsApp CTA renders.
	 *
	 * @param bool $show Whether to render.
	 */
	return (bool) apply_filters( 'wonderland_show_floating_cta', $show );
}

add_action(
	'wp_footer',
	function () {
		if ( ! wonderland_show_floating_cta() ) {
			return;
		}

		$number = wonderland_whatsapp_number();
		if ( ! $number ) {
			return;
		}

		$message = apply_filters(
			'wonderland_whatsapp_message',
			"Hi Wonderland! I'd like to ask about planning an event in Bali."
		);

		$href = 'https://wa.me/' . rawurlencode( $number ) . '?text=' . rawurlencode( $message );
		$label = __( 'Chat with us on WhatsApp', 'wonderland' );
		?>
		<a class="wl-wa" href="<?php echo esc_url( $href ); ?>"
			target="_blank" rel="noopener"
			aria-label="<?php echo esc_attr( $label ); ?>"
			data-wa-cta>
			<span class="wl-wa__radar" aria-hidden="true"></span>
			<span class="wl-wa__radar wl-wa__radar--2" aria-hidden="true"></span>
			<span class="wl-wa__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="currentColor" focusable="false">
					<path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 2a8 8 0 1 1-4.1 14.9l-.3-.2-2.6.7.7-2.5-.2-.3A8 8 0 0 1 12 4zm-3 4.3c-.2 0-.5 0-.8.3-.3.3-1 1-1 2.4s1 2.7 1.2 2.9c.1.2 2 3.1 4.8 4.3 2.4 1 2.9.8 3.4.7.6 0 1.7-.7 2-1.4.2-.7.2-1.2.2-1.3-.1-.1-.3-.2-.6-.4l-2-.9c-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.6.1a8 8 0 0 1-2.4-1.5 9 9 0 0 1-1.7-2c-.2-.3 0-.4.1-.6l.5-.5.3-.6c.1-.2 0-.4 0-.5l-.9-2.1c-.2-.5-.4-.4-.6-.4z"/>
				</svg>
			</span>
			<span class="screen-reader-text"><?php echo esc_html( $label ); ?></span>
		</a>
		<?php
	},
	20
);
