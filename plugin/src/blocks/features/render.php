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
			<?php // Pull-quotes: the words lead, the attribution follows. ?>
			<div class="wl-features__quotes" style="--wl-cols:<?php echo esc_attr( (string) $cols ); ?>">
				<?php foreach ( $items as $item ) : ?>
					<?php
					$who   = trim( (string) ( $item['title'] ?? '' ) );
					$quote = trim( (string) ( $item['text'] ?? '' ) );
					if ( '' === $quote ) {
						continue;
					}
					?>
					<figure class="wl-features__quote">
						<blockquote><?php echo wp_kses_post( $quote ); ?></blockquote>
						<?php if ( $who ) : ?>
							<figcaption><?php echo wp_kses_post( $who ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endforeach; ?>
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
					<a class="wl-features__link" href="<?php echo esc_url( $url ); ?>">
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
