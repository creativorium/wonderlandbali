<?php
/**
 * Server-side render for wonderland/divider.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$slides   = isset( $attributes['slides'] ) && is_array( $attributes['slides'] ) ? $attributes['slides'] : array();
$duration = isset( $attributes['slideDuration'] ) ? max( 1500, (int) $attributes['slideDuration'] ) : 5000;
$heading  = $attributes['heading'] ?? '';

if ( empty( $slides ) ) {
	return;
}

$wrapper = get_block_wrapper_attributes( array( 'class' => 'wl-divider' . ( $heading ? ' has-heading' : '' ) ) );

$band_attrs = '';
if ( count( $slides ) > 1 ) {
	$band_attrs = ' data-slideshow="1" data-slide-duration="' . esc_attr( (string) $duration ) . '"';
}
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $heading ) : ?>
		<h2 class="wl-divider__title"><?php echo wp_kses_post( $heading ); ?></h2>
	<?php endif; ?>
	<div class="wl-divider__band"<?php echo $band_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-hidden="true">
		<?php foreach ( $slides as $i => $slide ) : ?>
			<?php $url = is_array( $slide ) ? ( $slide['url'] ?? '' ) : (string) $slide; ?>
			<?php if ( $url ) : ?>
				<div class="wl-divider__slide js-slide<?php echo 0 === $i ? ' is-active' : ''; ?>" style="background-image:url(<?php echo esc_url( $url ); ?>)"></div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</section>
