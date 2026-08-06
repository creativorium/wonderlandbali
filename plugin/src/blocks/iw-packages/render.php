<?php
/**
 * Server-side render for wonderland/iw-packages.
 *
 * Tiers sit side by side as equal-height cards; the featured one carries a
 * badge and a coral edge. No prices are published — each card's button sends
 * the visitor to the request form for a tailored quote.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$eyebrow = $attributes['eyebrow'] ?? '';
$heading = $attributes['heading'] ?? '';
$intro   = $attributes['intro'] ?? '';
$bg      = ( 'white' === ( $attributes['background'] ?? '' ) ) ? 'is-white' : 'is-greige';
$tiers   = isset( $attributes['tiers'] ) && is_array( $attributes['tiers'] ) ? $attributes['tiers'] : array();
$note    = $attributes['note'] ?? '';

if ( ! $tiers ) {
	return;
}

$wrapper = get_block_wrapper_attributes( array( 'class' => 'wl-iw-packages ' . $bg ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wl-iw-packages__inner">
		<?php if ( $eyebrow ) : ?>
			<p class="wl-iw-packages__eyebrow"><?php echo wp_kses_post( $eyebrow ); ?></p>
		<?php endif; ?>

		<?php if ( $heading ) : ?>
			<h2 class="wl-iw-packages__title"><?php echo wp_kses_post( $heading ); ?></h2>
		<?php endif; ?>

		<?php if ( $intro ) : ?>
			<p class="wl-iw-packages__intro"><?php echo wp_kses_post( $intro ); ?></p>
		<?php endif; ?>

		<div class="wl-iw-packages__tiers">
			<?php foreach ( $tiers as $tier ) : ?>
				<?php
				$name    = trim( (string) ( $tier['name'] ?? '' ) );
				$blurb   = trim( (string) ( $tier['blurb'] ?? '' ) );
				$badge   = trim( (string) ( $tier['badge'] ?? '' ) );
				$items   = isset( $tier['items'] ) && is_array( $tier['items'] ) ? $tier['items'] : array();
				$bt      = trim( (string) ( $tier['buttonText'] ?? '' ) );
				$bu      = trim( (string) ( $tier['buttonUrl'] ?? '' ) );
				$feature = $badge ? ' is-featured' : '';
				if ( '' === $name ) {
					continue;
				}
				?>
				<article class="wl-iw-packages__tier<?php echo esc_attr( $feature ); ?>">
					<?php if ( $badge ) : ?>
						<p class="wl-iw-packages__badge"><?php echo esc_html( $badge ); ?></p>
					<?php endif; ?>

					<h3 class="wl-iw-packages__name"><?php echo wp_kses_post( $name ); ?></h3>

					<?php if ( $blurb ) : ?>
						<p class="wl-iw-packages__blurb"><?php echo wp_kses_post( $blurb ); ?></p>
					<?php endif; ?>

					<?php if ( $items ) : ?>
						<ul class="wl-iw-packages__list">
							<?php foreach ( $items as $item ) : ?>
								<?php $line = is_array( $item ) ? ( $item['text'] ?? '' ) : (string) $item; ?>
								<?php if ( '' === trim( (string) $line ) ) : ?>
									<?php continue; ?>
								<?php endif; ?>
								<li><?php echo esc_html( $line ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<?php if ( $bt && $bu ) : ?>
						<a class="wl-iw-packages__btn" href="<?php echo esc_url( wonderland_link_url( $bu ) ); ?>">
							<?php echo esc_html( $bt ); ?>
							<span class="screen-reader-text"><?php echo esc_html( ' — ' . $name ); ?></span>
						</a>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>

		<?php if ( $note ) : ?>
			<p class="wl-iw-packages__note"><?php echo wp_kses_post( $note ); ?></p>
		<?php endif; ?>
	</div>
</section>
