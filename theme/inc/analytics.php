<?php
/**
 * Google Tag Manager, GA4 and the Meta Pixel.
 *
 * Two guards matter more than the tags themselves:
 *
 *  - Only fires on the production host. Without that, every local build, staging
 *    deploy and headless screenshot lands in the client's real analytics.
 *  - Never fires for logged-in editors, whose own visits would otherwise inflate
 *    the numbers they are trying to read.
 *
 * Conversion events are pushed to the dataLayer from theme/src/main.js — form
 * successes (our forms redirect to ?wl_sent=1), WhatsApp taps, and phone/email
 * clicks. GTM triggers hang off those.
 *
 * @package Wonderland
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Container and pixel IDs. Empty disables that tag.
 *
 * @return array{gtm:string,ga4:string,pixel:string}
 */
function wonderland_analytics_ids() {
	return apply_filters(
		'wonderland_analytics_ids',
		array(
			'gtm'   => 'GTM-KN4527FS',
			// Left empty on purpose: GA4 is configured inside the GTM container.
			// Loading gtag.js as well would count every page view twice. Only set
			// this if GA4 is NOT a tag in the container.
			'ga4'   => '',
			'pixel' => '1429432984502561',
		)
	);
}

/**
 * Hosts that are allowed to send real analytics data.
 *
 * @return string[]
 */
function wonderland_analytics_hosts() {
	return apply_filters(
		'wonderland_analytics_hosts',
		array( 'wonderlandbali.com', 'www.wonderlandbali.com' )
	);
}

/**
 * Whether tracking should run for this request.
 *
 * @return bool
 */
function wonderland_analytics_enabled() {
	$host = wp_parse_url( home_url(), PHP_URL_HOST );

	$enabled = in_array( $host, wonderland_analytics_hosts(), true )
		&& ! is_user_logged_in()
		&& ! is_admin()
		&& ! wp_doing_ajax();

	/**
	 * Filters whether analytics tags render.
	 *
	 * Set true on a staging host to test the container end to end — just
	 * remember that data lands in the live property.
	 *
	 * @param bool $enabled Whether to render tags.
	 */
	return (bool) apply_filters( 'wonderland_analytics_enabled', $enabled );
}

/**
 * Head tags: GTM, optionally GA4, and the Meta Pixel.
 */
add_action(
	'wp_head',
	function () {
		if ( ! wonderland_analytics_enabled() ) {
			return;
		}

		$ids = wonderland_analytics_ids();
		?>
		<script>window.dataLayer = window.dataLayer || [];</script>
		<?php if ( $ids['gtm'] ) : ?>
		<!-- Google Tag Manager -->
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','<?php echo esc_js( $ids['gtm'] ); ?>');</script>
		<!-- End Google Tag Manager -->
		<?php endif; ?>

		<?php if ( $ids['ga4'] ) : ?>
		<!-- Google tag (gtag.js) -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ids['ga4'] ); ?>"></script>
		<script>
			function gtag(){dataLayer.push(arguments);}
			gtag('js', new Date());
			gtag('config', '<?php echo esc_js( $ids['ga4'] ); ?>');
		</script>
		<?php endif; ?>

		<?php if ( $ids['pixel'] ) : ?>
		<!-- Meta Pixel -->
		<script>
		!function(f,b,e,v,n,t,s)
		{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
		n.callMethod.apply(n,arguments):n.queue.push(arguments)};
		if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
		n.queue=[];t=b.createElement(e);t.async=!0;
		t.src=v;s=b.getElementsByTagName(e)[0];
		s.parentNode.insertBefore(t,s)}(window,document,'script',
		'https://connect.facebook.net/en_US/fbevents.js');
		fbq('init', '<?php echo esc_js( $ids['pixel'] ); ?>');
		fbq('track', 'PageView');
		</script>
		<noscript><img height="1" width="1" style="display:none" alt=""
			src="https://www.facebook.com/tr?id=<?php echo esc_attr( $ids['pixel'] ); ?>&ev=PageView&noscript=1" /></noscript>
		<!-- End Meta Pixel -->
		<?php endif; ?>
		<?php
	},
	1
);

/**
 * The GTM <noscript> frame, which belongs immediately after <body>.
 */
add_action(
	'wp_body_open',
	function () {
		if ( ! wonderland_analytics_enabled() ) {
			return;
		}

		$gtm = wonderland_analytics_ids()['gtm'];
		if ( ! $gtm ) {
			return;
		}
		?>
		<!-- Google Tag Manager (noscript) -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr( $gtm ); ?>"
			height="0" width="0" style="display:none;visibility:hidden" title="Google Tag Manager"></iframe></noscript>
		<!-- End Google Tag Manager (noscript) -->
		<?php
	}
);

/**
 * Tell the front-end script whether a form submission just succeeded.
 *
 * Our forms redirect back to the page with ?wl_sent=1 rather than posting over
 * AJAX, so the conversion is simply a page load carrying that flag — no need to
 * intercept fetch or watch the DOM the way the old Elementor snippet did.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! wonderland_analytics_enabled() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag.
		$sent = isset( $_GET['wl_sent'] ) ? sanitize_text_field( wp_unslash( $_GET['wl_sent'] ) ) : '';
		if ( '1' !== $sent ) {
			return;
		}

		$form = is_page( 'request' ) ? 'request' : 'contact';

		wp_add_inline_script(
			'wonderland-main',
			'window.wlFormSubmitted = ' . wp_json_encode( $form ) . ';',
			'before'
		);
	},
	20
);
