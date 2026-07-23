<?php
/**
 * Fallback template (blog / archives / anything without a more specific template).
 *
 * @package Wonderland
 */

get_header();
?>

<div class="content-list">
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class( 'content-list__item' ); ?>>
				<h2 class="content-list__title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h2>
				<div class="content-list__excerpt">
					<?php the_excerpt(); ?>
				</div>
			</article>
			<?php
		endwhile;

		the_posts_pagination();
	else :
		?>
		<p><?php esc_html_e( 'Nothing found.', 'wonderland' ); ?></p>
		<?php
	endif;
	?>
</div>

<?php
get_footer();
