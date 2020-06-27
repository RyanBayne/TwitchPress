<?php
/**
 * TwitchPress - Deprecated functions from the entire TwitchPress system. 
 * 
 * Move extension functions here and avoid creating file like this in every extension.  
 * 
 * @author   Ryan Bayne
 * @category Scripts
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
* @deprecated use twitchpress_get_main_channels_code() 
*/
function twitchpress_get_main_channel_code() {
    return get_option( 'twitchpress_main_code' );
}

/**
* @deprecated use twitchpress_get_main_channels_code() 
*/
function twitchpress_get_main_client_code() {
    return twitchpress_get_main_channel_code();
}

/**
* @deprecated use twitchpress_get_app_id()
*/
function twitchpress_get_main_client_id() {
    return get_option( 'twitchpress_main_client_id' );
}  

/**
* Stores the main application token and main application scopes
* as an option value.
* 
* @param mixed $token
* @param mixed $scopes
* 
* @version 2.0
* 
* @deprecated 2.3.0 Use object registry approach.
* @see TwitchPress_Object_Registry()
*/
function twitchpress_update_main_client_token( $token, $scopes ) {
    update_option( 'twitchpress_main_token', $token );
    update_option( 'twitchpress_main_token_scopes', $scopes );
}