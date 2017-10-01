<?php
/**
 * TwitchPress Admin - Quick Debuging Class 
 * 
 * Including this class starts debugging. The level
 * and depth of debugging depends on configuration.
 * 
 * @class    TwitchPress_Admin
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Admin
 * @version  2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( !class_exists( 'TwitchPress_Debug' ) ) :
                              
function bugnet_current_user_allowed() {
    if( twitchpress_is_background_process() ) { 
        return false; 
    }        
    
    if( !get_option( 'twitchpress_displayerrors' ) || get_option( 'twitchpress_displayerrors' ) !== 'yes' ) {
        return false;
    }

    if( !current_user_can( 'activate_plugins' ) ) {
        return false;
    }
    
    if( get_current_user_id() != get_option( 'bugnet_error_dump_user_id') ) {
        return false;    
    }   
    
    return true;
}

class TwitchPress_Error_Dump {
    
    public function var_dump( $var ) {
        if( !bugnet_current_user_allowed() ) { return false; }
        echo '<pre>'; var_dump( $var ); echo '</pre>';    
    }    
    
    public function wp_die( $html ) {
        if( !bugnet_current_user_allowed() ) { return false; }
        wp_die( esc_html( $html ) );    
    }
    
    /**
    * Dump errors for quick debugging. 
    * 
    * @version 2.6
    */
    public function dump_errors() {
        if( !bugnet_current_user_allowed() ) { return false; }

        global $wpdb;
     
        ini_set( 'display_errors', 1 );
        error_reporting(E_ALL);      
        
        $wpdb->show_errors();
        $wpdb->print_error();
    }
    
    /**
    * Dump $_POST
    */
    private function dump_post() {
        if( !bugnet_current_user_allowed() ) { return false; }
        
        echo '<h1>$_POST</h1>';
        echo '<pre>';
        var_dump( $_POST );
        echo '</pre>';
    }   
      
    /**
    * Dump $_GET
    */
    private function dump_get() {
        if( !bugnet_current_user_allowed() ) { return false; }
        
        echo '<h1>$_GET</h1>';
        echo '<pre>';
        var_dump( $_GET );
        echo '</pre>';
    }        
}

endif; 

$dump = new TwitchPress_Error_Dump();
$dump->dump_errors();