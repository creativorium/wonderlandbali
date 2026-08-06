<?php
/**
 * Server-side render for wonderland/iw-cta.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$heading = $attributes['heading'] ?? '';
$text    = $attributes['text'] ?? '';
$bg      = ( 'greige' === ( $attributes['background'] ?? '' ) ) ? 'is-greige' : 'is-taupe';
$buttons = isset( $attributes['buttons'] ) && is_array( $attributes['buttons'] ) ? $attributes['buttons'] : array();
$note    = $attributes['note'] ?? '';

$wrapper = get_block_wrapper_attributes( array( 'class' => 'wl-iw-cta ' . $bg ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wl-iw-cta__inner">
		<?php if ( $heading ) : ?>
			<h2 class="wl-iw-cta__title"><?php echo wp_kses_post( $heading ); ?></h2>
		<?php endif; ?>

		<?php if ( $text ) : ?>
			<p class="wl-iw-cta__text"><?php echo wp_kses_post( $text ); ?></p>
		<?php endif; ?>

		<?php if ( $buttons ) : ?>
			<div class="wl-iw-cta__actions">
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
					<a class="wl-iw-cta__btn<?php echo esc_attr( $ghost ); ?>" href="<?php echo esc_url( wonderland_link_url( $b_url ) ); ?>"<?php echo $blank ? ' target="_blank" rel="noopener"' : ''; ?>>
						<?php echo function_exists( 'wonderland_mark_svg' ) ? wonderland_mark_svg( 'wl-iw-cta__mark' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php echo esc_html( $b_text ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $note ) : ?>
			<p class="wl-iw-cta__note"><?php echo wp_kses_post( $note ); ?></p>
		<?php endif; ?>
	</div>
</section>
