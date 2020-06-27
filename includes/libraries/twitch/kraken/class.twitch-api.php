<?php
/**
 * The main Twitch API updated for Kraken version after it's original download from GitHub.
 * 
 * Do not use this class unless you accept the Twitch Developer Services Agreement
 * @link https://www.twitch.tv/p/developer-agreement
 * 
 * @class    TwitchPress_Admin
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Core
 * @version  5.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Make sure we meet our dependency requirements
if (!extension_loaded('curl')) trigger_error('cURL is not currently installed on your server, please install cURL if your wish to use Twitch services in TwitchPress.');
if (!extension_loaded('json')) trigger_error('PECL JSON or pear JSON is not installed, please install either PECL JSON or compile pear JSON if you wish to use Twitch services in TwitchPress.');

if( !class_exists( 'TWITCHPRESS_Twitch_API' ) ) :

class TWITCHPRESS_Twitch_API {

    // Sites main Twitch.tv app credentials (added version 2.0.4)
    private $app_id = null; 
    private $app_secret = null;// Possibly change to protected?
    private $app_redirect = null;
    private $app_token = null;
    private $app_scopes = null;
    
    // Main channel credentials (added version 2.0.4)    
    private $main_channels_code = null;
    private $main_channels_wpowner_id = null;
    private $main_channels_token = null;
    private $main_channels_refresh = null; 
    private $main_channels_scopes = null;
    private $main_channels_name = null;
    private $main_channels_id = null;
    
    // Pre-2.0.4 values being phased out        
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
            'user:edit:broadcast',       // Edit your channelâ€™s broadcast configuration, including extension configuration. (This scope implies user:read:broadcast capability.)
            'user:read:broadcast',       // View your broadcasting configuration, including extension configurations.
            'user:read:email',           // Read authorized userâ€™s email address.  
            'chat:edit',
            'chat:read'          
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
        'lolindark1'       => array( 'display_name' => 'LOLinDark' ),
        'nookyyy'          => array( 'display_name' => 'nookyyy' ),        
        'testgaming'       => array( 'display_name' => 'TESTGaming' ),
        'capn_flint'       => array( 'display_name' => 'capn_flint' ),
        'wtfosaurus'       => array( 'display_name' => 'WTFOSAURUS' ),
        'starcitizen'      => array( 'display_name' => 'StarCitizen' ),
        'cigcommunity'     => array( 'display_name' => 'CIGCommunity' ),
        'dtox_tv'          => array( 'display_name' => 'DTOX_TV' ),
        'sgt_gamble'       => array( 'display_name' => 'SGT_Gamble' ),
        'bristolboy88'     => array( 'display_name' => 'BristolBoy88' ),
        'mzhartz'          => array( 'display_name' => 'MzHartz' ),
        'boredgameruk'     => array( 'display_name' => 'BoredGamerUK' ),
        'thenoobifier1337' => array( 'display_name' => 'TheNOOBIFIER1337' ),
        'thatgirlslays'    => array( 'display_name' => 'ThatGirlSlays' ),
    );
        
    /**
    * Requirements will be checked here and constants set.
    * 
    * @author Ryan R. Bayne            
    * @version 1.0
    */
    public function __construct(){ 
        $this->set_all_credentials();
    } 

    /**
    * This method makes it possible to store different Developer Applications
    * in the WordPress options table. 
    * 
    * @param mixed $app
    * 
    * @version 5.0
    */
    public function set_all_credentials( $app = 'main' ) {

        // Use 2.0.4 values which come from registry approach.
        $obj = TwitchPress_Object_Registry::get( 'twitchapp' );
        $this->app_id           = $obj->app_id; 
        $this->app_secret       = $obj->app_secret;
        $this->app_redirect     = $obj->app_redirect;
        $this->app_token        = $obj->app_token;
        $this->app_scopes       = $obj->app_scopes; 
               
        // Main channel credentials loaded for service use (added version 2.0.4) 
        $obj = TwitchPress_Object_Registry::get( 'mainchannelauth' );  
        $this->main_channels_code       = $obj->main_channels_code;
        $this->main_channels_wpowner_id = $obj->main_channels_wpowner_id;
        $this->main_channels_token      = $obj->main_channels_token;
        $this->main_channels_refresh    = $obj->main_channels_refresh; 
        $this->main_channels_scopes     = $obj->main_channels_scopes;
        $this->main_channels_name       = $obj->main_channels_name;
        $this->main_channels_id         = $obj->main_channels_id;
                    
        // Old values pre version 2.0.4
        $this->twitch_default_channel = $this->main_channels_name;   
        $this->twitch_channel_id      = $this->main_channels_id;   
        $this->twitch_client_url      = $this->app_redirect;   
        $this->twitch_client_id       = $this->app_id; 
        $this->twitch_client_secret   = $this->app_secret;                           
        $this->twitch_client_code     = $this->main_channels_code; 
        $this->twitch_client_token    = $this->app_token;

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
        }
        
        curl_close($handle);
        
        // Log the HTTP status in more detail if it isn't a good response. 
        if( $httpdStatus !== 200 ) 
        {
            $status_meaning = twitchpress_kraken_httpstatuses( $httpdStatus, 'wiki' );
            if( !is_string( $status_meaning ) ) { $status_meaning = __( 'Sorry, no more information could be retrieved for this status.', 'twitchpress' ); }
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
        }
        
        curl_close($handle);
        
        // Log anything that isn't a good response. 
        if( $httpdStatus !== 200 ) {
            $status_meaning = twitchpress_kraken_httpstatuses( $httpdStatus, 'wiki' );
            if( !is_string( $status_meaning ) ) { $status_meaning = __( 'Sorry, no more information could be retrieved for this status.', 'twitchpress' ); }
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
        }

        curl_close($handle);
        
        // Log anything that isn't a good response. 
        if( $httpdStatus !== 200 ) {
            $status_meaning = twitchpress_kraken_httpstatuses( $httpdStatus, 'wiki' );
            if( !is_string( $status_meaning ) ) { $status_meaning = __( 'Sorry, no more information could be retrieved for this status.', 'twitchpress' ); }
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
            $status_meaning = twitchpress_kraken_httpstatuses( $httpdStatus, 'wiki' );
            if( !is_string( $status_meaning ) ) { $status_meaning = __( 'Sorry, no more information could be retrieved for this status.', 'twitchpress' ); }
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
        $call_object = new TwitchPress_Curl();
        $call_object->originating_function = __FUNCTION__;
        $call_object->originating_line = __LINE__;
        $call_object->type = 'POST';
        $call_object->endpoint = 'https://api.twitch.tv/kraken/oauth2/token?client_id=' . twitchpress_get_app_id();
                
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
        
        // Use app data from registry...
        $twitch_app = TwitchPress_Object_Registry::get( 'twitchapp' );
        $call_object->set_curl_body( array(
            'client_id'        => $twitch_app->app_id,
            'client_secret'    => $twitch_app->app_secret,
            'redirect_uri'     => $twitch_app->app_redirect,
            'grant_type'       => 'client_credentials'
        ) );

        // Start + make the request in one line... 
        $call_object->do_call( 'twitch' );
        
        // This method returns $call_twitch->curl_response_body;
        return $call_object;
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
            return $result;
        } 
        else 
        {
            $request_string = '';
            if( $requesting_function == null ) { $request_string = __( 'Requesting function is not known!', 'twitchpress' ); }
            else{ $request_string = __( 'Requesting function is ', 'twitchpress' ) . $requesting_function; }
            return false;
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

        $url = 'https://api.twitch.tv/kraken';
        $post = array( 
            'oauth_token' => $this->twitch_client_token, 
            'client_id'   => $this->twitch_client_id,          
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
            'scope' => twitchpress_prepare_scopes( twitchpress_get_visitor_scopes() )
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
 
    /**
    * Get Users Follows [from giving ID]  - Originally created for Helix class.
    * 
    * Gets information on follow relationships between two Twitch users. 
    * Information returned is sorted in order, most recent follow first. 
    * This can return information like â€œwho is lirik following,â€?, 
    * â€œwho is following lirik,â€? or â€œis user X following user Y.â€?
    * 
    * The response has a JSON payload with a data field containing an array 
    * of follow relationship elements and a pagination field containing 
    * information required to query for more follow information.
    * 
    * @link https://dev.twitch.tv/docs/v5/reference/users#get-user-follows
    * 
    * @param mixed $after
    * @param mixed $first Maximum number of objects to return. Maximum: 100. Default: 20.
    * @param mixed $from_id User ID. The request returns information about users who are being followed by the from_id user.
    * @param mixed $to_id User ID. The request returns information about users who are following the to_id user.
    * 
    * @version 1.0
    */
    public function get_users_follows( $after = null, $first = null, $from_id = null, $to_id = null ) {
        return false; /* Method probably won't be complete as Kraken is no longer supported */
        /*    
        $endpoint = 'https://api.twitch.tv/helix/users/follows';  
        
        if( $after ) { $endpoint = add_query_arg( array( 'after' => $after ), $endpoint ); }
        if( $first ) { $endpoint = add_query_arg( array( 'first' => $first ), $endpoint ); }
        if( $from_id ) { $endpoint = add_query_arg( array( 'from_id' => $from_id ), $endpoint ); }
        if( $to_id ) { $endpoint = add_query_arg( array( 'to_id' => $to_id ), $endpoint ); }

        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint ); 

        $this->call( 'GET', $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' ); 
                             
        return $this->curl_object->curl_reply_body;       
        */
         
    }
}

endif;                         