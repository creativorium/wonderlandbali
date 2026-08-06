<?php
/**
 * Server-side render for wonderland/features.
 *
 * Two variants:
 *  - cards : each item is a titled block of copy (the default).
 *  - list  : a bulleted list flowed into columns, optionally under a lead line.
 *            Items with both a title and text render as "Title: text".
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$heading = $attributes['heading'] ?? '';
$items   = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();
$cols    = isset( $attributes['columns'] ) ? max( 1, min( 4, (int) $attributes['columns'] ) ) : 2;
$bg      = $attributes['background'] ?? 'white';
$bg      = in_array( $bg, array( 'greige', 'taupe' ), true ) ? 'is-' . $bg : 'is-white';
$variant = $attributes['variant'] ?? '';
$variant = in_array( $variant, array( 'list', 'stats', 'links', 'quotes' ), true ) ? $variant : 'cards';
$intro   = $attributes['intro'] ?? '';
$marker  = ( 'ring' === ( $attributes['marker'] ?? '' ) ) ? 'has-ring' : 'has-dot';

$wrapper = get_block_wrapper_attributes(
	array( 'class' => "wl-features $bg wl-features--$variant" )
);
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $heading ) : ?>
		<h2 class="wl-features__title"><?php echo wp_kses_post( $heading ); ?></h2>
	<?php endif; ?>

	<?php if ( $intro ) : ?>
		<p class="wl-features__intro"><?php echo wp_kses_post( $intro ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $items ) ) : ?>
		<?php if ( 'stats' === $variant ) : ?>
			<?php // A thin band of proof — the figure leads, its label sits under it. ?>
			<ul class="wl-features__stats">
				<?php foreach ( $items as $item ) : ?>
					<?php
					$figure = trim( (string) ( $item['title'] ?? '' ) );
					$label  = trim( (string) ( $item['text'] ?? '' ) );
					if ( '' === $figure && '' === $label ) {
						continue;
					}
					?>
					<li>
						<?php if ( $figure ) : ?>
							<span class="wl-features__stat-figure"><?php echo wp_kses_post( $figure ); ?></span>
						<?php endif; ?>
						<?php if ( $label ) : ?>
							<span class="wl-features__stat-label"><?php echo wp_kses_post( $label ); ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php elseif ( 'quotes' === $variant ) : ?>
			<?php
			// Pull-quotes: the words lead, the attribution follows. Past the column
			// count it becomes the same snap-scrolling row as the Indian page's
			// reviews — same hooks, same script.
			$q_sliding = count( $items ) > $cols;
			?>
			<div class="wl-features__quotes-slider<?php echo $q_sliding ? ' is-sliding' : ''; ?>"
				<?php echo $q_sliding ? 'data-quotes data-quotes-autoplay="7000"' : ''; ?>>
				<div class="wl-features__quotes" data-quotes-track style="--wl-cols:<?php echo esc_attr( (string) $cols ); ?>">
					<?php foreach ( $items as $item ) : ?>
						<?php
						$who    = trim( (string) ( $item['title'] ?? '' ) );
						$quote  = trim( (string) ( $item['text'] ?? '' ) );
						$rating = isset( $item['rating'] ) ? max( 0, min( 5, (int) $item['rating'] ) ) : 0;
						if ( '' === $quote ) {
							continue;
						}
						?>
						<figure class="wl-features__quote" data-quotes-item>
							<?php if ( $rating ) : ?>
								<p class="wl-features__quote-stars">
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
							<blockquote><?php echo wp_kses_post( $quote ); ?></blockquote>
							<?php if ( $who ) : ?>
								<figcaption><?php echo wp_kses_post( $who ); ?></figcaption>
							<?php endif; ?>
						</figure>
					<?php endforeach; ?>
				</div>

				<?php if ( $q_sliding ) : ?>
					<button class="wl-features__quotes-arrow is-prev" type="button" data-quotes-prev
						aria-label="<?php esc_attr_e( 'Previous reviews', 'wonderland-blocks' ); ?>">&lsaquo;</button>
					<button class="wl-features__quotes-arrow is-next" type="button" data-quotes-next
						aria-label="<?php esc_attr_e( 'More reviews', 'wonderland-blocks' ); ?>">&rsaquo;</button>
					<div class="wl-quotes-dots" data-quotes-dots aria-hidden="true"></div>
				<?php endif; ?>
			</div>
		<?php elseif ( 'links' === $variant ) : ?>
			<?php // Cross-links: the whole card is the link, so the target is easy to hit. ?>
			<div class="wl-features__links" style="--wl-cols:<?php echo esc_attr( (string) $cols ); ?>">
				<?php foreach ( $items as $item ) : ?>
					<?php
					$title = trim( (string) ( $item['title'] ?? '' ) );
					$copy  = trim( (string) ( $item['text'] ?? '' ) );
					$url   = trim( (string) ( $item['url'] ?? '' ) );
					if ( '' === $title || '' === $url ) {
						continue;
					}
					?>
					<?php
					$rating = isset( $item['rating'] ) ? max( 0, min( 5, (int) $item['rating'] ) ) : 0;
					$badge  = trim( (string) ( $item['badge'] ?? '' ) );
					?>
					<a class="wl-features__link" href="<?php echo esc_url( $url ); ?>">
						<?php if ( $rating || $badge ) : ?>
							<?php // Small signal that what follows is a review, not a page. ?>
							<span class="wl-features__link-meta">
								<?php if ( $badge ) : ?>
									<img class="wl-features__link-badge"
										src="<?php echo esc_url( function_exists( 'wonderland_bg_url' ) ? wonderland_bg_url( $badge, 'medium' ) : $badge ); ?>"
										alt="" loading="lazy" decoding="async" width="28" height="28" />
								<?php endif; ?>
								<?php if ( $rating ) : ?>
									<span class="wl-features__link-stars" aria-hidden="true"><?php echo esc_html( str_repeat( '★', $rating ) ); ?></span>
									<span class="screen-reader-text">
										<?php
										printf(
											/* translators: %d: star rating out of five. */
											esc_html__( '%d out of 5 stars', 'wonderland-blocks' ),
											(int) $rating
										);
										?>
									</span>
								<?php endif; ?>
							</span>
						<?php endif; ?>
						<span class="wl-features__link-title"><?php echo wp_kses_post( $title ); ?></span>
						<?php if ( $copy ) : ?>
							<span class="wl-features__link-text"><?php echo wp_kses_post( $copy ); ?></span>
						<?php endif; ?>
						<span class="wl-features__link-arrow" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
								<path d="M5 12h13M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php elseif ( 'list' === $variant ) : ?>
			<ul class="wl-features__list <?php echo esc_attr( $marker ); ?>" style="--wl-cols:<?php echo esc_attr( (string) $cols ); ?>">
				<?php foreach ( $items as $item ) : ?>
					<?php
					$title = trim( (string) ( $item['title'] ?? '' ) );
					$text  = trim( (string) ( $item['text'] ?? '' ) );
					if ( '' === $title && '' === $text ) {
						continue;
					}
					?>
					<li>
						<?php if ( $title && $text ) : ?>
							<strong><?php echo wp_kses_post( $title ); ?>:</strong> <?php echo wp_kses_post( $text ); ?>
						<?php else : ?>
							<?php echo wp_kses_post( $title ? $title : $text ); ?>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<div class="wl-features__grid" style="--wl-cols:<?php echo esc_attr( (string) $cols ); ?>">
				<?php foreach ( $items as $item ) : ?>
					<?php $media = $item['imageUrl'] ?? ''; ?>
					<div class="wl-features__item<?php echo $media ? ' has-media' : ''; ?>">
						<?php if ( $media ) : ?>
							<figure class="wl-features__media">
								<?php
								echo wonderland_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									$media,
									array(
										'size'  => 'medium_large',
										'sizes' => '(max-width: 760px) 100vw, (max-width: 1000px) 50vw, ' . round( 100 / $cols ) . 'vw',
									)
								);
								?>
							</figure>
						<?php endif; ?>
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
	<?php endif; ?>
</section>
