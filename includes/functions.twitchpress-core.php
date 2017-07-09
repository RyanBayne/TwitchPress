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
        'twitchpress_base'           => '',
        'category_base'          => '',
        'tag_base'               => '',
        'attribute_base'         => '',
        'use_verbose_page_rules' => false,
    ) );

    // Ensure rewrite slugs are set.
    $permalinks['twitchfeed_rewrite_slug']   = untrailingslashit( empty( $permalinks['twitchfeed_base'] ) ? _x( 'twitchfeed', 'slug', 'twitchpress' )             : $permalinks['twitchfeed_base'] );
    $permalinks['category_rewrite_slug']  = untrailingslashit( empty( $permalinks['category_base'] ) ? _x( 'twitchfeed-category', 'slug', 'twitchpress' )   : $permalinks['category_base'] );
    $permalinks['tag_rewrite_slug']       = untrailingslashit( empty( $permalinks['tag_base'] ) ? _x( 'twitchfeed-tag', 'slug', 'twitchpress' )             : $permalinks['tag_base'] );
    $permalinks['attribute_rewrite_slug'] = untrailingslashit( empty( $permalinks['attribute_base'] ) ? '' : $permalinks['attribute_base'] );

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
* @version 1.0
*/
function twitchpress_returning_url_nonced( $new_parameters_array, $action, $specified_url = null  ) {
    return esc_url( 
                wp_nonce_url( 
                        add_query_arg( $new_parameters_array, $specified_url ) 
                ), $action 
           );
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