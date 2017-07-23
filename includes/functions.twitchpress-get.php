<?php
/**
 * TwitchPress - Small Get Functions
 *
 * Make the plugins API easier with get functions. Try to avoid functions
 * that need to include files or create objects. The goal is for this file
 * to offer functions that don't come with drawbacks on performance. 
 *
 * @author   Ryan Bayne
 * @category Core
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
* Get visitors IP address.
* 
* @version 1.1
*/
function twitchpress_get_ip_address() {         
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];
    $ip = null;
    
    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }

    return $ip;
}

/**
* Count total number of "administrators". 
*/
function twitchpress_get_total_administrators( $partial_admin = false, $return_users = false ) {   
    $user_query = new WP_User_Query( array( 'role' => 'administrator' ) );
    return $user_query->total_users;      
} 

/**
 * Retrieves the value of a passed parameter in a URL string, positionally insensitive
 * 
 * @param $url - [string] URL string provided
 * @param $param - [string] The string parameter name to look for
 * @param $maxMatchLength - [int] The maximum match length for the value, defaults to 40
 * @param $matchSymbols - [bool] Sets the search to look for symbols in the value
 * 
 * @return $value - [string] The string value of that parameter searched for
 */ 
function twitchpress_getURLParamValue($url, $param, $maxMatchLength = 40, $matchSymbols = false){
    if ($matchSymbols){
        $match = '[\w._@#$%\^\*\(\)!+\\|-]';
    } else {
        $match = '[\w]';
    }
    
    //init and dump the chars into the regex
    $param_regex = '';
    $chars = str_split($param);
    
    // Build a char match for the param, case insensitive
    foreach ($chars as $char){
        $param_regex .= '[' . strtoupper($char) . strtolower($char) . ']';
    }
    
    $value_arr = array();
    preg_match('(' . $param_regex . '=' . $match . '{1,' . $maxMatchLength . '})', $url, $value_arr);
    
    // Dump to a string
    $value = $value_arr[0];
    
    // Strip out the identifier
    $value = preg_replace('([a-z]{1,40}=)', '', $value);
    
    // Clean memory
    unset($url, $param, $maxMatchLength, $matchSymbols, $match, $param_regex, $chars, $char, $value_arr);
    
    return $value;
}

/**
* Grabs an array of all URL parameters and values
* 
* @param $url - [string] URL string provided
* @param $maxMatchLength - [int] The maximum match length for all matches, defaults to 40
* @param $matchSymbols - [bool] Sets the search to look for symbols in the value
* 
* @return $parameters - [array] A keyed array of all values returned, key is param
*/
function twitchpress_getURLParams( $url, $maxMatchLength = 40, $matchSymbols = false ){
    if ($matchSymbols){
        $match = '[\w._@#$%\^\*\(\)!+\\|-]';
    } else {
        $match = '[\w]';
    }

    $matches = array();
    $parameters = array();
    preg_match('(([\w]{1,' . $maxMatchLength . '}=' . $match . '{1,' . $maxMatchLength . '}[&]{0,1}){1,' . $maxMatchLength . '})', $url, $matches);

    $split = split('&', $matches[0]);

    foreach ($split as $row) {
        $splitRow = split('=', $row);
        $parameters[$splitRow[0]] = $splitRow[1];
    }

    unset($url, $maxMatchLength, $matchSymbols, $match, $matches, $split, $row, $splitRow);

    return $parameters;
}