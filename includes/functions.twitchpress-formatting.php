<?php
/**
 * TwitchPress - Data Formatting Functions
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
* Find the middle of a string and split it there.
* 
* @returns mixed 
* @version 1.0
*/
function twitchpress_string_half( $string, $ret = null ) {        
    $a = array();
    $splitstring1 = substr( $string, 0, floor( strlen( $string ) / 2 ) );
    $splitstring2 = substr( $string, floor (strlen( $string ) / 2 ) );

    if ( substr( $splitstring1, 0, -1 ) != ' ' AND substr( $splitstring2, 0, 1 ) != ' ' )
    {
        $middle = strlen( $splitstring1 ) + strpos( $splitstring2, ' ' ) + 1;
    }
    else
    {
        $middle = strrpos( substr( $string, 0, floor( strlen( $string ) / 2) ), ' ' ) + 1;    
    }

    if( $ret == 1 )
    {
        $string1 = substr( $string, 0, $middle );  // "The Quick : Brown Fox Jumped "
        return $string1;
    }
    elseif( $ret == 2 )
    {
        $string2 = substr( $string, $middle );  // "Over The Lazy / Dog" 
        return $string2;    
    }
    
    $a[] = $string1;
    $a[] = $string2;
                    
    return $a;
}

/**
 * Normalize postcodes.
 *
 * Remove spaces and convert characters to uppercase.
 *
 * @param string $postcode
 * @return string Sanitized postcode.
 */
function twitchpress_normalize_postcode( $postcode ) {          
    return preg_replace( '/[\s\-]/', '', trim( strtoupper( $postcode ) ) );
}

/**
 * format_phone function.
 *
 * @param mixed $tel
 * @return string
 */
function twitchpress_format_phone_number( $tel ) {            
    return str_replace( '.', '-', $tel );
}

/**
 * Make a string lowercase.
 * Try to use mb_strtolower() when available.
 *
 * @param  string $string
 * @return string
 */
function twitchpress_strtolower( $string ) {                    
    return function_exists( 'mb_strtolower' ) ? mb_strtolower( $string ) : strtolower( $string );
}

/**
 * Trim a string and append a suffix.
 * @param  string  $string
 * @param  integer $chars
 * @param  string  $suffix
 * @return string
 */
function twitchpress_trim_string( $string, $chars = 200, $suffix = '...' ) {      
    if ( strlen( $string ) > $chars ) {
        if ( function_exists( 'mb_substr' ) ) {
            $string = mb_substr( $string, 0, ( $chars - mb_strlen( $suffix ) ) ) . $suffix;
        } else {
            $string = substr( $string, 0, ( $chars - strlen( $suffix ) ) ) . $suffix;
        }
    }
    return $string;
}     


/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 * @param string|array $var
 * @return string|array
 */
function twitchpress_clean( $var ) {
    if ( is_array( $var ) ) {
        return array_map( 'twitchpress_clean', $var );
    } else {
        return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
    }
}