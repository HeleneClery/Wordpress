<?php
/**
 * The template used for displaying Portfolio Archive view
 *
 * @package Argent
 */
?>

<header class="page-header">
	<?php argent_portfolio_title( '<h1 class="page-title">', '</h1>' ); ?>

	<?php argent_portfolio_thumbnail( '<div class="portfolio-featured-image">', '</div>'); ?>

	<?php argent_portfolio_content( '<div class="taxonomy-description">', '</div>' ); ?>
</header><!-- .page-header -->

<div id="portfolio-wrapper">
	<?php /* Start the Loop */ ?>
	<?php while ( have_posts() ) : the_post(); ?>

		<?php get_template_part( 'content', 'portfolio' ); ?>

	<?php endwhile; ?>
</div><!-- #portfolio-wrapper -->

<?php
	the_posts_navigation( array(
		'prev_text'          => esc_html__( 'Older projects', 'argent' ),
		'next_text'          => esc_html__( 'Newer projects', 'argent' ),
		'screen_reader_text' => esc_html__( 'Portfolio navigation', 'argent' ),
	) );
?>