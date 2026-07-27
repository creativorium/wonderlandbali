<?php
/**
 * Server-side render for wonderland/page-hero.
 *
 * Three layouts:
 *  - center  : big centred title (optional eyebrow/subtitle/background).
 *  - split   : full-bleed image band with a giant title straddling the bottom
 *              edge — the first line sits (white) over the image, the second
 *              (dark) drops onto the page below.
 *  - aside   : boxed two-column opener — stacked title, lead and body copy on
 *              the left, image on the right. Used by the service pages, which
 *              follow a deliberately plainer layout than the rest of the site.
 *
 * @var array $attributes Block attributes.
 * @package WonderlandBlocks
 */

$eyebrow  = $attributes['eyebrow'] ?? '';
$title    = $attributes['title'] ?? '';
$subtitle = $attributes['subtitle'] ?? '';
$bg_url   = $attributes['backgroundUrl'] ?? '';
$overlay  = isset( $attributes['overlayOpacity'] ) ? (float) $attributes['overlayOpacity'] / 100 : 0.4;
$layout   = $attributes['layout'] ?? 'center';
$badge    = $attributes['badgeUrl'] ?? '';

if ( 'aside' === $layout ) :
	$text    = $attributes['text'] ?? '';
	$img_pos = $attributes['imagePosition'] ?? 'center';
	$wrapper = get_block_wrapper_attributes( array( 'class' => 'wl-page-hero wl-page-hero--aside' ) );
	?>
	<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="wl-page-hero__aside-inner">
			<div class="wl-page-hero__copy">
				<?php if ( $eyebrow ) : ?>
					<p class="wl-page-hero__eyebrow"><?php echo wp_kses_post( $eyebrow ); ?></p>
				<?php endif; ?>
				<h1 class="wl-page-hero__aside-title"><?php echo wp_kses_post( $title ); ?></h1>
				<?php if ( $subtitle ) : ?>
					<p class="wl-page-hero__lead"><?php echo wp_kses_post( $subtitle ); ?></p>
				<?php endif; ?>
				<?php if ( $text ) : ?>
					<div class="wl-page-hero__body"><?php echo wp_kses_post( wpautop( $text ) ); ?></div>
				<?php endif; ?>
			</div>
			<?php if ( $bg_url ) : ?>
				<figure class="wl-page-hero__aside-media">
					<?php
					echo wonderland_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$bg_url,
						array(
							'size'     => 'large',
							'sizes'    => '(max-width: 860px) 100vw, 50vw',
							'priority' => true, // opener image: the likely LCP element
							'style'    => 'object-position:' . $img_pos,
						)
					);
					?>
				</figure>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return;
endif;

if ( 'split' === $layout ) :
	$t_top    = $attributes['titleTop'] ?? '';
	$t_bottom = $attributes['titleBottom'] ?? '';
	$img_pos  = $attributes['imagePosition'] ?? 'center';
	$wrapper  = get_block_wrapper_attributes( array( 'class' => 'wl-page-hero wl-page-hero--split' ) );
	$media_style = $bg_url ? ' style="background-image:url(' . esc_url( $bg_url ) . ');background-position:' . esc_attr( $img_pos ) . '"' : '';
	?>
	<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="wl-page-hero__media"<?php echo $media_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php if ( $badge ) : ?>
				<img class="wl-page-hero__badge" src="<?php echo esc_url( $badge ); ?>" alt="" loading="lazy" decoding="async" />
			<?php endif; ?>
		</div>
		<?php $single = ( '' === trim( (string) $t_top ) || '' === trim( (string) $t_bottom ) ); ?>
		<h1 class="wl-page-hero__split-title<?php echo $single ? ' is-single' : ''; ?>">
			<?php if ( '' !== trim( (string) $t_top ) ) : ?>
				<span class="wl-page-hero__t1"><?php echo wp_kses_post( $t_top ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== trim( (string) $t_bottom ) ) : ?>
				<span class="wl-page-hero__t2"><?php echo wp_kses_post( $t_bottom ); ?></span>
			<?php endif; ?>
		</h1>
	</section>
	<?php
	return;
endif;

$height  = ( $attributes['height'] ?? 'short' ) === 'tall' ? 'wl-page-hero--tall' : 'wl-page-hero--short';
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
