// JavaScript Document
jQuery(document).ready(function($){
	$('.wpdocs_dir').on('change', function(){
		window.location.href = wpdocs.this_url+'?dir='+$(this).val();
	});
	
});