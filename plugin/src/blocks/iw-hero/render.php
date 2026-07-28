<?php
/**
 * Server-side render for wonderland/iw-hero.
 *
 * The Indian Weddings opener: eyebrow, page <h1>, lead line and two actions
 * centred over a full-bleed photograph, with a strip of credentials fixed to
 * the bottom of the section.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$eyebrow  = $attributes['eyebrow'] ?? '';
$title    = $attributes['title'] ?? '';
$subtitle = $attributes['subtitle'] ?? '';
$bg_url   = $attributes['backgroundUrl'] ?? '';
$overlay  = isset( $attributes['overlayOpacity'] ) ? (float) $attributes['overlayOpacity'] / 100 : 0.5;
$buttons  = isset( $attributes['buttons'] ) && is_array( $attributes['buttons'] ) ? $attributes['buttons'] : array();
$note     = $attributes['note'] ?? '';
$stats    = isset( $attributes['stats'] ) && is_array( $attributes['stats'] ) ? $attributes['stats'] : array();

$wrapper = get_block_wrapper_attributes( array( 'class' => 'wl-iw-hero' . ( $bg_url ? ' has-bg' : '' ) ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $bg_url ) : ?>
		<div class="wl-iw-hero__media" aria-hidden="true">
			<?php
			echo wonderland_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$bg_url,
				array(
					'size'     => 'full',
					'sizes'    => '100vw',
					'priority' => true, // opener image: the likely LCP element
				)
			);
			?>
			<span class="wl-iw-hero__overlay" style="opacity:<?php echo esc_attr( (string) $overlay ); ?>"></span>
		</div>
	<?php endif; ?>

	<div class="wl-iw-hero__inner">
		<?php if ( $eyebrow ) : ?>
			<p class="wl-iw-hero__eyebrow"><?php echo wp_kses_post( $eyebrow ); ?></p>
		<?php endif; ?>

		<?php if ( $title ) : ?>
			<h1 class="wl-iw-hero__title"><?php echo wp_kses_post( $title ); ?></h1>
		<?php endif; ?>

		<?php if ( $subtitle ) : ?>
			<p class="wl-iw-hero__lead"><?php echo wp_kses_post( $subtitle ); ?></p>
		<?php endif; ?>

		<?php if ( $buttons ) : ?>
			<div class="wl-iw-hero__actions">
				<?php foreach ( $buttons as $btn ) : ?>
					<?php
					$b_text = is_array( $btn ) ? trim( (string) ( $btn['text'] ?? '' ) ) : '';
					$b_url  = is_array( $btn ) ? ( $btn['url'] ?? '' ) : '';
					if ( '' === $b_text || '' === $b_url ) {
						continue;
					}
					$ghost = ( 'ghost' === ( $btn['style'] ?? '' ) ) ? ' is-ghost' : '';
					$blank = ! empty( $btn['newTab'] );
					?>
					<a class="wl-iw-hero__btn<?php echo esc_attr( $ghost ); ?>" href="<?php echo esc_url( $b_url ); ?>"<?php echo $blank ? ' target="_blank" rel="noopener"' : ''; ?>>
						<?php echo esc_html( $b_text ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $note ) : ?>
			<p class="wl-iw-hero__note"><?php echo wp_kses_post( $note ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( $stats ) : ?>
		<ul class="wl-iw-hero__stats">
			<?php foreach ( $stats as $stat ) : ?>
				<?php $label = is_array( $stat ) ? ( $stat['text'] ?? '' ) : (string) $stat; ?>
				<?php if ( '' === trim( (string) $label ) ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<li><?php echo esc_html( $label ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>
