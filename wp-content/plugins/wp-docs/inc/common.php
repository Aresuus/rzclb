<?php
	add_action('admin_init', 'wpdocs_version_type_update');
	
	function wpdocs_version_type_update(){

				if(isset($_POST['version_type'])){			
					if ( 
						! isset( $_POST['version_type_nonce_field'] ) 
						|| ! wp_verify_nonce( $_POST['version_type_nonce_field'], 'version_type_action' ) 
					) {
					
					   print 'Sorry, your nonce did not verify.';
					   exit;
					
					} else {
					
					   // process form data
					
						
							
							update_option('wpdocs_versions_type', $_POST['version_type']);
							
							if($_POST['version_type']=='new')
							wp_redirect('options-general.php?page=wpdocs');
							else
							wp_redirect('admin.php?page=wpdocs-engine.php');
							
							exit;
						
					}
				}
	}
				
	function wpdocs_downward_compatibility(){
		global $wpdocs_data, $wpdocs_versions_type;
?>		
			<h2><?php echo $wpdocs_data['Name']; ?> <?php echo '('.$wpdocs_data['Version'].($wpdocs_pro?') Pro':')'); ?> - Settings</h2>
            <?php if(!$wpdocs_pro): ?>
            <a style="float:right; position:relative; top:-40px;" href="<?php echo $wpdocs_premium_link; ?>" target="_blank">Go Premium</a>
            <?php endif; ?>
            
            
            <?php 
							

				$wpdocs_versions_type = get_option('wpdocs_versions_type', 'old');
			?>
            
            <style type="text/css">
				.versions_type{
					background-color:#CCC;
					border-radius:4px;
					padding:10px 20px 20px 20px;
					
				}
				.versions_type label{
					font-weight:normal;
					padding:0;
					margin:0;					
				}
				.versions_type input[type="radio"]{
					padding:0;
					margin:0;
				}
			</style>
            <script type="text/javascript" language="javascript">
				jQuery(document).ready(function($){
					$('.versions_type input[type="radio"]').on('click', function(){
						$('.versions_type > form').submit();
					});
				});				
			</script>
            
            <div class="versions_type">
            	<form action="" method="post">
                <?php wp_nonce_field( 'version_type_action', 'version_type_nonce_field' ); ?>
            	<strong><?php _e('Switch to'); ?>:</strong><br />
            	<input type="radio" name="version_type" value="old" id="version_type_old" <?php checked( $wpdocs_versions_type=='old' ); ?> /><label for="version_type_old"><?php _e('Old Version'); ?> [<?php _e('Legacy'); ?>]</label><br />
                <input type="radio" name="version_type" value="new" id="version_type_new" <?php checked( $wpdocs_versions_type=='new' ); ?>/><label for="version_type_new"><?php _e('New Version'); ?> [<?php _e('Recommended'); ?>]</label>                
                </form>
            </div>
            <div class="wpdocs_help">
            	<h6>How it works?</h6>
                <p>A default page is created with title "WP Docs" in <a href="edit.php?post_type=page" target="_blank">pages</a>. You can create more pages with a shortcode <code>[wpdocs]</code>. Create directories, sub-directories and add documents to list them with the shortcode. That's it.</p>
            </div>
<?php
	}