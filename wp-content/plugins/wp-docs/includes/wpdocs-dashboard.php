<?php if ( ! defined( 'ABSPATH' ) ) exit; 
function wpdocs_dashboard_menu() {
	global $add_error, $current_user;
	
	$wpdocs_allow_upload = get_option('wpdocs-allow-upload');
	if(!is_array($wpdocs_allow_upload)) $wpdocs_allow_upload = array();
	$wp_roles = get_editable_roles(); 
	if($current_user->roles[0] == 'administrator') {
		$role_object = get_role('administrator');
		$role_object->add_cap( 'wpdocs-dashboard');
	}
	
	foreach($wpdocs_allow_upload as $index => $role) {
		if($current_user->roles[0] == $index) {
			$role_object = get_role($index);
			$role_object->add_cap( 'wpdocs-dashboard');
		}
	}
	//echo WPDOCS_URL;exit;
	add_menu_page( __('WP Docs','wpdocs'), __('WP Docs','wpdocs'), 'wpdocs-dashboard', 'wpdocs-engine.php', 'wpdocs_dashboard', WPDOCS_URL.'includes/imgs/folder.png'  );
	

	if ( is_admin() ){
		add_action('admin_init','wpdocs_register_settings');
		add_action('admin_enqueue_scripts', 'wpdocs_admin_script');
	}
	// ERRORS AND UPDATES
	if(isset($_FILES['wpdocs']) && $_FILES['wpdocs']['name'] == '' && $_POST['wpdocs-type'] == 'wpdocs-add')  { wpdocs_errors(WPDOCS_ERROR_1,'error'); $add_error = true; }
}

function wpdocs_dashboard() {
	global $add_error;
	if(isset($_FILES['wpdocs']) && $_FILES['wpdocs']['name'] != '' && $_POST['wpdocs-type'] == 'wpdocs-add') wpdocs_file_upload();
	elseif(isset($_FILES['wpdocs']) && $_POST['wpdocs-type'] == 'wpdocs-update') wpdocs_file_upload();
	elseif(isset($_GET['action']) && $_GET['action'] == 'delete-doc' && !isset($_POST['wpdocs-type'])) wpdocs_delete();
	elseif(isset($_GET['action']) && $_GET['action'] == 'delete-version') wpdocs_delete_version();
	elseif(isset($_POST['action']) && $_POST['action'] == 'wpdocs-import') {
		if(wpdocs_file_upload_max_size() < $_FILES['size']) wpdocs_errors(WPDOCS_ERROR_7, 'error');
		else {
			//wpdocs_import_zip();
		}
	} elseif(isset($_POST['action']) && $_POST['action'] == 'wpdocs-update-revision') wpdocs_update_revision();
	elseif(isset($_GET['action']) && $_GET['action'] == 'wpdocs-versions') wpdocs_versions();
	elseif(isset($_POST['action']) && $_POST['action'] == 'wpdocs-update-cats') wpdocs_update_cats();
	wpdocs_dashboard_view();
}

function wpdocs_dashboard_view() {
	if($current_cat == null) $current_cat = $_GET['wpdocs-cat'];
	if($current_cat == 'import') wpdocs_import($current_cat);
	elseif($current_cat == 'export') wpdocs_export($current_cat);
	elseif($current_cat == 'cats') wpdocs_edit_cats($current_cat);
	elseif($current_cat == 'settings') wpdocs_settings($current_cat);
	elseif($current_cat == 'batch') wpdocs_batch_upload($current_cat);
	elseif($current_cat == 'short-codes') wpdocs_shortcodes($current_cat);
	elseif($current_cat == 'filesystem-cleanup') wpdocs_filesystem_cleanup($current_cat);
	elseif($current_cat == 'restore') wpdocs_restore_defaults($current_cat);
	elseif($current_cat == 'allowed-file-types') wpdocs_allowed_file_types($current_cat);
	elseif($current_cat == 'find-lost-files') wpdocs_search_lost_files($current_cat);
	elseif($current_cat == 'server-compatibility') wpdocs_server_compatibility($current_cat);
	else echo (is_admin()?wpdocs_the_list():wpdocs_the_list2());
}

function wpdocs_delete() {
	if ( $_REQUEST['wpdocs-nonce'] == WPDOCS_NONCE ) {
		$wpdocs = get_option('wpdocs-list');
		//$wpdocs = wpdocs_sort_by($wpdocs, 0, 'dashboard', false);
		$wpdocs = wpdocs_array_sort();
		$index = $_GET['wpdocs-index'];
		$upload_dir = wp_upload_dir();
		$wpdocs_file = $wpdocs[$index];
		if(is_array($wpdocs[$index]['archived'])) foreach($wpdocs[$index]['archived'] as $key => $value) @unlink($upload_dir['basedir'].'/wpdocs/'.$value);
		wp_delete_attachment( intval($wpdocs_file['id']), true );
		wp_delete_post( intval($wpdocs_file['parent']), true );
		if(file_exists($upload_dir['basedir'].'/wpdocs/'.$wpdocs_file['filename'])) @unlink($upload_dir['basedir'].'/wpdocs/'.$wpdocs_file['filename']);
		unset($wpdocs[$index]);
		$wpdocs = array_values($wpdocs);
		wpdocs_save_list($wpdocs);
	} else wpdocs_errors(WPDOCS_ERROR_4,'error');
}

function wpdocs_add_update_ajax($edit_type='Add Document') {
	$cats = get_option('wpdocs-cats');
	$wpdocs = wpdocs_array_sort();
	$mdoc_index = '';
	if(isset($_POST['wpdocs-id'])) {	
		foreach($wpdocs as $index => $doc) {
			if($_POST['wpdocs-id'] == $doc['id']) {
				$mdoc_index = $index; break;
			}
		}
		$wpdocs_post = get_post($wpdocs[$mdoc_index]['parent']);
		$wpdocs_desc = $wpdocs_post->post_excerpt;
	}
	if(!is_string($mdoc_index) && $edit_type == 'Update Document' || $edit_type == 'Add Document') {
		if($edit_type == 'Update Document') $mdoc_type = 'wpdocs-update';
		else $mdoc_type = 'wpdocs-add';
		
		$post_tags = wp_get_post_tags($wpdocs[$mdoc_index]['parent']);
		foreach($post_tags as $post_tag) $the_tags .= $post_tag->name.', ';
		$the_tags = rtrim($the_tags, ', ');
		$wpdocs[$mdoc_index]['post-tags'] = $the_tags;
		$date_format = get_option('wpdocs-date-format');
		$wpdocs[$mdoc_index]['timestamp'] = gmdate($date_format,time()+WPDOCS_TIME_OFFSET);
		$json = json_encode($wpdocs[$mdoc_index]);
		echo $json;
	} else {
		$error['error'] = __('Index value not found, something has gone wrong.', 'wpdocs')."\n\r";
		$error['error'] .= __('[ Index Value ]', 'wpdocs').' => '.$mdoc_index."\n\r";
		$error['error'] .= __('[ Edit Type ]', 'wpdocs').' => '.$edit_type;
		echo json_encode($error);
	}
}

function wpdocs_uploader() {
	global $current_user;
	$cats = get_option('wpdocs-cats');
	@session_start();
	if(isset($_SESSION['wpdocs-nonce'])) $wpdocs_session = $_SESSION['wpdocs-nonce'];
	else $wpdocs_session = '';
	@session_write_close();
?>
<div class="row">
	<div class="col-md-12" id="wpdocs-add-update-container">
		<div class="page-header">
			<h1 id="wpdocs-add-update-header"></h1>
		</div>
		<div class="">
			<form class="form-horizontal" enctype="multipart/form-data" action="" method="POST" id="wpdocs-add-update-form">
				<input type="hidden" name="wpdocs-current-user" value="<?php echo $current_user->user_login; ?>" />
				<input type="hidden" name="wpdocs-type" value="" />
				<input type="hidden" name="wpdocs-index" value="" />
				<input type="hidden" name="wpdocs-cat" value="" />
				<input type="hidden" name="wpdocs-pname" value="" />
				<input type="hidden" name="wpdocs-nonce" value="<?php echo $wpdocs_session; ?>" />
				<input type="hidden" name="wpdocs-post-status-sys" value="" />
				
				<div class="well well-lg">
                	<div class="form-group form-group-lg">
						<label class="col-sm-2 control-label" for="wpdocs"><?php _e('File','wpdocs'); ?></label>
						<div class="col-sm-10">
							<input class="" type="file" name="wpdocs" />
							<p class="help-block" id="wpdocs-current-doc"></p>
						</div>
					</div>
                    
					<div class="page-header">
						<h2 id="wpdocs-add-update-header"><?php _e('File Properties','wpdocs'); ?></h2>
					</div>
					<div class="form-group form-group-lg ">
						<label class="col-sm-2 control-label" for="wpdocs-name"><?php _e('Title','wpdocs'); ?></label>
						<div class="col-sm-10">
							<input class="form-control" type="text" name="wpdocs-name" id="wpdocs-name" />
						</div>
					</div>
					<div class="form-group form-group-lg ">
						<label class="col-sm-2 control-label" for="wpdocs-cat"><?php _e('Directory','wpdocs'); ?></label>
						<div class="col-sm-10">
							<select class="form-control" name="wpdocs-cat">
							<?php wpdocs_get_cats($cats, $current_cat); ?>
							</select>
						</div>
					</div>
					<div class="form-group form-group-lg hide">
						<label class="col-sm-2 control-label" for="wpdocs-version"><?php _e('Version','wpdocs'); ?></label>
						<div class="col-sm-10">
							<input class="form-control" type="text" name="wpdocs-version" value="1.0" />
						</div>
					</div>
					<div class="form-group form-group-lg hide">
						<label class="col-sm-2 control-label" for="wpdocs-last-modified"><?php _e('Date','wpdocs'); ?></label>
						<div class="col-sm-10">
							<input class="form-control" type="text" name="wpdocs-last-modified" value="" />
						</div>
					</div>
				
					<div class="form-group hide">
						<label class="col-sm-2 control-label" for="wpdocs-file-status"><?php _e('File Status','wpdocs'); ?></label>
						<div class="col-sm-10">
							<select class="form-control input-lg" name="wpdocs-file-status" id="wpdocs-file-status" >
								<option value="public" ><?php _e('Public','wpdocs'); ?></option>
								<option value="hidden" ><?php _e('Hidden','wpdocs'); ?></option>
							</select>
						</div>
					</div>
					<div class="form-group hide">
						<label class="col-sm-2 control-label" for="wpdocs-post-status"><?php _e('Post Status','wpdocs'); ?></label>
						<div class="col-sm-10">
							<select class="form-control input-lg" name="wpdocs-post-status" id="wpdocs-post-status" >
								<option value="publish" ><?php _e('Published','wpdocs'); ?></option>
								<option value="private" ><?php _e('Private','wpdocs');  ?></option>
								<option value="pending"  ><?php _e('Pending Review','wpdocs');  ?></option>
								<option value="draft" ><?php _e('Draft','wpdocs');  ?></option>
							</select>
						</div>
					</div>
					<div class="form-group hide">
						<label class="col-sm-2 control-label" for="wpdocs-social"><?php _e('Show Social Apps','wpdocs'); ?></label>
						<div class="col-sm-1">
							<input class="form-control" type="checkbox" name="wpdocs-social" checked />
						</div>
						<label class="col-sm-3 control-label" for="wpdocs-non-members"><?php _e('Downloadable by Non Members','wpdocs'); ?></label>
						<div class="col-sm-1">
							<input class="form-control" type="checkbox" name="wpdocs-non-members" checked />
						</div>
					</div>
					<div class="form-group form-group-lg hide">
						<label class="col-sm-2 control-label" for="wpdocs-social"><?php _e('Contributors','wpdocs'); ?></label>
						<div class="col-sm-10">
							<div id="wpdocs-contributors-container">
								<span class="label label-primary wpdocs-contributors" id="wpdocs-current-owner"></span>
							</div>
							<input autocomplete="off" class="form-control" type="text" name="wpdocs-add-contributors" id="wpdocs-add-contributors" placeholder="<?php _e('Add contributor, users and roles types are allowed.'); ?>"/>
							<div class="wpdocs-user-search-list hidden"></div>
						</div>
					</div>
					<div class="form-group form-group-lg hide">
						<label class="col-sm-2 control-label" for="wpdocs-tags"><?php _e('Tags','wpdocs'); ?></label>
						<div class="col-sm-10">
							<input class="form-control" type="text" name="wpdocs-tags" id="wpdocs-tags" placeholder="<?php _e('Comma Separated List', 'wpdocs'); ?>" />
						</div>
					</div>
					<div class="form-group">
						<div>
							<label><?php _e('Description','wpdocs'); ?></label>
							<br>
							<div>
							<?php
							//$wp_edit_settings = array('quicktags' => false);
							//https://codex.wordpress.org/Function_Reference/wp_editor
							$settings = array(
							);
							wp_editor('', "wpdocs-desc", $settings);
							?>
							</div>
						</div>
					</div>
				</div>
				
				<input type="submit" class="button button-primary" id="wpdocs-save-doc-btn" value="" />
				
			</form>
		</div>
	</div>
</div>
	
<?php

}
?>