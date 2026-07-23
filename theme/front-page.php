<?php
/**
 * Front page. The homepage is a normal Page composed of Gutenberg blocks,
 * so we simply render its content — the blocks (from the Wonderland Blocks
 * plugin) drive the layout.
 *
 * @package Wonderland
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'front-page' ); ?>>
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
