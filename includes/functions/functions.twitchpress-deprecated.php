<?php
/**
 * TwitchPress - Deprecated Functions
 *
 * Please add the WordPress core function for triggering and error if a
 * Deprecated function is used. 
 * 
 * Use: _deprecated_function( 'twitchpress_function_called', '2.1', 'twitchpress_replacement_function' );  
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
 * @deprecated example only
 */
function twitchpress_function_called() {
    _deprecated_function( 'twitchpress_function_called', '2.1', 'twitchpress_replacement_function' );
    //twitchpress_replacement_function();
}