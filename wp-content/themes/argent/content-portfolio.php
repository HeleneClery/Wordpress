<?php
/**
 * Template for displaying portfolio content.
 *
 * @package Argent
 */

$project_img = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'argent-project-thumbnail' );
$background_style = '';

if ( !empty ( $project_img ) ) :
	$background_style = "background-image:url( ' " . esc_url( $project_img[0] ) . " ' )";
endif;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="project-image" style="<?php echo $background_style; ?>;">

		<a href="<?php the_permalink(); ?>" rel="bookmark">
			<div class="project-summary">
				<?php the_title( '<h2 class="project-title">', '</h2>' ); ?>
			</div><!-- .project-summary -->
		</a>

	</div><!-- .project-image -->

</article><!-- #post-## -->
