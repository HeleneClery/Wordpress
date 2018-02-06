<?php
/**
 * Jetpack Compatibility File
 * See: https://jetpack.me/
 *
 * @package Argent
 */

/**
 * Add theme support for Infinite Scroll.
 * See: https://jetpack.me/support/infinite-scroll/
 */
function argent_jetpack_setup() {
	add_theme_support( 'infinite-scroll', array(
		'wrapper' 		 => false,
		'footer_widgets' => array( 'sidebar-1', 'sidebar-2', 'sidebar-3' ),
		'container' 	 => 'main',
		'footer'    	 => 'page',
	) );

	add_theme_support( 'jetpack-responsive-videos' );

	add_theme_support( 'jetpack-portfolio', array(
		'title'          => true,
		'content'        => true,
		'featured-image' => true,
	) );
}
add_action( 'after_setup_theme', 'argent_jetpack_setup' );

/**
 * Change Jetpack's Infinite Scroll settings.
 */
function argent_infinite_scroll_js_settings( $settings ) {

	// For the portfolio, change the wrapper id and text handle.
	if ( is_post_type_archive( 'jetpack-portfolio' ) || is_tax( 'jetpack-portfolio-type' ) || is_tax( 'jetpack-portfolio-tag' ) ) {
		$settings['id'] = 'portfolio-wrapper';
		$settings[ 'text' ] = esc_js( esc_html__( 'Older projects', 'argent' ) );
	}

	return $settings;
}
add_filter( 'infinite_scroll_js_settings', 'argent_infinite_scroll_js_settings' );

/**
 * Change the render if we are on the portfolio
 * archive or page template.
 */
function argent_infinite_scroll_render() {
	if ( is_post_type_archive( 'jetpack-portfolio' ) || is_tax( 'jetpack-portfolio-type' ) || is_tax( 'jetpack-portfolio-tag' ) ) {
		while ( have_posts() ) {
			the_post();

			get_template_part( 'content', 'portfolio' );
		}
	}
}
add_action( 'infinite_scroll_render', 'argent_infinite_scroll_render' );

/**
 * Portfolio Title.
 */
function argent_portfolio_title( $before = '', $after = '' ) {
	$jetpack_portfolio_title = get_option( 'jetpack_portfolio_title' );
	$title = '';

	if ( is_post_type_archive( 'jetpack-portfolio' ) ) {
		if ( isset( $jetpack_portfolio_title ) && '' != $jetpack_portfolio_title ) {
			$title = esc_html( $jetpack_portfolio_title );
		} else {
			$title = post_type_archive_title( '', false );
		}
	} elseif ( is_tax( 'jetpack-portfolio-type' ) || is_tax( 'jetpack-portfolio-tag' ) ) {
		$title = single_term_title( '', false );
	}

	echo $before . $title . $after;
}

/**
 * Portfolio Content.
 */
function argent_portfolio_content( $before = '', $after = '' ) {
	$jetpack_portfolio_content = get_option( 'jetpack_portfolio_content' );

	if ( is_tax() && get_the_archive_description() ) {
		echo $before . get_the_archive_description() . $after;
	} else if ( isset( $jetpack_portfolio_content ) && '' != $jetpack_portfolio_content ) {
		$content = convert_chars( convert_smilies( wptexturize( stripslashes( wp_filter_post_kses( addslashes( $jetpack_portfolio_content ) ) ) ) ) );
		echo $before . $content . $after;
	}
}

/**
 * Portfolio Featured Image.
 */
function argent_portfolio_thumbnail( $before = '', $after = '' ) {
	$jetpack_portfolio_featured_image = get_option( 'jetpack_portfolio_featured_image' );

	if ( isset( $jetpack_portfolio_featured_image ) && '' != $jetpack_portfolio_featured_image ) {
		$featured_image = wp_get_attachment_image( (int) $jetpack_portfolio_featured_image, 'argent-single-thumbnail' );
		echo $before . $featured_image . $after;
	}
}