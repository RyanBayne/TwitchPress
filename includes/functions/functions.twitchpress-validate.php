<?php
/**
 * TwitchPress - Validation Functions
 *
 * Functions with strict conditions. Functions here can have unlimited arguments
 * and should not be constrained for the sake for performance. The input should
 * match strict requirements i.e. patterns, limits, dates and times.
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
* Checks if an extension is loaded on the server
* @uses get_loaded_extensions()
* @param string $giving_extension (name of the extension)
* @return boolean 
*/
function twitchpress_is_extensionloaded( $giving_extension ){      
    $loaded_extensions = get_loaded_extensions();
    foreach( $loaded_extensions as $key => $extension){
        if( $extension == $giving_extension){
            return true;
        }
    }
    return false;
} 
     
/**
* Checks if value is valid a url (http https ftp)
* 1. Does not check if url is active
* 2. Removes a filename if exists
* 
* @uses dirname()
* @return true if valid false if not a valid url
* @param url $url
*/
function twitchpress_is_url( $url ){            
    if (!preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $url ) ){
        return false;
    } else {
        return true;
    }
}
 
/**
* Determines if numeric value is decimal.
* 1. checks if value is actually numeric first
* 
* @returns boolean
* 
* @version 2.0
*/
function twitchpress_is_decimalnumber( $val ){       
    return is_numeric( $val ) && floor( $val ) != $val;    
}
        
/**
* Checks if url has an image extension, does not validate that resource exists
* @returns boolean
*/
function twitchpress_is_image_url( $img_url ){       
    $img_formats = array( "png", "jpg", "jpeg", "gif", "tiff", "bmp"); 
    $path_info = pathinfo( $img_url );
    if(is_array( $path_info) && isset( $path_info['extension'] ) ){
        if (in_array(strtolower( $path_info['extension'] ), $img_formats) ) {
            return true;
        }
    }
    return false;
}