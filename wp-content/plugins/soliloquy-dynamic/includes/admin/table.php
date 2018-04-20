<?php
/**
 * Common class.
 *
 * @since 2.1.9
 *
 * @package Soliloquy_Dynamic
 * @author  Tim Carr
 */
class Soliloquy_Dynamic_Table {

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
     * Holds the Dynamic ID.
     *
     * @since 2.1.9
     *
     * @var int
     */
    public $dynamic_id;

    /**
     * Primary class constructor.
     *
     * @since 2.1.9
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Soliloquy_Dynamic::get_instance();
        
        // Get Dynamic ID
        $this->dynamic_id = get_option( 'soliloquy_dynamic' );

        // Actions and Filters
        add_action( 'admin_head', array( $this, 'remove_checkbox' ) );
        add_filter( 'page_row_actions', array( $this, 'remove_row_actions' ), 10, 2 );
        add_filter( 'post_row_actions', array( $this, 'remove_row_actions' ), 10, 2 );

    }
    
    /**
     * Removes the Checkbox from the Soliloquy Dynamic Post
     * This prevents accidental trashing of the Post
     *
     * @since 2.1.9
     * 
     */
    public function remove_checkbox() {
        
        // Slider
        if ( isset( get_current_screen()->post_type ) && 'soliloquy' == get_current_screen()->post_type ) {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function($){
                    $('#post-<?php echo $this->dynamic_id; ?> .check-column, #post-<?php echo $this->dynamic_id; ?> .column-shortcode, #post-<?php echo $this->dynamic_id; ?> .column-template, #post-<?php echo $this->dynamic_id; ?> .column-images').empty();
                });
            </script>
            <?php
        }
        
    }
   
    /**
     * Removes Trash and View actions from the Soliloquy Dynamic Post
     *
     * @since 2.1.9
     *
     * @param array $actions Post Row Actions
     * @param WP_Post $post WordPress Post
     * @return array Post Row Actions
     */
    public function remove_row_actions( $actions, $post ) {
        
        switch ( get_post_type( $post ) ) {
            case 'soliloquy':
                // Check Post = Soliloquy Dynamic Post
                if ( $post->ID != $this->dynamic_id ) {
                    return $actions;
                }
                break;
            default:
                // Not a Soliloquy CPT
                return $actions;
                break;
        }
        
        
        // If here, this is the Soliloquy Dynamic Post
        // Remove View + Trash Actions
        unset( $actions['trash'], $actions['view'] );
        
        return $actions;
        
    }  
    
    /**
     * Returns the singleton instance of the class.
     *
     * @since 2.1.9
     *
     * @return object The Soliloquy_Dynamic_Table object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Soliloquy_Dynamic_Table ) ) {
            self::$instance = new Soliloquy_Dynamic_Table();
        }

        return self::$instance;

    }

}

// Load the table class.
$soliloquy_dynamic_table = Soliloquy_Dynamic_Table::get_instance();