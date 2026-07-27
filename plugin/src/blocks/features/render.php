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
$bg      = ( $attributes['background'] ?? 'white' ) === 'greige' ? 'is-greige' : 'is-white';
$variant = ( 'list' === ( $attributes['variant'] ?? '' ) ) ? 'list' : 'cards';
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
		<?php if ( 'list' === $variant ) : ?>
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
								<img src="<?php echo esc_url( $media ); ?>" alt="" loading="lazy" decoding="async" />
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
