<?php
/**
 * Server-side render for wonderland/iw-hero.
 *
 * The Indian Weddings opener: eyebrow, page <h1>, lead line and two actions
 * centred over a full-bleed photograph, with a strip of credentials fixed to
 * the bottom of the section.
 *
 * Given more than one slide the banner crossfades between them, driven by the
 * shared `[data-slideshow]` script in frontend.js — the same one the home page
 * hero uses.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$eyebrow  = $attributes['eyebrow'] ?? '';
$title    = $attributes['title'] ?? '';
$subtitle = $attributes['subtitle'] ?? '';
$bg_url   = $attributes['backgroundUrl'] ?? '';
$slides   = isset( $attributes['slides'] ) && is_array( $attributes['slides'] ) ? $attributes['slides'] : array();
$duration = isset( $attributes['slideDuration'] ) ? max( 1000, (int) $attributes['slideDuration'] ) : 5000;
$overlay  = isset( $attributes['overlayOpacity'] ) ? (float) $attributes['overlayOpacity'] / 100 : 0.5;
$buttons  = isset( $attributes['buttons'] ) && is_array( $attributes['buttons'] ) ? $attributes['buttons'] : array();
$note     = $attributes['note'] ?? '';
$stats    = isset( $attributes['stats'] ) && is_array( $attributes['stats'] ) ? $attributes['stats'] : array();

// One background image is just a slideshow of one; the older single-image
// attribute keeps working as the fallback.
if ( ! $slides && $bg_url ) {
	$slides = array( array( 'url' => $bg_url ) );
}

// Drop the empties before counting, or a stray blank line in the editor would
// turn a single image into a "slideshow" that never advances.
$slides = array_values(
	array_filter(
		array_map(
			static function ( $slide ) {
				return is_array( $slide ) ? trim( (string) ( $slide['url'] ?? '' ) ) : trim( (string) $slide );
			},
			$slides
		)
	)
);

$wrapper_args = array( 'class' => 'wl-iw-hero' . ( $slides ? ' has-bg' : '' ) );
if ( count( $slides ) > 1 ) {
	$wrapper_args['data-slideshow']      = '1';
	$wrapper_args['data-slide-duration'] = (string) $duration;
}
$wrapper = get_block_wrapper_attributes( $wrapper_args );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $slides ) : ?>
		<div class="wl-iw-hero__media" aria-hidden="true">
			<?php foreach ( $slides as $i => $url ) : ?>
				<div class="wl-iw-hero__slide js-slide<?php echo 0 === $i ? ' is-active' : ''; ?>">
					<?php if ( 0 === $i ) : ?>
						<?php
						echo wonderland_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							$url,
							array(
								'size'       => 'full',
								'sizes'      => '100vw',
								'priority'   => true, // opener image: the likely LCP element
								'decorative' => true, // the heading carries the meaning
							)
						);
						?>
					<?php else : ?>
						<?php
						// Every slide sits inside the viewport, so `loading="lazy"` would
						// hold none of them back — the browser would fetch the lot up
						// front. The URLs are handed over instead and the script gives a
						// slide its image just before its turn.
						$att_id = function_exists( 'wonderland_attachment_id_from_url' ) ? wonderland_attachment_id_from_url( $url ) : 0;
						$srcset = $att_id ? wp_get_attachment_image_srcset( $att_id, 'full' ) : '';
						?>
						<img class="js-slide-img" alt="" decoding="async"
							data-src="<?php echo esc_url( wonderland_media_url( $url ) ); ?>"
							<?php echo $srcset ? 'data-srcset="' . esc_attr( $srcset ) . '" data-sizes="100vw"' : ''; ?> />
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
			<span class="wl-iw-hero__overlay" style="opacity:<?php echo esc_attr( (string) $overlay ); ?>"></span>
		</div>
	<?php endif; ?>

	<div class="wl-iw-hero__inner">
		<?php if ( $eyebrow ) : ?>
			<p class="wl-iw-hero__eyebrow"><?php echo wp_kses_post( $eyebrow ); ?></p>
		<?php endif; ?>

		<?php if ( $title ) : ?>
			<h1 class="wl-iw-hero__title"><?php echo wp_kses_post( $title ); ?></h1>
		<?php endif; ?>

		<?php if ( $subtitle ) : ?>
			<p class="wl-iw-hero__lead"><?php echo wp_kses_post( $subtitle ); ?></p>
		<?php endif; ?>

		<?php if ( $buttons ) : ?>
			<div class="wl-iw-hero__actions">
				<?php foreach ( $buttons as $btn ) : ?>
					<?php
					$b_text = is_array( $btn ) ? trim( (string) ( $btn['text'] ?? '' ) ) : '';
					$b_url  = is_array( $btn ) ? ( $btn['url'] ?? '' ) : '';
					if ( '' === $b_text || '' === $b_url ) {
						continue;
					}
					$ghost = ( 'ghost' === ( $btn['style'] ?? '' ) ) ? ' is-ghost' : '';
					$blank = ! empty( $btn['newTab'] );
					?>
					<a class="wl-iw-hero__btn<?php echo esc_attr( $ghost ); ?>" href="<?php echo esc_url( wonderland_link_url( $b_url ) ); ?>"<?php echo $blank ? ' target="_blank" rel="noopener"' : ''; ?>>
						<?php echo esc_html( $b_text ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $note ) : ?>
			<p class="wl-iw-hero__note"><?php echo wp_kses_post( $note ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( $stats ) : ?>
		<ul class="wl-iw-hero__stats">
			<?php foreach ( $stats as $stat ) : ?>
				<?php $label = is_array( $stat ) ? ( $stat['text'] ?? '' ) : (string) $stat; ?>
				<?php if ( '' === trim( (string) $label ) ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<li><?php echo esc_html( $label ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>
