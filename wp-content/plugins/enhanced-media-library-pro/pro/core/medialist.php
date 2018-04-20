<?php

if ( ! defined( 'ABSPATH' ) )
	exit;



/**
 *  wpuxss_eml_pro_print_media_settings_templates
 *
 *  @since    2.1.6
 *  @created  19/01/16
 */

add_action( 'print_media_templates', 'wpuxss_eml_pro_print_media_settings_templates' );

if ( ! function_exists( 'wpuxss_eml_pro_print_media_settings_templates' ) ) {

    function wpuxss_eml_pro_print_media_settings_templates() { ?>

        <script type="text/html" id="tmpl-eml-pro-gallery-order">

            <label class="setting orderby">
                <span><?php _e( 'Order By', 'enhanced-media-library' ); ?></span>
                <select class="orderby" name="orderby"
                    data-setting="orderby">
                    <option value="date" <# if ( 'date' == wp.media.gallery.defaults.orderby ) { #>selected="selected"<# } #>>
                        <?php esc_attr_e( 'Date', 'enhanced-media-library' ); ?>
                    </option>
                    <option value="title" <# if ( 'title' == wp.media.gallery.defaults.orderby ) { #>selected="selected"<# } #>>
                        <?php esc_attr_e( 'Title', 'enhanced-media-library' ); ?>
                    </option>
                    <option value="menuOrder" <# if ( 'menuOrder' == wp.media.gallery.defaults.orderby ) { #>selected="selected"<# } #>>
                        <?php esc_attr_e( 'Custom Order', 'enhanced-media-library' ); ?>
                    </option>
                    <option value="rand" <# if ( 'rand' == wp.media.gallery.defaults.orderby ) { #>selected="selected"<# } #>>
                        <?php esc_attr_e( 'Random', 'enhanced-media-library' ); ?>
                    </option>
                </select>
            </label>

            <label class="setting order">
                <span><?php _e( 'Order', 'enhanced-media-library' ); ?></span>
                <select class="order" name="order"
                    data-setting="order">
                    <option value="ASC" <# if ( 'ASC' == wp.media.gallery.defaults.order ) { #>selected="selected"<# } #>>
                        <?php esc_attr_e( 'Ascending', 'enhanced-media-library' ); ?>
                    </option>
                    <option value="DESC" <# if ( 'DESC' == wp.media.gallery.defaults.order ) { #>selected="selected"<# } #>>
                        <?php esc_attr_e( 'Descending', 'enhanced-media-library' ); ?>
                    </option>
                </select>
            </label>

        </script>

        <script type="text/html" id="tmpl-eml-pro-gallery-additional-params">

            <label class="setting limit">
                <span><?php _e( 'Limit', 'enhanced-media-library' ); ?></span>
                <input type="text" data-setting="limit" value="{{wp.media.gallery.defaults.limit}}" />
            </label>

        </script>

        <script type="text/html" id="tmpl-eml-pro-playlist-additional-params">

            <label class="setting orderby">
                <span><?php _e( 'Order By', 'enhanced-media-library' ); ?></span>
                <select class="orderby" name="orderby"
                    data-setting="orderby">
                    <option value="date" <# if ( 'date' == wp.media.playlist.defaults.orderby ) { #>selected="selected"<# } #>>
                        <?php esc_attr_e( 'Date', 'enhanced-media-library' ); ?>
                    </option>
                    <option value="title" <# if ( 'title' == wp.media.playlist.defaults.orderby ) { #>selected="selected"<# } #>>
                        <?php esc_attr_e( 'Title', 'enhanced-media-library' ); ?>
                    </option>
                    <option value="menuOrder" <# if ( 'menuOrder' == wp.media.playlist.defaults.orderby ) { #>selected="selected"<# } #>>
                        <?php esc_attr_e( 'Custom Order', 'enhanced-media-library' ); ?>
                    </option>
                    <option value="rand" <# if ( 'rand' == wp.media.playlist.defaults.orderby ) { #>selected="selected"<# } #>>
                        <?php esc_attr_e( 'Random', 'enhanced-media-library' ); ?>
                    </option>
                </select>
            </label>

            <label class="setting order">
                <span><?php _e( 'Order', 'enhanced-media-library' ); ?></span>
                <select class="order" name="order"
                    data-setting="order">
                    <option value="ASC" <# if ( 'ASC' == wp.media.playlist.defaults.order ) { #>selected="selected"<# } #>>
                        <?php esc_attr_e( 'Ascending', 'enhanced-media-library' ); ?>
                    </option>
                    <option value="DESC" <# if ( 'DESC' == wp.media.playlist.defaults.order ) { #>selected="selected"<# } #>>
                        <?php esc_attr_e( 'Descending', 'enhanced-media-library' ); ?>
                    </option>
                </select>
            </label>

            <label class="setting limit">
                <span><?php _e( 'Limit', 'enhanced-media-library' ); ?></span>
                <input type="text" data-setting="limit" value="{{wp.media.playlist.defaults.limit}}" />
            </label>

        </script>
    <?php }
}

?>
