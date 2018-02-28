<?php
/**
 * Kraken5 main class for calling the Twitch API. 
 * 
 * Do not use this class unless you accept the Twitch Developer Services Agreement
 * @link https://www.twitch.tv/p/developer-agreement
 * 
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

if( !class_exists( 'TWITCHPRESS_Twitch_API_Calls' ) ) :

class TWITCHPRESS_Twitch_API_Calls extends TWITCHPRESS_Twitch_API {
    
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
    * @version 5.8
    */
    public function get_users( $users ) {
        
        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = $this->confirm_scope( 'user_read', 'both', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; }
        
        // We need a string.
        if( is_array( $users ) ) {
            $users_string = implode( ',', $users );
        } else {
            $users_string = $users;
        }

        $url = 'https://api.twitch.tv/kraken/users?login=' . $users_string;
        $get = array( /* Client ID is not currently required, it causes failure when added (Dec 2017) */ );
        
        // Build our cURL query and store the array
        $usersObject = json_decode( $this->cURL_get( $url, $get, array(), false, __FUNCTION__ ), true );

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
     * @version 5.7
     */ 
    public function getUserObject_Authd( $token, $code ){
        
        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = $this->confirm_scope( 'user_read', 'channel', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; }
         
        $url = 'https://api.twitch.tv/kraken/user';
        $get = array( 'oauth_token' => $token, 'client_id' => $this->twitch_client_id );
                          
        // Build our cURL query and store the array
        $userObject = json_decode( $this->cURL_get( $url, $get, array(), false, __FUNCTION__ ), true );

        return $userObject;
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
    public function get_tokens_channel( $token = null ){        
                                 
        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = $this->confirm_scope( 'channel_read', 'channel', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; }
        
        if( !$token ) {
            $token = $this->twitch_client_token;
        }

        $url = 'https://api.twitch.tv/kraken/channel';
        $get = array( 'oauth_token' => $token, 'client_id' => $this->twitch_client_id );

        $object = json_decode($this->cURL_get($url, $get, array(), false, __FUNCTION__ ), true);
        
        if (!is_array($object)){
            $object = array(); // Catch to make sure that an array is returned no matter what, technically our fail state
        }
        
        return $object;
    }  
        
    /**
    * Returns posts from the feed of the giving channel.
    * 
    * @link https://dev.twitch.tv/docs/v5/reference/channel-feed#get-multiple-feed-posts
    * 
    * @param $chan - [string] Channel name to grab video objects from
    * @param $limit - [int] Limit of channel objects to return
    * @param $offset - [int] Maximum number of objects to return
    * @param $returnTotal - [bool] Returns a _total row in the array
    * 
    * @return $feedpostsObjects - [array] array of all returned video objects, Key is ID
    * 
    * @author Ryan R. Bayne
    * @version 5.3
    */ 
    public function getFeedPosts( $chan, $limit = -1, $offset = 0, $returnTotal = false ) {
        $feedpostsObjects = array();     
        $feedposts = array();
       
        $url = 'https://api.twitch.tv/kraken/feed/' . $chan . '/posts';

        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
            
        // Build our cURL query and store the array
        $feedposts = $this->get_iterated( $url, array(), $limit, $offset, 'videos', null, null, null, null, null, null, null, null, null, $returningTotal);

        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            $feedpostsObjects['_total'] = count($feedposts);
        }

        // Key the data
        foreach ( $feedposts as $k => $post ){
            if ( $k == '_total' ){
                $feedpostsObjects[$k] = $post;
                continue;
            }
            
            $key = $post['id'];
            $feedpostsObjects[$key] = $post;
        }

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
    * @version 6.0
    */
    public function postFeedPost( $postparam = array(), $token ){

        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = $this->confirm_scope( 'channel_feed_edit', 'both', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; }

        $url = 'https://api.twitch.tv/kraken/feed/' . $this->twitch_channel_id . '/posts';
        $post = array( 'oauth_token' => $token, 'client_id' => $this->twitch_client_id );
        $post = array_merge( $post, $postparam );
                    
        $returned_status = $this->cURL_post( $url, $post, array(), true, __FUNCTION__ );
        
        unset($url,$post,$options,$postparam);

        return $returned_status;  
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
     * 
     * @version 1.2
     */ 
    public function addBlockedUser($chan, $username, $token, $code){

        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = $this->confirm_scope( 'user_blocks_edit', 'channel', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; }
        
        if( !$token ) {
            $token = $this->twitch_client_token;
        }
                
        $url = 'https://api.twitch.tv/kraken/users/' . $chan . '/blocks/' . $username;
        $post = array( 'oauth_token' => $token, 'client_id' => $this->twitch_client_id);
            
        $result = $this->cURL_put($url, $post, array(), true);
        
        // What did we get returned status wise?
        if ($result = 200){                                                                    
            $success = true;
        } else {                                                                                  
            $success = false;
        }

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
     * 
     * @version 1.5
     */ 
    public function removeBlockedUser($chan, $username, $token, $code){

        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = $this->confirm_scope( 'user_blocks_edit', 'channel', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; }
        
        if( !$token ) {
            $token = $this->twitch_client_token;
        }
        
        $url = 'https://api.twitch.tv/kraken/users/' . $chan . '/blocks/' . $username;

        $post = array( 'oauth_token' => $token, 'client_id' => $this->twitch_client_id );
            
        $success = $this->cURL_delete($url, $post, array() );
        
        if ($success == '204'){
            // Successfully removed ' . $username . ' from ' . $chan . '\'s list of blocked users',
        } else if ($success == '422') {
            // Service unavailable or delete failed
        } else {
            // Do error here
        }
        
        // Basically we either deleted or they were never there
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
     * @version 5.3
     */
    public function getChannelObject( $channel_id ){
        $url = 'https://api.twitch.tv/kraken/channels/' . $channel_id;
        $get = array( 'client_id'   => $this->twitch_client_id );

        $object = json_decode($this->cURL_get($url, $get, array(), false), true);
        
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
     * 
     * @version 1.2
     */ 
    public function getEditors($chan, $limit = -1, $offset = 0, $token, $code, $returnTotal = false){

        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = $this->confirm_scope( 'channel_read', 'channel', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; }
        
        if( !$token ) {
            $token = $this->twitch_client_token;
        }
        
        $url = 'https://api.twitch.tv/kraken/channels/' . $chan . '/editors';
        $counter = 0;
        $editors = array();
        $editorsObject = array();
            
        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
    
        $editorsObject = $this->get_iterated( $url, array(), $limit, $offset, 'users', $token, null, null, null, null, null, null, null, null, $returningTotal);
        
        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            $editors['_total'] = count($editorsObject);
        }
            
        foreach ($editorsObject as $key => $editor){
            if ($key == '_total'){
                $editors[$key] = $editor;
                continue;
            }
            
            $editors[$counter] = $editor[TWITCH_KEY_NAME];
        }
        
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
     * 
     * @version 1.5
     */ 
    public function updateChannelObject($chan, $token, $code, $title = null, $game = null, $delay = null){

        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = $this->confirm_scope( 'channel_editor', 'channel', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; }
        
        if( !$token ) {
            $token = $this->twitch_client_token;
        }
        
        $url = 'https://api.twitch.tv/kraken/channels/' . $chan;
        $updatedObjects = array();
        $options = array();
        
        $updatedObjects['oauth_token'] = $token;
        
        if ($title != null || ''){
            // New title added to array
            $updatedObjects['channel']['status'] = $title;
        } 
        
        if ($game  != null || ''){
            // New game added to array
            $updatedObjects['channel']['game'] = $game;
        } 
        
        if ($delay != null || ''){
            // New Stream Delay added to array
            $updatedObjects['channel']['delay'] = $delay;
        } 
        
        $result = $this->cURL_put($url, $updatedObjects, $options, true);
        
        if (($result != 404) || ($result != 400)){
            $result = true;
        } else {
            $result = false;
        }
        
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
     * 
     * @version 5.0
     */ 
    public function reset_channel_stream_key( $chan, $token, $code ){   

        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = $this->confirm_scope( 'channel_stream', 'channel', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; }
        
        if( !$token ) {
            $token = $this->twitch_client_token;
        }
        
        $url = 'https://api.twitch.tv/kraken/channels/' . $chan . '/stream_key';
        $post = array( 'oauth_token' => $token, 'client_id' => $this->twitch_client_id );
        
        $result = $this->cURL_delete($url, $post, array(), true);
        
        if ($result == 204){
            $result = true;
        } else {
            $result = false;
        }
        
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
     * 
     * @version 5.0
     */ 
    public function startCommercial($chan, $token, $code, $length = 30){

        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = $this->confirm_scope( 'channel_commercial', 'channel', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; }
        
        if( !$token ) {
            $token = $this->twitch_client_token;
        }
        
        // Check the length to see if it is valid
        if ($length % 30 == 0){
            $length = 30;
        }
        
        $url = 'https://api.twitch.tv/kraken/channels/' . $chan . '/commercial';
        $post = array(
            'oauth_token' => $token,
            'length' => $length,
            'client_id' => $this->twitch_client_id
        );
        
        $result = $this->cURL_post($url, $post, array(), true);
        
        if ($result == 204){
            // Commercial successfully started
            $result = true;
        } else {
            // Commercial unable to be started
            $result = false;
        }
        
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

        $url = 'https://api.twitch.tv/kraken/chat/emoticons';
        $object = array();
        
        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
        
        $objects = $this->get_iterated( $url, array(), $limit, $offset, 'emoticons', null, null, null, null, null, null, null, null, null, $returningTotal);

        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            $object['_total'] = count($objects);
        }

        // Set keys
        foreach ($objects as $key => $row){
            if ($key == '_total'){
                $object[$key] = $row;
                continue;
            }
            
            $k = $row['regex'];
            $object[$k] = $row;
        }

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

        $url = 'https://api.twitch.tv/kraken/chat/' . $user . '/emoticons';
        $object = array();
        
        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
        
        $objects = $this->get_iterated( $url, array(), $limit, $offset, 'emoticons', null, null, null, null, null, null, null, null, null, $returningTotal);

        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            $object['_total'] = count($objects);
        }

        // Set keys
        foreach ($objects as $key => $row){
            if ($key == '_total'){
                $object[$key] = $row;
                continue;
            }
            
            $k = $row['regex'];
            $object[$k] = $row;
        }
        
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
     * 
     * @version 5.0
     */     
    public function chat_getBadges($chan){        

        $url = 'https://api.twitch.tv/kraken/chat/' . $chan . '/badges';
        $get = array( 'client_id' => $this->twitch_client_id );
        
        $object = json_decode($this->cURL_get($url, $get, array(), false), true);
        
        return $object;                
    }
    
    /**
     * Generates an OAuth token for chat login
     * 
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * 
     * @return $chatToken - [string] complete login token for chat login
     * 
     * @version 1.0
     */
    public function chat_generateToken($token, $code){

        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = $this->confirm_scope( 'chat_login', 'both', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; }        
        
        $prefix = 'oauth:';

        if( !$token ) {
            $token = $this->twitch_client_token;
        }
        
        $chatToken = $prefix . $token;

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
     * 
     * @version 1.0
     */ 
    public function getFollowers($chan, $limit = -1, $offset = 0, $sorting = 'desc', $returnTotal = false){

        $url = 'https://api.twitch.tv/kraken/channels/' . $chan . '/follows';
        $followersObject = array();
        $followers = array();
        
        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
             
        $followersObject = $this->get_iterated( $url, array(), $limit, $offset, 'follows', null, null, null, null, null, null, null, null, null, $returningTotal);
        
        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            $followers['_total'] = count($followersObject);
        }
        
        foreach ($followersObject as $k => $follower){
            if ($k == '_total'){
                $followers[$k] = $follower;
                continue;
            }
            
            $key = $follower['user'][TWITCH_KEY_NAME];
            $followers[$key] = $follower;
        }
        
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
     * 
     * @version 1.0
     */ 
    public function getFollows($username, $limit = -1, $offset = 0, $sorting = 'desc', $sortBy = 'created_at', $returnTotal = false){
        
        // Init some vars       
        $channels = array();
        $url = 'https://api.twitch.tv/kraken/users/' . $username . '/follows/channels';
        
        // Chck our sortby option
        $sortBy = ($sortBy == 'last_broadcast') ? $sortBy : 'created_at';
        
        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
            
        // Build our cURL query and store the array
        $channelsObject = $this->get_iterated( $url, array(), $limit, $offset, 'follows', null, null, null, null, null, null, null, null, null, $returningTotal, $sortBy);
        
        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            // Including _total as the count of all object
            $channels['_total'] = count($channelsObject);
        }
        
        foreach ($channelsObject as $k => $channel){
            if ($k == '_total'){
                // Setting key
                $channels[$k] = $channel;
                continue;
            }
            
            $key = $channel['channel'][TWITCH_KEY_NAME];
            $channels[$key] = $channel;
        }
        
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
     * 
     * @version 1.0
     */ 
    public function checkUserFollowsChannel($targetChannel, $user){
        $targetChannel = strval($targetChannel);
        $user          = strval($user);
        
        // Init some vars
        $url = "https://api.twitch.tv/kraken/users/$user/follows/channels/$targetChannel";
            
        // Build our cURL query and store the array
        $relationShipObject = $this->cURL_get($url);
        
        // If the user was not found or is not following, return false
        if (isset($relationShipObject['status']) && ($relationShipObject['status'] == 404)) {
            return false;
        }

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
     * 
     * @version 5.0
     */ 
    public function followChan($user, $chan, $token, $code){

        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = $this->confirm_scope( 'user_follows_edit', 'user', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; } 
        
        if( !$token ) {
            $token = $this->twitch_client_token;
        }
        
        $url = 'https://api.twitch.tv/kraken/users/' . $user . '/follows/channels/' . $chan;
        $post = array( 'oauth_token' => $token, 'client_id' => $this->twitch_client_id );
        
        $result = $this->cURL_put($url, $post, array(), true);

        if ($result == 200){
            // Sucessfully followed channel
            $result = true;              
        } else {
            // Unable to follow channel
            $result = false;            
        }

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
     * 
     * @version 5.1
     */ 
    public function unfollowChan($user, $chan, $token, $code){
        
        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = $this->confirm_scope( 'user_follows_edit', 'user', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; } 

        if( !$token ) {
            $token = $this->twitch_client_token;
        }
        
        $url = 'https://api.twitch.tv/kraken/users/' . $user . '/follows/channels/' . $chan;
        $delete = array( 'oauth_token' => $token, 'client_id' => $this->twitch_client_id );
        
        $result = $this->cURL_delete($url, $delete, array(), true);

        if ($result == 204){
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Grabs a list of most popular games being streamed on twitch with iteration
     * to perform the request multiple times. This is to fulfill the requested number of
     * games where initial request does not meet demand.  
     * 
     * @param $limit - [int] Set the limit of objects to grab
     * @param $offset - [int] Sets the initial offset to start the query from
     * @param $hls - [bool] Sets the query only to grab streams using HLS
     * @param $returnTotal - [bool] Sets iteration to not ignore the _total key
     * 
     * @return $object - [array] A complete array of all channel objects in order based on the sorting rules
     * 
     * @version 1.0
     */ 
    public function get_top_games_iterated($limit = -1, $offset = 0, $hls = false, $returnTotal = false){
        // Init some vars       
        $gamesObject = array();
        $games = array();        
        $url = 'https://api.twitch.tv/kraken/games/top';

        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
        
        $gamesObject = $this->get_iterated( $url, array(), $limit, $offset, 'top', null, $hls, null, null, null, null, null, null, null, $returningTotal);
        
        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            // Including _total as the count of all object
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
        }
        
        return $games;
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
     * 
     * @version 5.0
     */ 
    public function get_top_games( $using_function ){
        $url = 'https://api.twitch.tv/kraken/games/top';
        $get = array( 'client_id' => $this->twitch_client_id );
        $object = json_decode($this->cURL_get($url, $get, array(), false, $using_function ), true);
        
        if (!is_array($object)){
            $object = array();
        }
        
        return $object;
    }
        
    /**
     * Grabs All currently registered Ingest servers and some base stats
     * 
     * @return $ingests - [array] All returned ingest servers and the information associated with them
     * 
     * @version 5.0
     */
    public function getIngests(){
        $ingests = array();
        $url = 'https://api.twitch.tv/kraken/ingests';
        $get = array( 'client_id' => $this->twitch_client_id );

        $result = json_decode($this->cURL_get($url, $get, array()), true);

        if (is_array($result) && !empty($result)){
            foreach ($result as $key => $value){
                if ($key == '_links'){
                    continue;
                }
                
                foreach ($value as $val){
                    $k = $val['name'];
                    $ingests[$k] = $val;
                }
            }
        }

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
        $url = 'https://api.twitch.tv/kraken/search/games';
        $get = array(
            'query' => $query,
            'type' => 'suggest',
            'live' => $live, 
            'client_id' => $this->twitch_client_id );
        $result = array();
        $object = array();
        
        $result = json_decode($this->cURL_get($url, $get, array(), false), true);

        foreach ($result as $key => $value){
            if ($key !== '_links'){
                foreach ($value as $game){
                    $k = $game['name'];
                    if ($k != 'h'){
                        $object[$k] = $game;
                    }
                }                
            }
        }

        return $object;
    }
    
    /**
     * Grabs the stream object of a given channel
     * 
     * @param $channel_id - [string] Channel ID to get the stream object for
     * 
     * @return $object - [array or null] Returned array of all stream object data or null if stream is offline
     * 
     * @version 5.0
     */ 
    public function getStreamObject( $channel_id ){
        $url = 'https://api.twitch.tv/kraken/streams/' . $channel_id;
        $get = array( 'client_id' => $this->twitch_client_id );

        $result = json_decode($this->cURL_get($url, $get, array(), false), true);
        
        if ($result['stream'] != null){
            $object = $result['stream'];
        } else {
            $object = null;
        }
        
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
        // Init some vars       
        $url = 'https://api.twitch.tv/kraken/streams';
        $streamsObject = array();
        $streams = array();
        
        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
        
        // Build our cURL query and store the array
        $streamsObject = $this->get_iterated( $url, array(), $limit, $offset, 'streams', null, $hls, null, $channels, $embedable, $client_id, null, null, $game, $returningTotal);
        
        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
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
        // Init some vars
        $featured = array();          
        $url = 'https://api.twitch.tv/kraken/streams/featured';

        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
        
        // Build our cURL query and store the array
        $featuredObject = $this->get_iterated( $url, array(), $limit, $offset, 'featured', null, null, null, null, null, null, null, null, null, $returningTotal);
        
        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
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
     * 
     * @version 1.3
     */
    public function getFollowedStreams($limit = -1, $offset = 0, $token, $code, $hls = false, $returnTotal = false){
 
        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = $this->confirm_scope( 'user_read', 'user', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; } 
        
        if( !$token ) {
            $token = $this->twitch_client_token;
        }
        
        $streams = array();
        $url = 'https://api.twitch.tv/kraken/streams/followed';

        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
        
        $streamsObject = $this->get_iterated( $url, array(), $limit, $offset, 'streams', $token, $hls, null, null, null, null, null, null, null, $returningTotal);

        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
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
                $streams[$k] = $value;
            }
        }

        return $streams;
     }
    
    /**
     * Gets the current viewers and the current live channels for Twitch
     * 
     * @param $hls - [bool] Limit sear to channels only using hls
     * 
     * @return $statistics - [array] (keyed) The current Twitch Statistics 
     * 
     * @version 1.0
     */ 
    public function getTwitchStatistics($hls = false){
        $statistics = array();
        $url = 'https://api.twitch.tv/kraken/streams/summary';
        $get = array( 'hls' => $hls, 'client_id' => $this->twitch_client_id);

        $result = json_decode($this->cURL_get($url, $get, array()), true);

        if (is_array($result) && !empty($result)){
            $statistics = $result;
        }

        return $statistics;        
    }
    
    /**
     * Returns the video object for the specified ID
     * 
     * @param $id - [string] String ID of the video to get
     * 
     * @return $object - [array] Video object returned from the query, key is the ID
     * 
     * @version 1.0
     */
    public function getVideo_ID($id){
        // init some vars
        $object = array();
        $url = 'https://api.twitch.tv/kraken/videos/' . $id;
        $get = array( 'client_id' => $this->twitch_client_id );
        
        $result = json_decode($this->cURL_get($url, $get, array(), false), true);
        
        // A safe way of checking that the video was returned
        if (!empty($result) && array_key_exists('_id', $result)){
            // Set the key and the array
            $object[$id] = $result;            
        } else {
            $object[$id] = array();
        }

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
     * @version 1.3
     */ 
    public function getVideo_channel($chan, $limit = -1, $offset = 0, $boradcastsOnly = false, $returnTotal = false){
        // Init some vars
        $videoObjects = array();     
        $videos = array();
        $url = 'https://api.twitch.tv/kraken/channels/' . $chan . '/videos';
        
        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
            
        // Build our cURL query and store the array
        $videos = $this->get_iterated( $url, array(), $limit, $offset, 'videos', null, null, null, null, null, null, $boradcastsOnly, null, null, $returningTotal);
        
        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            $videoObjects['_total'] = count($videos);
        }
        
        // Key the data
        foreach ($videos as $k => $video){
            if ($k == '_total'){
                $videoObjects[$k] = $video;
                continue;
            }
            
            $key = $video['_id'];
            $videoObjects[$key] = $video;
        }
        
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
     * 
     * @version 1.0
     */ 
    public function getVideo_followed($limit = -1, $offset = 0, $token, $code, $returnTotal = false){

        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = $this->confirm_scope( 'user_read', 'user', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; } 
        
        if( !$token ) {
            $token = $this->twitch_client_token;
        }
        
        // Init some vars       
        $videosObject = array();            
        $videos = array();
        $url = 'https://api.twitch.tv/kraken/videos/followed';

        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
        
        // Build our cURL query and store the array
        $videos = $this->get_iterated( $url, array(), $limit, $offset, 'videos', $token, null, null, null, null, null, null, null, null, $returningTotal);
        
        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)) {
            $videosObject['_total'] = count($videos);
        }
        
        // Set our keys
        foreach ($videos as $k => $video) {
            if ($k == '_total') {
                $videosObject[$k] = $video;
                continue;
            }
            
            $key = $video['_id'];
            $videosObject[$key] = $video;
        }

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
     * 
     * @version 1.0
     */ 
    public function getTopVideos($game = '', $limit = -1, $offset = 0, $period = 'week', $returnTotal = false){
        // check the period to make sure it is valid
        if (($period != 'week') && ($period != 'month') && ($period != 'all')){
            $period = 'week';
        }
        
        // Init some vars       
        $videosObject = array();
        $videos = array();
        $url = 'https://api.twitch.tv/kraken/videos/top';

        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
            
        // Build our cURL query and store the array
        $videos = $this->get_iterated( $url, array(), $limit, $offset, 'videos', null, null, null, null, null, null, null, $period, $game, $returningTotal);

        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            $videosObject['_total'] = count($videos);
        }
        
        // Set our keys
        foreach ($videos as $k => $video){
            if ($k == '_total'){
                $videosObject[$k] = $video;
                continue;
            }
            
            $key = $video['_id'];
            $videosObject[$key] = $video;
        }

        return $videosObject;         
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
        
        if( $this->twitch_sandbox_mode ) { return $this->get_channel_subscriptions_sandbox(); }
                                                                                                    
        $url = 'https://api.twitch.tv/kraken/channels/' . $chan . '/subscriptions';                          
                                                                                        
        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.              
        $confirm_scope = $this->confirm_scope( 'channel_subscriptions', 'channel', __FUNCTION__ );               
        if( is_wp_error( $confirm_scope) ) 
        {
            $this->bugnet->log_error( __FUNCTION__, __( 'Kraken5 was not giving sccope channel_subscriptions in the get_channel_subscribers() function.', 'twitchpress' ), array(), true ); 
            return $confirm_scope; 
        }                                            
                                                                                                 
        // Default to main channel credentials.                                                              
        if( !$token ){ $token = $this->twitch_client_token; }                                                
        if( !$code ){ $code = $this->twitch_client_code; }                                                   

        $get = array( 'oauth_token' => $token, 
                      'limit'       => $limit, 
                      'offset'      => $offset, 
                      'direction'   => $direction, 
                      'client_id' => $this->twitch_client_id );
         
        return json_decode( $this->cURL_get($url, $get, array( /* cURL options */), false, __FUNCTION__ ), true);
    }
    
    /**
    * Sandbox version of get_channel_subscriptions().
    * 
    * @version 1.0
    */
    public function get_channel_subscriptions_sandbox() { 
        return array( 
                        "_total" => 4,
                        "subscriptions" => array( 
                            array(
                                "_id"            => "e5e2ddc37e74aa9636625e8d2cc2e54648a30418",
                                "created_at"     => "2016-04-06T04:44:31Z",
                                "sub_plan"       => "1000",
                                "sub_plan_name"  =>  "Channel Subscription (mr_woodchuck)",
                                "user"               => array(
                                    "_id"            => "89614178",
                                    "bio"            => "Twitch staff member who is a heimerdinger main on the road to diamond.",
                                    "created_at"     => "2015-04-26T18:45:34Z",
                                    "display_name"   => "Mr_Woodchuck",
                                    "logo"           => "https://static-cdn.jtvnw.net/jtv_user_pictures/mr_woodchuck-profile_image-a8b10154f47942bc-300x300.jpeg",
                                    "name"           => "mr_woodchuck",
                                    "type"           => "staff",
                                    "updated_at"     => "2017-04-06T00:14:13Z" ),
                                    
                            )
                        )
        );
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
        
        // I witnessed a possible empty string in $user resulting in wrong URL endpoint.
        if( !$twitch_user_id ){ $this->bugnet->log_error( __FUNCTION__, __( 'Twitch user ID not giving.', 'twitchpress' ), array(), true ); }            
                                                                   
        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = $this->confirm_scope( 'channel_check_subscription', 'channel', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; }
        
        $url = 'https://api.twitch.tv/kraken/channels/' . $chan_id . '/subscriptions/' . $twitch_user_id;
        $get = array( 'oauth_token' => $token, 'client_id' => $this->twitch_client_id );
        
        $subscribed = json_decode( $this->cURL_get( $url, $get, array(), false, __FUNCTION__ ), true );
         
        // only check results here to log them and return the original response.
        if( isset( $subscribed['error'] ) ) 
        {
            $this->bugnet->log_error( __FUNCTION__, sprintf( __( 'Error on GET subscription - User ID %s - Channel ID %s.', 'twitchpress' ), $twitch_user_id, $chan_id ), array(), false );
            return $subscribed;
        } 
        elseif( isset( $subscribed['sub_plan'] ) )
        {
            $this->bugnet->log( __FUNCTION__, sprintf( __( 'Good Subscription GET - User ID %s - Channel ID %s.', 'twitchpress' ), $twitch_user_id, $chan_id ), array(), false, false );
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
        $this->bugnet->log_error( __FUNCTION__, sprintf( __( 'Unexpected response from request for subscribers data. User ID: %s Channel ID: %s.', 'twitchpress' ), $twitch_user_id, $chan_id ), array(), false );
        
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
        
        $this->bugnet->log_error( __FUNCTION__, sprintf( __( 'Examine the unexpected response: %s', 'twitchpress' ), $unexpected ), array(), false );
          
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
        $confirm_scope = $this->confirm_scope( 'channel_check_subscription', 'user', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) 
        {
            $this->bugnet->log_error( __FUNCTION__, sprintf( __( 'TwitchPress Error: The function %s() requires the channel_check_subscription scope to be permitted.', 'twitchpress' ), __FUNCTION__ ), array(), true ); 
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
     * Gets the a users subscription data for specified channel from the user side.
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
    public function getUserSubscription( $twitch_user_id, $chan_id, $user_token = false ){   
      
        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = $this->confirm_scope( 'channel_check_subscription', 'user', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; }
        
        $url = 'https://api.twitch.tv/kraken/users/' . $twitch_user_id . '/subscriptions/' . $chan_id;
        $get = array( 'oauth_token' => $user_token, 'client_id' => $this->twitch_client_id );   

        // Build our cURL query and store the array
        $subscribed = json_decode( $this->cURL_get( $url, $get, array(), false, __FUNCTION__ ), true );
                 
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
        // Init some vars       
        $teams = array();        
        $url = 'https://api.twitch.tv/kraken/teams';
        
        // Check if we are returning a total and if we are in a limitless return (We can just count at that point and we will always have the correct number)
        $returningTotal = (($limit != -1) || ($offset != 0)) ? $returnTotal : false;
        
        // Build our cURL query and store the array
        $teamsObject = $this->get_iterated( $url, array(), $limit, $offset, 'teams', null, null, null, null, null, null, null, null, null, $returningTotal);
        
        // Include the total if we were asked to return it (In limitless cases))
        if ($returnTotal && ($limit == -1) && ($offset == 0)){
            $teams['_total'] = count($teamsObject);
        }
        
        // Transfer to teams
        foreach ($teamsObject as $k => $team){
            if ($k == '_total'){
                $teams[$k] = $team;
                continue;
            }
            
            $key = $team[TWITCH_KEY_NAME];
            $teams[$key] = $team;
        }
        
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
        $url = 'https://api.twitch.tv/kraken/teams/' . $team;
        $get = array();
        
        // Build our cURL query and store the array
        $teamObject = json_decode($this->cURL_get($url, $get, array(), false, __FUNCTION__), true);

        return $teamObject;
    }    
    
    /**
     * Revoke access token for account. 
     * 
     * @version 5.0
     */ 
    public function revoke_access_tokens(){
        $url = 'https://api.twitch.tv/kraken/oauth2/revoke';
        $get = array( 'token' => $this->twitch_client_token, 'client_id' => $this->twitch_client_id );
        
        // Build our cURL query and store the array
        $userObject = json_decode($this->cURL_get($url, $get, array(), false, __FUNCTION__), true);

        return $userObject;          
    }  
      
}

endif;

//TWITCHPRESS_Twitch_API_Calls::init();