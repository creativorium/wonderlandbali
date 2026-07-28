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

// Wonderland mark for the CTA (same mark as the hero button).
$rabbit_svg = function_exists( 'wonderland_mark_svg' ) ? wonderland_mark_svg( 'wl-testi__rabbit' ) : '';
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wl-testi__bg" aria-hidden="true">
		<?php foreach ( $items as $i => $item ) : ?>
			<?php $img = is_array( $item ) ? ( $item['image'] ?? '' ) : ''; ?>
			<div class="wl-testi__slide js-testi-bg<?php echo 0 === $i ? ' is-active' : ''; ?>"<?php echo $img ? ' style="background-image:url(' . esc_url( wonderland_bg_url( $img ) ) . ')"' : ''; ?>></div>
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
