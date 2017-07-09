<?php
/**
 * Kraken5 main class for calling the Twitch API. 
 * 
 * @author Ryan R. Bayne
 * 
 * Do not use this class unless you accept the Twitch Developer Services Agreement
 * @link https://www.twitch.tv/p/developer-agreement
 * 
 * @class    TwitchPress_Admin
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Core
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Make sure we meet our dependency requirements
if (!extension_loaded('curl')) trigger_error('cURL is not currently installed on your server, please install cURL if your wish to use Twitch services in TwitchPress.');
if (!extension_loaded('json')) trigger_error('PECL JSON or pear JSON is not installed, please install either PECL JSON or compile pear JSON if you wish to use Twitch services in TwitchPress.');

if( !class_exists( 'TWITCHPRESS_Kraken5' ) ) :

class TWITCHPRESS_Kraken5_Calls extends TWITCHPRESS_Kraken5_Interface {
    
    /**
     * WordPress integrating constructor. 
     * 
     * Put add_action() specific to this class in here. 
     * Get WP option values required by class in here.
     * 
     * @package TwitchPress
     */
    public static function init() {              
        //add_action( 'shutdown', array( __CLASS__, 'store_notices' ) );
    }
    
    /**
    * Gets objects for multiple users.
    * 
    * @param mixed $users
    * 
    * @version 5.0
    */
    public function get_users( $users ) {
        $functionName = 'GET_USERS';
        
        // We need a string.
        if( is_array( $users ) ) {
            $users_string = implode( ',', $users );
        } else {
            $users_string = $users;
        }
        
        $this->generateOutput($functionName, 'Attempting to get objects for the following users: ' . $users_string, 1);
        
        $url = 'https://api.twitch.tv/kraken/users?login=' . $users_string . '';
        $options = array();
        $get = array();
        
        // Build our cURL query and store the array
        $usersObject = json_decode($this->cURL_get($url, $get, $options, false), true);
        $this->generateOutput($functionName, 'Raw return: ' . json_encode( $usersObject ), 4);
        
        //clean up
        $this->generateOutput($functionName, 'Cleaning Memory', 3);
        unset( $users_string, $url, $options, $get, $functionName );
        
        return $usersObject;        
    }
    
    /**
     * Gets the current authenticated users Twitch user object.
     * 
     * @param $user - [string] Username to grab the object for
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * 
     * @return $userObject - [array] Returned object for the query
     * 
     * @version 5.0
     */ 
    public function getUserObject_Authd( $token, $code ){
        $functionName = 'GET_USEROBJECT-AUTH';
        $requiredAuth = 'user_read';

        $this->generateOutput($functionName, 'Attempting to get the authenticated user object for the current user.', 1);
        
        // We were supplied an OAuth token. check it for validity and scopes
        if ( ( $token != null || '') || ( $code != null || false ) ){
            if ( $token != null || ''){
                $check = $this->checkToken( $token );
              
                if ($check["token"] != false){
                    $auth = $check;
                } else { // attempt to generate one
                    if ( $code != null || '' ){
                        $auth = $this->generateToken( $code ); // Assume generation and check later for failure
                    } else {
                        $this->generateError(400, 'Existing token expired and no code available for generation.');
                    }
                }
                
            // Assume the code was given instead and generate if we can    
            } else { 
                $auth = $this->generateToken( $code ); // Assume generation and check later for failure
            }
            
            // check to see if we recieved a token after all of that checking
            if ( !isset( $auth ) || !isset( $auth['token'] ) || $auth['token'] == false ) {
                $this->generateError(400, 'Auth key not returned, exiting function: ' . $functionName);
                return; // return out after the error is passed
            }
            
            $authSuccessful = false;

            // Check the array of scopes
            foreach ($auth['scopes'] as $type) {
                if ($type == $requiredAuth) {
                    // We found the scope, we are good then
                    $authSuccessful = true;
                    break;
                }
            }
            
            // Did we fail?
            if (!$authSuccessful) {
                $this->generateError(403, 'Authentication token failed to have permissions for ' . $functionName . '; required Auth: ' . $requiredAuth);
                return null;
            }
            
            // Assign our key
            $this->generateOutput($functionName, 'Required scope found in array', 3);
            $token = $auth['token'];
        }
        
        $url = 'https://api.twitch.tv/kraken/user?client_id=' . $this->twitch_client_id;
        $options = array();
        $get = array('oauth_token' => $token );
                          
        // Build our cURL query and store the array
        $userObject = json_decode( $this->cURL_get( $url, $get, $options, false ), true );
        $this->generateOutput( $functionName, 'Raw return: ' . json_encode( $userObject ), 4 );
       
        //clean up
        $this->generateOutput($functionName, 'Cleaning Memory', 3);
        unset($url, $options, $get, $token, $auth, $authSuccessful, $type, $functionName, $code);

        return $userObject;
    }
    
    /**
     * Grabs an authenticated channel object using an authentication key to determine what channel to grab
     * 
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * 
     * @return $object - [array] Keyed array of all channel data
     */ 
    public function getChannelObject_Authd( $token = null, $code = null ){                                     
        $functionName = 'GET_CHANNEL_AUTHED';
        $requiredAuth = 'channel_read';
        $this->generateOutput( $functionName, 'Grabbing authenticated channel object', 1 );
        
        if( !$token ) {
            $token = $this->twitch_client_token;
        }
        
        if( !$code ) {
            $code = $this->twitch_client_code;
        }
        
        // We were supplied an OAuth token. check it for validity and scopes
        if (($token != null || '') || ($code != null || false)){
            if ($token != null || ''){
                $check = $this->checkToken($token);
                
                if ($check["token"] != false){
                    $auth = $check;
                } else { // attempt to generate one
                    if ($code != null || ''){
                        $auth = $this->generateToken($code); // Assume generation and check later for failure
                    } else {
                        $this->generateError( 400, 'Existing token expired and no code available for generation.' );
                        return array();
                    }
                }
            } else { // Assume the code was given instead and generate if we can
                $auth = $this->generateToken($code); // Assume generation and check later for failure
            }
            
            // check to see if we recieved a token after all of that checking
            if ($auth['token'] == false) {
                $this->generateError( 400, 'Auth key not returned, exiting function: ' . $functionName );
                return array(); // return out after the error is passed
            }
            
            $authSuccessful = false;
            
            // Check the array of scopes
            foreach ($auth['scopes'] as $type){
                if ($type == $requiredAuth){
                    // We found the scope, we are good then
                    $authSuccessful = true;
                    break;
                }
            }
                             
            // Did we fail?
            if (!$authSuccessful){
                $this->generateError(403, 'Authentication token failed to have permissions for ' . $functionName . '; required Auth: ' . $requiredAuth);
                return array();
            }
            
            // Assign our key
            $this->generateOutput($functionName, 'Required scope found in array', 3);
            $token = $auth['token'];
        }
        
        $url = 'https://api.twitch.tv/kraken/channel';
        $get = array( 'oauth_token' => $token );
        $options = array();

        $object = json_decode($this->cURL_get($url, $get, $options, false), true);
        
        $this->generateOutput($functionName, 'Raw return: ' . json_encode($object), 4);
        
        // Clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($token, $code, $auth, $authSuccessful, $type, $url, $get, $options);
        
        if (!is_array($object)){
            $object = array(); // Catch to make sure that an array is returned no matter what, technically our fail state
        }
        
        return $object;
    }  
        
    /**
    * Returns posts from the feed of the giving channel.
    * 
    * @param $chan - [string] Channel name to grab video objects from
    * @param $limit - [int] Limit of channel objects to return
    * @param $offset - [int] Maximum number of objects to return
    * @param $returnTotal - [bool] Returns a _total row in the array
    * 
    * @return $feedpostsObjects - [array] array of all returned video objects, Key is ID
    * 
    * @author Ryan R. Bayne
    * @version 1.2
    */ 
    public function getFeedPosts( $chan, $limit = -1, $offset = 0, $returnTotal = false ) {
        $functionName = 'GET_FEEDPOSTS-CHANNEL';

        $this->generateOutput($functionName, 'Getting feed post objects for channel: ' . $chan, 1);

        // Init some vars
        $feedpostsObjects = array();     
        $feedposts = array();
        $options = array();
        $url = 'https://api.twitch.tv/kraken/feed/' . $chan . '/posts';

        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
            
        // Build our cURL query and store the array
        $feedposts = $this->get_iterated($functionName, $url, $options, $limit, $offset, 'videos', null, null, null, null, null, null, null, null, null, $returningTotal);

        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            $this->generateOutput($functionName, 'Including _total as the count of all object', 3);
            $feedpostsObjects['_total'] = count($feedposts);
        }

        // Key the data
        foreach ( $feedposts as $k => $video ){
            if ( $k == '_total' ){
                $this->generateOutput($functionName, 'Setting key: ' . $k, 3);
                $feedpostsObjects[$k] = $video;
                continue;
            }
            
            $key = $video['id'];
            $feedpostsObjects[$key] = $video;
            $this->generateOutput($functionName, 'Setting key: ' . $key, 3);
        }

        // Clean up quickly
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($chan, $limit, $offset, $functionName, $video, $feedposts, $key, $options, $url, $k, $returnTotal, $returningTotal);
        
        return $feedpostsObjects;                  
    }

    /**
    * Get the very latest feed post from the giving channel.
    * 
    * @param mixed $channel
    * @param mixed $value
    * 
    * @version 1.0
    */
    public function getLatestFeed( $channel = 'ZypheREvolved', $value = null ){
        $post = self::getFeedPosts( $channel, 1, -1 );
        if( !$value ) { return $post; }                                  
        
        // Specific value has been requested.
        $post = array_shift( $post );
           
        if( isset( $post[ $value ] ) ) { return $post[ $value ]; }
        
        return false;
    }
    
    /**
    * Submit new post to the feed for the giving channel.
    * 
    * @param mixed $url
    * @param mixed $post
    * @param mixed $options
    * @param mixed $returnStatus
    * 
    * @author Ryan R. Bayne
    * @version 1.2
    */
    public function postFeedPost( $postparam = array(), $options = array() ){
        $functionName = 'ADD_FEEDPOST';
        $requiredAuth = 'channel_feed_edit';
        
        $url = 'https://api.twitch.tv/kraken/feed/';
        $url.= $this->twitch_channel_id;
        $url.= '/posts';
        $url.= '?client_id=' . $this->twitch_client_id; 
                          
        $post = array( 'oauth_token' => $this->twitch_client_token );
        $post = array_merge( $post, $postparam );
                    
        $returned_status = $this->cURL_post( $url, $post, $options, true );
        
        unset($url,$post,$options,$postparam);

        return $returned_status;  
    }  
        
    /**
     * Grabs a list of the users blocked from a channel
     * 
     * @param $chan - [string] Channel name to grab blocked users list from
     * @param $limit - [int] Limit of users to grab, -1 is unlimited
     * @param $offset - [int] The starting offset of the query
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * @param $returnTotal - [bool] Returns a _total row in the array
     * 
     * @return $blockedUsers - Unkeyed array of all blocked users to limit
     */ 
    public function getBlockedUsers($chan, $limit = -1, $offset = 0, $token, $code, $returnTotal = false) {
        $functionName = 'GET_BLOCKED';
        $requiredAuth = 'user_blocks_read';
        
        $this->generateOutput($functionName, 'Attempting to pull a complete list of blocked users for the channel: ' . $chan, 1);
        
        // We were supplied an OAuth token. check it for validity and scopes
        if (($token != null || '') || ($code != null || false)) {
            if ($token != null || '') {
                $check = $this->checkToken($token);
                
                if ($check["token"] != false) {
                    $auth = $check;
                } else { // attempt to generate one
                    if ($code != null || '') {
                        $auth = $this->generateToken($code); // Assume generation and check later for failure
                    } else {
                        $this->generateError(400, 'Existing token expired and no code available for generation.');
                        return array(); // return out here, match the fail state of the call
                    }
                }
            } else { // Assume the code was given instead and generate if we can
                $auth = $this->generateToken($code); // Assume generation and check later for failure
            }
            
            // check to see if we recieved a token after all of that checking
            if ($auth['token'] == false) {
                $this->generateError(400, 'Auth key not returned, exiting function: ' . $functionName);
                return array(); // return out after the error is passed, match the fail tate of the call
            }
            
            $authSuccessful = false;
            
            // Check the array of scopes
            foreach ($auth['scopes'] as $type) {
                if ($type == $requiredAuth) {
                    // We found the scope, we are good then
                    $authSuccessful = true;
                    break;
                }
            }
            
            // Did we fail?
            if (!$authSuccessful) {
                $this->generateError(403, 'Authentication token failed to have permissions for ' . $functionName . '; required Auth: ' . $requiredAuth);
                return array(); // Match the fail state of the call so users are not thrown off
            }
            
            // Assign our key
            $this->generateOutput($functionName, 'Required scope found in array', 3);
            $token = $auth['token'];
        }
        
        $url = 'https://api.twitch.tv/kraken/users/' . $chan . '/blocks';
        $options = array(); // For things where I don't put in any default data, I will leave the end user the capability to configure here
        $usernames = array();
        $usernamesObject = array();
        $counter = 0;
        
        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
        
        if ($returningTotal) {
            $this->generateOutput($functionName, 'Returning total count of objets as reported by API', 2);
        }
        
        $usernamesObject = $this->get_iterated($functionName, $url, $options, $limit, $offset, 'blocks', $token, null, null, null, null, null, null, null, null, $returningTotal);
        
        $this->generateOutput($functionName, 'Raw return: ' . json_encode($usernamesObject), 4);
        
        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)) {
            $this->generateOutput($functionName, 'Including _total as the count of all object', 3);
            $usernames['_total'] = count($usernamesObject);
        }
        
        // Set the array
        foreach ($usernamesObject as $key => $user){
            if ($key == '_total'){
                // It isn't really the user, but this stops code changes
                $this->generateOutput($functionName, 'Setting key: ' . $key, 3);
                $usernames[$key] = $user;
                continue;
            }
            
            $this->generateOutput($functionName, 'Setting Key for username: ' . $user['user'][TWITCH_KEY_NAME], 3);
            $usernames[$counter] = $user['user'][TWITCH_KEY_NAME];
            $counter ++;
        }
        
        // Was anything returned?  If not, put some output
        if (empty($usernames)){
            $this->generateOutput($functionName, 'No blocked users returned for channel: ' . $chan, 3);
        }
        
        // Clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($options, $url, $get, $limit, $usernamesObject, $key, $k, $value, $v, $functionName, $returnTotal, $returningTotal);
        
        // Return out our unkeyed or empty array
        return $usernames;
    }
    
    /**
     * Adds a user to a channel's blocked list
     * 
     * @param $chan - [string] channel to add the user to
     * @param $username - [string] username of newly banned user
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * 
     * @return $success - [bool] Result of the query
     */ 
    public function addBlockedUser($chan, $username, $token, $code){
        $functionName = 'ADD_BLOCKED';
        $requiredAuth = 'user_blocks_edit';
        
        $this->generateOutput($functionName, 'Attempting to add ' . $username . ' to ' . $chan . '\'s list of blocked users', 1);
        
        // We were supplied an OAuth token. check it for validity and scopes
        if (($token != null || '') || ($code != null || false)){
            if ($token != null || ''){
                $check = $this->checkToken($token);
                
                if ($check["token"] != false){
                    $auth = $check;
                } else { // attempt to generate one
                    if ($code != null || ''){
                        $auth = $this->generateToken($code); // Assume generation and check later for failure
                    } else {
                        $this->generateError(400, 'Existing token expired and no code available for generation.');
                        return false; // return out here, match the fail state of the call
                    }
                }
            } else { // Assume the code was given instead and generate if we can
                $auth = $this->generateToken($code); // Assume generation and check later for failure
            }
            
            // check to see if we recieved a token after all of that checking
            if ($auth['token'] == false) {
                $this->generateError(400, 'Auth key not returned, exiting function: ' . $functionName);
                return false; // return out after the error is passed, match the fail state of the call
            }
            
            $authSuccessful = false;
            
            // Check the array of scopes
            foreach ($auth['scopes'] as $type){
                if ($type == $requiredAuth){
                    // We found the scope, we are good then
                    $authSuccessful = true;
                    break;
                }
            }
            
            // Did we fail?
            if (!$authSuccessful){
                $this->generateError(403, 'Authentication token failed to have permissions for ' . $functionName . '; required Auth: ' . $requiredAuth);
                return false; // match fail state
            }
            
            // Assign our key
            $this->generateOutput($functionName, 'Required scope found in array', 3);
            $token = $auth['token'];
        }
                
        $url = 'https://api.twitch.tv/kraken/users/' . $chan . '/blocks/' . $username;
        $options = array();
        $post = array('oauth_token' => $token);
            
        $result = $this->cURL_put($url, $post, $options, true);
        
        // What did we get returned status wise?
        if ($result = 200){
            $this->generateOutput($functionName, 'Successfully blocked channel ' . $username, 3);
            $success = true;
        } else {
            $this->generateOutput($functionName, 'Unsuccessfully blocked channel ' . $username, 3);
            $success = false;
        }
        
        // Clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($chan, $username, $token, $code, $result, $functionName, $requiredAuth, $auth, $authSuccessful, $type, $url, $options, $post);
        
        // Post handles successs, so pass the info on
        return $success;  
    }
    
    /**
     * Removes a user from being blocked on a channel
     * 
     * @param $chan     - [string] channel to remove the user from.
     * @param $username - [string] username of newly pardoned user
     * @param $token  - [string] Authentication key used for the session
     * @param $code     - [string] Code used to generate an Authentication key
     * 
     * @return $success - [bool] Result of the query
     */ 
    public function removeBlockedUser($chan, $username, $token, $code){
        $functionName = 'REMOVE_BLOCKED';
        $requiredAuth = 'user_blocks_edit';
        
        $this->generateOutput($functionName, 'Attempting to remove ' . $username . ' from ' . $chan . '\'s list of blocked users', 1);
        
        // We were supplied an OAuth token. check it for validity and scopes
        if (($token != null || '') || ($code != null || false)){
            if ($token != null || ''){
                $check = $this->checkToken($token);
                
                if ($check["token"] != false){
                    $auth = $check;
                } else { // attempt to generate one
                    if ($code != null || ''){
                        $auth = $this->generateToken($code); // Assume generation and check later for failure
                    } else {
                        $this->generateError(400, 'Existing token expired and no code available for generation.');
                        return false; // return out here, match the fail state of the call
                    }
                }
            } else { // Assume the code was given instead and generate if we can
                $auth = $this->generateToken($code); // Assume generation and check later for failure
            }
            
            // check to see if we recieved a token after all of that checking
            if ($auth['token'] == false) {
                $this->generateError(400, 'Auth key not returned, exiting function: ' . $functionName);
                return false; // return out after the error is passed, match the fail state of the call
            }
            
            $authSuccessful = false;
            
            // Check the array of scopes
            foreach ($auth['scopes'] as $type){
                if ($type == $requiredAuth){
                    // We found the scope, we are good then
                    $authSuccessful = true;
                    break;
                }
            }
            
            // Did we fail?
            if (!$authSuccessful){
                $this->generateError(403, 'Authentication token failed to have permissions for ' . $functionName . '; required Auth: ' . $requiredAuth);
                return false; // match fail state
            }
            
            // Assign our key
            $this->generateOutput($functionName, 'Required scope found in array', 3);
            $token = $auth['token'];
        }
        
        $url = 'https://api.twitch.tv/kraken/users/' . $chan . '/blocks/' . $username;
        $options = array();
        $post = array(
            'oauth_token' => $token);
            
        $success = $this->cURL_delete($url, $post, $options);
        
        $this->generateOutput($functionName, 'Raw return: ' . json_encode($success), 4);
        
        if ($success == '204'){
            $this->generateOutput($functionName, 'Successfully removed ' . $username . ' from ' . $chan . '\'s list of blocked users', 3);
        } else if ($success == '422') {
            $this->generateOutput($functionName, 'Service unavailable or delete failed', 3);
        } else {
            $this->generateOutput($functionName, 'Failed to remove ' . $username . ' from ' . $chan . '\'s list of blocked users', 3);
        }
        
        // Clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($chan, $username, $token, $code, $auth, $authSuccessful, $type, $url, $options, $post, $functionName);
        
        // Bascally we either deleted or they were never there
        return true;  
    }
    
    /**
     * Grabs a full channel object of all publically available data for the channel
     * 
     * @param $channel_id - [string] ID of the channel to grab the object for
     * @param $clientid - [string]
     * 
     * @return $object - [array] Keyed array of all publically available channel data
     * 
     * @version 5.0
     */
    public function getChannelObject( $channel_id ){
        $functionName = 'GET_CHANNEL';
        $this->generateOutput($functionName, 'Grabbing channel object for channel: ' . $channel_id, 1);
        
        $url = 'https://api.twitch.tv/kraken/channels/' . $channel_id . '?client_id=' . $this->twitch_client_id;
        $get = array();
        $options = array();
        
        $this->generateOutput($functionName, 'Grabbing channel object for ' . $channel_id, 3);
        
        $object = json_decode($this->cURL_get($url, $get, $options, false), true);
        
        $this->generateOutput($functionName, 'Raw return: ' . json_encode($object), 4);
        
        // Clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset( $channel_id, $functionName, $url, $get, $options );
        
        if (!is_array($object)){
            $object = array(); // Catch to make sure that an array is returned no matter what, technically our fail state
        }
        
        return $object;
    }
    
    /**
     * Grabs a list of all editors supplied for the channel
     * 
     * @param $chan - [string] the string channel name to grab the editors for
     * @param $limit - [int] Limit of users to grab, -1 is unlimited
     * @param $offset - [int] The initial offset of the query
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * @param $returnTotal - [bool] Returns a _total row in the array
     * 
     * @return $editors - [array] unkeyed array of all editor names
     */ 
    public function getEditors($chan, $limit = -1, $offset = 0, $token, $code, $returnTotal = false){
        $functionName = 'GET_EDITORS';
        $requiredAuth = 'channel_read';
        $this->generateOutput($functionName, 'Grabbing editors for ' . $chan . '\'s channel', 1);
        
        // We were supplied an OAuth token. check it for validity and scopes
        if (($token != null || '') || ($code != null || false)){
            if ($token != null || ''){
                $check = $this->checkToken($token);
                
                if ($check["token"] != false){
                    $auth = $check;
                } else { // attempt to generate one
                    if ($code != null || ''){
                        $auth = $this->generateToken($code); // Assume generation and check later for failure
                    } else {
                        $this->generateError(400, 'Existing token expired and no code available for generation.');
                        return array();
                    }
                }
            } else { // Assume the code was given instead and generate if we can
                $auth = $this->generateToken($code); // Assume generation and check later for failure
            }
            
            // check to see if we recieved a token after all of that checking
            if ($auth['token'] == false) {
                $this->generateError(400, 'Auth key not returned, exiting function: ' . $functionName);
                return array(); // return out after the error is passed
            }
            
            $authSuccessful = false;
            
            // Check the array of scopes
            foreach ($auth['scopes'] as $type){
                if ($type == $requiredAuth){
                    // We found the scope, we are good then
                    $authSuccessful = true;
                    break;
                }
            }
            
            // Did we fail?
            if (!$authSuccessful){
                $this->generateError(403, 'Authentication token failed to have permissions for ' . $functionName . '; required Auth: ' . $requiredAuth);
                return array();
            }
            
            // Assign our key
            $this->generateOutput($functionName, 'Required scope found in array', 3);
            $token = $auth['token'];
        }
        
        $url = 'https://api.twitch.tv/kraken/channels/' . $chan . '/editors';
        $options = array(); // For things where I don't put in any default data, I will leave the end user the capability to configure here
        $counter = 0;
        $editors = array();
        $editorsObject = array();
            
        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
    
        $editorsObject = $this->get_iterated($functionName, $url, $options, $limit, $offset, 'users', $token, null, null, null, null, null, null, null, null, $returningTotal);
        
        $this->generateOutput($functionName, 'Raw return: ' . json_encode($editorsObject), 4);
        
        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            $this->generateOutput($functionName, 'Including _total as the count of all object', 3);
            $editors['_total'] = count($editorsObject);
        }

        foreach ($editorsObject as $key => $editor){
            if ($key == '_total'){
                $this->generateOutput($functionName, 'Setting key: ' . $key, 3);
                $editors[$key] = $editor;
                continue;
            }
            
            $editors[$counter] = $editor[TWITCH_KEY_NAME];
        }
        
        // Clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($chan, $limit, $offset, $token, $code, $auth, $authSuccessful, $type, $functionName, $url, $options, $counter, $editor, $editorsObject, $returnTotal, $returningTotal, $key);
        
        return $editors;
    }
    
    /**
     * Updates the channel object with new info
     * 
     * @param $chan - [string] Channel to update
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * @param $title - [string] New title for the stream
     * @param $game - [string] Game title to update to the channel
     * @param $delay - [int] Seconds of stream delay to put into effect
     * 
     * @return $result - [bool] Success of the query
     */ 
    public function updateChannelObject($chan, $token, $code, $title = null, $game = null, $delay = null){
        $requiredAuth = 'channel_editor';
        $functionName = 'UPDATE_CHANNEL';
        
        $this->generateOutput($functionName, 'Updating Channel object', 1);
        
        // We were supplied an OAuth token. check it for validity and scopes
        if (($token != null || '') || ($code != null || false)){
            if ($token != null || ''){
                $check = $this->checkToken($token);
                
                if ($check["token"] != false){
                    $auth = $check;
                } else { // attempt to generate one
                    if ($code != null || ''){
                        $auth = $this->generateToken($code); // Assume generation and check later for failure
                    } else {
                        $this->generateError(400, 'Existing token expired and no code available for generation.');
                        return false;
                    }
                }
            } else { // Assume the code was given instead and generate if we can
                $auth = $this->generateToken($code); // Assume generation and check later for failure
            }
            
            // check to see if we recieved a token after all of that checking
            if ($auth['token'] == false) {
                $this->generateError(400, 'Auth key not returned, exiting function: ' . $functionName);
                return false; // return out after the error is passed
            }
            
            $authSuccessful = false;
            
            // Check the array of scopes
            foreach ($auth['scopes'] as $type){
                if ($type == $requiredAuth){
                    // We found the scope, we are good then
                    $authSuccessful = true;
                    break;
                }
            }
            
            // Did we fail?
            if (!$authSuccessful){
                $this->generateError(403, 'Authentication token failed to have permissions for ' . $functionName . '; required Auth: ' . $requiredAuth);
                return false;
            }
            
            // Assign our key
            $this->generateOutput($functionName, 'Required scope found in array', 3);
            $token = $auth['token'];
        }
        
        $url = 'https://api.twitch.tv/kraken/channels/' . $chan;
        $updatedObjects = array();
        $options = array();
        
        $updatedObjects['oauth_token'] = $token;
        
        if ($title != null || ''){
            $this->generateOutput($functionName, 'New title added to array: ' . $title, 2);
            $updatedObjects['channel']['status'] = $title;
        } 
        
        if ($game  != null || ''){
            $this->generateOutput($functionName, 'New game added to array: ' . $game, 2);
            $updatedObjects['channel']['game'] = $game;
        } 
        
        if ($delay != null || ''){
            $this->generateOutput($functionName, 'New Stream Delay added to array: ' . $delay, 2);
            $updatedObjects['channel']['delay'] = $delay;
        } 
        
        $result = $this->cURL_put($url, $updatedObjects, $options, true);
        
        $this->generateOutput($functionName, 'Status return: ' . $result, 4);
        
        if (($result != 404) || ($result != 400)){
            $result = true;
        } else {
            $result = false;
        }
        
        // Clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($chan, $token, $code, $title, $game, $delay, $auth, $authSuccessful, $type, $url, $updatedObjects, $options, $functionName);        
        
        return $result;
    }
    
    /**
     * This resets the stream key for a user.  
     * Should only be used when absolutely neccesary.
     * 
     * @param $chan - [string] Channel name to reset the stream key for
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * 
     * @return $result - True on success, else false on failure.
     */ 
    public function resetStreamKey($chan, $token, $code){   
        $requiredAuth = 'channel_stream';
        $functionName = 'RESET_STREAM_KEY';
        
        $this->generateOutput($functionName, 'Resetting stream key for channel: ' . $chan, 1);
        
        // We were supplied an OAuth token. check it for validity and scopes
        if (($token != null || '') || ($code != null || false)){
            if ($token != null || ''){
                $check = $this->checkToken($token);
                
                if ($check["token"] != false){
                    $auth = $check;
                } else { // attempt to generate one
                    if ($code != null || ''){
                        $auth = $this->generateToken($code); // Assume generation and check later for failure
                    } else {
                        $this->generateError(400, 'Existing token expired and no code available for generation.');
                        return false;
                    }
                }
            } else { // Assume the code was given instead and generate if we can
                $auth = $this->generateToken($code); // Assume generation and check later for failure
            }
            
            // check to see if we recieved a token after all of that checking
            if ($auth['token'] == false) {
                $this->generateError(400, 'Auth key not returned, exiting function: ' . $functionName);
                
                return false; // return out after the error is passed
            }
            
            $authSuccessful = false;
            
            // Check the array of scopes
            foreach ($auth['scopes'] as $type){
                if ($type == $requiredAuth){
                    // We found the scope, we are good then
                    $authSuccessful = true;
                    break;
                }
            }
            
            // Did we fail?
            if (!$authSuccessful){
                $this->generateError(403, 'Authentication token failed to have permissions for ' . $functionName . '; required Auth: ' . $requiredAuth);
                return false;
            }
            
            // Assign our key
            $this->generateOutput($functionName, 'Required scope found in array', 3);
            $token = $auth['token'];
        }
        
        $url = 'https://api.twitch.tv/kraken/channels/' . $chan . '/stream_key';
        $options = array();
        $post = array('oauth_token' => $token);
        
        $result = $this->cURL_delete($url, $post, $options, true);
        
        $this->generateOutput($functionName, 'Status return: ' . $result, 3);
        
        if ($result == 204){
            $result = true;
        } else {
            $result = false;
        }
        
        //clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($chan, $token, $code, $auth, $authSuccessful, $type, $url, $options, $post, $functionName);
        
        return $result;
    }
    
    /**
     * This starts a commercial on the channel in question
     * 
     * @param $chan - [string] Channel name to start the commercial on
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * @param $length - [int] Length of time for the commercial break.  Valid options are 30,60,90.
     * 
     * @return $return - True on success, else false
     */ 
    public function startCommercial($chan, $token, $code, $length = 30){
        $functionName = 'START_COMMERCIAL';
        $requiredAuth = 'channel_commercial';
        
        $this->generateOutput($functionName, 'Starting commercial for channel: ' . $chan, 1);
        
        // We were supplied an OAuth token. check it for validity and scopes
        if (($token != null || '') || ($code != null || false)){
            if ($token != null || ''){
                $check = $this->checkToken($token);
                
                if ($check["token"] != false){
                    $auth = $check;
                } else { // attempt to generate one
                    if ($code != null || ''){
                        $auth = $this->generateToken($code); // Assume generation and check later for failure
                    } else {
                        $this->generateError(400, 'Existing token expired and no code available for generation.');
                        return false;
                    }
                }
            } else { // Assume the code was given instead and generate if we can
                $auth = $this->generateToken($code); // Assume generation and check later for failure
            }
            
            // check to see if we recieved a token after all of that checking
            if ($auth['token'] == false) {
                $this->generateError(400, 'Auth key not returned, exiting function: ' . $functionName);
                return false; // return out after the error is passed
            }
            
            $authSuccessful = false;
            
            // Check the array of scopes
            foreach ($auth['scopes'] as $type){
                if ($type == $requiredAuth){
                    // We found the scope, we are good then
                    $authSuccessful = true;
                    break;
                }
            }
            
            // Did we fail?
            if (!$authSuccessful){
                $this->generateError(403, 'Authentication token failed to have permissions for ' . $functionName . '; required Auth: ' . $requiredAuth);
                return false;
            }
            
            // Assign our key
            $this->generateOutput($functionName, 'Required scope found in array', 3);
            $token = $auth['token'];
        }
        
        $this->generateOutput($functionName, 'Commercial time recieved as: ' . $length, 2);
        
        // Check the length to see if it is valid
        if ($length % 30 == 0){
            $this->generateOutput($functionName, 'Commercial time invalid, set to 30 seconds', 2);
            $length = 30;
        }
        
        $url = 'https://api.twitch.tv/kraken/channels/' . $chan . '/commercial';
        $options = array();
        $post = array(
            'oauth_token' => $token,
            'length' => $length
        );
        
        $result = $this->cURL_post($url, $post, $options, true);
        
        $this->generateOutput($functionName, 'Raw return: ' . json_encode($result), 4);
        
        if ($result == 204){
            $this->generateOutput($functionName, 'Commercial successfully started', 3);
            $result = true;
        } else {
            $this->generateOutput($functionName, 'Commercial unable to be started', 3);
            $result = false;
        }
        
        //clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($chan, $token, $code, $length, $auth, $authSuccessful, $type, $url, $options, $post, $functionName);
        
        return $result;
    }
    
    /**
     * Grabs a list of all twitch emoticons
     * 
     * @param $limit - [int] The limit of objets to grab for the query
     * @param $offset - [int] the offest to start the query from
     * @param $returnTotal - [bool] Returns a _total row in the array
     * 
     * @return $object - [array] Keyed array of all returned data for the emoticins, including the supplied regex match used to parse it
     */ 
    public function chat_getEmoticonsGlobal($limit = -1, $offset = 0, $returnTotal = false){
        $functionName = 'GET_EMOTICONS_GLOBAL';
        $this->generateOutput($functionName, 'Grabbing global Twitch emoticons', 1);
        
        $url = 'https://api.twitch.tv/kraken/chat/emoticons';
        $options = array();
        $object = array();
        
        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
        
        $objects = $this->get_iterated($functionName, $url, $options, $limit, $offset, 'emoticons', null, null, null, null, null, null, null, null, null, $returningTotal);
        
        $this->generateOutput($functionName, 'Raw return: ' . json_encode($objects), 4);
        
        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            $this->generateOutput($functionName, 'Including _total as the count of all object', 3);
            $object['_total'] = count($objects);
        }

        $this->generateOutput($functionName, 'Setting Keys', 3);
        
        // Set keys
        foreach ($objects as $key => $row){
            if ($key == '_total'){
                $this->generateOutput($functionName, 'Setting key: ' . $key, 3);
                $object[$key] = $row;
                continue;
            }
            
            $k = $row['regex'];
            $object[$k] = $row;
        }
        
        // clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($limit, $offset, $url, $options, $functionName, $objects, $row, $k, $key);
        
        return $object;
    }
    
    /**
     * Grabs a list of call channel specific emoticons
     * 
     * @param $user - [string] username to grab emoticons for
     * @param $limit - [int] The limit of objects to grab for the query
     * @param $offest - [int] The offset to start the query from
     * @param $returnTotal - [bool] Returns a _total row in the array
     * 
     * @return $object - [array] Keyed array of all returned data for the emoticons
     */ 
    public function chat_getEmoticons($user, $limit = -1, $offset = 0, $returnTotal = false){
        $functionName = 'GET_EMOTICONS';
        $this->generateOutput($functionName, 'Grabbing emoticons for channel: ' . $user, 1);
        
        $url = 'https://api.twitch.tv/kraken/chat/' . $user . '/emoticons';
        $options = array();
        $object = array();
        
        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
        
        $objects = $this->get_iterated($functionName, $url, $options, $limit, $offset, 'emoticons', null, null, null, null, null, null, null, null, null, $returningTotal);
        
        $this->generateOutput($functionName, 'Raw return: ' . json_encode($objects), 4);
        
        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            $this->generateOutput($functionName, 'Including _total as the count of all object', 3);
            $object['_total'] = count($objects);
        }
        
        $this->generateOutput($functionName, 'Setting Keys', 3);
        
        // Set keys
        foreach ($objects as $key => $row){
            if ($key == '_total'){
                $this->generateOutput($functionName, 'Setting key: ' . $key, 3);
                $object[$key] = $row;
                continue;
            }
            
            $k = $row['regex'];
            $object[$k] = $row;
        }
        
        // clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($user, $limit, $offset, $functionName, $url, $options, $objects, $k, $row, $key);
        
        return $object;
    }

    /**
     * Grabs a list of call channel specific badges
     * 
     * @param $chan - [string] Channel name to grab badges for
     * @param $limit - [int] The limit of object to grab for the query
     * @param $offest - [int] The offset to start the query from
     * 
     * @return $object - [array] Keyed array of all returned data for the badges
     */     
    public function chat_getBadges($chan){        
        $functionName = 'GET_BADGES';
        
        $this->generateOutput($functionName, 'Grabbing badges for channel: ' . $chan, 1);
        
        $url = 'https://api.twitch.tv/kraken/chat/' . $chan . '/badges';
        $options = array();
        $get = array();
        
        $object = json_decode($this->cURL_get($url, $get, $options, false), true);
        
        $this->generateOutput($functionName, 'Raw return: ' . json_encode($object), 4);
        
        // clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($chan, $url, $options, $get, $functionName);        
        
        return $object;                
    }
    
    /**
     * Generates an OAuth token for chat login
     * 
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * 
     * @return $chatToken - [string] complete login token for chat login
     */
     public function chat_generateToken($token, $code){
        $functionName = 'CHAT_GENERATE_TOKEN';
        $requiredAuth = 'chat_login';
        $prefix = 'oauth:';
        
        $this->generateOutput($functionName, 'Generating chat login token', 1);
        
        // We were supplied an OAuth token. check it for validity and scopes
        if (($token != null || '') || ($code != null || false)){
            if ($token != null || ''){
                $check = $this->checkToken($token);
                
                if ($check["token"] != false){
                    $auth = $check;
                } else { // attempt to generate one
                    if ($code != null || ''){
                        $auth = $this->generateToken($code); // Assume generation and check later for failure
                    } else {
                        $this->generateError(400, 'Existing token expired and no code available for generation.');
                    }
                }
            } else { // Assume the code was given instead and generate if we can
                $auth = $this->generateToken($code); // Assume generation and check later for failure
            }
            
            // check to see if we recieved a token after all of that checking
            if ($auth['token'] == false) {
                $this->generateError(400, 'Auth key not returned, exiting function: ' . $functionName);
                return; // return out after the error is passed
            }
            
            $authSuccessful = false;
            
            // Check the array of scopes
            foreach ($auth['scopes'] as $type){
                if ($type == $requiredAuth){
                    // We found the scope, we are good then
                    $authSuccessful = true;
                    break;
                }
            }
            
            // Did we fail?
            if (!$authSuccessful){
                $this->generateError(403, 'Authentication token failed to have permissions for ' . $functionName . '; required Auth: ' . $requiredAuth);
                return null;
            }
            
            // Assign our key
            $this->generateOutput($functionName, 'Required scope found in array', 3);
            $token = $auth['token'];
        }
        
        $this->generateOutput($functionName, 'Token generated, concating prefix', 3);
        $chatToken = $prefix . $token;
        
        $this->generateOutput($functionName, 'Prefix added, login credential made: ' . $chatToken, 3);
        
        // clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($token, $auth, $authSuccessful, $code, $requiredAuth, $functionName, $type);        
        
        return $chatToken;                
     }
    
    /**
     * Gets a list of users that follow a given channel
     * 
     * @param $chan - [string] Channel name to get the followers for
     * @param $limit - [int] the limit of users
     * @param $offset - [int] The starting offset of the query
     * @param $sorting - [string] Sorting direction, valid options are 'asc' and 'desc'
     * @param $returnTotal - [bool] Returns a _total row in the array
     * 
     * @return $follows - [array] An unkeyed array of all followers to limit
     */ 
    public function getFollowers($chan, $limit = -1, $offset = 0, $sorting = 'desc', $returnTotal = false){
        $functionName = 'GET_FOLLOWERS';
        $this->generateOutput($functionName, 'Getting the list of channels followed by channel: ' . $chan, 1);
        
        $url = 'https://api.twitch.tv/kraken/channels/' . $chan . '/follows';
        $options = array();
        $followersObject = array();
        $followers = array();
        
        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
             
        $followersObject = $this->get_iterated($functionName, $url, $options, $limit, $offset, 'follows', null, null, null, null, null, null, null, null, null, $returningTotal);
        
        $this->generateOutput($functionName, 'Raw return: ' . json_encode($followersObject), 4);
        
        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            $this->generateOutput($functionName, 'Including _total as the count of all object', 3);
            $followers['_total'] = count($followersObject);
        }
        
        foreach ($followersObject as $k => $follower){
            if ($k == '_total'){
                $this->generateOutput($functionName, 'Setting key: ' . $k, 3);
                $followers[$k] = $follower;
                continue;
            }
            
            $key = $follower['user'][TWITCH_KEY_NAME];
            $followers[$key] = $follower;
            $this->generateOutput($functionName, 'Setting key: ' . $key, 3);
        }
        
        // Clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($chan, $limit, $offset, $sorting, $follower, $followersObject, $functionName, $key, $k);
        
        // Return out our array
        return $followers;
    }
    
    /**
     * Grab a lits of all channels a user follows
     * 
     * @param $username - [string] Username to get the follows of
     * @param $limit - [int] the limit of users
     * @param $offset - [int] The starting offset of the query
     * @param $sorting - [string] Sorting direction, valid options are 'asc' and 'desc'
     * @param $sortBy - [string] Sets the sort key.  Accepts 'created_at' and 'last_broadcast'
     * @param $returnTotal - [bool] Returns a _total row in the array
     * 
     * @return $channels - [array] An unkeyed array of all followed channels to limit
     */ 
    public function getFollows($username, $limit = -1, $offset = 0, $sorting = 'desc', $sortBy = 'created_at', $returnTotal = false){
        $functionName = 'GET_FOLLOWS';
        $this->generateOutput($functionName, 'Getting the list of channels following channel: ' . $username, 1);
        
        // Init some vars       
        $channels = array();
        $url = 'https://api.twitch.tv/kraken/users/' . $username . '/follows/channels';
        $options = array();
        
        // Chck our sortby option
        $sortBy = ($sortBy == 'last_broadcast') ? $sortBy : 'created_at';
        
        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
            
        // Build our cURL query and store the array
        $channelsObject = $this->get_iterated($functionName, $url, $options, $limit, $offset, 'follows', null, null, null, null, null, null, null, null, null, $returningTotal, $sortBy);
        
        $this->generateOutput($functionName, 'Raw return: ' . json_encode($channelsObject), 4);
        
        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            $this->generateOutput($functionName, 'Including _total as the count of all object', 3);
            $channels['_total'] = count($channelsObject);
        }
        
        foreach ($channelsObject as $k => $channel){
            if ($k == '_total'){
                $this->generateOutput($functionName, 'Setting key: ' . $k, 3);
                $channels[$k] = $channel;
                continue;
            }
            
            $key = $channel['channel'][TWITCH_KEY_NAME];
            $channels[$key] = $channel;
            $this->generateOutput($functionName, 'Setting key: ' . $key, 3);
        }
        
        // Clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($username, $limit, $offset, $sorting, $channelsObject, $channel, $url, $options, $key, $functionName, $k, $sortBy);
        
        // Return out our unkeyed array
        return $channels;        
    }
    
    /**
     * Checks To see if the provided user is currently following the channel
     * 
     * @param $targetChannel - [string] The target channel to check the relationship against
     * @param $user          - [string] The user to check the relationship for
     * 
     * @return $following - [mixed] False if the user is not following or the user object if the user is
     */ 
    public function checkUserFollowsChannel($targetChannel, $user){
        $targetChannel = strval($targetChannel);
        $user          = strval($user);
        
        $functionName = 'CHECK_USER_FOLLOWS_CHANNEL';
        $this->generateOutput($functionName, "Checking to see if [$user] is following channel [$targetChannel]", 1);
        
        // Init some vars
        $url = "https://api.twitch.tv/kraken/users/$user/follows/channels/$targetChannel";
            
        // Build our cURL query and store the array
        $relationShipObject = $this->cURL_get($url);
        
        $this->generateOutput($functionName, 'Raw return: ' . json_encode($relationShipObject), 4);
        
        // If the user was not found or is not following, return false
        if (isset($relationShipObject['status']) && ($relationShipObject['status'] == 404)) {
            return false;
        }
        
        // Clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($targetChannel, $user, $functionName, $url);
        
        // Return out our unkeyed array
        return $relationShipObject;        
    }
    
    /**
     * Adds a channel to a user's following list
     * 
     * @param $user - [string] Username of the account to add the channel to
     * @param $chan - [string] Channel name that the user will have added to their list
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * 
     * @return $success - [bool] Success of the query
     */ 
    public function followChan($user, $chan, $token, $code){
        $functionName = 'FOLLOW_CHANNEL';
        $requiredAuth = 'user_follows_edit';
        
        $this->generateOutput($functionName, 'Attempting to have channel ' . $user . ' follow the user ' . $chan, 1);      
        
        // We were supplied an OAuth token. check it for validity and scopes
        if (($token != null || '') || ($code != null || false)){
            if ($token != null || ''){
                $check = $this->checkToken($token);
                
                if ($check["token"] != false){
                    $auth = $check;
                } else { // attempt to generate one
                    if ($code != null || ''){
                        $auth = $this->generateToken($code); // Assume generation and check later for failure
                    } else {
                        $this->generateError(400, 'Existing token expired and no code available for generation.');
                    }
                }
            } else { // Assume the code was given instead and generate if we can
                $auth = $this->generateToken($code); // Assume generation and check later for failure
            }
            
            // check to see if we recieved a token after all of that checking
            if ($auth['token'] == false) {
                $this->generateError(400, 'Auth key not returned, exiting function: ' . $functionName);
                return; // return out after the error is passed
            }
            
            $authSuccessful = false;
            
            // Check the array of scopes
            foreach ($auth['scopes'] as $type){
                if ($type == $requiredAuth){
                    // We found the scope, we are good then
                    $authSuccessful = true;
                    break;
                }
            }
            
            // Did we fail?
            if (!$authSuccessful){
                $this->generateError(403, 'Authentication token failed to have permissions for ' . $functionName . '; required Auth: ' . $requiredAuth);
                return null;
            }
            
            // Assign our key
            $this->generateOutput($functionName, 'Required scope found in array', 3);
            $token = $auth['token'];
        }
        
        $url = 'https://api.twitch.tv/kraken/users/' . $user . '/follows/channels/' . $chan;
        $options = array();
        $post = array('oauth_token' => $token);
        
        $result = $this->cURL_put($url, $post, $options, true);
        
        $this->generateOutput($functionName, 'Raw return: ' . $result, 4);
        
        if ($result == 200){
            $this->generateOutput($functionName, 'Sucessfully followed channel.', 3);
            $result = true;              
        } else {
            $this->generateOutput($functionName, 'Unable to follow channel.  Channel not found', 3);
            $result = false;            
        }

        // Clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($user, $chan, $token, $code, $auth, $authSuccessful, $type, $url, $options, $post, $functionName);
        
        return $result;
    }
    
    /**
     * Removes a channel from a user's follow list
     * 
     * @param $user - [string] Username of the account to add the channel to
     * @param $chan - [string] Channel name that the user will have added to their list
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * 
     * @return $success - [bool] Success of the query
     */ 
    public function unfollowChan($user, $chan, $token, $code){
        $functionName = 'UNFOLLOW_CHANNEL';
        $requiredAuth = 'user_follows_edit';
        
        $this->generateOutput($functionName, 'Attempting have channel ' . $user . ' unfollow channel ' . $chan, 1);
        
        // We were supplied an OAuth token. check it for validity and scopes
        if (($token != null || '') || ($code != null || false)){
            if ($token != null || ''){
                $check = $this->checkToken($token);
                
                if ($check["token"] != false){
                    $auth = $check;
                } else { // attempt to generate one
                    if ($code != null || ''){
                        $auth = $this->generateToken($code); // Assume generation and check later for failure
                    } else {
                        $this->generateError(400, 'Existing token expired and no code available for generation.');
                    }
                }
            } else { // Assume the code was given instead and generate if we can
                $auth = $this->generateToken($code); // Assume generation and check later for failure
            }
            
            // check to see if we recieved a token after all of that checking
            if ($auth['token'] == false) {
                $this->generateError(400, 'Auth key not returned, exiting function: ' . $functionName);
                return; // return out after the error is passed
            }
            
            $authSuccessful = false;
            
            // Check the array of scopes
            foreach ($auth['scopes'] as $type){
                if ($type == $requiredAuth){
                    // We found the scope, we are good then
                    $authSuccessful = true;
                    break;
                }
            }
            
            // Did we fail?
            if (!$authSuccessful){
                $this->generateError(403, 'Authentication token failed to have permissions for ' . $functionName . '; required Auth: ' . $requiredAuth);
                return null;
            }
            
            // Assign our key
            $this->generateOutput($functionName, 'Required scope found in array', 3);
            $token = $auth['token'];
        }
        
        $url = 'https://api.twitch.tv/kraken/users/' . $user . '/follows/channels/' . $chan;
        $options = array();
        $delete = array('oauth_token' => $token);
        
        $result = $this->cURL_delete($url, $delete, $options, true);
        
        $this->generateOutput($functionName, 'Raw return: ' . $result, 4);
        
        // Clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($user, $chan, $token, $code, $auth, $authSuccessful, $type, $url, $options, $delete);
        
        if ($result == 204){
            $this->generateOutput($functionName, 'Successfully unfollowed channel', 3);
            unset($functionName);
            return true;
        } else {
            $this->generateOutput($functionName, 'Unsuccessfully unfollowed channel', 3);
            unset($functionName);
            return false;
        }
    }
    
    /**
     * Grabs a list of most popular games being streamed on twitch
     * 
     * @param $limit - [int] Set the limit of objects to grab
     * @param $offset - [int] Sets the initial offset to start the query from
     * @param $hls - [bool] Sets the query only to grab streams using HLS
     * @param $returnTotal - [bool] Sets iteration to not ignore the _total key
     * 
     * @return $object - [array] A complete array of all channel objects in order based on the sorting rules
     */ 
    public function getLargestGame($limit = -1, $offset = 0, $hls = false, $returnTotal = false){
        $functionName = 'GET_LARGEST_GAME';
        
        $this->generateOutput($functionName, 'Attempting to get a list of the channels currently live to limit sorted by viewer count', 1);
        
        // Init some vars       
        $gamesObject = array();
        $games = array();        
        $url = 'https://api.twitch.tv/kraken/games/top';
        $options = array();
        
        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
        
        $gamesObject = $this->get_iterated($functionName, $url, $options, $limit, $offset, 'top', null, $hls, null, null, null, null, null, null, null, $returningTotal);
        
        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            $this->generateOutput($functionName, 'Including _total as the count of all object', 3);
            $games['_total'] = count($gamesObject);
        }
        
        // Strip out only the usernames from our array set
        foreach ($gamesObject as $k => $game){
            if ($k == '_total'){
                // It isn't really the user, but this stops code changes
                $games[$k] = $game;
                continue;
            }
            
            $key = $game['game']['name'];
            $games[$key] = $game;
            $this->generateOutput($functionName, 'Setting key: ' . $key, 3);
        }
        
        // Clean up quickly
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($limit, $offset, $hls, $url, $options, $gamesObject, $key, $game, $functionName, $k, $returnTotal, $returningTotal);
        
        return $games;
    }
    
    /**
     * Grabs All currently registered Ingest servers and some base stats
     * 
     * @return $ingests - [array] All returned ingest servers and the information associated with them
     */
    public function getIngests(){
        $functionName = 'GET_INGESTS';
        
        $this->generateOutput($functionName, 'Getting current ingests and ingest statistics for Twitch', 1);
        
        $ingests = array();
        $url = 'https://api.twitch.tv/kraken/ingests';
        $get = array();
        $options = array();
        
        $result = json_decode($this->cURL_get($url, $get, $options), true);
        $this->generateOutput($functionName, 'Raw return: ' . json_encode($result), 4);
        
        if (is_array($result) && !empty($result)){
            foreach ($result as $key => $value){
                if ($key == '_links'){
                    continue;
                }
                
                foreach ($value as $val){
                    $k = $val['name'];
                    $this->generateOutput($functionName, 'Setting Key: ' . $k, 3);
                    $ingests[$k] = $val;
                }
            }
        }
        
        // Clean up quickly
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($url, $get, $options, $key, $value, $val, $functionName, $result, $k);
        
        return $ingests;        
    }        
    
    /**
     * Returns an array of all streamers streaming in the supplied game catagory
     * 
     * @param $query - [string] A string parameter to search for
     * @param $live - [bool] Sets the query to search only for live channels
     * 
     * @return $object - [array] An array of all resulting search returns
     */ 
    public function searchGameCat($query, $live = true){
        $functionName = 'SEARCH_GAME';
        $this->generateOutput($functionName, 'Searching all game catagories for the string: ' . $query, 1);
        
        $url = 'https://api.twitch.tv/kraken/search/games';
        $get = array(
            'query' => $query,
            'type' => 'suggest',
            'live' => $live);
        $options = array();
        $result = array();
        $object = array();
        
        $result = json_decode($this->cURL_get($url, $get, $options, false), true);
        $this->generateOutput($functionName, 'Raw return: ' . json_encode($result), 4);
        
        foreach ($result as $key => $value){
            if ($key !== '_links'){
                foreach ($value as $game){
                    $k = $game['name'];
                    if ($k != 'h'){
                        $this->generateOutput($functionName, 'Setting key: ' . $k, 3);
                        $object[$k] = $game;
                    }
                }                
            }
        }
        
        // Clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($query, $live, $url, $get, $options, $result, $k, $key, $value, $game, $functionName);
    
        return $object;
    }
    
    /**
     * Grabs the stream object of a given channel
     * 
     * @param $chan - [string] Channel name to get the stream object for
     * 
     * @return $object - [array or null] Returned array of all stream object data or null if stream is offline
     */ 
    public function getStreamObject($chan){
        $functionName = 'GET_STREAM_OBJECT';
        
        $this->generateOutput($functionName, 'Getting the stream object for channel ' . $chan, 1);
        
        $url = 'https://api.twitch.tv/kraken/streams/' . $chan;
        $get = array();
        $options = array();
        
        $result = json_decode($this->cURL_get($url, $get, $options, false), true);
        
        if ($result['stream'] != null){
            $object = $result['stream'];
        } else {
            $object = null;
        }
        
        // Clean up
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($chan, $url, $get, $result, $functionName);
        
        return $object;
    }
    
    /**
     * Gets the stream object of multiple channels and credentials
     * All Params are optional or have default values
     * 
     * @param $game - [string] Limit returns to a specific game
     * @param $channels - [array] Limit search to a specific set of channels
     * @param $limit - [int] Limit of channel objects to return
     * @param $offset - [int] Maximum number of objects to return
     * @param $embedable - [bool] Limit search to only embedable channels
     * @param $hls - [bool] Limit sear to channels only using hls
     * @param $client_id - [string] Limit searches to only show streams from the applications of the supplied ID
     * @param $returnTotal - [bool] Returns a _total row in the array
     * 
     * @return $object - [array] All returned data for the query parameters
     */ 
    public function getStreamsObjects($game = null, $channels = array(), $limit = -1, $offset = 0, $embedable = false, $hls = false, $client_id = null, $returnTotal = false){
        $functionName = 'GET_STREAM_OBJECTS';
        $this->generateOutput($functionName, 'Attempting to get stream objects for the provided parameters', 1);
        
        // Init some vars       
        $url = 'https://api.twitch.tv/kraken/streams';
        $options = array();
        $streamsObject = array();
        $streams = array();
        
        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
        
        // Build our cURL query and store the array
        $streamsObject = $this->get_iterated($functionName, $url, $options, $limit, $offset, 'streams', null, $hls, null, $channels, $embedable, $client_id, null, null, $game, $returningTotal);
        
        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            $this->generateOutput($functionName, 'Including _total as the count of all object', 3);
            $streams['_total'] = count($streamsObject);
        }
        
        // Strip out the data we don't need
        foreach ($streamsObject as $key => $value) {
            if ($key == '_total') {
                // It isn't really the user, but this stops code changes
                $streams[$key] = $value;
                continue;
            }
            
            foreach ($value as $k => $v) {
                if ($k == 'channel') {
                    $objKey = $v[TWITCH_KEY_NAME];
                    $streams[$objKey] = $value;
                    break;
                }
            }
        }
        
        // Clean up quickly
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($game, $channels, $limit, $offset, $embedable, $hls, $client_id, $url, $options, $streamsObject, $key, $k, $value, $v, $objKey, $functionName, $returnTotal, $returningTotal);
        
        return $streams;              
    }
    
    /**
     * Grabs a list of all featured streamers objects
     * 
     * @param $limit - [int] Limit of channel objects to return
     * @param $offset - [int] Maximum number of objects to return
     * @param $hls - [bool] Limit sear to channels only using hls
     * @param $returnTotal - [bool] Returns a _total row in the array
     * 
     * @return $featuredObject - [array] Array of all stream objects for the query or false if the query fails
     */ 
    public function getFeaturedStreams($limit = -1, $offset = 0, $hls = false, $returnTotal = false){
        $functionName = 'GET_FEATURED';
        
        $this->generateOutput($functionName, 'Getting all featured streamers to limit', 1);
        
        // Init some vars
        $featured = array();          
        $url = 'https://api.twitch.tv/kraken/streams/featured';
        $options = array();
        
        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
        
        // Build our cURL query and store the array
        $featuredObject = $this->get_iterated($functionName, $url, $options, $limit, $offset, 'featured', null, null, null, null, null, null, null, null, null, $returningTotal);
        
        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            $this->generateOutput($functionName, 'Including _total as the count of all object', 3);
            $featured['_total'] = count($featuredObject);
        }
        
        // Strip out the uneeded data
        foreach ($featuredObject as $key => $value){
            if ($key == '_total'){
                // It isn't really the user, but this stops code changes
                $featured[$key] = $value;
                continue;
            }
            
            if (($key != 'self') && ($key != 'next')){
                $k = $value['stream']['channel'][TWITCH_KEY_NAME];
                $featured[$k] = $value;
            }
        }
        
        // Clean up quickly
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($limit, $offset, $embedable, $hls, $url, $options, $featuredObject, $key, $value, $k, $functionName, $returnTotal, $returningTotal);
        
        return $featured;           
    }
    
    /**
     * Grabs the list of online channels that a user is following
     * 
     * @param $limit - [int] Limit of channel objects to return
     * @param $offset - [int] Maximum number of objects to return
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * @param $hls - [bool] Limit sear to channels only using hls
     * @param $returnTotal - [bool] Returns a _total row in the array
     * 
     * @return $videos - [array] array of all followed streams online
     */
    public function getFollowedStreams($limit = -1, $offset = 0, $token, $code, $hls = false, $returnTotal = false){
        $functionName = 'STREAMS_FOLLOWED';
        $requiredAuth = 'user_read';
        
        $this->generateOutput($functionName, 'Attempting to grab all live channels for auth code: ' . $code, 1);
        
        // We were supplied an OAuth token. check it for validity and scopes
        if (($token != null || '') || ($code != null || false)){
            if ($token != null || ''){
                $check = $this->checkToken($token);
                
                if ($check["token"] != false){
                    $auth = $check;
                } else { // attempt to generate one
                    if ($code != null || ''){
                        $auth = $this->generateToken($code); // Assume generation and check later for failure
                    } else {
                        $this->generateError(400, 'Existing token expired and no code available for generation.');
                    }
                }
            } else { // Assume the code was given instead and generate if we can
                $auth = $this->generateToken($code); // Assume generation and check later for failure
            }
            
            // check to see if we recieved a token after all of that checking
            if ($auth['token'] == false) {
                $this->generateError(400, 'Auth key not returned, exiting function: ' . $functionName);
                return array(); // return out after the error is passed
            }
            
            $authSuccessful = false;
            
            // Check the array of scopes
            foreach ($auth['scopes'] as $type){
                if ($type == $requiredAuth){
                    // We found the scope, we are good then
                    $authSuccessful = true;
                    break;
                }
            }
            
            // Did we fail?
            if (!$authSuccessful){
                $this->generateError(403, 'Authentication token failed to have permissions for ' . $functionName . '; required Auth: ' . $requiredAuth);
                return array();
            }
            
            // Assign our key
            $this->generateOutput($functionName, 'Required scope found in array', 3);
            $token = $auth['token'];
        }
        
        $streams = array();
        $url = 'https://api.twitch.tv/kraken/streams/followed';
        $options = array();
        
        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
        
        $streamsObject = $this->get_iterated($functionName, $url, $options, $limit, $offset, 'streams', $token, $hls, null, null, null, null, null, null, null, $returningTotal);

        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            $this->generateOutput($functionName, 'Including _total as the count of all object', 3);
            $streams['_total'] = count($streamsObject);
        }
        
        // Strip out the uneeded data
        foreach ($streamsObject as $key => $value){
            if ($key == '_total'){
                // It isn't really the user, but this stops code changes
                $streams[$key] = $value;
                continue;
            }
            
            if (($key != 'self') && ($key != 'next')){
                $k = $value['channel'][TWITCH_KEY_NAME];
                $this->generateOutput($functionName, 'Setting key: ' . $k, 3);
                $streams[$k] = $value;
            }
        }
        
        // Clean up quickly
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($limit, $offset, $token, $auth, $authSuccessful, $hls, $code, $returnTotal, $requiredAuth, $returningTotal, $k, $key, $value, $url, $options);
        
        return $streams;
     }
    
    /**
     * Gets the current viewers and the current live channels for Twitch
     * 
     * @param $hls - [bool] Limit sear to channels only using hls
     * 
     * @return $statistics - [array] (keyed) The current Twitch Statistics 
     */ 
    public function getTwitchStatistics($hls = false){
        $functionName = 'GET_STATISTICS';
        
        $this->generateOutput($functionName, 'Getting current statistics for Twitch', 1);
        
        $statistics = array();
        $url = 'https://api.twitch.tv/kraken/streams/summary';
        $get = array(
            'hls' => $hls);
        $options = array();
        
        $result = json_decode($this->cURL_get($url, $get, $options), true);
        $this->generateOutput($functionName, 'Raw return: ' . json_encode($result), 4);
        
        if (is_array($result) && !empty($result)){
            $this->generateOutput($functionName, 'Statistics transfered', 3);
            $statistics = $result;
        }

        // Clean up quickly
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($hls, $url, $get, $options, $key, $value, $functionName, $result);
        
        return $statistics;        
    }
    
    /**
     * Returns the video object for the specified ID
     * 
     * @param $id - [string] String ID of the video to get
     * 
     * @return $object - [array] Video object returned from the query, key is the ID
     */
    public function getVideo_ID($id){
        $functionName = 'GET_VIDEO-ID';
        
        $this->generateOutput($functionName, 'Getting the video object for the video with the ID: ' . $id, 1);
        
        // init some vars
        $object = array();
        $url = 'https://api.twitch.tv/kraken/videos/' . $id;
        $get = array();
        $options = array();
        
        $result = json_decode($this->cURL_get($url, $get, $options, false), true);
        
        // A safe way of checking that the video was returned
        if (!empty($result) && array_key_exists('_id', $result)){
            // Set the key and the array
            $object[$id] = $result;            
        } else {
            $object[$id] = array();
        }

        // Clean up quickly
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($id, $functionName, $url, $get, $options, $result);
        
        return $object;             
    }
    
    /**
     * Returns the video objects of the given channel
     * 
     * @param $chan - [string] Channel name to grab video objects from
     * @param $limit - [int] Limit of channel objects to return
     * @param $offset - [int] Maximum number of objects to return
     * @param $boradcastsOnly - [bool] If true, limits query to only past broadcasts, else will return highlights only
     * @param $returnTotal - [bool] Returns a _total row in the array
     * 
     * @return $videoObjects - [array] array of all returned video objects, Key is ID
     * 
     * @version 1.2
     */ 
    public function getVideo_channel($chan, $limit = -1, $offset = 0, $boradcastsOnly = false, $returnTotal = false){
        $functionName = 'GET_VIDEO-CHANNEL';
        
        $this->generateOutput($functionName, 'Getting the video objects for channel: ' . $chan, 1);
        
        // Init some vars
        $videoObjects = array();     
        $videos = array();
        $options = array();
        $url = 'https://api.twitch.tv/kraken/channels/' . $chan . '/videos';
        
        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
            
        // Build our cURL query and store the array
        $videos = $this->get_iterated($functionName, $url, $options, $limit, $offset, 'videos', null, null, null, null, null, null, $boradcastsOnly, null, null, $returningTotal);
        
        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            $this->generateOutput($functionName, 'Including _total as the count of all object', 3);
            $videoObjects['_total'] = count($videos);
        }
        
        // Key the data
        foreach ($videos as $k => $video){
            if ($k == '_total'){
                $this->generateOutput($functionName, 'Setting key: ' . $k, 3);
                $videoObjects[$k] = $video;
                continue;
            }
            
            $key = $video['_id'];
            $videoObjects[$key] = $video;
            $this->generateOutput($functionName, 'Setting key: ' . $key, 3);
        }
        
        // Clean up quickly
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($chan, $limit, $offset, $boradcastsOnly, $functionName, $video, $videos, $key, $options, $url, $k, $returnTotal, $returningTotal);
        
        return $videoObjects;                  
    }
    
    /**
     * Grabs all videos for all channels a user is following
     * 
     * @param $limit - [int] Limit of channel objects to return
     * @param $offset - [int] Maximum number of objects to return
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * @param $returnTotal - [bool] Returns a _total row in the array
     * 
     * @return $videosObject - [array] All video objects returned by the query, Key is ID
     */ 
    public function getVideo_followed($limit = -1, $offset = 0, $token, $code, $returnTotal = false){
        $requiredAuth = 'user_read';
        $functionName = 'GET_VIDEO-FOLLOWED';
        
        $this->generateOutput($functionName, 'Grabbing all video objects for the channels using the code: ' . $code, 1);
        
        // We were supplied an OAuth token. check it for validity and scopes
        if (($token != null || '') || ($code != null || false)){
            if ($token != null || ''){
                $check = $this->checkToken($token);
                
                if ($check["token"] != false){
                    $auth = $check;
                } else { // attempt to generate one
                    if ($code != null || ''){
                        $auth = $this->generateToken($code); // Assume generation and check later for failure
                    } else {
                        $this->generateError(400, 'Existing token expired and no code available for generation.');
                    }
                }
            } else { // Assume the code was given instead and generate if we can
                $auth = $this->generateToken($code); // Assume generation and check later for failure
            }
            
            // check to see if we recieved a token after all of that checking
            if ($auth['token'] == false) {
                $this->generateError(400, 'Auth key not returned, exiting function: ' . $functionName);
                return; // return out after the error is passed
            }
            
            $authSuccessful = false;
            
            // Check the array of scopes
            foreach ($auth['scopes'] as $type) {
                if ($type == $requiredAuth) {
                    // We found the scope, we are good then
                    $authSuccessful = true;
                    break;
                }
            }
            
            // Did we fail?
            if (!$authSuccessful) {
                $this->generateError(403, 'Authentication token failed to have permissions for ' . $functionName . '; required Auth: ' . $requiredAuth);
                return null;
            }
            
            // Assign our key
            $this->generateOutput($functionName, 'Required scope found in array', 3);
            $token = $auth['token'];
        }
        
        // Init some vars       
        $videosObject = array();            
        $videos = array();
        $url = 'https://api.twitch.tv/kraken/videos/followed';
        $options = array();
        
        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
        
        // Build our cURL query and store the array
        $videos = $this->get_iterated($functionName, $url, $options, $limit, $offset, 'videos', $token, null, null, null, null, null, null, null, null, $returningTotal);
        
        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)) {
            $this->generateOutput($functionName, 'Including _total as the count of all object', 3);
            $videosObject['_total'] = count($videos);
        }
        
        // Set our keys
        foreach ($videos as $k => $video) {
            if ($k == '_total') {
                $this->generateOutput($functionName, 'Setting key: ' . $k, 3);
                $videosObject[$k] = $video;
                continue;
            }
            
            $key = $video['_id'];
            $videosObject[$key] = $video;
            $this->generateOutput($functionName, 'Setting key: ' . $key, 3);
        }

        // Clean up quickly
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($limit, $offset, $token, $code, $requiredAuth, $functionName, $auth, $authSuccessful, $token, $type, $videos, $video, $url, $options, $key, $k, $returnTotal, $returningTotal);
        
        return $videosObject;      
    }
    
    /**
     * Gets a list of the top viewed videos by the sorting parameters
     * 
     * @param $game - [string] Game name to sory the query by
     * @param $limit - [int] Limit of channel objects to return
     * @param $offset - [int] Maximum number of objects to return
     * @param $period - [string] set the period for the query, valid values are 'week', 'month', 'all'
     * @param $returnTotal - [bool] Returns a _total row in the array
     * 
     * @return $videosObject - [array] Array of all returned video objects, Key is ID
     */ 
    public function getTopVideos($game = '', $limit = -1, $offset = 0, $period = 'week', $returnTotal = false){
        $functionName = 'GET_TOP_VIDEOS';
        $this->generateOutput($functionName, 'Grabbing all of the top videos to limit', 1);
        
        // check the period to make sure it is valid
        if (($period != 'week') && ($period != 'month') && ($period != 'all')){
            $period = 'week';
        }
        
        // Init some vars       
        $videosObject = array();
        $videos = array();
        $url = 'https://api.twitch.tv/kraken/videos/top';
        $options = array();

        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
            
        // Build our cURL query and store the array
        $videos = $this->get_iterated($functionName, $url, $options, $limit, $offset, 'videos', null, null, null, null, null, null, null, $period, $game, $returningTotal);

        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            $this->generateOutput($functionName, 'Including _total as the count of all object', 3);
            $videosObject['_total'] = count($videos);
        }
        
        // Set our keys
        foreach ($videos as $k => $video){
            if ($k == '_total'){
                $this->generateOutput($functionName, 'Setting key: ' . $k, 3);
                $videosObject[$k] = $video;
                continue;
            }
            
            $key = $video['_id'];
            $videosObject[$key] = $video;
            $this->generateOutput($functionName, 'Setting key: ' . $key, 3);
        }
        
        // Clean up quickly
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($game, $limit, $offset, $period, $functionName, $video, $videos, $key, $url, $options, $k, $returnTotal, $returningTotal);
        
        return $videosObject;         
    }
    
    /**
     * Gets a lits of all users subscribed to a channel
     * 
     * @param $chan - [string] Channel name to grab the subscribers list of
     * @param $limit - [int] Limit of channel objects to return
     * @param $offset - [int] Maximum number of objects to return
     * @param $direction - [string] Sorting direction, valid options are 'asc' and 'desc'
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * @param $returnTotal - [bool] Returns a _total row in the array
     * 
     * @return $subscribers - [array] Unkeyed array of all subscribed users
     */ 
    public function getChannelSubscribers($chan, $limit = -1, $offset = 0, $direction = 'asc', $token, $code, $returnTotal = false){
        $requiredAuth = 'channel_subscriptions';
        $functionName = 'GET_SUBSCRIBERS';
        
        $this->generateOutput($functionName, 'Getting the list of subcribers to channel: ' . $chan, 1);      
        
        // We were supplied an OAuth token. check it for validity and scopes
        if (($token != null || '') || ($code != null || false)){
            if ($token != null || ''){
                $check = $this->checkToken($token);
                
                if ($check["token"] != false){
                    $auth = $check;
                } else { // attempt to generate one
                    if ($code != null || ''){
                        $auth = $this->generateToken($code); // Assume generation and check later for failure
                    } else {
                        $this->generateError(400, 'Existing token expired and no code available for generation.');
                    }
                }
            } else { // Assume the code was given instead and generate if we can
                $auth = $this->generateToken($code); // Assume generation and check later for failure
            }
            
            // check to see if we recieved a token after all of that checking
            if ($auth['token'] == false) {
                $this->generateError(400, 'Auth key not returned, exiting function: ' . $functionName);
                return; // return out after the error is passed
            }
            
            $authSuccessful = false;
            
            // Check the array of scopes
            foreach ($auth['scopes'] as $type){
                if ($type == $requiredAuth){
                    // We found the scope, we are good then
                    $authSuccessful = true;
                    break;
                }
            }
            
            // Did we fail?
            if (!$authSuccessful){
                $this->generateError(403, 'Authentication token failed to have permissions for ' . $functionName . '; required Auth: ' . $requiredAuth);
                return null;
            }
            
            // Assign our key
            $this->generateOutput($functionName, 'Required scope found in array', 3);
            $token = $auth['token'];
        }
        
        // Check our sorting direction
        if (($direction != 'asc') && ($direction != 'desc')){
            $direction = 'asc';
        }

        // Init some vars       
        $subscribers = array();
        $subscribersObject = array();
        $url = 'https://api.twitch.tv/kraken/channels/' . $chan . '/subscriptions';
        $options = array();
        
        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
        
        // Build our cURL query and store the array
        $subscribersObject = $this->get_iterated($functionName, $url, $options, $limit, $offset, 'subscriptions', $token, null, $direction, null, null, null, null, null, null, $returningTotal);

        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            $this->generateOutput($functionName, 'Including _total as the count of all object', 3);
            $subscribers['_total'] = count($subscribersObject);
        }
        
        // Set the keys and array
        foreach ($subscribersObject as $k => $subscriber){
            if ($k == '_total'){
                $subscribers[$k] = $subscriber;
                continue;
            }
            
            $key = $subscriber['user'][TWITCH_KEY_NAME];
            $subscribers[$key] = $subscriber;
        }
        
        // Clean up quickly
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($chan, $limit, $offset, $direction, $token, $code, $requiredAuth, $token, $auth, $authSuccessful, $type, $subscriber, $subscribersObject, $key, $url, $options, $k, $returnTotal, $returningTotal);
        
        return $subscribers;
    }
    
    /**
     * Checks to see if a user is subscribed to a specified channel from the channel side
     * 
     * @param $user - [string] Username of the user check against
     * @param $chan - [string] Channel name of the channel to check against
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * 
     * @return $subscribed - [bool] the status of the user subscription
     */ 
    public function checkChannelSubscription($user, $chan, $token, $code){
        $requiredAuth = 'channel_subscriptions';
        $functionName = 'CHECK_CHANNEL_SUBSCRIPTION';
        
        $this->generateOutput($functionName, 'Checking to see if user ' . $user . ' is subscribed to channel ' . $chan, 1);
        
        // We were supplied an OAuth token. check it for validity and scopes
        if (($token != null || '') || ($code != null || false)){
            if ($token != null || ''){
                $check = $this->checkToken($token);
                
                if ($check["token"] != false){
                    $auth = $check;
                } else { // attempt to generate one
                    if ($code != null || ''){
                        $auth = $this->generateToken($code); // Assume generation and check later for failure
                    } else {
                        $this->generateError(400, 'Existing token expired and no code available for generation.');
                    }
                }
            } else { // Assume the code was given instead and generate if we can
                $auth = $this->generateToken($code); // Assume generation and check later for failure
            }
            
            // check to see if we recieved a token after all of that checking
            if ($auth['token'] == false) {
                $this->generateError(400, 'Auth key not returned, exiting function: ' . $functionName);
                return; // return out after the error is passed
            }
            
            $authSuccessful = false;
            
            // Check the array of scopes
            foreach ($auth['scopes'] as $type){
                if ($type == $requiredAuth){
                    // We found the scope, we are good then
                    $authSuccessful = true;
                    break;
                }
            }
            
            // Did we fail?
            if (!$authSuccessful){
                $this->generateError(403, 'Authentication token failed to have permissions for ' . $functionName . '; required Auth: ' . $requiredAuth);
                return null;
            }
            
            // Assign our key
            $this->generateOutput($functionName, 'Required scope found in array', 3);
            $token = $auth['token'];
        }
        
        $url = 'https://api.twitch.tv/kraken/channels/' . $chan . '/subscriptions/' . $user;
        $options = array();
        $get = array('oauth_token' => $token);
        
        // Build our cURL query and store the array
        $subscribed = json_decode($this->cURL_get($url, $get, $options, true), true);
        
        // Check the return
        if ($subscribed == 403){
            $this->generateOutput($functionName, 'Authentication failed to have access to channel account.  Please check channel ' . $chan . ' access.', 3);
            $subscribed = false;
        } elseif ($subscribed == 422) {
            $this->generateOutput($functionName, 'Channel ' . $chan . ' does not have subscription program available', 3);
            $subscribed = false;
        } elseif ($subscribed == 404) {
            $this->generateOutput($functionName, 'User ' . $user . ' is not subscribed to channel ' . $chan, 3);
            $subscribed = false;
        } else {
            $this->generateOutput($functionName, 'User ' . $user . ' is subscribed to channel' . $chan, 3);
            $subscribed = true;
        }
        
        // Clean up quickly
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($user, $chan, $token, $code, $requiredAuth, $functionName, $auth, $authSuccessful, $token, $type, $url, $options, $get);
        
        return $subscribed;
    }
    
    /**
     * Checks to see if a user is subscribed to a specified channel from the user side
     * 
     * @param $user - [string] Username of the user check against
     * @param $chan - [string] Channel name of the channel to check against
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * 
     * @return $subscribed - [bool] the status of the user subscription
     */ 
    public function checkUserSubscription($user, $chan, $token, $code){
        $requiredAuth = 'channel_check_subscription';
        $functionName = 'CHECK_USER_SUBSCRIPTION';
        
        $this->generateOutput($functionName, 'Checking to see if user ' . $user . ' is subscribed to channel ' . $chan, 1);
        
        // We were supplied an OAuth token. check it for validity and scopes
        if (($token != null || '') || ($code != null || false)){
            if ($token != null || ''){
                $check = $this->checkToken($token);
                
                if ($check["token"] != false){
                    $auth = $check;
                } else { // attempt to generate one
                    if ($code != null || ''){
                        $auth = $this->generateToken($code); // Assume generation and check later for failure
                    } else {
                        $this->generateError(400, 'Existing token expired and no code available for generation.');
                    }
                }
            } else { // Assume the code was given instead and generate if we can
                $auth = $this->generateToken($code); // Assume generation and check later for failure
            }
            
            // check to see if we recieved a token after all of that checking
            if ($auth['token'] == false) {
                $this->generateError(400, 'Auth key not returned, exiting function: ' . $functionName);
                return; // return out after the error is passed
            }
            
            $authSuccessful = false;
            
            // Check the array of scopes
            foreach ($auth['scopes'] as $type){
                if ($type == $requiredAuth){
                    // We found the scope, we are good then
                    $authSuccessful = true;
                    break;
                }
            }
            
            // Did we fail?
            if (!$authSuccessful){
                $this->generateError(403, 'Authentication token failed to have permissions for ' . $functionName . '; required Auth: ' . $requiredAuth);
                return null;
            }
            
            // Assign our key
            $this->generateOutput($functionName, 'Required scope found in array', 3);
            $token = $auth['token'];
        }
        
        $url = 'https://api.twitch.tv/kraken/users/' . $user . '/subscriptions/' . $chan;
        $options = array();
        $get = array('oauth_token' => $token);
        
        // Build our cURL query and store the array
        $subscribed = json_decode($this->cURL_get($url, $get, $options, true), true);
        
        // Check the return
        if ($subscribed == 403){
            $this->generateOutput($functionName, 'Authentication failed to have access to channel account.  Please check user ' . $user . '\'s access.', 3);
            $subscribed = false;
        } elseif ($subscribed == 422) {
            $this->generateOutput($functionName, 'Channel ' . $chan . ' does not have subscription program available', 3);
            $subscribed = false;
        } elseif ($subscribed == 404) {
            $this->generateOutput($functionName, 'User ' . $user . ' is not subscribed to channel ' . $chan, 3);
            $subscribed = false;
        } else {
            $this->generateOutput($functionName, 'User ' . $user . ' is subscribed to channel ' . $chan, 3);
            $subscribed = true;
        }
        
        // Clean up quickly
        $this->generateOutput($functionName, 'Cleaning memory', 3);
        unset($user, $chan, $token, $code, $requiredAuth, $functionName, $auth, $authSuccessful, $token, $type, $url, $options, $get);
        
        return $subscribed;
    }
    
    /**
     * Gets the team objects for all active teams
     * 
     * @param $limit - [int] Limit of channel objects to return
     * @param $offset - [int] Maximum number of objects to return
     * @param $returnTotal - [bool] Returns a _total row in the array
     * 
     * @return $teams - [array] Keyed array of all team objects.  Key is the team name
     */ 
    public function getTeams($limit = -1, $offset = 0, $returnTotal = false){        
        $functionName = 'GET_TEAMS';
        
        $this->generateOutput($functionName, 'Grabbing all available teams objects', 1);
        
        // Init some vars       
        $teams = array();        
        $url = 'https://api.twitch.tv/kraken/teams';
        $options = array();
        
        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
        
        // Build our cURL query and store the array
        $teamsObject = $this->get_iterated($functionName, $url, $options, $limit, $offset, 'teams', null, null, null, null, null, null, null, null, null, $returningTotal);
        
        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            $this->generateOutput($functionName, 'Including _total as the count of all object', 3);
            $teams['_total'] = count($teamsObject);
        }
        
        // Transfer to teams
        foreach ($teamsObject as $k => $team){
            if ($k == '_total'){
                $this->generateOutput($functionName, 'Setting key: ' . $k, 3);
                $teams[$k] = $team;
                continue;
            }
            
            $key = $team[TWITCH_KEY_NAME];
            $teams[$key] = $team;
            $this->generateOutput($functionName, 'Setting key: ' . $key, 3);
        }
        
        // Clean up quickly
        $this->generateOutput($functionName, 'Cleaning Memory', 3);
        unset($limit, $offset, $teamsObject, $team, $url, $options, $key, $k, $returnTotal, $returningTotal);
        
        return $teams;
    }
    
    /**
     * Grabs the team object for the supplied team
     * 
     * @param $team - [string] Name of the team to grab the object for
     * 
     * @return $teamObject - [array] Object returned for the team queried
     */ 
    public function getTeam( $team ){
        $functionName = 'GET_USEROBJECT';
        $this->generateOutput($functionName, 'Attempting to get the team object for user: ' . $team, 1);
        
        $url = 'https://api.twitch.tv/kraken/teams/' . $team;
        $options = array();
        $get = array();
        
        // Build our cURL query and store the array
        $teamObject = json_decode($this->cURL_get($url, $get, $options, false), true);

        //clean up
        $this->generateOutput($functionName, 'Cleaning Memory', 3);
        unset($team, $url, $options, $get, $functionName);
        
        return $teamObject;
    }    
    
    /**
     * Revoke access token for account. 
     * 
     * @todo This function now requires clientid appended to URL ?client_id=' . $client_id
     */ 
    public function revoke_access_tokens(){
        $functionName = 'GET_USEROBJECT';
        $this->generateOutput($functionName, 'Attempting to revoke access token for this client!', 1);
        
        $url = 'https://api.twitch.tv/kraken/oauth2/revoke?client_id=' . $this->twitch_client_id . '&token=' . $this->twitch_client_token;
        $options = array();
        $get = array();
        
        // Build our cURL query and store the array
        $userObject = json_decode($this->cURL_get($url, $get, $options, false), true);
        $this->generateOutput($functionName, 'Raw return: ' . json_encode($userObject), 4);
        
        //clean up
        $this->generateOutput($functionName, 'Cleaning Memory', 3);
        unset($user, $url, $options, $get, $functionName);
        
        return $userObject;          
    }    
}

endif;

//TWITCHPRESS_Kraken5_Calls::init();