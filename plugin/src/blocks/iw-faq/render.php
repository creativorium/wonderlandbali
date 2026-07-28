<?php
/**
 * Server-side render for wonderland/iw-faq.
 *
 * A definition list is the honest markup for Q&A; the two columns come from
 * CSS multicol, which keeps the reading order down each column.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$eyebrow = $attributes['eyebrow'] ?? '';
$heading = $attributes['heading'] ?? '';
$bg      = ( 'greige' === ( $attributes['background'] ?? '' ) ) ? 'is-greige' : 'is-white';
$cols    = isset( $attributes['columns'] ) ? max( 1, min( 3, (int) $attributes['columns'] ) ) : 2;
$items   = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();

$wrapper = get_block_wrapper_attributes( array( 'class' => 'wl-iw-faq ' . $bg ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wl-iw-faq__inner">
		<?php if ( $eyebrow ) : ?>
			<p class="wl-iw-faq__eyebrow"><?php echo wp_kses_post( $eyebrow ); ?></p>
		<?php endif; ?>

		<?php if ( $heading ) : ?>
			<h2 class="wl-iw-faq__title"><?php echo wp_kses_post( $heading ); ?></h2>
		<?php endif; ?>

		<?php if ( $items ) : ?>
			<dl class="wl-iw-faq__list" style="--wl-cols:<?php echo esc_attr( (string) $cols ); ?>">
				<?php foreach ( $items as $item ) : ?>
					<?php
					$q = trim( (string) ( $item['question'] ?? '' ) );
					$a = trim( (string) ( $item['answer'] ?? '' ) );
					if ( '' === $q ) {
						continue;
					}
					?>
					<div class="wl-iw-faq__item">
						<dt class="wl-iw-faq__q"><?php echo wp_kses_post( $q ); ?></dt>
						<?php if ( $a ) : ?>
							<dd class="wl-iw-faq__a"><?php echo wp_kses_post( $a ); ?></dd>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</dl>
		<?php endif; ?>
	</div>
</section>
