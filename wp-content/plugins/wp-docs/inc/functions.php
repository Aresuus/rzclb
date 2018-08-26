<?php


	function wpdocs_admin_enqueue_script(){
		if(isset($_GET['page']) && $_GET['page']=='wpdocs'){
			wp_enqueue_script( 'wpdocs_boostrap', plugin_dir_url( dirname(__FILE__) ) . 'js/bootstrap.min.js' );
			wp_enqueue_style( 'wpdocs-boostrap', plugins_url('css/bootstrap.min.css', dirname(__FILE__)));			
			wp_enqueue_style( 'wpdocs-font-awesome2-style', '//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.css');
			
			wp_enqueue_media();
			
			wp_enqueue_style( 'wpdocs-common', plugins_url('css/common-styles.css', dirname(__FILE__)));
			wp_enqueue_style( 'wpdocs-admin', plugins_url('css/admin-styles.css', dirname(__FILE__)));
			
			wp_enqueue_script( 'wpdocs_admin_scripts', plugin_dir_url( dirname(__FILE__) ) . 'js/admin-scripts.js' );
			wp_localize_script( 'wpdocs_admin_scripts', 'ajax_object',
				array( 
						'ajax_url' => admin_url( 'admin-ajax.php' ),  
				) );				
		}
	
			
	}	
	add_action( 'admin_enqueue_scripts', 'wpdocs_admin_enqueue_script' );
	
	add_action( 'wp_enqueue_scripts', 'wpdocs_wp_enqueue_script' );
	
	function wpdocs_wp_enqueue_script(){
		wp_enqueue_script( 'wpdocs_front_scripts', plugin_dir_url( dirname(__FILE__) ) . 'js/front-scripts.js' );
		wp_enqueue_style( 'wpdocs-common', plugins_url('css/common-styles.css', dirname(__FILE__)));
		wp_enqueue_style( 'wpdocs-front', plugins_url('css/front-styles.css', dirname(__FILE__)));
		
		wp_enqueue_style( 'wpdocs-font-awesome2-style', '//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.css');
		wp_localize_script( 'wpdocs_front_scripts', 'wpdocs',
				array( 
						'ajax_url' => admin_url( 'admin-ajax.php' ),  
						'this_url' => get_permalink(),  
				) );	
	}
	
	if(is_admin()){
		add_action( 'admin_menu', 'wpdocs_menu' );	
	}
	function wpdocs_menu()
	{
		global $wpdocs_data, $wpdocs_pro;
		
		$title = $wpdocs_data['Name'].' '.($wpdocs_pro?' '.__('Pro', 'wpdocs'):'');
		
		add_options_page($title, $title, 'activate_plugins', 'wpdocs', 'wpdocs_settings');



	}
	function wpdocs_settings(){
		include_once('wpdocs_settings.php');
	}
	
	function wpdocs_list($post_parent=0){
		$ret = array();
		
		if(is_numeric($post_parent)){			
			$args = array(
				'posts_per_page'   => -1,
				'offset'           => 0,
				'category'         => '',
				'category_name'    => '',
				'orderby'          => 'title',
				'order'            => 'ASC',
				'include'          => '',
				'exclude'          => '',
				'meta_key'         => '',
				'meta_value'       => '',
				'post_type'        => 'wpdocs_folder',
				'post_mime_type'   => '',
				'post_parent'      => $post_parent,
				'author'	   => '',
				'author_name'	   => '',
				'post_status'      => 'hidden',
				'suppress_filters' => true 
			);
			
			//pree($args);
			$posts_array = get_posts( $args );
			if(!empty($posts_array)){
				foreach($posts_array as $posts){
					$ret[] = array('id'=>$posts->ID, 'title'=>$posts->post_title);
				}
			}
		}
		//pree($ret);
		return $ret;
				
	}
	add_action( 'wp_ajax_wpdocs_create_folder', 'wpdocs_create_folder' );
	
	function wpdocs_create_folder(){
		
		$post_parent = $_POST['parent_dir'];
		
		$my_post = array(
		  'post_title'    => 'New Folder',
		  'post_content'  => '',
		  'post_status'   => 'hidden',
		  'post_author'   => 1,
		  'post_type'   => 'wpdocs_folder',
		  'post_parent'      => (($post_parent>0 && wpdocs_folder_exists($post_parent))?$post_parent:0),
		  'post_category' => array()
		);
		
		wp_insert_post( $my_post );	
		
		
		
		exit;
		
	}
	
	function wpdocs_list_added_items($dir){
		
		$wpdocs_items = wpdocs_added_items($dir); //pree($wpdocs_items);
		$files_list = '';
		
		
		if(!empty($wpdocs_items)){
			
			foreach($wpdocs_items as $item){
				$file_url = wp_get_attachment_url( $item );
				$title = basename($file_url);
				$ext = explode('.', $title);
				$ext = end($ext);
				switch($ext){
					case 'png':
					case 'jpg':
					case 'jpeg':
					case 'gif':
					case 'bmp':
					
						$class .= 'fa-image';
					
					break;
					
					default:
						$class .= 'fa-file';
					break;				
				}
				
				$files_list .= '<li data-id="'.$item.'"><a href="'.$file_url.'" target="_blank" class="file fa '.$class.'"></a><a class="ftitle" title="'.$title.'">'.$title.'</a></li>';
			}
		}	
		
		return $files_list;	
	}
	
	add_shortcode('wpdocs', 'wpdocs_front_list');
	
	function wpdocs_front_list(){
		ob_start();
		
		$dir = ((isset($_GET['dir']) && wpdocs_folder_exists($_GET['dir']))?$_GET['dir']:0);
		//pree($dir);
		$wpdocs_list = wpdocs_list($dir);
		$files_list = wpdocs_list_added_items($dir);
		//pree($files_list);
?>		
<div class="wpdocs_folders">
<?php	
		if(!empty($wpdocs_list)){
?>

<select class="wpdocs_dir" name="dir">
<option value=""><?php _e('Select'); ?></option>		
<?php			
			foreach($wpdocs_list as $list){
?>
<option value="<?php echo $list['id']; ?>"><?php echo $list['title']; ?></option>		
<?php		
			}
?>
</select>
<?php
		}
?>		
<div class="wpdocs_list">
<ul>
<?php echo ($files_list!=''?$files_list:''); ?>
</ul>
</div>
</div>
<?php			
			
		
		
		$out1 = ob_get_contents();
		
		ob_end_clean();		
		
		return $out1;
	}
	
	function wpdocs_parent_folder($id){
		//pree($id);
		$parent_id = 0;
		if(wpdocs_folder_exists($id)){
			$post_data = get_post( $id );		
			//pree($post_data);
			$parent_id = $post_data->post_parent;
		}
		return ($parent_id);
	}
	
	function wpdocs_added_items($dir_id){
		$wpdocs_items = array();
		if(is_numeric($dir_id) && $dir_id>0 && wpdocs_folder_exists($dir_id)){
			$wpdocs_items = get_post_meta( $dir_id, 'wpdocs_items', true );
			$wpdocs_items = is_array(maybe_unserialize($wpdocs_items))?maybe_unserialize($wpdocs_items):array();
		}
		return $wpdocs_items;
	}
		
	function wpdocs_folder_exists($id){
		//pree($id);
		$posts_array = array();
		if(is_numeric($id) && $id>0){
			$args = array(
				'posts_per_page'   => -1,
				'offset'           => 0,
				'category'         => '',
				'category_name'    => '',
				'orderby'          => 'title',
				'order'            => 'ASC',
				'include'          => array($id),
				'exclude'          => '',
				'meta_key'         => '',
				'meta_value'       => '',
				'post_type'        => 'wpdocs_folder',
				'post_mime_type'   => '',
				'post_parent'      => $post_parent,
				'author'	   => '',
				'author_name'	   => '',
				'post_status'      => 'hidden',
				'suppress_filters' => true 
			);
			$posts_array = get_posts( $args );	
		}
		return (count($posts_array)>0);
	}
	
	add_action( 'wp_ajax_wpdocs_update_folder', 'wpdocs_update_folder' );
	
	function wpdocs_update_folder(){
		
		$dir_id = $_POST['dir_id'];
		
		if($dir_id>0 && wpdocs_folder_exists($dir_id)){
			
			$my_post = array(
			  'post_title'    => $_POST['new_name'],
			  'ID'  => $dir_id,
			);
			
			wp_update_post( $my_post );	
		}
		
		
		exit;
		
	}	

	
	add_action( 'wp_ajax_wpdocs_add_files', 'wpdocs_add_files' );
	
	function wpdocs_add_files(){
		
		$dir_id = $_POST['dir_id'];
		$files = $_POST['files'];
		$files = is_array($files)?$files:array($files);
		//pree($dir_id);
		//pree($files);
		if($dir_id>0 && wpdocs_folder_exists($dir_id) && count($files)>0){
			
			
			$wpdocs_items = wpdocs_added_items($dir_id);
			
			$wpdocs_items = $wpdocs_items + $files;
			
			$wpdocs_items = array_unique($wpdocs_items);
			
			//pree($wpdocs_items);
			
			update_post_meta( $dir_id, 'wpdocs_items', $wpdocs_items );	
		}
		
		
		exit;
		
	}		
	