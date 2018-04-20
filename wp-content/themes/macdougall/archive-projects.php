<?php
	
/**
* Projects Archive Template
**/


//* Add custom body class to the head
add_filter( 'body_class', 'custom_body_class' );
function custom_body_class( $classes ) {
	
	$classes[] = 'projects-archive-template';
	return $classes;
	
}

//* Force full width content
add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );


//* Add Page Hader
add_action( 'genesis_after_header', 'archive_projects_header', 11 );

function archive_projects_header() {
	
		$image = get_field('projects_archive_banner', 'option');
		$size = 'banner'; 
		$banner_img = wp_get_attachment_image( $image, $size );
		
		if ( empty( $banner_img ) ) {
			$banner_img = '<img src="' . get_stylesheet_directory_uri() . '/images/MacDougall-DefaultBanner.jpg" alt="MacDougall - Kithcen">';
		}
		
		echo '<div class="banner">' . $banner_img . '<div class="overlay"></div></div>';
	
}



function be_archive_post_class( $classes ) {
	global $wp_query;
	if( ! $wp_query->is_main_query() )
		return $classes;
		
	$classes[] = 'one-half';
	if( 0 == $wp_query->current_post % 2 )
		$classes[] = 'first';
	return $classes;
}
add_filter( 'post_class', 'be_archive_post_class' );


//* Remove genesis detault content loop
remove_action( 'genesis_loop', 'genesis_do_loop' );


// Add custom content loop
add_action( 'genesis_loop', 'taxonomy_loop' );
function taxonomy_loop() {

$args = array(
'post_per_page' => 9999,
);

$loop = new WP_Query( $args );
$terms = get_terms( 'project_category' );
$count=0;


if( $terms ) { 

foreach( $terms as $term ){
	
	$term_link = get_term_link( $term );
	
	$image = get_field('project_category_featured_image', $term);
	$size = 'feature'; 
	$cat_image = wp_get_attachment_image( $image, $size );
	
		
	if ( empty( $cat_image) ) {
		$cat_image = '<img src="' . get_stylesheet_directory_uri() . '/images/MacDougall-DefaultProjectCategoryImage.jpg" alt="MacDougall - Project Category">';
	}


	echo '<div class="project-category-item">';
	echo '<a href="' . $term_link . '">';
	
	echo '<div class="item-image">' . $cat_image . '</div>';
	
	echo '<div class="item-info">';
	echo '<h3>' . $term->name . '</h3>';
	echo '<button>View Projects</button>';
	//echo '< class="button" href="' . esc_url( $term_link ) . '">See Work</a>';
	echo '</div>';
	
	echo '</a>';
	echo '</div>';

 }

 
} // end of if taxonomoy loop has terms

} // end of taxonomy loop


genesis();


/*	
	$archive_settings = get_option('genesis-cpt-archive-settings-projects');
	$archive_headline = $archive_settings['headline'];
	$archive_intro_text = $archive_settings['intro_text'];
*/