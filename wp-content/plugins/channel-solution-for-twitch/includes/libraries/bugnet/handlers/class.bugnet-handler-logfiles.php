<?php
/**
 * BugNet for WordPress - Log files Handler
 * 
 * Use for generating custom log files other than WordPress core debug.txt
 * and server PHP log file.
 *
 * Traces are output to their own individual folders.
 * Daily log files are generated for standard PHP errors.          
 */
 
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * BugNet for WordPress - Log files Handler
 * 
 * Use for generating custom log files other than WordPress core debug.txt
 * and server PHP log file.
 *
 * Traces are output to their own individual folders.
 * Daily log files are generated for standard PHP errors.          
 *
 * @author   Ryan Bayne
 * @category Handler
 * @package  BugNet/Handler
 * @since    1.0
 */
class BugNet_Handler_LogFiles extends BugNet {

    public function __construct() {
        $this->todays_string = date( 'd-M-Y');   
    }
    
    /**
    * Standard error_log approach which we can use to bypass
    * 
    * Types
    * 0    message is sent to PHP's system logger, using the Operating System's system logging mechanism or a file, depending on what the error_log configuration directive is set to. This is the default option.
    * 1    message is sent by email to the address in the destination parameter. This is the only message type where the fourth parameter, extra_headers is used.
    * 2    No longer an option.
    * 3    message is appended to the file destination. A newline is not automatically added to the end of the message string.
    * 4    message is sent directly to the SAPI logging handler.
    * 
    * @param mixed $message
    * @param mixed $message_type
    * 
    * @version 2.0
    */
    public function system_log( $message, $error = false  ) {
        if( !parent::is_system_logging_on() ) { return; }
        $prepend = 'TwitchPress: ';
        if( $error) {$prepend = 'TwitchPress Error: ';}
        error_log ( $prepend . $message );            
    } 
    
    public function get_daily_log_path() {
        return BUGNET_LOG_DIR . 'daily/' . $this->todays_string . '.csv'; 
    }
    
    public function new_line_daily( $tag, $message, $args  ) {
        wp_mkdir_p( BUGNET_LOG_DIR . 'daily/' );
        
        $fp = @fopen( $this->get_daily_log_path() , 'a' );

        if( !$fp ) { return new WP_Error( 'bugnetdailyfileopenfailed', __( 'fopen() has returned false when trying to open .csv file to append new line.', 'bugnet' ) ); }

        // More default arguments. These activate outputs. 
        $defaults = array(
            'time'     => time(),
            'tag'      => $tag,
            'message'  => $message,
            'line'     => 'X',
            'function' => 'X',
            'file'     => 'X',
            'userid'   => 'X',            
        );

        $fields = wp_parse_args( $args, array_merge( $defaults, $defaults ) );

        fputcsv( $fp, array_values( $fields ) );

        fclose( $fp );
    }
    
    public function cleanup() {
        # TODO: Delete some daily log files.    
    }   
    
    public function is_daily_logfile_on() {
        # TODO: Has separate daily log been activated.         
    }
    
    public function is_daily_logfile_present() {
        # TODO: Check if the daily logfile is present or not.         
    }
    
    public function create_logfile() {
        # TODO: Create a .txt file     
    }   
}