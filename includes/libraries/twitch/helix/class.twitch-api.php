<?php
/**
 * Twitch API Helix for WordPress
 * 
 * WARNING: Still contains Kraken endpoints due to Twitch devs still not having
 * enough endpoints in the newer API
 * 
 * Do not use this class unless you accept the Twitch Developer Services Agreement
 * @link https://www.twitch.tv/p/developer-agreement
 * 
 * @author   Ryan Bayne
 * @version 6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Make sure we meet our dependency requirements
if (!extension_loaded('curl')) trigger_error('cURL is not currently installed on your server, please install cURL if your wish to use Twitch services in TwitchPress.');
if (!extension_loaded('json')) trigger_error('PECL JSON or pear JSON is not installed, please install either PECL JSON or compile pear JSON if you wish to use Twitch services in TwitchPress.');

if( !class_exists( 'TwitchPress_Twitch_API' ) ) :

class TwitchPress_Twitch_API {
    
    /**
    * Post-request boolean value for tracking the calls purpose
    * and ability to meet requirements. 
    * 
    * @var mixed
    */
    public $call_result = false; 
    
    // Debugging variables.
    public $twitch_call_name = 'Unknown';

    // Public notice assistance (built outside of this class)...
    public $public_notice_title = null;
    public $public_notice_actions = array();
    
    // Administrator notice creation (built within this class)...
    public $admin_notice_title = null;      // Usually a string of text
    public $admin_notice_body = null;       // Usually just a string of text
    public $admin_notice_actions = array(); // Multiple actions may be offered 
    public $admin_user_request = false;     // true triggers output for the current admin user
    
    /**
    * Twitch API Version 6 Scopes
    * 
    * @var mixed
    */
    public $twitch_scopes = array( 
            'channel_check_subscription',
            'channel_commercial',
            'channel_editor',
            'channel_read',
            'channel_stream',
            'channel_subscriptions',
            'collections_edit',
            'communities_edit',
            'communities_moderate',
            'user_blocks_edit',
            'user_blocks_read',
            'user_follows_edit',
            'user_read',
            'user_subscriptions',
            'viewing_activity_read',
            'openid',
            'analytics:read:extensions', // View analytics data for your extensions.
            'analytics:read:games',      // View analytics data for your games.
            'bits:read',                 // View Bits information for your channel.
            'clips:edit',                // Manage a clip object.
            'user:edit',                 // Manage a user object.
            'user:edit:broadcast',       // Edit your channel’s broadcast configuration, including extension configuration. (This scope implies user:read:broadcast capability.)
            'user:read:broadcast',       // View your broadcasting configuration, including extension configurations.
            'user:read:email',           // Read authorized user’s email address.
                        
    );
  
    /**
    * Array of endorsed channels, only partnered or official channels will be 
    * added here to reduce the risk of unwanted/nsfw sample content. 
    * 
    * @var mixed
    * 
    * @version 1.0
    */
    public $twitchchannels_endorsed = array(
        'lolindark1'  => array( 'display_name' => 'LOLinDark1' ),
        'nookyyy'     => array( 'display_name' => 'nookyyy' ),        
        'starcitizen' => array( 'display_name' => 'StarCitizen' ),
    );

    public function __construct(){
        $curl_info = curl_version();
        $this->curl_version = $curl_info['version'];         
    } 
    
    /**
    * Creates the $this->call_object using class TwitchPress_Curl()
    * and it is after this method we can add our options/parameters.
    * 
    * We then use $this->call() to execute. 
    * 
    * @version 1.0
    */
    public function curl( $file, $function, $line, $type = 'get', $endpoint ) {
        
        // Create our own special Curl object which uses WP_Http_Curl()
        $this->curl_object = new TwitchPress_Curl();
        $this->curl_object->originating_file = $file;
        $this->curl_object->originating_function = $function;
        $this->curl_object->originating_line = $line;
        $this->curl_object->type = $type;
        $this->curl_object->endpoint = $endpoint;
                
        // Add none API related parameters to the object...
        $this->curl_object->call_params(  
            false, 
            0, 
            false, 
            null, 
            false, 
            false, 
            __FUNCTION__,
            __LINE__ 
        );

        // Add common/default headers...
        $this->curl_object->add_headers( array(
            'Client-ID' => twitchpress_get_app_id(),
            'Authorization' => 'Bearer ' . twitchpress_get_app_token(),
        ) );
    }   
    
    /**
    * Using the values in $this->call_object execute a call to Twitch. 
    *  
    * @version 1.0
    */
    function call() {
        // Decide between kraken and helix 
        $this->set_accept_header();
        
        // Start + make the request to Twitch.tv API in one line... 
        $this->curl_object->do_call( 'twitch' );
           
        if( isset( $this->curl_object->response_code ) && $this->curl_object->response_code == '200' ) {
            // This will tell us that we should expect our wanted data to exist in $call_object
            // and we can use $this->call_result to assume that any database insert/update has happened also
            $this->curl_object->call_result = true;
        }
        else 
        {    
            $this->curl_object->call_result = false; 

            if( !isset( $this->curl_object->response_code ) ) {
                // __( 'Response code not returned! Call ID [%s]', 'twitchpress' ), $this->curl_object->get_call_id() ), array(), true, false );            
            }
       
            if( $this->curl_object->response_code !== '200' ) {   
                // __( 'Response code [%s] Call ID [%s]', 'twitchpress' ), $this->curl_object->response_code, $this->curl_object->get_call_id() ), array(), true, false );            
            }
        }
    }  
    
    /**
    * Checks if application credentials are set.
    * 
    * @returns boolean true if set else an array of all the missing credentials.
    * 
    * @version 1.0
    */
    public function is_app_set() {
        
        /*
            Incomplete - added temporarily to solve login error...
            
            Originally in the Kraken version of this class...
            
            The values being checked are not available in this class and
            so we probably need to access the object registry directly to
            perform this check-up
        */
        
        return true;
    }        
              
    public function set_accept_header() {
        if( !isset( $this->curl_object->headers['Accept:'] ) ) {
            $this->curl_object->add_headers( array(
                //'Accept:' => 'Accept: application/vnd.twitchtv.v6+json',
            ) );            
        }
    }
    
    /**
    * Create a new HTTP Curl object with default Twitch app credentials.
    * 
    * You can easily use the contents of this function to create a custom
    * function outside of this class.
    * 
    * @param mixed $type is GET,PUT,POST,DELETE
    * @param mixed $endpoint
    * @param mixed $headers
    * @param mixed $body
    * @param mixed $additional
    * 
    * @return TwitchPress_Extend_WP_Http_Curl
    * 
    * @version 2.0 - Renamed Twitch_Request from WP_HTTP_Curl() 
    */
    public function Twitch_Request( $type, $endpoint, $headers = array(), $body = array(), $additional = array() ) {
        // Create new curl object for performing an API call...
        $new_curl = new TwitchPress_Extend_WP_Http_Curl();
        $new_curl->start_new_request(
            twitchpress_get_app_id(),
            twitchpress_get_app_secret(),
            twitchpress_get_app_token(),
            $type,                                                                       
            $endpoint
        ); 
        
        // Add headers if the default does not add them in the current package...
        $new_curl->option_headers_additional( $headers );
        
        // Add body parameters if the package hasn't been designed to add them automatically...
        $new_curl->option_body_additional( $body ); 
        
        // Now add miscellanous values that will make up our curl request...
        $new_curl->option_other_additional( $additional );    
        
        $new_curl->final_prep();
        
        $new_curl->do_call();
        
        $new_curl->call_array['response']['body'] = json_decode( $new_curl->call_array['response']['body'] );

        return $new_curl->call_array['response'];          
    }
        
    /**
     * Generate an App Access Token as part of OAuth Client Credentials Flow. 
     * 
     * This token is meant for authorizing the application and making API calls that are not channel-auth specific. 
     * 
     * @param $code - [string] String of auth code used to grant authorization
     * 
     * @return object entire TwitchPress_Curl() object for handling any way required.
     * 
     * @version 2.0
     */
    public function request_app_access_token( $requesting_function = null ){

        // Create our Curl object which uses WP_Http_Curl()
        $this->curl_object = new TwitchPress_Curl();
        $this->curl_object->originating_file = __FILE__;
        $this->curl_object->originating_function = __FUNCTION__;
        $this->curl_object->originating_line = __LINE__;
        $this->curl_object->type = 'POST';
        $this->curl_object->endpoint = 'https://id.twitch.tv/oauth2/token?client_id=' . twitchpress_get_app_id();
     
        // Set none API related parameters i.e. cache and rate controls...
        $this->curl_object->call_params( 
            false, 
            0, 
            false, 
            null, 
            false, 
            false, 
            __FUNCTION__,
            __LINE__ 
        );
        
        // Use app data from registry...
        $twitch_app = TwitchPress_Object_Registry::get( 'twitchapp' );
        $this->curl_object->set_curl_body( array(
            'client_id'        => $twitch_app->app_id,
            'client_secret'    => $twitch_app->app_secret,
            'redirect_uri'     => $twitch_app->app_redirect,
            'grant_type'       => 'client_credentials'
        ) );
        unset($twitch_app);

        // Start + make the request in one line... 
        $this->curl_object->do_call( 'twitch' );
        
        // This method returns $call_twitch->curl_response_body;
        return $this->curl_object;
    }
    
    /**
    * Processes the object created by class TwitchPress_Curl(). 
    * 
    * Function request_app_access_token() is called first, it returns $call_object
    * so we can perform required validation and then we call this method.
    * 
    * @param mixed $call_object
    * 
    * @version 2.0
    */
    public function app_access_token_process_call_reply( $call_object ) {
        $options = array();

        if( !isset( $call_object->curl_reply_body->access_token ) ) {
            return false;
        }
        
        if( !isset( $call_object->curl_reply_body->expires_in ) ) {
            return false;
        }
        
        // Update option record and object registry...            
        twitchpress_update_app_token( $call_object->curl_reply_body->access_token );
        twitchpress_update_app_token_expiry( $call_object->curl_reply_body->expires_in ); 

        return $call_object->curl_reply_body->access_token; 
    }
    
    /**
     * Generate a visitor/user access token. This also applies to the 
     * administrator who sets the main and bot accounts...  
     * 
     * @param $code - [string] String of auth code used to grant authorization
     * 
     * @return array $token - The generated token and the array of all scopes returned with the token, keyed.
     * 
     * @version 5.2
     */
    public function request_user_access_token( $code, $requesting_function = null ){

        $endpoint = 'https://id.twitch.tv/oauth2/token';
        
        /*                    NEWER APPROACH STILL DOESNT WORK 
        $headers = array(
            'Authorization' => 'Bearer ' . twitchpress_get_app_token(), 
            "Client-ID" => twitchpress_get_app_id() 
        );
        
        $body = array(                  
            "client_id"     => twitchpress_get_app_id(),
            "client_secret" => twitchpress_get_app_secret(),
            "code"          => $code,
            "grant_type"    => "authorization_code",
            "redirect_uri"  => twitchpress_get_app_redirect() ); 
        
        $response = $this->Twitch_Request( 'POST', $endpoint, $headers, $body, $additional = array() );
        
      
        
        return; 
        */
        
        
        
        $request_array = array(
            "headers" =>
                array(
                  "Authorization" => 'Bearer ' . twitchpress_get_app_token(),
                  "Client-ID" => twitchpress_get_app_id(),
                ),
            "method" => "POST",
            "body" =>
                array(
                  "client_id"     => twitchpress_get_app_id(),
                  "client_secret" => twitchpress_get_app_secret(),
                  "code"          => $code,
                  "grant_type"    => "authorization_code",
                  "redirect_uri"  => twitchpress_get_app_redirect(),
                ),
            "user-agent" => "curl/" . $this->curl_version,
            "stream" => false,
            "filename" => false,
            "decompress" => false           
        );
        
        $WP_Http_Curl_Object = new WP_Http_Curl();
       
        $response = $WP_Http_Curl_Object->request( $endpoint, $request_array );       

        if( isset( $response['response']['code'] ) && $response['response']['code'] == 200 ) 
        {
            if( isset( $response['body'] ) ) {
                return json_decode( $response['body'] );
            }
        } 
        else
        {
            // Prepare meta data for investigation into the response...
            $decoded_body = json_decode( $response['body'] );
  
            $call_values = array(
                'date' => $response['headers']['date'],
                'response_code'    => $response['response']['code'],
                'response_message' => $response['response']['message'],
                'body_status'      => $decoded_body->status,
                'body_message'     => $decoded_body->message,
                'cliend_id'        => $request_array['body']['client_id'],
                'code'             => $code,
                'redirect_uri'     => $request_array['body']['redirect_uri'],
                'function'         => $requesting_function
            );
            
            // Generate fault report...
            $bugnet_api_net = new BugNet_API_Net();
            $bugnet_api_net->report_call( 
                'twitch', 
                false,
                $endpoint, 
                __( 'Code was not 200', 'twitchpress' ), 
                __( 'Requesting User Access Token', 'twitchpress' ), 
                $call_values 
            );    
            unset($bugnet_api_net);
        }       
    }
                       
    /**
     * Checks a token for validity and access grants available.
     * 
     * @return array $result if token is still valid, else false.  
     * 
     * @version 5.2
     * 
     * @deprecated this has not been a great approach, new approach coming October 2018
     */    
    public function check_application_token(){                    

        $url = 'https://id.twitch.tv/oauth2/validate';
        $post = array( 
            'oauth_token' => $this->twitch_client_token, 
            'client_id'   => twitchpress_get_app_id(),          
        );

        $result = json_decode( $this->cURL_get( $url, $post, array(), false, __FUNCTION__ ), true );                   
        
        if ( isset( $result['token']['valid'] ) && $result['token']['valid'] )
        {      
            return $result;
        } 
        else 
        {
             return false;
        }
        
        return false;     
    }        
                   
    /**
     * Checks a user oAuth2 token for validity.
     * 
     * @param $authToken - [string] The token that you want to check
     * 
     * @return $authToken - [array] Either the provided token and the array of scopes if it was valid or false as the token and an empty array of scopes
     * 
     * @version 6.0
     */    
    public function check_user_token( $wp_user_id ){
        
        // Get the giving users token. 
        $user_token = twitchpress_get_user_token( $wp_user_id );
        
        if( !$user_token ){ return false;}
        
        $endpoint = 'https://id.twitch.tv/oauth2/validate';

        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint );
        
        $this->call();
        
        $result = $this->call_result;

        $token = array();
        
        if ( isset( $result['token'] ) && isset( $result['token']['valid'] ) && $result['token']['valid'] !== false )
        {      
            $token['token'] = $user_token;
            $token['scopes'] = $result['token']['authorization']['scopes'];
            $token['name'] = $result['token']['user_name'];
        } 
        else 
        {
            $token['token'] = false;
            $token['scopes'] = array();
            $token['name'] = '';
        }
        
        return $token;     
    }

    /**
    * Establish an application token.
    * 
    * This method will check the existing token.
    * Existing token invalid, it will request a new one. 
    * Various values can be replaced during this procedure to help
    * generate debugging information for users.  
    * 
    * @param mixed $old_token
    * 
    * @returns array $result if token valid, else returns the return from request_app_access_token(). 
    * 
    * @version 5.0
    * 
    * @deprecated a new approach that relies on the access token expiry and call responses is WIP
    */
    public function establish_application_token( $function ) {     
        $result = $this->check_application_token();  

        // check_application_token() makes a call and if token invalid the following values will not be returned by the API
        if ( !isset( $result['token']['valid'] ) || !$result['token']['valid'] ){
            return $this->request_app_access_token( $function . ' + ' . __FUNCTION__ );
        }
                                  
        return $result;
    }
    
    /**
    * Establish current user token or token on behalf of a user who has
    * giving permission for extended sessions.
    * 
    * @returns array $result if token valid, else returns the return from request_app_access_token(). 
    * 
    * @version 5.2
    */
    public function establish_user_token( $function, $user_id ) { 
        // Maybe use an existing token? 
        $result = $this->check_user_token( $user_id );  

        if( isset( $result['token'] ) && $result['token'] !== false )
        {      
            return $result['token'];// Old token is still in session.    
        }
        elseif ( !isset( $result['token']['valid'] ) || !$result['token']['valid'] )
        {    
            // Attempt to refresh the users token, else request a new one.
            // This method updates user meta. 
            $new_token = $this->refresh_token_by_userid( $user_id );
                      
            if( is_string( $new_token ) ) 
            {            
                return $new_token;
            }
            elseif( !$new_token )
            {
                // Refresh failed - attempt to request a new token.
                $code = twitchpress_get_user_code( $user_id ); 

                // This method does not update user meta because $user_id is not always available where it is used.
                $user_access_token_array = $this->request_user_access_token( $code, __FUNCTION__ );

                twitchpress_update_user_token( $user_id, $user_access_token_array['access_token'] );
                twitchpress_update_user_token_refresh( $user_id, $user_access_token_array['refresh_token'] );
                       
                return $user_access_token_array['access_token'];
            }
        }
    }
    
    /**
    * Refreshes an existing token to extend a session. 
    * 
    * @link https://dev.twitch.tv/docs/authentication#refreshing-access-tokens
    * 
    * @version 1.0
    * 
    * @param integer $user_id
    */
    public function refresh_token_by_userid( $user_id ) {
        $token_refresh = twitchpress_get_user_token_refresh( $user_id );
        if( !$token_refresh ) { return false; }
        
        $endpoint = 'https://id.twitch.tv/oauth2/token';

        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'POST', $endpoint );
        
        $this->curl_object->grant_type = 'refresh_token';
        
        $this->curl_object->refresh_token = $token_refresh;
        
        $this->curl_object->scope = twitchpress_prepare_scopes( twitchpress_get_visitor_scopes() );
        
        $this->call();
        
        $result = $this->call_result;

        if( isset( $result['access_token'] ) && !isset( $result['error'] ) )
        {
            twitchpress_update_user_token( $user_id, $result['access_token'] );
            twitchpress_update_user_token_refresh( $user_id, $result['refresh_token'] );
            
            return $result['access_token'];
        }
        elseif( isset( $result['error'] ) ) 
        {
            return false;    
        }
        else
        {
            return false;    
        }
    } 

    /**
    * A method for administration accounts (not visitors). Call this when
    * all credentails are presumed ready in options table. Can pass $account
    * value to change which credentials are applied.
    * 
    * Recommended for admin requests as it generates notices.  
    * 
    * @author Ryan Bayne
    * @version 1.2
    */
    public function start_twitch_session_admin( $account = 'main' ) {
        // Can change from the default "main" credentails. 
        if( $account !== 'main' ) {
            self::set_application_credentials( $app = 'main' );
        }

        // The plugin will bring the user to their original admin view using the redirectto value.
        $state = array( 'redirectto' => admin_url( '/admin.php?page=twitchpress&tab=kraken&amp;' . 'section=entermaincredentials' ),
                        'userrole'   => 'administrator',
                        'outputtype' => 'admin' 
        );

        wp_redirect( twitchpress_generate_authorization_url( twitchpress_get_global_accepted_scopes(), $state ) );
        exit;                       
    }      
    
    public function get_main_streamlabs_user() {
                  
        // Endpoint
        $url = 'https://streamlabs.com/api/v1.0/user?access_token=' . $this->get_main_access_token();
     
        // Call Parameters
        $request_body = array(
            'client_id'        => $this->streamlabs_app_id,
            'client_secret'    => $this->streamlabs_app_secret,
            'redirect_uri'     => $this->streamlabs_app_uri,
        );                           

        $curl = new WP_Http_Curl();

        $response = $curl->request( $url, 
            array( 
                'method'     => 'GET', 
                'body'       => $request_body,
                'user-agent' => 'curl/' . $this->curl_version,
                'stream'     => false,
                'filename'   => false,
                'decompress' => false 
            ) 
        );

        if( isset( $response['response']['code'] ) && $response['response']['code'] == 200 ) {
            if( isset( $response['body'] ) ) {
                $response_body = json_decode( $response['body'] );
                return $response_body;
            }
        }
         
        return false;  
    }
        
    /**
     * Gets a users Twitch.tv object by their oAuth token stored in user meta.
     * 
     * @param $user - [string] Username to grab the object for
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * 
     * @return $userObject - [array] Returned object for the query
     * 
     * @version 5.8
     */ 
    public function getUserObject_Authd( $token, $code ){
        
        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = twitchpress_confirm_scope( 'user_read', 'channel', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) || $confirm_scope == false ) { return $confirm_scope; }
         
        $url = 'https://api.twitch.tv/kraken/user';
        $get = array( 'oauth_token' => $token, 'client_id' => twitchpress_get_app_id() );
                          
        // Build our cURL query and store the array
        $userObject = json_decode( $this->cURL_get( $url, $get, array(), false, __FUNCTION__ ), true );

        return $userObject;        
    }

    /**
    * User current users oauth token and the app code to get Twitch.tv user object.
    * 
    * @version 1.0
    */
    public function get_current_userobject_authd() {
    
        if( !$wp_user_id = get_current_user_id() ) {
            return false;    
        }
        
        return $this->getUserObject_Authd( get_user_meta( $wp_user_id, 'twitchpress_token', true ), $this->twitch_client_code );    
    }
    
    /**
     * Gets the channel object that belongs to the giving token.
     * 
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * 
     * @return $object - [array] Keyed array of all channel data
     * 
     * @version 5.3
     */ 
    public function get_tokens_channel( $token ){        
                                 
        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = twitchpress_confirm_scope( 'channel_read', 'channel', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; }
        
        $endpoint = 'https://api.twitch.tv/helix/channel';
        
        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint );
        
        $this->call();
        
        return $this->curl_object->curl_reply_response;
    }  
         
    /**
     * Gets a list of all users subscribed to a channel.
     * 
     * @param $chan - [string] Channel name to grab the subscribers list of
     * @param $limit - [int] Limit of channel objects to return
     * @param $offset - [int] Maximum number of objects to return
     * @param $direction - [string] Sorting direction, valid options are 'asc' and 'desc'
     * @param $token - [string] Token related to the channel being queried for sub data.
     * @param $code - [string] Code related to the channel being queried for sub data.
     * 
     * @version 5.6
     */ 
    public function get_channel_subscribers( $chan, $limit = -1, $offset = 0, $direction = 'asc', $token = null, $code = null ){
                                                                                            
        $url = 'https://api.twitch.tv/kraken/channels/' . $chan . '/subscriptions';                          
                                                                                        
        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.              
        $confirm_scope = twitchpress_confirm_scope( 'channel_subscriptions', 'channel', __FUNCTION__ );               
        if( is_wp_error( $confirm_scope) ) 
        {
            return $confirm_scope; 
        }                                            
                                                                                                 
        // Default to main channel credentials.                                                              
        if( !$token ){ $token = $this->twitch_client_token; }                                                
        if( !$code ){ $code = $this->twitch_client_code; }                                                   

        $get = array( 'oauth_token' => $token, 
                      'limit'       => $limit, 
                      'offset'      => $offset, 
                      'direction'   => $direction, 
                      'client_id'   => $this->twitch_client_id );
         
        return json_decode( $this->cURL_get($url, $get, array( /* cURL options */), false, __FUNCTION__ ), true);
    }  
    
    /**
     * Gets a giving users subscription details for a giving channel
     * 
     * @param $user_id - [string] Username of the user check against
     * @param $chan - [string] Channel name of the channel to check against
     * @param $token - [string] Channel owners own user token, not the visitors.
     * 
     * @returns $subscribed - [mixed] the subscription details (array) or error details (array) or null if Twitch returns null.
     * 
     * @version 5.4
     */ 
    public function getChannelSubscription( $twitch_user_id, $chan_id, $token ){
                                                             
        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = twitchpress_confirm_scope( 'channel_check_subscription', 'channel', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; }
        
        $url = 'https://api.twitch.tv/kraken/channels/' . $chan_id . '/subscriptions/' . $twitch_user_id;
        $get = array( 'oauth_token' => $token, 'client_id' => $this->twitch_client_id );
        
        $subscribed = json_decode( $this->cURL_get( $url, $get, array(), false, __FUNCTION__ ), true );
         
        // only check results here to log them and return the original response.
        if( isset( $subscribed['error'] ) ) 
        {
            return $subscribed;
        } 
        elseif( isset( $subscribed['sub_plan'] ) )
        {
            return $subscribed;   
        }
        elseif( $subscribed === null )
        {
            // Channel does not have a subscription scheme. 
            return null;
        }
             
        // We should never arrive here. 
        // These lines were added to debugging the new "null" response which the documentation says nothing about for this endpint. 
        // This bug may be the cause of 500 errors on returning from Twitch.
        if( is_array( $subscribed ) ) 
        {
            $unexpected = error_log( print_r( $subscribed, TRUE ) );
        }
        elseif( is_string( $subscribed ) )
        {
            $unexpected = $subscribed;
        }
        elseif( empty( $subscribed ) ) 
        {
            $unexpected = __( 'json_decode() has returned an empty value!', 'twitchpress' );
        }
        
        return $subscribed;
    }
    
    /**
    * Uses a users own Twitch code and token to get their subscription
    * status for the sites main/default channel.
    * 
    * @param mixed $user_id
    * 
    * @version 3.0
    */
    public function is_user_subscribed_to_main_channel( $user_id ) {

        if( !$credentials = twitchpress_get_user_twitch_credentials( $user_id ) ) {
            return null;    
        }        

        // Returns boolean, false if no subscription else true.     
        return $this->get_users_subscription_apicall( 
            twitchpress_get_user_twitchid_by_wpid($user_id), 
            twitchpress_get_main_channels_twitchid(), 
            $credentials['token'] 
        );    
    }
    
    /**
     * Checks to see if a user is subscribed to a specified channel from the user side.
     * 
     * @param $user_id - [string] User ID of the user check against
     * @param $chan    - [string] Channel name of the channel to check against
     * @param $token   - [string] Authentication key used for the session
     * @param $code    - [string] Code used to generate an Authentication key
     * 
     * @return $subscribed - [bool] the status of the user subscription
     * 
     * @version 5.5
     */ 
    public function get_users_subscription_apicall( $twitch_user_id, $chan_id, $user_token = false ){

        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = twitchpress_confirm_scope( 'channel_check_subscription', 'user', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) 
        {
            return $confirm_scope; 
        }
                               
        $url = 'https://api.twitch.tv/kraken/users/' . $twitch_user_id . '/subscriptions/' . $chan_id;
        $get = array( 'oauth_token' => $user_token, 'client_id' => $this->twitch_client_id );   

        // Build our cURL query and store the array
        $subscribed = json_decode( $this->cURL_get( $url, $get, array(), true, __FUNCTION__ ), true );

        // Check the return
        if ( $subscribed == 403 ){      
            // Authentication failed to have access to channel account.  Please check user access.
            $subscribed = false;
        } elseif ( $subscribed == 422 ) {     
            // Channel ' . $chan . ' does not have subscription program available
            $subscribed = false;
        } elseif ( $subscribed == 404 ) {    
            // User ' . $user_id . ' is not subscribed to channel ' . $chan
            $subscribed = false;
        } else {
            // User ' . $user_id . ' is subscribed to channel ' . $chan
            $subscribed = true;
        }
                 
        return $subscribed;
    }

    /**
    * Get the giving WordPress users Twitch subscription plan for the
    * main channel using the users own oAuth2 code and token.
    * 
    * This method is done from the users side.
    * 
    * @param mixed $user_id
    * 
    * @version 5.1
    */
    public function getUserSubscriptionPlan( $user_id ) {
        if( !$credentials = twitchpress_get_user_twitch_credentials( $user_id ) ) {
            return null;    
        }        

        $sub = $this->getUserSubscription(             
            $user_id, 
            $this->twitch_channel_id, 
            $credentials['token'], 
            $credentials['code']  
        );    
          
        return $sub['sub_plan'];
    }
    
    /**
     * Gets the a users subscription data (array) for specified channel from the user side.
     * 
     * @param $twitch_user_id - [string] User ID of the user check against
     * @param $chan_id    - [string] Channel name of the channel to check against
     * @param $user_token   - [string] Authentication key used for the session
     * @param $code    - [string] Code used to generate an Authentication key
     * 
     * @return $subscribed - [array] subscription data.
     * 
     * @version 5.1
     */ 
    public function getUserSubscription( $twitch_user_id, $chan_id, $user_token ){   
        
        $call_authentication = 'channel_check_subscription';

        $endpoint = 'https://api.twitch.tv/kraken/users/' . $twitch_user_id . '/subscriptions/' . $chan_id;  
        
        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'automatic', $endpoint );
        
        $this->call();        
    }    
                                 
    /**
    * Get Game Analytics
    * 
    * Gets a URL that game developers can use to download analytics reports 
    * (CSV files) for their games. The URL is valid for 5 minutes. For detail 
    * about analytics and the fields returned, see the Insights & Analytics guide.
    * 
    * The response has a JSON payload with a data field containing an array of 
    * games information elements and can contain a pagination field containing 
    * information required to query for more streams.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-game-analytics
    * 
    * @param string $after
    * @param string $ended_at
    * @param integer $first
    * @param string $game_id
    * @param string $started_at
    * @param string $type
    * 
    * @version 1.0
    */
    public function get_game_analytics( $after = null, $ended_at = null, $first = null, $game_id = null, $started_at = null, $type = null ) {

        $call_authentication = 'scope';
        
        $scope = 'analytics:read:games';

        $endpoint = 'https://api.twitch.tv/helix/analytics/games';    
        
        $this->get( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );    
    }
    
    /**
    * Get Bits Leaderboard 
    * 
    * Gets a ranked list of Bits leaderboard information 
    * for an authorized broadcaster.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-bits-leaderboard
    * @version 1.0 
    * 
    * @param mixed $count
    * @param mixed $period
    * @param mixed $started_at
    * @param mixed $user_id
    * 
    * @version 1.0
    */
    public function get_bits_leaderboard( $count = null, $period = null, $started_at = null, $user_id = null ) {

        $call_authentication = 'scope';
        
        $scope = 'bits:read';

        $endpoint = 'https://api.twitch.tv/helix/bits/leaderboard';
        
        // Establishes headers...
        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint ); 
        
        // Initiates call and does some initial response processing...
        $this->call(); 
           
        return $this->curl_object->curl_reply_body;        
    }
    
    /**
    * Create Clip
    * 
    * Creates a clip programmatically. This returns both an ID 
    * and an edit URL for the new clip.
    * 
    * Clip creation takes time. We recommend that you query Get Clips, 
    * with the clip ID that is returned here. If Get Clips returns a 
    * valid clip, your clip creation was successful. If, after 15 seconds, 
    * you still have not gotten back a valid clip from Get Clips, assume 
    * that the clip was not created and retry Create Clip.
    * 
    * This endpoint has a global rate limit, across all callers. The limit 
    * may change over time, but the response includes informative headers:
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#create-clip
    * 
    * @param mixed $broadcaster_id
    * @param mixed $has_delay
    * 
    * @version 1.0
    */
    public function create_clip( $broadcaster_id, $has_delay = null ) {

        $call_authentication = 'scope';

        $scope = 'clips:edit';
 
        $endpoint = 'https://api.twitch.tv/helix/clips';   
        
        $this->post( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );        
    }
    
    /**
    * Get Clips
    * 
    * Gets clip information by clip ID (one or more), broadcaster ID (one only), 
    * or game ID (one only).
    * 
    * The response has a JSON payload with a data field containing an array 
    * of clip information elements and a pagination field containing 
    * information required to query for more streams.
    * 
    * @param mixed $broadcaster_id
    * @param mixed $game_id
    * @param mixed $id
    * @param mixed $after
    * @param mixed $before
    * @param mixed $ended_at
    * @param mixed $first
    * @param mixed $started_at
    * 
    * @version 1.0
    */
    public function get_clips( $broadcaster_id, $game_id, $id, $after = null, $before = null, $ended_at = null, $first = null, $started_at = null ) {

        $call_authentication = 'none';

        $endpoint = 'https://api.twitch.tv/helix/clips'; 
        
        if( $broadcaster_id ) { $endpoint = add_query_arg( 'broadcaster_id', $broadcaster_id, $endpoint ); }
        if( $game_id ) { $endpoint = add_query_arg( 'game_id', $game_id, $endpoint ); }
        if( $id ) { $endpoint = add_query_arg( 'id', $id, $endpoint ); }
                         
        // Establishes headers...
        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint ); 
        
        // Initiates call and does some initial response processing...
        $this->call(); 
           
        return $this->curl_object->curl_reply_body;          
    }
        
    /**
    * Create Entitlement Grants Upload URL
    * 
    * Creates a URL where you can upload a manifest file and notify users that
    * they have an entitlement. Entitlements are digital items that users are 
    * entitled to use. Twitch entitlements are granted to users gratis or as 
    * part of a purchase on Twitch.
    * 
    * See the Drops Guide for details about using this 
    * endpoint to notify users about Drops.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#create-entitlement-grants-upload-url
    * 
    * @param mixed $manifest_id
    * @param mixed $type
    * 
    * @version 1.0
    */
    public function create_entitlement_grants_upload_url( $manifest_id, $type ) {

        $call_authentication = 'app_access_token';

        $endpoint = 'https://api.twitch.tv/helix/entitlements/upload';  
        
        $this->post( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );         
    }
         
    /**
    * Get Games
    * 
    * Gets game information by game ID or name. The response has a JSON 
    * payload with a data field containing an array of games elements.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-games
    * 
    * @param mixed $id
    * @param mixed $name
    * @param mixed $box_art_url
    * 
    * @version 1.0
    */
    public function get_games( $id, $name ) {

        $call_authentication = 'none';

        $endpoint = 'https://api.twitch.tv/helix/games';  
        
        if( $id ) { $endpoint = add_query_arg( 'id', $id, $endpoint ); }
        if( $name ) { $endpoint = add_query_arg( 'name', str_replace( ' ', '%20', $name ), $endpoint ); }
        
        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint );    
        
        $this->call();

        return $this->curl_object->curl_reply_body;                 
    }
         
    /**
    * Get Top (viewed) Games
    * 
    * Gets games sorted by number of current viewers on Twitch, most popular first.
    * 
    * The response has a JSON payload with a data field containing an array 
    * of games information elements and a pagination field containing 
    * information required to query for more streams.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-top-games
    * 
    * @param mixed $after
    * @param mixed $before
    * @param mixed $first
    * 
    * @version 1.0
    */
    public function get_top_games( $after = null, $before = null, $first = 10 ) {
        $call_authentication = 'none';

        $endpoint = 'https://api.twitch.tv/helix/games/top?first=' . $first;    

        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint ); 
        
        $this->call( 'GET', $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' ); 
           
        return $this->curl_object->curl_reply_body;
    }
         
    /**
    * Get Streams
    * 
    * Gets information about active streams. Streams are returned sorted by 
    * number of current viewers, in descending order. Across multiple pages of 
    * results, there may be duplicate or missing streams, 
    * as viewers join and leave streams.
    * 
    * The response has a JSON payload with a data field containing an array of 
    * stream information elements and a pagination field containing information 
    * required to query for more streams.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-streams
    * 
    * @param mixed $after
    * @param mixed $before
    * @param mixed $community_id
    * @param mixed $first
    * @param mixed $game_id
    * @param mixed $language
    * @param mixed $user_id
    * @param mixed $user_login
    * 
    * @version 2.1
    */
    public function get_streams( $after = null, $before = null, $community_id = null, $first = null, $game_id = null, $language = null, $user_id = array(), $user_login = array() ) {
                          
        if( is_array( $user_id ) ) 
        {   
            $user_id_string = '?';
            
            $count = count( $user_id );

            $i = 0;
            foreach( $user_id as $id ) {
                $user_id_string .= 'user_id=' . $id;
                ++$i;
                if( $i !== $count ) { $user_id_string .= '&'; }
            }     
            
            $endpoint = 'https://api.twitch.tv/helix/streams' . $user_id_string;
        }
        else
        {
            $endpoint = 'https://api.twitch.tv/helix/streams?user_id=' . $user_id;   
        }          
         
        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint );    

        $this->call();
                           
        return $this->curl_object->curl_reply_body;
    }
    
    /**
    * Get a single stream using Twitch user ID or channel ID.
    * 
    * @uses get_streams()
    * 
    * @param mixed $twitch_user_id
    * 
    * @version 1.0
    */
    public function get_stream_by_userid( $twitch_user_id ) {
        $result = $this->get_streams( null, null, null, null, null, null, $twitch_user_id, null );     
    
        if( isset( $result->data[0] ) && !empty( $result->data[0] ) ) {
            return $result->data[0];
        }
        
        return false;
    }    
    
    /**
    * Get two or more streams using an array of Twitch user ID or channel ID.
    * 
    * @uses get_streams()
    * 
    * @param mixed $twitch_user_id
    * 
    * @version 2.0 Now splits array $twitch_user_id into chunks of 100 and performs multiple calls (due to API limit)
    */
    public function get_streams_by_userid( $twitch_user_id = array() ) {
        
        $streams_array = array();
        
        $chunks = array_chunk( $twitch_user_id, 100, false );
        
        foreach( $chunks as $group ) {
        
            $result = $this->get_streams( null, null, null, null, null, null, $group, null );     

            if( isset( $result->data[0] ) && !empty( $result->data[0] ) ) {
                $streams_array = array_merge( $streams_array, $result->data );
            }            
        }
        
        if( $streams_array ) { return $streams_array; }
        return false;
    }
         
    /**
    * Get Streams Metadata
    * 
    * Gets metadata information about active streams playing Overwatch or 
    * Hearthstone. Streams are sorted by number of current viewers, in 
    * descending order. Across multiple pages of results, there may be 
    * duplicate or missing streams, as viewers join and leave streams.
    * 
    * The response has a JSON payload with a data field containing an array of 
    * stream information elements and a pagination field containing information 
    * required to query for more streams.
    * 
    * This endpoint has a global rate limit, across all callers. The limit 
    * may change over time, but the response includes informative headers:
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-streams-metadata
    * 
    * @param mixed $after
    * @param mixed $before
    * @param mixed $community_id
    * @param mixed $first
    * @param mixed $game_id
    * @param mixed $language
    * @param mixed $user_id
    * @param mixed $user_login
    * 
    * @version 1.1
    */
    public function get_streams_metadata( $after = null, $before = null, $community_id = null, $first = 100, $game_id = null, $language = null, $user_id = null, $user_login = null ) {

        $call_authentication = 'none';

        $endpoint = 'https://api.twitch.tv/helix/streams/metadata';    
        
        $this->get( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );       
    }
         
    /**
    * Create Stream Marker
    * 
    * Creates a marker in the stream of a user specified by a user ID. 
    * A marker is an arbitrary point in a stream that the broadcaster 
    * wants to mark; e.g., to easily return to later. The marker is 
    * created at the current timestamp in the live broadcast when the 
    * request is processed. Markers can be created by the stream owner 
    * or editors. The user creating the marker is identified by a Bearer token.
    * 
    * Markers cannot be created in some cases (an error will occur):
    *   ~ If the specified user’s stream is not live.
    *   ~ If VOD (past broadcast) storage is not enabled for the stream.
    *   ~ For premieres (live, first-viewing events that combine uploaded videos with live chat).
    *   ~ For reruns (subsequent (not live) streaming of any past broadcast, including past premieres).
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#create-stream-marker
    * 
    * @param mixed $user_id
    * @param mixed $description
    * 
    * @version 1.0
    */
    public function create_stream_markers( $user_id, $description = null ) {

        $call_authentication = 'scope';
        
        $scope = 'user:edit:broadcast';

        $endpoint = 'https://api.twitch.tv/helix/streams/markers';
        
        $this->post( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );           
    }
         
    /**
    * Get Streams Markers
    * 
    * Gets a list of markers for either a specified user’s most recent stream 
    * or a specified VOD/video (stream), ordered by recency. A marker is an 
    * arbitrary point in a stream that the broadcaster wants to mark; 
    * e.g., to easily return to later. The only markers returned are those 
    * created by the user identified by the Bearer token.
    * 
    * The response has a JSON payload with a data field containing an array of 
    * marker information elements and a pagination field containing information 
    * required to query for more follow information.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-stream-markers
    * 
    * @param mixed $user_id
    * @param mixed $video_id
    * @param mixed $after
    * @param mixed $before
    * @param mixed $first
    * 
    * @version 1.0
    */
    public function get_streams_markers( $user_id, $video_id, $after = null, $before = null, $first = null ) {

        $call_authentication = 'scope';

        $scope = 'user:read:broadcast';
        
        $endpoint = 'https://api.twitch.tv/helix/streams/markers';    
        
        $this->get( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );       
    }
    
    /**
    * Get Broadcasters Subscriptions
    * 
    * Get all of a broadcaster’s subscriptions.
    * The current user is determined by the OAuth token provided in the Authorization header.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-broadcaster-subscriptions
    * 
    * @param mixed $broadcaster_id
    * 
    * @version 1.0
    */
    public function get_broadcaster_subscriptions( $broadcaster_id ) {
        $scope = 'channel:read:subscriptions';
        
        $endpoint = 'https://api.twitch.tv/helix/subscriptions'; 
        
        $endpoint = add_query_arg( array( 'broadcaster_id' => $broadcaster_id ), $endpoint );

        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint ); 

        $this->call( 'GET', $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' ); 
                             
        return $this->curl_object->curl_reply_body;           
    }
    
    /**
    * Get Broadcasters Subscribers
    * 
    * Gets broadcaster’s subscriptions by user ID (one or more).
    * OAuth Token (e.g. User Access Token)
    * The current user is determined by the OAuth token provided in the Authorization header.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-broadcaster-s-subscribers
    * 
    * @param mixed $broadcaster_id
    * @param mixed $user_id
    * 
    * @version 1.0
    */
    public function get_broadcasters_subscribers( $broadcaster_id, $user_id ) {
        $scope = 'channel:read:subscriptions';
        
        $endpoint = 'https://api.twitch.tv/helix/subscriptions';  
                
        $endpoint = add_query_arg( array( 'broadcaster_id' => $broadcaster_id ), $endpoint );
        $endpoint = add_query_arg( array( 'user_id' => $user_id ), $endpoint );

        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint ); 

        $this->call( 'GET', $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' ); 
                             
        return $this->curl_object->curl_reply_body;              
    }
    
    public function get_user( $login_name, $plus_email = false ) {
        if( $plus_email ) {
            return $this->get_user_plus_email_by_login_name( $login_name );
        }
        
        return $this->get_user_without_email_by_login_name( $login_name );    
    }    
    
    public function get_user_by_id( $twitch_user_id ) {
 
        $endpoint = 'https://api.twitch.tv/helix/users?id=' . $twitch_user_id;
        
        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint ); 
        
        $this->call();
  
        return $this->curl_object->curl_reply_body;    
    }   
    
    public function get_channel_id_by_name( $channel_name ) {
        $response = $this->get_user_without_email_by_login_name( $channel_name );  
        if( isset( $response->data[0]->id ) ) { return $response->data[0]->id; }
        return false;      
    }
    
    public function get_user_by_bearer_token( $bearer_token ) {
   
        $call_authentication = 'scope';
        
        $endpoint = 'https://api.twitch.tv/helix/users';
        
        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint ); 

        $this->curl_object->scope = 'user:read:email';

        // Bearer token header "Authorization" is usually already set...
        unset( $this->curl_object->headers['Authorization']);
        
        $this->curl_object->add_headers( array(
            'Authorization' => 'Bearer ' . $bearer_token,
        ) );

        $this->call();    
 
        return $this->curl_object->curl_reply_body->data[0];
    }
      
    /**
    * Get a user using login name without using scope. Using scope would get the
    * users email address also. 
    * 
    * Gets information about one or more specified Twitch users. 
    * Users are identified by optional user IDs and/or login name. 
    * If neither a user ID nor a login name is specified, the user is 
    * looked up by Bearer token.
    * 
    * The response has a JSON payload with a data field containing an array 
    * of user-information elements. If this is provided, the response 
    * includes the user’s email address.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-users
    * 
    * @param mixed $id
    * @param mixed $login
    * 
    * @version 6.0
    */
    public function get_user_without_email_by_login_name( $login_name ) {
 
        $endpoint = 'https://api.twitch.tv/helix/users?login=' . $login_name . '&login=' . $login_name;
        
        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint ); 
        
        $this->call();
  
        return $this->curl_object->curl_reply_body;          
    }
    
    /**
    * Get a user using login name without using scope. Using scope would get the
    * users email address also. 
    * 
    * Gets information about one or more specified Twitch users. 
    * Users are identified by optional user IDs and/or login name. 
    * If neither a user ID nor a login name is specified, the user is 
    * looked up by Bearer token.
    * 
    * The response has a JSON payload with a data field containing an array 
    * of user-information elements. If this is provided, the response 
    * includes the user’s email address.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-users
    * 
    * @param mixed $id
    * @param mixed $login
    * 
    * @version 6.0
    */
    public function get_user_plus_email_by_login_name( $login_name ) {
 
        $call_authentication = 'scope';
        
        $endpoint = 'https://api.twitch.tv/helix/users?login=' . $login_name . '&login=' . $login_name;
        
        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint ); 
        
        $this->curl_object->scope = 'user:read:email';
        
        $result = $this->call();          
    }

    /**
    * Get Users Follows [from giving ID]
    * 
    * Gets information on follow relationships between two Twitch users. 
    * Information returned is sorted in order, most recent follow first. 
    * This can return information like “who is lirik following,�?, 
    * “who is following lirik,�? or “is user X following user Y.�?
    * 
    * The response has a JSON payload with a data field containing an array 
    * of follow relationship elements and a pagination field containing 
    * information required to query for more follow information.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-users-follows
    * 
    * @param mixed $after
    * @param mixed $first Maximum number of objects to return. Maximum: 100. Default: 20.
    * @param mixed $from_id User ID. The request returns information about users who are being followed by the from_id user.
    * @param mixed $to_id User ID. The request returns information about users who are following the to_id user.
    * 
    * @version 1.0
    */
    public function get_users_follows( $after = null, $first = null, $from_id = null, $to_id = null ) {
    
        $endpoint = 'https://api.twitch.tv/helix/users/follows';  
        
        if( $after ) { $endpoint = add_query_arg( array( 'after' => $after ), $endpoint ); }
        if( $first ) { $endpoint = add_query_arg( array( 'first' => $first ), $endpoint ); }
        if( $from_id ) { $endpoint = add_query_arg( array( 'from_id' => $from_id ), $endpoint ); }
        if( $to_id ) { $endpoint = add_query_arg( array( 'to_id' => $to_id ), $endpoint ); }

        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint ); 

        $this->call( 'GET', $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' ); 
                             
        return $this->curl_object->curl_reply_body;        
    }
                   
    /**
    * The request returns information about users who are being followed by the from_id user.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-users-follows
    * 
    * @param mixed $after
    * @param mixed $first
    * @param mixed $from_id
    * 
    * @version 1.0
    */
    public function get_users_follows_from_id( $after = null, $first = null, $from_id = null ) {
    
        $endpoint = 'https://api.twitch.tv/helix/users/follows';  
        
        if( $after ) { $endpoint = add_query_arg( array( 'after' => $after ), $endpoint ); }
        if( $first ) { $endpoint = add_query_arg( array( 'first' => $first ), $endpoint ); }
        if( $from_id ) { $endpoint = add_query_arg( array( 'from_id' => $from_id ), $endpoint ); }
 
        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint ); 

        $this->call( 'GET', $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' ); 
                             
        return $this->curl_object->curl_reply_body;         
    }
            
    /**
    * The request returns information about users who are following the to_id user.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-users-follows
    * 
    * @param mixed $after
    * @param mixed $first
    * @param mixed $to_id
    * 
    * @version 2.0
    */
    public function get_users_follows_to_id( $after = null, $first = null, $to_id = null ) {
        $endpoint = 'https://api.twitch.tv/helix/users/follows';  
                                     
        if( $after ) { $endpoint = add_query_arg( array( 'after' => $after ), $endpoint ); }
        if( $first ) { $endpoint = add_query_arg( array( 'first' => $first ), $endpoint ); }
        if( $to_id ) { $endpoint = add_query_arg( array( 'to_id' => $to_id ), $endpoint ); }

        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint ); 

        $this->call( 'GET', $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' ); 
             
        return $this->curl_object->curl_reply_body;         
    }
             
    /**
    * Update User
    * 
    * Updates the description of a user specified by a Bearer token.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#update-user
    * 
    * @version 1.0 
    */
    public function update_user() {
  
        $call_authentication = 'scope';

        $scope = 'user:edit';
        
        $endpoint = 'https://api.twitch.tv/helix/users?description=<description>';     
        
        $this->put( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );      
    }
         
    /**
    * Get User Extensions
    * 
    * Gets a list of all extensions (both active and inactive) for a 
    * specified user, identified by a Bearer token.
    * 
    * The response has a JSON payload with a data field containing an array 
    * of user-information elements.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-user-extensions
    * 
    * @version 1.0 
    */
    public function get_user_extensions() {
 
        $call_authentication = 'scope';
        
        $scope = 'user:read:broadcast';

        $endpoint = 'https://api.twitch.tv/helix/users/extensions/list';       
        
        $this->get( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );    
    }
         
    /**
    * Get User Active Extensions
    * 
    * Gets information about active extensions installed by a specified user, 
    * identified by a user ID or Bearer token.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-user-active-extensions
    * 
    * @param string $user_id 
    * 
    * @version 1.0 
    */
    public function get_user_active_extensions( string $user_id = null ) {

        $call_authentication = 'scope';

        $scope = array( 'user:read:broadcast', 'user:edit:broadcast' ); 
        
        $endpoint = 'https://api.twitch.tv/helix/users/extensions';      
        
        $this->get( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );     
    }
         
    /**
    * Update User Extensions
    * 
    * Updates the activation state, extension ID, and/or version number of 
    * installed extensions for a specified user, identified by a Bearer token. 
    * If you try to activate a given extension under multiple extension types, 
    * the last write wins (and there is no guarantee of write order).
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#update-user-extensions
    * 
    * @version 1.0 
    */
    public function update_user_extensions() {

        $call_authentication = 'scope';
        
        $scope = 'user:edit:broadcast';

        $endpoint = 'https://api.twitch.tv/helix/users/extensions';     
        
        $this->put( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );      
    }
         
    /**
    * Get Videos
    * 
    * Gets video information by video ID (one or more), user ID (one only), 
    * or game ID (one only).
    * 
    * The response has a JSON payload with a data field containing an array 
    * of video elements. For lookup by user or game, pagination is available, 
    * along with several filters that can be specified as query string parameters.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-videos
    * 
    * @param mixed $id
    * @param mixed $user_id
    * @param mixed $game_id
    * @param mixed $after
    * @param mixed $before
    * @param mixed $first
    * @param mixed $language
    * @param mixed $period
    * @param mixed $sort
    * @param mixed $type
    * 
    * @version 1.0
    */
    public function get_videos( $id = null, $user_id = null, $game_id = null, $after = null, $before = null, $first = null, $language = null, $period = null, $sort = null, $type = null ) {
  
        $call_authentication = 'none';

        $endpoint = 'https://api.twitch.tv/helix/videos';          
        
        if( $id ) { $endpoint = add_query_arg( array( 'id' => $id ), $endpoint ); }
        if( $user_id ) { $endpoint = add_query_arg( array( 'user_id' => $user_id ), $endpoint ); }
        if( $game_id ) { $endpoint = add_query_arg( array( 'game_id' => $game_id ), $endpoint ); }
        if( $after ) { $endpoint = add_query_arg( array( 'after' => $after ), $endpoint ); }
        if( $before ) { $endpoint = add_query_arg( array( 'before' => $before ), $endpoint ); }
        if( $first ) { $endpoint = add_query_arg( array( 'first' => $first ), $endpoint ); }
        if( $language ) { $endpoint = add_query_arg( array( 'language' => $language ), $endpoint ); }
        if( $period ) { $endpoint = add_query_arg( array( 'period' => $period ), $endpoint ); }
        if( $sort ) { $endpoint = add_query_arg( array( 'sort' => $sort ), $endpoint ); }
        if( $type ) { $endpoint = add_query_arg( array( 'type' => $type ), $endpoint ); }
     
        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint ); 

        $this->call( 'GET', $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' ); 
                             
        return $this->curl_object->curl_reply_body;
    }
         
    /**
    * Get Webhook Subscriptions
    * 
    * Gets Webhook subscriptions, in order of expiration.
    * 
    * The response has a JSON payload with a data field containing an array 
    * of subscription elements and a pagination field containing information 
    * required to query for more subscriptions.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-webhook-subscriptions
    * 
    * @param mixed $after
    * @param mixed $first
    * @param mixed $callback
    * @param mixed $expires_at
    * @param mixed $pagination
    * @param mixed $topic
    * @param mixed $total
    * 
    * @version 1.0
    */
    public function get_webhook_subscriptions( $after, $first, $callback = null, $expires_at = null, $pagination = null, $topic = null, $total = null ) {

        $call_authentication = 'app_access_token';

        $endpoint = 'https://api.twitch.tv/helix/webhooks/subscriptions';       
        
        $this->get( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );    
    }     
    
    public function webhook_new( $headers, $callback, $mode, $topic, $lease_seconds = null, $secret = null ) {
        
        $call_authentication = 'none';

        $endpoint = 'https://api.twitch.tv/helix/webhooks/hub';     
        
        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'POST', $endpoint );    

        $this->curl_object->add_headers( array(
            'hub.callback'      => $callback,
            'hub.mode'          => $mode,
            'hub.topic'         => $topic,
            'hub.lease_seconds' => $lease_seconds,
            'hub.secret'        => $secret,
        ) );
        
        $this->curl_object->set_curl_body( $headers );
                       
        $this->call();
                     
        return $this->curl_object->curl_reply_body; 
    } 
    
    public function webhook_new_user_follows( $first, $callback, $mode, $topic, $from_id = null, $to_id = null, $lease_seconds = null, $secret = null ) {
        
        $headers = array(
            'first'   => $first,
            'from_id' => $from_id,
            'to_id'   => $to_id 
        );
        
        return $this->webhook_new( $headers, $callback, $mode, $topic, $lease_seconds = null, $secret = null );    
    } 

    /**
    * Kraken endpoint due not no equal in v6
    * 
    * Gets tje giving team with stream status included.
    * 
    * @param mixed $team_name
    * 
    * @version 1.0
    */
    public function get_team( $team_name ) {
        $endpoint = 'https://api.twitch.tv/kraken/teams/' . $team_name;            
        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint );    
        
        // Add v5 due to not being available in v6 yet - Sep 2019

        $this->curl_object->add_headers( array(
            'Accept:' => 'Accept: application/vnd.twitchtv.v5+json',
        ) );
   
        $this->call();

        return $this->curl_object->curl_reply_body; 
    }
    
    public function get_channel( $channel_id ) {
        $endpoint = 'https://api.twitch.tv/kraken/channels/' . $channel_id;            
        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint );    
        
        $this->curl_object->add_headers( array(
            'Accept:' => 'Accept: application/vnd.twitchtv.v5+json',
        ) );
   
        $this->call();

        return $this->curl_object->curl_reply_body;  
    }
}

endif;                         