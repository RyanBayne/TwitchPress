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
* Log a PHP error with extra information. Bypasses any WP configuration.

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

if( !function_exists( 'twitchpress_is_request' ) ) {
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
    
    if( !isset( $returned_value['access_token'] ) ) {
        return false;
    }

    if( !twitchpress_validate_token( $returned_value['access_token'] ) ) {
        return false;
    }
    
    return true;
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
    
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/libraries/kraken5/class.kraken-interface.php' );
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/libraries/kraken5/class.kraken-calls.php' );
      
    // Make call to Twitch for the latest feed post. 
    $kraken = new TWITCHPRESS_Twitch_API_Calls();
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
* Check if giving post name (slug) already exists in wp_posts.
* 
* @param mixed $post_name
* 
* @version 1.0
*/
function twitchpress_does_post_name_exist( $post_name ) {
    global $wpdb;
    $result = $wpdb->get_var( $wpdb->prepare( "SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = '%s'", $post_name ), 'ARRAY_A' );
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

/**
* Gets a channel post 
* 
* @param mixed $channel_id
*/
function twitchpress_get_channel_post( $channel_id ) {
    // args to query for your key
    $args = array(
        'post_type' => 'twitchchannels',
        'meta_query' => array(
            array(
                'key' => 'twitchpress_channel_id',
                'value' => $channel_id
            )
        ),
    );
    
    // perform the query
    $query = new WP_Query( $args );
                            
    if ( !empty( $query->posts ) ) {     
        return $query->posts[0]->ID;
    }

    return false;     
}

/**
* Checks if the giving post type is one that
* has been permitted for sharing to Twitch channel feeds.
* 
* @version 1.0
* 
* @param string $post_type
*/
function twitchpress_is_posttype_shareable( $post_type ) {
    if( get_option( 'twitchpress_shareable_posttype_' . $post_type ) ) {
        return true;
    }
    return false;
}

/**
* Handles redirects with log entries and added arguments to URL for 
* easy visual monitoring.
* 
* @param mixed $url
* @param mixed $line
* @param mixed $function
* @param mixed $file
* 
* @version 1.2
*/
function twitchpress_redirect_tracking( $url, $line, $function, $file = '' ) {
    global $bugnet;

    $redirect_counter = 1;
    
    // Refuse the redirect and log if twitchpressredirected=1 in giving $url. 
    if( strstr( $url, 'twitchpressredirected=1' ) ) {
        $bugnet->log_error( __FUNCTION__, __( 'Possible redirect loop in progress. The giving URL was used to redirect the visitor already.', 'twitchpress' ), array(), true );    
        ++$redirect_counter;
    }elseif( strstr( $url, 'twitchpressredirected=2' ) ){
        $bugnet->log_error( __FUNCTION__, __( 'Redirect loop in progress. The giving URL was used twice.', 'twitchpress' ), array(), true );    
        return;
    }
    
    // Tracking adds more values to help trace where redirect was requested. 
    if( get_option( 'twitchress_redirect_tracking_switch' ) == 'yes' ) {
        $url = add_query_arg( array( 'redirected-line' => $line, 'redirected-function' => $function ), $url );
 
        $bugnet->trace(
            'twitchpressredirects',
            $line,
            $function,
            $file,
            false,
            __( 'TwitchPress System Redirect Visitor To: ' . $url, 'twitchpress' )           
        );
    }    

    // Add twitchpressredirected to show that the URL has had a redirect. 
    // If it ever becomes normal to redirect again, we can increase the integer.
    wp_safe_redirect( add_query_arg( array( 'twitchpressredirected' => $redirect_counter ), $url ) );
    exit;
}

/**
* Determines if giving value is a valid Twitch subscription plan. 
* 
* @param mixed $value
* 
* @returns boolean true if the $value is valid.
* 
* @version 1.0
*/
function twitchpress_is_valid_sub_plan( $value ){
    $sub_plans = array( 'prime', 1000, 2000, 3000 );
    if( !is_string( $value ) && !is_numeric( $value ) ){ return false;}
    if( is_string( $value ) ){ $value = strtolower( $value ); }
    if( in_array( $value, $sub_plans ) ) { return true;}
    return false;
}

/**
* Generates a random 14 character string.
* 
* @version 2.0
*/
function twitchpress_random14(){ 
    return rand( 10000000, 99999999 ) . rand( 100000, 999999 );   
}

function var_dump_twitchpress( $var ) {     
    if( !bugnet_current_user_allowed() ) { return false; }
    echo '<pre>'; var_dump( $var ); echo '</pre>';
}

function wp_die_twitchpress( $html ) {
    if( !twitchpress_are_errors_allowed() ){ return; }
    wp_die( esc_html( $html ) ); 
}

/**
* Checks if the current user is permitted to view 
* error dumps for the entire blog.
* 
* Assumes the BugNet library.
* 
* @version 1.0
*/
function twitchpress_are_errors_allowed() {
    if( twitchpress_is_background_process() ) { 
        return false; 
    }        
                     
    if( !get_option( 'twitchpress_displayerrors' ) || get_option( 'twitchpress_displayerrors' ) !== 'yes' ) {
        return false;
    }

    // We can bypass the protection to display errors for a specified user.
    if( 'BYPASS' == get_option( 'bugnet_error_dump_user_id') ) {
        return true;    
    } 
    
    if( !current_user_can( 'activate_plugins' ) ) {
        return false;
    }  

    // We can display errors for all administrators. 
    if( 'ADMIN' == get_option( 'bugnet_error_dump_user_id') ) {
        return true;    
    }
    
    // Now assume numeric value was entered and ensure the current user is that ID.
    if( get_current_user_id() != get_option( 'bugnet_error_dump_user_id') ) {
        return false;    
    } 
    
    return true;
}

/**
* Adds spaces between each scope as required by the Twitch API. 
* 
* @param mixed $scopes_array
* @param mixed $for_url
* 
* @version 1.2
*/
function twitchpress_prepare_scopes( $scopes_array, $for_url = true ) {
        $scopes_string = '';
        foreach ( $scopes_array as $s ){
            $scopes_string .= $s . '+';
        }

        $prepped_scopes = rtrim( $scopes_string, '+' );
        
        return $prepped_scopes;
}

function twitchpress_scopecheckbox_required_icon( $scope ){
    global $system_scopes_status;
 
    $required = false; 
    
    // Do not assume every extension has set this global properly. 
    if( !is_array( $system_scopes_status ) || empty( $system_scopes_status ) ) { return ''; }
    
    // Check if $scope is required for the admins main account. 
    foreach( $system_scopes_status['admin'] as $extension_slug => $scope_information )
    {
        if( in_array( $scope, $scope_information['required'] ) ) { $required = true; break; }                      
    }    
    
    if( $required ) 
    {
        $icon = '<span class="dashicons dashicons-yes"></span>';
    }
    else
    {
        $icon = '<span class="dashicons dashicons-no"></span>';
    }
    
    return $icon;
}

function twitchpress_scopecheckboxpublic_required_icon( $scope ){
    global $system_scopes_status;
                 
    $required = false; 
    
    // Do not assume every extension has set this global properly. 
    if( !is_array( $system_scopes_status ) || empty( $system_scopes_status ) ) { return ''; }

    // Check if $scope is required for visitors accounts. 
    foreach( $system_scopes_status['public'] as $extension_slug => $scope_information )
    {
        if( in_array( $scope, $scope_information['required'] ) ) { $required = true; break; }     
    }

    if( $required ) 
    {
        $icon = '<span class="dashicons dashicons-yes"></span>';
    }
    else
    {
        $icon = '<span class="dashicons dashicons-no"></span>';
    }
    
    return $icon;
}

/**
* Get a Twitch users Twitch ID.
* 
* @version 1.0
* 
* @return integer from Twitch user object or false if failure detected.
*/
function twitchpress_get_user_twitchid( $twitch_username ) {
    $kraken = new TWITCHPRESS_Twitch_API_Calls();
    $user_object = $kraken->get_users( $twitch_username );
    if( isset( $user_object['users'][0]['_id'] ) && is_numeric( $user_object['users'][0]['_id'] ) ) {
        return $user_object['users'][0]['_id'];
    } else {
        return false;
    }
    unset( $kraken );   
}

/**
* CSS for API Requests table.
* 
* @version 1.0
*/
function twitchpress_css_listtable_apirequests() {
    if( !isset( $_GET['page'] ) ) { return; }
    if( !isset( $_GET['tab'] ) ) { return; }
    if( $_GET['page'] !== 'twitchpress_data' ) { return; }
    if( $_GET['tab'] !== 'kraken5requests_list_tables' ) { return; }
    
    echo '<style type="text/css">';
    echo '.wp-list-table .column-time { width: 10%; }';
    echo '.wp-list-table .column-function { width: 20%; }';
    echo '.wp-list-table .column-header { width: 30%; }';
    echo '.wp-list-table .column-url { width: 20%; }';
    echo '</style>';
    
}
add_action('admin_head', 'twitchpress_css_listtable_apirequests');

/**
* CSS for API Errors table.
* 
* @version 1.0
*/
function twitchpress_css_listtable_apiresponses() {
    if( !isset( $_GET['page'] ) ) { return; }
    if( !isset( $_GET['tab'] ) ) { return; }
    if( $_GET['page'] !== 'twitchpress_data' ) { return; }
    if( $_GET['tab'] !== 'apiresponses_list_tables' ) { return; }
    
    echo '<style type="text/css">';
    echo '.wp-list-table .column-time { width: 10%; }';
    echo '.wp-list-table .column-httpdstatus { width: 10%; }';
    echo '.wp-list-table .column-function { width: 20%; }';
    echo '.wp-list-table .column-error_no { width: 10%; }';
    echo '.wp-list-table .column-result { width: 50%; }';
    echo '</style>';
    
}
add_action('admin_head', 'twitchpress_css_listtable_apiresponses');

/**
* CSS for API Errors table.
* 
* @version 1.0
*/
function twitchpress_css_listtable_apierrors() {
    if( !isset( $_GET['page'] ) ) { return; }
    if( !isset( $_GET['tab'] ) ) { return; }
    if( $_GET['page'] !== 'twitchpress_data' ) { return; }
    if( $_GET['tab'] !== 'apierrors_list_tables' ) { return; }
    
    echo '<style type="text/css">';
    echo '.wp-list-table .column-time { width: 10%; }';
    echo '.wp-list-table .column-function { width: 20%; }';
    echo '.wp-list-table .column-error_string { width: 30%; }';
    echo '.wp-list-table .column-error_no { width: 10%; }';
    echo '.wp-list-table .column-curl_url { width: 40%; }';
    echo '</style>';
    
}
add_action('admin_head', 'twitchpress_css_listtable_apierrors');

/**
* Get the sync timing array which holds delays for top level sync activity.
* 
* This option avoids having to creation options per service at the top level
* but if needed services can have additional options to control individual
* processes.
* 
* @version 1.0
*/
function twitchpress_get_sync_timing() {
    $sync_timing_array = get_option( 'twitchpress_sync_timing' );
    if( !$sync_timing_array || !is_array( $sync_timing_array ) ) { return array(); }
    return $sync_timing_array;
}

function twitchpress_update_sync_timing( $sync_timing_array ) {
    update_option( 'twitchpress_sync_timing', $sync_timing_array, false );    
}

/**
* Add a new sync time for a giving procedure. 
* 
* @param mixed $file
* @param mixed $function
* @param mixed $line
* @param mixed $delay
* 
* @version 1.0
*/
function twitchpress_add_sync_timing( $file, $function, $line, $delay ) {
    $sync_timing_array = twitchpress_get_sync_timing();
    $sync_timing_array[$file][$function][$line]['delay'] = $delay;
    $sync_timing_array[$file][$function][$line]['time'] = time();
    twitchpress_update_sync_timing( $sync_timing_array );    
}

/**
* A standard method for establishing time delay and if a giving method is
* due to run. Use this within any procedure to end it short or continue. 
* 
* Sets new time() if due to make it easier to manage delays within procedures. 
* 
* @param mixed $function
* @param mixed $line
* @param mixed $file
* @param mixed $delay
* 
* @returns boolean true if delay has passed already else false.
* 
* @version 2.0
*/
function twitchpress_is_sync_due( $file, $function, $line, $delay ) {
    $sync_timing_array = twitchpress_get_sync_timing();
    
    // Init the delay for the first time
    if( !isset( $sync_timing_array[$file][$function][$line] ) )
    {
        twitchpress_add_sync_timing( $file, $function, $line, $delay );
        return true;    
    }    
    else
    {
        $last_time = $sync_timing_array[$file][$function][$line]['time'];
        $soonest_time = $last_time + $delay;
        if( $soonest_time > time() ) 
        {
            $sync_timing_array[$file][$function][$line]['delay'] = $delay;
            $sync_timing_array[$file][$function][$line]['time'] = time();
            twitchpress_update_sync_timing( $sync_timing_array );
            return true;    
        }   
        
        // Not enough time has passed since the last event. 
        return false;
    }
}

/**
* Determines if the current logged in user is also the owner of the main channel.
* 
* @version 2.0
*/
function twitchpress_is_current_user_main_channel_owner( $user_id = null ) {
    if( !$user_id )
    {
        $user_id = get_current_user_id();
    }
    
    // Avoid processing the owner of the main channel (might not be admin with ID 1)
    if( twitchpress_get_main_channels_wpowner_id() == $user_id ) { return true; }
    return false;    
}


/**
* Returns the user meta value for the last time their Twitch data
* was synced with WordPress. Value is 
* 
* @returns integer time set using time() or false/null. 
* @version 1.0
*/
function twitchpress_get_user_sync_time( $user_id ) {
    return get_user_meta( $user_id, 'twitchpress_sync_time', true );
}