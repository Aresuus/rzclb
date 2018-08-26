/*
Plugin Name: WP Docs
Plugin URI: http://www.websitedesignwebsitedevelopment.com/WP-Documents-Library/
Description: A documents repository for WordPress. 
Version: 3.1.2
Author URI: https://profiles.wordpress.org/fahadmahmood/

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
 */
var toggle_share = false;

// INITIALIZE THE ADMIN JAVASCRIPT
function wpdocs_wp() {
    // ADDS A DOCUMENTS TAG TO THE BODY FOR STYLE ISSUE FIXES
    jQuery('body').addClass('wpdocs');
    // INITALIZE BOOTSTRAP POPOVER FUNCTIONALITY
    //jQuery('[data-toggle="popover"]').popover();
    // ADD UPDATE DOCUMENT
    wpdocs_add_update_documents();
    // TOOGLE DESCRIPTION/PREVIEW
    wpdocs_toogle_description_preview();
    // DESRIPTION PREVIEW
    wpdocs_description_preview();
    // FILE PREVIEW
    wpdocs_file_preview();
    // IMAGE PREVIEW
    wpdocs_image_preview();
    // RATING SYSTEM
    wpdocs_ratings();
    // SORT OPTIONS
    wpdocs_sort_files();
    // RATINGS SUBMIT
    wpdocs_submit_rating('small');
    // SHARING MODAL
    wpdocs_share_modal();
    // CHECK WITH OF DOCUMENTS CONTAINER
    //wpdocs_check_width();
    //jQuery(window).resize(function() { wpdocs_check_width(); });
    // MODAL TOOGLE
    wpdocs_toogle_modals();
}
// INITIALIZE THE ADMIN JAVASCRIPT
function wpdocs_admin() {
    tinyMCE.init({ remove_linebreaks: false, });  
    //FIX FOCUS ISSUE WITH TINYMCE TEXTBOXES
    jQuery(document).on('focusin', function(e) { e.stopImmediatePropagation(); });
    // MODAL CLOSE EVENT
    wpdocs_modal_close();
    // INITALIZE BOOTSTRAP POPOVER FUNCTIONALITY
    jQuery('[data-toggle="popover"]').popover();
    wpdocs_color_pickers();
    // INITALIZE DRAGGABLE
    //jQuery(".draggable").draggable();
    // DISABLED SETTINGS
    wpdocs_toogle_disable_setting('#wpdocs-hide-all-files','#wpdocs-hide-all-files-non-members');
    wpdocs_toogle_disable_setting('#wpdocs-hide-all-files-non-members','#wpdocs-hide-all-files');
    wpdocs_toogle_disable_setting('#wpdocs-hide-all-posts','#wpdocs-hide-all-posts-non-members');
    wpdocs_toogle_disable_setting('#wpdocs-hide-all-posts-non-members','#wpdocs-hide-all-posts');
     // ADD UPDATE DOCUMENT
    wpdocs_add_update_documents();
    // DESRIPTION PREVIEW
    wpdocs_description_preview();
    // FILE PREVIEW
    wpdocs_file_preview();
    // IMAGE PREVIEW
    wpdocs_image_preview();
    // ADD MIME TYPE
    wpdocs_add_mime_type();
    // REMOVE MIME TYPE
    wpdocs_remove_mime_type();
    // RESTORE DEFAULT FILE TYPES
    wpdocs_restore_default_mimes();
    // SORT OPTIONS
    wpdocs_sort_files();
    // RATING SYSTEM
    wpdocs_ratings();
    // SHARING MODAL
    wpdocs_share_modal();
    // RUN 3.0 PATCH ON CLICK
    jQuery('#mdosc-3-0-patch-btn').click(function(event) {
	event.preventDefault();
	var numfiles = Number(jQuery(this).data('num-docs'));
	wpdocs_v3_0_patch(numfiles);
    });
    // SOCIAL CLICKED
    jQuery('.wpdocs-show-social').click(function() {
	    if (jQuery(this).hasClass('fa fa-plus-sign-alt')) {
		    jQuery(this).removeClass('fa fa-plus-sign-alt');
		    jQuery(this).addClass('fa fa-minus-sign-alt');
		    var raw_id = jQuery(this).prop('id');
	    raw_id = raw_id.split("-");
	    var id = raw_id[raw_id.length-1];
	    jQuery('#wpdocs-social-index-'+id).show();
	    } else {
		    jQuery(this).removeClass('fa fa-minus-sign-alt');
		    jQuery(this).addClass('icon-plus-sign-alt');
		    var raw_id = jQuery(this).prop('id');
	    raw_id = raw_id.split("-");
	    var id = raw_id[raw_id.length-1];
	    jQuery('#wpdocs-social-index-'+id).hide();
	    }

    });
   // ADD ROOT CATEGORY
    jQuery('#wpdocs-add-cat').click(function(event) { event.preventDefault(); });
    jQuery('input[id="wpdocs-cat-remove"]').click(function() {
	var confirm = window.confirm(wpdocs_js.category_delete);
	var cat = jQuery(this).prop('name');
	if (confirm) {
		jQuery('[name="wpdocs-cats['+cat+'][remove]"]').prop('value',1);
		jQuery('#wpdocs-cats').submit();
	}
    });
    /*
    jQuery('#wpdocs-file-status').change(function() {
	    if (jQuery(this).val() == 'hidden') jQuery('#wpdocs-post-status').prop('disabled','true');
	    else if (jQuery(this).val() == 'public') jQuery('#wpdocs-post-status').removeAttr('disabled');
    });
    */
    // BOX VIEW REFRESH
    wpdocs_box_view_refresh();
    // CHECK WITH OF DOCUMENTS CONTAINER
    //wpdocs_check_width();
    //jQuery(window).resize(function() { wpdocs_check_width(); });
    // FIND LOST FILES
    wpdocs_search_lost_files();
    // MODAL TOOGLE
    wpdocs_toogle_modals();
}
 // ADD / UPDATE DOCUMENTS
function wpdocs_add_update_documents() {
    jQuery('.add-update-btn').click(function(event) {
	var action_type = jQuery(this).data('action-type');
	var wpdocs_id = jQuery(this).data('wpdocs-id');
	var current_cat = jQuery(this).data('current-cat');
	var nonce = jQuery(this).data('nonce');
	var is_admin = jQuery(this).data('is-admin');
	jQuery.post(wpdocs_js.ajaxurl,{action: 'wpdocs-submit', 'type': action_type,'wpdocs-id': wpdocs_id, 'current-cat': current_cat, 'is-admin': is_admin},function(data) {
	    var action_text = 'Add Document';
	    var doc_data = JSON.parse(data);
	    console.debug(doc_data['error']);
	    if (doc_data['error'] != null) {
		alert(doc_data['error']);
	    } else {
		if (action_type == 'update-doc') {
		    jQuery('#wpdocs-add-update-form').prop('action', 'admin.php?page=wpdocs-engine.php&wpdocs-cat='+current_cat);
		    jQuery('input[name="wpdocs-type"]').prop('value', 'wpdocs-update');
		    jQuery('input[name="wpdocs-index"]').prop('value', wpdocs_id);
		    jQuery('input[name="wpdocs-pname"]').prop('value', doc_data['name']);
		    jQuery('input[name="wpdocs-post-status-sys"]').prop('value', doc_data['post_status']);
		    action_text = wpdocs_js.update_doc+' <small>'+doc_data['filename']+'</small>';
    
    
		    jQuery('#wpdocs-current-doc').html(wpdocs_js.current_file+': '+doc_data['filename']);
		    jQuery('input[name="wpdocs-name"]').prop('value',doc_data['name']);
		    jQuery('option[value="'+doc_data['cat']+'"]').prop('selected',true);
		    jQuery('input[name="wpdocs-version"]').prop('value', doc_data['version']);
		    jQuery('input[name="wpdocs-tags"]').prop('value', doc_data['post-tags']);
		    jQuery('input[name="wpdocs-last-modified"]').prop('value', doc_data['timestamp']);
		    if (doc_data['show_social'] == 'on') jQuery('input[name="wpdocs-social"]').prop('checked',true);
		    else jQuery('input[name="wpdocs-social"]').prop('checked',false);
		    if (doc_data['non_members'] == 'on') jQuery('input[name="wpdocs-non-members"]').prop('checked',true);
		    else jQuery('input[name="wpdocs-non-members"]').prop('checked',false);
		    //if(doc_data['file_status'] == 'hidden') jQuery("#wpdocs-post-status").prop('disabled', true);
		    jQuery("#wpdocs-file-status option").each(function() {
			if(doc_data['file_status'] == jQuery(this).val()) jQuery(this).prop('selected',true);
		    });
		    jQuery("#wpdocs-post-status option").each(function() {
			if(doc_data['post_status'] == jQuery(this).val()) jQuery(this).prop('selected',true);
		    });
		    jQuery('#wpdocs-current-owner').html('<i class="fa fa-user"></i> '+doc_data['owner']);
		    jQuery('#wpdocs-contributors-container').append('<input type="hidden" value="'+doc_data['owner']+'" name="wpdocs-owner-value"/>');
		    wpdocs_contributor_editor(doc_data);
		    //console.debug(doc_data['desc']);
		    //doc_data['desc'] = doc_data['desc'].replace(/(?:\r\n|\r|\n)/g, '<br />');
		    //console.debug(doc_data['desc']);
		    //doc_data['desc'] = doc_data['desc'].replace(/(?:\r\n|\r|\n|&nbsp;)/g, '');
		    //doc_data['desc'] = doc_data['desc'].replace(/&nbsp;/g, '<br />');
		    doc_data['desc'] = doc_data['desc'].replace(/\\/g, '');
		    doc_data['desc'] = doc_data['desc'].replace(/&quot;/g, '');
		    doc_data['desc'] = doc_data['desc'];
		    tinyMCE.activeEditor.setContent(doc_data['desc']);
		    jQuery('#wpdocs-save-doc-btn').prop('value', wpdocs_js.update_doc_btn);
		} else {
		    jQuery('input[name="wpdocs-name"]').val('');
		    jQuery('option[value="'+current_cat+'"]').prop('selected', true);
		    jQuery('input[name="wpdocs-version"]').val('1.0');
		    jQuery('#wpdocs-current-doc').html('');
		    jQuery('#wpdocs-current-owner').html('<i class="fa fa-user"></i> '+jQuery('input[name="wpdocs-current-user"]').val());
		    jQuery('#wpdocs-contributors-container').append('<input type="hidden" value="'+jQuery('input[name="wpdocs-current-user"]').val()+'" id="wpdocs-owner-value"/>');
		    jQuery('input[name="wpdocs-type"]').prop('value', 'wpdocs-add');
		    jQuery('input[name="wpdocs-last-modified"]').prop('value', doc_data['timestamp']);
		    wpdocs_contributor_editor(doc_data);
		    action_text = wpdocs_js.add_doc;
		    jQuery('#wpdocs-save-doc-btn').prop('value', wpdocs_js.add_doc_btn);
		}
	       jQuery('#wpdocs-add-update-header').html(action_text);
	    }
	});
	

    });


}
// CONTRIBUTOR EDITOR
function wpdocs_contributor_editor(doc_data) {
    jQuery('span[id^="wpdocs-contributors["]').each(function() { jQuery(this).remove(); });
    jQuery('input[name^="wpdocs-contributors["]').each(function() { jQuery(this).remove(); });
    if (doc_data['contributors'] == null) doc_data['contributors'] = {};
    for(var i = 0; i < doc_data['contributors'].length; i++) {
	jQuery('#wpdocs-contributors-container').append('<span class="label label-success wpdocs-contributors" id="wpdocs-contributors['+i+']" data-index="'+i+'"><i class="fa fa-user"></i> '+doc_data['contributors'][i]+' <i class="fa fa-times wpdocs-contributors-delete-btn" id="wpdocs-contributors-delete[wpdocs-contributors['+i+']]"></i></span>');
	jQuery('#wpdocs-contributors-container').append('<input type="hidden" value="'+doc_data['contributors'][i]+'" name="wpdocs-contributors['+i+']"/>');
    }
    activate_contributors_delete_btn();
    jQuery('#wpdocs-add-contributors').keyup(function() {
	var search_string_length = jQuery(this).val().length;
	if (search_string_length > 1) {
	    
	    var contributors = []
	    jQuery('input[name^="wpdocs-contributors"]').each(function() {
		contributors.push(jQuery(this).val()); 
	     });
	    
	    jQuery('.wpdocs-user-search-list').removeClass('hidden');
	    jQuery.post(wpdocs_js.ajaxurl,{action: 'wpdocs-submit', 'type': 'search-users', 'user-search-string': jQuery('#wpdocs-add-contributors').val(), 'owner': jQuery('input[name="wpdocs-owner-value"]').val(), 'contributors':contributors},function(data) {
		jQuery('.wpdocs-user-search-list').html(data);
		jQuery('.wpdocs-search-results-roles').click(function(event) {
		    event.preventDefault();
		    //jQuery(this).remove();
		    jQuery('#wpdocs-contributors-container').append('<span class="label label-success wpdocs-contributors" id="wpdocs-contributors['+i+']" data-index="'+i+'"><i class="fa fa-user"></i> '+jQuery(this).data('value')+' <i class="fa fa-times wpdocs-contributors-delete-btn" id="wpdocs-contributors-delete[wpdocs-contributors['+i+']]"></i></span>');
		    jQuery('#wpdocs-contributors-container').append('<input type="hidden" value="'+jQuery(this).data('value')+'" name="wpdocs-contributors['+i+']"/>');
		    i++;
		    jQuery('#wpdocs-add-contributors').val('');
		    jQuery('.wpdocs-user-search-list').html('');
		    jQuery('#wpdocs-add-contributors').focus();
		    jQuery('.wpdocs-user-search-list').addClass('hidden');
		    activate_contributors_delete_btn();
		});
		 jQuery('.wpdocs-search-results-users').click(function(event) {
		    event.preventDefault();
		    //jQuery(this).remove();
		    jQuery('#wpdocs-contributors-container').append('<span class="label label-success wpdocs-contributors" id="wpdocs-contributors['+i+']" data-index="'+i+'"><i class="fa fa-user"></i> '+jQuery(this).data('value')+' <i class="fa fa-times wpdocs-contributors-delete-btn" id="wpdocs-contributors-delete[wpdocs-contributors['+i+']]"></i></span>');
		    jQuery('#wpdocs-contributors-container').append('<input type="hidden" value="'+jQuery(this).data('value')+'" name="wpdocs-contributors['+i+']"/>');
		    i++;
		    jQuery('#wpdocs-add-contributors').val('');
		    jQuery('.wpdocs-user-search-list').html('');
		    jQuery('#wpdocs-add-contributors').focus();
		    jQuery('.wpdocs-user-search-list').addClass('hidden');
		    activate_contributors_delete_btn();
		});	
	    });
	} else  {
	    jQuery('.wpdocs-user-search-list').addClass('hidden');
	    jQuery('.wpdocs-user-search-list').html();
	}
    });
}
//ACTIVATE CONTRIBUTOR DELETE BUTTON
function activate_contributors_delete_btn() {
    jQuery('.wpdocs-contributors-delete-btn').click(function() {
	var index = jQuery(this).parent().data('index');
	jQuery(this).parent().remove();
	jQuery('input[name="wpdocs-contributors['+index+']"]').remove();
    });
}
// ADD ROOT CATEGORY
function add_main_category(total_cats) {
    wpdocs_add_sub_cat(total_cats, '', 0, jQuery('#the-list'), true);
}
// === COLOR PICKERS === //
//INITIALIZE IRIS COLOR PICKER
function wpdocs_color_pickers() {
    var button_bg_color_normal = jQuery('#bg-color-wpdocs-picker').prop('value');
    var button_bg_color_hover = jQuery('#bg-hover-color-wpdocs-picker').prop('value');
    var button_text_color_normal = jQuery('#bg-text-color-wpdocs-picker').prop('value');
    var button_text_color_hover =  jQuery('#bg-text-hover-color-wpdocs-picker').prop('value');
    var color_options = {
	change: function(event, ui) {
	    var element = jQuery(this).prop('id');
	    if (element == 'bg-color-wpdocs-picker') {
		button_bg_color_normal = ui.color.toString();
		jQuery('.wpdocs-download-btn-config').css('background', button_bg_color_normal);
	    } else if (element == 'bg-hover-color-wpdocs-picker') button_bg_color_hover = ui.color.toString();
	    if (element == 'bg-text-color-wpdocs-picker') {
		button_text_color_normal = ui.color.toString();
		jQuery('.wpdocs-download-btn-config').css('color', button_text_color_normal);
	    } else if (element == 'bg-text-hover-color-wpdocs-picker') button_text_color_hover = ui.color.toString();
	}
    }
    jQuery('[id$="wpdocs-picker"]').wpColorPicker(color_options);
    // HOVER ADMIN DOWNLOAD BUTTON PREVIEW
     jQuery('.wpdocs-download-btn-config').hover(
	function() {
	    jQuery(this).css('background', button_bg_color_hover);
	    jQuery(this).css('color', button_text_color_hover);
	}, function() {
	    jQuery(this).css('background', button_bg_color_normal);
	    jQuery(this).css('color', button_text_color_normal);
	}
    );
}
// === ALLOWED FILE TYPES === //
// RESTORE MIME TYPES
function wpdocs_restore_default_mimes() {
    jQuery('#wpdocs-restore-default-file-types').click(function(event) {
	event.preventDefault();
	jQuery.post(wpdocs_js.ajaxurl,{action: 'wpdocs-submit', type: 'restore-mime', is_admin: true},function(data) {
	    jQuery('.wpdocs-mime-table').html(data);
	    wpdocs_remove_mime_type();
	    wpdocs_add_mime_type();
	});
    });
}
// ADD MIME TYPE
function wpdocs_add_mime_type() {
    jQuery('#wpdocs-add-mime').click(function(event) {
	event.preventDefault();
	var file_extension = jQuery('input[name="wpdocs-file-extension"]').val();
	var mime_type = jQuery('input[name="wpdocs-mime-type"]').val();
	jQuery.post(wpdocs_js.ajaxurl,{action: 'wpdocs-submit', type: 'add-mime', file_extension: file_extension, mime_type: mime_type, is_admin: true},function(data) {
	    jQuery(data).insertBefore('.wpdocs-mime-submit');
	    wpdocs_remove_mime_type();
	    jQuery('input[name="wpdocs-file-extension"]').val('');
	    jQuery('input[name="wpdocs-mime-type"]').val('');
	});
    });
}
// REMOVE MIME TYPE
function wpdocs_remove_mime_type() {
    jQuery('.wpdocs-remove-mime').click(function(event) {
	event.preventDefault();
	var file_extension = jQuery(this).parent().parent().data('file-type');
	jQuery(this).parent().parent().remove();
	jQuery.post(wpdocs_js.ajaxurl,{action: 'wpdocs-submit', type: 'remove-mime', file_extension: file_extension, is_admin: true},function(data) { });
    });
}
// ========================== //
// ADD SUB CATEGORY
//var subcat_index = 0;
var add_button_clicks = 1;
function wpdocs_add_sub_cat(total_cats, parent, parent_depth, object, is_parent) {
    //wpdocs_set_onleave();
    
    jQuery.post(wpdocs_js.ajaxurl,{action: 'wpdocs-submit', type: 'wpdocs-cat-index', 'check-index': jQuery('#wpdocs-cats').data('check-index')},function(data) {
	var subcat_index = parseInt(data);
	var check_index = subcat_index+1;
	jQuery('#wpdocs-cats').data('cat-index', subcat_index);
	jQuery('#wpdocs-cats').data('check-index', check_index);
	var child_depth = parseInt(parent_depth)+1;
	var parent_id = 'class="wpdocs-cats-tr"';
	if (child_depth <= wpdocs_js.levels) {
	    jQuery('input[name="wpdocs-update-cat-index"]').val(add_button_clicks++);
	    var padding = 'style="padding-left: '+(11*child_depth)+'px; "';
	    if (subcat_index == 0) subcat_index = parseInt(total_cats)+1;
	    else subcat_index++;
	    var order = parseInt(jQuery('input[name="wpdocs-cats['+parent+'][num_children]"]').val())+1;
	    var disabled = '';
	    jQuery('input[name="wpdocs-cats['+parent+'][num_children]"]').val(order);
	    if (is_parent) {
		padding = 0;
		order = jQuery('.wp-list-table > tbody > tr').size()+1;
		disabled = '';
		child_depth = 0;
		parent_id = 'class="wpdocs-cats-tr parent-cat"';
	    }
	    if (jQuery('input[name="wpdocs-cats['+parent+'][index]"]').val() != undefined) {
		var parent_index = jQuery('input[name="wpdocs-cats['+parent+'][index]"]').val();
	    } else var parent_index = 0;
	    var subcat_index = jQuery('#wpdocs-cats').data('cat-index');
	    var html = '\
		<tr '+parent_id+'>\
		    <td  id="name" '+padding+' >\
		       <input type="hidden" name="wpdocs-cats[wpdocs-cat-'+subcat_index+'][index]" value="'+subcat_index+'"/>\
			<input type="hidden" name="wpdocs-cats[wpdocs-cat-'+subcat_index+'][parent_index]" value="'+parent_index+'"/>\
			<input type="hidden" name="wpdocs-cats[wpdocs-cat-'+subcat_index+'][num_children]" value="0" />\
			<input type="hidden" name="wpdocs-cats[wpdocs-cat-'+subcat_index+'][depth]" value="'+child_depth+'"/>\
			<input type="hidden" name="wpdocs-cats[wpdocs-cat-'+subcat_index+'][parent]" value="'+parent+'"/>\
			<input type="hidden" name="wpdocs-cats[wpdocs-cat-'+subcat_index+'][slug]" value="wpdocs-cat-'+subcat_index+'" />\
			<input type="text" name="wpdocs-cats[wpdocs-cat-'+subcat_index+'][name]"  value="'+wpdocs_js.new_category+'"  />\
		    </td>\
		    <td id="order">\
			<input  type="text" name="wpdocs-cats[wpdocs-cat-'+subcat_index+'][order]"  value="'+order+'" '+disabled+' />\
		    </td>\
		    <td id="remove">\
			    <input type="hidden" name="wpdocs-cats[wpdocs-cat-'+subcat_index+'][remove]" value="0"/>\
			    <input type="button" id="wpdocs-sub-cats-remove-new-'+subcat_index+'" class="button button-primary" value="Remove"  />\
		    </td>\
		    <td id="add-cat">\
			<input type="button" class="wpdocs-add-sub-cat button button-primary" id="wpdocs-sub-cats-add-new-'+subcat_index+'" value="'+wpdocs_js.add_folder+'""   />\
		    </td>\
		</tr>\
		';
	    if (jQuery(object).prop('id') == 'the-list') jQuery(object).append(html);
	    else jQuery(html).insertAfter(jQuery(object).parent().parent());
	    jQuery('input[id="wpdocs-sub-cats-remove-new-'+subcat_index+'"]').click(function() {
		jQuery('input[name="wpdocs-cats['+parent+'][num_children]"]').val(order-1);
		jQuery(this).parent().parent().remove();
	    });
	    jQuery('input[id="wpdocs-sub-cats-add-new-'+subcat_index+'"]').click(function() {
		var id = jQuery(this).prop('id').split('-');
		id = id[id.length-1];
		var parent = jQuery('input[name="wpdocs-cats[wpdocs-cat-'+id+'][parent]"]').val();
		var slug = jQuery('input[name="wpdocs-cats[wpdocs-cat-'+id+'][slug]"]').val();
		var depths = jQuery('input[name="wpdocs-cats[wpdocs-cat-'+id+'][depth]"]').val();
		//console.debug(jQuery('input[name="wpdocs-cats[wpdocs-cat-'+id+'][depth]"]').val());
		//alert(jQuery('input[name="wpdocs-cats[wpdocs-cat-3][depth]"]').val());
		wpdocs_add_sub_cat(subcat_index,slug, depths, this);
	    });
	    
	} else alert(wpdocs_js.category_support);
	//jQuery('.wpdocs-add-sub-cat').unbind('click');
    });

}
// FUNCTIONS
function wpdocs_set_onleave() { window.onbeforeunload = function() { return wpdocs_js.leave_page;}; }
function wpdocs_reset_onleave() { window.onbeforeunload = null; }
function wpdocs_download_zip(zip_file) {
    jQuery('#wpdocs-export-container').append('<h5 id="wpdocs-export-cog"><i class="fa fa-cog fa-1x fa-spin"></i> '+wpdocs_js.create_export_file+'</h5>');
    jQuery('#wpdocs-export-submit').addClass('disabled');
    jQuery.post(wpdocs_js.ajaxurl,{action: 'wpdocs-submit', type: 'wpdocs-export', 'zip-file': zip_file},function(data) {
	jQuery('#wpdocs-export-cog').html(wpdocs_js.export_creation_complete_starting_download);
	jQuery('#wpdocs-export-cog').delay(3000).fadeOut(1000, function() { jQuery('#wpdocs-export-cog').remove(); jQuery('#wpdocs-export-submit').removeClass('disabled'); });
	window.location.href = '?wpdocs-export-file='+zip_file;
    });
}
function wpdocs_download_current_version(version_id) {window.location.href = '?wpdocs-file='+version_id; }
//function wpdocs_download_version(version_id) { window.location.href = '?wpdocs-file='+version_id; }
function wpdocs_download_version(version_file) { window.location.href = '?wpdocs-version='+version_file; }
function wpdocs_delete_version(version_file, index, category, nonce) {
    var confirm = window.confirm(wpdocs_js.version_delete);
    if (confirm) {
	    window.location.href = 'admin.php?page=wpdocs-engine.php&wpdocs-cat='+category+'&action=delete-version&version-file='+version_file+'&wpdocs-index='+index+'&wpdocs-nonce='+nonce;
    }
}
function wpdocs_delete_file(index, category, nonce) {
    var confirm = window.confirm(wpdocs_js.version_file);
    if (confirm) {
	window.location.href = 'admin.php?page=wpdocs-engine.php&action=delete-doc&wpdocs-index='+index+'&wpdocs-cat='+category+'&wpdocs-nonce='+nonce;
    }
}
function wpdocs_download_file(wpdocs_file, wpdocs_url) { window.location.href = '?wpdocs-file='+wpdocs_file+'&wpdocs-url=false'; }
function wpdocs_share(wpdocs_link,wpdocs_direct,the_id) {
	if (toggle_share == false) {
		jQuery('#'+the_id+' .wpdocs-share-link').remove();
		jQuery('#'+the_id).append('<div class="wpdocs-share-link"><br>');
		jQuery('.wpdocs-share-link').append('<div class="well well-sm"><h3><i class="fa fa-arrow-circle-o-right"></i> Download Page</h3><p>'+wpdocs_link+'</p></div>');
		jQuery('.wpdocs-share-link').append('<div class="well well-sm"><h3><i class="fa fa-download"></i> Direct Download</h3><p>'+wpdocs_direct+'</p></div>');
		jQuery('.wpdocs-share-link').append('</div>');
		toggle_share = true;
	} else {
		jQuery('#'+the_id+' .wpdocs-share-link').remove();
		toggle_share = false;
	}
}
function wpdocs_toogle_disable_setting(main, disable) {
	var checked = jQuery(main).prop('checked');
	if (checked) jQuery(disable).prop('disabled', true);
	else jQuery(disable).prop('disabled', false);
	jQuery(main).click(function() {
		var checked = jQuery(this).prop('checked');
		if (checked) jQuery(disable).prop('disabled', true);
		else jQuery(disable).prop('disabled', false);
	});
}
// RATINGS
function wpdocs_ratings() {
    // DISPLAY RATING WIDGET
    jQuery('.ratings-button' ).click(function(event) {
	jQuery('.wpdocs-ratings-body').empty();
	var wpdocs_file_id = jQuery(this).data('wpdocs-id');
	var wpdocs_is_admin = jQuery(this).data('is-admin');
	jQuery.post(wpdocs_js.ajaxurl,{action: 'wpdocs-submit', type: 'rating',wpdocs_file_id: wpdocs_file_id, wp_root: wpdocs_js.wp_root},function(data) {
		jQuery('.wpdocs-ratings-body').html(data);
		wpdocs_submit_rating('large',wpdocs_file_id);
	});
    });
}
function wpdocs_submit_rating(size,file_id) {
    var my_rating = jQuery('.wpdocs-ratings-stars').data('my-rating');
    if (size == 'large') {
	size = 'fa-5x';
	for (index = 1; index < 6; ++index) {
	if (my_rating >= index) jQuery('#'+index).prop('class', 'fa fa-star '+size+'  wpdocs-gold wpdocs-my-rating');
	    else  jQuery('#'+index).prop('class', 'fa fa-star-o '+size+' wpdocs-my-rating');
	}
    } else size = 'fa-1x';

    jQuery('.wpdocs-my-rating').click(function(event) {
	if (size == 'fa-1x') file_id = jQuery('.wpdocs-post-header').data('wpdocs-id');
	my_rating = jQuery(this).prop('id');
	jQuery.post(wpdocs_js.ajaxurl,{action: 'wpdocs-submit', type: 'rating-submit',wpdocs_file_id: file_id, 'wpdocs-rating': my_rating},function(data) {
	    //jQuery('.wpdocs-post').append(data);
	    window.location.href = '';
	});
    });
    jQuery('.wpdocs-my-rating').mouseover(function() {
	for (index = 1; index < 6; ++index) {
	    if (this.id >= index) jQuery('#'+index).prop('class', 'fa fa-star '+size+' wpdocs-gold wpdocs-my-rating');
	    else  jQuery('#'+index).prop('class', 'fa fa-star-o '+size+' wpdocs-my-rating');
	}
    });
    jQuery('.wpdocs-rating-container-small, .wpdocs-rating-container').mouseout(function() {
	my_rating = jQuery('.wpdocs-ratings-stars').data('my-rating');
	for (index = 1; index < 6; ++index) {
	    if (my_rating >= index) jQuery('#'+index).prop('class', 'fa fa-star '+size+'  wpdocs-gold wpdocs-my-rating');
	    else  jQuery('#'+index).prop('class', 'fa fa-star-o '+size+' wpdocs-my-rating');
	}
    });
}
// RESTORE DEFAULT
function wpdocs_restore_default() {
   if (confirm(wpdocs_js.restore_warning)) {
	jQuery.post(wpdocs_js.ajaxurl,{action: 'wpdocs-submit', type:'restore', blog_id: wpdocs_js.blog_id, is_admin: true},function(data) {
	    window.location.href = "admin.php?page=wpdocs-engine.php&wpdocs-cat=wpdocs&restore-default=true";
	});
    }

}
// DESRIPTION PREVIEW
function wpdocs_description_preview() {
    jQuery('.description-preview' ).click(function(event) {
	jQuery('.wpdocs-description-preview-body').empty();
	var wpdocs_file_id = jQuery(this).data('wpdocs-id');
	var wpdocs_is_admin = jQuery(this).data('is-admin');
	jQuery.post(wpdocs_js.ajaxurl,{action: 'wpdocs-submit', type: 'show-desc',wpdocs_file_id: wpdocs_file_id, is_admin: wpdocs_is_admin},function(data) {
		jQuery('.wpdocs-description-preview-body').html(data);
	});
    });
}
// FILE PREVIEW
function wpdocs_file_preview() {
    jQuery('.file-preview' ).click(function(event) {
	jQuery('.wpdocs-file-preview-body').empty();
	var wpdocs_file_id = jQuery(this).data('wpdocs-id');
	var wpdocs_is_admin = jQuery(this).data('is-admin');
	jQuery.post(wpdocs_js.ajaxurl,{action: 'wpdocs-submit', type: 'file',wpdocs_file_id: wpdocs_file_id, is_admin: wpdocs_is_admin},function(data) {
	    jQuery('.wpdocs-file-preview-body').html(data);
	});
    });
}
// IMAGE PREVIEW
function wpdocs_image_preview() {
     jQuery('.img-preview' ).click(function() {
	jQuery('.wpdocs-file-preview-body').empty();
	var wpdocs_file_id = jQuery(this).data('wpdocs-id');
	var wpdocs_is_admin = jQuery(this).data('is-admin');
	jQuery.post(wpdocs_js.ajaxurl,{action: 'wpdocs-submit', type: 'img',wpdocs_file_id: wpdocs_file_id, is_admin: wpdocs_is_admin},function(data) {
	    jQuery('.wpdocs-file-preview-body').html(data);
	});
    });
}
// TOOGLE DESCRIPTION/PREVIEW
function wpdocs_toogle_description_preview() {
    jQuery('.wpdocs-nav-tab' ).click(function(event) {
	event.preventDefault();
	jQuery('.wpdocs-nav-tab').each(function() { jQuery(this).removeClass('wpdocs-nav-tab-active'); });
	jQuery(this).addClass('wpdocs-nav-tab-active');
	jQuery('#wpdocs-show-container-'+wpdocs_file_id).empty();
	var wpdocs_file_id = jQuery(this).data('wpdocs-id');
	var show_type = jQuery(this).data('wpdocs-show-type');
	jQuery.post(wpdocs_js.ajaxurl,{action: 'wpdocs-submit', type:'show', show_type:show_type,wpdocs_file_id: wpdocs_file_id, wp_root: wpdocs_js.wp_root},function(data) {
	    jQuery('#wpdocs-show-container-'+wpdocs_file_id).html(data);
	});
    });
}
// SORT FUNCTIONALITY
function wpdocs_sort_files() {
    jQuery('.wpdocs-sort-option').click(function() {
	if (jQuery(this).data('disable-user-sort') == false) {
	    var permalink = jQuery(this).data('permalink');
	    var current_cat = jQuery(this).data('current-cat');
	    var sort_type = jQuery(this).data('sort-type');
	    var sort_range = jQuery(this).children(':first').prop('class');
	    if (sort_range == 'fa fa-chevron-down') {
		sort_range = 'asc';
	    } else sort_range = 'desc';
	    jQuery.post(wpdocs_js.ajaxurl,{action: 'wpdocs-submit', type: 'sort', sort_type: sort_type, sort_range: sort_range  },function(data) {
		jQuery('.wpdocs-container').append(data);
		window.location.href = permalink;
	    });
	}
    });
}
// CHECK WIDTH OF DOCUMENTS AREA
is_collapsed = null;
function wpdocs_check_width() {

    if(jQuery('#wpdocs-navbar').width() < 600 && is_collapsed == false || jQuery('#wpdocs-navbar').width() < 600 && is_collapsed == null) {
	is_collapsed = true;
	jQuery('#wpdocs-nav-expand').remove();
	jQuery.post(wpdocs_js.ajaxurl,{action: 'wpdocs-submit', type: 'nav-collaspse'  },function(data) {
	    jQuery('head').append(data);
	});
    } else if(jQuery('#wpdocs-navbar').width() > 600 && is_collapsed == true || jQuery('#wpdocs-navbar').width() > 600 &&  is_collapsed == null) {
	is_collapsed = false;
	jQuery('#wpdocs-nav-collapse').remove();
	jQuery.post(wpdocs_js.ajaxurl,{action: 'wpdocs-submit', type: 'nav-expand'  },function(data) {
	    jQuery('head').append(data);
	});
    }
}
// SHARING MODAL
function wpdocs_share_modal() {
    jQuery('.sharing-button').click(function() {
	jQuery('.wpdocs-share-body').empty();
	jQuery('.wpdocs-share-body').html('<h1>Sharing</h1>');
	jQuery('.wpdocs-share-body').append('<div class="well well-sm"><h3><i class="fa fa-arrow-circle-o-right"></i> Download Page</h3><p>'+jQuery(this).data('permalink')+'</p></div>');
	jQuery('.wpdocs-share-body').append('<div class="well well-sm"><h3><i class="fa fa-download"></i> Direct Download</h3><p>'+jQuery(this).data('download')+'</p></div>');
	jQuery.post(wpdocs_js.ajaxurl,{action: 'wpdocs-submit', type: 'show-social', 'doc-index': jQuery(this).data('doc-index')  },function(data) {
	    jQuery('.wpdocs-share-body').append(data);
	    //TWITTER
	    !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');
	    jQuery.getScript('http://platform.twitter.com/widgets.js');
	    //twttr.widgets.load();
	    //GOOGLE +1
	    (function() {
	      var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
	      po.src = '//apis.google.com/js/plusone.js';
	      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
	    })();
	    //FACEBOOK LIKE
	    window.fbAsyncInit = function() {
		FB.init({ xfbml: true, version: 'v2.4' });
	    };
	    (function(d, s, id){
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) { return; }
		js = d.createElement(s); js.id = id;
		js.src = "//connect.facebook.net/en_US/sdk.js";
		fjs.parentNode.insertBefore(js, fjs);
	    }(document, 'script', 'facebook-jssdk'));
	    FB.XFBML.parse();
	    jQuery('.wpdocs-share-body').append('<div id="fb-root"></div>');
	    // LINKEDIN
	    var din ='<script id="holderLink" type="IN/Share" data-url="" data-counter="right"></script>';
	    jQuery(".linkedinDetail").html(din);
	    IN.parse();
	});
    });
}
// VERSION 3.0 JAVASCRIPT PATCH
function wpdocs_v3_0_patch(_numfiles) {
    if (typeof wpdocs_js == 'object') var wpdocs_vars = wpdocs_js;
    else var wpdocs_vars = wpdocs_patch_js;

    jQuery.post(wpdocs_vars.ajaxurl,{action: 'wpdocs-submit', type: 'wpdocs-v3-0-patch'  },function(data) {
	jQuery('body').append(data);
	jQuery('#run-updater-3-0').click(function() {
	    jQuery('.container-3-0').html('\
		<div class="btn-container-3-0">\
		    <h3>'+wpdocs_vars.patch_text_3_0_1+'</h3>\
		    <h1><i class="fa fa-spinner fa-pulse fa-3x"></i></h1>\
		    <h2>'+wpdocs_vars.patch_text_3_0_2+'</h2>\
		</div>\
		');
	    jQuery.post(wpdocs_vars.ajaxurl,{action: 'wpdocs-submit', type: 'wpdocs-v3-0-patch-run-updater'  },function(data) {
		window.location.href = '';
	    });
	});
	jQuery('#not-now-3-0').click(function() {
	    jQuery.post(wpdocs_vars.ajaxurl,{action: 'wpdocs-submit', type: 'wpdocs-v3-0-patch-cancel-updater'  },function(data) {
		jQuery('html, body').css('overflowY', 'auto');
		jQuery('.bg-3-0').remove();
		jQuery('.container-3-0').remove();
	    });
	});
    });
}
// FIND LOST FILES
function wpdocs_search_lost_files() {
    jQuery('#wpdocs-find-lost-file-start').click(function() {
	jQuery('#wpdocs-find-lost-file-start').addClass('disabled');
	jQuery.post(wpdocs_js.ajaxurl,{action: 'wpdocs-submit', type: 'lost-file-search-start'  },function(data) {
	    jQuery('#wpdocs-find-lost-file-start').removeClass('disabled');
	    jQuery('#wpdocs-find-lost-files-results').html(data);
	    wpdocs_search_lost_files_save();
	});
    });
    
}
function wpdocs_search_lost_files_save() {
    jQuery('#wpdocs-find-lost-files-save').submit(function(event) {
	jQuery('#wpdocs-find-lost-files-save-btn').addClass('disabled');
	event.preventDefault();
	var form_data = jQuery(this).serialize();
	jQuery.post(wpdocs_js.ajaxurl,{action: 'wpdocs-submit', type: 'lost-file-save', 'form-data': form_data  },function(data) {
	     jQuery('#wpdocs-find-lost-files-results').html(data);
	});
    });
}
// BOX VIEW REFRESH
function wpdocs_box_view_refresh() {
    jQuery('.box-view-refresh-button').click(function() {
	var filename = jQuery(this).data('filename');
	var index = jQuery(this).data('index');
	jQuery.post(wpdocs_js.ajaxurl,{action: 'wpdocs-submit', type: 'box-view-refresh', 'filename': filename, 'index': index  },function(data) {
	    jQuery('.wpdocs-container').append(data);
	    jQuery('#box-view-updated').delay(3000).fadeOut(400, function() { jQuery('#box-view-updated').remove()});
	});
    });
}
// CLOSE ALL MODALS
function wpdocs_modal_close() {
    jQuery('.modal').on('hidden.bs.modal', function () {
	jQuery('.wpdocs-modal-body').empty();
    })
}
// MODAL TOOGLE
function wpdocs_toogle_modals() {
     jQuery('body').on('click.collapse-next.data-api', '[data-toggle=wpdocs-modal]', function(event) {
	event.preventDefault();
	var options = {};
	var target = jQuery(this).data('target');
	jQuery(target).modal(options)   
    });
}