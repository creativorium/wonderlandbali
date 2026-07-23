<?php
/**
 * Site header.
 *
 * @package Wonderland
 */

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

<header class="site-header" id="site-header">
	<div class="site-header__inner">
		<div class="site-header__brand">
			<?php
			if ( has_custom_logo() ) {
				the_custom_logo();
			} else {
				printf(
					'<a class="site-header__title" href="%1$s">%2$s</a>',
					esc_url( home_url( '/' ) ),
					esc_html( get_bloginfo( 'name' ) )
				);
			}
			?>
		</div>

		<nav class="site-header__nav" aria-label="<?php esc_attr_e( 'Primary', 'wonderland' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'site-nav',
					'fallback_cb'    => false,
					'depth'          => 2,
				)
			);
			?>
		</nav>
	</div>
</header>

<main id="content" class="site-main">
