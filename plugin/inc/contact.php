<?php
/**
 * Front-end contact / request form handler.
 *
 * Forms POST to admin-post.php with action=wonderland_contact and a nonce.
 * On success we email the site and redirect back with ?wl_sent=1#form.
 *
 * Field sets are declared per preset (see wonderland_form_fields) so the same
 * handler serves the short Contact form and the long Make a Request form. The
 * submitted preset decides which fields are read — never the POST body — so a
 * bot can't inject extra rows into the email.
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

/**
 * Field definitions per form preset.
 *
 * Each field: label, type (text|email|tel|date|number|textarea|select|group),
 * required, placeholder, options (select), half (share a row with the next field).
 * A `group` entry is a visual section legend, not an input.
 *
 * @param string $preset 'contact' or 'request'.
 * @return array<string,array>
 */
function wonderland_form_fields( $preset = 'contact' ) {
	$contact = array(
		'first_name' => array(
			'label'    => 'First Name',
			'type'     => 'text',
			'required' => true,
			'half'     => true,
		),
		'last_name'  => array(
			'label'    => 'Last Name',
			'type'     => 'text',
			'required' => true,
			'half'     => true,
		),
		'email'      => array(
			'label'    => 'Email',
			'type'     => 'email',
			'required' => true,
			'half'     => true,
		),
		'phone'      => array(
			'label' => 'Phone',
			'type'  => 'tel',
			'half'  => true,
		),
		'message'    => array(
			'label'    => 'Message',
			'type'     => 'textarea',
			'required' => true,
		),
	);

	$request = array(
		'g_event'      => array(
			'label' => 'Your Event',
			'type'  => 'group',
		),
		'service'      => array(
			'label'    => 'Service You Need',
			'type'     => 'select',
			'required' => true,
			'options'  => wonderland_form_services(),
		),
		'region'       => array(
			'label'    => 'Region',
			'type'     => 'select',
			'required' => true,
			'options'  => wonderland_form_regions(),
			'half'     => true,
		),
		'country'      => array(
			'label'   => 'Select Your Country',
			'type'    => 'select',
			'options' => wonderland_form_countries(),
			'half'    => true,
		),
		'event_date'   => array(
			'label'    => 'Preferred Date',
			'type'     => 'date',
			'required' => true,
			'half'     => true,
		),
		'guest_count'  => array(
			'label'       => 'Guest Count',
			'type'        => 'number',
			'required'    => true,
			'placeholder' => 'e.g. 80',
			'half'        => true,
		),
		'budget'       => array(
			'label'       => 'Budget',
			'type'        => 'text',
			'required'    => true,
			'placeholder' => 'e.g. USD 20,000',
			'half'        => true,
		),
		'venue'        => array(
			'label'       => 'Venue Preferences',
			'type'        => 'text',
			'placeholder' => 'Cliffside, beachfront, jungle…',
			'half'        => true,
		),
		'g_contact'    => array(
			'label' => 'Contact Information',
			'type'  => 'group',
		),
		'bride_name'   => array(
			'label'    => "Bride's Full Name",
			'type'     => 'text',
			'required' => true,
			'half'     => true,
		),
		'partner_name' => array(
			'label' => "Partner's Full Name",
			'type'  => 'text',
			'half'  => true,
		),
		'email'        => array(
			'label'    => 'Email',
			'type'     => 'email',
			'required' => true,
			'half'     => true,
		),
		'phone'        => array(
			'label' => 'Phone Number',
			'type'  => 'tel',
			'half'  => true,
		),
		'message'      => array(
			'label'       => 'Message',
			'type'        => 'textarea',
			'placeholder' => 'Additional comments or special requests',
		),
	);

	$fields = ( 'request' === $preset ) ? $request : $contact;

	/**
	 * Filters the field set for a form preset.
	 *
	 * @param array  $fields Field definitions.
	 * @param string $preset Preset name.
	 */
	return apply_filters( 'wonderland_form_fields', $fields, $preset );
}

/**
 * Services offered, matching the service pages in the main menu.
 *
 * @return string[]
 */
function wonderland_form_services() {
	return apply_filters(
		'wonderland_form_services',
		array(
			'Wedding Planning & Styling',
			'Event Planning & Styling',
			'Indian Wedding',
			'Decoration',
			'Elopement',
			'Not sure yet — please advise',
		)
	);
}

/**
 * Regions we plan in — the destinations that have their own pages.
 *
 * @return string[]
 */
function wonderland_form_regions() {
	return apply_filters(
		'wonderland_form_regions',
		array(
			'Bali',
			'Portugal',
			'Italy',
		)
	);
}

/**
 * Country list for the request form. Bali/destination markets first, then A–Z.
 *
 * @return string[]
 */
function wonderland_form_countries() {
	$top  = array( 'Indonesia', 'Australia', 'Singapore', 'India', 'United Kingdom', 'United States' );
	$rest = array(
		'Austria', 'Belgium', 'Brazil', 'Canada', 'China', 'Denmark', 'Finland', 'France',
		'Germany', 'Hong Kong', 'Ireland', 'Italy', 'Japan', 'Malaysia', 'Mexico',
		'Netherlands', 'New Zealand', 'Norway', 'Philippines', 'Poland', 'Portugal', 'Qatar',
		'Russia', 'Saudi Arabia', 'South Africa', 'South Korea', 'Spain', 'Sweden',
		'Switzerland', 'Taiwan', 'Thailand', 'Turkey', 'United Arab Emirates', 'Vietnam',
	);
	return array_merge( $top, $rest, array( 'Other' ) );
}

/**
 * Handle a submitted form: validate, email, redirect back.
 */
function wonderland_handle_form() {
	$nonce = isset( $_POST['wl_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wl_nonce'] ) ) : '';
	$back  = isset( $_POST['wl_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['wl_redirect'] ) ) : home_url( '/' );

	if ( ! wp_verify_nonce( $nonce, 'wonderland_contact' ) ) {
		wp_safe_redirect( add_query_arg( 'wl_sent', 'error', $back ) );
		exit;
	}

	// Honeypot — bots fill this hidden field. Pretend success.
	if ( ! empty( $_POST['wl_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'wl_sent', '1', $back ) );
		exit;
	}

	$preset  = isset( $_POST['wl_preset'] ) ? sanitize_key( wp_unslash( $_POST['wl_preset'] ) ) : 'contact';
	$subject = isset( $_POST['wl_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['wl_subject'] ) ) : 'Website enquiry';
	$fields  = wonderland_form_fields( $preset );

	// reCAPTCHA v3 — only enforced once both keys are configured.
	$score = '';
	if ( function_exists( 'wonderland_recaptcha_active' ) && wonderland_recaptcha_active() ) {
		$token  = isset( $_POST['wl_recaptcha'] ) ? sanitize_text_field( wp_unslash( $_POST['wl_recaptcha'] ) ) : '';
		$result = wonderland_verify_recaptcha( $token );

		if ( ! $result['ok'] ) {
			wp_safe_redirect( add_query_arg( 'wl_sent', 'captcha', $back ) . '#form' );
			exit;
		}
		$score = $result['score'];
	}

	$lines      = array();
	$values     = array();
	$missing    = array();
	$reply_name = '';
	$reply_mail = '';

	foreach ( $fields as $key => $field ) {
		if ( 'group' === $field['type'] ) {
			$lines[] = '';
			$lines[] = strtoupper( $field['label'] );
			continue;
		}

		$raw = isset( $_POST[ 'wl_' . $key ] ) ? wp_unslash( $_POST[ 'wl_' . $key ] ) : '';

		if ( 'textarea' === $field['type'] ) {
			$value = sanitize_textarea_field( $raw );
		} elseif ( 'email' === $field['type'] ) {
			$value = sanitize_email( $raw );
		} else {
			$value = sanitize_text_field( $raw );
		}

		// A select must be one of its own options.
		if ( 'select' === $field['type'] && '' !== $value && ! in_array( $value, $field['options'], true ) ) {
			$value = '';
		}

		if ( '' === $value ) {
			// `required` in the markup only holds while the browser plays along;
			// a direct POST has to be turned away here too.
			if ( ! empty( $field['required'] ) ) {
				$missing[] = $field['label'];
			}
			continue;
		}

		$lines[]        = $field['label'] . ': ' . $value;
		$values[ $key ] = $value;

		if ( 'email' === $field['type'] && ! $reply_mail ) {
			$reply_mail = $value;
		}
		if ( ! $reply_name && in_array( $key, array( 'first_name', 'bride_name' ), true ) ) {
			$reply_name = $value;
		}
	}

	if ( $missing ) {
		wp_safe_redirect( add_query_arg( 'wl_sent', 'required', $back ) . '#form' );
		exit;
	}

	// Store first: a DB record survives a bouncing mail server.
	$submission_id = 0;
	if ( function_exists( 'wonderland_store_submission' ) ) {
		$submission_id = wonderland_store_submission(
			$preset,
			$values,
			array(
				'score' => $score,
				'ip'    => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
				'page'  => $back,
			)
		);
	}

	$body = trim( implode( "\n", $lines ) );
	if ( $submission_id ) {
		$body .= "\n\n" . __( 'View in the dashboard:', 'wonderland-blocks' ) . ' ' . get_edit_post_link( $submission_id, 'raw' );
	}
	$body .= "\n\n—\n" . __( 'Sent from', 'wonderland-blocks' ) . ' ' . home_url( '/' );

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
	if ( is_email( $reply_mail ) ) {
		$headers[] = 'Reply-To: ' . ( $reply_name ? $reply_name . ' <' . $reply_mail . '>' : $reply_mail );
	}

	$recipient = function_exists( 'wonderland_forms_recipient_for' )
		? wonderland_forms_recipient_for( $preset )
		: get_option( 'admin_email' );

	wp_mail(
		$recipient,
		sprintf( '[Wonderland] %s — %s', wonderland_form_label( $preset ), $subject ),
		$body,
		$headers
	);

	wp_safe_redirect( add_query_arg( 'wl_sent', '1', $back ) . '#form' );
	exit;
}

/**
 * Verify a reCAPTCHA v3 token with Google.
 *
 * @param string $token Token from the front end.
 * @return array{ok:bool,score:string}
 */
function wonderland_verify_recaptcha( $token ) {
	$fail = array(
		'ok'    => false,
		'score' => '',
	);

	if ( ! $token ) {
		return $fail;
	}

	$settings = wonderland_forms_settings();

	$response = wp_remote_post(
		'https://www.google.com/recaptcha/api/siteverify',
		array(
			'timeout' => 10,
			'body'    => array(
				'secret'   => $settings['recaptcha_secret'],
				'response' => $token,
				'remoteip' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		// Google unreachable — don't punish a real visitor for our network.
		return array(
			'ok'    => true,
			'score' => '',
		);
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $data ) || empty( $data['success'] ) ) {
		return $fail;
	}

	$score     = isset( $data['score'] ) ? (float) $data['score'] : 0.0;
	$threshold = (float) $settings['recaptcha_threshold'];

	return array(
		'ok'    => $score >= $threshold,
		'score' => (string) $score,
	);
}
/**
 * Forms post back to their own page URL and are picked up here.
 *
 * We deliberately do NOT use admin-post.php: the site runs a rename-login plugin
 * (change-wp-admin-login) which bounces logged-out requests to /wp-admin/* back
 * to the home page, so admin-post.php silently swallowed every submission.
 */
add_action(
	'init',
	function () {
		if ( ! isset( $_POST['wl_action'] ) || 'wonderland_contact' !== $_POST['wl_action'] ) {
			return;
		}
		wonderland_handle_form();
	}
);

/**
 * Render one field's markup.
 *
 * @param string $key   Field key (without the wl_ prefix).
 * @param array  $field Field definition.
 */
function wonderland_render_field( $key, $field ) {
	if ( 'group' === $field['type'] ) {
		printf( '<h3 class="wl-form__group">%s</h3>', esc_html( $field['label'] ) );
		return;
	}

	$id       = 'wl-' . $key;
	$name     = 'wl_' . $key;
	$required = ! empty( $field['required'] );
	$classes  = 'wl-form__field' . ( ! empty( $field['half'] ) ? ' is-half' : '' );
	?>
	<p class="<?php echo esc_attr( $classes ); ?>">
		<label for="<?php echo esc_attr( $id ); ?>">
			<?php echo esc_html( $field['label'] ); ?>
			<?php if ( $required ) : ?><span class="wl-form__req" aria-hidden="true">*</span><?php endif; ?>
		</label>
		<?php if ( 'textarea' === $field['type'] ) : ?>
			<textarea
				id="<?php echo esc_attr( $id ); ?>"
				name="<?php echo esc_attr( $name ); ?>"
				rows="5"
				placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"
				<?php echo $required ? 'required' : ''; ?>></textarea>
		<?php elseif ( 'select' === $field['type'] ) : ?>
			<select
				id="<?php echo esc_attr( $id ); ?>"
				name="<?php echo esc_attr( $name ); ?>"
				<?php echo $required ? 'required' : ''; ?>>
				<option value="">Please select…</option>
				<?php foreach ( $field['options'] as $option ) : ?>
					<option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></option>
				<?php endforeach; ?>
			</select>
		<?php else : ?>
			<input
				type="<?php echo esc_attr( $field['type'] ); ?>"
				id="<?php echo esc_attr( $id ); ?>"
				name="<?php echo esc_attr( $name ); ?>"
				placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"
				<?php echo $required ? 'required' : ''; ?> />
		<?php endif; ?>
	</p>
	<?php
}

/**
 * Render a reusable form.
 *
 * @param array $args preset, subject, button.
 * @return string
 */
function wonderland_render_form( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'preset'  => 'contact',
			'subject' => 'Website enquiry',
			'button'  => 'Send Message',
		)
	);

	$preset = in_array( $args['preset'], array( 'contact', 'request' ), true ) ? $args['preset'] : 'contact';
	$fields = wonderland_form_fields( $preset );
	$sent   = isset( $_GET['wl_sent'] ) ? sanitize_text_field( wp_unslash( $_GET['wl_sent'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	ob_start();
	?>
	<form class="wl-form wl-form--<?php echo esc_attr( $preset ); ?>" id="form" method="post" action="<?php echo esc_url( get_permalink() ); ?>">
		<?php if ( '1' === $sent ) : ?>
			<p class="wl-form__notice is-success" role="status">Thank you! Your message has been sent — we'll be in touch soon.</p>
		<?php elseif ( 'captcha' === $sent ) : ?>
			<p class="wl-form__notice is-error" role="alert">We couldn't verify that you're human. Please reload the page and try again.</p>
		<?php elseif ( 'required' === $sent ) : ?>
			<p class="wl-form__notice is-error" role="alert">Please fill in every field marked with an asterisk and send again.</p>
		<?php elseif ( 'error' === $sent ) : ?>
			<p class="wl-form__notice is-error" role="alert">Sorry, something went wrong. Please try again.</p>
		<?php endif; ?>

		<input type="hidden" name="wl_action" value="wonderland_contact" />
		<input type="hidden" name="wl_preset" value="<?php echo esc_attr( $preset ); ?>" />
		<input type="hidden" name="wl_subject" value="<?php echo esc_attr( $args['subject'] ); ?>" />
		<input type="hidden" name="wl_redirect" value="<?php echo esc_url( get_permalink() ); ?>" />
		<?php wp_nonce_field( 'wonderland_contact', 'wl_nonce' ); ?>
		<p class="wl-form__hp"><label>Website<input type="text" name="wl_website" tabindex="-1" autocomplete="off" /></label></p>

		<div class="wl-form__grid">
			<?php foreach ( $fields as $key => $field ) : ?>
				<?php wonderland_render_field( $key, $field ); ?>
			<?php endforeach; ?>
		</div>

		<?php if ( wonderland_recaptcha_active() ) : ?>
			<?php $site_key = wonderland_forms_settings()['recaptcha_site']; ?>
			<input type="hidden" name="wl_recaptcha" value="" />
			<p class="wl-form__legal">
				Protected by reCAPTCHA — Google's
				<a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Privacy Policy</a> and
				<a href="https://policies.google.com/terms" target="_blank" rel="noopener">Terms</a> apply.
			</p>
			<script src="https://www.google.com/recaptcha/api.js?render=<?php echo esc_attr( $site_key ); ?>" defer></script>
			<script>
			// Fetch a fresh token at submit time — v3 tokens expire after two minutes.
			( function () {
				var form = document.currentScript.closest( 'form' );
				if ( ! form ) { return; }
				var sending = false;
				form.addEventListener( 'submit', function ( e ) {
					if ( sending || ! window.grecaptcha ) { return; }
					e.preventDefault();
					grecaptcha.ready( function () {
						grecaptcha.execute( '<?php echo esc_js( $site_key ); ?>', { action: 'wonderland_form' } )
							.then( function ( token ) {
								form.querySelector( '[name="wl_recaptcha"]' ).value = token;
								sending = true;
								form.submit();
							} )
							.catch( function () { sending = true; form.submit(); } );
					} );
				} );
			} )();
			</script>
		<?php endif; ?>

		<button type="submit" class="wl-form__submit"><?php echo esc_html( $args['button'] ); ?></button>
	</form>
	<?php
	return ob_get_clean();
}
