<?php if ( ! defined( 'ABSPATH' ) ) exit; 
if(isset($_GET['wpdocs-file'])) wpdocs_download_file();
if(isset($_GET['wpdocs-version'])) wpdocs_download_file($_GET['wpdocs-version']);
if(isset($_GET['wpdocs-export-file'])) wpdocs_download_export_file($_GET['wpdocs-export-file']);
if(isset($_GET['wpdocs-img-preview'])) wpdocs_img_preview();

function wpdocs_download_file() { add_action( 'plugins_loaded', 'wpdocs_plugin_loaded' ); }
function wpdocs_plugin_loaded() {
	global $current_user;
	$upload_dir = wp_upload_dir();
	$wpdocs = get_option('wpdocs-list');
	$wpdocs_hide_all_files = get_option( 'wpdocs-hide-all-files' );
	$wpdocs_hide_all_files_non_members = get_option( 'wpdocs-hide-all-files-non-members' );
	$is_logged_in = is_user_logged_in();
	$login_denied = false;
	$non_member = '';
	$file_status = '';
	$send_bot_alert = false;
	if(isset($_GET['is-box-view']) && $_GET['is-box-view'] == 'true') $box_view = true;
	else $box_view = false;
	if(!empty($_GET['wpdocs-export-file']) ) { $filename = $_GET['wpdocs-export-file']; }
	elseif(isset($_GET['wpdocs-version']) ) { $filename = isset($_GET['wpdocs-version']); }
	else {
		
		$serialized_file = stripslashes($_GET['wpdocs-file']);
		
		$serialized_file = unserialize(str_replace('\'','"', $serialized_file));
		
		//pree($serialized_file);exit;
		
		if($serialized_file != false) {
			$wpdocs_file = $serialized_file['id'];
			$is_google = true;
		}
		
		if($box_view === true) $send_bot_alert = false;
		else $send_bot_alert = true;
		if($serialized_file == false) $wpdocs_file = $_GET["wpdocs-file"];
		foreach($wpdocs as $index => $value) {
			if($value['id'] == $wpdocs_file ) {
				$filename = $value['filename'];
				$non_member = $value['non_members'];
				$file_status = $value['file_status'];
				$contributors = $value['contributors'];
				$owner = $value['owner'];
				break;
			} //else $filename = 'wpdocs-empty';
		}
	}
	if($non_member == '' && $is_logged_in == false || $file_status == 'hidden' && !is_admin() || $wpdocs_hide_all_files  || $wpdocs_hide_all_files_non_members && is_user_logged_in() == false) $login_denied = true;
	else $login_denied = false;
	foreach($contributors as $user) {
		$login_denied = true;
		if($current_user->user_login == $user) $login_denied = false;
	}
	if($current_user->user_login == $owner) $login_denied = false;
	if($current_user->roles[0] == 'administrator') $login_denied = false;
	
	$wpdocs_is_bot = wpdocs_is_bot();
	if($wpdocs_is_bot === false && $login_denied == false && !isset($_GET['wpdocs-export-file']) &&  $box_view === false && !isset($_GET['wpdocs-version']) && $is_google == false) {
		$wpdocs[$index]['downloads'] = (string)(intval($wpdocs[$index]['downloads'])+1);
		wpdocs_save_list($wpdocs);
	}
	
	//if(isset($_GET['wpdocs-export-file'])) wpdocs_export_zip();
	$file = $upload_dir['basedir']."/wpdocs/".$filename;
	if(isset($_GET['wpdocs-version'])) $filename = substr($filename, 0, strrpos($filename, '-'));
	$filetype = wp_check_filetype($file );
	if($login_denied == false  || $box_view || $is_google) {
		if (file_exists($file) && $wpdocs_is_bot === false  ) {		
			header('Content-Description: File Transfer');
			header('Content-Type: '.$filetype['type']);
			header('Content-Disposition: attachment; filename='.$filename);
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false); 
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			ob_clean();
			flush();
			readfile($file);
			if($send_bot_alert) wpdocs_send_bot_alert($wpdocs_is_bot);
			exit;
		} else if(!file_exists($file) && $box_view == true) die(__('WP Documents Error','wpdocs').': '.basename($file).' '.__('was not found, no preview created for this file.', 'wpdocs'));
		else {
			die(__('WP Documents Error','wpdocs').': <b>'.basename($file).'</b> '.__('was not found, please contact the owner for assistance.', 'wpdocs'));
		}
	} else die(__('Sorry you are unauthorized to download this file.','wpdocs'));
	
}

function wpdocs_img_preview() {
	require_once(ABSPATH . 'wp-includes/pluggable.php');
	$upload_dir = wp_upload_dir();
	$image = $upload_dir['basedir'].WPDOCS_DIR.$_GET['wpdocs-img-preview'];
	$content = file_get_contents($image);
	header('Content-Type: image/jpeg');
	echo $content; exit();
}
?>