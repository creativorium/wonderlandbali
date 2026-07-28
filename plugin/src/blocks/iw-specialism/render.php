<?php
/**
 * Server-side render for wonderland/iw-specialism.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$eyebrow = $attributes['eyebrow'] ?? '';
$heading = $attributes['heading'] ?? '';
$text    = $attributes['text'] ?? '';
$bg      = ( 'greige' === ( $attributes['background'] ?? '' ) ) ? 'is-greige' : 'is-white';
$chips   = isset( $attributes['chips'] ) && is_array( $attributes['chips'] ) ? $attributes['chips'] : array();

$wrapper = get_block_wrapper_attributes( array( 'class' => 'wl-iw-specialism ' . $bg ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wl-iw-specialism__inner">
		<?php if ( $eyebrow ) : ?>
			<p class="wl-iw-specialism__eyebrow"><?php echo wp_kses_post( $eyebrow ); ?></p>
		<?php endif; ?>

		<?php if ( $heading ) : ?>
			<h2 class="wl-iw-specialism__title"><?php echo wp_kses_post( $heading ); ?></h2>
		<?php endif; ?>

		<?php if ( $text ) : ?>
			<div class="wl-iw-specialism__text"><?php echo wp_kses_post( wpautop( $text ) ); ?></div>
		<?php endif; ?>

		<?php if ( $chips ) : ?>
			<ul class="wl-iw-specialism__chips">
				<?php foreach ( $chips as $chip ) : ?>
					<?php $label = is_array( $chip ) ? ( $chip['text'] ?? '' ) : (string) $chip; ?>
					<?php if ( '' === trim( (string) $label ) ) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<li><?php echo esc_html( $label ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
