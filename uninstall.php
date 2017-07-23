<?php
/**
 * Uninstall plugin.
 *
 * @author      Ryan Bayne
 * @category    Core
 * @package     TwitchPress/Uninstaller
 * @version     1.0.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb, $wp_version;

if( get_option( 'twitchpress_removeall' ) == 'yes' ) {
    
}