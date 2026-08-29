<?php
/**
 * Delivery of a stored submission's notification email, with retries.
 *
 * wp_mail() returning false means the mailer refused the handoff — SMTP down,
 * credentials rejected, the host throttling. The enquiry is already in the
 * database by then, so nothing is lost, but nobody is told about it either.
 * This layer keeps the composed message on the submission and re-attempts it on
 * WP-Cron up to WONDERLAND_MAIL_MAX_ATTEMPTS times.
 *
 * The `_wl_mail_sent` flag is the guard against a second copy: it is set the
 * moment a send succeeds, and every entry point checks it first. A retry that
 * finds it set does nothing, so a queued job left over from an attempt that
 * actually got through cannot mail the office twice.
 *
 * @package WonderlandBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const WONDERLAND_MAIL_MAX_ATTEMPTS = 3;
const WONDERLAND_MAIL_RETRY_HOOK   = 'wonderland_retry_submission_mail';

/**
 * Minutes to wait before attempt N+1. Short first, then long enough for a
 * blocked SMTP account or a throttling host to come back.
 *
 * @param int $attempts Attempts already made.
 * @return int Delay in seconds.
 */
function wonderland_mail_retry_delay( $attempts ) {
	$schedule = array( 1 => 5 * MINUTE_IN_SECONDS, 2 => 30 * MINUTE_IN_SECONDS );
	return isset( $schedule[ $attempts ] ) ? $schedule[ $attempts ] : HOUR_IN_SECONDS;
}

/**
 * Send a submission's notification, recording the outcome on the submission.
 *
 * @param int   $submission_id Submission post ID, or 0 when storage is off.
 * @param array $payload       to, subject, body, headers.
 * @return bool Whether the mailer accepted the message.
 */
function wonderland_send_submission_mail( $submission_id, $payload ) {
	// No stored submission means nothing to retry against: one attempt, and the
	// visitor still gets their confirmation because the form does not depend on
	// the mail going out.
	if ( ! $submission_id ) {
		return (bool) wp_mail( $payload['to'], $payload['subject'], $payload['body'], $payload['headers'] );
	}

	if ( get_post_meta( $submission_id, '_wl_mail_sent', true ) ) {
		return true;
	}

	$attempts = (int) get_post_meta( $submission_id, '_wl_mail_attempts', true );
	if ( $attempts >= WONDERLAND_MAIL_MAX_ATTEMPTS ) {
		return false;
	}

	// Kept so a retry sends the same message rather than rebuilding it from
	// meta and risking a different one.
	update_post_meta( $submission_id, '_wl_mail_payload', $payload );
	update_post_meta( $submission_id, '_wl_mail_attempts', $attempts + 1 );

	// wp_mail() only reports a boolean; the reason arrives on this hook.
	$error = '';
	$catch = function ( $wp_error ) use ( &$error ) {
		$error = $wp_error->get_error_message();
	};
	add_action( 'wp_mail_failed', $catch );

	$ok = (bool) wp_mail( $payload['to'], $payload['subject'], $payload['body'], $payload['headers'] );

	remove_action( 'wp_mail_failed', $catch );

	if ( $ok ) {
		update_post_meta( $submission_id, '_wl_mail_sent', 1 );
		update_post_meta( $submission_id, '_wl_mail_sent_at', current_time( 'mysql' ) );
		delete_post_meta( $submission_id, '_wl_mail_error' );
		wonderland_unschedule_submission_mail( $submission_id );
		return true;
	}

	update_post_meta( $submission_id, '_wl_mail_error', $error ? $error : __( 'The mailer refused the message.', 'wonderland-blocks' ) );
	wonderland_schedule_submission_mail( $submission_id );

	return false;
}

/**
 * Queue the next attempt, unless the cap is reached or one is already queued.
 *
 * @param int $submission_id Submission post ID.
 */
function wonderland_schedule_submission_mail( $submission_id ) {
	$attempts = (int) get_post_meta( $submission_id, '_wl_mail_attempts', true );
	if ( $attempts >= WONDERLAND_MAIL_MAX_ATTEMPTS ) {
		// Spent. Clear anything still queued so a stale job cannot wake up after
		// the cap and mail an enquiry the office has already chased by hand.
		wonderland_unschedule_submission_mail( $submission_id );
		return;
	}

	$args = array( (int) $submission_id );
	if ( wp_next_scheduled( WONDERLAND_MAIL_RETRY_HOOK, $args ) ) {
		return;
	}

	wp_schedule_single_event( time() + wonderland_mail_retry_delay( $attempts ), WONDERLAND_MAIL_RETRY_HOOK, $args );
}

/**
 * Drop any queued attempt for a submission.
 *
 * @param int $submission_id Submission post ID.
 */
function wonderland_unschedule_submission_mail( $submission_id ) {
	$args = array( (int) $submission_id );
	$next = wp_next_scheduled( WONDERLAND_MAIL_RETRY_HOOK, $args );
	if ( $next ) {
		wp_unschedule_event( $next, WONDERLAND_MAIL_RETRY_HOOK, $args );
	}
}

/**
 * The retry itself.
 */
add_action(
	WONDERLAND_MAIL_RETRY_HOOK,
	function ( $submission_id ) {
		$payload = get_post_meta( $submission_id, '_wl_mail_payload', true );
		if ( ! is_array( $payload ) || empty( $payload['to'] ) ) {
			return;
		}
		wonderland_send_submission_mail( $submission_id, $payload );
	}
);

/**
 * Delivery state for the admin, as a label and a CSS colour.
 *
 * @param int $submission_id Submission post ID.
 * @return array{status:string,label:string,colour:string,detail:string}
 */
function wonderland_mail_status( $submission_id ) {
	$attempts = (int) get_post_meta( $submission_id, '_wl_mail_attempts', true );
	$error    = (string) get_post_meta( $submission_id, '_wl_mail_error', true );

	if ( get_post_meta( $submission_id, '_wl_mail_sent', true ) ) {
		$at = get_post_meta( $submission_id, '_wl_mail_sent_at', true );
		return array(
			'status' => 'sent',
			'label'  => __( 'Sent', 'wonderland-blocks' ),
			'colour' => '#2e7d32',
			'detail' => $at ? sprintf( /* translators: %s: date and time. */ __( 'Emailed %s', 'wonderland-blocks' ), $at ) : '',
		);
	}

	// Nothing recorded at all: stored before this feature existed, or storage
	// was switched on after the fact.
	if ( ! $attempts ) {
		return array(
			'status' => 'unknown',
			'label'  => '—',
			'colour' => '#8c8f94',
			'detail' => '',
		);
	}

	if ( $attempts >= WONDERLAND_MAIL_MAX_ATTEMPTS ) {
		return array(
			'status' => 'failed',
			'label'  => __( 'Not sent', 'wonderland-blocks' ),
			'colour' => '#b32d2e',
			'detail' => $error,
		);
	}

	return array(
		'status' => 'retrying',
		'label'  => sprintf( /* translators: 1: attempts made, 2: attempts allowed. */ __( 'Retrying %1$d/%2$d', 'wonderland-blocks' ), $attempts, WONDERLAND_MAIL_MAX_ATTEMPTS ),
		'colour' => '#996800',
		'detail' => $error,
	);
}

/* -------------------------------------------------------------------------
 * Manual resend — for when all three attempts are spent.
 * ---------------------------------------------------------------------- */

add_action(
	'admin_post_wonderland_resend_submission',
	function () {
		$id = isset( $_GET['submission'] ) ? absint( $_GET['submission'] ) : 0;

		if ( ! $id || ! current_user_can( 'edit_post', $id ) ) {
			wp_die( esc_html__( 'You cannot resend that submission.', 'wonderland-blocks' ) );
		}
		check_admin_referer( 'wonderland_resend_' . $id );

		$payload = get_post_meta( $id, '_wl_mail_payload', true );
		$back    = wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=' . WONDERLAND_SUBMISSION_CPT );

		if ( ! is_array( $payload ) || empty( $payload['to'] ) ) {
			wp_safe_redirect( add_query_arg( 'wl_resent', 'missing', $back ) );
			exit;
		}

		// A manual resend is a deliberate act: it clears the attempt count so the
		// automatic retries start over, but it still respects `_wl_mail_sent`, so
		// pressing it on a delivered enquiry cannot produce a duplicate.
		delete_post_meta( $id, '_wl_mail_attempts' );
		$ok = wonderland_send_submission_mail( $id, $payload );

		wp_safe_redirect( add_query_arg( 'wl_resent', $ok ? '1' : '0', $back ) );
		exit;
	}
);

/**
 * Link that triggers the resend for a submission.
 *
 * @param int $submission_id Submission post ID.
 * @return string
 */
function wonderland_resend_url( $submission_id ) {
	return wp_nonce_url(
		admin_url( 'admin-post.php?action=wonderland_resend_submission&submission=' . (int) $submission_id ),
		'wonderland_resend_' . (int) $submission_id
	);
}

add_action(
	'admin_notices',
	function () {
		if ( ! isset( $_GET['wl_resent'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		$state = sanitize_key( wp_unslash( $_GET['wl_resent'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$messages = array(
			'1'       => array( 'success', __( 'The enquiry was emailed.', 'wonderland-blocks' ) ),
			'0'       => array( 'error', __( 'That still did not send — check the mail settings (WP Mail SMTP).', 'wonderland-blocks' ) ),
			'missing' => array( 'warning', __( 'There is no saved email for that enquiry to resend.', 'wonderland-blocks' ) ),
		);

		if ( ! isset( $messages[ $state ] ) ) {
			return;
		}
		printf(
			'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
			esc_attr( $messages[ $state ][0] ),
			esc_html( $messages[ $state ][1] )
		);
	}
);

/* -------------------------------------------------------------------------
 * Test send — proves the whole path, without a real enquiry.
 * ---------------------------------------------------------------------- */

/**
 * Plausible values for a preset's fields, so a test email reads like the real
 * thing rather than a row of "test test test".
 *
 * Built from the field definitions, so a field added to the form appears here
 * with no second list to update: a select takes one of its own options, and
 * anything unrecognised falls back to a generic string.
 *
 * @param string $preset Form preset.
 * @return array<string,string>
 */
function wonderland_sample_submission( $preset = 'contact' ) {
	$people  = array(
		array( 'Amara', 'Sethi', 'amara.sethi@example.com' ),
		array( 'Priya', 'Raman', 'priya.raman@example.com' ),
		array( 'Nadia', 'Kusuma', 'nadia.kusuma@example.com' ),
		array( 'Elena', 'Marchetti', 'elena.marchetti@example.com' ),
	);
	$partners = array( 'Rohan', 'Arjun', 'Wayan', 'Luca' );
	$notes    = array(
		'We are thinking of a three-day celebration next year and would love to hear what you would suggest.',
		'A friend was married in Bali last season and recommended you — could we start with a call?',
		'Still deciding between two venues. Any advice on which suits a larger guest list?',
	);

	$person = $people[ wp_rand( 0, count( $people ) - 1 ) ];
	$fields = wonderland_form_fields( $preset );
	$values = array();

	foreach ( $fields as $key => $field ) {
		if ( 'group' === $field['type'] ) {
			continue;
		}

		switch ( true ) {
			case 'select' === $field['type']:
				$options        = array_values( (array) $field['options'] );
				$values[ $key ] = $options ? $options[ wp_rand( 0, count( $options ) - 1 ) ] : '';
				break;
			case 'email' === $field['type']:
				$values[ $key ] = $person[2];
				break;
			case 'tel' === $field['type']:
				$values[ $key ] = '+62 812 ' . wp_rand( 1000, 9999 ) . ' ' . wp_rand( 1000, 9999 );
				break;
			case 'date' === $field['type']:
				$values[ $key ] = gmdate( 'Y-m-d', time() + wp_rand( 90, 400 ) * DAY_IN_SECONDS );
				break;
			case 'number' === $field['type']:
				$values[ $key ] = (string) ( wp_rand( 4, 30 ) * 10 );
				break;
			case 'textarea' === $field['type']:
				$values[ $key ] = $notes[ wp_rand( 0, count( $notes ) - 1 ) ];
				break;
			case in_array( $key, array( 'first_name', 'bride_name' ), true ):
				$values[ $key ] = $person[0];
				break;
			case 'last_name' === $key:
				$values[ $key ] = $person[1];
				break;
			case 'groom_name' === $key || false !== strpos( $key, 'partner' ):
				$values[ $key ] = $partners[ wp_rand( 0, count( $partners ) - 1 ) ];
				break;
			default:
				$values[ $key ] = 'Sample ' . strtolower( $field['label'] );
		}
	}

	return $values;
}

add_action(
	'admin_post_wonderland_test_email',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You cannot send a test email.', 'wonderland-blocks' ) );
		}
		check_admin_referer( 'wonderland_test_email' );

		$preset = isset( $_POST['preset'] ) ? sanitize_key( wp_unslash( $_POST['preset'] ) ) : 'contact';
		$preset = in_array( $preset, array( 'contact', 'request', 'brochure' ), true ) ? $preset : 'contact';

		$fields = wonderland_form_fields( $preset );
		$values = wonderland_sample_submission( $preset );
		$lines  = array( __( 'This is a test. Nobody sent this enquiry.', 'wonderland-blocks' ), '' );

		foreach ( $fields as $key => $field ) {
			if ( 'group' === $field['type'] ) {
				$lines[] = '';
				$lines[] = strtoupper( $field['label'] );
				continue;
			}
			if ( ! empty( $values[ $key ] ) ) {
				$lines[] = $field['label'] . ': ' . $values[ $key ];
			}
		}

		$lines[] = '';
		$lines[] = '—';
		$lines[] = __( 'Sent from', 'wonderland-blocks' ) . ' ' . home_url( '/' );

		// An address typed into the box is a spot check — it goes there and only
		// there. Left empty, the test is a dress rehearsal: the real recipient
		// with the real Cc and Bcc.
		$to        = isset( $_POST['to'] ) ? sanitize_email( wp_unslash( $_POST['to'] ) ) : '';
		$custom    = is_email( $to );
		$recipient = $custom ? $to : wonderland_forms_recipient_for( $preset );
		$headers   = $custom
			? array( 'Content-Type: text/plain; charset=UTF-8' )
			: wonderland_form_mail_headers( $preset );

		// Straight to wp_mail(): a test is not a submission, so it is not stored
		// and never enters the retry queue.
		$error = '';
		$catch = function ( $wp_error ) use ( &$error ) {
			$error = $wp_error->get_error_message();
		};
		add_action( 'wp_mail_failed', $catch );

		$ok = (bool) wp_mail(
			$recipient,
			sprintf( '[Wonderland] %s — %s', wonderland_form_label( $preset ), __( 'Test email', 'wonderland-blocks' ) ),
			implode( "\n", $lines ),
			$headers
		);

		remove_action( 'wp_mail_failed', $catch );

		$copies = array();
		foreach ( $headers as $header ) {
			if ( 0 === stripos( $header, 'Cc:' ) || 0 === stripos( $header, 'Bcc:' ) ) {
				$copies[] = $header;
			}
		}

		// A transient rather than the URL: the mailer's error can be long, and it
		// has no business being in the address bar.
		set_transient(
			'wonderland_test_email_result',
			array(
				'ok'     => $ok,
				'to'     => $recipient,
				'custom' => $custom,
				'copies' => implode( ' · ', $copies ),
				'error'  => $error,
			),
			MINUTE_IN_SECONDS
		);

		// The settings screen is the menu page itself — `wonderland-forms`, not a
		// separate slug. Anything else lands on "you are not allowed to access
		// this page", which reads like the test was refused when it was not.
		wp_safe_redirect( admin_url( 'admin.php?page=wonderland-forms&wl_test=1' ) );
		exit;
	}
);

add_action(
	'admin_notices',
	function () {
		if ( ! isset( $_GET['wl_test'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		$result = get_transient( 'wonderland_test_email_result' );
		if ( ! is_array( $result ) ) {
			return;
		}
		delete_transient( 'wonderland_test_email_result' );

		if ( $result['ok'] ) {
			$message = sprintf(
				/* translators: %s: recipient address. */
				__( 'Test email handed to the mailer for %s. If it never arrives, the address is right but delivery is not — check WP Mail SMTP and the spam folder.', 'wonderland-blocks' ),
				$result['to']
			);
			if ( ! empty( $result['copies'] ) ) {
				$message .= ' — ' . $result['copies'];
			}
			if ( ! empty( $result['custom'] ) ) {
				$message .= ' ' . __( 'Copies were skipped: a one-off test address is sent on its own.', 'wonderland-blocks' );
			}
		} else {
			$message = sprintf(
				/* translators: %s: error reported by the mailer. */
				__( 'The test email could not be sent: %s', 'wonderland-blocks' ),
				$result['error'] ? $result['error'] : __( 'the mailer refused the message.', 'wonderland-blocks' )
			);
		}

		printf(
			'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
			$result['ok'] ? 'success' : 'error',
			esc_html( $message )
		);
	}
);
