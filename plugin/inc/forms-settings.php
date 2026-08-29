<?php
/**
 * Wonderland Forms — one settings screen for every form on the site.
 *
 * Covers where submissions are emailed, whether they are stored in the database,
 * and the reCAPTCHA v3 keys. Both the Contact and Make a Request forms read from
 * here, so there is a single place to configure them.
 *
 * @package WonderlandBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WONDERLAND_FORMS_OPTION', 'wonderland_forms' );

/**
 * Stored form settings, merged over defaults.
 *
 * @return array
 */
function wonderland_forms_settings() {
	$defaults = array(
		'recipient'          => get_option( 'admin_email' ),
		'recipient_request'  => '',
		'cc'                 => '',
		'bcc'                => '',
		'store_submissions'  => 1,
		'recaptcha_site'     => '',
		'recaptcha_secret'   => '',
		'recaptcha_threshold' => '0.5',
	);

	$saved = get_option( WONDERLAND_FORMS_OPTION, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	return wp_parse_args( $saved, $defaults );
}

/**
 * Where a given form's submissions should be emailed.
 *
 * Falls back to the general recipient when no per-form address is set.
 *
 * @param string $preset Form preset.
 * @return string
 */
function wonderland_forms_recipient_for( $preset = 'contact' ) {
	$s = wonderland_forms_settings();

	if ( 'request' === $preset && ! empty( $s['recipient_request'] ) ) {
		$to = $s['recipient_request'];
	} else {
		$to = $s['recipient'];
	}

	if ( ! is_email( $to ) ) {
		$to = get_option( 'admin_email' );
	}

	/** Filters the recipient for a form preset. */
	return apply_filters( 'wonderland_form_recipient', $to, $preset );
}

/**
 * Clean a comma-separated list of addresses, dropping anything invalid.
 *
 * @param string $value Raw list.
 * @return string Comma-separated valid addresses.
 */
function wonderland_sanitize_email_list( $value ) {
	$parts = preg_split( '/[,;\s]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY );
	$clean = array();

	foreach ( (array) $parts as $part ) {
		$mail = sanitize_email( $part );
		if ( is_email( $mail ) ) {
			$clean[] = $mail;
		}
	}

	return implode( ', ', array_unique( $clean ) );
}

/**
 * Mail headers for a submission: content type, and the copies configured in
 * settings. Reply-To is added by the caller, which knows the enquirer.
 *
 * @param string $preset Form preset.
 * @return string[]
 */
function wonderland_form_mail_headers( $preset = 'contact' ) {
	$s       = wonderland_forms_settings();
	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

	foreach ( array( 'Cc' => 'cc', 'Bcc' => 'bcc' ) as $header => $key ) {
		$list = wonderland_sanitize_email_list( $s[ $key ] ?? '' );
		if ( $list ) {
			$headers[] = $header . ': ' . $list;
		}
	}

	/**
	 * Filters the headers on a submission notification.
	 *
	 * @param string[] $headers Headers.
	 * @param string   $preset  Form preset.
	 */
	return apply_filters( 'wonderland_form_mail_headers', $headers, $preset );
}

/**
 * Whether reCAPTCHA v3 is fully configured.
 *
 * @return bool
 */
function wonderland_recaptcha_active() {
	$s = wonderland_forms_settings();
	return ! empty( $s['recaptcha_site'] ) && ! empty( $s['recaptcha_secret'] );
}

/* -------------------------------------------------------------------------
 * Settings screen (under the Wonderland Forms menu registered in submissions.php)
 * ---------------------------------------------------------------------- */

add_action(
	'admin_init',
	function () {
		register_setting(
			'wonderland_forms_group',
			WONDERLAND_FORMS_OPTION,
			array(
				'sanitize_callback' => 'wonderland_forms_sanitize',
				'default'           => array(),
			)
		);
	}
);

/**
 * Sanitize the settings form.
 *
 * @param array $in Raw values.
 * @return array
 */
function wonderland_forms_sanitize( $in ) {
	$in = is_array( $in ) ? $in : array();

	$threshold = isset( $in['recaptcha_threshold'] ) ? (float) $in['recaptcha_threshold'] : 0.5;
	$threshold = max( 0, min( 1, $threshold ) );

	return array(
		'recipient'           => sanitize_email( $in['recipient'] ?? '' ),
		'recipient_request'   => sanitize_email( $in['recipient_request'] ?? '' ),
		'cc'                  => wonderland_sanitize_email_list( $in['cc'] ?? '' ),
		'bcc'                 => wonderland_sanitize_email_list( $in['bcc'] ?? '' ),
		'store_submissions'   => empty( $in['store_submissions'] ) ? 0 : 1,
		'recaptcha_site'      => sanitize_text_field( $in['recaptcha_site'] ?? '' ),
		'recaptcha_secret'    => sanitize_text_field( $in['recaptcha_secret'] ?? '' ),
		'recaptcha_threshold' => (string) $threshold,
	);
}

/**
 * Render the settings screen.
 */
function wonderland_forms_settings_page() {
	$s    = wonderland_forms_settings();
	$name = WONDERLAND_FORMS_OPTION;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Wonderland Forms — Settings', 'wonderland-blocks' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'These settings apply to every Wonderland form on the site — Contact and Make a Request.', 'wonderland-blocks' ); ?>
		</p>

		<form method="post" action="options.php">
			<?php settings_fields( 'wonderland_forms_group' ); ?>

			<h2 class="title"><?php esc_html_e( 'Where submissions go', 'wonderland-blocks' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="wlf-recipient"><?php esc_html_e( 'Notification email', 'wonderland-blocks' ); ?></label></th>
					<td>
						<input id="wlf-recipient" class="regular-text" type="email" name="<?php echo esc_attr( $name ); ?>[recipient]" value="<?php echo esc_attr( $s['recipient'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Every form is emailed here unless overridden below.', 'wonderland-blocks' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wlf-recipient-request"><?php esc_html_e( 'Make a Request email', 'wonderland-blocks' ); ?></label></th>
					<td>
						<input id="wlf-recipient-request" class="regular-text" type="email" name="<?php echo esc_attr( $name ); ?>[recipient_request]" value="<?php echo esc_attr( $s['recipient_request'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Optional. Send booking requests to a different address than general enquiries.', 'wonderland-blocks' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wlf-cc"><?php esc_html_e( 'Cc', 'wonderland-blocks' ); ?></label></th>
					<td>
						<input id="wlf-cc" class="regular-text" type="text" name="<?php echo esc_attr( $name ); ?>[cc]" value="<?php echo esc_attr( $s['cc'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Optional. Comma-separated. Everyone here can see the other recipients.', 'wonderland-blocks' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wlf-bcc"><?php esc_html_e( 'Bcc', 'wonderland-blocks' ); ?></label></th>
					<td>
						<input id="wlf-bcc" class="regular-text" type="text" name="<?php echo esc_attr( $name ); ?>[bcc]" value="<?php echo esc_attr( $s['bcc'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Optional. Comma-separated. Hidden from the other recipients — use this for an archive address.', 'wonderland-blocks' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Store in database', 'wonderland-blocks' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[store_submissions]" value="1" <?php checked( ! empty( $s['store_submissions'] ) ); ?> />
							<?php esc_html_e( 'Save every submission so it can be read under Wonderland Forms → Submissions', 'wonderland-blocks' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Recommended — email can bounce or land in spam; the stored copy never does.', 'wonderland-blocks' ); ?></p>
					</td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Spam protection — reCAPTCHA v3', 'wonderland-blocks' ); ?></h2>
			<p class="description">
				<?php
				printf(
					/* translators: %s: link to the Google reCAPTCHA admin console. */
					esc_html__( 'Create a v3 key pair at %s, then paste both keys below. Leave them empty to turn reCAPTCHA off (the hidden honeypot field still runs).', 'wonderland-blocks' ),
					'<a href="https://www.google.com/recaptcha/admin" target="_blank" rel="noopener">google.com/recaptcha/admin</a>'
				);
				?>
			</p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="wlf-rc-site"><?php esc_html_e( 'Site key', 'wonderland-blocks' ); ?></label></th>
					<td><input id="wlf-rc-site" class="regular-text" type="text" name="<?php echo esc_attr( $name ); ?>[recaptcha_site]" value="<?php echo esc_attr( $s['recaptcha_site'] ); ?>" autocomplete="off" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="wlf-rc-secret"><?php esc_html_e( 'Secret key', 'wonderland-blocks' ); ?></label></th>
					<td><input id="wlf-rc-secret" class="regular-text" type="password" name="<?php echo esc_attr( $name ); ?>[recaptcha_secret]" value="<?php echo esc_attr( $s['recaptcha_secret'] ); ?>" autocomplete="off" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="wlf-rc-threshold"><?php esc_html_e( 'Score threshold', 'wonderland-blocks' ); ?></label></th>
					<td>
						<input id="wlf-rc-threshold" type="number" step="0.1" min="0" max="1" name="<?php echo esc_attr( $name ); ?>[recaptcha_threshold]" value="<?php echo esc_attr( $s['recaptcha_threshold'] ); ?>" />
						<p class="description"><?php esc_html_e( 'v3 scores each visitor from 0.0 (bot) to 1.0 (human). 0.5 is Google\'s default; raise it if spam gets through, lower it if real enquiries are blocked.', 'wonderland-blocks' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Status', 'wonderland-blocks' ); ?></th>
					<td>
						<?php if ( wonderland_recaptcha_active() ) : ?>
							<span style="color:#00a32a;font-weight:600">&#10003; <?php esc_html_e( 'Active', 'wonderland-blocks' ); ?></span>
						<?php else : ?>
							<span style="color:#996800;font-weight:600">&#9679; <?php esc_html_e( 'Not configured — honeypot only', 'wonderland-blocks' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>

		<hr />

		<h2 class="title"><?php esc_html_e( 'Test the delivery', 'wonderland-blocks' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'Sends a made-up enquiry through the same path a real one takes — same recipients, same Cc and Bcc, same mailer. Nothing is saved to Submissions. Save your changes first: the test uses the settings as stored.', 'wonderland-blocks' ); ?>
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="wonderland_test_email" />
			<?php wp_nonce_field( 'wonderland_test_email' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="wlf-test-preset"><?php esc_html_e( 'Pretend it is', 'wonderland-blocks' ); ?></label></th>
					<td>
						<select id="wlf-test-preset" name="preset">
							<?php foreach ( array( 'contact', 'request' ) as $preset ) : ?>
								<option value="<?php echo esc_attr( $preset ); ?>"><?php echo esc_html( wonderland_form_label( $preset ) ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wlf-test-to"><?php esc_html_e( 'Send it to', 'wonderland-blocks' ); ?></label></th>
					<td>
						<input id="wlf-test-to" class="regular-text" type="email" name="to"
							placeholder="<?php echo esc_attr( wonderland_forms_recipient_for( 'contact' ) ); ?>" />
						<p class="description">
							<?php
							printf(
								/* translators: %s: the configured recipient address. */
								esc_html__( 'Leave empty for the full dress rehearsal — %s plus the Cc and Bcc above. Put an address here to spot-check delivery instead: it goes there alone, with no copies.', 'wonderland-blocks' ),
								'<strong>' . esc_html( wonderland_forms_recipient_for( 'contact' ) ) . '</strong>'
							);
							?>
						</p>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Send test email', 'wonderland-blocks' ), 'secondary', 'submit', false ); ?>
		</form>
	</div>
	<?php
}
