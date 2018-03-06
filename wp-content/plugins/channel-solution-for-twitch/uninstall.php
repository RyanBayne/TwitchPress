<?php
/**
 * Uninstall plugin.
 * 
 * The uninstall.php file is a standard approach to running an uninstall
 * procedure for a plugin. It should be as simple as possible.
 *
 * @author      Ryan Bayne
 * @category    Core
 * @package     TwitchPress/Uninstaller
 * @version     2.0
 */
 
// Ensure plugin uninstall is being run by WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/**
* Uninstall all of the plugins options without any care. Meant as a developer tool and 
* a part of 100% uninstallation.
* 
* @version 2.0
*/
function twitchpress_remove_options() {
    // Include the array of known options (extensions might create some we do not know about yet)
    include_once( 'options.php' );
    
    // Some options need to be removed last. 
    $remove_last = array( 
        'twitchpress_remove_options', 
        'twitchpress_remove_feed_posts',
        'twitchpress_remove_database_tables',
        'twitchpress_remove_extensions',
        'twitchpress_remove_user_data',
        'twitchpress_remove_media' 
    );
    
    foreach( twitchpress_options_array() as $option_group_key => $option_group_array ) {
        foreach( $option_group_array as $option_key => $option_array ) {
            if( !in_array( $option_key, $remove_last ) ) {
                delete_option( $option_key );
            }
        }
    }
    
    // Now remove the uninstallation options. 
    delete_option( 'twitchpress_remove_options' );
    delete_option( 'twitchpress_remove_feed_posts' );
    delete_option( 'twitchpress_remove_database_tables' );
    delete_option( 'twitchpress_remove_extensions' );
    delete_option( 'twitchpress_remove_user_data' );
    delete_option( 'twitchpress_remove_media' );

}    

/**
* Remove feed posts (the core plugins custom post type)
* 
* @version 1.0
*/
function twitchpress_remove_feed_posts() {}

/**
* Remove database tables created by the TwitchPress core.
* 
* @version 1.0 
*/
function twitchpress_remove_database_tables() {}

/**
* Remove all TwitchPress extensions. 
* 
* @version 1.0
*/
function twitchpress_remove_extensions() {}

/**
* Remove all user data created by the core plugin.
* 
* @version 1.0
*/
function twitchpress_remove_user_data() {}

/**
* Remove media created by TwitchPress. 
* 
* @version 1.0
*/
function twitchpress_remove_media() {
    do_action( 'twitchpress_test' );
}

if( 'yes' == get_option( 'twitchpress_remove_options' ) ) { twitchpress_remove_options(); }
if( 'yes' == get_option( 'twitchpress_remove_feed_posts' ) ) { twitchpress_remove_feed_posts(); }
if( 'yes' == get_option( 'twitchpress_remove_database_tables' ) ) { twitchpress_remove_database_tables(); }
if( 'yes' == get_option( 'twitchpress_remove_extensions' ) ) { twitchpress_remove_extensions(); }
if( 'yes' == get_option( 'twitchpress_remove_user_data' ) ) { twitchpress_remove_user_data(); }
if( 'yes' == get_option( 'twitchpress_remove_media' ) ) { twitchpress_remove_media(); }
