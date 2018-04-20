<?php
/**
 * Dynamic Shortcode class.
 *
 * @since 2.1.9
 *
 * @package Soliloquy_Dynamic
 * @author  Tim Carr
 */
class Soliloquy_Dynamic_Shortcode {

    /**
     * Holds the class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $base;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

    	// Register custom shortcode
    	add_shortcode( 'soliloquy_dynamic', array( $this, 'shortcode' ) );

    	// Filters
    	add_filter( 'soliloquy_custom_slider_data', array( $this, 'parse_shortcode_attributes' ), 10, 3 ) ;
    	add_filter( 'soliloquy_pre_data', array( $this, 'inject_slides' ), 10, 3 );
    	add_filter( 'post_gallery', array( $this, 'override_gallery' ), 9999, 2 );

        // Register Filters for Dynamic Types
        add_filter( 'soliloquy_dynamic_get_custom_images', array( $this, 'get_custom_images' ), 10, 3 );
        add_filter( 'soliloquy_dynamic_get_nextgen_images', array( $this, 'get_nextgen_images' ), 10, 3 );
        add_filter( 'soliloquy_dynamic_get_folder_images', array( $this, 'get_folder_images' ), 10, 3 );
        add_filter( 'soliloquy_dynamic_get_envira_images', array( $this, 'get_envira_images' ), 10, 3 );

    }

    /**
     * Returns an array of Dynamic Slider Types
     * Defaults: Media Library Images, NextGen Gallery Images and Soliloquy Images
     *
     * Other Addons can add to this list of types and then define their own actions for grabbing the images for
     * insertion into the Dynamic Slider.
     *
     * @since 2.1.9
     *
     * @return array Types
     */
    public function get_dynamic_slider_types() {

    	// Build array of default types
    	// key = WordPress Filter, value = preg_match statement
    	$types = array(
    		'soliloquy_dynamic_get_custom_images' 	=> '#^custom-#',
    		'soliloquy_dynamic_get_nextgen_images' 	=> '#^nextgen-#',
    		'soliloquy_dynamic_get_folder_images'	=> '#^folder-#',
    		'soliloquy_dynamic_get_envira_images'	=> '#^envira-#',
    	);

    	// Filter types
    	$types = apply_filters( 'soliloquy_dynamic_get_dynamic_slider_types', $types );

    	return $types;

    }
    		
    /**
	 * Parses the Dynamic Slider attributes and filters them into the data.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $bool   Boolean (false) since no data is found yet.
	 * @param array $atts  Array of shortcode attributes to parse.
	 * @param object $post The current post object.
	 * @return array $data Array of dynamic gallery data.
	 */
    public function parse_shortcode_attributes( $bool, $atts, $post ) {
		
	    // If the dynamic attribute is not set to true, do nothing.
	    if ( empty( $atts['dynamic'] ) ) {
	        return $bool;
	    }

	    // Now that we have a dynamic slider, prepare atts to be parsed with defaults.
	    if ( isset( $atts['default'] ) ){
		    
		    $defaults   = get_post_meta( $atts['default'], '_sol_slider_data', true );	
		    
		    //reset and remove some of the stuff from the original slider
			$defaults['config']['type'] = 'dynamic';
			$defaults['id'] = '';
			$defaults['slider'] = array();
			
	    }else{
		    
		    $dynamic_id = Soliloquy_Dynamic_Common::get_instance()->get_dynamic_id();
		    $defaults   = get_post_meta( $dynamic_id, '_sol_slider_data', true );
	    
	    }
	    $data       = array();
	    foreach ( (array) $atts as $key => $value ) {
	        // Cast any 'true' or 'false' atts to a boolean value.
	        if ( 'true' == $value ) {
	            $atts[$key] = 1;
	            $value      = 1;
	        }
	
	        if ( 'false' == $value ) {
	            $atts[$key] = 0;
	            $value      = 0;
	        }
	
	        // Store data
	        $data[ $key ] = $value;
	    }
	
	    // If the data is empty, return false.
	    if ( empty( $data ) || empty( $defaults ) ) {
	        return false;
	    }
	    
	    // Merge in the defaults into the data.
	    $config           = $defaults;
	    $config['id']     = str_replace( '-', '_', $atts['dynamic'] ); // Replace dashes with underscores.
	    $config_array     = $defaults['config'];
	    $parsed_array     = wp_parse_args( $data, $defaults['config'] );
	    $config['config'] = $parsed_array;

	    // Parse the args and return the data.
	    return apply_filters( 'soliloquy_dynamic_parsed_data', $config, $data, $defaults, $atts, $post );
	    
    }

    /**
	 * Injects slides into the given $data array, using the $data settings (i.e. the dynamic slider settings)
	 *
	 * @since 1.0.0
	 *
	 * @param array $data  Slider Config.
	 * @param int $id      The slider ID.
	 * @return array $data Amended array of slider config, with slides.
	 */
	function inject_slides( $data, $id ) {
		
	    // Return early if not a Dynamic slider.
	    $instance = Soliloquy_Shortcode::get_instance();
	    if ( 'dynamic' !== $instance->get_config( 'type', $data ) ) {
	        return $data;
	    }
	
	    // $id should be false, so we need to set it now.
	    if ( ! $id ) {
	        $id = $instance->get_config( 'dynamic', $data );
	    }
	    
	    /**
		* Get slides based on supplied Dynamic settings
	    * Checks for:
	    * - Media Library Image IDs: [soliloquy-dynamic id="custom-xxx" images="id,id,id"]
	    * - NextGen Gallery ID: [soliloquy-dynamic id="nextgen-id"]
	    * - Soliloquy ID: [soliloquy-dynamic id="envira-id"]
	    * - Folder: [soliloquy-dynamic id="folder-foldername"]
	    */
	    $dynamic_data = array();
	    $rule_matched = false;
	    $types = $this->get_dynamic_slider_types();
	    foreach ( $types as $filter_to_execute => $preg_match ) {
	    	if ( preg_match( $preg_match, $id ) ) {
	    		// Run action for this preg_match
	    		$rule_matched = true;
	    		$dynamic_data = apply_filters( $filter_to_execute, $dynamic_data, $id, $data );
	    		break;
	    	}
	    }

	    /**
		* Get images based on supplied Dynamic settings
	    * Checks for:
	    * - Post/Page ID: [soliloquy-dynamic id="id" exclude="id,id,id"]
	    */
		if ( ! $rule_matched ) {
			$exclude      = ! empty( $data['config']['exclude'] ) ? $data['config']['exclude'] : false;
	        $images       = $this->get_attached_images( $id, $exclude );
	        $dynamic_data = $this->get_custom_images( $dynamic_data, $id, $data, implode( ',', (array) $images ) );
		}

		// Filter 
		$data = apply_filters( 'soliloquy_dynamic_queried_data', $data, $id, $dynamic_data );
		
		// Check image(s) were found
		if ( count( $dynamic_data ) == 0 ) {
			// No images found, nothing to inject - just return data
			return $data;
		}

		// Images found - insert into data
		$data['slider'] = $dynamic_data;

	    // Return the modified data.
	    return apply_filters( 'soliloquy_dynamic_data', $data, $id );

	}
	
	/**
	* Retrieves the image data for custom image sets
	*
	* @param array $dynamic_data 	Existing Dynamic Data Array
	* @param int $id				ID (either custom-ID or Page/Post ID)
	* @param array $data			Slider Configuration
	* @param bool|array $images		Array of image IDs to use (optional)
	* @return bool|array			Array of data on success, false on failure
	*/
	public function get_custom_images( $dynamic_data, $id, $data, $images = false ) {

		// Image IDs will be set in either:
		// - 1. $data['config']['images'] (by parse_shortcode_attributes())
		// - 2. $images array (passed to this function)
		$instance    = Soliloquy_Shortcode::get_instance();
	    $data_images = $instance->get_config( 'images', $data );
	    if ( ! $data_images ) {
	        if ( ! $images ) {
		        // No images specified matching (1) or (2) above - bail
	            return false;
	        }
	    } else {
	        $images = $data_images;
	    }
	    
	    // $images now reflects the exact images we want to include in the Slider
	    // Convert it to an array
	    $images     = explode( ',', (string) $images );

	    // There may be some links also specified - store these for later use in the loop
	    $data_links = $instance->get_config( 'links', $data );
	    if ( $data_links !== false && ! empty( $data_links ) ) {
	    	$data_links = explode( ',', (string) $data_links );
	    }
	    
	    $data_captions =  $instance->get_config( 'captions', $data );
	    
	    if ( $data_captions !== false && ! empty( $data_captions ) ) {
		    
	    	$data_captions = explode( ',', (string) $data_captions );
	    
	    }
	    
	    // Iterate through images
	    foreach ( (array) $images as $i => $image_id ) {
		    
	    	// Check if Image ID is numeric or not
	    	if ( is_numeric( $image_id ) ) {
	    		/**
	    		* Media Library
	    		*/

				// Get image attachment and check it exists
	    		$attachment = get_post( $image_id );
			    if ( ! $attachment ) {
			    	continue;
			    }

			    // Get image source
			    $src = wp_get_attachment_image_src( $image_id, 'full' ); // Image ID in Media Library

			    // Build image attributes to match Soliloquy
			    $dynamic_data[ $image_id ] = array(
					'src' 				=> ( isset( $src[0] ) ? esc_url( $src[0] ) : '' ),
					'title' 			=> $attachment->post_title,
					'link' 				=> '',
					'alt' 				=> get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
					'caption' 			=> $attachment->post_excerpt,
			    );

			    // Set the link property based on $data variable.
		        $link = $instance->get_config( 'link', $data );
		        if ( $link ) {
		            if ( 'file' == $link ) {
		                $dynamic_data[ $image_id ]['link'] = ! empty( $src[0] ) ? $src[0] : '';
		            } else if ( 'attachment' == $link ) {
		                $dynamic_data[ $image_id ]['link'] = get_attachment_link( $image_id );
		            } else if ( 'post' == $link ) {
		                $dynamic_data[ $image_id ]['link'] = get_permalink( $attachment->post_parent );
		            }
		        }

		        // If link is still empty, check our $data_links array to see if the user has manually specified a link
		        if ( is_array( $data_links ) ) {
			        
			        if ( isset( $data_links[ $i ] ) ){
			        
		        		$dynamic_data[ $image_id ]['link'] = $data_links[ $i ];
		        	
		        	}
		        	
		        }
		        
		        // If caption is still empty, check our $data_captions array to see if the user has manually specified a caption
		        if ( is_array( $data_captions ) ) {
			        
			        if ( isset( $data_captions[ $i ] ) ){
				        
		        		$dynamic_data[ $image_id ]['caption'] = $data_captions[ $i ];
		        	
		        	}
		        }
		        
	    	} else {
	    		/**
	    		* URL?
	    		*/

	    		// Check if an image URL
	    		if ( ! filter_var( $image_id, FILTER_VALIDATE_URL ) ) {
	    			continue;
	    		}

	    		// Build image attributes to match Soliloquy
			    $dynamic_data[ $image_id ] = array(
					'src' 				=> $image_id,
					'title' 			=> '',
					'link' 				=> $image_id,
					'alt' 				=> '',
					'caption' 			=> '',
			    );
			    
		        // If link is still empty, check our $data_links array to see if the user has manually specified a link
		        if ( is_array( $data_links ) ) {
			        
		        	$dynamic_data[ $image_id ]['link'] = $data_links[ $i ];
		        }
		        
		        // If caption is still empty, check our $data_captions array to see if the user has manually specified a caption
		        if ( is_array( $data_captions ) ) {
			        
		        	$dynamic_data[ $image_id ]['caption'] = $data_captions[ $i ];
		        	
		        }			    
	    	}
	    }

	    return apply_filters( 'soliloquy_dynamic_custom_image_data', $dynamic_data, $id, $data );
	    
	}
	
	/**
	* Retrieves the image data for a given NextGen Gallery ID
	*
	* @param array $dynamic_data 	Existing Dynamic Data Array
	* @param int $id				NextGen Gallery ID
	* @param array $data			Slider Configuration
	* @return bool|array			Array of data on success, false on failure
	*/
	public function get_nextgen_images( $dynamic_data, $id, $data ) {

		// Return false if the NextGen database class is not available.
	    if ( ! class_exists( 'nggdb' ) ) {
	        return false;
	    }

	    // Get shortcode instance
	    $instance    = Soliloquy_Shortcode::get_instance();

	    // Get NextGen Gallery ID
	    $nextgen_id   = explode( '-', $id );
	    $id = $nextgen_id[1];
	    
	    // Get NextGen Gallery Objects
	    $nggdb = new nggdb();
	    $objects = apply_filters( 'soliloquy_dynamic_get_nextgen_image_data', $nggdb->get_gallery( $id ), $id );
	    
	    // Return if no objects found
	    if ( ! $objects ) {
		    return false;
	    }

		// Build gallery
		foreach ( (array) $objects as $key => $object ) {
			// Depending on the NextGEN version, the structure of the object will vary
			if ( ! isset( $object->_ngiw ) ) {
				// Get path for gallery
				if ( ! isset( $nextgen_gallery_path ) ) {
					global $wpdb;
					$nextgen_gallery_path = $wpdb->get_row( $wpdb->prepare( "SELECT path FROM $wpdb->nggallery WHERE gid = %d", $id) );
				}

				$image = $object->_orig_image;
				$image_url = get_bloginfo( 'url' ) . '/' . $nextgen_gallery_path->path . '/' . str_replace( ' ', '%20', $image->filename );
			} else {
				$image = $object->_ngiw->_orig_image;
				$image_url = get_bloginfo( 'url' ) . '/' . $image->path . '/' . str_replace( ' ', '%20', $image->filename );
			}

			// Build image attributes to match Soliloquy
		    $dynamic_data[ $image->pid ] = array(
				'src' 				=> $image_url,
				'title' 			=> ( isset( $image->alttext ) ? strip_tags( esc_attr( $image->alttext ) ) : '' ),
				'link' 				=> '',
				'alt' 				=> ( isset( $image->alttext ) ? strip_tags( esc_attr( $image->alttext ) ) : '' ),
				'caption' 			=> ( isset( $image->description ) ? $image->description : '' ),
		    ); 

		    // Set the link property based on $data variable.
	        $link = $instance->get_config( 'link', $data );
	        if ( $link ) {
	            if ( 'file' == $link || 'attachment' == $link ) {
	                $dynamic_data[ $image->pid ]['link'] = isset( $image_url ) ? esc_url( $image_url ) : '';
	            }
	        }

		}

	    return apply_filters( 'soliloquy_dynamic_nextgen_images', $dynamic_data, $objects, $id, $data );
	    
	}

	/**
	* Retrieves the image data for a given Soliloquy ID
	*
	* @param array $dynamic_data 	Existing Dynamic Data Array
	* @param int $id				Soliloquy ID
	* @param array $data			Slider Configuration
	* @return bool|array			Array of data on success, false on failure
	*/
	public function get_envira_images( $dynamic_data, $id, $data ) {

		// Return false if Envira is not available.
	    if ( ! class_exists( 'Envira_Gallery' ) ) {
	        return false;
	    }

	    // Get Soliloquy ID
	    $envira_id   = explode( '-', $id );
	    $id = $envira_id[1];

		// Get Soliloquy
	    $envira_gallery = Envira_Gallery::get_instance();
	    $gallery_data = apply_filters( 'soliloquy_dynamic_get_envira_image_data', $envira_gallery->get_gallery( $id ), $id );
	    
	    if ( ! $gallery_data ) {
	        return false;
	    }
	    if ( ! isset( $gallery_data['gallery'] ) ) {
	        return false;
	    }

	    $image_data = array();

	    // Loop through the gallery images and prepare the data
	    foreach( (array) $gallery_data['gallery'] as $attachment_id => $image ) {
	        $image_data[ $attachment_id ] = $image;
	        unset(  $image_data[ $attachment_id ]['status'],
	                $image_data[ $attachment_id ]['thumb'] );
	    }

	    return apply_filters( 'soliloquy_dynamic_envira_images', $image_data, $gallery_data, $id, $data );
	    
	}

	/**
	* Retrieves the image data for a given folder inside the wp-content folder
	*
	* @param array $dynamic_data 	Existing Dynamic Data Array
	* @param string $folder			Directory Name
	* @param array $data			Gallery Configuration
	* @return bool|array			Array of data on success, false on failure
	*/
	public function get_folder_images( $dynamic_data, $folder, $data ) {

		// Get any instances we want to use
		$instance = Soliloquy_Shortcode::get_instance();

		// Get folder
		$folder_parts = explode( '-', $folder );
		$folder = '';
		foreach ( $folder_parts as $i => $folder_part ) {
			// Skip first string (= folder)
			if ( $i == 0 ) {
				continue;
			}

			// Add to folder string
			$folder .= '/' . $folder_part;
		}

		// Check directory exists
		$folder_path = WP_CONTENT_DIR . $folder;
		$folder_url = WP_CONTENT_URL . $folder;
		if ( ! file_exists( $folder_path ) ) {
			return false;
		}
		
		// Get all files from the folder
		$h = opendir( $folder_path );
		$files = array();
		while( $file = readdir( $h ) ) {
			$files[] = $file;		
		}
		
		// Get all images from $files
		$images = preg_grep( '/\.(jpg|jpeg|png|gif)(?:[\?\#].*)?$/i', $files );

		// Check we have at least one image
		if ( count( $images ) == 0 ) {
			return false;
		}
		
		// Build gallery
	    foreach ( (array) $images as $i => $image_filename ) {
		    
		    // Get file path and URL
		    $file_path = $folder_path . '/' . $image_filename;
		    $file_url = $folder_url . '/' . $image_filename;
		    
		    // Get file info
		    $info = pathinfo( $folder_path . '/' . $image_filename );
			$ext  = $info['extension'];
			$name = wp_basename( $file_path, ".$ext" );

		    // If the current file we are on is a resized file, don't include it in the results
			// Gallery
			$suffix = '-' . $instance->get_config( 'slider_width', $data ) . 'x' . $instance->get_config( 'slider_height', $data ) . ( $instance->get_config( 'slider', $data ) ? '_c' : '' ) . '.' . $ext;
			if ( strpos( $image_filename, $suffix ) !== false ) {
				continue;
			}

			// Mobile
			$suffix = '-' . $instance->get_config( 'mobile_width', $data ) . 'x' . $instance->get_config( 'mobile_height', $data ) . ( $instance->get_config( 'slider', $data ) ? '_c' : '' ) . '.' . $ext;
			if ( strpos( $image_filename, $suffix ) !== false ) {
				continue;
			}

			// Lightbox Thumbnails
			$suffix = '-' . $instance->get_config( 'lightbox_twidth', $data ) . 'x' . $instance->get_config( 'lightbox_theight', $data ) . '_c.' . $ext;
			if ( strpos( $image_filename, $suffix ) !== false ) {
				continue;
			}

			// Build image attributes to match Soliloquy
		    $dynamic_data[ $i ] = array(
		    	'status'			=> 'published',
				'src' 				=> $file_url,
				'title' 			=> '',
				'link' 				=> '',
				'alt' 				=> '',
				'caption' 			=> '',
		    ); 

	    }

	    return apply_filters( 'soliloquy_dynamic_folder_images', $dynamic_data, $files, $folder, $data );
			
	}
	
	/**
	* Retrieves the image data for images attached to the given Post/Page/CPT ID
	*
	* @param int $id			Post/Page/CPT ID
	* @param array $data		Slider Configuration
	* @param string $fields		Fields to return
	* @return bool|array		Array of data on success, false on failure
	*/
	public function get_attached_images( $post_id, $exclude, $fields = 'ids' ) {
		
		// Prepare query args.
	    $args = array(
	        'orderby'        => 'menu_order',
	        'order'          => 'ASC',
	        'post_type'      => 'attachment',
	        'post_parent'    => $post_id,
	        'post_mime_type' => 'image',
	        'post_status'    => null,
	        'posts_per_page' => -1,
	        'fields'         => $fields
	    );

	    // Add images to exclude if necessary.
	    if ( $exclude ) {
	        $args['post__not_in'] = (array) explode( ',', $exclude );
	    }

	    // Allow args to be filtered and then query the images.
	    $args   = apply_filters( 'soliloquy_dynamic_attached_image_args', $args, $post_id, $fields, $exclude );
	    $images = get_posts( $args );

	    // If no images are found, return false.
	    if ( ! $images ) {
	        return false;
	    }

	    return apply_filters( 'soliloquy_dynamic_attached_images', $images, $post_id, $exclude, $fields );
		
	}
	
	/**
	* Overrides the default WordPress Gallery with an Soliloquy
	*
	* @since 1.0.0
	*
	* @param string $html HTML
	* @param array $atts Attributes
	* @return string HTML
	*/
	function override_gallery( $html, $atts ) {

	    // If there is no Soliloquy attribute or we want to stop the slider output, return the default gallery output.
	    if ( empty( $atts['soliloquy'] ) || apply_filters( 'soliloquy_dynamic_pre_gallery', false ) ) {
	        return $html;
	    }

	    // Declare a static incremental to ensure unique IDs when multiple sliders are called.
	    global $post;
	    static $dynamic_i = 0;

	    // Either grab custom images or images attached to the post.
	    $images = false;
	    if ( ! empty( $atts['ids'] ) ) {
	        $images = $atts['ids'];
	    } else {
	        if ( empty( $post->ID ) ) {
	            return $html;
	        }

	        $exclude = ! empty( $atts['exclude'] ) ? $atts['exclude'] : false;
	        $images  = $this->get_attached_images( $post->ID, $exclude );
	    }

	    // If no images have been found, return the default HTML.
	    if ( ! $images ) {
	        return $html;
	    }

	    // Set the shortcode atts to be passed into shortcode regardless.
	    $args           = array();
	    $args['images'] = implode( ',', (array) $images );
	    $args['link']   = ! empty( $atts['link'] ) ? $atts['link'] : 'none';

	    // Check if the soliloquy_args attribute is set and parse the query string provided.
	    if ( ! empty( $atts['soliloquy_args'] ) ) {
	        wp_parse_str( html_entity_decode( $atts['soliloquy_args'] ), $parsed_args );
	        $args = array_merge( $parsed_args, $args );
	        $args = apply_filters( 'soliloquy_dynamic_gallery_args', $args, $atts, $dynamic_i );
	    }

	    // Prepare the args to be output into query string shortcode format for the shortcode.
	    $output_args = '';
	    foreach ( $args as $k => $v ) {
	        $output_args .= $k . '=' . $v . ' ';
	    }

	    // Increment the static counter.
	    $dynamic_i++;

	    // Map to the new Soliloquy shortcode with the proper data structure.
	    return do_shortcode( '[soliloquy dynamic="custom-gallery-' . $dynamic_i . '" ' . trim( $output_args ) . ']' );
	
	}
	
	/**
	 * Registers the soliloquy-dynamic shortcode, changing it to [soliloquy dynamic...]
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Array of shortcode attributes.
	 */
	function shortcode( $atts ) {

		// If no ID, return false.
	    if ( empty( $atts['id'] ) ) {
	        return false;
	    }

	    // Pull out the ID and remove from atts.
	    $id = $atts['id'];
	    unset( $atts['id'] );

	    // Prepare the args to be output into query string shortcode format for the shortcode.
	    $output_args = '';
	    foreach ( $atts as $k => $v ) {
	        $output_args .= $k . '=' . $v . ' ';
	    }

	    // Map to the new Soliloquy shortcode with the proper data structure.
	    return do_shortcode( '[soliloquy dynamic="' . $id . '" ' . trim( $output_args ) . ']' );
	
	}
	    
    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Soliloquy_Dynamic_Shortcode object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Soliloquy_Dynamic_Shortcode ) ) {
            self::$instance = new Soliloquy_Dynamic_Shortcode();
        }

        return self::$instance;

    }

}

// Load the shortcode class.
$soliloquy_dynamic_shortcode = Soliloquy_Dynamic_Shortcode::get_instance();

// Conditionally load the template tag.
if ( ! function_exists( 'soliloquy_dynamic' ) ) {
    /**
     * Template tag function for outputting dynamic sliders with Soliloquy.
     *
     * @since 1.0.0
     *
     * @param array $args  Args used for the slider init script.
     * @param bool $return Flag for returning or echoing the slider content.
     */
    function soliloquy_dynamic( $args = array(), $return = false ) {

        // If no ID, return false.
        if ( empty( $args['id'] ) ) {
            return false;
        }

        // Pull out the ID and remove from args.
        $id = $args['id'];
        unset( $args['id'] );

        // Map to v2 template tag method of using dynamic sliders.
        soliloquy( $id, 'dynamic', $args, $return );

    }
}