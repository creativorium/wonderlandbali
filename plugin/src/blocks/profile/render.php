<?php
/**
 * Server-side render for wonderland/profile.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$name = $attributes['name'] ?? '';
$text = $attributes['text'] ?? '';
$img  = $attributes['imageUrl'] ?? '';
$pos  = ( $attributes['imagePosition'] ?? 'left' ) === 'right' ? 'is-right' : 'is-left';

$wrapper = get_block_wrapper_attributes( array( 'class' => "wl-profile $pos" ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $name ) : ?>
		<h2 class="wl-profile__name"><?php echo wp_kses_post( $name ); ?></h2>
	<?php endif; ?>
	<div class="wl-profile__grid">
		<figure class="wl-profile__media">
			<?php if ( $img ) : ?>
				<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $name ) ); ?>" loading="lazy" decoding="async" />
			<?php endif; ?>
		</figure>
		<div class="wl-profile__text"><?php echo wp_kses_post( wpautop( $text ) ); ?></div>
	</div>
</section>
