<?php
/**
 * BugNet for WordPress (library, not a plugin)
 * 
 * Add to existing plugins or themes or turn into a plugin on it's own.
 * OR just use my plugins and save time. 
 *
 * @author Ryan Bayne
 * @license GNU General Public License, Version 3
 * @copyright 2017 - 2019 Ryan R. Bayne (SqueekyCoder@Gmail.com)
 * @version 0.1.1
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const BUGNET_VERSION = '2.0.0';
   
require_once( plugin_basename( '/functions.bugnet.php' ) );
                                                                   
// Main class is not needed until installation is performed...
if( !get_option( 'bugnet_version' ) ) {return;}
                        
if( !class_exists( 'BugNet' ) ) :

/**
 * BugNet for WordPress - Main class for loading BugNet
 *
 * @author   Ryan Bayne
 * @category Core
 * @package  BugNet/Core
 * @since    1.0
 */
class BugNet {

    /**
    * This will hold the WP_Error object. Use for traces when
    * each entry is within a single request i.e. ensure there are
    * no re-directs else initial trace entries will be lost.  
    * 
    * @var mixed
    */
    public $bugnet_wp_errors = null;
    
    public $log_directory = '';

    public function __construct() {

        // Include files on a per service basis...
        bugnet_includes();
        
        // Begin custom configuration here...
        $this->config = new BugNet_Configuration();
    }    
    
    public function init() {

    }
 
    /**
     * Add a simple log entry with optional handles to
     * build more detailed logs and share the logs i.e. to a central
     * WordPress database acting as the main site in a network of sites. 
     * 
     * @version 1.6
     */
    public function log( $tag, $message, $args = array(), $flood_prevention = true, $error = false ) {
         
        // Confirm administrator setting for this service is 'yes'. 
        if( $this->config->is_logging_enabled !== 'yes' ) { return; }
           
        // Set our default arguments to meet criteria for the outputs we use. 
        $defaults = array( 
            'systemlog'            => true,
            'line'                 => '',
            'function'             => '',
            'file'                 => '',
            'level'                => '100'
        );    
        $args = wp_parse_args( $args, $defaults );
          
        // Do not log if the giving level is not active.
        if( !$this->config->is_active_level( $args['level'] ) ) { return; }  
                    
        // Force a delay between the same log being reported. 
        if( !$this->is_log_allowed( $tag ) ) {
            return;
        }
         
        if( isset( $args['error'] ) && $args['error'] === true || $error === true ) {
            return new WP_Error( $tag, $message, $args );        
        }
    }   
    
    public static function log_2019( $tag, $message, $atts = array(), $flood_prevention = true ) {
          
        if( !get_option( 'bugnet_activate_log', false ) ) { return; }
                                                      
        $defaults = array( 
            'systemlog'            => true,
            'dailylog'             => true,
            'restapi'              => false,
            'wpdb'                 => false,
            'line'                 => '',
            'function'             => '',
            'file'                 => '',
            'level'                => '100'
        );    
        $args = wp_parse_args( $arguments, $defaults );
        
        // Rule out reasons to deny request...
        if( !BugNet_Configuration::is_active_level( $args['level'] ) ){ return; }      
        if( !self::is_log_allowed( $tag ) ){ return; }
                          
        // Write to system log...
        if( $args['systemlog'] ) { BugNet_Handler_LogFiles::system_log( $tag . ': ' . $message ); }

        // Return an error so that "return log()" can be used at the point of error.
        if( isset( $args['error'] ) && $args['error'] === true || $error === true ) {
            return new WP_Error( $tag, $message, $args );        
        }    
    }
    
    /**
    * Calls $this->log() and passes error parameter. 
    * 
    * @uses $this->log()
    * 
    * @param mixed $tag
    * @param mixed $message
    * @param mixed $atts
    * @param mixed $flood_prevention
    * 
    * @version 1.0
    */
    public function log_error( $tag, $message, $atts = array(), $flood_prevention = true ) {
        $this->log( $tag, $message, $atts, $flood_prevention, true  );    
    }
    
    public static function log_error_2019( $tag, $message, $atts = array(), $flood_prevention = true ) {
        self::log_2019( $tag, $message, $atts, $flood_prevention, true );      
    }

    /**
    * Use to establish a delay between duplicate log entries. This
    * is a measure to prevent abuse by flooding logs. 
    * 
    * @param mixed $tag
    * 
    * @version 1.0
    */
    public function is_log_allowed( $tag ) {  
        ### need to check for a short-life transient        
    }
    
    /**
    * Create a transient used to force delays been duplicate log entries.
    * This is done based on the tag alone and is a flood prevention ethod.
    * 
    * @param mixed $tag
    * @param mixed $seconds
    * 
    * @version 1.0
    */
    public function create_shortlife_log_transient( $tag, $seconds = 10 ) {
        set_transient( 'bugnet_log_' . $tag, array( 'time' => time() ), $seconds );    
    }

    public static function is_system_logging_on(){
        return get_option( 'bugnet_systemlogging_switch', false );
    }
}

endif;