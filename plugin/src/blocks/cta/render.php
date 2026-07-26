<?php
/**
 * Server-side render for wonderland/cta.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$text     = $attributes['text'] ?? '';
$variant  = $attributes['variant'] ?? 'plain';
$btn_text = $attributes['buttonText'] ?? '';
$btn_url  = $attributes['buttonUrl'] ?? '#';
$new_tab  = ! empty( $attributes['buttonNewTab'] );
$bg_url   = $attributes['backgroundUrl'] ?? '';
$overlay  = isset( $attributes['overlayOpacity'] ) ? (float) $attributes['overlayOpacity'] / 100 : 0.45;

$classes = 'wl-cta wl-cta--' . preg_replace( '/[^a-z0-9_-]/', '', (string) $variant );
if ( $bg_url ) {
	$classes .= ' has-bg';
}
$args = array( 'class' => $classes );
if ( $bg_url ) {
	$args['style'] = 'background-image:url(' . esc_url( $bg_url ) . ');';
}
$wrapper = get_block_wrapper_attributes( $args );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $bg_url ) : ?>
		<span class="wl-cta__overlay" style="opacity:<?php echo esc_attr( (string) $overlay ); ?>" aria-hidden="true"></span>
	<?php endif; ?>
	<div class="wl-cta__inner">
		<?php if ( $text ) : ?>
			<div class="wl-cta__text"><?php echo wp_kses_post( wpautop( $text ) ); ?></div>
		<?php endif; ?>
		<?php if ( $btn_text ) : ?>
			<a class="wl-cta__btn" href="<?php echo esc_url( $btn_url ); ?>"<?php echo $new_tab ? ' target="_blank" rel="noopener"' : ''; ?>>
				<?php echo esc_html( $btn_text ); ?>
			</a>
		<?php endif; ?>
	</div>
</section>
