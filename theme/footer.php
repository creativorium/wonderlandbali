<?php
/**
 * Site footer — native rebuild of the Elementor footer.
 *
 * @package Wonderland
 */

$logo_uri  = WONDERLAND_URI . '/assets/img/logo.svg';
$uploads   = content_url( '/uploads' );
$badge_1   = $uploads . '/2023/06/Gold-Winner-Balis-Best-Awards-2024.png';
$badge_2   = $uploads . '/2023/06/IGP-Gold-Winner2.png';
?>
</main><!-- #content -->

<footer class="site-footer" id="site-footer">
	<div class="site-footer__top">
		<div class="site-footer__cta">
			<p class="site-footer__tagline">Give us a call and let&rsquo;s create wonders together!</p>
			<a class="site-footer__request" href="<?php echo esc_url( home_url( '/request-2/' ) ); ?>">Make a Request</a>
		</div>
	</div>

	<div class="site-footer__main">
		<div class="site-footer__col site-footer__col--brand">
			<img class="site-footer__logo" src="<?php echo esc_url( $logo_uri ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="120" height="107" />
			<div class="site-footer__social">
				<a href="https://www.instagram.com/wonderland_events_worldwide/" target="_blank" rel="noopener" aria-label="Instagram">Instagram</a>
				<a href="https://www.facebook.com/wonderlandbali/" target="_blank" rel="noopener" aria-label="Facebook">Facebook</a>
				<a href="https://wa.me/6287861138090" target="_blank" rel="noopener" aria-label="WhatsApp">WhatsApp</a>
			</div>
		</div>

		<div class="site-footer__col site-footer__col--contact">
			<h2 class="site-footer__heading">Contact Us</h2>
			<ul class="site-footer__list">
				<li><a href="mailto:info@wonderlandbali.com">info@wonderlandbali.com</a></li>
				<li><a href="mailto:anastasia@wonderlandbali.com">anastasia@wonderlandbali.com</a></li>
				<li><a href="tel:+6287861138090">+62 878 6113 8090</a> &nbsp;|&nbsp; English</li>
			</ul>
		</div>

		<div class="site-footer__col site-footer__col--awards">
			<img src="<?php echo esc_url( $badge_1 ); ?>" alt="Bali's Best Awards 2024 — Gold Winner" loading="lazy" />
			<img src="<?php echo esc_url( $badge_2 ); ?>" alt="IGP Gold Winner" loading="lazy" />
		</div>
	</div>

	<div class="site-footer__bar">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Wonderland Bali Event &nbsp;|&nbsp; All Rights Reserved &nbsp;|&nbsp; Made by Cular Creative</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
