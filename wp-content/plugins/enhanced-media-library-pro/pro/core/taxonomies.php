<?php

if ( ! defined( 'ABSPATH' ) )
	exit;



/**
 *  wpuxss_eml_add_attachment
 *
 *  @since    2.2
 *  @created  11/02/16
 */

add_action( 'add_attachment', 'wpuxss_eml_add_attachment' );

if ( ! function_exists( 'wpuxss_eml_add_attachment' ) ) {

    function wpuxss_eml_add_attachment( $id ) {

        $attachment = get_post( $id );

        if ( ! $attachment->post_parent )
            return;

        $post = get_post( $attachment->post_parent );
        $wpuxss_eml_taxonomies = get_option('wpuxss_eml_taxonomies');

        foreach ( get_object_taxonomies( $post->post_type, 'names' ) as $taxonomy ) {

            if ( ! isset( $wpuxss_eml_taxonomies[$taxonomy] ) ||
                 $wpuxss_eml_taxonomies[$taxonomy]['eml_media'] ||
                 ! $wpuxss_eml_taxonomies[$taxonomy]['assigned'] ||
                 ! $wpuxss_eml_taxonomies[$taxonomy]['taxonomy_auto_assign'] ) {
                continue;
            }

            $term_ids = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );

            if ( is_wp_error( $term_ids ) || empty( $term_ids ) )
                continue;

            wp_set_object_terms( $id, $term_ids, $taxonomy, false );
        }
    }
}



/**
 *  wpuxss_eml_synchronize_terms
 *
 *  @since    2.2
 *  @created  21/02/16
 */

add_action( 'wp_ajax_eml-synchronize-terms', 'wpuxss_eml_synchronize_terms' );

if ( ! function_exists( 'wpuxss_eml_synchronize_terms' ) ) {

    function wpuxss_eml_synchronize_terms() {

        if ( ! isset( $_REQUEST['post_type'] ) )
            wp_send_json_error();

        if ( ! isset( $_REQUEST['taxonomy'] ) )
            wp_send_json_error();

        check_ajax_referer( 'eml-bulk-edit-nonce', 'nonce' );

        $args = array(
            'posts_per_page' => -1,
            'post_type'      => $_REQUEST['post_type'], //$post_type,
            'post_status'    => 'publish',
        );

        foreach( get_posts( $args ) as $post ) {

            $attachments = get_attached_media( '', $post->ID );

            if ( empty( $attachments ) )
                continue;

            $term_ids = wp_get_object_terms( $post->ID, /*$taxonomy*/$_REQUEST['taxonomy'], array( 'fields' => 'ids' ) );

            if ( is_wp_error( $term_ids ) || empty( $term_ids ) )
                continue;

            foreach( $attachments as $attachment ) {

                wp_set_object_terms( $attachment->ID, $term_ids, /*$taxonomy*/$_REQUEST['taxonomy'], false );
            }

            wp_send_json_success();
        }
    }
}

?>
