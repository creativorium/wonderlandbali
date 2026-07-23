<?php
/**
 * Site footer.
 *
 * @package Wonderland
 */

?>
</main><!-- #content -->

<footer class="site-footer" id="site-footer">
	<div class="site-footer__inner">
		<nav class="site-footer__nav" aria-label="<?php esc_attr_e( 'Footer', 'wonderland' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'footer-nav',
					'fallback_cb'    => false,
					'depth'          => 1,
				)
			);
			?>
		</nav>
		<p class="site-footer__copy">
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>
		</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
