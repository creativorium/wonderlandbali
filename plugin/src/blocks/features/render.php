<?php
/**
 * Server-side render for wonderland/features.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$heading = $attributes['heading'] ?? '';
$items   = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();
$cols    = isset( $attributes['columns'] ) ? max( 1, min( 3, (int) $attributes['columns'] ) ) : 2;
$bg      = ( $attributes['background'] ?? 'white' ) === 'greige' ? 'is-greige' : 'is-white';

$wrapper = get_block_wrapper_attributes( array( 'class' => "wl-features $bg" ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $heading ) : ?>
		<h2 class="wl-features__title"><?php echo wp_kses_post( $heading ); ?></h2>
	<?php endif; ?>
	<?php if ( ! empty( $items ) ) : ?>
		<div class="wl-features__grid" style="--wl-cols:<?php echo esc_attr( (string) $cols ); ?>">
			<?php foreach ( $items as $item ) : ?>
				<div class="wl-features__item">
					<?php if ( ! empty( $item['title'] ) ) : ?>
						<h3 class="wl-features__name"><?php echo wp_kses_post( $item['title'] ); ?></h3>
					<?php endif; ?>
					<?php if ( ! empty( $item['text'] ) ) : ?>
						<div class="wl-features__text"><?php echo wp_kses_post( wpautop( $item['text'] ) ); ?></div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
