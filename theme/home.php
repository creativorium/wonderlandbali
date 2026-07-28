<?php
/**
 * Blog index — the page assigned as "Posts page" in Settings → Reading.
 *
 * Each entry is a card: its featured image, title and date. Posts carry their
 * own copy once written; the card only ever shows the excerpt when there is one,
 * so an image-only entry reads as a gallery tile rather than an empty article.
 *
 * @package Wonderland
 */

get_header();

$blog_id    = (int) get_option( 'page_for_posts' );
$blog_title = $blog_id ? get_the_title( $blog_id ) : __( 'Journal', 'wonderland' );
$blog_intro = $blog_id ? get_post_field( 'post_excerpt', $blog_id ) : '';
?>

<section class="wl-blog">
	<header class="wl-blog__head">
		<p class="wl-blog__eyebrow"><?php esc_html_e( 'Wonderland Bali Events', 'wonderland' ); ?></p>
		<h1 class="wl-blog__title"><?php echo esc_html( $blog_title ); ?></h1>
		<?php if ( $blog_intro ) : ?>
			<p class="wl-blog__intro"><?php echo esc_html( $blog_intro ); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="wl-blog__grid">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'wl-blog__card' ); ?>>
					<a class="wl-blog__link" href="<?php the_permalink(); ?>">
						<?php if ( has_post_thumbnail() ) : ?>
							<figure class="wl-blog__media">
								<?php
								the_post_thumbnail(
									'large',
									array(
										'loading' => 'lazy',
										'sizes'   => '(max-width: 700px) 100vw, (max-width: 1000px) 50vw, 33vw',
									)
								);
								?>
							</figure>
						<?php endif; ?>

						<div class="wl-blog__body">
							<p class="wl-blog__date"><?php echo esc_html( get_the_date() ); ?></p>
							<h2 class="wl-blog__name"><?php the_title(); ?></h2>
							<?php if ( has_excerpt() ) : ?>
								<p class="wl-blog__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
							<?php endif; ?>
						</div>
					</a>
				</article>
				<?php
			endwhile;
			?>
		</div>

		<?php
		the_posts_pagination(
			array(
				'class'     => 'wl-blog__pagination',
				'mid_size'  => 1,
				'prev_text' => esc_html__( 'Previous', 'wonderland' ),
				'next_text' => esc_html__( 'Next', 'wonderland' ),
			)
		);
		?>
	<?php else : ?>
		<p class="wl-blog__empty"><?php esc_html_e( 'Nothing published yet — check back soon.', 'wonderland' ); ?></p>
	<?php endif; ?>
</section>

<?php
get_footer();
