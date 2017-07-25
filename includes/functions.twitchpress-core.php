<?php
/**
 * TwitchPress - Core Functions
 *
 * Place a function here when it is doesn't make sense in other files or needs
 * to be obviously available to third-party developers. 
 * 
 * @author   Ryan Bayne
 * @category Core
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} 

// Include core functions (available in both admin and frontend).
include( 'functions.twitchpress-formatting.php' );

/**
 * is_ajax - Returns true when the page is loaded via ajax.
 * 
 * The DOING_AJAX constant is set by WordPress.
 * 
 * @return bool
 */
function twitchpress_is_ajax() {          
    return defined( 'DOING_AJAX' );
}
    
/**
* Check if the home URL (stored during WordPress installation) is HTTPS. 
* If it is, we don't need to do things such as 'force ssl'.
*
* @return bool
*/
function twitchpress_is_https() {      
    return false !== strstr( get_option( 'home' ), 'https:' );
}

/**
* Determine if on the dashboard page. 
* 
* $current_screen is not set early enough for calling in some actions. So use this
* function instead.
*/
function twitchpress_is_dashboard() {      
    global $pagenow;
    // method one: check $pagenow value which could be "index.php" and that means the dashboard
    if( isset( $pagenow ) && $pagenow == 'index.php' ) { return true; }
    // method two: should $pagenow not be set, check the server value
    return strstr( $this->PHP->currenturl(), 'wp-admin/index.php' );
}

/**
* Use to check for Ajax or XMLRPC request. Use this function to avoid
* running none urgent tasks during existing operations and demanding requests.
*/
function twitchpress_is_background_process() {        
    if ( ( 'wp-login.php' === basename( $_SERVER['SCRIPT_FILENAME'] ) )
            || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
            || ( defined( 'DOING_CRON' ) && DOING_CRON )
            || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
                return true;
    }
               
    return false;
}

/**
 * Output any queued javascript code in the footer.
 */
function twitchpress_print_js() {
    global $twitchpress_queued_js;

    if ( ! empty( $twitchpress_queued_js ) ) {
        // Sanitize.
        $twitchpress_queued_js = wp_check_invalid_utf8( $twitchpress_queued_js );
        $twitchpress_queued_js = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", $twitchpress_queued_js );
        $twitchpress_queued_js = str_replace( "\r", '', $twitchpress_queued_js );

        $js = "<!-- TwitchPress JavaScript -->\n<script type=\"text/javascript\">\njQuery(function($) { $twitchpress_queued_js });\n</script>\n";

        /**
         * twitchpress_queued_js filter.
         *
         * @since 2.6.0
         * @param string $js JavaScript code.
         */
        echo apply_filters( 'twitchpress_queued_js', $js );

        unset( $twitchpress_queued_js );
    }
}

/**
 * Display a WordPress TwitchPress help tip.
 *
 * @since  2.5.0
 *
 * @param  string $tip        Help tip text
 * @param  bool   $allow_html Allow sanitized HTML if true or escape
 * @return string
 */
function twitchpress_help_tip( $tip, $allow_html = false ) {
    if ( $allow_html ) {
        $tip = twitchpress_sanitize_tooltip( $tip );
    } else {
        $tip = esc_attr( $tip );
    }

    return '<span class="twitchpress-help-tip" data-tip="' . $tip . '"></span>';
}

/**
 * Queue some JavaScript code to be output in the footer.
 *
 * @param string $code
 */
function twitchpress_enqueue_js( $code ) {
    global $twitchpress_queued_js;

    if ( empty( $twitchpress_queued_js ) ) {
        $twitchpress_queued_js = '';
    }

    $twitchpress_queued_js .= "\n" . $code . "\n";
}

/**
 * Get permalink settings for TwitchPress independent of the user locale.
 *
 * @since  1.0.0
 * @return array
 */
function twitchpress_get_permalink_structure() {
    if ( function_exists( 'switch_to_locale' ) && did_action( 'admin_init' ) ) {
        switch_to_locale( get_locale() );
    }
                      
    $permalinks = wp_parse_args( (array) get_option( 'twitchpress_permalinks', array() ), array(
        'twitchpress_base'       => '',
        'category_base'          => '',
        'tag_base'               => '',
        'attribute_base'         => '',
        'use_verbose_page_rules' => false,
    ) );

    // Ensure rewrite slugs are set.
    $permalinks['twitchfeed_rewrite_slug'] = untrailingslashit( empty( $permalinks['twitchfeed_base'] ) ? _x( 'twitchfeed',          'slug', 'twitchpress' )             : $permalinks['twitchfeed_base'] );
    $permalinks['category_rewrite_slug']   = untrailingslashit( empty( $permalinks['category_base'] )   ? _x( 'twitchfeed-category', 'slug', 'twitchpress' )   : $permalinks['category_base'] );
    $permalinks['tag_rewrite_slug']        = untrailingslashit( empty( $permalinks['tag_base'] )        ? _x( 'twitchfeed-tag',      'slug', 'twitchpress' )             : $permalinks['tag_base'] );
    $permalinks['attribute_rewrite_slug']  = untrailingslashit( empty( $permalinks['attribute_base'] )  ? '' : $permalinks['attribute_base'] );

    if ( function_exists( 'restore_current_locale' ) && did_action( 'admin_init' ) ) {
        restore_current_locale();
    }
    return $permalinks;
}

/**
* Log an error with extra information.
* 
* Feel free to use error_log() on its own however keep in mind that
* 
* Common Use: twitchpress_error( 'DEEPTRACE', 0, null, null, __LINE__, __FUNCTION__, __CLASS__, time() );
* 
* @version 1.2
* 
* @param string $message
* @param int $message_type 0=PHP logger|1=Email|2=Depreciated|3=Append to file|4=SAPI logging handler
* @param string $destination
* @param string $extra_headers
* @param mixed $line
* @param mixed $function
* @param mixed $class
* @param mixed $time
*/
function twitchpress_error( $message, $message_type = 0, $destination = null, $extra_headers = null, $line = null, $function = null, $class = null, $time = null ) {
    $error = 'TwitchPress Plugin: ';
    $error .= $message;
    $error .= ' (get squeekycoder@gmail.com)';
    
    // Add extra information. 
    if( $line != null || $function != null || $class != null || $time != null )
    {
        if( $line )
        {
            $error .= ' Line: ' . $line;
        }    
        
        if( $function )
        {
            $error .= ' Function: ' . $function;
        }
        
        if( $class )
        {
            $error .= ' Class: ' . $class;    
        }
        
        if( $time )
        {
            $error .= ' Time: ' . $time;
        }
    }

    return error_log( $error, $message_type, $destination, $extra_headers );
}

/**
* Create a nonced URL for returning to the current page.
* 
* @param mixed $new_parameters_array
* 
* @version 1.2
*/
function twitchpress_returning_url_nonced( $new_parameters_array, $action, $specified_url = null  ) {

    $url = add_query_arg( $new_parameters_array, $specified_url );
    
    $url = wp_nonce_url( $url, $action );
    
    return $url;
} 

/**
 * What type of request is this?
 *
 * Functions and constants are WordPress core. This function will allow
 * you to avoid large operations or output at the wrong time.
 * 
 * @param  string $type admin, ajax, cron or frontend.
 * @return bool
 */
function twitchpress_is_request( $type ) {
    switch ( $type ) {
        case 'admin' :
            return is_admin();
        case 'ajax' :
            return defined( 'DOING_AJAX' );
        case 'cron' :
            return defined( 'DOING_CRON' );
        case 'frontend' :
            return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
    }
} 

/**
* Validate the value passed as a $_GET['code'] prior to using it.
* 
* @return boolean false if not valid else true
* 
* @version 1.0
*/
function twitchpress_validate_code( $code ) {
    if( strlen ( $code ) !== 30  ) {
        return false;
    }           
    
    if( !ctype_alnum( $code ) ) {
        return false;
    }
    
    return true;
}      

/**
* Validate a token value.
* 
* @return boolean false if not valid else true
* 
* @version 1.0
*/
function twitchpress_validate_token( $token ) {
    if( strlen ( $token ) !== 30  ) {
        return false;
    }           
    
    if( !ctype_alnum( $token ) ) {
        return false;
    }
    
    return true;
}    

/**
* Determines if the value returned by generateToken() is a token or not.
* 
* Does not check if the token is valid as this is intended for use straight
* after a token is generated. 
* 
* @returns boolean true if the value appears normal.
* 
* @version 1.0
*/
function twitchpress_was_valid_token_returned( $returned_value ){
    
    if( !array( $returned_value ) ) {
        return false;
    }
    
    if( !isset( $returned_value['token'] ) ) {
        return false;
    }

    if( !twitchpress_validate_token( $returned_value['token'] ) ) {
        return false;
    }
    
    return true;
}                     

/**
* Checks if the giving user has Twitch API credentials.
* 
* @returns boolean false if no credentials else true
* 
* @param mixed $user_id
* 
* @version 1.0
*/
function twitchpress_is_user_authorized( $user_id ) { 
    if( !get_user_meta( $user_id, 'twitchpress_code' ) ) {
        return false;
    }    
    if( !get_user_meta( $user_id, 'twitchpress_token' ) ) {
        return false;
    }    
    return true;
}

/**
* Gets a giving users Twitch credentials from user meta and if no user
* is giving defaults to the current logged in user. 
* 
* @returns mixed array if user has credentials else false.
* @param mixed $user_id
* 
* @version 1.0
*/
function twitchpress_get_user_twitch_credentials( $user_id ) {
    
    if( !$user_id ) {
        return false;
    } 
    
    if( !$code = get_user_meta( $user_id, 'twitchpress_code', true ) ) {  
        return false;
    }
    
    if( !$token = get_user_meta( $user_id, 'twitchpress_token', true ) ) {  
        return false;
    }

    return array(
        'code'  => $code,
        'token' => $token
    );
}

/**
* Update giving users oauth2 code.
* 
* @param mixed $user_id
* @param mixed $code
* 
* @version 1.0
*/
function twitchpress_update_user_code( $user_id, $code ) { 
    update_user_meta( $user_id, 'twitchpress_auth_time', time() );
    update_user_meta( $user_id, 'twitchpress_code', $code );    
}

/**
* Update users oauth2 token.
* 
* @param mixed $user_id
* @param mixed $token
* 
* @version 1.0
*/
function twitchpress_update_user_token( $user_id, $token ) { 
    update_user_meta( $user_id, 'twitchpress_auth_time', time() );
    update_user_meta( $user_id, 'twitchpress_token', $token );    
}

/**
* Update users Twitch ID (in Kraken version 5 user ID and channel ID are the same).
* 
* @param mixed $user_id
* @param mixed $twitch_user_id
* 
* @version 1.0
*/
function twitchpress_update_user_twitchid( $user_id, $twitch_user_id ) {
    update_user_meta( $user_id, 'twitchpress_auth_time', time() );
    update_user_meta( $user_id, 'twitchpress_id', $twitch_user_id );    
}

/**
* Updates user code and token for Twitch.tv API.
* 
* We always store the Twitch user ID that the code and token matches. This
* will help to avoid mismatched data.
* 
* @param mixed $user_id
* @param mixed $code
* @param mixed $token
* 
* @version 1.0
*/
function twitchpress_update_user_oauth( $user_id, $code, $token, $twitch_user_id ) {
    twitchpress_update_user_code( $user_id, $code );
    twitchpress_update_user_token( $user_id, $token ); 
    twitchpress_update_user_twitchid( $user_id, $twitch_user_id );     
}

/**
* Schedule an event for syncing feed posts into WP.
* 
* @version 1.0
*/
function twitchpress_schedule_sync_channel_to_wp() {
    wp_schedule_event(
        time() + 2,
        3600,
        'twitchpress_sync_feed_to_wp'
    );    
}

/**
* Controlled by CRON - sync feed posts into wp for a giving channel.
* 
* Assumes settings have been checked.                          
* 
* @version 1.0
*/
function twitchpress_sync_feed_to_wp( $channel_id = false ) {
    $new_posts_ids = array();

    // If no $channel_id we assume we are syncing the main channel. 
    if( !$channel_id ) { 
        $channel_id = twitchpress_get_main_channels_twitchid();   
    }
   
    if( !$channel_id ) {
        return false;
    }
    
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/libraries/kraken5/class.kraken5-interface.php' );
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/libraries/kraken5/class.kraken5-calls.php' );
      
    // Make call to Twitch for the latest feed post. 
    $kraken = new TWITCHPRESS_Kraken5_Calls();
    $feed_posts = $kraken->getFeedPosts( $channel_id, 5 );
    unset( $kraken );
    if( !$feed_posts) { return; }

    // Check which feed ID's do not exist in the blog and treat them as new Twitch entries.
    foreach( $feed_posts as $entry_id => $entry_array ) {
   
        // Skip feed entries alrady in the database.
        if( twitchpress_does_feed_item_id_exist( $entry_id ) ) {
            continue;
        }
            
        // Set WP post author ID based on the feed entry author (channel owner).
        $post_author_id = twitchpress_feed_owner_wpuser_id( $channel_id );    
            
        $new_post_id = twitchpress_insert_feed_post( $channel_id, $entry_array, $post_author_id );
        
        if( is_numeric( $new_post_id ) ) {   
            $new_posts_ids[] = $new_post_id;    
        }      
    }

    return $new_posts_ids;
}

/**
* Determines if a giving feed item ID exists already or not.
*      
* @param mixed $feed_item_id
* 
* @returns boolean true if the item ID is found in post meta else returns false.
* 
* @version 1.0
*/
function twitchpress_does_feed_item_id_exist( $feed_item_id ){ 
    $args = array(
        'post_type' => 'twitchfeed',
        'meta_query' => array(
            array(
                'key' => 'twitchpress_feed_item_id',
                'value' => $feed_item_id
            )
        ),
        'fields' => 'ids'
    );
    
    $query = new WP_Query( $args );
  
    if ( !empty( $query->posts ) ) {     
        return true;
    }

    return false;    
}                 

/**
* Insert a new "twitchfeed" post.
* 
* @param mixed $channel_id
* @param mixed $feed_entry pass the feed item object as returned from the Twitch API.
* @param mixed $post_author author must be determined based on channel owner if the owner is also a user.
* @param string $process channeltowp|csvimport|customui
* 
* @returns integer post ID or a string explaining why the post was not created.
* 
* @version 1.0
*/
function twitchpress_insert_feed_post( $channel_id, $feed_entry, $post_author, $process = 'channeltowp' ) {
   
    // Ensure feed item does not already exist based on it's ID.
    if( twitchpress_does_feed_item_id_exist( $feed_entry['id'] ) ) {
        return __( 'The channel feed item already exists in this WordPress site. This was establishing by checking the items ID which was found in the database already.', 'twitchpress' );
    }    
                                           
    $post = array(
        'post_author' => 1,
        'post_title' => __( 'Latest Update by', 'twitchpress' ) . ' ' .  $feed_entry['user']['display_name'],
        'post_content' => $feed_entry['body'],
        'post_status' => 'draft',
        'post_type' => 'twitchfeed',
    );
    
    $post_id = wp_insert_post( $post, true );
    
    if( is_wp_error( $post_id ) ) {     
        return false;
    }
    
    // Add Twitch channel ID to the post as a permanent pairing. 
    add_post_meta( $post_id, 'twitchpress_channel_id', $channel_id );
    add_post_meta( $post_id, 'twitchpress_feed_item_id', $feed_entry['id'] );

    return $post_id;    
}

/**
* Determine the owner of a channel within the WP site i.e. if administrator
* entered the channel, then they own it 100% and no other user can be linked.
* 
* But what we want to establish is a linked WP user who is a subscriber to the Twitch channel
* or even just a follower. If the service allows them to enter their own channel and own
* the channel on this site then we will return their WP user ID. 
* 
* @param mixed $channel_id
* @return mixed
* 
* @version 1.0
*/
function twitchpress_feed_owner_wpuser_id( $channel_id ) {
    
    /**
    * A channels ID is the same as user ID and they will be stored in user meta. 
    * 
    * So here we will get the WP user ID that has the channel ID in their meta else
    * return a default ID. 
    */
    
    return 1;// WIP - other areas of the plugin and extensions need to progress    
}

/**
* Queries the custom post type 'twitchchannels' and returns post ID's that
* have a specific meta key and specific meta value.
* 
* @version 1.0
*/
function twitchpress_get_channels_by_meta( $post_meta_key, $post_meta_value, $limit = 100 ) {
    // args to query for your key
    $args = array(
        'post_type' => 'twitchchannels',
        'meta_query' => array(
            array(
                'key' => $post_meta_key,
                'value' => $post_meta_value
            )
        ),
        'fields' => 'ids'
    );
    
    // perform the query
    $query = new WP_Query( $args );
  
    if ( !empty( $query->posts ) ) {     
        return true;
    }

    return false;    
}

/**
* Adds post meta that act as settings for the main channel.
* 
* @version 1.0
*/
function twitchpress_activate_channel_feedtowp_sync( $channel_post_id ) {
    update_post_meta( $channel_post_id, 'twitchpress_sync_feed_to_wp' );      
}

/**
* Get the main/default/official channel ID for the WP site.
* 
* @version 1.0
*/
function twitchpress_get_main_channels_twitchid() {
    return get_option( 'twitchpress_main_channel_id' );   
}

/**
* Get the main/default/official channels related post ID.
* 
* @version 1.0
*/
function twitchpress_get_main_channels_postid() {
    return get_option( 'twitchpress_main_channel_postid' );   
}

/**
* Check if giving post name (slug) already exists in wp_posts.
* 
* @param mixed $post_name
*/
function twitchpress_does_post_name_exist( $post_name ) {
    global $wpdb;
    $result = $wpdb->get_var( $wpdb->prepare( "SELECT post_name FROM {$wpdb->prefix}_posts WHERE post_name = '%s'", $post_name ), 'ARRAY_A' );
    if( $result ) {
        return true;
    } else {
        return false;
    }
}

/**
* Checks if a channel ID exists in post meta for custom post type "twitchchannels"
* 
* @returns boolean true if the Twitch channel ID already exists in post meta.
*  
* @param mixed $channel_id
* 
* @version 1.0
*/
function twitchpress_channelid_in_postmeta( $channel_id ) {
    // args to query for your key
    $args = array(
        'post_type' => 'twitchchannels',
        'meta_query' => array(
            array(
                'key' => 'twitchpress_channel_id',
                'value' => $channel_id
            )
        ),
        'fields' => 'ids'
    );
    
    // perform the query
    $query = new WP_Query( $args );
  
    if ( !empty( $query->posts ) ) {     
        return true;
    }

    return false;
}

/**
* Converts "2016-11-29T15:52:27Z" format into a timestamp. 
* 
* @param mixed $date_time_string
* 
* @version 1.0
*/
function twitchpress_convert_created_at_to_timestamp( $date_time_string ) {  
    return date_timestamp_get( date_create( $date_time_string ) );      
}