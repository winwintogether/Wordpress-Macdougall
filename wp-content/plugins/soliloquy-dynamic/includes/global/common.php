<?php
/**
 * Common class.
 *
 * @since 2.1.9
 *
 * @package Soliloquy_Dynamic
 * @author  Tim Carr
 */
class Soliloquy_Dynamic_Common {

    /**
     * Holds the class object.
     *
     * @since 2.1.9
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 2.1.9
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 2.1.9
     *
     * @var object
     */
    public $base;

    /**
     * Primary class constructor.
     *
     * @since 2.1.9
     */
    public function __construct() {

    }
    
    /**
     * Retrieves the dynamic slider ID for holding dynamic settings.
     *
     * @since 2.1.9
     *
     * @return int The post ID for the dynamic settings.
     */
    function get_dynamic_id() {
    
        return get_option( 'soliloquy_dynamic' );
    
    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 2.1.9
     *
     * @return object The Soliloquy_Dynamic_Common object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Soliloquy_Dynamic_Common ) ) {
            self::$instance = new Soliloquy_Dynamic_Common();
        }

        return self::$instance;

    }

}

// Load the common class.
$soliloquy_dynamic_common = Soliloquy_Dynamic_Common::get_instance();