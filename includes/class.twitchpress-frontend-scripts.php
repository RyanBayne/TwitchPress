<?php
/**
 * TwitchPress - Load Frontend Scripts
 *
 * Register and queue scripts, css and filters that are not used globally. 
 * 
 * @author   Ryan Bayne
 * @category Scripts
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Frontend_Scripts' ) ) : 

/**
 * TwitchPress_Frontend_Scripts Class.
 */
class TwitchPress_Frontend_Scripts {
                                            
    /**
     * Contains an array of script handles registered by TwitchPress.
     * @var array
     */
    private static $scripts = array();

    /**
     * Contains an array of script handles registered by TwitchPress.
     * @var array
     */
    private static $styles = array();

    /**
     * Contains an array of script handles localized by TwitchPress.
     * @var array
     */
    private static $wp_localize_scripts = array();

    /**
     * Hook in methods.
     */
    public static function init() {
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );
        add_action( 'wp_print_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
        add_action( 'wp_print_footer_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
    }
                                         
    /**
     * Get styles for the frontend.
     * @access private
     * @return array
     */
    public static function get_styles() {                 
        return apply_filters( 'twitchpress_enqueue_styles', array(
            'twitchpress-general' => array(
                'src'     => str_replace( array( 'http:', 'https:' ), '', TwitchPress()->plugin_url() ) . '/assets/css/twitchpress.css',
                'deps'    => '',
                'version' => TWITCHPRESS_VERSION,
                'media'   => 'all'
            ),
        ) );
    }
    
    /**
     * Register a style for use.
     *
     * @uses   wp_register_style()
     * @access private
     * @param  string   $handle
     * @param  string   $path
     * @param  string[] $deps
     * @param  string   $version
     * @param  string   $media
     */
    private static function register_style( $handle, $path, $deps = array(), $version = TWITCHPRESS_VERSION, $media = 'all' ) {
        self::$styles[] = $handle;
        wp_register_style( $handle, $path, $deps, $version, $media );
    }
        
    /**
     * Register and enqueue a styles for use.
     *
     * @uses   wp_enqueue_style()
     * @access private
     * @param  string   $handle
     * @param  string   $path
     * @param  string[] $deps
     * @param  string   $version
     * @param  string   $media
     */
    private static function enqueue_style( $handle, $path = '', $deps = array(), $version = TWITCHPRESS_VERSION, $media = 'all' ) {
        if ( ! in_array( $handle, self::$styles ) && $path ) {
            self::register_style( $handle, $path, $deps, $version, $media );
        }
        wp_enqueue_style( $handle );
    }
                                                
    /**
     * Register a script for use.
     *
     * @uses   wp_register_script()
     * @access private
     * @param  string   $handle
     * @param  string   $path
     * @param  string[] $deps
     * @param  string   $version
     * @param  boolean  $in_footer
     */
    private static function register_script( $handle, $path, $deps = array( 'jquery' ), $version = TWITCHPRESS_VERSION, $in_footer = true ) {
        self::$scripts[] = $handle;
        wp_register_script( $handle, $path, $deps, $version, $in_footer );
    }

    /**
     * Register and enqueue a script for use.
     *
     * @uses   wp_enqueue_script()
     * @access private
     * @param  string   $handle
     * @param  string   $path
     * @param  string[] $deps
     * @param  string   $version
     * @param  boolean  $in_footer
     */
    private static function enqueue_script( $handle, $path = '', $deps = array( 'jquery' ), $version = TWITCHPRESS_VERSION, $in_footer = true ) {
        if ( ! in_array( $handle, self::$scripts ) && $path ) {
            self::register_script( $handle, $path, $deps, $version, $in_footer );
        }
        wp_enqueue_script( $handle );
    }

    /**
     * Register/queue frontend scripts.
     */
    public static function load_scripts() {
        global $post;

        if ( ! did_action( 'before_twitchpress_init' ) ) {
            return;
        }                   

        $suffix               = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        $assets_path          = str_replace( array( 'http:', 'https:' ), '', TwitchPress()->plugin_url() ) . '/assets/';
        $frontend_script_path = $assets_path . 'js/frontend/';
    }

    /**
     * Localize a TwitchPress script once.
     * @access private
     * @since  2.3.0 this needs less wp_script_is() calls due to https://core.trac.wordpress.org/ticket/28404 being added in WP 4.0.
     * @param  string $handle
     */
    private static function localize_script( $handle ) {
        if ( ! in_array( $handle, self::$wp_localize_scripts ) && wp_script_is( $handle ) && ( $data = self::get_script_data( $handle ) ) ) {
            $name                        = str_replace( '-', '_', $handle ) . '_params';
            self::$wp_localize_scripts[] = $handle;
            wp_localize_script( $handle, $name, apply_filters( $name, $data ) );
        }
    }

    /**
     * Return data for script handles.
     * @access private
     * @param  string $handle
     * @return array|bool
     */
    private static function get_script_data( $handle ) {
        global $wp;

        return false;
    }

    /**
     * Localize scripts only when enqueued.
     */
    public static function localize_printed_scripts() {
        foreach ( self::$scripts as $handle ) {
            self::localize_script( $handle );
        }
    }
}

endif;

return TwitchPress_Frontend_Scripts::init();