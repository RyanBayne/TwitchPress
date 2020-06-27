<?php
/**
 * Giveaways system in the TwitchPress plugin for WordPress
 *
 * @version 0.1.1
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const TWITCHPRESS_GIVEAWAYS_VERSION = '1.0.0';
   
require_once( plugin_basename( '/functions.twitchpress-giveaways.php' ) );
                                                           
// Main class is not needed until installation is performed...
if( !get_option( 'twitchpress_giveaways_switch' ) ) {return;}

if( get_option( 'twitchpress_giveaways_switch' ) == 'no' ) {return;}

// Include the entire Giveaways System...
include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'systems/giveaways/functions.twitchpress-raffles-shortcodes.php' );
                        
if( !class_exists( 'TwitchPress_Giveaways' ) ) :

class TwitchPress_Giveaways {

    public function __construct() {
        $this->config = new Twitchpress_Giveaways_Configuration();
    }    
    
    public function init() {

    }
}

endif;