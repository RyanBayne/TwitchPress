<?php 
/**
 * All-API class helps to manage API consumption in a plugin consuming many API.
 * 
 * @class    TWITCHPRESS_All_API
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/All-API
 * @version  2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Make sure we meet our dependency requirements
if (!extension_loaded('curl')) trigger_error('cURL is not installed on your server, please install cURL to use TwitchPress.');
if (!extension_loaded('json')) trigger_error('PECL JSON or pear JSON is not installed, please install either PECL JSON or compile pear JSON to use TwitchPress.');

if( !class_exists( 'TWITCHPRESS_All_API' ) ) :

class TWITCHPRESS_All_API {

    protected $subject_name = null; /* for Twitch.tv this would be the channel or username used in URL slug */
    protected $subject_id = null; /* In the case of Twitch.tv this is the channel and user ID not the name */
    
    // Configuration & Status
    protected $service = null; /* streamlabs, twitch, streamlements */
    protected $version = null; /* if required provide version number */
    protected $profile = null; /* development transition support i.e. choose kraken or helix */ 
    public $app_ready = false; /* set to true once all credentials established */
    public $response = null;   /* original API service response */
    public $decompress = true; /* set to false if the body should stay compressed */
    
    // App Credentials 
    protected $app_id     = null;
    protected $app_secret = null;
    protected $app_uri    = null; /* redirect resource as submitted during app creation */ 
    protected $app_code   = null;
    protected $app_token  = null;
    protected $app_token_secret = null;
    protected $app_scope  = array();
        
    // User Credentials (optional)
    protected $user_wordpress_id = null;
    protected $user_service_id   = null; 
    protected $user_oauth_code   = null;
    protected $user_oauth_token  = null;
    protected $user_scope        = array();
    
    // Debugging variables.
    public $allapi_call_name = 'Unknown';

    public function __construct( $service = 'twitch', $profile = 'default' ){ 
        $this->set_application( $profile );
        $this->service = strtolower( $service );
        $this->profile = strtolower( $profile );
        
        // Set the current or queried user credentials.
        $this->set_user_credentials();
    } 
    
    public function set_application( $profile = 'default' ) {        
        $this->app_uri    = get_option( 'twitchpress_allapi_' . $service . '_' . $profile . '_uri', null );
        $this->app_id     = get_option( 'twitchpress_allapi_' . $service . '_' . $profile . '_id', null );
        $this->app_secret = get_option( 'twitchpress_allapi_' . $service . '_' . $profile . '_secret', null );
        $this->app_token  = get_option( 'twitchpress_allapi_' . $service . '_' . $profile . '_access_token', null );
        $this->app_code   = get_option( 'twitchpress_allapi_' . $service . '_' . $profile . '_code', null );
    }    
    
    /**
    * Sets user API credentials.
    * 
    * @version 1.0
    */
    public function set_user_credentials( $user_id = null ) {
        if( !$user_id || !is_numeric( get_current_user_id() ) ) {
            if( !is_user_logged_in() ) { return false; }
            $user_id = get_current_user_id();
        }
        
        $this->user_access_token     = get_user_meta( $user_id, 'twitchpress_allapi_access_token_' . $this->service, true );    
        $this->user_refresh_token    = get_user_meta( $user_id, 'twitchpress_allapi_refresh_token_' . $this->service, true );
        $this->user_token_lifetime   = get_user_meta( $user_id, 'twitchpress_allapi_token_lifetime' . $this->service, true );
        $this->user_token_created_at = get_user_meta( $user_id, 'twitchpress_allapi_token_created_at_' . $this->service, true );
        $this->user_scope     = get_user_meta( $user_id, 'twitchpress_allapi_scope_' . $this->service, true );
        
        return true;
    }
    
    /**
    * Create a new HTTP Curl object using WP_Http_Curl(). 
    * 
    * @param mixed $type is GET,PUT,POST,DELETE
    * @param url $endpoint
    * @param array $headers
    * @param array $body
    * @param mixed $additional
    * 
    * @return TwitchPress_Extend_WP_Http_Curl() class object which extends class WP_Http_Curl()
    * 
    * @version 1.0
    */
    public function call_service( $type, $endpoint ) {
        // Create new curl object that includes our custom call values...
        // This involves a custom TwitchPress class that uses class WP_Http_Curl()... 
        $new_curl = new TwitchPress_Extend_WP_Http_Curl();                      
        
        $new_curl->start_new_request( 
            $this->app_id,
            $this->app_secret,               
            $this->app_token,
            $type,                                                                       
            $endpoint
        );                  

        // Add headers if the default does not add them in the current package...
        $new_curl->option_headers_additional();
                 
        // Add body parameters if the package hasn't been designed to add them automatically...
        $new_curl->option_body_additional(); 
                    
        // Now add miscellanous values that will make up our curl request...
        $new_curl->option_other_additional();    
               
        // Final prep on call array including global override settings...
        $new_curl->final_prep(); 

        // Exectute the request, which is finally done by WP_Http_Curl()...
        $new_curl->do_call(); 

        $new_curl->call_array['response']['body'] = json_decode( $new_curl->call_array['response']['body'] );

        return $new_curl->call_array['response'];        
    }  
            
    public function update_main_code( $code ) {
        update_option( 'twitchpress_' . $this->service . '_main_code', $code );    
    }    
    
    public function update_main_owner( $wp_user_id ) {
        update_option( 'twitchpress_' . $this->service . '_main_owner', $wp_user_id );    
    }  

    public function update_main_access_token( $access_token ) {
        update_option( 'twitchpress_' . $this->service . '__main_access_token', $access_token );
    }
    
    public function update_main_expires_in( $expires_in ) {
        update_option( 'twitchpress_' . $this->service . '_main_expires_in', $expires_in );        
    }
    
    public function update_main_refresh_token( $refresh_token ) {
        update_option( 'twitchpress_' . $this->service . '_main_refresh_token', $refresh_token );        
    }

    public function update_user_code( $wp_user_id, $code ) {
        update_user_meta( $wp_user_id, 'twitchpress_' . $this->service . '_code', $code );  
    }
    
    public function update_user_access_token( $wp_user_id, $access_token ) {
        update_user_meta( $wp_user_id, 'twitchpress_' . $this->service . '_access_token', $access_token );
    }
    
    public function update_user_expires_in( $wp_user_id, $expires_in ) {
        update_user_meta( $wp_user_id, 'twitchpress_' . $this->service . '_expires_in ', $expires_in );
    }
        
    public function update_user_refresh_token( $wp_user_id, $refresh_token ) {
        update_user_meta( $wp_user_id, 'twitchpress_' . $this->service . '_refresh_token', $refresh_token );
    }
    
    public function update_user_scope( $wp_user_id, $scope ) {
        update_user_meta( $wp_user_id, 'twitchpress_' . $this->service . '_scope', $scope );
    }

    public function get_main_code( $code ) {
        return get_option( 'twitchpress_' . $this->service . '_main_code', $code );    
    }    
    
    public function get_main_owner() {
        return get_option( 'twitchpress_' . $this->service . '_main_owner' );    
    }  

    public function get_main_access_token() {
        return get_option( 'twitchpress_' . $this->service . '_main_access_token' );
    }
    
    public function get_main_expires_in() {
        return get_option( 'twitchpress_' . $this->service . '_main_expires_in' );        
    }
    
    public function get_main_refresh_token() {
        return get_option( 'twitchpress_' . $this->service . '_main_refresh_token' );        
    }

    public function get_user_code() {
        return get_user_meta( $wp_user_id, 'twitchpress_' . $this->service . '_code', true );  
    }
    
    public function get_user_access_token() {
        return get_user_meta( $wp_user_id, 'twitchpress_' . $this->service . '_access_token', true );
    }
    
    public function get_user_expires_in() {
        return get_user_meta( $wp_user_id, 'twitchpress_' . $this->service . '_expires_in ', true );
    }
        
    public function get_user_refresh_token() {
        return get_user_meta( $wp_user_id, 'twitchpress_' . $this->service . '_refresh_token', true );
    }
    
    public function get_user_scope() {
        return get_user_meta( $wp_user_id, 'twitchpress_' . $this->service . '_scope', true );
    }
       
    public function get_main_users_wp_id() {
        return $this->get_main_owner();    
    }
         
    /**
    * Returns an array of scopes with user-friendly form input labels and descriptions.
    * 
    * @author Ryan R. Bayne
    * @version 1.23
    */
    public function scopes( $scopes_only = false) {
        return array();  
    }   

    /**
    * Checks if minimum application credentials are set and ready in object.
    * 
    * @returns boolean
    * 
    * @version 2.0
    */
    public function is_app_set() {
                                           
        if( !$this->app_id ) {           
            $this->app_ready = false;
            return false;        
        } 

        if( !$this->streamlabs_app_secret ) {   
            $this->app_ready = false;
            return false;        
        } 
                 
        if( !$this->streamlabs_app_uri ) {     
            $this->app_ready = false;
            return false;        
        }    

        if( !$this->streamlabs_app_token ) {    
            $this->app_ready = false;
            return false;        
        }       

        $this->app_ready = true;
        return true;
    }
    
    public function app_missing_values() {
        $missing = array(); 

        if( !$this->app_id ) {
            $missing[] = __( 'Client ID', 'twitchpress' );        
        } 

        if( !$this->app_secret ) {
            $missing[] = __( 'Client Secret', 'twitchpress' );        
        } 
                 
        if( !$this->app_uri ) {
            $missing[] = __( 'Client URI', 'twitchpress' );        
        }    

        if( !$this->app_token ) {
            $missing[] = __( 'Client Token', 'twitchpress' );        
        }       

        return $missing;        
    }
            
    /**
    * Generate a locally stored state with unique ID for the listener to identify 
    * server response and use the state data to secure the request...
    * 
    * @param mixed $attributes
    * 
    * @version 1.0
    */
    public function new_state( $attributes ) {
         $default = array( 
            'redirectto' => admin_url( 'index.php?page=twitchpress-setup&step=next_steps' ),
            'userrole'   => null,
            'outputtype' => 'admin',// use to configure output levels, sensitivity of data and styling.
            'reason'     => 'oauth2request',// use in conditional statements to access applicable procedures.
            'function'   => __FUNCTION__,
            'statekey'   => twitchpress_random14(),// add this to the "state=" value of the API request.
        ); 
        $final = wp_parse_args( $attributes, $default );  
        set_transient( 'twitchpress_streamlabs_oauthstate_' . $default['statekey'], $final ); 
        return $final; 
    }
}

endif;