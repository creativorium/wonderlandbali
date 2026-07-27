<?php
/**
 * Stored form submissions.
 *
 * Every submission is saved as a private `wl_submission` post so it can be read
 * in wp-admin even if the notification email bounces. The list is grouped by
 * form (Contact vs Make a Request) so it is obvious which is which.
 *
 * @package WonderlandBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WONDERLAND_SUBMISSION_CPT', 'wl_submission' );

/**
 * Human label for a form preset.
 *
 * @param string $preset Preset key.
 * @return string
 */
function wonderland_form_label( $preset ) {
	$labels = array(
		'contact' => __( 'Contact', 'wonderland-blocks' ),
		'request' => __( 'Make a Request', 'wonderland-blocks' ),
	);
	return $labels[ $preset ] ?? ucfirst( $preset );
}

/* -------------------------------------------------------------------------
 * Post type + admin menu
 * ---------------------------------------------------------------------- */

add_action(
	'init',
	function () {
		register_post_type(
			WONDERLAND_SUBMISSION_CPT,
			array(
				'labels'          => array(
					'name'          => __( 'Submissions', 'wonderland-blocks' ),
					'singular_name' => __( 'Submission', 'wonderland-blocks' ),
					'menu_name'     => __( 'Submissions', 'wonderland-blocks' ),
					'search_items'  => __( 'Search submissions', 'wonderland-blocks' ),
					'not_found'     => __( 'No submissions yet.', 'wonderland-blocks' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => 'wonderland-forms',
				'show_in_rest'    => false,
				'has_archive'     => false,
				'rewrite'         => false,
				'query_var'       => false,
				'supports'        => array( 'title' ),
				'capability_type' => 'post',
				'map_meta_cap'    => true,
				'capabilities'    => array(
					// Submissions arrive from the front end — nobody creates them by hand.
					'create_posts' => 'do_not_allow',
				),
			)
		);
	}
);

add_action(
	'admin_menu',
	function () {
		add_menu_page(
			__( 'Wonderland Forms', 'wonderland-blocks' ),
			__( 'Wonderland Forms', 'wonderland-blocks' ),
			'edit_pages',
			'wonderland-forms',
			'wonderland_forms_settings_page',
			'dashicons-feedback',
			26
		);

		// The CPT attaches itself as the first submenu item; add Settings after it.
		add_submenu_page(
			'wonderland-forms',
			__( 'Form Settings', 'wonderland-blocks' ),
			__( 'Settings', 'wonderland-blocks' ),
			'manage_options',
			'wonderland-forms',
			'wonderland_forms_settings_page'
		);
	}
);

/* -------------------------------------------------------------------------
 * Saving
 * ---------------------------------------------------------------------- */

/**
 * Store one submission.
 *
 * @param string $preset Form preset.
 * @param array  $values Sanitized key => value pairs (only non-empty fields).
 * @param array  $meta   Extra context: score, ip, page.
 * @return int Post ID, or 0 when storage is switched off.
 */
function wonderland_store_submission( $preset, $values, $meta = array() ) {
	$settings = wonderland_forms_settings();
	if ( empty( $settings['store_submissions'] ) ) {
		return 0;
	}

	$fields = wonderland_form_fields( $preset );

	// Title should identify the enquiry at a glance in the list table.
	$who = '';
	foreach ( array( 'bride_name', 'first_name', 'email' ) as $key ) {
		if ( ! empty( $values[ $key ] ) ) {
			$who = $values[ $key ];
			break;
		}
	}
	if ( ! empty( $values['last_name'] ) && ! empty( $values['first_name'] ) ) {
		$who = $values['first_name'] . ' ' . $values['last_name'];
	}

	$title = sprintf(
		'%s — %s',
		wonderland_form_label( $preset ),
		$who ? $who : __( 'Unknown sender', 'wonderland-blocks' )
	);

	$post_id = wp_insert_post(
		array(
			'post_type'   => WONDERLAND_SUBMISSION_CPT,
			'post_status' => 'private',
			'post_title'  => $title,
		),
		true
	);

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return 0;
	}

	update_post_meta( $post_id, '_wl_form', $preset );
	update_post_meta( $post_id, '_wl_field_order', array_keys( $fields ) );

	foreach ( $values as $key => $value ) {
		update_post_meta( $post_id, '_wl_' . $key, $value );
	}

	foreach ( array( 'score', 'ip', 'page' ) as $key ) {
		if ( isset( $meta[ $key ] ) && '' !== $meta[ $key ] ) {
			update_post_meta( $post_id, '_wl_meta_' . $key, $meta[ $key ] );
		}
	}

	return $post_id;
}

/* -------------------------------------------------------------------------
 * List table
 * ---------------------------------------------------------------------- */

add_filter(
	'manage_' . WONDERLAND_SUBMISSION_CPT . '_posts_columns',
	function () {
		return array(
			'cb'       => '<input type="checkbox" />',
			'wl_form'  => __( 'Form', 'wonderland-blocks' ),
			'title'    => __( 'From', 'wonderland-blocks' ),
			'wl_email' => __( 'Email', 'wonderland-blocks' ),
			'wl_phone' => __( 'Phone', 'wonderland-blocks' ),
			'wl_extra' => __( 'Summary', 'wonderland-blocks' ),
			'date'     => __( 'Received', 'wonderland-blocks' ),
		);
	}
);

add_action(
	'manage_' . WONDERLAND_SUBMISSION_CPT . '_posts_custom_column',
	function ( $column, $post_id ) {
		switch ( $column ) {
			case 'wl_form':
				$preset = get_post_meta( $post_id, '_wl_form', true );
				$colour = 'request' === $preset ? '#8a4b7d' : '#3d6b8a';
				printf(
					'<span style="display:inline-block;padding:2px 10px;border-radius:10px;background:%s;color:#fff;font-size:11px;letter-spacing:.04em">%s</span>',
					esc_attr( $colour ),
					esc_html( wonderland_form_label( $preset ) )
				);
				break;

			case 'wl_email':
				$email = get_post_meta( $post_id, '_wl_email', true );
				if ( $email ) {
					printf( '<a href="mailto:%1$s">%1$s</a>', esc_attr( $email ) );
				} else {
					echo '—';
				}
				break;

			case 'wl_phone':
				echo esc_html( get_post_meta( $post_id, '_wl_phone', true ) ?: '—' );
				break;

			case 'wl_extra':
				$bits = array();
				foreach ( array( 'service', 'event_date', 'guest_count' ) as $key ) {
					$v = get_post_meta( $post_id, '_wl_' . $key, true );
					if ( $v ) {
						$bits[] = esc_html( $v );
					}
				}
				echo $bits ? esc_html( implode( ' · ', array_map( 'wp_strip_all_tags', $bits ) ) ) : '—';
				break;
		}
	},
	10,
	2
);

/**
 * Filter the list by form type.
 */
add_action(
	'restrict_manage_posts',
	function ( $post_type ) {
		if ( WONDERLAND_SUBMISSION_CPT !== $post_type ) {
			return;
		}
		$current = isset( $_GET['wl_form'] ) ? sanitize_key( wp_unslash( $_GET['wl_form'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		?>
		<select name="wl_form">
			<option value=""><?php esc_html_e( 'All forms', 'wonderland-blocks' ); ?></option>
			<?php foreach ( array( 'contact', 'request' ) as $preset ) : ?>
				<option value="<?php echo esc_attr( $preset ); ?>" <?php selected( $current, $preset ); ?>>
					<?php echo esc_html( wonderland_form_label( $preset ) ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}
);

add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( WONDERLAND_SUBMISSION_CPT !== $query->get( 'post_type' ) ) {
			return;
		}
		$form = isset( $_GET['wl_form'] ) ? sanitize_key( wp_unslash( $_GET['wl_form'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $form ) {
			$query->set(
				'meta_query',
				array(
					array(
						'key'   => '_wl_form',
						'value' => $form,
					),
				)
			);
		}
	}
);

/* -------------------------------------------------------------------------
 * Detail view
 * ---------------------------------------------------------------------- */

add_action(
	'add_meta_boxes',
	function () {
		add_meta_box(
			'wl-submission',
			__( 'Submission', 'wonderland-blocks' ),
			'wonderland_submission_meta_box',
			WONDERLAND_SUBMISSION_CPT,
			'normal',
			'high'
		);
	}
);

/**
 * Render the read-only submission detail.
 *
 * @param WP_Post $post Submission post.
 */
function wonderland_submission_meta_box( $post ) {
	$preset = get_post_meta( $post->ID, '_wl_form', true );
	$fields = wonderland_form_fields( $preset ? $preset : 'contact' );
	$score  = get_post_meta( $post->ID, '_wl_meta_score', true );
	$ip     = get_post_meta( $post->ID, '_wl_meta_ip', true );
	$page   = get_post_meta( $post->ID, '_wl_meta_page', true );
	?>
	<table class="widefat striped">
		<tbody>
			<tr>
				<th style="width:220px"><?php esc_html_e( 'Form', 'wonderland-blocks' ); ?></th>
				<td><strong><?php echo esc_html( wonderland_form_label( $preset ) ); ?></strong></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Received', 'wonderland-blocks' ); ?></th>
				<td><?php echo esc_html( get_the_date( 'Y-m-d H:i', $post ) ); ?></td>
			</tr>
			<?php
			foreach ( $fields as $key => $field ) :
				if ( 'group' === $field['type'] ) {
					continue;
				}
				$value = get_post_meta( $post->ID, '_wl_' . $key, true );
				if ( '' === $value ) {
					continue;
				}
				?>
				<tr>
					<th><?php echo esc_html( $field['label'] ); ?></th>
					<td>
						<?php if ( 'email' === $field['type'] ) : ?>
							<a href="mailto:<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $value ); ?></a>
						<?php elseif ( 'textarea' === $field['type'] ) : ?>
							<?php echo nl2br( esc_html( $value ) ); ?>
						<?php else : ?>
							<?php echo esc_html( $value ); ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if ( '' !== $score ) : ?>
				<tr>
					<th><?php esc_html_e( 'reCAPTCHA score', 'wonderland-blocks' ); ?></th>
					<td><?php echo esc_html( $score ); ?> <span class="description"><?php esc_html_e( '(1.0 = very likely human)', 'wonderland-blocks' ); ?></span></td>
				</tr>
			<?php endif; ?>
			<?php if ( $page ) : ?>
				<tr>
					<th><?php esc_html_e( 'Sent from', 'wonderland-blocks' ); ?></th>
					<td><a href="<?php echo esc_url( $page ); ?>"><?php echo esc_html( $page ); ?></a></td>
				</tr>
			<?php endif; ?>
			<?php if ( $ip ) : ?>
				<tr>
					<th><?php esc_html_e( 'IP address', 'wonderland-blocks' ); ?></th>
					<td><?php echo esc_html( $ip ); ?></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
	<?php
}

/**
 * Submissions are records, not drafts — hide the editor chrome that implies editing.
 */
add_action(
	'admin_head',
	function () {
		$screen = get_current_screen();
		if ( ! $screen || WONDERLAND_SUBMISSION_CPT !== $screen->post_type ) {
			return;
		}
		echo '<style>#minor-publishing,#misc-publishing-actions,#titlediv{display:none}</style>';
	}
);
