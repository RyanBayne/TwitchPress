<?php
/**
 * TwitchPress - SPL Autoloader Class
 *
 * @author   Ryan Bayne
 * @category System
 * @package  TwitchPress/System
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}    

/**
* TwitchPress SPL Autoloader Class
*/
class TwitchPress_Autoloader {

    /**
     * Path to the includes directory.
     *
     * @var string
     */
    private $include_path = '';

    /**
     * The Constructor.
     * 
     * @version 1.2
     */
    public function __construct() {
        if ( function_exists( "__autoload" ) ) {
            spl_autoload_register( "__autoload" );
        }

        spl_autoload_register( array( $this, 'autoload' ) );

        $this->include_path = untrailingslashit( plugin_dir_path( TWITCHPRESS_ABSPATH ) ) . '/includes/';
    }

    /**
     * Take a class name and turn it into a file name.
     *
     * @param  string $class
     * @return string
     */
    private function get_file_name_from_class( $class ) {
        return 'class.' . str_replace( '_', '-', $class ) . '.php';
    }

    /**
     * Include a class file.
     *
     * @param  string $path
     * @return bool successful or not
     */
    private function load_file( $path ) {
        if ( $path && is_readable( $path ) ) {
            include_once( $path );
            return true;
        }
        return false;
    }

    /**
     * Auto-load TwitchPress classes on demand to reduce memory consumption.
     *
     * @param string $class
     */
    public function autoload( $class ) {
        $class = strtolower( $class );
        $file  = $this->get_file_name_from_class( $class );
        $path  = '';
   
        if ( strpos( $class, 'twitchpress_shortcode_' ) === 0 ) {
            $path = $this->include_path . 'shortcodes/';
        } elseif ( strpos( $class, 'twitchpress_meta_box' ) === 0 ) {
            $path = $this->include_path . 'admin/meta-boxes/';
        } elseif ( strpos( $class, 'twitchpress_admin' ) === 0 ) {
            $path = $this->include_path . 'admin/';
        } 
    
        if ( empty( $path ) || ( ! $this->load_file( $path . $file ) && strpos( $class, 'twitchpress-' ) === 0 ) ) {
            $this->load_file( $this->include_path . $file );
        }
    }
}

new TwitchPress_Autoloader();