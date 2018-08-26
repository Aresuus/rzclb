<?php if ( ! defined( 'ABSPATH' ) ) exit; 
function wpdocs_batch_upload($current_cat) {
	// INPUT SANITIZATION
	$post_page = sanitize_text_field($_REQUEST['page']);
	$post_cat = sanitize_text_field($_REQUEST['wpdocs-cat']);
	$do_zip = false;
	//$wpdocs = get_option('wpdocs-list');
	$cats = get_option('wpdocs-cats');
	$do_complte = false;
	if(isset($_FILES['wpdocs-batch']) && strpos($_FILES['wpdocs-batch']['type'],'zip') == false) {
		$string = '<h5>'.__('Please upload a zip file.','wpdocs').'</h5>';
		$string .= '<h6>'.__('Output:','wpdocs').'</h6>';
		foreach($_FILES['wpdocs-batch'] as $index => $value) $string .=  $index.' ==> '.$value.'<br>';
		$string .= '</p>';
		wpdocs_errors($string, 'error');
	} elseif(isset($_FILES['wpdocs-batch']) && strpos($_FILES['wpdocs-batch']['type'],'zip') >= 0) {
		//if(!file_exists('/tmp/')) mkdir('/tmp/');
		if(!file_exists(sys_get_temp_dir().'/wpdocs/')) mkdir(sys_get_temp_dir().'/wpdocs/');
		$zip_result = wpdocs_unzip($_FILES['wpdocs-batch']['tmp_name'], sys_get_temp_dir());
		$do_zip = true;
	} elseif (isset($_POST['wpdocs-batch-complete'])) {
		$do_complte = true;
	}
	wpdocs_list_header();
?>
<!--
<div class="alert alert-info">
	<h3>Warning</h3>
	<p><?php _e('Batch Upload  is still in beta.  Be sure to backup your library before running this process.  If anything should go wrong, just import the backup using the"Overwrite Saved Variables" option, and then run the "File System Cleanup" process to revert to the original state.','wpdocs'); ?></p>
</div>
-->
<div class="alert alert-success">
	<h3><?php _e('Batch Library Upload','wpdocs'); ?></h3>
	<p><?php _e('Create a zip file of all the documents you want to upload.  You may name the file whatever you want.  Once you have created the file, simply upload it, then use the quick select form to place the files in the proper directory.  Once satisfied press the \'Complete\' button to finsh the process.','wpdocs'); ?></p><br>
	<h4><?php _e('NOTE: Depending on the amout of files, batch upload can take a long time, please be patient.', 'wpdocs'); ?></h4>
</div>

<?php if($do_zip == false && $do_complte == false) { ?>
<form class="wpdocs-uploader-form" enctype="multipart/form-data" action="<?php echo get_site_url().'/wp-admin/admin.php?page='.$post_page.'&wpdocs-cat='.$post_cat; ?>" method="POST">
	<label><?php _e('Default Directory','wpdocs'); ?>:
	<select name="wpdocs[cat][<?php echo $index; ?>]">
			<?php wpdocs_get_cats($cats, $current_cat); ?>
	</select>
	</label><br><br>
	<input type="file" name="wpdocs-batch" /><br>
	<input type="submit" class="button button-primary" value="<?php _e('Upload Zip File','wpdocs') ?>" /><br/>
</form>
<?php } elseif($do_zip) {
	$cats = get_option('wpdocs-cats');
	if(!is_array($_POST['wpdocs']['cat'])) $current_cat = key($cats);
	else $current_cat = $_POST['wpdocs']['cat'][0];
	?>
	<form class="wpdocs-uploader-form" enctype="multipart/form-data" action="<?php echo get_site_url().'/wp-admin/admin.php?page='.$post_page.'&wpdocs-cat='.$post_cat; ?>" method="POST">
		<input type="hidden" name="wpdocs-batch-complete" value="1" />
		<input type="hidden" name="wpdocs-type" value="wpdocs-add" />
		<?php
		foreach($zip_result['file'] as $index => $zip_file) {
			$filesize_mb = number_format(round(filesize($zip_file)/1024,0));
			$file = explode('/',$zip_file);
			if(count($file) == 1) $file = explode('\\',$zip_file);
			$file = $file[count($file)-1];
			$file = preg_replace('/[^A-Za-z0-9\-._]/', '', $file);
			$file = str_replace(' ','-', $file);
			$filename = wpdocs_filenames_to_latin($file);
			$ext = strrchr($file,'.');
			$file = str_replace($ext, '', $file);
			?>
			<div class="wpdocs-batch-container">
				<input type="hidden" name="wpdocs[filename][<?php echo $index; ?>]" value="<?php echo $filename; ?>" />
				<input type="hidden" name="wpdocs[tmp-file][<?php echo $index; ?>]" value="<?php echo $zip_file; ?>" />
				<label><?php _e('File Name','wpdocs'); ?>:
					<input type="text" name="wpdocs[name][<?php echo $index; ?>]" value="<?php echo $file; ?>"/> <?php echo $filesize_mb.' '.__('KB','wpdocs'); ?>
				</label>
				<label><?php _e('Category','wpdocs'); ?>:
					<select name="wpdocs[cat][<?php echo $index; ?>]">
						<?php wpdocs_get_cats($cats, $current_cat); ?>
					</select>
				</label>
				<label>
						<?php _e('Version','wpdocs'); ?>: 
					<input type="text" name="wpdocs[version][<?php echo $index; ?>]" value="1.0" />
				</label>
			</div>
			<?php
		}
		?>
		<br>
		<input type="submit" class="button button-primary" value="<?php _e('Complete','wpdocs') ?>" />
		<br/>
	</form>
	<?php
} elseif ($_POST['wpdocs-batch-complete'] ) {
	$file = array();
	$current_user = wp_get_current_user();
	$batch_log = '';
	foreach($_POST['wpdocs']['tmp-file'] as $index => $tmp) {
		$valid_mime_type = false;
		$file['name'] = $_POST['wpdocs']['filename'][$index];
		$result = wp_check_filetype($tmp);
		$file['tmp_name'] = $tmp;
		$file['error'] = 0;
		if(file_exists($tmp)) $file['size'] = filesize($tmp);
		$file['post_status'] = 'publish';
		$file['post-status'] = 'publish';
		$wpdocs_fle_type = substr(strrchr($file['name'], '.'), 1 );
		//MDOCS FILE TYPE VERIFICATION	
		$mimes = get_allowed_mime_types();
		foreach ($mimes as $type => $mime) {
		  if ($mime === $result['type']) {
			$valid_mime_type = true;
			break;
		  }
		}
		$batch_log .= __('Processed File => ','wpdocs').$file['name']."<br>";
		if($valid_mime_type) {
			$upload = wpdocs_process_file($file);
			$wpdocs = get_option('wpdocs-list');
			if(get_option('wpdocs-preview-type') == 'box' && get_option('wpdocs-box-view-key') != '') {
				$boxview = new wpdocs_box_view();
				$boxview_file = $boxview->uploadFile(get_site_url().'/?wpdocs-file='.$upload['attachment_id'].'&wpdocs-url=false&is-box-view=true', $upload['filename']);
			} else $boxview_file['id'] = 0;
			array_push($wpdocs, array(
				'id'=>(string)$upload['attachment_id'],
				'parent'=>(string)$upload['parent_id'],
				'filename'=>$upload['filename'],
				'name'=>$_POST['wpdocs']['name'][$index],
				'desc'=>'',
				'type'=>$wpdocs_fle_type,
				'cat'=>$_POST['wpdocs']['cat'][$index],
				'owner'=>$current_user->user_login,
				'contributors'=>array(),
				'size'=>(string)$file['size'],
				'modified'=>(string)time()+WPDOCS_TIME_OFFSET,
				'version'=>(string)$_POST['wpdocs']['version'][$index],
				'show_social'=>(string)'on',
				'non_members'=> (string)'on',
				'file_status'=>(string)'public',
				'post_status'=> (string)'publish',
				'post_status_sys'=> (string)'publish',
				'doc_preview'=>(string)'',
				'downloads'=>(string)0,
				'archived'=>array(),
				'ratings'=>array(),
				'rating'=>0,
				'box-view-id' => $boxview_file['id'],
			));
			$wpdocs = wpdocs_array_sort($wpdocs);
			wpdocs_save_list($wpdocs);
			$batch_log .= __('Mime Type Allowed => ','wpdocs').$result['type']."<br>";
			$batch_log .= __('File Uploaded with No Errors.','wpdocs')."<br><br>";
		} else {
			$batch_log .= __("Invalid Mime Type => ").$result['type'].__(" Unable to process file.")."<br>";
			$batch_log .= __('File Was Not Uploaded because an Error occured.','wpdocs')."<br><br>";
		} 
		$file = array();
	}
	$batch_log .= __("Cleaning up tmp folder and files")."<br><br>";
	$files = glob(sys_get_temp_dir().'/wpdocs/*');
	if(file_exists(sys_get_temp_dir().'/wpdocs/.htaccess')) unlink(sys_get_temp_dir().'/wpdocs/.htaccess');
	foreach($files as $file) {
		if(is_file($file)) unlink($file);
		if(is_dir($file)) {
			$dir_files = glob($file.'/*');
			foreach($dir_files as $dir_file) if(is_file($file)) unlink($dir_file);
			rmdir($file);
		}
	}
	if(is_dir('/tmp/wpdocs')) rmdir('/tmp/wpdocs');
	$batch_log .= __("Batch Process Complete.");
	?>
	<div class="alert alert-info">
		<p><?php _e('The batch process has completed, below is a log of results:','wpdocs'); ?></p>
		<p><?php echo $batch_log; ?></p>
	</div>
	<form class="wpdocs-uploader-form" enctype="multipart/form-data" action="<?php echo get_site_url().'/wp-admin/admin.php?page='.$post_page.'&wpdocs-cat='.$post_cat; ?>" method="POST">
		<input type="file" name="wpdocs-batch" /><br/>
		<input type="submit" class="button button-primary" value="<?php _e('Upload Zip File','wpdocs') ?>" /><br/>
	</form>
	<?php
}
}
?>