<?php
if( !session_id() ) { session_start(); }
include_once( dirname(__FILE__).'/lib/wfu_functions.php' );
include_once( dirname(__FILE__).'/lib/wfu_security.php' );
wfu_download_file();

function wfu_download_file() {
	$file_code = (isset($_POST['file']) ? $_POST['file'] : (isset($_GET['file']) ? $_GET['file'] : ''));
	$ticket = (isset($_POST['ticket']) ? $_POST['ticket'] : (isset($_GET['ticket']) ? $_GET['ticket'] : ''));
	if ( $file_code == '' || $ticket == '' ) die();
	
	$ticket = wfu_sanitize_code($ticket);
	//if download ticket does not exist or is expired die
	if ( !isset($_SESSION['wfu_download_ticket_'.$ticket]) || time() > $_SESSION['wfu_download_ticket_'.$ticket] ) die();
	//destroy ticket so it cannot be used again
	unset($_SESSION['wfu_download_ticket_'.$ticket]);
	
	$file_code = wfu_sanitize_code($file_code);
	//if file_code starts with exportdata, then this is a request for export of
	//uploaded file data, so disposition_name will not be the filename of the file
	//but wfu_export.csv; also set flag to delete file after download operation
	if ( substr($file_code, 0, 10) == "exportdata" ) {
		$file_code = substr($file_code, 10);
		$filepath = wfu_get_filepath_from_safe($file_code);
		$disposition_name = "wfu_export.csv";
		$delete_file = true;
	}
	else {
		$filepath = wfu_get_filepath_from_safe($file_code);
		if ( $filepath === false ) die();
		$filepath = wfu_flatten_path($filepath);
		if ( substr($filepath, 0, 1) == "/" ) $filepath = substr($filepath, 1);
		$filepath = ( substr($filepath, 0, 6) == 'ftp://' || substr($filepath, 0, 7) == 'ftps://' || substr($filepath, 0, 7) == 'sftp://' ? $filepath : $_SESSION['wfu_ABSPATH'].$filepath );
		$disposition_name = wfu_basename($filepath);
		$delete_file = false;
	}
	//check that file exists
	if ( !file_exists($filepath) ) {
		$_SESSION['wfu_download_status_'.$ticket] = 'failed';
		die('<script language="javascript">alert("'.( isset($_SESSION['wfu_browser_downloadfile_notexist']) ? $_SESSION['wfu_browser_downloadfile_notexist'] : 'File does not exist!' ).'");</script>');
	}
	//get mime type

	@set_time_limit(0); // disable the time limit for this script
	$fsize = filesize($filepath);
	if ( $fd = @fopen ($filepath, "rb") ) {
		header('Content-Type: application/octet-stream');
		header("Content-Disposition: attachment; filename=\"".$disposition_name."\"");
		header('Content-Transfer-Encoding: binary');
		header('Connection: Keep-Alive');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header("Content-length: $fsize");
		$failed = false;
		while( !feof($fd) ) {
			$buffer = @fread($fd, 1024*8);
			echo $buffer;
			ob_flush();
			flush();
			if ( connection_status() != 0 ) {
				$failed = true;
				break;
			}
		}
		fclose ($fd);
	}
	else $failed = true;
	
	if ( $delete_file ) unset($filepath);
	
	if ( !$failed ) {
		$_SESSION['wfu_download_status_'.$ticket] = 'downloaded';
		die();
	}
	else {
		$_SESSION['wfu_download_status_'.$ticket] = 'failed';
		die('<script language="javascript">alert("'.( isset($_SESSION['wfu_browser_downloadfile_failed']) ? $_SESSION['wfu_browser_downloadfile_failed'] : 'Could not download file!' ).'");</script>');
	}
}

?>
