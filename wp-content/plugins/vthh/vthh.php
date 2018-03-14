<?php
/**
 * Plugin Name: VTHH
 * Plugin URI:
 * Description: Ajout des fonctions.
 * Author: Your VTHH
 * Author URI: 
 * Version: 1.0
 */
 
add_filter( 'wp_nav_menu_args', 'my_wp_nav_menu_args' );
function my_wp_nav_menu_args( $args = '' ) {
if( is_user_logged_in() && (!current_user_can('administrator') | !is_admin())) { 
    $args['menu'] = 'connecté';
}
if (!is_user_logged_in()) {
    $args['menu'] = 'non-connecté';
}
if (current_user_can('administrator') | is_admin()) {
	$args['menu'] = 'admin';
 }
    return $args;
}



add_action('init','custom_login');
function custom_login(){
 global $pagenow;
 if( 'wp-login.php' == $pagenow && $_GET['action']!="logout") {
  wp_redirect('/wordpress/');
  exit();
 }
}




add_action( 'template_redirect', 'redirect_to_specific_page' );
function redirect_to_specific_page() {
if ( is_page(27) && ! is_user_logged_in() ) {
auth_redirect(); 
  }
if (is_page(80) && !is_admin() && !current_user_can('administrator')) {
	auth_redirect();
}
}



add_action('user_register','changer_nom_affiche');
function changer_nom_affiche( $user_id ) {
    $info = get_userdata( $user_id );
	$args = array(
		'ID' => $user_id,
		'display_name' => $info->first_name . ' ' . $info->last_name
	);
    wp_update_user( $args );
}



function redirect($url){
    $string = '<script type="text/javascript">';
    $string .= 'window.location = "' . $url . '"';
    $string .= '</script>';
    echo $string;
}



add_filter( 'wp_authenticate_user', 'verifier_utilisateur', 1 );
function verifier_utilisateur( $user ) {
    if (is_wp_error($user)) {
        return $user;
    }
	$user_id = get_current_user_id();
    $verif = get_user_meta($user->ID, 'verif', true );
	$level = get_user_meta($user->ID,'wp_user_level',true);
	if ( $verif != "true" && $level != 10) {
		return new WP_ERROR();
    }
    return $user;
}



add_action('after_setup_theme', 'remove_admin_bar');
 
function remove_admin_bar() {
if (!current_user_can('administrator') && !is_admin()) {
  show_admin_bar(false);
}
}
