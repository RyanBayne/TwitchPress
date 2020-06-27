<?php
/**
 * TwitchPress Pro
 * 
 * This is the primary file for the TwitchPress upgrade that adds professional
 * level features and tools to WordPress. These files are not updated by
 * WordPress.org and are only provided to backers to myself Ryan Bayne, gaming
 * handle ZypheREvolved. 
 * 
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'TwitchPress_Pro' ) ) :

class TwitchPress_Pro {
    
    var $pro_version = '1.0';
    
    static function init() {
        add_action( 'init', array( __CLASS__, 'include_functions' ), 0 );
    }
    
    static function include_functions() {
        include_once( plugin_basename( 'twitchpress-pro-core-functions.php' ) );    
    }
}

endif;


