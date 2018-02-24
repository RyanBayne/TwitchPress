<?php
/**
* File created January 2018. 
* 
* Transition get and update functions that use the WP core to this
* file and leave functions that perform API calls in the Twitch library.
* 
* This will be done systematically. All we need to do is come to this file
* first for the function we need. If it does not exist, find the existing
* solution and depreciate it or move it to here. 
*/

######################################################################
#                                                                    #
#                              USER                                  #
#                                                                    #
######################################################################

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
    if( !get_user_meta( $user_id, 'twitchpress_code', true ) ) {
        return false;
    }    
    if( !get_user_meta( $user_id, 'twitchpress_token', true ) ) {
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
    
    if( !$code = twitchpress_get_user_code( $user_id ) ) {  
        return false;
    }
    
    if( !$token = twitchpress_get_user_token( $user_id ) ) {  
        return false;
    }

    return array(
        'code'  => $code,
        'token' => $token
    );
}

/**
* Updates user code and token for Twitch.tv API.
* 
* We always store the Twitch user ID that the code and token matches. This
* will help to avoid mismatched data.
* 
* @param integer $wp_user_id
* @param string $code
* @param string $token
* 
* @version 1.0
*/
function twitchpress_update_user_oauth( $wp_user_id, $code, $token, $twitch_user_id ) {
    twitchpress_update_user_code( $wp_user_id, $code );
    twitchpress_update_user_token( $wp_user_id, $token ); 
    twitchpress_update_user_twitchid( $wp_user_id, $twitch_user_id );     
}

function twitchpress_get_user_twitchid_by_wpid( $user_id ) {
    return get_user_meta( $user_id, 'twitchpress_twitch_id', true );
}

/**
* Update users Twitch ID (in Kraken version 5 user ID and channel ID are the same).
* 
* @param integer $user_id
* @param integer $twitch_user_id
* 
* @version 1.0
*/
function twitchpress_update_user_twitchid( $user_id, $twitch_user_id ) {
    update_user_meta( $user_id, 'twitchpress_twitch_id', $twitch_user_id );    
}

function twitchpress_get_user_code( $user_id ) {
    return get_user_meta( $user_id, 'twitchpress_code', true );    
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

function twitchpress_get_user_token( $user_id ) {
    return get_user_meta( $user_id, 'twitchpress_token', true );    
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

function twitchpress_get_users_token_scopes( $user_id ) {
    return get_user_meta( $user_id, 'twitchpress_token_scope', true );    
}
 
/**
* Get the token_refresh string for extending a session. 
* 
* @param integer $user_id
* @param boolean $single
* 
* @version 1.0
*/
function twitchpress_get_user_token_refresh( $user_id, $single = true ) {
    return get_user_meta( $user_id, 'twitchpress_token_refresh', $single );
}

/**
* Update users oauth2 token_refresh string.
* 
* @param integer $user_id
* @param boolean $token
* 
* @version 1.0
*/
function twitchpress_update_user_token_refresh( $user_id, $token ) { 
    update_user_meta( $user_id, 'twitchpress_token_refresh', $token );    
}

function twitchpress_get_sub_plan( $wp_user_id, $twitch_channel_id ) {
    return get_user_meta( $wp_user_id, 'twitchpress_sub_plan_' . $twitch_channel_id, true  );    
}

######################################################################
#                                                                    #
#                           MAIN CHANNEL                             #
#                                                                    #
######################################################################

function twitchpress_get_main_channels_name() {
    return get_option( 'twitchpress_main_channel_name' );
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

function twitchpress_get_main_channels_token() {
    return get_option( 'twitchpress_main_channels_token' );
}

function twitchpress_get_main_channels_code() {
    return get_option( 'twitchpress_main_channels_code' );
}

function twitchpress_get_main_channels_wpowner_id() {
    return get_option( 'twitchpress_main_channels_wpowner_id' ); 
}

function twitchpress_get_main_channels_refresh() {
    return get_option( 'twitchpress_main_channels_refresh' );
}

function twitchpress_update_main_channels_code( $code ) {
    return update_option( 'twitchpress_main_channels_code', $code, false );
}

function twitchpress_update_main_channels_wpowner_id( $wp_user_id ) {
    return update_option( 'twitchpress_main_channels_wpowner_id', $wp_user_id, false );
}

function twitchpress_update_main_channels_token( $token ) { 
    return update_option( 'twitchpress_main_channels_token', $token, false );
}

function twitchpress_update_main_channels_refresh_token( $refresh_token ) {
    return update_option( 'twitchpress_main_channels_refresh', $refresh_token, false );
}

function twitchpress_update_main_channels_scope( $scope ) {
    return update_option( 'twitchpress_main_channels_scopes', $scope, false );
}
    
######################################################################
#                                                                    #
#                           APPLICATION                              #
#                                                                    #
######################################################################

/**
* @deprecated use twitchpress_get_app_id()
*/
function twitchpress_get_main_client_id() {
    return get_option( 'twitchpress_main_client_id' );
}  
          
function twitchpress_get_app_id() {
    return get_option( 'twitchpress_app_id' );
}          

function twitchpress_get_app_code() {
    return get_option( 'twitchress_app_code'); 
}

/**
* @deprecated use twitchpress_get_app_token()
*/
function twitchpress_get_main_client_token() {
    return get_option( 'twitchpress_main_token' );
}  

function twitchpress_get_app_token() {
    return get_option( 'twitchpress_app_token' );    
}

function twitchpress_get_app_redirect() {
    return get_option( 'twitchpress_app_redirect' ); 
}

/**
* Stores the main application token and main application scopes
* as an option value.
* 
* @param mixed $token
* @param mixed $scopes
* 
* @version 2.0
*/
function twitchpress_update_main_client_token( $token, $scopes ) {
    update_option( 'twitchpress_main_token', $token );
    update_option( 'twitchpress_main_token_scopes', $scopes );
}
    