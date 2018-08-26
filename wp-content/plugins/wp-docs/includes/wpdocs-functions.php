<?php if ( ! defined( 'ABSPATH' ) ) exit; 

	global $wpdocs_select, $wpdocs_list;
	$wpdocs_select = $wpdocs_list = array();
	
	
	
//error_reporting(E_ALL);
$the_rating = array();
function wpdocs_edit_file($the_wpdocs, $index, $current_cat) {
	?>
	<div class="wpdocs-edit-file">
		<span class="update" id="<?php echo $index ?>">
			<i class="fa fa-pencil"></i> <a href="<?php echo 'admin.php?page=wpdocs-engine.php&wpdocs-cat='.$current_cat.'&action=update-doc&wpdocs-index='.$index; ?>" title="Update this file" class="edit"><?php _e('Update','wpdocs'); ?></a> |
		</span>
		<span class='delete'>
			<i class="fa fa-remove"></i> <a class='submitdelete' onclick="return showNotice.warn();" href="<?php echo 'admin.php?wpdocs-nonce='.$_SESSION['wpdocs-nonce'].'&page=wpdocs-engine.php&wpdocs-cat='.$current_cat.'&action=delete-doc&wpdocs-index='.$index; ?>"><?php _e('Delete','wpdocs'); ?></a> |
		</span>
		<span class="versions">
			<i class="icon-off"></i> <a href="<?php echo 'admin.php?page=wpdocs-engine.php&wpdocs-cat='.$current_cat.'&wpdocs-index='.$index; ?>&action=wpdocs-versions" title="<?php _e('Versions','wpdocs'); ?>" class="edit"><?php _e('Versions','wpdocs'); ?></a></span>
	</div>
	<?php
}

function  wpdocs_des_preview_tabs($the_wpdoc) {
	$wpdocs_desc = apply_filters('the_content', $the_wpdoc['desc']);
	$wpdocs_desc = str_replace('\\','',$wpdocs_desc);
	$wpdocs_default_content = get_option('wpdocs-default-content');
	$wpdocs_show_description = get_option('wpdocs-show-description');
	$wpdocs_show_preview = get_option('wpdocs-show-preview');
	$wpdocs_hide_all_files = get_option( 'wpdocs-hide-all-files' );
	$wpdocs_hide_all_files_non_members = get_option( 'wpdocs-hide-all-files-non-members' );
	$upload_dir = wp_upload_dir();
	ob_start();
	?>
	<?php if($wpdocs_show_description && $wpdocs_show_preview) { ?><a class="wpdocs-nav-tab <?php if($wpdocs_default_content=='description') echo 'wpdocs-nav-tab-active'; ?>" data-wpdocs-show-type="desc" data-wpdocs-id="<?php echo $the_wpdoc['id']; ?>"><?php _e('Description', 'wpdocs'); ?></a><?php } ?>
	<?php if($wpdocs_show_preview && $wpdocs_show_description) { ?><a class="wpdocs-nav-tab <?php if($wpdocs_default_content=='preview') echo 'wpdocs-nav-tab-active'; ?>"  data-wpdocs-show-type="preview" data-wpdocs-id="<?php echo $the_wpdoc['id']; ?>"><?php _e('Preview', 'wpdocs'); ?></a><?php } ?>
	<div class="wpdocs-show-container" id="wpdocs-show-container-<?php echo $the_wpdoc['id']; ?>">
		<?php
		if(!isset($_POST['show_type']) && $wpdocs_show_description && $wpdocs_default_content == 'description') {
			?>
			<div class="mdoc-desc">
				<?php wpdocs_show_description($the_wpdoc['id']); ?>
			</div>
			<?php
		} elseif(!isset($_POST['show_type']) && $wpdocs_show_preview && $wpdocs_default_content == 'preview') {
			if($wpdocs_hide_all_files || $the_wpdoc['file_status'] == 'hidden') {
				 echo '<div class="alert alert-warning" role="alert">'.__('Preview is unavailable for this file.','wpdocs').'</div class="alert alert-warning" role="alert">';
			} else if( is_user_logged_in() == false && $wpdocs_hide_all_files_non_members) {
				echo '<div class="alert alert-warning" role="alert">'.__('Please login to view this file preview.','wpdocs').'</div>';
			} else {
				$show_preview = wpdocs_file_access($the_wpdoc);
				if( $show_preview ) {
					$is_image = getimagesize($upload_dir['basedir'].WPDOCS_DIR.$the_wpdoc['filename']);
				?>
				<div class="mdoc-desc">
				<?php if($is_image == false) { ?>
				<p><?php wpdocs_doc_preview($the_wpdoc); ?></p>
				<?php
				} else wpdocs_show_image_preview($the_wpdoc); ?>
				</div>
				<?php
				} else { echo '<p>'.__('Please login to access the preview.','wpdocs').'</p>'; }
			}
		}  ?>
	</div>
	<?php
	$the_des = ob_get_clean();
	return $the_des;
}


function wpdocs_post_page($att=null) {
	global $post;
	if($post->post_type = 'wpdocs-posts') {
		$wpdocs = get_option('wpdocs-list');
		foreach($wpdocs as $index => $value) {
			if($value['parent'] == $post->ID) { $the_wpdoc = $value; break; }
		}
		
		$query = new WP_Query('post_type=attachment&post_status=inherit&post_parent='.$post->ID);
		
		$user_info = get_userdata($post->post_author);
		$wpdocs_file = $query->post;
		$upload_dir = wp_upload_dir();
		$file = substr(strrchr($wpdocs_file->post_excerpt, '/'), 1 );
		//$filesize = filesize($upload_dir['basedir'].'/wpdocs/'.$file);
		$query = new WP_Query('pagename=wpdocs-library');	
		$permalink = get_permalink($query->post->ID);
		if( strrchr($permalink, '?page_id=')) $wpdocs_link = site_url().'/'.strrchr($permalink, '?page_id=');
		else $wpdocs_link = site_url().'/'.$query->post->post_name.'/';
		$wpdocs_desc = apply_filters('the_content', $post->post_excerpt);
		ob_start();
		$the_page = '<div class="wpdocs-post wpdocs-post-current-file">';
		$the_page .= wpdocs_file_info_large($the_wpdoc, 'site', $index, null);
		$the_page .= '<div class="wpdocs-clear-both"></div>';
		$the_page .= wpdocs_social($the_wpdoc);
		$the_page .= '</div>';
		$the_page .= '<div class="wpdocs-clear-both"></div>';
		$the_page .= wpdocs_des_preview_tabs($the_wpdoc);
		$the_page .= '<div class="wpdocs-clear-both"></div>';
		$the_page .= '</div>';
		$the_page .= ob_get_clean();
		//var_dump(is_user_logged_in());
		//var_dump(get_option('wpdocs-hide-all-posts-non-members'));
		if(get_option('wpdocs-hide-all-posts') == false && get_option('wpdocs-hide-all-posts-non-members') == false) return $the_page;
		elseif(is_user_logged_in() && get_option('wpdocs-hide-all-posts-non-members')) return $the_page;
		elseif(is_user_logged_in() == false && get_option('wpdocs-hide-all-posts-non-members')) return 'You must be logged in to see this page.';
		elseif(get_option('wpdocs-hide-all-posts')) return 'Sorry you can\'t see the page.';
		   
		   
		   //|| is_user_logged_in() == false && get_option('wpdocs-hide-all-posts-non-members') == '1') return $the_page;
	} else {
		print nl2br(get_the_content('Continue Reading &rarr;'));
	}
}

function wpdocs_rename_file($upload, $file_name) {
	//exit;
	$upload_dir = wp_upload_dir();
	$index = 0;
	$org_filename = $file_name;
	while(file_exists($upload_dir['basedir'].'/wpdocs/'.$file_name)) {
		$index++;
		$explode = explode('.',$org_filename);
		$tail = $index.'.'.$explode[count($explode)-1];
		array_pop($explode);
		$file_name = implode('',$explode).$tail;
	}
	$upload['url'] = $upload_dir['baseurl'].'/wpdocs/'.$file_name;
	$upload['file'] = $upload_dir['basedir'].'/wpdocs/'.$file_name;
	$upload['filename'] = $file_name;
	$name = substr($file_name, 0, strrpos($file_name, '.') );
	if(!isset($_POST['wpdocs-name']) || $_POST['wpdocs-name'] == '') $upload['name'] = $name;
	else $upload['name'] = $_POST['wpdocs-name'];
	return $upload;
}

function wpdocs_process_file($file, $import=false) {
	global $current_user;
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	if(isset($_POST['wpdocs-type'])) $wpdocs_type = $_POST['wpdocs-type'];
	else $wpdocs_type = null;
	
	//pree($_POST);exit;
	//if(!isset($_POST['wpdocs-desc'])) $desc = WPDOCS_DEFAULT_DESC;
	$desc = $_POST['wpdocs-desc'];
	$post_status = $file['post-status'];
	if($import) $desc = $file['desc'];
	if($desc == null) $desc = '';
	$upload_dir = wp_upload_dir();
	$date_format = get_option('wpdocs-date-format');
	if(method_exists('DateTime', 'createFromFormat')) {
		$dtime = DateTime::createFromFormat($date_format, $_POST['wpdocs-last-modified']);
		if($dtime != false) {
			$timestamp = $dtime->getTimestamp();
			$wordpress_date = gmdate('Y-m-d H:i:s',$timestamp);
		} else {
			$timestamp = time()+WPDOCS_TIME_OFFSET;
			$wordpress_date = gmdate('Y-m-d H:i:s',$timestamp);
			//$upload['error'] = __('The date format you have entered is incorrect. Please use this format','wpdocs').' <b>'.$date_format.'</b>';
		}
	} else {
		$timestamp = time()+WPDOCS_TIME_OFFSET;
		$wordpress_date = gmdate('Y-m-d H:i:s',$timestamp);
	}
	if($file['error'] > 0) {
		$upload['error'] = __('An Error has occured.','wpdocs');
		return $upload;
	}
	if($import == false) {
		$upload['url'] = $upload_dir['baseurl'].'/wpdocs/'.$file['name'];
		$upload['file'] = $upload_dir['basedir'].'/wpdocs/'.$file['name'];
		$upload['filename'] = $file['name'];
		if(file_exists($upload_dir['basedir'].'/wpdocs/'.$file['name'])) $upload = wpdocs_rename_file($upload, $file['name']);
		else {
			//exit;
			$name = substr($file['name'], 0, strrpos($file['name'], '.') );
			if(!isset($_POST['wpdocs-name']) || $_POST['wpdocs-name'] == '') $upload['name'] = $name;
			else $upload['name'] = $_POST['wpdocs-name'];
		}
		//pree($_POST);exit;
		$result = move_uploaded_file($file['tmp_name'], $upload['file']);
		if($result == false) rename($file['tmp_name'], $upload['file']);
		//chmod($upload['file'], 0600);
	} else {
		$upload['url'] = $upload_dir['baseurl'].'/wpdocs/'.$file['filename'];
		$upload['file'] = $upload_dir['basedir'].'/wpdocs/'.$file['filename'];
		$upload['filename'] = $file['filename'];
		$upload['name'] = $file['name'];
	}
	$wp_filetype = wp_check_filetype($upload['file'], null );
	if($wpdocs_type == 'wpdocs-add' || $import == true) {
		$wpdocs_post = array(
			'post_title' => $upload['name'],
			'post_status' => $post_status,
			'post_content' => '[wpdocs_post_page new=true]',
			'post_author' => $current_user->ID,
			'post_excerpt' => $desc,
			'post_date' => $wordpress_date,
			'post_type' => 'wpdocs-posts',
		);
		$wpdocs_post_id = wp_insert_post( $wpdocs_post, true );
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => preg_replace('/\.[^.]+$/', '', basename($upload['name'])),
			'post_content' => '',
			'post_author' => $current_user->ID,
			'post_status' => 'inherit',
			'post_excerpt' => $upload['url'],
			'comment_status' => 'closed',
			'post_date' => $wordpress_date,
		 );
		$wpdocs_attach_id = wp_insert_attachment( $attachment, $upload['file'], $wpdocs_post_id );
		$wpdocs_attach_data = wp_generate_attachment_metadata( $wpdocs_attach_id, $upload['file'] );
		wp_update_attachment_metadata( $wpdocs_attach_id, $wpdocs_attach_data );
		$upload['parent_id'] = $wpdocs_post_id;
		$upload['attachment_id'] = $wpdocs_attach_id;
		//wp_set_post_tags( $wpdocs_post_id, $upload['name'].', WP Docs, '.$wp_filetype['type'] );
		wp_set_post_tags($wpdocs_post_id, $_POST['wpdocs-tags']);
	} elseif($wpdocs_type == 'wpdocs-update') {
		$wpdocs_post = array(
			'ID' => $file['parent'],
			'post_title' => $upload['name'],
			'post_status' =>$post_status,
			'post_content' => '[wpdocs_post_page]',
			'post_author' => $current_user->ID,
			'post_excerpt' => $desc,
			'post_date' => $wordpress_date,
		);
		//var_dump($wpdocs_post);
		$wpdocs_post_id = wp_update_post( $wpdocs_post );
		$attachment = array(
			'ID' => $file['id'],
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => $upload['name'],
			'post_content' => '',
			'post_author' => $current_user->ID,
			'post_status' => 'inherit',
			'post_excerpt' => $upload['url'],
			'post_date' => $wordpress_date,
		 );
		update_attached_file( $file['id'], $upload['file'] );
		$wpdocs_attach_id = wp_update_post( $attachment );
		$wpdocs_attach_data = wp_generate_attachment_metadata( $wpdocs_attach_id, $upload['file'] );
		wp_update_attachment_metadata( $wpdocs_attach_id, $wpdocs_attach_data );
		//wp_set_post_tags( $wpdocs_post_id, $upload['name'].', WP Docs, '.$wp_filetype['type'] );
		wp_set_post_tags($wpdocs_post_id, $_POST['wpdocs-tags']);
	}
	$upload['desc'] = $desc;
	return $upload;
}

function wpdocs_nonce() {
	session_start();
	if(isset($_SESSION['wpdocs-nonce'])) define('WPDOCS_NONCE',$_SESSION['wpdocs-nonce']);
	if(!isset($_SESSION['wpdocs-nonce']) || isset($_REQUEST['wpdocs-nonce'])) $_SESSION['wpdocs-nonce'] = md5(rand(0,1000000));
	session_write_close();	
}
function wpdocs_array_sort($the_array=null, $orderby=null, $sort_types=null) {
	if($the_array == null) $the_array = get_option('wpdocs-list');
	if($orderby == null) $orderby = get_option('wpdocs-sort-type');
	if($sort_types == null) $sort_types = get_option('wpdocs-sort-style');
	$disable_user_sort = get_option('wpdocs-disable-user-sort');
	if($disable_user_sort == false) {
		$sort_types = get_option('wpdocs-sort-style');
		if(isset($_COOKIE['wpdocs-sort-type'])) $orderby = $_COOKIE['wpdocs-sort-type'];
		if(isset($_COOKIE['wpdocs-sort-range'])) $sort_types = $_COOKIE['wpdocs-sort-range'];
	}
	if($sort_types == 'desc') $sort_types = SORT_DESC;
	if($sort_types == 'asc') $sort_types = SORT_ASC;
    if($the_array != null) {
		foreach($the_array as $a){ 
			foreach($a as $key=>$value){ 
				if(!isset($sortArray[$key])){ 
					$sortArray[$key] = array(); 
				} 
				$sortArray[$key][] = $value; 
			} 
		}
		$array_lowercase = array_map('strtolower', $sortArray[$orderby]);
		if(is_numeric($array_lowercase[0])) $sort_var_type = SORT_NUMERIC;
		else $sort_var_type = SORT_STRING;
		array_multisort($array_lowercase, $sort_types, $sort_var_type,$the_array);
		$the_array = array_values($the_array);
		return $the_array;
	} else return array();
}

function wpdocs_export_file_status() {
	$upload_dir = wp_upload_dir();
	$wpdocs_zip = get_option('wpdocs-zip');
	if(file_exists($upload_dir['basedir'].'/wpdocs/'.$wpdocs_zip)) {
		wpdocs_errors(WPDOCS_ZIP_STATUS_OK);
	} else wpdocs_errors(WPDOCS_ZIP_STATUS_FAIL,'error');
}


function wpdocs_errors($error, $type='updated') {
	if($type == 'error') $error = '<b>'.__('WP Error','wpdocs').': </b>'.$error;
	else $error = '<b>'.__('WP Info','wpdocs').': </b>'.$error;
	?>
	<div class="<?php echo $type; ?>" style="clear:both;">
		<div id="wpdocs-error">
		<p><?php _e($error); ?></p>
		</div>
	</div>
    <?php
}

function wpdocs_get_rating($the_wpdoc) {
	global $current_user;
	$avg = 0;
	$the_rating = array('total'=>0,'your_rating'=>0);
	if(is_array($the_wpdoc['ratings']) && count($the_wpdoc['ratings']) > 0 ) {
		foreach($the_wpdoc['ratings'] as $index => $average) {
			$avg += $average;
			$the_rating['total']++;
			if($current_user->user_email == $index) $the_rating['your_rating'] = floatval($average);
		}
		$the_rating['average'] =  floatval(number_format($avg/$the_rating['total'],1));
		return $the_rating;
	} else {
		$the_rating['total'] = 0;
		$the_rating['average'] = '-';
		return $the_rating;
	}
	
}

function is_wpdocs_google_doc_viewer() {
	if(stripos($_SERVER['HTTP_USER_AGENT'], 'AppsViewer; http://drive.google.com' )) return true;
	else return false;
}

function wpdocs_is_bot() {
	/*
	$upload_dir = wp_upload_dir();
	$bots = strip_tags(file_get_contents(WPDOCS_ROBOTS));
	$bots = explode('|:::|',$bots);
	foreach($bots as $bot) {
        if ( stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false && $bot != 'via docs.google.com') {
			return true;
		}
    }
    */
	return false;
}

function wpdocs_send_bot_alert($is_bot=false) {
	if(isset($_SERVER['HTTP_REFERER'])) $url = $_SERVER['HTTP_REFERER'];
	if($is_bot === true) $bot = "<span style='color: red;'>This is a know bot</span>";
	else $bot = "<span style='color: green;'>This is either a legitimate download or a unknown bot.</span>";
	$to      = 'fahad@androisbubbles.com';
	$subject = 'Bot Alert';
	$message = "<h4>This is a debug message for WP Docs it is used to track bots.  Only user agent information and site url are being tracked.</h4>";
	$message .= '<h3>User Agent Info</h3><b>'.preg_replace('/\)/',')<br>',$_SERVER['HTTP_USER_AGENT'])."</b>";
	$message .= "<h4>Site URL: ". $url ."</h4>";
	$message .= "<h4>WP Version: ".WPDOCS_VERSION."</h4>";
	$message .= "<h3>Bot analyst: ".$bot."</h3>";
	$headers = 'From: '.get_bloginfo('admin_email') . "\r\n";
	$headers .= 'Reply-To: '.get_bloginfo('admin_email') . "\r\n";
	$headers .= 'X-Mailer: PHP/' . phpversion()."\r\n";
	$headers .= "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1";
	return mail($to, $subject, $message, $headers);
}

function wpdocs_hide_show_toogle() {
	$wpdocs = get_option( 'wpdocs-list' );
	$wpdocs_hide_all_files = get_option( 'wpdocs-hide-all-files' );
	$wpdocs_hide_all_files_non_members = get_option( 'wpdocs-hide-all-files-non-members' );
	/*
	$wpdocs_hide_all_posts = get_option( 'wpdocs-hide-all-posts' );
	$wpdocs_hide_all_posts_default = get_option( 'wpdocs-hide-all-posts-default' );
	$wpdocs_hide_all_posts_non_members = get_option( 'wpdocs-hide-all-posts-non-members' );
	$wpdocs_hide_all_posts_non_members_default = get_option( 'wpdocs-hide-all-posts-non-members-default' );
	
	if($wpdocs_hide_all_posts_non_members != $wpdocs_hide_all_posts_non_members_default) {
		if($wpdocs_hide_all_posts_non_members) {
			$query = new WP_Query('post_type=wpdocs-posts&posts_per_page=-1');
			foreach((array)$query->posts as $posts => $the_post) {
				$update_post = array(
					'ID' => $the_post->ID,
					'post_status' =>'private',
				);
				wp_update_post( $update_post );
			}
			update_option( 'wpdocs-hide-all-posts-non-members-default', true );
		} else {
			$file_status = 'public';
			$query = new WP_Query('post_type=wpdocs-posts&posts_per_page=-1');
			foreach((array)$query->posts as $posts => $the_post) {
				foreach($wpdocs as $mdoc => $the_doc) {
					$q = new WP_Query('post_type=attachment&post_status=inherit&post_parent='.$the_post->ID);
					if(intval($the_doc['id']) == $q->posts[0]->ID) { $file_status = $the_doc['file_status']; break; }
				}
				if($file_status == 'public' ) {
					$update_post = array(
						'ID' => $the_post->ID,
						'post_status' =>'publish',
					);
					$post_status = 'publish';
				} else {
					$update_post = array(
						'ID' => $the_post->ID,
						'post_status' =>'draft',
					);
					$post_status = 'draft';
				}
				wp_update_post( $update_post );
				$wpdocs[$mdoc]['post_status'] = $post_status;
			}
			wpdocs_save_list($wpdocs);
			update_option( 'wpdocs-hide-all-posts-non-members-default', false );
		}
	}
		
	if($wpdocs_hide_all_posts != $wpdocs_hide_all_posts_default) {
		if($wpdocs_hide_all_posts) {
			$query = new WP_Query('post_type=wpdocs-posts&posts_per_page=-1');
			foreach((array)$query->posts as $posts => $the_post) {
				$update_post = array(
					'ID' => $the_post->ID,
					'post_status' =>'draft',
				);
				wp_update_post( $update_post );
			}
			update_option( 'wpdocs-hide-all-posts-default', true );
		} elseif($wpdocs_hide_all_posts_non_members == false) {
			$file_status = 'public';
			$query = new WP_Query('post_type=wpdocs-posts&posts_per_page=-1');
			foreach((array)$query->posts as $posts => $the_post) {
				foreach($wpdocs as $mdoc => $the_doc) {
					$q = new WP_Query('post_type=attachment&post_status=inherit&post_parent='.$the_post->ID);
					if(intval($the_doc['id']) == $q->posts[0]->ID) { $file_status = $the_doc['file_status']; break; }
				}
				if($file_status == 'public') {
					$update_post = array(
						'ID' => $the_post->ID,
						'post_status' =>'publish',
					);
					} else {
					$update_post = array(
						'ID' => $the_post->ID,
						'post_status' =>'draft',
					);	
				}
				wp_update_post( $update_post );
			}
			update_option( 'wpdocs-hide-all-posts-default', false );
		}
		
	}
	*/
}

function wpdocs_check_read_write() {
	$is_read_write = false;
	$upload_dir = wp_upload_dir();
	if(is_readable($upload_dir['basedir']) && is_writable($upload_dir['basedir'])) $is_read_write = true;
	return $is_read_write;
}

function wpdocs_doc_preview($file_path) {
	
	if(get_option('wpdocs-preview-type') == 'google') {
		
		$file = str_replace('"', '\'', serialize($file_path));
		$link = site_url().'/?wpdocs-file='.$file;
		?>
		<script>
			var screenHeight = window.innerHeight-250;
			jQuery('#wpdocs-box-view-iframe').css({'height': screenHeight})
		</script>
        <?php //pree($file_path); ?>
		<iframe data-url="" id="wpdocs-box-view-iframe" class="drive-preview" src="//drive.google.com/viewerng/viewer?embedded=true&url=<?php echo $link; ?>" style="border: none; width: 100%;" seamless fullscreen></iframe>
		<?php
	} elseif(get_option('wpdocs-preview-type') == 'box' && get_option('wpdocs-box-view-key') != '') {
		$boxview = new wpdocs_box_view();
		$view_file = $boxview->downloadFile($file['box-view-id']);
		if(isset($view_file) && $view_file['type'] != 'error') { ?>
		<script>
			var screenHeight = window.innerHeight-275;
			jQuery('#wpdocs-box-view-iframe').css({'height': screenHeight})
		</script>
		<iframe id="wpdocs-box-view-iframe" src="https://view-api.box.com/1/sessions/<?php echo $view_file['id']; ?>/view?theme=dark" seamless fullscreen style="width: 100%; "></iframe>
		<?php } else { ?>
		<div class="alert alert-warning" role="alert"><?php echo $view_file['details'][0]['message']; ?></div>
		<?php
		}
	} else _e('No preview type has been selected','wpdocs');
}

function wpdocs_file_access($the_wpdoc) {
	$access = false;
	$wpdocs_hide_all_files_non_members = get_option( 'wpdocs-hide-all-files-non-members' );
	$wpdocs_hide_all_files = get_option( 'wpdocs-hide-all-files' );
	$wpdocs_hide_all_files_non_members = get_option( 'wpdocs-hide-all-files-non-members' );
	$wpdocs_show_non_members = $the_wpdoc['non_members'];
	$user_logged_in = is_user_logged_in();
	if($wpdocs_hide_all_files_non_members && $user_logged_in == false) $access = false;
	elseif($user_logged_in && $the_wpdoc['non_members'] == '') $access = true;
	elseif($wpdocs_show_non_members == false) $access = false;
	elseif($wpdocs_hide_all_files == false ) $access = true;
	else $access = false;
	return $access;
}

function wpdocs_get_cats($cats, $current_cat, $depth=0, $echo=true) {
	$nbsp = '';
	for($i=0;$i < $depth;$i++) $nbsp .= '&nbsp;&nbsp;';
	foreach( $cats as $index => $cat ){
		if($current_cat === $cat['slug']) $is_selected = 'selected="selected"';
		else $is_selected = '';
		//$is_selected = ( $cat['slug'] == $current_cat ) ? 'selected="selected"' : '';
		if($echo) echo '<option  value="'.$cat['slug'].'" '.$is_selected.'>'.$nbsp.$cat['name'].'</option>';
		if(count($cat['children']) > 0) { 
			wpdocs_get_cats($cat['children'], $current_cat ,$cat['depth']+1);
		}
	}
}

function wpdocs_recursive_search($array, $search_string='') {
    if ($array) {
        foreach ($array as $index => $value) {
            if (is_array($value)) {
                $result = wpdocs_recursive_search($value, $search_string);
				if($result != null) return $result;
            } else {
				if($search_string === $value) return $array;
            }
        }
    }
}

function wpdocs_get_current_cat_array($att) {
	$cats =  get_option('wpdocs-cats');
	if($att != null && !isset($_GET['wpdocs-cat'])) $current_cat_array = wpdocs_get_the_cat($att['cat']);
	elseif(isset($_GET['wpdocs-cat'])) $current_cat_array = wpdocs_get_the_cat($_GET['wpdocs-cat']);
	else $current_cat_array = wpdocs_get_the_cat($cats[0]['name']);
	return $current_cat_array;
}

function wpdocs_get_the_cat($current_cat='', $cat_array=null, $cat_base=null) {
	return wpdocs_recursive_search(get_option('wpdocs-cats'),$current_cat);
}

function wpdocs_update_num_cats($increase) {	update_option('wpdocs-num-cats',intval(get_option('wpdocs-num-cats')+$increase));
	}
function wpdocs_get_subcats($current_cat, $att=null) {
	global $post;
	if(!is_admin()) {
		$permalink = get_permalink($post->ID);
		if(preg_match('/\?page_id=/',$permalink) || preg_match('/\?p=/',$permalink)) {
			$permalink = $permalink.'&wpdocs-cat=';
		} else $permalink = $permalink.'?wpdocs-cat=';
	} else $permalink = 'admin.php?page=wpdocs-engine.php&wpdocs-cat=';
	$num_cols = 1;
	if(get_option('wpdocs-show-downloads') == '1' || get_option('wpdocs-show-downloads')) $num_cols++;
	if(get_option('wpdocs-show-author') == '1' || get_option('wpdocs-show-author')) $num_cols++;
	if(get_option('wpdocs-show-version') == '1' || get_option('wpdocs-show-version')) $num_cols++;
	if(get_option('wpdocs-show-update') == '1' || get_option('wpdocs-show-update')) $num_cols++;
	if(get_option('wpdocs-show-ratings') == '1' || get_option('wpdocs-show-ratings')) $num_cols++;
	if(get_option('wpdocs-list-type') == 'large') echo '<table class="wpdocs-list-table">';
	if($current_cat['parent'] != '') {
		$parent_cat = wpdocs_get_the_cat($current_cat['parent']);
	?>
	<tr class="wpdocs-parent-cat" >
		<td colspan="<?php echo $num_cols; ?>" id="title" class="wpdocs-tooltip">
			<a href="<?php echo $permalink.$parent_cat['slug'].'&att='.$att; ?>" alt="<?php echo $permalink.$parent_cat['slug']; ?>">
				<i class="fa fa-reply"></i> <?php echo $parent_cat['name']; ?>
				
			</a>
		</td>
	</tr>
	<?php
	} 
	if(isset($current_cat['children']) && count($current_cat['children']) > 0) {
		foreach($current_cat['children'] as $index => $child) {
			?>
			<tr class="wpdocs-sub-cats" >
				<td colspan="<?php echo $num_cols; ?>" id="title" class="wpdocs-tooltip">
					<a href="<?php echo $permalink.$child['slug'].'&att='.$att; ?>" alt="<?php echo $child['name']; ?>"><i class="fa fa-folder-o"></i> <?php echo $child['name']; ?>
				</td>
			</tr>
			<?php
		}
	}
	?>
	<tr class="wpdocs-current-cat" >
		<td colspan="<?php echo $num_cols; ?>" id="title" class="wpdocs-tooltip">
			<p><i class="fa fa fa-folder-open-o"></i> <?php echo $current_cat['name']; ?></p>
		</td>
	</tr>
	<?php
	if(get_option('wpdocs-list-type') == 'large') echo '</table>';
	return $num_cols;
}
function wpdocs_get_subcats2($current_cat, $multiple=1, $return=array()) {
	
	global $post, $wpdocs_select, $wpdocs_list;
	//pree($current_cat);
	$return = (!is_array($return)?array():$return);
	
	$multiple++;
	
	if($current_cat['parent'] != '') {
		$parent_cat = wpdocs_get_the_cat($current_cat['parent']);
	} 
	

	$select = array();
	
	//if(!empty($current_cat['children'])){
		$nth = array();
		$keys = array();
		
		$select_prep = array();
		$select_items = 0;
		
		$select_prep[] = '<select class="wpdocs-scats '.($multiple>2?'hides':'').'" id="'.$current_cat['slug'].'">
    				<option value="">Select</option>';

		$keys[] = $current_cat['slug'];
		foreach($current_cat['children'] as $cats){
			
			if(!is_array($cats))
			continue;
			
			if(!empty($cats['children'])){
				$nth[$cats['slug']] = $cats['children'];				
			}
			$keys[]= $cats['slug'];
			

		$select_prep[] = '<option data-parent="'.$cats['parent'].'" value="'.$cats['slug'].'">'.$cats['name'].' ('.count($cats['children']).')</option>';
		$select_items++;

		}

		$select_prep[] = '</select>';

		$select = ($select_items>0?$select_prep:$select);
		//$return['select'][] = $select;
		$wpdocs_select[$multiple][] = $select; 


		$select2 = array();
		
		if(!empty($nth)){
			//pree($nth);
			foreach($nth as $key=>$iter){


				if(!empty($iter)){
					
					$iter_str = '<select class="hides wpdocs-scats" id="'.$key.'">
					<option value="">Select</option>';
					
					foreach($iter as $list){
						$keys[] = $list['slug'];
						$iter_str .= '<option data-parent="'.$list['parent'].'" value="'.$list['slug'].'">'.$list['name'].'</option>';
						if(!empty($list['children'])){
							//$return['select'][] = wpdocs_get_subcats2($list, $multiple);
							wpdocs_get_subcats2($list, $multiple);
						}
					}
		
				}
				$iter_str .= '</select>';
				
				$select2[] = $iter_str;
		
			}

			//$return['select'][] = $select2;
			$wpdocs_select[$multiple][] = $select2;
				
		}
		
		//pree($keys);
	
		if(!empty($keys)){
			//$return['list'][] = '<div class="wpdocs-items">';

			$wpdocs = get_option('wpdocs-list');
			$wpdocs = wpdocs_array_sort();
			foreach($keys as $slug){
			
				$current_cat = $slug;
				//$return['list'][] = wpdoc_iteration($wpdocs, $current_cat);			
				$wpdocs_list[$multiple][] = wpdoc_iteration($wpdocs, $current_cat);
				
			}
			//$return['list'][] = '</div>';
			
		}
	//}

	

	$return = array_filter_recursive($return);
	return $return;
}
	function array_filter_recursive($input) 
	{ 
		foreach ($input as &$value) 
		{ 
		  if (is_array($value)) 
		  { 
			$value = array_filter_recursive($value); 
		  } 
		} 
		
		return array_filter($input); 
	} 
function wpdocs_custom_mime_types($existing_mimes=array()) {
	// Add file extension 'extension' with mime type 'mime/type'
	$wpdocs_allowed_mime_types = get_option('wpdocs-allowed-mime-types');
	foreach($wpdocs_allowed_mime_types as $index => $mime) {
		$existing_mimes[$index] = $mime;
	}
	$wpdocs_removed_mime_types = get_option('wpdocs-removed-mime-types');
	foreach($wpdocs_removed_mime_types as $index => $mime) {
		unset($existing_mimes[$mime]);
	}
	return $existing_mimes;
}

function wpdocs_list_header($show=true) {
	wpdocs_load_modals();
	if($show) {
		global $post, $wpdocs_data, $wpdocs_pro, $wpdocs_premium_link;
		$wpdocs = get_option('wpdocs-list');
		$num_docs = count($wpdocs);
		$cats = get_option('wpdocs-cats');
		$upload_dir = wp_upload_dir();
		$message = '';
		$current_cat = wpdocs_get_current_cat();
		
		if($post == null) $is_admin = true;
		else {
			$is_admin = false;
			$permalink = get_permalink($post->ID);
			if(preg_match('/\?page_id=/',$permalink) || preg_match('/\?p=/',$permalink)) {
				$permalink = $permalink.'&wpdocs-cat=';
			} else $permalink = $permalink.'?wpdocs-cat=';
		}
		
		?>
		<div class="wrap wpdocs-wrapper">
			
			<div class="wpdocs-admin-preview"></div>
			<?php if($message != "" && $type != 'update') { ?> <div id="message" class="error" ><p><?php _e($message); ?></p></div> <?php }?>
<?php 
			if(is_admin()) { 
				wpdocs_downward_compatibility();
			
?>
			
			<div class="btn-group" style="float:left;">
				<a class="add-update-btn btn btn-default btn-sm" data-toggle="modal" data-target="#wpdocs-add-update" data-wpdocs-id="" data-is-admin="<?php echo is_admin(); ?>" data-action-type="add-doc"  data-current-cat="<?php echo $current_cat; ?>" href=""><?php _e('Add New Document','wpdocs'); ?> <i class="fa fa-upload fa-lg"></i></a>
			</div>
			<?php
			if (current_user_can( 'administrator' )) {
			?>
			<div class="btn-group" style="float:right;">
				<button class="btn btn-default dropdown-toggle btn-sm" type="button" id="dropdownMenu1" data-toggle="dropdown"><?php _e('Options','wpdocs'); ?><span class="caret"></span></button>
				<ul class="dropdown-menu options-menu" role="menu" aria-labelledby="dropdownMenu1">
					 <li role="presentation" class="dropdown-header"><?php _e('File Options','wpdocs'); ?></li>
				  <li role="presentation"><a role="menuitem" tabindex="-1" href="?page=wpdocs-engine.php&wpdocs-cat=cats"><?php _e('Manage Directories','wpdocs'); ?></a></li>
				  <li role="presentation"><a role="menuitem" tabindex="-1" href="?page=wpdocs-engine.php&wpdocs-cat=allowed-file-types"><?php _e('Allowed File Types','wpdocs'); ?></a></li>
				  <li role="presentation" class="hide"><a role="menuitem" tabindex="-1" href="?page=wpdocs-engine.php&wpdocs-cat=import"><?php _e('Import','wpdocs'); ?></a></li>
				  <li role="presentation" class="hide"><a role="menuitem" tabindex="-1" href="?page=wpdocs-engine.php&wpdocs-cat=export"><?php _e('Export','wpdocs'); ?></a></li>
				  <li role="presentation" class="<?php echo ($wpdocs_pro?'':'hide'); ?>"><a role="menuitem" tabindex="-1" href="?page=wpdocs-engine.php&wpdocs-cat=batch"><?php _e('Batch Upload'); ?></a></li>
				  <li role="presentation" class="divider hide"></li>
				  <li role="presentation" class="dropdown-header hide"><?php _e('Admin Options','wpdocs'); ?></li>
				  <li class="hide" role="presentation"><a role="menuitem" tabindex="-1" href="?page=wpdocs-engine.php&wpdocs-cat=settings"><?php _e('Settings','wpdocs'); ?></a></li>
				  <li role="presentation" class="hide"><a role="menuitem" tabindex="-1" href="?page=wpdocs-engine.php&wpdocs-cat=find-lost-files"><?php _e('Find Lost Files','wpdocs'); ?></a></li>
				  <li role="presentation" class="hide"><a role="menuitem" tabindex="-1" href="?page=wpdocs-engine.php&wpdocs-cat=filesystem-cleanup"><?php _e('File System Cleanup','wpdocs'); ?></a></li>
				   <li role="presentation" class="hide"><a role="menuitem" tabindex="-1" href="?page=wpdocs-engine.php&wpdocs-cat=restore"><?php _e('Restore To Default','wpdocs'); ?></a></li>
					<li role="presentation" class="hide"><a role="menuitem" tabindex="-1" href="?page=wpdocs-engine.php&wpdocs-cat=short-codes"><?php _e('Short Codes','wpdocs'); ?></a></li>
					<li role="presentation" class="hide"><a role="menuitem" tabindex="-1" href="?page=wpdocs-engine.php&wpdocs-cat=server-compatibility"><?php _e('Test Server Compatibility','wpdocs'); ?></a></li>
					<?php
					if(get_option('wpdocs-preview-type') == 'box' && get_option('wpdocs-box-view-key') != '') {
					  ?>
					  
					  <li role="presentation" class="hide"><a class="success" role="menuitem" tabindex="-1" href="#" id="mdosc-3-0-patch-btn" data-num-docs="<?php echo $num_docs; ?>"><?php _e('Run Box View Preview and Thumbnail Updater','wpdocs'); ?></a></li>
					  <?php } ?>
				</ul>
			</div>
			<?php } ?>
			<br><br>
			<?php } ?>
		
<?php
	if(is_admin()){		
?>        
			<nav class="navbar navbar-default" role="navigation" id="wpdocs-navbar">
				<div class="container-fluid">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#wpdocs-navbar-collapse">
						  <span class="sr-only">Toggle navigation</span>
						  <span class="icon-bar"></span>
						  <span class="icon-bar"></span>
						  <span class="icon-bar"></span>
						</button>
						<span class="navbar-brand" href="admin.php?page=wpdocs-engine.php"><?php _e('Directories','wpdocs'); ?></span>
					</div>
					<div class="collapse navbar-collapse" id="wpdocs-navbar-collapse">
						<ul class="nav navbar-nav">
							<?php
							if(!empty($cats)) {
								foreach( $cats as $index => $cat ){
				//pree($current_cat_array);
				//pree($cat);
				//pree($cat['slug'].' - '.$current_cat);
				if(isset($cat['slug'])){
					if( $cat['slug'] == $current_cat){
						$class = ' active';
						
					}elseif(isset($cats[$current_cat_array['base_parent']]['slug']) && $cats[$current_cat_array['base_parent']]['slug'] == $cat['slug']){
						$class = ' active';
						
					}else{
						$class = '';
					}
					
				}else{
					$class = '';
				}
									
									
									if(is_dir($upload_dir['basedir'].'/wpdocs/')) {
										if($is_admin) echo '<li class="'.$class.'"><a href="?page=wpdocs-engine.php&wpdocs-cat='.$cat['slug'].' ">'.__($cat['name']).'</a></li>';
										else echo '<li class="'.$class.'"><a href="'.$permalink.$cat['slug'].'">'.__($cat['name']).'</a></li>';
									}
								}
							}
							?>
						</ul>
					</div>
				</div>
			</nav>
<?php
	}else{
?>
		
        <?php
        if(!empty($cats)) {
			?>
            <select class="wpdocs-folders">
            <option value="">Select</option>
            <?php
            foreach( $cats as $index => $cat ){
                if(isset($cat['slug']) && !empty($current_cat_array)) {
                    if( $cat['slug'] == $current_cat) $class = ' active';
                    elseif(isset($cats[$current_cat_array['base_parent']]['slug']) && $cats[$current_cat_array['base_parent']]['slug'] == $cat['slug']) $class = ' active';
                    else $class = '';
                } else $class = '';
                if(is_dir($upload_dir['basedir'].'/wpdocs/')) {
                    if($is_admin) echo '<a class="'.$class.'" href="?page=wpdocs-engine.php&wpdocs-cat='.$cat['slug'].' ">'.__($cat['name']).'</a>';
                    else{
						$val = $cat['slug'];
						?>
                        <option data-id="<?php echo $val; ?>" value="<?php echo $permalink.$val; ?>" class="<?php echo $class; ?>" <?php echo (($_GET['wpdocs-cat']==$val || (!isset($_GET['wpdocs-cat']) && $index==0))?'selected="selected"':''); ?>><?php echo __($cat['name']); ?> (<?php echo count($cat['children']); ?>)</option>
                         <?php
					}
                }
            }
			?>
            </select>
            <?php
        }
        ?>



<?php		
	}
?>
		<?php
		
	} else {
		echo '<div class="wrap">';
	}
	return $current_cat;
}

function wpdocs_get_current_cat($atts=null) {
	$cats =  get_option('wpdocs-cats');
	$current_cat = '';
	if(isset($_GET['wpdocs-cat']) && !isset($atts['cat'])) $current_cat = $_GET['wpdocs-cat'];
	elseif(!is_string($cats) && !isset($atts['cat'])) $current_cat = $cats[0]['slug'];
	elseif(isset($_GET['wpdocs-cat']) && isset($atts['cat']) && isset($_GET['att']) &&  $atts['cat'] == $_GET['att']) {
		$cat = wpdocs_recursive_search($cats, $_GET['wpdocs-cat']);
		$current_cat = $cat['slug'];
	} elseif(isset($atts['cat'])) {
		$cat = wpdocs_recursive_search($cats, $atts['cat']);
		$current_cat = $cat['slug'];
	} 
	return $current_cat;
}

// GET ALL MDOCS POST AND DISPLAYS THEM ON THE MAIN PAGE.
add_filter( 'pre_get_posts', 'wpdocs_get_posts' );
function wpdocs_get_posts( $query ) {
	if ( is_home() && $query->is_main_query() ||  $query->is_search == false && !is_admin() && isset($post) && has_shortcode( $post->post_content, 'wpdocs' )) {
		if(get_option('wpdocs-hide-all-posts') == false && get_option('wpdocs-hide-all-posts-non-members') == false) {
			$query->set( 'post_type', array( 'post', 'wpdocs-posts' ) );
		} elseif(is_user_logged_in() && get_option('wpdocs-hide-all-posts-non-members') == true) {
			$query->set( 'post_type', array( 'post', 'wpdocs-posts' ) );
		}
	}
}
// CREATES THE CUSTOM POST TYPE for this plugin Posts which handles all the WP Document Libaray posts.
function wpdocs_post_pages() {
	$labels = array(
		'name'               => __( 'WP Documents Posts', 'wpdocs' ),
		'singular_name'      => _x( 'wpdocs', 'wpdocs' ),
		'add_new'            => __( 'Add New', 'wpdocs' ),
		'add_new_item'       => __( 'Add New Documents', 'wpdocs' ),
		'edit_item'          => __( 'Edit Documents', 'wpdocs' ),
		'new_item'           => __( 'New Documents', 'wpdocs' ),
		'all_items'          => __( 'All Documents', 'wpdocs' ),
		'view_item'          => __( 'View Documents', 'wpdocs' ),
		'search_items'       => __( 'Search Documents', 'wpdocs' ),
		'not_found'          => __( 'No documents found', 'wpdocs' ),
		'not_found_in_trash' => __( 'No documents found in the Trash', 'wpdocs' ), 
		'parent_item_colon'  => '',
		'menu_name'          => 'WPDOCS Posts'
	);
	$show_wpdocs_post_menu = get_option('wpdocs-show-post-menu');
	$supports = array( 'title', 'editor','author','comments','revisions','page-attributes','post-formats'  );
	$args = array(
		'labels'              		=> $labels,
		'public'              		=> true,
		'publicly_queryable'  => true,
		'show_ui'             	=> $show_wpdocs_post_menu,
		'show_in_menu' 		=> true,
		'query_var'           	=> true,
		'rewrite'             		=> array( 'slug' => 'wpdocs-posts' ),
		'capability_type'     	=> 'post',
		'has_archive'         	=> true,
		'hierarchical'        	=> false,
		'menu_position'       => 5,
		'taxonomies' 			=> array('category','post_tag'),
		'supports'            		=> $supports,
	 );
	register_post_type( 'wpdocs-posts', $args );
}
add_action( 'init', 'wpdocs_post_pages' );

function wpdocs_save_list($wpdocs_list, $is_empty=false) {
	if($wpdocs_list != null || is_array($wpdocs_list)) {
		update_option('wpdocs-list', sanitize_wpdocs_data($wpdocs_list), 'no');
	}
	//else wpdocs_errors(WPDOCS_ERROR_7,'error'); 
}

function wpdocs_nav_size($collapse) {
	if($collapse) {
?>
<style type="text/css" media="screen" id="wpdocs-nav-collapse">
	@media (max-width: 10000px) {
		.navbar-header { float: none; }
		.navbar-toggle { display: block; }
		.navbar-collapse { border-top: 1px solid transparent; box-shadow: inset 0 1px 0 rgba(255,255,255,0.1); }
		.navbar-collapse.collapse { display: none!important; }
		.navbar-nav { float: none !important; margin: 7.5px -15px; }
		.navbar-nav>li { float: none; }
		.navbar-nav>li>a { padding-top: 10px; padding-bottom: 10px; }
		.navbar-collapse.collapse.in { display: block!important; }
		.collapsing { overflow: hidden!important; }
		#wpdocs-navbar .navbar-collapse ul li { margin: 0; }
	}
</style>
<?php
	} else {
?>
<style type="text/css" media="screen" id="wpdocs-nav-expand">
	#wpdocs-navbar .navbar-toggle { display: none !important; }
	#wpdocs-navbar .navbar-header { float: left; margin: 0;  }
	#wpdocs-navbar .navbar-header .navbar-brand  { margin: 0; } 
	#wpdocs-navbar .navbar-collapse { display: block; margin: 0px; border: none; }
	#wpdocs-navbar .navbar-collapse ul, #wpdocs-navbar .navbar-collapse ul li { float: left; height: 50px;}
	#wpdocs-navbar .navbar-collapse ul li a { padding: 15px; }
</style>
<?php
	}
}
function wpdocs_box_view_update_v3_0() {
	?>
	<style>
		body, html { overflow: hidden; }
		.bg-3-0 { width: 100%; height: 100%; background: #000; position: absolute; top: 0; left: 0; z-index: 9999; padding: 0; margin: 0;  opacity:  0.7;}
		.container-3-0 { position: absolute; top: 50px; z-index: 10000; width: 500px; background: #fff; margin-left: 50%; left: -250px; padding: 10px;}
		.container-3-0 h1 { color: #2ea2cc; }
		.container-3-0 h3 { color: red; }
		.btn-container-3-0 { text-align: center; }
		@media (max-width: 640px) {
			.container-3-0 { width: 360px; left: -180px; z-index: 10000; margin-left: 50%}
		}
	</style>
	<div class="bg-3-0"></div>
	<div class="container-3-0">
		<h1><?php _e('WP Docs', 'wpdocs'); ?></h1>
		<h2><?php _e('Document Preview Updater', 'wpdocs'); ?></h2>
		<p><?php _e('Version 3.0 of WP Docs now uses a new documents preview tool Called', 'wpdocs'); ?> <a href="https://box-view.readme.io" target="_blank"><?php _e('Box View', 'wpdocs'); ?></a>.</p>
		<p><?php _e('This process requires an update to your WP Docs, which will be adding information needed for', 'wpdocs'); ?> <a href="https://box-view.readme.io" target="_blank"><?php _e('Box View', 'wpdocs'); ?></a> <?php _e('to work properly.', 'wpdocs'); ?></p>
		<h3><?php _e('Important, Please Read', 'wpdocs'); ?></h3>
		<p><b><?php _e('The process depending on the size of your Library can take a long time, so make sure you have the time run this updater.', 'wpdocs'); ?></b></p>
		<p><b><?php _e('If you choose not to run this updater now preview will not work.', 'wpdocs'); ?></b></p>
		<p><b><?php _e('You may run this process anytime by going to the Settings menu and pressing "Run Preview and Thumbnail Updater".', 'wpdocs'); ?></b></p>
		<h3><?php _e('DO NOT LEAVE PAGE ONCE THIS UPDATER HAS STARTER!', 'wpdocs'); ?></h3>
		<div class="btn-container-3-0">
			<button id="run-updater-3-0"><?php _e('Run Updater', 'wpdocs'); ?></button>
			<button id="not-now-3-0"><?php _e('Not Right Now', 'wpdocs'); ?></button>
		</div>
	</div>
	
	<?php
}
function wpdocs_v3_0_patch_run_updater() {
	$wpdocs = get_option('wpdocs-list');
	$boxview = new wpdocs_box_view();
	foreach($wpdocs as $index => $the_wpdoc) {
		//if(!isset($the_wpdoc['box-view-id'])) {
			$upload_file = $boxview->uploadFile(get_site_url().'/?wpdocs-file='.$the_wpdoc['id'].'&wpdocs-url=false&is-box-view=true', $the_wpdoc['filename']);
			$the_wpdoc['box-view-id'] = $upload_file['id'];
			$wpdocs[$index] = $the_wpdoc;
			update_option('wpdocs-list', $wpdocs, '' , 'no');
		//}
	}
	update_option('wpdocs-v3-0-patch-var-1',true);
	update_option('wpdocs-box-view-updated',true);
}
function wpdocs_v3_0_patch_cancel_updater() {
	update_option('wpdocs-v3-0-patch-var-1',true);
	update_option('wpdocs-box-view-updated',false);
}
function wpdocs_show_description($id) {
	$wpdocs = get_option('wpdocs-list');
	foreach($wpdocs as $index => $the_wpdoc) if($the_wpdoc['id'] == $id) { break; }
	$wpdocs_desc = apply_filters('the_content', $the_wpdoc['desc']);
	$wpdocs_desc = str_replace('\\','',$wpdocs_desc);
	if(get_option('wpdocs-preview-type') == 'box' && get_option('wpdocs-box-view-key') != '') {
		$boxview = new wpdocs_box_view();
		$thumbnail = $boxview->getThumbnail($the_wpdoc['box-view-id']);
		$json_thumbnail = json_decode($thumbnail,true);
	} else $json_thumbnail['type'] = 'error';
	$image_size = @getimagesize(get_site_url().'?wpdocs-img-preview='.$the_wpdoc['filename']);
	$thumbnail_size = 256;
	?>
	<h4><?php echo $the_wpdoc['filename']; ?></h4>
	<?php
	if($json_thumbnail['type'] != 'error') {
		if(function_exists('imagecreatefromjpeg')) {
			?>
			<div class="">
				<img class="wpdocs-thumbnail pull-left img-thumbnail img-responsive" src="<?php $boxview->displayThumbnail($thumbnail); ?>" alt="<?php echo $the_wpdoc['filename']; ?>" />
			</div>
			<?php
		}
	} elseif($the_wpdoc['type'] == 'pdf' && class_exists('imagick')) {
		$upload_dir = wp_upload_dir();
		$file = $upload_dir['basedir']."/wpdocs/".$the_wpdoc['filename'].'[0]';
		$thumbnail = new Imagick($file);
		$thumbnail->setbackgroundcolor('rgb(64, 64, 64)');
		$thumbnail->thumbnailImage(450, 300, true);
		$thumbnail->setImageFormat('png');
		$uri = "data:image/png;base64," . base64_encode($thumbnail);
		?>
		<div class="" >
			<img class="wpdocs-thumbnail pull-left img-thumbnail  img-responsive" src="<?php echo $uri; ?>" alt="<?php echo $the_wpdoc['filename']; ?>" />
		</div>
		<?php
	} elseif( $image_size != false) {
		$width = $image_size[0];
		$height = $image_size[1];
		$aspect_ratio = round($width/$height,2);
		// Width is greater than height and width is greater than thumbnail size
		if($aspect_ratio > 1&&  $width > $thumbnail_size) {
			$thumbnail_width = $thumbnail_size;
			$thumbnail_height = $thumbnail_size/$aspect_ratio;
		// Heigth is greater than width and height is greater then thumbnail size
		} elseif($aspect_ratio < 1 && $height > $thumbnail_size) {
			$aspect_ratio = round($height/$width,2);
			$thumbnail_width = $thumbnail_size/$aspect_ratio;
			$thumbnail_height = $thumbnail_size;
		// Heigth is greater than width and height is less then thumbnail size
		} elseif($aspect_ratio < 1 && $height < $thumbnail_size) {
			$aspect_ratio = round($height/$width,2);
			$thumbnail_width = $thumbnail_size/$aspect_ratio;
			$thumbnail_height = $thumbnail_size;
		// Width and height are equal
		} elseif($aspect_ratio == 1 ) {
			$thumbnail_width = $thumbnail_size;
			$thumbnail_height = $thumbnail_size;
		// Width is greater than height and width is less than thumbnail size
		} elseif($aspect_ratio > 1 && $width < $thumbnail_size) {
			$thumbnail_width = $thumbnail_size;
			$thumbnail_height = $thumbnail_size/$aspect_ratio;
		// Hieght is greater than width and height is less than thumbnail size
		} elseif($aspect_ratio > 1 && $height < $thumbnail_size) {
			$thumbnail_width = $thumbnail_size/$aspect_ratio;
			$thumbnail_height = $thumbnail_size;
		} else {
			$thumbnail_width = $thumbnail_size;
			$thumbnail_height = $thumbnail_size;
		}
		
		if(function_exists('imagecreatefromjpeg')) {
			ob_start();
			$upload_dir = wp_upload_dir();
			$src_image = $upload_dir['basedir'].WPDOCS_DIR.$the_wpdoc['filename'];
		
			if($image_size['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($src_image);
			elseif($image_size['mime'] == 'image/png') $image = imagecreatefrompng($src_image);
			elseif($image_size['mime'] == 'image/gif') $image = imagecreatefromgif($src_image);
			$thumnail =imagecreatetruecolor($thumbnail_width,$thumbnail_height);
			$white = imagecolorallocate($thumnail, 255, 255, 255);
			imagefill($thumnail, 0, 0, $white);
			imagecopyresampled($thumnail,$image,0,0,0,0,$thumbnail_width,$thumbnail_height,$image_size[0],$image_size[1]);
			
			imagepng($thumnail);
			imagedestroy($image);
			imagedestroy($thumnail);
			$png = ob_get_clean();
			$uri = "data:image/png;base64," . base64_encode($png);
			?>
			<div class="">
				<img class="wpdocs-thumbnail pull-left img-thumbnail  img-responsive" src="<?php echo $uri; ?>" alt="<?php echo $the_wpdoc['filename']; ?>" />
			</div>
			<?php
		}
	}
	echo $wpdocs_desc; ?>
	<div class=clearfix"></div>
	<?php
}

function wpdocs_show_image_preview($the_wpdoc) {
	//pree($the_wpdoc);
	?>
	<div style="text-align: center;">
		<img class="img-thumbnail wpdocs-img-preview" src="?wpdocs-img-preview=<?php echo $the_wpdoc['filename']; ?>" />
	</div>
	<?php
}

function wpdocs_search_users($user_search_string, $owner, $contributors) {
	if(!is_array($owner)) $owner = array();
	if(!is_array($contributors)) $contributors = array();
	$wp_roles = get_editable_roles();
	$found_roles = array();
	foreach($wp_roles as $index => $role) {
		if(substr( $index, 0, strlen($user_search_string) ) === strtolower($user_search_string)) $found_roles[$index] = $role['name'];
	}
	$users_filter_search = get_users( array( 'search' => $user_search_string.'*' ) );
	$found_users = array();
	foreach($users_filter_search as $index => $user) $found_users[$user->user_login] = $user->display_name;
	if(count($found_roles) > 0) {
		echo '<h4>Roles</h4>';
		echo '<div class="list-group">';
	} 
	foreach($found_roles as $index => $role) {
		if(!in_array($index, $contributors)) {
			echo '<a href="#" class="list-group-item list-group-item-warning wpdocs-search-results-roles" data-value="'.$index.'">'.$role.'</a>';
		}
	}
	if(count($found_roles) > 0) echo '</div>';
	if(count($found_users) > 0) {
		echo '<h4>Users</h4>';
		echo '<div class="list-group">';
	}
	foreach($found_users as $index => $user) {
		if($owner != $index && !in_array($index, $contributors)) {
			echo '<a href="#" class="list-group-item list-group-item-warning wpdocs-search-results-users" data-value="'.$index.'" >'.$index.' - ('. $user.')</a>';
		}
	}
	if(count($found_users) > 0) echo '</div>';
}

function wpdocs_file_upload_max_size() {
  static $max_size = -1;

  if ($max_size < 0) {
    // Start with post_max_size.
    $max_size = wpdocs_parse_size(ini_get('post_max_size'));

    // If upload_max_size is less, then reduce. Except if upload_max_size is
    // zero, which indicates no limit.
    $upload_max = wpdocs_parse_size(ini_get('upload_max_filesize'));
    if ($upload_max > 0 && $upload_max < $max_size) {
      $max_size = $upload_max;
    }
  }
  return $max_size;
}

function wpdocs_parse_size($size) {
  $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
  $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
  if ($unit) {
    // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
    return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
  }
  else {
    return round($size);
  }
}

	function wpdocs_plugin_links($links) { 
		global $wpdocs_premium_link, $wpdocs_pro;
		
		$settings_link = '<a href="admin.php?page=wpdocs-engine.php&wpdocs-cat=cats">Settings</a>';
		
		if($wpdocs_pro){
			array_unshift($links, $settings_link); 
		}else{			 
			$wpdocs_premium_link = '<a href="'.$wpdocs_premium_link.'" title="Go Premium" target=_blank>Go Premium</a>'; 
			array_unshift($links, $settings_link, $wpdocs_premium_link); 		
		}
		
		
		return $links; 
	}
	
	function wpdocs_admin_header(){
		
?>
	<style type="text/css">
    <?php if(isset($_GET['page']) && $_GET['page']=='wpdocs-engine.php'){
?>
	#message{
	    display:none;
    }
<?php		
	}
?>
    </style>
<?php		
	}
	
	add_action('admin_head', 'wpdocs_admin_header');