<?php

function wfu_maintenance_actions($message = '') {
	if ( !current_user_can( 'manage_options' ) ) return wfu_manage_mainmenu();

	$siteurl = site_url();
	
	$echo_str = '<div class="wrap">';
	$echo_str .= "\n\t".'<h2>Wordpress File Upload Control Panel</h2>';
	if ( $message != '' ) {
		$echo_str .= "\n\t".'<div class="updated">';
		$echo_str .= "\n\t\t".'<p>'.$message.'</p>';
		$echo_str .= "\n\t".'</div>';
	}
	$echo_str .= "\n\t".'<div style="margin-top:20px;">';
	$echo_str .= wfu_generate_dashboard_menu("\n\t\t", "Maintenance Actions");
	//maintenance actions
	$echo_str .= "\n\t\t".'<h3 style="margin-bottom: 10px;">Maintenance Actions</h3>';
	$echo_str .= "\n\t\t".'<table class="form-table">';
	$echo_str .= "\n\t\t\t".'<tbody>';
	$echo_str .= "\n\t\t\t\t".'<tr>';
	$echo_str .= "\n\t\t\t\t\t".'<th scope="row">';
	$echo_str .= "\n\t\t\t\t\t\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&amp;action=sync_db" class="button" title="Update database to reflect current status of files">Sync Database</a>';
	$echo_str .= "\n\t\t\t\t\t".'</th>';
	$echo_str .= "\n\t\t\t\t\t".'<td>';
	$echo_str .= "\n\t\t\t\t\t\t".'<label>Update database to reflect current status of files.</label>';
	$echo_str .= "\n\t\t\t\t\t".'</td>';
	$echo_str .= "\n\t\t\t\t".'</tr>';
	$echo_str .= "\n\t\t\t\t".'<tr>';
	$echo_str .= "\n\t\t\t\t\t".'<th scope="row">';
	$echo_str .= "\n\t\t\t\t\t\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&amp;action=clean_log_ask" class="button" title="Clean all database log">Clean Log</a>';
	$echo_str .= "\n\t\t\t\t\t".'</th>';
	$echo_str .= "\n\t\t\t\t\t".'<td>';
	$echo_str .= "\n\t\t\t\t\t\t".'<label>Clean-up all database log, including file information and user data (files will not be affected).</label>';
	$echo_str .= "\n\t\t\t\t\t".'</td>';
	$echo_str .= "\n\t\t\t\t".'</tr>';
	$echo_str .= "\n\t\t\t".'</tbody>';
	$echo_str .= "\n\t\t".'</table>';
	$echo_str .= "\n\t".'</div>';
	//export actions
	$echo_str .= "\n\t".'<div style="margin-top:20px;">';
	$echo_str .= "\n\t\t".'<h3 style="margin-bottom: 10px;">Export Actions</h3>';
	$echo_str .= "\n\t\t".'<table class="form-table">';
	$echo_str .= "\n\t\t\t".'<tbody>';
	$echo_str .= "\n\t\t\t\t".'<tr>';
	$echo_str .= "\n\t\t\t\t\t".'<th scope="row">';
	$echo_str .= "\n\t\t\t\t\t\t".'<a href="javascript:wfu_download_file(\'exportdata\', 1);" class="button" title="Export uploaded file data">Export Uploaded File Data</a>';
	$echo_str .= "\n\t\t\t\t\t\t".'<input id="wfu_download_file_nonce" type="hidden" value="'.wp_create_nonce('wfu_download_file_invoker').'" />';
	$echo_str .= "\n\t\t\t\t\t".'</th>';
	$echo_str .= "\n\t\t\t\t\t".'<td>';
	$echo_str .= "\n\t\t\t\t\t\t".'<label>Export uploaded valid file data, together with any userdata fields, to a comma-separated text file.</label>';
	$echo_str .= "\n\t\t\t\t\t\t".'<div id="wfu_file_download_container_1" style="display: none;"></div>';
	$echo_str .= "\n\t\t\t\t\t".'</td>';
	$echo_str .= "\n\t\t\t\t".'</tr>';
	$echo_str .= "\n\t\t\t".'</tbody>';
	$echo_str .= "\n\t\t".'</table>';
	$echo_str .= "\n\t".'</div>';
	$echo_str .= "\n".'</div>';
	
	echo $echo_str;
}

function wfu_clean_log_prompt() {
	$siteurl = site_url();

	if ( !current_user_can( 'manage_options' ) ) return wfu_manage_mainmenu();

	$echo_str = "\n".'<div class="wrap">';
	$echo_str .= "\n\t".'<div style="margin-top:20px;">';
	$echo_str .= "\n\t\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&amp;action=maintenance_actions" class="button" title="go back">Go back</a>';
	$echo_str .= "\n\t".'</div>';
	$echo_str .= "\n\t".'<h2 style="margin-bottom: 10px;">Clean Database Log</h2>';
	$echo_str .= "\n\t".'<form enctype="multipart/form-data" name="clean_log" id="clean_log" method="post" action="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload" class="validate">';
	$nonce = wp_nonce_field('wfu_clean_log', '_wpnonce', false, false);
	$nonce_ref = wp_referer_field(false);
	$echo_str .= "\n\t\t".$nonce;
	$echo_str .= "\n\t\t".$nonce_ref;
	$echo_str .= "\n\t\t".'<input type="hidden" name="action" value="clean_log">';
	$echo_str .= "\n\t\t".'<label>Are you sure that you want to clean the database log? This will erase all file data kept by the plugin in the database. Files uploaded by the plugin will be maintained, however all relevant information, such as user data, will be erased.</label><br/>';
	$echo_str .= "\n\t\t".'<p class="submit">';
	$echo_str .= "\n\t\t\t".'<input type="submit" class="button-primary" name="submit" value="Yes">';
	$echo_str .= "\n\t\t\t".'<input type="submit" class="button-primary" name="submit" value="Cancel">';
	$echo_str .= "\n\t\t".'</p>';
	$echo_str .= "\n\t".'</form>';
	$echo_str .= "\n".'</div>';
	return $echo_str;
}

function wfu_clean_log() {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	global $wpdb;

	if ( !current_user_can( 'manage_options' ) ) return -1;
	if ( !check_admin_referer('wfu_clean_log') ) return -1;
	
	$count = -1;
	if ( isset($_POST['submit']) && $_POST['submit'] == "Yes" ) {
		$table_name1 = $wpdb->prefix . "wfu_log";
		$table_name2 = $wpdb->prefix . "wfu_userdata";
		$table_name3 = $wpdb->prefix . "wfu_dbxqueue";

		$count = $wpdb->query("DELETE FROM $table_name1");
		$count += $wpdb->query("DELETE FROM $table_name2");
		$count += $wpdb->query("DELETE FROM $table_name3");
	}
	
	return $count;
}

?>