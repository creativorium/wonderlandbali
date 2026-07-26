<?php
/**
 * Server-side render for wonderland/portfolio.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$heading  = $attributes['heading'] ?? '';
$images   = isset( $attributes['images'] ) && is_array( $attributes['images'] ) ? $attributes['images'] : array();
$btn_text = $attributes['buttonText'] ?? '';
$btn_url  = $attributes['buttonUrl'] ?? '#';

$wrapper = get_block_wrapper_attributes( array( 'class' => 'wl-portfolio' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $heading ) : ?>
		<h2 class="wl-portfolio__title"><?php echo wp_kses_post( $heading ); ?></h2>
	<?php endif; ?>

	<?php if ( ! empty( $images ) ) : ?>
		<div class="wl-portfolio__grid">
			<?php
			foreach ( $images as $img ) :
				$url = is_array( $img ) ? ( $img['url'] ?? '' ) : (string) $img;
				if ( ! $url ) {
					continue;
				}
				?>
				<a class="wl-portfolio__item" href="<?php echo esc_url( $btn_url ); ?>">
					<img src="<?php echo esc_url( $url ); ?>" alt="" loading="lazy" decoding="async" />
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( $btn_text ) : ?>
		<div class="wl-portfolio__more">
			<a class="wl-portfolio__btn" href="<?php echo esc_url( $btn_url ); ?>">
				<span><?php echo esc_html( $btn_text ); ?></span>
				<svg class="wl-portfolio__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M10 8l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</a>
		</div>
	<?php endif; ?>
</section>
