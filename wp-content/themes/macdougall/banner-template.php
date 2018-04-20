<?php
/**
* Template Name: Banner Template
* Description: Template for Banner
**/


//* Add custom body class to the head
add_filter( 'body_class', 'custom_body_class' );
function custom_body_class( $classes ) {
	if(is_page('client-testimonials')) {
		$classes[] = 'banner-template client-testimonials';
		return $classes;
	}
	else {
		$classes[] = 'banner-template';
		return $classes;
	}

	
}


// Add Banner
add_action( 'genesis_after_header', 'banner_template_page_header', 11 );

function banner_template_page_header() {
	    
	    //global $post;
	    
		$image = get_field('image');
		$size = 'banner'; 
		$banner_img = wp_get_attachment_image( $image, $size );
	
		if ( empty( $banner_img ) ) {
			$banner_img = '<img src="' . get_stylesheet_directory_uri() . '/images/MacDougall-DefaultBanner.jpg" alt="MacDougall - Kithcen">';
		}
		
		echo '<div class="banner">' . $banner_img . '<div class="overlay"></div></div>';
	
}


genesis();