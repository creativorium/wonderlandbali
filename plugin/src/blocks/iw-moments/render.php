<?php
/**
 * Server-side render for wonderland/iw-moments.
 *
 * The gallery on /indian-weddings/: the wedding film first, then photographs in
 * justified rows — each frame keeps its own proportions and the row's widths are
 * shared out in proportion to them, so nothing is cropped to a common tile.
 *
 * Everything past the opening batch ships in the markup but carries `hidden`,
 * so those images are not fetched until "Load More" reveals them (initReveal in
 * frontend.js). Without JS the button is still a link-free control, so the
 * markup below renders every frame for crawlers either way.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$eyebrow  = $attributes['eyebrow'] ?? '';
$heading  = $attributes['heading'] ?? '';
$intro    = $attributes['intro'] ?? '';
$bg       = ( 'greige' === ( $attributes['background'] ?? '' ) ) ? 'is-greige' : 'is-white';
// Stored root-relative, like every other media URL in the content — rebased so
// a subdirectory install (dev.cularcreative.com/wonderland/) resolves them.
$video    = wonderland_media_url( trim( (string) ( $attributes['videoUrl'] ?? '' ) ) );
$poster   = wonderland_media_url( trim( (string) ( $attributes['videoPoster'] ?? '' ) ) );
$vcaption = trim( (string) ( $attributes['videoCaption'] ?? '' ) );
$items    = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();
$initial  = max( 1, (int) ( $attributes['initial'] ?? 12 ) );
$batch    = max( 1, (int) ( $attributes['batch'] ?? 6 ) );
$btn_text = $attributes['buttonText'] ?? '';

// The film is its own band above the grid, so the batch counts photographs only.
$shown_at_start = $initial;
$total          = count( $items );

$wrapper = get_block_wrapper_attributes( array( 'class' => 'wl-iw-moments ' . $bg ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wl-iw-moments__inner">
		<?php if ( $eyebrow ) : ?>
			<p class="wl-iw-moments__eyebrow"><?php echo wp_kses_post( $eyebrow ); ?></p>
		<?php endif; ?>

		<?php if ( $heading ) : ?>
			<h2 class="wl-iw-moments__title"><?php echo wp_kses_post( $heading ); ?></h2>
		<?php endif; ?>

		<?php if ( $intro ) : ?>
			<p class="wl-iw-moments__intro"><?php echo wp_kses_post( $intro ); ?></p>
		<?php endif; ?>

		<?php if ( $video ) : ?>
			<?php
			// The film gets the full width of the column — it is the section's
			// opening statement, not one frame among many. Same play-button
			// contract as the video block, so initFilmPlayers drives it: the
			// markup ships `controls` for the no-JS case and the script swaps in
			// our own button.
			?>
			<figure class="wl-iw-moments__film">
				<div class="wl-iw-moments__film-frame" data-video>
					<video class="wl-iw-moments__video"
						<?php echo $poster ? 'poster="' . esc_url( $poster ) . '"' : ''; ?>
						preload="none" controls playsinline data-video-el>
						<source src="<?php echo esc_url( $video ); ?>" type="video/mp4" />
					</video>

					<button class="wl-iw-moments__play" type="button" data-video-play
						aria-label="<?php esc_attr_e( 'Play the wedding film', 'wonderland-blocks' ); ?>">
						<span class="wl-iw-moments__play-icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13l11-6.5z"/></svg>
						</span>
						<span class="wl-iw-moments__play-text"><?php esc_html_e( 'Watch the film', 'wonderland-blocks' ); ?></span>
					</button>

					<?php if ( $vcaption ) : ?>
						<span class="wl-iw-moments__cap"><?php echo esc_html( $vcaption ); ?></span>
					<?php endif; ?>
				</div>
			</figure>
		<?php endif; ?>

		<?php if ( $items ) : ?>
			<div class="wl-iw-moments__wrap">
				<div class="wl-iw-moments__grid"
					data-lightbox
					data-reveal="<?php echo esc_attr( (string) $batch ); ?>"
					data-reveal-item=".wl-iw-moments__frame">
					<?php
					$index = 0;

					foreach ( $items as $item ) :
						$url = is_array( $item ) ? trim( (string) ( $item['url'] ?? '' ) ) : (string) $item;
						if ( ! $url ) {
							continue;
						}
						$alt    = is_array( $item ) ? trim( (string) ( $item['alt'] ?? '' ) ) : '';
						$w      = is_array( $item ) ? (int) ( $item['w'] ?? 0 ) : 0;
						$h      = is_array( $item ) ? (int) ( $item['h'] ?? 0 ) : 0;
						$ar     = ( $w > 0 && $h > 0 ) ? round( $w / $h, 4 ) : 1.5;
						$hidden = $index >= $shown_at_start;
						?>
						<figure class="wl-iw-moments__frame<?php echo $hidden ? ' is-hidden' : ''; ?>"
							style="--wl-ar:<?php echo esc_attr( (string) $ar ); ?>"
							data-i="<?php echo esc_attr( (string) $index ); ?>"
							<?php echo $hidden ? ' hidden' : ''; ?>>
							<?php // The lightbox opens the full file, so its href needs rebasing too. ?>
						<a class="wl-iw-moments__link" href="<?php echo esc_url( wonderland_media_url( $url ) ); ?>" data-lb data-i="<?php echo esc_attr( (string) $index ); ?>">
								<?php
								echo wonderland_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									$url,
									array(
										'alt'   => $alt,
										'size'  => 'large',
										'sizes' => '(max-width: 700px) 100vw, (max-width: 1100px) 50vw, 33vw',
									)
								);
								?>
							</a>
						</figure>
						<?php
						++$index;
					endforeach;
					?>
				</div>

				<?php if ( $btn_text && $total > $shown_at_start ) : ?>
					<div class="wl-iw-moments__more">
						<button type="button" class="wl-iw-moments__btn" data-reveal-btn>
							<?php echo esc_html( $btn_text ); ?>
						</button>
						<p class="wl-iw-moments__count">
							<span data-shown><?php echo esc_html( (string) min( $initial, $total ) ); ?></span>
							<?php echo esc_html( sprintf( ' / %d', $total ) ); ?>
						</p>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
