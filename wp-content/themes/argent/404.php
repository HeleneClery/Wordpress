<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @package Argent
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<section class="error-404 not-found">
				<header class="page-header">
					<h2 class="page-title"><?php _e( 'Oops! That page can&rsquo;t be found.', 'argent' ); ?></h2>
				</header><!-- .page-header -->

				<div class="page-content">
					<p><?php _e( 'It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'argent' ); ?></p>

					<?php get_search_form(); ?>
					<div class="widget-container">
					<?php
						$args = array(
							'before_title'  => '<h3 class="widget-title">',
							'after_title'   => '</h3>'
						);

						the_widget( 'WP_Widget_Recent_Posts', 'number=5', $args );
					?>

					<?php if ( argent_categorized_blog() ) : // Only show the widget if site has multiple categories. ?>
					<div class="widget widget_categories">
						<h3 class="widget-title"><?php _e( 'Popular Categories', 'argent' ); ?></h3>
						<ul>
						<?php
							wp_list_categories( array(
								'orderby'    => 'count',
								'order'      => 'DESC',
								'show_count' => 1,
								'title_li'   => '',
								'number'     => 10,
							) );
						?>
						</ul>
					</div><!-- .widget -->
					<?php endif; ?>

					</div><!-- .widget-container -->
				</div><!-- .page-content -->
			</section><!-- .error-404 -->

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
