<?php
/**
 * The template for displaying the Portfolio archive page.
 *
 * @package Argent
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php
			if ( have_posts() ) {
				get_template_part( 'content', 'portfolio-archive' );
			} else {
				get_template_part( 'content', 'none' );
			}
		?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
