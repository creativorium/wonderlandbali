<?php
/**
 * Site header — transparent overlay on the front page (over the hero), solid
 * elsewhere. The "Menu" button opens a full-screen overlay navigation.
 *
 * @package Wonderland
 */

$is_overlay = is_front_page();
$header_class = 'site-header ' . ( $is_overlay ? 'site-header--transparent' : 'site-header--solid' );
$logo_uri = WONDERLAND_URI . '/assets/img/logo.svg';
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

		<button class="site-header__toggle" id="menu-toggle" aria-expanded="false" aria-controls="menu-overlay">
			<span class="site-header__toggle-label"><?php esc_html_e( 'Menu', 'wonderland' ); ?></span>
			<span class="site-header__burger" aria-hidden="true"><span></span><span></span><span></span></span>
		</button>
	</div>
</header>

<div class="menu-overlay" id="menu-overlay" hidden>
	<div class="menu-overlay__inner">
		<button class="menu-overlay__close" id="menu-close" aria-label="<?php esc_attr_e( 'Close menu', 'wonderland' ); ?>">&times;</button>
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
	</div>
</div>

<main id="content" class="site-main">
