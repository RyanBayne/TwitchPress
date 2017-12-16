<?php
/**
 * The main Twitch API updated for Kraken version after it's original
 * download from GitHub.

 * Do not use this class unless you accept the Twitch Developer Services Agreement
 * @link https://www.twitch.tv/p/developer-agreement
 * 
 * @class    TwitchPress_Admin
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Core
 * @version  5.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Make sure we meet our dependency requirements
if (!extension_loaded('curl')) trigger_error('cURL is not currently installed on your server, please install cURL if your wish to use Twitch services in TwitchPress.');
if (!extension_loaded('json')) trigger_error('PECL JSON or pear JSON is not installed, please install either PECL JSON or compile pear JSON if you wish to use Twitch services in TwitchPress.');

if( !class_exists( 'TWITCHPRESS_Kraken_API' ) ) :

class TWITCHPRESS_Kraken_API {
    
    protected $twitch_wperror                = null;
    protected $twitch_default_channel        = null;// Services own channel name, not ID.
    protected $twitch_channel_id             = null;
    protected $twitch_client_id              = null;
    protected $twitch_client_secret          = null;
    protected $twitch_client_url             = null;
    protected $twitch_client_code            = null;
    protected $twitch_client_token           = null;
    protected $twitch_global_accepted_scopes = null;
    protected $twitch_user_token             = null;
    
    // Debugging variables.
    public $twitch_call_name = 'Unknown';
    public $twitch_call_id   = null;
    public $twitch_sandbox_mode = false;
    
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
    * I'm also using channels I endorse to ensure test content is high quality.
    * 
    * @var mixed
    * 
    * @version 1.0
    */
    public $twitchchannels_endorsed = array(
        'zypherevolved'        => array( 'display_name' => 'ZypheREvolved' ),
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
        'thatgirlslays'        => array( 'display_name' => 'ThatGirlSlays' ),
    );
        
    /**
    * Requirements will be checked here and constants set.
    * 
    * @author Ryan R. Bayne            
    * @version 1.0
    */
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
    * @version 5.0
    */
    public function set_application_credentials( $app = 'main' ) {
        
        $this->twitch_default_channel = get_option( 'twitchpress_' . $app . '_channel_name' );   
        $this->twitch_channel_id      = get_option( 'twitchpress_' . $app . '_channel_id' );   
        $this->twitch_client_url      = get_option( 'twitchpress_' . $app . '_redirect_uri' );   
        $this->twitch_client_id       = get_option( 'twitchpress_' . $app . '_client_id' ); 
        $this->twitch_client_secret   = get_option( 'twitchpress_' . $app . '_client_secret' );                           
        $this->twitch_client_code     = get_option( 'twitchpress_' . $app . '_code' ); 
        
        // Tokens expire so we will check our current token and update option if needed.  
        $this->establish_application_token( __FUNCTION__ );
        
        // Set token which should be old and valid or new and valid.                           
        $this->twitch_client_token = get_option( 'twitchpress_' . $app . '_token' );   
        
        // Set users token.
        $this->twitch_user_token = twitchpress_get_user_token( get_current_user_id() );               
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
     * 
     * @version 1.0
     */
    public static function init() {   
        add_action( 'plugins_loaded', array( __CLASS__, 'administrator_main_account_listener' ), 50 );
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
        
        // This listener is for requests started on administration side only.  
        if( !is_user_logged_in() ) {         
            return;
        }        
        
        // This is not a listener for processing WordPress logins.
        // This $_GET is set by TwitchPress_Login_Extension.
        if( isset( $_GET['twitchpress_sentto_login'] ) ) {
            return;
        }
                
        // Create a notice for an error.
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
        $bugnet->trace( 'oauth2mainaccount',
                        __LINE__,
                        __FUNCTION__,
                        __FILE__,
                        false,
                        __( 'TwitchPress Main Account Listener: Starting listener for oAuth2 on main account by administrator.', 'twitchpress' )
        );
                     
        if( !isset( $_GET['code'] ) ) {       
            $return = true;
            $return_reason .= __( 'TwitchPress Main Account Listener: No code returned.', 'twitchpress' );
        }          

        // We require the local state value stored in transient. 
        if( !$transient_state = get_transient( 'twitchpress_oauth_' . $_GET['state'] ) ) {       
            $return = true;
            $return_reason .= __( 'TwitchPress Main Account Listener: No matching transient.', 'twitchpress' );
        }   
         
        // Ensure we have the admin view or page the user needs to be sent to. 
        if( !isset( $transient_state['redirectto'] ) ) {         
            $return = true;
            $return_reason .= __( 'TwitchPress Main Account Listener: The redirectto value does not exist.', 'twitchpress' );    
        } 
          
        // For this procedure the userrole MUST be administrator.
        if( !isset( $transient_state['userrole'] ) ) {        
            $return = true;
            $return_reason .= __( 'TwitchPress Main Account Listener: this request is not an expected operation related to the main account.', 'twitchpress' );    
        }          
        
        if( !isset( $transient_state['userrole'] ) || 'administrator' !== $transient_state['userrole'] ) {        
            $return = true;
            $return_reason .= __( 'TwitchPress Main Account Listener: User is not an administrator.', 'twitchpress' );    
        }         
                
        // Validate the code as a measure to prevent URL spamming that gets further than here.
        if( !twitchpress_validate_code( $_GET['code'] ) ) {        
            $return = true;
            $return_reason .= __( 'TwitchPress Main Account Listener: Code is invalid.', 'twitchpress' );
        }
        else
        {
            $code = $_GET['code'];
        }
        
        // If we have a return reason, add it to the trace then do the return. 
        if( $return === true ) {
            // We can end the trace here early but more trace entries will follow. 
            $bugnet->trace( 'oauth2mainaccount',
                __LINE__,
                __FUNCTION__,
                __FILE__,
                true,
                $return_reason
            );
            
            return false;
        }
   
        // We established a legit oAuth2 scenario by an administator. 
        update_option( 'twitchpress_main_code', esc_url( $code ) );
   
        // Update current users meta with the main code also. 
        twitchpress_update_user_code( get_current_user_id(), $code ); 
        
        $kraken = new TWITCHPRESS_Kraken_Calls();
        
        // We need a Twitch API user token for the current administrator only. 
        $token = $kraken->establish_user_token( __FUNCTION__, get_current_user_id() );

        if( !$token ) {        
            $bugnet->trace( 'oauth2mainaccount',
                __LINE__,
                __FUNCTION__,
                __FILE__,
                true,
                __( 'No existing token could be used and Kraken did not return a fresh one.', 'twitchpress' )
            );      
                  
            return;     
        }                             
        
        TwitchPress_Admin_Notices::add_custom_notice( 'mainkrakenapplicationsetup', __( 'Twitch.tv provided a token to allow this site to access your channel based on the permissions (scopes) you selected.')  );
               
        // Confirm the giving main (default) channel is valid. 
        $user_objects = $kraken->get_users( $kraken->twitch_default_channel );
        
        if( !isset( $user_objects['users'][0]['_id'] ) ) {
            TwitchPress_Admin_Notices::add_custom_notice( 'listenerchanneldoesnotexist', __( '<strong>Channel Not Confirmed:</strong> TwitchPress wants to avoid errors in future by ensuring what you typed is correct. So far it could not confirm your entered channel is correct. Please check the spelling of your channel and the status of Twitch. If your entered channel name is correct and Twitch is online, please report this message.', 'twitchpress' ) );      
            
            $bugnet->trace( 'oauth2mainaccount',
                __LINE__,
                __FUNCTION__,
                __FILE__,
                true,
                __( 'TwitchPress Main Account Listener: Kraken user object cannot confirm channel exists.', 'twitchpress' )
            );
                        
            return;                         
        } 

        update_option( 'twitchpress_main_channel_id', $user_objects['users'][0]['_id'], true );        
  
        // Assume the channel is owned by the current logged in admin or they just want it paired with the current WP account. 
        // Store all possible details in user meta. 
        twitchpress_update_user_oauth( 
            get_current_user_id(), 
            $code, 
            $token, 
            $user_objects['users'][0]['_id'] 
        );
        
        // Not going to end trace here, will end it on Setup Wizard. 
        $bugnet->trace( 'oauth2mainaccount',
            __LINE__,
            __FUNCTION__,
            __FILE__,
            true,
            __( 'TwitchPress Main Account Listener: Admin Listener Passed. Forwarding user to: ' . $transient_state['redirectto'], 'twitchpress' )
        );
                    
        // Forward user to the custom destinaton i.e. where they were before oAuth2. 
        twitchpress_redirect_tracking( $transient_state['redirectto'], __LINE__, __FUNCTION__ );
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
        $scope['channel_read']['userdesc']               = __( 'Read your non-public channel information. Including email address and stream key.', 'twitchpress' );
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
    * @version 1.0
    */
    private function store_curl_get( $function, $result, $httpdstatus, $header, $get, $url, $curl_url, $error_string, $error_no, $arguments = array() ) {

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
            $this->update_main_client_token( $token['token'] );
            
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
     * @version 5.2
     */
    public function request_user_access_token( $code = null, $requesting_function = null ){

        if( !$code ) {
            $code = $this->twitch_client_code;
        }
        
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
                       
    /**
     * Checks a token for validity and access grants available.
     * 
     * @return array $result if token is still valid, else false.  
     * 
     * @version 5.2
     */    
    public function check_application_token(){
        $token = $this->get_main_client_token();
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
            $this->bugnet->log( __FUNCTION__, __( 'Invalid token', 'twitchpress' ), array(), true, true );
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
    public function check_user_token( $user_id ){
        
        // Get the giving users token. 
        $user_token = twitchpress_get_user_token( $user_id );
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
    
    public function update_main_client_token( $token ) {
        return update_option( 'twitchpress_main_token', $token );
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
TWITCHPRESS_Kraken_API::init();