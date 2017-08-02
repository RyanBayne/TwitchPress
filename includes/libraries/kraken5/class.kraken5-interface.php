<?php
/**
 * The main Twitch API interface updated for Kraken version after it's original
 * download from GitHub.

 * Do not use this class unless you accept the Twitch Developer Services Agreement
 * @link https://www.twitch.tv/p/developer-agreement
 * 
 * @class    TwitchPress_Admin
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Core
 * @version  5.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Make sure we meet our dependency requirements
if (!extension_loaded('curl')) trigger_error('cURL is not currently installed on your server, please install cURL if your wish to use Twitch services in TwitchPress.');
if (!extension_loaded('json')) trigger_error('PECL JSON or pear JSON is not installed, please install either PECL JSON or compile pear JSON if you wish to use Twitch services in TwitchPress.');

if( !class_exists( 'TWITCHPRESS_Kraken5_Interface' ) ) :

class TWITCHPRESS_Kraken5_Interface {
    
    protected $twitch_wperror                = null;
    protected $twitch_default_channel        = null;// Services own channel name, not ID.
    protected $twitch_channel_id             = null;
    protected $twitch_client_id              = null;
    protected $twitch_client_secret          = null;
    protected $twitch_client_url             = null;
    protected $twitch_client_code            = null;
    protected $twitch_client_token           = null;
    protected $twitch_global_accepted_scopes = null;
    
    // Debugging variables.
    public $twitch_call_name = 'Unknown';
    public $twitch_call_id   = null;
    
    public $twitch_scopes = array( 
            'channel_check_subscription',
            'channel_commercial',
            'channel_editor',
            'channel_feed_edit',
            'channel_feed_read',
            'channel_read',
            'channel_stream',
            'channel_subscriptions',
            'chat_login',
            'collections_edit',
            'communities_edit',
            'communities_moderate',
            'user_blocks_edit',
            'user_blocks_read',
            'user_follows_edit',
            'user_read',
            'user_subscriptions',
            'viewing_activity_read',
            'openid'
    );
  
    /**
    * Array of streams for testing and generating sample content.
    * 
    * @var mixed
    * 
    * @version 1.0
    */
    public $twitchchannels_endorsed = array(
        'zypherevolved'        => array( 'display_name' => 'ZypheREvolved' ),
        'thatgirlslays'        => array( 'display_name' => 'ThatGirlSlays' ),
        'nookyyy'              => array( 'display_name' => 'nookyyy' ),
        'starcitizengiveaways' => array( 'display_name' => 'StarCitizenGiveaways' ),        
        'testgaming'           => array( 'display_name' => 'TESTGaming' ),
        'capn_flint'           => array( 'display_name' => 'capn_flint' ),
        'wtfosaurus'           => array( 'display_name' => 'WTFOSAURUS' ),
        'starcitizen'          => array( 'display_name' => 'StarCitizen' ),
        'cigcommunity'         => array( 'display_name' => 'CIGCommunity' ),
        'dtox_tv'              => array( 'display_name' => 'DTOX_TV' ),
        'sgt_gamble'           => array( 'display_name' => 'SGT_Gamble' ),
        'baiorofred'           => array( 'display_name' => 'BaiorOfRed' ),
        'bristolboy88'         => array( 'display_name' => 'BristolBoy88' ),
        'mzhartz'              => array( 'display_name' => 'MzHartz' ),
        'boredgameruk'         => array( 'display_name' => 'BoredGamerUK' ),
        'thenoobifier1337'     => array( 'display_name' => 'TheNOOBIFIER1337' ),
    );
        
    /**
    * Requirements will be checked here and constants set.
    * 
    * @author Ryan R. Bayne            
    * @version 1.0
    */
    public function __construct(){
        $this->set_application_credentials();
    } 

    /**
    * This method makes it possible to store different Developer Applications
    * in the WordPress options table. 
    * 
    * @param mixed $app
    */
    public function set_application_credentials( $app = 'main' ) {
        $this->twitch_default_channel = get_option( 'twitchpress_' . $app . '_channel_name' );   
        $this->twitch_channel_id      = get_option( 'twitchpress_' . $app . '_channel_id' );   
        $this->twitch_client_url      = get_option( 'twitchpress_' . $app . '_redirect_uri' );   
        $this->twitch_client_id       = get_option( 'twitchpress_' . $app . '_client_id' ); 
        $this->twitch_client_secret   = get_option( 'twitchpress_' . $app . '_client_secret' );                           
        $this->twitch_client_code     = get_option( 'twitchpress_' . $app . '_code' );                           
        $this->twitch_client_token    = get_option( 'twitchpress_' . $app . '_token' );                           
    }
    
    /**
    * Checks if application credentials are set.
    * 
    * @returns boolean true if set else an array of all the missing credentials.
    * 
    * @version 1.0
    */
    public function is_app_set() {
        $missing = array();
        
        if( !$this->twitch_channel_id ) {
            $missing[] = __( 'Channel ID', 'twitchpress' );        
        }    
        
        if( !$this->twitch_client_url ) {
            $missing[] = __( 'Client URL', 'twitchpress' );        
        }    
        
        if( !$this->twitch_client_id ) {
            $missing[] = __( 'Client ID', 'twitchpress' );        
        }    
        
        if( !$this->twitch_client_secret ) {
            $missing[] = __( 'Client Secret', 'twitchpress' );        
        }    
        
        if( !$this->twitch_client_code ) {
            $missing[] = __( 'Client Code', 'twitchpress' );        
        }    
        
        if( !$this->twitch_client_token ) {
            $missing[] = __( 'Client Token', 'twitchpress' );        
        }       
        
        if( $missing ) {
            return $missing;
        }
        
        return true;
    }
    
    /**
     * WordPress integrating constructor. 
     * 
     * Put add_action() specific to this class in here. 
     * Get WP option values required by class in here.
     * 
     * @package TwitchPress
     */
    public static function init() {              
        add_action( 'init', array( __CLASS__, 'administrator_main_account_listener' ) );
    }
    
    /**
    * Listen for administration only Twitch API related events. 
    * 
    * Return when a negative condition is found.
    * 
    * Add methods between returns, where arguments satisfy minimum security. 
    * 
    * @version 1.4
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
       
        if( !is_user_logged_in() ) {        
            return;
        }        
        
        if( !user_can( TWITCHPRESS_CURRENTUSERID, 'activate_plugins' ) ) {    
            return;    
        }
                   
        // Create a notice for an error.
        if( isset( $_GET['error'] ) ) {       
            if( isset( $_GET['error_description'] ) ) {
                TwitchPress_Admin_Notices::add_custom_notice( 'krakenerror', sprintf( __( 'Twitch oAuth2 Error: %s.'), esc_html( $_GET['error_description'] ) ) );
                return;
            } else {
                TwitchPress_Admin_Notices::add_custom_notice( 'krakenerrorunknown', __( 'Error: An error has been detected. There is no error description available but it is probably related to a Twitch oAuth2 request.') );
            }
            
            // Never continue after here if URL contains the error value.
            return;
        } 
        
        // Do not trust the $_GET! 
        if( !isset( $_GET['code'] ) || !twitchpress_validate_code( $_GET['code'] ) ) {        
            return;
        } else {
            $twitch_code = $_GET['code'];
            update_option( 'twitchpress_main_code', $twitch_code );
        }          
        
        if( !isset( $_GET['scope'] ) ) {      
            return;
        }          
        
        if( !isset( $_GET['state'] ) ) {       
            return;
        }          
        
        // This transient confirms that the user made a recent oAuth2 request for Twitch API.
        if( !$oauth_transient = get_transient( TWITCHPRESS_CURRENTUSERID . '_twitchpress_oauth_mainrequest_states' ) ) {     
            return;
        }          

        // Ensure we have the admin view or page the user needs to be sent to. 
        if( !isset( $oauth_transient['redirectto'] ) ) {        
            return;    
        } 
                            
        $kraken = new TWITCHPRESS_Kraken5_Calls();
        $new_token = $kraken->generateToken( $twitch_code );
       
        if( !$new_token ) {      
            return;
        }

        if( !$new_token['token'] ) {     
            return;
        }
             
        update_option( 'twitchpress_main_token', $new_token['token'] );
              
        TwitchPress_Admin_Notices::add_custom_notice( 'mainkrakenapplicationsetup', __( 'A token has been granted by the Twitch API. Your site is now authorized to make calls to the Twitch API and will attempt to make a call now.')  );
               
        // Confirm the giving main (default) channel is valid. 
        $user_objects = $kraken->get_users( $kraken->twitch_default_channel );
        
        if( !isset( $user_objects['users'][0]['_id'] ) ) {
            TwitchPress_Admin_Notices::add_custom_notice( 'wizardchanneldoesnotexist', __( '<strong>Channel Not Found:</strong> TwitchPress wants to avoid errors in future by ensuring what you typed is correct. So far it could not confirm your entered channel is correct. Please check the spelling of your channel and the status of Twitch. If your entered channel name is correct and Twitch is online, please report this message.', 'twitchpress' ) );      
            return;                         
        } 

        update_option( 'twitchpress_main_channel_id', $user_objects['users'][0]['_id'], true );        
  
        // For now we will assume that the sites official Twitch account is also the users
        // own personal account. This is temporary, we really need a procedure that offers
        // the chance to re-authorize a second Twitch account and store that as the users personal.
        twitchpress_update_user_oauth( 
            get_current_user_id(), 
            $_GET['code'], 
            $new_token['token'], 
            $user_objects['users'][0]['_id'] 
        );
        
        // Forward user to the custom destinaton i.e. where they were before oAuth2. 
        wp_redirect( get_site_url() . $oauth_transient['redirectto'] );
        exit;
    }      
          
    /**
    * Returns an array of scopes with user-friendly form input labels and descriptions.
    * 
    * @author Ryan R. Bayne
    * @version 1.2
    */
    public function scopes( $scopes_only = false) {
        // We can return scopes without additional information.
        if( $scopes_only ) { return $this->twitch_scopes; }
              
        $scope = array(
            'channel_check_subscription' => array(),
            'channel_commercial'         => array(),
            'channel_editor'             => array(),
            'channel_feed_edit'          => array(),
            'channel_feed_read'          => array(),
            'channel_read'               => array(),
            'channel_stream'             => array(),
            'channel_subscriptions'      => array(),
            'chat_login'                 => array(),
            'collections_edit'           => array(),
            'communities_edit'           => array(),
            'communities_moderate'       => array(),
            'user_blocks_edit'           => array(),
            'user_blocks_read'           => array(),
            'user_follows_edit'          => array(),
            'user_read'                  => array(),
            'user_subscriptions'         => array(),
            'viewing_activity_read'      => array(),
            'openid'                     => array()        
        );
        
        // Add form input labels for use in form input labels. 
        $scope['user_read']['label']                  = __( 'General Account Details', 'twitchpress' );
        $scope['user_blocks_edit']['label']           = __( 'Ignore Users', 'twitchpress' );
        $scope['user_blocks_read']['label']           = __( 'Get Ignored Users', 'twitchpress' );
        $scope['user_follows_edit']['label']          = __( 'Follow Users', 'twitchpress' );
        $scope['channel_read']['label']               = __( 'Get Channel Data', 'twitchpress' );
        $scope['channel_editor']['label']             = __( 'Edit Channel', 'twitchpress' );
        $scope['channel_commercial']['label']         = __( 'Trigger Commercials', 'twitchpress' );
        $scope['channel_stream']['label']             = __( 'Reset Stream Key', 'twitchpress' );
        $scope['channel_subscriptions']['label']      = __( 'Get Your Subscribers', 'twitchpress' );
        $scope['user_subscriptions']['label']         = __( 'Get Your Subscriptions', 'twitchpress' );
        $scope['channel_check_subscription']['label'] = __( 'Check Viewers Subscription', 'twitchpress' );
        $scope['chat_login']['label']                 = __( 'Chat Permission', 'twitchpress' );
        $scope['channel_feed_read']['label']          = __( 'Get Channel Feed', 'twitchpress' );
        $scope['channel_feed_edit']['label']          = __( 'Post To Channels Feed', 'twitchpress' );
        $scope['communities_edit']['label']           = __( 'Manage Users Communities', 'twitchpress' );
        $scope['communities_moderate']['label']       = __( 'Manage Community Moderators', 'twitchpress' );
        $scope['collections_edit']['label']           = __( 'Manage Video Collections', 'twitchpress' );
        $scope['viewing_activity_read']['label']      = __( 'Viewer Heartbeat Service', 'twitchpress' );
        $scope['openid']['label']                     = __( 'OpenID Connect Service', 'twitchpress' );
                
        // Add official api descriptions - copied from official API documention.
        $scope['user_read']['apidesc']                  = __( 'Read access to non-public user information, such as email address.', 'twitchpress' );
        $scope['user_blocks_edit']['apidesc']           = __( 'Ability to ignore or unignore on behalf of a user.', 'twitchpress' );
        $scope['user_blocks_read']['apidesc']           = __( 'Read access to a user’s list of ignored users.', 'twitchpress' );
        $scope['user_follows_edit']['apidesc']          = __( 'Access to manage a user’s followed channels.', 'twitchpress' );
        $scope['channel_read']['apidesc']               = __( 'Read access to non-public channel information, including email address and stream key.', 'twitchpress' );
        $scope['channel_editor']['apidesc']             = __( 'Write access to channel metadata (game, status, etc).', 'twitchpress' );
        $scope['channel_commercial']['apidesc']         = __( 'Access to trigger commercials on channel.', 'twitchpress' );
        $scope['channel_stream']['apidesc']             = __( 'Ability to reset a channel’s stream key.', 'twitchpress' );
        $scope['channel_subscriptions']['apidesc']      = __( 'Read access to all subscribers to your channel.', 'twitchpress' );
        $scope['user_subscriptions']['apidesc']         = __( 'Read access to subscriptions of a user.', 'twitchpress' );
        $scope['channel_check_subscription']['apidesc'] = __( 'Read access to check if a user is subscribed to your channel.', 'twitchpress' );
        $scope['chat_login']['apidesc']                 = __( 'Ability to log into chat and send messages', 'twitchpress' );
        $scope['channel_feed_read']['apidesc']          = __( 'Ability to view to a channel feed.', 'twitchpress' );
        $scope['channel_feed_edit']['apidesc']          = __( 'Ability to add posts and reactions to a channel feed.', 'twitchpress' );
        $scope['communities_edit']['apidesc']           = __( 'Manage a user’s communities.', 'twitchpress' );
        $scope['communities_moderate']['apidesc']       = __( 'Manage community moderators.', 'twitchpress' );
        $scope['collections_edit']['apidesc']           = __( 'Manage a user’s collections (of videos).', 'twitchpress' );
        $scope['viewing_activity_read']['apidesc']      = __( 'Turn on Viewer Heartbeat Service ability to record user data.', 'twitchpress' );
        $scope['openid']['apidesc']                     = __( 'Use OpenID Connect authentication.', 'twitchpress' );
                
        // Add user-friendly descriptions.
        $scope['user_read']['userdesc']                  = __( 'Get email address.', 'twitchpress' );
        $scope['user_blocks_edit']['userdesc']           = __( 'Ability to ignore or unignore other users.', 'twitchpress' );
        $scope['user_blocks_read']['userdesc']           = __( 'Access to your list of ignored users.', 'twitchpress' );
        $scope['user_follows_edit']['userdesc']          = __( 'Permission to manage your followed channels.', 'twitchpress' );
        $scope['channel_read']['userdesc']               = __( 'Read your non-public channel information., including email address and stream key.', 'twitchpress' );
        $scope['channel_editor']['userdesc']             = __( 'Ability to update meta data like game, status, etc.', 'twitchpress' );
        $scope['channel_commercial']['userdesc']         = __( 'Access to trigger commercials on channel.', 'twitchpress' );
        $scope['channel_stream']['userdesc']             = __( 'Ability to reset your channel’s stream key.', 'twitchpress' );
        $scope['channel_subscriptions']['userdesc']      = __( 'Read access to all subscribers to your channel.', 'twitchpress' );
        $scope['user_subscriptions']['userdesc']         = __( 'Permission to get your subscriptions.', 'twitchpress' );
        $scope['channel_check_subscription']['userdesc'] = __( 'Read access to check if a user is subscribed to your channel.', 'twitchpress' );
        $scope['chat_login']['userdesc']                 = __( 'Ability to log into your chat and send messages', 'twitchpress' );
        $scope['channel_feed_read']['userdesc']          = __( 'Ability to import your channel feed.', 'twitchpress' );
        $scope['channel_feed_edit']['userdesc']          = __( 'Ability to add posts and reactions to your channel feed.', 'twitchpress' );
        $scope['communities_edit']['label']              = __( 'Manage your user’s communities.', 'twitchpress' );
        $scope['communities_moderate']['label']          = __( 'Manage your community moderators.', 'twitchpress' );
        $scope['collections_edit']['label']              = __( 'Manage your collections (of videos).', 'twitchpress' );
        $scope['viewing_activity_read']['label']         = __( 'Turn on Viewer Heartbeat Service to record your user data.', 'twitchpress' );
        $scope['openid']['label']                        = __( 'Allow your OpenID Connect for authentication on this site.', 'twitchpress' );
        
        return $scope;  
    }   
       
    /**
     * This allows users to bind into their error systems, here for compatability, defaults to echos for testing
     * 
     * @param $errNo - [int] Error number of the error tossed
     * @param $errStr - [str] Error string returned for the error tossed
     * @param $return - [mixed] The return provided to the query
     * 
     * @return User defined return
     */ 
    protected function generateError( $errNo, $errStr, $return = null ) {
        twitchpress_error( __( 'The Twitch API (version 5) has thrown an error: ' . $errNo . ' - ' . $errStr, 'twitchpress' ) );
    }
    
    /**
     * This allows developers to bind into their output systems, 
     * here for compatability, defaults to echo's for testing
     * 
     * @param $function - [string] String name of the function or alias being called
     * @param $errStr - [string] debug output to be passed
     * @param $outputLevel - [int] The level of the output passed, used in output suppression, Default level of output is 5 (Lowest)
     * 
     * @return User defined return
     * 
     * @version 1.3
     */ 
    protected function generateOutput( $function, $errStr, $outputLevel = 4 ){   
        // Add strings or integers to permit specific output to be displayed.            
        $accepted_outputtypes = array( 1, 2 );

        $current = get_option( 'twitchpress_debug_trace_kraken' ); 
        if( !$current ){
            return false;
        }
               
        if ( in_array( $outputLevel, $accepted_outputtypes ) ) {                      
            //$this->UI = new TWITCHPRESS_UI();
            //$this->UI->success_notice( $function, $errStr );
        }
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
     * @version 1.2
     */
    protected function cURL_get($url, array $get = array(), array $options = array(), $returnStatus = false){
        $functionName = 'GET';

        $this->generateOutput($functionName, 'Starting GET query', 1);
        
        // Specify the header
        $header = array('Accept: application/vnd.twitchtv.v' . TWITCHPRESS_API_VERSION . '+json'); // Always included
        
        $header = (( TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $get) === 1) 
                        || (array_key_exists('oauth_token', $get) === true))) 
                                ? array_merge($header, array('Authorization: OAuth ' . $get['oauth_token'])) : $header ;
                                
        $header = (( $this->twitch_client_id !== '') && ($this->twitch_client_id !== ' ')) ? array_merge($header, array('Client-ID: ' . $this->twitch_client_id)) : $header;

        if (( TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $get) === 1) || (array_key_exists('oauth_token', $get) === true))) {
            unset($get['oauth_token']);
        }
        
        // Send the header info to the output
        foreach ($header as $row){
            $this->generateOutput($functionName, 'Header row => ' . $row, 3);
        }
        
        $cURL_URL = rtrim($url . '?' . http_build_query($get), '?');
        
        $this->generateOutput( $functionName, 'API Version set to: ' . TWITCHPRESS_API_VERSION, 3 );
        
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
            $this->generateOutput($functionName, 'Using supplied certificate for true HTTPS', 3);
            
            // Overwrite outr defaults to include the SSL cert and options
            array_merge($default, array(
                CURLOPT_SSL_VERIFYPEER => 1,
                CURLOPT_SSL_VERIFYHOST => 1,
                CURLOPT_CAINFO         => realpath( TWITCHPRESS_CERT_PATH ) // This requires the real path of the certificate (Strict, may use CAPATH instead if it causes problems)
            ));
        }
        
        if (empty($options)){
            $this->generateOutput($functionName, 'No additional options set', 3);
        }
        
        $handle = curl_init();
        
        if (function_exists('curl_setopt_array')) {
            $this->generateOutput($functionName, 'Options set as an array', 3);
            curl_setopt_array($handle, ($options + $default));
        } else { 
            // nope, set them one at a time
            // Options are set last so you can override anything you don't want to keep from defaults
            foreach (($default + $options) as $key => $opt) {
                $this->generateOutput($functionName, 'Options set as individual values', 3);
                curl_setopt($handle, $key, $opt);
            }
        }
        
        $this->generateOutput( $functionName, 'Command GET => URL: ' . $cURL_URL, 2 );
        
        foreach ( $get as $param => $val ) {
            if ( is_array( $val ) ) {
                foreach ($val as $key => $value) {
                    $this->generateOutput( $functionName, 'GET option: [' . $param . '] ' . $key . '=>' . $value, 2 );
                }
            } else {
                $this->generateOutput( $functionName, 'GET option: ' . $param . '=>' . $val, 2 );
            }
        }
        
        $result = curl_exec( $handle );
        $httpdStatus = curl_getinfo( $handle, CURLINFO_HTTP_CODE );
        
        // Check our HTTPD status that was returned for error returns
        if (($httpdStatus == 404) || ($httpdStatus == 0) || ($httpdStatus == 503)) {
            $errStr = curl_error($handle);
            $errNo = curl_errno($handle);
            $this->generateError($errNo, $errStr);
        }
        
        curl_close($handle);
        
        $this->generateOutput($functionName, 'Status Returned: ' . $httpdStatus, 3);
        $this->generateOutput($functionName, 'Raw Return: ' . $result, 4);
       
        // Clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($url, $get, $options, $debug, $header, $cURL_URL, $default, $key, $opt, $k, $v, $handle, $row);
        
        // Are we returning the HHTPD status?
        if ($returnStatus) {
            $this->generateOutput($functionName, 'Returning HTTPD status', 3);
            unset($result, $functionName);
            return $httpdStatus;
        } else {
            $this->generateOutput($functionName, 'Returning result', 3);
            unset($httpdStatus, $functionName);
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
     */ 
    protected function cURL_post($url, array $post = array(), array $options = array(), $returnStatus = false){
        $functionName = 'POST';
        $postfields = '';
        
        $this->generateOutput($functionName, 'Starting POST query', 1);
        
        // Specify the header
        $header = array('Accept: application/vnd.twitchtv.v' . TWITCHPRESS_API_VERSION . '+json'); // Always included
        $header = (( TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $post) === 1) || (array_key_exists('oauth_token', $post) === true))) ? array_merge($header, array('Authorization: OAuth ' . $post['oauth_token'])) : $header;
        $header = (( $this->twitch_client_id !== '') && ($this->twitch_client_id !== ' ')) ? array_merge($header, array('Client-ID: ' . $this->twitch_client_id)) : $header;
        
        if (( TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $post) === 1) || (array_key_exists('oauth_token', $post) === true))) {
            unset($post['oauth_token']);
        }
                
        // Send the header info to the output
        foreach ($header as $row) {
            $this->generateOutput( $functionName, 'Header row => ' . $row, 3 );
        }
        
        $this->generateOutput( $functionName, 'API Version set to: ' . TWITCHPRESS_API_VERSION, 3 );

        // Custom build the post fields
        foreach ($post as $field => $value) {
            $postfields .= $field . '=' . urlencode( $value ) . '&';
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
            $this->generateOutput($functionName, 'Using supplied certificate for true HTTPS', 3);
            
            // Overwrite outr defaults to include the SSL cert and options
            array_merge($default, array(
                CURLOPT_SSL_VERIFYPEER => 1,
                CURLOPT_SSL_VERIFYHOST => 1,
                CURLOPT_CAINFO         => realpath( TWITCHPRESS_CERT_PATH ) // This requires the real path of the certificate (Strict, may use CAPATH instead if it causes problems)
            ));
        }
        
        if (empty($options)){
            $this->generateOutput($functionName, 'No additional options set', 3);
        }
        
        $handle = curl_init();
        
        if (function_exists('curl_setopt_array')) {
            $this->generateOutput($functionName, 'Options set as an array', 3);
            curl_setopt_array($handle, ($options + $default));
        } else { // nope, set them one at a time
            // Options are set last so you can override anything you don't want to keep from defaults.
            foreach (($default + $options) as $key => $opt) {
                $this->generateOutput($functionName, 'Options set as individual values', 3);
                curl_setopt($handle, $key, $opt);
            }
        }
        
        $this->generateOutput($functionName, 'command POST => URL: ' . $url, 2);
        
        foreach ($post as $param => $val) {
            if (is_array($val)) {
                foreach ($val as $key => $value) {
                    $this->generateOutput($functionName, 'POST option: [' . $param . '] ' . $key . '=>' . $value, 2);
                }
            } else {
                $this->generateOutput($functionName, 'POST option: ' . $param . '=>' . $val, 2);
            }
        }
      
        $result = curl_exec( $handle );
        
        $httpdStatus = curl_getinfo( $handle, CURLINFO_HTTP_CODE );
        
        // Check our HTTPD status that was returned for error returns
        if (($httpdStatus == 404) || ($httpdStatus == 0) || ($httpdStatus == 503)) {
            $errStr = curl_error($handle);
            $errNo = curl_errno($handle);
            $this->generateError($errNo, $errStr);
        }
        
        curl_close($handle);
        
        $this->generateOutput($functionName, 'Status Returned: ' . $httpdStatus, 3);
        $this->generateOutput($functionName, 'Raw Return: ' . $result, 4);
        
        // Clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($url, $post, $options, $debug, $postfields, $header, $field, $value, $postfields, $default, $errStr, $errNo, $handle, $row);
        
        // Are we returning the HHTPD status?
        if ($returnStatus) {
            $this->generateOutput($functionName, 'Returning HTTPD status', 3);
            unset($result, $functionName);
            return $httpdStatus;
        } else {
            $this->generateOutput($functionName, 'Returning result', 3);
            unset($httpdStatus, $functionName);
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
     */ 
    protected function cURL_put($url, array $put = array(), array $options = array(), $returnStatus = false) {
        $functionName = 'PUT';
        $postfields = '';
        
        $this->generateOutput($functionName, 'Starting PUT query', 1);
        
        // Specify the header
        $header = array('Accept: application/vnd.twitchtv.v' . TWITCHPRESS_API_VERSION . '+json'); // Always included
        $header = ((TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $put) === 1) || (array_key_exists('oauth_token', $put) === true))) ? array_merge($header, array('Authorization: OAuth ' . $put['oauth_token'])) : $header ;
        $header = (($this->twitch_client_id !== '') && ($this->twitch_client_id !== ' ')) ? array_merge($header, array('Client-ID: ' . $this->twitch_client_id)) : $header;
        
        if ((TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $put) === 1) || (array_key_exists('oauth_token', $put) === true))) {
            unset($put['oauth_token']);
        }
                
        // Send the header info to the output
        foreach ($header as $row) {
            $this->generateOutput($functionName, 'Header row => ' . $row, 3);
        }
        
        $this->generateOutput($functionName, 'API Version set to: ' . TWITCHPRESS_API_VERSION, 3);
        
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
            $this->generateOutput($functionName, 'Using supplied certificate for true HTTPS', 3);
            
            // Overwrite outr defaults to include the SSL cert and options
            array_merge($default, array(
                CURLOPT_SSL_VERIFYPEER => 1,
                CURLOPT_SSL_VERIFYHOST => 1,
                CURLOPT_CAINFO         => realpath( TWITCHPRESS_CERT_PATH ) // This requires the real path of the certificate (Strict, may use CAPATH instead if it causes problems)
            ));
        }
        
        if ( empty( $options ) ) {
            $this->generateOutput($functionName, 'No additional options set', 3);
        }
        
        $handle = curl_init();
        
        if ( function_exists('curl_setopt_array') ) {
            $this->generateOutput($functionName, 'Options set as an array', 3);
            curl_setopt_array($handle, ($options + $default));
        } else { // nope, set them one at a time
            // Options are set last so you can override anything you don't want to keep from defaults.
            foreach (($default + $options) as $key => $opt) {
                $this->generateOutput($functionName, 'Options set as individual values', 3);
                curl_setopt($handle, $key, $opt);
            }
        }
        
        $this->generateOutput($functionName, 'command PUT => URL: ' . $url, 2);
        
        foreach ($put as $param => $val){
            if (is_array($val)){
                foreach ($val as $key => $value){
                    $this->generateOutput($functionName, 'PUT option: [' . $param . '] ' . $key . '=>' . $value, 2);
                }
            } else {
                $this->generateOutput($functionName, 'PUT option: ' . $param . '=>' . $val, 2);
            }
        }
        
        $result = curl_exec($handle);
        $httpdStatus = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        
        // Check our HTTPD status that was returned for error returns
        if (($httpdStatus == 404) || ($httpdStatus == 0) || ($httpdStatus == 503)) {
            $errStr = curl_error($handle);
            $errNo = curl_errno($handle);
            $this->generateError($errNo, $errStr);
        }

        curl_close($handle);
        
        $this->generateOutput($functionName, 'Status Returned: ' . $httpdStatus, 3);
        $this->generateOutput($functionName, 'Raw Return: ' . $result, 4);
        
        // Clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($url, $put, $options, $debug, $postfields, $header, $field, $value, $postfields, $default, $errStr, $errNo, $handle, $row);
        
        // Are we returning the HHTPD status?
        if ($returnStatus) {
            $this->generateOutput($functionName, 'Returning HTTPD status', 3);
            unset($result, $functionName);
            return $httpdStatus;
        } else {
            $this->generateOutput($functionName, 'Returning result', 3);
            unset($httpdStatus, $functionName);
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
     */ 
    protected function cURL_delete($url, array $post = array(), array $options = array(), $returnStatus = true) {
        $functionName = 'DELETE';
        
        $this->generateOutput($functionName, 'Starting DELETE query', 1);
        
        // Specify the header
        $header = array('Accept: application/vnd.twitchtv.v' . TWITCHPRESS_API_VERSION . '+json'); // Always included
        $header = ((TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $post) === 1) || (array_key_exists('oauth_token', $post) === true))) ? array_merge($header, array('Authorization: OAuth ' . $post['oauth_token'])) : $header ;
        $header = (($this->twitch_client_id !== '') && ($this->twitch_client_id !== ' ')) ? array_merge($header, array('Client-ID: ' . $this->twitch_client_id)) : $header;
        
        if ((TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $post) === 1) || (array_key_exists('oauth_token', $post) === true))) {
            unset($post['oauth_token']);
        }
                
        // Send the header info to the output
        foreach ($header as $row) {
            $this->generateOutput($functionName, 'Header row => ' . $row, 3);
        }
        
        $this->generateOutput($functionName, 'API Version set to: ' . TWITCHPRESS_API_VERSION, 3);
        
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
            $this->generateOutput($functionName, 'Using supplied certificate for true HTTPS', 3);
            
            // Overwrite outr defaults to include the SSL cert and options
            array_merge($default, array(
                CURLOPT_SSL_VERIFYPEER => 1,
                CURLOPT_SSL_VERIFYHOST => 1,
                CURLOPT_CAINFO         => realpath(TWITCHPRESS_CERT_PATH) // This requires the real path of the certificate (Strict, may use CAPATH instead if it causes problems)
            ));
        }
        
        $handle = curl_init();
        
        if (function_exists('curl_setopt_array')) {
            $this->generateOutput($functionName, 'Options set as an array', 3);
            curl_setopt_array($handle, ($options + $default));
        } else { // nope, set them one at a time
            // Options are set last so you can override anything you don't want to keep from defaults.
            foreach (($default + $options) as $key => $opt) {
                $this->generateOutput($functionName, 'Options set as individual values', 3);
                curl_setopt($handle, $key, $opt);
            }
        }
        
        $this->generateOutput($functionName, 'command DELETE => URL: ' . $url, 2);
        
        foreach ($post as $param => $val){
            if (is_array($val)){
                foreach ($val as $key => $value){
                    $this->generateOutput($functionName, 'DELETE option: [' . $param . '] ' . $key . '=>' . $value, 2);
                }
            } else {
                $this->generateOutput($functionName, 'DELETE option: ' . $param . '=>' . $val, 2);
            }
        }        
        
        ob_start();
        $result = curl_exec($handle);
        $httpdStatus = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle); 
        ob_end_clean();
        
        $this->generateOutput($functionName, 'Status returned: ' . $httpdStatus, 3);   

        // Clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);        
        unset($url, $post, $options, $header, $handle, $default, $key, $opt, $row);
        
        // Are we returning the HHTPD status?
        if ($returnStatus){
            $this->generateOutput($functionName, 'Returning HTTPD status', 3);
            unset($result, $functionName);
            return $httpdStatus;
        } else {
            $this->generateOutput($functionName, 'Returning result', 3);
            unset($httpdStatus, $functionName);
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
     */ 
    protected function get_iterated($functionName, $url, $options, $limit, $offset, $arrayKey = null, $authKey = null, $hls = null, $direction = null, $channels = null, $embedable = null, $client_id = null, $broadcasts = null, $period = null, $game = null, $returnTotal = false, $sortBy = null) {
        $functionName = 'ITERATION-' . $functionName;
        
        $this->generateOutput($functionName, 'starting Iteration sequence', 1);
        $this->generateOutput($functionName, 'Calculating parameters', 3); 
        $this->generateOutput($functionName, 'Limit recieved as: ' . $limit, 2);
        
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
        
        $this->generateOutput($functionName, 'Limit set to: ' . $limit, 2);
        $this->generateOutput($functionName, 'Offset recieved as: ' . $offset, 2);
        
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
        
        $this->generateOutput($functionName, 'Offset set to: ' . $offset, 2);
        
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
            $this->generateOutput($functionName, 'Starting Limit set to: ' . $startingLimit, 2);
        } else {
            $startingLimit = $toDo;
            $this->generateOutput($functionName, 'Starting Limit set to: ' . $startingLimit, 2);
        }
        
        // Build our GET array for the first iteration, these values will always be supplied
        $get = array('limit' => $startingLimit,
            'offset' => $offset);
            
        // Now check every optional param to see if it exists and att it to the array
        if ($authKey != null) {
            $get['oauth_token'] = $authKey;
            $this->generateOutput($functionName, 'Auth Key added to GET array', 2);
        }
        
        if ($hls != null) {
            $get['hls'] = $hls;
            $this->generateOutput($functionName, 'HLS added to GET array', 2);            
        }
        
        if ($direction != null) {
            $get['direction'] = $direction;
            $this->generateOutput($functionName, 'Direction added to GET array', 2);   
        }
        
        if ($channels != null) {
            foreach ($channels as $channel) {
                $channelBlock .= $channel . ',';
                $get['channel'] = $channelBlock;
            }
            
            $channelBlock = rtrim($channelBlock, ','); 
            $this->generateOutput($functionName, 'Channels added to GET array', 2);
        }
        
        if ($embedable != null) {
            $get['embedable'] = $embedable;
            $this->generateOutput($functionName, 'Embedable added to GET array', 2);
        }
        
        if ($client_id != null) {
            $get['client_id'] = $client_id;
            $this->generateOutput($functionName, 'Client ID added to GET array', 2);
        }
        
        if ($broadcasts != null) {
            $get['broadcasts'] = $broadcasts;
            $this->generateOutput($functionName, 'Broadcasts only added to GET array', 2);
        }
        
        if ($period != null) {
            $get['period'] = $period;
            $this->generateOutput($functionName, 'Period added to GET array', 2);
        }
        
        if ($game != null) {
            $get['game'] = $game;
            $this->generateOutput($functionName, 'Game added to GET array', 2);
        }
        
        if ($sortBy != null) {
            $get['sortby'] = $sortBy;
            $this->generateOutput($functionName, 'Sort By added to GET array', 2);
        }
        
        if ($returnTotal) {
            $this->generateOutput($functionName, 'Returning total objects as reported by API', 2);
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
                    $this->generateOutput($functionName, 'Retrying call', 3);
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
        
        $this->generateOutput($functionName, 'Iterations Completed: ' . $iterations, 3);
        $this->generateOutput($functionName, 'Current return rows: ' . $currentReturnRows, 3);
        
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
                        $this->generateOutput($functionName, 'Retrying call', 3);
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
                    $this->generateOutput($functionName, 'Setting _total as the reported value from the API', 3);
                    $object['_total'] = $value;
                }
            }
            
            // Calculate our returns and our expected returns
            $expectedReturns = $startingLimit * $iterations;
            $currentReturns = $counter - 1;
            
            // Have we gotten everything we requested?
            if ($toDo <= 0){
                $this->generateOutput($functionName, 'All items requested returned, breaking iteration', 3);
                break;
            }
            
            $this->generateOutput($functionName, 'Current counter: ' . $currentReturns, 3);
            $this->generateOutput($functionName, 'Expected counter: ' . $expectedReturns, 3);
            
            // Are we no longer getting data? (Some fancy math here)
            if ($currentReturns != $expectedReturns) {
                $this->generateOutput($functionName, 'Expected number of returns not met, breaking', 3);
                break;
            }
            
            if ($limit != -1){
                $toDo = $limit - $currentReturns;
            }
            
            if ($toDo == 1){
                $toDo = 2; // Catch this, it will drop one return
            }
            
            $this->generateOutput($functionName, 'Returns to grab: ' . $toDo, 3);
            $this->generateOutput($functionName, 'Calculating new Parameters', 3);
            
            // Check how many we have left
            if (($toDo > $startingLimit) && ($toDo > 0) && ($limit != -1)){
                $this->generateOutput($functionName, 'Continuing iteration', 3);
                
                $get = array('limit' => $currentReturns + $startingLimit,
                    'offset' => $currentReturns);
                    
                // Now check every optional param to see if it exists and att it to the array
                if ($authKey != null) {
                    $get['oauth_token'] = $authKey;
                    $this->generateOutput($functionName, 'Auth Key added to GET array', 2);
                }
                
                if ($hls != null) {
                    $get['hls'] = $hls;
                    $this->generateOutput($functionName, 'HLS added to GET array', 2);            
                }
                
                if ($direction != null) {
                    $get['direction'] = $direction;
                    $this->generateOutput($functionName, 'Direction added to GET array', 2);   
                }
                
                if ($channels != null) {
                    foreach ($channels as $channel) {
                        $channelBlock .= $channel . ',';
                        $get['channel'] = $channelBlock;
                    }
                    
                    $channelBlock = rtrim($channelBlock, ','); 
                    $this->generateOutput($functionName, 'Channels added to GET array', 2);
                }
                
                if ($embedable != null) {
                    $get['embedable'] = $embedable;
                    $this->generateOutput($functionName, 'Embedable added to GET array', 2);
                }
                
                if ($client_id != null) {
                    $get['client_id'] = $client_id;
                    $this->generateOutput($functionName, 'Client ID added to GET array', 2);
                }
                
                if ($broadcasts != null) {
                    $get['broadcasts'] = $broadcasts;
                    $this->generateOutput($functionName, 'Broadcasts only added to GET array', 2);
                }
                
                if ($period != null) {
                    $get['period'] = $period;
                    $this->generateOutput($functionName, 'Period added to GET array', 2);
                }
                
                if ($game != null) {
                    $get['game'] = $game;
                    $this->generateOutput($functionName, 'Game added to GET array', 2);
                }
                
                if ($sortBy != null) {
                    $get['sortby'] = $sortBy;
                    $this->generateOutput($functionName, 'Sort By added to GET array', 2);
                }
            } elseif ($limit == -1) {
                $this->generateOutput($functionName, 'Continuing iteration', 3);
                
                $get = array('limit' => $currentReturns + $startingLimit,
                    'offset' => $currentReturns);
                    
                // Now check every optional param to see if it exists and att it to the array
                if ($authKey != null) {
                    $get['oauth_token'] = $authKey;
                    $this->generateOutput($functionName, 'Auth Key added to GET array', 2);
                }
                
                if ($hls != null) {
                    $get['hls'] = $hls;
                    $this->generateOutput($functionName, 'HLS added to GET array', 2);            
                }
                
                if ($direction != null) {
                    $get['direction'] = $direction;
                    $this->generateOutput($functionName, 'Direction added to GET array', 2);   
                }
                
                if ($channels != null) {
                    foreach ($channels as $channel) {
                        $channelBlock .= $channel . ',';
                        $get['channel'] = $channelBlock;
                    }
                    
                    $channelBlock = rtrim($channelBlock, ','); 
                    $this->generateOutput($functionName, 'Channels added to GET array', 2);
                }
                
                if ($embedable != null) {
                    $get['embedable'] = $embedable;
                    $this->generateOutput($functionName, 'Embedable added to GET array', 2);
                }
                
                if ($client_id != null) {
                    $get['client_id'] = $client_id;
                    $this->generateOutput($functionName, 'Client ID added to GET array', 2);
                }
                
                if ($broadcasts != null) {
                    $get['broadcasts'] = $broadcasts;
                    $this->generateOutput($functionName, 'Broadcasts only added to GET array', 2);
                }
                
                if ($period != null) {
                    $get['period'] = $period;
                    $this->generateOutput($functionName, 'Period added to GET array', 2);
                }
                
                if ($game != null) {
                    $get['game'] = $game;
                    $this->generateOutput($functionName, 'Game added to GET array', 2);
                }
                
                if ($sortBy != null) {
                    $get['sortby'] = $sortBy;
                    $this->generateOutput($functionName, 'Sort By added to GET array', 2);
                }
                
            // Last return in a limited case    
            } else { 
                $this->generateOutput($functionName, 'Last return to grab', 3);
                
                $get = array('limit' => $toDo + 1,
                    'offset' => $currentReturns);
                    
                // Now check every optional param to see if it exists and att it to the array
                if ($authKey != null) {
                    $get['oauth_token'] = $authKey;
                    $this->generateOutput($functionName, 'Auth Key added to GET array', 2);
                }
                
                if ($hls != null){
                    $get['hls'] = $hls;
                    $this->generateOutput($functionName, 'HLS added to GET array', 2);            
                }
                
                if ($direction != null){
                    $get['direction'] = $direction;
                    $this->generateOutput($functionName, 'Direction added to GET array', 2);   
                }
                
                if ($channels != null){
                    foreach ($channels as $channel){
                        $channelBlock .= $channel . ',';
                        $get['channel'] = $channelBlock;
                    }
                    
                    $channelBlock = rtrim($channelBlock, ','); 
                    $this->generateOutput($functionName, 'Channels added to GET array', 2);
                }
                
                if ($embedable != null){
                    $get['embedable'] = $embedable;
                    $this->generateOutput($functionName, 'Embedable added to GET array', 2);
                }
                
                if ($client_id != null){
                    $get['client_id'] = $client_id;
                    $this->generateOutput($functionName, 'Client ID added to GET array', 2);
                }
                
                if ($broadcasts != null){
                    $get['broadcasts'] = $broadcasts;
                    $this->generateOutput($functionName, 'Broadcasts only added to GET array', 2);
                }
                
                if ($period != null){
                    $get['period'] = $period;
                    $this->generateOutput($functionName, 'Period added to GET array', 2);
                }
                
                if ($game != null){
                    $get['game'] = $game;
                    $this->generateOutput($functionName, 'Game added to GET array', 2);
                }
                
                if ($sortBy != null){
                    $get['sortby'] = $sortBy;
                    $this->generateOutput($functionName, 'Sort By added to GET array', 2);
                }
            }
            
            $this->generateOutput($functionName, 'New query built, passing to GET:', 3);
            
            // Run a new query
            unset($return); // unset for a clean return
            $return = json_decode($this->cURL_get($url, $get, $options), true);
            
            $iterations ++;
            
            $this->generateOutput($functionName, 'Iterations Completed: ' . $iterations, 3);
            $this->generateOutput($functionName, 'Current rows returned: ' . $currentReturnRows, 3);
            $this->generateOutput($functionName, 'End of iteration sequence', 3);
        }
        
        $this->generateOutput($functionName, 'Exited Iteration', 3);
        
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
                $this->generateOutput($functionName, 'Setting _total as the reported value from the API', 3);
                $object['_total'] = $value;
            }
        }
        
        if ($returnTotal && !key_exists('_total', $object) == 1){
            $this->generateOutput($functionName, '_total not found as a row in returns, but was requested.  Skipped', 3);
            $object['_total'] = count($object);
        }
        
        $this->generateOutput($functionName, 'Total returned rows: ' . ($counter - 1), 3);
        
        // Clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($functionName, $url, $options, $limit, $offset, $arrayKey, $authKey, $hls, $direction, $channels, $embedable, $client_id, $broadcasts, $period, $game, $functionName, $grabbedRows, $currentReturnRows, $counter, $iterations, $toDo, $startingLimit, $channel, $channelBlock, $return, $set, $key, $value, $currentReturns, $expectedReturns, $k, $v, $sortBy);
        
        return $object;
    }
            
    /**
     * Generate an Auth key (token) for our session to use if we don't have one.
     * 
     * @param $code - [string] String of auth code used to grant authorization
     * 
     * @return $token - The generated token and the array of all scopes returned with the token, keyed
     * 
     * @version 1.2
     */
    public function generateToken( $code = null ){
        $functionName = 'Generate_Token';
        $this->generateOutput($functionName, 'Generating auth token', 1);
                
        if( !$code ) {
            $code = $this->twitch_client_code;
        }
        
        $url = 'https://api.twitch.tv/kraken/oauth2/token';
        $post = array(
            'client_secret' => $this->twitch_client_secret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->twitch_client_url,
            'code' => $code,
            'client_id' => $this->twitch_client_id,
            'state' => '1',
        );
       
        $options = array();
                    
        $result = json_decode($this->cURL_post($url, $post, $options, false), true);
 
        if ( array_key_exists( 'access_token', $result ) ){
            $token['token'] = $result['access_token'];
            $token['scopes'] = $result['scope'];
            $this->generateOutput($functionName, 'Access token returned: ' . $token['token'], 3);            
        } else {
            $token['token'] = false;
            $token['grants'] = array();
            $this->generateOutput($functionName, 'Access token not returned', 3);
        }
        
        // Clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($code, $functionName, $url, $post, $options, $result);
        
        return $token;
    }
                       
    /**
     * Checks a token for validity and access grants available.
     * 
     * @param $authToken - [string] The token that you want to check
     * 
     * @return $authToken - [array] Either the provided token and the array of scopes if it was valid or false as the token and an empty array of scopes
     * 
     * @version 5.0
     */    
    public function checkToken( $authToken = null ){
        $functionName = 'Check_Token';         
        $this->generateOutput($functionName, 'Checking OAuth token', 1);
             
        if( !$authToken ) {
            $authToken = $this->twitch_client_token;
        }                             
        
        $url = 'https://api.twitch.tv/kraken?client_id=' . $this->twitch_client_id;
        $post = array(
            'oauth_token' => $authToken
        );
        $options = array();
        
        $result = json_decode( $this->cURL_get( $url, $post, $options, false ), true );                   
        
        $token = array();
        
        if ( isset( $result['token'] ) && isset( $result['token']['valid'] ) && $result['token']['valid'] ){
            $this->generateOutput($functionName, 'Token valid', 3);
            $token['token'] = $authToken;
            $token['scopes'] = $result['token']['authorization']['scopes'];
            $token['name'] = $result['token']['user_name'];
        } else {
            $this->generateOutput($functionName, 'Token not valid', 3);
            $token['token'] = false;
            $token['scopes'] = array();
            $token['name'] = '';
        }
        
        // Clean up
        $this->generateOutput( $functionName, 'Cleaning memory', 3 );
        unset( $authToken, $functionName, $url, $post, $options, $result );
        
        return $token;     
    }
    
    /**
    * Generate an oAuth2 Twitch API URL for an administrator only. The procedure
    * for public visitors will use different methods for total clarity when it comes to
    * security. 
    * 
    * @author Ryan Bayne
    * @version 1.1
    * 
    * @param mixed $permitted_scopes
    * @param mixed $state_array
    */
    public function generate_authorization_url_admin( $permitted_scopes, $state_array ) {
        $this->generateOutput( __FUNCTION__, 'Generating private redirect URL', 1 );
        
        // Default $state array is stored in current users meta, sent to Kraken, returned and compared.
        // It's a WP level of security to ensure the same account making the request is the one processing it. 
        $state_array = shortcode_atts(
                array( 
                        'granttype' => 'admin',
                        'redirectto' => ''// Use to send users to somewhere different. 
                ), 
            $state_array
        ); 
        
        // More security - a transient based on current user ID which is checked on return from Twitch.tv
        delete_transient( TWITCHPRESS_CURRENTUSERID . '_twitchpress_oauth_mainrequest_states' );
        set_transient( TWITCHPRESS_CURRENTUSERID . '_twitchpress_oauth_mainrequest_states', $state_array, 900 );

        $state_imploded = implode( ',', $state_array );
        $state_urlencoded = urlencode( $state_imploded );        
        
        // Create, clean and encode a string from $permitted_scopes array for adding to URL. 
        $scopes_string = '';
        foreach ( $permitted_scopes as $s ){
            $scopes_string .= $s . ' ';
        }
        $scopes_rtrimmed = rtrim( $scopes_string, ' ' );
        $scopes_urlencoded = urlencode( $scopes_rtrimmed );

        // Build oauth2 URL.
        $urlRedirect = 'https://api.twitch.tv/kraken/oauth2/authorize?' .
            'response_type=code' . '&' .
            'client_id=' . $this->twitch_client_id . '&' .
            'redirect_uri=' . $this->twitch_client_url . '&' .
            'scope=' . $scopes_urlencoded . '&' .
            'state=' . $state_urlencoded;
    
        // Clean up
        $this->generateOutput( __FUNCTION__, 'Cleaning URL builder memory', 3 );
        unset( $s, $scopes_rtrimmed, $grantType, $scopes_urlencoded, $functionName, $acceptedscopes, $state_imploded, $state_urlencoded, $state_array );

        return $urlRedirect;       
    }
    
    /**
    * Generate an oAuth2 Twitch API URL for public visitors.
    * 
    * @author Ryan Bayne
    * @version 1.0
    * 
    * @param mixed $permitted_scopes
    * @param mixed $state_array
    */
    public function generate_authorization_url_public( $permitted_scopes, $state_array ) {
        $this->generateOutput( __FUNCTION__, 'Generating public redirect URL', 1 );
        
        // Default $state array is stored in current users meta, sent to Kraken, returned and compared.
        // It's a WP level of security to ensure the same account making the request is the one processing it. 
        $state_array = shortcode_atts(
                array( 
                        'granttype' => 'twitchpresspublic',
                        'redirectto' => ''// Use to send users to somewhere different. 
                ), 
            $state_array
        ); 

        $state_imploded = implode( ',', $state_array );
        $state_urlencoded = urlencode( $state_imploded );        
 
        // Create, clean and encode a string from $permitted_scopes array for adding to URL. 
        $scopes_string = '';
        foreach ( $permitted_scopes as $s ){
            $scopes_string .= $s . ' ';
        }
        $scopes_rtrimmed = rtrim( $scopes_string, ' ' );
        $scopes_urlencoded = urlencode( $scopes_rtrimmed );

        // Build oauth2 URL.
        $urlRedirect = 'https://api.twitch.tv/kraken/oauth2/authorize?' .
            'response_type=code' . '&' .
            'client_id=' . $this->twitch_client_id . '&' .
            'redirect_uri=' . $this->twitch_client_url . '&' .
            'scope=' . $scopes_urlencoded . '&' .
            'state=' . $state_urlencoded;

        // Clean up
        $this->generateOutput( __FUNCTION__, 'Cleaning public URL builder memory', 3 );
        unset( $s, $scopes_rtrimmed, $grantType, $scopes_urlencoded, $functionName, $acceptedscopes, $state_imploded, $state_urlencoded, $state_array );

        return $urlRedirect;       
    }

    /**
    * Set a short-term cookie as a first security measure towards protecting
    * users who do not destroy their session. This cookie will ensure that
    * a user who is part the way through authorizing Twitch but leaves their 
    * computer unattended. Does not allow someone else to complete the authorization
    * which could lead to the creation of a new account that they might have access to.
    * 
    * @version 1.2
    */
    public function wp_setcookie_twitchoauth2_ongoing() { 
        // Create a cookie we use to hold the fact that the user is going through authorization.
        // The cookie can be checked along with $_GET['code'] and $_GET['scope']   
        $result = setcookie( 
            'twitchpressoauthprocess', 
            'adminsiderequest', 
            time() + 120,
            COOKIEPATH,// COOKIEPATH or SITECOOKIEPATH ??? 
            COOKIE_DOMAIN,
            false,
            true      
        );        
        return $result;
    }  
                  
    /**
     * A function able to grab the authentication code from URL generated by Twitch's auth servers
     * 
     * @param $url - [string] The redirect URL from Twitch's authentication servers
     * 
     * @return $code - [string] The returned authentication code used in authenticated calls
     */ 
    public function retrieveRedirectCode( $url,$param = 'code' ) {
        $functionName = 'RETRIEVE_CODE';
        
        $this->generateOutput($functionName, 'Retrieving code from URL String', 1);
        
        $code = twitchpress_getURLParamValue( $url, $param );
        
        //clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($functionName);
        
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
    * @version 1.1
    */
    public function start_twitch_session_admin( $account = 'main' ) {
        // Can change from the default "main" credentails. 
        if( $account !== 'main' ) {
            self::set_application_credentials( $app = 'main' );
        }

        // The plugin will bring the user to their original admin view using the redirectto value.
        $state = array( 'redirectto' => '/wp-admin/admin.php?page=twitchpress&tab=kraken&amp;' . 'section=entermaincredentials' );

        $oAuth2_URL = $this->generate_authorization_url_admin( $this->get_global_accepted_scopes(), $state ); 
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
}

endif;
TWITCHPRESS_Kraken5_Interface::init();