<?php if ( ! defined( 'ABSPATH' ) ) exit; 
function wpdocs_file_info_small($the_wpdoc, $index=0, $current_cat) {
	global $post, $wpdocs_img_types;
	$upload_dir = wp_upload_dir();
	$the_wpdoc_permalink = htmlspecialchars(get_permalink($the_wpdoc['parent']));
	$the_post = get_post($the_wpdoc['parent']);
	$is_new = preg_match('/new=true/',$the_post->post_content);
	$post_date = strtotime($the_post->post_date);
	$last_modified = gmdate(get_option('wpdocs-date-format'),$the_wpdoc['modified']);
	$user_logged_in = is_user_logged_in();
	$wpdocs_show_non_members = $the_wpdoc['non_members'];
	$wpdocs_hide_all_files = get_option( 'wpdocs-hide-all-files' );
	$wpdocs_hide_all_posts = get_option( 'wpdocs-hide-all-posts' );
	$wpdocs_hide_all_files_non_members = get_option( 'wpdocs-hide-all-files-non-members' );
	$wpdocs_show_downloads = get_option( 'wpdocs-show-downloads' );
	$wpdocs_show_author = get_option( 'wpdocs-show-author' );
	$wpdocs_show_version = get_option( 'wpdocs-show-version' );
	$wpdocs_show_update = get_option( 'wpdocs-show-update' );
	$wpdocs_show_ratings = get_option( 'wpdocs-show-ratings' );
	$wpdocs_show_new_banners = get_option('wpdocs-show-new-banners');
	$wpdocs_time_to_display_banners = get_option('wpdocs-time-to-display-banners');
	$wpdocs_default_content = get_option('wpdocs-default-content');
	$wpdocs_show_description = get_option('wpdocs-show-description');
	$wpdocs_show_preview = get_option('wpdocs-show-preview');
	if(isset($post)) $permalink = get_permalink($post->ID);
	else $permalink = '';
	
	if(preg_match('/\?page_id=/',$permalink) || preg_match('/\?p=/',$permalink)) {
		$wpdocs_get = $permalink.'&wpdocs-cat=';
	} else $wpdocs_get = $permalink.'?wpdocs-cat=';
		$the_rating = wpdocs_get_rating($the_wpdoc);
		$file_type = wp_check_filetype($the_wpdoc['filename']);
		if(file_exists(plugin_dir_path( __FILE__ ).'includes/imgs/filetype-icons/'.$file_type['ext'].'.png'))  $file_icon = '<img src="'.plugins_url().'/wp-docs/includes/imgs/filetype-icons/'.$file_type['ext'].'.png" class="hidden-xs hidden-sm"/>';
		else $file_icon = '<img src="'.plugins_url().'/wp-docs/includes/imgs/filetype-icons/unknow.png" />';
		if($wpdocs_show_new_banners) {
			$modified = floor($the_wpdoc['modified']/86400)*86400;
			$today = floor(time()/86400)*86400;
			$days = (($today-$modified)/86400);
			if($wpdocs_time_to_display_banners > $days) {
				if($is_new == true) $status_tag = '<span class="wpdocs-new-updated-small badge pull-left alert-success ">'.__('New','wpdocs').'</span>';
				else $status_tag = '<span class="wpdocs-new-updated-small badge pull-left alert-info ">'.__('Updated','wpdocs').'</span>';
			} else $status_tag = '';
		} else $status_tag = '';
		if ( current_user_can('read_private_posts') ) $read_private_posts = true;
		else $read_private_posts = false;
	?>
		<tr>
			<td id="title" class="wpdocs-tooltip">
					<div class="btn-group">
						<a class="wpdocs-title-href" data-toggle="dropdown" href="#" ><?php echo $file_icon.' '.str_replace('\\','',$the_wpdoc['name']).$status_tag; ?></a>
						
						<ul class="dropdown-menu wpdocs-dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
							<li role="presentation" class="dropdown-header"><i class="fa fa-medium"></i> &#187; <?php echo $the_wpdoc['name']; ?></li>
							<li role="presentation" class="divider"></li>
							<li role="presentation" class="dropdown-header"><?php _e('File Options'); ?></li>
							<?php
								wpdocs_download_rights($the_wpdoc);
								wpdocs_desciption_rights($the_wpdoc);
								wpdocs_preview_rights($the_wpdoc);
								wpdocs_rating_rights($the_wpdoc);
								wpdocs_goto_post_rights($the_wpdoc, $the_wpdoc_permalink);
								wpdocs_share_rights($index, $the_wpdoc_permalink, get_site_url().'/?wpdocs-file='.$the_wpdoc['id'].'&wpdocs-url=false');
								if(is_admin()) { ?>
							<li role="presentation" class="divider"></li>
							<li role="presentation" class="dropdown-header"><?php _e('Admin Options'); ?></li>
							<?php
								wpdocs_add_update_rights($the_wpdoc, $current_cat);
								wpdocs_manage_versions_rights($the_wpdoc, $index, $current_cat);
								wpdocs_delete_file_rights($the_wpdoc, $index, $current_cat);
								if(get_option('wpdocs-preview-type') == 'box' && get_option('wpdocs-box-view-key') != '') {
									wpdocs_refresh_box_view($the_wpdoc, $index);
								}
							?>
							<li role="presentation" class="divider"></li>
							<li role="presentation" class="dropdown-header"><i class="fa fa-laptop"></i> <?php _e('File Status:'.' '.ucfirst($the_wpdoc['file_status'])); ?></li>
							<li role="presentation" class="dropdown-header"><i class="fa fa-bullhorn"></i> <?php _e('Post Status:'.' '.ucfirst($the_wpdoc['post_status'])); ?></li>
							<?php } ?>
						  </ul>
					</div>
			</td>
			<?php if($wpdocs_show_downloads) { ?><td id="downloads"><i class="fa fa-cloud-download"></i> <b class="wpdocs-orange"><?php echo $the_wpdoc['downloads'].' '.__('Downloads','wpdocs'); ?></b></td><?php } ?>
			<?php if($wpdocs_show_version) { ?><td class="hide" id="version"><i class="fa fa-power-off"></i><b class="wpdocs-blue"> <?php echo $the_wpdoc['version']; ?></b></td><?php } ?>
			<?php if($wpdocs_show_author) { ?><td class="hide" id="owner"><i class="fa fa-pencil"></i> <i class="wpdocs-green"><?php echo get_user_by('login', $the_wpdoc['owner'])->display_name; ?></i></td><?php } ?>
			<?php if($wpdocs_show_update) { ?><td id="update"><i class="fa fa-calendar"></i> <b class="wpdocs-red"><?php echo $last_modified; ?></b></td><?php } ?>
			<?php
				if($wpdocs_show_ratings) {
					echo '<td id="rating">';
					for($i=1;$i<=5;$i++) {
						if($the_rating['average'] >= $i) echo '<i class="fa fa-star wpdocs-gold" id="'.$i.'"></i>';
						elseif(ceil($the_rating['average']) == $i ) echo '<i class="fa fa-star-half-full wpdocs-gold" id="'.$i.'"></i>';
						else echo '<i class="fa fa-star-o" id="'.$i.'"></i>';
					}
					echo '</td>';
				} ?>
		</tr>
		<tr>
<?php
	//}
}
function wpdocs_file_info_small2($the_wpdoc, $index=0, $current_cat) {
	//pree($the_wpdoc);
	//pree($index);
	//pree($current_cat);
	
	global $post, $wpdocs_img_types;
	$upload_dir = wp_upload_dir();
	$the_wpdoc_permalink = htmlspecialchars(get_permalink($the_wpdoc['parent']));
	$the_post = get_post($the_wpdoc['parent']);
	$is_new = preg_match('/new=true/',$the_post->post_content);
	$post_date = strtotime($the_post->post_date);
	$last_modified = gmdate(get_option('wpdocs-date-format'),$the_wpdoc['modified']);
	$user_logged_in = is_user_logged_in();
	$wpdocs_show_non_members = $the_wpdoc['non_members'];
	$wpdocs_hide_all_files = get_option( 'wpdocs-hide-all-files' );
	$wpdocs_hide_all_posts = get_option( 'wpdocs-hide-all-posts' );
	$wpdocs_hide_all_files_non_members = get_option( 'wpdocs-hide-all-files-non-members' );
	$wpdocs_show_downloads = get_option( 'wpdocs-show-downloads' );
	$wpdocs_show_author = get_option( 'wpdocs-show-author' );
	$wpdocs_show_version = get_option( 'wpdocs-show-version' );
	$wpdocs_show_update = get_option( 'wpdocs-show-update' );
	$wpdocs_show_ratings = get_option( 'wpdocs-show-ratings' );
	$wpdocs_show_new_banners = get_option('wpdocs-show-new-banners');
	$wpdocs_time_to_display_banners = get_option('wpdocs-time-to-display-banners');
	$wpdocs_default_content = get_option('wpdocs-default-content');
	$wpdocs_show_description = get_option('wpdocs-show-description');
	$wpdocs_show_preview = get_option('wpdocs-show-preview');
	if(isset($post)) $permalink = get_permalink($post->ID);
	else $permalink = '';
	
	if(preg_match('/\?page_id=/',$permalink) || preg_match('/\?p=/',$permalink)) {
		$wpdocs_get = $permalink.'&wpdocs-cat=';
	} else $wpdocs_get = $permalink.'?wpdocs-cat=';
		$the_rating = wpdocs_get_rating($the_wpdoc);
		$file_type = wp_check_filetype($the_wpdoc['filename']);
		if(file_exists(plugin_dir_path( __FILE__ ).'includes/imgs/filetype-icons/'.$file_type['ext'].'.png'))  $file_icon = '<img src="'.plugins_url().'/wp-docs/includes/imgs/filetype-icons/'.$file_type['ext'].'.png" class="hidden-xs hidden-sm"/>';
		else $file_icon = '<img src="'.plugins_url().'/wp-docs/includes/imgs/filetype-icons/unknow.png" />';
		if($wpdocs_show_new_banners) {
			$modified = floor($the_wpdoc['modified']/86400)*86400;
			$today = floor(time()/86400)*86400;
			$days = (($today-$modified)/86400);
			if($wpdocs_time_to_display_banners > $days) {
				if($is_new == true) $status_tag = '<span class="wpdocs-new-updated-small badge pull-left alert-success ">'.__('New','wpdocs').'</span>';
				else $status_tag = '<span class="wpdocs-new-updated-small badge pull-left alert-info ">'.__('Updated','wpdocs').'</span>';
			} else $status_tag = '';
		} else $status_tag = '';
		if ( current_user_can('read_private_posts') ) $read_private_posts = true;
		else $read_private_posts = false;
	?>
		
					<div class="btn-group hides wpdoc-items group-<?php echo $current_cat; ?>">
						<a class="wpdocs-title-href" data-toggle="dropdown" href="#" ><?php echo $file_icon.' '.str_replace('\\','',$the_wpdoc['name']).$status_tag; ?></a>
						
						<ul class="dropdown-menu wpdocs-dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
							<li role="presentation" class="dropdown-header"><i class="fa fa-medium"></i> &#187; <?php echo $the_wpdoc['name']; ?></li>
							<li role="presentation" class="divider"></li>
							<li role="presentation" class="dropdown-header"><?php _e('File Options'); ?></li>
							<?php
								wpdocs_download_rights($the_wpdoc);
								wpdocs_desciption_rights($the_wpdoc);
								wpdocs_preview_rights($the_wpdoc);
								wpdocs_rating_rights($the_wpdoc);
								wpdocs_goto_post_rights($the_wpdoc, $the_wpdoc_permalink);
								wpdocs_share_rights($index, $the_wpdoc_permalink, get_site_url().'/?wpdocs-file='.$the_wpdoc['id'].'&wpdocs-url=false');
								if(is_admin()) { ?>
							<li role="presentation" class="divider"></li>
							<li role="presentation" class="dropdown-header"><?php _e('Admin Options'); ?></li>
							<?php
								wpdocs_add_update_rights($the_wpdoc, $current_cat);
								wpdocs_manage_versions_rights($the_wpdoc, $index, $current_cat);
								wpdocs_delete_file_rights($the_wpdoc, $index, $current_cat);
								if(get_option('wpdocs-preview-type') == 'box' && get_option('wpdocs-box-view-key') != '') {
									wpdocs_refresh_box_view($the_wpdoc, $index);
								}
							?>
							<li role="presentation" class="divider"></li>
							<li role="presentation" class="dropdown-header"><i class="fa fa-laptop"></i> <?php _e('File Status:'.' '.ucfirst($the_wpdoc['file_status'])); ?></li>
							<li role="presentation" class="dropdown-header"><i class="fa fa-bullhorn"></i> <?php _e('Post Status:'.' '.ucfirst($the_wpdoc['post_status'])); ?></li>
							<?php } ?>
						  </ul>
					</div>
			
<?php
	//}
}
?>