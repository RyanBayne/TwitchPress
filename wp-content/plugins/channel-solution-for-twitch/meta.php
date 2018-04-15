<?php
/**
* Arrays of all known meta keys. Multiple uses are planned for this data
* to aid in development and configuration.
* 
* @author Ryan R. Bayne
* @package TwitchPress
* @version 1.0
*/

function twitchpress_meta_array() {

    return array(
        'user'     => twitchpress_meta_user(),
    );   
     
}

function twitchpress_meta_user() { 
    $arr = array();

    $arr[ 'twitchpress_auth_time' ] = array();
    $arr[ 'twitchpress_token' ] = array();
    $arr[ 'twitchpress_token_refresh' ] = array();
    $arr[ 'twitchpress_code' ] = array();
    $arr[ 'twitchpress_twitch_id' ] = array();

    return $arr;  
}