<?php
/**
 * FooGallery ZOOM gallery template
 * This is the template that is run when a FooGallery shortcode is rendered to the frontend
 */
//the current FooGallery that is currently being rendered to the frontend
global $current_foogallery;
//the current shortcode args
global $current_foogallery_arguments;
//get our thumbnail sizing args
$args = foogallery_gallery_template_setting( 'thumbnail_size', 'thumbnail' );
//add the link setting to the args

//Disable anchor tags around each gallery image
$args['link'] = 'none';

//Set the classname of each image
$args['image_attributes'] = array(
	'class'  => 'small',
);

//Gallery Settings 
// $lightbox = foogallery_gallery_template_setting( 'lightbox', 'none' );

$spacing = foogallery_gallery_template_setting( 'spacing', '' );
$viswidth = foogallery_gallery_template_setting( 'viswidth', 200 );
$lenssize = foogallery_gallery_template_setting( 'lenssize', 200 );
$bordersize = foogallery_gallery_template_setting( 'bordersize', 5 );
$bordercolor = foogallery_gallery_template_setting( 'bordercolor', '#333333' );
$border_style = foogallery_gallery_template_setting( 'border-style', 'border-style-square-white' );
$shadowdepth = foogallery_gallery_template_setting( 'shadowdepth', 5 );
$gray = foogallery_gallery_template_setting( 'grayscale', 'none' );
$blur = foogallery_gallery_template_setting( 'blur', 'none' );
$grayblur = $gray . $blur;
?>
<!--	Styles that depend on Gallery Settings 
		All other styles are in gallery-zoom.css
-->
<style>
.foogallery-zoom .small {max-width: <?php echo $viswidth; ?>px;}
.large {
	width: <?php echo $lenssize; ?>px; 
	height: <?php echo $lenssize; ?>px; 
	-webkit-box-shadow: 0 0 0 <?php echo $bordersize; ?>px <?php echo $bordercolor; ?>, 
	0 0 <?php echo $shadowdepth; ?>px <?php echo $shadowdepth/3; ?>px rgba(0, 0, 0, 0.35), 
	inset 0 0 40px 0 rgba(0, 0, 0, 0.5);
	
	-moz-box-shadow: 0 0 0 <?php echo $bordersize; ?>px <?php echo $bordercolor; ?>, 
	0 0 <?php echo $shadowdepth; ?>px <?php echo $shadowdepth/3; ?>px rgba(0, 0, 0, 0.35), 
	inset 0 0 40px 0 rgba(0, 0, 0, 0.5);
	
	box-shadow: 0 0 0 <?php echo $bordersize; ?>px <?php echo $bordercolor; ?>, 
	0 0 <?php echo $shadowdepth; ?>px <?php echo $shadowdepth/3; ?>px rgba(0, 0, 0, 0.35), 
	inset 0 0 40px 0 rgba(0, 0, 0, 0.5);
	}
	
<!-- SVG filter styles for Mozilla -->
<?php
switch($grayblur) {
	case 'no-grayblur-on-load' : ?>
		.foogallery-zoom.blur-on-load .small,
		.foogallery-zoom.blur-on-hover .magnify:hover .small  {
			-webkit-filter: url(#GaussianBlurFilter);
			filter: url(#GaussianBlurFilter);
			} 
		<?php break;
	
	case 'no-grayblur-on-hover' : ?>
		.foogallery-zoom.blur-on-hover .magnify:hover .small  {
			-webkit-filter: url(#GaussianBlurFilter);
			filter: url(#GaussianBlurFilter);
			} 
		<?php break;
		
	case 'gray-on-loadblur-on-load' : 
	case 'gray-on-hoverblur-on-hover' : ?>
		.foogallery-zoom.blur-on-load.gray-on-load .small,
		.foogallery-zoom.blur-on-load.gray-on-load .magnify:hover .small,
		.foogallery-zoom.blur-on-hover.gray-on-hover .magnify:hover .small {
			-webkit-filter: url(#GrayBlur);
			filter: url(#GrayBlur);
			} 
		<?php break;
		
	case 'gray-on-loadno-blur' : ?>
		.foogallery-zoom.gray-on-load .small,
		.foogallery-zoom.gray-on-load .magnify:hover .small  {
			-webkit-filter: url(#GrayScaleFilter);
			filter: url(#GrayScaleFilter);
			} 
		<?php break;
		
	case 'gray-on-loadblur-on-hover' : ?>
		.foogallery-zoom.gray-on-load.blur-on-hover .small,
			-webkit-filter: url(#GrayScaleFilter);
			filter: url(#GrayScaleFilter);
			} 
		.foogallery-zoom.gray-on-load.blur-on-hover .magnify:hover .small,
			-webkit-filter: url(#GrayBlur);
			filter: url(#GrayBlur);
			}
		<?php break;

	case 'gray-on-hoverblur-on-load' : ?>
		.foogallery-zoom.blur-on-load .small {
			-webkit-filter: url(#GaussianBlurFilter);
			filter: url(#GaussianBlurFilter);
			} 
		.foogallery-zoom.gray-on-hover.blur-on-load .magnify:hover .small,
			-webkit-filter: url(#GrayBlur);
			filter: url(#GrayBlur);
			}
		<?php break;
		
	case 'gray-on-hoverno-blur' : ?>
		.foogallery-zoom.gray-on-hover.no-blur .small {
			-webkit-filter: none;
			filter: none;
			} 
		.foogallery-zoom.gray-on-hover.no-blur .magnify:hover .small {
			-webkit-filter: url(#GrayScaleFilter);
			filter: url(#GrayScaleFilter);
			}
		<?php break;
} ?>
 
</style>

<!-- Gallery HTML Output -->
<div class="foogallery-container foogallery-zoom<?php echo foogallery_build_class_attribute( $current_foogallery, $spacing, $border_style, $gray, $blur); ?>">
	<?php foreach ( $current_foogallery->attachments() as $attachment ) {
		$attr['src'] = apply_filters( 'foogallery_attachment_resize_thumbnail', $attachment->url, $args, $this );
		echo '<div class="magnify" id="' . $attachment->ID . '"><div class="large"  style="background: url(' . $attr['src'] . ') no-repeat;" data-url="' . $attachment->url . '"></div>' . $attachment->html_img( $args ) . '</div>';
	}//End foreach
	
//SVG filters for Mozilla
if ( ($blur == 'blur-on-load') || ($blur == 'blur-on-hover') ) {
?>
<svg width="0%" height="0%" xmlns="http://www.w3.org/2000/svg" version="1.1">
   <filter id="GaussianBlurFilter">
      <feGaussianBlur stdDeviation="3"/>
   </filter>
</svg>
<?php }
if ( ($gray == 'gray-on-load') || ($gray == 'gray-on-hover') ) {
?>
<svg width="0%" height="0%" xmlns="http://www.w3.org/2000/svg" version="1.1">
<filter id="GrayScaleFilter">
   <feColorMatrix type="matrix" values="0.2126 0.7152 0.0722 0 0 0.2126 0.7152 0.0722 0 0 0.2126 0.7152 0.0722 0 0 0 0 0 1 0" />
  </filter>
</svg>
<?php }
if ( ($grayblur == 'gray-on-loadblur-on-load') || ($grayblur == 'gray-on-hoverblur-on-load') || ($grayblur == 'gray-on-loadblur-on-hover') || ($grayblur == 'gray-on-hoverblur-on-hover')) {

?>
<svg width="0%" height="0%" xmlns="http://www.w3.org/2000/svg" version="1.1">
<filter id="GrayBlur">
	<feGaussianBlur stdDeviation="3"/>
	<feColorMatrix type="matrix" values="0.2126 0.7152 0.0722 0 0 0.2126 0.7152 0.0722 0 0 0.2126 0.7152 0.0722 0 0 0 0 0 1 0" />
  </filter>
</svg>
</div>
<?php }