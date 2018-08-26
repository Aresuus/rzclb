<?php if ( ! defined( 'ABSPATH' ) ) exit; 
function wpdocs_allowed_file_types() {
?>
<?php wpdocs_list_header(); ?>
<h2><?php _e('Allowed File Types','wpdocs'); ?></h2>
<table class="table form-table">
	<tr>
		<th><?php _e('Allowed File Types','wpdocs'); ?></th>
		<?php
		$mimes = get_allowed_mime_types();
		?>
		<td>
			<table class="wpdocs-mime-table">
				<tr>
					<th><?php _e('Extension','wpdocs'); ?></th>
					<th><?php _e('Mime Type','wpdocs'); ?></th>
					<th><?php _e('Options','wpdocs'); ?></th>
				</tr>
					<?php
					foreach($mimes as $index => $mime) {
						echo '<tr data-file-type="'.$index.'" ><td>'.$index.'</td><td>'.$mime.'</td>';
						echo '<td><a href="#" class="wpdocs-remove-mime">'.__('remove','wpdocs').'</a></td>';
						echo '</tr>';
					}
					?>
				<tr class="wpdocs-mime-submit">
					<td><input type="text" placeholder="Enter File Type..." name="wpdocs-file-extension" value=""/></td>
					<td><input type="text" placeholder="Enter Mime Type..." name="wpdocs-mime-type" value=""/></td>
					<td><a href="#" id="wpdocs-add-mime"><?php _e('add','wpdocs'); ?></a></td>
				</tr>
			</table>
			<a href="http://www.freeformatter.com/mime-types-list.html#mime-types-list" alt="<?php _e('List of Files and Their Mime Types','wpdocs'); ?>" target="_blank"><?php _e('List of Files and Their Mime Types','wpdocs'); ?></a><br>
			<a href="#" id="wpdocs-restore-default-file-types" alt="<?php _e('Restore Default File Types','wpdocs'); ?>"><?php _e('Restore Default File Types','wpdocs'); ?></a>
		</td>
	</tr>
</table>
<?php
}
?>