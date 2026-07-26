<?php
/**
 * Server-side render for wonderland/intro (About / Our Story).
 *
 * Async desktop layout: left column = main image + story; right column = big
 * title with the secondary image overlapping under it. On mobile the columns
 * unwrap (display:contents) into a simple stacked layout.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$label    = $attributes['label'] ?? '';
$eyebrow  = $attributes['eyebrow'] ?? '';
$text     = $attributes['text'] ?? '';
$btn_text = $attributes['buttonText'] ?? '';
$btn_url  = $attributes['buttonUrl'] ?? '#';
$img_main = $attributes['imageMainUrl'] ?? '';
$img_sub  = $attributes['imageSubUrl'] ?? '';

$wrapper = get_block_wrapper_attributes( array( 'class' => 'wl-intro' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wl-intro__grid">
		<div class="wl-intro__col wl-intro__col--left">
			<figure class="wl-intro__media wl-intro__media--main">
				<?php if ( $img_main ) : ?>
					<img src="<?php echo esc_url( $img_main ); ?>" alt="" loading="lazy" decoding="async" />
				<?php endif; ?>
			</figure>
			<div class="wl-intro__story">
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

		<div class="wl-intro__col wl-intro__col--right">
			<?php if ( $label ) : ?>
				<h2 class="wl-intro__title"><?php echo wp_kses_post( $label ); ?></h2>
			<?php endif; ?>
			<figure class="wl-intro__media wl-intro__media--sub">
				<?php if ( $img_sub ) : ?>
					<img src="<?php echo esc_url( $img_sub ); ?>" alt="" loading="lazy" decoding="async" />
				<?php endif; ?>
			</figure>
		</div>
	</div>
</section>
