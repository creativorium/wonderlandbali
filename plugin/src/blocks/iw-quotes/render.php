<?php
/**
 * Server-side render for wonderland/iw-quotes.
 *
 * The rating is drawn as stars but announced as text, so a screen reader hears
 * "5 out of 5" rather than five bullet characters.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$eyebrow = $attributes['eyebrow'] ?? '';
$heading = $attributes['heading'] ?? '';
$cols    = isset( $attributes['columns'] ) ? max( 1, min( 4, (int) $attributes['columns'] ) ) : 3;
$items   = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();

if ( ! $items ) {
	return;
}

$wrapper = get_block_wrapper_attributes( array( 'class' => 'wl-iw-quotes' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wl-iw-quotes__inner">
		<?php if ( $eyebrow ) : ?>
			<p class="wl-iw-quotes__eyebrow"><?php echo wp_kses_post( $eyebrow ); ?></p>
		<?php endif; ?>

		<?php if ( $heading ) : ?>
			<h2 class="wl-iw-quotes__title"><?php echo wp_kses_post( $heading ); ?></h2>
		<?php endif; ?>

		<div class="wl-iw-quotes__grid" style="--wl-cols:<?php echo esc_attr( (string) $cols ); ?>">
			<?php foreach ( $items as $item ) : ?>
				<?php
				$quote  = trim( (string) ( $item['quote'] ?? '' ) );
				$name   = trim( (string) ( $item['name'] ?? '' ) );
				$rating = isset( $item['rating'] ) ? max( 0, min( 5, (int) $item['rating'] ) ) : 5;
				if ( '' === $quote ) {
					continue;
				}
				?>
				<figure class="wl-iw-quotes__item">
					<?php if ( $rating ) : ?>
						<p class="wl-iw-quotes__stars">
							<span aria-hidden="true"><?php echo esc_html( str_repeat( '★', $rating ) ); ?></span>
							<span class="screen-reader-text">
								<?php
								printf(
									/* translators: %d: star rating out of five. */
									esc_html__( '%d out of 5 stars', 'wonderland-blocks' ),
									(int) $rating
								);
								?>
							</span>
						</p>
					<?php endif; ?>
					<blockquote class="wl-iw-quotes__quote"><?php echo wp_kses_post( $quote ); ?></blockquote>
					<?php if ( $name ) : ?>
						<figcaption class="wl-iw-quotes__name"><?php echo esc_html( $name ); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
