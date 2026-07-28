<?php
/**
 * Server-side render for wonderland/iw-gallery.
 *
 * A uniform grid of photographs; each caption doubles as the image's alt text,
 * so the tiles stay meaningful without a separate description.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$eyebrow  = $attributes['eyebrow'] ?? '';
$heading  = $attributes['heading'] ?? '';
$bg       = ( 'greige' === ( $attributes['background'] ?? '' ) ) ? 'is-greige' : 'is-white';
$cols     = isset( $attributes['columns'] ) ? max( 1, min( 4, (int) $attributes['columns'] ) ) : 3;
$items    = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();
$btn_text = $attributes['buttonText'] ?? '';
$btn_url  = $attributes['buttonUrl'] ?? '';

$wrapper = get_block_wrapper_attributes( array( 'class' => 'wl-iw-gallery ' . $bg ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wl-iw-gallery__inner">
		<?php if ( $eyebrow ) : ?>
			<p class="wl-iw-gallery__eyebrow"><?php echo wp_kses_post( $eyebrow ); ?></p>
		<?php endif; ?>

		<?php if ( $heading ) : ?>
			<h2 class="wl-iw-gallery__title"><?php echo wp_kses_post( $heading ); ?></h2>
		<?php endif; ?>

		<?php if ( $items ) : ?>
			<div class="wl-iw-gallery__grid" style="--wl-cols:<?php echo esc_attr( (string) $cols ); ?>">
				<?php foreach ( $items as $item ) : ?>
					<?php
					$url = is_array( $item ) ? ( $item['url'] ?? '' ) : (string) $item;
					if ( ! $url ) {
						continue;
					}
					$caption = is_array( $item ) ? trim( (string) ( $item['caption'] ?? '' ) ) : '';
					$alt     = is_array( $item ) ? trim( (string) ( $item['alt'] ?? '' ) ) : '';
					?>
					<figure class="wl-iw-gallery__tile">
						<?php
						echo wonderland_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							$url,
							array(
								'alt'   => $alt ? $alt : $caption,
								'size'  => 'large',
								'sizes' => '(max-width: 700px) 100vw, (max-width: 1000px) 50vw, ' . round( 100 / $cols ) . 'vw',
							)
						);
						?>
						<?php if ( $caption ) : ?>
							<figcaption class="wl-iw-gallery__cap"><?php echo esc_html( $caption ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $btn_text && $btn_url ) : ?>
			<div class="wl-iw-gallery__more">
				<a class="wl-iw-gallery__btn" href="<?php echo esc_url( $btn_url ); ?>"><?php echo esc_html( $btn_text ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>
