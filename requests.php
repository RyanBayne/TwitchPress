<?php
/**
 * TwitchPress $_POST processing using admin-post.php the proper way!
 *
 * @author   Ryan Bayne
 * @category Shortcodes
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {    
    exit;
}

include_once( dirname( __FILE__ ) . '/includes/requests/developer-toolbar-requests.php' );