<?php
/**
 * Server-side render for wonderland/iw-quotes.
 *
 * A horizontal, snap-scrolling row of review cards. Scrolling is native — the
 * arrows only call scrollBy — so it swipes on touch without any slider library
 * and still works with JavaScript off.
 *
 * The rating is drawn as stars but announced as text, so a screen reader hears
 * "5 out of 5" rather than five bullet characters.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$eyebrow = $attributes['eyebrow'] ?? '';
$heading = $attributes['heading'] ?? '';
$intro   = $attributes['intro'] ?? '';
$cols    = isset( $attributes['columns'] ) ? max( 1, min( 4, (int) $attributes['columns'] ) ) : 3;
$items   = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();
$src_txt = trim( (string) ( $attributes['sourceText'] ?? '' ) );
$src_url = trim( (string) ( $attributes['sourceUrl'] ?? '' ) );
$badge   = trim( (string) ( $attributes['sourceBadge'] ?? '' ) );

if ( ! $items ) {
	return;
}

// Only worth arrows when there is something past the fold.
$sliding = count( $items ) > $cols;

$wrapper = get_block_wrapper_attributes(
	array( 'class' => 'wl-iw-quotes' . ( $sliding ? ' is-sliding' : '' ) )
);
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wl-iw-quotes__inner">
		<?php if ( $eyebrow ) : ?>
			<p class="wl-iw-quotes__eyebrow"><?php echo wp_kses_post( $eyebrow ); ?></p>
		<?php endif; ?>

		<?php if ( $heading ) : ?>
			<h2 class="wl-iw-quotes__title"><?php echo wp_kses_post( $heading ); ?></h2>
		<?php endif; ?>

		<?php if ( $intro ) : ?>
			<p class="wl-iw-quotes__intro"><?php echo wp_kses_post( $intro ); ?></p>
		<?php endif; ?>

		<div class="wl-iw-quotes__slider" data-quotes data-quotes-autoplay="7000">
			<div class="wl-iw-quotes__track" data-quotes-track style="--wl-cols:<?php echo esc_attr( (string) $cols ); ?>">
				<?php foreach ( $items as $item ) : ?>
					<?php
					$quote  = trim( (string) ( $item['quote'] ?? '' ) );
					$name   = trim( (string) ( $item['name'] ?? '' ) );
					$rating = isset( $item['rating'] ) ? max( 0, min( 5, (int) $item['rating'] ) ) : 5;
					if ( '' === $quote ) {
						continue;
					}
					?>
					<figure class="wl-iw-quotes__item" data-quotes-item>
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

			<?php if ( $sliding ) : ?>
				<button class="wl-iw-quotes__arrow wl-iw-quotes__arrow--prev" type="button"
					data-quotes-prev aria-label="<?php esc_attr_e( 'Previous reviews', 'wonderland-blocks' ); ?>">&lsaquo;</button>
				<button class="wl-iw-quotes__arrow wl-iw-quotes__arrow--next" type="button"
					data-quotes-next aria-label="<?php esc_attr_e( 'More reviews', 'wonderland-blocks' ); ?>">&rsaquo;</button>
			<?php endif; ?>

			<?php if ( $sliding ) : ?>
				<div class="wl-quotes-dots" data-quotes-dots aria-hidden="true"></div>
			<?php endif; ?>
		</div>

		<?php if ( $src_txt ) : ?>
			<?php // Where the reviews came from: badge, rating and a link to the source. ?>
			<div class="wl-iw-quotes__source">
				<?php if ( $badge ) : ?>
					<img class="wl-iw-quotes__source-badge"
						src="<?php echo esc_url( function_exists( 'wonderland_bg_url' ) ? wonderland_bg_url( $badge, 'medium' ) : $badge ); ?>"
						alt="" loading="lazy" decoding="async" width="56" height="56" />
				<?php endif; ?>

				<p class="wl-iw-quotes__source-text">
					<span class="wl-iw-quotes__source-stars" aria-hidden="true">★★★★★</span>
					<?php if ( $src_url ) : ?>
						<a href="<?php echo esc_url( $src_url ); ?>" target="_blank" rel="noopener">
							<?php echo esc_html( $src_txt ); ?>
						</a>
					<?php else : ?>
						<?php echo esc_html( $src_txt ); ?>
					<?php endif; ?>
				</p>
			</div>
		<?php endif; ?>
	</div>
</section>
