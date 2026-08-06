<?php
/**
 * Server-side render for wonderland/contact.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$heading = $attributes['heading'] ?? '';
$text    = $attributes['text'] ?? '';
$note    = $attributes['note'] ?? '';
$email1  = $attributes['email1'] ?? '';
$email2  = $attributes['email2'] ?? '';
$phone   = $attributes['phone'] ?? '';
$wa      = $attributes['whatsapp'] ?? '';
$ig      = $attributes['instagram'] ?? '';
$fb      = $attributes['facebook'] ?? '';
$preset  = ( 'request' === ( $attributes['formPreset'] ?? '' ) ) ? 'request' : 'contact';

$intro_image = $attributes['introImageUrl'] ?? '';

// The intro copy and the image can each sit in the left intro column or directly
// above the form, so Contact and Request compose differently from one block.
$image_in_form = 'form' === ( $attributes['imagePlacement'] ?? 'intro' );
$text_in_form  = 'form' === ( $attributes['textPlacement'] ?? 'intro' );

/**
 * Emit the intro copy.
 *
 * @param string $text     Raw text.
 * @param string $modifier Extra BEM modifier suffix.
 */
$render_text = function ( $text, $modifier = '' ) {
	if ( ! $text ) {
		return;
	}
	printf(
		'<div class="wl-contact__text%s">%s</div>',
		$modifier ? ' wl-contact__text--' . esc_attr( $modifier ) : '',
		wp_kses_post( wpautop( $text ) )
	);
};

/**
 * Emit the supporting image.
 *
 * @param string $url      Image URL.
 * @param string $modifier Extra BEM modifier suffix.
 */
$render_image = function ( $url, $modifier = '' ) {
	if ( ! $url ) {
		return;
	}
	printf(
		'<figure class="wl-contact__image%s">%s</figure>',
		$modifier ? ' wl-contact__image--' . esc_attr( $modifier ) : '',
		wonderland_image( $url, array( 'size' => 'medium_large', 'sizes' => '(max-width: 860px) 100vw, 40vw' ) )
	);
};

// Icons are decorative; degrade to plain text links if the helper is unavailable.
$icon = function ( $name ) {
	return function_exists( 'wonderland_icon_svg' ) ? wonderland_icon_svg( $name ) : '';
};

$wrapper = get_block_wrapper_attributes( array( 'class' => 'wl-contact wl-contact--' . $preset ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wl-contact__inner">
		<div class="wl-contact__intro">
			<?php if ( $heading ) : ?>
				<h2 class="wl-contact__title"><?php echo wp_kses_post( $heading ); ?></h2>
			<?php endif; ?>
			<?php
			if ( ! $text_in_form ) {
				$render_text( $text );
			}
			if ( ! $image_in_form ) {
				$render_image( $intro_image );
			}
			?>

			<?php if ( $email1 || $email2 || $phone ) : ?>
				<ul class="wl-contact__details">
					<?php if ( $email1 ) : ?><li><a href="mailto:<?php echo esc_attr( $email1 ); ?>"><?php echo $icon( 'email' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $email1 ); ?></span></a></li><?php endif; ?>
					<?php if ( $email2 ) : ?><li><a href="mailto:<?php echo esc_attr( $email2 ); ?>"><?php echo $icon( 'email' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $email2 ); ?></span></a></li><?php endif; ?>
					<?php if ( $phone ) : ?><li><a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ); ?>"><?php echo $icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $phone ); ?></span></a></li><?php endif; ?>
				</ul>
			<?php endif; ?>

			<?php if ( $ig || $fb || $wa ) : ?>
				<div class="wl-contact__social">
					<?php if ( $ig ) : ?><a href="<?php echo esc_url( wonderland_link_url( $ig ) ); ?>" target="_blank" rel="noopener"><?php echo $icon( 'instagram' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>Instagram</span></a><?php endif; ?>
					<?php if ( $fb ) : ?><a href="<?php echo esc_url( wonderland_link_url( $fb ) ); ?>" target="_blank" rel="noopener"><?php echo $icon( 'facebook' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>Facebook</span></a><?php endif; ?>
					<?php if ( $wa ) : ?><a href="https://wa.me/<?php echo esc_attr( preg_replace( '/\D/', '', $wa ) ); ?>" target="_blank" rel="noopener"><?php echo $icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>WhatsApp</span></a><?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $note ) : ?>
				<p class="wl-contact__note"><?php echo wp_kses_post( $note ); ?></p>
			<?php endif; ?>
		</div>

		<div class="wl-contact__form">
			<?php
			// Sits immediately above the form, inside the same column.
			if ( $image_in_form ) {
				$render_image( $intro_image, 'above-form' );
			}
			if ( $text_in_form ) {
				$render_text( $text, 'above-form' );
			}

			if ( function_exists( 'wonderland_render_form' ) ) {
				echo wonderland_render_form( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'preset'  => $preset,
					'subject' => $attributes['formSubject'] ?? 'Website enquiry',
					'button'  => $attributes['formButton'] ?? 'Send Message',
				) );
			}
			?>
		</div>
	</div>
</section>
