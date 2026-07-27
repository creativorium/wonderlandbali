<?php
/**
 * Admin UI: an admin-bar switch (the everyday control) and a settings page
 * for the wording, background and bypass key.
 *
 * @package WonderlandMaintenance
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Persist a new enabled state.
 *
 * @param bool $on Whether maintenance mode should be on.
 */
function wlm_set_enabled( $on ) {
	$settings            = wlm_settings();
	$settings['enabled'] = $on ? 1 : 0;
	update_option( WLM_OPTION, $settings );
}

/* -------------------------------------------------------------------------
 * Admin bar switch — the "on/off I can flip easily" control.
 * ---------------------------------------------------------------------- */

add_action(
	'admin_bar_menu',
	function ( $bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$on     = wlm_is_enabled();
		$toggle = wp_nonce_url(
			admin_url( 'admin-post.php?action=wlm_toggle' ),
			'wlm_toggle'
		);

		$bar->add_node(
			array(
				'id'    => 'wlm',
				'title' => sprintf(
					'<span class="wlm-bar-dot" style="display:inline-block;width:8px;height:8px;border-radius:50%%;background:%s;margin-right:7px;vertical-align:middle"></span>%s',
					$on ? '#ff6b6b' : '#5ac37d',
					$on ? esc_html__( 'Maintenance: ON', 'wonderland-maintenance' ) : esc_html__( 'Site: Live', 'wonderland-maintenance' )
				),
				'href'  => $toggle,
				'meta'  => array(
					'title' => $on
						? __( 'Click to take the site live', 'wonderland-maintenance' )
						: __( 'Click to put the site into maintenance mode', 'wonderland-maintenance' ),
				),
			)
		);

		$bar->add_node(
			array(
				'parent' => 'wlm',
				'id'     => 'wlm-toggle',
				'title'  => $on ? __( '→ Take site live', 'wonderland-maintenance' ) : __( '→ Enable maintenance mode', 'wonderland-maintenance' ),
				'href'   => $toggle,
			)
		);

		$bar->add_node(
			array(
				'parent' => 'wlm',
				'id'     => 'wlm-settings',
				'title'  => __( 'Maintenance settings', 'wonderland-maintenance' ),
				'href'   => admin_url( 'options-general.php?page=wonderland-maintenance' ),
			)
		);
	},
	100
);

/**
 * Handle the toggle click.
 */
add_action(
	'admin_post_wlm_toggle',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do that.', 'wonderland-maintenance' ) );
		}
		check_admin_referer( 'wlm_toggle' );

		wlm_set_enabled( ! wlm_is_enabled() );

		// The holding page is cacheable output — clear LiteSpeed so it flips at once.
		if ( function_exists( 'do_action' ) ) {
			do_action( 'litespeed_purge_all' );
		}

		$back = wp_get_referer();
		wp_safe_redirect( add_query_arg( 'wlm_toggled', wlm_is_enabled() ? '1' : '0', $back ? $back : admin_url() ) );
		exit;
	}
);

/**
 * A persistent warning while the site is closed, so it never stays on by accident.
 */
add_action(
	'admin_notices',
	function () {
		if ( ! current_user_can( 'manage_options' ) || ! wlm_is_enabled() ) {
			return;
		}
		$toggle = wp_nonce_url( admin_url( 'admin-post.php?action=wlm_toggle' ), 'wlm_toggle' );
		?>
		<div class="notice notice-warning">
			<p>
				<strong><?php esc_html_e( 'Maintenance mode is ON.', 'wonderland-maintenance' ); ?></strong>
				<?php esc_html_e( 'Visitors see the holding page; logged-in editors see the real site.', 'wonderland-maintenance' ); ?>
				<a class="button button-primary" style="margin-left:.5rem" href="<?php echo esc_url( $toggle ); ?>"><?php esc_html_e( 'Take site live', 'wonderland-maintenance' ); ?></a>
			</p>
		</div>
		<?php
	}
);

/* -------------------------------------------------------------------------
 * Settings page
 * ---------------------------------------------------------------------- */

add_action(
	'admin_menu',
	function () {
		add_options_page(
			__( 'Maintenance Mode', 'wonderland-maintenance' ),
			__( 'Maintenance Mode', 'wonderland-maintenance' ),
			'manage_options',
			'wonderland-maintenance',
			'wlm_render_settings_page'
		);
	}
);

add_action(
	'admin_init',
	function () {
		register_setting(
			'wlm_group',
			WLM_OPTION,
			array(
				'sanitize_callback' => 'wlm_sanitize_settings',
				'default'           => array(),
			)
		);
	}
);

/**
 * Sanitize the settings form.
 *
 * @param array $input Raw form values.
 * @return array
 */
function wlm_sanitize_settings( $input ) {
	$input = is_array( $input ) ? $input : array();

	return array(
		'enabled'    => empty( $input['enabled'] ) ? 0 : 1,
		'eyebrow'    => sanitize_text_field( $input['eyebrow'] ?? '' ),
		'heading'    => sanitize_text_field( $input['heading'] ?? '' ),
		'message'    => sanitize_textarea_field( $input['message'] ?? '' ),
		'email'      => sanitize_email( $input['email'] ?? '' ),
		'phone'      => sanitize_text_field( $input['phone'] ?? '' ),
		'background' => esc_url_raw( $input['background'] ?? '' ),
		'bypass_key' => sanitize_text_field( $input['bypass_key'] ?? '' ),
	);
}

/**
 * Render the settings screen.
 */
function wlm_render_settings_page() {
	$s      = wlm_settings();
	$on     = ! empty( $s['enabled'] );
	$name   = WLM_OPTION;
	$toggle = wp_nonce_url( admin_url( 'admin-post.php?action=wlm_toggle' ), 'wlm_toggle' );
	$preview_url = $s['bypass_key']
		? add_query_arg( 'wlm_key', rawurlencode( $s['bypass_key'] ), home_url( '/' ) )
		: '';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Maintenance Mode', 'wonderland-maintenance' ); ?></h1>

		<div style="margin:1.5rem 0;padding:1.25rem 1.5rem;background:#fff;border:1px solid #c3c4c7;border-left:4px solid <?php echo $on ? '#d63638' : '#00a32a'; ?>;">
			<h2 style="margin-top:0">
				<?php
				echo $on
					? esc_html__( 'The site is currently CLOSED to visitors.', 'wonderland-maintenance' )
					: esc_html__( 'The site is currently LIVE.', 'wonderland-maintenance' );
				?>
			</h2>
			<p><?php esc_html_e( 'You can flip this any time from the admin bar at the top of any page.', 'wonderland-maintenance' ); ?></p>
			<p>
				<a class="button button-primary button-hero" href="<?php echo esc_url( $toggle ); ?>">
					<?php
					echo $on
						? esc_html__( 'Take the site live', 'wonderland-maintenance' )
						: esc_html__( 'Enable maintenance mode', 'wonderland-maintenance' );
					?>
				</a>
			</p>
		</div>

		<form method="post" action="options.php">
			<?php settings_fields( 'wlm_group' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="wlm-eyebrow"><?php esc_html_e( 'Eyebrow', 'wonderland-maintenance' ); ?></label></th>
					<td><input id="wlm-eyebrow" class="regular-text" type="text" name="<?php echo esc_attr( $name ); ?>[eyebrow]" value="<?php echo esc_attr( $s['eyebrow'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="wlm-heading"><?php esc_html_e( 'Heading', 'wonderland-maintenance' ); ?></label></th>
					<td><input id="wlm-heading" class="regular-text" type="text" name="<?php echo esc_attr( $name ); ?>[heading]" value="<?php echo esc_attr( $s['heading'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="wlm-message"><?php esc_html_e( 'Message', 'wonderland-maintenance' ); ?></label></th>
					<td><textarea id="wlm-message" class="large-text" rows="4" name="<?php echo esc_attr( $name ); ?>[message]"><?php echo esc_textarea( $s['message'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="wlm-email"><?php esc_html_e( 'Contact email', 'wonderland-maintenance' ); ?></label></th>
					<td><input id="wlm-email" class="regular-text" type="email" name="<?php echo esc_attr( $name ); ?>[email]" value="<?php echo esc_attr( $s['email'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="wlm-phone"><?php esc_html_e( 'Contact phone', 'wonderland-maintenance' ); ?></label></th>
					<td><input id="wlm-phone" class="regular-text" type="text" name="<?php echo esc_attr( $name ); ?>[phone]" value="<?php echo esc_attr( $s['phone'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="wlm-background"><?php esc_html_e( 'Background image URL', 'wonderland-maintenance' ); ?></label></th>
					<td>
						<input id="wlm-background" class="large-text" type="url" name="<?php echo esc_attr( $name ); ?>[background]" value="<?php echo esc_attr( $s['background'] ); ?>" placeholder="/wp-content/uploads/…" />
						<p class="description"><?php esc_html_e( 'Optional. Leave empty for the plain greige brand screen; add a photo for a full-bleed image with a dark overlay.', 'wonderland-maintenance' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wlm-bypass"><?php esc_html_e( 'Preview key', 'wonderland-maintenance' ); ?></label></th>
					<td>
						<input id="wlm-bypass" class="regular-text" type="text" name="<?php echo esc_attr( $name ); ?>[bypass_key]" value="<?php echo esc_attr( $s['bypass_key'] ); ?>" />
						<p class="description">
							<?php esc_html_e( 'Optional. Lets you share the closed site with a client without an account.', 'wonderland-maintenance' ); ?>
							<?php if ( $preview_url ) : ?>
								<br><code><?php echo esc_html( $preview_url ); ?></code>
							<?php endif; ?>
						</p>
					</td>
				</tr>
			</table>

			<?php // Keep the current on/off state when saving wording, so Save never flips the switch. ?>
			<input type="hidden" name="<?php echo esc_attr( $name ); ?>[enabled]" value="<?php echo esc_attr( $on ? 1 : 0 ); ?>" />

			<?php submit_button( __( 'Save changes', 'wonderland-maintenance' ) ); ?>
		</form>
	</div>
	<?php
}
