<?php
/**
 * TwitchPress - Twitch API application credentials are set here ONLY!
 * 
 * Class is initiated and added to the object registry for use by any plugin within
 * any request. In theory this class will be initiated once during any page request
 * but we can use it to check on app status.
 * 
 * @author   Ryan Bayne
 * @category Scripts
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Set_App' ) ) :

class TwitchPress_Set_App {
    
    // Twitch API application credentials 
    public $app_id = null; 
    public $app_secret = null;
    public $app_redirect = null;
    public $app_token = null;
    
    /**
    * Scopes to be accepted by visitors for this application. Scopes are not
    * required by the application itself. 
    * 
    * @var mixed
    */
    public $app_scopes = null;
    public $app_ready = true;

    public function set() {
        $this->app_id           = get_option( 'twitchpress_app_id', 0 ); 
        $this->app_secret       = get_option( 'twitchpress_app_secret', 0 );
        $this->app_redirect     = get_option( 'twitchpress_app_redirect', 0 );
        $this->app_token        = get_option( 'twitchpress_app_token', 0 );        
        $this->app_scopes       = get_option( 'twitchpress_app_scopes' ); 
        $this->app_expiry       = get_option( 'twitchpress_app_expiry', 0 ); 
        $this->app_status       = $this->status( false );
         
        if( !$this->app_token && $this->app_id && $this->app_secret && $this->app_redirect ) 
        {
            add_action( 'init', array( $this, 'missing_token' ), 5 );     
        }
        else
        {
            // Ensure token is still valid else request new one...
            add_action( 'init', array( $this, 'validate_token' ), 5 );
        } 
    }
    
    public function status( $code = false ) {                            

        if( !$this->app_id && !$this->app_secret && !$this->app_redirect && !$this->app_token && !$this->app_scopes && !$this->app_expiry ) 
        {
            if( $code ){ return 0; }
            return array( 0, __( 'Twitch application credentials have not been setup. Please complete the Setup Wizard.', 'twitchpress' ) );        
        }
        
        if( !$this->app_id )
        {
            if( $code ){ return 2; }
            return array( 2, __( 'Twitch application ID has not been stored. Correct this by completing the Setup Wizard.', 'twitchpress' ) );
        }
        
        if( !$this->app_secret ) 
        {
            if( $code ) { return 3; }
            return array( 3, __( 'Twitch application secret has not been stored. You can fix this by completing the Setup Wizard or in Settings.', 'twitchpress' ) );           
        }        
        
        if( !$this->app_redirect ) 
        {
            if( $code ) { return 4; }
            return array( 4, __( 'Twitch application needs a redirect value. You can resolve this in Settings under the Twitch API tab.', 'twitchpress' ) );
            
        }
        
        if( !$this->app_scopes ) 
        {
            if( $code ) { return 5; }
            return array( 5, __( 'No scope/permissions have been stored for the Twitch application. You can add those in the Setup Wizard and Settings.', 'twitchpress' ) );
            
        }

        if( !$this->app_token ) 
        {
            if( $code ) { return 6; }
            return array( 6, __( 'No token was found for the Twitch API application. This should be generated automatically and is an issue that needs immediate attention.', 'twitchpress' ) );
        }
        
        // Returning 1 is suitable for a positive result (true)... 
        if( $code ){ return 1; }
        return array( 1, __( 'Twitch API application is ready!', 'twitchpress' ) );
    }
    
    public function get( $value = null ) {
        if( $value ) {
            return eval( '$this->main_app_$value' );
        }   
        return array(
            'id'       => $this->app_id,
            'secret'   => $this->app_secret,
            'redirect' => $this->app_redirect,
            'token'    => $this->app_token,
            'scopes'   => $this->app_scopes
        );
    }

    public function missing_token() {

        // Create our own special Curl object which uses WP_Http_Curl()
        $call_object = new TwitchPress_Curl();
        $call_object->originating_file = __FILE__;
        $call_object->originating_function = __FUNCTION__;
        $call_object->originating_line = __LINE__;
        $call_object->type = 'POST';
        
        if( TWITCHPRESS_API_NAME == 'kraken' ) 
        {
            $call_object->endpoint = 'https://api.twitch.tv/kraken/oauth2/token?client_id=' . twitchpress_get_app_id();
        }
        else
        {
            $call_object->endpoint = 'https://id.twitch.tv/oauth2/token';   
        }
        
        // Set none API related parameters i.e. cache and rate controls...
        $call_object->call_params(  
            false, 
            0, 
            false, 
            null, 
            false, 
            false,
            __FUNCTION__,
            __LINE__ 
        );
        
        // Add app credentails to the request body
        $call_object->set_curl_body( array(
            'client_id'        => $this->app_id,
            'client_secret'    => $this->app_secret,
            'redirect_uri'     => $this->app_redirect,
            'grant_type'       => 'client_credentials'
        ) );

        // Start + make the request to Twitch.tv API in one line... 
        $call_object->do_call( 'twitch' );
        
        // Was the access_token value in $curl_reply_body set? 
        if( !isset( $call_object->curl_reply_body->access_token ) ) {
            return false;
        }
               
        if( !isset( $call_object->curl_reply_body->expires_in ) ) {
            return false;
        }
        
        // Update option record and object registry...            
        twitchpress_update_app_token( $call_object->curl_reply_body->access_token );
        twitchpress_update_app_token_expiry( $call_object->curl_reply_body->expires_in ); 

        // Update this objects app_token value...
        $this->app_token = $call_object->curl_reply_body->access_token;
    }

    public function validate_token() {
                     
        // Lets be sure we actually have a token...
        if( !$this->app_token ) {
            return __( 'Token validation could not be performed because the app access token was not set and may not exist!', 'twitchpress' );    
        }
        
        // Create our own special Curl object which uses WP_Http_Curl()
        $call_object = new TwitchPress_Curl();
        
        
        $call_object->originating_file = __FILE__;
        $call_object->originating_function = __FUNCTION__;
        $call_object->originating_line = __LINE__;
        $call_object->type = 'GET';
        
        if( TWITCHPRESS_API_NAME == 'kraken' ) 
        {
            $call_object->endpoint = 'https://id.twitch.tv/oauth2/validate';
        }
        else
        {
            $call_object->endpoint = 'https://id.twitch.tv/oauth2/validate';   
        }
            
        // Set none API related parameters i.e. cache and rate controls...
        $call_object->call_params( 
            false, 
            0, 
            false, 
            null, 
            false, 
            false, 
            __FUNCTION__,
            __LINE__ 
        );
        
        if( TWITCHPRESS_API_NAME == 'kraken' ) 
        {
            // Add the access_token as an OAuth header...
            $call_object->headers = array(
                'Authorization' => 'OAuth ' . $this->app_token,
                'Accept'        => 'application/vnd.twitchtv.v5+json'
            );
        }
        else
        {                            
            // Add the access_token as an OAuth header...
            $call_object->headers = array(
                'Authorization' => 'OAuth ' . $this->app_token,
                //'Accept'        => 'application/vnd.twitchtv.v5+json'
            );   
        }
        
        // Start + make the request to Twitch.tv API in one line... 
        $call_object->do_call( 'twitch' );
        
        // Was the access_token value in $curl_reply_body set? 
        if( isset( $call_object->response_code ) && $call_object->response_code == '200' ) {
            return true;
        }
 
        // Request a new access_token (stored as app_token in the TwitchPress system)...
        $this->new_token();
    }
    
    public function new_token() {
 
        // Create our own special Curl object which uses WP_Http_Curl()
        $call_object = new TwitchPress_Curl();
        $call_object->originating_function = __FILE__;
        $call_object->originating_function = __FUNCTION__;
        $call_object->originating_line = __LINE__;
        $call_object->type = 'POST';
        
        if( TWITCHPRESS_API_NAME == 'kraken' ) 
        {
            $call_object->endpoint = 'https://api.twitch.tv/kraken/oauth2/token?client_id=' . twitchpress_get_app_id();
        }
        else
        {
            $call_object->endpoint = 'https://id.twitch.tv/oauth2/token';   
        }

        // Set none API related parameters i.e. cache and rate controls...
        $call_object->call_params( 
            false, 
            0, 
            false, 
            null, 
            false, 
            false, 
            __FUNCTION__,
            __LINE__ 
        );
        
        // Add app credentails to the request body
        $call_object->set_curl_body( array(
            'client_id'        => $this->app_id,
            'client_secret'    => $this->app_secret,
            'redirect_uri'     => $this->app_redirect,
            'grant_type'       => 'client_credentials'
        ) );

        // Start + make the request to Twitch.tv API in one line... 
        $call_object->do_call( 'twitch' );
        
        // Was the access_token value in $curl_reply_body set? 
        if( !isset( $call_object->curl_reply_body->access_token ) ) {
           return false;
        }

        // Update option record and object registry...            
        twitchpress_update_app_token( $call_object->curl_reply_body->access_token );
        twitchpress_update_app_token_expiry( $call_object->curl_reply_body->expires_in ); 

        return __( 'New access_token and expiry time stored.', 'twitchpress' );               
    }
}

endif;

function twitchpress_init_main_app() {
    $set_app = new TwitchPress_Set_App();
    $set_app->set();
    TwitchPress_Object_Registry::add( 'twitchapp', $set_app );    
}

// Priority needs to put app setup before most other things in TwitchPress...
add_action( 'init', 'twitchpress_init_main_app', 1 );