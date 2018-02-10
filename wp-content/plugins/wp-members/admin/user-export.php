<?php
/**
 * WP-Members Export Functions
 *
 * Mananges exporting users to a CSV file.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2017  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2017
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * New export function to export all or selected users
 *
 * @since 2.9.7
 *
 * @param array $args
 * @param array $users
 */
function wpmem_export_users( $args, $users = null ) {

	global $wpmem;

	$today = date( "m-d-y" ); 

	// Setup defaults.
	$defaults = array(
		'export'         => 'all',
		'filename'       => 'wp-members-user-export-' . $today . '.csv',
		'export_fields'  => wpmem_fields(), //array(),
		'exclude_fields' => array( 'password', 'confirm_password', 'confirm_email' ),
		'entity_decode'  => false,
	);

	// Merge $args with defaults.
	/**
	 * Filter the default export arguments.
	 *
	 * @since 2.9.7
	 *
	 * @param array $args An array of arguments to merge with defaults. Default null.
 	 */
	$args = wp_parse_args( apply_filters( 'wpmem_export_args', $args ), $defaults );

	// Output needs to be buffered, start the buffer.
	ob_start();

	// If exporting all, get all of the users.
	$users = ( 'all' == $args['export'] ) ? get_users( array( 'fields' => 'ID' ) ) : $users;

	// Generate headers and a filename based on date of export.
	header( "Content-Description: File Transfer" );
	header( "Content-type: application/octet-stream" );
	header( "Content-Disposition: attachment; filename=" . $args['filename'] );
	header( "Content-Type: text/csv; charset=" . get_option( 'blog_charset' ), true );
	echo "\xEF\xBB\xBF"; // UTF-8 BOM

	// Do the header row.
	$hrow = "User ID,Username,";

	foreach ( $args['export_fields'] as $meta => $field ) {
		if ( ! in_array( $meta, $args['exclude_fields'] ) ) {
			$hrow.= $field['label'] . ",";
		}
	}

	$hrow .= ( $wpmem->mod_reg == 1 ) ? __( 'Activated?', 'wp-members' ) . "," : '';
	$hrow .= ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) ? __( 'Subscription', 'wp-members' ) . "," . __( 'Expires', 'wp-members' ) . "," : '';

	$hrow .= __( 'Registered', 'wp-members' ) . ",";
	$hrow .= __( 'IP', 'wp-members' );
	$data  = $hrow . "\r\n";

	/*
	 * We used the fields array once,
	 * rewind so we can use it again.
	 */
	reset( $args['export_fields'] );

	/*
	 * Loop through the array of users,
	 * build the data, delimit by commas, wrap fields with double quotes, 
	 * use \n switch for new line.
	 */
	foreach ( $users as $user ) {

		$user_info = get_userdata( $user );

		$data .= '"' . $user_info->ID . '","' . $user_info->user_login . '",';

		$wp_user_fields = array( 'user_email', 'user_nicename', 'user_url', 'display_name' );
		foreach ( $args['export_fields'] as $meta => $field ) {
			if ( ! in_array( $meta, $args['exclude_fields'] ) ) {
				// @todo Research using fputcsv to escape fields for export.
				if ( in_array( $meta, $wp_user_fields ) ) {
					$data .= '"' . $user_info->{$meta} . '",';	
				} else {
					$raw_data  = get_user_meta( $user, $meta, true );
					$formatted = ( $args['entity_decode'] ) ? html_entity_decode( $raw_data ) : $raw_data;
					$data .= '"' . $formatted . '",';
				}
			}
		}
		
		$data .= ( $wpmem->mod_reg == 1 ) ? '"' . ( get_user_meta( $user, 'active', 1 ) ? __( 'Yes' ) : __( 'No' ) ) . '",' : '';
		$data .= ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) ? '"' . get_user_meta( $user, "exp_type", true ) . '",' : '';
		$data .= ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) ? '"' . get_user_meta( $user, "expires", true  ) . '",' : '';
		
		$data .= '"' . $user_info->user_registered . '",';
		$data .= '"' . get_user_meta( $user, "wpmem_reg_ip", true ). '"';
		$data .= "\r\n";
		
		// Update the user record as being exported.
		if ( 'all' != $args['export'] ){
			update_user_meta( $user, 'exported', 1 );
		}
	}

	// We are done, output the CSV.
	echo $data; 

	// Clear the buffer.
	ob_flush();

	exit();
}

// End of file.