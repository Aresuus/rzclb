<?php
//This init class is used to add the extension to the extensions list while you are developing them.
//When the extension is added to the supported list of extensions, this file is no longer needed.

if ( !class_exists( 'ZOOM_Template_FooGallery_Extension_Init' ) ) {
	class ZOOM_Template_FooGallery_Extension_Init {

		function __construct() {
			add_filter( 'foogallery_available_extensions', array( $this, 'add_to_extensions_list' ) );
		}

		function add_to_extensions_list( $extensions ) {
			$extensions[] = array(
				'slug'=> 'zoom',
				'class'=> 'ZOOM_Template_FooGallery_Extension',
				'title'=> __('ZOOM', 'foogallery-zoom'),
				'file'=> 'foogallery-zoom-extension.php',
				'description'=> __('A simple gallery with one awesome effect: ZOOM', 'foogallery-zoom'),
				'author'=> ' Matt Cromwell',
				'author_url'=> 'http://mattcromwell.com',
				'thumbnail'=> ZOOM_FG_URL . '/assets/extension_bg.png',
				'tags'=> array( __('template', 'foogallery') ),	//use foogallery translations
				'categories'=> array( __('Build Your Own', 'foogallery') ), //use foogallery translations
				'source'=> 'generated'
			);

			return $extensions;
		}
	}

	new ZOOM_Template_FooGallery_Extension_Init();
}