<?php
/**
 * TwitchPress Sync Extension - Core Functions

 * @author   Ryan Bayne
 * @category Core
 * @package  TwitchPress Sync Extension
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
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