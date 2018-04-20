<?php
/**
 * Settings class.
 *
 * @since 1.0.0
 *
 * @package Soliloquy
 * @author  Thomas Griffin
 */

 // Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Soliloquy_Settings {

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
     * Holds the submenu pagehook.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $hook;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Soliloquy::get_instance();

        // Add custom settings submenu.
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );

        // Add callbacks for settings tabs.
        add_action( 'soliloquy_tab_settings_general', array( $this, 'settings_general_tab' ) );
        add_action( 'soliloquy_tab_settings_addons', array( $this, 'settings_addons_tab' ) );

        // Add the settings menu item to the Plugins table.
        add_filter( 'plugin_action_links_' . plugin_basename( plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'soliloquy.php' ), array( $this, 'settings_link' ) );

        // Check if the soliloquy-publishing-default option is set. If not, default it to active
        $publishing_default = get_option( 'soliloquy-publishing-default' );
        if ( ! $publishing_default ) {
            update_option ( 'soliloquy-publishing-default', 'active' );
        }
        
        // Check if the soliloquy_slide_view option is set. If not, default it to grid
        $defaul_view = get_option( 'soliloquy_slide_view' );
        if ( ! $defaul_view ) {
	        
            update_option ( 'soliloquy_slide_view', 'grid' );
        }
        
        // Possibly add a callback for upgrading.
        $upgrade_lite = get_option( 'soliloquy_upgrade' );
        if ( $upgrade_lite ) {
            return;
        }

        $v1_license = get_option( 'soliloquy_license_key' );
        if ( ! $v1_license ) {
            return;
        }

        add_action( 'soliloquy_tab_settings_upgrade', array( $this, 'settings_upgrade_tab' ) );

    }

    /**
     * Register the Settings submenu item for Soliloquy.
     *
     * @since 1.0.0
     */
    public function admin_menu() {

        // Register the submenu.
        $this->hook = add_submenu_page(
            'edit.php?post_type=soliloquy',
            esc_attr__( 'Soliloquy Settings', 'soliloquy' ),
            esc_attr__( 'Settings', 'soliloquy' ),
            apply_filters( 'soliloquy_menu_cap', 'manage_options' ),
            $this->base->plugin_slug . '-settings',
            array( $this, 'settings_page' )
        );

        // If successful, load admin assets only on that page and check for addons refresh.
        if ( $this->hook ) {
            add_action( 'load-' . $this->hook, array( $this, 'maybe_fix_migration' ) );
            add_action( 'load-' . $this->hook, array( $this, 'update_settings' ) );
            add_action( 'load-' . $this->hook, array( $this, 'settings_page_assets' ) );
        }

    }

    /**
     * Maybe fixes the broken migration.
     *
     * @since 2.3.9.6
     *
     * @return null Return early if not fixing the broken migration
     */
    public function maybe_fix_migration() {

	    // Check if user pressed 'Fix' button and nonce is valid
	    if ( !isset( $_POST['soliloquy-serialization-submit'] ) ) {
		   	return;
		}
		if ( !wp_verify_nonce( $_POST['soliloquy-serialization-nonce'], 'soliloquy-serialization-nonce' ) ) {
			return;
		}

		// If here, fix potentially broken migration
		// Get WPDB and serialization class
		global $wpdb, $fixedSliders;
		require plugin_dir_path( __FILE__ ) . 'serialization.php';
		$instance = Soliloquy_Serialization_Admin::get_instance();

		// Keep count of the number of sliders that get fixed
		$fixedSliders = 0;

		// Query to get all Soliloquy CPTs
		$sliders = new WP_Query( array (
			'post_type' => 'soliloquy',
			'post_status' => 'any',
			'posts_per_page' => -1,
		) );

		// Iterate through sliders
		if ( $sliders->posts ) {
			foreach ( $sliders->posts as $slider ) {

				// Attempt to get slider data
				$slider_data = get_post_meta( $slider->ID, '_sol_slider_data', true );
				if ( is_array( $slider_data ) ) {
					// Nothing to fix here, continue
					continue;
				}

				// Need to fix the broken serialized string for this slider
				// Get raw string from DB
				$query = $wpdb->prepare( "	SELECT meta_value
					        				FROM ".$wpdb->prefix."postmeta
					        				WHERE post_id = %d
					        				AND meta_key = %s
					        				LIMIT 1",
					        				$slider->ID,
					        				'_sol_slider_data' );
				$raw_slider_data = $wpdb->get_row( $query );

				// Do the fix, which returns an unserialized array
				$slider_data = $instance->fix_serialized_string( $raw_slider_data->meta_value );

				// Check we now have an array of unserialized data
				if ( is_array ( $slider_data ) ) {
					update_post_meta( $slider->ID, '_sol_slider_data', $slider_data );
					$fixedSliders++;
				}
			}
		}

	    // Output an admin notice so the user knows what happened
	    add_action( 'admin_notices', array( $this, 'fixed_migration' ) );

    }

    /**
     * Update settings, if defined
     *
     * @since 2.3.9.6
     *
     * @return null Invalid nonce / no need to save
     */
    public function update_settings() {

        // Check form was submitted
        if ( ! isset( $_POST['soliloquy-settings-submit'] ) ) {
            return;
        }

        // Check nonce is valid
		if ( ! wp_verify_nonce( $_POST['soliloquy-settings-nonce'], 'soliloquy-settings-nonce' ) ) {
			return;
		}

		// Update options
		update_option( 'soliloquy-publishing-default', $_POST['soliloquy-publishing-default'] );
        update_option( 'soliloquy_slide_position', $_POST['soliloquy_slide_position'] );
        update_option( 'soliloquy_slide_view', $_POST['soliloquy_slide_view'] );

        // Show confirmation notice
        add_action( 'admin_notices', array( $this, 'updated_settings' ) );

    }

    /**
	 * Outputs a WordPress style notification to tell the user how many sliders were
	 * fixed after running the migration fixer
	 *
	 * @since 2.3.9.6
	 */
    public function fixed_migration() {
	    global $fixedSliders;

	    ?>
	    <div class="updated">
            <p><strong><?php echo $fixedSliders . esc_html__( ' slider(s) fixed successfully.', 'soliloquy' ); ?></strong></p>
        </div>
	    <?php

    }

     /**
	 * Outputs a WordPress style notification to tell the user their settings were saved
	 *
	 * @since 2.3.9.6
	 */
    public function updated_settings() {
	    ?>
	    <div class="updated">
            <p><?php esc_html_e( 'Settings updated.', 'soliloquy' ); ?></p>
        </div>
	    <?php

    }

    /**
     * Loads assets for the settings page.
     *
     * @since 1.0.0
     */
    public function settings_page_assets() {

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

    }

    /**
     * Register and enqueue settings page specific CSS.
     *
     * @since 1.0.0
     */
    public function enqueue_admin_styles() {

        wp_register_style( $this->base->plugin_slug . '-settings-style', plugins_url( 'assets/css/settings.css', $this->base->file ), array(), $this->base->version );
        wp_enqueue_style( $this->base->plugin_slug . '-settings-style' );

        // Run a hook to load in custom styles.
        do_action( 'soliloquy_settings_styles' );

    }

    /**
     * Register and enqueue settings page specific JS.
     *
     * @since 1.0.0
     */
    public function enqueue_admin_scripts() {

        wp_enqueue_script( 'jquery-ui-tabs' );
	    wp_register_script( $this->base->plugin_slug . '-chosen', plugins_url( 'assets/js/min/chosen.jquery-min.js', $this->base->file ), array(), $this->base->version, true );
		wp_enqueue_script( $this->base->plugin_slug . '-chosen' );

        wp_register_script( $this->base->plugin_slug . '-settings-script', plugins_url( 'assets/js/min/settings-min.js', $this->base->file ), array( 'jquery', 'jquery-ui-tabs' ), $this->base->version, true );
        wp_enqueue_script( $this->base->plugin_slug . '-settings-script' );
        wp_localize_script(
            $this->base->plugin_slug . '-settings-script',
            'soliloquy_settings',
            array(
                'ajax'             => admin_url( 'admin-ajax.php' ),
                'proceed'          => esc_attr__( 'Proceed', 'soliloquy' ),
                'redirect'         => esc_url( add_query_arg( array( 'post_type' => 'soliloquy', 'soliloquy-upgraded' => true ), admin_url( 'edit.php' ) ) ),
                'upgrade_nonce'    => wp_create_nonce( 'soliloquy-upgrade' )

            )
        );

        // Run a hook to load in custom scripts.
        do_action( 'soliloquy_settings_scripts' );

    }

    /**
     * Callback to output the Soliloquy settings page.
     *
     * @since 1.0.0
     */
    public function settings_page() {

        ?>
        <h1 id="soliloquy-tabs-nav" class="soliloquy-clear">

        <?php $i = 0; foreach ( (array) $this->get_soliloquy_settings_tab_nav() as $id => $title ) : $class = 0 === $i ? 'soliloquy-active nav-tab-active' : ''; ?>

        <a class="nav-tab <?php echo $class; ?>" href="#soliloquy-tab-<?php echo $id; ?>" title="<?php echo $title; ?>"><?php echo $title; ?></a>

        <?php $i++; endforeach; ?>

        </h1>

        <div id="soliloquy-settings" class="wrap">

	        <h1 class="soliloquy-hideme"></h1>

            <div class="soliloquy soliloquy-clear">

                <div id="soliloquy-tabs" class="soliloquy-clear">

                    <?php $i = 0; foreach ( (array) $this->get_soliloquy_settings_tab_nav() as $id => $title ) : $class = 0 === $i ? 'soliloquy-active' : ''; ?>

                    <div id="soliloquy-tab-<?php echo $id; ?>" class="soliloquy-tab soliloquy-clear <?php echo $class; ?>">

                        <?php do_action( 'soliloquy_tab_settings_' . $id ); ?>

                    </div>

                    <?php $i++; endforeach; ?>

                </div>

            </div>

        </div>

        <?php

    }

    /**
     * Callback for getting all of the settings tabs for Soliloquy.
     *
     * @since 1.0.0
     *
     * @return array Array of tab information.
     */
    public function get_soliloquy_settings_tab_nav() {

        $tabs = array(
            'general' => esc_attr__( 'General', 'soliloquy' ), // This tab is required. DO NOT REMOVE VIA FILTERING.
        );
        $tabs = apply_filters( 'soliloquy_settings_tab_nav', $tabs );

        // Possibly add a tab for upgrading.
        $upgrade_lite = get_option( 'soliloquy_upgrade' );
        if ( $upgrade_lite ) {
            return $tabs;
        }

        $v1_license = get_option( 'soliloquy_license_key' );
        if ( ! $v1_license ) {
            return $tabs;
        }

        $tabs['upgrade'] = esc_attr__( 'Upgrade', 'soliloquy' );

        return $tabs;

    }

    /**
     * Callback for displaying the UI for general settings tab.
     *
     * @since 1.0.0
     */
    public function settings_general_tab() {

	    // Get settings
	    $publishingDefault = get_option( 'soliloquy-publishing-default' );
        $slide_position = get_option( 'soliloquy_slide_position' );
        $slide_view = get_option( 'soliloquy_slide_view' );

        if ( empty ( $slide_position ) ) {
            $slide_position = 'after';
        }
        ?>
        <div id="soliloquy-settings-general">

            <table class="form-table soliloquy-settings-table" cellpadding="40">

                <tbody>

                    <tr id="soliloquy-settings-key-box">

                        <th scope="row">

                            <label for="soliloquy-settings-key"><?php esc_html_e( 'Soliloquy License Key', 'soliloquy' ); ?></label>

                        </th>

                        <td>

                            <form id="soliloquy-settings-verify-key" method="post">

                                <input class="soliloquy-input" type="password" name="soliloquy-license-key" id="soliloquy-settings-key" value="<?php echo ( $this->base->get_license_key() ? $this->base->get_license_key() : '' ); ?>" />

                                <?php wp_nonce_field( 'soliloquy-key-nonce', 'soliloquy-key-nonce' ); ?>

                                <?php submit_button( esc_attr__( 'Verify Key', 'soliloquy' ), 'button-soliloquy', 'soliloquy-verify-submit', false ); ?>

                                <?php submit_button( esc_attr__( 'Deactivate Key', 'soliloquy' ), 'button-soliloquy-secondary', 'soliloquy-deactivate-submit', false ); ?>

                                <p class="description"><?php esc_html_e( 'License key to enable automatic updates for Soliloquy.', 'soliloquy' ); ?></p>

                            </form>

                        </td>

                    </tr>

                    <?php $type = $this->base->get_license_key_type(); if ( ! empty( $type ) ) : ?>

                    <tr id="soliloquy-settings-key-type-box">

                        <th scope="row">

                            <label for="soliloquy-settings-key-type"><?php esc_html_e( 'Soliloquy License Key Type', 'soliloquy' ); ?></label>

                        </th>

                        <td>

                            <form id="soliloquy-settings-key-type" method="post">

                                <span class="soliloquy-license-type"><?php printf( __( 'Your license key type for this site is <strong>%s.</strong>', 'soliloquy' ), $this->base->get_license_key_type() ); ?>

                                <input class="soliloquy-input" type="hidden" name="soliloquy-license-key" value="<?php echo $this->base->get_license_key(); ?>" />

                                <?php wp_nonce_field( 'soliloquy-key-nonce', 'soliloquy-key-nonce' ); ?>

                                <?php submit_button( esc_attr__( 'Refresh Key', 'soliloquy' ), 'button-soliloquy', 'soliloquy-refresh-submit', false ); ?>

                                <p class="description"><?php esc_html_e( 'Your license key type (handles updates and Addons). Click refresh if your license has been upgraded or the type is incorrect.', 'soliloquy' ); ?></p>

                            </form>

                        </td>

                    </tr>

                    <?php endif; ?>

                    <tr id="soliloquy-serialization-box">

                        <th scope="row">

                            <label for="soliloquy-serialization"><?php esc_html_e( 'Fix Broken Migration', 'soliloquy' ); ?></label>

                        </th>

                        <td>

                            <form id="soliloquy-serialization" method="post">

                                <?php wp_nonce_field( 'soliloquy-serialization-nonce', 'soliloquy-serialization-nonce' ); ?>

                                <?php submit_button( esc_attr__( 'Fix', 'soliloquy' ), 'button-soliloquy', 'soliloquy-serialization-submit', false ); ?>

                                <p class="description"><?php esc_html_e( 'If you have changed the URL of your WordPress web site, and manually executed a search/replace query on URLs in your WordPress database, your sliders will probably no longer show any slides.  <strong>If this is the case</strong>, click the button above to fix this. We recommend using a migration plugin or script next time :)', 'soliloquy' ); ?></p>

                            </form>

                        </td>

                    </tr>

                </tbody>

            </table>

            <!-- General Settings -->
            <form id="soliloquy-settings" method="post">

                <table class="form-table soliloquy-settings-table">

                    <tbody>

                        <!-- Publishing Default -->
                        <tr id="soliloquy-publishing-default">
                            <th scope="row">
                                <label for="soliloquy-publishing-default"><?php esc_html_e( 'New Slide Status', 'soliloquy' ); ?></label>
                            </th>
                            <td>
	                            <div class="soliloquy-select">
	                                <select name="soliloquy-publishing-default" size="1" class="soliloquy-chosen" data-soliloquy-chosen-options='{ "disable_search":"true" }'>
		                                <option value="active"<?php selected( $publishingDefault, 'active' ); ?>><?php esc_html_e( 'Published', 'soliloquy' ); ?></option>
		                                <option value="pending"<?php selected( $publishingDefault, 'pending' ); ?>><?php esc_html_e( 'Draft', 'soliloquy' ); ?></option>
	                                </select>
	                            </div>
                                <p class="description"><?php esc_html_e( 'Choose the default status of any new slides that are added/uploaded to your Soliloquy sliders. You can always change slides on an individual basis by editing them.', 'soliloquy' ); ?></p>
                            </td>
                        </tr>

                        <!-- Media Position -->
                        <tr id="soliloquy-slide-view-box">
                            <th scope="row">
                                <label for="soliloquy-slide-position"><?php esc_html_e( 'New Slide Position', 'soliloquy' ); ?></label>
                            </th>
                            <td>
	                            <div class="soliloquy-select">
                                <select id="soliloquy-view" name="soliloquy_slide_position" class="soliloquy-chosen" data-soliloquy-chosen-options='{ "disable_search":"true" }'>
                                    <?php foreach ( (array) Soliloquy_Common_Admin::get_instance()->get_slide_positions() as $i => $data ) : ?>
                                        <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $slide_position ); ?>><?php echo $data['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
	                            </div>
                                <p class="description"><?php esc_html_e( 'When adding slides to a Slider, choose whether to add slides before or after any existing slides.', 'soliloquy' ); ?></p>
                            </td>
                        </tr>
                        <!-- Media Position -->
                        <tr id="soliloquy-slide-view-box">
                            <th scope="row">
                                <label for="soliloquy-slide-view"><?php esc_html_e( 'Default View Position', 'soliloquy' ); ?></label>
                            </th>
                            <td>
	                            <div class="soliloquy-select">
                                <select id="soliloquy-slide-view" name="soliloquy_slide_view" class="soliloquy-chosen" data-soliloquy-chosen-options='{ "disable_search":"true" }'>
                                        <option value="grid"<?php selected( 'grid', $slide_view ); ?>><?php esc_html_e( 'Grid', 'soliloquy' ); ?></option>
                                        <option value="list"<?php selected( 'list', $slide_view ); ?>><?php esc_html_e( 'List', 'soliloquy' ); ?></option>

                                </select>
	                            </div>
                                <p class="description"><?php esc_html_e( 'Default view When adding slides to a Slider.', 'soliloquy' ); ?></p>
                            </td>
                        </tr>
                        <!-- Submit -->
                        <tr class="soliloquy-no-border">
                            <th scope="row">
                                &nbsp;
                            </th>
                            <td>
                                <?php
                                wp_nonce_field( 'soliloquy-settings-nonce', 'soliloquy-settings-nonce' );
                                submit_button( esc_attr__( 'Save Settings', 'soliloquy' ), 'button-soliloquy', 'soliloquy-settings-submit', false );
                                ?>
                            </td>
                        </tr>

                        <?php do_action( 'soliloquy_settings_general_box' ); ?>
                    </tbody>
                </table>
            </form>
        </div>
        <?php

    }

    /**
     * Callback for displaying the UI for upgrade settings tab.
     *
     * @since 1.0.0
     */
    public function settings_upgrade_tab() {

        ?>
        <div id="soliloquy-settings-upgrade">
            <p><strong><?php esc_html_e( 'You have upgraded to v2 of Soliloquy. You need to upgrade your sliders using our custom upgrading tool. Click on the button below to start the process.', 'soliloquy' ); ?></strong></p>
            <p><a class="button button-primary soliloquy-start-upgrade" href="#" title="<?php esc_attr_e( 'Click Here to Start the Upgrade Process', 'soliloquy' ); ?>"><?php esc_html_e( 'Click Here to Start the Upgrade Process', 'soliloquy' ); ?></a> <span class="spinner soliloquy-spinner"></span></p>
        </div>
        <?php

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
     * Add Settings page to plugin action links in the Plugins table.
     *
     * @since 1.0.0
     *
     * @param array $links  Default plugin action links.
     * @return array $links Amended plugin action links.
     */
    public function settings_link( $links ) {

        $settings_link = sprintf( '<a href="%s">%s</a>', esc_url( add_query_arg( array( 'post_type' => 'soliloquy', 'page' => 'soliloquy-settings' ), admin_url( 'edit.php' ) ) ), esc_attr__( 'Settings', 'soliloquy' ) );
        array_unshift( $links, $settings_link );

        return $links;

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Soliloquy_Settings object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Soliloquy_Settings ) ) {
            self::$instance = new Soliloquy_Settings();
        }

        return self::$instance;

    }

}

// Load the settings class.
$soliloquy_settings = Soliloquy_Settings::get_instance();