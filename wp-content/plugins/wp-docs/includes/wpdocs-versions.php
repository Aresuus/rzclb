<?php if ( ! defined( 'ABSPATH' ) ) exit; 
function wpdocs_versions() {
	$cats = get_option('wpdocs-cats');
	$wpdocs = get_option('wpdocs-list');
	$wpdocs = wpdocs_array_sort();
	$mdoc_index = $_GET['wpdocs-index'];
	$upload_dir = wp_upload_dir();
	if(isset($_GET['wpdocs-cat'])) $current_cat = $_GET['wpdocs-cat'];
	else $current_cat = $current_cat = key($cats);
	$the_wpdoc = $wpdocs[$mdoc_index];
	$date_format = get_option('wpdocs-date-format');
	$the_wpdoc_date_modified = gmdate($date_format, filemtime($upload_dir['basedir'].'/wpdocs/'.$the_wpdoc['fliename'])+WPDOCS_TIME_OFFSET);
?>
<div class="wpdocs-uploader-bg"></div>
<div class="wpdocs-uploader">
	<a href="<?php echo 'admin.php?page=wpdocs-engine.php&wpdocs-cat='.$current_cat; ?>" type="button" class="close" id="wpdocs-version-close"><span aria-hidden="true">&times;</span><span class="sr-only"><?php _e('Close','wpdocs'); ?></span></a>
	<div class="page-header">
		<h1 id="wpdocs-version-header"><?php echo __('Versions','wpdocs'); ?> <small><?php echo $the_wpdoc['filename']; ?></h1>
	</div>
	<div class="wpdocs-ds-container">
		<div class="wpdocs-uploader-content">
			<form class="wpdocs-uploader-form" enctype="multipart/form-data" action="" method="POST">
				<input type="hidden" name="wpdocs-nonce" value="<?php echo WPDOCS_NONCE; ?>" />
				<input type="hidden" name="wpdocs-index" value="<?php echo $mdoc_index; ?>" />
				<input type="hidden" name="action" value="wpdocs-update-revision" />
				<table  class="wp-list-table widefat plugins">
					<thead>
						<tr>
							<th scope="col" class="manage-column column-name" ><?php _e('File','wpdocs'); ?></th>
							<th scope="col" class="manage-column column-name" ><?php _e('Version','wpdocs'); ?></th>
							<th scope="col" class="manage-column column-name" ><?php _e('Date Modified','wpdocs'); ?></th>
							<th scope="col" class="manage-column column-name" ><?php _e('Download','wpdocs'); ?></th>
							<th scope="col" class="manage-column column-name" ><?php _e('Delete','wpdocs'); ?></th>
							<th scope="col" class="manage-column column-name" ><?php _e('Current','wpdocs'); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th scope="col" class="manage-column column-name" ><?php _e('File','wpdocs'); ?></th>
							<th scope="col" class="manage-column column-name" ><?php _e('Version','wpdocs'); ?></th>
							<th scope="col" class="manage-column column-name" ><?php _e('Date Modified','wpdocs'); ?></th>
							<th scope="col" class="manage-column column-name" ><?php _e('Download','wpdocs'); ?></th>
							<th scope="col" class="manage-column column-name" ><?php _e('Delete','wpdocs'); ?></th>
							<th scope="col" class="manage-column column-name" ><?php _e('Current','wpdocs'); ?></th>
						</tr>
					</tfoot>
					<tbody id="the-list">
							<tr class="wpdocs-bg-odd">
								<td class="wpdocs-blue" id="file" ><?php echo $the_wpdoc['filename']; ?></td>
								<td class="wpdocs-green" id="version" ><?php echo $the_wpdoc['version']; ?></td>
								<td class="wpdocs-red" id="date"><?php  echo $the_wpdoc_date_modified; ?></td>
								<td id="download"><input type="button" id="wpdocs-download" onclick="wpdocs_download_current_version('<?php echo $the_wpdoc['id']; ?>')" class="button button-primary" value=<?php _e("Download"); ?>  /></td>
								<td></td>
								<td id="current"><input type="radio" name="wpdocs-version" value="<?php echo 'current'; ?>" checked /></td>
							</tr>
						</tr>
					<?php
						$bgcolor = 'wpdocs-bg-even';
						foreach( array_reverse($the_wpdoc['archived']) as $key => $archive ){
							$file = substr($archive, 0, strrpos($archive, '-'));
							$version = substr(strrchr($archive, '-'), 2 );
							
							if(file_exists($upload_dir['basedir'].'/wpdocs/'.$archive)) {
							
							
								$archive_date_modified = gmdate($date_format, filemtime($upload_dir['basedir'].'/wpdocs/'.$archive)+WPDOCS_TIME_OFFSET);
								?>
								<tr class="<?php echo $bgcolor; ?>">
									<td class="wpdocs-blue" id="file" ><?php echo $file; ?></td>
									<td class="wpdocs-green" id="version" ><?php echo $version; ?></td>
									<td class="wpdocs-red" id="date"><?php  echo $archive_date_modified; ?></td>
									<td id="download"><input onclick="wpdocs_download_version('<?php echo $archive; ?>')" type="button" id="wpdocs-download" name="<?php echo $key; ?>" class="button button-primary" value=<?php _e("Download"); ?>  /></td>
									<td id="download"><input onclick="wpdocs_delete_version('<?php echo $archive; ?>','<?php echo $mdoc_index; ?>','<?php echo $current_cat; ?>','<?php echo WPDOCS_NONCE; ?>')" type="button" id="wpdocs-delete" name="<?php echo $key; ?>" class="button button-primary" value=<?php _e("Delete"); ?>  /></td>
									<td id="current"><input type="radio" name="wpdocs-version" value="<?php echo count($the_wpdoc['archived'])-$key-1; ?>" /></td>
								</tr>
								<?php
								if($bgcolor == "wpdocs-bg-even") $bgcolor = "wpdocs-bg-odd";
								else $bgcolor = "wpdocs-bg-even";
							}
						}
						?>
					</tbody>
				</table>
				<br/>
				<input type="submit" class="button button-primary" value="<?php _e('Update To Revision') ?>" /><br/>
			</form>
		</div>
	</div>
</div>
<?php
}

function wpdocs_delete_version() {
	if ($_GET['wpdocs-nonce'] == WPDOCS_NONCE ) {
		$index = $_GET['wpdocs-index'];
		$version_file = $_GET['version-file'];
		$wpdocs = get_option('wpdocs-list');
		$wpdocs = wpdocs_array_sort();
		$the_wpdoc = $wpdocs[$index];
		$upload_dir = wp_upload_dir();
		$archive_index = array_search($version_file,$the_wpdoc['archived']);
		unset($the_wpdoc['archived'][$archive_index]);
		$the_wpdoc['archived'] = array_values($the_wpdoc['archived']);
		$wpdocs[$index] = $the_wpdoc;
		wpdocs_save_list($wpdocs);
		unlink($upload_dir['basedir'].'/wpdocs/'.$version_file);
	} else wpdocs_errors(WPDOCS_ERROR_4,'error');
}

function wpdocs_update_revision() {
	//MDOCS NONCE VERIFICATION
	if ($_REQUEST['wpdocs-nonce'] == WPDOCS_NONCE ) {
		if($_POST['wpdocs-version'] != 'current') {
			global $current_user;
			$wpdocs = get_option('wpdocs-list');
			$wpdocs = wpdocs_array_sort();
			$wpdocs_index = $_POST['wpdocs-index'];
			$upload_dir = wp_upload_dir();
			$the_wpdoc = $wpdocs[$wpdocs_index];
			$the_update =  substr($the_wpdoc['archived'][$_POST['wpdocs-version']], 0, strrpos($the_wpdoc['archived'][$_POST['wpdocs-version']], '-'));
			$the_update_type =  substr(strrchr($the_update, '.'), 1 );
			$old_doc_name = $the_wpdoc['filename'].'-v'.preg_replace('/ /','',$the_wpdoc['version']);
			if(in_array($old_doc_name, $the_wpdoc['archived'])) $old_doc_name = $old_doc_name.'.'.time();
			$name = substr($the_wpdoc['filename'], 0, strrpos($the_wpdoc['filename'], '.') );
			$filename = $name.'.'.$the_update_type;
			rename($upload_dir['basedir'].'/wpdocs/'.$the_wpdoc['filename'],$upload_dir['basedir'].'/wpdocs/'.$old_doc_name);
			copy($upload_dir['basedir'].'/wpdocs/'.$the_wpdoc['archived'][$_POST['wpdocs-version']], $upload_dir['basedir'].'/wpdocs/'.$filename);
			$new_version = $the_wpdoc['version'].' revised';
			$wpdocs[$wpdocs_index]['filename'] = $filename;
			$wpdocs[$wpdocs_index]['name'] = $the_wpdoc['name'];
			$wpdocs[$wpdocs_index]['desc'] = $the_wpdoc['desc'];
			$wpdocs[$wpdocs_index]['version'] = (string)$new_version;
			$wpdocs[$wpdocs_index]['type'] = (string)$the_update_type;
			$wpdocs[$wpdocs_index]['cat'] = $the_wpdoc['cat'];
			$wpdocs[$wpdocs_index]['owner'] = $wpdocs_user = $current_user->display_name;
			$wpdocs[$wpdocs_index]['size'] = (string)filesize($upload_dir['basedir'].'/wpdocs/'.$filename);
			$wpdocs[$wpdocs_index]['modified'] = (string)time();
			array_push($wpdocs[$wpdocs_index]['archived'], $old_doc_name);
			$wpdocs = wpdocs_array_sort($wpdocs);
			wpdocs_save_list($wpdocs);
			$wp_filetype = wp_check_filetype($upload_dir['basedir'].'/wpdocs/'.$filename, null );
			$wpdocs_post = array(
				'ID' => $the_wpdoc['parent'],
				'post_author' => $current_user->ID
			);
			$wpdocs_post_id = wp_update_post( $wpdocs_post );
			$attachment = array(
				'ID' => $the_wpdoc['id'],
				'post_mime_type' => $wp_filetype['type'],
				'post_title' => $the_wpdoc['name'],
				'post_author' => $current_user->ID
			 );
			update_attached_file( $the_wpdoc['id'], $upload_dir['basedir'].'/wpdocs/'.$filename );
			$wpdocs_attach_id = wp_update_post( $attachment );
			$wpdocs_attach_data = wp_generate_attachment_metadata( $wpdocs_attach_id, $upload_dir['basedir'].'/wpdocs/'.$filename );
			wp_update_attachment_metadata( $wpdocs_attach_id, $wpdocs_attach_data );
			//wp_set_post_tags( $wpdocs_post_id, $the_wpdoc['name'].', '.$the_wpdoc['cat'].', WP Docs, '.$wp_filetype['type'] );
			wp_set_post_tags($wpdocs_post_id, $_POST['wpdocs-tags']);
		
		} else wpdocs_errors('You are already at the most recent version of this document.');
	} else wpdocs_errors(WPDOCS_ERROR_4,'error'); 
}

?>