<?php

function wordpress_file_upload_admin_init() {
	$uri = $_SERVER['REQUEST_URI'];
	$is_admin = current_user_can( 'manage_options' );
	if ( is_admin() && ( ( $is_admin && strpos($uri, "options-general.php") !== false ) ) ) {
		//apply wfu_before_admin_scripts to get additional settings 
		$changable_data = array();
		$ret_data = apply_filters('wfu_before_admin_scripts', $changable_data);
		//if $ret_data contains 'return_value' key then no scripts will be
		//registered
		if ( isset($ret_data['return_value']) ) return $ret_data['return_value'];
		//continue with script and style registering
		wp_register_style('wordpress-file-upload-admin-style', WPFILEUPLOAD_DIR.'css/wordpress_file_upload_adminstyle.css',false,'1.0','all');
		wp_register_script('wordpress_file_upload_admin_script', WPFILEUPLOAD_DIR.'js/wordpress_file_upload_adminfunctions.js', array( 'wp-color-picker' ), false, true);
	}
}

function wordpress_file_upload_add_admin_pages() {
	$page_hook_suffix = false;
	if ( current_user_can( 'manage_options' ) ) $page_hook_suffix = add_options_page('Wordpress File Upload', 'Wordpress File Upload', 'manage_options', 'wordpress_file_upload', 'wordpress_file_upload_manage_dashboard');
	if ( $page_hook_suffix !== false ) add_action('admin_print_scripts-'.$page_hook_suffix, 'wfu_enqueue_admin_scripts');
}

function wfu_enqueue_admin_scripts() {
	$uri = $_SERVER['REQUEST_URI'];
	$is_admin = current_user_can( 'manage_options' );
	if ( is_admin() && ( ( $is_admin && strpos($uri, "options-general.php") !== false ) ) ) {
		//apply wfu_before_admin_scripts to get additional settings 
		$changable_data = array();
		$ret_data = apply_filters('wfu_before_admin_scripts', $changable_data);
		//if $ret_data contains 'return_value' key then no scripts will be
		//enqueued
		if ( isset($ret_data['return_value']) ) return $ret_data['return_value'];
		//continue with script and style enqueuing
		wp_enqueue_style('wordpress-file-upload-admin-style');
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script('wordpress_file_upload_admin_script');
		$AdminParams = array("wfu_ajax_url" => site_url()."/wp-admin/admin-ajax.php");
		wp_localize_script( 'wordpress_file_upload_admin_script', 'AdminParams', $AdminParams );
	}
}

function wordpress_file_upload_install() {
	global $wpdb;
	global $wfu_tb_log_version;
	global $wfu_tb_userdata_version;
	global $wfu_tb_dbxqueue_version;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$table_name1 = $wpdb->prefix . "wfu_log";
	$installed_ver = get_option( "wordpress_file_upload_table_log_version" );
	if( $installed_ver != $wfu_tb_log_version ) {
		$sql = "CREATE TABLE " . $table_name1 . " ( 
			idlog mediumint(9) NOT NULL AUTO_INCREMENT,
			userid int NOT NULL,
			uploaduserid int NOT NULL,
			uploadtime bigint,
			sessionid VARCHAR(40),
			filepath TEXT NOT NULL,
			filehash VARCHAR(100) NOT NULL,
			filesize bigint NOT NULL,
			uploadid VARCHAR(20) NOT NULL,
			pageid mediumint(9),
			blogid mediumint(9),
			sid VARCHAR(10),
			date_from DATETIME,
			date_to DATETIME,
			action VARCHAR(20) NOT NULL,
			linkedto mediumint(9),
			filedata TEXT,
			PRIMARY KEY  (idlog))
			DEFAULT CHARACTER SET = utf8
			DEFAULT COLLATE = utf8_general_ci;";
		dbDelta($sql);
		update_option("wordpress_file_upload_table_log_version", $wfu_tb_log_version);
	}

	$table_name2 = $wpdb->prefix . "wfu_userdata";
	$installed_ver = get_option( "wordpress_file_upload_table_userdata_version" );
	if( $installed_ver != $wfu_tb_userdata_version ) {
		$sql = "CREATE TABLE " . $table_name2 . " ( 
			iduserdata mediumint(9) NOT NULL AUTO_INCREMENT,
			uploadid VARCHAR(20) NOT NULL,
			property VARCHAR(100) NOT NULL,
			propkey mediumint(9) NOT NULL,
			propvalue TEXT,
			date_from DATETIME,
			date_to DATETIME,
			PRIMARY KEY  (iduserdata))
			DEFAULT CHARACTER SET = utf8
			DEFAULT COLLATE = utf8_general_ci;";
		dbDelta($sql);
		update_option("wordpress_file_upload_table_userdata_version", $wfu_tb_userdata_version);
	}

	$table_name3 = $wpdb->prefix . "wfu_dbxqueue";
	$installed_ver = get_option( "wordpress_file_upload_table_dbxqueue_version" );
	if( $installed_ver != $wfu_tb_dbxqueue_version ) {
		$sql = "CREATE TABLE " . $table_name3 . " ( 
			iddbxqueue mediumint(9) NOT NULL AUTO_INCREMENT,
			fileid mediumint(9) NOT NULL,
			priority mediumint(9) NOT NULL,
			status mediumint(9) NOT NULL,
			jobid VARCHAR(10) NOT NULL,
			start_time bigint,
			PRIMARY KEY  (iddbxqueue))
			DEFAULT CHARACTER SET = utf8
			DEFAULT COLLATE = utf8_general_ci;";
		dbDelta($sql);
		update_option("wordpress_file_upload_table_dbxqueue_version", $wfu_tb_dbxqueue_version);
	}
}

function wordpress_file_upload_update_db_check() {
	global $wfu_tb_log_version;
	global $wfu_tb_userdata_version;
	global $wfu_tb_dbxqueue_version;
//	update_option("wordpress_file_upload_table_log_version", "0");
//	update_option("wordpress_file_upload_table_userdata_version", "0");
//	update_option("wordpress_file_upload_table_dbxqueue_version", "0");
	if ( get_option('wordpress_file_upload_table_log_version') != $wfu_tb_log_version || get_option('wordpress_file_upload_table_userdata_version') != $wfu_tb_userdata_version || get_option('wordpress_file_upload_table_dbxqueue_version') != $wfu_tb_dbxqueue_version ) {
		wordpress_file_upload_install();
	}
}

// This is the callback function that generates dashboard page content
function wordpress_file_upload_manage_dashboard() {
	$_POST = stripslashes_deep($_POST);
	$_GET = stripslashes_deep($_GET);
	$action = (!empty($_POST['action']) ? $_POST['action'] : (!empty($_GET['action']) ? $_GET['action'] : ''));
	$dir = (!empty($_POST['dir']) ? $_POST['dir'] : (!empty($_GET['dir']) ? $_GET['dir'] : ''));
	$file = (!empty($_POST['file']) ? $_POST['file'] : (!empty($_GET['file']) ? $_GET['file'] : ''));
	$referer = (!empty($_POST['referer']) ? $_POST['referer'] : (!empty($_GET['referer']) ? $_GET['referer'] : ''));
	$data_enc = (!empty($_POST['data']) ? $_POST['data'] : (!empty($_GET['data']) ? $_GET['data'] : ''));
	$postid = (!empty($_POST['postid']) ? $_POST['postid'] : (!empty($_GET['postid']) ? $_GET['postid'] : ''));
	$nonce = (!empty($_POST['nonce']) ? $_POST['nonce'] : (!empty($_GET['nonce']) ? $_GET['nonce'] : ''));
	$tag = (!empty($_POST['tag']) ? $_POST['tag'] : (!empty($_GET['tag']) ? $_GET['tag'] : ''));
	$echo_str = "";

	if ( $action == 'edit_settings' ) {
		wfu_update_settings();
		$echo_str = wfu_manage_settings();
	}
	elseif ( $action == 'shortcode_composer' ) {
		$echo_str = wfu_shortcode_composer();
	}
	elseif ( $action == 'file_browser' ) {
		$echo_str = wfu_browse_files($dir);
	}
	elseif ( $action == 'view_log' ) {
		$echo_str = wfu_view_log();
	}
	elseif ( $action == 'rename_file' && $file != "" ) {
		$echo_str = wfu_rename_file_prompt($file, 'file', false);
	}
	elseif ( $action == 'rename_dir' && $file != "" ) {
		$echo_str = wfu_rename_file_prompt($file, 'dir', false);
	}
	elseif ( $action == 'renamefile' && $file != "" ) {
		if ( wfu_rename_file($file, 'file') ) $echo_str = wfu_browse_files($dir);
		else $echo_str = wfu_rename_file_prompt($file, 'file', true);
	}
	elseif ( $action == 'renamedir' && $file != "" ) {
		if ( wfu_rename_file($file, 'dir') ) $echo_str = wfu_browse_files($dir);
		else $echo_str = wfu_rename_file_prompt($file, 'dir', true);
	}
	elseif ( $action == 'delete_file' && $file != "" && $referer != "" ) {
		if ( substr($file, 0, 5) == "list:" ) $file = explode(",", substr($file, 5));
		$echo_str = wfu_delete_file_prompt($file, 'file', $referer);
	}
	elseif ( $action == 'delete_dir' && $file != "" && $referer != "" ) {
		$echo_str = wfu_delete_file_prompt($file, 'dir', $referer);
	}
	elseif ( $action == 'deletefile' && $file != "" ) {
		if ( substr($file, 0, 5) == "list:" ) $file = explode(",", substr($file, 5));
		wfu_delete_file($file, 'file');
		$referer_url = wfu_flatten_path(wfu_get_filepath_from_safe(wfu_sanitize_code($referer)));
		if ( $referer_url === false ) $referer_url = "";
		$match = array();
		preg_match("/\&dir=(.*)/", $referer_url, $match);
		$dir = ( isset($match[1]) ? $match[1] : "" );
		$echo_str = wfu_browse_files($dir);	
	}
	elseif ( $action == 'deletedir' && $file != "" ) {
		wfu_delete_file($file, 'dir');
		$referer_url = wfu_flatten_path(wfu_get_filepath_from_safe(wfu_sanitize_code($referer)));
		if ( $referer_url === false ) $referer_url = "";
		$match = array();
		preg_match("/\&dir=(.*)/", $referer_url, $match);
		$dir = ( isset($match[1]) ? $match[1] : "" );
		$echo_str = wfu_browse_files($dir);	
	}
	elseif ( $action == 'create_dir' ) {
		$echo_str = wfu_create_dir_prompt($dir, false);
	}
	elseif ( $action == 'createdir' ) {
		if ( wfu_create_dir($dir) ) $echo_str = wfu_browse_files($dir);
		else $echo_str = wfu_create_dir_prompt($dir, true);
	}
	elseif ( $action == 'include_file' && $file != "" && $referer != "" ) {
		if ( substr($file, 0, 5) == "list:" ) $file = explode(",", substr($file, 5));
		$echo_str = wfu_include_file_prompt($file, $referer);
	}
	elseif ( $action == 'includefile' && $file != "" ) {
		if ( substr($file, 0, 5) == "list:" ) $file = explode(",", substr($file, 5));
		wfu_include_file($file);
		$referer_url = wfu_flatten_path(wfu_get_filepath_from_safe(wfu_sanitize_code($referer)));
		if ( $referer_url === false ) $referer_url = "";
		$match = array();
		preg_match("/\&dir=(.*)/", $referer_url, $match);
		$dir = ( isset($match[1]) ? $match[1] : "" );
		$echo_str = wfu_browse_files($dir);	
	}
	elseif ( $action == 'file_details' && $file != "" ) {
		$echo_str = wfu_file_details($file, false);
	}
	elseif ( $action == 'edit_filedetails' && $file != "" ) {
		wfu_edit_filedetails($file);
		$echo_str = wfu_file_details($file, false);
	}
	elseif ( $action == 'maintenance_actions' ) {
		$echo_str = wfu_maintenance_actions();
	}
	elseif ( $action == 'sync_db' ) {
		$affected_items = wfu_sync_database();
		$echo_str = wfu_maintenance_actions('Database updated. '.$affected_items.' items where affected.');
	}
	elseif ( $action == 'clean_log_ask' ) {
		$echo_str = wfu_clean_log_prompt();
	}
	elseif ( $action == 'clean_log' ) {
		$ret = wfu_clean_log();
		if ( $ret <= -1 ) $echo_str = wfu_maintenance_actions();
		else $echo_str = wfu_maintenance_actions('Database cleaned. '.$ret.' items where affected.');
	}
	elseif ( $action == 'plugin_settings' ) {
		$echo_str = wfu_manage_settings();	
	}
	elseif ( $action == 'add_shortcode' && $postid != "" && $nonce != "" && $tag != "" ) {
		if ( $_SESSION['wfu_add_shortcode_ticket_for_'.$tag] != $nonce ) $echo_str = wfu_manage_mainmenu();
		elseif ( wfu_add_shortcode($postid, $tag) ) $echo_str = wfu_manage_mainmenu();
		else $echo_str = wfu_manage_mainmenu(WFU_DASHBOARD_ADD_SHORTCODE_REJECTED);
		$_SESSION['wfu_add_shortcode_ticket'] = 'noticket';
	}
	elseif ( $action == 'edit_shortcode' && $data_enc != "" && $tag != "" ) {
		$data = wfu_decode_array_from_string(wfu_get_shortcode_data_from_safe($data_enc));
		if ( $data['post_id'] == "" || wfu_check_edit_shortcode($data) ) wfu_shortcode_composer($data, $tag);
		else $echo_str = wfu_manage_mainmenu(WFU_DASHBOARD_EDIT_SHORTCODE_REJECTED);
	}
	elseif ( $action == 'delete_shortcode' && $data_enc != "" ) {
		$data = wfu_decode_array_from_string(wfu_get_shortcode_data_from_safe($data_enc));
		if ( wfu_check_edit_shortcode($data) ) $echo_str = wfu_delete_shortcode_prompt($data_enc);
		else $echo_str = wfu_manage_mainmenu(WFU_DASHBOARD_DELETE_SHORTCODE_REJECTED);
	}
	elseif ( $action == 'deleteshortcode' && $data_enc != "" ) {
		$data = wfu_decode_array_from_string(wfu_get_shortcode_data_from_safe($data_enc));
		if ( wfu_check_edit_shortcode($data) ) {
			if ( wfu_delete_shortcode($data) ) wfu_clear_shortcode_data_from_safe($data_enc);
			$echo_str = wfu_manage_mainmenu();
		}
		else $echo_str = wfu_manage_mainmenu(WFU_DASHBOARD_DELETE_SHORTCODE_REJECTED);
	}
	else {
		$echo_str = wfu_manage_mainmenu();
	}

	echo $echo_str;
}

function wfu_manage_mainmenu($message = '') {
	if ( !current_user_can( 'manage_options' ) ) return;
	
	//get php version
	$php_version = preg_replace("/-.*/", "", phpversion());

	$plugin_options = wfu_decode_plugin_options(get_option( "wordpress_file_upload_options" ));
	
	$echo_str = '<div class="wrap">';
	$echo_str .= "\n\t".'<h2>Wordpress File Upload Control Panel</h2>';
	if ( $message != '' ) {
		$echo_str .= "\n\t".'<div class="updated">';
		$echo_str .= "\n\t\t".'<p>'.$message.'</p>';
		$echo_str .= "\n\t".'</div>';
	}
	$echo_str .= "\n\t".'<div style="margin-top:20px;">';
	$echo_str .= wfu_generate_dashboard_menu("\n\t\t", "Main");
	$echo_str .= "\n\t\t".'<h3 style="margin-bottom: 10px;">Status';
	if ( $plugin_options["altserver"] == "1" && substr(trim(WFU_VAR("WFU_ALT_IPTANUS_SERVER")), 0, 5) == "http:" ) {
		$echo_str .= '<div style="display: inline-block; margin-left:20px;" title="'.WFU_WARNING_ALT_IPTANUS_SERVER_ACTIVATED.'"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 200 800" version="1.1" style="background:darkorange; border-radius:13px; padding:2px; vertical-align:middle; border: 1px solid silver;"><path d="M 110,567 L 90,567 L 42,132 C 40,114 40,100 40,90 C 40,70 45,49 56,35 C 70,22 83,15 100,15 C 117,15 130,22 144,35 C 155,49 160,70 160,90 C 160,100 160,114 158,132 z M 100,640 A 60,60 0 1,1 100,760 A 60,60 0 1,1 100,640 z"/></svg></div>';
	}
	$echo_str .= '</h3>';
	$echo_str .= "\n\t\t".'<table class="form-table">';
	$echo_str .= "\n\t\t\t".'<tbody>';
	//plugin edition
	$echo_str .= "\n\t\t\t\t".'<tr>';
	$echo_str .= "\n\t\t\t\t\t".'<th scope="row">';
	$echo_str .= "\n\t\t\t\t\t\t".'<label style="cursor:default;">Edition</label>';
	$echo_str .= "\n\t\t\t\t\t".'</th>';
	$echo_str .= "\n\t\t\t\t\t".'<td style="width:100px; vertical-align:top;">';
	$echo_str .= "\n\t\t\t\t\t\t".'<label style="font-weight:bold; cursor:default;">Free</label>';
	$echo_str .= "\n\t\t\t\t\t".'</td>';
	$echo_str .= "\n\t\t\t\t\t".'<td>';
	$echo_str .= "\n\t\t\t\t\t\t".'<div style="display:inline-block; background-color:bisque; padding:0 0 0 4px; border-left:3px solid lightcoral;">';
	$echo_str .= "\n\t\t\t\t\t\t\t".'<label style="cursor:default;">Consider </label><a href="'.WFU_PRO_VERSION_URL.'">Upgrading</a><label style="cursor:default;"> to the Professional Version. </label>';
	$echo_str .= "\n\t\t\t\t\t\t\t".'<button onclick="if (this.innerText == \'See why >>\') {this.innerText = \'<< Close\'; document.getElementById(\'wfu_version_comparison\').style.display = \'inline-block\';} else {this.innerText = \'See why >>\'; document.getElementById(\'wfu_version_comparison\').style.display = \'none\';}">See why >></button>';
	$echo_str .= "\n\t\t\t\t\t\t".'</div>';
	$echo_str .= "\n\t\t\t\t\t\t".'<br /><div id="wfu_version_comparison" style="display:none; background-color:lightyellow; border:1px solid yellow; margin:10px 0; padding:10px;">';
	$echo_str .= "\n\t\t\t\t\t\t\t".'<img src="'.WFU_IMAGE_VERSION_COMPARISON.'" style="display:block; margin-bottom:6px;" />';
	$echo_str .= "\n\t\t\t\t\t\t\t".'<a class="button-primary" href="'.WFU_PRO_VERSION_URL.'">Go for the PRO version</a>';
	$echo_str .= "\n\t\t\t\t\t\t".'</div>';
	$echo_str .= "\n\t\t\t\t\t".'</td>';
	$echo_str .= "\n\t\t\t\t".'</tr>';
	//plugin version
	$echo_str .= "\n\t\t\t\t".'<tr>';
	$echo_str .= "\n\t\t\t\t\t".'<th scope="row">';
	$echo_str .= "\n\t\t\t\t\t\t".'<label style="cursor:default;">Version</label>';
	$echo_str .= "\n\t\t\t\t\t".'</th>';
	$echo_str .= "\n\t\t\t\t\t".'<td style="width:100px;">';
	$cur_version = wfu_get_plugin_version();
	$echo_str .= "\n\t\t\t\t\t\t".'<label style="font-weight:bold; cursor:default;">'.$cur_version.'</label>';
	$echo_str .= "\n\t\t\t\t\t".'</td>';
	$echo_str .= "\n\t\t\t\t\t".'<td>';
	$lat_version = wfu_get_latest_version();
	$ret = wfu_compare_versions($cur_version, $lat_version);
	if ( $lat_version == "" && WFU_VAR("WFU_DISABLE_VERSION_CHECK") != "true" ) {
		$echo_str .= "\n\t\t\t\t\t\t".'<div style="display:inline-block; background-color:transparent; padding:0 0 0 4px; color:red;">';
		$echo_str .= "\n\t\t\t\t\t\t\t".'<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 200 800" version="1.1" style="background:transparent; border-radius:13px; padding:2px; vertical-align:middle; border: 2px solid red; fill:red;"><path d="M 110,567 L 90,567 L 42,132 C 40,114 40,100 40,90 C 40,70 45,49 56,35 C 70,22 83,15 100,15 C 117,15 130,22 144,35 C 155,49 160,70 160,90 C 160,100 160,114 158,132 z M 100,640 A 60,60 0 1,1 100,760 A 60,60 0 1,1 100,640 z"/></svg>';
		$echo_str .= "\n\t\t\t\t\t\t\t".'<label style="cursor:default;">'.WFU_WARNING_IPTANUS_SERVER_UNREACHABLE.'</label>';
		$echo_str .= "\n\t\t\t\t\t\t".'</div>';
	}
	elseif ( $ret['status'] && $ret['result'] == 'lower' ) {
		$echo_str .= "\n\t\t\t\t\t\t".'<div style="display:inline-block; background-color:bisque; padding:0 0 0 4px; border-left:3px solid lightcoral;">';
		$echo_str .= "\n\t\t\t\t\t\t\t".'<label style="cursor:default;">Version <strong>'.$lat_version.'</strong> of the plugin is available. Go to Plugins page of your Dashboard to update to the latest version.</label>';
		if ( $ret['custom'] ) $echo_str .= '<label style="cursor:default; color: purple;"> <em>Please note that you are using a custom version of the plugin. If you upgrade to the newest version, custom changes will be lost.</em></label>';
		$echo_str .= "\n\t\t\t\t\t\t".'</div>';
	}
	elseif ( $ret['status'] && $ret['result'] == 'equal' ) {
		$echo_str .= "\n\t\t\t\t\t\t".'<div style="display:inline-block; background-color:rgb(220,255,220); padding:0 0 0 4px; border-left:3px solid limegreen;">';
		$echo_str .= "\n\t\t\t\t\t\t\t".'<label style="cursor:default;">You have the latest version.</label>';
		if ( $ret['custom'] ) $echo_str .= '<label style="cursor:default; color: purple;"> <em>(Please note that your version is custom)</em></label>';
		$echo_str .= "\n\t\t\t\t\t\t".'</div>';
	}
	$echo_str .= "\n\t\t\t\t\t".'</td>';
	$echo_str .= "\n\t\t\t\t".'</tr>';
	//server environment
	$php_env = wfu_get_server_environment();
	$echo_str .= "\n\t\t\t\t".'<tr>';
	$echo_str .= "\n\t\t\t\t\t".'<th scope="row">';
	$echo_str .= "\n\t\t\t\t\t\t".'<label style="cursor:default;">Server Environment</label>';
	$echo_str .= "\n\t\t\t\t\t".'</th>';
	$echo_str .= "\n\t\t\t\t\t".'<td style="width:100px;">';
	if ( $php_env == '64bit' ) $echo_str .= "\n\t\t\t\t\t\t".'<label style="font-weight:bold; cursor:default;">64bit</label></td><td><label style="font-weight:normal; font-style:italic; cursor:default;">(Your server supports files up to 1 Exabyte, practically unlimited)</label>';
	if ( $php_env == '32bit' ) $echo_str .= "\n\t\t\t\t\t\t".'<label style="font-weight:bold; cursor:default;">32bit</label></td><td><label style="font-weight:normal; font-style:italic; cursor:default;">(Your server does not support files larger than 2GB)</label>';
	if ( $php_env == '' ) $echo_str .= "\n\t\t\t\t\t\t".'<label style="font-weight:bold; cursor:default;">Unknown</label></td><td><label style="font-weight:normal; font-style:italic; cursor:default;">(The maximum file size supported by the server cannot be determined)</label>';
	$echo_str .= "\n\t\t\t\t\t".'</td>';
	$echo_str .= "\n\t\t\t\t".'</tr>';
	$echo_str .= "\n\t\t\t\t".'<tr>';
	$echo_str .= "\n\t\t\t\t\t".'<th scope="row">';
	$echo_str .= "\n\t\t\t\t\t\t".'<label style="cursor:default;">PHP Version</label>';
	$echo_str .= "\n\t\t\t\t\t".'</th>';
	$echo_str .= "\n\t\t\t\t\t".'<td style="width:100px;">';
	$cur_version = wfu_get_plugin_version();
	$echo_str .= "\n\t\t\t\t\t\t".'<label style="font-weight:bold; cursor:default;">'.$php_version.'</label>';
	$echo_str .= "\n\t\t\t\t\t".'</td>';
	$echo_str .= "\n\t\t\t\t\t".'<td>';
	$echo_str .= "\n\t\t\t\t\t".'</td>';
	$echo_str .= "\n\t\t\t\t".'</tr>';
	$echo_str .= "\n\t\t\t\t".'<tr>';
	$echo_str .= "\n\t\t\t\t\t".'<th scope="row">';
	$echo_str .= "\n\t\t\t\t\t\t".'<label style="cursor:default;">Release Notes</label>';
	$echo_str .= "\n\t\t\t\t\t".'</th>';
	$echo_str .= "\n\t\t\t\t\t".'<td colspan="2" style="width:100px;">';
	$rel_path = ABSWPFILEUPLOAD_DIR.'release_notes.txt';
	$rel_notes = '';
	if ( file_exists($rel_path) ) $rel_notes = file_get_contents($rel_path);
	$echo_str .= "\n\t\t\t\t\t\t".'<div style="text-align:justify;">'.$rel_notes.'</div>';
	$echo_str .= "\n\t\t\t\t\t".'</td>';
	$echo_str .= "\n\t\t\t\t".'</tr>';
	$echo_str .= "\n\t\t\t".'</tbody>';
	$echo_str .= "\n\t\t".'</table>';

	$echo_str .= wfu_manage_instances();

	$echo_str .= "\n\t".'</div>';
	$echo_str .= "\n".'</div>';
	
	echo $echo_str;
}

function wfu_generate_dashboard_menu($dlp, $active) {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	$siteurl = site_url();
	
	$echo_str = $dlp.'<h2 class="nav-tab-wrapper" style="margin-bottom:40px;">';
	$echo_str .= $dlp."\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload" class="nav-tab'.( $active == "Main" ? ' nav-tab-active' : '' ).'" title="Main">Main</a>';
	$echo_str .= $dlp."\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&amp;action=plugin_settings" class="nav-tab'.( $active == "Settings" ? ' nav-tab-active' : '' ).'" title="Settings">Settings</a>';
	$echo_str .= $dlp."\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&amp;action=file_browser" class="nav-tab'.( $active == "File Browser" ? ' nav-tab-active' : '' ).'" title="File browser">File Browser</a>';
	$echo_str .= $dlp."\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&amp;action=view_log" class="nav-tab'.( $active == "View Log" ? ' nav-tab-active' : '' ).'" title="View log">View Log</a>';
	$echo_str .= $dlp."\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&amp;action=maintenance_actions" class="nav-tab'.( $active == "Maintenance Actions" ? ' nav-tab-active' : '' ).'" title="Maintenance Actions">Maintenance Actions</a>';
	$echo_str .= $dlp.'</h2>';
	
	return $echo_str;
}

function wfu_test_function() {
	$str = 'hello τεστ A piñata Ёнвидюнт';
	
	echo "pass: ".$str;
}

function wfu_construct_post_list($posts) {
	$ids = array();
	$list = array();
	$id_keys = array();
	//construct item indices
	foreach ( $posts as $key => $post ) {
		if ( !array_key_exists($post->post_type, $ids) ) {
			$ids[$post->post_type] = array();
			$list[$post->post_type] = array();
		}
		array_push($ids[$post->post_type], $post->ID);
		$id_keys[$post->ID] = $key;
	}
	//create post list in tree order; items are sorted by post status (publish, private, draft) and then by title
	$i = 0;
	while ( $i < count($posts) ) {
		$post = $posts[$i];
		//find topmost element in family tree
		$tree = array( $post->ID );
		$topmost = $post;
		$par_id = $topmost->post_parent;
		while ( in_array($par_id, $ids[$post->post_type]) ) {
			$topmost = $posts[$id_keys[$par_id]];
			array_splice($tree, 0, 0, $par_id);
			$par_id = $topmost->post_parent;
		}
		//find which needs to be processed
		$level = 0;
		$host = &$list[$post->post_type];
		foreach ( $tree as $process_id ) {
			$found_key = -1;
			foreach ( $host as $key => $item )
				if ( $item['id'] == $process_id ) {
					$found_key = $key;
					break;
				}
			if ( $found_key == -1 ) break;
			$level++;
			$host = &$host[$found_key]['children'];
		}
		if ( $found_key == -1 ) {
			$processed = $posts[$id_keys[$process_id]];
			//add the processed item in the right position in children's list
			$pos = 0;
			$status = ( $processed->post_status == 'publish' ? 0 : ( $processed->post_status == 'private' ? 1 : 2 ) );
			foreach ($host as $item) {
				if ( $status < $item['status'] ) break;
				if ( $status == $item['status'] && strcmp($processed->post_title, $item['title']) < 0 ) break;
				$pos++;
			}
			$new_item = array(
				'id'		=> $process_id,
				'title' 	=> $processed->post_title,
				'status' 	=> $status,
				'level' 	=> $level,
				'children' 	=> array()
			);
			array_splice($host, $pos, 0, array($new_item));
		}
		//advance index if we have finished processing all the tree
		if ( $process_id == $post->ID ) $i++;
	}
	return $list;
}

function wfu_flatten_post_list($list) {
	$flat = array();
	if ( !is_array($list) ) return $flat;
	foreach( $list as $item ) {
		$flat_item = array(
			'id'		=> $item['id'],
			'title'		=> $item['title'],
			'status'	=> $item['status'],
			'level'		=> $item['level']
		);
		array_push($flat, $flat_item);
		$flat = array_merge($flat, wfu_flatten_post_list($item['children']));
	}
	return $flat;
}

function wfu_manage_instances() {
	$echo_str = wfu_manage_instances_of_shortcode('wordpress_file_upload', 'Uploader Instances', 'uploader', 1);
	
	return $echo_str;
}

function wfu_manage_instances_of_shortcode($tag, $title, $slug, $inc) {
	global $wp_registered_widgets, $wp_registered_sidebars;
	
	$siteurl = site_url();
	$args = array( 'post_type' => array( "post", "page" ), 'post_status' => "publish,private,draft", 'posts_per_page' => -1 );
	$args = apply_filters("_wfu_get_posts", $args, "manage_instances");
	$posts = get_posts($args);
	$wfu_shortcodes = array();
	//get shortcode instances from page/posts 
	foreach ( $posts as $post ) {
		$ret = wfu_get_content_shortcodes($post, $tag);
		if ( $ret !== false ) $wfu_shortcodes = array_merge($wfu_shortcodes, $ret);
	}
	//get shortcode instances from sidebars
	$data = array();
	$widget_base = $tag.'_widget';
	if ( is_array($wp_registered_widgets) ) {
		foreach ( $wp_registered_widgets as $id => $widget ) {
			if ( substr($id, 0, strlen($widget_base)) == $widget_base ) {
				$widget_obj = ( isset($widget['callback']) ? ( isset($widget['callback'][0]) ? ( $widget['callback'][0] instanceof WP_Widget ? $widget['callback'][0] : false ) : false ) : false );
				$widget_sidebar = is_active_widget(false, $id, $widget_base);
				if ( $widget_obj !== false && $widget_sidebar !== false ) {
					if ( isset($wp_registered_sidebars[$widget_sidebar]) && isset($wp_registered_sidebars[$widget_sidebar]['name']) ) $widget_sidebar = $wp_registered_sidebars[$widget_sidebar]['name'];
					$data['post_id'] = "";
					$data['post_hash'] = "";
					$data['shortcode'] = $widget_obj->shortcode();
					$data['position'] = 0;
					$data['widgetid'] = $id;
					$data['sidebar'] = $widget_sidebar;
					array_push($wfu_shortcodes, $data);
				}
			}
		}
	}

	$list = wfu_construct_post_list($posts);
	$pagelist = wfu_flatten_post_list($list["page"]);
	$postlist = wfu_flatten_post_list($list["post"]);

	$echo_str = "\n\t\t".'<h3 style="margin-bottom: 10px; margin-top: 40px;">'.$title.'</h3>';
	$onchange_js = 'document.getElementById(\'wfu_add_plugin_ok_'.$inc.'\').disabled = !((document.getElementById(\'wfu_page_type_'.$inc.'\').value == \'page\' && document.getElementById(\'wfu_page_list_'.$inc.'\').value != \'\') || (document.getElementById(\'wfu_page_type_'.$inc.'\').value == \'post\' && document.getElementById(\'wfu_post_list_'.$inc.'\').value != \'\'));';
	$no_shortcodes = ( count($wfu_shortcodes) == 0 );
	$echo_str .= "\n\t\t".'<div id="wfu_add_plugin_button_'.$inc.'" style="'. ( !$no_shortcodes ? '' : 'color:blue; font-weight:bold; font-size:larger;' ).'margin-bottom: 20px; margin-top: 10px;">';
	$addbutton_pre = ( !$no_shortcodes ? '' : '<label>Press </label>');
	$addbutton_post = ( !$no_shortcodes ? '' : '<label> to get started and add the '.$slug.' in a page</label>');
	$echo_str .= "\n\t\t\t".$addbutton_pre.'<button onclick="document.getElementById(\'wfu_add_plugin_button_'.$inc.'\').style.display = \'none\'; document.getElementById(\'wfu_add_plugin_'.$inc.'\').style.display = \'inline-block\'; '.$onchange_js.'">'.( !$no_shortcodes ? 'Add Plugin Instance' : 'here' ).'</button>'.$addbutton_post;
	$echo_str .= "\n\t\t".'</div>';
	$echo_str .= "\n\t\t".'<div id="wfu_add_plugin_'.$inc.'" style="margin-bottom: 20px; margin-top: 10px; position:relative; display:none;">';
	$echo_str .= "\n\t\t\t".'<div id="wfu_add_plugin_'.$inc.'_overlay" style="position:absolute; top:0; left:0; width:100%; height:100%; background-color:rgba(255,255,255,0.8); border:none; display:none;">';
	$echo_str .= "\n\t\t\t\t".'<table style="background:none; border:none; margin:0; padding:0; line-height:1; border-spacing:0; width:100%; height:100%; table-layout:fixed;"><tbody><tr><td style="text-align:center; vertical-align:middle;"><div style="display:inline-block;"><span class="spinner" style="opacity:1; float:left; margin:0; display:inline;"></span><label style="margin-left:4px;">please wait...</label></div></td></tr></tbody></table>';
	$echo_str .= "\n\t\t\t".'</div>';
	$echo_str .= "\n\t\t\t".'<label>Add '.$slug.' to </label><select id="wfu_page_type_'.$inc.'" onchange="document.getElementById(\'wfu_page_list_'.$inc.'\').style.display = (this.value == \'page\' ? \'inline-block\' : \'none\'); document.getElementById(\'wfu_post_list_'.$inc.'\').style.display = (this.value == \'post\' ? \'inline-block\' : \'none\'); '.$onchange_js.'"><option value="page" selected="selected">Page</option><option value="post">Post</option></select>';
	$echo_str .= "\n\t\t\t".'<select id="wfu_page_list_'.$inc.'" style="margin-bottom:6px;" onchange="'.$onchange_js.'">';
	$echo_str .= "\n\t\t\t\t".'<option value=""></option>';
	foreach ( $pagelist as $item )
		$echo_str .= "\n\t\t\t\t".'<option value="'.$item['id'].'">'.str_repeat('&nbsp;', 4 * $item['level']).( $item['status'] == 1 ? '[Private]' : ( $item['status'] == 2 ? '[Draft]' : '' ) ).$item['title'].'</option>';
	$echo_str .= "\n\t\t\t".'</select>';
	$echo_str .= "\n\t\t\t".'<select id="wfu_post_list_'.$inc.'" style="display:none; margin-bottom:6px;" onchange="'.$onchange_js.'">';
	$echo_str .= "\n\t\t\t\t".'<option value=""></option>';
	foreach ( $postlist as $item )
		$echo_str .= "\n\t\t\t\t".'<option value="'.$item['id'].'">'.str_repeat('&nbsp;', 4 * $item['level']).( $item['status'] == 1 ? '[Private]' : ( $item['status'] == 2 ? '[Draft]' : '' ) ).$item['title'].'</option>';
	$echo_str .= "\n\t\t\t".'</select><br />';
	$add_shortcode_ticket = wfu_create_random_string(16);
	$_SESSION['wfu_add_shortcode_ticket_for_'.$tag] = $add_shortcode_ticket;
	$echo_str .= "\n\t\t".'<button id="wfu_add_plugin_ok_'.$inc.'" style="float:right; margin: 0 2px 0 4px;" disabled="disabled" onclick="document.getElementById(\'wfu_add_plugin_'.$inc.'_overlay\').style.display = \'block\'; window.location = \''.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&amp;action=add_shortcode&amp;tag='.$tag.'&amp;postid=\' + (document.getElementById(\'wfu_page_type_'.$inc.'\').value == \'page\' ? document.getElementById(\'wfu_page_list_'.$inc.'\').value : document.getElementById(\'wfu_post_list_'.$inc.'\').value) + \'&amp;nonce='.$add_shortcode_ticket.'\';">Ok</button>';
	$echo_str .= "\n\t\t".'<button style="float:right;" onclick="document.getElementById(\'wfu_page_type_'.$inc.'\').value = \'page\'; document.getElementById(\'wfu_page_list_'.$inc.'\').value = \'\'; document.getElementById(\'wfu_post_list_'.$inc.'\').value = \'\'; document.getElementById(\'wfu_add_plugin_'.$inc.'\').style.display = \'none\'; document.getElementById(\'wfu_add_plugin_button_'.$inc.'\').style.display = \'inline-block\';">Cancel</button>';
	$echo_str .= "\n\t\t".'</div>';
	$echo_str .= "\n\t\t".'<table class="wp-list-table widefat fixed striped">';
	$echo_str .= "\n\t\t\t".'<thead>';
	$echo_str .= "\n\t\t\t\t".'<tr>';
	$echo_str .= "\n\t\t\t\t\t".'<th scope="col" width="5%" style="text-align:center;">';
	$echo_str .= "\n\t\t\t\t\t\t".'<label>#</label>';
	$echo_str .= "\n\t\t\t\t\t".'</th>';
//	$echo_str .= "\n\t\t\t\t\t".'<th scope="col" width="10%" style="text-align:center;">';
//	$echo_str .= "\n\t\t\t\t\t\t".'<label>ID</label>';
//	$echo_str .= "\n\t\t\t\t\t".'</th>';
	$echo_str .= "\n\t\t\t\t\t".'<th scope="col" width="10%" style="text-align:center;">';
	$echo_str .= "\n\t\t\t\t\t\t".'<label>Contained In</label>';
	$echo_str .= "\n\t\t\t\t\t".'</th>';
	$echo_str .= "\n\t\t\t\t\t".'<th scope="col" width="30%" style="text-align:center;">';
	$echo_str .= "\n\t\t\t\t\t\t".'<label>Page/Post Title</label>';
	$echo_str .= "\n\t\t\t\t\t".'</th>';
	$echo_str .= "\n\t\t\t\t\t".'<th scope="col" width="45%" style="text-align:center;">';
	$echo_str .= "\n\t\t\t\t\t\t".'<label>Shortcode</label>';
	$echo_str .= "\n\t\t\t\t\t".'</th>';
	$echo_str .= "\n\t\t\t\t".'</tr>';
	$echo_str .= "\n\t\t\t".'</thead>';
	$echo_str .= "\n\t\t\t".'<tbody>';
	$i = 1;
	foreach ( $wfu_shortcodes as $key => $data ) {
		$widget_id = ( isset($data['widgetid']) ? $data['widgetid'] : '' );
		if ( $widget_id == "" ) {
			$id = $data['post_id'];
			$posttype_obj = get_post_type_object(get_post_type($id));
			$type = ( $posttype_obj ? $posttype_obj->labels->singular_name : "" );
			$title = get_the_title($id);
			if ( trim($title) == "" ) $title = 'ID: '.$id;
		}
		else {
			$type = 'Sidebar';
			$title = $data['sidebar'];
		}
		$data_enc = wfu_safe_store_shortcode_data(wfu_encode_array_to_string($data));
		$echo_str .= "\n\t\t\t\t".'<tr onmouseover="var actions=document.getElementsByName(\'wfu_shortcode_actions_'.$inc.'\'); for (var i=0; i<actions.length; i++) {actions[i].style.visibility=\'hidden\';} document.getElementById(\'wfu_shortcode_actions_'.$inc.'_'.$i.'\').style.visibility=\'visible\'" onmouseout="var actions=document.getElementsByName(\'wfu_shortcode_actions_'.$inc.'\'); for (var i=0; i<actions.length; i++) {actions[i].style.visibility=\'hidden\';}">';
		$echo_str .= "\n\t\t\t\t\t".'<td style="padding: 5px 5px 5px 10px; text-align:center;">';
		$echo_str .= "\n\t\t\t\t\t\t".'<a class="row-title" href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&action=edit_shortcode&tag='.$tag.'&data='.$data_enc.'" title="Instance #'.$i.'">Instance '.$i.'</a>';
		$echo_str .= "\n\t\t\t\t\t\t".'<div id="wfu_shortcode_actions_'.$inc.'_'.$i.'" name="wfu_shortcode_actions_'.$inc.'" style="visibility:hidden;">';
		$echo_str .= "\n\t\t\t\t\t\t\t".'<span>';
		$echo_str .= "\n\t\t\t\t\t\t\t\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&action=edit_shortcode&tag='.$tag.'&data='.$data_enc.'" title="Edit this shortcode">Edit</a>';
		$echo_str .= "\n\t\t\t\t\t\t\t\t".' | ';
		$echo_str .= "\n\t\t\t\t\t\t\t".'</span>';
		$echo_str .= "\n\t\t\t\t\t\t\t".'<span>';
		$echo_str .= "\n\t\t\t\t\t\t\t\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&action=delete_shortcode&data='.$data_enc.'" title="Delete this shortcode">Delete</a>';
		$echo_str .= "\n\t\t\t\t\t\t\t".'</span>';
		$echo_str .= "\n\t\t\t\t\t\t".'</div>';
		$echo_str .= "\n\t\t\t\t\t".'</td>';
//		$echo_str .= "\n\t\t\t\t\t".'<td style="padding: 5px 5px 5px 10px; text-align:center;">'.$id.'</td>';
		$echo_str .= "\n\t\t\t\t\t".'<td style="padding: 5px 5px 5px 10px; text-align:center;">'.$type.'</td>';
		$echo_str .= "\n\t\t\t\t\t".'<td style="padding: 5px 5px 5px 10px; text-align:center;">'.$title.'</td>';
		$echo_str .= "\n\t\t\t\t\t".'<td style="padding: 5px 5px 5px 10px; text-align:left;">';
		$echo_str .= "\n\t\t\t\t\t\t".'<textarea rows="3" disabled="disabled" style="width:100%;">'.trim($data['shortcode']).'</textarea>';
		$echo_str .= "\n\t\t\t\t\t".'</td>';
		$echo_str .= "\n\t\t\t\t".'</tr>';
		$i++;
	}
	$echo_str .= "\n\t\t\t".'</tbody>';
	$echo_str .= "\n\t\t".'</table>';
	
	return $echo_str;
}

function wfu_get_content_shortcodes($post, $tag) {
	global $shortcode_tags;
	$found_shortcodes = array();
	$content = $post->post_content;
	if ( false === strpos( $content, '[' ) ) return false;
	$hash = hash('md5', $content);

	if ( array_key_exists( $tag, $shortcode_tags ) ) wfu_match_shortcode_nested($tag, $post, $hash, $content, 0, $found_shortcodes);

	if ( count($found_shortcodes) == 0 ) return false;
	return $found_shortcodes;
}

function wfu_match_shortcode_nested($tag, $post, $hash, $content, $position, &$found_shortcodes) {
	if ( false === strpos( $content, '[' ) ) return false;
	preg_match_all( '/' . get_shortcode_regex() . '/s', $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE );
	if ( empty( $matches ) ) return false;
	foreach ( $matches as $shortcode ) {
		if ( $tag === $shortcode[2][0] ) {
			$data['post_id'] = $post->ID;
			$data['post_hash'] = $hash;
			$data['shortcode'] = $shortcode[0][0];
			$data['position'] = (int)$shortcode[0][1] + (int)$position;
			array_push($found_shortcodes, $data);
		}
		wfu_match_shortcode_nested($tag, $post, $hash, $shortcode[5][0], $shortcode[5][1] + (int)$position, $found_shortcodes);
	}
	return false;
}

function wfu_check_edit_shortcode($data) {
	$post = get_post($data['post_id']);
	$content = $post->post_content;
	$hash = hash('md5', $content);
	
	return ( $hash == $data['post_hash'] );
}

function wfu_add_shortcode($postid, $tag) {
	$post = get_post($postid);
	$new_content = '['.$tag.']'.$post->post_content;
	$new_post = array( 'ID' => $postid, 'post_content' => $new_content );
	return ( wp_update_post( wfu_slash($new_post) ) === 0 ? false : true );
}

function wfu_replace_shortcode($data, $new_shortcode) {
	$post = get_post($data['post_id']);
	$new_content = substr($post->post_content, 0, $data['position']).$new_shortcode.substr($post->post_content, (int)$data['position'] + strlen($data['shortcode']));
	$new_post = array( 'ID' => $data['post_id'], 'post_content' => $new_content );
	return ( wp_update_post( wfu_slash($new_post) ) === 0 ? false : true );
}

function wfu_delete_shortcode_prompt($data_enc) {
	$siteurl = site_url();
	$data = wfu_decode_array_from_string(wfu_get_shortcode_data_from_safe($data_enc));
	$postid = $data['post_id'];
	$echo_str = "\n".'<div class="wrap">';
	$echo_str .= "\n\t".'<h2>Wordpress File Upload Control Panel</h2>';
	$echo_str .= "\n\t".'<div style="margin-top:20px;">';
	$echo_str .= "\n\t\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&amp;action=manage_mainmenu" class="button" title="go back">Go to Main Menu</a>';
	$echo_str .= "\n\t".'</div>';
	$echo_str .= "\n\t".'<h2 style="margin-bottom: 10px; margin-top: 20px;">Delete Shortcode</h2>';
	$echo_str .= "\n\t".'<form enctype="multipart/form-data" name="deletefile" id="deleteshortcode" method="post" action="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload" class="validate">';
	$echo_str .= "\n\t\t".'<input type="hidden" name="action" value="deleteshortcode">';
	$echo_str .= "\n\t\t".'<input type="hidden" name="data" value="'.$data_enc.'">';
	$echo_str .= "\n\t\t".'<label>Are you sure that you want to delete shortcode for <strong>'.get_post_type($postid).' "'.get_the_title($postid).'" ('.$postid.') Position '.$data['position'].'</strong> ?</label><br/>';
	$echo_str .= "\n\t\t".'<p class="submit">';
	$echo_str .= "\n\t\t\t".'<input type="submit" class="button-primary" name="submit" value="Delete">';
	$echo_str .= "\n\t\t\t".'<input type="submit" class="button-primary" name="submit" value="Cancel">';
	$echo_str .= "\n\t\t".'</p>';
	$echo_str .= "\n\t".'</form>';
	$echo_str .= "\n".'</div>';
	return $echo_str;
}

function wfu_delete_shortcode($data) {
	//check if user is allowed to perform this action
	if ( !current_user_can( 'manage_options' ) ) return false;

	$res = true;
	if ( isset($_POST['submit']) ) {
		if ( $_POST['submit'] == "Delete" ) {
			$res = wfu_replace_shortcode($data, '');
		}
	}
	return $res;
}

function wfu_media_editor_properties() {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	$plugin_options = wfu_decode_plugin_options(get_option( "wordpress_file_upload_options" ));
	if ( $plugin_options["mediacustom"] != "1" ) return;
	
	$post = get_post();
	$meta = wp_get_attachment_metadata( $post->ID );
	
	$echo_str = "";
	if ( isset($meta["WFU User Data"]) && is_array($meta["WFU User Data"]) ) {
		foreach ( $meta["WFU User Data"] as $label => $value )
			$echo_str .= '<div class="misc-pub-section misc-pub-userdata">'.$label.': <strong>'.$value.'</strong></div>';
	}
	echo $echo_str;
}

?>
