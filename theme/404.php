<?php
/**
 * 404 — page not found.
 *
 * Full-bleed brand screen in the page-hero idiom: butterfly mark, oversized
 * display numeral straddling the copy, then a short set of real destinations so
 * a wrong URL still lands the visitor somewhere useful.
 *
 * @package Wonderland
 */

get_header();

/**
 * The handful of pages worth offering from a dead end, by slug.
 * Missing pages are skipped, so this stays safe if a slug is renamed.
 */
$suggested = array(
	'portfolio' => 'See our work',
	'about-us'  => 'Who we are',
	'contact'   => 'Talk to us',
	'request'   => 'Start planning',
);
?>

<section class="wl-404">
	<div class="wl-404__inner">
		<?php if ( function_exists( 'wonderland_mark_svg' ) ) : ?>
			<?php echo wonderland_mark_svg( 'wl-404__mark' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endif; ?>

		<p class="wl-404__eyebrow"><?php esc_html_e( 'Down the rabbit hole', 'wonderland' ); ?></p>

		<div class="wl-404__headline">
			<span class="wl-404__code" aria-hidden="true">404</span>
			<h1 class="wl-404__title"><?php esc_html_e( 'This page has wandered off', 'wonderland' ); ?></h1>
		</div>

		<p class="wl-404__text">
			<?php esc_html_e( '"I can\'t go back to yesterday, because I was a different person then." The page you were looking for has moved, been renamed, or never existed. Let\'s get you somewhere lovelier.', 'wonderland' ); ?>
		</p>

		<div class="wl-404__actions">
			<a class="wl-404__button" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Back to home', 'wonderland' ); ?>
			</a>
		</div>

		<?php
		$links = array();
		foreach ( $suggested as $slug => $label ) {
			$page = get_page_by_path( $slug );
			if ( $page && 'publish' === $page->post_status ) {
				$links[] = array( get_permalink( $page ), $label );
			}
		}
		?>
		<?php if ( $links ) : ?>
			<nav class="wl-404__links" aria-label="<?php esc_attr_e( 'Suggested pages', 'wonderland' ); ?>">
				<?php foreach ( $links as $link ) : ?>
					<a href="<?php echo esc_url( $link[0] ); ?>"><?php echo esc_html( $link[1] ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<div class="wl-404__search">
			<?php get_search_form(); ?>
		</div>
	</div>
</section>

<?php
get_footer();
