<?php
/**
 * Plugin Name: TwitchPress
 * Plugin URI: https://twitchpress.wordpress.com/
 * Github URI: https://github.com/RyanBayne/TwitchPress
 * Description: Add the power of Twitch.tv to WordPress 
 * Version: 3.5.2
 * Author: Ryan Bayne
 * Author URI: https://ryanbayne.wordpress.com/
 * Requires at least: 5.2
 * Tested up to: 5.4
 * License: GPL3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /i18n/languages/
 */
 
const TWITCHPRESS_VERSION = '3.5.2';

// Exit if accessed directly. 
if ( ! defined( 'ABSPATH' ) ) { exit; }
                                                             
if ( ! class_exists( 'WordPressTwitchPress' ) ) :

    if ( ! defined( 'TWITCHPRESS_ABSPATH' ) ) { define( 'TWITCHPRESS_ABSPATH', __FILE__ ); }
    if ( ! defined( 'TWITCHPRESS_PLUGIN_BASENAME' ) ) { define( 'TWITCHPRESS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); }
    if ( ! defined( 'TWITCHPRESS_PLUGIN_DIR_PATH' ) ) { define( 'TWITCHPRESS_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) ); }
    if ( ! defined( 'TWITCHPRESS_PLUGIN_URL' ) ) { define( 'TWITCHPRESS_PLUGIN_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) ); }
                         
    // Pro-Upgrade Extension...
    if( is_dir( TWITCHPRESS_PLUGIN_DIR_PATH . 'pro' ) ) {    
        if ( ! defined( 'TWITCHPRESS_PRO' ) ) { 
            define( 'TWITCHPRESS_PRO', true ); 
            define( 'TWITCHPRESS_PRO_DIR_PATH', TWITCHPRESS_PLUGIN_DIR_PATH . 'pro/' );
            define( 'TWITCHPRESS_PRO_DIR_URL', plugins_url( '/twitchpress/pro/') );  
        }       
    } elseif( is_dir( WP_PLUGIN_DIR . '/twitchpresspro/pro' ) ) {
        if ( ! defined( 'TWITCHPRESS_PRO' ) ) { 
            define( 'TWITCHPRESS_PRO', true ); 
            define( 'TWITCHPRESS_PRO_DIR_PATH', WP_PLUGIN_DIR . '/twitchpresspro/pro/' );
            define( 'TWITCHPRESS_PRO_DIR_URL', plugins_url( '/twitchpresspro/pro/') ); 
        }        
    } elseif( ! defined( 'TWITCHPRESS_PRO' ) ) { 
        define( 'TWITCHPRESS_PRO', false ); 
    } 
    
    // Create a request key for tracing/debugging...
    if( !defined( 'TWITCHPRESS_REQUEST_KEY' ) ) { define( 'TWITCHPRESS_REQUEST_KEY', $_SERVER["REQUEST_TIME_FLOAT"] . rand( 10000, 99999 ) ); }
                                        
    // Load object registry class to handle class objects without using $global. 
    include_once( plugin_basename( 'includes/classes/class.twitchpress-object-registry.php' ) );
                     
    // Load core functions with importance on making them available to third-party.                                            
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'functions.php' );
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'deprecated.php' );            
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/functions/functions.twitchpress-formatting.php' );
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/functions/functions.twitchpress-validate.php' );
                      
    // Run the plugin...
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'loader.php' );
    
    register_activation_hook( __FILE__, 'twitchpress_activation_installation' );
    register_deactivation_hook( __FILE__, array( 'TwitchPress_Deactivate', 'deactivate' ) );    
    register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
                
endif;