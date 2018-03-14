<?php

function wfu_mk_dir_deep($conn_id, $basepath, $path) {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	@ftp_chdir($conn_id, $basepath);
	$parts = explode('/', $path);
	foreach ( $parts as $part ) {
		if( !@ftp_chdir($conn_id, $part) ) {
			ftp_mkdir($conn_id, $part);
			ftp_chdir($conn_id, $part);
			ftp_chmod($conn_id, 493, $part);
		}
	}
}

function wfu_is_dir($path, $ftpdata) {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	$result = false;
	if ( substr($path, 0, 7) == "sftp://" ) {
		$ftpdata_flat =  str_replace(array('\:', '\@'), array('\_', '\_'), $ftpdata);
		$pos1 = strpos($ftpdata_flat, ":");
		$pos2 = strpos($ftpdata_flat, "@");
		if ( $pos1 && $pos2 && $pos2 > $pos1 ) {
			$ftp_username = str_replace(array('\:', '\@'), array(':', '@'), substr($ftpdata, 0, $pos1));
			$ftp_password = str_replace(array('\:', '\@'), array(':', '@'), substr($ftpdata, $pos1 + 1, $pos2 - $pos1 - 1));
			$ftp_host = substr($ftpdata, $pos2 + 1);
			$ftp_port = preg_replace("/^[^:]*:?/", "", $ftp_host);
			$ftp_host_clean = preg_replace("/:.*/", "", $ftp_host);
			if ( substr($ftp_port, 0, 1) == "s" ) {
				$ftp_port = substr($ftp_port, 1);
				if ( $ftp_port == "" ) $ftp_port = "22";
				$ftp_host = $ftp_host_clean.":".$ftp_port;
				$flat_host = preg_replace("/^(.*\.)?([^.]*\..*)$/", "$2", $ftp_host);
				$pos1 = strpos($path, $flat_host);
				if ( $pos1 ) {
					$path = substr($path, $pos1 + strlen($flat_host));
					$conn = ssh2_connect($ftp_host_clean, $ftp_port);
					if ( $conn && @ssh2_auth_password($conn, $ftp_username, $ftp_password) ) {
						$sftp = @ssh2_sftp($conn);
						if ( $sftp ) {
							$result = is_dir('ssh2.sftp://'.$sftp.$path);
						}
					}
				}
			}
		}
		
	}
	else $result = is_dir($path);
	
	return $result;
}

function wfu_create_directory($path, $method, $ftpdata) {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	$ret_message = "";
	if ( $method == "" || $method == "normal" ) {
		mkdir($path, 0777, true);
	}
	else if ( $method == "ftp" && $ftpdata != "" ) {
		$ftpdata_flat =  str_replace(array('\:', '\@'), array('\_', '\_'), $ftpdata);
		$pos1 = strpos($ftpdata_flat, ":");
		$pos2 = strpos($ftpdata_flat, "@");
		if ( $pos1 && $pos2 && $pos2 > $pos1 ) {
			$ftp_username = str_replace(array('\:', '\@'), array(':', '@'), substr($ftpdata, 0, $pos1));
			$ftp_password = str_replace(array('\:', '\@'), array(':', '@'), substr($ftpdata, $pos1 + 1, $pos2 - $pos1 - 1));
			$ftp_host = substr($ftpdata, $pos2 + 1);
			$ftp_port = preg_replace("/^[^:]*:?/", "", $ftp_host);
			$ftp_host_clean = preg_replace("/:.*/", "", $ftp_host);
			$is_sftp = false;
			if ( substr($ftp_port, 0, 1) == "s" ) {
				$is_sftp = true;
				$ftp_port = substr($ftp_port, 1);
				if ( $ftp_port == "" ) $ftp_port = "22";
			}
			if ( $ftp_port != "" ) $ftp_host = $ftp_host_clean.":".$ftp_port;
			$flat_host = preg_replace("/^(.*\.)?([^.]*\..*)$/", "$2", $ftp_host);
			$pos1 = strpos($path, $flat_host);
			if ( $pos1 ) {
				$path = substr($path, $pos1 + strlen($flat_host));
				if ( $is_sftp && $ftp_port != "" ) {
					wfu_create_dir_deep_sftp($ftp_host_clean, $ftp_port, $ftp_username, $ftp_password, $path);
				}
				else {
					if ( $ftp_port != "" ) $conn_id = ftp_connect($ftp_host_clean, $ftp_port);
					else $conn_id = ftp_connect($ftp_host_clean);
					$login_result = ftp_login($conn_id, $ftp_username, $ftp_password);
					if ( $conn_id && $login_result ) {
						wfu_mk_dir_deep($conn_id, '/', $path);
					}
					else {
						$ret_message = WFU_ERROR_ADMIN_FTPINFO_INVALID;
					}
					ftp_quit($conn_id);
				}
			}
			else {
				$ret_message = WFU_ERROR_ADMIN_FTPFILE_RESOLVE;
			}
		}

		else {
			$ret_message = WFU_ERROR_ADMIN_FTPINFO_EXTRACT;
		}
	}
	else {
		$ret_message = WFU_ERROR_ADMIN_FTPINFO_INVALID;
	}
	return $ret_message;
}


function wfu_upload_file($source, $target, $method, $ftpdata, $passive, $fileperms) {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	$ret_array = array();
	$ret_array["uploaded"] = false;
	$ret_array["admin_message"] = "";
	$ret_message = "";
	$target_perms = substr(sprintf('%o', fileperms(dirname($target))), -4);
	$target_perms = octdec($target_perms);
	$target_perms = (int)$target_perms;
	if ( $method == "" || $method == "normal" ) {
		$ret_array["uploaded"] = move_uploaded_file($source, $target);
		if ( !$ret_array["uploaded"] && !is_writable(dirname($target)) ) {
			$ret_message = WFU_ERROR_ADMIN_DIR_PERMISSION;
		}
	}
	elseif ( $method == "ftp" &&  $ftpdata != "" ) {
		$result = false;
		$ftpdata_flat =  str_replace(array('\:', '\@'), array('\_', '\_'), $ftpdata);
		$pos1 = strpos($ftpdata_flat, ":");
		$pos2 = strpos($ftpdata_flat, "@");
		if ( $pos1 && $pos2 && $pos2 > $pos1 ) {
			$ftp_username = str_replace(array('\:', '\@'), array(':', '@'), substr($ftpdata, 0, $pos1));
			$ftp_password = str_replace(array('\:', '\@'), array(':', '@'), substr($ftpdata, $pos1 + 1, $pos2 - $pos1 - 1));
			$ftp_host = substr($ftpdata, $pos2 + 1);
			$ftp_port = preg_replace("/^[^:]*:?/", "", $ftp_host);
			$ftp_host_clean = preg_replace("/:.*/", "", $ftp_host);
			$is_sftp = false;
			if ( substr($ftp_port, 0, 1) == "s" ) {
				$is_sftp = true;
				$ftp_port = substr($ftp_port, 1);
				if ( $ftp_port == "" ) $ftp_port = "22";
			}
			if ( $ftp_port != "" ) $ftp_host = $ftp_host_clean.":".$ftp_port;
			$flat_host = preg_replace("/^(.*\.)?([^.]*\..*)$/", "$2", $ftp_host);
			$pos1 = strpos($target, $flat_host);
			if ( $pos1 ) {
				$target = substr($target, $pos1 + strlen($flat_host));
				if ( $is_sftp && $ftp_port != "" ) {
					$ret_message = wfu_upload_file_sftp($ftp_host_clean, $ftp_port, $ftp_username, $ftp_password, $source, $target, $fileperms);
					$ret_array["uploaded"] = ( $ret_message == "" );
					unlink($source);
				}
				else {
					if ( $ftp_port != "" ) $conn_id = ftp_connect($ftp_host_clean, $ftp_port);
					else $conn_id = ftp_connect($ftp_host_clean);
					$login_result = ftp_login($conn_id, $ftp_username, $ftp_password);
					if ( $conn_id && $login_result ) {
						if ( $passive == "true" ) ftp_pasv($conn_id, true);
//						$temp_fname = tempnam(dirname($target), "tmp");
//						move_uploaded_file($source, $temp_fname);
//						ftp_chmod($conn_id, 0755, dirname($target));
						$ret_array["uploaded"] = ftp_put($conn_id, $target, $source, FTP_BINARY);
						//apply user-defined permissions to file
						$fileperms = trim($fileperms);
						if ( strlen($fileperms) == 4 && sprintf("%04o", octdec($fileperms)) == $fileperms ) {
							$fileperms = octdec($fileperms);
							$fileperms = (int)$fileperms;
							ftp_chmod($conn_id, $fileperms, $target);
						}
//						ftp_chmod($conn_id, 0755, $target);
//						ftp_chmod($conn_id, $target_perms, dirname($target));
						unlink($source);
						if ( !$ret_array["uploaded"] ) {
							$ret_message = WFU_ERROR_ADMIN_DIR_PERMISSION;
						}
					}
					else {
						$ret_message = WFU_ERROR_ADMIN_FTPINFO_INVALID;
					}
					ftp_quit($conn_id);
				}
			}
			else {
				$ret_message = WFU_ERROR_ADMIN_FTPFILE_RESOLVE;
			}
		}
		else {
			$ret_message = WFU_ERROR_ADMIN_FTPINFO_EXTRACT.$ftpdata_flat;
		}
	}		
	else {
		$ret_message = WFU_ERROR_ADMIN_FTPINFO_INVALID;
	}

	$ret_array["admin_message"] = $ret_message;
	return $ret_array;
}

function wfu_upload_file_sftp($ftp_host, $ftp_port, $ftp_username, $ftp_password, $source, $target, $fileperms) {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	$ret_message = "";
	$conn = @ssh2_connect($ftp_host, $ftp_port);
	if ( !$conn ) $ret_message = WFU_ERROR_ADMIN_FTPHOST_FAIL;
	else {
		if ( !@ssh2_auth_password($conn, $ftp_username, $ftp_password) ) $ret_message = WFU_ERROR_ADMIN_FTPLOGIN_FAIL;
		else {
			$sftp = @ssh2_sftp($conn);
			if ( !$sftp ) $ret_message = WFU_ERROR_ADMIN_SFTPINIT_FAIL;
			else {
				$f = @fopen("ssh2.sftp://$sftp$target", 'w');
				if ( !$f ) $ret_message = WFU_ERROR_ADMIN_FTPFILE_RESOLVE;
				else {
					$contents = @file_get_contents($source);
					if ( $contents === false ) $ret_message = WFU_ERROR_ADMIN_FTPSOURCE_FAIL;
					else {
						if ( @fwrite($f, $contents) === false ) $ret_message = WFU_ERROR_ADMIN_FTPTRANSFER_FAIL;
						//apply user-defined permissions to file
						$fileperms = trim($fileperms);
						if ( strlen($fileperms) == 4 && sprintf("%04o", octdec($fileperms)) == $fileperms ) {
							$fileperms = octdec($fileperms);
							$fileperms = (int)$fileperms;
							ssh2_sftp_chmod($sftp, $target, $fileperms);
						}
					}
					@fclose($f);
				}
			}
		}
	}
	
	return $ret_message;
}

function wfu_create_dir_deep_sftp($ftp_host, $ftp_port, $ftp_username, $ftp_password, $path) {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	$ret_message = "";
	$conn = @ssh2_connect($ftp_host, $ftp_port);
	if ( !$conn ) $ret_message = WFU_ERROR_ADMIN_FTPHOST_FAIL;
	else {
		if ( !@ssh2_auth_password($conn, $ftp_username, $ftp_password) ) $ret_message = WFU_ERROR_ADMIN_FTPLOGIN_FAIL;
		else {
			$sftp = @ssh2_sftp($conn);
			if ( !$sftp ) $ret_message = WFU_ERROR_ADMIN_SFTPINIT_FAIL;
			else {
				ssh2_sftp_mkdir($sftp, $path, 493, true );
			}
		}
	}
	
	return $ret_message;
}

?>
