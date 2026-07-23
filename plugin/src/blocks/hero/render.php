<?php
/**
 * Server-side render for wonderland/hero.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner block content (unused).
 * @var WP_Block $block      Block instance.
 *
 * @package WonderlandBlocks
 */

$heading    = $attributes['heading'] ?? '';
$subheading = $attributes['subheading'] ?? '';
$btn_text   = $attributes['buttonText'] ?? '';
$btn_url    = $attributes['buttonUrl'] ?? '#';
$bg_url     = $attributes['backgroundUrl'] ?? '';
$overlay    = isset( $attributes['overlayOpacity'] ) ? (float) $attributes['overlayOpacity'] / 100 : 0.4;

$wrapper_args = array( 'class' => 'wl-hero' );
if ( $bg_url ) {
	$wrapper_args['style'] = 'background-image:url(' . esc_url( $bg_url ) . ');';
}
$wrapper = get_block_wrapper_attributes( $wrapper_args );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<span class="wl-hero__overlay" style="opacity:<?php echo esc_attr( (string) $overlay ); ?>" aria-hidden="true"></span>
	<div class="wl-hero__inner">
		<?php if ( $heading ) : ?>
			<h1 class="wl-hero__title"><?php echo wp_kses_post( $heading ); ?></h1>
		<?php endif; ?>

		<?php if ( $subheading ) : ?>
			<p class="wl-hero__subtitle"><?php echo wp_kses_post( $subheading ); ?></p>
		<?php endif; ?>

		<?php if ( $btn_text ) : ?>
			<a class="wl-hero__cta" href="<?php echo esc_url( $btn_url ); ?>">
				<?php echo esc_html( $btn_text ); ?>
			</a>
		<?php endif; ?>
	</div>
</section>
