<?php

if ( ! defined( 'ABSPATH' ) )
	exit;



/**
 *  wpuxss_eml_pro_on_admin_init
 *
 *  @since    2.0
 *  @created  01/10/14
 */

add_action( 'admin_init', 'wpuxss_eml_pro_on_admin_init' );

if ( ! function_exists( 'wpuxss_eml_pro_on_admin_init' ) ) {

    function wpuxss_eml_pro_on_admin_init() {

        // plugin settings: bulk edit
        register_setting(
            'media-taxonomies',
            'wpuxss_eml_pro_bulkedit_savebutton_off',
            'wpuxss_eml_pro_bulkedit_savebutton_off_validate'
        );

        // plugin settings: updates
        register_setting(
            'eml-update-settings',
            'wpuxss_eml_pro_update_options',
            'wpuxss_eml_pro_update_options_validate'
        );

        // plugin settings: updates
        register_setting(
            'eml-license-settings',
            'wpuxss_eml_pro_license_key',
            'wpuxss_eml_pro_license_key_validate'
        );
    }
}



/**
 *  wpuxss_eml_pro_bulkedit_savebutton_off_validate
 *
 *  @type     callback function
 *  @since    2.0
 *  @created  01/10/14
 */

if ( ! function_exists( 'wpuxss_eml_pro_bulkedit_savebutton_off_validate' ) ) {

    function wpuxss_eml_pro_bulkedit_savebutton_off_validate( $input ) {

        $input = isset( $input ) && !! $input ? 1 : 0;

        return $input;
    }
}



/**
 *  wpuxss_eml_pro_update_options_validate
 *
 *  @type     callback function
 *  @since    2.5
 *  @created  12/01/18
 */

if ( ! function_exists( 'wpuxss_eml_pro_update_options_validate' ) ) {

    function wpuxss_eml_pro_update_options_validate( $input ) {

        foreach ( (array)$input as $key => $option ) {
            $input[$key] = isset( $option ) && !! $option ? 1 : 0;
        }

        return $input;
    }
}



/**
 *  wpuxss_eml_pro_license_key_validate
 *
 *  @type     callback function
 *  @since    2.0
 *  @created  13/10/14
 */

if ( ! function_exists( 'wpuxss_eml_pro_license_key_validate' ) ) {

    function wpuxss_eml_pro_license_key_validate( $license_key ) {


        // deactivation
        if ( isset( $_POST['eml-license-deactivate'] ) ) {

            $license_key = '';

            add_settings_error(
                'eml-settings',
                'eml_license_deactivated',
                __( 'Your license has been deactivated.', 'enhanced-media-library' ),
                'updated'
            );

            wpuxss_eml_pro_look_for_update( $license_key );

            return $license_key;
        }


        // activation
        if ( isset( $_POST['eml-license-activate'] ) ) {

            if ( '' === $license_key ) {

                add_settings_error(
                    'eml-settings',
                    'eml_empty_license',
                    __( 'Please check if your license key is correct and try again.', 'enhanced-media-library' ),
                    'error'
                );

                return $license_key;
            }

            $license_key = sanitize_text_field( $license_key );

            wpuxss_eml_pro_look_for_update( $license_key );

            $eml_transient = get_site_transient( 'eml_transient' );

            if ( isset( $eml_transient->update_error ) ) {

                $license_key = '';

                add_settings_error(
                    'eml-settings',
                    'eml_wrong_license',
                    sprintf(
                        __('Activation failed with the error: %s. Please <a href="%s">contact plugin authors</a>.', 'enhanced-media-library'),
                        $eml_transient->update_error,
                        'https://www.wpuxsolutions.com/support/'
                    ),
                    'error'
                );

                return $license_key;
            }

            if ( '' === $eml_transient->package ) {

                $license_key = '';

                add_settings_error(
                    'eml-settings',
                    'eml_wrong_license',
                    sprintf(
                        __('Your license key is incorrect or canceled. Please <a href="%s">contact plugin authors</a>.', 'enhanced-media-library'),
                        'https://www.wpuxsolutions.com/support/'
                    ),
                    'error'
                );

                return $license_key;
            }

            add_settings_error(
                'eml-settings',
                'eml_license_activated',
                __('You license has been activated.', 'enhanced-media-library'),
                'updated'
            );
        }

        return $license_key;
    }
}



/**
 *  wpuxss_eml_pro_on_update_options_update
 *
 *  updates transient on wpuxss_eml_pro_update_options change
 *
 *  @since    2.5
 *  @created  28/01/18
 */

add_action( 'update_option_wpuxss_eml_pro_update_options', 'wpuxss_eml_pro_on_update_options_update', 10, 2 );

if ( ! function_exists( 'wpuxss_eml_pro_on_update_options_update' ) ) {

    function wpuxss_eml_pro_on_update_options_update( $old_value, $update_options ) {

        if ( $old_value === $update_options ) {
            return;
        }

        $license_key = get_option( 'wpuxss_eml_pro_license_key', '' );
        wpuxss_eml_pro_look_for_update( $license_key );
    }
}



/**
 *  wpuxss_eml_pro_extend_non_media_taxonomy_options
 *
 *  adds Auto-assign media items to non-media taxonomies
 *
 *  @since    2.2
 *  @created  21/02/16
 */

add_filter( 'wpuxss_eml_extend_non_media_taxonomy_options', 'wpuxss_eml_pro_extend_non_media_taxonomy_options', 10, 4 );

if ( ! function_exists( 'wpuxss_eml_pro_extend_non_media_taxonomy_options' ) ) {

    function wpuxss_eml_pro_extend_non_media_taxonomy_options( $options, $taxonomy, $post_type, $wpuxss_eml_taxonomies ) {

        $post_singular_name = strtolower ( $post_type->labels->singular_name );

        $options = '<li><input type="checkbox" class="wpuxss-eml-taxonomy_auto_assign" name="wpuxss_eml_taxonomies[' . $taxonomy->name . '][taxonomy_auto_assign]" id="wpuxss_eml_taxonomies-' . $taxonomy->name . '-taxonomy_auto_assign" value="1" ' . checked( 1, $wpuxss_eml_taxonomies[$taxonomy->name]['taxonomy_auto_assign'], false ) . ' /><label for="wpuxss_eml_taxonomies-' . $taxonomy->name . '-taxonomy_auto_assign">' . sprintf( __('Auto-assign media items to parent %s %s on upload','enhanced-media-library'), $post_singular_name, $taxonomy->label ) . '</label>
        <a class="add-new-h2 eml-button-synchronize-terms" data-post-type="' . $post_type->name . '" data-taxonomy="' . $taxonomy->name . '" href="javascript:;">' . __( 'Synchronize Now', 'enhanced-media-library' ) . '</a><p class="description">' . sprintf( __('%sWarning:%s As a result of clicking "Synchronize Now" all media items attached to a %s will be assigned to %s of their parent %s. Currently assigned %s will not be saved. Media items that are not attached to any %s will not be affected.'), '<strong style="color:red">', '</strong>', $post_singular_name, $taxonomy->label, $post_singular_name, $taxonomy->label, $post_singular_name ) . '</p></li>';

        return $options;
    }
}



/**
 *  wpuxss_eml_pro_extend_taxonomies_option_page
 *
 *  adds Bulk Edit options to taxonomies options page
 *
 *  @since    2.0.4
 *  @created  30/01/15
 */

add_action( 'wpuxss_eml_extend_taxonomies_option_page', 'wpuxss_eml_pro_extend_taxonomies_option_page' );

if ( ! function_exists( 'wpuxss_eml_pro_extend_taxonomies_option_page' ) ) {

    function wpuxss_eml_pro_extend_taxonomies_option_page() { ?>

        <h2><?php _e('Bulk Edit','enhanced-media-library'); ?></h2>

        <div class="postbox">

            <div class="inside">

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Turn off \'Save Changes\' button','enhanced-media-library'); ?></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><span><?php _e('Turn off \'Save Changes\' button','enhanced-media-library'); ?></span></legend>
                                <label><input name="wpuxss_eml_pro_bulkedit_savebutton_off" type="checkbox" id="wpuxss_eml_pro_bulkedit_savebutton_off" value="1" <?php checked( true, get_option('wpuxss_eml_pro_bulkedit_savebutton_off'), true ); ?> /> <?php _e('Save changes on the fly','enhanced-media-library'); ?></label>
                                <p class="description"><?php _e( 'Any click on a taxonomy checkbox during media files bulk editing will lead to an <strong style="color:red">immediate saving</strong> of the data. Please, be careful! You have much greater chance to <strong style="color:red">accidentally perform wrong re-assigning</strong> of a lot of your media files / taxonomies with this option turned on.', 'enhanced-media-library' ); ?></p>
                                <p class="description"><?php _e( 'NOT recommended if you work with more than hundred files at a time.', 'enhanced-media-library' ); ?></p>
                            </fieldset>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>

            </div>

        </div>

        <?php
    }
}



/**
 *  wpuxss_eml_pro_print_updates_options
 *
 *  adds Update options to Settings > EML page
 *
 *  @since    2.1
 *  @created  25/10/15
 */

add_action( 'wpuxss_eml_extend_settings_page', 'wpuxss_eml_pro_extend_settings_page' );

if ( ! function_exists( 'wpuxss_eml_pro_extend_settings_page' ) ) {

    function wpuxss_eml_pro_extend_settings_page() {

        $wpuxss_eml_pro_update_options = get_option( 'wpuxss_eml_pro_update_options' );
        $license_key = get_option( 'wpuxss_eml_pro_license_key', '' );

        $eml_transient = get_site_transient( 'eml_transient' );
        $plugin_name   = __( 'Enhanced Media Library PRO', 'enhanced-media-library' ); ?>


        <div class="postbox">

            <h3 class="hndle" id="eml-license-key-section"><?php _e('Updates','enhanced-media-library'); ?></h3>


            <div class="inside">

                <?php if ( isset( $eml_transient->update_error ) ) :

                    $error_text = ( '' !== $license_key ) ? __( 'to check if your license key is correct', 'enhanced-media-library' ) : __( 'to check if an update is available', 'enhanced-media-library' ); ?>

                    <div class="notice inline notice-warning notice-alt"><p>
                        <?php printf( __( '%1$s could not establish a secure connection %2$s. An error occurred: %3$s. Please <a href="%4$s">contact plugin authors</a>.', 'enhanced-media-library' ),
                            $plugin_name,
                            $error_text,
                            '<strong>'.$eml_transient->update_error.'</strong>',
                            'https://www.wpuxsolutions.com/support/'
                        ); ?>
                    </p></div>

                <?php else : ?>

                    <form method="post" action="options.php" id="wpuxss-eml-form-updates">

                        <?php settings_fields( 'eml-license-settings' ); ?>

                        <?php if ( (bool) $eml_transient->license_was_active ) : ?>

                            <h4><?php _e('Your license is active!','enhanced-media-library'); ?></h4>

                            <input name="wpuxss_eml_pro_license_key" type="hidden" id="wpuxss_eml_pro_license_key" value="" />

                            <?php submit_button( __('Deactivate License','enhanced-media-library'), 'primary', 'eml-license-deactivate' ); ?>

                        <?php else : ?>

                            <p><?php printf(
                                __('To unlock updates please enter your license key below. You can get your license key in <a href="%s">Your Account</a>. If you do not have a license, you are welcome to <a href="%s">purchase it</a>.', 'enhanced-media-library'),
                                'https://www.wpuxsolutions.com/account/',
                                'https://www.wpuxsolutions.com/pricing/'
                            ); ?></p>

                            <table class="form-table">
                                <tr>
                                    <th scope="row"><label for="wpuxss_eml_pro_license_key"><?php _e('License Key','enhanced-media-library'); ?></label></th>
                                    <td>
                                        <input name="wpuxss_eml_pro_license_key" type="text" id="wpuxss_eml_pro_license_key" value="" />
                                        <?php submit_button( __( 'Activate License', 'enhanced-media-library' ), 'primary', 'eml-license-activate' ); ?>
                                    </td>
                                </tr>
                            </table>

                        <?php endif; ?>

                    </form>

                <?php endif; ?>

                <?php if ( isset( $eml_transient->update_error ) || (bool) $wpuxss_eml_pro_update_options['ssl_verification_off'] ) : ?>

                    <form method="post" action="options.php">

                        <?php settings_fields( 'eml-update-settings' ); ?>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Turn off SSL verification','enhanced-media-library'); ?></th>
                                <td>
                                    <fieldset>
                                        <legend class="screen-reader-text"><span><?php _e('Turn off SSL verification','enhanced-media-library'); ?></span></legend>
                                        <label><input name="wpuxss_eml_pro_update_options[ssl_verification_off]" type="hidden" value="0" /><input name="wpuxss_eml_pro_update_options[ssl_verification_off]" type="checkbox" value="1" <?php checked( true, $wpuxss_eml_pro_update_options['ssl_verification_off'], true ); ?> /> <?php _e('Try this if you see the error message above.', 'enhanced-media-library'); ?></label>
                                        <p class="description"><?php printf( __( 'This will turn off SSL verification for the update requests that %s sends to its server https://wpuxsolutions.com only.', 'enhanced-media-library'), $plugin_name ); ?></p>
                                    </fieldset>

                                    <?php submit_button(); ?>
                                </td>
                            </tr>
                        </table>

                    </form>

                <?php endif; ?>

            </div>
        </div>

    <?php
    }
}



/**
 *  wpuxss_eml_pro_get_settings
 *
 *  @since    2.1
 *  @created  25/10/15
 */

add_filter( 'wpuxss_eml_pro_get_settings', 'wpuxss_eml_pro_get_settings' );

if ( ! function_exists( 'wpuxss_eml_pro_get_settings' ) ) {

    function wpuxss_eml_pro_get_settings( $settings ) {

        $wpuxss_eml_pro_bulkedit_savebutton_off = get_option( 'wpuxss_eml_pro_bulkedit_savebutton_off' );
        $wpuxss_eml_pro_update_options = get_option( 'wpuxss_eml_pro_update_options' );


        $settings['pro_bulkedit_savebutton_off'] = $wpuxss_eml_pro_bulkedit_savebutton_off;
        $settings['pro_update_options'] = $wpuxss_eml_pro_update_options;

        return $settings;
    }
}



/**
 *  wpuxss_eml_pro_set_settings
 *
 *  @since    2.1
 *  @created  25/10/15
 */

add_action( 'wpuxss_eml_pro_set_settings', 'wpuxss_eml_pro_set_settings', 10, 1 );

if ( ! function_exists( 'wpuxss_eml_pro_set_settings' ) ) {

    function wpuxss_eml_pro_set_settings( $settings ) {

        update_option( 'wpuxss_eml_pro_bulkedit_savebutton_off', $settings['pro_bulkedit_savebutton_off'] );
        update_option( 'wpuxss_eml_pro_update_options', $settings['pro_update_options'] );
    }
}



/**
 *  wpuxss_eml_pro_add_options
 *
 *  @since    2.5
 *  @created  02/02/18
 */

add_filter( 'wpuxss_eml_pro_add_options', 'wpuxss_eml_pro_add_options' );

if ( ! function_exists( 'wpuxss_eml_pro_add_options' ) ) {

    function wpuxss_eml_pro_add_options( $options ) {

        $pro_options = array(
            'wpuxss_eml_pro_bulkedit_savebutton_off',
            'wpuxss_eml_pro_update_options',
            'wpuxss_eml_pro_license_key'
        );

        $options = array_merge( $options, $pro_options );

        return $options;
    }
}



/**
 *  wpuxss_eml_pro_add_transients
 *
 *  @since    2.5
 *  @created  02/02/18
 */

add_filter( 'wpuxss_eml_pro_add_transients', 'wpuxss_eml_pro_add_transients' );

if ( ! function_exists( 'wpuxss_eml_pro_add_transients' ) ) {

    function wpuxss_eml_pro_add_transients( $transients ) {

        $pro_transients = array(
            'eml_transient'
        );

        $transients = array_merge( $transients, $pro_transients );

        return $transients;
    }
}

?>
