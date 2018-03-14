<?php

function wfu_view_log($page = 1, $only_table_rows = false) {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	global $wpdb;
	$siteurl = site_url();
	$table_name1 = $wpdb->prefix . "wfu_log";
	$table_name2 = $wpdb->prefix . "wfu_userdata";
	$plugin_options = wfu_decode_plugin_options(get_option( "wordpress_file_upload_options" ));

	if ( !current_user_can( 'manage_options' ) ) return;
	//get log data from database
	$files_total = $wpdb->get_var('SELECT COUNT(idlog) FROM '.$table_name1);
	$filerecs = $wpdb->get_results('SELECT * FROM '.$table_name1.' ORDER BY date_from DESC'.( WFU_VAR("WFU_HISTORYLOG_TABLE_MAXROWS") > 0 ? ' LIMIT '.WFU_VAR("WFU_HISTORYLOG_TABLE_MAXROWS").' OFFSET '.(($page - 1) * (int)WFU_VAR("WFU_HISTORYLOG_TABLE_MAXROWS")) : '' ));

	$echo_str = "";
	if ( !$only_table_rows ) {
		$echo_str .= "\n".'<div class="wrap">';
		$echo_str .= "\n\t".'<h2>Wordpress File Upload Control Panel</h2>';
		$echo_str .= "\n\t".'<div style="margin-top:20px;">';
		$echo_str .= wfu_generate_dashboard_menu("\n\t\t", "View Log");
		$echo_str .= "\n\t".'<div style="position:relative;">';
		$echo_str .= wfu_add_loading_overlay("\n\t\t", "historylog");
		$echo_str .= "\n\t\t".'<div class="wfu_historylog_header" style="width: 100%;">';
		if ( WFU_VAR("WFU_HISTORYLOG_TABLE_MAXROWS") > 0 ) {
			$pages = ceil($files_total / WFU_VAR("WFU_HISTORYLOG_TABLE_MAXROWS"));
			$echo_str .= wfu_add_pagination_header("\n\t\t\t", "historylog", 1, $pages);
		}
		$echo_str .= "\n\t\t".'</div>';
		$echo_str .= "\n\t\t".'<table id="wfu_historylog_table" class="wp-list-table widefat fixed striped">';
		$echo_str .= "\n\t\t\t".'<thead>';
		$echo_str .= "\n\t\t\t\t".'<tr>';
		$echo_str .= "\n\t\t\t\t\t".'<th scope="col" width="5%" style="text-align:center;">';
		$echo_str .= "\n\t\t\t\t\t\t".'<label>#</label>';
		$echo_str .= "\n\t\t\t\t\t".'</th>';
		$echo_str .= "\n\t\t\t\t\t".'<th scope="col" width="15%" style="text-align:left;">';
		$echo_str .= "\n\t\t\t\t\t\t".'<label>Date</label>';
		$echo_str .= "\n\t\t\t\t\t".'</th>';
		$echo_str .= "\n\t\t\t\t\t".'<th scope="col" width="10%" style="text-align:center;">';
		$echo_str .= "\n\t\t\t\t\t\t".'<label>Action</label>';
		$echo_str .= "\n\t\t\t\t\t".'</th>';
		$echo_str .= "\n\t\t\t\t\t".'<th scope="col" width="30%" style="text-align:left;">';
		$echo_str .= "\n\t\t\t\t\t\t".'<label>File</label>';
		$echo_str .= "\n\t\t\t\t\t".'</th>';
		$echo_str .= "\n\t\t\t\t\t".'<th scope="col" width="15%" style="text-align:center;">';
		$echo_str .= "\n\t\t\t\t\t\t".'<label>User</label>';
		$echo_str .= "\n\t\t\t\t\t".'</th>';
		$echo_str .= "\n\t\t\t\t\t".'<th scope="col" width="25%" style="text-align:left;">';
		$echo_str .= "\n\t\t\t\t\t\t".'<label>Remarks</label>';
		$echo_str .= "\n\t\t\t\t\t".'</th>';
		$echo_str .= "\n\t\t\t\t".'</tr>';
		$echo_str .= "\n\t\t\t".'</thead>';
		$echo_str .= "\n\t\t\t".'<tbody>';
	}

	$userdatarecs = $wpdb->get_results('SELECT * FROM '.$table_name2);
	$deletedfiles = array();
	$filecodes = array();
	$time0 = strtotime("0000-00-00 00:00:00");
	$i = ($page - 1) * (int)WFU_VAR("WFU_HISTORYLOG_TABLE_MAXROWS");
	foreach ( $filerecs as $filerec ) {
		$remarks = '';
		if ( $filerec->action == 'delete' ) array_push($deletedfiles, $filerec->linkedto);
		elseif ( $filerec->action == 'rename' ) {
			$prevfilepath = '';
			$prevfilerec = wfu_get_file_rec_from_id($filerec->linkedto);
			if ( $prevfilerec != null ) $prevfilepath = $prevfilerec->filepath;
			if ( $prevfilepath != '' )
				$remarks = "\n\t\t\t\t\t\t".'<label>Previous filepath: '.$prevfilepath.'</label>';
		}
		elseif ( $filerec->action == 'upload' || $filerec->action == 'modify' || $filerec->action == 'datasubmit' ) {
			foreach ( $userdatarecs as $userdata ) {
				if ( $userdata->uploadid == $filerec->uploadid ) {
					$userdata_datefrom = strtotime($userdata->date_from);
					$userdata_dateto = strtotime($userdata->date_to);
					$filerec_datefrom = strtotime($filerec->date_from);
					if ( $filerec_datefrom >= $userdata_datefrom && ( $userdata_dateto == $time0 || $filerec_datefrom < $userdata_dateto ) )
						$remarks .= "\n\t\t\t\t\t\t\t".'<option>'.$userdata->property.': '.$userdata->propvalue.'</option>';
				}
			}
			if ( $remarks != '' ) {
				$remarks = "\n\t\t\t\t\t\t".'<select multiple="multiple" style="width:100%; height:40px; background:none; font-size:small; overflow:scroll;">'.$remarks;
				$remarks .= "\n\t\t\t\t\t\t".'</select>';
			}
		}
		elseif ( $filerec->action == 'changeuser' ) {
			$prevuploaduserid = '';
			$prevfilerec = wfu_get_file_rec_from_id($filerec->linkedto);
			if ( $prevfilerec != null ) $prevuploaduserid = $prevfilerec->uploaduserid;
			if ( $prevuploaduserid != '' ) {
				$prevuploaduser = wfu_get_username_by_id($prevuploaduserid);
				$remarks = "\n\t\t\t\t\t\t".'<label>Previous upload user: '.$prevuploaduser.'</label>';
			}
		}
		elseif ( $filerec->action == 'other' ) {
			$info = $filerec->filepath;
			$filerec->filepath = '';
			$remarks = "\n\t\t\t\t\t\t".'<textarea style="width:100%; resize:vertical; background:none;" readonly="readonly">'.$info.'</textarea>';
		}
		$i ++;
		$otheraction = ( $filerec->action == 'other' );
		$echo_str .= "\n\t\t\t\t".'<tr>';
		$echo_str .= "\n\t\t\t\t\t".'<td style="padding: 5px 5px 5px 10px; text-align:center;">'.$i.'</td>';
		$echo_str .= "\n\t\t\t\t\t".'<td style="padding: 5px 5px 5px 10px; text-align:left;">'.get_date_from_gmt($filerec->date_from).'</td>';
		$echo_str .= "\n\t\t\t\t\t".'<td style="padding: 5px 5px 5px 10px; text-align:center;">'.$filerec->action.'</td>';
		if ( !$otheraction ) {	
			$echo_str .= "\n\t\t\t\t\t".'<td style="padding: 5px 5px 5px 10px; text-align:left;">';
			if ( $filerec->action == 'datasubmit' ) $echo_str .= "\n\t\t\t\t\t\t".'<span>N/A</span>';
			elseif ( in_array($filerec->linkedto, $deletedfiles) || in_array($filerec->idlog, $deletedfiles) ) $echo_str .= "\n\t\t\t\t\t\t".'<span>'.$filerec->filepath.'</span>';
			else {
				$lid = 0;
				if ( $filerec->action == 'upload' || $filerec->action == 'include' ) $lid = $filerec->idlog;
				elseif ( $filerec->linkedto > 0 ) $lid = $filerec->linkedto;
				if ( $lid > 0 ) {
					if ( !isset($filecodes[$lid]) ) $filecodes[$lid] = wfu_safe_store_filepath($filerec->filepath);
					$echo_str .= "\n\t\t\t\t\t\t".'<a class="row-title" href="'.$siteurl.'/wp-admin/options-general.php?page=wordpress_file_upload&action=file_details&file='.$filecodes[$lid].'" title="View and edit file details" style="font-weight:normal;">'.$filerec->filepath.'</a>';
				}
				else $echo_str .= "\n\t\t\t\t\t\t".'<span>'.$filerec->filepath.'</span>';
			}
			$echo_str .= "\n\t\t\t\t\t".'</td>';
			$echo_str .= "\n\t\t\t\t\t".'<td style="padding: 5px 5px 5px 10px; text-align:center;">'.wfu_get_username_by_id($filerec->userid).'</td>';
		}
		$echo_str .= "\n\t\t\t\t\t".'<td style="padding: 5px 5px 5px 10px; text-align:left;"'.( $otheraction ? ' colspan="3"' : '' ).'>';
		$echo_str .= $remarks;
		$echo_str .= "\n\t\t\t\t\t".'</td>';
		$echo_str .= "\n\t\t\t\t".'</tr>';
	}
	if ( !$only_table_rows ) {
		$echo_str .= "\n\t\t\t".'</tbody>';
		$echo_str .= "\n\t\t".'</table>';
		$echo_str .= "\n\t".'</div>';
		$echo_str .= "\n\t".'</div>';
		$echo_str .= "\n".'</div>';
	}

	return $echo_str;
}

?>
