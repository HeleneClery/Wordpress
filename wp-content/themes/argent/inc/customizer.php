<?php
/**
 * argent Theme Customizer
 *
 * @package Argent
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function argent_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	$wp_customize->add_section( 'argent_front_options', array(
		'priority' 			=> 130,
		'capability' 		=> 'edit_theme_options',
		'theme_supports'	=> '',
		'title' 			=> __( 'Theme Options', 'argent' ),
		'description' 		=> __( 'Front Page Portfolio section settings', 'argent' ),
	) );

	$wp_customize->add_setting( 'argent_front_portfolio', array(
		'default'           => '1',
		'sanitize_callback' => 'argent_sanitize_checkbox',
	) );

	$wp_customize->add_control( 'argent_front_portfolio', array(
		'label'             => __( 'Show Portfolio Section', 'argent' ),
		'section'           => 'argent_front_options',
		'type'              => 'checkbox',
	) );

	$wp_customize->add_setting( 'argent_front_portfoliotitle', array(
		'default'           => 'Recent Projects',
		'sanitize_callback' => 'argent_sanitize_text',
	) );

	$wp_customize->add_control( 'argent_front_portfoliotitle', array(
		'label'             => __( 'Portfolio Section Title', 'argent' ),
		'section'          	=> 'argent_front_options',
		'type'              => 'text',
	) );

	$wp_customize->add_setting( 'argent_front_portfolio_number', array(
		'default'           => '3',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( 'argent_front_portfolio_number', array(
		'label'             => __( 'Number of Projects to display', 'argent' ),
		'section'          	=> 'argent_front_options',
		'type'              => 'select',
			'choices' 		=> array(
				'3'	=> '3',
				'6'	=> '6',
				'9' => '9',
			),
	) );
}
add_action( 'customize_register', 'argent_customize_register' );

/**
 * Sanitize the checkbox.
 *
 * @param boolean $input.
 * @return boolean (true|false).
 */
function argent_sanitize_checkbox( $input ) {
	if ( 1 == $input ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Sanitize text
 */
function argent_sanitize_text( $input ) {
	return wp_kses_post( force_balance_tags( $input ) );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function argent_customize_preview_js() {
	wp_enqueue_script( 'argent_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20130508', true );
}
add_action( 'customize_preview_init', 'argent_customize_preview_js' );
