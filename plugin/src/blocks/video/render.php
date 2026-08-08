<?php
/**
 * Server-side render for wonderland/video.
 *
 * A poster frame with a play button over it. The <video> carries preload="none"
 * and its source, so the browser fetches the poster with the page and not one
 * byte of the film until the visitor asks for it — this file is far larger than
 * everything else on the page put together.
 *
 * The overlay button is hidden until the front-end script marks the block ready.
 * Without JavaScript the native controls are what you get, which still plays.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$eyebrow = $attributes['eyebrow'] ?? '';
$heading = $attributes['heading'] ?? '';
$text    = $attributes['text'] ?? '';
$video   = trim( (string) ( $attributes['videoUrl'] ?? '' ) );
$poster  = trim( (string) ( $attributes['posterUrl'] ?? '' ) );
$caption = $attributes['caption'] ?? '';
$bg      = $attributes['background'] ?? 'ink';
$bg      = in_array( $bg, array( 'white', 'greige' ), true ) ? 'is-' . $bg : 'is-ink';

if ( '' === $video ) {
	return;
}

$src        = wonderland_media_url( $video );
$poster_url = $poster ? wonderland_media_url( $poster ) : '';
$label      = $heading ? wp_strip_all_tags( $heading ) : __( 'the film', 'wonderland-blocks' );

$wrapper = get_block_wrapper_attributes( array( 'class' => "wl-video $bg" ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wl-video__inner">
		<?php if ( $eyebrow ) : ?>
			<p class="wl-video__eyebrow"><?php echo wp_kses_post( $eyebrow ); ?></p>
		<?php endif; ?>

		<?php if ( $heading ) : ?>
			<h2 class="wl-video__heading"><?php echo wp_kses_post( $heading ); ?></h2>
		<?php endif; ?>

		<?php if ( $text ) : ?>
			<p class="wl-video__text"><?php echo wp_kses_post( $text ); ?></p>
		<?php endif; ?>

		<figure class="wl-video__figure">
			<div class="wl-video__frame" data-video>
				<video class="wl-video__el"
					<?php echo $poster_url ? 'poster="' . esc_url( $poster_url ) . '"' : ''; ?>
					preload="none" controls playsinline data-video-el>
					<source src="<?php echo esc_url( $src ); ?>" type="video/mp4" />
				</video>

				<?php // Revealed by the script; without JS the native controls stand in. ?>
				<button class="wl-video__play" type="button" data-video-play
					aria-label="<?php echo esc_attr( sprintf( /* translators: %s: film title. */ __( 'Play %s', 'wonderland-blocks' ), $label ) ); ?>">
					<span class="wl-video__play-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13l11-6.5z"/></svg>
					</span>
					<span class="wl-video__play-text"><?php esc_html_e( 'Watch the film', 'wonderland-blocks' ); ?></span>
				</button>
			</div>

			<?php if ( $caption ) : ?>
				<figcaption class="wl-video__caption"><?php echo wp_kses_post( $caption ); ?></figcaption>
			<?php endif; ?>
		</figure>
	</div>
</section>
