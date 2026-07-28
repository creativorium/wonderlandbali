<?php
/**
 * Server-side render for wonderland/follow.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$heading      = $attributes['heading'] ?? '';
$btn_text     = $attributes['buttonText'] ?? '';
$btn_url      = $attributes['buttonUrl'] ?? '#';
$shortcode    = $attributes['shortcode'] ?? '';
$placeholders = isset( $attributes['placeholders'] ) && is_array( $attributes['placeholders'] ) ? $attributes['placeholders'] : array();

$ig_icon   = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>';
$play_icon = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>';

$wrapper = get_block_wrapper_attributes( array( 'class' => 'wl-follow' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wl-follow__head">
		<?php if ( $heading ) : ?>
			<h2 class="wl-follow__title"><?php echo wp_kses_post( $heading ); ?></h2>
		<?php endif; ?>
		<?php if ( $btn_text ) : ?>
			<a class="wl-follow__btn" href="<?php echo esc_url( $btn_url ); ?>" target="_blank" rel="noopener">
				<?php echo $ig_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( $btn_text ); ?></span>
			</a>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $placeholders ) ) : ?>
		<div class="wl-follow__grid">
			<?php
			foreach ( $placeholders as $ph ) :
				$url = is_array( $ph ) ? ( $ph['url'] ?? '' ) : (string) $ph;
				if ( ! $url ) {
					continue;
				}
				?>
				<a class="wl-follow__item" href="<?php echo esc_url( $btn_url ); ?>" target="_blank" rel="noopener">
					<?php
					// Placeholder tiles are decorative — the feed itself carries the
					// meaning — but they still deserve a right-sized file.
					echo wonderland_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$url,
						array(
							'size'       => 'medium_large',
							'sizes'      => '(max-width: 700px) 50vw, 25vw',
							'decorative' => true,
						)
					);
					?>
					<span class="wl-follow__play" aria-hidden="true"><?php echo $play_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php elseif ( $shortcode ) : ?>
		<div class="wl-follow__feed"><?php echo do_shortcode( $shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	<?php endif; ?>
</section>
