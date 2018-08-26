// JavaScript Document
jQuery(document).ready(function($){
	$('.new-folder').on('click', function(){
		var data = {
			'action': 'wpdocs_create_folder',
			'parent_dir': $(this).data('id'),
		};
		// We can also pass the url value separately from ajaxurl for front end AJAX implementations
		$.post(ajax_object.ajax_url, data, function(response) {
			window.location.reload();
		});		
	});
	$('.wpdocs_list ul li > a.dtitle').on('click', function(){
		var id = $(this).parent().data('id');
		var rename_to = prompt("Do you want to rename this folder?", $(this).html());
		
		if($.trim(rename_to)!=''){
			var data = {
				'action': 'wpdocs_update_folder',
				'dir_id': id,
				'new_name': rename_to,
			};
			// We can also pass the url value separately from ajaxurl for front end AJAX implementations
			$.post(ajax_object.ajax_url, data, function(response) {
				window.location.reload();
			});			
		}
	});
	$('.wpdocs_list ul li > a.folder').on('click', function(){
		window.location.href = 'options-general.php?page=wpdocs&dir='+$(this).parent().data('id');		
	});
	$('.back-folder').on('click', function(){
		window.location.href = 'options-general.php?page=wpdocs&dir='+$(this).data('parent');		
	});
	
	if ($('.new-file:visible').length > 0) {
		if ( typeof wp !== 'undefined' && wp.media && wp.media.editor) {
			$('.new-file:visible').on('click', function(e) {
				var id = $(this).data('id');
				e.preventDefault();
		

				wp.media.editor.send.attachment = function(props, attachment) {
					var data = {
						'action': 'wpdocs_add_files',
						'dir_id': id,
						'files': attachment.id,
					};
					// We can also pass the url value separately from ajaxurl for front end AJAX implementations
					$.post(ajax_object.ajax_url, data, function(response) {
						window.location.reload();
					});						
				};
				wp.media.editor.open($(this));
				//return false;
			});
			
		}		
	}	
	
});