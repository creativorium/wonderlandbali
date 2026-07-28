<?php
/**
 * Server-side render for wonderland/portfolio.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$heading  = $attributes['heading'] ?? '';
$images   = isset( $attributes['images'] ) && is_array( $attributes['images'] ) ? $attributes['images'] : array();
$btn_text = $attributes['buttonText'] ?? '';
$btn_url  = $attributes['buttonUrl'] ?? '#';
$columns  = isset( $attributes['columns'] ) ? max( 1, min( 6, (int) $attributes['columns'] ) ) : 3;
$gap      = isset( $attributes['gap'] ) ? max( 0, (int) $attributes['gap'] ) : 6;
$lightbox = ! empty( $attributes['lightbox'] );

// `masonry` balances varied heights across columns; `strip` is a single
// full-bleed row of equal-height frames, used by the service pages.
$layout = ( 'strip' === ( $attributes['layout'] ?? '' ) ) ? 'strip' : 'masonry';

// Opt-in roomier framing: more air around the block and between frames.
$padded = ! empty( $attributes['padded'] ) ? ' is-padded' : '';

// Progressive reveal. Large galleries start partly hidden and grow a batch at a
// time — hidden items keep loading="lazy", so their files are never fetched
// until they are revealed.
$reveal  = ! empty( $attributes['reveal'] );
$initial = isset( $attributes['initialCount'] ) ? max( 1, (int) $attributes['initialCount'] ) : 12;
$batch   = isset( $attributes['batchSize'] ) ? max( 1, (int) $attributes['batchSize'] ) : 12;
$total   = count( $images );
$reveal  = $reveal && $total > $initial;

$wrapper = get_block_wrapper_attributes( array( 'class' => 'wl-portfolio wl-portfolio--' . $layout . $padded ) );

$grid_style = '--wl-cols:' . $columns . ';--wl-gap:' . $gap . 'px;';
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $heading ) : ?>
		<h2 class="wl-portfolio__title"><?php echo wp_kses_post( $heading ); ?></h2>
	<?php endif; ?>

	<?php if ( ! empty( $images ) ) : ?>
		<?php
		// Masonry is built as real columns rather than CSS multicol. Multicol
		// re-balances every frame each time the gallery grows, so "Show More"
		// reshuffled the whole grid; assigning each frame to the shortest column
		// up front means revealing more only ever appends to the bottom.
		$assigned  = array();
		$col_count = 'strip' === $layout ? 1 : $columns;
		$heights   = array_fill( 0, $col_count, 0.0 );
		$index     = -1;

		foreach ( $images as $img ) {
			$url = is_array( $img ) ? ( $img['url'] ?? '' ) : (string) $img;
			if ( ! $url ) {
				continue;
			}
			++$index;

			// Column height is tracked in aspect ratios, which is all that is
			// needed to keep equal-width columns level.
			$ratio = 1.33;
			$att   = function_exists( 'wonderland_attachment_id_from_url' ) ? wonderland_attachment_id_from_url( $url ) : 0;
			if ( $att ) {
				$meta = wp_get_attachment_metadata( $att );
				if ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
					$ratio = (float) $meta['height'] / (float) $meta['width'];
				}
			}

			$shortest = array_search( min( $heights ), $heights, true );
			$heights[ $shortest ] += $ratio;

			$assigned[ $shortest ][] = array(
				'url'   => $url,
				'alt'   => is_array( $img ) ? ( $img['alt'] ?? '' ) : '',
				'index' => $index,
			);
		}

		// The strip is one row of N, the masonry grid is columns of N.
		$img_sizes = 'strip' === $layout
			? '(max-width: 560px) 100vw, (max-width: 900px) 50vw, ' . round( 100 / max( 1, count( $images ) ) ) . 'vw'
			: '(max-width: 900px) 50vw, ' . round( 100 / $columns ) . 'vw';
		?>
		<div class="wl-portfolio__grid" style="<?php echo esc_attr( $grid_style ); ?>"<?php echo $lightbox ? ' data-lightbox="1"' : ''; ?><?php echo $reveal ? ' data-reveal="' . esc_attr( (string) $batch ) . '"' : ''; ?>>
			<?php foreach ( $assigned as $column ) : ?>
				<div class="wl-portfolio__col">
					<?php foreach ( $column as $item ) : ?>
						<?php
						$href   = $lightbox ? $item['url'] : $btn_url;
						$hidden = $reveal && $item['index'] >= $initial;
						?>
						<?php // --i restores the original sequence once the column wrappers step aside on small screens. ?>
						<a class="wl-portfolio__item<?php echo $hidden ? ' is-hidden' : ''; ?>" href="<?php echo esc_url( $href ); ?>" data-i="<?php echo esc_attr( (string) $item['index'] ); ?>" style="--i:<?php echo esc_attr( (string) $item['index'] ); ?>"<?php echo $lightbox ? ' data-lb' : ''; ?><?php echo $hidden ? ' hidden aria-hidden="true" tabindex="-1"' : ''; ?>>
							<?php
							echo wonderland_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								$item['url'],
								array(
									'alt'   => $item['alt'],
									'size'  => 'large',
									'sizes' => $img_sizes,
								)
							);
							?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( $reveal ) : ?>
		<div class="wl-portfolio__more">
			<button type="button" class="wl-portfolio__btn wl-portfolio__reveal" data-reveal-btn
				aria-label="<?php esc_attr_e( 'Show more images', 'wonderland-blocks' ); ?>">
				<span><?php esc_html_e( 'Show More', 'wonderland-blocks' ); ?></span>
				<svg class="wl-portfolio__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 10l4 4 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
			<p class="wl-portfolio__count" data-reveal-count>
				<?php
				printf(
					/* translators: 1: images currently shown, 2: total images. */
					esc_html__( 'Showing %1$s of %2$s', 'wonderland-blocks' ),
					'<span data-shown>' . esc_html( (string) min( $initial, $total ) ) . '</span>',
					esc_html( (string) $total )
				);
				?>
			</p>
		</div>
	<?php endif; ?>

	<?php if ( $btn_text ) : ?>
		<div class="wl-portfolio__more">
			<a class="wl-portfolio__btn" href="<?php echo esc_url( $btn_url ); ?>">
				<span><?php echo esc_html( $btn_text ); ?></span>
				<svg class="wl-portfolio__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M10 8l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</a>
		</div>
	<?php endif; ?>
</section>
