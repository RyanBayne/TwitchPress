<?php  
/**
 * TwitchPress - require_once() on shortcode files for Subscriber Manager extension.
 * 
 * @author   Ryan Bayne
 * @category Shortcodes
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
   
require_once( plugin_basename( 'shortcodes/functions.twitchpress-subman-shortcode-umrole-update.php' ) );
