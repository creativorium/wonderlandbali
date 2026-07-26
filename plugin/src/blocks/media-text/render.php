<?php
/**
 * Server-side render for wonderland/media-text.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$eyebrow  = $attributes['eyebrow'] ?? '';
$heading  = $attributes['heading'] ?? '';
$text     = $attributes['text'] ?? '';
$btn_text = $attributes['buttonText'] ?? '';
$btn_url  = $attributes['buttonUrl'] ?? '';
$img      = $attributes['imageUrl'] ?? '';
$pos      = ( $attributes['imagePosition'] ?? 'left' ) === 'right' ? 'is-right' : 'is-left';
$bg       = ( $attributes['background'] ?? 'white' ) === 'greige' ? 'is-greige' : 'is-white';

$wrapper = get_block_wrapper_attributes( array( 'class' => "wl-mt $pos $bg" ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wl-mt__grid">
		<div class="wl-mt__media">
			<?php if ( $img ) : ?>
				<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $heading ) ); ?>" loading="lazy" decoding="async" />
			<?php endif; ?>
		</div>
		<div class="wl-mt__body">
			<?php if ( $eyebrow ) : ?>
				<p class="wl-mt__eyebrow"><?php echo wp_kses_post( $eyebrow ); ?></p>
			<?php endif; ?>
			<?php if ( $heading ) : ?>
				<h2 class="wl-mt__heading"><?php echo wp_kses_post( $heading ); ?></h2>
			<?php endif; ?>
			<?php if ( $text ) : ?>
				<div class="wl-mt__text"><?php echo wp_kses_post( wpautop( $text ) ); ?></div>
			<?php endif; ?>
			<?php if ( $btn_text && $btn_url ) : ?>
				<a class="wl-mt__cta" href="<?php echo esc_url( $btn_url ); ?>"><?php echo esc_html( $btn_text ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>
