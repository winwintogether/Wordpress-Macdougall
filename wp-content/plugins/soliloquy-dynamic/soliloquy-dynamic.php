<?php
/**
 * Plugin Name: Soliloquy - Dynamic Addon
 * Plugin URI:  http://soliloquywp.com
 * Description: Enables Dynamic sliders in Soliloquy.
 * Author:      Soliloquy Team
 * Author URI:  http://soliloquywp.com
 * Version:     2.3.2
 * Text Domain: soliloquy-dynamic
 * Domain Path: languages
 *
 * Soliloquy is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Soliloquy is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Soliloquy. If not, see <http://www.gnu.org/licenses/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main plugin class.
 *
 * @since 2.1.9
 *
 * @package Soliloquy_Dynamic
 * @author  Tim Carr
 */
class Soliloquy_Dynamic {

    /**
     * Holds the class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Plugin version, used for cache-busting of style and script file references.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $version = '2.3.2';

    /**
     * The name of the plugin.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $plugin_name = 'Soliloquy Dynamic';

    /**
     * Unique plugin slug identifier.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $plugin_slug = 'soliloquy-dynamic';

    /**
     * Plugin file.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the plugin textdomain.
        add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

        // Load the plugin.
        add_action( 'soliloquy_init', array( $this, 'init' ), 99 );
        
    }

    /**
     * Loads the plugin textdomain for translation.
     *
     * @since 1.0.0
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain( $this->plugin_slug, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

    }
    
    /**
     * Fired when the plugin is activated.
     *
     * @since 1.0.0
     *
     * @global object $wpdb         The WordPress database object.
     * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false otherwise.
     */
    public function activate( $network_wide = false ) {
        
        // Bail if the main class does not exist.
        if ( ! class_exists( 'Soliloquy' ) ) {
            return;
        }
        
        // Check if we are on a multisite install, activating network wide, or a single install
        if ( is_multisite() && $network_wide ) {
            // Multisite network wide activation
            // Iterate through each blog in multisite, creating dynamic slider if needed
            global $wpdb;
            $site_list = $wpdb->get_results( "SELECT * FROM $wpdb->blogs ORDER BY blog_id" );
            foreach ( (array) $site_list as $site ) {
                switch_to_blog( $site->blog_id );
                $this->generate_dynamic_slider();
                restore_current_blog();
            }
        } else {
            // Single Site - create dynamic slider if needed
            $this->generate_dynamic_slider();
        }
            
    }
    
    /**
    * Checks if a Soliloquy Dynamic Slider already exists. If not, a dynamic slider is created.
    *
    * @since 2.1.9
    */
    public function generate_dynamic_slider() {
        
        global $wpdb;

        // Get Soliloquy Common Instance
        $instance = Soliloquy_Common::get_instance();
        
        // Generate the custom slider options holder for default dynamic galleries if it does not exist.
        $query = $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_name = '%s' AND post_type = '%s' LIMIT 1", 
                                 'soliloquy-dynamic-slider', 
                                 'soliloquy' );
        $exists = $wpdb->get_var( $query );
        if ( !is_null( $exists ) ) {
            update_option( 'soliloquy_dynamic', $exists );
            return;
        }
        
        // Dynamic slider does not exist - create it
        $args = array(
            'post_type'   => 'soliloquy',
            'post_name'   => 'soliloquy-dynamic-slider',
            'post_title'  => esc_attr__( 'Soliloquy Dynamic Slider', 'soliloquy-dynamic' ),
            'post_status' => 'publish'
        );
        $dynamic_id = wp_insert_post( $args );
        
        // If successful, update our option so that we can know which slider is dynamic.
        if ( $dynamic_id ) {
            update_option( 'soliloquy_dynamic', $dynamic_id );

            // Loop through the defaults and prepare them to be stored.
            $defaults = $instance->get_config_defaults( $dynamic_id );
            foreach ( $defaults as $key => $default ) {
                $fields['config'][$key] = $default;
            }

            // Update some default post meta fields.
            $fields = array(
                'id'     => $dynamic_id,
                'config' => array(
                    'title'   => esc_attr__( 'Soliloquy Dynamic Slider', 'soliloquy-dynamic' ),
                    'slug'    => 'soliloquy-dynamic-slider',
                    'classes' => array( 'soliloquy-dynamic-slider' ),
                    'type'    => 'dynamic'
                ),
                'slider' => array(),
            );

            // Update the meta field.
            update_post_meta( $dynamic_id, '_sol_slider_data', $fields );
        }
        
    }
    
    /**
     * Fired when the plugin is uninstalled.
     *
     * @since 1.0.0
     *
     * @global object $wpdb The WordPress database object.
     */
    function deactivate() {
    
        // Bail if the main class does not exist.
        if ( ! class_exists( 'Soliloquy' ) ) {
            return;
        }
        
        // Check if we are on a multisite install, activating network wide, or a single install
        if ( is_multisite() ) {
	        
            // Multisite network wide activation           
			if ( is_plugin_active_for_network( 'soliloquy-dynamic/soliloquy-dynamic.php' ) ) {
			    // Plugin is activated
				global $wpdb;
				
	            $site_list = $wpdb->get_results( "SELECT * FROM $wpdb->blogs ORDER BY blog_id" );
				
				// Iterate through each blog in multisite, removing dynamic slider if needed
	            foreach ( (array) $site_list as $site ) {
	                switch_to_blog( $site->blog_id );
	                $this->remove_dynamic_slider();
	                restore_current_blog();
	            }
            
            }else{
	            
	            $current_blog = get_current_blog_id();    
	            
	            switch_to_blog( $current_blog ); 
	         
				$this->remove_dynamic_slider();
	            
	            restore_current_blog();

            }
            
        } else {
            // Single Site - remove dynamic slider if needed
            $this->remove_dynamic_slider();
        }
    
    }
    
    /**
    * Removes the dynamic slider
    *
    * @since 2.1.9
    */
    public function remove_dynamic_slider() {
        
        // Grab the dynamic slider ID and use that to delete the slider.
        $dynamic_id = get_option( 'soliloquy_dynamic' );
        if ( $dynamic_id ) {
            wp_delete_post( $dynamic_id, true );
        }

        // Delete the option.
        delete_option( 'soliloquy_dynamic' );
        
    }

    /**
     * Loads the plugin into WordPress.
     *
     * @since 2.1.9
     */
    public function init() {

        // Display a notice if Soliloquy does not meet the proper version to run the addon.
        if ( version_compare( Soliloquy::get_instance()->version, '2.0.2', '<' ) ) {
            add_action( 'admin_notices', array( $this, 'version_notice' ) );
            return;
        };

        // Load admin only components.
        if ( is_admin() ) {
            $this->require_admin();
        }

        // Load global components.
        $this->require_global();

        // Load the updater
        add_action( 'soliloquy_updater', array( $this, 'updater' ) );

    }

    /**
     * Outputs a notice if Soliloquy doesn't meet the required minimum version
     *
     * @since 2.1.9
     */
    public function version_notice() {

        ?>
        <div class="error">
            <p><?php printf( esc_attr__( 'The <strong>%s</strong> requires Soliloquy 2.0.2 or later to work. Please update Soliloquy to use this addon.', 'soliloquy-dynamic' ), SOLILOQUY_DYNAMIC_PLUGIN_NAME ); ?></p>
        </div>
        <?php

    }

    /**
     * Loads all admin related files into scope.
     *
     * @since 1.0.0
     */
    public function require_admin() {

        require plugin_dir_path( __FILE__ ) . 'includes/admin/metaboxes.php';
        require plugin_dir_path( __FILE__ ) . 'includes/admin/table.php';

    }

    /**
     * Initializes the addon updater.
     *
     * @since 2.1.9
     *
     * @param string $key The user license key.
     */
    function updater( $key ) {

        $args = array(
            'plugin_name' => $this->plugin_name,
            'plugin_slug' => $this->plugin_slug,
            'plugin_path' => plugin_basename( __FILE__ ),
            'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . $this->plugin_slug,
            'remote_url'  => 'http://soliloquywp.com/',
            'version'     => $this->version,
            'key'         => $key
        );
        $this->updater = new Soliloquy_Updater( $args );

    }

    /**
     * Loads all global files into scope.
     *
     * @since 2.1.9
     */
    public function require_global() {

        require plugin_dir_path( __FILE__ ) . 'includes/global/common.php';
        require plugin_dir_path( __FILE__ ) . 'includes/global/shortcode.php';
        
    }

     /**
     * Returns the singleton instance of the class.
     *
     * @since 2.1.9
     *
     * @return object The Soliloquy_Dynamic object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Soliloquy_Dynamic ) ) {
            self::$instance = new Soliloquy_Dynamic();
        }

        return self::$instance;

    }

}

// Load the main plugin class.
$soliloquy_dynamic = Soliloquy_Dynamic::get_instance();

// Register activation and deactivation hooks
register_activation_hook( __FILE__, array( &$soliloquy_dynamic, 'activate' ) );
register_deactivation_hook( __FILE__, array( &$soliloquy_dynamic, 'deactivate' ) );