<?php
/**
 * Server-side render for wonderland/follow.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$heading   = $attributes['heading'] ?? '';
$handle    = $attributes['handle'] ?? '';
$btn_text  = $attributes['buttonText'] ?? '';
$btn_url   = $attributes['buttonUrl'] ?? '#';
$shortcode = $attributes['shortcode'] ?? '';
$logos     = isset( $attributes['logos'] ) && is_array( $attributes['logos'] ) ? $attributes['logos'] : array();

$wrapper = get_block_wrapper_attributes( array( 'class' => 'wl-follow' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wl-follow__head">
		<?php if ( $heading ) : ?>
			<h2 class="wl-follow__title"><?php echo wp_kses_post( $heading ); ?></h2>
		<?php endif; ?>
		<?php if ( $handle ) : ?>
			<a class="wl-follow__handle" href="<?php echo esc_url( $btn_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $handle ); ?></a>
		<?php endif; ?>
		<?php if ( $btn_text ) : ?>
			<a class="wl-follow__btn" href="<?php echo esc_url( $btn_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $btn_text ); ?></a>
		<?php endif; ?>
	</div>

	<?php if ( $shortcode ) : ?>
		<div class="wl-follow__feed">
			<?php echo do_shortcode( $shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $logos ) ) : ?>
		<div class="wl-follow__logos">
			<?php
			foreach ( $logos as $logo ) :
				$url = is_array( $logo ) ? ( $logo['url'] ?? '' ) : (string) $logo;
				if ( $url ) :
					?>
					<img class="wl-follow__logo" src="<?php echo esc_url( $url ); ?>" alt="" loading="lazy" decoding="async" />
					<?php
				endif;
			endforeach;
			?>
		</div>
	<?php endif; ?>
</section>
