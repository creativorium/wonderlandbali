<?php
/**
 * Server-side render for wonderland/iw-included.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$eyebrow = $attributes['eyebrow'] ?? '';
$heading = $attributes['heading'] ?? '';
$bg      = ( 'white' === ( $attributes['background'] ?? '' ) ) ? 'is-white' : 'is-greige';
$cols    = isset( $attributes['columns'] ) ? max( 1, min( 4, (int) $attributes['columns'] ) ) : 3;
$items   = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();

$wrapper = get_block_wrapper_attributes( array( 'class' => 'wl-iw-included ' . $bg ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wl-iw-included__inner">
		<?php if ( $eyebrow ) : ?>
			<p class="wl-iw-included__eyebrow"><?php echo wp_kses_post( $eyebrow ); ?></p>
		<?php endif; ?>

		<?php if ( $heading ) : ?>
			<h2 class="wl-iw-included__title"><?php echo wp_kses_post( $heading ); ?></h2>
		<?php endif; ?>

		<?php if ( $items ) : ?>
			<div class="wl-iw-included__grid" style="--wl-cols:<?php echo esc_attr( (string) $cols ); ?>">
				<?php foreach ( $items as $item ) : ?>
					<?php
					$name = trim( (string) ( $item['title'] ?? '' ) );
					$copy = trim( (string) ( $item['text'] ?? '' ) );
					if ( '' === $name && '' === $copy ) {
						continue;
					}
					?>
					<article class="wl-iw-included__card">
						<?php if ( $name ) : ?>
							<h3 class="wl-iw-included__name"><?php echo wp_kses_post( $name ); ?></h3>
						<?php endif; ?>
						<?php if ( $copy ) : ?>
							<p class="wl-iw-included__text"><?php echo wp_kses_post( $copy ); ?></p>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
