<?php
/**
 * Server-side render for wonderland/intro (About / Our Story).
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner block content (unused).
 * @var WP_Block $block      Block instance.
 *
 * @package WonderlandBlocks
 */

$label     = $attributes['label'] ?? '';
$eyebrow   = $attributes['eyebrow'] ?? '';
$text      = $attributes['text'] ?? '';
$btn_text  = $attributes['buttonText'] ?? '';
$btn_url   = $attributes['buttonUrl'] ?? '#';
$img_main  = $attributes['imageMainUrl'] ?? '';
$img_sub   = $attributes['imageSubUrl'] ?? '';

$wrapper = get_block_wrapper_attributes( array( 'class' => 'wl-intro' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wl-intro__grid">
		<div class="wl-intro__media">
			<?php if ( $img_main ) : ?>
				<img class="wl-intro__img wl-intro__img--main" src="<?php echo esc_url( $img_main ); ?>" alt="" loading="lazy" decoding="async" />
			<?php endif; ?>
			<?php if ( $img_sub ) : ?>
				<img class="wl-intro__img wl-intro__img--sub" src="<?php echo esc_url( $img_sub ); ?>" alt="" loading="lazy" decoding="async" />
			<?php endif; ?>
		</div>

		<div class="wl-intro__body">
			<?php if ( $label ) : ?>
				<h2 class="wl-intro__title"><?php echo wp_kses_post( $label ); ?></h2>
			<?php endif; ?>
			<?php if ( $eyebrow ) : ?>
				<p class="wl-intro__eyebrow"><?php echo wp_kses_post( $eyebrow ); ?></p>
			<?php endif; ?>
			<?php if ( $text ) : ?>
				<div class="wl-intro__text"><?php echo wp_kses_post( wpautop( $text ) ); ?></div>
			<?php endif; ?>
			<?php if ( $btn_text ) : ?>
				<a class="wl-intro__cta" href="<?php echo esc_url( $btn_url ); ?>"><?php echo esc_html( $btn_text ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>
