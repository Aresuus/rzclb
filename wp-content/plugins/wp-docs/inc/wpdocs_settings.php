<div class="wrap wpdocs-wrapper">
<?php
	wpdocs_downward_compatibility();
	$dir = ((isset($_GET['dir']) && $_GET['dir']>0)?$_GET['dir']:0);
	$files_list = wpdocs_list_added_items($dir);
	
?>

<div class="wpdocs_in_action">

<div class="wpdocs_folders">
<div class="wpdocs_toolbar">
<ul><li><a class="back-folder fa fa-hand-o-left" title="<?php _e('Click here to go back'); ?>" data-parent="<?php echo wpdocs_parent_folder($dir); ?>" data-id="<?php echo ($dir); ?>"></a></li>
<?php if($dir>0): ?>
<li><a class="new-file" data-id="<?php echo $dir; ?>"><i class="fa fa-plus-circle"></i><?php _e('Add Files'); ?></a></li>
<?php endif; ?>
<li><a class="new-folder" data-id="<?php echo $dir; ?>"><?php _e('New folder'); ?></a></li></ul>
</div>
<div class="wpdocs_list">
<ul>
<?php $wpdocs_list = wpdocs_list($dir); if(!empty($wpdocs_list)){ foreach($wpdocs_list as $list){ ?>
	<li data-id="<?php echo $list['id']; ?>"><a class="folder fa fa-folder"></a><a class="dtitle" title="<?php _e('Click here to rename'); ?>"><?php echo ($list['title']?$list['title']:'&nbsp;'); ?></a></li>
<?php } }?>    
<?php echo ($files_list!=''?$files_list:''); ?>
</ul>
</div>
</div>
<div class="wpdocs_log">
</div>

	
</div>

</div>	