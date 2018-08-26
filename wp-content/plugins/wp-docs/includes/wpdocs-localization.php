<?php if ( ! defined( 'ABSPATH' ) ) exit; 
// ********** WP Docs VERSION *********************//
define('WPDOCS_VERSION', '3.1.2');
//*************************************************************************************//
$upload_dir = wp_upload_dir();
$wpdocs_zip = get_option('wpdocs-zip');
// LOCALIZATION INIT
function wpdocs_localization() {

	//FOR TESTING LANG FILES
	//global $locale; $locale = 'he_IL';
	$loaded = load_plugin_textdomain('wpdocs', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action('init', 'wpdocs_localization');
//PASS VARIABLES TO JAVASCRIPT
function wpdocs_js_handle($script) {
	global $wpdocs_pro, $wpdocs_levels;
	wp_localize_script( $script, 'wpdocs_js', array(
		'version_file' => __("You are about to delete this file.  Once deleted you will lose this file!\n\n'Cancel' to stop, 'OK' to delete.",'wpdocs'),
		'version_delete' => __("You are about to delete this version.  Once deleted you will lose this version of the file!\n\n'Cancel' to stop, 'OK' to delete.",'wpdocs'),
		'category_delete' => __("You are about to delete this directory.  Any file in this directory will be lost!\n\n'Cancel' to stop, 'OK' to delete.",'wpdocs'),
		'remove' => __('Remove','wpdocs'),
		'new_category' => __('New Directory','wpdocs'),
		'leave_page' => __('Are you sure you want to navigate away from this page?','wpdocs'),
		'levels'=> ($wpdocs_pro?$wpdocs_levels:2),
		'category_support' => __('WP Docs '.(!$wpdocs_pro?'(Free Version) only ':'').'supports upto '.($wpdocs_pro?$wpdocs_levels:2).' sub categories. '.($wpdocs_pro?'Do you need more depth? Please contact WordPress Mechanic.':'Complex directory structure? Go Premium.'),'wpdocs'),
		'restore_warning' => __('Are you sure you want continue.  All you files, posts and directories will be delete.','wpdocs'),
		'add_folder' => __('Add Directory', 'wpdocs'),
		'update_doc' => __('Updating Document', 'wpdocs'),
		'update_doc_btn' => __('Update Document' , 'wpdocs'),
		'add_doc' => __('Adding Document', 'wpdocs'),
		'add_doc_btn' => __('Submit', 'wpdocs'),
		'current_file' => __('Current File','wpdocs'),
		'patch_text_3_0_1' => __('UPDATE HAS STARTER, DO NOT LEAVE THIS PAGE!'),
		'patch_text_3_0_2' => __('Go grab a coffee this my take awhile.'),
		'create_export_file' => __('Creating the export file, please be patient.'),
		'export_creation_complete_starting_download' => __('Export file creation complete, staring download of zip file.'),
		'blog_id' => get_current_blog_id(),
		'plugin_url' => plugins_url().'/wp-docs/',
		'wp_root' => get_option('wpdocs-wp-root'),
		'ajaxurl' => admin_url( 'admin-ajax.php' ), 
	));
}
// PROCESS AJAX REQUESTS
add_action( 'wp_ajax_nopriv_wpdocs-submit', 'wpdocs_ajax_processing' );
add_action( 'wp_ajax_wpdocs-submit', 'wpdocs_ajax_processing' );
function wpdocs_ajax_processing() {
	switch($_POST['type']) {
		case 'file':
			wpdocs_load_preview();
			break;
		case 'img':
			wpdocs_load_preview();
			break;
		case 'show':
			wpdocs_load_preview();
			break;
		case 'add-mime':
			wpdocs_update_mime();
			break;
		case 'remove-mime':
			wpdocs_update_mime();
			break;
		case 'restore-mime':
			wpdocs_update_mime();
			break;
		case 'restore':
			wpdocs_restore_default();
			break;
		case 'sort':
			wpdocs_sort();
			break;
		case 'rating':
			wpdocs_ratings();
			break;
		case 'rating-submit':
			wpdocs_set_rating(intval($_POST['wpdocs_file_id']));
			break;
		case 'nav-collaspse':
			//wpdocs_nav_size(true);
			break;
		case 'nav-expand':
			//wpdocs_nav_size(false);
			break;
		case 'add-doc':
			wpdocs_add_update_ajax('Add Document');
			break;
		case 'update-doc':
			wpdocs_add_update_ajax('Update Document');
			break;
		case 'wpdocs-v3-0-patch':
			//wpdocs_box_view_update_v3_0();
			break;
		case 'wpdocs-v3-0-patch-run-updater':
			wpdocs_v3_0_patch_run_updater();
			break;
		case 'wpdocs-v3-0-patch-cancel-updater':
			wpdocs_v3_0_patch_cancel_updater();
			break;
		case 'show-desc':
			wpdocs_show_description(intval($_POST['wpdocs_file_id']));
			break;
		case 'search-users':
			wpdocs_search_users($_POST['user-search-string'], $_POST['owner'], $_POST['contributors']);
			break;
		case 'show-social':
			echo wpdocs_social(intval($_POST['doc-index']));
			break;
		case 'box-view-refresh':
			$wpdocs = wpdocs_array_sort();
			$file = get_site_url().'/?wpdocs-file='.$wpdocs[$_POST['index']]['id'].'&wpdocs-url=false&is-box-view=true';
			$boxview = new wpdocs_box_view();
			$results = $boxview->uploadFile($file);
			$wpdocs[$_POST['index']]['box-view-id'] = $results['id'];
			update_option('wpdocs-list', sanitize_wpdocs_data($wpdocs));
			echo '<div class="alert alert-success" role="alert" id="box-view-updated">'.$wpdocs[$_POST['index']]['filename'].' '.__('preview has been updated.', 'wpdocs').'</div>';
			break;
		case 'lost-file-search-start':
			wpdocs_find_lost_files();
			break;
		case 'lost-file-save':
			wpdocs_save_lost_files();
			break;
		case 'wpdocs-export':
			wpdocs_export_zip();
			wpdocs_download_export_file($_POST['zip-file']);
			break;
		case 'wpdocs-cat-index':
			$check_index = intval($_POST['check-index']);
			do {
				$found = wpdocs_find_cat('wpdocs-cat-'.$check_index);
				$empty_index = $check_index;
				$check_index++;
			} while ($found == true);
			update_option('wpdocs-num-cats', sanitize_wpdocs_data($empty_index));
			echo $empty_index;
			break;
	}
	exit;
}
function wpdocs_get_inline_css() {
	$num_show = 0;
	if(get_option('wpdocs-show-downloads')==1) $num_show++;
	if(get_option('wpdocs-show-author')==1) $num_show++;
	if(get_option('wpdocs-show-version')==1) $num_show++;
	if(get_option('wpdocs-show-update')==1) $num_show++;
	if(get_option('wpdocs-show-ratings')==1) $num_show++;
	$wpdocs_font_size = get_option('wpdocs-font-size');
	if($num_show==5) $title_width = '35%';
	if($num_show==4) $title_width = '45%';
	if($num_show==3) $title_width = '55%';
	if($num_show==2) $title_width = '65%';
	if($num_show==1) $title_width = '75%';
	$download_button_color = get_option('wpdocs-download-text-color-normal');
	$download_button_bg = get_option('wpdocs-download-color-normal'); 
	$download_button_hover_color = get_option('wpdocs-download-text-color-hover');
	$download_button_hover_bg = get_option('wpdocs-download-color-hover');
	$set_inline_style = "
		.wpdocs-list-table #title { width: $title_width !important }
		.wpdocs-download-btn-config:hover { background: $download_button_hover_bg; color: $download_button_hover_color; }
		.wpdocs-download-btn-config { color: $download_button_color; background: $download_button_bg ; }
		.wpdocs-download-btn, .wpdocs-download-btn:active { color: $download_button_color !important; background: $download_button_bg !important;  }
		.wpdocs-download-btn:hover { background: $download_button_hover_bg !important; color: $download_button_hover_color !important;}
		.wpdocs-container table { font-size: ".$wpdocs_font_size."px !important; }
		.wpdocs-container #title { font-size: ".$wpdocs_font_size."px !important; }
	";
	return $set_inline_style;
}
function wpdocs_get_inline_admin_css() {
	$num_show = 0;
	if(get_option('wpdocs-show-downloads')==1) $num_show++;
	if(get_option('wpdocs-show-author')==1) $num_show++;
	if(get_option('wpdocs-show-version')==1) $num_show++;
	if(get_option('wpdocs-show-update')==1) $num_show++;
	if(get_option('wpdocs-show-ratings')==1) $num_show++;
	if($num_show==5) $title_width = '35%';
	if($num_show==4) $title_width = '45%';
	if($num_show==3) $title_width = '55%';
	if($num_show==2) $title_width = '65%';
	if($num_show==1) $title_width = '75%';
	$download_button_color = get_option('wpdocs-download-text-color-normal');
	$download_button_bg = get_option('wpdocs-download-color-normal'); 
	$download_button_hover_color = get_option('wpdocs-download-text-color-hover');
	$download_button_hover_bg = get_option('wpdocs-download-color-hover');
	$set_inline_style = "
		body { background: transparent; }
		dd, li { margin: 0; }
		.wpdocs-list-table #title { width: $title_width !important }
		.wpdocs-download-btn-config:hover { background: $download_button_hover_bg; color: $download_button_hover_color; }
		.wpdocs-download-btn-config { color: $download_button_color; background: $download_button_bg ; }
		.wpdocs-download-btn, .wpdocs-download-btn:active { color: $download_button_color !important; background: $download_button_bg !important;  }
		.wpdocs-download-btn:hover { background: $download_button_hover_bg !important; color: $download_button_hover_color !important;}
	";
	return $set_inline_style;
}
function wpdocs_localize() {
	global $upload_dir, $wpdocs_zip;
	$query = new WP_Query('pagename=wpdocs-library');	
	$permalink = get_permalink($query->post->ID);
	if( strrchr($permalink, '?page_id=')) $wpdocs_link = site_url().'/'.strrchr($permalink, '?page_id=');
	else $wpdocs_link = site_url().'/'.$query->post->post_name.'/';
	define('WPDOCS_ZIP_STATUS_OK',__('WP Docs has an export file on this WordPress instance it was created on '.gmdate('F jS Y \a\t g:i A',@filemtime($upload_dir['basedir'].'/wpdocs/'.$wpdocs_zip)+WPDOCS_TIME_OFFSET).'.<br><br><!--Click <a href="'.$upload_dir['baseurl'].'/wpdocs/'.$wpdocs_zip.'" tiltle="Old Export File">here</a> to download this version of the export file.-->','wpdocs'));
	define('WPDOCS_ZIP_STATUS_FAIL',__('WP Docs has no export file on this WordPress instance.  You may want to create an export file now.','wpdocs'));
	define('WPDOCS_DEFAULT_DESC', __('This file is part of the Documents Library.','wpdocs'));	
	//ERRORS
	define('WPDOCS_ERROR_1',__('No file was uploaded, please try again.','wpdocs'));
	define('WPDOCS_ERROR_2',__('Sorry, this file type is not permitted for security reasons.  If you want to add this file type please goto the setting page of WP Docs and add it to the Allowed File Type menu.','wpdocs'));
	define('WPDOCS_ERROR_3',__('No categories found.  The upload process can not proceed.','wpdocs'));
	define('WPDOCS_ERROR_4',__('Data was not submitted.  The submit process is out of sync, please refresh your browser and try again.','wpdocs'));
	define('WPDOCS_ERROR_5', __('File Upload Error.  Please try again.','wpdocs'));
	define('WPDOCS_ERROR_6', __('You are already at the most recent version of this document.','wpdocs'));
	define('WPDOCS_ERROR_7', __('The import file is too large, please update your php.ini files upload_max_filesize.','wpdocs'));
	define('WPDOCS_ERROR_8', __('An error occurred when creating a folder, please try again.','wpdocs'));
	define('WPDOCS_ERROR_9', __('You have reached the maxium number of input variable allowed for your servers configuration, this means you can not edit folders anymore.  To be able to edit folders again, please increase the variable max_input_vars in your php.ini file.','wpdocs'));
}
?>