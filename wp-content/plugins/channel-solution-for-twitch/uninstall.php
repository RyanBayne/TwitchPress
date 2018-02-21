<?php
/**
 * Uninstall plugin.
 *
 * @author      Ryan Bayne
 * @category    Core
 * @package     TwitchPress/Uninstaller
 * @version     1.2.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb, $wp_version;

if( get_option( 'twitchpress_removeall' ) == 'yes' ) {
    include_once( dirname( __FILE__ ) . '/includes/admin/class.twitchpress-admin-settings.php' );
    include_once( dirname( __FILE__ ) . '/includes/admin/class.twitchpress-admin-uninstall.php' );
    TwitchPress_Uninstall::removeall();    
}