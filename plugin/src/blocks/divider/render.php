<?php
/**
 * Server-side render for wonderland/divider.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$slides   = isset( $attributes['slides'] ) && is_array( $attributes['slides'] ) ? $attributes['slides'] : array();
$duration = isset( $attributes['slideDuration'] ) ? max( 1500, (int) $attributes['slideDuration'] ) : 5000;

if ( empty( $slides ) ) {
	return;
}

$args = array( 'class' => 'wl-divider' );
if ( count( $slides ) > 1 ) {
	$args['data-slideshow']      = '1';
	$args['data-slide-duration'] = (string) $duration;
}
$wrapper = get_block_wrapper_attributes( $args );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-hidden="true">
	<?php foreach ( $slides as $i => $slide ) : ?>
		<?php $url = is_array( $slide ) ? ( $slide['url'] ?? '' ) : (string) $slide; ?>
		<?php if ( $url ) : ?>
			<div class="wl-divider__slide js-slide<?php echo 0 === $i ? ' is-active' : ''; ?>" style="background-image:url(<?php echo esc_url( $url ); ?>)"></div>
		<?php endif; ?>
	<?php endforeach; ?>
</section>
