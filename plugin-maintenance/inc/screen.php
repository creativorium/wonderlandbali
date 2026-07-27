<?php
/**
 * The branded holding page.
 *
 * Rendered standalone (no theme templates) so it still works if the theme is
 * mid-update, and with inlined CSS + the brand fonts as the only extra request.
 *
 * @package WonderlandMaintenance
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output the maintenance screen with a 503 so search engines come back later.
 */
function wlm_render_screen() {
	$s = wlm_settings();

	// 503 + Retry-After tells crawlers this is temporary; noindex keeps it out of results.
	if ( ! headers_sent() ) {
		status_header( 503 );
		header( 'Retry-After: 3600' );
		header( 'X-Robots-Tag: noindex, nofollow', true );
		nocache_headers();
	}

	$fonts   = get_theme_file_uri( 'assets/fonts/fonts.css' );
	$logo    = get_theme_file_uri( 'assets/img/logo.svg' );
	$has_bg  = ! empty( $s['background'] );
	$mark    = function_exists( 'wonderland_mark_svg' ) ? wonderland_mark_svg( 'wlm__mark' ) : '';
	$classes = 'wlm' . ( $has_bg ? ' wlm--photo' : '' );
	?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title><?php echo esc_html( wp_strip_all_tags( $s['heading'] ) . ' — ' . get_bloginfo( 'name' ) ); ?></title>
	<link rel="stylesheet" href="<?php echo esc_url( $fonts ); ?>">
	<style>
		*,*::before,*::after{box-sizing:border-box}
		:root{
			--wl-greige:#e3deda;--wl-taupe:#c5b7b3;--wl-coral:#efa58f;--wl-ink:#000;
			--wl-font-head:'Analogue Modern','Times New Roman',serif;
			--wl-font-label:'Gill','Gill Sans','Gill Sans MT',system-ui,sans-serif;
			--wl-font-body:'Gill','Gill Sans',system-ui,sans-serif;
		}
		html,body{margin:0;padding:0}
		img{max-width:100%;height:auto;display:block}
		.wlm{
			position:relative;min-height:100svh;display:flex;align-items:center;justify-content:center;
			padding:clamp(3rem,8vh,6rem) clamp(1.25rem,5vw,6rem);
			background:var(--wl-greige);color:var(--wl-ink);text-align:center;
			font-family:var(--wl-font-body);line-height:1.6;overflow:hidden;
		}
		.wlm__bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
		.wlm__bg::after{content:""}
		.wlm--photo::after{content:"";position:absolute;inset:0;background:rgba(0,0,0,.5)}
		.wlm--photo{color:#fff}
		.wlm__inner{position:relative;z-index:1;width:100%;max-width:40rem;
			display:flex;flex-direction:column;align-items:center}
		.wlm__logo{width:clamp(5rem,9vw,7rem);height:auto;margin-bottom:2rem}
		.wlm--photo .wlm__logo{filter:brightness(0) invert(1)}
		.wlm__mark{width:clamp(2.5rem,5vw,3.5rem);height:auto;color:var(--wl-coral);margin-bottom:1.5rem}
		.wlm__eyebrow{margin:0;font-family:var(--wl-font-label);font-size:.75rem;
			letter-spacing:.32em;text-transform:uppercase;opacity:.65}
		.wlm__heading{margin:1rem 0 0;font-family:var(--wl-font-head);font-weight:400;
			font-size:clamp(2rem,6vw,3.75rem);line-height:1.1}
		.wlm__message{margin:1.5rem 0 0;max-width:32rem;font-size:clamp(.95rem,1.2vw,1.05rem);
			line-height:1.75;opacity:.8}
		.wlm__contact{margin:2.5rem 0 0;padding-top:2rem;width:100%;max-width:32rem;
			border-top:1px solid currentColor;display:flex;flex-wrap:wrap;justify-content:center;
			gap:.75rem 2rem;font-family:var(--wl-font-label);font-size:.8rem;letter-spacing:.12em}
		.wlm__contact a{color:inherit;text-decoration:none;border-bottom:1px solid transparent}
		.wlm__contact a:hover{border-bottom-color:var(--wl-coral)}
		.wlm__contact-divider{opacity:.35}
		@media (max-width:520px){.wlm__contact{flex-direction:column;gap:.6rem}
			.wlm__contact-divider{display:none}}
	</style>
</head>
<body class="<?php echo esc_attr( $classes ); ?>">
	<?php if ( $has_bg ) : ?>
		<img class="wlm__bg" src="<?php echo esc_url( $s['background'] ); ?>" alt="" />
	<?php endif; ?>

	<div class="wlm__inner">
		<?php if ( $logo ) : ?>
			<img class="wlm__logo" src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
		<?php elseif ( $mark ) : ?>
			<?php echo $mark; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endif; ?>

		<?php if ( $s['eyebrow'] ) : ?>
			<p class="wlm__eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p>
		<?php endif; ?>

		<h1 class="wlm__heading"><?php echo esc_html( $s['heading'] ); ?></h1>

		<?php if ( $s['message'] ) : ?>
			<p class="wlm__message"><?php echo esc_html( $s['message'] ); ?></p>
		<?php endif; ?>

		<?php if ( $s['email'] || $s['phone'] ) : ?>
			<div class="wlm__contact">
				<?php if ( $s['email'] ) : ?>
					<a href="mailto:<?php echo esc_attr( $s['email'] ); ?>"><?php echo esc_html( $s['email'] ); ?></a>
				<?php endif; ?>
				<?php if ( $s['email'] && $s['phone'] ) : ?>
					<span class="wlm__contact-divider" aria-hidden="true">|</span>
				<?php endif; ?>
				<?php if ( $s['phone'] ) : ?>
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $s['phone'] ) ); ?>"><?php echo esc_html( $s['phone'] ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</body>
</html>
	<?php
}
