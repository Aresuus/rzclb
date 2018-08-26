<?php if ( ! defined( 'ABSPATH' ) ) exit; 
function wpdocs_file_info_large($the_wpdoc, $index=0, $current_cat) {
	global $post;
	ob_start();
	$upload_dir = wp_upload_dir();
	$the_wpdoc_permalink = htmlspecialchars(get_permalink($the_wpdoc['parent']));
	$the_post = get_post($the_wpdoc['parent']);
	$is_new = preg_match('/new=true/',$the_post->post_content);
	$post_date = strtotime($the_post->post_date);
	$date_format = get_option('wpdocs-date-format');
	$last_modified = gmdate($date_format,$the_wpdoc['modified']);
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
	$post_status = $the_post->post_status;
	if(isset($post)) $permalink = get_permalink($post->ID);
	else $permalink = '';
	if(preg_match('/\?page_id=/',$permalink) || preg_match('/\?p=/',$permalink)) {
		$wpdocs_get = $permalink.'&wpdocs-cat=';
	} else $wpdocs_get = $permalink.'?wpdocs-cat=';
	//if($wpdocs_hide_all_files_non_members && $user_logged_in == false) $show_files = false;
	//elseif($wpdocs_hide_all_files == false || $page_type == 'dashboard') $show_files = true;
	//else $show_files = false;
	$show_files = true;
	if( $show_files) {
		wpdocs_social_scripts();
		if(isset($_GET['wpdocs-rating'])) $the_wpdoc = wpdocs_set_rating($index);
		$the_rating = wpdocs_get_rating($the_wpdoc);
		if($wpdocs_show_new_banners) {
			$modified = floor($the_wpdoc['modified']/86400)*86400;
			$today = floor(time()/86400)*86400;
			$days = (($today-$modified)/86400);
			if($wpdocs_time_to_display_banners > $days) {
				if($is_new == true) echo '<div class="wpdocs-new">'.__('New','wpdocs').'</div>';
				else echo '<div class="wpdocs-updated">'.__('Updated','wpdocs').'</div>';
			}
		}
	?>
	<div class="wpdocs-post-header" data-wpdocs-id="<?php echo $the_wpdoc['id']; ?>">
	<div class="wpdocs-post-button-box">
		<?php
		if ( current_user_can('read_private_posts') ) $read_private_posts = true;
		else $read_private_posts = false;
		if($the_wpdoc['post_status'] == 'private' && $read_private_posts == false) echo '<h2>'.str_replace('\\','',$the_wpdoc['name']).'</h2>';
		else { ?><h2><a href="<?php echo $the_wpdoc_permalink; ?>" ><?php echo str_replace('\\','',$the_wpdoc['name']); ?></a></h2><?php }
		?>
		<?php
		if($wpdocs_hide_all_files || $the_wpdoc['file_status'] == 'hidden') { ?><div class="wpdocs-login-msg"><?php _e('This file can not<br>be downloaded.','wpdocs'); ?></div><?php }
		else if($wpdocs_show_non_members  == 'off' && $user_logged_in == false || $user_logged_in == false && $wpdocs_hide_all_files_non_members) { ?>
			<div class="wpdocs-login-msg"><?php _e('Please login<br>to download this file','wpdocs'); ?></div>
		<?php } elseif($the_wpdoc['non_members'] == 'on' || $user_logged_in ) { ?>
			<input type="button" onclick="wpdocs_download_file('<?php echo $the_wpdoc['id']; ?>','<?php  echo $the_post->ID; ?>');" class="wpdocs-download-btn" value="<?php echo __('Download','wpdocs'); ?>">
		</h2>
		<?php } else { ?>
			<div class="wpdocs-login-msg"><?php _e('Please login<br>to download this file','wpdocs'); ?></div>
		<?php } ?>
	</div>
	<?php
	$user_logged_in = is_user_logged_in();
	if($user_logged_in && $wpdocs_show_ratings) {
			if($the_rating['your_rating'] == 0) $text = __("Rate Me!");
			else $text = __("Your Rating");
			echo '<div class="wpdocs-rating-container-small">';
			echo '<div class="wpdocs-green">'.$text.'</div><div id="wpdocs-star-container">';
			echo '<div class="wpdocs-ratings-stars" data-my-rating="'.$the_rating['your_rating'].'">';
			for($i=1;$i<=5;$i++) {
				if($the_rating['your_rating'] >= $i) echo '<i class="fa fa-star  wpdocs-gold wpdocs-my-rating" id="'.$i.'"></i>';
				else echo '<i class="fa fa-star-o wpdocs-my-rating" id="'.$i.'"></i>';
			}
			echo '</div></div></div>';
		}
		?>
	<div class="wpdocs-post-file-info">
		<?php if($wpdocs_show_ratings) { ?><p><i class="fa fa-star"></i> <?php echo $the_rating['average']; ?> <?php _e('Stars', 'wpdocs'); ?> (<?php echo $the_rating['total']; ?>)</p> <?php } ?>
		<?php if($wpdocs_show_downloads) { ?><p class="wpdocs-file-info"><i class="fa fa-cloud-download"></i> <b class="wpdocs-orange"><?php echo $the_wpdoc['downloads'].' '.__('Downloads','wpdocs'); ?></b></p> <?php } ?>
		<?php if($wpdocs_show_author) { ?><p><i class="fa fa-pencil"></i> <?php _e('Author','wpdocs'); ?>: <i class="wpdocs-green"><?php echo get_user_by('login', $the_wpdoc['owner'])->display_name; ?></i></p> <?php } ?>
		<?php if($wpdocs_show_version) { ?><p><i class="fa fa-power-off"></i> <?php _e('Version','wpdocs') ?>:  <b class="wpdocs-blue"><?php echo $the_wpdoc['version']; ?></b>
				<!--<a href="<?php echo $the_wpdoc_permalink.'&wpdocs-cat='.$current_cat.'&wpdocs-index='.$index; ?>&action=wpdocs-versions">[ View More Versions ]</a>-->
		</p><?php } ?>
		<?php if($wpdocs_show_update) { ?><p><i class="fa fa-calendar"></i> <?php _e('Last Updated','wpdocs'); ?>: <b class="wpdocs-red"><?php echo $last_modified; ?></b></p><?php } ?>
		<?php if(is_admin()) { ?>
		<p><i class="fa fa-file "></i> <?php echo __('File Status','wpdocs').': <b class="wpdocs-olive">'.strtoupper($the_wpdoc['file_status']).'</b>'; ?></p>
		<p><i class="fa fa-file-text"></i> <?php echo __('Post Status','wpdocs').': <b class="wpdocs-salmon">'.strtoupper($post_status).'</b>'; ?></p>
		<?php } ?>
	</div>
	</div>
<?php
		$the_page = ob_get_clean();
		return $the_page;
	}
}
?>