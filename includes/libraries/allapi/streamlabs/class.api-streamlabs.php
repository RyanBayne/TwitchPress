<?php
/**
 * Streamlabs API Class for TwitchPress - primary purpose is to set
 * the main application account and user (admin/keyholder/site owner)
 *
 * @link https://dev.streamlabs.com/
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Streamlabs Extension
 * @version  1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( !class_exists( 'TWITCHPRESS_All_API' ) ) { return; }

if( !class_exists( 'TWITCHPRESS_Streamlabs_API' ) ) :

class TwitchPress_Streamlabs_API extends TWITCHPRESS_All_API {
    
    public $streamlabs_app_ready = false;
    public $response = null;
    public $decompress = true;
    
    protected $streamlabs_app_id     = null;
    protected $streamlabs_app_secret = null;
    protected $streamlabs_app_uri    = null; 
    protected $streamlabs_app_code   = null;
    protected $streamlabs_app_token  = null;
    protected $streamlabs_app_scope  = array();
    
    protected $url = 'https://streamlabs.com/api/';
    public $version = '1.0';// API version
    
    /**
    * Store user data here during a procedure to help avoid 
    * repeating database queries.  
    * 
    * @var mixed
    */
    public $users = array();
        
    public function __construct( $profile = 'default' ){      
        parent::__construct( $profile );
        $this->set_application( $profile );
        $this->is_app_set();
        add_action( 'wp_loaded', array( $this, 'oauth_admin_listener' ), 1 );
    } 
    
    public function url() {
        return $this->url . TWITCHPRESS_VERSION . '/';
    }
                                                           
    public function set_application( $profile = 'default' ) {        
        $this->streamlabs_app_uri    = get_option( 'twitchpress_allapi_streamlabs_' . $profile . '_uri', null );
        $this->streamlabs_app_id     = get_option( 'twitchpress_allapi_streamlabs_' . $profile . '_id', null );
        $this->streamlabs_app_secret = get_option( 'twitchpress_allapi_streamlabs_' . $profile . '_secret', null );
        $this->streamlabs_app_token  = get_option( 'twitchpress_allapi_streamlabs_' . $profile . '_access_token', null );
        $this->streamlabs_app_code   = get_option( 'twitchpress_allapi_streamlabs_' . $profile . '_code', null );
    }
    
    /**
    * Listen for administration only oAuth2 return/redirect. 
    * 
    * Return when a negative condition is found.
    * 
    * Add methods between returns, where arguments satisfy minimum security. 
    * 
    * @version 1.23
    * 
    * @deprecated still in use by extension but core plugin uses a newer function
    */
    public static function oauth_admin_listener() {                                         
        if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {   
            return;
        }
                 
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {    
            return;
        }
         
        if( defined( 'DOING_CRON' ) && DOING_CRON ) {    
            return;    
        }        
         
        if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {      
            return;    
        }
        
        // This listener is for requests started on administration side only.  
        if( !is_user_logged_in() ) {         
            return;
        }      
        
        $wp_user_id = get_current_user_id();  
        
        if( !current_user_can( 'activate_plugins' ) ) {
            return; 
        } 
                      
        if( isset( $_GET['error'] ) ) {  
            return;
        } 
         
        if( !isset( $_GET['code'] ) ) {       
            return;
        }     
            
        if( !isset( $_GET['state'] ) ) {       
            return;
        }    
        
        // Change to true when $_REQUEST cannot be validated. 
        $return = false;
        $return_reason = '';
              
        if( !$transient_state = get_transient( 'twitchpress_streamlabs_oauthstate_' . $_GET['state'] ) ) {      
            $return = true;
            $return_reason .= __( 'Streamlabs Listener: No matching transient.', 'twitchpress' );
        }
        // Ensure the reason for this request is an attempt to set the main channels credentials
        elseif( !isset( $transient_state['reason'] ) ) {
            $return = true;
            $return_reason .= __( 'Streamlabs Listener: no reason for request.', 'twitchpress' );            
        }
        // Ensure we have the admin view or page the user needs to be sent to. 
        elseif( $transient_state['reason'] !== 'streamlabsextensionowneroauth2request' ) {         
            $return = true;
            $return_reason .= __( 'Streamlabs Listener: reason rejected.', 'twitchpress' );    
        }        
        // Ensure we have the admin view or page the user needs to be sent to. 
        elseif( !isset( $transient_state['redirectto'] ) ) {         
            $return = true;
            $return_reason .= __( 'Streamlabs Listener: "redirectto" value does not exist.', 'twitchpress' );    
        } 
        // For this procedure the userrole MUST be administrator.
        elseif( !isset( $transient_state['userrole'] ) ) {        
            $return = true;
            $return_reason .= __( 'Streamlabs Listener: unexpected request, related to the main account.', 'twitchpress' );    
        }
        elseif( !isset( $transient_state['userrole'] ) || 'administrator' !== $transient_state['userrole'] ) {        
            $return = true;
            $return_reason .= __( 'Streamlabs Listener: not an administrator.', 'twitchpress' );    
        }       
        // NEW IF - Validate the code as a measure to prevent URL spamming that gets further than here.
        elseif( !$this->validate_code( $_GET['code'] ) ) {        
            $return = true;
            $return_reason .= __( 'Streamlabs Listener: invalid code.', 'twitchpress' );
        }

        // If we have a return reason, add it to the trace then do the return. 
        if( $return === true ) {
            return false;
        } 

        // Update the main Streamlab account details using the current admins credentials.
        $this->update_main_code( $_GET['code'] );
        $this->update_main_owner( $wp_user_id );        
        $this->streamlabs_app_code = $_GET['code'];
        
        // Request a token on behalf of the main administrator (current user).
        $request_body = $this->api_request_token();
        
        if( $request_body === false ) 
        {
            TwitchPress_Admin_Notices::add_custom_notice( 'streamlabs_main_tokenrequest', __( 'The request for a Streamlabs access token has failed this time, please try again.') );
            return false;                
        }

        $this->update_main_access_token( $request_body->access_token );
        $this->update_main_expires_in( $request_body->expires_in );
        $this->update_main_refresh_token( $request_body->refresh_token );

        // Update current users Streamlabs API credentials just as we would any other user.
        $this->update_user_code( $wp_user_id, $_GET['code'] );
        $this->update_user_access_token( $wp_user_id, $request_body->access_token );
        $this->update_user_expires_in( $wp_user_id, $request_body->expires_in );
        $this->update_user_refresh_token( $wp_user_id, $request_body->refresh_token );

        // Token notice
        TwitchPress_Admin_Notices::add_custom_notice( 'streamlabs_mainapplicationsetup', __( 'Streamlabs API provided a token, allowing this site to access the main account based on the permissions gave.') );
               
        // Populate the main users Streamlabs user-meta values.
        $result = $this->update_main_users_meta();   
      
        if( !$result )
        {
            TwitchPress_Admin_Notices::add_custom_notice( 'streamlabs_listener_test_failed', __( '<strong>Final Test Failed:</strong> The administrator account listener has passed validation but failed the first attempt to request data from the services server.', 'twitchpress' ) );      
            
            $bugnet->trace( 'streamlabs_oauth2mainaccount',
                __LINE__,
                __FUNCTION__,
                __FILE__,
                true,
                __( 'Streamlabs Listener: the giving subject cannot be confirmed as the server response indicates a failure.', 'twitchpress' )
            );
            
            return;
        }     

        // Forward user to the custom destinaton i.e. where they were before oAuth2. 
        twitchpress_redirect_tracking( $transient_state['redirectto'], __LINE__, __FUNCTION__ );
        exit;
    }   

    /**
    * Create a new HTTP Curl object with default Streamlabs app credentials.
    * 
    * @param mixed $type is GET,PUT,POST,DELETE
    * @param url $endpoint
    * @param array $headers
    * @param array $body
    * @param mixed $additional
    * 
    * @return TwitchPress_Extend_WP_Http_Curl which extends class WP_Http_Curl
    * 
    * @version 1.0
    */
    public function call_streamlabs( $type, $endpoint ) {
        // Create new curl object that includes our custom call values...
        // This involves a custom TwitchPress class that uses class WP_Http_Curl()... 
        $new_curl = new TwitchPress_Extend_WP_Http_Curl();                      
        
        $new_curl->start_new_request( 
            $this->streamlabs_app_id,
            $this->streamlabs_app_secret,               
            $this->streamlabs_app_token,
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

    public function api_get_user_owner() {
        $endpoint = 'https://streamlabs.com/api/v1.0/user';
        $endpoint = add_query_arg(
            array( 
                'access_token' => $this->streamlabs_app_token, 
            ),
            $endpoint 
        );
         
        $call_response = $this->call_streamlabs( 'GET', $endpoint );
        return $call_response['body'];
    }    
    
    public function api_get_users_points( $twitch_user_name ) {
        $endpoint = 'https://streamlabs.com/api/v1.0/points';
        $endpoint = add_query_arg(
            array( 
                'access_token' => $this->streamlabs_app_token,
                'username'     => $twitch_user_name, 
                'channel'      => $twitch_user_name,
            ),
            $endpoint 
        );
         
        $response = $this->call_streamlabs( 'GET', $endpoint );

        return $response;
    }
            
    public function update_main_code( $code ) {
        update_option( 'twitchpress_streamlabs_main_code', $code );    
    }    
    
    public function update_main_owner( $wp_user_id ) {
        update_option( 'twitchpress_streamlabs_main_owner', $wp_user_id );    
    }  

    public function update_main_access_token( $access_token ) {
        update_option( 'twitchpress_streamlabs_main_access_token', $access_token );
    }
    
    public function update_main_expires_in( $expires_in ) {
        update_option( 'twitchpress_streamlabs_main_expires_in', $expires_in );        
    }
    
    public function update_main_refresh_token( $refresh_token ) {
        update_option( 'twitchpress_streamlabs_main_refresh_token', $refresh_token );        
    }

    public function update_user_code( $wp_user_id, $code ) {
        update_user_meta( $wp_user_id, 'twitchpress_streamlabs_code', $code );  
    }
    
    public function update_user_access_token( $wp_user_id, $access_token ) {
        update_user_meta( $wp_user_id, 'twitchpress_streamlabs_access_token', $access_token );
    }
    
    public function update_user_expires_in( $wp_user_id, $expires_in ) {
        update_user_meta( $wp_user_id, 'twitchpress_streamlabs_expires_in ', $expires_in );
    }
        
    public function update_user_refresh_token( $wp_user_id, $refresh_token ) {
        update_user_meta( $wp_user_id, 'twitchpress_streamlabs_refresh_token', $refresh_token );
    }
    
    public function update_user_scope( $wp_user_id, $scope ) {
        update_user_meta( $wp_user_id, 'twitchpress_streamlabs_scope', $scope );
    }

    public function get_main_code( $code ) {
        return get_option( 'twitchpress_streamlabs_main_code', $code );    
    }    
    
    public function get_main_owner() {
        return get_option( 'twitchpress_streamlabs_main_owner' );    
    }  

    public function get_main_access_token() {
        return get_option( 'twitchpress_streamlabs_main_access_token' );
    }
    
    public function get_main_expires_in() {
        return get_option( 'twitchpress_streamlabs_main_expires_in' );        
    }
    
    public function get_main_refresh_token() {
        return get_option( 'twitchpress_streamlabs_main_refresh_token' );        
    }

    public function get_user_code() {
        return get_user_meta( $wp_user_id, 'twitchpress_streamlabs_code', true );  
    }
    
    public function get_user_access_token() {
        return get_user_meta( $wp_user_id, 'twitchpress_streamlabs_access_token', true );
    }
    
    public function get_user_expires_in() {
        return get_user_meta( $wp_user_id, 'twitchpress_streamlabs_expires_in ', true );
    }
        
    public function get_user_refresh_token() {
        return get_user_meta( $wp_user_id, 'twitchpress_streamlabs_refresh_token', true );
    }
    
    public function get_user_scope() {
        return get_user_meta( $wp_user_id, 'twitchpress_streamlabs_scope', true );
    }
    
    /**
    * Updates the MAIN WP USER credentials which should be the key holder. 
    * 
    * @version 1.0
    */
    public function update_main_users_meta() {
        $wp_user_id = $this->get_main_users_wp_id();
        if ( !$wp_user_id ) { return false; }
        $main_user = $this->get_main_streamlabs_user();
        if( $main_user ) {
            update_user_meta( $wp_user_id, 'streamlabs_id', $main_user->streamlabs->id );
            update_user_meta( $wp_user_id, 'streamlabs_display_name', $main_user->streamlabs->display_name );
                                  
            $main_channel = TwitchPress_Object_Registry::get( 'mainchannelauth' );
            
            // Update points for main channel, this is simply to satisfy the UI as the main owner will not have points for their own channel.  
            $this->update_users_points_meta( $wp_user_id, $main_channel->main_channel_id, 0 );
            return true;
        }
        return false;   
    }
    
    public function update_users_points_meta( $wp_user_id, $channel, $points ) {
        update_user_meta( $wp_user_id, 'streamlabs_points_' . $channel, 0 );
        update_user_meta( $wp_user_id, 'streamlabs_points_time_' . $channel, time() );      
    }
                         
    /**
    * Get the main users Streamlab credentails.  
    * 
    * @param mixed $service
    * @param mixed $service_object
    * 
    * @version 1.2
    */
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
        $curl_info = curl_version();

        $response = $curl->request( $url, 
            array( 
                'method'     => 'GET', 
                'body'       => $request_body,
                'user-agent' => 'curl/' . $curl_info['version'],
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
        // We can return scopes without additional information.
        if( $scopes_only ) { return $this->streamlabs_scopes; }
              
        $scope = array();
        
        // Add form input labels for use in form input labels. 
        $scope['donations.create']['label'] = __( 'Create Donation', 'twitchpress' );
        $scope['donations.read']['label']   = __( 'Get Donations', 'twitchpress' );
        $scope['alerts.create']['label']    = __( 'Create Alerts', 'twitchpress' );
        $scope['legacy.token']['label']     = __( 'Get Legacy Token', 'twitchpress' );
        $scope['socket.token']['label']     = __( 'Get Socket Token', 'twitchpress' );
        $scope['points.read']['label']      = __( 'Get Points', 'twitchpress' );
        $scope['points.write']['label']     = __( 'Add Points', 'twitchpress' );
        $scope['alerts.write']['label']     = __( 'Create Alerts', 'twitchpress' );
        $scope['credits.write']['label']    = __( 'Roll Credits', 'twitchpress' );
        $scope['profiles.write']['label']   = __( 'Get and Activate Profiles', 'twitchpress' );
        $scope['jar.write']['label']        = __( 'Jar', 'twitchpress' );
        $scope['wheel.write']['label']      = __( 'Spin Wheel', 'twitchpress' );

        // Add official api descriptions - copied from official API documention.
        $scope['donations.create']['apidesc'] = __( 'None.', 'twitchpress' );
        $scope['donations.read']['apidesc']   = __( 'None.', 'twitchpress' );
        $scope['alerts.create']['apidesc']    = __( 'None.', 'twitchpress' );
        $scope['legacy.token']['apidesc']     = __( 'None.', 'twitchpress' );
        $scope['socket.token']['apidesc']     = __( 'None.', 'twitchpress' );
        $scope['points.read']['apidesc']      = __( 'None.', 'twitchpress' );
        $scope['points.write']['apidesc']     = __( 'None.', 'twitchpress' );
        $scope['alerts.write']['apidesc']     = __( 'None.', 'twitchpress' );
        $scope['credits.write']['apidesc']    = __( 'None.', 'twitchpress' );
        $scope['profiles.write']['apidesc']   = __( 'None.', 'twitchpress' );
        $scope['jar.write']['apidesc']        = __( 'None.', 'twitchpress' );
        $scope['wheel.write']['apidesc']      = __( 'None.', 'twitchpress' );

        // Add user-friendly descriptions.
        $scope['donations.create']['userdesc'] = __( 'Create a new donation.', 'twitchpress' );
        $scope['donations.read']['userdesc']   = __( 'Get donations data.', 'twitchpress' );
        $scope['alerts.create']['userdesc']    = __( 'Create a new alert.', 'twitchpress' );
        $scope['legacy.token']['userdesc']     = __( 'Get the legacy token.', 'twitchpress' );
        $scope['socket.token']['userdesc']     = __( 'Get the socket token.', 'twitchpress' );
        $scope['points.read']['userdesc']      = __( 'Get a users points.', 'twitchpress' );
        $scope['points.write']['userdesc']     = __( 'Subtract points, import points and add points to all users.', 'twitchpress' );
        $scope['alerts.write']['userdesc']     = __( 'Jukebox controls including skip, mute, unmute, pause, unpause and send test alert.', 'twitchpress' );
        $scope['credits.write']['userdesc']    = __( 'Roll the credits.', 'twitchpress' );
        $scope['profiles.write']['userdesc']   = __( 'Get profiles and activate them.', 'twitchpress' );
        $scope['jar.write']['userdesc']        = __( 'Jar access.', 'twitchpress' );
        $scope['wheel.write']['userdesc']      = __( 'Spin the wheel.', 'twitchpress' );

        return $scope;  
    }   

    /**
    * Checks if minimum application credentials are set and ready in object.
    * 
    * @returns boolean
    * 
    * @version 2.0
    */
    public function is_app_set() {
                                           
        if( !$this->streamlabs_app_id ) {           
            $this->streamlabs_app_ready = false;
            return false;        
        } 

        if( !$this->streamlabs_app_secret ) {   
            $this->streamlabs_app_ready = false;
            return false;        
        } 
                 
        if( !$this->streamlabs_app_uri ) {     
            $this->streamlabs_app_ready = false;
            return false;        
        }    

        if( !$this->streamlabs_app_token ) {    
            $this->streamlabs_app_ready = false;
            return false;        
        }       

        $this->streamlabs_app_ready = true;
        return true;
    }
    
    public function app_missing_values() {
        $missing = array(); 

        if( !$this->streamlabs_app_id ) {
            $missing[] = __( 'Client ID', 'twitchpress' );        
        } 

        if( !$this->streamlabs_app_secret ) {
            $missing[] = __( 'Client Secret', 'twitchpress' );        
        } 
                 
        if( !$this->streamlabs_app_uri ) {
            $missing[] = __( 'Client URI', 'twitchpress' );        
        }    

        if( !$this->streamlabs_app_token ) {
            $missing[] = __( 'Client Token', 'twitchpress' );        
        }       

        return $missing;        
    }
    
    /**
    * @deprecated use twitchpress_streamlabs_validate_code()
    * 
    * @param mixed $code
    */
    public function validate_code( $code ) {
        if( strlen ( $code ) !== 40  ) {
            return false;
        }           
        
        if( !ctype_alnum( $code ) ) {
            return false;
        }
        
        return true;
    }
    
    /**
    * Request user access token after oAuth2 success. 
    * 
    * @returns body as stdClass
    * @version 1.2
    */
    public function api_request_token() {
        
        // Endpoint
        $url = 'https://streamlabs.com/api/v1.0/token';
                    
        // Call Parameters
        $body = array(
            'grant_type'       => 'authorization_code',
            'client_id'        => $this->streamlabs_app_id,
            'client_secret'    => $this->streamlabs_app_secret,
            'redirect_uri'     => $this->streamlabs_app_uri,
            'code'             => $this->streamlabs_app_code
        );                           

        $curl = new WP_Http_Curl();
        $curl_info = curl_version();
        
        $response = $curl->request( $url, 
            array( 
                'method'     => 'POST', 
                'body'       => $body, 
                'user-agent' => 'curl/' . $curl_info['version'], 
                'stream'     => false,
                'filename'   => false,
                'decompress' => false  
            ) 
        );            
                
        if( is_string( $response ) ) {
            $response = json_decode( $response );
        }
             
        if( !isset( $response['body'] ) ) {
            return false;
        }   
         
        $body = json_decode( $response['body'] );

        if( !isset( $body->access_token ) ) 
        {
            return false;
        }
        elseif( isset( $response['error'] ) ) 
        {
            return false;
        }
             
        // Store the credentials in transient while we finish the request. 
        set_transient( 'twitchpress_streamlabs_token_request_response', $response, 120 );

        return $body;           
    }
    
    /**
    * Checks if the giving WordPress user has authorized their Streamlabs
    * account to be accessed by the site. 
    * 
    * @return boolean
    * 
    * @version 1.0
    */
    public function is_user_ready( $wp_user_id ) {
        $username = get_user_meta( $wp_user_id, 'streamlabs_display_name', true );
        if( !$username || !is_string( $username ) ) 
        {
            return false;
        }

        return true;
    }   
     
    public function api_refresh_users_token() {
                  
    }      
        
    /**
    * Main function for retrieving a users Streamlabs points...
    * 
    * @param integer $wp_user_id
    * @param string $channel_name
    */
    public function get_wp_users_points( $wp_user_id = null ) {
        global $GLOBAL; 

        $points_array = get_user_meta( $wp_user_id, 'streamlabs_points', true );
        if( !$points_array || !is_array( $points_array ) || !isset( $points_array['synced_time'] ) || !isset( $points_array['points'] ) ) 
        {
            return $this->api_get_users_points( $wp_user_id, $channel_name );
        }
        
        
        // Call Streamlabs...
        $earliest_sync_time = $points_array['synced_time'] + 120;
        if( time() > $earliest_sync_time )
        {
            return $this->api_get_users_points( $wp_user_id, $channel_name );
        }

        return $points_array['points'];
    } 
            
    /**
    * Get the user associated with an access token...
    * 
    * @param mixed $access_token
    * 
    * @version 1.0
    */
    public function api_get_user_by_access_token( $access_token ) {

        $url = 'https://streamlabs.com/api/v1.0/user?access_token=' . $access_token;
   
        $curl = new WP_Http_Curl();
        $curl_info = curl_version();
        
        $response = $curl->request( $url, 
            array( 
                'method'     => 'GET',  
                'user-agent' => 'curl/' . $curl_info['version'],  
            ) 
        );     
        
        if( is_string( $response ) ) {
            $response = json_decode( $response );
        }

        if( !isset( $response['body'] ) ) {
            return false;
        }   
        
        $body = json_decode( $response['body'] );
        
        // Store the credentials in transient while we finish the request. 
        set_transient( 'twitchpress_streamlabs_' . $access_token, $response, 120 );

        return $body; 
    } 
    
    public function oauth2_url_mainaccount() {
        $scope = 'donations.create';
        $scope .= '+donations.read';
        $scope .= '+alerts.create';
        $scope .= '+legacy.token';
        $scope .= '+socket.token';
        $scope .= '+points.read';
        $scope .= '+points.write';
        $scope .= '+alerts.write';
        $scope .= '+credits.write';
        $scope .= '+profiles.write';
        $scope .= '+jar.write';
        $scope .= '+wheel.write';
        return 'https://www.streamlabs.com/api/v1.0/authorize?client_id=' . $this->streamlabs_app_id . '&redirect_uri=' . $this->streamlabs_app_uri . '&response_type=code&scope=' . $scope;   
    } 
            
    public function oauth2_url_visitors() {
        $scope = 'donations.read+donations.create';
        return 'https://www.streamlabs.com/api/v1.0/authorize?client_id=' . $this->streamlabs_app_id . '&redirect_uri=' . $this->streamlabs_app_uri . '&response_type=code&scope=' . $scope;    
    }
            
    /**
     * This function iterates through calls.  Put in here to keep the code the exact same every time
     * This assumes that all values are checked before being passed to here, PLEASE CHECK YOUR PARAMS
     * 
     * @param $functionName - [string] The calling function's identity, used for logging only
     * @param $url - [string] The URL to iterate on
     * @param $options - [array] The array of options to use for the iteration
     * @param $limit - [int] The limit of the query
     * @param $offset - [int] The starting offset of the query
     * 
     * -- OPTIONAL PARAMS --
     * The following params are all optional and are specific the the calling funciton.  Null disables the param from being passed
     * 
     * @param $arrayKey - [string] The key to look into the array for for data
     * @param $authKey - [string] The OAuth token for the session of calls
     * @param $hls - [bool] Limit the calls to only streams using HLS
     * @param $direction - [string] The sorting direction
     * @param $channels - [array] The array of channels to be included in the query
     * @param $embedable - [bool] Limit query to only channels that are embedable
     * @param $client_id - [string] Limit searches to only show content from the applications of the supplied client ID
     * @param $broadcasts - [bool] Limit returns to only show broadcasts
     * @param $period - [string] The period of time in which  to limit the search for
     * @param $game - [string] The game to limit the query to
     * @param $returnTotal - [bool] Sets iteration to not ignore the _total key
     * @param $sortBy - [string] Sets the sorting key
     * 
     * @return $object - [arary] unkeyed array of data requested or rmpty array if no data was returned
     * 
     * @version 1.5
     */ 
    protected function get_iterated( $url, $options, $limit, $offset, $arrayKey = null, $authKey = null, $hls = null, $direction = null, $channels = null, $embedable = null, $client_id = null, $broadcasts = null, $period = null, $game = null, $returnTotal = false, $sortBy = null) {

        // Check to make sure limit is an int
        if ((gettype($limit) != 'integer') && (gettype($limit) != 'double') && (gettype($limit) != 'float')) {
            // Either the limit was not valid
            $limit = -1;
        } elseif (gettype($limit != 'integer')) {
            // Make sure we have an int
            $limit = floor($limit);
            
            if ($limit < 0) {
                // Set to unlimited
                $limit = -1;
            }
        }

        // Perform the same check on the offset
        if ((gettype($offset) != 'integer') && (gettype($offset) != 'double') && (gettype($offset) != 'float')){
            $offset = 0;
        } elseif (gettype($offset != 'integer')) {
            // Make sure we have an int
            $offset = floor($offset);
            
            if ($offset < 0){
                // Set to base
                $offset = 0;
            }
        }

        // Init some vars
        $channelBlock = '';
        $grabbedRows = 0;
        $toDo = 0;
        $currentReturnRows = 0;
        $counter = 1;
        $iterations = 1;
        $object = array();
        if ($limit == -1){
            $toDo = 100000000; // Set to an arbritrarily large number so that we can itterate forever if need be
        } else {
            $toDo = $limit; // We have a finite amount of iterations to do, account for the _links object in the first return
        }
        
        // Calculate the starting limit
        if ($toDo > ( TWITCHPRESS_CALL_LIMIT_SETTING + 1)){
            $startingLimit = TWITCHPRESS_CALL_LIMIT_SETTING;
        } else {
            $startingLimit = $toDo;                                                             
        }
        
        // Build our GET array for the first iteration, these values will always be supplied
        $get = array('limit' => $startingLimit,
            'offset' => $offset);
            
        // Now check every optional param to see if it exists and att it to the array
        if ($authKey != null) {
            $get['oauth_token'] = $authKey;                                       
        }
        
        if ($hls != null) {
            $get['hls'] = $hls;                                                          
        }
        
        if ($direction != null) {
            $get['direction'] = $direction;                                          
        }
        
        if ($channels != null) {
            foreach ($channels as $channel) {
                $channelBlock .= $channel . ',';
                $get['channel'] = $channelBlock;
            }
            
            $channelBlock = rtrim($channelBlock, ',');                             
        }
        
        if ($embedable != null) {
            $get['embedable'] = $embedable;                                        
        }
        
        if ($client_id != null) {
            $get['client_id'] = $client_id;                                         
        }
        
        if ($broadcasts != null) {
            $get['broadcasts'] = $broadcasts;                                            
        }
        
        if ($period != null) {
            $get['period'] = $period;                                            
        }
        
        if ($game != null) {
            $get['game'] = $game;                                              
        }
        
        if ($sortBy != null) {
            $get['sortby'] = $sortBy;                                            
        }

        // Build our cURL query and store the array
        $return = json_decode($this->cURL_get($url, $get, $options), true);

        // check to see if return was 0, this indicates a staus return
        if ($return == 0) {
            for ($i = 1; $i <= TWITCHPRESS_RETRY_COUNTER; $i++) {
                $return = json_decode($this->cURL_get($url, $get, $options), true);
                if ($return != 0) {
                    break;
                }
            }
        }
        
        // How many returns did we get?
        if ($arrayKey != null) {
            if ((array_key_exists($arrayKey, $return) == 1) || (array_key_exists($arrayKey, $return) == true)){
                $currentReturnRows = count($return[$arrayKey]);
            } else {
                // Retry the call if we can
                for ($i = 1; $i <= TWITCHPRESS_RETRY_COUNTER; $i++){
                    $return = json_decode($this->cURL_get($url, $get, $options), true);
                    
                    if ((array_key_exists($arrayKey, $return) == 1) || (array_key_exists($arrayKey, $return) == true)){
                        $currentReturnRows = count($return[$arrayKey]);
                        break;
                    }
                }                
            }
            
        } else {
            $currentReturnRows = count($return);
        }

        // Iterate until we have everything grabbed we want to have
        while (($toDo > TWITCHPRESS_CALL_LIMIT_SETTING + 1) && ($toDo > 0) || ($limit == -1)){
            // check to see if return was 0, this indicates a staus return
            if ($return == 0){
                for ($i = 1; $i <= TWITCHPRESS_RETRY_COUNTER; $i++){
                    $return = json_decode($this->cURL_get($url, $get, $options), true);
                    
                    if ($return != 0){
                        break;
                    }
                }
            }
            
            // How many returns did we get?
            if ($arrayKey != null){
                if ((array_key_exists($arrayKey, $return) == 1) || (array_key_exists($arrayKey, $return) == true)) {
                    $currentReturnRows = count($return[$arrayKey]);
                } else {
                    // Retry the call if we can
                    for ($i = 1; $i <= TWITCHPRESS_RETRY_COUNTER; $i++){
                        $return = json_decode($this->cURL_get($url, $get, $options), true);
                        
                        if ((array_key_exists($arrayKey, $return) == 1) || (array_key_exists($arrayKey, $return) == true)){
                            $currentReturnRows = count($return[$arrayKey]);
                            break;
                        }
                    }                
                }
                
            } else {
                $currentReturnRows = count($return);
            }
            
            $grabbedRows += $currentReturnRows;

            // Return the data we requested into the array
            foreach ($return as $key => $value){
                // Skip some of the data we don't need
                if (is_array($value) && ($key != '_links')) {
                    foreach ($value as $k => $v) {
                        if (($k === '_links') || ($k === '_total') || !(is_array($v))){
                            continue;
                        }
                        
                        $object[$counter] = $v;
                        $counter ++;
                    }                        
                } elseif ($returnTotal && ($key == '_total') && !(key_exists('_total', $object) == 1)) {
                    // Are we on the _total key?  As well, have we already set it? (I might revert the key check if it ends up providing odd results)
                    $object['_total'] = $value;
                }
            }
            
            // Calculate our returns and our expected returns
            $expectedReturns = $startingLimit * $iterations;
            $currentReturns = $counter - 1;
            
            // Have we gotten everything we requested?
            if ($toDo <= 0){
                break;
            }
            
            // Are we no longer getting data? (Some fancy math here)
            if ($currentReturns != $expectedReturns) {
                break;
            }
            
            if ($limit != -1){
                $toDo = $limit - $currentReturns;
            }
            
            if ($toDo == 1){
                $toDo = 2; // Catch this, it will drop one return
            }
            
            // Check how many we have left
            if (($toDo > $startingLimit) && ($toDo > 0) && ($limit != -1)){

                $get = array('limit' => $currentReturns + $startingLimit,
                    'offset' => $currentReturns);
                    
                // Now check every optional param to see if it exists and att it to the array
                if ($authKey != null) {
                    $get['oauth_token'] = $authKey;
                }
                
                if ($hls != null) {
                    $get['hls'] = $hls;            
                }
                
                if ($direction != null) {
                    $get['direction'] = $direction;   
                }
                
                if ($channels != null) {
                    foreach ($channels as $channel) {
                        $channelBlock .= $channel . ',';
                        $get['channel'] = $channelBlock;
                    }
                    
                    $channelBlock = rtrim($channelBlock, ','); 
                }
                
                if ($embedable != null) {
                    $get['embedable'] = $embedable;                                         
                }
                
                if ($client_id != null) {
                    $get['client_id'] = $client_id;                                        
                }
                
                if ($broadcasts != null) {
                    $get['broadcasts'] = $broadcasts;                                            
                }
                
                if ($period != null) {
                    $get['period'] = $period;                                            
                }
                
                if ($game != null) {
                    $get['game'] = $game;
                }
                
                if ($sortBy != null) {
                    $get['sortby'] = $sortBy;
                }
            } elseif ($limit == -1) {
                
                $get = array('limit' => $currentReturns + $startingLimit,
                    'offset' => $currentReturns);
                    
                // Now check every optional param to see if it exists and att it to the array
                if ($authKey != null) {
                    $get['oauth_token'] = $authKey;
                }
                
                if ($hls != null) {
                    $get['hls'] = $hls;            
                }
                
                if ($direction != null) {
                    $get['direction'] = $direction;   
                }
                
                if ($channels != null) {
                    foreach ($channels as $channel) {
                        $channelBlock .= $channel . ',';
                        $get['channel'] = $channelBlock;
                    }
                    
                    $channelBlock = rtrim($channelBlock, ',');   
                }
                
                if ($embedable != null) {
                    $get['embedable'] = $embedable;
                }
                
                if ($client_id != null) {
                    $get['client_id'] = $client_id;
                }
                
                if ($broadcasts != null) {
                    $get['broadcasts'] = $broadcasts; 
                }
                
                if ($period != null) {
                    $get['period'] = $period;       
                }
                
                if ($game != null) {
                    $get['game'] = $game;         
                }
                
                if ($sortBy != null) {
                    $get['sortby'] = $sortBy;  
                }
                
            // Last return in a limited case    
            } else { 

                $get = array('limit' => $toDo + 1,
                    'offset' => $currentReturns);
                    
                // Now check every optional param to see if it exists and att it to the array
                if ($authKey != null) {
                    $get['oauth_token'] = $authKey;

                }
                
                if ($hls != null){
                    $get['hls'] = $hls;            
                }
                
                if ($direction != null){
                    $get['direction'] = $direction;   
                }
                
                if ($channels != null){
                    foreach ($channels as $channel){
                        $channelBlock .= $channel . ',';
                        $get['channel'] = $channelBlock;
                    }
                    
                    $channelBlock = rtrim($channelBlock, ','); 
                }
                
                if ($embedable != null){
                    $get['embedable'] = $embedable;
                }
                
                if ($client_id != null){
                    $get['client_id'] = $client_id;
                }
                
                if ($broadcasts != null){
                    $get['broadcasts'] = $broadcasts;
                }
                
                if ($period != null){
                    $get['period'] = $period;
                }
                
                if ($game != null){
                    $get['game'] = $game;
                }
                
                if ($sortBy != null){
                    $get['sortby'] = $sortBy;
                }
            }

            // Run a new query
            unset($return); // unset for a clean return
            $return = json_decode($this->cURL_get($url, $get, $options), true);
            
            $iterations ++;
        }

        // Run this one last time, a little redundant, but we could have skipped a return
        foreach ($return as $key => $value){
            // Skip some of the data we don't need
            if (is_array($value) && ($key != '_links')) {
                foreach ($value as $k => $v){
                    if (($k === '_links') || ($k === '_total') || !(is_array($v))){
                        continue;
                    }
                    
                    $object[$counter] = $v;
                    $counter ++;
                }                        
            } elseif ($returnTotal && ($key == '_total') && !(key_exists('_total', $object) == 1)) {
                // Are we on the _total key?  As well, have we already set it? (I might revert the key check if it ends up providing odd results)
                $object['_total'] = $value;
            }
        }
        
        if ($returnTotal && !key_exists('_total', $object) == 1){
            $object['_total'] = count($object);
        }
        
        return $object;
    }    
    
    /**
    * Confirms if the $scope has been permitted for the
    * $side the call applies to.
    * 
    * Should be called at the beginning of most calls methods. 
    * 
    * The $function is passed to aid debugging. 
    * 
    * @param mixed $scope
    * @param mixed $side
    * @param mixed $function
    * 
    * @version 1.2
    */
    public function confirm_scope( $scope, $side, $function ) {
        global $bugnet;
        
        // Confirm $scope is a real scope. 
        if( !in_array( $scope, twitchpress_twitch_scopes ) ) {
            ### error 
            return sprintf( __( 'A Kraken5 call is using an invalid scope. See %s()', 'twitchpress' ), $function );
        }    
        
        // Check applicable $side array scope.
        switch ( $side ) {
           case 'user':
                if( !in_array( $scope, twitchpress_get_visitor_scopes() ) ) { 
                    ### error
                    return sprintf( __( 'TwitchPress requires visitor scope: %s for function %s()', 'twitchpress' ), $scope, $function ); 
                }
             break;           
           case 'channel':
                if( !in_array( $scope, twitchpress_get_global_accepted_scopes() ) ) { 
                    ### error
                    return sprintf( __( 'TwitchPress scope %s was not permitted by administration and is required by %s().', 'twitchpress' ), $scope, $function ); 
                }
             break;         
           case 'both':
                // This measure is temporary, to avoid faults, until we confirm which $side some calls apply to. 
                if( !in_array( $scope, twitchpress_get_global_accepted_scopes() ) &&
                        !in_array( $scope, twitchpress_get_visitor_scopes() ) ) { 
                            ### error
                            return __( 'A Kraken5 call requires a scope that has not been permitted.', 'twitchpress' ); 
                }
             break;
        }
        
        // Arriving here means the scope is valid and was found. 
        return true;
    }   

    /**             
    * Runs when an API's credentials are being changed. 
    * 
    * GitHub issue created because the $redirect_uri is not being applied yet. 
    * https://github.com/RyanBayne/TwitchPress/issues/263
    * 
    * @param mixed $redirect_uri
    * @param mixed $key
    * @param mixed $secret
    * 
    * @version 3.0
    */
    public function do_application_being_updated( $redirect_uri, $key, $secret ) { 
                                         
        // Generate local oauth state credentials for security.
        $new_state = $this->new_state( array (             
            'redirectto' => $redirect_uri, // i.e. admin_url( 'admin.php?page=twitchpress&tab=otherapi&section=streamlabs' ),
            'userrole'   => 'administrator',
            'outputtype' => 'admin',// use to configure output levels, sensitivity of data and styling.
            'reason'     => 'streamlabsowneroauth2request',// use in conditional statements to access applicable procedures.
            'function'   => __FUNCTION__,
            'file'       => __FILE__,
        ));  
                         
        // Add the random state key for our credentials to the API request for validation on return. 
        $uri = add_query_arg( 'state', $new_state['statekey'], $this->oauth2_url_mainaccount() );
                    
        twitchpress_redirect_tracking( $uri, __LINE__, __FUNCTION__, __FILE__ );
        exit;
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