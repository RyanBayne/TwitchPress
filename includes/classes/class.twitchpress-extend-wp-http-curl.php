<?php
/**
 * Uses @WP_Http_Curl to make API calls - designed to work with a specific format of data.
 * 
 * This class has been designed as a single procedure with the primary intention of using the entire object
 * in any way required. So the focus is on adding all required data to $this and 
 *
 * @class    TwitchPress_Curl
 * @version  1.0
 * @package  TwitchPress/ Classes
 * @category Class
 * @author   Ryan Bayne
 * 
 * See class.twitchpress-extend-wp-http-curl.php for an alternative class that might replace this.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TwitchPress_Extend_WP_Http_Curl extends WP_Http_Curl {

    /**
    * Call is logged by adding data to $log_record
    */
    public $call_array = array();
    
    /**
    * TwitchPress generates an ID for each request pre-call
    * 
    * @var mixed
    */
    public $call_id = null; 
    
    /**
    * Activate diagnostic method which performs standard validation
    * and can find simple mistakes quickly
    * 
    * @var mixed
    */
    public $troubleshoot = false; 
    
    public function __construct() {
        $this->call_id = twitchpress_get_new_call_id();            
    }   

    public function start_new_request( $client_id, $client_secret, $client_token, $type, $endpoint ) {
        $this->curl_version = curl_version();
        $this->current_user = get_current_user_id();        
        $backtrace = debug_backtrace();
        $this->call_array = array(
            'call_id'       => $this->call_id,
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'client_token'  => $client_token,
            'php_line'      => $backtrace[0]['line'], 
            'php_function'  => $backtrace[0]['function'],
            'php_file'      => $backtrace[0]['file'], 
            'time'          => time(), 
            'type'          => $type,
            'endpoint'      => $endpoint, 
            'headers'       => array(),
            'body'          => array() 
        );        
        
        if( $this->current_user ) {
            $this->call_array = array_merge( $this->call_array, array( 'wp_user_id' => $this->current_user ) );
        }
    }
    
    public function option_headers_additional( $additional_headers_array = array() ){
        $this->call_array['headers'] = array_merge( $this->call_array['headers'], $additional_headers_array );
    }
        
    public function option_body_additional( $additional_body_parameters = array() ) {
        $this->call_array['body'] = array_merge( $this->call_array['body'], $additional_body_parameters );        
    }
    
    /**
    * None header or body values...
    * 
    * @param mixed $args
    * 
    * @version 1.0
    */
    public function option_other_additional( $args = array() ) {
        // We can add in-line defaults here but set them to false...
        $this->misc = array(  
            'user-agent' => 'curl/' . $this->curl_version['version'],
            'stream'     => false,
            'filename'   => false,                             
            'decompress' => true            
        );        
        
        // Combine inline misc with additional custom $args
        $this->additional = wp_parse_args( $args, $this->misc );   
    }
    
    /**
    * Make FINAL changes to the call array i.e. admin options that overwrite ALL calls...
    * 
    * @version 1.0
    */
    public function final_prep() {
        
        // Add values set in option_other_addition()...
        if( isset( $this->additional ) ) {
            $this->prepared_call_array = wp_parse_args( $this->additional, $this->call_array );
        }
        
        /*
            Now apply global override settings for ALL API services...
        */  
    }
        
    /**
    * Executes API call using WP_HTTP_Curl() with additional logging and 
    * processing of the response to provide the most commonly required data. 
    * 
    * @version 2.0
    */
    public function do_call() {                              
        $this->call_array['response'] = $this->request( $this->call_array['endpoint'], $this->prepared_call_array );
    }
}