<?php
/**
 * Server-side render for wonderland/services.
 *
 * Two equal-weight flex columns; items are assigned to a column via
 * $item['column'] ('left' | 'right'). Cards flex to fill the column height, so
 * a column with 2 items gives 50% each and one with 3 gives ~33% each, and both
 * columns end at the same bottom. A card may use a looping video instead of an image.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$heading = $attributes['heading'] ?? '';
$items   = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();

$left  = array();
$right = array();
foreach ( $items as $item ) {
	if ( ( $item['column'] ?? 'left' ) === 'right' ) {
		$right[] = $item;
	} else {
		$left[] = $item;
	}
}
$columns = array_values( array_filter( array( $left, $right ) ) );

$wrapper = get_block_wrapper_attributes( array( 'class' => 'wl-services' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $heading ) : ?>
		<h2 class="wl-services__title"><?php echo wp_kses_post( $heading ); ?></h2>
	<?php endif; ?>

	<?php if ( ! empty( $columns ) ) : ?>
		<div class="wl-services__grid">
			<?php foreach ( $columns as $col ) : ?>
				<div class="wl-services__col">
					<?php
					foreach ( $col as $item ) :
						$title = $item['title'] ?? '';
						$text  = $item['text'] ?? '';
						$url   = $item['url'] ?? '';
						$btn   = $item['buttonText'] ?? 'Click here';
						$img   = $item['imageUrl'] ?? '';
						$video = $item['video'] ?? '';
						?>
						<article class="wl-services__card">
							<a class="wl-services__media" href="<?php echo esc_url( $url ); ?>">
								<?php if ( $video ) : ?>
									<video autoplay muted loop playsinline preload="metadata"<?php echo $img ? ' poster="' . esc_url( $img ) . '"' : ''; ?>>
										<source src="<?php echo esc_url( $video ); ?>" type="video/mp4" />
									</video>
								<?php elseif ( $img ) : ?>
									<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $title ) ); ?>" loading="lazy" decoding="async" />
								<?php endif; ?>
							</a>
							<div class="wl-services__body">
								<?php if ( $title ) : ?><h3 class="wl-services__name"><?php echo wp_kses_post( $title ); ?></h3><?php endif; ?>
								<?php if ( $text ) : ?><p class="wl-services__text"><?php echo wp_kses_post( $text ); ?></p><?php endif; ?>
								<?php if ( $btn && $url ) : ?><a class="wl-services__cta" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $btn ); ?></a><?php endif; ?>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
