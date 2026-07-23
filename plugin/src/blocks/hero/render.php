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
$side_text  = $attributes['sideText'] ?? '';
$btn_text   = $attributes['buttonText'] ?? '';
$btn_url    = $attributes['buttonUrl'] ?? '#';
$bg_url     = $attributes['backgroundUrl'] ?? '';
$badge_url  = $attributes['badgeUrl'] ?? '';
$overlay    = isset( $attributes['overlayOpacity'] ) ? (float) $attributes['overlayOpacity'] / 100 : 0.35;

$wrapper_args = array( 'class' => 'wl-hero' );
if ( $bg_url ) {
	$wrapper_args['style'] = 'background-image:url(' . esc_url( $bg_url ) . ');';
}
$wrapper = get_block_wrapper_attributes( $wrapper_args );

// Location-pin icon for the CTA.
$pin_svg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>';
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<span class="wl-hero__overlay" style="opacity:<?php echo esc_attr( (string) $overlay ); ?>" aria-hidden="true"></span>

	<?php if ( $side_text ) : ?>
		<p class="wl-hero__side"><?php echo esc_html( $side_text ); ?></p>
	<?php endif; ?>

	<div class="wl-hero__inner">
		<?php if ( $heading ) : ?>
			<h1 class="wl-hero__title"><?php echo wp_kses_post( $heading ); ?></h1>
		<?php endif; ?>

		<?php if ( $subheading ) : ?>
			<p class="wl-hero__subtitle"><?php echo wp_kses_post( $subheading ); ?></p>
		<?php endif; ?>

		<?php if ( $btn_text ) : ?>
			<a class="wl-hero__cta" href="<?php echo esc_url( $btn_url ); ?>">
				<?php echo $pin_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( $btn_text ); ?></span>
			</a>
		<?php endif; ?>
	</div>

	<?php if ( $badge_url ) : ?>
		<img class="wl-hero__badge" src="<?php echo esc_url( $badge_url ); ?>" alt="<?php esc_attr_e( 'Award badge', 'wonderland-blocks' ); ?>" loading="lazy" decoding="async" />
	<?php endif; ?>
</section>
