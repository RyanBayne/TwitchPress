<?php
/**
 * BugNet for WordPress - Admin Side wp_die() for Public (logged in or not)
 */
 
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * BugNet for WordPress - Admin Side wp_die() for Public (logged in or not)
 *
 * Use to output potentially detailed information within a wp_die() view. 
 * Pass information that any visitor can see though and nothing sensitive. 
 * This allows us to display a serious fault view to any visitor but encourage 
 * them to focus on information that can help them continue. 
 *
 * @author   Ryan Bayne
 * @category Core
 * @package  BugNet
 * @since    1.0
 */
class BugNet_Notices_WPDIEPublic {
    
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