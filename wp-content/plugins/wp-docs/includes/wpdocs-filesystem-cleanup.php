<?php if ( ! defined( 'ABSPATH' ) ) exit; 
function wpdocs_filesystem_cleanup() {
if(!isset($_POST['wpdocs-filesystem-cleanup'])) {
	wpdocs_list_header();
	?>
	<div class="alert alert-success">
	<form enctype="multipart/form-data" method="post" action="" class="wpdocs-setting-form">
		<h3><?php _e('Filesystem Cleanup','wpdocs'); ?></h3>
		<p><?php _e('Use this functionality to run a system check to locate and remove any broken files/data links inside WP Docs.<br>Be sure to make a backup copy before running this check.','wpdocs'); ?></p>
		<input type="hidden" name="wpdocs-filesystem-cleanup" value="init-cleanup" />
		<input style="margin:15px;" type="submit" class="button-primary" id="wpdocs-filesystem-cleanup" value="<?php _e('Run Filesystem Cleanup','wpdocs') ?>" />
	</form>
	</div>
<?php
} elseif(isset($_POST['wpdocs-filesystem-cleanup']) && $_POST['wpdocs-filesystem-cleanup'] == 'init-cleanup') {
	wpdocs_cleanup_init_html();
} elseif(isset($_POST['wpdocs-filesystem-cleanup']) && $_POST['wpdocs-filesystem-cleanup'] == 'submit-cleanup') {
	wpdocs_cleanup_submit_html();
}
}
function wpdocs_cleanup_submit_html() {
	wpdocs_filesystem_cleanup_submit();
	wpdocs_list_header();
	?>
	<div class="alert alert-success">
		<h3><?php _e('Filesystem Cleanup Complete','wpdocs'); ?></h3>
		<p><?php _e('Your file system has been cleaned.  Remember if you encounter any issues revert back to your previous version using the import tool.','wpdocs'); ?></p>
		<div class="cleanup-files">
			<h3><?php _e('Unlinked Files','wpdocs'); ?></h3>
		<?php
		$cleanup = wpdocs_filesystem_cleanup_init();
		foreach($cleanup['files'] as $file) echo $file.'<br>';
		if(count($cleanup['files']) == 0) _e('There are no unlinked files.','wpdocs');
		?>
		</div>
		<div class="cleanup-data">
			<h3><?php _e('Unlinked Data','wpdocs'); ?></h3>
		<?php
		foreach($cleanup['data'] as $data) echo __('Element ID','wpdocs').': '.$data['index'].'<br>';
		if(count($cleanup['data']) == 0) _e('There is no unlinked data.','wpdocs');
		?>
		</div>
		<div class="wpdocs-clear-both"></div>
		<form enctype="multipart/form-data" method="post" action="" class="wpdocs-setting-form">
			<input type="hidden" name="wpdocs-filesystem-cleanup" value="init-cleanup" />
			<input style="margin:15px;" type="submit" class="button-primary" id="wpdocs-filesystem-cleanup" value="<?php _e('Run File System Cleanup Again','wpdocs') ?>" />
		</form>
	</div>
	<?php
}
function wpdocs_cleanup_init_html() {
	$cleanup = wpdocs_filesystem_cleanup_init();
	wpdocs_list_header();
	?>
	<div class="alert alert-success">
		<h3><?php _e('Filesystem Analyzed','wpdocs'); ?></h3>
		<p><?php _e('Below is a list of files and data that look to be broken and or unused by WP Docs.  The next phase of the process will try and remove all this unlinked information.<br>Please make sure you have made an export of the files before continuing.  If anything goes wrong just import your export file to revert all changes.','wpdocs'); ?></p>
		<div class="cleanup-files">
			<h3><?php _e('Unlinked Files','wpdocs'); ?></h3>
		<?php
		foreach($cleanup['files'] as $file) echo $file.'<br>';
		if(count($cleanup['files']) == 0) _e('There are no unlinked files.','wpdocs');
		?>
		</div>
		<div class="cleanup-data">
			<h3><?php _e('Unlinked Data','wpdocs'); ?></h3>
		<?php
		foreach($cleanup['data'] as $data) echo __('Element ID','wpdocs').': '.$data['index'].'<br>';
		if(count($cleanup['data']) == 0) _e('There is no unlinked data.','wpdocs');
		?>
		</div>
		<div class="wpdocs-clear-both"></div>
		<form enctype="multipart/form-data" method="post" action="" class="wpdocs-setting-form">
		   <input type="hidden" name="wpdocs-filesystem-cleanup" value="submit-cleanup" />
		   <input style="margin:15px;" type="submit" class="button-primary" id="wpdocs-filesystem-cleanup" value="<?php _e('Cleanup The File System','wpdocs') ?>" />
	   </form>
	</div>
	<?php
}

function wpdocs_filesystem_cleanup_submit() {
	$wpdocs = get_option('wpdocs-list');
	$cleanup = wpdocs_filesystem_cleanup_init();
	$upload_dir = wp_upload_dir();
	foreach($cleanup['files'] as $file) {
		if(is_file($upload_dir['basedir'].'/wpdocs/'.$file)) unlink($upload_dir['basedir'].'/wpdocs/'.$file);
	}
	foreach($cleanup['data'] as $data) {
		if(isset($data['id'])) wp_delete_attachment( intval($data['id']), true );
		if(isset($data['parent'])) wp_delete_post( intval($data['parent']), true );
		unset($wpdocs[$data['index']]);
		$wpdocs = array_values($wpdocs);
		wpdocs_save_list($wpdocs);
	}
}

function wpdocs_filesystem_cleanup_init() {
	$wpdocs_zip_file = get_option('wpdocs-zip');
	$upload_dir = wp_upload_dir();
	$wpdocs = get_option('wpdocs-list');
	$files = glob($upload_dir['basedir'].'/wpdocs/*');
	$clean_up_files = array();
	$valid_file = false;
	foreach($files as $the_file) {
		foreach($wpdocs as $key => $the_doc) {
			//$the_file = explode('/',$the_file);
			//$the_file = $the_file[count($the_file)-1];
			$the_file = basename($the_file);
			$d = explode('.',$the_doc['filename']);
			$d = $d[0];
			if(preg_match('/'.$d.'/', $the_file)) {
				$valid_file = true;
				break;
			}
			if($the_file == $wpdocs_zip_file) {
				$valid_file = true;
				break;
			}
			if($the_file == 'wpdocs-files.bak') {
				$valid_file = true;
				break;
			}
			if($the_file == $the_doc['filename']) {	
				$valid_file = true;
				break;
			}
			if($the_file == 'wpdocs-files.bak') {
				$valid_file = true;
				break;
			}
			if(is_array($the_doc['archived'])) {
				$valid_data = true;
				foreach($the_doc['archived'] as $the_archive) {
					if($the_file == $the_archive) {
						$valid_file = true;
						break;
					}
				}
			}
		}
		if($valid_file == false) array_push($clean_up_files, $the_file);
		$valid_file = false;
	}
	$valid_data = false;
	$clean_up_data = array();
	foreach($wpdocs as $key => $the_doc) {
		if(!isset($the_doc['filename']) || $the_doc['filename'] == '' || !is_array($the_doc['archived'])) {
			$the_doc['index'] = $key;
			array_push($clean_up_data, $the_doc);
		}
	}
	
	return array('files'=> $clean_up_files, 'data'=>$clean_up_data);
}
?>