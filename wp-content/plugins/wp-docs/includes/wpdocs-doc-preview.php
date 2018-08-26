<?php if ( ! defined( 'ABSPATH' ) ) exit; 
function wpdocs_load_preview() {
	if(isset($_POST['type'])) {
		global $wpdocs_img_types, $current_user;
		$wpdocs = get_option('wpdocs-list');
		$wpdocs_show_preview = get_option('wpdocs-show-preview');
		$wpdocs_hide_all_files = get_option( 'wpdocs-hide-all-files' );
		$wpdocs_hide_all_files_non_members = get_option( 'wpdocs-hide-all-files-non-members' );
		$found = false;
		$is_admin = $_POST['is_admin'];
		//pre($wpdocs);
		foreach($wpdocs as $index => $the_wpdoc) {
			$wpdocs_show_non_members = $the_wpdoc['non_members'];
			if(intval($the_wpdoc['id']) == intval($_POST['wpdocs_file_id']) && $found == false) {
				if(is_user_logged_in() || $wpdocs_show_non_members == 'on' && $wpdocs_show_preview = '1' ) {
					if($_POST['type'] == 'file') {
						$upload_dir = wp_upload_dir();
						$file_url = get_site_url().'/?wpdocs-file='.$the_wpdoc['id'].'|'.is_user_logged_in();
						foreach($the_wpdoc['contributors'] as $user) {
							$contributor = false;
							if($current_user->user_login == $user) $contributor = true;
						}
						if($wpdocs_hide_all_files && $contributor == false && $the_wpdoc['owner'] != $current_user->user_login && $current_user->roles[0] != 'administrator') {
							echo '<div class="alert alert-warning" role="alert">'.__('Preview is unavailable for this file.','wpdocs').'</div>';
						} elseif($the_wpdoc['file_status'] == 'hidden' && $contributor == false && $the_wpdoc['owner'] != $current_user->user_login && $current_user->roles[0] != 'administrator') {
							echo '<div class="alert alert-warning" role="alert">'.__('Preview is unavailable for this file.','wpdocs').'</div>';
						} else if( is_user_logged_in() == false && $wpdocs_hide_all_files_non_members) {
							echo '<div class="alert alert-warning" role="alert">'.__('Please login to view this file preview.','wpdocs').'</div>';
						} else {
							echo '<h4>'.$the_wpdoc['filename'].'</h4>';
							wpdocs_doc_preview($the_wpdoc);
						}
					} elseif($_POST['type'] == 'img') {
						foreach($the_wpdoc['contributors'] as $user) {
							$contributor = false;
							if($current_user->user_login == $user) $contributor = true;
						}
						if($wpdocs_hide_all_files && $contributor == false && $the_wpdoc['owner'] != $current_user->user_login && $current_user->roles[0] != 'administrator') {
							echo '<div class="alert alert-warning" role="alert">'.__('Preview is unavailable for this file.','wpdocs').'</div>';
						} elseif($the_wpdoc['file_status'] == 'hidden' && $contributor == false && $the_wpdoc['owner'] != $current_user->user_login && $current_user->roles[0] != 'administrator') {
							echo '<div class="alert alert-warning" role="alert">'.__('Preview is unavailable for this file.','wpdocs').'</div>';
						} else if( is_user_logged_in() == false && $wpdocs_hide_all_files_non_members) {
							echo '<div class="alert alert-warning" role="alert">'.__('Please login to view this file preview.','wpdocs').'</div>';
						} else {
							echo '<h4>'.$the_wpdoc['filename'].'</h4>';
							wpdocs_show_image_preview($the_wpdoc);
						}
					} elseif($_POST['type'] == 'show') {
						if($_POST['show_type'] == 'preview') {
							$upload_dir = wp_upload_dir();
							$file_url = get_site_url().'/?wpdocs-file='.$the_wpdoc['id'].'|'.is_user_logged_in();
							if($wpdocs_hide_all_files || $the_wpdoc['file_status'] == 'hidden') {
								echo '<div class="alert alert-warning" role="alert">'.__('Preview is unavailable for this file.','wpdocs').'</div></div>';
							} else if( is_user_logged_in() == false && $wpdocs_hide_all_files_non_members) {
								echo '<div class="alert alert-warning" role="alert">'.__('Please login to view this file preview.','wpdocs').'</div>';
							} else {
								echo '<h4>'.$the_wpdoc['filename'].'</h4>';
								if(in_array($the_wpdoc['type'], $wpdocs_img_types)) wpdocs_show_image_preview($the_wpdoc);
								else wpdocs_doc_preview($the_wpdoc);
							}
						} else {
							$wpdocs_desc = apply_filters('the_content', $the_wpdoc['desc']);
							$wpdocs_desc = str_replace('\\','',$wpdocs_desc);
							?>
							<div class="mdoc-desc">
								
								<?php wpdocs_show_description($the_wpdoc['id']); ?>
							</div>
							<?php
						}
					}
				}  else {
					?><div class="alert alert-warning" role="alert"><h1><?php _e('Sorry you are unauthorized to preview this file.','wpdocs'); ?></h1></div><?php
				}
				$found = true;
				break;
			}
		}
	}
}