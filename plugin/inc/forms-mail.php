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
