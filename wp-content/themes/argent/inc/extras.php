<?php
/**
 * Custom functions that act independently of the theme templates
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package Argent
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function argent_body_classes( $classes ) {
	// Adds a class of group-blog to blogs with more than 1 published author.
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	if ( ! has_post_thumbnail() ) {
		$classes[] = 'without-featured-image';
	}

	return $classes;
}
add_filter( 'body_class', 'argent_body_classes' );

/**
 * Display page-links for paginated posts before Jetpack share buttons and related posts.
 */
function argent_custom_link_pages( $content ) {
	if( is_singular() && is_main_query() ) {
		$content .= wp_link_pages( array(
						'before'      => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'argent' ) . '</span>',
						'after'       => '</div>',
						'link_before' => '<span>',
						'link_after'  => '</span>',
						'echo'		  => 0
					) );
	}
	return $content;
}
add_filter( 'the_content', 'argent_custom_link_pages', 1 );

/**
 * Get the gallery shortcode from the project content.
 */
function argent_get_gallery() {
	global $post;
	$pattern = get_shortcode_regex();

	if (   preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches )
		&& array_key_exists( 2, $matches )
		&& in_array( 'gallery', $matches[2] ) )
	{
		return $matches;
	}
}

if ( version_compare( $GLOBALS['wp_version'], '4.1', '<' ) ) :
	/**
	 * Filters wp_title to print a neat <title> tag based on what is being viewed.
	 *
	 * @param string $title Default title text for current view.
	 * @param string $sep Optional separator.
	 * @return string The filtered title.
	 */
	function argent_wp_title( $title, $sep ) {
		if ( is_feed() ) {
			return $title;
		}

		global $page, $paged;

		// Add the blog name
		$title .= get_bloginfo( 'name', 'display' );

		// Add the blog description for the home/front page.
		$site_description = get_bloginfo( 'description', 'display' );
		if ( $site_description && ( is_home() || is_front_page() ) ) {
			$title .= " $sep $site_description";
		}

		// Add a page number if necessary:
		if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() ) {
			$title .= " $sep " . sprintf( __( 'Page %s', 'argent' ), max( $paged, $page ) );
		}

		return $title;
	}
	add_filter( 'wp_title', 'argent_wp_title', 10, 2 );

	/**
	 * Title shim for sites older than WordPress 4.1.
	 *
	 * @link https://make.wordpress.org/core/2014/10/29/title-tags-in-4-1/
	 * @todo Remove this function when WordPress 4.3 is released.
	 */
	function argent_render_title() {
		?>
		<title><?php wp_title( '|', true, 'right' ); ?></title>
		<?php
	}
	add_action( 'wp_head', 'argent_render_title' );
endif;
