<?php
/**
* Template Name: Slider Template
* Description: Template for the homepage
**/

//* Add custom body class to the head
add_filter( 'body_class', 'custom_body_class' );
function custom_body_class( $classes ) {
	
	$classes[] = 'slider-template';
	return $classes;
	
}



//* Force full width content layout
add_filter( 'genesis_site_layout', '__genesis_return_full_width_content' );


//* Add Start of Slider Header Wrapper

add_action( 'genesis_before_header', 'start_slider_header_wrapper', 6 );

function start_slider_header_wrapper() {

	echo '<div class="slider-header-wrapper">';
}


//* Add End of Slider Header Wrapper

add_action( 'genesis_after_header', 'end_slider_header_wrapper', 12 );

function end_slider_header_wrapper() {

	echo '</div>';
}


//* Add Slider
add_action( 'genesis_before_header', 'slider_template_page_header', 8 );

function slider_template_page_header() {
	
	
		// If at least 1 image is present in the Project Images Gallery field
		if( $images = get_field('slider_images') ) {
			$image_ids = wp_list_pluck( $images, 'id' );
			// Soliloquy Dynamic requires image IDs to be passed as a comma separated list    
			$image_ids_string = implode( ',', $image_ids );
			 
			echo '<div class="slider">';
				soliloquy_dynamic( array(
					'id' => 'slider-images',
					'images' => $image_ids_string
				) );
			
			echo '</div>';
			echo '<div class="overlay"></div>';
		} elseif( has_post_thumbnail() ) {
			$image_args = array(
				'size' => 'slide',
			);
			genesis_image( $image_args );
		} else {
			echo '<img src="'. get_stylesheet_directory_uri() .'/images/MacDougall-DefaultBanner.jpg" alt="MacDougall - Kithcen" />';
		}
		
}




genesis();
