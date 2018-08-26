<?php if ( ! defined( 'ABSPATH' ) ) exit; 
	function wpdocs_edit_cats() {
		$wpdocs_cats = get_option('wpdocs-cats');
		wpdocs_list_header();
		$check_index = 1;
		do {
			$found = wpdocs_find_cat('wpdocs-cat-'.$check_index);
			$empty_index = $check_index;
			$check_index++;
		} while ($found == true);
		update_option('wpdocs-num-cats', sanitize_wpdocs_data($empty_index));
		?>
		<div class="wpdocs-ds-container">
			<h2><?php _e('Manage Directories', 'wpdocs'); ?> <button class="btn btn-success btn-sm" id="wpdocs-add-cat" onclick="add_main_category('<?php echo intval(get_option('wpdocs-num-cats')); ?>')"class="wpdocs-grey-btn"><?php _e('Add Main Directory','wpdocs'); ?></button></h2>
			<form  id="wpdocs-cats" method="post" action="admin.php?page=wpdocs-engine.php&wpdocs-cat=cats" data-cat-index="<?php echo get_option('wpdocs-num-cats'); ?>" data-check-index="1">
				<input type="hidden" value="wpdocs-update-cats" name="action"/>
				<input type="hidden" name="wpdocs-update-cat-index" value="0"/>
				<table class="wp-list-table widefat plugins">
					<thead>
						<tr>
							<th scope="col" class="manage-column column-name" ><?php _e('Directory','wpdocs'); ?></th>
							<th scope="col"  class="manage-column column-name" ><?php _e('Order','wpdocs'); ?></th>
							<th scope="col"  class="manage-column column-name" ><?php _e('Remove','wpdocs'); ?></th>
							<th scope="col" class="manage-column column-name" ><?php _e('Add Directory','wpdocs'); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th scope="col" class="manage-column column-name" ><?php _e('Directory','wpdocs'); ?></th>
							<th scope="col" class="manage-column column-name" ><?php _e('Order','wpdocs'); ?></th>
							<th scope="col" class="manage-column column-name" ><?php _e('Remove','wpdocs'); ?></th>
							<th scope="col" class="manage-column column-name" ><?php _e('Add Directory','wpdocs'); ?></th>
						</tr>
					</tfoot>
					<tbody id="the-list">
				<?php
				$index = 0;
				if(!empty($wpdocs_cats)) {
					$wpdocs_cats = array_values($wpdocs_cats);
					wpdocs_build_cat_td($wpdocs_cats);
				} else {
					?>
					<tr>
						<td class="wpdocs-nofiles" colspan="4">
							<p><?php _e('No folders created.','wpdocs'); ?></p>
						</td>
					</tr>
				<?php 
				}
				?>
					</tbody>
				</table><br>
				<input type="submit" class="button button-primary" id="wpdocs-import-submit" onclick="wpdocs_reset_onleave()" value="<?php _e('Save Directories','wpdocs') ?>" />
			</form>
		</div>
		<?php
		//if(isset($_POST['action']) && $_POST['action'] == 'wpdocs-update-cats') wpdocs_update_cats();
	}
	function wpdocs_build_cat_td($wpdocs_cat,$parent_index=0) {
		global $wpdocs_input_text_bg_colors;
		$padding = '';
		//pree($wpdocs_cat);exit;
		foreach($wpdocs_cat as $index => $cat) {
			if(!is_array($cat))
			continue;
			
			if($cat['slug'] != null) {
				$parent_id = 'class="wpdocs-cats-tr"';
				if($cat['depth'] == 0) $parent_id = 'class="wpdocs-cats-tr parent-cat"';
				elseif($cat['depth'] > 0) $padding = 'style="padding-left: '.(11*$cat['depth']).'px; "';
				$color_scheme = 'style="background: '.$wpdocs_input_text_bg_colors[($cat['depth'])].'"';
				?>
				<tr <?php echo $parent_id; ?>>
					<td  id="name" <?php echo $padding; ?>>
						<input type="hidden" name="wpdocs-cats[<?php echo $cat['slug']; ?>][index]" value="<?php echo $index; ?>"/>
						<input type="hidden" name="wpdocs-cats[<?php echo $cat['slug']; ?>][parent_index]" value="<?php echo $parent_index; ?>"/>
						<input type="hidden" name="wpdocs-cats[<?php echo $cat['slug']; ?>][num_children]" value="<?php echo count($cat['children']); ?>"/>
						<input type="hidden" name="wpdocs-cats[<?php echo $cat['slug']; ?>][depth]" value="<?php echo $cat['depth']; ?>"/>
						<input type="hidden" name="wpdocs-cats[<?php echo $cat['slug']; ?>][parent]" value="<?php echo $cat['parent']; ?>"/>
						<input type="hidden" name="wpdocs-cats[<?php echo $cat['slug']; ?>][slug]" value="<?php echo $cat['slug']; ?>"/>
						<input <?php echo $color_scheme; ?> type="text" name="wpdocs-cats[<?php echo $cat['slug']; ?>][name]"  value="<?php echo $cat['name']; ?>" />
					</td>
					<td id="order">
						<input <?php echo $color_scheme; ?> type="text" name="wpdocs-cats[<?php echo $cat['slug']; ?>][order]"  value="<?php echo $index+1; ?>" <?php if($cat['parent'] != '') echo ''; ?> title="Sorry this functionality is disabled"/>
						
					</td>
					<td id="remove">
						<input type="hidden" name="wpdocs-cats[<?php echo $cat['slug']; ?>][remove]" value="0"/>
						<?php if(count($cat['children']) == 0) { ?> 
						<input type="button" id="wpdocs-cat-remove" name="<?php echo $cat['slug']; ?>" class="button button-primary" value="Remove"  />
						<?php }else{ ?>
                        <?php 
							//pree($cat);
						?>
                        <?php } ?>
					</td>
					<td id="add-cat">
						<input  type="button" class="wpdocs-add-sub-cat button button-primary" value="<?php _e('Add Directory', 'wpdocs'); ?>" onclick="wpdocs_add_sub_cat( '<?php echo intval(get_option('wpdocs-num-cats')); ?>', '<?php echo $cat['slug']; ?>','<?php echo $cat['depth']; ?>', this);" />
					</td>
				</tr>
				<?php
				$child = array_values($cat['children']);
				if(count($child) > 0) wpdocs_build_cat_td($child,$index);
			} else {
				//$cats = get_option('wpdocs-cats');
				//unset($cats[$parent_index]['children'][$index]);
				//update_option('wpdocs-cats', $cats);
			}
		}
	}
	function update_wpdocs_tree($wpdocs_cats, $arr, $depth='-1', $order=0){

		$order = ($order==0?$arr['base_parent']:$order);
		$depth = ($depth<0?$arr['depth']:$depth);
		
		if($depth>0){
			$depth--;
			$wpdocs_cats = update_wpdocs_tree($wpdocs_cats, $arr, $depth);
		}
		
		
		

		return $wpdocs_cats;
		
	}
	//add_action('admin_init', 'wpdocs_update_cats');
	
	if(!function_exists('wpdocs_update_cats')){
		function wpdocs_update_cats() {
			
			global $wpdocs_pro;
			
			if(intval(ini_get("max_input_vars")) > count($_POST, COUNT_RECURSIVE)) {
				$folder_creation = true;
				$wpdocs_cats = array();
				$upload_dir = wp_upload_dir();
				//if(isset($_POST['wpdocs-update-cat-index'])) wpdocs_update_num_cats(intval($_POST['wpdocs-update-cat-index']));
				if(isset($_POST['wpdocs-cats'])) {
					$wpdocs_cats_post = $_POST['wpdocs-cats'];
					//echo serialize($_POST);exit;
					$parent_id = 0;
					$parent_ids = array();
					$depth = 0;
					$prev_depth = 0;
					$ids_slugs = array();
					//pree($wpdocs_cats_post);exit;
					foreach($wpdocs_cats_post as $index => $cat) {
						if($cat['parent']!='')
						$ids_slugs[$cat['parent']][] = $cat['slug'];
					}
					
					//pree($ids_slugs);
					
					foreach($wpdocs_cats_post as $index => $cat) {
						if($cat['slug'] != null) {
							//pree($cat);
							$cat['index'] = intval($cat['index']);
							$cat['parent_index'] = intval($cat['parent_index']);
							$cat['depth'] = intval($cat['depth']);
							$cat['order'] = intval($cat['order']);							
							$curr_depth = intval($cat['depth']);
							$depth = intval($cat['depth']);
							
							
							if($cat['parent'] == '')  {
								$base_parent_id = intval($cat['order'])-1;
								$wpdocs_cats[$base_parent_id] = array('base_parent'=>'','index' => $cat['index'], 'parent_index'=>$cat['parent_index'], 'slug' => $cat['slug'], 'name' => $cat['name'], 'parent' => '', 'children' => array(), 'depth' => 0);
								if($cat['remove'] == 1) unset($wpdocs_cats[$base_parent_id]);
								
							} else {
								
								$order = intval($cat['order'])-1;
								switch($depth) {
									case 1:
									$wpdocs_cats[$base_parent_id]['children'][$order] = array('base_parent'=>$base_parent_id,'index' => $cat['index'], 'parent_index'=>$cat['parent_index'], 'slug' => $cat['slug'], 'name' => $cat['name'], 'parent' => $cat['parent'], 'children' => array(), 'depth' => $depth);
									if($cat['remove'] == 1) unset($wpdocs_cats[$base_parent_id]['children'][$order]);
									$parent1_id = $order;
																						
									break;
									case 2:
									$wpdocs_cats[$base_parent_id]['children'][$parent1_id]['children'][$order] = array('base_parent'=>$base_parent_id,'index' => $cat['index'], 'parent_index'=>$cat['parent_index'],'slug' => $cat['slug'], 'name' => $cat['name'], 'parent' => $cat['parent'], 'children' => array(), 'depth' => $depth);
									if($cat['remove'] == 1) unset($wpdocs_cats[$base_parent_id]['children'][$parent1_id]['children'][$order]);
									$parent2_id = $order;
									
									break;							
									
								} 
								
							}
							$parent_slug = $cat['slug'];
							if($cat['remove'] == 1) wpdocs_cleanup_cats($cat);
						} else  $folder_creation = false;
						
						//exit;
					}
					
					
					
					
					//pree($wpdocs_cats);exit;
					//exit;
					
					if($folder_creation) {
						foreach($wpdocs_cats as $index_1 => $cat1) {
							ksort($cat1['children']);
							$cat1 = array_values($cat1['children']);
							$wpdocs_cats[$index_1]['children'] = $cat1;
							foreach($cat1 as $index_2 => $cat2) {
								ksort($cat2['children']);
								$cat2 = array_values($cat2['children']);
								//pree($cat2);
								$wpdocs_cats[$index_1]['children'][$index_2]['children'] = $cat2;
							}
						}
						
						ksort($wpdocs_cats);
						$wpdocs_cats = array_values($wpdocs_cats);
						//echo '<br />'.str_repeat('-', 200).'<br />';
						//pree($wpdocs_cats);exit;
						//$wpdocs_cats = array();
						
						//pree($wpdocs_cats);
						//exit;
						update_option('wpdocs-cats', sanitize_wpdocs_data($wpdocs_cats), '' , 'no');
					} else wpdocs_errors(WPDOCS_ERROR_8, 'error');
				}
			} else {
				wpdocs_errors(__('max_input_vars','wpdocs').': '.ini_get("max_input_vars").' '.__('Input variables sent','wpdocs').': '.count($_POST, COUNT_RECURSIVE), 'error');
				wpdocs_errors(WPDOCS_ERROR_9, 'error');
			}
		}
	}
		
	function wpdocs_recursive_unset(&$array, $unwanted_key) {
		unset($array[$unwanted_key]);
		foreach ($array as &$value) {
			if (is_array($value)) {
				wpdocs_recursive_unset($value, $unwanted_key);
			}
		}
	}
	
	function wpdocs_cats_loop($the_cats) {
		foreach($the_cats as $key => $cat) {
			if(count($cat['children']) > 0) {
				//ksort($the_cats[$key]['children']);
				$the_cats[$key]['children'] = array_values($the_cats[$key]['children']);
				wpdocs_cats_loop($the_cats[$key]['children']);
			}
		}
		return $the_cats;
	}
	
	function wpdocs_cleanup_cats($value) {
		$upload_dir = wp_upload_dir();
		$wpdocs = get_option('wpdocs-list');
		$wpdocs_cats = get_option('wpdocs-cats');
		foreach($wpdocs as $k => $v) {
			if($v['cat'] == $value['slug']) {
				wp_delete_attachment( intval($v['id']), true );
				wp_delete_post( intval($v['parent']), true );
				$name = substr($v['filename'], 0, strrpos($v['filename'], '.') );
				if(file_exists($upload_dir['basedir'].'/wpdocs/'.$v['filename'])) @unlink($upload_dir['basedir'].'/wpdocs/'.$v['filename']);
				foreach($v['archived'] as $a) @unlink($upload_dir['basedir'].'/wpdocs/'.$a);
				$thumbnails = glob($upload_dir['basedir'].'/wpdocs/'.$name.'-150x55*');
				foreach($thumbnails as $t) unlink($t);
				unset($wpdocs[$k]);
			}
		}
		if(isset($value['children'])) {
			if(count($value['children']) > 0) {
				foreach($value['children'] as $key) {
					wpdocs_cleanup_cats($key);	
				}
			}
		}
	
		wpdocs_save_list($wpdocs);
	}