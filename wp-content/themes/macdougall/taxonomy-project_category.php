<?php
	
/**
* Projects Category Template
**/



//* Add custom body class to the head
add_filter( 'body_class', 'custom_body_class' );
function custom_body_class( $classes ) {
	
	$classes[] = 'projects-category-template';
	return $classes;
	
}

//* Force full width content
add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );


//* Add Page Hader
add_action( 'genesis_after_header', 'category_projects_header', 11 );

function category_projects_header() {
	
		$image = get_field('projects_archive_banner', 'option');
		$size = 'banner'; 
		$banner_img = wp_get_attachment_image( $image, $size );
		
		if ( empty( $banner_img ) ) {
			$banner_img = '<img src="' . get_stylesheet_directory_uri() . '/images/MacDougall-DefaultBanner.jpg" alt="MacDougall - Kithcen">';
		}
		
		echo '<div class="banner">' . $banner_img . '<div class="overlay"></div></div>';
	
}

// Genesis Grid Loop
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


//* Adjusting entry header
remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_open', 5 );
remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_close', 15 );
remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
remove_action( 'genesis_entry_header', 'genesis_do_post_title' );

//* Remove entry content
remove_action( 'genesis_entry_content', 'genesis_do_post_content' );


// Add entry item info

add_action( 'genesis_entry_content', 'item_info_open', 9 );
function item_info_open() {
	echo '<div class="item-info">';
	echo '<a href="' .get_permalink(). '">';

}

add_action( 'genesis_entry_content', 'genesis_do_post_title', 10 );

add_action( 'genesis_entry_content', 'view_project', 11 );
function view_project() {
	echo '<button>View Project</button>';
}

add_action( 'genesis_entry_content', 'item_info_close', 12 );
function item_info_close() {
	echo '</a>';
	echo '</div>';
}

add_filter( 'genesis_link_post_title', 'unlink_post_title' );
function unlink_post_title() {
    return false;
}


//* Adjusting entry footer
remove_action( 'genesis_entry_footer', 'genesis_entry_footer_markup_open', 5 );
remove_action( 'genesis_entry_footer', 'genesis_entry_footer_markup_close', 15 );
remove_action( 'genesis_entry_footer', 'genesis_post_meta' );






genesis();


/*
	
$archive_settings = get_option('genesis-cpt-archive-settings-projects');
$archive_headline = $archive_settings['headline'];
$archive_intro_text = $archive_settings['intro_text'];
	
// Feature Image


*/