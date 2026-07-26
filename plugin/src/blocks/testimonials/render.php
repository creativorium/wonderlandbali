<?php
/**
 * Server-side render for wonderland/testimonials.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$items    = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();
$overlay  = isset( $attributes['overlayOpacity'] ) ? (float) $attributes['overlayOpacity'] / 100 : 0.45;
$duration = isset( $attributes['duration'] ) ? (int) $attributes['duration'] : 8000;

if ( empty( $items ) ) {
	return;
}

$args = array(
	'class'         => 'wl-testi',
	'data-testi'    => '1',
	'data-duration' => (string) $duration,
);
$wrapper = get_block_wrapper_attributes( $args );

// White-rabbit icon for the CTA.
$rabbit_svg = '<svg class="wl-testi__rabbit" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8.4 2.6c-.9 0-1.5 1.4-1.5 3.6 0 1.7.4 3.2 1 4.1-1.8 1-3 2.8-3 4.9C4.9 18.4 8.1 21 12 21s7.1-2.6 7.1-5.8c0-2.1-1.2-3.9-3-4.9.6-.9 1-2.4 1-4.1 0-2.2-.6-3.6-1.5-3.6-1 0-1.8 1.8-1.8 4.1 0 .5 0 .9.1 1.3-.6-.2-1.3-.3-1.9-.3s-1.3.1-1.9.3c.1-.4.1-.8.1-1.3 0-2.3-.8-4.1-1.8-4.1zM9.6 14.2a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm4.8 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2zM12 16.6c.7 0 1.3.3 1.3.8 0 .4-.6.8-1.3.8s-1.3-.4-1.3-.8c0-.5.6-.8 1.3-.8z"/></svg>';
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wl-testi__bg" aria-hidden="true">
		<?php foreach ( $items as $i => $item ) : ?>
			<?php $img = is_array( $item ) ? ( $item['image'] ?? '' ) : ''; ?>
			<div class="wl-testi__slide js-testi-bg<?php echo 0 === $i ? ' is-active' : ''; ?>"<?php echo $img ? ' style="background-image:url(' . esc_url( $img ) . ')"' : ''; ?>></div>
		<?php endforeach; ?>
	</div>

	<span class="wl-testi__overlay" style="opacity:<?php echo esc_attr( (string) $overlay ); ?>" aria-hidden="true"></span>

	<?php if ( count( $items ) > 1 ) : ?>
		<button class="wl-testi__arrow wl-testi__arrow--prev" data-testi-prev aria-label="<?php esc_attr_e( 'Previous', 'wonderland-blocks' ); ?>">&lsaquo;</button>
		<button class="wl-testi__arrow wl-testi__arrow--next" data-testi-next aria-label="<?php esc_attr_e( 'Next', 'wonderland-blocks' ); ?>">&rsaquo;</button>
	<?php endif; ?>

	<div class="wl-testi__items">
		<?php foreach ( $items as $i => $item ) : ?>
			<?php
			$name  = is_array( $item ) ? ( $item['name'] ?? '' ) : '';
			$quote = is_array( $item ) ? ( $item['quote'] ?? '' ) : '';
			$bt    = is_array( $item ) ? ( $item['buttonText'] ?? '' ) : '';
			$bu    = is_array( $item ) ? ( $item['buttonUrl'] ?? '' ) : '';
			?>
			<blockquote class="wl-testi__item js-testi-item<?php echo 0 === $i ? ' is-active' : ''; ?>">
				<?php if ( $name ) : ?><p class="wl-testi__name"><?php echo wp_kses_post( $name ); ?></p><?php endif; ?>
				<?php if ( $quote ) : ?><div class="wl-testi__quote"><?php echo wp_kses_post( $quote ); ?></div><?php endif; ?>
				<?php if ( $bt && $bu ) : ?><a class="wl-testi__btn" href="<?php echo esc_url( $bu ); ?>"><?php echo $rabbit_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $bt ); ?></span></a><?php endif; ?>
			</blockquote>
		<?php endforeach; ?>
	</div>
</section>
