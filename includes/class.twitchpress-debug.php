<?php
/**
 * TwitchPress Admin - Debuging Class 
 * 
 * Including this class starts debugging. The level
 * and depth of debugging depends on configuration.
 * 
 * @class    TwitchPress_Admin
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( !class_exists( 'TwitchPress_Debug' ) ) :

class TwitchPress_Debug {
    
    /**
    * Old Error display and debugging method from 2015 - will be replaced. 
    */
    public function debugmode() {
        if( twitchpress_is_background_process() ) return; 
            
        global $wpdb;
        
        ini_set( 'display_errors',1);
        error_reporting(E_ALL);      
        
        $wpdb->show_errors();
        $wpdb->print_error();
    }
    
    /**
    * Dump $_POST
    */
    private function dump_post() {
        if( !current_user_can( 'activate_plugins') ) return;

        echo '<h1>$_POST</h1>';
        echo '<pre>';
        var_dump( $_POST );
        echo '</pre>';
    }   
      
    /**
    * Dump $_GET
    */
    private function dump_get() {
        if( !current_user_can( 'activate_plugins') ) return;

        echo '<h1>$_GET</h1>';
        echo '<pre>';
        var_dump( $_GET );
        echo '</pre>';
    }        
}

endif; 