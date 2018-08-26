<?php if ( ! defined( 'ABSPATH' ) ) exit; 
function wpdocs_export() {
	$upload_dir = wp_upload_dir();
	$path = $upload_dir['basedir'];
	$wpdocs = get_option('wpdocs-list');
	$wpdocs = htmlspecialchars(serialize($wpdocs));
	$cats = htmlspecialchars(serialize(get_option('wpdocs-cats')));
	wpdocs_list_header();
?>
<div id="wpdocs-export-container"> 
<h2><?php _e('Export Files','wpdocs'); ?></h2>
<p>When you click the buttons below the document repository will create a ZIP files for you to save to your computer.</p>
<p>This compressed data, will contain your documents, saved variables, and media posts tied to each document.</p>
<p>Once you've saved the download file, you can use the Import function in another WordPress installation to import the content from this site.</p>
<h3>Click the Button to Export WP Documents</h3>
<form action="" method="post" id="wpdocs-export">
	<input type="button" onclick="wpdocs_download_zip('<?php echo get_option('wpdocs-zip'); ?>');" id="wpdocs-export-submit" class="button button-primary" value="<?php _e('Export WP Docs','wpdocs'); ?>">
</form><br>
</div>
<?php
	//if($_GET['wpdocs-cat'] == 'export' || $_GET['wpdocs-cat'] == 'import') wpdocs_export_file_status();
}
function wpdocs_export_zip() {
	$wpdocs_zip = get_option('wpdocs-zip');
	$wpdocs_list = get_option('wpdocs-list');
	if(empty($wpdocs_list)) $wpdocs_list = array();
	$wpdocs_cats = get_option('wpdocs-cats');
	if(is_string($wpdocs_cats)) $wpdocs_cats = array();
	$upload_dir = wp_upload_dir();
	$wpdocs_zip_file = sys_get_temp_dir().'/'.$wpdocs_zip;
	if(is_dir(sys_get_temp_dir().'/wpdocs/')) {
		$files = glob(sys_get_temp_dir().'/wpdocs/*');
		foreach($files as $file) if(is_file($file)) unlink($file);
		if(is_file(sys_get_temp_dir().'/wpdocs/.htaccess')) unlink(sys_get_temp_dir().'/wpdocs/.htaccess');
		rmdir(sys_get_temp_dir().'/wpdocs/');
	}
	mkdir(sys_get_temp_dir().'/wpdocs/');
	$wpdocs_cats_file = sys_get_temp_dir().'/wpdocs/'.WPDOCS_CATS;
	$wpdocs_list_file = sys_get_temp_dir().'/wpdocs/'.WPDOCS_LIST;
	file_put_contents($wpdocs_cats_file, serialize($wpdocs_cats));
	file_put_contents($wpdocs_list_file, serialize($wpdocs_list));
	wpdocs_zip_dir($upload_dir['basedir'].'/wpdocs',$wpdocs_zip_file,true);
	if(file_exists($wpdocs_cats_file)) unlink($wpdocs_cats_file);
	if(file_exists($wpdocs_list_file)) unlink($wpdocs_list_file);
	
	if(is_dir(sys_get_temp_dir().'/wpdocs/')) {
		$files = glob(sys_get_temp_dir().'/wpdocs/*');
		if(file_exists(sys_get_temp_dir().'/wpdocs/.htaccess')) unlink(sys_get_temp_dir().'/wpdocs/.htaccess');
		foreach($files as $file) if(is_file($file)) unlink($file);
		rmdir(sys_get_temp_dir().'/wpdocs/');
	}
	
}
function wpdocs_zip_dir($sourcePath, $outZipPath)  { 
    @unlink($outZipPath);
	$pathInfo = pathInfo($sourcePath); 
    $parentPath = $pathInfo['dirname']; 
    $dirName = $pathInfo['basename'];
	if(class_exists('ZipArchive')) {
		$z = new ZipArchive(); 
		$z->open($outZipPath, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE); 
		$z->addEmptyDir($dirName); 
		wpdocs_folder_zip($sourcePath, $z, strlen("$parentPath/")); 
		$z->close();
	} else die('ZipArchive Not Installed.');
    
}
function wpdocs_folder_zip($folder, &$zipFile, $exclusiveLength) { 
	$handle = opendir($folder);
	$zipFile->addFile(sys_get_temp_dir().'/wpdocs/'.WPDOCS_CATS, 'wpdocs/'.WPDOCS_CATS); 
	$zipFile->addFile(sys_get_temp_dir().'/wpdocs/'.WPDOCS_LIST, 'wpdocs/'.WPDOCS_LIST); 
    while (false !== $f = readdir($handle)) { 
      if ($f != '.' && $f != '..' && $f != 'wpdocs-files.bak') { 
        $filePath = "$folder/$f";
        // Remove prefix from file path before add to zip. 
        $localPath = substr($filePath, $exclusiveLength); 
        if (is_file($filePath)) { 
          $zipFile->addFile($filePath, $localPath); 
        } elseif (is_dir($filePath)) { 
          // Add sub-directory. 
          $zipFile->addEmptyDir($localPath); 
          wpdocs_folder_zip($filePath, $zipFile, $exclusiveLength); 
        } 
      } 
    } 
    closedir($handle); 
}
function wpdocs_download_export_file($file) {
	$filename = $file;
	$file = sys_get_temp_dir()."/".$file;
	if (file_exists($file)) {		
			header('Content-Description: File Transfer');
			header('Content-Type: application/zip');
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
			exit;
	} else wpdocs_errors(__('WP Documents Error','wpdocs').': '.basename($file).' '.__('was not found, file not exported.', 'wpdocs'), 'error');
}
?>