<?php
/**
 * The template for Front Page Portfolio section
 *
 * @package Argent
 */

// Get Portfolio title
$front_portfolio_title = get_theme_mod ( 'argent_front_portfoliotitle', __( 'Recent Projects', 'argent') );

// Get number of projects to display
$portfolio_items_number = absint( get_theme_mod( 'argent_front_portfolio_number', 3 ) );

// Portfolio Query
if ( get_query_var( 'paged' ) ) :
	$paged = get_query_var( 'paged' );
elseif ( get_query_var( 'page' ) ) :
	$paged = get_query_var( 'page' );
else :
	$paged = 1;
endif;

$args = array(
	'post_type'      => 'jetpack-portfolio',
	'paged'          => $paged,
	'posts_per_page' => $portfolio_items_number ,
);

$project_query = new WP_Query ( $args );

?>

<?php if ( $project_query -> have_posts() ) : ?>

	<div class="front-page-block portfolio">

		<?php if ( !empty( $front_portfolio_title ) ) : ?>
			<h2 class="section-title"><?php echo esc_html( $front_portfolio_title ); ?></h2>
		<?php endif; ?>

		<div id="portfolio-wrapper">

		<?php
			while ( $project_query->have_posts() ) : $project_query->the_post();
				get_template_part( 'content', 'portfolio' );
			endwhile;

			wp_reset_postdata();
		?>
		</div><!-- #portfolio-wrapper -->

	</div><!-- .front-page-block.portfolio -->

<?php else : ?>

	<?php if ( current_user_can( 'publish_posts' ) ) : ?>

	<section class="no-results not-found">

		<div class="page-content">
			<h3 class="page-title"><?php _e( 'No Projects Found', 'argent' ); ?></h3>

			<p>
				<?php printf( esc_html__( 'This section will display your latest projects. It can be disabled via the Customizer.', 'argent' ) ); ?><br />
				<?php printf( wp_kses( __( 'Ready to publish your first project? <a href="%1$s">Get started here</a>.', 'argent' ), array( 'a' => array( 'href' => array() ) ) ), esc_url( admin_url( 'post-new.php?post_type=jetpack-portfolio' ) ) ); ?>
			</p>
		</div>

	</section><!-- .no-results.not-found -->

	<?php endif; ?>

<?php endif; ?>
