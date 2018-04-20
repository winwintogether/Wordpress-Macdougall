<?php
/**
 * Addon Page class.
 *
 * @since 2.5
 *
 * @package Soliloquy
 * @author  Thomas Griffin
 */
 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
	
class Soliloquy_Addons{
	
	public static $instance;
	
	public $file = __FILE__;
	
	public $base;
	
	public $hook;
	
	public function __construct(){
		
        // Load the base class object.
        $this->base = Soliloquy::get_instance();

        // Add custom settings submenu.
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );

	}
	
	public function admin_menu(){
		
        // Register the submenu.
        $this->hook = add_submenu_page(
            'edit.php?post_type=soliloquy',
            esc_attr__( 'Soliloquy Addons', 'soliloquy' ),
            esc_attr__( 'Addons', 'soliloquy' ),
            apply_filters( 'soliloquy_menu_cap', 'manage_options' ),
            $this->base->plugin_slug . '-addons',
            array( $this, 'addons_page' )
        );

        // If successful, load admin assets only on that page and check for addons refresh.
        if ( $this->hook ) {
            add_action( 'load-' . $this->hook, array( $this, 'maybe_refresh_addons' ) );
			add_action( 'load-' . $this->hook, array( $this, 'addons_page_assets' ) );
        }		

	}
    /**
     * Loads assets for the settings page.
     *
     * @since 1.0.0
     */
    public function addons_page_assets() {

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

    }
    
	/**
	 * enqueue_admin_styles function.
	 * 
	 * @access public
	 * @return void
	 */
	public function enqueue_admin_styles(){
        wp_register_style( $this->base->plugin_slug . '-addons-style', plugins_url( 'assets/css/addons.css', $this->base->file ), array(), $this->base->version );
        wp_enqueue_style( $this->base->plugin_slug . '-addons-style' );

        // Run a hook to load in custom styles.
        do_action( 'soliloquy_addons_styles' );		
	}
	
	/**
	 * enqueue_admin_scripts function.
	 * 
	 * @access public
	 * @return void
	 */
	public function enqueue_admin_scripts(){
      
        wp_enqueue_script( 'jquery-ui-tabs' );
        
	    wp_register_script( $this->base->plugin_slug . '-chosen', plugins_url( 'assets/js/min/chosen.jquery-min.js', $this->base->file ), array(), $this->base->version, true );
		wp_enqueue_script( $this->base->plugin_slug . '-chosen' );
		        
        wp_register_script( $this->base->plugin_slug . '-addons-script', plugins_url( 'assets/js/addons.js', $this->base->file ), array( 'jquery', 'jquery-ui-tabs' ), $this->base->version, true );
        wp_enqueue_script( $this->base->plugin_slug . '-addons-script' );
        wp_localize_script(
            $this->base->plugin_slug . '-addons-script',
            'soliloquy_addons',
            array(
                'active'           => esc_attr__( 'Active', 'soliloquy' ),
                'activate'         => esc_attr__( 'Activate', 'soliloquy' ),
                'activate_nonce'   => wp_create_nonce( 'soliloquy-activate' ),
                'activating'       => esc_attr__( 'Activating...', 'soliloquy' ),
                'ajax'             => admin_url( 'admin-ajax.php' ),
                'deactivate'       => esc_attr__( 'Deactivate', 'soliloquy' ),
                'deactivate_nonce' => wp_create_nonce( 'soliloquy-deactivate' ),
                'deactivating'     => esc_attr__( 'Deactivating...', 'soliloquy' ),
                'inactive'         => esc_attr__( 'Inactive', 'soliloquy' ),
                'install'          => esc_attr__( 'Install Addon', 'soliloquy' ),
                'install_nonce'    => wp_create_nonce( 'soliloquy-install' ),
                'installing'       => esc_attr__( 'Installing...', 'soliloquy' ),
                'proceed'          => esc_attr__( 'Proceed', 'soliloquy' ),
                'ajax'             => admin_url( 'admin-ajax.php' ),
                'redirect'         => esc_url( add_query_arg( array( 'post_type' => 'soliloquy', 'soliloquy-upgraded' => true ), admin_url( 'edit.php' ) ) ),
                'upgrade_nonce'    => wp_create_nonce( 'soliloquy-upgrade' )
            )
        );

        // Run a hook to load in custom scripts.
        do_action( 'soliloquy_addons_scripts' );		
	}
    /**
     * Maybe refreshes the addons page.
     *
     * @since 1.0.0
     *
     * @return null Return early if not refreshing the addons.
     */
    public function maybe_refresh_addons() {

        if ( ! $this->is_refreshing_addons() ) {
            return;
        }

        if ( ! $this->refresh_addons_action() ) {
            return;
        }

        if ( ! $this->base->get_license_key() ) {
            return;
        }

        $this->get_addons_data( $this->base->get_license_key() );

    }
    
    public function addons_page(){ 
		
        // Go ahead and grab the type of license. It will be necessary for displaying Addons.
		$type = $this->base->get_license_key_type();	    
		
		?>
	    
	    <div id="soliloquy-heading">
				<h1><?php esc_html_e( 'Soliloquy Addons', 'soliloquy' ); ?></h1>
	    </div>
		    
	    <div class="wrap">
	    
	    	<h1 class="soliloquy-hideme"></h1> <?php

			// Only display the Addons information if no license key errors are present.
			if ( ! $this->base->get_license_key_errors() ) : ?>
			
			<div id="soliloquy-settings-addons">
        	 
        	    <?php if ( empty( $type ) ) : ?>
        	    
        	        <div class="error below-h2">
	        	        
	        	        <p><?php esc_html_e( 'In order to get access to Addons, you need to verify your license key for Soliloquy.', 'soliloquy' ); ?></p>

					</div>
				
				<?php else : 
				
					$addons = $this->get_addons(); 

					if ( $addons ) : ?>
                
                    <form id="soliloquy-settings-refresh-addons-form" method="post">
                
                        <p><?php _e( '<strong>Missing addons that you think you should be able to see?</strong> Try clicking the button below to refresh the addon data.', 'soliloquy' ); ?><?php submit_button( esc_attr__( 'Refresh Addons', 'soliloquy' ), 'button-soliloquy-secondary', 'soliloquy-refresh-addons-submit', false ); ?>
</p>

                        <?php wp_nonce_field( 'soliloquy-refresh-addons', 'soliloquy-refresh-addons' ); ?>
   
                    </form>
                    
           	        <form id="soliloquy-addon-filters" class="soliloquy-right">
	           	        <label class="soliloquy-addon-filter"><?php esc_html_e( 'Filter', 'soliloquy'); ?>:</label>
	           	        <div class="soliloquy-select">
	        	        <select id="soliloquy-addon-filter" class="soliloquy-chosen">
		        	        <option value="asc"><?php esc_html_e( 'A-Z', 'soliloquy' ); ?></option>
		        	        <option value="desc"><?php esc_html_e( 'Z-A', 'soliloquy' ); ?></option>
		        	        <option value="active"><?php esc_html_e( 'Active', 'soliloquy' ); ?></option>
		        	        <option value="inactive"><?php esc_html_e( 'Inactive', 'soliloquy' ); ?></option>
		        	        <option value="installed"><?php esc_html_e( 'Not Installed', 'soliloquy' ); ?></option>

	        	        </select>
	           	        </div>
        	        
        	        </form>
        	        
        	        <div class="soliloquy-clearfix"></div>
        	        
                    <div id="soliloquy-addons-area" class="soliloquy-clear">
   
                        <?php
                        // Let's begin outputting the addons.
                        $i = 0;
                        foreach ( (array) $addons as $i => $addon ) {
                            // Attempt to get the plugin basename if it is installed or active.
                            $plugin_basename   = $this->get_plugin_basename_from_slug( $addon->slug );
                            $installed_plugins = get_plugins();
                            $last              = ( 2 == $i%3 ) ? 'last' : '';
   							if ( is_plugin_active( $plugin_basename ) ) {
   								$status = 'active';
   							}
                            
                           elseif ( ! isset( $installed_plugins[$plugin_basename] ) ) {
                            	$status = 'not_installed';

                            } elseif ( is_plugin_inactive( $plugin_basename ) ) {
	                            $status = 'inactive';

                            }
                            // If site is HTTPS, serve $addon->image as HTTPS too, this prevents warnings
                            if ( is_ssl() ) {
	                            $addon->image = str_replace( 'http://', 'https://', $addon->image );
                            }
							
							//ADD THE SPINNER
							//<span class="spinner soliloquy-spinner"></span>
                            
                            echo '<div class="soliloquy-addon ' . $last . '" data-addon-title="'. esc_html( $addon->title ) . '" data-addon-status="'. $status . '">';
                            
                            	echo '<div class="soliloquy-addon-content">';
                            
                            	    echo '<h3 class="soliloquy-addon-title">' . esc_html( $addon->title ) . '</h3>';

									echo '<img class="soliloquy-addon-thumb" src="' . esc_url( $addon->image ) . '" width="300px" height="250px" alt="' . esc_attr( $addon->title ) . '" />';

									echo '<p class="soliloquy-addon-excerpt">' . esc_html( $addon->excerpt ) . '</p>';
                              
                                echo '</div>';
							
								echo '<div class="soliloquy-addon-footer">';
								
                                // If the plugin is active, display an active message and deactivate button.
                                if ( is_plugin_active( $plugin_basename ) ) {
                                    echo '<div class="soliloquy-addon-active soliloquy-addon-message">';
                                        echo '<span class="addon-status-active addon-status">' . esc_html__( 'Status:', 'soliloquy' ) . '&nbsp;<span>' . esc_html__( 'Active', 'soliloquy' ) . '</span></span>';
                                        echo '<div class="soliloquy-addon-action">';
                                            echo '<a class="soliloquy-icon-toggle-on button button-soliloquy-secondary soliloquy-addon-action-button soliloquy-deactivate-addon" href="#" rel="' . esc_attr( $plugin_basename ) . '">' . esc_html__( 'Deactivate', 'soliloquy' ) . '</a>';
                                        echo '</div>';
                                    echo '</div>';
                                }

                                // If the plugin is not installed, display an install message and install button.
                                if ( ! isset( $installed_plugins[$plugin_basename] ) ) {
                                    echo '<div class="soliloquy-addon-not-installed soliloquy-addon-message">';
                                        echo '<span class="addon-status-not-installed addon-status">' . esc_html__( 'Status:', 'soliloquy' ) . '&nbsp;<span>' . esc_html__( 'Not Installed', 'soliloquy' ) . '</span></span>';
                                        echo '<div class="soliloquy-addon-action">';
                                            echo '<a class="soliloquy-icon-cloud-download button button-soliloquy-secondary soliloquy-addon-action-button soliloquy-install-addon" href="#" rel="' . esc_url( $addon->url ) . '">' . esc_html__( 'Install Addon', 'soliloquy' ) . '</a>';
                                        echo '</div>';
                                    echo '</div>';
                                }
                                // If the plugin is installed but not active, display an activate message and activate button.
                                elseif ( is_plugin_inactive( $plugin_basename ) ) {
                                    echo '<div class="soliloquy-addon-inactive soliloquy-addon-message">';
                                        echo '<span class="addon-status-inactive addon-status">' . esc_html__( 'Status:', 'soliloquy' ) . '&nbsp;<span>' . esc_html__( 'Inactive', 'soliloquy' ) . '</span></span>';
                                        echo '<div class="soliloquy-addon-action">';
                                            echo '<a class="soliloquy-icon-toggle-on button button-soliloquy-secondary soliloquy-addon-action-button soliloquy-activate-addon" href="#" rel="' . esc_attr( $plugin_basename ) . '">' . esc_html__( 'Activate', 'soliloquy' ) . '</a>';
                                        echo '</div>';
                                    echo '</div>';
                                }
								echo '</div>';
                            echo '</div>';
                            $i++;
                        }
                        ?>
                    </div>
                    
	                <?php else : ?>
	                    
	                    <form id="soliloquy-settings-refresh-addons-form" method="post">
	                    
	                        <p><?php esc_html_e( 'There was an issue retrieving the addons for this site. Please click on the button below the refresh the addons data.', 'soliloquy' ); ?></p>
	                    
	                        <?php wp_nonce_field( 'soliloquy-refresh-addons', 'soliloquy-refresh-addons' ); ?>
	                    
	                        <?php submit_button( esc_html__( 'Refresh Addons', 'soliloquy' ), 'button-soliloquy', 'soliloquy-refresh-addons-submit', false ); ?>
	                   
	                    </form>
	                
	                <?php endif; 
										
		        endif; ?>


        </div>
        
        <?php else : ?>
        
            <div class="error below-h2"><p><?php esc_html_e( 'In order to get access to Addons, you need to resolve your license key errors.', 'soliloquy' ); ?></p></div>
        
        <?php
        
        endif;

		if (  !in_array( $type, array('developer','master'), true ) ): 

			$upgrade_addons = $this->get_all_addons(); ?>
			
			<div class="soliloquy-clearfix"></div>
			
			<div id="soliloquy-addons-upgrade-area">
			
				<h2 class="soliloquy-addons-upgrade"><?php esc_html_e( 'Unlock More Addons', 'soliloquy' );  ?></h2>
			
				<p class="soliloquy-unlock-text"><strong><?php esc_html_e('Want even more addons?', 'soliloquy' ); ?>&nbsp;</strong><a href="https://soliloquywp.com/members-area/#upgrade" target="_blank"><?php esc_html_e('Upgrade your Soliloquy account', 'soliloquy' ); ?></a><span>&nbsp;<?php esc_html_e( 'and unlock the following addons.','soliloquy' ); ?></span></p>
			
			<?php // Let's begin outputting the addons.
            if ( $upgrade_addons ) :
            $i = 0;
            
            foreach ( (array) $upgrade_addons as $i => $addon ) {
			// Attempt to get the plugin basename if it is installed or active.
                $plugin_basename   = $this->get_plugin_basename_from_slug( $addon->slug );
                $installed_plugins = get_plugins();
                $last              = ( 2 == $i%3 ) ? 'last' : '';
                
                // If site is HTTPS, serve $addon->image as HTTPS too, this prevents warnings
                if ( is_ssl() ) {
                    $addon->image = str_replace( 'http://', 'https://', $addon->image );
                }
				                            
                echo '<div class="soliloquy-addon ' . $last . '">';
                
                	echo '<div class="soliloquy-addon-content">';
                
                	    echo '<h3 class="soliloquy-addon-title">' . esc_html( $addon->title ) . '</h3>';

						echo '<img class="soliloquy-addon-thumb" src="' . esc_url( $addon->image ) . '" width="300px" height="250px" alt="' . esc_attr( $addon->title ) . '" />';

						echo '<p class="soliloquy-addon-excerpt">' . esc_html( $addon->excerpt ) . '</p>';
                  
                    echo '</div>';
				
					echo '<div class="soliloquy-addon-footer">';

                        echo '<div class="soliloquy-addon-unlock soliloquy-addon-message">';
                            echo '<a class="button button-soliloquy soliloquy-addon-action-button soliloquy-unlock-addon" href="http://soliloquywp.com/pricing/" rel="' . esc_attr( $plugin_basename ) . '" target="_blank">' . esc_html__( 'Upgrade Now', 'soliloquy' ) . '</a>';
                        echo '</div>';
					echo '</div>';
                echo '</div>';
                $i++;	                
            } 
            
            endif;
            ?>
            
			</div>
			
			<?php 
				endif; ?>    
	    </div>
	    
	    <?php   
	    
    }

    public function get_all_addons(){

        $key = $this->base->get_license_key();

        if ( false === ( $addons = get_transient( '_sol_all_addons' ) ) ) {
            $addons = $this->get_all_addons_data( $key );
        } else {
            return $addons;
        }	    
    }
    
    public function get_all_addons_data( $key ){
	    
        $addons = Soliloquy_License::get_instance()->perform_remote_request( 'get-all-addons-data', array( 'tgm-updater-key' => $key ) );
        
		
        // If there was an API error, set transient for only 10 minutes.
        if ( ! $addons ) {
            set_transient( '_sol_all_addons', false, 10 * MINUTE_IN_SECONDS );
            return false;
        }

        // If there was an error retrieving the addons, set the error.
        if ( isset( $addons->error ) ) {
            set_transient( '_sol_all_addons', false, 10 * MINUTE_IN_SECONDS );
            return false;
        }

        // Otherwise, our request worked. Save the data and return it.
        set_transient( '_sol_all_addons', $addons, DAY_IN_SECONDS );
        return $addons;	    
    }
    

    public function get_addons(){

        $key = $this->base->get_license_key();
        if ( ! $key ) {
            return false;
        }

        if ( false === ( $addons = get_transient( '_sol_addons' ) ) ) {
            $addons = $this->get_addons_data( $key );
        } else {
            return $addons;
        }	    
    }
    
    public function get_addons_data( $key ){
	    
        $addons = Soliloquy_License::get_instance()->perform_remote_request( 'get-addons-data', array( 'tgm-updater-key' => $key ) );
				
        // If there was an API error, set transient for only 10 minutes.
        if ( ! $addons ) {
            set_transient( '_sol_addons', false, 10 * MINUTE_IN_SECONDS );
            return false;
        }

        // If there was an error retrieving the addons, set the error.
        if ( isset( $addons->error ) ) {
            set_transient( '_sol_addons', false, 10 * MINUTE_IN_SECONDS );
            return false;
        }

        // Otherwise, our request worked. Save the data and return it.
        set_transient( '_sol_addons', $addons, DAY_IN_SECONDS );
        return $addons;	    
    }
    
	public function is_refreshing_addons(){
      
        return isset( $_POST['soliloquy-refresh-addons-submit'] );
	 	
	}
	
    /**
     * Verifies nonces that allow addon refreshing.
     *
     * @since 1.0.0
     *
     * @return bool True if nonces check out, false otherwise.
     */
    public function refresh_addons_action() {

        return isset( $_POST['soliloquy-refresh-addons-submit'] ) && wp_verify_nonce( $_POST['soliloquy-refresh-addons'], 'soliloquy-refresh-addons' );

    }
    
    /**
     * Retrieve the plugin basename from the plugin slug.
     *
     * @since 1.0.0
     *
     * @param string $slug The plugin slug.
     * @return string      The plugin basename if found, else the plugin slug.
     */
    public function get_plugin_basename_from_slug( $slug ) {

        $keys = array_keys( get_plugins() );

        foreach ( $keys as $key ) {
            if ( preg_match( '|^' . $slug . '|', $key ) ) {
                return $key;
            }
        }

        return $slug;

    }	
    
    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Soliloquy_Settings object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Soliloquy_Addons ) ) {
            self::$instance = new Soliloquy_Addons();
        }

        return self::$instance;

    }

}

$soliloquy_addons = Soliloquy_Addons::get_instance();