<?php
/**
 * All-API Streamlabs API Class for TwitchPress.
 *
 * @link https://dev.streamlabs.com/
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Core
 * @version  1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( !class_exists( 'TWITCHPRESS_All_API' ) ) :

class TWITCHPRESS_All_API_Streamlabs {

    // App Credentials
    protected $allapi_service = null;// name, not title
    protected $allapi_profile = null;// kraken, helix, streamlabs etc
    protected $allapi_app_key = null;
    protected $allapi_app_secret = null;
    protected $allapi_app_uri = null;
    protected $allapi_app_code = null;
    protected $allapi_app_token = null;
    
    // User Credentials
    protected $allapi_user_wordpress_id = null;
    protected $allapi_user_service_id = null; 
    protected $allapi_user_oauth_code = null;
    protected $allapi_user_oauth_token = null;
    
    // Debugging variables.
    public $allapi_call_name = 'Unknown';
    public $allapi_sandbox_mode = false;

    public function __construct(){
        // Load logging, reporting and debugging service. 
        $this->bugnet = new BugNet();
        
        // Set all app credentials for this library to use. 
        $this->set_application_credentials();
    } 
    
    /**
    * This method makes it possible to store different Developer Applications
    * in the WordPress options table. 
    * 
    * @param mixed $app
    * 
    * @version 1.23
    */
    public function set_application_credentials( $app_profile ) {
        
        $this->allapi_service_title = 'STILL TO DO';
        $this->allapi_profile = $app_profile;
    
        // Store the apps credentials in their own options.
        $this->allapi_subject_id = get_option( 'allapi_streamlabs_' . $app . '_subject_id' );
        $this->allapi_app_key    = get_option( 'allapi_streamlabs_' . $app . '_key' );
        $this->allapi_app_secret = get_option( 'allapi_streamlabs_' . $app . '_secret' );
        $this->allapi_app_uri    = get_option( 'allapi_streamlabs_' . $app . '_uri' );
        $this->allapi_app_code   = get_option( 'allapi_streamlabs_' . $app . '_code' );
        $this->allapi_app_token  = get_option( 'allapi_streamlabs_' . $app . '_token' );   
            
        // Tokens expire so we will check our current token and update option if needed.  
        $this->establish_application_token( __FUNCTION__ );
        
        // Set users token.
        $this->allapi_user_oauth_token = twitchpress_get_user_token( get_current_user_id() );
        
        // Set $allapi_service_object which makes our requested API class ready to use. 
        $this->set_services_object( $this->allapi_service );             
    }
    
    /**
    * Checks if application credentials are set.
    * 
    * @returns boolean true (all set) else array of missing values.
    * 
    * @version 1.23
    */
    public function is_app_set() {
        $missing = array();
        
        if( !$this->allapi_subject_id ) {
            $missing[] = __( 'Subject ID', 'twitchpress' );        
        }    
        
        if( !$this->allapi_app_uri ) {
            $missing[] = __( 'Streamlabs Application URI', 'twitchpress' );        
        }    
        
        if( !$this->allapi_app_key ) {
            $missing[] = __( 'Streamlabs Application Key', 'twitchpress' );        
        }    
        
        if( !$this->allapi_app_secret ) {
            $missing[] = __( 'Streamlabs Application Secret', 'twitchpress' );        
        }    
        
        if( !$this->allapi_app_code ) {
            $missing[] = __( 'Streamlabs Application Code', 'twitchpress' );        
        }    
        
        if( !$this->allapi_app_token ) {
            $missing[] = __( 'Streamlabs Application Token', 'twitchpress' );        
        }       
        
        if( $missing ) {
            return $missing;
        }
        
        return true;
    }
    
    /**
     * Listen for administrators main account, for a giving service, being
     * put through oAuth2 request at the point of redirection from service to WordPress. 
     * 
     * @version 1.23
     */
    public static function init() {   
        add_action( 'plugins_loaded', array( __CLASS__, 'administrator_main_account_listener' ), 50 );
    }
    
    /**
    * Listen for administration only oAuth2 return/redirect. 
    * 
    * Return when a negative condition is found.
    * 
    * Add methods between returns, where arguments satisfy minimum security. 
    * 
    * @version 1.23
    */
    public static function administrator_main_account_listener() {
        
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
            
        if( isset( $_GET['error'] ) ) {  
            return;
        } 
         
        if( !isset( $_GET['scope'] ) ) {       
            return;
        }     
            
        if( !isset( $_GET['state'] ) ) {       
            return;
        }    
        
        // Change to true when $_REQUEST cannot be validated. 
        $return = false;
        $return_reason = '';
        
        // Start a trace that continues throughout the oauth2 procedure. 
        global $bugnet;
        $bugnet->trace( 'allapi_oauth2mainaccount',
                        __LINE__,
                        __FUNCTION__,
                        __FILE__,
                        false,
                        sprintf( __( 'Streamlabs Listener: doing listener for main Streamlabs account setup.', 'twitchpress' ), $this->allapi_service_title )
        );
                     
        if( !isset( $_GET['code'] ) ) {       
            $return = true;
            $return_reason .= sprintf( __( 'Streamlabs Listener: No code returned.', 'twitchpress' ), $this->allapi_service_title );
        }          

        // We require the local current state value stored in transient.
        // This transient is created when generating the oAuth2 URL and used to validate everything about the request. 
        elseif( !$transient_state = get_transient( 'twitchpress_oauth_' . $_GET['state'] ) ) {      
            $return = true;
            $return_reason .= sprintf( __( 'Streamlabs Listener: No matching transient.', 'twitchpress' ), $this->allapi_service_title );
        }  
        
        // Ensure the reason for this request is an attempt to set the main channels credentials
        elseif( !isset( $transient_state['reason'] ) ) {
            $return = true;
            $return_reason .= sprintf( __( 'Streamlabs Listener: no reason for request.', 'twitchpress' ), $this->allapi_service_title );            
        }              
         
        // Ensure we have the admin view or page the user needs to be sent to. 
        elseif( $transient_state['reason'] !== 'mainadminaccountsetup' ) {         
            $return = true;
            $return_reason .= sprintf( __( 'Streamlabs Listener: reason rejected.', 'twitchpress' ), $this->allapi_service_title );    
        }
                 
        // Ensure we have the admin view or page the user needs to be sent to. 
        elseif( !isset( $transient_state['redirectto'] ) ) {         
            $return = true;
            $return_reason .= sprintf( __( 'Streamlabs Listener: "redirectto" value does not exist.', 'twitchpress' ), $this->allapi_service_title );    
        } 
          
        // For this procedure the userrole MUST be administrator.
        elseif( !isset( $transient_state['userrole'] ) ) {        
            $return = true;
            $return_reason .= sprintf( __( 'Streamlabs Listener: unexpected request, related to the main account.', 'twitchpress' ), $this->allapi_service_title );    
        }          
        
        elseif( !isset( $transient_state['userrole'] ) || 'administrator' !== $transient_state['userrole'] ) {        
            $return = true;
            $return_reason .= __( 'Streamlabs Listener: not an administrator.', 'twitchpress' );    
        }         
                
        // NEW IF - Validate the code as a measure to prevent URL spamming that gets further than here.
        elseif( !twitchpress_validate_code( $_GET['code'] ) ) {        
            $return = true;
            $return_reason .= sprintf( __( 'Streamlabs Listener: invalid code.', 'twitchpress' ), $this->allapi_service_title );
        }

        // If we have a return reason, add it to the trace then do the return. 
        if( $return === true ) {
            // We can end the trace here early but more trace entries will follow. 
            $bugnet->trace( 'streamlabs_oauth2mainaccount',
                __LINE__,
                __FUNCTION__,
                __FILE__,
                true,
                $return_reason
            );
            
            return false;
        } 
        
        // Create API calls object for the current service. 
        $service_calls_object = $this->load_calls_object( $transient_state['app_service'] );
        
        // Generate oAuth token (current user, who is admin, for the giving profile)
        $token_array = $this->request_user_access_token( $_GET['code'], __FUNCTION__ );
        
        // Update this administrators access to the giving service.
        $this->update_user_code( $wp_user_id, $_GET['code'] );
        $this->update_user_token( $wp_user_id, $token_array['access_token'] );
        $this->update_user_refresh_token( $wp_user_id, $token_array['refresh_token'] );

        // Start storing main channel credentials.  
        $this->update_app_code( $service, $_GET['code'] );
        $this->update_app_wpowner_id( $service, $wp_user_id );
        $this->update_app_token( $service, $token_array['access_token'] );
        $this->update_app_refresh_token( $service, $token_array['refresh_token'] );
        $this->update_app_scope( $service, $token_array['scope'] );

        // Token notice
        TwitchPress_Admin_Notices::add_custom_notice( 'streamlabs_mainapplicationsetup', sprintf( __( '%s provided a token, allowing this site to access your channel based on the permissions gave.'), $this->allapi_service_title )  );
               
        // Run a test to ensure all credentials are fine and that the services subject exists i.e. the users Twitch username/channel.
        // The response from the service is stored, the data is used to populate required values.  
        $test_result = $this->app_credentials_test( $service, $service_calls_object );
        
        if( !$test_result )
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
               
        switch ( $this->allapi_service ) {
           case 'twitch':
                // Subject (Twitch.tv channel) is owned or under control by the admin user.  
                twitchpress_update_user_oauth( 
                    get_current_user_id(), 
                    $_GET['code'], 
                    $token_array['access_token'], 
                    $user_objects['users'][0]['_id'] 
                );
             break;
           case 'streamlabs':
        
             break;
           case 'youtube':
        
             break;
        }
  
        // Not going to end trace here, will end it on Setup Wizard. 
        $bugnet->trace( 'streamlabs_oauth2mainaccount',
            __LINE__,
            __FUNCTION__,
            __FILE__,
            true,
            __( 'Streamlabs Listener: Pass, forwarding user to: ' . $transient_state['redirectto'], 'twitchpress' )
        );
               
        // Forward user to the custom destinaton i.e. where they were before oAuth2. 
        twitchpress_redirect_tracking( $transient_state['redirectto'], __LINE__, __FUNCTION__ );
        exit;
    }     
    
    /**
    * Store a users oAuth2 code in user meta.
    * 
    * @param mixed $wp_user_id
    * @param mixed $code
    * @param mixed $service
    * 
    * @version 1.23
    */
    public function update_user_code( $wp_user_id, $code ) {
        return update_user_meta( $wp_user_id, 'twitchpress_streamlabs_code', $code );        
    }
    
    /**
    * Store a users oAuth2 token in user meta. 
    * 
    * @param mixed $wp_user_id
    * @param mixed $service
    * @param mixed $token
    * 
    * @version 1.23
    */
    public function update_user_token( $wp_user_id, $token ) {
        return update_user_meta( $wp_user_id, 'twitchpress_streamlabs_token', $token );        
    }    
    
    /**
    * Store a users oAuth2 refresh token in user meta.
    * 
    * @param mixed $wp_user_id
    * @param mixed $service
    * @param mixed $refresh_token
    * 
    * @version 1.23
    */
    public function update_user_refresh_token( $wp_user_id, $refresh_token ) {
        return update_user_meta( 
            $wp_user_id,
            'streamlabs_refresh_token',
            $refresh_token 
        );        
    }    
    
    /**
    * Update services code.
    * 
    * @param mixed $service
    * @param mixed $code
    * 
    * @version 1.23
    */
    public function update_app_code( $code ) {
        return update_option( 'streamlabs_code', $code, false );    
    } 
    
    /**
    * Update services credentials group with a WordPress Owner ID. 
    * 
    * @param mixed $service
    * @param mixed $wp_user_id
    * 
    * @version 1.23
    */
    public function update_app_wpowner_id( $wp_user_id ) {
        return update_option( 'streamlabs_wpowner_id', $wp_user_id, false );    
    }
    
    /**
    * Update servics token. 
    *     
    * @param mixed $service
    * @param mixed $token
    * 
    * @version 1.23
    */
    public function update_app_token( $token ) {
        return update_option( 'streamlabs_token', $token, false );    
    }  
    
    /**
    * Update services refresh token.
    * 
    * @param mixed $service
    * @param mixed $refresh_token
    * 
    * @version 1.23
    */
    public function update_app_refresh_token( $refresh_token ) {
        return update_option( 'streamlabs_refresh_token', $refresh_token, false );    
    }
    
    /**
    * Update services scope array. 
    * 
    * This is the returned scopes, not scopes selected by admin or user. 
    * These are the scopes sent to the service and accepted. 
    * 
    * @param mixed $service
    * @param mixed $scope_array
    * 
    * @version 1.23
    */
    public function update_app_scope( $scope_array ) {
        return update_option( 'streamlabs_scope', $scope_array, false );    
    }
    
    /**
    * Runs a different test for each service.  
    * 
    * @param mixed $service
    * @param mixed $service_object
    * 
    * @version 1.0
    */
    public function app_credentails_test() {

        return false;  
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
              
        $scope = array(
            'EMPTY' => array(),
        );
        
        // Add form input labels for use in form input labels. 
        $scope['empty']['label']                  = __( 'EMPTY', 'twitchpress' );

                
        // Add official api descriptions - copied from official API documention.
        $scope['empty']['apidesc']                  = __( 'OI DEV EMPTY AND EMPTY AND EMPTY.', 'twitchpress' );

        // Add user-friendly descriptions.
        $scope['empty']['userdesc']                  = __( 'HELLO USER THIS IS EMPTY.', 'twitchpress' );

        return $scope;  
    }   
    
    
    
    
    
    
    
    /**
     * This operates a GET style command through cURL.  Will return raw data as an associative array
     * 
     * @param $url - [string] URL supplied for the connection
     * @param $get - [array]  All supplied data used to define what data to get
     * @param $options - [array] Set options for the cURL session
     * @param $returnStatus - [bool] Sets the function to return the numerical status instead of the raw result
     * 
     * @return $result - [mixed] The raw return of the resulting query or the numerical status
     * 
     * @version 1.6
     */
    protected function cURL_get($url, array $get = array(), array $options = array(), $returnStatus = false, $function = '' ){

        $header = array('Accept: application/vnd.twitchtv.v' . TWITCHPRESS_API_VERSION . '+json'); // Always included
        $header = (( $this->twitch_client_id !== '') && ($this->twitch_client_id !== ' ')) ? array_merge($header, array('Client-ID: ' . $this->twitch_client_id)) : $header;
        $header = (( TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $get) === 1) 
                        || (array_key_exists('oauth_token', $get) === true))) 
                                ? array_merge($header, array('Authorization: OAuth ' . $get['oauth_token'])) : $header ;
                                                        // v6 Authorization: Bearer    <access token>"  https://api.twitch.tv/helix/

        if (( TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $get) === 1) || (array_key_exists('oauth_token', $get) === true))) {
            unset($get['oauth_token']);
        }

        $cURL_URL = rtrim($url . '?' . http_build_query($get), '?');
              
        $default = array(
            CURLOPT_URL => $cURL_URL, 
            CURLOPT_HEADER => 0, 
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CONNECTTIMEOUT => TWITCHPRESS_DEFAULT_RETURN_TIMEOUT,
            CURLOPT_TIMEOUT => TWITCHPRESS_DEFAULT_TIMEOUT,
            CURLOPT_HTTPHEADER => $header
        );
    
        // Do we have a certificate to use?  if OpenSSL is available, there will be a certificate
        if ( TWITCHPRESS_CERT_PATH != '' ){

            // Overwrite outr defaults to include the SSL cert and options
            array_merge($default, array(
                CURLOPT_SSL_VERIFYPEER => 1,
                CURLOPT_SSL_VERIFYHOST => 1,
                CURLOPT_CAINFO         => realpath( TWITCHPRESS_CERT_PATH ) // This requires the real path of the certificate (Strict, may use CAPATH instead if it causes problems)
            ));
        }

        $handle = curl_init();
        
        if (function_exists('curl_setopt_array')) {
            curl_setopt_array($handle, ($options + $default));
        } else { 
            // nope, set them one at a time
            // Options are set last so you can override anything you don't want to keep from defaults
            foreach (($default + $options) as $key => $opt) {
                curl_setopt($handle, $key, $opt);
            }
        }
        
        $result = curl_exec( $handle );
        $httpdStatus = curl_getinfo( $handle, CURLINFO_HTTP_CODE );
        
        // Check our HTTPD status that was returned for error returns
        $error_string = '';
        $error_no = '';
        if (($httpdStatus == 404) || ($httpdStatus == 0) || ($httpdStatus == 503)) 
        {
            $error_string = curl_error($handle);
            $error_no = curl_errno($handle);
            $this->bugnet->log_error( __FUNCTION__, sprintf( __( 'TwitchPress Error %s: %s', 'twitchpress' ), $error_no, $error_string ), array(), true );
        }
        
        curl_close($handle);
        
        // Log the HTTP status in more detail if it isn't a good response. 
        if( $httpdStatus !== 200 ) 
        {
            $status_meaning = kraken_httpstatuses( $httpdStatus, 'wiki' );
            if( !is_string( $status_meaning ) ) { $status_meaning = __( 'Sorry, no more information could be retrieved for this status.', 'twitchpress' ); }
            $this->bugnet->log( __FUNCTION__, sprintf( __( 'TwitchPress HTTPDStatus: %s - %s', 'twitchpress' ), $httpdStatus, $status_meaning ), array(), true, false );
        }
        
        if ($returnStatus) {
            $result_details = $httpdStatus;
        } else {
            $result_details = $result; 
        }
        
        // Store the get request - this is done using transients. 
        $this->store_curl_get( $function, 
                               json_decode( $result_details ), 
                               $httpdStatus, 
                               $header, 
                               $get, 
                               $url,
                               $cURL_URL, 
                               $error_string, 
                               $error_no, 
                               array( /* args */)     
        ); 
        
        // Are we returning the HHTPD status?
        if ($returnStatus) {
            return $httpdStatus;
        } else {
            return $result; 
        }
    }
   
   
        
   
    /**
     * This operates a POST style cURL command.  Will return success.
     * 
     * @param $url - [string] URL supplied for the connection
     * @param $post - [array] All supplied data used to define what data to post
     * @param $options - [array] Set options for the cURL session
     * @param $returnStatus - [bool] Sets the function to return the numerical status instead of the raw result
     * 
     * @return $result - [mixed] The raw return of the resulting query or the numerical status
     * 
     * @version 1.7
     */ 
    protected function cURL_post($url, array $post = array(), array $options = array(), $returnStatus = false){
        $postfields = '';
        
        // Specify the header
        $header = array('Accept: application/vnd.twitchtv.v' . TWITCHPRESS_API_VERSION . '+json'); // Always included
        $header = (( TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $post) === 1) || (array_key_exists('oauth_token', $post) === true))) ? array_merge($header, array('Authorization: OAuth ' . $post['oauth_token'])) : $header;
        $header = (( $this->twitch_client_id !== '') && ($this->twitch_client_id !== ' ')) ? array_merge($header, array('Client-ID: ' . $this->twitch_client_id)) : $header;                           // v6 Authorization: Bearer    <access token>"  https://api.twitch.tv/helix/
    
        if (( TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $post) === 1) || (array_key_exists('oauth_token', $post) === true))) {
            unset($post['oauth_token']);
        }

        // Custom build the post fields
        foreach ($post as $field => $value) {
            $postfields .= $field . '=' . $value . '&';
        }
        
        // Strip the trailing &
        $postfields = rtrim($postfields, '&');
        
        $default = array( 
            CURLOPT_CONNECTTIMEOUT => TWITCHPRESS_DEFAULT_RETURN_TIMEOUT,
            CURLOPT_TIMEOUT => TWITCHPRESS_DEFAULT_TIMEOUT,
            CURLOPT_POSTFIELDS => $postfields,
            CURLOPT_URL => $url, 
            CURLOPT_POST => count($post),
            CURLOPT_HEADER => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_FRESH_CONNECT => 1, 
            CURLOPT_RETURNTRANSFER => 1, 
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_HTTPHEADER => $header
        );
                  
        // Do we have a certificate to use?  if OpenSSL is available, there will be a certificate
        if ( TWITCHPRESS_CERT_PATH != '' ){
            // Overwrite outr defaults to include the SSL cert and options
            array_merge($default, array(
                CURLOPT_SSL_VERIFYPEER => 1,
                CURLOPT_SSL_VERIFYHOST => 1,
                CURLOPT_CAINFO         => realpath( TWITCHPRESS_CERT_PATH ) // This requires the real path of the certificate (Strict, may use CAPATH instead if it causes problems)
            ));
        }

        $handle = curl_init();
        
        if (function_exists('curl_setopt_array')) {
            curl_setopt_array($handle, ($options + $default));
        } else { // nope, set them one at a time
            // Options are set last so you can override anything you don't want to keep from defaults.
            foreach (($default + $options) as $key => $opt) {
                curl_setopt($handle, $key, $opt);
            }
        }
      
        $result = curl_exec( $handle );
        
        $httpdStatus = curl_getinfo( $handle, CURLINFO_HTTP_CODE );
        
        // Check our HTTPD status that was returned for error returns
        if (($httpdStatus == 404) || ($httpdStatus == 0) || ($httpdStatus == 503)) {
            $error_string = curl_error($handle);
            $error_no = curl_errno($handle);
            $this->bugnet->log_error( __FUNCTION__, sprintf( __( 'TwitchPress Error %s: %s', 'twitchpress' ), $error_no, $error_string ), array(), true );
        }
        
        curl_close($handle);
        
        // Log anything that isn't a good response. 
        if( $httpdStatus !== 200 ) {
            $status_meaning = kraken_httpstatuses( $httpdStatus, 'wiki' );
            if( !is_string( $status_meaning ) ) { $status_meaning = __( 'Sorry, no more information could be retrieved for this status.', 'twitchpress' ); }
            $this->bugnet->log( __FUNCTION__, sprintf( __( 'TwitchPress HTTPDStatus: %s - %s', 'twitchpress' ), $httpdStatus, $status_meaning ), array(), true, false );
        }
        
        // Are we returning the HHTPD status?
        if ($returnStatus) {
            return $httpdStatus;
        } else {
            return $result; 
        }
    }
    
    
     
    
    /**
     * This operates a PUT style cURL command.  Will return success.
     * 
     * @param $url - [string] URL supplied for the connection
     * @param $put - [array] All supplied data used to define what data to put
     * @param $options - [array] Set options for the cURL session
     * @param $returnStatus - [bool] Sets the function to return the numerical status instead of the raw result
     * 
     * @return $result - [mixed] The raw return of the resulting query or the numerical status
     * 
     * @version 1.6
     */ 
    protected function cURL_put($url, array $put = array(), array $options = array(), $returnStatus = false) {
        $postfields = '';

        // Specify the header
        $header = array('Accept: application/vnd.twitchtv.v' . TWITCHPRESS_API_VERSION . '+json'); // Always included
        $header = ((TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $put) === 1) || (array_key_exists('oauth_token', $put) === true))) ? array_merge($header, array('Authorization: OAuth ' . $put['oauth_token'])) : $header ;
        $header = (($this->twitch_client_id !== '') && ($this->twitch_client_id !== ' ')) ? array_merge($header, array('Client-ID: ' . $this->twitch_client_id)) : $header;                         // v6 Authorization: Bearer    <access token>"  https://api.twitch.tv/helix/
        
        if ((TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $put) === 1) || (array_key_exists('oauth_token', $put) === true))) {
            unset($put['oauth_token']);
        }

        // Custom build the post fields
        $postfields = (is_array($put)) ? http_build_query($put) : $put;
        
        $default = array( 
            CURLOPT_CONNECTTIMEOUT => TWITCH_DEFAULT_RETURN_TIMEOUT,
            CURLOPT_TIMEOUT => TWITCH_DEFAULT_TIMEOUT,
            CURLOPT_POSTFIELDS => $postfields,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_FRESH_CONNECT => 1, 
            CURLOPT_RETURNTRANSFER => 1, 
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_HTTPHEADER => $header
        );
        
        // Do we have a certificate to use?  if OpenSSL is available, there will be a certificate
        if ( TWITCHPRESS_CERT_PATH != '' ){

            // Overwrite outr defaults to include the SSL cert and options
            array_merge($default, array(
                CURLOPT_SSL_VERIFYPEER => 1,
                CURLOPT_SSL_VERIFYHOST => 1,
                CURLOPT_CAINFO         => realpath( TWITCHPRESS_CERT_PATH ) // This requires the real path of the certificate (Strict, may use CAPATH instead if it causes problems)
            ));
        }

        $handle = curl_init();
        
        if ( function_exists('curl_setopt_array') ) {
            curl_setopt_array($handle, ($options + $default));
        } else { // nope, set them one at a time
            // Options are set last so you can override anything you don't want to keep from defaults.
            foreach (($default + $options) as $key => $opt) {
                curl_setopt($handle, $key, $opt);
            }
        }
        
        $result = curl_exec($handle);
        $httpdStatus = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        
        // Check our HTTPD status that was returned for error returns
        if (($httpdStatus == 404) || ($httpdStatus == 0) || ($httpdStatus == 503)) {
            $error_string = curl_error($handle);
            $error_no = curl_errno($handle);
            $this->bugnet->log_error( __FUNCTION__, sprintf( __( 'TwitchPress Error %s: %s', 'twitchpress' ), $error_no, $error_string ), array(), true );       
        }

        curl_close($handle);
        
        // Log anything that isn't a good response. 
        if( $httpdStatus !== 200 ) {
            $status_meaning = kraken_httpstatuses( $httpdStatus, 'wiki' );
            if( !is_string( $status_meaning ) ) { $status_meaning = __( 'Sorry, no more information could be retrieved for this status.', 'twitchpress' ); }
            $this->bugnet->log( __FUNCTION__, sprintf( __( 'TwitchPress HTTPDStatus: %s - %s', 'twitchpress' ), $httpdStatus, $status_meaning ), array(), true, false );
        }
        
        // Are we returning the HHTPD status?
        if ($returnStatus) {
            return $httpdStatus;
        } else {
            return $result; 
        }
    }
    
    
    
        
   
    /**
     * This operates a POST style cURL command with the DELETE custom command option.
     * 
     * @param $url - [string] URL supplied for the connection
     * @param $post = [array]  All supplied data used to define what data to delete
     * @param $options - [array] Set options for the cURL session
     * @param $returnStatus - [bool] Sets the function to return the numerical status instead of the raw result {DEFAULTS TRUE}
     * 
     * @return $result - [mixed] The raw return of the resulting query or the numerical status
     * 
     * @version 1.2
     */ 
    protected function cURL_delete($url, array $post = array(), array $options = array(), $returnStatus = true) {
        // Specify the header
        $header = array('Accept: application/vnd.twitchtv.v' . TWITCHPRESS_API_VERSION . '+json'); // Always included
        $header = ((TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $post) === 1) || (array_key_exists('oauth_token', $post) === true))) ? array_merge($header, array('Authorization: OAuth ' . $post['oauth_token'])) : $header ;
        $header = (($this->twitch_client_id !== '') && ($this->twitch_client_id !== ' ')) ? array_merge($header, array('Client-ID: ' . $this->twitch_client_id)) : $header;                           // v6 Authorization: Bearer    <access token>"  https://api.twitch.tv/helix/
        
        if ((TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $post) === 1) || (array_key_exists('oauth_token', $post) === true))) {
            unset($post['oauth_token']);
        }
        
        $default = array(
            CURLOPT_URL => $url,
            CURLOPT_CONNECTTIMEOUT => TWITCHPRESS_DEFAULT_RETURN_TIMEOUT, 
            CURLOPT_TIMEOUT => TWITCHPRESS_DEFAULT_TIMEOUT,
            CURLOPT_HEADER => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => $header
        );
                
        // Do we have a certificate to use?  if OpenSSL is available, there will be a certificate
        if (TWITCHPRESS_CERT_PATH != '') {
            // Overwrite outr defaults to include the SSL cert and options
            array_merge($default, array(
                CURLOPT_SSL_VERIFYPEER => 1,
                CURLOPT_SSL_VERIFYHOST => 1,
                CURLOPT_CAINFO         => realpath(TWITCHPRESS_CERT_PATH) // This requires the real path of the certificate (Strict, may use CAPATH instead if it causes problems)
            ));
        }
        
        $handle = curl_init();
        
        if (function_exists('curl_setopt_array')) {
            curl_setopt_array($handle, ($options + $default));
        } else { // nope, set them one at a time
            // Options are set last so you can override anything you don't want to keep from defaults.
            foreach (($default + $options) as $key => $opt) {
                curl_setopt($handle, $key, $opt);
            }
        }

        ob_start();
        $result = curl_exec($handle);
        $httpdStatus = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle); 
        ob_end_clean();
        
        // Log anything that isn't a good response. 
        if( $httpdStatus !== 200 ) {
            $status_meaning = kraken_httpstatuses( $httpdStatus, 'wiki' );
            if( !is_string( $status_meaning ) ) { $status_meaning = __( 'Sorry, no more information could be retrieved for this status.', 'twitchpress' ); }
            $this->bugnet->log( __FUNCTION__, sprintf( __( 'TwitchPress HTTPDStatus: %s - %s', 'twitchpress' ), $httpdStatus, $status_meaning ), array(), true, false );
        }
        
        // Are we returning the HHTPD status?
        if ($returnStatus){
            return $httpdStatus;
        } else {
            return $result; 
        }
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
    * Store a cURL GET in Kraken5 requests transient: twitchpress_kraken_requests
    * 
    * @param mixed $result
    * @param mixed $httpdstatus
    * @param mixed $header
    * @param mixed $get
    * @param mixed $url
    * @param mixed $curl_url
    * @param mixed $error_string
    * @param mixed $error_no
    * @param mixed $arguments
    * 
    * @version 1.2
    */
    private function store_curl_get( $function, $result, $httpdstatus, $header, $get, $url, $curl_url, $error_string, $error_no, $arguments = array() ) {

        $excluded_functions = array( 'check_application_token' );
        
        if( in_array( $function, $excluded_functions ) ) { return; }
        
        $default_arguments = array(
            'function'     => $function,
            'result'       => $result,
            'httpdstatus'  => $httpdstatus,
            'header'       => $header,
            'get'          => $get,
            'url'          => $url,
            'curl_url'     => $curl_url,
            'error_string' => $error_string,
            'error_no'     => $error_no
        );
        $args = wp_parse_args( $arguments, $default_arguments );
                      
        // Get current stored get requests (and responses)
        $gets = get_transient( 'twitchpress_kraken_requests' );
        if( !is_array( $gets ) ) 
        { 
            $gets = array(); 
        }
        else
        { 
            // Delete 2 old entries to help maintain the size of this transient. 
            if( count( $gets['get']['requests'] ) > 50 ) {
                current( $gets['get']['requests'] );
                $key = key( $gets['get']['requests'] ); 
                unset( $gets['get']['requests'][ $key ] );                 
                next( $gets['get']['requests'] );
                $key = key( $gets['get']['requests'] ); 
                unset( $gets['get']['requests'][ $key ] );            
            }
        }

        // Set the lasttime value to help confirm when a request to Twitch.tv was last made.
        $gets['get']['lasttime'] = time();
        
        // Add the request data as a new entry. 
        $gets['get']['requests'][] = $args;
        
        // Get the new array key we just created. 
        end( $gets['get']['requests'] );
        $key = key( $gets['get']['requests'] );
        
        // Add some extra information to our get request entry for displaying to human-beings. 
        $gets['get']['requests'][ $key ]['time'] = time();
        
        // Add WP user ID if request is happening due to the actions of a logged in visitor. 
        if( function_exists( 'is_user_logged_in' ) && function_exists( 'get_current_user_id' ) ) {
            if( is_user_logged_in() ) {
                $gets['get']['requests'][ $key ]['wp_user_id'] = get_current_user_id();        
            }
        }
        
        delete_transient( 'twitchpress_kraken_requests' );
        set_transient( 'twitchpress_kraken_requests', $gets, 600 );
    }                          

     
    
    /**
     * Generate an App Access Token as part of OAuth Client Credentials Flow. 
     * 
     * @link https://dev.twitch.tv/docs/authentication#oauth-authorization-code-flow-user-access-tokens
     * 
     * This token is meant for authorizing the application and making API calls that are not channel-auth specific. 
     * 
     * @param $code - [string] String of auth code used to grant authorization
     * 
     * @return array $token - The generated token and the array of all scopes returned with the token, keyed.
     * 
     * @version 1.2
     */
    public function request_app_access_token( $requesting_function = null ){

        $url = 'https://api.twitch.tv/kraken/oauth2/token';
        $post = array(
            'client_id'     => $this->twitch_client_id,
            'client_secret' => $this->twitch_client_secret,
            'grant_type'    => 'client_credentials',
            'scope'         => twitchpress_prepare_scopes( $this->get_global_accepted_scopes() ),
        );
       
        $options = array();
          
        $result = json_decode($this->cURL_post($url, $post, $options, false), true);
    
        if ( is_array( $result ) && array_key_exists( 'access_token', $result ) )
        {
            $token['token'] = $result['access_token'];
            $token['scopes'] = $result['scope'];
            
            $appending = '';
            if( $requesting_function == null ) 
            { 
                $appending = $token['token']; 
            }
            else
            { 
                $appending = sprintf( __( 'Requesting function was %s() and the token is %s.', 'twitchpress' ), $requesting_function, $token['token'] ); 
            }
            
            $this->bugnet->log( __FUNCTION__, sprintf( __( 'Access token returned. %s', 'twitchpress' ), $appending ), array(), true, false );
            
            // Store the new token for the entire TwitchPress system to use.
            $this->update_main_client_token( $token['token'], $token['scopes'] );
            
            return $token;
        } 
        else 
        {
            $request_string = '';
            if( $requesting_function == null ) { $request_string = __( 'Requesting function is not known!', 'twitchpress' ); }
            else{ $request_string = __( 'Requesting function is ', 'twitchpress' ) . $requesting_function; }
            $this->bugnet->log( __FUNCTION__, sprintf( __( 'No access token returned: %s()', 'twitchpress' ), $request_string ), array(), true, false );
        
            return false;
        }
    }
    
    
      
    
    /**
     * Generate a visitor/user access token. This also applies to the administrator who
     * sets the main account because they are also a user.  
     * 
     * @param $code - [string] String of auth code used to grant authorization
     * 
     * @return array $token - The generated token and the array of all scopes returned with the token, keyed.
     * 
     * @version 1.23
     */
    public function request_user_access_token( $code, $requesting_function ){

        // Request a user access token from the giving service for the giving profile. 
        if( $allapi_service == 'twitch' )// Twitch.tv (Kraken 2018 or Helix 2019)
        {              
            $url = 'https://api.twitch.tv/kraken/oauth2/token';
            $post = array(
                'client_id' => $this->twitch_client_id,         
                'client_secret' => $this->twitch_client_secret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->twitch_client_url,
                'code' => $code,
                'state' => $this->twitch_client_token
            );
           
            $options = array();
              
            $result = json_decode($this->cURL_post($url, $post, $options, false), true);
     
            if ( is_array( $result ) && array_key_exists( 'access_token', $result ) )
            {
                $appending = '';
                if( $requesting_function == null ) { $appending = $token; }
                else{ $appending = sprintf( __( 'Requesting function was %s() and the token is %s.', 'twitchpress' ), $requesting_function, $result['access_token'] ); }
                $this->bugnet->log( __FUNCTION__, sprintf( __( 'Access token returned. %s', 'twitchpress' ), $appending ), array(), true, false );

                return $result;
            } 
            else 
            {
                $request_string = '';
                if( $requesting_function == null ) { $request_string = __( 'Requesting function is not known!', 'twitchpress' ); }
                else{ $request_string = __( 'Requesting function is ', 'twitchpress' ) . $requesting_function; }
                $this->bugnet->log( __FUNCTION__, sprintf( __( 'No access token returned: %s()', 'twitchpress' ), $request_string ), array(), true, false );
            
                return false;
            }
        }
        
        return null;
    }
    
    
       
                       
    /**
     * Checks a token for validity and access grants available.
     * 
     * @return array $result if token is still valid, else false.  
     * 
     * @version 5.2
     */    
    public function check_application_token(){
        $token = get_option( 'twitchpress_app_token' );
        $url = 'https://api.twitch.tv/kraken';
        $post = array( 
            'oauth_token' => $token, 
            'client_id'   => $this->twitch_client_id,          
        );

        $result = json_decode( $this->cURL_get( $url, $post, array(), false, __FUNCTION__ ), true );                   
    
        if ( isset( $result['token']['valid'] ) && $result['token']['valid'] )
        {       
            return $result;
        } 
        else 
        {
            $this->bugnet->log( __FUNCTION__, __( 'Invalid app token', 'twitchpress' ), array(), true, true );
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
     * @version 5.5
     */    
    public function check_user_token( $wp_user_id ){
        
        // Get the giving users token. 
        $user_token = twitchpress_get_user_token( $wp_user_id );
        if( !$user_token ){ return false;}
        
        $url = 'https://api.twitch.tv/kraken';
        $post = array(
            'oauth_token' => $user_token,
            'client_id'   => $this->twitch_client_id,
        );
        $options = array();

        $result = json_decode( $this->cURL_get( $url, $post, $options, false, __FUNCTION__ ), true );                   

        $token = array();
        
        if ( isset( $result['token'] ) && isset( $result['token']['valid'] ) && $result['token']['valid'] !== false )
        {      
            $token['token'] = $user_token;
            $token['scopes'] = $result['token']['authorization']['scopes'];
            $token['name'] = $result['token']['user_name'];
        } 
        else 
        {
            $this->bugnet->log( __FUNCTION__, __( 'Token has expired', 'twitchpress' ), array(), true, true );
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
    */
    public function establish_application_token( $function ) {     
        $result = $this->check_application_token();  

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
                
                #   Example for $user_access_token_array             
                #      
                #  'access_token' => string 'psv9jaiqgimari17zb1ekeg9emlw38' (length=30)
                #  'refresh_token' => string 'lmgdjnlik871s4qzxe94scu4x8ou0rxzacvgfni95bbob0crxv' (length=50)
                #  'scope' => 
                #      array (size=19)
                #         0 => string 'channel_check_subscription' (length=26)
                #         1 => string 'channel_commercial' (length=18)
                #     'expires_in' => int 15384         
                
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
        
        $url = 'https://api.twitch.tv/kraken/oauth2/token';
        $post = array(
            'client_id' => $this->twitch_client_id,         
            'client_secret' => $this->twitch_client_secret,
            'grant_type' => 'refresh_token',
            'refresh_token' => urlencode( $token_refresh ),
            'scope' => twitchpress_prepare_scopes( $this->get_user_scopes() )
        );
       
        $options = array();
        $result = json_decode( $this->cURL_post( $url, $post, $options, false ), true );
        
            # Success Example $result
            #
            # "access_token": "asdfasdf",
            # "refresh_token": "eyJfMzUtNDU0OC04MWYwLTQ5MDY5ODY4NGNlMSJ9%asdfasdf=",
            # "scope": "viewing_activity_read"
            
            # Failed Example Result 
            #
            # "error": "Bad Request",
            # "status": 400,
            # "message": "Invalid refresh token"
             
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
    * Generate an oAuth2 Twitch API URL for an administrator only. The procedure
    * for public visitors will use different methods for total clarity when it comes to
    * security. 
    * 
    * @author Ryan Bayne
    * @version 5.0
    * 
    * @param array $permitted_scopes
    * @param array $state_array
    */
    public function generate_authorization_url( $permitted_scopes, $local_state ) {
        global $bugnet;
            
        // Scope value will be a random code that can be matched to a transient on return.
        if( !isset( $local_state['random14'] ) ) { $local_state['random14'] = twitchpress_random14();}

        $bugnet->log( __FUNCTION__, sprintf( __( 'oAuth2 URL has been requested.', 'twitchpress' ), $local_state['random14'] ), array(), true, false );
        
        // Primary request handler - value is checked on return from Twitch.tv
        set_transient( 'twitchpress_oauth_' . $local_state['random14'], $local_state, 6000 );

        $scope = twitchpress_prepare_scopes( $permitted_scopes, true );

        // Build oauth2 URL.
        $url = 'https://api.twitch.tv/kraken/oauth2/authorize?' .
            'response_type=code' . '&' .
            'client_id=' . $this->twitch_client_id . '&' .
            'redirect_uri=' . $this->twitch_client_url . '&' .
            'scope=' . $scope . '&' .
            'state=' . $local_state['random14'];
            
        $bugnet->log( __FUNCTION__, sprintf( __( 'The oAuth2 URL is %s.', 'twitchpress' ), $url ), array(), true, false );
        
        return $url;       
    } 
    
     
    
                  
    /**
     * A function able to grab the authentication code from URL generated by Twitch's auth servers
     * 
     * @param $url - [string] The redirect URL from Twitch's authentication servers
     * 
     * @return $code - [string] The returned authentication code used in authenticated calls
     */ 
    public function retrieveRedirectCode( $url,$param = 'code' ) {
        $code = twitchpress_getURLParamValue( $url, $param );
        return $code;
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

        $oAuth2_URL = $this->generate_authorization_url( $this->get_global_accepted_scopes(), $state ); 
        wp_redirect( $oAuth2_URL );
        exit;                       
    }
    
    
          
    
    
    /**
    * Each scope is stored in an individual option. Use this method when
    * an array of them is required. 
    * 
    * Usually when a scope name exists in options, it is an accepted scope. We will
    * not assume it though. 
    * 
    * @version 2.0
    */
    public function get_global_accepted_scopes() {
        $global_accepted_scopes = array();
 
        foreach( $this->twitch_scopes as $scope ) {
            if( get_option( 'twitchpress_scope_' . $scope ) == 'yes' ) {
                $global_accepted_scopes[] = $scope;
            }
        }       
        
        return $global_accepted_scopes;
    }
    
    
     
    
    /**
    * Returns an array of the scopes that visitors are required to
    * accept when doing oAuth2. These scopes are for public features
    * and features involving interaction with a visitors channel. 
    * 
    * @returns array 
    * 
    * @version 1.0
    */
    public function get_user_scopes() {
        $visitor_scopes = array();
        
        foreach( $this->twitch_scopes as $scope ) {
            if( get_option( 'twitchpress_visitor_scope_' . $scope ) == 'yes' ) {
                $visitor_scopes[] = $scope;
            }
        }       

        return $visitor_scopes;        
    }

    
    
    
    public function get_objects_default_channel() {
        return $this->twitch_default_channel;     
    }
    
    
    
    
    public function get_objects_main_channel_id() {
        return $this->twitch_channel_id;
    }
    
    
    
    
    public function get_objects_main_client_id() {
        return $this->twitch_client_id;
    }
    
    
    
    
    public function get_objects_main_client_code() {
        return $this->twitch_client_code;
    }
    
    
    
    

    public function get_objects_client_token() {
        return $this->twitch_client_token;
    } 
    
    
    
 
    public function get_main_default_channel() {
        return get_option( 'twitchpress_main_channel_name' );     
    }    
    
    
     
    
    public function get_main_channel_name() {
        return get_option( 'twitchpress_main_channel_name' );     
    }
    
    
    
    
    
    public function get_main_channel_id() {
        return get_option( 'twitchpress_main_channel_id' );
    }
    
    
    
    
    
    
    public function get_main_client_id() {
        return get_option( 'twitchpress_main_client_id' );
    }
    
    
    
    
    
    public function get_main_client_code() {
        return get_option( 'twitchpress_main_code' );
    }

    
    
    
    
    public function get_main_client_token() {
        return get_option( 'twitchpress_main_token' );
    }  
    
    
    
    
    
    /**
    * Stores the main application token and main application scopes
    * as an option value.
    * 
    * @param mixed $token
    * @param mixed $scopes
    * 
    * @version 2.0
    */
    public function update_main_client_token( $token, $scopes ) {
        update_option( 'twitchpress_main_token', $token );
        update_option( 'twitchpress_main_token_scopes', $scopes );
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
        if( !in_array( $scope, $this->twitch_scopes ) ) {
            return $bugnet->log_error( 'twitchpressinvalidscope', sprintf( __( 'A Kraken5 call is using an invalid scope. See %s()', 'twitchpress' ), $function ), true );
        }    
        
        // Check applicable $side array scope.
        switch ( $side ) {
           case 'user':
                if( !in_array( $scope, $this->get_user_scopes() ) ) { return $bugnet->log_error( 'twitchpressscopenotpermittedbyuser', sprintf( __( 'TwitchPress requires visitor scope: %s for function %s()', 'twitchpress' ), $scope, $function ), true ); }
             break;           
           case 'channel':
                if( !in_array( $scope, $this->get_global_accepted_scopes() ) ) { return $bugnet->log_error( 'twitchpressscopenotpermittedbyadmin', sprintf( __( 'TwitchPress scope %s was not permitted by administration and is required by %s().', 'twitchpress' ), $scope, $function ), true ); }
             break;         
           case 'both':
                // This measure is temporary, to avoid faults, until we confirm which $side some calls apply to. 
                if( !in_array( $scope, $this->get_global_accepted_scopes() ) &&
                        !in_array( $scope, $this->get_user_scopes() ) ) { 
                            return $bugnet->log_error( 'twitchpressscopenotpermitted', sprintf( __( 'A Kraken5 call requires a scope that has not been permitted.', 'twitchpress' ), $function ), true ); 
                }
             break;
        }
        
        // Arriving here means the scope is valid and was found. 
        return true;
    }   
}

endif;