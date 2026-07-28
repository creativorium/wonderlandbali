<?php
/**
 * Single post.
 *
 * The featured image runs full width above a narrow measure of copy — the same
 * editorial rhythm as the service pages, without the async overlaps, because a
 * post is one column of reading.
 *
 * @package Wonderland
 */

get_header();

$blog_id  = (int) get_option( 'page_for_posts' );
$blog_url = $blog_id ? get_permalink( $blog_id ) : home_url( '/' );

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'wl-post' ); ?>>
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="wl-post__media">
				<?php
				the_post_thumbnail(
					'full',
					array(
						'loading'       => 'eager',
						'fetchpriority' => 'high',
						'sizes'         => '100vw',
					)
				);
				?>
			</figure>
		<?php endif; ?>

		<header class="wl-post__head">
			<p class="wl-post__meta">
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				<?php
				$wl_terms = get_the_category();
				if ( $wl_terms ) :
					?>
					<span aria-hidden="true"> · </span><?php echo esc_html( $wl_terms[0]->name ); ?>
				<?php endif; ?>
			</p>
			<h1 class="wl-post__title"><?php the_title(); ?></h1>
		</header>

		<?php if ( trim( wp_strip_all_tags( get_the_content() ) ) ) : ?>
			<div class="wl-post__content">
				<?php the_content(); ?>
			</div>
		<?php endif; ?>

		<footer class="wl-post__foot">
			<a class="wl-post__back" href="<?php echo esc_url( $blog_url ); ?>">
				<?php esc_html_e( 'Back to all stories', 'wonderland' ); ?>
			</a>
			<a class="wl-post__cta" href="<?php echo esc_url( home_url( '/request/' ) ); ?>">
				<?php esc_html_e( 'Make a Request', 'wonderland' ); ?>
			</a>
		</footer>
	</article>
	<?php
endwhile;

get_footer();
