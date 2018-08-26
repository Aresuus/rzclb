<?php if ( ! defined( 'ABSPATH' ) ) exit; 
/************************************************ .php ************************************************/
/************************************************ .php ************************************************/
/************************************************ .php ************************************************/
/************************************************ .php ************************************************/
/************************************************ wpdocs-modals.php ************************************************/
function wpdocs_load_modals() {
	wpdocs_load_preview_modal();
	wpdocs_load_ratings_modal();
	wpdocs_load_add_update_modal();
	wpdocs_load_share_modal();
	wpdocs_load_description_modal();
}
function wpdocs_load_add_update_modal() {
	?>
	<div class="modal fade wpdocs-modal" id="wpdocs-add-update" tabindex="-1" role="dialog" aria-labelledby="wpdocs-add-update" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-body">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php _e('Close','wpdocs'); ?></span></button>
					<div class="wpdocs-add-update-body">
						<?php wpdocs_uploader(); ?>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				</div>
			</div>
		</div>
	</div>
	<?php
}
function wpdocs_load_description_modal() {
	?>
	<div class="modal fade wpdocs-modal" id="wpdocs-description-preview" tabindex="-1" role="dialog" aria-labelledby="wpdocs-description-preview" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-body">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php _e('Close','wpdocs'); ?></span></button>
					<div class="wpdocs-description-preview-body wpdocs-modal-body wpdocs-post"></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				</div>
			</div>
		</div>
	</div>
	<?php
}
function wpdocs_load_preview_modal() {
	?>
	<div class="modal fade wpdocs-modal" id="wpdocs-file-preview" tabindex="-1" role="dialog" aria-labelledby="wpdocs-file-preview" aria-hidden="true" >
		<div class="modal-dialog modal-lg" style="height: 100% !important;">
			<div class="modal-content">
				<div class="modal-body">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php _e('Close','wpdocs'); ?></span></button>
					<div class="wpdocs-file-preview-body wpdocs-modal-body"></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				</div>
			</div>
		</div>
	</div>
	<?php
}

function wpdocs_load_ratings_modal() {
	?>
	<div class="modal fade wpdocs-modal" id="wpdocs-rating" tabindex="-1" role="dialog" aria-labelledby="wpdocs-ratings" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-body">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php _e('Close','wpdocs'); ?></span></button>
					<div class="wpdocs-ratings-body wpdocs-modal-body"></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				</div>
			</div>
		</div>
	</div>
	<?php
}
function wpdocs_load_share_modal() {
	?>
	<div class="modal fade wpdocs-modal" id="wpdocs-share" tabindex="-1" role="dialog" aria-labelledby="wpdocs-share" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-body">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php _e('Close','wpdocs'); ?></span></button>
					<div class="wpdocs-share-body wpdocs-modal-body"></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				</div>
			</div>
		</div>
	</div>
	<?php
}
/******************************************* wpdocs-restore-defaults.php *******************************************/
function wpdocs_restore_default() {
	if(isset($_POST['type']) && $_POST['type'] == 'restore') {
		$blog_id = intval($_POST['blog_id']);
		if ( is_main_site($blog_id) ) wpdocs_single_site_remove();
		else wpdocs_single_site_remove($blog_id);
	} else { 
		if (is_multisite()) {
			 wpdocs_multi_site_remove();
		} else {
			wpdocs_single_site_remove();
		}
	}
}

function wpdocs_multi_site_remove() {
	global $wpdb;
	$blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
	if ($blogs) {
		$init_blog = true;
		foreach($blogs as $blog) {
			switch_to_blog($blog['blog_id']);
			$upload_dir = wp_upload_dir();
			$wpdocs_list = get_option('wpdocs-list');
			if(is_array($wpdocs_list)) {
				foreach($wpdocs_list as $the_doc) {
					wp_delete_attachment( intval($the_doc['id']), true );
					wp_delete_post( intval($the_doc['parent']), true );
				}
			}
			if($init_blog) $results = $wpdb->get_results( 'SELECT * FROM wp_options WHERE option_name LIKE "wpdocs%" ', ARRAY_A );
			else $results = $wpdb->get_results( 'SELECT * FROM wp_'.$blog['blog_id'].'_options WHERE option_name LIKE "wpdocs%" ', ARRAY_A );
			foreach($results as $result) delete_option($result['option_name']);
			$files = glob($upload_dir['basedir'].'/wpdocs/*'); 
			foreach($files as $file) if(is_file($file)) unlink($file);
			$files = glob($upload_dir['basedir'].'/wpdocs/.*'); 
			foreach($files as $file) if(is_file($file)) unlink($file);
			if(is_dir($upload_dir['basedir'].'/wpdocs/')) rmdir($upload_dir['basedir'].'/wpdocs/');
			$query = new WP_Query('pagename=wpdocs-library');
			wp_delete_post( $query->post->ID, true );
			$init_blog = false;
		}
		restore_current_blog();
	}
}
function wpdocs_single_site_remove($blog_id=null) {
	global $wpdb;
	$upload_dir = wp_upload_dir();
	$wpdocs_list = get_option('wpdocs-list');
	if(is_array($wpdocs_list)) {
		foreach($wpdocs_list as $the_doc) {
			wp_delete_attachment( intval($the_doc['id']), true );
			wp_delete_post( intval($the_doc['parent']), true );
		}
	}
	if($blog_id == null) $results = $wpdb->get_results( 'SELECT * FROM wp_options WHERE option_name LIKE "wpdocs%" ', ARRAY_A );
	else $results = $wpdb->get_results( 'SELECT * FROM wp_'.$blog_id.'_options WHERE option_name LIKE "wpdocs%" ', ARRAY_A ); 
	foreach($results as $result) delete_option($result['option_name']);
	$files = glob($upload_dir['basedir'].'/wpdocs/*'); 
	foreach($files as $file) if(is_file($file)) unlink($file);
	if(is_dir($upload_dir['basedir'].'/wpdocs/')) rmdir($upload_dir['basedir'].'/wpdocs/');
	$query = new WP_Query('pagename=wpdocs-library');
	wp_delete_post( $query->post->ID, true );
}


/************************************************ wpdocs-restore.php ************************************************/
function wpdocs_restore_defaults() {
	wpdocs_list_header();
	?>
	<div class="alert alert-info">
		<h3><?php _e('Restore WP Document Library\'s to Defaults','wpdocs'); ?></h3>
		<p><?php _e('This will return WP Docs to its default install state.  This means that all you files, post, and categories will be remove and all setting will return to their default state. <b>Please backup your files before continuing.</b>','wpdocs'); ?></p>
		<div class="wpdocs-clear-both"></div>
		<form enctype="multipart/form-data" method="post" action="" class="wpdocs-setting-form">
			<input type="hidden" name="wpdocs-restore-default" value="clean-up" />
			<input style="margin:15px;" type="button" class="button-primary" onclick="wpdocs_restore_default()" value="<?php _e('Restore To Default','wpdocs') ?>" />
		</form>
	</div>
	<?php
}
/************************************************ wpdocs-ratings.php ************************************************/
function wpdocs_ratings() {
	if(isset($_POST['type']) && $_POST['type'] == 'rating') {
		$wpdocs = get_option('wpdocs-list');
		$wpdocs_show_ratings = get_option( 'wpdocs-show-ratings' );
		$found = false;
		foreach($wpdocs as $index => $the_wpdoc) {
			if(intval($the_wpdoc['id']) == intval($_POST['wpdocs_file_id']) && $found == false) {
				if($wpdocs_show_ratings) {
					$the_rating = wpdocs_get_rating($the_wpdoc);
					if($the_rating['your_rating'] == 0) $text = __("Rate Me!");
					else $text = __("Your Rating");
					echo '<div class="wpdocs-rating-container">';
					echo '<h1>'.$the_wpdoc['name'].'</h1>';
					echo '<div class="wpdocs-ratings-stars" data-my-rating="'.$the_rating['your_rating'].'">';
					echo '<p>'.$text,'</p>';
					for($i=1;$i<=5;$i++) {
						if($the_rating['average'] >= $i) echo '<i class="fa fa-star fa-5x wpdocs-gold  wpdocs-my-rating" id="'.$i.'"></i>';
						elseif(ceil($the_rating['average']) == $i ) echo '<i class="fa fa-star-half-full fa-5x wpdocs-gold wpdocs-my-rating" id="'.$i.'"></i>';
						else echo '<i class="fa fa-star-o fa-5x wpdocs-my-rating" id="'.$i.'"></i>';
					}
					echo '</div>';
					echo '</div>';
				} else _e('Ratings functionality is off.','wpdocs');
				$found = true;
				break;
			}
		}
	}
}
function wpdocs_set_rating($the_id) {
	global $current_user;
	$avg = 0;
	if(isset($_GET['wpdocs-rating'])) $the_rating = $_GET['wpdocs-rating'];
	elseif(isset($_POST['wpdocs-rating'])) $the_rating = intval($_POST['wpdocs-rating']);
	$wpdocs = get_option('wpdocs-list');
	foreach($wpdocs as $index => $doc) if($doc['id'] == $the_id) $doc_index = $index;
	$wpdocs[$doc_index]['ratings'][$current_user->user_email] = $the_rating;
	foreach($wpdocs[$doc_index]['ratings'] as $index => $rating) $avg += $rating;
	$wpdocs[$doc_index]['rating'] = floatval(number_format($avg/count($wpdocs[$doc_index]['ratings']),1));
	
	wpdocs_save_list($wpdocs);
	$_POST['type'] = 'rating';
	wpdocs_ratings();
	
}
/************************************************ wpdocs-rights.php ************************************************/
function wpdocs_contributors_check($contrib) {
	if(!is_array($contrib))  {
		return array();
	} else return $contrib;
}
function wpdocs_add_update_rights($the_wpdoc, $current_cat) {
	global $current_user;
	$is_allowed = wpdocs_check_role_rights($the_wpdoc);
	$the_wpdoc['contributors'] = wpdocs_contributors_check($the_wpdoc['contributors']);
	if($current_user->user_login === $the_wpdoc['owner'] || current_user_can( 'administrator' ) || in_array($current_user->user_login, $the_wpdoc['contributors']) || $is_allowed) {
	?>
	<li role="presentation">
		<a class="add-update-btn" role="menuitem" tabindex="-1" data-toggle="wpdocs-modal" data-target="#wpdocs-add-update" data-wpdocs-id="<?php echo $the_wpdoc['id']; ?>" data-is-admin="<?php echo is_admin(); ?>" data-action-type="update-doc"  data-current-cat="<?php echo $current_cat; ?>" href="">
			<i class="fa fa-file-o" ></i> <?php _e('Manage File','wpdocs'); ?>
		</a>
	</li>
	<?php
	}
}
function wpdocs_check_role_rights($the_wpdoc) {
	global $current_user;
	$is_allowed = false;
	if(is_array($the_wpdoc['contributors'])) {
		foreach($the_wpdoc['contributors'] as $index => $role) {
			if(in_array($role, $current_user->roles)) { $is_allowed = true; break; }
		}
	}
	return $is_allowed;
}
function wpdocs_goto_post_rights($the_wpdoc, $the_wpdoc_permalink) {
	global $current_user;
	$hide_all_post = get_option('wpdocs-hide-all-posts');
	$hide_all_post_non_members = get_option('wpdocs-hide-all-posts-non-members');
	$wpdocs_view_private = get_option('wpdocs-view-private');
	foreach($wpdocs_view_private as $index => $role) {
		$private_viewable = false;
		if($current_user->role[0] == $index) $show_private = $private_viewable = true;
	}
	if($hide_all_post == false && $hide_all_post_non_members == false || is_user_logged_in() == true && $hide_all_post_non_members == true) {
		if($private_viewable == true || $current_user->user_login == $the_wpdoc['owner'] || $current_user->role[0] == 'administrator') {
	?>
	<li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo $the_wpdoc_permalink; ?>" target="_blank"><i class="fa fa-arrow-circle-o-right"></i> <?php _e('Goto Post','wpdocs'); ?></a></li>
	<?php
		}
	}
}
function wpdocs_manage_versions_rights($the_wpdoc, $index, $current_cat) {
	global $current_user;
	$is_allowed = wpdocs_check_role_rights($the_wpdoc);
	$the_wpdoc['contributors'] = wpdocs_contributors_check($the_wpdoc['contributors']);
	if($current_user->user_login === $the_wpdoc['owner'] || current_user_can( 'administrator' ) || in_array($current_user->user_login, $the_wpdoc['contributors']) || $is_allowed) {
	?>
	<li role="presentation"><a role="menuitem" tabindex="-1" href="?page=wpdocs-engine.php&wpdocs-cat=<?php echo $current_cat; ?>&action=wpdocs-versions&wpdocs-index=<?php echo $index; ?>"><i class="fa fa-road"></i> <?php _e('Manage Versions','wpdocs'); ?></a></li>
	<?php
	}
}
function wpdocs_download_rights($the_wpdoc) {
	global $post, $current_user;
	$the_wpdoc_permalink = htmlspecialchars(get_permalink($the_wpdoc['parent']));
	$wpdocs_show_non_members = $the_wpdoc['non_members'];
	if($the_wpdoc['contributors'] != null) {
		foreach($the_wpdoc['contributors'] as $user) {
			$contributor = false;
			if($current_user->user_login == $user) $contributor = true;
		}
	} else $contributor = false;
	if($the_wpdoc['file_status'] != 'hidden' || $contributor == true || $the_wpdoc['owner'] == $current_user->user_login || $current_user->roles[0] == 'administrator') {
		if($wpdocs_show_non_members  == 'off' && is_user_logged_in() == false && is_admin() == false) { ?>
			<li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo wp_login_url($the_wpdoc_permalink); ?>"><i class="fa fa-cloud-download"></i> <?php _e('Download','wpdocs'); ?></a></li><?php
		} elseif($the_wpdoc['non_members'] == 'on' || is_user_logged_in() || is_admin()) { ?>
			<li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo site_url().'/?wpdocs-file='.$the_wpdoc['id'].'&wpdocs-url=false'; ?>"><i class="fa fa-cloud-download"></i> <?php _e('Download','wpdocs'); ?></a></li><?php
		} else { ?>
		<li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo wp_login_url($the_wpdoc_permalink); ?>"><i class="fa fa-cloud-download"></i> <?php _e('Download','wpdocs'); ?></a></li>
		<?php }
	}
}
function wpdocs_preview_rights($the_wpdoc) {
	global $wpdocs_img_types, $current_user;
	$wpdocs_show_preview = get_option('wpdocs-show-preview');
	$wpdocs_show_description = get_option('wpdocs-show-description');
	$wpdocs_show_preview = get_option('wpdocs-show-preview');
	$wpdocs_hide_all_files = get_option( 'wpdocs-hide-all-files' );
	$wpdocs_show_non_members = $the_wpdoc['non_members'];
	$preview_type = 'file-preview';
	
	if(!in_array($the_wpdoc['type'], $wpdocs_img_types) ) $preview_type = 'file-preview';
	else $preview_type = 'img-preview';
	if($the_wpdoc['contributors'] != null) {
		foreach($the_wpdoc['contributors'] as $user) {
			$contributor = false;
			if($current_user->user_login == $user) $contributor = true;
		}
	} else $contributor = false; 
	if($contributor == true || $the_wpdoc['owner'] == $current_user->user_login || $current_user->roles[0] == 'administrator') {
		?>
	<li role="presentation"><a class="<?php echo $preview_type; ?>" role="menuitem" tabindex="-1" data-toggle="wpdocs-modal" data-target="#wpdocs-file-preview" data-wpdocs-id="<?php echo $the_wpdoc['id']; ?>" data-is-admin="<?php echo is_admin(); ?>" href=""><i class="fa fa-search wpdocs-preview-icon" ></i> <?php _e('Preview','wpdocs'); ?></a></li>
	<?php
	} elseif($wpdocs_hide_all_files) {
		//fail
	} else if($wpdocs_show_preview == false) {
		//fail
	} else if( is_user_logged_in() == false && $wpdocs_hide_all_files_non_members) {
		//fail
	} elseif ( $the_wpdoc['file_status'] == 'hidden') {
		//fail
	} else {
		?>
	<li role="presentation"><a class="<?php echo $preview_type; ?>" role="menuitem" tabindex="-1" data-toggle="wpdocs-modal" data-target="#wpdocs-file-preview" data-wpdocs-id="<?php echo $the_wpdoc['id']; ?>" data-is-admin="<?php echo is_admin(); ?>" href=""><i class="fa fa-search wpdocs-preview-icon" ></i> <?php _e('Preview','wpdocs'); ?></a></li>
	<?php
	}
}
function wpdocs_desciption_rights($the_wpdocs) {
	$wpdocs_show_description = get_option('wpdocs-show-description');
	if($wpdocs_show_description == true) {
	?>
	<li role="presentation"><a class="description-preview" role="menuitem" tabindex="-1" href="#" data-toggle="wpdocs-modal" data-target="#wpdocs-description-preview" data-wpdocs-id="<?php echo $the_wpdocs['id']; ?>" data-is-admin="<?php echo is_admin(); ?>" ><i class="fa fa-leaf"></i> <?php _e('Description','wpdocs'); ?></a></li>
	<?php
	}
}
function wpdocs_share_rights($index, $permalink, $download) {
	$wpdocs_show_show = get_option('wpdocs-show-share');
	if($wpdocs_show_show) {
	?>
	<li role="presentation"><a class="sharing-button" role="menuitem" tabindex="-1" href="#" data-toggle="wpdocs-modal" data-doc-index="<?php echo $index; ?>" data-target="#wpdocs-share" data-permalink="<?php echo $permalink;?>" data-download="<?php echo $download; ?>" ><i class="fa fa-share"></i> <?php _e('Share','wpdocs'); ?></a></li>
	<?php
	}
}
function wpdocs_rating_rights($the_wpdoc) {
	if(get_option( 'wpdocs-show-ratings' )) {
	?>
	<li role="presentation"><a class="ratings-button" role="menuitem" tabindex="-1" href="" data-toggle="wpdocs-modal" data-target="#wpdocs-rating" data-wpdocs-id="<?php echo $the_wpdoc['id']; ?>" data-is-admin="<?php echo is_admin(); ?>"><i class="fa fa-star"></i> <?php _e('Rate','wpdocs'); ?></a></li>
	<?php
	}
}
function wpdocs_delete_file_rights($the_wpdoc, $index, $current_cat) {
	global $current_user;
	$is_allowed = wpdocs_check_role_rights($the_wpdoc);
	$the_wpdoc['contributors'] = wpdocs_contributors_check($the_wpdoc['contributors']);
	if($current_user->user_login === $the_wpdoc['owner'] || current_user_can( 'administrator' ) || in_array($current_user->user_login, $the_wpdoc['contributors']) || $is_allowed) {
	?>
	<li role="presentation">
		<a onclick="wpdocs_delete_file('<?php echo $index; ?>','<?php echo $current_cat; ?>','<?php echo $_SESSION['wpdocs-nonce']; ?>');" role="menuitem" tabindex="-1" href="#"><i class="fa fa-times-circle"></i> <?php _e('Delete File','wpdocs'); ?></a>
	</li>
	<?php
	}
}
function wpdocs_refresh_box_view($the_wpdoc, $index) {
	if($current_user->user_login === $the_wpdoc['owner'] || current_user_can( 'administrator' ) || in_array($current_user->user_login, $the_wpdoc['contributors']) || $is_allowed) {
		?>
		<li role="presentation"><a class="box-view-refresh-button" role="menuitem" tabindex="-1" href="#" data-toggle="wpdocs-modal" data-index="<?php echo $index; ?>" data-filename="<?php echo $the_wpdoc['filename']; ?>" ><i class="fa fa-refresh"></i> <?php _e('Refresh Preview and Thumbnail','wpdocs'); ?></a></li>
		<?php
	}
}
/************************************************ wpdocs-social.php ************************************************/

function wpdocs_social($the_wpdoc, $page_type='site') {
	$wpdocs = get_option('wpdocs-list');
	$wpdocs = wpdocs_array_sort();
	if(is_numeric($the_wpdoc)) {
		$the_wpdoc = $wpdocs[$the_wpdoc];
		ob_start();
		$the_permalink = get_permalink($the_wpdoc['parent']);
		if($the_wpdoc['show_social'] ==='on' && get_option('wpdocs-show-social') ) { ?>
		<div class="wpdocs-social-container">
			<div class="wpdocs-tweet"><a href="https://twitter.com/share" class="twitter-share-button" data-url="<?php echo $the_permalink;?>" data-counturl="<?php echo $the_permalink;?>" data-text="<?php echo __('Download','wpdocs').' #'.strtolower(preg_replace('/-| /','_',$the_wpdoc['name'])).' #WPDocumentsLibrary'; ?>" width="50">Tweet</a></div>
			
			<div class="wpdocs-like"><div class="fb-like" data-href="<?php echo $the_permalink;?>" data-layout="button_count" data-action="like" data-show-faces="true" data-share="false"></div></div>
			<div class="wpdocs-linkedin"><script src="//platform.linkedin.com/in.js" type="text/javascript"> lang: en_US</script><script type="IN/Share" data-url="<?php echo $the_permalink;?>" data-counter="right"></script></div>
			<div class="wpdocs-plusone" ><div class="g-plusone" data-size="medium" data-href="<?php echo $the_permalink;?>"></div></div>
		</div>
		<?php
		}
		//wpdocs_social_scripts();
		$the_social = ob_get_clean();
		return $the_social;
	}
	$the_rating = wpdocs_get_rating($the_wpdoc);
	$wpdocs_show_ratings = get_option( 'wpdocs-show-ratings' );
	ob_start();
	if(get_option('wpdocs-hide-all-posts') == false && get_option('wpdocs-hide-all-files') == false || is_user_logged_in() &&  get_option('wpdocs-hide-all-posts-non-members') ) {
		$the_permalink = get_permalink($the_wpdoc['parent']);
		$the_direct_download = get_site_url().'/?wpdocs-file='.$the_wpdoc['id'].'&wpdocs-url=false';
		?>
		<div class="wpdocs-social-container <?php if($page_type == 'dashboard') echo 'wpdocs-socail-dashboard'; ?>"  id="wpdocs-social-<?php echo $the_wpdoc['id']; ?>" >
			<?php if(get_option('wpdocs-show-share')) { ?>
			<div class="wpdocs-share" onclick="wpdocs_share('<?php echo $the_permalink; ?>','<?php echo $the_direct_download; ?>', 'wpdocs-social-<?php echo $the_wpdoc['id']; ?>');"><p><i class="fa fa-share wpdocs-green"></i> <?php _e('Share', 'wpdocs'); ?></p></div>
			<?php } ?>
		<?php if($the_wpdoc['show_social'] ==='on' && get_option('wpdocs-show-social') ) { ?>
			<div class="wpdocs-tweet"><a href="https://twitter.com/share" class="twitter-share-button" data-url="<?php echo $the_permalink;?>" data-counturl="<?php echo $the_permalink;?>" data-text="<?php echo __('Download','wpdocs').' #'.strtolower(preg_replace('/-| /','_',$the_wpdoc['name'])).' #WPDocumentsLibrary'; ?>" width="50">Tweet</a></div>
			<div class="wpdocs-like"><div class="fb-like" data-href="<?php echo $the_permalink;?>" data-layout="button_count" data-action="like" data-show-faces="true" data-share="false"></div></div>
			
			<div class="wpdocs-linkedin"><script src="//platform.linkedin.com/in.js" type="text/javascript"> lang: en_US</script><script type="IN/Share" data-url="<?php echo $the_permalink;?>" data-counter="right"></script></div>
			<div class="wpdocs-plusone" ><div class="g-plusone" data-size="medium" data-href="<?php echo $the_permalink;?>"></div></div>
			
			<?php
		}
		/*
		if($wpdocs_show_ratings && $page_type != 'dashboard') {
			echo '<div class="wpdocs-rating-container-info-large">';
			for($i=1;$i<=5;$i++) {
				if($the_rating['average'] >= $i) echo '<i class="fa fa-star fa-2x wpdocs-gold wpdocs-big-star" id="'.$i.'"></i>';
				elseif(ceil($the_rating['average']) == $i ) echo '<i class="fa fa-star-half-full fa-2x wpdocs-gold wpdocs-big-star" id="'.$i.'"></i>';
				else echo '<i class="fa fa-star-o fa-2x wpdocs-gold wpdocs-big-star" id="'.$i.'"></i>';
			}
			echo '</div>';
		}
		*/
	} else {
		?>
		<div class="wpdocs-social"  >
			<!--<h2><?php _e('This page is hidden to all users accepts admins.','wpdocs'); ?></h2>-->
		<?php
	}
	$the_social = ob_get_clean();
	return $the_social;
}

function wpdocs_social_scripts() {
	?>
<div id="fb-root"></div>
<script>
//FACEBOOK LIKE
(function(d, s, id) {
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) return;
	js = d.createElement(s); js.id = id;
	js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&amp;status=0&amp;appId=12345";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
//TWITTER TWEET
!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');
//GOOGLE +1
(function() {
  var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
  po.src = 'https://apis.google.com/js/plusone.js';
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();
</script>
	<?php
}

/****************************************** wpdocs-server-compatibility.php ******************************************/

function wpdocs_server_compatibility() {
	wpdocs_list_header();
	$success = '<i class="fa fa-check fa-2x text-success"></i>';
	$fail = '<i class="fa fa-times fa-2x text-danger"></i>';
	if(function_exists('imagecreatefromjpeg'))  $php_gd = $success;
	else $php_gd = $fail.' ( '.__('Document thumbnails will not work.').')';
	if(intval(ini_get("max_input_vars")) >= 1000) $input_vars = $success.' ( '.__('The recommended value is 1000, if you have lots of folders we recommend increasing this value.').')';
	else $input_vars = $fail.' ( '.__('The recommended value is 1000, if you have lots of folders you may need to increasing this value.').')';
	if(!method_exists('DateTime', 'createFromFormat')) $datetime_method = $fail.' '.__('( PHP version 5.3 or greater is required to be able to modify dates )', 'wpdocs');
	else $datetime_method = $success;
	if(!class_exists('ZipArchive')) $zip_archive_method = $fail.' '.__('( ZipArchive is used in import, export and batch upload, it needs to be install in order for these to function. )', 'wpdocs');
	else $zip_archive_method = $success;
	if(!class_exists('imagick')) $imagick_method = $fail.' '.__('( Imagick is used to create pdf thumbnails. )', 'wpdocs');
	else $imagick_method = $success;
	if(wpdocs_check_read_write() == false) $upload_dir_read_write = $fail.' '.__('( The WordPress upload directory is not read/writeable.  WP Docs will not work with this access. )', 'wpdocs');
	else $upload_dir_read_write = $success;
?>
<div class="alert alert-info">
	<h2><?php _e('WP Documents Server Compatiability Check', 'wpdocs'); ?></h2>
	<h5><?php _e('Wordpres upload direcotry read/write access', 'wpdocs');?> <?php echo $upload_dir_read_write; ?></h5>
	<h5><?php _e('PHP Image Processing and GD', 'wpdocs');?> <?php echo $php_gd; ?></h5>
	<h5><?php _e('Recommended Maxim PHP Input Vars', 'wpdocs'); ?> = <?php echo  ini_get("max_input_vars"); ?> <?php echo $input_vars; ?></h5>
	<h5><?php _e('Date Time Method Avaiable', 'wpdocs');?> <?php echo $datetime_method; ?></h5>
	<h5><?php _e('ZipArchive Installed', 'wpdocs');?> <?php echo $zip_archive_method; ?></h5>
	<h5><?php _e('Imagick Installed', 'wpdocs');?> <?php echo $imagick_method; ?></h5>
</div>
<?php
}

/********************************************* wpdocs-settings-page.php *********************************************/

function wpdocs_settings($cat) {
	$upload_dir = wp_upload_dir();
	$wpdocs_list_type = get_option( 'wpdocs-list-type' );
	$wpdocs = get_option('wpdocs-list');
	$wpdocs_list_type_dashboard = get_option( 'wpdocs-list-type-dashboard' );
	$wpdocs_hide_all_files = get_option( 'wpdocs-hide-all-files' );
	$wpdocs_show_post_menu = get_option('wpdocs-show-post-menu');
	$wpdocs_hide_all_files_non_members = get_option( 'wpdocs-hide-all-files-non-members' );
	$wpdocs_hide_all_posts = get_option( 'wpdocs-hide-all-posts' );
	$wpdocs_hide_all_posts_default = get_option( 'wpdocs-hide-all-posts-default' );
	$wpdocs_hide_all_posts_non_members = get_option( 'wpdocs-hide-all-posts-non-members' );
	$wpdocs_hide_all_posts_non_members_default = get_option( 'wpdocs-hide-all-posts-non-members-default' );
	$wpdocs_show_downloads = get_option( 'wpdocs-show-downloads' );
	$wpdocs_show_author = get_option( 'wpdocs-show-author' );
	$wpdocs_show_version = get_option( 'wpdocs-show-version' );
	$wpdocs_show_update = get_option( 'wpdocs-show-update' );
	$wpdocs_show_ratings = get_option( 'wpdocs-show-ratings' );
	$wpdocs_show_social = get_option( 'wpdocs-show-social' );
	$wpdocs_show_share = get_option('wpdocs-show-share');
	$wpdocs_show_new_banners = get_option('wpdocs-show-new-banners');
	$wpdocs_time_to_display_banners = strval(get_option('wpdocs-time-to-display-banners'));
	$wpdocs_sort_type = get_option('wpdocs-sort-type');
	$wpdocs_sort_style = get_option('wpdocs-sort-style');
	$wpdocs_default_content = get_option('wpdocs-default-content');
	$wpdocs_show_description = get_option('wpdocs-show-description');
	$wpdocs_show_preview = get_option('wpdocs-show-preview');
	$wpdocs_htaccess = get_option('wpdocs-htaccess');
	$wpdocs_font_size = get_option('wpdocs-font-size');
	$wpdocs_hide_subfolders = get_option('wpdocs-hide-subfolders');
	$wpdocs_disable_bootstrap = get_option('wpdocs-disable-bootstrap');
	$wpdocs_disable_jquery = get_option('wpdocs-disable-jquery');
	$wpdocs_disable_fontawesome = get_option('wpdocs-disable-fontawesome');
	$wpdocs_show_no_file_found = get_option('wpdocs-show-no-file-found');
	$wpdocs_preview_type = get_option('wpdocs-preview-type');
	wpdocs_hide_show_toogle();
	wpdocs_list_header();
	/* FOR TESTING PROPOSES */
	/*
	$cats = json_decode(file_get_contents(dirname(__FILE__).'/cats.txt'), true);
	$list = json_decode(file_get_contents(dirname(__FILE__).'/list.txt'), true);
	update_option('wpdocs-list',$list);
	update_option('wpdocs-cats', $cats);
	*/
?>
<!-- COLOR PICKER 
<input type="text" value="#bada55" class="wpdocs-color-picker" />
<input type="text" value="#bada55" class="wpdocs-color-picker" data-default-color="#effeff" />
-->


<h2><?php _e('Library Settings','wpdocs'); ?></h2>
<form enctype="multipart/form-data" method="post" action="options.php" class="wpdocs-setting-form">
	<?php settings_fields( 'wpdocs-global-settings' ); ?>
	<input type="hidden" name="wpdocs-view-private[administrator]" value="1" />
	<input type="hidden" name="wpdocs-download-color-normal" value="<?php echo get_option( 'wpdocs-download-color-normal' ); ?>" />
	<input type="hidden" name="wpdocs-download-color-hover" value="<?php echo get_option( 'wpdocs-download-color-hover' ); ?>" />
	<input type="hidden" name="wpdocs-hide-all-posts-default" value="<?php echo get_option( 'wpdocs-hide-all-posts-default' ); ?>" />
	<input type="hidden" name="wpdocs-hide-all-posts-non-members-default" value="<?php echo get_option( 'wpdocs-hide-all-posts-non-members-default' ); ?>" />

<table class="table form-table wpdocs-settings-table">
	<tr>
		<th><?php _e('Allowed to Upload'); ?></th>
		<td>
			<?php
			$wp_roles = get_editable_roles();
			if(!is_array(get_option('wpdocs-allow-upload'))) update_option('wpdocs-allow-upload' , array());
			$wpdocs_allow_upload = get_option('wpdocs-allow-upload');
			foreach($wp_roles as $index => $role) {
				$checked = false;
				if($index != 'administrator') {
					$role_object = get_role($index);
					if(array_key_exists($index, $wpdocs_allow_upload)) { $checked = true; $role_object->add_cap( 'wpdocs-dashboard'); }
					else $role_object->remove_cap( 'wpdocs-dashboard');
			?>
			<input type="checkbox" name="wpdocs-allow-upload[<?php echo $index; ?>]" value="1"  <?php checked($checked , 1) ?> /> <span><?php echo ucfirst($index); ?></span><br>
			<?php
				} 
			} 
			?>
		</td>
		<th><?php _e('Disable Thrid Party Includes'); ?>:</th>
		<td>
			<input type="checkbox" name="wpdocs-disable-bootstrap" value="1"  <?php checked(1,$wpdocs_disable_bootstrap) ?> /> <span><?php _e('Bootstrap'); ?></span><br>
			<input type="checkbox" name="wpdocs-disable-jquery" value="1"  <?php checked(1,$wpdocs_disable_jquery) ?> /> <span><?php _e('jQuery'); ?></span><br>
			<input type="checkbox" name="wpdocs-disable-fontawesome" value="1"  <?php checked(1,$wpdocs_disable_fontawesome) ?> /> <span><?php _e('Fontawesome'); ?></span><br>
		</td>
	</tr>
	<tr>
		<th><?php _e('Document Preview Settings', 'wpdocs'); ?></th>
		<td>
			<input type="radio" name="wpdocs-preview-type" value="google" <?php checked('google', $wpdocs_preview_type) ?>> <?php _e('Use Google Docuement Preview', 'wpdocs'); ?><br>			
			<input type="radio" name="wpdocs-preview-type" value="box" <?php checked('box', $wpdocs_preview_type) ?>> <?php _e('Use Box Document Preview', 'wpdocs'); ?><br>
			<label><?php _e('Box View Key','wpdocs'); ?></label><br>
			<input style="width: 100%;" type="text" value="<?php echo get_option('wpdocs-box-view-key'); ?>" name="wpdocs-box-view-key"  placeholder="<?php _e('Enter your key here', 'wpdocs'); ?>"/><br>
			<a href="https://developers.box.com" target="_blank" alt="<?php _e('Sign up for Box Developer site to get your key.', 'wpdocs'); ?>"><?php _e('Sign up for Box Developer site to get your key.', 'wpdocs'); ?></a>
			<p><?php _e('A Box View Key is needed to use Box Document Preview,', 'wpdocs'); ?><br><?php _e('just click on the link above and sign up to create you own key.', 'wpdocs'); ?></p>
			
		</td>
		<th><?php _e('Document List Font Size'); ?>:</th>
		<td>
			<select name="wpdocs-font-size" id="wpdocs-font-size" >
				<option value="3" <?php if($wpdocs_font_size == '3') echo 'selected'; ?>><?php _e('3px','wpdocs'); ?></option>
				<option value="4" <?php if($wpdocs_font_size == '4') echo 'selected'; ?>><?php _e('4px','wpdocs'); ?></option>
				<option value="5" <?php if($wpdocs_font_size == '5') echo 'selected'; ?>><?php _e('5px','wpdocs'); ?></option>
				<option value="6" <?php if($wpdocs_font_size == '6') echo 'selected'; ?>><?php _e('6px','wpdocs'); ?></option>
				<option value="7" <?php if($wpdocs_font_size == '7') echo 'selected'; ?>><?php _e('7px','wpdocs'); ?></option>
				<option value="8" <?php if($wpdocs_font_size == '8') echo 'selected'; ?>><?php _e('8px','wpdocs'); ?></option>
				<option value="9" <?php if($wpdocs_font_size == '9') echo 'selected'; ?>><?php _e('9px','wpdocs'); ?></option>
				<option value="10" <?php if($wpdocs_font_size == '10') echo 'selected'; ?>><?php _e('10px','wpdocs'); ?></option>
				<option value="11" <?php if($wpdocs_font_size == '11') echo 'selected'; ?>><?php _e('11px','wpdocs'); ?></option>
				<option value="12" <?php if($wpdocs_font_size == '12') echo 'selected'; ?>><?php _e('12px','wpdocs'); ?></option>
				<option value="13" <?php if($wpdocs_font_size == '13') echo 'selected'; ?>><?php _e('13px','wpdocs'); ?></option>
				<option value="14" <?php if($wpdocs_font_size == '14') echo 'selected'; ?>><?php _e('14px','wpdocs'); ?></option>
				<option value="15" <?php if($wpdocs_font_size == '15') echo 'selected'; ?>><?php _e('15px','wpdocs'); ?></option>
				<option value="16" <?php if($wpdocs_font_size == '16') echo 'selected'; ?>><?php _e('16px','wpdocs'); ?></option>
				<option value="17" <?php if($wpdocs_font_size == '17') echo 'selected'; ?>><?php _e('17px','wpdocs'); ?></option>
				<option value="18" <?php if($wpdocs_font_size == '18') echo 'selected'; ?>><?php _e('18px','wpdocs'); ?></option>
				<option value="19" <?php if($wpdocs_font_size == '19') echo 'selected'; ?>><?php _e('19px','wpdocs'); ?></option>
				<option value="20" <?php if($wpdocs_font_size == '20') echo 'selected'; ?>><?php _e('20px','wpdocs'); ?></option>
				<option value="21" <?php if($wpdocs_font_size == '21') echo 'selected'; ?>><?php _e('21px','wpdocs'); ?></option>
				<option value="22" <?php if($wpdocs_font_size == '22') echo 'selected'; ?>><?php _e('22px','wpdocs'); ?></option>
				<option value="23" <?php if($wpdocs_font_size == '23') echo 'selected'; ?>><?php _e('23px','wpdocs'); ?></option>
				<option value="24" <?php if($wpdocs_font_size == '24') echo 'selected'; ?>><?php _e('24px','wpdocs'); ?></option>
				<option value="25" <?php if($wpdocs_font_size == '25') echo 'selected'; ?>><?php _e('25px','wpdocs'); ?></option>
				<option value="26" <?php if($wpdocs_font_size == '26') echo 'selected'; ?>><?php _e('26px','wpdocs'); ?></option>
				<option value="27" <?php if($wpdocs_font_size == '27') echo 'selected'; ?>><?php _e('27px','wpdocs'); ?></option>
				<option value="28" <?php if($wpdocs_font_size == '28') echo 'selected'; ?>><?php _e('28px','wpdocs'); ?></option>
				<option value="29" <?php if($wpdocs_font_size == '29') echo 'selected'; ?>><?php _e('29px','wpdocs'); ?></option>
				<option value="30" <?php if($wpdocs_font_size == '30') echo 'selected'; ?>><?php _e('30px','wpdocs'); ?></option>
				<option value="31" <?php if($wpdocs_font_size == '31') echo 'selected'; ?>><?php _e('31px','wpdocs'); ?></option>
				<option value="32" <?php if($wpdocs_font_size == '32') echo 'selected'; ?>><?php _e('32px','wpdocs'); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php _e('Private File Post Viewing','wpdocs'); ?></th>
		<td>
			<?php
			$wp_roles = get_editable_roles(); 
			$wpdocs_view_private = get_option('wpdocs-view-private');
			foreach($wp_roles as $index => $role) {
				if($index != 'administrator') {
					$checked = false;
					if(array_key_exists($index, $wpdocs_view_private)) $checked = true;
					else {
						$role_object = get_role($index);
						$role_object->remove_cap('read_private_posts');
						$role_object->remove_cap('read_private_pages');
					}
			?>
			<input type="checkbox" name="wpdocs-view-private[<?php echo $index; ?>]" value="1"  <?php checked($checked , 1) ?> /> <span><?php echo ucfirst($index); ?></span><br>
			<?php
				}
			}
			?>
		</td>
		<th><?php _e('Style Settings','wpdocs'); ?></th>
		<td>
			<h2><?php _e('Download Button Options','wpdocs'); ?></h2>
			<h4><?php _e('Background Options','wpdocs'); ?></h4>
			<label><?php _e('Background Color','wpdocs'); ?></label>
			<input type="text" value="<?php echo get_option('wpdocs-download-color-normal'); ?>" name="wpdocs-download-color-normal" id="bg-color-wpdocs-picker" data-default-color="#d14836" /><br>
			<label><?php _e('Background Hover Color','wpdocs'); ?></label>
			<input type="text" value="<?php echo get_option('wpdocs-download-color-hover'); ?>" name="wpdocs-download-color-hover" id="bg-hover-color-wpdocs-picker" data-default-color="#c34131" /><br>
			<label><?php _e('Text Color','wpdocs'); ?></label>
			<input type="text" value="<?php echo get_option('wpdocs-download-text-color-normal'); ?>" name="wpdocs-download-text-color-normal" id="bg-text-color-wpdocs-picker" data-default-color="#ffffff" /><br>
			<label><?php _e('Text Hover Color','wpdocs'); ?></label>
			<input type="text" value="<?php echo get_option('wpdocs-download-text-color-hover'); ?>" name="wpdocs-download-text-color-hover" id="bg-text-hover-color-wpdocs-picker" data-default-color="#ffffff" /><br>
			<h4><?php _e('Download Button Preview','wpdocs'); ?></h4>
			<button class="btn btn-primary wpdocs-download-btn-config"><?php echo __('Download','wpdocs');?></button>
		</td>
	</tr>
	<tr>
		<th><?php _e('Displayed File Information','wpdocs'); ?></th>
		<td>
			<input type="checkbox" name="wpdocs-show-downloads" value="1"  <?php checked(1,$wpdocs_show_downloads) ?>/> <?php _e('Downloads','wpdocs'); ?><br>
			<input type="checkbox" name="wpdocs-show-author" value="1"  <?php checked( $wpdocs_show_author, 1) ?>/> <?php _e('Author','wpdocs'); ?><br>
			<input type="checkbox" name="wpdocs-show-version" value="1"  <?php checked( $wpdocs_show_version, 1) ?>/> <?php _e('Version','wpdocs'); ?><br>
			<input type="checkbox" name="wpdocs-show-update" value="1"  <?php checked( $wpdocs_show_update, 1) ?>/> <?php _e('Updated','wpdocs'); ?><br>
			<input type="checkbox" name="wpdocs-show-ratings" value="1"  <?php checked( $wpdocs_show_ratings, 1) ?>/> <?php _e('Ratings','wpdocs'); ?><br>
			<input type="checkbox" name="wpdocs-show-social" value="1"  <?php checked( $wpdocs_show_social, 1) ?>/> <?php _e('Social','wpdocs'); ?><br>
		</td>
	
		<th><?php _e('Hide Things','wpdocs'); ?></th>
		<td>
			<input type="checkbox" name="wpdocs-show-no-file-found" value="1"  <?php checked(1,$wpdocs_show_no_file_found) ?> /> <span><?php _e('Show No Files Found'); ?></span><br>
			<input type="checkbox" id="wpdocs-show-post-menu" name="wpdocs-show-post-menu" value="1"  <?php checked(1,$wpdocs_show_post_menu) ?>/> <?php _e('Show WP Posts Menu Item','wpdocs'); ?><br>
			<input type="checkbox" name="wpdocs-hide-subfolders" value="1"  <?php checked(1,$wpdocs_hide_subfolders) ?>/> <span><?php _e('Hide Sub Directories'); ?></span><br>
			<input type="checkbox" id="wpdocs-hide-all-files" name="wpdocs-hide-all-files" value="1"  <?php checked(1,$wpdocs_hide_all_files) ?>/> <?php _e('All Files','wpdocs'); ?><br>
			<input type="checkbox" id="wpdocs-hide-all-posts" name="wpdocs-hide-all-posts" value="1"  <?php checked(1,$wpdocs_hide_all_posts) ?>/> <?php _e('All Posts','wpdocs'); ?><br>
			<input type="checkbox" id="wpdocs-hide-all-files-non-members" name="wpdocs-hide-all-files-non-members" value="1"  <?php checked(1,$wpdocs_hide_all_files_non_members) ?>/> <?php _e('All Files: (Non Members)','wpdocs'); ?><br>
			<input type="checkbox" id="wpdocs-hide-all-posts-non-members" name="wpdocs-hide-all-posts-non-members" value="1"  <?php checked(1,$wpdocs_hide_all_posts_non_members) ?>/> <?php _e('All Posts: (Non Members)','wpdocs'); ?><br>
			
		</td>
	</tr>
	<tr>
		<th><?php _e('Show Sharing Button/Link','wpdocs'); ?></th>
		<td>
			<input type="checkbox" name="wpdocs-show-share" value="1"  <?php checked( $wpdocs_show_share, 1) ?>/> 
		</td>
		<th><?php _e('Date/Time Format'); ?></th>
		<td>
			<input type="text" name="wpdocs-date-format" value="<?php echo get_option('wpdocs-date-format');?>"  /><br>
			<a href="http://php.net/manual/en/function.date.php" target="_blank" alt="<?php _e('Date/Time Format Reference'); ?>"><?php _e('Date/Time Format Reference'); ?></a>
		</td>
	</tr>
	<tr>
		<th><?php _e('New & Updated Banner','wpdocs'); ?></th>
		<td>
			<input type="checkbox" id="wpdocs-show-new-banners" name="wpdocs-show-new-banners" value="1"  <?php checked(1,$wpdocs_show_new_banners) ?>/> <?php _e('Show New & Updated Banner','wpdocs'); ?><br>
			<input class="width-30" type="text" id="wpdocs-time-to-display-banners" name="wpdocs-time-to-display-banners" value="<?php echo $wpdocs_time_to_display_banners; ?>"/> <?php _e('days - Time to Displayed','wpdocs'); ?><br>
		</td>
		<th><?php _e('Default Sort Options','wpdocs'); ?></th>
		<td>
			<label><?php _e('Order Types:','wpdocs'); ?>
				<select name="wpdocs-sort-type" id="wpdocs-sort-type" >
					<option value="name" <?php if($wpdocs_sort_type == 'name') echo 'selected'; ?>><?php _e('File Name','wpdocs'); ?></option>
					<option value="downloads" <?php if($wpdocs_sort_type == 'downloads') echo 'selected'; ?>><?php _e('Number of Downloads','wpdocs'); ?></option>
					<option value="version" <?php if($wpdocs_sort_type == 'version') echo 'selected'; ?>><?php _e('Version','wpdocs'); ?></option>
					<option value="owner" <?php if($wpdocs_sort_type == 'owner') echo 'selected'; ?>><?php _e('Author','wpdocs'); ?></option>
					<option value="modified" <?php if($wpdocs_sort_type == 'modified') echo 'selected'; ?>><?php _e('Last Updated','wpdocs'); ?></option>
					<option value="rating" <?php if($wpdocs_sort_type == 'rating') echo 'selected'; ?>><?php _e('Rating','wpdocs'); ?></option>
				</select>
			</label><br><br>
			<label><?php _e('Order Style:','wpdocs'); ?>
				<select name="wpdocs-sort-style" id="wpdocs-sort-style" >
					<option value="desc" <?php if($wpdocs_sort_style == 'desc') echo 'selected'; ?>><?php _e('Sort Descending','wpdocs'); ?></option>
					<option value="asc" <?php if($wpdocs_sort_style == 'asc') echo 'selected'; ?>><?php _e('Sort Ascending','wpdocs'); ?></option>
				</select>
			</label><br><br>
			<label><?php _e('Disable User Sort','wpdocs'); ?>
				<input type="checkbox" id="wpdocs-disable-user-sort" name="wpdocs-disable-user-sort" value="1"  <?php checked(1,get_option('wpdocs-disable-user-sort')) ?>/>
			</label>
		</td>
	</tr>
	<tr>
		<th><?php _e('Document Page Settings','wpdocs'); ?></th>
		<td>
			<label><?php _e('Default Content:','wpdocs'); ?>
				<select name="wpdocs-default-content" id="wpdocs-default-content" >
					<option value="description" <?php if($wpdocs_default_content == 'description') echo 'selected'; ?>><?php _e('Description','wpdocs'); ?></option>
					<option value="preview" <?php if($wpdocs_default_content == 'preview') echo 'selected'; ?>><?php _e('Preview','wpdocs'); ?></option>
				</select>
			</label><br><br>
			<input type="checkbox" id="wpdocs-show-description" name="wpdocs-show-description" value="1"  <?php checked(1,$wpdocs_show_description) ?>/> <?php _e('Show Description','wpdocs'); ?><br>
			<input type="checkbox" id="wpdocs-show-preview" name="wpdocs-show-preview" value="1"  <?php checked(1,$wpdocs_show_preview) ?>/> <?php _e('Show Preview','wpdocs'); ?><br>
		</td>
		<th><?php _e('.htaccess File Editor','wpdocs'); ?></th>
		<?php
		
		if(isset($_GET['settings-updated']) && $_GET['page'] == 'wpdocs-engine.php') {
			$upload_dir = wp_upload_dir();
			$htaccess = file_put_contents($upload_dir['basedir'].WPDOCS_DIR.'.htaccess', $wpdocs_htaccess);
		}
		?>
		<td>
				<textarea cols="30" rows="10" name="wpdocs-htaccess"><?php echo $wpdocs_htaccess; ?></textarea>
		</td>
	</tr>
	<!-- ROLES AND PERMISSION SETTINGS 
	<tr>
		<td colspan="3">
			<h2><?php _e('Roles & Permissions','wpdocs'); ?></h2>
			<p>Roles and permissions are cascading meaning if you choose author everyone above and including author will have access to the documents library.</p>
			<label>Add Documents</label>
			<select>
			<?php
				global $wp_roles;
				$roles = $wp_roles->get_names();
				foreach($roles as $key => $name) {
					echo '<option>'.$name.'</option>';
				}
			?>
			</select><br>
			<label>Edit Categories</label>
			<select>
			<?php
				global $wp_roles;
				$roles = $wp_roles->get_names();
				foreach($roles as $key => $name) {
					echo '<option>'.$name.'</option>';
				}
			?>
			</select>
		</td>		
	</tr>
	-->
</table>

<input style="margin:15px;" type="submit" class="button-primary" value="<?php _e('Save Changes','wpdocs') ?>" />
</form>
<?php
}

/************************************************ wpdocs-shortcodes.php ************************************************/

function wpdocs_shortcodes($current_cat) {
	wpdocs_list_header();
	?>
	<div class="alert alert-info">
		<h3>Short Codes</h3>
		<table class="table table-hover" >
			<tr>
				<th><?php _e('Short Codes','wpdocs');?></th>
				<th><?php _e('Description','wpdocs');?></th>
			</tr>
			<tr>
				<td>[wpdocs]</td>
				<td><?php _e('Adds the default WP Docs file list to any page, post or widget.','wpdocs');?></td>
			</tr>
			<tr>
				<td>[wpdocs cat="<?php _e('The Category Name','wpdocs');?>"]</td>
				<td><?php _e('Adds files from  a specific folder of the WP Docs on any page, post or widget.','wpdocs');?></td>
			</tr>
			<tr>
				<td>[wpdocs cat="All Files"]</td>
				<td><?php _e('Adds a list of all files of the WP Docs on any page, post or widget.','wpdocs');?></td>
			</tr>
			<tr>
				<td>[wpdocs header="<?php _e('This text will show up above the documents list.','wpdocs'); ?>"]</td>
				<td><?php _e('Adds a header to the WP Docs on ay page, post or widget.','wpdocs');?></td>
			</tr>
		</table>
	</div>
	<?php
}

/************************************************ wpdocs-sort.php ************************************************/

function wpdocs_sort() {
	if(isset($_POST['type'])) {
		global $wpdocs_img_types;
		$wpdocs_sort_type = get_option('wpdocs-sort-type');
		$wpdocs_sort_style = get_option('wpdocs-sort-style');
		$wpdocs = get_option('wpdocs-list');
		if(isset($_POST['sort_type'])) {
			$sort_type = $_POST['sort_type'];
			setcookie('wpdocs-sort-type', $sort_type,null,'/'); 
		} elseif(isset($_COOKIE['wpdocs-sort-type'])) $sort_type = $_COOKIE['wpdocs-sort-type'];
		else $sort_type = $wpdocs_sort_type;
		if(isset($_POST['sort_range'])) {
			$sort_range = $_POST['sort_range'];
			setcookie('wpdocs-sort-range', $sort_range,null,'/'); 
		} elseif(isset($_COOKIE['wpdocs-sort-range'])) $sort_range = $_COOKIE['wpdocs-sort-range'];
		else $sort_range = $wpdocs_sort_style;
	}
}

/************************************************ wpdocs-settings.php ************************************************/

add_filter( 'wp_default_editor', create_function('', 'return "tinymce";') );

//define('WPDOCS_VERSION', );
$add_error = false;
$wpdocs_img_types = array('jpeg','jpg','png','gif');
$wpdocs_input_text_bg_colors = array('#f1f1f1','#e5eaff','#efffe7','#ffecdc','#ffe9fe','#ff5000','#00ff20', '#f1c40f', '#87CEFA', '#FFD700', '#7FFF00', '#ff0080', '#f5ccff', '#CCEEFF', '#FFFF00', '#00FF00', '#eaeded', '#76d7c4');


function wpdocs_register_settings() {
	//CREATE REPOSITORY DIRECTORY
	$upload_dir = wp_upload_dir();
	$is_read_write = wpdocs_check_read_write();
	if($is_read_write) {
		//BACKUP FILE CREATE
		$backup_list = json_encode(get_option('wpdocs-list'));
		$current_list = get_option('wpdocs-list');
		if($current_list != null || is_array($current_list)) file_put_contents($upload_dir['basedir'].WPDOCS_DIR.'wpdocs-files.bak', $backup_list);
		elseif(file_exists($upload_dir['basedir'].WPDOCS_DIR.'wpdocs-files.bak') && !isset($_GET['restore-default'])) {
			$backup_list = json_decode(file_get_contents($upload_dir['basedir'].WPDOCS_DIR.'wpdocs-files.bak'),true);
			update_option('wpdocs-list', sanitize_wpdocs_data($backup_list), '' , 'no');
		}
		//PATCHES
		if(!isset($_GET['restore-default'])) {
			// PATCHES
			// 3.0 patch 3
			register_setting('wpdocs-patch-vars', 'wpdocs-v3-0-patch-var-3');
			add_option('wpdocs-v3-0-patch-var-3',false);
			if(get_option('wpdocs-v3-0-patch-var-3') == false && is_array(get_option('wpdocs-list'))) {
				$list = get_option('wpdocs-list');
				$cats = get_option('wpdocs-cats');
				delete_option('wpdocs-list');
				delete_option('wpdocs-cats');
				add_option('wpdocs-list', $list, '','no');
				add_option('wpdocs-cats', $cats, '', 'no');
				update_option('wpdocs-v3-0-patch-var-3', true);
			}
			// 3.0 patch 2
			register_setting('wpdocs-patch-vars', 'wpdocs-v3-0-patch-var-2');
			add_option('wpdocs-v3-0-patch-var-2',false);
			if(get_option('wpdocs-v3-0-patch-var-2') == false && is_array(get_option('wpdocs-list'))) {
				$wpdocs = get_option('wpdocs-list');
				global $current_user;
				foreach($wpdocs as $index => $the_wpdoc) {
					$wpdocs[$index]['owner'] = $current_user->user_login;
					$wpdocs[$index]['contributors'] = array();
				}
				update_option('wpdocs-list', sanitize_wpdocs_data($wpdocs), '' , 'no');
				update_option('wpdocs-v3-0-patch-var-2',true);
			}
			// 3.0 patch 1
			//delete_option('wpdocs-v3-0-patch-var-1');
			//delete_option('wpdocs-box-view-updated');
			register_setting('wpdocs-patch-vars', 'wpdocs-v3-0-patch-var-1');
			add_option('wpdocs-v3-0-patch-var-1',false);
			register_setting('wpdocs-patch-vars', 'wpdocs-box-view-updated');
			add_option('wpdocs-box-view-updated',false);
			if(get_option('wpdocs-v3-0-patch-var-1') == false && is_array(get_option('wpdocs-list')) && count(get_option('wpdocs-list')) > 0) {
				add_action( 'admin_head', 'wpdocs_v3_0_patch' );
				function wpdocs_v3_0_patch() {
					$wpdocs = get_option('wpdocs-list');
					//WP DOCS
					wp_register_script( 'wpdocs-script-patch', WPDOCS_URL.'includes/js/wpdocs-script.js');
					wp_enqueue_script('wpdocs-script-patch');
					wp_register_style( 'wpdocs-font-awesome2-style-patch', '//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.css');
					wp_enqueue_style( 'wpdocs-font-awesome2-style-patch' );
					wp_localize_script( 'wpdocs-script-patch', 'wpdocs_patch_js', array('ajaxurl' => admin_url( 'admin-ajax.php' ), 'patch_text_3_0_1' => __('UPDATE HAS STARTER, DO NOT LEAVE THIS PAGE!'),'patch_text_3_0_2' => __('Go grab a coffee this may take a while.'),));
					?>
					<script type="application/x-javascript">
						jQuery(document).ready(function() {
							wpdocs_v3_0_patch(<?php echo count($wpdocs); ?>);
						});
					</script>
					<?php
				}
				wp_deregister_script('wpdocs-script-patch');
				wp_deregister_style('wpdocs-font-awesome2-style-patch');
			} else {
				update_option('wpdocs-v3-0-patch-var-1',true);
				update_option('wpdocs-box-view-updated',true);
			}
			// 2.6.6
			register_setting('wpdocs-patch-vars', 'wpdocs-v2-6-6-patch-var-1');
			add_action('wpdocs-v2-6-6-patch-var-1',false);
			if(get_option('wpdocs-v2-6-6-patch-var-1') == false && is_array(get_option('wpdocs-list'))) {
				$this_query = new WP_Query('category_name=wpdocs-media&posts_per_page=-1');	
				foreach($this_query->posts as $index => $post) set_post_type($post->ID,'wpdocs-posts');
				update_option('wpdocs-v2-6-6-patch-var-1',true);
			}
			// 2.6.7
			register_setting('wpdocs-patch-vars', 'wpdocs-v2-6-7-patch-var-1');
			add_action('wpdocs-v2-6-7-patch-var-1',false);
			if(get_option('wpdocs-v2-6-7-patch-var-1') == false && is_array(get_option('wpdocs-list'))) {
				$wpdocs_cat = get_category_by_slug('wpdocs-media');
				wp_delete_category($wpdocs_cat->cat_ID);
				update_option('wpdocs-v2-6-7-patch-var-1',true);
			} 
			// 2.5
			register_setting('wpdocs-patch-vars', 'wpdocs-v2-5-patch-var-1');
			add_action('wpdocs-v2-5-patch-var-1',false);
			if(get_option('wpdocs-v2-5-patch-var-1') == false && is_array(get_option('wpdocs-list'))) {
				$num_cats = 1;
				foreach( get_option('wpdocs-cats') as $index => $cat ){ $num_cats++;}
				update_option('wpdocs-num-cats', sanitize_wpdocs_data($num_cats));
				add_action( 'admin_notices', 'wpdocs_v2_5_admin_notice_v1' );
				update_option('wpdocs-v2-5-patch-var-1',true);
			} else update_option('wpdocs-v2-5-patch-var-1',true);
			// 2.4
			register_setting('wpdocs-patch-vars', 'wpdocs-v2-4-patch-var-1');
			add_option('wpdocs-v2-4-patch-var-1',false);
			if(get_option('wpdocs-v2-4-patch-var-1') == false  && is_array(get_option('wpdocs-list'))) {
				$wpdocs_cats = get_option('wpdocs-cats');
				$new_wpdocs_cats = array();
				foreach($wpdocs_cats as $index => $cat) array_push($new_wpdocs_cats, array('slug' => $index,'name' => $cat, 'parent' => '', 'children' => array(), 'depth' => 0));
				update_option('wpdocs-cats', sanitize_wpdocs_data($new_wpdocs_cats), '' , 'no');
				update_option('wpdocs-v2-4-patch-var-1', true);
				add_action( 'admin_notices', 'wpdocs_v2_4_admin_notice_v1' );
			} else update_option('wpdocs-v2-4-patch-var-1', true);
			// 2.3
			register_setting('wpdocs-patch-vars', 'wpdocs-v2-3-1-patch-var-1');
			add_option('wpdocs-v2-3-1-patch-var-1',false);
			if(get_option('wpdocs-v2-3-1-patch-var-1') == false  && is_array(get_option('wpdocs-list'))) {
				$htaccess = $upload_dir['basedir'].'/wpdocs/.htaccess';
				$fh = fopen($htaccess, 'w');
				update_option('wpdocs-htaccess', "Deny from all\nOptions +Indexes\nAllow from .google.com");
				$wpdocs_htaccess = get_option('wpdocs-htaccess');
				fwrite($fh, $wpdocs_htaccess);
				fclose($fh);
				chmod($htaccess, 0660);
				update_option('wpdocs-v2-3-1-patch-var-1', true);
				add_action( 'admin_notices', 'wpdocs_v2_2_1_admin_notice_v1' );
			} else update_option('wpdocs-v2-3-1-patch-var-1', true);
			//2.1 
			register_setting('wpdocs-settings', 'wpdocs-2-1-patch-1');
			add_option('wpdocs-2-1-patch-1',false);
			if(get_option('wpdocs-2-1-patch-1') == false  && is_array(get_option('wpdocs-list'))) {
				$wpdocs = get_option('wpdocs-list');
				foreach(get_option('wpdocs-list') as $index => $the_wpdoc) {
					if(!is_array($the_wpdoc['ratings'])) {
						$the_wpdoc['ratings'] = array();
						$the_wpdoc['rating'] = 0;
						$wpdocs[$index] = $the_wpdoc;
					}
					if(!key_exists('rating', $wpdocs)) {
						$the_wpdoc['rating'] = 0;
						$wpdocs[$index] = $the_wpdoc;
					}
				}
				wpdocs_save_list($wpdocs);
				update_option('wpdocs-2-1-patch-1', true);
			} else update_option('wpdocs-2-1-patch-1', true);
		} else {
			update_option('wpdocs-v2-6-6-patch-var-1',true);
			update_option('wpdocs-v2-6-7-patch-var-1',true);
			update_option('wpdocs-v2-5-patch-var-1',true);
			update_option('wpdocs-v2-4-patch-var-1', true);
			update_option('wpdocs-v2-3-1-patch-var-1', true);
			update_option('wpdocs-2-1-patch-1', true);
			@unlink($upload_dir['basedir'].WPDOCS_DIR.'wpdocs-files.bak');
		}
		// Creating File Structure
		if(!is_dir($upload_dir['basedir'].'/wpdocs/') && $upload_dir['error'] === false) mkdir($upload_dir['basedir'].'/wpdocs/');
		elseif(!is_dir($upload_dir['basedir'].'/wpdocs/') && $upload_dir['error'] !== false) wpdocs_errors(__('Unable to create the directory "wpdocs" which is needed by WP Docs. Is its parent directory writable by the server?','wpdocs'),'error');
		//CREATE MDOCS PAGE
		$query = new WP_Query('pagename=wpdocs-library');	
		if(empty($query->posts) && empty($query->queried_object) ) {
			$wpdocs_page = array(
				'post_title' => __('WP Docs','wpdocs'),
				'post_name' => 'wpdocs-library',
				'post_content' => '[wpdocs]',
				'post_status' => 'publish',
				'post_author' => 1,
				'post_type' => 'page',
				'comment_status' => 'closed'
			);
			$wpdocs_post_id = wp_insert_post( $wpdocs_page );	
		}
		//REGISTER SAVED VARIABLES
		wpdocs_init_settings();
		$upload_dir = wp_upload_dir();
		if(!file_exists($upload_dir['basedir'].'/wpdocs/.htaccess')) {
			if(!file_exists($upload_dir['basedir'].'/wpdocs/')) mkdir($upload_dir['basedir'].'/wpdocs/');
			$htaccess = $upload_dir['basedir'].'/wpdocs/.htaccess';
			$fh = fopen($htaccess, 'w');
			$wpdocs_htaccess = get_option('wpdocs-htaccess');
			fwrite($fh, $wpdocs_htaccess);
			fclose($fh);
			chmod($htaccess, 0660);
		}
	} else wpdocs_errors(__('Unable to create the directory "wpdocs" which is needed by WP Docs. Its parent directory is not readable/writable by the server?','wpdocs'),'error');
	
}

function wpdocs_init_settings() {
	add_filter('upload_mimes', 'wpdocs_custom_mime_types');
	$temp_cats = array();
	$temp_cats[0] = array('base_parent' => '', 'index' => 0, 'parent_index' => 0, 'slug' => 'wpdocs', 'name' => 'Documents', 'parent' => '', 'children' => array(), 'depth' => 0,);
	register_setting('wpdocs-settings', 'wpdocs-cats');
	add_option('wpdocs-cats',$temp_cats, '' , 'no');
	if(is_string(get_option('wpdocs-cats'))) update_option('wpdocs-cats', sanitize_wpdocs_data($temp_cats), '' , 'no');
	register_setting('wpdocs-settings', 'wpdocs-list');
	add_option('wpdocs-list',array(), '' , 'no');
	register_setting('wpdocs-settings', 'wpdocs-num-cats');
	add_option('wpdocs-num-cats',1);
	register_setting('wpdocs-settings', 'wpdocs-num-cats');
	add_option('wpdocs-num-cats',1);
	register_setting('wpdocs-settings', 'wpdocs-zip');
	add_option('wpdocs-zip','wpdocs-export.zip');
	register_setting('wpdocs-settings', 'wpdocs-wp-root');
	update_option('wpdocs-wp-root',get_home_path());
	register_setting('wpdocs-top-downloads', 'wpdocs-top-downloads');
	add_option('wpdocs-top-downloads',10);
	register_setting('wpdocs-top-downloads', 'wpdocs-top-rated');
	add_option('wpdocs-top-rated',10);
	register_setting('wpdocs-top-downloads', 'wpdocs-last-updated');
	add_option('wpdocs-last-updated',10);
	//GLOBAL VARIABLES
	register_setting('wpdocs-global-settings', 'wpdocs-list-type');
	update_option('wpdocs-list-type','small');
	register_setting('wpdocs-global-settings', 'wpdocs-list-type-dashboard');
	add_option('wpdocs-list-type-dashboard','small');
	register_setting('wpdocs-global-settings', 'wpdocs-hide-all-files-non-members');
	add_option('wpdocs-hide-all-files-non-members', false);
	register_setting('wpdocs-global-settings', 'wpdocs-hide-all-posts-non-members');
	add_option('wpdocs-hide-all-posts-non-members', false);
	register_setting('wpdocs-global-settings', 'wpdocs-hide-all-posts-non-members-default');
	add_option('wpdocs-hide-all-posts-non-members-default', false);
	register_setting('wpdocs-global-settings', 'wpdocs-hide-all-files');
	add_option('wpdocs-hide-all-files', false);
	register_setting('wpdocs-global-settings', 'wpdocs-hide-all-posts');
	add_option('wpdocs-hide-all-posts', false);
	register_setting('wpdocs-global-settings', 'wpdocs-hide-all-posts-default');
	add_option('wpdocs-hide-all-posts-default', false);
	register_setting('wpdocs-global-settings', 'wpdocs-show-downloads');
	add_option('wpdocs-show-downloads', true);
	register_setting('wpdocs-global-settings', 'wpdocs-show-author');
	add_option('wpdocs-show-author', true);
	register_setting('wpdocs-global-settings', 'wpdocs-show-version');
	add_option('wpdocs-show-version', true);

	register_setting('wpdocs-global-settings', 'wpdocs-show-update');
	add_option('wpdocs-show-update', true);
	register_setting('wpdocs-global-settings', 'wpdocs-show-social');
	add_option('wpdocs-show-social', true);
	register_setting('wpdocs-global-settings', 'wpdocs-show-ratings');
	add_option('wpdocs-show-ratings', true);
	register_setting('wpdocs-global-settings', 'wpdocs-show-share');
	add_option('wpdocs-show-share', true);
	register_setting('wpdocs-global-settings', 'wpdocs-download-color-normal');
	add_option('wpdocs-download-color-normal', '#d14836');
	register_setting('wpdocs-global-settings', 'wpdocs-download-color-hover');
	add_option('wpdocs-download-color-hover', '#c34131');
	register_setting('wpdocs-global-settings', 'wpdocs-download-text-color-normal');
	add_option('wpdocs-download-text-color-normal', '#ffffff');
	register_setting('wpdocs-global-settings', 'wpdocs-download-text-color-hover');
	add_option('wpdocs-download-text-color-hover', '#ffffff');
	register_setting('wpdocs-global-settings', 'wpdocs-show-new-banners');
	add_option('wpdocs-show-new-banners', true);
	register_setting('wpdocs-global-settings', 'wpdocs-time-to-display-banners');
	add_option('wpdocs-time-to-display-banners', 14);
	register_setting('wpdocs-global-settings', 'wpdocs-doc-preview');
	add_option('wpdocs-doc-preview', false);
	register_setting('wpdocs-global-settings', 'wpdocs-sort-type');
	add_option('wpdocs-sort-type','modified');
	register_setting('wpdocs-global-settings', 'wpdocs-sort-style');
	add_option('wpdocs-sort-style','desc');
	register_setting('wpdocs-global-settings', 'wpdocs-default-content');
	add_option('wpdocs-default-content','description');
	register_setting('wpdocs-global-settings', 'wpdocs-show-description');
	add_option('wpdocs-show-description',true);
	register_setting('wpdocs-global-settings', 'wpdocs-show-preview');
	add_option('wpdocs-show-preview', true);
	register_setting('wpdocs-global-settings', 'wpdocs-htaccess');
	add_option('wpdocs-htaccess', "Deny from all\nOptions +Indexes\nAllow from .google.com");
	register_setting('wpdocs-global-settings', 'wpdocs-view-private');
	add_option('wpdocs-view-private', wpdocs_init_view_private());
	register_setting('wpdocs-global-settings', 'wpdocs-date-format');
	add_option('wpdocs-date-format', 'd-m-Y G:i');
	register_setting('wpdocs-global-settings', 'wpdocs-allow-upload');
	add_option('wpdocs-allow-upload', array());
	register_setting('wpdocs-global-settings', 'wpdocs-font-size');
	add_option('wpdocs-font-size', '14');
	register_setting('wpdocs-global-settings', 'wpdocs-hide-subfolders');
	add_option('wpdocs-hide-subfolders', true);
	register_setting('wpdocs-global-settings', 'wpdocs-show-post-menu');
	add_option('wpdocs-show-post-menu', false);
	register_setting('wpdocs-global-settings', 'wpdocs-disable-user-sort');
	add_option('wpdocs-disable-user-sort', false);
	register_setting('wpdocs-global-settings', 'wpdocs-disable-bootstrap');
	add_option('wpdocs-disable-bootstrap', false);
	register_setting('wpdocs-global-settings', 'wpdocs-disable-jquery');
	add_option('wpdocs-disable-jquery', false);
	register_setting('wpdocs-global-settings', 'wpdocs-disable-fontawesome');
	add_option('wpdocs-disable-fontawesome', false);
	register_setting('wpdocs-global-settings', 'wpdocs-show-no-file-found');
	add_option('wpdocs-show-no-file-found', true);
	register_setting('wpdocs-global-settings', 'wpdocs-preview-type');
	add_option('wpdocs-preview-type', 'google');
	register_setting('wpdocs-global-settings', 'wpdocs-preview-type');
	add_option('wpdocs-preview-type', 'google');
	register_setting('wpdocs-global-settings', 'wpdocs-box-view-key');
	add_option('wpdocs-box-view-key', '');
	// GLOBAL SETTING 2
	register_setting('wpdocs-global-settings-2', 'wpdocs-allowed-mime-types');
	add_option('wpdocs-allowed-mime-types', array());
	if(is_string(get_option('wpdocs-allowed-mime-types'))) update_option('wpdocs-allowed-mime-types',array());
	register_setting('wpdocs-global-settings-2', 'wpdocs-removed-mime-types');
	add_option('wpdocs-removed-mime-types', array());
	if(is_string(get_option('wpdocs-removed-mime-types'))) update_option('wpdocs-removed-mime-types',array());
	
	
	
	
	//Update View Private Users
	wpdocs_update_view_private_users();
}
function wpdocs_update_view_private_users() {
	$wpdocs_roles = get_option('wpdocs-view-private');
	
	if(!is_array($wpdocs_roles)) {
		$wpdocs_roles = wpdocs_init_view_private();
		update_option('wpdocs-view-private', sanitize_wpdocs_data(wpdocs_init_view_private()));
	}
	$wp_roles = get_editable_roles();
	foreach($wp_roles as $index => $wp_role) {
		if(isset($wpdocs_roles[$index])) {
			if($wpdocs_roles[$index] == 1) {
				$add_role = get_role( $index );
				$add_role->add_cap( 'read_private_pages' );
				$add_role->add_cap( 'read_private_posts' );
			} else {
				$add_role = get_role( $index );
				$add_role->remove_cap( 'read_private_pages' );
				$add_role->remove_cap( 'read_private_posts' );
			}
		}
	}
}
function wpdocs_init_view_private() {
	$roles = get_editable_roles();
	$view_private = array();
	foreach($roles as $index => $role) {
		$view_private[$index] = $role;
	}
	return $view_private;
}

//ADD CONTENT TO DOCUMENTS PAGE
//[wpdocs]
function wpdocs_shortcode($att, $content=null) { return (is_admin()?wpdocs_the_list($att):wpdocs_the_list2($att)); }
add_shortcode( 'wpdocs', 'wpdocs_shortcode' );
//[wpdocs_post_page]
function wpdocs_post_page_shortcode($att, $content=null) {
	return wpdocs_post_page($att);
}
add_shortcode( 'wpdocs_post_page', 'wpdocs_post_page_shortcode' );
function wpdocs_admin_script() {
	if(isset($_GET['page']) && $_GET['page'] == 'wpdocs-engine.php') {
		//JQUERY
		wp_enqueue_script("jquery");
		//BOOTSTRAP
		if(isset($_GET['page']) && $_GET['page'] == 'wpdocs-engine.php') {
			//wp_register_style( 'wpdocs-bootstrap-style', '//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css');
			//wp_enqueue_style( 'wpdocs-bootstrap-style' );
			wp_register_style( 'wpdocs-bootstrap-style2', '//maxcdn.bootstrapcdn.com/bootswatch/3.2.0/cerulean/bootstrap.min.css');
			wp_enqueue_style( 'wpdocs-bootstrap-style2' );
			wp_enqueue_script( 'wpdocs-bootstrap-script', '//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js' );
		}
		//JQUERY UI
		//wp_register_style( 'wpdocs-jquery-ui-style', '//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css');
		//wp_enqueue_style( 'wpdocs-jquery-ui-style' );
		//wp_enqueue_script( 'wpdocs-jquery-ui-script', '//code.jquery.com/ui/1.10.3/jquery-ui.js' );
		//TWITTER WIDGET
		wp_enqueue_script( 'widgets.js', '//platform.twitter.com/widgets.js' );
		//WP DOCS
		
		wp_register_style( 'wpdocs-admin-style', WPDOCS_URL.'includes/css/wpdocs-admin-style.css');
		wp_enqueue_style( 'wpdocs-admin-style' );
		wp_register_style( 'wpdocs-style', WPDOCS_URL.'includes/css/wpdocs-style.css');
		wp_enqueue_style( 'wpdocs-style' );
		wp_register_script( 'wpdocs-admin-script', WPDOCS_URL.'includes/js/wpdocs-script.js');
		wp_enqueue_script('wpdocs-admin-script');
		if(is_rtl() ) {
			wp_register_style( 'wpdocs-rtl-style', WPDOCS_URL.'includes/css/rtl.css');
			wp_enqueue_style( 'wpdocs-rtl-style' );
		}
		//INLINE STYLE
		wp_enqueue_script('wpdocs-admin-script');
		wpdocs_inline_admin_css('wpdocs-admin-style');
		//FONT-AWESOME STYLE
		wp_register_style( 'wpdocs-font-awesome2-style', '//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.css');
		wp_enqueue_style( 'wpdocs-font-awesome2-style' );
		//WORDPRESS IRIS COLOR PICKER
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wpdocs-color-picker', plugins_url('js/wpdocs-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
		wpdocs_js_handle('wpdocs-admin-script');
	}
}

function wpdocs_script() {
	global $post;
	if(isset($post)) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if(get_post_type($post) == 'wpdocs-posts' || has_shortcode( $post->post_content, 'wpdocs' ) || is_home() || is_plugin_active('so-widgets-bundle/so-widgets-bundle.php')) {
			//JQUERY
			if(get_option('wpdocs-disable-jquery') == false) {
				wp_enqueue_script("jquery");
			}
			//BOOTSTRAP
			if(get_option('wpdocs-disable-bootstrap') == false) {
				$handle = 'bootstrap.min.js';
				$list = 'enqueued';
				if (wp_script_is( $handle, $list )) { return; }
				else {
					wp_register_style( 'wpdocs-bootstrap-style', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css');
					wp_enqueue_style( 'wpdocs-bootstrap-style' );
					wp_register_script( 'bootstrap.min.js', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js');
					wp_enqueue_script( 'bootstrap.min.js' );
				}
			} 
			//WP DOCS 
			wp_register_style( 'wpdocs-style', WPDOCS_URL.'includes/css/wpdocs-style.css');
			wp_enqueue_style( 'wpdocs-style' );
			wp_register_script( 'wpdocs-script', WPDOCS_URL.'includes/js/wpdocs-script.js');
			wp_enqueue_script('wpdocs-script');
			if(is_rtl() ) {
				wp_register_style( 'wpdocs-rtl-style', WPDOCS_URL.'css/rtl.css');
				wp_enqueue_style( 'wpdocs-rtl-style' );
			}
			wpdocs_inline_css('wpdocs-style');
			//FONT-AWESOME STYLE
			if(get_option('wpdocs-disable-fontawesome') == false) {
				wp_register_style( 'wpdocs-font-awesome2-style', '//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.css');
				wp_enqueue_style( 'wpdocs-font-awesome2-style' );
			}
			wpdocs_js_handle('wpdocs-script');
		}
	}
	
	wp_enqueue_script( 'wpdocs-custom-scripts', plugins_url('includes/js/wpdocs-custom-scripts.js', dirname(__FILE__)), array('jquery'), date('Yhis'), true );
	
		
	$wpdocs_array = array(
		'wpdocs-cat' => (isset($_GET['wpdocs-cat'])?$_GET['wpdocs-cat']:''),
		
	);
	wp_localize_script( 'wpdocs-custom-scripts', 'wpdocs', $wpdocs_array );	
}

function wpdocs_inline_css($style_name) {
	$set_inline_style = wpdocs_get_inline_css();
	wp_add_inline_style( $style_name, $set_inline_style );
}
function wpdocs_inline_admin_css($style_name) {
	$set_inline_style = wpdocs_get_inline_admin_css();
	wp_add_inline_style( $style_name, $set_inline_style );
}

function wpdocs_document_ready_wp() {
	global $post;
	if(isset($post)) {
		if(get_post_type($post) == 'wpdocs-posts' || has_shortcode( $post->post_content, 'wpdocs' ) || is_plugin_active('so-widgets-bundle/so-widgets-bundle.php') ) {
	?>
	<script type="application/x-javascript">
			jQuery( document ).ready(function() {
				wpdocs_wp('<?php echo WPDOCS_URL; ?>', '<?php echo ABSPATH; ?>');
			});	
		</script>
	<?php
		}
	}
}
function wpdocs_document_ready_admin() {
	if(!is_network_admin() && isset($_GET['page']) && $_GET['page'] == 'wpdocs-engine.php') {
?>
<script type="text/javascript">
	jQuery( document ).ready(function() {
		wpdocs_admin('<?php echo WPDOCS_URL; ?>', '<?php echo ABSPATH; ?>');
	});	
</script>
<?php
	}
}
function wpdocs_ie_compat() { ?><meta http-equiv="X-UA-Compatible" content="IE=11; IE=10; IE=9; IE=8; IE=7; IE=EDGE" /><?php }
function wpdocs_send_headers() {
	//SET SORT VALUES SITE
	if(isset($_POST['sort_type'])) setcookie('wpdocs-sort-type-site', $_POST['wpdocs-sort-type']); 
	if(isset($_POST['sort_range'])) setcookie('wpdocs-sort-range-site', $_POST['wpdocs-sort-range']);
	//$get_browser = new wpdocs_browser_compatibility();
	//$browser = $get_browser->get_browser();
	//if($browser['name'] == 'Internet Explorer') wpdocs_ie_compat();
}
function wpdocs_send_headers_dashboard() {
	//SET SORT VALUES DASHBOARD
	if(isset($_POST['sort_type'])) setcookie('wpdocs-sort-type-dashboard', $_POST['wpdocs-sort-type']); 
	if(isset($_POST['sort_range'])) setcookie('wpdocs-sort-range-dashboard', $_POST['wpdocs-sort-range']);
	//wpdocs_ie_compat();
}
function wpdocs_v2_2_1_admin_notice_v1() {
    ?>
    <div class="update-nag">
        <p><?php _e('Your WP <b>.htaccess</b> file has been updated to allow google.com access to the system.   This step is necessary to allow documents to be previewed.','wpdocs'); ?></p>
    </div>
    <?php
}
function wpdocs_v2_4_admin_notice_v1() {
    ?>
    <div class="update-nag">
        <p><?php _e('Your WP <b>Categories</b> have been updated to handle subcategories this should not effect your current file system in anyway.  If there is any issues please post a comment in the support forum of this plugin.  It is recommended to re-export your files again due to the new way categories are structured.','wpdocs'); ?></p>
    </div
    ><?php
}
function wpdocs_v2_5_admin_notice_v1() {
    ?>
    <div class="update-nag">
        <p><?php _e('Your WP <b>Categories</b> have been counted to handle subcategories this should not effect your current file system in anyway.  If there is any issues please post a comment in the support forum of this plugin.  It is recommended to re-export your files again due to the new way categories are structured.','wpdocs'); ?></p>
    </div
    ><?php
}

/************************************************ wpdocs-the-list.php ************************************************/
function wpdocs_the_list($att=null) {
	global $current_user, $post;
	ob_start();
	$is_read_write = wpdocs_check_read_write();
	if($is_read_write) {
		$site_url = site_url();
		$upload_dir = wp_upload_dir();	
		$wpdocs = get_option('wpdocs-list');
		$cats =  get_option('wpdocs-cats');
		$current_cat_array = wpdocs_get_current_cat_array($att);
		$current_cat = $current_cat_array['slug'];
		if(isset($att['cat']) && $att['cat'] == 'All Files') { $current_cat = 'all'; wpdocs_list_header(false); }
		else if(!isset($att['cat'])) wpdocs_list_header(true);
		else wpdocs_list_header(false);
		if(is_array($post)) $permalink = get_permalink($post->ID);
		else $permalink = '';
		if(preg_match('/\?page_id=/',$permalink) || preg_match('/\?p=/',$permalink)) {
			$wpdocs_get = $permalink.'&wpdocs-cat=';
		} else $wpdocs_get = $permalink.'?wpdocs-cat=';
		$wpdocs_sort_type = get_option('wpdocs-sort-type');
		$wpdocs_sort_style = get_option('wpdocs-sort-style');
		$disable_user_sort = get_option('wpdocs-disable-user-sort');
		if(isset($_COOKIE['wpdocs-sort-type']) && $disable_user_sort == false) $wpdocs_sort_type = $_COOKIE['wpdocs-sort-type'];
		if(isset($_COOKIE['wpdocs-sort-range']) && $disable_user_sort == false) $wpdocs_sort_style = $_COOKIE['wpdocs-sort-range'];
		if($wpdocs_sort_style == 'desc') $wpdocs_sort_style_icon = ' <i class="fa fa-chevron-down"></i>';		
		else $wpdocs_sort_style_icon = ' <i class="fa fa-chevron-up"></i>';
	?>
	<div class="wpdocs-container">	
		<?php if(isset($att['header'])) echo '<p>'.__($att['header']).'</p>'; ?>
		<?php
		//wpdocs_load_modals();
		$wpdocs = wpdocs_array_sort();
		$count = 0;
		$num_tds = 1;
		if(get_option('wpdocs-list-type') == 'small') echo '<table class="table table-hover table-condensed wpdocs-list-table">';
		?>
		<tr class="hidden-sm hidden-xs">
		<th class="wpdocs-sort-option" data-disable-user-sort="<?php echo $disable_user_sort; ?>" data-sort-type="name" data-current-cat="<?php echo $current_cat; ?>" data-permalink="<?php echo $permalink; ?>"><?php _e('Name','wpdocs'); ?><?php if($wpdocs_sort_type == 'name') echo $wpdocs_sort_style_icon; ?></th>
		<?php if(get_option('wpdocs-show-downloads')) { $num_tds++; ?>
		<th class="wpdocs-sort-option" data-disable-user-sort="<?php echo $disable_user_sort; ?>" data-sort-type="downloads" data-current-cat="<?php echo $current_cat; ?>" data-permalink="<?php echo $permalink; ?>"><?php _e('Downloads','wpdocs'); ?><?php if($wpdocs_sort_type == 'downloads') echo $wpdocs_sort_style_icon; ?></th><?php } ?>
		<?php if(get_option('wpdocs-show-version')) { $num_tds++; ?>
		<th class="hide wpdocs-sort-option" data-disable-user-sort="<?php echo $disable_user_sort; ?>" data-sort-type="version" data-current-cat="<?php echo $current_cat; ?>" data-permalink="<?php echo $permalink; ?>"><?php _e('Version','wpdocs'); ?><?php if($wpdocs_sort_type == 'version') echo $wpdocs_sort_style_icon; ?></th><?php } ?>
		<?php if(get_option('wpdocs-show-author')) { $num_tds++; ?>
		<th class="hide wpdocs-sort-option" data-disable-user-sort="<?php echo $disable_user_sort; ?>" data-sort-type="owner" data-current-cat="<?php echo $current_cat; ?>" data-permalink="<?php echo $permalink; ?>"><?php _e('Owner','wpdocs'); ?><?php if($wpdocs_sort_type == 'owner') echo $wpdocs_sort_style_icon; ?></th><?php } ?>
		<?php if(get_option('wpdocs-show-update')) { $num_tds++; ?>
		<th class="wpdocs-sort-option" data-disable-user-sort="<?php echo $disable_user_sort; ?>" data-sort-type="modified" data-current-cat="<?php echo $current_cat; ?>" data-permalink="<?php echo $permalink; ?>"><?php _e('Last Modified','wpdocs'); ?><?php if($wpdocs_sort_type == 'modified') echo $wpdocs_sort_style_icon; ?></th><?php } ?>
		<?php if(get_option('wpdocs-show-ratings')) { $num_tds++; ?>
		<th class="wpdocs-sort-option" data-disable-user-sort="<?php echo $disable_user_sort; ?>" data-sort-type="rating" data-current-cat="<?php echo $current_cat; ?>" data-permalink="<?php echo $permalink; ?>"><?php _e('Rating','wpdocs'); ?><?php if($wpdocs_sort_type == 'rating') echo $wpdocs_sort_style_icon; ?></th><?php } ?>
		</tr>
		<?php
		// SUB CATEGORIES
		//$current_cat_array = wpdocs_get_the_cat($current_cat);
		//$parent_cat_array = wpdocs_get_the_cat($current_cat_array['parent']);
		$hide_sub_folders = get_option('wpdocs-hide-subfolders');
		if(!isset($att['cat'])) $num_cols = (is_admin()?wpdocs_get_subcats($current_cat_array, 'null'):wpdocs_get_subcats2($current_cat_array));
		elseif(isset($current_cat_array['children']) && $hide_sub_folders == false && isset($att['cat'])) $num_cols = (is_admin()?wpdocs_get_subcats($current_cat_array, $att['cat']):wpdocs_get_subcats2($current_cat_array));
		foreach($wpdocs as $index => $the_wpdoc) {
			if($the_wpdoc['cat'] == $current_cat || $current_cat == 'all') {
				$is_allowed = wpdocs_check_role_rights($the_wpdoc);
				if($the_wpdoc['file_status'] == 'public' || is_admin() && $the_wpdoc['owner'] == $current_user->user_login ||  in_array($current_user->user_login, $the_wpdoc['contributors']) || $is_allowed || $current_user->roles[0] == 'administrator') {
					$count ++;
					$wpdocs_post = get_post($the_wpdoc['parent']);
					//$wpdocs_desc = apply_filters('the_content', $wpdocs_post->post_excerpt);
					
					if(get_option('wpdocs-list-type') == 'small') {
						
						if(is_admin()){
						wpdocs_file_info_small($the_wpdoc, $index, $current_cat); 
						}else{
						wpdocs_file_info_small2($the_wpdoc, $index, $current_cat); 
						}
					} else {
						$user_logged_in = is_user_logged_in();
						$wpdocs_hide_all_files = get_option( 'wpdocs-hide-all-files' );
						$wpdocs_hide_all_files_non_members = get_option( 'wpdocs-hide-all-files-non-members' );
						if($wpdocs_hide_all_files_non_members && $user_logged_in == false) $show_files = false;
						elseif($wpdocs_hide_all_files == false ) $show_files = true;
						else $show_files = false;
						if( $show_files) {
							?>
							<div class="wpdocs-post">
								<?php wpdocs_file_info_large($the_wpdoc, $index, $current_cat); ?>
								<div class="wpdocs-clear-both"></div>
								<?php wpdocs_social($the_wpdoc); ?>
							</div>
							<div class="wpdocs-clear-both"></div>
							<?php wpdocs_des_preview_tabs($the_wpdoc); ?>
							<div class="wpdocs-clear-both"></div>
							</div>
							<?php
						}
					}
				}
			} 
		}
		if($count == 0 && get_option('wpdocs-show-no-file-found')) {
			?><tr><td colspan="<?php echo $num_tds; ?>"><p class="wpdocs-nofiles" ><?php _e('No files are found in this directory.','wpdocs'); ?></p></td></tr><?php
		}
		if(get_option('wpdocs-list-type') == 'small') echo '</table></div>';
	} else wpdocs_errors(__('Unable to create the directory "wpdocs" which is needed by WP Docs. Its parent directory is not writable by the server?','wpdocs'),'error');
	//echo '</div>';
	$the_list = ob_get_clean();
	return $the_list;
}

function ksort_recursive(&$array)
{
    if (is_array($array)) {
        ksort($array);
        array_walk($array, 'ksort_recursive');
    }
}

function wpdocs_the_list2($att=null) {
	global $current_user, $post;
	ob_start();
?>
	<div class="wpdocs-library">
<?php	
	$is_read_write = wpdocs_check_read_write();
	if($is_read_write) {
		$site_url = site_url();
		$upload_dir = wp_upload_dir();	
		$wpdocs = get_option('wpdocs-list');
		$cats =  get_option('wpdocs-cats');
		$current_cat_array = wpdocs_get_current_cat_array($att);
		$current_cat = $current_cat_array['slug'];
		if(isset($att['cat']) && $att['cat'] == 'All Files') { $current_cat = 'all'; wpdocs_list_header(false); }
		else if(!isset($att['cat'])) wpdocs_list_header(true);
		else wpdocs_list_header(false);
		if(is_array($post)) $permalink = get_permalink($post->ID);
		else $permalink = '';
		if(preg_match('/\?page_id=/',$permalink) || preg_match('/\?p=/',$permalink)) {
			$wpdocs_get = $permalink.'&wpdocs-cat=';
		} else $wpdocs_get = $permalink.'?wpdocs-cat=';
		$wpdocs_sort_type = get_option('wpdocs-sort-type');
		$wpdocs_sort_style = get_option('wpdocs-sort-style');
		$disable_user_sort = get_option('wpdocs-disable-user-sort');
		if(isset($_COOKIE['wpdocs-sort-type']) && $disable_user_sort == false) $wpdocs_sort_type = $_COOKIE['wpdocs-sort-type'];
		if(isset($_COOKIE['wpdocs-sort-range']) && $disable_user_sort == false) $wpdocs_sort_style = $_COOKIE['wpdocs-sort-range'];
		if($wpdocs_sort_style == 'desc') $wpdocs_sort_style_icon = ' <i class="fa fa-chevron-down"></i>';		
		else $wpdocs_sort_style_icon = ' <i class="fa fa-chevron-up"></i>';


		//wpdocs_load_modals();
		$wpdocs = wpdocs_array_sort();
		$count = 0;
		$num_tds = 1;
	
		// SUB CATEGORIES
		//$current_cat_array = wpdocs_get_the_cat($current_cat);
		//$parent_cat_array = wpdocs_get_the_cat($current_cat_array['parent']);
		$hide_sub_folders = get_option('wpdocs-hide-subfolders');
		if(!isset($att['cat'])) $num_cols = (is_admin()?wpdocs_get_subcats($current_cat_array, 'null'):wpdocs_get_subcats2($current_cat_array));
		elseif(isset($current_cat_array['children']) && $hide_sub_folders == false && isset($att['cat'])) $num_cols = (is_admin()?wpdocs_get_subcats($current_cat_array, $att['cat']):wpdocs_get_subcats2($current_cat_array));
		
		global $wpdocs_select, $wpdocs_list;
		ksort_recursive($wpdocs_select);
		ksort_recursive($wpdocs_list);
		//pree($wpdocs_select);pree($wpdocs_list);exit;
		if(!empty($wpdocs_select)){
			foreach($wpdocs_select as $select){
				if(!empty($select)){
					foreach($select as $select1){
						echo implode('', $select1);
					}
				}
			}
		}
		//pree($wpdocs_list);
		//pree(get_option('wpdocs-list'));
		if(!empty($wpdocs_list)){
			echo '<div class="wpdocs-items">';
				foreach($wpdocs_list as $list){
					echo implode('', $list);
				}

			echo '</div>';
		}		

		//pree($num_cols);exit;
		//$wpdocs = wpdocs_array_sort();
		//$current_cat = $current_cat_array['slug'];
		//wpdoc_iteration($wpdocs, $current_cat);
		
		if($count == 0 && get_option('wpdocs-show-no-file-found')) {
			?>
            <?php
		}
		
	} else wpdocs_errors(__('Unable to create the directory "wpdocs" which is needed by WP Docs. Its parent directory is not writable by the server?','wpdocs'),'error');
?>
</div>	
	<?php
	
	$the_list = ob_get_clean();
	return $the_list;
}

function wpdoc_iteration($wpdocs, $current_cat){
		ob_start();	
		//pree($wpdocs);
		foreach($wpdocs as $index => $the_wpdoc) {
					if($the_wpdoc['cat'] == $current_cat || $current_cat == 'all') {
						$is_allowed = wpdocs_check_role_rights($the_wpdoc);
						if($the_wpdoc['file_status'] == 'public' || is_admin() && $the_wpdoc['owner'] == $current_user->user_login ||  in_array($current_user->user_login, $the_wpdoc['contributors']) || $is_allowed || $current_user->roles[0] == 'administrator') {
							$count ++;
							$wpdocs_post = get_post($the_wpdoc['parent']);
							//$wpdocs_desc = apply_filters('the_content', $wpdocs_post->post_excerpt);
							
							if(get_option('wpdocs-list-type') == 'small') {
								if(is_admin()){
									
									wpdocs_file_info_small($the_wpdoc, $index, $current_cat); 
								}else{
									wpdocs_file_info_small2($the_wpdoc, $index, $current_cat); 
								}
							} else {
								$user_logged_in = is_user_logged_in();
								$wpdocs_hide_all_files = get_option( 'wpdocs-hide-all-files' );
								$wpdocs_hide_all_files_non_members = get_option( 'wpdocs-hide-all-files-non-members' );
								if($wpdocs_hide_all_files_non_members && $user_logged_in == false) $show_files = false;
								elseif($wpdocs_hide_all_files == false ) $show_files = true;
								else $show_files = false;
						
						
							}
						}
					} 
		}	
		
		$return = ob_get_clean();
		return $return;
}
/************************************************ wpdocs-update-mime.php ************************************************/
function wpdocs_update_mime() {
	if(isset($_POST['type'])  && $_POST['type'] == 'add-mime') {
		$file_extension = $_POST['file_extension'];
		$mime_type = $_POST['mime_type'];
		$wpdocs_allowed_mime_types = get_option('wpdocs-allowed-mime-types');
		$wpdocs_allowed_mime_types[$file_extension] = $mime_type;
		update_option('wpdocs-allowed-mime-types', sanitize_wpdocs_data($wpdocs_allowed_mime_types));
		$wpdocs_removed_mime_types = get_option('wpdocs-removed-mime-types');
		unset($wpdocs_removed_mime_types[$file_extension]);
		update_option('wpdocs-removed-mime-types', sanitize_wpdocs_data($wpdocs_removed_mime_types));
		add_filter('upload_mimes', 'wpdocs_custom_mime_types');
		echo '<tr data-file-type="'.$file_extension.'" ><td>'.$file_extension.'</td><td>'.$mime_type.'</td>';
		echo '<td><a href="#" class="wpdocs-remove-mime">remove</a></td></tr>';
	} elseif(isset($_POST['type'])  && $_POST['type'] == 'remove-mime') {
		$file_extension = $_POST['file_extension'];
		$wpdocs_removed_mime_types = get_option('wpdocs-removed-mime-types');
		$wpdocs_removed_mime_types[strval($file_extension)] = $file_extension;
		update_option('wpdocs-removed-mime-types', sanitize_wpdocs_data($wpdocs_removed_mime_types));
	} elseif(isset($_POST['type'])  && $_POST['type'] == 'restore-mime') {
		update_option('wpdocs-allowed-mime-types', array());
		update_option('wpdocs-removed-mime-types', array());
		add_filter('upload_mimes', 'wpdocs_custom_mime_types');
		$mimes = get_allowed_mime_types();
		?>
		<tr>
			<th><?php _e('Extension','wpdocs'); ?></th>
			<th><?php _e('Mime Type','wpdocs'); ?></th>
			<th><?php _e('Options','wpdocs'); ?></th>
		</tr>
		<?php
		foreach($mimes as $index => $mime) {
			echo '<tr data-file-type="'.$index.'" ><td>'.$index.'</td><td>'.$mime.'</td>';
			echo '<td><a href="#" class="wpdocs-remove-mime">remove</a></td>';
			echo '</tr>';
		}
		?>
		<tr class="wpdocs-mime-submit">
			<td><input type="text" placeholder="Enter File Type..." name="wpdocs-file-extension" value=""/></td>
			<td><input type="text" placeholder="Enter Mime Type..." name="wpdocs-mime-type" value=""/></td>
			<td><a href="#" id="wpdocs-add-mime"><?php _e('add','wpdocs'); ?></a></td>
		</tr>
		<?php
	}
}

/************************************************ wpdocs-upload.php ************************************************/
function wpdocs_file_upload() {
	//exit;
	global $current_user, $wp_filetype;
	$wpdocs = wpdocs_array_sort();
	$wpdocs_cats = get_option('wpdocs-cats');
	foreach($wpdocs as $index => $doc) {
		if($_POST['wpdocs-index'] == $doc['id']) {
			$wpdocs_index = $index; break;
		}
	}
	$_FILES['wpdocs']['name'] = wpdocs_filenames_to_latin($_FILES['wpdocs']['name']);
	$wpdocs_filename = $_FILES['wpdocs']['name'];
	$wpdocs_name = $_POST['wpdocs-name'];
	$wpdocs_fle_type = substr(strrchr($wpdocs_filename, '.'), 1 );
	$wpdocs_fle_size = $_FILES["wpdocs"]["size"];
	$wpdocs_type = $_POST['wpdocs-type'];
	$wpdocs_cat = $_POST['wpdocs-cat'];
	$wpdocs_desc = $_POST['wpdocs-desc'];
	$wpdocs_version = $_POST['wpdocs-version'];
	$wpdocs_social = $_POST['wpdocs-social'];
	$wpdocs_non_members = @$_POST['wpdocs-non-members'];
	$wpdocs_file_status = $_POST['wpdocs-file-status'];
	$wpdocs_doc_preview = @$_POST['wpdocs-doc-preview'];
	if(!isset($_POST['wpdocs-contributors']))  $_POST['wpdocs-contributors'] = array();
	if(isset($_POST['wpdocs-post-status'])) $wpdocs_post_status = $_POST['wpdocs-post-status'];
	else $wpdocs_post_status = $_POST['wpdocs-post-status-sys'];
	$date_format = get_option('wpdocs-date-format');
	
	//pree($_POST);exit;
	
	if(method_exists('DateTime', 'createFromFormat')) {
		$dtime = DateTime::createFromFormat($date_format, $_POST['wpdocs-last-modified']);
		if($dtime != false) {
			$wpdocs_last_modified = $dtime->getTimestamp();
		} else $wpdocs_last_modified =time()+WPDOCS_TIME_OFFSET;
	} else $wpdocs_last_modified =time()+WPDOCS_TIME_OFFSET;
	$upload_dir = wp_upload_dir();	
	$wpdocs_user = $current_user->user_login;
	if($wpdocs_file_status == 'hidden') $wpdocs_post_status_sys = 'draft';
	else $wpdocs_post_status_sys = $wpdocs_post_status;
	$the_post_status = $wpdocs_post_status_sys;
	$_FILES['wpdocs']['name'] = preg_replace('/[^A-Za-z0-9\-._]/', '', $_FILES['wpdocs']['name']);
	$_FILES['wpdocs']['name'] = str_replace(' ','', $_FILES['wpdocs']['name']);
	$_FILES['wpdocs']['post_status'] = $the_post_status;
	//MDOCS FILE TYPE VERIFICATION	
	$mimes = get_allowed_mime_types();
	$valid_mime_type = false;
	foreach ($mimes as $type => $mime) {
		$file_type = wp_check_filetype($_FILES['wpdocs']['name']);
		$found_ext = strpos($type,$file_type['ext']);
		if($found_ext !== false) {
			$valid_mime_type = true;
			break;
		}
	}
	//MDOCS NONCE VERIFICATION
	if ($_REQUEST['wpdocs-nonce'] == WPDOCS_NONCE ) {
		if(!empty($wpdocs_cats)) {
			if($wpdocs_type == 'wpdocs-add') {
				if($valid_mime_type) {
		$_FILES['wpdocs']['post-status'] = $wpdocs_post_status;
		$upload = wpdocs_process_file($_FILES['wpdocs']);
		if($wpdocs_version == '') $wpdocs_version = '1.0';
		//elseif(!is_numeric($wpdocs_version)) $wpdocs_version = '1.0';
		if(!isset($upload['error'])) {
			if(get_option('wpdocs-preview-type') == 'box' && get_option('wpdocs-box-view-key') != '') {
				$boxview = new wpdocs_box_view();
				$boxview_file = $boxview->uploadFile(get_site_url().'/?wpdocs-file='.$upload['attachment_id'].'&wpdocs-url=false&is-box-view=true', $upload['filename']);
			} else $boxview_file['id'] = 0;
			//if(!is_array($_POST['wpdocs-contributors']))  $_POST['wpdocs-contributors'] = array();
			array_push($wpdocs, array(
				'id'=>(string)$upload['attachment_id'],
				'parent'=>(string)$upload['parent_id'],
				'filename'=>$upload['filename'],
				'name'=>$upload['name'],
				'desc'=>$upload['desc'],
				'type'=>$wpdocs_fle_type,
				'cat'=>$wpdocs_cat,
				'owner'=>$wpdocs_user,
				'contributors'=>$_POST['wpdocs-contributors'],
				'size'=>(string)$wpdocs_fle_size,
				'modified'=>(string)$wpdocs_last_modified,
				'version'=>(string)$wpdocs_version,
				'show_social'=>(string)$wpdocs_social,
				'non_members'=> (string)$wpdocs_non_members,
				'file_status'=>(string)$wpdocs_file_status,
				'post_status'=> (string)$wpdocs_post_status,
				'post_status_sys'=> (string)$wpdocs_post_status_sys,
				'doc_preview'=>(string)$wpdocs_doc_preview,
				'downloads'=>(string)0,
				'archived'=>array(),
				'ratings'=>array(),
				'rating'=>0,
				'box-view-id' => $boxview_file['id'],
			));
			$wpdocs = wpdocs_array_sort($wpdocs);
			wpdocs_save_list($wpdocs);
		} else wpdocs_errors($upload['error'],'error');
	} else wpdocs_errors(WPDOCS_ERROR_2 , 'error');
			} elseif($wpdocs_type == 'wpdocs-update') {
				if($_FILES['wpdocs']['name'] != '') {
					if($valid_mime_type) {
						$old_doc = $wpdocs[$wpdocs_index];
						$old_doc_name = $old_doc['filename'].'-v'.preg_replace('/ /', '',$old_doc['version']);
						@rename($upload_dir['basedir'].'/wpdocs/'.$old_doc['filename'],$upload_dir['basedir'].'/wpdocs/'.$old_doc_name);
						$name = substr($old_doc['filename'], 0, strrpos($old_doc['filename'], '.') );
						$filename = $name.'.'.$wpdocs_fle_type;
						$_FILES['wpdocs']['name'] = $filename;
						$_FILES['wpdocs']['parent'] = $old_doc['parent'];
						$_FILES['wpdocs']['id'] = $old_doc['id'];
						$_FILES['wpdocs']['post-status'] = $wpdocs_post_status;
						$upload = wpdocs_process_file($_FILES['wpdocs']);
						if(!isset($upload['error'])) {
							if(get_option('wpdocs-preview-type') == 'box' && get_option('wpdocs-box-view-key') != '') {
								$boxview = new wpdocs_box_view();
								$boxview_file = $boxview->uploadFile(get_site_url().'/?wpdocs-file='.$old_doc['id'].'&wpdocs-url=false&is-box-view=true', $filename);
							} else $boxview_file['id'] = 0;
							if($wpdocs_version == '') $wpdocs_version = '1.0';
							else if($wpdocs_version == $wpdocs[$wpdocs_index]['version']) $wpdocs_version = $wpdocs[$wpdocs_index]['version'].'.1';
							$wpdocs[$wpdocs_index]['filename'] = $upload['filename'];
							$wpdocs[$wpdocs_index]['name'] = $upload['name'];
							$wpdocs[$wpdocs_index]['desc'] = $upload['desc'];
							$wpdocs[$wpdocs_index]['version'] = (string)$wpdocs_version;
							$wpdocs[$wpdocs_index]['type'] = (string)$wpdocs_fle_type;
							$wpdocs[$wpdocs_index]['cat'] = $wpdocs_cat;
							$wpdocs[$wpdocs_index]['owner'] = $wpdocs[$wpdocs_index]['owner'];
							$wpdocs[$wpdocs_index]['contributors'] = $_POST['wpdocs-contributors'];
							$wpdocs[$wpdocs_index]['size'] = (string)$wpdocs_fle_size;
							$wpdocs[$wpdocs_index]['modified'] = (string)$wpdocs_last_modified;
							$wpdocs[$wpdocs_index]['show_social'] =(string)$wpdocs_social;
							$wpdocs[$wpdocs_index]['non_members'] =(string)$wpdocs_non_members;
							$wpdocs[$wpdocs_index]['file_status'] =(string)$wpdocs_file_status;
							$wpdocs[$wpdocs_index]['post_status'] =(string)$wpdocs_post_status;
							$wpdocs[$wpdocs_index]['post_status_sys'] =(string)$wpdocs_post_status_sys;
							$wpdocs[$wpdocs_index]['doc_preview'] =(string)$wpdocs_doc_preview;
							$wpdocs[$wpdocs_index]['box-view-id'] = $boxview_file['id'];
							array_push($wpdocs[$wpdocs_index]['archived'], $old_doc_name);
							$wpdocs = wpdocs_array_sort($wpdocs);
							wpdocs_save_list($wpdocs);
						} else wpdocs_errors($upload['error'],'error');
					} else wpdocs_errors(WPDOCS_ERROR_2 , 'error');
				} else {
					//if($wpdocs_desc == '') $desc = WPDOCS_DEFAULT_DESC;
					//else
					$desc = $wpdocs_desc;
					if($wpdocs_name == '') $wpdocs[$wpdocs_index]['name'] = $_POST['wpdocs-pname'];
					else $wpdocs[$wpdocs_index]['name'] = $wpdocs_name;
					if($wpdocs_version == '') $wpdocs_version = $wpdocs[$wpdocs_index]['version'];
					$wpdocs[$wpdocs_index]['desc'] = $desc;
					$wpdocs[$wpdocs_index]['version'] = (string)$wpdocs_version;
					$wpdocs[$wpdocs_index]['cat'] = $wpdocs_cat;
					$wpdocs[$wpdocs_index]['owner'] = $wpdocs[$wpdocs_index]['owner'];
					$wpdocs[$wpdocs_index]['contributors'] = $_POST['wpdocs-contributors'];
					$wpdocs[$wpdocs_index]['modified'] = (string)$wpdocs_last_modified;
					$wpdocs[$wpdocs_index]['show_social'] =(string)$wpdocs_social;
					$wpdocs[$wpdocs_index]['non_members'] =(string)$wpdocs_non_members;
					$wpdocs[$wpdocs_index]['file_status'] =(string)$wpdocs_file_status;
					$wpdocs[$wpdocs_index]['post_status'] =(string)$wpdocs_post_status;
					$wpdocs[$wpdocs_index]['post_status_sys'] =(string)$wpdocs_post_status_sys;
					$wpdocs[$wpdocs_index]['doc_preview'] =(string)$wpdocs_doc_preview;
					$wpdocs_post = array(
						'ID' => $wpdocs[$wpdocs_index]['parent'],
						'post_title' => $wpdocs[$wpdocs_index]['name'],
						'post_content' => '[wpdocs_post_page]',
						'post_status' => $the_post_status,
						'post_excerpt' => $desc,
						'post_date' => gmdate('Y-m-d H:i:s',$wpdocs_last_modified)
					);
					$wpdocs_post_id = wp_update_post( $wpdocs_post );
					//wp_set_post_tags( $wpdocs_post_id, $wpdocs_name.', '.$wpdocs_cat.', WP Docs, '.$wp_filetype['type'] );
					wp_set_post_tags($wpdocs_post_id, $_POST['wpdocs-tags']);
					$wpdocs_attachment = array(
						'ID' => $wpdocs[$wpdocs_index]['id'],
						'post_title' => $wpdocs_name
					);
					wp_update_post( $wpdocs_attachment );
					$wpdocs = wpdocs_array_sort($wpdocs);
					wpdocs_save_list($wpdocs);
				}
			}
		} else wpdocs_errors(WPDOCS_ERROR_3,'error');
	} else wpdocs_errors(WPDOCS_ERROR_4,'error');
}

function wpdocs_create_document($valid_mime_type) {
	
}

/************************************************ wpdocs-widgets.php ************************************************/

function wpdocs_widgets() {
	register_widget( 'wpdocs_top_downloads' );
	register_widget( 'wpdocs_top_rated' );
	register_widget( 'wpdocs_last_updated' );
}
class wpdocs_last_updated extends WP_Widget {
	function wpdocs_last_updated() {
		// Instantiate the parent object
		parent::__construct( 'wpdocs_last_updated', 'WP Last Updated' );
	}
	function widget( $args, $instance ) {
		$wpdocs = get_option('wpdocs-list');
		$the_list  = wpdocs_array_sort($wpdocs,'modified', SORT_DESC);
		?>
		<div class="wpdocs-widget-container">
		<h1>Last Updated</h1>
		<table>
			<tr>
				<th></th>
				<th>File</th>
				<th>Date</th>
			</tr>
		<?php
		for($i=0; $i< get_option('wpdocs-last-updated');$i++) {
			if(!isset($the_list[$i])) break;
			$permalink = htmlspecialchars(get_permalink( $the_list[$i]['parent'] ));
			if($i%2 == 0) $row_type = 'wpdocs-even';
			else $row_type = 'wpdocs-odd';
			echo '<tr class="'.$row_type.'">';
			echo '<td>'.($i+1).'.</td>';
			echo '<td><a href="'.$permalink.'" >'.$the_list[$i]['name'].'</a></td>';
			echo '<td>'.date('m-d-y',$the_list[$i]['modified']).'</td>';
			echo '</tr>';
		}
		?>
			
		</table>
		</div>
		<?php
	}
	function update( $new_instance, $old_instance ) {
		$instance['title'] = strip_tags( $new_instance['title'] );
		update_option('wpdocs-last-updated', sanitize_wpdocs_data($_POST['wpdocs-last-updated']));
		return $instance;
	}
	function form( $instance ) {
		?>
		<p>
			<input type="text" name="wpdocs-last-updated" value="<?php echo get_option('wpdocs-last-updated'); ?>" />
		</p>
		<?php
	}
}
class wpdocs_top_rated extends WP_Widget {
	function wpdocs_top_rated() {
		// Instantiate the parent object
		parent::__construct( 'wpdocs_top_rated', 'WP Top Rated' );
	}
	function widget( $args, $instance ) {
		$wpdocs = get_option('wpdocs-list');
		$the_list  = wpdocs_array_sort($wpdocs,'rating', SORT_DESC);
		?>
		<div class="wpdocs-widget-container">
		<h1>Top Rated</h1>
		<table>
			<tr>
				<th></th>
				<th>File</th>
				<th>Rating</th>
			</tr>
		<?php
		for($i=0; $i< get_option('wpdocs-top-rated');$i++) {
			if(!isset($the_list[$i])) break;
			$permalink = htmlspecialchars(get_permalink( $the_list[$i]['parent'] ));
			if($i%2 == 0) $row_type = 'wpdocs-even';
			else $row_type = 'wpdocs-odd';
			echo '<tr class="'.$row_type.'">';
			echo '<td>'.($i+1).'.</td>';
			echo '<td><a href="'.$permalink.'" >'.$the_list[$i]['name'].'</a></td>';
			echo '<td>';
			for($j=1;$j<=5;$j++) {
				if($the_list[$i]['rating'] >= $j) echo '<i class="fa fa-star wpdocs-gold" id="'.$j.'"></i>';
				elseif(ceil($the_list[$i]['rating']) == $j ) echo '<i class="fa fa-star-half-full wpdocs-gold" id="'.$j.'"></i>';
				else echo '<i class="fa fa-star-o" id="'.$j.'"></i>';
			}
			echo '</td>';
			echo '</tr>';
		}
		?>
			
		</table>
		</div>
		<?php
	}
	function update( $new_instance, $old_instance ) {
		$instance['title'] = strip_tags( $new_instance['title'] );
		update_option('wpdocs-top-rated', sanitize_wpdocs_data($_POST['wpdocs-top-rated']));
		return $instance;
	}
	function form( $instance ) {
		?>
		<p>
			<input type="text" name="wpdocs-top-rated" value="<?php echo get_option('wpdocs-top-rated'); ?>" />
		</p>
		<?php
	}
}
class wpdocs_top_downloads extends WP_Widget {
	function wpdocs_top_downloads() {
		// Instantiate the parent object
		parent::__construct( 'wpdocs_top_downloads', 'WP Top Downloads' );
	}
	function widget( $args, $instance ) {
		$wpdocs = get_option('wpdocs-list');
		$the_list  = wpdocs_array_sort($wpdocs,'downloads', SORT_DESC);
		?>
		<div class="wpdocs-widget-container">
		<h1>Top Downloads</h1>
		<table>
			<tr>
				<th></th>
				<th>File</th>
				<th>DLs</th>
			</tr>
		<?php
		for($i=0; $i< get_option('wpdocs-top-downloads');$i++) {
			if(!isset($the_list[$i])) break;
			$permalink = htmlspecialchars(get_permalink( $the_list[$i]['parent'] ));
			if($i%2 == 0) $row_type = 'wpdocs-even';
			else $row_type = 'wpdocs-odd';
			echo '<tr class="'.$row_type.'">';
			echo '<td>'.($i+1).'.</td>';
			echo '<td><a href="'.$permalink.'" >'.$the_list[$i]['name'].'</a></td>';
			echo '<td>'.$the_list[$i]['downloads'].'</td>';
			echo '</tr>';
		}
		?>
			
		</table>
		</div>
		<?php
	}
	function update( $new_instance, $old_instance ) {
		$instance['title'] = strip_tags( $new_instance['title'] );
		update_option('wpdocs-top-downloads', sanitize_wpdocs_data($_POST['wpdocs-top-downloads']));
		return $instance;
	}
	function form( $instance ) {
		?>
		<p>
			<input type="text" name="wpdocs-top-downloads" value="<?php echo get_option('wpdocs-top-downloads'); ?>" />
		</p>
		<?php
	}
}