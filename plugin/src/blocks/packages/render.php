<?php
/**
 * Server-side render for wonderland/packages.
 *
 * Each group is a priced tier: a name, a headline price, either a list of
 * inclusions or a set of labelled add-on prices, and a photograph. Copy is set
 * flush right against the image so the prices read as one column.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$heading  = $attributes['heading'] ?? '';
$bg       = ( 'white' === ( $attributes['background'] ?? '' ) ) ? 'is-white' : 'is-greige';
$groups   = isset( $attributes['groups'] ) && is_array( $attributes['groups'] ) ? $attributes['groups'] : array();
$btn_text = $attributes['buttonText'] ?? '';
$btn_url  = $attributes['buttonUrl'] ?? '';
$note     = $attributes['note'] ?? '';
$straddle = ! empty( $attributes['straddle'] ) ? ' is-straddle' : '';

if ( ! $groups ) {
	return;
}

$wrapper = get_block_wrapper_attributes( array( 'class' => 'wl-packages ' . $bg . $straddle ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $heading ) : ?>
		<h2 class="wl-packages__title"><?php echo wp_kses_post( $heading ); ?></h2>
	<?php endif; ?>

	<div class="wl-packages__inner">
		<?php foreach ( $groups as $group ) : ?>
			<?php
			$name  = $group['title'] ?? '';
			$price = $group['price'] ?? '';
			$items = isset( $group['items'] ) && is_array( $group['items'] ) ? $group['items'] : array();
			$rows  = isset( $group['rows'] ) && is_array( $group['rows'] ) ? $group['rows'] : array();
			$image = $group['imageUrl'] ?? '';
			?>
			<div class="wl-packages__group">
				<div class="wl-packages__copy">
					<?php if ( $name ) : ?>
						<h3 class="wl-packages__name">
							<?php echo wp_kses_post( $name ); ?>
							<?php if ( $price ) : ?>
								<span class="wl-packages__price"><?php echo wp_kses_post( $price ); ?></span>
							<?php endif; ?>
						</h3>
					<?php endif; ?>

					<?php if ( $items ) : ?>
						<ul class="wl-packages__list">
							<?php foreach ( $items as $item ) : ?>
								<li><?php echo esc_html( is_array( $item ) ? ( $item['text'] ?? '' ) : (string) $item ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<?php if ( $rows ) : ?>
						<dl class="wl-packages__rows">
							<?php foreach ( $rows as $row ) : ?>
								<dt><?php echo esc_html( $row['label'] ?? '' ); ?></dt>
								<dd><?php echo esc_html( $row['price'] ?? '' ); ?></dd>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>
				</div>

				<?php if ( $image ) : ?>
					<figure class="wl-packages__figure">
						<?php
						echo function_exists( 'wonderland_image' )
							? wonderland_image( $image, array( 'size' => 'large', 'sizes' => '(max-width: 900px) 100vw, 40vw' ) ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							: '<img src="' . esc_url( wonderland_media_url( $image ) ) . '" alt="" loading="lazy" decoding="async" />';
						?>
					</figure>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>

		<?php if ( $btn_text && $btn_url ) : ?>
			<div class="wl-packages__cta">
				<a class="wl-packages__btn" href="<?php echo esc_url( wonderland_link_url( $btn_url ) ); ?>" download>
					<?php echo esc_html( $btn_text ); ?>
				</a>
			</div>
		<?php endif; ?>

		<?php if ( $note ) : ?>
			<div class="wl-packages__note"><?php echo wp_kses_post( wpautop( $note ) ); ?></div>
		<?php endif; ?>
	</div>
</section>
