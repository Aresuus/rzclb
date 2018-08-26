jQuery(document).ready(function($){
	
	var wpdocs_selected_dir = "";
	
	$('.wpdocs-folders').change(function(){
		var obj = $(this).find('option:selected');
		var val = obj.val();
		var id = obj.data('id');
		$('.folders-2').hide();
		wpdocs_refresh();
		
		//console.log(val+' - '+id+' - '+wpdocs_selected_dir);
		if(val!="" && id!=wpdocs_selected_dir)
		document.location.href = val;
		
	});
	
	$('select[id^="wpdocs-cat-"]').on('change keyup', function(event){
		var val = $(this).val();
		//$('select.hides, .wpdoc-items').hide();
		//$('.wpd_files').val("").trigger('change');
		//console.log('#'+val);
		//console.log(event.type);
		//$('.wpdocs-scats').not(this).hide();
		var dp = $(this).find('option:selected').data('parent');
		
		if(val!=""){
			
			$('select[data-parent="'+dp+'"]').hide();
			
			//console.log($('.btn-group.group-'+val));
			//console.log($('#'+val));
			
			if($('#'+val).length>0){
				$('#'+val).attr('data-parent', dp);
				$('#'+val).show().trigger('change');
			}
			
			if($('.btn-group.group-'+val).length>0){
				$('.btn-group.group-'+val).show();
			}
			
		}else{
			$.each($(this).nextAll('select.hides'), function(){
				$(this).val("").hide();
			});
			
		}
		
		update_files_group();
	});	
	function update_select_group(){
		$.each($('select[id^="wpdocs-cat-"]'), function(){
			if($(this).is(':visible')){
				$(this).trigger('change');
			}
		});
		
	}		
	function update_files_group(){
		$('.hides.btn-group').hide();
		$.each($('select[id^="wpdocs-cat-"]'), function(){				
			var val = $(this).val();
			if(val!=""){
				if($('.btn-group.group-'+val).length>0){
					$('.btn-group.group-'+val).show();
				}
			}
		});
		wpdocs_refresh();
	}
	update_select_group();
	update_files_group();
	
	$('#wpdocs-navbar .navbar-brand').on('click', function(){
		document.location.href = $(this).attr('href');
	});
	
	function wpdocs_refresh(){
		$.each(wpdocs, function(i, v){
			//console.log(i+' - '+v);			
			switch(i){
				case "wpdocs-cat":
					var layer = $('.wpdocs-items .group-'+v);
					
					if(wpdocs_selected_dir=="")
					wpdocs_selected_dir = v;
					
					if(layer.length>0){
						if($( ".wpdocs-folders" ).val()==""){
							layer.hide();					
						}else{
							layer.show();					
						}
					}
				break;
			}
		});
	}
	wpdocs_refresh();
});
	