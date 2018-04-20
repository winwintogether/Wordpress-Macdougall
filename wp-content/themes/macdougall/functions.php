<?php
	
/**
 * @package MacDougall
 * @author  Adaptive Web Studio
 * @link    http://www.adaptivewebstudio.com/
 */


//* Start the engine
include_once( get_template_directory() . '/lib/init.php' );

//* Setup Theme
include_once( get_stylesheet_directory() . '/lib/theme-defaults.php' );

//* Set Localization (do not remove)
load_child_theme_textdomain( 'genesis-sample', apply_filters( 'child_theme_textdomain', get_stylesheet_directory() . '/languages', 'genesis-sample' ) );

//* Add Image upload and Color select to WordPress Theme Customizer
require_once( get_stylesheet_directory() . '/lib/customize.php' );

//* Include Customizer CSS
include_once( get_stylesheet_directory() . '/lib/output.php' );

//* Child theme (do not remove)
define( 'CHILD_THEME_NAME', 'MacDougall' );
define( 'CHILD_THEME_URL', 'https://www.macdougallmechanical.com/' );
define( 'CHILD_THEME_VERSION', '1.0' );

//* Enqueue Scripts and Styles
add_action( 'wp_enqueue_scripts', 'genesis_sample_enqueue_scripts_styles' );
function genesis_sample_enqueue_scripts_styles() {
	
	//Google Fonts
	wp_enqueue_style( 'genesis-fonts', 'https://fonts.googleapis.com/css?family=Work+Sans:400,500,600,700', array(), CHILD_THEME_VERSION );
	
	// Dashicons
	wp_enqueue_style( 'dashicons' );
	
	// Font Awesome
	wp_enqueue_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css' );
	
	// Accordion Content
	wp_enqueue_script( 'accordion-content', get_stylesheet_directory_uri() . '/js/accordion-content.js', array( 'jquery' ), '1.0.0', true );
	
	
	// Accordion Content
	wp_enqueue_script( 'modal-popup', get_stylesheet_directory_uri() . '/js/modal-popup.js', array( 'jquery' ), '1.0.0', true );
		
	// Responsive Menu
	wp_enqueue_script( 'genesis-sample-responsive-menu', get_stylesheet_directory_uri() . '/js/responsive-menu.js', array( 'jquery' ), '1.0.0', true );
	$output = array(
		'mainMenu' => __( 'Menu', 'genesis-sample' ),
		'subMenu'  => __( 'Menu', 'genesis-sample' ),
	);
	wp_localize_script( 'genesis-sample-responsive-menu', 'genesisSampleL10n', $output );

	// Custom JS
	wp_enqueue_script( 'macdogall-custom-js', get_stylesheet_directory_uri() . '/js/custom.js', array( 'jquery' ), '1.0.0', true );
	
}



// Header & Navs
///////////////////////////////////////////////////////////////////////////////////////////////////


//* Add support for custom header
add_theme_support( 'custom-header', array(
	'width'           => 264,
	'height'          => 80,
	'header-selector' => '.site-title a',
	'header-text'     => false,
	'flex-height'     => true,
) );



//* Reposition the secondary navigation menu
remove_action( 'genesis_after_header', 'genesis_do_subnav' );
add_action( 'genesis_footer', 'genesis_do_subnav', 5 );


//* Reduce the secondary navigation menu to one level depth
add_filter( 'wp_nav_menu_args', 'genesis_sample_secondary_menu_args' );
function genesis_sample_secondary_menu_args( $args ) {

	if ( 'secondary' != $args['theme_location'] ) {
		return $args;
	}

	$args['depth'] = 1;

	return $args;

}


//* Reposition the primary navigation menu
remove_action( 'genesis_after_header', 'genesis_do_nav' );
add_action( 'genesis_after_header', 'genesis_do_nav', 6 );


//* Add responsive menu
add_action( 'genesis_header_right', 'responsive_menu_pro', 9 );
function responsive_menu_pro() {

	echo do_shortcode('[responsive_menu_pro]'); 
}
  	 
// Imagery
///////////////////////////////////////////////////////////////////////////////////////////////////

// Add Theme Support for Thumbnails
add_theme_support('post-thumbnails');


//* Add Custom Image Sizes
add_image_size( 'slide', 1600, 800, true );
add_image_size( 'banner', 1600, 600, true );
add_image_size( 'feature', 800, 600, true );
add_image_size( 'small', 600, 9999, true );
add_image_size( 'medium', 800, 9999, true );
add_image_size( 'large', 1024, 9999, true );
add_image_size( 'gallery', 600, 600, true );


//* Modify size of the Gravatar in the author box
add_filter( 'genesis_author_box_gravatar_size', 'genesis_sample_author_box_gravatar' );
function genesis_sample_author_box_gravatar( $size ) {

	return 90;

}

//* Modify size of the Gravatar in the entry comments
add_filter( 'genesis_comment_list_args', 'genesis_sample_comments_gravatar' );
function genesis_sample_comments_gravatar( $args ) {

	$args['avatar_size'] = 60;

	return $args;

}


  	 
// Custom Post Types
///////////////////////////////////////////////////////////////////////////////////////////////////

 
//* Add Archive Settings option to Press Releases CPT
add_post_type_support( 'projects', 'genesis-cpt-archives-settings' );


if( function_exists('acf_add_options_page') ) {


	acf_add_options_sub_page(array(
		'page_title' 	=> 'Archive Banner',
		'menu_title'	=> 'Archive Banner',
		'parent_slug'	=> 'edit.php?post_type=projects',
	));

}



// Flexible Content Areas
///////////////////////////////////////////////////////////////////////////////////////////////////


//* Add flexible content
add_action ( 'genesis_entry_content', 'macdougall_partials');
function macdougall_partials(){

if ( is_page() || is_single() ):

	if( have_rows('flexible_content') ):
	

	//echo '<div class="content-blocks">';
	//echo '<span class="divider"><hr></span>';
	
		while ( have_rows('flexible_content') ) : the_row();

			if( get_row_layout() == 'header' ):
				echo '<div class="section-title clear" id="';
				the_sub_field('scroll_id');
				echo'"><h2>';
				the_sub_field('title');
				echo '</h2></div>';
			endif;
			
			if( get_row_layout() == 'sub_header' ):
				echo '<div class="section-sub-header clear"><h6>';
				the_sub_field('title');
				echo '</h6></div>';
			endif;
			
			if( get_row_layout() == 'full_width' ):
				get_template_part('partials/full-width', 'full-width');				
			endif;
			
			if( get_row_layout() == '2_column' ):
				get_template_part('partials/2-column', '2-column');
			endif;
			
			if( get_row_layout() == '3_column' ):
				get_template_part('partials/3-column', '3-column');
			endif;
			
			if( get_row_layout() == 'section_divider' ):
				if( get_sub_field('divider_type') == 'line' ): 
				echo '<span class="divider"><hr></span>';
				endif;
			endif;
			
			if( get_row_layout() == 'section_divider' ):
				if( get_sub_field('divider_type') == 'space' ): 
					echo '<span class="space"></span>';
				endif;
			endif;
			
			if( get_row_layout() == 'accordion_content' ):
				get_template_part('partials/accordion-content', 'accordion-content');
			endif;
			
			if( get_row_layout() == 'featured_content' ):
				get_template_part('partials/featured-content', 'featured-content');
			endif;
			
			if( get_row_layout() == 'featured_testimonial' ):
				get_template_part('partials/featured-testimonial', 'featured-testimonial');
			endif;
			
			if( get_row_layout() == 'featured_news' ):
				get_template_part('partials/featured-news', 'featured-news');
			endif;
			
			if( get_row_layout() == 'content_sidebar' ):
				get_template_part('partials/content-sidebar', 'content-sidebar');
			endif;
			
			if( get_row_layout() == 'sidebar_content' ):
				get_template_part('partials/sidebar-content', 'sidebar-content');
			endif;

			if( get_row_layout() == 'insignias_people' ):
				get_template_part('partials/insignias-people', 'insignias-people');
			endif;
				
			
		endwhile;
		//echo '</div>'; // End of content blocks div
		
		else :
	
	endif;

endif;

}



//* Add specialty content

add_action ( 'genesis_before_footer', 'macdougall_specialty_content', 6);
function macdougall_specialty_content(){

if ( is_page() ):

	if( have_rows('specialty_content') ):
	
		while ( have_rows('specialty_content') ) : the_row();
						
			if( get_row_layout() == 'specialty_content_type' ):
			
				/*
				if( get_sub_field('choices') == 'affiliates' ): 
				
					genesis_widget_area( 'affiliate-widget', array(
					'before' => '<div class="affiliate-widget'. custom_widget_area_class( 'affiliate-widget' ) .'widget-area">',
					'after'  => '</div>',
					) );
					
				endif;
				*/
				
				/*
				if( get_sub_field('choices') == 'newsletter_signup' ): 
				
					genesis_widget_area( 'newsletter-widget', array(
					'before' => '<div class="newsletter-widget">',
					'after'  => '</div>',
					) );
					
				endif;
				*/
				
				if( get_sub_field('choices') == 'call_to_action' ): 
					genesis_widget_area( 'cta-widget', array(
					'before' => '<div class="cta-widget'. custom_widget_area_class( 'cta-widget' ) .' widget-area">',
					'after'  => '</div>',
					) );
					
				endif;
				
			endif;
			
			if( get_row_layout() == 'testimonial_section' ):
			
					echo '<div class="site-testimonial">';
						echo '<h3>What Our Clients Say</h3>';
						echo '<p>'.get_sub_field('testimonial_section_quote').'</p>';
						echo '<h6>'.get_sub_field('testimonial_section_name').'</h6>';
					echo '</div>';
					//get_template_part('partials/testimonial-section', 'testimonial-section');
				
			endif;
		
		endwhile;
		
		
		else :

		// no layouts found
	
	endif;

endif;

}


// Widgets
///////////////////////////////////////////////////////////////////////////////////////////////////

/*	
//* Register Social Media Widget

genesis_register_sidebar( array(
	'id'          => 'social-widget',
	'name'        => __( 'Social Widget', 'base' ),
	'description' => __( 'This is the widget area for the social media area.', 'base' ),
) );


//* Register Newsletter Widget

genesis_register_sidebar( array(
	'id'          => 'newsletter-widget',
	'name'        => __( 'Newsletter Widget', 'base' ),
	'description' => __( 'This is the widget area for the newsletter area.', 'base' ),
) );


//* Register Call To Action Widget

genesis_register_sidebar( array(
	'id'          => 'cta-widget',
	'name'        => __( 'Call To Action Widget', 'base' ),
	'description' => __( 'This is the widget area for the call to action area.', 'base' ),
) );
*/

//* Register Footer Widgets

genesis_register_sidebar( array(
	'id'          => 'footer-widget-area-1',
	'name'        => __( 'Footer Widget Area 1', 'base' ),
	'description' => __( 'This is a widget area for the footer area.', 'base' ),
) );

genesis_register_sidebar( array(
	'id'          => 'footer-widget-area-2',
	'name'        => __( 'Footer Widget Area 2', 'base' ),
	'description' => __( 'This is a widget area for the footer area.', 'base' ),
) );

genesis_register_sidebar( array(
	'id'          => 'footer-widget-area-3',
	'name'        => __( 'Footer Widget Area 3', 'base' ),
	'description' => __( 'This is a widget area for the footer area.', 'base' ),
) );

genesis_register_sidebar( array(
	'id'          => 'footer-widget-area-4',
	'name'        => __( 'Footer Widget Area 4', 'base' ),
	'description' => __( 'This is a widget area for the footer area.', 'base' ),
) );

genesis_register_sidebar( array(
	'id'          => 'footer-widget-area-5',
	'name'        => __( 'Footer Widget Area 5', 'base' ),
	'description' => __( 'This is a widget area for the footer area.', 'base' ),
) );

/*
genesis_register_sidebar( array(
	'id'          => 'footer-widget-area-6',
	'name'        => __( 'Footer Widget Area 6', 'base' ),
	'description' => __( 'This is a widget area for the footer area.', 'base' ),
	
) );
*/



// WP Editor
///////////////////////////////////////////////////////////////////////////////////////////////////


// Attach callback to 'tiny_mce_before_init' 
add_filter( 'tiny_mce_before_init', 'my_mce_before_init_insert_formats' ); 


//* Add Custom Styles Button to the WP Editor
function wpb_mce_buttons_2($buttons) {
	array_unshift($buttons, 'styleselect');
	return $buttons;
}
add_filter('mce_buttons_2', 'wpb_mce_buttons_2');


//* Add Custom Styles the WP Editor
function my_theme_add_editor_styles() {
    add_editor_style( 'custom-editor-style.css' );
}
add_action( 'init', 'my_theme_add_editor_styles' );


//* Callback function to filter the MCE settings

function my_mce_before_init_insert_formats( $init_array ) {  


// Define the style_formats array

	$style_formats = array(  
/*
* Each array child is a format with it's own settings
* Notice that each array has title, block, classes, and wrapper arguments
* Title is the label which will be visible in Formats menu
* Block defines whether it is a span, div, selector, or inline style
* Classes allows you to define CSS classes
* Wrapper whether or not to add a new block-level element around any selected elements
*/
		array(  
			'title' => 'Intro Text',  
			'block' => 'span',  
			'classes' => 'intro-text',
			'wrapper' => true,
			
		),  
		array(  
			'title' => 'Button',  
			'block' => 'a',  
			'classes' => 'button',
			'wrapper' => true,
		),
	);  
	// Insert the array, JSON ENCODED, into 'style_formats'
	$init_array['style_formats'] = json_encode( $style_formats );  
	
	return $init_array;  
  
} 

//* * Customize TinyMCE's configuration
add_filter('tiny_mce_before_init','configure_tinymce');

function configure_tinymce($in) {
  $in['paste_preprocess'] = "function(plugin, args){
    // Strip all HTML tags except those we have whitelisted
    var whitelist = 'p,span,b,strong,i,em,h3,h4,h5,h6,ul,li,ol';
    var stripped = jQuery('<div>' + args.content + '</div>');
    var els = stripped.find('*').not(whitelist);
    for (var i = els.length - 1; i >= 0; i--) {
      var e = els[i];
      jQuery(e).replaceWith(e.innerHTML);
    }
    // Strip all class and id attributes
    stripped.find('*').removeAttr('id').removeAttr('class');
    // Return the clean HTML
    args.content = stripped.html();
  }";
  return $in;
}



// Footer
///////////////////////////////////////////////////////////////////////////////////////////////////

//* Change the footer text
add_filter('genesis_footer_creds_text', 'sp_footer_creds_filter');
function sp_footer_creds_filter( $creds ) {
	//$creds = '[footer_copyright] <a href="'.get_stylesheet_directory_uri().'">Audio Video Design</a>';
	$creds = '[footer_copyright] MacDougall';
	return $creds;
	//&middot;- Plumbing & Mechanical LLC. All Rights Reserved.
}


//* Add Footer Widget Areas

add_action( 'genesis_before_footer', 'footer_widget_areas', 12 );
function footer_widget_areas() {
echo '<span class="site-footer-wigets-border"></span>';
echo '<div class="site-footer-widgets">';

echo '<div class="one-third first">';
dynamic_sidebar( 'footer-widget-area-1');
echo '</div>';

echo '<div class="one-third">';
dynamic_sidebar( 'footer-widget-area-2');
echo '</div>';

echo '<div class="one-third">';
dynamic_sidebar( 'footer-widget-area-3');
echo '</div>';

echo '<div class="layout-full-width clear">';
dynamic_sidebar( 'footer-widget-area-4');
echo '</div>';

echo '<div class="layout-full-width clear">';
dynamic_sidebar( 'footer-widget-area-5');
echo '</div>';

/*
echo '<div class="one-third">';
dynamic_sidebar( 'footer-widget-area-6');
echo '</div>';
*/

echo '</div>'; //end of section

}





// Soliloquy
///////////////////////////////////////////////////////////////////////////////////////////////////
//* Add white label to Soliloquy Slider
add_filter( 'gettext', 'tgm_soliloquy_whitelabel', 10, 3 );
function tgm_soliloquy_whitelabel( $translated_text, $source_text, $domain ) {

    // If not in the admin, return the default string.
    if ( ! is_admin() ) {
        return $translated_text;
    }

    if ( strpos( $source_text, 'Soliloquy Slider' ) !== false ) {
        return str_replace( 'Soliloquy Slider', 'Slider', $translated_text );
    }

    if ( strpos( $source_text, 'Soliloquy Sliders' ) !== false ) {
        return str_replace( 'Soliloquy Sliders', 'Sliders', $translated_text );
    }

    if ( strpos( $source_text, 'Soliloquy slider' ) !== false ) {
        return str_replace( 'Soliloquy slider', 'slider', $translated_text );
    }

    if ( strpos( $source_text, 'Soliloquy' ) !== false ) {
        return str_replace( 'Soliloquy', 'Slider', $translated_text );
    }

    return $translated_text;

}



// Removing Elements
///////////////////////////////////////////////////////////////////////////////////////////////////


// Removing admin tabs
function remove_menus(){
  
  remove_menu_page( 'edit.php' );                   //Posts
  remove_menu_page( 'edit-comments.php' );          //Comments
  //remove_menu_page( 'themes.php' );                 //Appearance
  //remove_menu_page( 'plugins.php' );                //Plugins
  //remove_menu_page( 'users.php' );                  //Users
  //remove_menu_page( 'tools.php' );                  //Tools
  //remove_menu_page( 'options-general.php' );        //Settings
  
}
add_action( 'admin_menu', 'remove_menus' );


/* Unregister sidebar/content layout setting
genesis_unregister_layout( 'sidebar-content' );
*/

/* Unregister content/sidebar layout setting
genesis_unregister_layout( 'content-sidebar' );
*/

//* Unregister content/sidebar/sidebar layout setting
genesis_unregister_layout( 'content-sidebar-sidebar' );
 
//* Unregister sidebar/sidebar/content layout setting
genesis_unregister_layout( 'sidebar-sidebar-content' );
 
//* Unregister sidebar/content/sidebar layout setting
genesis_unregister_layout( 'sidebar-content-sidebar' );

//* Unregister secondary sidebar
unregister_sidebar( 'sidebar-alt' );





// Misc
///////////////////////////////////////////////////////////////////////////////////////////////////

//* Add HTML5 markup structure
add_theme_support( 'html5', array( 'caption', 'comment-form', 'comment-list', 'gallery', 'search-form' ) );


//* Add Accessibility support
add_theme_support( 'genesis-accessibility', array( '404-page', 'drop-down-menu', 'headings', 'rems', 'search-form', 'skip-links' ) );


//* Add viewport meta tag for mobile browsers
add_theme_support( 'genesis-responsive-viewport' );


/** Remove Edit Link */
add_filter( 'edit_post_link', '__return_false' );



/* Customize the post info function
add_filter( 'genesis_post_info', 'sp_post_info_filter' );
function sp_post_info_filter($post_info) {
if ( !is_page() ) {
	$post_info = '[post_date][post_edit]';
	return $post_info;
}}
*/


//* Display a custom Gravatar
add_filter( 'avatar_defaults', 'sp_gravatar' );
function sp_gravatar ($avatar) {
	$custom_avatar = get_stylesheet_directory_uri() . '/images/macdougall-favicon.png';
	$avatar[$custom_avatar] = "Custom Gravatar";
	return $avatar;
}

//* Add favicon
add_filter( 'genesis_pre_load_favicon', 'sp_favicon_filter' );
function sp_favicon_filter( $favicon_url ) {
	return ''.get_stylesheet_directory_uri().'/images/favicon.png';
}

//* Check if a page is child page
function is_child($pageid) { 
    global $post; 
    if( is_page() && ($post->post_parent == $pageid) ) {
       return true; // is child
    } else { 
       return false; // not child
    }
}

//* Set Press & News Releases sidebar for single and archive pages of Press releases CPT
add_action('get_header','ins_change_genesis_sidebar');
function ins_change_genesis_sidebar() {
    if ( is_singular('press_releases') || is_archive('press_releases') ) { // Check if we're on a single post for my CPT called "'press_releases'"
        remove_action( 'genesis_sidebar', 'genesis_do_sidebar' ); //remove the default genesis sidebar
        remove_action( 'genesis_sidebar', 'ss_do_sidebar' ); //remove ss genesis sidebar
        add_action( 'genesis_sidebar', 'ins_do_sidebar' ); //add an action hook to call the function for my custom sidebar
    }
}

//Function to output my custom sidebar
function ins_do_sidebar() {
	dynamic_sidebar( 'press-releases-news' );
}


//* Remove unused genesis templates 

function be_remove_genesis_page_templates( $page_templates ) {
	unset( $page_templates['page_archive.php'] );
	unset( $page_templates['page_blog.php'] );
	return $page_templates;
}
add_filter( 'theme_page_templates', 'be_remove_genesis_page_templates' );



/* Hide ACF WP Menu Item
add_filter('acf/settings/show_admin', '__return_false');
*/

