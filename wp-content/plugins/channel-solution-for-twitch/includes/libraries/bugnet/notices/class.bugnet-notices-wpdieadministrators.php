<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BugNet for WordPress - Safe Administrator wp_die() Notice
 *
 * Use this class to output a detailed display passed to wp_die(). This is
 * a safe approach for passing any information because administrator role
 * is required. 
 *
 * @author   Ryan Bayne
 * @category Notices
 * @package  BugNet/Notices
 * @since    1.0
 */
class BugNet_Notices_WPDIEAdministrators {

    public function emergency() {
        # TODO: Build emergency level content. 
        
        $this->dead_end( $final_string );
    }    
    
    public function alert() {
        # TODO: Build alert level content. 
        
        $this->dead_end( $final_string );
    }    
    
    public function critical() {
        # TODO: Build critical level content. 
        
        $this->dead_end( $final_string );
    }    
    
    public function error() {
        # TODO: Build error level content. 
        
        $this->dead_end( $final_string );
    }    
    
    /**
    * Finally do the wp_die() and output the giving string.
    * 
    * @param mixed $final_string
    * 
    * @version 1.0
    */
    public function dead_end( $final_string ) {
        wp_die( esc_html( $final_string ) ); 
    }

}