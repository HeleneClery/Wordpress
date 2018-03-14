<?php
/**
 * Plugin Name: Outils admin
 * Plugin URI:
 * Description: Ajout des fonctions.
 * Author: VTHH 2017-2018
 * Author URI: 
 * Version: 1.0
 */
 
// afficher le menu correspondant au statut de l'utilisateur: non-connecté, connecté et admin
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



// restreindre l'accès au wp-admin
add_action('init','custom_login');
function custom_login(){
 global $pagenow;
 if( 'wp-login.php' == $pagenow && $_GET['action']!="logout") {
  wp_redirect('/');
  exit();
 }
}



// restreindre l'accès aux pages "Depot rapport", "Envoi email"
add_action( 'template_redirect', 'redirect_to_specific_page' );
function redirect_to_specific_page() {
if ( is_page(27) && ! is_user_logged_in() ) {
auth_redirect(); 
  }
if (is_page(80) && !is_admin() && !current_user_can('administrator')) {
	auth_redirect();
}
}



// Changer le nom d'affichage au format Nom + prénom
add_action('user_register','changer_nom_affiche');
function changer_nom_affiche( $user_id ) {
    $info = get_userdata( $user_id );
	$args = array(
		'ID' => $user_id,
		'display_name' => $info->first_name . ' ' . $info->last_name
	);
    wp_update_user( $args );
}



// Méthode pour rédiger l'utilisateur vers une autre page
function redirect($url){
    $string = '<script type="text/javascript">';
    $string .= 'window.location = "' . $url . '"';
    $string .= '</script>';
    echo $string;
}



// restreindre la connexion des utilisateurs qui n'ont pas validé l'inscription
add_filter( 'wp_authenticate_user', 'verifier_utilisateur', 1 );
function verifier_utilisateur( $user ) {
    if (is_wp_error($user)) {
        return $user;
    }
	$user_id = get_current_user_id();
    $verif = get_user_meta($user->ID, 'verif', true );
	$level = get_user_meta($user->ID,'wp_m2ccitours_user_level',true);
	if ( $verif != "true" && $level != 10) {
		return new WP_ERROR();
    }
    return $user;
}



// Cacher la barre d'admin aux utilisateurs normaux
add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
if (!current_user_can('administrator') && !is_admin()) {
  show_admin_bar(false);
}
}



// Afficher le nom d'utilisateur sur le menu. Attention: le nom de l'onglet dans le menu doit être [Compte]
add_filter('the_title', 'modif_titre_menu');
function modif_titre_menu($title) {
    $user = wp_get_current_user();
    $name = $user->display_name;
    if (!is_admin() && !current_user_can('administrator') && $title == '[Compte]') {
        if (is_user_logged_in()) {
            $title = "Bonjour, " . $name;
        }
    }
    return $title;
}



// Définir l'adresse mail et le nom affiché
add_filter('wp_mail_from','adresse_mail');
function adresse_mail($content_type) {
  return 'm2ccitours@gmail.com';
}
add_filter('wp_mail_from_name','nom_affiche');
function nom_affiche($name) {
  return 'Gestion Rapport de stage';
}







