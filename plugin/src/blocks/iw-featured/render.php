<?php
/**
 * Server-side render for wonderland/iw-featured.
 *
 * The press band on /indian-weddings/: the Honeycombers headline set as a
 * pull-quote, its byline beneath, a short summary of what the interview covers,
 * the couples the feature quotes, and one action through to the article.
 *
 * Flush left inside the same boxed column as every other section on the page —
 * the reviews sit in a row underneath, unboxed, so the words carry them.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$eyebrow  = $attributes['eyebrow'] ?? '';
$heading  = $attributes['heading'] ?? '';
$meta     = $attributes['meta'] ?? '';
$text     = $attributes['text'] ?? '';
$bg       = $attributes['background'] ?? 'taupe';
$bg       = in_array( $bg, array( 'greige', 'white' ), true ) ? 'is-' . $bg : 'is-taupe';
$quotes   = isset( $attributes['quotes'] ) && is_array( $attributes['quotes'] ) ? $attributes['quotes'] : array();
$btn_text = $attributes['buttonText'] ?? '';
$btn_url  = $attributes['buttonUrl'] ?? '';
$blank    = ! empty( $attributes['buttonNewTab'] );

$wrapper = get_block_wrapper_attributes( array( 'class' => 'wl-iw-featured ' . $bg ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wl-iw-featured__inner">
		<?php if ( $eyebrow ) : ?>
			<p class="wl-iw-featured__eyebrow"><?php echo wp_kses_post( $eyebrow ); ?></p>
		<?php endif; ?>

		<?php if ( $heading ) : ?>
			<?php // A quoted headline, so it is marked up as a quotation and not just styled like one. ?>
			<blockquote class="wl-iw-featured__quote">
				<h2 class="wl-iw-featured__title"><?php echo wp_kses_post( $heading ); ?></h2>
			</blockquote>
		<?php endif; ?>

		<?php if ( $meta ) : ?>
			<p class="wl-iw-featured__meta"><?php echo wp_kses_post( $meta ); ?></p>
		<?php endif; ?>

		<?php if ( $text ) : ?>
			<div class="wl-iw-featured__text"><?php echo wp_kses_post( wpautop( $text ) ); ?></div>
		<?php endif; ?>

		<?php if ( $quotes ) : ?>
			<?php // The couples the article quotes. No card, no rule around them — a hairline above each keeps the row tidy. ?>
			<ul class="wl-iw-featured__reviews">
				<?php foreach ( $quotes as $q ) : ?>
					<?php
					$q_text = is_array( $q ) ? trim( (string) ( $q['quote'] ?? '' ) ) : trim( (string) $q );
					$q_name = is_array( $q ) ? trim( (string) ( $q['name'] ?? '' ) ) : '';
					if ( '' === $q_text ) {
						continue;
					}
					?>
					<li class="wl-iw-featured__review">
						<figure>
							<blockquote class="wl-iw-featured__review-text"><?php echo wp_kses_post( wpautop( $q_text ) ); ?></blockquote>
							<?php if ( $q_name ) : ?>
								<figcaption class="wl-iw-featured__review-name"><?php echo esc_html( $q_name ); ?></figcaption>
							<?php endif; ?>
						</figure>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( $btn_text && $btn_url ) : ?>
			<div class="wl-iw-featured__actions">
				<a class="wl-iw-featured__btn" href="<?php echo esc_url( wonderland_link_url( $btn_url ) ); ?>"<?php echo $blank ? ' target="_blank" rel="noopener"' : ''; ?>>
					<?php echo esc_html( $btn_text ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>
