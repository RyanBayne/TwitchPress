<?php
/**
* Arrays of all known extensions. Multiple uses are planned for this data
* to aid in development and configuration.
* 
* @author Ryan R. Bayne
* @package TwitchPress
* @version 1.0
*/

function twitchpress_extensions_array() {

    return array(
        'official'   => twitchpress_extensions_official(),
        'unofficial' => twitchpress_extensions_unofficial(),
    );   
     
}

function twitchpress_extensions_official() { 
    $arr = array();

    $arr[ 'twitchpress-deepbot-extension' ] = array();
    $arr[ 'twitchpress-embed-everything' ] = array();
    $arr[ 'twitchpress-login-extension' ] = array();
    $arr[ 'twitchpress-um-extension' ] = array();

    return $arr;  
}

function twitchpress_extensions_unofficial() { 
    $arr = array();

    //$arr[ 'example' ] = array();

    return $arr;  
}