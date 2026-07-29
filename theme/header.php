<?php
/**
 * Site header — edge-to-edge transparent overlay on the front page (over the
 * hero, with an oversized logo that shrinks on scroll), solid elsewhere.
 * The "Menu" button opens a right-side off-canvas panel.
 *
 * @package Wonderland
 */

$inline_nav   = function_exists( 'wonderland_uses_inline_nav' ) && wonderland_uses_inline_nav();
$is_overlay   = ! $inline_nav && ( function_exists( 'wonderland_page_has_hero' ) ? wonderland_page_has_hero() : is_front_page() );
$header_class = 'site-header ' . ( $is_overlay ? 'site-header--transparent' : 'site-header--solid' )
	. ( $inline_nav ? ' site-header--inline' : '' );
$logo_uri     = WONDERLAND_URI . '/assets/img/logo.svg';
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'wonderland' ); ?></a>

<header class="<?php echo esc_attr( $header_class ); ?>" id="site-header">
	<div class="site-header__inner">
		<a class="site-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<img class="site-header__logo" src="<?php echo esc_url( $logo_uri ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="120" height="107" />
			<?php endif; ?>
		</a>

		<?php if ( $inline_nav ) : ?>
			<nav class="site-nav" aria-label="<?php esc_attr_e( 'Primary', 'wonderland' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'site-nav__menu',
						'fallback_cb'    => false,
						'depth'          => 2,
					)
				);
				?>
			</nav>
		<?php endif; ?>

		<?php
		// The primary action stays in reach on every page and at every scroll
		// position: after the last nav item on the service pages, and beside the
		// Menu button everywhere else. Hidden on the request page itself, where
		// the form already is the action.
		if ( ! is_page( 'request' ) ) :
			?>
			<a class="site-header__cta" href="<?php echo esc_url( home_url( '/request/' ) ); ?>">
				<?php esc_html_e( 'Enquire', 'wonderland' ); ?>
			</a>
		<?php endif; ?>

		<button class="site-header__toggle<?php echo $inline_nav ? ' is-compact' : ''; ?>" id="menu-toggle" aria-expanded="false" aria-controls="menu-overlay">
			<span class="site-header__toggle-label"><?php esc_html_e( 'Menu', 'wonderland' ); ?></span>
			<span class="site-header__burger" aria-hidden="true"><span></span><span></span><span></span></span>
		</button>
	</div>
</header>

<div class="menu-overlay" id="menu-overlay" hidden>
	<div class="menu-overlay__backdrop" data-menu-close></div>
	<aside class="menu-overlay__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Menu', 'wonderland' ); ?>">
		<button class="menu-overlay__close" id="menu-close" aria-label="<?php esc_attr_e( 'Close menu', 'wonderland' ); ?>">&times;</button>

		<a class="menu-overlay__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img src="<?php echo esc_url( $logo_uri ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="120" height="107" />
		</a>

		<nav class="menu-overlay__nav" aria-label="<?php esc_attr_e( 'Primary', 'wonderland' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'overlay-nav',
					'fallback_cb'    => false,
					'depth'          => 2,
				)
			);
			?>
		</nav>

		<div class="menu-overlay__social">
			<a href="https://www.facebook.com/wonderlandbali/" target="_blank" rel="noopener" aria-label="Facebook">Facebook</a>
			<a href="https://www.instagram.com/wonderland_events_worldwide/" target="_blank" rel="noopener" aria-label="Instagram">Instagram</a>
			<a href="https://wa.me/6287861138090" target="_blank" rel="noopener" aria-label="WhatsApp">WhatsApp</a>
		</div>

		<ul class="menu-overlay__contact">
			<li><a href="mailto:info@wonderlandbali.com">info@wonderlandbali.com</a></li>
			<li><a href="mailto:anastasia@wonderlandbali.com">anastasia@wonderlandbali.com</a></li>
			<li><a href="tel:+6287861138090">+62 878 6113 8090</a> &nbsp;|&nbsp; English</li>
		</ul>
	</aside>
</div>

<main id="content" class="site-main">
