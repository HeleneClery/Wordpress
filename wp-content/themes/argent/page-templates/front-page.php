<?php
/**
 * Template Name: Front Page
 *
 * @package Argent
 */

$front_portfolio = get_theme_mod( 'argent_front_portfolio', 1 );

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php while ( have_posts() ) : the_post(); ?>
			<?php if ( '' != get_the_content() ) : ?>
			<div class="page-content">
				<?php the_content(); ?>
			</div>
			<?php endif; ?>
		<?php endwhile; // end of the loop. ?>

		<?php
			if ( 1 == $front_portfolio ) :
				get_template_part( 'content', 'front-portfolio' );
			endif;
		?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
