<?php

function wfu_browse_files($basedir_code) {
	$siteurl = site_url();
	$plugin_options = wfu_decode_plugin_options(get_option( "wordpress_file_upload_options" ));
	$user = wp_get_current_user();
	//store session variables for use from the downloader
	
	if ( !current_user_can( 'manage_options' ) ) return;

	//first decode basedir_code
	$basedir = wfu_get_filepath_from_safe($basedir_code);
	//clean session array holding dir and file paths if it is too big
	if ( isset($_SESSION['wfu_filepath_safe_storage']) && count($_SESSION['wfu_filepath_safe_storage']) > WFU_VAR("WFU_PHP_ARRAY_MAXLEN") ) $_SESSION['wfu_filepath_safe_storage'] = array();
	
	//extract sort info from basedir
	$sort = "";
	if ( $basedir !== false ) {
		$ret = wfu_extract_sortdata_from_path($basedir);
		$basedir = $ret['path'];
		$sort = $ret['sort'];
	}
	if ( $sort == "" ) $sort = 'name';
	if ( substr($sort, 0, 1) == '-' ) $order = SORT_DESC;
	else $order = SORT_ASC;

	//adjust basedir to have a standard format
	if ( $basedir !== false ) {
		if ( substr($basedir, -1) != '/' ) $basedir .= '/';
		if ( substr($basedir, 0, 1) == '/' ) $basedir = substr($basedir, 1);
		//calculate the absolute path of basedir knowing that basedir is relative to website root
		$basedir = wfu_path_rel2abs($basedir);
		if ( !file_exists($basedir) ) $basedir = false;
	}
	//set basedit to default value if empty
	if ( $basedir === false ) {
		$plugin_options = wfu_decode_plugin_options(get_option( "wordpress_file_upload_options" ));
		$basedir = ( isset($plugin_options['basedir']) ? $plugin_options['basedir'] : "" );
		$temp_params = array( 'uploadpath' => $basedir, 'accessmethod' => 'normal', 'ftpinfo' => '', 'useftpdomain' => 'false' );
		$basedir = wfu_upload_plugin_full_path($temp_params);
	}
	//find relative dir
	$reldir = str_replace(wfu_abspath(), "root/", $basedir);
	//save dir route to an array
	$parts = explode('/', $reldir);
	$route = array();
	$prev = "";
	foreach ( $parts as $part ) {
		$part = trim($part);
		if ( $part != "" ) {
//			if ( $part == 'root' && $prev == "" ) $prev = wfu_abspath();
			if ( $part == 'root' && $prev == "" ) $prev = "";
			else $prev .= $part.'/';
			array_push($route, array( 'item' => $part, 'path' => $prev ));
		}
	}
	//calculate upper directory
	$updir = substr($basedir, 0, -1);
	$delim_pos = strrpos($updir, '/');
	if ( $delim_pos !== false ) $updir = substr($updir, 0, $delim_pos + 1);

	$echo_str = "\n".'<div class="wrap">';
	$echo_str .= "\n\t".'<h2>Wordpress File Upload Control Panel</h2>';
	$echo_str .= "\n\t".'<div style="margin-top:20px;">';
	$echo_str .= wfu_generate_dashboard_menu("\n\t\t", "File Browser");
	$echo_str .= "\n\t".'<div>';
	$echo_str .= "\n\t\t".'<span><strong>Location:</strong> </span>';
	foreach ( $route as $item ) {
		// store dir path that we need to pass to other functions in session, instead of exposing it in the url
		$dir_code = wfu_safe_store_filepath($item['path']);
		$echo_str .= '<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&action=file_browser&dir='.$dir_code.'">'.$item['item'].'</a>';
		$echo_str .= '<span>/</span>';
	}
	//define referer (with sort data) to point to this url for use by the elements
	$referer = $siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&action=file_browser&dir='.$basedir_code;
	$referer_code = wfu_safe_store_filepath($referer.'[['.$sort.']]');
	//file browser header
	$echo_str .= "\n\t".'</div>';
//	$dir_code = wfu_safe_store_filepath(wfu_path_abs2rel($basedir).'[['.$sort.']]');
//	$echo_str .= "\n\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&amp;action=create_dir&dir='.$dir_code.'" class="button" title="create folder" style="margin-top:6px">Create folder</a>';
	$echo_str .= "\n\t".'<div style="margin-top:10px;">';
	$echo_str .= "\n\t\t".'<div class="wfu_adminbrowser_header" style="width: 100%;">';
	$bulkactions = array(
		array( "name" => "delete", "title" => "Delete" ),
		array( "name" => "include", "title" => "Include" )
	);
	$echo_str .= wfu_add_bulkactions_header("\n\t\t\t", "adminbrowser", $bulkactions);
	$echo_str .= "\n\t\t\t".'<input id="wfu_adminbrowser_action_url" type="hidden" value="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload" />';
	$echo_str .= "\n\t\t\t".'<input id="wfu_adminbrowser_referer" type="hidden" value="'.$referer_code.'" />';
	$echo_str .= "\n\t\t\t".'<input id="wfu_download_file_nonce" type="hidden" value="'.wp_create_nonce('wfu_download_file_invoker').'" />';
	$echo_str .= "\n\t\t\t".'<input id="wfu_include_file_nonce" type="hidden" value="'.wp_create_nonce('wfu_include_file').'" />';
	//define header parameters that can be later used when defining file actions
	$header_params = array();
	$echo_str .= "\n\t\t".'</div>';
	$echo_str .= "\n\t\t".'<table class="wp-list-table widefat fixed striped">';
	$echo_str .= "\n\t\t\t".'<thead>';
	$echo_str .= "\n\t\t\t\t".'<tr>';
	$echo_str .= "\n\t\t\t\t\t".'<th scope="col" width="5%" style="text-align:center;">';
	$echo_str .= "\n\t\t\t\t\t\t".'<input id="wfu_select_all_visible" type="checkbox" onchange="wfu_adminbrowser_select_all_visible_changed();" style="-webkit-appearance:checkbox;" />';
	$echo_str .= "\n\t\t\t\t\t".'</th>';
	$echo_str .= "\n\t\t\t\t\t".'<th scope="col" width="25%" style="text-align:left;">';
	$dir_code = wfu_safe_store_filepath(wfu_path_abs2rel($basedir).'[['.( substr($sort, -4) == 'name' ? ( $order == SORT_ASC ? '-name' : 'name' ) : 'name' ).']]');
	$echo_str .= "\n\t\t\t\t\t\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&action=file_browser&dir='.$dir_code.'">Name'.( substr($sort, -4) == 'name' ? ( $order == SORT_ASC ? ' &uarr;' : ' &darr;' ) : '' ).'</a>';
	$echo_str .= "\n\t\t\t\t\t".'</th>';
	$echo_str .= "\n\t\t\t\t\t".'<th scope="col" width="10%" style="text-align:right;">';
	$dir_code = wfu_safe_store_filepath(wfu_path_abs2rel($basedir).'[['.( substr($sort, -4) == 'size' ? ( $order == SORT_ASC ? '-size' : 'size' ) : 'size' ).']]');
	$echo_str .= "\n\t\t\t\t\t\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&action=file_browser&dir='.$dir_code.'">Size'.( substr($sort, -4) == 'size' ? ( $order == SORT_ASC ? ' &uarr;' : ' &darr;' ) : '' ).'</a>';
	$echo_str .= "\n\t\t\t\t\t".'</th>';
	$echo_str .= "\n\t\t\t\t\t".'<th scope="col" width="20%" style="text-align:left;">';
	$dir_code = wfu_safe_store_filepath(wfu_path_abs2rel($basedir).'[['.( substr($sort, -4) == 'date' ? ( $order == SORT_ASC ? '-date' : 'date' ) : 'date' ).']]');
	$echo_str .= "\n\t\t\t\t\t\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&action=file_browser&dir='.$dir_code.'">Date'.( substr($sort, -4) == 'date' ? ( $order == SORT_ASC ? ' &uarr;' : ' &darr;' ) : '' ).'</a>';
	$echo_str .= "\n\t\t\t\t\t".'</th>';
	$echo_str .= "\n\t\t\t\t\t".'<th scope="col" width="10%" style="text-align:center;">';
	$dir_code = wfu_safe_store_filepath(wfu_path_abs2rel($basedir).'[['.( substr($sort, -4) == 'user' ? ( $order == SORT_ASC ? '-user' : 'user' ) : 'user' ).']]');
	$echo_str .= "\n\t\t\t\t\t\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&action=file_browser&dir='.$dir_code.'">Uploaded By'.( substr($sort, -4) == 'user' ? ( $order == SORT_ASC ? ' &uarr;' : ' &darr;' ) : '' ).'</a>';
	$echo_str .= "\n\t\t\t\t\t".'</th>';
	$echo_str .= "\n\t\t\t\t\t".'<th scope="col" width="30%" style="text-align:left;">';
	$echo_str .= "\n\t\t\t\t\t\t".'<label>User Data</label>';
	$echo_str .= "\n\t\t\t\t\t".'</th>';
	$echo_str .= "\n\t\t\t\t".'</tr>';
	$echo_str .= "\n\t\t\t".'</thead>';
	$echo_str .= "\n\t\t\t".'<tbody>';

	//find contents of current folder
	$dirlist = array();
	$filelist = array();
	if ( $handle = opendir($basedir) ) {
		$blacklist = array('.', '..');
		while ( false !== ($file = readdir($handle)) )
			if ( !in_array($file, $blacklist) ) {
				$filepath = $basedir.$file;
				$stat = stat($filepath);
				if ( is_dir($filepath) ) {
					array_push($dirlist, array( 'name' => $file, 'fullpath' => $filepath, 'mdate' => $stat['mtime'] ));
				}
				else {
					//find relative file record in database together with user data;
					//if the file is php, then file record is null meaning that the file can only be viewed
					//if file record is not found then the file can only be viewed
					if ( preg_match("/\.php$/", $filepath) ) $filerec = null;
					else $filerec = wfu_get_file_rec($filepath, true);
					//find user who uploaded the file
					$username = '';
					if ( $filerec != null ) $username = wfu_get_username_by_id($filerec->uploaduserid);
					array_push($filelist, array( 'name' => $file, 'fullpath' => $filepath, 'size' => $stat['size'], 'mdate' => $stat['mtime'], 'user' => $username, 'filedata' => $filerec ));
				}
			}
		closedir($handle);
	}
	$dirsort = ( substr($sort, -4) == 'date' ? 'mdate' : substr($sort, -4) );
	$filesort = $dirsort;
	$dirorder = $order;
	if ( $dirsort == 'size' ) { $dirsort = 'name'; $dirorder = SORT_ASC; }
	if ( $dirsort == 'user' ) { $dirsort = 'name'; $dirorder = SORT_ASC; }
	switch ( $dirsort ) {
		case "name": $dirsort .= ":s"; break;
		case "size": $dirsort .= ":n"; break;
		case "mdate": $dirsort .= ":n"; break;
		case "user": $dirsort .= ":s"; break;
	}
	$dirlist = wfu_array_sort($dirlist, $dirsort, $dirorder);
	switch ( $filesort ) {
		case "name": $filesort .= ":s"; break;
		case "size": $filesort .= ":n"; break;
		case "mdate": $filesort .= ":n"; break;
		case "user": $filesort .= ":s"; break;
	}
	$filelist = wfu_array_sort($filelist, $filesort, $order);

	//show subfolders first
	if ( $reldir != "root/" ) {
		$dir_code = wfu_safe_store_filepath(wfu_path_abs2rel($updir));
		$echo_str .= "\n\t\t\t\t".'<tr>';
		$echo_str .= "\n\t\t\t\t\t".'<td width="5%" style="padding: 5px 5px 5px 10px; text-align:center;"><input type="checkbox" disabled="disabled" /></td>';
		$echo_str .= "\n\t\t\t\t\t".'<td width="25%" style="padding: 5px 5px 5px 10px; text-align:left;">';
		$echo_str .= "\n\t\t\t\t\t\t".'<a class="row-title" href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&action=file_browser&dir='.$dir_code.'" title="go up">..</a>';
		$echo_str .= "\n\t\t\t\t\t".'</td>';
		$echo_str .= "\n\t\t\t\t\t".'<td width="10%" style="padding: 5px 5px 5px 10px; text-align:right;"> </td>';
		$echo_str .= "\n\t\t\t\t\t".'<td width="20%" style="padding: 5px 5px 5px 10px; text-align:left;"> </td>';
		$echo_str .= "\n\t\t\t\t\t".'<td width="10%" style="padding: 5px 5px 5px 10px; text-align:center;"> </td>';
		$echo_str .= "\n\t\t\t\t\t".'<td width="30%" style="padding: 5px 5px 5px 10px; text-align:left;"> </td>';
		$echo_str .= "\n\t\t\t\t".'</tr>';
	}
	$ii = 1;
	foreach ( $dirlist as $dir ) {
		$dir_code = wfu_safe_store_filepath(wfu_path_abs2rel($dir['fullpath']).'[['.$sort.']]');
		$echo_str .= "\n\t\t\t\t".'<tr onmouseover="var actions=document.getElementsByName(\'wfu_dir_actions\'); for (var i=0; i<actions.length; i++) {actions[i].style.visibility=\'hidden\';} document.getElementById(\'wfu_dir_actions_'.$ii.'\').style.visibility=\'visible\'" onmouseout="var actions=document.getElementsByName(\'wfu_dir_actions\'); for (var i=0; i<actions.length; i++) {actions[i].style.visibility=\'hidden\';}">';
		$echo_str .= "\n\t\t\t\t\t".'<td width="5%" style="padding: 5px 5px 5px 10px; text-align:center;"><input type="checkbox" disabled="disabled" /></td>';
		$echo_str .= "\n\t\t\t\t\t".'<td width="25%" style="padding: 5px 5px 5px 10px; text-align:left;">';
		$echo_str .= "\n\t\t\t\t\t\t".'<a class="row-title" href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&action=file_browser&dir='.$dir_code.'" title="'.$dir['name'].'">'.$dir['name'].'</a>';
		$echo_str .= "\n\t\t\t\t\t\t".'<div id="wfu_dir_actions_'.$ii.'" name="wfu_dir_actions" style="visibility:hidden;">';
		$echo_str .= "\n\t\t\t\t\t\t\t".'<span style="visibility:hidden;">';
		$echo_str .= "\n\t\t\t\t\t\t\t\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&action=file_browser&dir=">Noaction</a>';
		$echo_str .= "\n\t\t\t\t\t\t\t\t".' | ';
		$echo_str .= "\n\t\t\t\t\t\t\t".'</span>';
//		$echo_str .= "\n\t\t\t\t\t\t\t".'<span>';
//		$echo_str .= "\n\t\t\t\t\t\t\t\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&action=rename_dir&file='.$dir_code.'" title="Rename this folder">Rename</a>';
//		$echo_str .= "\n\t\t\t\t\t\t\t\t".' | ';
//		$echo_str .= "\n\t\t\t\t\t\t\t".'</span>';
//		$echo_str .= "\n\t\t\t\t\t\t\t".'<span>';
//		$echo_str .= "\n\t\t\t\t\t\t\t\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&action=delete_dir&file='.$dir_code.'" title="Delete this folder">Delete</a>';
//		$echo_str .= "\n\t\t\t\t\t\t\t".'</span>';
		$echo_str .= "\n\t\t\t\t\t\t".'</div>';
		$echo_str .= "\n\t\t\t\t\t".'</td>';
		$echo_str .= "\n\t\t\t\t\t".'<td width="10%" style="padding: 5px 5px 5px 10px; text-align:right;"> </td>';
		$echo_str .= "\n\t\t\t\t\t".'<td width="20%" style="padding: 5px 5px 5px 10px; text-align:left;">'.get_date_from_gmt(date("Y-m-d H:i:s", $dir['mdate']), "d/m/Y H:i:s").'</td>';
		$echo_str .= "\n\t\t\t\t\t".'<td width="10%" style="padding: 5px 5px 5px 10px; text-align:center;"> </td>';
		$echo_str .= "\n\t\t\t\t\t".'<td width="30%" style="padding: 5px 5px 5px 10px; text-align:left;"> </td>';
		$echo_str .= "\n\t\t\t\t".'</tr>';
		$ii ++;
	}
	//show contained files
	foreach ( $filelist as $file ) {
		$is_included = ( $file['filedata'] != null );
		$can_be_included = ( $plugin_options['includeotherfiles'] == "1" ) && !wfu_file_extension_blacklisted($file['name']);
		$file_code = '';
		if ( $is_included || $can_be_included ) $file_code = wfu_safe_store_filepath(wfu_path_abs2rel($file['fullpath']).'[['.$sort.']]');
		$echo_str .= "\n\t\t\t\t".'<tr onmouseover="var actions=document.getElementsByName(\'wfu_file_actions\'); for (var i=0; i<actions.length; i++) {actions[i].style.visibility=\'hidden\';} document.getElementById(\'wfu_file_actions_'.$ii.'\').style.visibility=\'visible\'" onmouseout="var actions=document.getElementsByName(\'wfu_file_actions\'); for (var i=0; i<actions.length; i++) {actions[i].style.visibility=\'hidden\';}">';
		$echo_str .= "\n\t\t\t\t\t".'<td width="5%" style="padding: 5px 5px 5px 10px; text-align:center;">';
		if ( $is_included || $can_be_included ) $echo_str .= "\n\t\t\t\t\t\t".'<input class="wfu_selectors'.( $is_included ? ' wfu_included' : '' ).' wfu_selcode_'.$file_code.'" type="checkbox" onchange="wfu_adminbrowser_selector_changed(this);" />';
		else $echo_str .= "\n\t\t\t\t\t\t".'<input type="checkbox" disabled="disabled" />';
		$echo_str .= "\n\t\t\t\t\t".'</td>';
		$echo_str .= "\n\t\t\t\t\t".'<td width="25%" style="padding: 5px 5px 5px 10px; text-align:left;">';
		if ( $is_included || $can_be_included )
			$echo_str .= "\n\t\t\t\t\t\t".'<a id="wfu_file_link_'.$ii.'" class="row-title" href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&action=file_details&file='.$file_code.'" title="View and edit file details" style="font-weight:normal;'.( $is_included ? '' : ' display:none;' ).'">'.$file['name'].'</a>';
		if ( !$is_included )
			$echo_str .= "\n\t\t\t\t\t\t".'<span id="wfu_file_flat_'.$ii.'">'.$file['name'].'</span>';
		//set additional $file properties for generating file actions
		$file["index"] = $ii;
		$file["code"] = $file_code;
		$file["referer_code"] = $referer_code;
		$file_actions = wfu_adminbrowser_file_actions($file, $header_params);
		$echo_str .= "\n\t\t\t\t\t\t".'<div id="wfu_file_actions_'.$ii.'" name="wfu_file_actions" style="visibility:hidden;">';
		if ( $is_included || $can_be_included ) {
			$echo_str .= "\n\t\t\t\t\t\t\t".'<div id="wfu_file_is_included_actions_'.$ii.'" style="display:'.( $is_included ? 'block' : 'none' ).';">';
			//add file actions for files already included
			$array_keys = array_keys($file_actions["is_included"]);
			$lastkey = array_pop($array_keys);
			foreach ( $file_actions["is_included"] as $key => $action ) {
				$echo_str .= "\n\t\t\t\t\t\t\t\t".'<span>';
				foreach ( $action as $line )
					$echo_str .= "\n\t\t\t\t\t\t\t\t\t".$line;
				if ( $key != $lastkey ) $echo_str .= "\n\t\t\t\t\t\t\t\t\t".' | ';
				$echo_str .= "\n\t\t\t\t\t\t\t\t".'</span>';
			}
			$echo_str .= "\n\t\t\t\t\t\t\t".'</div>';
			$echo_str .= "\n\t\t\t\t\t\t\t".'<div id="wfu_file_can_be_included_actions_'.$ii.'" style="display:'.( $is_included ? 'none' : 'block' ).';">';
			//add file actions for files that can be included
			$array_keys = array_keys($file_actions["can_be_included"]);
			$lastkey = array_pop($array_keys);
			foreach ( $file_actions["can_be_included"] as $key => $action ) {
				$echo_str .= "\n\t\t\t\t\t\t\t\t".'<span>';
				foreach ( $action as $line )
					$echo_str .= "\n\t\t\t\t\t\t\t\t\t".$line;
				if ( $key != $lastkey ) $echo_str .= "\n\t\t\t\t\t\t\t\t\t".' | ';
				$echo_str .= "\n\t\t\t\t\t\t\t\t".'</span>';
			}
			$echo_str .= "\n\t\t\t\t\t\t\t".'</div>';
		}
		else {
			$echo_str .= "\n\t\t\t\t\t\t\t".'<span style="visibility:hidden;">';
			$echo_str .= "\n\t\t\t\t\t\t\t\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&action=file_browser&dir=">Noaction</a>';
			$echo_str .= "\n\t\t\t\t\t\t\t\t".' | ';
			$echo_str .= "\n\t\t\t\t\t\t\t".'</span>';
		}
		$echo_str .= "\n\t\t\t\t\t\t".'</div>';
		$echo_str .= "\n\t\t\t\t\t\t".'<div id="wfu_file_download_container_'.$ii.'" style="display: none;"></div>';
		$echo_str .= "\n\t\t\t\t\t".'</td>';
		$echo_str .= "\n\t\t\t\t\t".'<td width="10%" style="padding: 5px 5px 5px 10px; text-align:right;">'.$file['size'].'</td>';
		$echo_str .= "\n\t\t\t\t\t".'<td width="20%" style="padding: 5px 5px 5px 10px; text-align:left;">'.get_date_from_gmt(date("Y-m-d H:i:s", $file['mdate']), "d/m/Y H:i:s").'</td>';
		$echo_str .= "\n\t\t\t\t\t".'<td width="10%" style="padding: 5px 5px 5px 10px; text-align:center;">'.$file['user'].'</td>';
		$echo_str .= "\n\t\t\t\t\t".'<td width="30%" style="padding: 5px 5px 5px 10px; text-align:left;">';
		if ( $is_included ) {
			if ( count($file['filedata']->userdata) > 0 ) {
				$echo_str .= "\n\t\t\t\t\t\t".'<select multiple="multiple" style="width:100%; height:40px; background:none; font-size:small;">';
				foreach ( $file['filedata']->userdata as $userdata )
					$echo_str .= "\n\t\t\t\t\t\t\t".'<option>'.$userdata->property.': '.$userdata->propvalue.'</option>';
				$echo_str .= "\n\t\t\t\t\t\t".'</select>';
			}
		}
		$echo_str .= "\n\t\t\t\t\t".'</td>';
		$echo_str .= "\n\t\t\t\t".'</tr>';
		$ii ++;
	}
	$echo_str .= "\n\t\t\t".'</tbody>';
	$echo_str .= "\n\t\t".'</table>';
	$echo_str .= "\n\t\t".'<iframe id="wfu_download_frame" style="display: none;"></iframe>';
	$echo_str .= "\n\t".'</div>';
	$echo_str .= "\n\t".'</div>';
	$echo_str .= "\n".'</div>';

	return $echo_str;
}

function wfu_adminbrowser_file_actions($file, $params) {
	$siteurl = site_url();
	$actions = array(
		"is_included"		=> array(),
		"can_be_included"	=> array()
	);
	//add file actions if file is already included
	$actions["is_included"] += array(
		array( '<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&action=file_details&file='.$file["code"].'" title="View and edit file details">Details</a>' ),
		array( '<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&action=rename_file&file='.$file["code"].'" title="Rename this file">Rename</a>' ),
		array( '<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&action=delete_file&file='.$file["code"].'&referer='.$file["referer_code"].'" title="Delete this file">Delete</a>' ),
		array( '<a href="javascript:wfu_download_file(\''.$file["code"].'\', '.$file["index"].');" title="Download this file">Download</a>' )
	);
	//add file actions if file is already included
	$actions["can_be_included"] += array(
		array(
			'<a id="wfu_include_file_'.$file["index"].'_a" href="javascript:wfu_include_file(\''.$file["code"].'\', '.$file["index"].');" title="Include file in plugin\'s database">Include File</a>',
			'<img id="wfu_include_file_'.$file["index"].'_img" src="'.WFU_IMAGE_ADMIN_SUBFOLDER_LOADING.'" style="width:12px; display:none;" />',
			'<input id="wfu_include_file_'.$file["index"].'_inpfail" type="hidden" value="File could not be included!" />'
		)
	);

	return $actions;
}

function wfu_user_owns_file($userid, $filerec) {
	if ( 0 == $userid )
		return false;
	if ( current_user_can('manage_options') ) return true;
	return false;
}

function wfu_current_user_owes_file($filepath) {
	//first check if file has a restricted extension; for security reasons some file extensions cannot be owned
	if ( wfu_file_extension_blacklisted($filepath) ) return false;
	//then get file data from database, if exist
	$filerec = wfu_get_file_rec($filepath, false);
	if ( $filerec == null ) return false;

	$user = wp_get_current_user();
	return wfu_user_owns_file($user->ID, $filerec);
}

function wfu_current_user_allowed_action($action, $filepath) {
	//first get file data from database, if exist
	$filerec = wfu_get_file_rec($filepath, false);

	$user = wp_get_current_user();
	if ( 0 == $user->ID ) return null;
	else $is_admin = current_user_can('manage_options');
	if ( !$is_admin ) {
			return null;
	}
	return $user;
}

function wfu_current_user_allowed_action_remote($action, $filepath, $userid) {
	//first get file data from database, if exist
	$filerec = wfu_get_file_rec($filepath, false);

	if ( 0 == $userid ) return null;
	else $is_admin = user_can($userid, 'manage_options');
	if ( !$is_admin ) {
		return null;
	}
	return true;
}

function wfu_rename_file_prompt($file_code, $type, $error) {
	if ( $type == 'dir' ) return;
	
	$siteurl = site_url();

	$is_admin = current_user_can( 'manage_options' );
	//check if user is allowed to view file details
	if ( !$is_admin ) {
			return;
	}
	$file_code = wfu_sanitize_code($file_code);
	$dec_file = wfu_get_filepath_from_safe($file_code);
	if ( $dec_file === false ) return;
	
	//first extract sort info from dec_file
	$ret = wfu_extract_sortdata_from_path($dec_file);
	$dec_file = wfu_path_rel2abs($ret['path']);
	if ( $type == 'dir' && substr($dec_file, -1) == '/' ) $dec_file = substr($dec_file, 0, -1);

	//check if user is allowed to perform this action
	if ( !wfu_current_user_owes_file($dec_file) ) return;

	$parts = pathinfo($dec_file);
	$newname = $parts['basename'];
	$dir_code = wfu_safe_store_filepath(wfu_path_abs2rel($parts['dirname']).'[['.$ret['sort'].']]');

	$echo_str = "\n".'<div class="wrap">';
	if ( $error ) {
		$newname = $_SESSION['wfu_rename_file']['newname'];
		$echo_str .= "\n\t".'<div class="error">';
		$echo_str .= "\n\t\t".'<p>'.$_SESSION['wfu_rename_file_error'].'</p>';
		$echo_str .= "\n\t".'</div>';
	}
	$echo_str .= "\n\t".'<div style="margin-top:20px;">';
	if ( $is_admin ) $echo_str .= "\n\t\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&amp;action=file_browser&dir='.$dir_code.'" class="button" title="go back">Go back</a>';
	$echo_str .= "\n\t".'</div>';
	$echo_str .= "\n\t".'<h2 style="margin-bottom: 10px;">Rename '.( $type == 'dir' ? 'Folder' : 'File' ).'</h2>';
	if ( $is_admin ) $echo_str .= "\n\t".'<form enctype="multipart/form-data" name="renamefile" id="renamefile" method="post" action="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload" class="validate">';
	$echo_str .= "\n\t\t".'<input type="hidden" name="action" value="rename'.( $type == 'dir' ? 'dir' : 'file' ).'">';
	$echo_str .= "\n\t\t".'<input type="hidden" name="dir" value="'.$dir_code.'">';
	$echo_str .= "\n\t\t".'<input type="hidden" name="file" value="'.$file_code.'">';
	if ( $type == 'dir' ) $echo_str .= "\n\t\t".'<label>Enter new name for folder <strong>'.$dec_file.'</strong></label><br/>';
	elseif ( $is_admin ) $echo_str .= "\n\t\t".'<label>Enter new filename for file <strong>'.$dec_file.'</strong></label><br/>';
	$echo_str .= "\n\t\t".'<input name="wfu_newname" id="wfu_newname" type="text" value="'.$newname.'" style="width:50%;" />';
	$echo_str .= "\n\t\t".'<p class="submit">';
	$echo_str .= "\n\t\t\t".'<input type="submit" class="button-primary" name="submit" value="Rename">';
	$echo_str .= "\n\t\t\t".'<input type="submit" class="button-primary" name="submit" value="Cancel">';
	$echo_str .= "\n\t\t".'</p>';
	$echo_str .= "\n\t".'</form>';
	$echo_str .= "\n".'</div>';
	return $echo_str;
}

function wfu_rename_file($file_code, $type) {
	if ( $type == 'dir' ) return;
	
	$user = wp_get_current_user();
	$is_admin = current_user_can( 'manage_options' );
	//check if user is allowed to view file details
	if ( !$is_admin ) {
			return;
	}
	$file_code = wfu_sanitize_code($file_code);
	$dec_file = wfu_get_filepath_from_safe($file_code);
	if ( $dec_file === false ) return;
	
	$dec_file = wfu_path_rel2abs(wfu_flatten_path($dec_file));
	if ( $type == 'dir' && substr($dec_file, -1) == '/' ) $dec_file = substr($dec_file, 0, -1);
	if ( !file_exists($dec_file) ) return;

	//check if user is allowed to perform this action
	if ( !wfu_current_user_owes_file($dec_file) ) return;

	$parts = pathinfo($dec_file);
	$error = "";
	if ( isset($_POST['wfu_newname'])  && isset($_POST['submit']) ) {
		if ( $_POST['submit'] == "Rename" && $_POST['wfu_newname'] != $parts['basename'] ) {
			$new_file = $parts['dirname'].'/'.$_POST['wfu_newname'];
			if ( $_POST['wfu_newname'] == "" ) $error = 'Error: New '.( $type == 'dir' ? 'folder ' : 'file' ).'name cannot be empty!';
			elseif ( preg_match("/[^A-Za-z0-9_.#\-$]/", $_POST['wfu_newname']) ) $error = 'Error: name contained invalid characters that were stripped off! Please try again.';
			elseif ( substr($_POST['wfu_newname'], -1 - strlen($parts['extension'])) != '.'.$parts['extension'] ) $error = 'Error: new and old file name extensions must be identical! Please correct.';
			elseif ( wfu_file_extension_blacklisted($_POST['wfu_newname']) ) $error = 'Error: the new file name has an extension that is forbidden for security reasons. Please correct.';
			elseif ( file_exists($new_file) ) $error = 'Error: The '.( $type == 'dir' ? 'folder' : 'file' ).' <strong>'.$_POST['wfu_newname'].'</strong> already exists! Please choose another one.';
			else {
				//pre-log rename action
				if ( $type == 'file' ) $retid = wfu_log_action('rename:'.$new_file, $dec_file, $user->ID, '', 0, 0, '', null);
				//perform rename action
				if ( rename($dec_file, $new_file) == false ) $error = 'Error: Rename of '.( $type == 'dir' ? 'folder' : 'file' ).' <strong>'.$parts['basename'].'</strong> failed!';
				//revert log action if file was not renamed
				if ( $type == 'file' && !file_exists($new_file) ) wfu_revert_log_action($retid);
			}
		}
	}
	if ( $error != "" ) {
		$_SESSION['wfu_rename_file_error'] = $error;
		$_SESSION['wfu_rename_file']['newname'] = preg_replace("/[^A-Za-z0-9_.#\-$]/", "", $_POST['wfu_newname']);
	}
	return ( $error == "" );
}

function wfu_delete_file_prompt($file_code, $type, $referer) {
	if ( $type == 'dir' ) return;
	
	$siteurl = site_url();

	$is_admin = current_user_can( 'manage_options' );
	//check if user is allowed to view file details
	if ( !$is_admin ) {
			return;
	}
	if ( !is_array($file_code) ) $file_code = array( $file_code );
	$names = array();
	foreach ( $file_code as $index => $code ) {
		$file_code[$index] = wfu_sanitize_code($code);
		$dec_file = wfu_get_filepath_from_safe($file_code[$index]);
		if ( $dec_file === false ) unset($file_code[$index]);
		else {
			//first extract sort info from dec_file
			$ret = wfu_extract_sortdata_from_path($dec_file);
			$dec_file = wfu_path_rel2abs($ret['path']);
			if ( $type == 'dir' && substr($dec_file, -1) == '/' ) $dec_file = substr($dec_file, 0, -1);
			//check if user is allowed to perform this action
			if ( !wfu_current_user_owes_file($dec_file) ) unset($file_code[$index]);
			else {
				$parts = pathinfo($dec_file);
				array_push($names, $parts['basename']);
			}
		}
	}
	if ( count($file_code) == 0 ) return;
	$file_code_list = "list:".implode(",", $file_code);

	$referer_url = wfu_get_filepath_from_safe(wfu_sanitize_code($referer));
	$ret = wfu_extract_sortdata_from_path($referer_url);
	$referer_url = $ret['path'];

	$echo_str = "\n".'<div class="wrap">';
	$echo_str .= "\n\t".'<div style="margin-top:20px;">';
	if ( $is_admin ) $echo_str .= "\n\t\t".'<a href="'.$referer_url.'" class="button" title="go back">Go back</a>';
	$echo_str .= "\n\t".'</div>';
	$echo_str .= "\n\t".'<h2 style="margin-bottom: 10px;">Delete '.( $type == 'dir' ? 'Folder' : 'File'.( count($names) == 1 ? '' : 's' ) ).'</h2>';
	if ( $is_admin ) $echo_str .= "\n\t".'<form enctype="multipart/form-data" name="deletefile" id="deletefile" method="post" action="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload" class="validate">';
	$echo_str .= "\n\t\t".'<input type="hidden" name="action" value="delete'.( $type == 'dir' ? 'dir' : 'file' ).'">';
	$echo_str .= "\n\t\t".'<input type="hidden" name="referer" value="'.$referer.'">';
	$echo_str .= "\n\t\t".'<input type="hidden" name="file" value="'.$file_code_list.'">';
	if ( count($names) == 1 )
		$echo_str .= "\n\t\t".'<label>Are you sure that you want to delete '.( $type == 'dir' ? 'folder' : 'file' ).' <strong>'.$names[0].'</strong>?</label><br/>';
	else {
		$echo_str .= "\n\t\t".'<label>Are you sure that you want to delete '.( $type == 'dir' ? 'folder' : 'files' ).':';
		$echo_str .= "\n\t\t".'<ul style="padding-left: 20px; list-style: initial;">';
		foreach ( $names as $name )
			$echo_str .= "\n\t\t\t".'<li><strong>'.$name.'</strong></li>';
		$echo_str .= "\n\t\t".'</ul>';
	}
	$echo_str .= "\n\t\t".'<p class="submit">';
	$echo_str .= "\n\t\t\t".'<input type="submit" class="button-primary" name="submit" value="Delete">';
	$echo_str .= "\n\t\t\t".'<input type="submit" class="button-primary" name="submit" value="Cancel">';
	$echo_str .= "\n\t\t".'</p>';
	$echo_str .= "\n\t".'</form>';
	$echo_str .= "\n".'</div>';
	return $echo_str;
}

function wfu_delete_file($file_code, $type) {
	if ( $type == 'dir' ) return;
	
	$user = wp_get_current_user();
	$is_admin = current_user_can( 'manage_options' );
	//check if user is allowed to view file details
	if ( !$is_admin ) {
			return;
	}
	if ( !is_array($file_code) ) $file_code = array( $file_code );
	$dec_files = array();
	foreach ( $file_code as $index => $code ) {
		$file_code[$index] = wfu_sanitize_code($code);
		$dec_file = wfu_get_filepath_from_safe($file_code[$index]);
		if ( $dec_file !== false ) {
			$dec_file = wfu_path_rel2abs(wfu_flatten_path($dec_file));
			if ( $type == 'dir' && substr($dec_file, -1) == '/' ) $dec_file = substr($dec_file, 0, -1);
			//check if user is allowed to perform this action
			if ( wfu_current_user_owes_file($dec_file) ) array_push($dec_files, $dec_file);
		}
	}
	if ( count($dec_files) == 0 ) return;

	if ( isset($_POST['submit']) ) {
		if ( $_POST['submit'] == "Delete" ) {
			foreach ( $dec_files as $dec_file ) {
				//pre-log delete action
				if ( $type == 'file' ) wfu_delete_file_execute($dec_file, $user->ID);
				elseif ( $type == 'dir' && $dec_file != "" ) wfu_delTree($dec_file);
			}
		}
	}
	return true;
}

function wfu_create_dir_prompt($dir_code, $error) {
	return;
	
	$siteurl = site_url();

	if ( !current_user_can( 'manage_options' ) ) return;

	$dir_code = wfu_sanitize_code($dir_code);
	$dec_dir = wfu_get_filepath_from_safe($dir_code);
	if ( $dec_dir === false ) return;
	
	//first extract sort info from dec_dir
	$ret = wfu_extract_sortdata_from_path($dec_dir);
	$dec_dir = wfu_path_rel2abs($ret['path']);
	if ( substr($dec_dir, -1) != '/' ) $dec_dir .= '/';
	$newname = '';

	$echo_str = "\n".'<div class="wrap">';
	if ( $error ) {
		$newname = $_SESSION['wfu_create_dir']['newname'];
		$echo_str .= "\n\t".'<div class="error">';
		$echo_str .= "\n\t\t".'<p>'.$_SESSION['wfu_create_dir_error'].'</p>';
		$echo_str .= "\n\t".'</div>';
	}
	$echo_str .= "\n\t".'<div style="margin-top:20px;">';
	$echo_str .= "\n\t\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&amp;action=file_browser&dir='.$dir_code.'" class="button" title="go back">Go back</a>';
	$echo_str .= "\n\t".'</div>';
	$echo_str .= "\n\t".'<h2 style="margin-bottom: 10px;">Create Folder</h2>';
	$echo_str .= "\n\t".'<form enctype="multipart/form-data" name="createdir" id="createdir" method="post" action="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload" class="validate">';
	$echo_str .= "\n\t\t".'<input type="hidden" name="action" value="createdir">';
	$echo_str .= "\n\t\t".'<input type="hidden" name="dir" value="'.$dir_code.'">';
	$echo_str .= "\n\t\t".'<label>Enter the name of the new folder inside <strong>'.$dec_dir.'</strong></label><br/>';
	$echo_str .= "\n\t\t".'<input name="wfu_newname" id="wfu_newname" type="text" value="'.$newname.'" style="width:50%;" />';
	$echo_str .= "\n\t\t".'<p class="submit">';
	$echo_str .= "\n\t\t\t".'<input type="submit" class="button-primary" name="submit" value="Create">';
	$echo_str .= "\n\t\t\t".'<input type="submit" class="button-primary" name="submit" value="Cancel">';
	$echo_str .= "\n\t\t".'</p>';
	$echo_str .= "\n\t".'</form>';
	$echo_str .= "\n".'</div>';
	return $echo_str;
}

function wfu_create_dir($dir_code) {
	return;
	
	if ( !current_user_can( 'manage_options' ) ) return;

	$dir_code = wfu_sanitize_code($dir_code);
	$dec_dir = wfu_get_filepath_from_safe($dir_code);
	if ( $dec_dir === false ) return;

	$dec_dir = wfu_path_rel2abs(wfu_flatten_path($dec_dir));
	if ( substr($dec_dir, -1) != '/' ) $dec_dir .= '/';
	if ( !file_exists($dec_dir) ) return;
	$error = "";
	if ( isset($_POST['wfu_newname'])  && isset($_POST['submit']) ) {
		if ( $_POST['submit'] == "Create" ) {
			$new_dir = $dec_dir.$_POST['wfu_newname'];
			if ( $_POST['wfu_newname'] == "" ) $error = 'Error: New folder name cannot be empty!';
			elseif ( preg_match("/[^A-Za-z0-9_.#\-$]/", $_POST['wfu_newname']) ) $error = 'Error: name contained invalid characters that were stripped off! Please try again.';
			elseif ( file_exists($new_dir) ) $error = 'Error: The folder <strong>'.$_POST['wfu_newname'].'</strong> already exists! Please choose another one.';
			elseif ( mkdir($new_dir) == false ) $error = 'Error: Creation of folder <strong>'.$_POST['wfu_newname'].'</strong> failed!';
		}
	}
	if ( $error != "" ) {
		$_SESSION['wfu_create_dir_error'] = $error;
		$_SESSION['wfu_create_dir']['newname'] = preg_replace("/[^A-Za-z0-9_.#\-$]/", "", $_POST['wfu_newname']);
	}
	return ( $error == "" );
}

function wfu_include_file_prompt($file_code, $referer) {
	if ( !current_user_can( 'manage_options' ) ) return;
	$plugin_options = wfu_decode_plugin_options(get_option( "wordpress_file_upload_options" ));
	if ( $plugin_options['includeotherfiles'] != "1" ) return;
	
	$siteurl = site_url();
	if ( !is_array($file_code) ) $file_code = array( $file_code );
	$names = array();
	foreach ( $file_code as $index => $code ) {
		$file_code[$index] = wfu_sanitize_code($code);
		$dec_file = wfu_get_filepath_from_safe($file_code[$index]);
		if ( $dec_file === false ) unset($file_code[$index]);
		else {
			$dec_file = wfu_path_rel2abs(wfu_flatten_path($dec_file));
			//do not include file if it has a forbidden extention or it is already included
			if ( wfu_file_extension_blacklisted(wfu_basename($dec_file)) || wfu_get_file_rec($dec_file, false) != null )
				unset($file_code[$index]);
			else array_push($names, wfu_basename($dec_file));
		}
	}
	if ( count($file_code) == 0 ) return;
	$file_code_list = "list:".implode(",", $file_code);

	$referer_url = wfu_get_filepath_from_safe(wfu_sanitize_code($referer));
	$ret = wfu_extract_sortdata_from_path($referer_url);
	$referer_url = $ret['path'];

	$echo_str = "\n".'<div class="wrap">';
	$echo_str .= "\n\t".'<div style="margin-top:20px;">';
	$echo_str .= "\n\t\t".'<a href="'.$referer_url.'" class="button" title="go back">Go back</a>';
	$echo_str .= "\n\t".'</div>';
	$echo_str .= "\n\t".'<h2 style="margin-bottom: 10px;">Include File'.( count($names) == 1 ? '' : 's' ).'</h2>';
	$echo_str .= "\n\t".'<form enctype="multipart/form-data" name="includefile" id="includefile" method="post" action="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload" class="validate">';
	$echo_str .= "\n\t\t".'<input type="hidden" name="action" value="includefile">';
	$echo_str .= "\n\t\t".'<input type="hidden" name="referer" value="'.$referer.'">';
	$echo_str .= "\n\t\t".'<input type="hidden" name="file" value="'.$file_code_list.'">';
	if ( count($names) == 1 )
		$echo_str .= "\n\t\t".'<label>Are you sure that you want to include file <strong>'.$names[0].'</strong>?</label><br/>';
	else {
		$echo_str .= "\n\t\t".'<label>Are you sure that you want to include files:';
		$echo_str .= "\n\t\t".'<ul style="padding-left: 20px; list-style: initial;">';
		foreach ( $names as $name )
			$echo_str .= "\n\t\t\t".'<li><strong>'.$name.'</strong></li>';
		$echo_str .= "\n\t\t".'</ul>';
	}
	$echo_str .= "\n\t\t".'<p class="submit">';
	$echo_str .= "\n\t\t\t".'<input type="submit" class="button-primary" name="submit" value="Include">';
	$echo_str .= "\n\t\t\t".'<input type="submit" class="button-primary" name="submit" value="Cancel">';
	$echo_str .= "\n\t\t".'</p>';
	$echo_str .= "\n\t".'</form>';
	$echo_str .= "\n".'</div>';
	return $echo_str;
}

function wfu_include_file($file_code) {
	if ( !current_user_can( 'manage_options' ) ) return;
	$plugin_options = wfu_decode_plugin_options(get_option( "wordpress_file_upload_options" ));
	if ( $plugin_options['includeotherfiles'] != "1" ) return;

	if ( !is_array($file_code) ) $file_code = array( $file_code );
	$dec_files = array();
	foreach ( $file_code as $index => $code ) {
		$file_code[$index] = wfu_sanitize_code($code);
		$dec_file = wfu_get_filepath_from_safe($file_code[$index]);
		if ( $dec_file !== false ) {
			$dec_file = wfu_path_rel2abs(wfu_flatten_path($dec_file));
			//include file if it does not have a forbidden extention and it not already included
			if ( !wfu_file_extension_blacklisted(wfu_basename($dec_file)) && wfu_get_file_rec($dec_file, false) == null )
				array_push($dec_files, $dec_file);
		}
	}
	if ( count($dec_files) == 0 ) return;

	$user = wp_get_current_user();
	if ( isset($_POST['submit']) ) {
		if ( $_POST['submit'] == "Include" ) {
			foreach ( $dec_files as $dec_file )
				$fileid = wfu_log_action('include', $dec_file, $user->ID, '', '', get_current_blog_id(), '', null);
		}
	}
	return true;
}

function wfu_file_details($file_code, $errorstatus) {
	global $wpdb;
	$table_name1 = $wpdb->prefix . "wfu_log";
	$siteurl = site_url();

	$user = wp_get_current_user();
	$is_admin = current_user_can( 'manage_options' );
	//check if user is allowed to view file details
	if ( !$is_admin ) {
			return;
	}
	$file_code = wfu_sanitize_code($file_code);
	$dec_file = wfu_get_filepath_from_safe($file_code);
	if ( $dec_file === false ) return;

	//extract file browser data from $file variable
	$ret = wfu_extract_sortdata_from_path($dec_file);
	$filepath = wfu_path_rel2abs($ret['path']);
	
	//check if user is allowed to perform this action
	if ( !wfu_current_user_owes_file($filepath) ) return;

	//get file data from database with user data
	$filedata = wfu_get_file_rec($filepath, true);
	if ( $filedata == null ) return;

	//get all users
	$users = get_users();

	//extract sort info and construct contained dir
	$parts = pathinfo($filepath);
	$dir_code = wfu_safe_store_filepath(wfu_path_abs2rel($parts['dirname']).'[['.$ret['sort'].']]');

	$stat = stat($filepath);

	$echo_str = '<div class="regev_wrap">';
	if ( $errorstatus == 'error' ) {
		$echo_str .= "\n\t".'<div class="error">';
		$echo_str .= "\n\t\t".'<p>'.$_SESSION['wfu_filedetails_error'].'</p>';
		$echo_str .= "\n\t".'</div>';
	}
	//show file detais
	$echo_str .= "\n\t".'<h2>Detais of File: '.$parts['basename'].'</h2>';
	$echo_str .= "\n\t".'<div style="margin-top:10px;">';
	if ( $is_admin ) {
		$echo_str .= "\n\t\t".'<a href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&amp;action=file_browser&dir='.$dir_code.'" class="button" title="go back">Go back</a>';
		$echo_str .= "\n\t\t".'<form enctype="multipart/form-data" name="editfiledetails" id="editfiledetails" method="post" action="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&amp;action=edit_filedetails" class="validate">';
	}
	$echo_str .= "\n\t\t\t".'<h3 style="margin-bottom: 10px; margin-top: 40px;">Upload Details</h3>';
	$echo_str .= "\n\t\t\t".'<input type="hidden" name="action" value="edit_filedetails" />';
	$echo_str .= "\n\t\t\t".'<input type="hidden" name="dir" value="'.$dir_code.'">';
	$echo_str .= "\n\t\t\t".'<input type="hidden" name="file" value="'.$file_code.'">';
	$echo_str .= "\n\t\t\t".'<table class="form-table">';
	$echo_str .= "\n\t\t\t\t".'<tbody>';
	if ( $is_admin ) {
		$echo_str .= "\n\t\t\t\t\t".'<tr>';
		$echo_str .= "\n\t\t\t\t\t\t".'<th scope="row">';
		$echo_str .= "\n\t\t\t\t\t\t\t".'<label>Full Path</label>';
		$echo_str .= "\n\t\t\t\t\t\t".'</th>';
		$echo_str .= "\n\t\t\t\t\t\t".'<td>';
		$echo_str .= "\n\t\t\t\t\t\t\t".'<input type="text" value="'.$filepath.'" readonly="readonly" />';
		$echo_str .= "\n\t\t\t\t\t\t".'</td>';
		$echo_str .= "\n\t\t\t\t\t".'</tr>';
		$echo_str .= "\n\t\t\t\t\t".'<tr>';
		$echo_str .= "\n\t\t\t\t\t\t".'<th scope="row">';
		$echo_str .= "\n\t\t\t\t\t\t\t".'<label>Uploaded By User</label>';
		$echo_str .= "\n\t\t\t\t\t\t".'</th>';
		$echo_str .= "\n\t\t\t\t\t\t".'<td>';
		$echo_str .= "\n\t\t\t\t\t\t\t".'<select id="wfu_filedetails_users" disabled="disabled">';
		foreach ( $users as $userid => $user )
			$echo_str .= "\n\t\t\t\t\t\t\t\t".'<option value="'.$user->ID.'"'.( $filedata->uploaduserid == $user->ID ? ' selected="selected"' : '' ).'>'.$user->display_name.' ('.$user->user_login.')</option>';
		$echo_str .= "\n\t\t\t\t\t\t\t".'</select>';
		$echo_str .= "\n\t\t\t\t\t\t\t".'<a class="button" id="btn_change" href="" onclick="document.getElementById(\'wfu_filedetails_users\').disabled = false; this.style.display = \'none\'; document.getElementById(\'btn_ok\').style.display = \'inline-block\'; document.getElementById(\'btn_cancel\').style.display = \'inline-block\'; return false;"'.( $is_admin ? '' : ' style="display:none;"' ).'>Change User</a>';
		$echo_str .= "\n\t\t\t\t\t\t\t".'<a class="button" id="btn_ok" href="" onclick="document.getElementById(\'wfu_filedetails_users\').disabled = true; document.getElementById(\'btn_change\').style.display = \'inline-block\'; this.style.display=\'none\'; document.getElementById(\'btn_cancel\').style.display = \'none\'; document.getElementById(\'wfu_filedetails_userid\').value = document.getElementById(\'wfu_filedetails_users\').value; wfu_filedetails_changed(); return false;" style="display:none;">Ok</a>';
		$echo_str .= "\n\t\t\t\t\t\t\t".'<a class="button" id="btn_cancel" href="" onclick="document.getElementById(\'wfu_filedetails_users\').disabled = true; document.getElementById(\'btn_change\').style.display = \'inline-block\'; this.style.display=\'none\'; document.getElementById(\'btn_ok\').style.display = \'none\'; document.getElementById(\'wfu_filedetails_users\').value = document.getElementById(\'wfu_filedetails_userid\').value; return false;" style="display:none;">Cancel</a>';
		$echo_str .= "\n\t\t\t\t\t\t\t".'<input type="hidden" id="wfu_filedetails_userid" name="wfu_filedetails_userid" value="'.$filedata->uploaduserid.'" />';
		$echo_str .= "\n\t\t\t\t\t\t\t".'<input type="hidden" id="wfu_filedetails_userid_default" value="'.$filedata->uploaduserid.'" />';
		$echo_str .= "\n\t\t\t\t\t\t".'</td>';
		$echo_str .= "\n\t\t\t\t\t".'</tr>';
	}
	$echo_str .= "\n\t\t\t\t\t".'<tr>';
	$echo_str .= "\n\t\t\t\t\t\t".'<th scope="row">';
	$echo_str .= "\n\t\t\t\t\t\t\t".'<label>File Size</label>';
	$echo_str .= "\n\t\t\t\t\t\t".'</th>';
	$echo_str .= "\n\t\t\t\t\t\t".'<td>';
	$echo_str .= "\n\t\t\t\t\t\t\t".'<input type="text" value="'.$filedata->filesize.'" readonly="readonly" style="width:auto;" />';
	$echo_str .= "\n\t\t\t\t\t\t".'</td>';
	$echo_str .= "\n\t\t\t\t\t".'</tr>';
	$echo_str .= "\n\t\t\t\t\t".'<tr>';
	$echo_str .= "\n\t\t\t\t\t\t".'<th scope="row">';
	$echo_str .= "\n\t\t\t\t\t\t\t".'<label>File Date</label>';
	$echo_str .= "\n\t\t\t\t\t\t".'</th>';
	$echo_str .= "\n\t\t\t\t\t\t".'<td>';
	$echo_str .= "\n\t\t\t\t\t\t\t".'<input type="text" value="'.get_date_from_gmt(date("Y-m-d H:i:s", $stat['mtime']), "d/m/Y H:i:s").'" readonly="readonly" style="width:auto;" />';
	$echo_str .= "\n\t\t\t\t\t\t".'</td>';
	$echo_str .= "\n\t\t\t\t\t".'</tr>';
	$echo_str .= "\n\t\t\t\t\t".'<tr>';
	$echo_str .= "\n\t\t\t\t\t\t".'<th scope="row">';
	$echo_str .= "\n\t\t\t\t\t\t\t".'<label>Uploaded From Page</label>';
	$echo_str .= "\n\t\t\t\t\t\t".'</th>';
	$echo_str .= "\n\t\t\t\t\t\t".'<td>';
	$echo_str .= "\n\t\t\t\t\t\t\t".'<input type="text" value="'.get_the_title($filedata->pageid).' ('.$filedata->pageid.')'.'" readonly="readonly" style="width:50%;" />';
	$echo_str .= "\n\t\t\t\t\t\t".'</td>';
	$echo_str .= "\n\t\t\t\t\t".'</tr>';
	if ( $is_admin ) {
		$echo_str .= "\n\t\t\t\t\t".'<tr>';
		$echo_str .= "\n\t\t\t\t\t\t".'<th scope="row">';
		$echo_str .= "\n\t\t\t\t\t\t\t".'<label>Upload Plugin ID</label>';
		$echo_str .= "\n\t\t\t\t\t\t".'</th>';
		$echo_str .= "\n\t\t\t\t\t\t".'<td>';
		$echo_str .= "\n\t\t\t\t\t\t\t".'<input type="text" value="'.$filedata->sid.'" readonly="readonly" style="width:auto;" />';
		$echo_str .= "\n\t\t\t\t\t\t".'</td>';
		$echo_str .= "\n\t\t\t\t\t".'</tr>';
	}
	$echo_str .= "\n\t\t\t\t".'</tbody>';
	$echo_str .= "\n\t\t\t".'</table>';
	if ( $is_admin ) {
		//show history details
		$echo_str .= "\n\t\t\t".'<h3 style="margin-bottom: 10px; margin-top: 40px;">File History</h3>';
		$echo_str .= "\n\t\t\t".'<table class="form-table">';
		$echo_str .= "\n\t\t\t\t".'<tbody>';
		$echo_str .= "\n\t\t\t\t\t".'<tr>';
		$echo_str .= "\n\t\t\t\t\t\t".'<th scope="row">';
		$echo_str .= "\n\t\t\t\t\t\t\t".'<label></label>';
		$echo_str .= "\n\t\t\t\t\t\t".'</th>';
		$echo_str .= "\n\t\t\t\t\t\t".'<td>';
		//read all linked records
		$filerecs = array();
		array_push($filerecs, $filedata);
		$currec = $filedata;
		while ( $currec->linkedto > 0 ) {
			$currec = $wpdb->get_row('SELECT * FROM '.$table_name1.' WHERE idlog = '.$currec->linkedto);
			if ( $currec != null ) array_push($filerecs, $currec);
			else break;
		}
		//construct report from db records
		$rep = '';
		foreach ( $filerecs as $filerec ) {
			$username = wfu_get_username_by_id($filerec->userid);
			$fileparts = pathinfo($filerec->filepath);
			if ( $rep != '' ) $rep .= "<br />";
			$rep .= '<strong>['.get_date_from_gmt($filerec->date_from).']</strong> ';
			if ( $filerec->action == 'upload' )
				$rep .= 'File uploaded with name <strong>'.$fileparts['basename'].'</strong> by user <strong>'.$username.'</strong>';
			elseif ( $filerec->action == 'include' )
				$rep .= 'File included in database with name <strong>'.$fileparts['basename'].'</strong> by user <strong>'.$username.'</strong>';
			elseif ( $filerec->action == 'download' )
				$rep .= 'File downloaded by user <strong>'.$username.'</strong>';
			elseif ( $filerec->action == 'rename' )
				$rep .= 'File renamed to <strong>'.$fileparts['basename'].'</strong> by user <strong>'.$username.'</strong>';
			elseif ( $filerec->action == 'delete' )
				$rep .= 'File deleted by user <strong>'.$username.'</strong>';
			elseif ( $filerec->action == 'modify' )
				$rep .= 'File userdata modified by user <strong>'.$username.'</strong>';
			elseif ( $filerec->action == 'changeuser' )
				$rep .= 'File upload user modified by user <strong>'.$username.'</strong>';
		}
		$echo_str .= "\n\t\t\t\t\t\t\t".'<div style="border:1px solid #dfdfdf; border-radius:3px; width:50%; overflow:scroll; padding:6px; height:100px; background-color:#eee;">';
		$echo_str .= "\n\t\t\t\t\t\t\t".'<span style="white-space:nowrap;">'.$rep.'</span>';
		$echo_str .= "\n\t\t\t\t\t\t\t".'</div>';
		$echo_str .= "\n\t\t\t\t\t\t".'</td>';
		$echo_str .= "\n\t\t\t\t\t".'</tr>';
		$echo_str .= "\n\t\t\t\t".'</tbody>';
		$echo_str .= "\n\t\t\t".'</table>';
	}

	$echo_str .= "\n\t\t\t".'<h3 style="margin-bottom: 10px; margin-top: 40px;">User Data Details</h3>';
	$echo_str .= "\n\t\t\t".'<table class="form-table">';
	$echo_str .= "\n\t\t\t\t".'<tbody>';
	if ( count($filedata->userdata) > 0 ) {
		foreach ( $filedata->userdata as $userdata ) {
			$echo_str .= "\n\t\t\t\t\t".'<tr>';
			$echo_str .= "\n\t\t\t\t\t\t".'<th scope="row">';
			$echo_str .= "\n\t\t\t\t\t\t\t".'<label>'.$userdata->property.'</label>';
			$echo_str .= "\n\t\t\t\t\t\t".'</th>';
			$echo_str .= "\n\t\t\t\t\t\t".'<td>';
//			$echo_str .= "\n\t\t\t\t\t\t\t".'<input id="wfu_filedetails_userdata_value_'.$userdata->propkey.'" name="wfu_filedetails_userdata" type="text"'.( $is_admin ? '' : ' readonly="readonly"' ).' value="'.$userdata->propvalue.'" />';
			$echo_str .= "\n\t\t\t\t\t\t\t".'<textarea id="wfu_filedetails_userdata_value_'.$userdata->propkey.'" name="wfu_filedetails_userdata" '.( $is_admin ? '' : ' readonly="readonly"' ).' value="'.$userdata->propvalue.'">'.$userdata->propvalue.'</textarea>';
			$echo_str .= "\n\t\t\t\t\t\t\t".'<input id="wfu_filedetails_userdata_default_'.$userdata->propkey.'" type="hidden" value="'.$userdata->propvalue.'" />';
			$echo_str .= "\n\t\t\t\t\t\t\t".'<input id="wfu_filedetails_userdata_'.$userdata->propkey.'" name="wfu_filedetails_userdata_'.$userdata->propkey.'" type="hidden" value="'.$userdata->propvalue.'" />';
			$echo_str .= "\n\t\t\t\t\t\t".'</td>';
			$echo_str .= "\n\t\t\t\t\t".'</tr>';
		}
	}
	else {
		$echo_str .= "\n\t\t\t\t\t".'<tr>';
		$echo_str .= "\n\t\t\t\t\t\t".'<th scope="row">';
		$echo_str .= "\n\t\t\t\t\t\t\t".'<label>No user data</label>';
		$echo_str .= "\n\t\t\t\t\t\t".'</th>';
		$echo_str .= "\n\t\t\t\t\t\t".'<td></td>';
		$echo_str .= "\n\t\t\t\t\t".'</tr>';
	}
	$echo_str .= "\n\t\t\t\t".'</tbody>';
	$echo_str .= "\n\t\t\t".'</table>';
	if ( $is_admin ) {
		$echo_str .= "\n\t\t\t".'<p class="submit">';
		$echo_str .= "\n\t\t\t\t".'<input id="dp_filedetails_submit_fields" type="submit" class="button-primary" name="submit" value="Update" disabled="disabled" />';
		$echo_str .= "\n\t\t\t".'</p>';
	}
	$echo_str .= "\n\t\t".'</form>';
	$echo_str .= "\n\t".'</div>';
	$handler = 'function() { wfu_Attach_FileDetails_Admin_Events(); }';
	$echo_str .= "\n\t".'<script type="text/javascript">if(window.addEventListener) { window.addEventListener("load", '.$handler.', false); } else if(window.attachEvent) { window.attachEvent("onload", '.$handler.'); } else { window["onload"] = '.$handler.'; }</script>';
	$echo_str .= '</div>';
    
	return $echo_str;
}

function wfu_edit_filedetails($file_code) {
	global $wpdb;
	$table_name2 = $wpdb->prefix . "wfu_userdata";

	$user = wp_get_current_user();
	$is_admin = current_user_can( 'manage_options' );
	//check if user is allowed to view file details
	if ( !$is_admin ) {
			return;
	}
	$file_code = wfu_sanitize_code($file_code);
	$dec_file = wfu_get_filepath_from_safe($file_code);
	if ( $dec_file === false ) return;

	$dec_file = wfu_path_rel2abs(wfu_flatten_path($dec_file));

	//check if user is allowed to perform this action
	if ( !wfu_current_user_owes_file($dec_file) ) return;

	//get file data from database with user data
	$filedata = wfu_get_file_rec($dec_file, true);
	if ( $filedata == null ) return;

	if ( isset($_POST['submit']) ) {
		if ( $_POST['submit'] == "Update" ) {
			if ( !is_array($filedata->userdata) ) $filedata->userdata = array();
			//check for errors
			$is_error = false;
			foreach ( $filedata->userdata as $userdata ) {
				if ( !isset($_POST['wfu_filedetails_userdata_'.$userdata->propkey]) ) {
					$is_error = true;
					break;
				}
			}
			if ( !$is_error ) {
				$now_date = date('Y-m-d H:i:s');
				$userdata_count = 0;
				foreach ( $filedata->userdata as $userdata ) {
					$userdata_count ++;
					//make existing userdata record obsolete
					$wpdb->update($table_name2,
						array( 'date_to' => $now_date ),
						array( 'uploadid' => $userdata->uploadid, 'propkey'  => $userdata->propkey ),
						array( '%s' ),
						array( '%s', '%s' )
					);
					//insert new userdata record
					$wpdb->insert($table_name2,
						array(
							'uploadid' 	=> $userdata->uploadid,
							'property' 	=> $userdata->property,
							'propkey' 	=> $userdata->propkey,
							'propvalue' 	=> $_POST['wfu_filedetails_userdata_'.$userdata->propkey],
							'date_from' 	=> $now_date,
							'date_to' 	=> 0
						),
						array(
							'%s',
							'%s',
							'%d',
							'%s',
							'%s',
							'%s'
						)
					);
				}
				if ( $userdata_count > 0 ) wfu_log_action('modify:'.$now_date, $dec_file, $user->ID, '', 0, 0, '', null);
			}
			if ( isset($_POST['wfu_filedetails_userid']) && $_POST['wfu_filedetails_userid'] != $filedata->uploaduserid ) {
				wfu_log_action('changeuser:'.$_POST['wfu_filedetails_userid'], $dec_file, $user->ID, '', 0, 0, '', null);
			}
		}
	}
	return true;
}

?>
