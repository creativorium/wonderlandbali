<?php
/**
 * Server-side render for wonderland/showcase.
 *
 * A two-column editorial section. The left column holds an optional lead image,
 * the copy, and an optional row of images beneath it; the right column holds a
 * stack of images and the call to action. Both Portugal sections are this block
 * with different slots filled.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$heading  = $attributes['heading'] ?? '';
$size     = ( 'normal' === ( $attributes['headingSize'] ?? '' ) ) ? 'normal' : 'giant';
$straddle = ! empty( $attributes['straddle'] );
$bg       = ( 'white' === ( $attributes['background'] ?? '' ) ) ? 'is-white' : 'is-greige';
$text     = $attributes['text'] ?? '';
$lead     = $attributes['leadImageUrl'] ?? '';
$side     = isset( $attributes['sideImages'] ) && is_array( $attributes['sideImages'] ) ? $attributes['sideImages'] : array();
$bottom   = isset( $attributes['bottomImages'] ) && is_array( $attributes['bottomImages'] ) ? $attributes['bottomImages'] : array();
$btn_text = $attributes['buttonText'] ?? '';
$btn_url  = $attributes['buttonUrl'] ?? '';

$classes = 'wl-showcase ' . $bg . ' heading-' . $size . ( $straddle ? ' is-straddle' : '' );
$wrapper = get_block_wrapper_attributes( array( 'class' => $classes ) );

/**
 * Emit one image, falling back gracefully when the helper is unavailable.
 *
 * @param mixed  $img   Image entry (array with url, or a bare URL).
 * @param string $sizes The sizes attribute.
 */
$figure = function ( $img, $sizes ) {
	$url = is_array( $img ) ? ( $img['url'] ?? '' ) : (string) $img;
	if ( ! $url ) {
		return;
	}
	echo '<figure class="wl-showcase__figure">';
	echo function_exists( 'wonderland_image' )
		? wonderland_image( $url, array( 'size' => 'large', 'sizes' => $sizes ) ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		: '<img src="' . esc_url( wonderland_media_url( $url ) ) . '" alt="" loading="lazy" decoding="async" />';
	echo '</figure>';
};
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $heading ) : ?>
		<h2 class="wl-showcase__title"><?php echo wp_kses_post( $heading ); ?></h2>
	<?php endif; ?>

	<div class="wl-showcase__inner">
		<div class="wl-showcase__main">
			<?php $figure( $lead, '(max-width: 900px) 100vw, 55vw' ); ?>

			<?php if ( $text ) : ?>
				<div class="wl-showcase__text"><?php echo wp_kses_post( wpautop( $text ) ); ?></div>
			<?php endif; ?>

			<?php if ( $bottom ) : ?>
				<div class="wl-showcase__row">
					<?php foreach ( $bottom as $img ) { $figure( $img, '(max-width: 900px) 50vw, 28vw' ); } ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="wl-showcase__aside">
			<?php foreach ( $side as $img ) { $figure( $img, '(max-width: 900px) 100vw, 35vw' ); } ?>

			<?php if ( $btn_text && $btn_url ) : ?>
				<div class="wl-showcase__cta">
					<a class="wl-showcase__btn" href="<?php echo esc_url( $btn_url ); ?>"><?php echo esc_html( $btn_text ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
