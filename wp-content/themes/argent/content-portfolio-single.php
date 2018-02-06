<?php
/**
 * @package Argent
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<header class="entry-header">
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
	</header>

	<?php
		if ( get_post_gallery() ) {
			get_template_part( 'content', 'portfolio-gallery' );
		}
	?>

	<div class="entry-content">
		<?php
			$content = get_the_content();

			/**
			 * Get the gallery data from the post.
			 */
			$gallery_shortcode = argent_get_gallery();

			/**
			 * Grab the first shortcode in post content, strip it out, and
			 * display the post content without the first gallery.
			 */
			if( $gallery_shortcode  && is_array( $gallery_shortcode ) ) {
				$content = str_replace( $gallery_shortcode[0][0], '', $content );
				$content = wp_kses_post( $content );
			}
			echo apply_filters( 'the_content', $content );
		?>
	</div><!-- .entry-content -->

	<footer class="entry-footer entry-meta">
		<?php
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_term_list( $post->ID, 'jetpack-portfolio-type', '', __( ', ', 'argent' ) );
			if ( $categories_list && argent_categorized_blog() ) {
				printf( '<span class="cat-links">' . __( 'Posted in %1$s', 'argent' ) . '</span>', $categories_list );
			}

			/* translators: used between list items, there is a space after the comma */
			$tags_list = get_the_term_list( $post->ID, 'jetpack-portfolio-tag', '', __( ', ', 'argent' ) );
			if ( $tags_list ) {
				printf( '<span class="tags-links">'. __( 'Tagged %1$s', 'argent' ) . '</span>', $tags_list );
			}
		?>

		<?php edit_post_link( __( 'Edit', 'argent' ), '<span class="edit-link">', '</span>' ); ?>
	</footer><!-- .entry-meta -->
</article><!-- #post-## -->
