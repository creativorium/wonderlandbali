<?php
/**
 * One-off: move Elementor's stored form submissions into our own Submissions CPT.
 *
 * Elementor is gone but its tables were left behind, and they hold every enquiry
 * the site has taken since September 2023 — real customer names, emails, phone
 * numbers and messages. Those should live somewhere the site can still show
 * them, and the orphaned tables should not be handed to anyone.
 *
 * Elementor stored field *ids*, never labels, so `field_70c2fa7` is all that
 * survives for custom fields. Values are kept verbatim under `_wl_<id>`; the
 * ones we can identify by shape (email, phone) are additionally written to the
 * canonical keys the admin columns read, so the list screen is usable.
 *
 * Idempotent: each new post records the Elementor id it came from, and rows
 * already migrated are skipped. Safe to re-run.
 *
 * Run with:
 *   wp eval-file scripts/migrate-elementor-submissions.php          # dry run
 *   wp eval-file scripts/migrate-elementor-submissions.php commit
 *
 * The argument is positional, not a flag — `eval-file` rejects unknown flags.
 *
 * @package WonderlandBlocks
 */

global $wpdb;

$commit = in_array( 'commit', (array) ( $args ?? array() ), true );

if ( ! defined( 'WONDERLAND_SUBMISSION_CPT' ) ) {
	WP_CLI::error( 'The blocks plugin is not active — WONDERLAND_SUBMISSION_CPT is undefined.' );
}

$table  = $wpdb->prefix . 'e_submissions';
$values = $wpdb->prefix . 'e_submissions_values';

if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
	WP_CLI::success( 'No Elementor submission tables — nothing to migrate.' );
	return;
}

$rows = $wpdb->get_results( "SELECT id, form_name, created_at, user_ip FROM $table ORDER BY created_at ASC" ); // phpcs:ignore
WP_CLI::log( sprintf( 'Found %d Elementor submissions.', count( $rows ) ) );

// Elementor's form names, mapped onto our presets. Anything else is recorded
// under its own name so the admin filter still groups it sensibly.
$form_map = array(
	'Request'             => 'request',
	'Wedding Request Form' => 'request',
	'New Form'            => 'contact',
);

$created = 0;
$skipped = 0;

foreach ( $rows as $row ) {
	$existing = get_posts(
		array(
			'post_type'      => WONDERLAND_SUBMISSION_CPT,
			'post_status'    => 'any',
			'meta_key'       => '_wl_legacy_id',      // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => (string) $row->id,    // phpcs:ignore WordPress.DB.SlowDBQuery
			'fields'         => 'ids',
			'posts_per_page' => 1,
		)
	);
	if ( $existing ) {
		++$skipped;
		continue;
	}

	$fields = $wpdb->get_results( $wpdb->prepare( "SELECT `key`, `value` FROM $values WHERE submission_id = %d", $row->id ) ); // phpcs:ignore

	$data = array();
	foreach ( $fields as $f ) {
		$data[ $f->key ] = $f->value;
	}

	// Identify by shape what Elementor no longer names.
	foreach ( $data as $key => $value ) {
		$value = trim( (string) $value );
		if ( '' === $value ) {
			continue;
		}
		if ( ! isset( $data['email'] ) && is_email( $value ) ) {
			$data['email'] = $value;
		}
		// 8–15 digits once punctuation is stripped: a phone number, not a date.
		$digits = preg_replace( '/[^0-9]/', '', $value );
		if ( ! isset( $data['phone'] ) && strlen( $digits ) >= 8 && strlen( $digits ) <= 15
			&& preg_match( '/^[0-9+()\-.\s]+$/', $value ) ) {
			$data['phone'] = $value;
		}
	}

	$name  = $data['name'] ?? ( $data['email'] ?? __( 'Enquiry', 'wonderland-blocks' ) );
	$title = sprintf( '%s — %s', $name, mysql2date( 'j M Y', $row->created_at ) );

	if ( ! $commit ) {
		WP_CLI::log( sprintf( '  would create: %s  (%d fields)', $title, count( $data ) ) );
		++$created;
		continue;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'   => WONDERLAND_SUBMISSION_CPT,
			'post_status' => 'publish',
			'post_title'  => $title,
			'post_date'   => $row->created_at,
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		WP_CLI::warning( sprintf( 'submission %d: %s', $row->id, $post_id->get_error_message() ) );
		continue;
	}

	update_post_meta( $post_id, '_wl_form', $form_map[ $row->form_name ] ?? sanitize_key( $row->form_name ) );
	update_post_meta( $post_id, '_wl_field_order', array_keys( $data ) );
	update_post_meta( $post_id, '_wl_legacy_id', (string) $row->id );
	update_post_meta( $post_id, '_wl_meta_source', 'elementor' );

	foreach ( $data as $key => $value ) {
		update_post_meta( $post_id, '_wl_' . sanitize_key( $key ), $value );
	}

	++$created;
}

WP_CLI::success(
	$commit
		? sprintf( 'Migrated %d submissions (%d already present).', $created, $skipped )
		: sprintf( 'Dry run: %d would be migrated, %d already present. Re-run with --commit.', $created, $skipped )
);
