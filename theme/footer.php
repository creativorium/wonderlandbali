<?php
/**
 * Site footer — press logos band + dark footer (contact / brand / socials).
 *
 * @package Wonderland
 */

$logo_uri = WONDERLAND_URI . '/assets/img/logo.svg';
$uploads  = content_url( '/uploads' );
$badge_1  = $uploads . '/2023/06/Gold-Winner-Balis-Best-Awards-2024.png';
$badge_2  = $uploads . '/2023/06/IGP-Gold-Winner2.png';

$icon_fb = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-8h2.5l.5-3h-3V8.2c0-.9.3-1.5 1.6-1.5H17V4.1C16.7 4 15.8 4 14.8 4 12.6 4 11 5.3 11 7.9V10H8.5v3H11v8h2.5z"/></svg>';
$icon_ig = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="5"/><circle cx="12" cy="12" r="3.5"/><circle cx="17" cy="7" r="1" fill="currentColor" stroke="none"/></svg>';
$icon_wa = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3a9 9 0 0 0-7.7 13.6L3 21l4.5-1.2A9 9 0 1 0 12 3zm0 2a7 7 0 0 1 5.9 10.7l-.3.5.6 2-2.1-.6-.5.3A7 7 0 1 1 12 5zm-2.6 3.3c-.2 0-.5 0-.7.3-.3.3-.9.9-.9 2.1s.9 2.4 1 2.6c.1.2 1.7 2.8 4.3 3.8 2.1.8 2.5.7 3 .6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.1-1.2 0-.1-.2-.2-.5-.3l-1.7-.8c-.2-.1-.4-.1-.6.1l-.7.9c-.1.2-.3.2-.5.1-.7-.3-1.4-.6-2.2-1.5-.6-.7-1-1.4-1.1-1.6-.1-.2 0-.4.1-.5l.5-.5c.1-.2.2-.3.3-.5.1-.2 0-.4 0-.5l-.8-1.9c-.1-.3-.3-.3-.5-.3z"/></svg>';
?>
</main><!-- #content -->

<section class="site-press" aria-label="<?php esc_attr_e( 'Featured in', 'wonderland' ); ?>">
	<div class="site-press__track">
		<?php // two identical runs so the marquee loops seamlessly. ?>
		<?php for ( $run = 0; $run < 2; $run++ ) : ?>
			<?php for ( $i = 1; $i <= 6; $i++ ) : ?>
				<img class="site-press__logo" src="<?php echo esc_url( $uploads . '/2023/06/' . $i . '.png' ); ?>" alt="" loading="lazy" decoding="async" <?php echo $run ? 'aria-hidden="true"' : ''; ?> />
			<?php endfor; ?>
		<?php endfor; ?>
	</div>
</section>

<footer class="site-footer" id="site-footer">
	<div class="site-footer__main">
		<div class="site-footer__col site-footer__col--left">
			<p class="site-footer__tagline">Give us a call and let&rsquo;s create wonders together!</p>
			<div class="site-footer__social">
				<a href="https://www.facebook.com/wonderlandbali/" target="_blank" rel="noopener" aria-label="Facebook"><?php echo $icon_fb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				<a href="https://www.instagram.com/wonderland_events_worldwide/" target="_blank" rel="noopener" aria-label="Instagram"><?php echo $icon_ig; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				<a href="https://wa.me/6287861138090" target="_blank" rel="noopener" aria-label="WhatsApp"><?php echo $icon_wa; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			</div>
			<div class="site-footer__awards">
				<img src="<?php echo esc_url( $badge_1 ); ?>" alt="Bali's Best Awards 2024 — Gold Winner" loading="lazy" />
				<img src="<?php echo esc_url( $badge_2 ); ?>" alt="IGP Gold Winner" loading="lazy" />
			</div>
		</div>

		<div class="site-footer__col site-footer__col--brand">
			<img class="site-footer__logo" src="<?php echo esc_url( $logo_uri ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="200" height="180" />
			<p class="site-footer__blurb">We are the wonder-makers. Inspired by Lewis Carroll&rsquo;s Alice&rsquo;s Adventures in Wonderland.<br>Focused on fairytale scenarios, with a strong emphasis on attention to detail.</p>
		</div>

		<div class="site-footer__col site-footer__col--contact">
			<h2 class="site-footer__heading">Contact Us</h2>
			<ul class="site-footer__list">
				<li><a href="mailto:info@wonderlandbali.com">info@wonderlandbali.com</a></li>
				<li><a href="mailto:anastasia@wonderlandbali.com">anastasia@wonderlandbali.com</a></li>
				<li><a href="tel:+6287861138090">+62 878 6113 8090</a> &nbsp;|&nbsp; English</li>
				<li><a href="https://wa.me/6287861138090" target="_blank" rel="noopener">+62 878 6113 8090</a></li>
			</ul>
			<a class="site-footer__request" href="<?php echo esc_url( home_url( '/request-2/' ) ); ?>">Make a Request</a>
		</div>
	</div>

	<div class="site-footer__bar">
		<p>&copy; Copyright <?php echo esc_html( gmdate( 'Y' ) ); ?>. WONDERLAND BALI EVENT &nbsp;|&nbsp; All Rights Reserved &nbsp;|&nbsp; Made by Cular Creative</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
