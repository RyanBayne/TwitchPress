<?php
function twitchpress_streamlabs_validate_code( $code ) {
    if( strlen ( $code ) !== 40  ) {
        return false;
    }           
    
    if( !ctype_alnum( $code ) ) {
        return false;
    }
    
    return true;
}

function twitchpress_streamlabs_update_main_code( $code ) {
    update_option( 'twitchpress_allapi_streamlabs_default_code', $code );    
}    

function twitchpress_streamlabs_update_main_owner( $wp_user_id ) {
    update_option( 'twitchpress_allapi_streamlabs_default_owner', $wp_user_id );    
}  

function twitchpress_streamlabs_update_main_access_token( $access_token ) {
    update_option( 'twitchpress_allapi_streamlabs_default_access_token', $access_token );
}

function twitchpress_streamlabs_update_main_expires_in( $expires_in ) {
    update_option( 'twitchpress_allapi_streamlabs_default_expires_in', $expires_in );        
}

function twitchpress_streamlabs_update_main_refresh_token( $refresh_token ) {
    update_option( 'twitchpress_allapi_streamlabs_default_refresh_token', $refresh_token );        
}

function twitchpress_streamlabs_update_user_code( $wp_user_id, $code ) {
    update_user_meta( $wp_user_id, 'twitchpress_streamlabs_code', $code );  
}

function twitchpress_streamlabs_update_user_access_token( $wp_user_id, $access_token ) {
    update_user_meta( $wp_user_id, 'twitchpress_streamlabs_access_token', $access_token );
}

function twitchpress_streamlabs_update_user_expires_in( $wp_user_id, $expires_in ) {
    update_user_meta( $wp_user_id, 'twitchpress_streamlabs_expires_in ', $expires_in );
}
    
function twitchpress_streamlabs_update_user_refresh_token( $wp_user_id, $refresh_token ) {
    update_user_meta( $wp_user_id, 'twitchpress_streamlabs_refresh_token', $refresh_token );
}

function twitchpress_streamlabs_update_user_scope( $wp_user_id, $scope ) {
    update_user_meta( $wp_user_id, 'twitchpress_streamlabs_scope', $scope );
}

function twitchpress_streamlabs_get_main_code( $code ) {
    return get_option( 'twitchpress_allapi_streamlabs_default_code', $code );    
}    

function twitchpress_streamlabs_get_main_owner() {
    return get_option( 'twitchpress_allapi_streamlabs_default_owner' );    
}  

function twitchpress_streamlabs_get_main_access_token() {
    return get_option( 'twitchpress_allapi_streamlabs_default_access_token' );
}

function twitchpress_streamlabs_get_main_expires_in() {
    return get_option( 'twitchpress_allapi_streamlabs_default_expires_in' );        
}

function twitchpress_streamlabs_get_main_refresh_token() {
    return get_option( 'twitchpress_allapi_streamlabs_default_refresh_token' );        
}

function twitchpress_streamlabs_get_user_code( $wp_user_id ) {
    return get_user_meta( $wp_user_id, 'twitchpress_streamlabs_code', true );  
}

function twitchpress_streamlabs_get_user_access_token( $wp_user_id ) {
    return get_user_meta( $wp_user_id, 'twitchpress_streamlabs_access_token', true );
}

function twitchpress_streamlabs_get_user_expires_in( $wp_user_id ) {
    return get_user_meta( $wp_user_id, 'twitchpress_streamlabs_expires_in ', true );
}
    
function twitchpress_streamlabs_get_user_refresh_token( $wp_user_id ) {
    return get_user_meta( $wp_user_id, 'twitchpress_streamlabs_refresh_token', true );
}

function twitchpress_streamlabs_get_user_scope( $wp_user_id ) {
    return get_user_meta( $wp_user_id, 'twitchpress_streamlabs_scope', true );
}