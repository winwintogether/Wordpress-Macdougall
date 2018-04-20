<?php

/**
 * Projects Single Template
*/

//* Add custom body class to the head
add_filter( 'body_class', 'custom_body_class' );
function custom_body_class( $classes ) {
	
	$classes[] = 'projects-single-template';
	return $classes;
	
}


//* Add Banner
add_action( 'genesis_after_header', 'banner_template_page_header', 11 );

function banner_template_page_header() {
	    
	    //global $post;
	    
		$image = get_field('image');
		$size = 'banner'; 
		$banner_img = wp_get_attachment_image( $image, $size );
	
		if ( empty( $banner_img ) ) {
			$banner_img = '<img src="' . get_stylesheet_directory_uri() . '/images/MacDougall-DefaultBanner.jpg" alt="MacDougall - Banner">';
		}
		
		echo '<div class="banner">' . $banner_img . '<div class="overlay"></div></div>';
	
}

//*Remove post info
remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );

//* Force full width content
add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );

//* Add project gallery
add_action( 'genesis_entry_content', 'project_gallery', 12 );
function project_gallery() {

	$image_ids = get_field('project_gallery_images', false, false);
	$shortcode = '[' . 'gallery ids="' . implode(',', $image_ids) . '" size="gallery" columns="2"]';

	echo do_shortcode( $shortcode );

}


genesis();
