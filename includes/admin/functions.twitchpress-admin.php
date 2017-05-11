<?php
/**
 * TwitchPress - Admin Only Functions
 *
 * This file will only be included during an admin request. Use a file
 * like functions.twitchpress-core.php if your function is meant for the frontend.   
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Admin
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Generate the complete nonce string, from the nonce base, the action 
 * and an item, e.g. twitchpress_delete_table_3.
 *
 * @since 1.0.0
 *
 * @param string      $action Action for which the nonce is needed.
 * @param string|bool $item   Optional. Item for which the action will be performed, like "table".
 * @return string The resulting nonce string.
 */
function twitchpress_nonce_prepend( $action, $item = false ) {
    $nonce = "twitchpress_{$action}";
    if ( $item ) {
        $nonce .= "_{$item}";
    }
    return $nonce;
}

/**
 * Get all WordPress TwitchPress screen ids.
 *
 * @return array
 */
function twitchpress_get_screen_ids() {

    $screen_ids = array(
        'twitchfeed_page_twitchpress-data',
        'twitchfeed_page_twitchpress-settings',
    );

    return apply_filters( 'twitchpress_screen_ids', $screen_ids );
}