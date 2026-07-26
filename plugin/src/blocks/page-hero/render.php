<?php
/**
 * Server-side render for wonderland/page-hero.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$eyebrow  = $attributes['eyebrow'] ?? '';
$title    = $attributes['title'] ?? '';
$subtitle = $attributes['subtitle'] ?? '';
$bg_url   = $attributes['backgroundUrl'] ?? '';
$overlay  = isset( $attributes['overlayOpacity'] ) ? (float) $attributes['overlayOpacity'] / 100 : 0.4;
$height   = ( $attributes['height'] ?? 'short' ) === 'tall' ? 'wl-page-hero--tall' : 'wl-page-hero--short';

$classes = 'wl-page-hero ' . $height . ( $bg_url ? ' has-bg' : '' );
$args    = array( 'class' => $classes );
if ( $bg_url ) {
	$args['style'] = 'background-image:url(' . esc_url( $bg_url ) . ');';
}
$wrapper = get_block_wrapper_attributes( $args );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $bg_url ) : ?>
		<span class="wl-page-hero__overlay" style="opacity:<?php echo esc_attr( (string) $overlay ); ?>" aria-hidden="true"></span>
	<?php endif; ?>
	<div class="wl-page-hero__inner">
		<?php if ( $eyebrow ) : ?>
			<p class="wl-page-hero__eyebrow"><?php echo wp_kses_post( $eyebrow ); ?></p>
		<?php endif; ?>
		<?php if ( $title ) : ?>
			<h1 class="wl-page-hero__title"><?php echo wp_kses_post( $title ); ?></h1>
		<?php endif; ?>
		<?php if ( $subtitle ) : ?>
			<p class="wl-page-hero__subtitle"><?php echo wp_kses_post( $subtitle ); ?></p>
		<?php endif; ?>
	</div>
</section>
