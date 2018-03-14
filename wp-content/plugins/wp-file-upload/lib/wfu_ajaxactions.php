<?php

function wfu_ajax_action_send_email_notification() {
	$_POST = stripslashes_deep($_POST);

	$user = wp_get_current_user();
	if ( 0 == $user->ID ) $is_admin = false;
	else $is_admin = current_user_can('manage_options');

	$params_index = sanitize_text_field($_POST['params_index']);
	$session_token = sanitize_text_field($_POST['session_token']);

	$arr = wfu_get_params_fields_from_index($params_index, $session_token);
	//check referer using server sessions to avoid CSRF attacks
	$sid = $arr['shortcode_id'];
	if ( $_SESSION["wfu_token_".$sid] != $session_token ) die();
	if ( $user->user_login != $arr['user_login'] ) die();

	$params_str = get_option('wfu_params_'.$arr['unique_id']);
	$params = wfu_decode_array_from_string($params_str);
	
	//check whether email notifications are activated
	if ( $params["notify"] != "true" ) die();
	
	$uniqueid = ( isset($_POST['uniqueuploadid_'.$sid]) ? sanitize_text_field($_POST['uniqueuploadid_'.$sid]) : "" );
	//uniqueid cannot be empty and cannot be "no-ajax"
	if ( $uniqueid == "" || $uniqueid == "no-ajax" ) die();

	//retrieve the list of uploaded files from session
	$target_path_list = array();
	$all_files_count = 0;
	if ( isset($_SESSION["filedata_".$uniqueid]) && is_array($_SESSION["filedata_".$uniqueid]) ) {
		$all_files_count = count($_SESSION["filedata_".$uniqueid]);
		foreach ( $_SESSION["filedata_".$uniqueid] as $file ) {
			if ( $file["upload_result"] == "success" || $file["upload_result"] == "warning" )
				array_push($target_path_list, $file["filepath"]);
		}
	}
	$uploaded_files_count = count($target_path_list);
	$nofileupload = ( $params["allownofile"] == "true" && $all_files_count == 0 );
	$force_notifications = ( WFU_VAR("WFU_FORCE_NOTIFICATIONS") == "true" );
	
	//in case no files have been uploaded and this is not a nofileupload
	//situation and Force Email Notifications option is not active then abort
	if ( $uploaded_files_count == 0 && !$nofileupload && !$force_notifications ) die();
	
	/* initialize return array */
	$params_output_array["version"] = "full";
	$params_output_array["general"]['shortcode_id'] = $params["uploadid"];
	$params_output_array["general"]['unique_id'] = $uniqueid;
	$params_output_array["general"]['state'] = 0;
	$params_output_array["general"]['files_count'] = 0;
	$params_output_array["general"]['update_wpfilebase'] = "";
	$params_output_array["general"]['redirect_link'] = "";
	$params_output_array["general"]['upload_finish_time'] = "";
	$params_output_array["general"]['message'] = "";
	$params_output_array["general"]['message_type'] = "";
	$params_output_array["general"]['admin_messages']['wpfilebase'] = "";
	$params_output_array["general"]['admin_messages']['notify'] = "";
	$params_output_array["general"]['admin_messages']['redirect'] = "";
	$params_output_array["general"]['admin_messages']['other'] = "";
	$params_output_array["general"]['errors']['wpfilebase'] = "";
	$params_output_array["general"]['errors']['notify'] = "";
	$params_output_array["general"]['errors']['redirect'] = "";
	$params_output_array["general"]['color'] = "black";
	$params_output_array["general"]['bgcolor'] = "#F5F5F5";
	$params_output_array["general"]['borcolor'] = "#D3D3D3";
	$params_output_array["general"]['notify_by_email'] = 0;
	$params_output_array["general"]['fail_message'] = WFU_ERROR_UNKNOWN;

	//retrieve userdata, first get default userdata from $params
	$userdata_fields = $params["userdata_fields"];
	//initialize default values
	foreach ( $userdata_fields as $userdata_key => $userdata_field )
		$userdata_fields[$userdata_key]["value"] = "";
	//then retrieve userdata from session if files exist
	if ( $all_files_count > 0 ) {
		foreach ( $_SESSION["filedata_".$uniqueid] as $file ) {
			if ( isset($file["user_data"]) ) {
				$userdata_fields = array();
				foreach ( $file["user_data"] as $userdata_key => $userdata_field )
					$userdata_fields[$userdata_key] = array( "label" => $userdata_field["label"], "value" => $userdata_field["value"] );
				break;
			}
		}
	}
	//in case there are no files in session (because allownofile attribute is
	//active and the user did not select any files for upload) then retrieve
	//userdata from the database based on uploadid
	else {
		$userdata_saved = wfu_get_userdata_from_uploadid($uniqueid);
		if ( $userdata_saved != null && is_array($userdata_saved) ) {
			$userdata_fields = array();
			foreach ( $userdata_saved as $userdata_saved_rec )
				$userdata_fields[$userdata_saved_rec->propkey] = array( "label" => $userdata_saved_rec->property, "value" => $userdata_saved_rec->propvalue );
		}
	}

	$send_error = wfu_send_notification_email($user, $target_path_list, $userdata_fields, $params);

	/* suppress any errors if user is not admin */
	if ( !$is_admin ) $send_error = "";

	if ( $send_error != "" ) {
		$params_output_array["general"]['admin_messages']['notify'] = $send_error;
		$params_output_array["general"]['errors']['notify'] = "error";
	}

	/* construct safe output */
	$sout = "0;".WFU_VAR("WFU_DEFAULTMESSAGECOLORS").";0";

	$echo_str = "wfu_fileupload_success::".$sout.":".wfu_encode_array_to_string($params_output_array);
	// allow an internal hook to process the final output
	$echo_str = apply_filters('_wfu_ajax_action_send_email_notification', $echo_str);
	
	die($echo_str); 
}

function wfu_ajax_action_ask_server() {
	if ( !isset($_REQUEST['session_token']) || !isset($_REQUEST['sid']) || !isset($_REQUEST['unique_id']) ) die();

	$_REQUEST = stripslashes_deep($_REQUEST);

	$session_token = sanitize_text_field( $_REQUEST["session_token"] );
	$sid = sanitize_text_field( $_REQUEST["sid"] );
	$unique_id = wfu_sanitize_code($_REQUEST['unique_id']);
	if ( $session_token == "" ) die();
	//check referrer using Wordpress nonces and server sessions to avoid CSRF attacks
	check_ajax_referer( 'wfu-uploader-nonce', 'wfu_uploader_nonce' );
	if ( $_SESSION["wfu_token_".$sid] != $session_token ) die();
	
	//prepare parameters for before-upload filters
	$ret = array( "status" => "", "echo" => "" );
	//retrieve file names and sizes from request parameters
	$filenames_raw = ( isset($_REQUEST['filenames']) ? $_REQUEST['filenames'] : "" );
	$filenames = array();
	if ( trim($filenames_raw) != "" ) $filenames = explode(";", $filenames_raw);
	foreach ( $filenames as $ind => $filename ) $filenames[$ind] = esc_attr(wfu_plugin_decode_string(trim($filename)));
	$filesizes_raw = ( isset($_REQUEST['filesizes']) ? $_REQUEST['filesizes'] : "" );
	$filesizes = array();
	if ( trim($filesizes_raw) != "" ) $filesizes = explode(";", $filesizes_raw);
	foreach ( $filesizes as $ind => $filesize ) $filesizes[$ind] = wfu_sanitize_int($filesize);
	$files = array();
	foreach ( $filenames as $ind => $filename ) {
		$filesize = "";
		if ( isset($filesizes[$ind]) ) $filesize = $filesizes[$ind];
		array_push($files, array( "filename" => $filename, "filesize" => $filesize ));
	}
	$attr = array( "sid" => $sid, "unique_id" => $unique_id, "files" => $files );
	//execute before upload filters
	$echo_str = "";
	//first execute any custom filters created by admin
	if ( has_filter("wfu_before_upload") ) {
		$changable_data = array( "error_message" => "", "js_script" => "" );
		$changable_data = apply_filters("wfu_before_upload", $changable_data, $attr);
		if ( $changable_data["error_message"] == "" ) $ret["status"] = "success";
		else {
			$ret["status"] = "error";
			$echo_str .= "CBUV[".$changable_data["error_message"]."]";
		}
		if ( $changable_data["js_script"] != "" ) $echo_str .= "CBUVJS[".$changable_data["js_script"]."]";
	}
	//then execute internal filters of extensions
	$ret = apply_filters("_wfu_before_upload", $ret, $attr);
	$echo_str .= $ret["echo"];
	//in case that no filters were executed, because $ret["status"] is
	//empty, then this call to wfu_ajax_action_ask_server was erroneous
	if ( $ret["status"] == "" )	$ret["status"] = "die";
	//create an internal flag stored in session regarding the status of this
	//upload, that will be used to verify or not the upload
	if ( $ret["status"] == "success" ) $_SESSION["wfu_uploadstatus_".$attr["unique_id"]] = 1;
	else $_SESSION["wfu_uploadstatus_".$attr["unique_id"]] = 0;
	
	if ( $ret["status"] == "success" || $ret["status"] == "error" )
		echo "wfu_askserver_".$ret["status"].":".$echo_str;
	
	die();
}

function wfu_ajax_action_cancel_upload() {
	if ( !isset($_REQUEST['session_token']) || !isset($_REQUEST['sid']) || !isset($_REQUEST['unique_id']) ) die();

	$_REQUEST = stripslashes_deep($_REQUEST);

	$session_token = sanitize_text_field( $_REQUEST["session_token"] );
	$sid = sanitize_text_field( $_REQUEST["sid"] );
	$unique_id = wfu_sanitize_code($_REQUEST['unique_id']);
	if ( $session_token == "" ) die();
	//check referrer using Wordpress nonces and server sessions to avoid CSRF attacks
	check_ajax_referer( 'wfu-uploader-nonce', 'wfu_uploader_nonce' );
	if ( $_SESSION["wfu_token_".$sid] != $session_token ) die();
	
	//setting status to 0 denotes cancelling of the upload
	$_SESSION["wfu_uploadstatus_".$unique_id] = 0;
	
	die("success");
}

function wfu_ajax_action_callback() {
	if ( !isset($_REQUEST['session_token']) ) die();

	$_REQUEST = stripslashes_deep($_REQUEST);
	$_POST = stripslashes_deep($_POST);
	
	$session_token = sanitize_text_field( $_REQUEST["session_token"] );
	if ( $session_token == "" ) die();
	check_ajax_referer( 'wfu-uploader-nonce', 'wfu_uploader_nonce' );

	if ( !isset($_REQUEST['params_index']) ) die();
	
	$params_index = sanitize_text_field( $_REQUEST["params_index"] );
	
	if ( $params_index == "" ) die();
	
	$user = wp_get_current_user();
	$arr = wfu_get_params_fields_from_index($params_index, $session_token);
	$sid = $arr['shortcode_id'];
	//check referrer using server sessions to avoid CSRF attacks
	if ( $_SESSION["wfu_token_".$sid] != $session_token ) {
		$echo_str = "Session failed!<br/><br/>Session Data:<br/>";
		$echo_str .= print_r(wfu_sanitize($_SESSION), true);
		$echo_str .= "<br/><br/>Post Data:<br/>";
		$echo_str .= print_r(wfu_sanitize($_POST), true);
		$echo_str .= 'force_errorabort_code';
		$echo_str = apply_filters('_wfu_upload_session_failed', $echo_str);
		die($echo_str);
	}

	if ( $user->user_login != $arr['user_login'] ) {
		$echo_str = "User failed!<br/><br/>User Data:<br/>";
		$echo_str .= print_r(wfu_sanitize($user), true);
		$echo_str .= "<br/><br/>Post Data:<br/>";
		$echo_str .= print_r(wfu_sanitize($_POST), true);
		$echo_str .= "<br/><br/>Params Data:<br/>";
		$echo_str .= print_r(wfu_sanitize($arr), true);
		$echo_str .= 'force_errorabort_code';
		$echo_str = apply_filters('_wfu_upload_user_failed', $echo_str);
		die($echo_str);
	}

	//if force_connection_close is set, then the first pass to this callback script is for closing the previous connection
	if ( isset($_POST["force_connection_close"]) && $_POST["force_connection_close"] === "1" ) {
		header("Connection: Close");
		die(apply_filters('_wfu_upload_force_connection_close', 'success'));
	}
	
	//get the unique id of the upload
	$unique_id = ( isset($_POST['uniqueuploadid_'.$sid]) ? sanitize_text_field($_POST['uniqueuploadid_'.$sid]) : "" );
	if ( strlen($unique_id) != 10 ) die(apply_filters('_wfu_upload_uniqueid_failed', 'force_errorabort_code'));
	
	//if before upload actions have been executed and they have rejected the 
	//upload, but for some reason (hack attempt) the upload continued, then
	//terminate it
	if ( isset($_SESSION["wfu_uploadstatus_".$unique_id]) && $_SESSION["wfu_uploadstatus_".$unique_id] == 0 ) die('force_errorabort_code');
	
	//if upload has finished then perform post upload actions
	if ( isset($_POST["upload_finished"]) && $_POST["upload_finished"] === "1" ) {
		$echo_str = "";
		//execute after upload filters
		$ret = wfu_execute_after_upload_filters($sid, $unique_id);
		if ( $ret["js_script"] != "" ) $echo_str = "CBUVJS[".$ret["js_script"]."]";
		die($echo_str);
	}
	
	$params_str = get_option('wfu_params_'.$arr['unique_id']);
	$params = wfu_decode_array_from_string($params_str);

	//apply filters to determine if the upload will continue or stop
	$ret = array( "status" => "", "echo" => "" );
	$attr = array( "sid" => $sid, "unique_id" => $unique_id, "params" => $params );
	$ret = apply_filters("_wfu_pre_upload_check", $ret, $attr);
	if ( $ret["status"] == "die" ) die($ret["echo"]);

	//if this is the first pass of an upload attempt then perform pre-upload actions
	if ( !isset($_SESSION['wfu_upload_first_pass_'.$unique_id]) || $_SESSION['wfu_upload_first_pass_'.$unique_id] != 'true' ) {
		$_SESSION['wfu_upload_first_pass_'.$unique_id] = 'true';
	}

	if ( !isset($_POST["subdir_sel_index"]) ) die();
	$subdir_sel_index = sanitize_text_field( $_POST["subdir_sel_index"] );
	$params['subdir_selection_index'] = $subdir_sel_index;
	$_SESSION['wfu_check_refresh_'.$params["uploadid"]] = 'do not process';

	$wfu_process_file_array = wfu_process_files($params, 'ajax');
	// extract safe_output from wfu_process_file_array and pass it as separate part of the response text
	$safe_output = $wfu_process_file_array["general"]['safe_output'];
	unset($wfu_process_file_array["general"]['safe_output']);
	// get javascript code that has been defined in wfu_after_file_upload action
	$js_script = wfu_plugin_encode_string($wfu_process_file_array["general"]['js_script']);
	unset($wfu_process_file_array["general"]['js_script']);

	$echo_str = "wfu_fileupload_success:".$js_script.":".$safe_output.":".wfu_encode_array_to_string($wfu_process_file_array);
	$echo_str = apply_filters('_wfu_upload_callback_success', $echo_str);
	die($echo_str); 
}

function wfu_ajax_action_save_shortcode() {
	if ( !current_user_can( 'manage_options' ) ) die();
	if ( !isset($_POST['shortcode']) || !isset($_POST['shortcode_original']) || !isset($_POST['post_id']) || !isset($_POST['post_hash']) || !isset($_POST['shortcode_position']) || !isset($_POST['shortcode_tag']) || !isset($_POST['widget_id']) ) die();

	$_POST = stripslashes_deep($_POST);
	
	//sanitize parameters
	$shortcode = wfu_sanitize_code($_POST['shortcode']);
	$shortcode_original = wfu_sanitize_code($_POST['shortcode_original']);
	$post_id = wfu_sanitize_int($_POST['post_id']);
	$post_hash = wfu_sanitize_code($_POST['post_hash']);
	$shortcode_position = wfu_sanitize_int($_POST['shortcode_position']);
	$shortcode_tag = wfu_sanitize_tag($_POST['shortcode_tag']);
	$widget_id = sanitize_text_field($_POST['widget_id']);
	
	if ( $post_id == "" && $widget_id == "" ) {
		die();
	}
	else {
		$data['post_id'] = $post_id;
		$data['post_hash'] = $post_hash;
		$data['shortcode'] = wfu_plugin_decode_string($shortcode_original);
		$data['position'] = $shortcode_position;
		if ( $post_id != "" && !wfu_check_edit_shortcode($data) ) $echo_str = "wfu_save_shortcode:fail:post_modified";
		else {
			if ( $widget_id == "" ) {
				$new_shortcode = "[".$shortcode_tag." ".wfu_plugin_decode_string($shortcode)."]";
				if ( wfu_replace_shortcode($data, $new_shortcode) ) {
					$post = get_post($post_id);
					$hash = hash('md5', $post->post_content);
					$echo_str = "wfu_save_shortcode:success:".$hash;
				}
				else $echo_str = "wfu_save_shortcode:fail:post_update_failed";
			}
			else {
				$widget_obj = wfu_get_widget_obj_from_id($widget_id);
				if ( $widget_obj === false ) $echo_str = "wfu_save_shortcode:fail:post_update_failed";
				else {
					$widget_sidebar = is_active_widget(false, $widget_id, "wordpress_file_upload_widget");
					if ( !$widget_sidebar ) $echo_str = "wfu_save_shortcode:fail:post_update_failed";
					else {
						$widget_obj->update_external(wfu_plugin_decode_string($shortcode));
						$hash = $data['post_hash'];
						$echo_str = "wfu_save_shortcode:success:".$hash;
					}
				}
			}
		}
	}

	$echo_str = apply_filters('_wfu_ajax_action_save_shortcode', $echo_str);
	die($echo_str);
}

function wfu_ajax_action_check_page_contents() {
	if ( !current_user_can( 'manage_options' ) ) die();
	if ( !isset($_POST['post_id']) || !isset($_POST['post_hash']) ) die();
	if ( $_POST['post_id'] == "" ) die();
	
	$_POST = stripslashes_deep($_POST);

	$data['post_id'] = wfu_sanitize_int($_POST['post_id']);
	$data['post_hash'] = wfu_sanitize_code($_POST['post_hash']);
	if ( wfu_check_edit_shortcode($data) ) $echo_str = "wfu_check_page_contents:current:";
	else $echo_str = "wfu_check_page_contents:obsolete:";

	$echo_str = apply_filters('_wfu_ajax_action_check_page_contents', $echo_str);
	die($echo_str);
}

function wfu_ajax_action_edit_shortcode() {
	global $wp_registered_widgets;
	global $wp_registered_sidebars;
	
	if ( !current_user_can( 'manage_options' ) ) die();
	if ( !isset($_POST['upload_id']) || !isset($_POST['post_id']) || !isset($_POST['post_hash']) || !isset($_POST['shortcode_tag']) || !isset($_POST['widget_id']) ) die();
	
	$_POST = stripslashes_deep($_POST);
	
	//sanitize parameters
	$upload_id = sanitize_text_field($_POST['upload_id']);
	$widget_id = sanitize_text_field($_POST['widget_id']);
	$post_id = wfu_sanitize_int($_POST['post_id']);
	$post_hash = wfu_sanitize_code($_POST['post_hash']);
	$shortcode_tag = wfu_sanitize_tag($_POST['shortcode_tag']);

	$keyname = "uploadid";
	if ( $shortcode_tag == "wordpress_file_upload_browser" ) $keyname = "browserid";

	$data['post_id'] = $post_id;
	$data['post_hash'] = $post_hash;
	if ( wfu_check_edit_shortcode($data) ) {
		if ( $widget_id == "" ) {
			$post = get_post($data['post_id']);
			//get default value for uploadid
			if ( $shortcode_tag == "wordpress_file_upload_browser" ) $defs = wfu_browser_attribute_definitions();
			else $defs = wfu_attribute_definitions();
			$default = "";
			foreach ( $defs as $key => $def ) {
				if ( $def['attribute'] == $keyname ) {
					$default = $def['value'];
					break;
				}
			}
			//get page shortcodes
			$wfu_shortcodes = wfu_get_content_shortcodes($post, $shortcode_tag);
			//find the shortcodes' uploadid and the correct one
			$validkey = -1;
			foreach ( $wfu_shortcodes as $key => $data ) {
				$shortcode = trim(substr($data['shortcode'], strlen('['.$shortcode_tag), -1));
				$shortcode_attrs = wfu_shortcode_string_to_array($shortcode);
				if ( array_key_exists($keyname, $shortcode_attrs) ) $uploadid = $shortcode_attrs[$keyname];
				else $uploadid = $default;
				if ( $uploadid == $upload_id ) {
					$validkey = $key;
					break;
				}
			}
			if ( $validkey == -1 ) die();
			$data_enc = wfu_safe_store_shortcode_data(wfu_encode_array_to_string($wfu_shortcodes[$validkey]));
		}
		else {
			$widget_obj = wfu_get_widget_obj_from_id($widget_id);
			if ( $widget_obj === false ) die();
			$widget_sidebar = is_active_widget(false, $widget_id, "wordpress_file_upload_widget");
			if ( !$widget_sidebar ) die();
			if ( isset($wp_registered_sidebars[$widget_sidebar]) && isset($wp_registered_sidebars[$widget_sidebar]['name']) ) $widget_sidebar = $wp_registered_sidebars[$widget_sidebar]['name'];
			$data['shortcode'] = $widget_obj->shortcode();
			$data['position'] = 0;
			$data['widgetid'] = $widget_id;
			$data['sidebar'] = $widget_sidebar;
			$data_enc = wfu_safe_store_shortcode_data(wfu_encode_array_to_string($data));
		}
		$url = site_url().'/wp-admin/options-general.php?page=wordpress_file_upload&tag='.$shortcode_tag.'&action=edit_shortcode&data='.$data_enc;
		$echo_str = "wfu_edit_shortcode:success:".wfu_plugin_encode_string($url);
	}
	else $echo_str = "wfu_edit_shortcode:check_page_obsolete:".WFU_ERROR_PAGE_OBSOLETE;

	$echo_str = apply_filters('_wfu_ajax_action_edit_shortcode', $echo_str);
	die($echo_str);
}

function wfu_ajax_action_read_subfolders() {
	if ( !isset($_POST['folder1']) || !isset($_POST['folder2']) ) die();

	$_POST = stripslashes_deep($_POST);

	$folder1 = wfu_sanitize_code($_POST['folder1']);
	$folder1 = wfu_sanitize_url(wfu_plugin_decode_string($folder1));
	$folder2 = wfu_sanitize_code($_POST['folder2']);
	$folder2 = wfu_sanitize_url(wfu_plugin_decode_string($folder2));
	if ( wfu_plugin_encode_string($folder1) != $_POST['folder1'] || wfu_plugin_encode_string($folder2) != $_POST['folder2'] ) die();

	$temp_params = array( 'uploadpath' => $folder1, 'accessmethod' => 'normal', 'ftpinfo' => '', 'useftpdomain' => 'false' );
	$path = wfu_upload_plugin_full_path($temp_params);

	if ( !is_dir($path) ) die(apply_filters('_wfu_ajax_action_read_subfolders', 'wfu_read_subfolders:error:Parent folder is not valid! Cannot retrieve subfolder list.'));

	$path2 = $folder2;
	$dirlist = "";
	if ( $handle = opendir($path) ) {
		$blacklist = array('.', '..');
		while ( false !== ($file = readdir($handle)) )
			if ( !in_array($file, $blacklist) ) {
				$filepath = $path.$file;
				if ( is_dir($filepath) ) {
					if ( $file == $path2 ) $file = '[['.$file.']]';
					$dirlist .= ( $dirlist == "" ? "" : "," ).$file;
				}
			}
		closedir($handle);
	}
	if ( $path2 != "" ) {
		$dirlist2 = $path2;
		$path .= $path2."/";
		if ( is_dir($path) ) {
			if ( $handle = opendir($path) ) {
				$blacklist = array('.', '..');
				while ( false !== ($file = readdir($handle)) )
					if ( !in_array($file, $blacklist) ) {
						$filepath = $path.$file;
						if ( is_dir($filepath) )
							$dirlist2 .= ",*".$file;
					}
				closedir($handle);
			}
		}
		$dirlist = str_replace('[['.$path2.']]', $dirlist2, $dirlist);
	}

	die(apply_filters('_wfu_ajax_action_read_subfolders', "wfu_read_subfolders:success:".wfu_plugin_encode_string($dirlist)));
}

function wfu_ajax_action_download_file_invoker() {
	$_POST = stripslashes_deep($_POST);
	$_GET = stripslashes_deep($_GET);

	$file_code = (isset($_POST['file']) ? $_POST['file'] : (isset($_GET['file']) ? $_GET['file'] : ''));
	$nonce = (isset($_POST['nonce']) ? $_POST['nonce'] : (isset($_GET['nonce']) ? $_GET['nonce'] : ''));
	if ( $file_code == '' || $nonce == '' ) die();

	//security check to avoid CSRF attacks
	if ( !wp_verify_nonce($nonce, 'wfu_download_file_invoker') ) die();
	
	//check if user is allowed to download files
	if ( !current_user_can( 'manage_options' ) ) {
		die();
	}
	
	$file_code = wfu_sanitize_code($file_code);
	//if file_code is exportdata, then export of data has been requested and
	//we need to create a file with export data and recreate file_code
	if ( $file_code == "exportdata" && current_user_can( 'manage_options' ) ) {
		$filepath = wfu_export_uploaded_files(null);
		if ( $filepath === false ) die();
		$file_code = "exportdata".wfu_safe_store_filepath($filepath);
	}
	//else get the file path from the safe
	else {
		$filepath = wfu_get_filepath_from_safe($file_code);
		if ( $filepath === false ) die();
		$filepath = wfu_path_rel2abs(wfu_flatten_path($filepath));
		//reject download of blacklisted file types for security reasons
		if ( wfu_file_extension_blacklisted($filepath) ) {
			die(apply_filters('_wfu_ajax_action_download_file_invoker', 'wfu_ajax_action_download_file_invoker:not_allowed:'.( isset($_POST['browser']) ? WFU_BROWSER_DOWNLOADFILE_NOTALLOWED : 'You are not allowed to download this file!' )));
		}
		//for front-end browser apply wfu_browser_check_file_action filter to allow or restrict the download
		if ( isset($_POST['browser']) ) {
			$changable_data["error_message"] = "";
			$filerec = wfu_get_file_rec($filepath, true);
			$userdata = array();
			foreach ( $filerec->userdata as $data )
				array_push($userdata, array( "label" => $data->property, "value" => $data->propvalue ));
			$additional_data = array(
				"file_action"	=> "download",
				"filepath"		=> $filepath,
				"uploaduser"	=> $filerec->uploaduserid,
				"userdata"		=> $userdata
			);
			$changable_data = apply_filters("wfu_browser_check_file_action", $changable_data, $additional_data);
			if ( $changable_data["error_message"] != "" )
				die(apply_filters('_wfu_ajax_action_download_file_invoker', 'wfu_ajax_action_download_file_invoker:not_allowed:'.$changable_data["error_message"]));
		}
		//for back-end browser check if user is allowed to perform this action on this file
		if ( !wfu_current_user_owes_file($filepath) ) die();
	}
	
	//generate download unique id to monitor this download
	$download_id = wfu_create_random_string(16);
	//store download status of this download
	$_SESSION['wfu_download_status_'.$download_id] = 'starting';
	//generate download ticket which expires in 30sec and store it in session
	//it will be used as security measure for the downloader script, which runs outside Wordpress environment
	$_SESSION['wfu_download_ticket_'.$download_id] = time() + 30;
	//generate download monitor ticket which expires in 30sec and store it in session
	//it will be used as security measure for the monitor script that will check download status
	$_SESSION['wfu_download_monitor_ticket_'.$download_id] = time() + 30;
	
	//store translatable strings to session so that they can be used by a script
	//that runs outside Wordpress environment
	$_SESSION['wfu_browser_downloadfile_notexist'] = ( isset($_POST['browser']) ? WFU_BROWSER_DOWNLOADFILE_NOTEXIST : 'File does not exist!' );
	$_SESSION['wfu_browser_downloadfile_failed'] = ( isset($_POST['browser']) ? WFU_BROWSER_DOWNLOADFILE_FAILED : 'Could not download file!' );

	//this routine returns a dynamically created iframe element, that will call the actual download script;
	//the actual download script runs outside Wordpress environment in order to ensure that no php warnings
	//or echo from other plugins is generated, that could scramble the downloaded file;
	//a ticket, similar to nonces, is passed to the download script to check that it is not a CSRF attack; moreover,the ticket is destroyed
	//by the time it is consumed by the download script, so it cannot be used again
	$response = '<iframe src="'.WFU_DOWNLOADER_URL.'?file='.$file_code.'&ticket='.$download_id.'" style="display: none;"></iframe>';

	die(apply_filters('_wfu_ajax_action_download_file_invoker', 'wfu_ajax_action_download_file_invoker:wfu_download_id;'.$download_id.':'.$response));
}

function wfu_ajax_action_download_file_monitor() {
	$_POST = stripslashes_deep($_POST);
	$_GET = stripslashes_deep($_GET);

	$file_code = (isset($_POST['file']) ? $_POST['file'] : (isset($_GET['file']) ? $_GET['file'] : ''));
	$id = (isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : ''));
	if ( $file_code == '' || $id == '' ) die();
	$id = wfu_sanitize_code($id);
	
	//ensure that this is not a CSRF attack by checking validity of a security ticket
	if ( !isset($_SESSION['wfu_download_monitor_ticket_'.$id]) || time() > $_SESSION['wfu_download_monitor_ticket_'.$id] ) die();
	//destroy monitor ticket so it cannot be used again
	unset($_SESSION['wfu_download_monitor_ticket_'.$id]);
	
	//initiate loop of 30secs to check the download status of the file;
	//the download status is controlled by the actual download script;
	//if the file finishes within the 30secs of the loop, then this routine logs the action and notifies
	//the client side about the download status of the file, otherwise an instruction
	//to the client side to repeat this routine and wait for another 30secs is dispatched
	$end_time = time() + 30;
	$upload_ended = false;
	while ( time() < $end_time ) {
		$upload_ended = ( isset($_SESSION['wfu_download_status_'.$id]) ? ( $_SESSION['wfu_download_status_'.$id] == 'downloaded' || $_SESSION['wfu_download_status_'.$id] == 'failed' ? true : false ) : false );
		if ( $upload_ended ) break;
		usleep(100);
	}
	
	if ( $upload_ended ) {
		$user = wp_get_current_user();
//		$filepath = wfu_plugin_decode_string($file_code);
		$filepath = wfu_get_filepath_from_safe($file_code);
		if ( $filepath === false ) die();
		$filepath = wfu_path_rel2abs(wfu_flatten_path($filepath));
		wfu_log_action('download', $filepath, $user->ID, '', 0, 0, '', null);
		die(apply_filters('_wfu_ajax_action_download_file_monitor', 'wfu_ajax_action_download_file_monitor:'.$_SESSION['wfu_download_status_'.$id].':'));
	}
	else {
		//regenerate monitor ticket
		$_SESSION['wfu_download_monitor_ticket_'.$id] = time() + 30;
		die(apply_filters('_wfu_ajax_action_download_file_monitor', 'wfu_ajax_action_download_file_monitor:repeat:'.$id));
	}
}

function wfu_ajax_action_get_historylog_page() {
	if ( !isset($_POST['token']) || !isset($_POST['page']) ) die();
	check_ajax_referer( 'wfu-historylog-page', 'token' );
	if ( !current_user_can( 'manage_options' ) ) die();
	if ( WFU_VAR("WFU_HISTORYLOG_TABLE_MAXROWS") <= 0 ) die();
	
	$_POST = stripslashes_deep($_POST);
	
	$page = wfu_sanitize_int($_POST['page']);
	$rows = wfu_view_log($page, true);
	
	die(apply_filters('_wfu_ajax_action_get_historylog_page', 'wfu_historylog_page_success:'.wfu_plugin_encode_string($rows)));
}

function wfu_ajax_action_include_file() {
	$_POST = stripslashes_deep($_POST);
	$_GET = stripslashes_deep($_GET);

	$file_code = (isset($_POST['file']) ? $_POST['file'] : (isset($_GET['file']) ? $_GET['file'] : ''));
	$nonce = (isset($_POST['nonce']) ? $_POST['nonce'] : (isset($_GET['nonce']) ? $_GET['nonce'] : ''));
	if ( $file_code == '' || $nonce == '' ) die();

	if ( !current_user_can( 'manage_options' ) ) die();
	//security check to avoid CSRF attacks
	if ( !wp_verify_nonce($nonce, 'wfu_include_file') ) die();
	
	$plugin_options = wfu_decode_plugin_options(get_option( "wordpress_file_upload_options" ));
	if ( $plugin_options['includeotherfiles'] != "1" ) die();
	
	$dec_file = wfu_get_filepath_from_safe($file_code);
	if ( $dec_file === false ) die();

	$user = wp_get_current_user();
	$dec_file = wfu_path_rel2abs(wfu_flatten_path($dec_file));
	$fileid = wfu_log_action('include', $dec_file, $user->ID, '', '', get_current_blog_id(), '', null);
	
	if ( $fileid !== false ) {
		die(apply_filters('_wfu_ajax_action_include_file', "wfu_include_file:success:".$fileid));
	}
	else die(apply_filters('_wfu_ajax_action_include_file', 'wfu_include_file:fail:'));
}

function wfu_ajax_action_notify_wpfilebase() {
	$_POST = stripslashes_deep($_POST);
	$_GET = stripslashes_deep($_GET);

	$params_index = (isset($_POST['params_index']) ? $_POST['params_index'] : (isset($_GET['params_index']) ? $_GET['params_index'] : ''));
	$session_token = (isset($_POST['session_token']) ? $_POST['session_token'] : (isset($_GET['session_token']) ? $_GET['session_token'] : ''));
	if ( $params_index == '' || $session_token == '' ) die();

	$params_index = sanitize_text_field($params_index);
	$session_token = sanitize_text_field($session_token);

	$arr = wfu_get_params_fields_from_index($params_index, $session_token);
	//check referer using server sessions to avoid CSRF attacks
	if ( $_SESSION["wfu_token_".$arr['shortcode_id']] != $session_token ) die();

	do_action('wpfilebase_sync');

	die();
}

?>
