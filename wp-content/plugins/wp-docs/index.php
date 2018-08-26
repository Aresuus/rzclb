<?php defined( 'ABSPATH' ) or die( 'No fankari bachay!' );
/*
Plugin Name: WP Docs
Plugin URI: http://www.websitedesignwebsitedevelopment.com/WP-Documents-Library/
Description: A documents management tool for education portals.
Author: fahadmahmood
Version: 1.1.8
Author URI: https://profiles.wordpress.org/fahadmahmood/
License: GPL2
	
This WordPress Plugin is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
This free software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with this software. If not, see http://www.gnu.org/licenses/gpl-2.0.html.	
*/

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	function sanitize_wpdocs_data( $input ) {

		if(is_array($input)){
		
			$new_input = array();
	
			foreach ( $input as $key => $val ) {
				$new_input[ $key ] = (is_array($val)?sanitize_wpdocs_data($val):sanitize_text_field( $val ));
			}
			
		}else{
			$new_input = sanitize_text_field($input);
		}
		
		return $new_input;
	}
	if(!function_exists('pre')){
	function pre($data){
			if(isset($_GET['debug'])){
				pree($data);
			}
		}	 
	} 	
	if(!function_exists('pree')){
	function pree($data){
				echo '<pre>';
				print_r($data);
				echo '</pre>';	
		
		}	 
	} 
	
	global $wpdocs_data, $wpdocs_pro, $wpdocs_premium_link, $wpdocs_dir, $wpdocs_levels, $wpdocs_versions_type;
	
	$wpdocs_data = get_plugin_data(__FILE__);
	$wpdocs_dir = plugin_dir_path( __FILE__ );
	$wpdocs_versions_type = get_option('wpdocs_versions_type', 'old');
	
	
	include_once 'inc/common.php';
	
	if($wpdocs_versions_type=='new'){
		include_once('inc/functions.php');
	}else{
	
	
		
		
		define('WPDOCS_CATS', 'wpdocs-cats.txt');
		define('WPDOCS_LIST', 'wpdocs-list.txt');
		define('WPDOCS_DIR','/wpdocs/');
		define('WPDOCS_URL',plugin_dir_url(__FILE__));
		define('WPDOCS_TIME_OFFSET', get_option('gmt_offset')*60*60);
		$wpdocs_premium_link = 'http://shop.androidbubbles.com/product/wp-docs-pro';
		
		$wpdocs_pro = file_exists($wpdocs_dir.'pro/wpdocs_extended.php');
		if($wpdocs_pro){
			include($wpdocs_dir.'pro/wpdocs_extended.php');
		}
		include 'includes/wpdocs-allowed-file-types.php';
		include 'includes/wpdocs-batch-upload.php';
		include 'includes/wpdocs-browser-compatibility.php';
		include 'includes/wpdocs-box-view.php';
		include 'includes/wpdocs-categories.php';
		include 'includes/wpdocs-dashboard.php';
		include 'includes/wpdocs-downloads.php';
		include 'includes/wpdocs-doc-preview.php';
		include 'includes/wpdocs-export.php';
		include 'includes/wpdocs-functions.php';
		include 'includes/wpdocs-find-lost-files.php';
		include 'includes/wpdocs-filesystem-cleanup.php';
		include 'includes/wpdocs-filenames-to-latin.php';
		include 'includes/wpdocs-file-info-large.php';
		include 'includes/wpdocs-file-info-small.php';
		//include 'includes/wpdocs-import.php';
		include 'includes/wpdocs-localization.php';
		//include 'includes/wpdocs-modals.php';
		//include 'includes/wpdocs-restore-defaults.php';
		//include 'includes/wpdocs-restore.php';
		//include 'includes/wpdocs-ratings.php';
		//include 'includes/wpdocs-rights.php';
		//include 'includes/wpdocs-social.php';
		//include 'includes/wpdocs-server-compatibility.php';
		//include 'includes/wpdocs-settings-page.php';
		//include 'includes/wpdocs-shortcodes.php';
		//include 'includes/wpdocs-sort.php';
		//include 'includes/wpdocs-settings.php';
		//include 'includes/wpdocs-the-list.php';
		//include 'includes/wpdocs-update-mime.php';
		//include 'includes/wpdocs-upload.php';	
		include 'includes/wpdocs-versions.php';
		//include 'includes/wpdocs-widgets.php';
		include 'includes/wpdocs-comb.php';
		
			
			
		
		wpdocs_nonce();
		
		if(!headers_sent() && stripos($_SERVER['REQUEST_URI'], '/feed') === false) add_action('send_headers', 'wpdocs_send_headers');
		elseif (!is_numeric(stripos($_SERVER['REQUEST_URI'], '/feed'))) {
			$message = sprintf('Premature output is preventing WP Docs from working properly. Outputs has started in %s on line %d.', $file, $line);
			echo '<div style="border: 1em solid red; background: #fff; color: #f00; margin:2em; padding: 1em;">', htmlspecialchars($message), '</div>';
			trigger_error($message);
			die();	
		}
		
		if ( is_admin()){
			
		
			add_action('admin_init', 'wpdocs_send_headers_dashboard');
			
			$plugin = plugin_basename(__FILE__); 
			add_filter("plugin_action_links_$plugin", 'wpdocs_plugin_links' );		
			
		}
		add_action('init', 'wpdocs_localize');
		add_action('admin_menu', 'wpdocs_dashboard_menu');
		add_action( 'wp_enqueue_scripts', 'wpdocs_script' );
		add_action('wp_head', 'wpdocs_document_ready_wp');
		add_action('admin_footer', 'wpdocs_document_ready_admin');
		add_action( 'widgets_init', 'wpdocs_widgets' );
		
	}