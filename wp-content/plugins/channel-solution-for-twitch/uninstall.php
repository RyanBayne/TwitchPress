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
    
}   

/**
* Uninstall options related to the uninstall procedure itself.
* 
* @version 1.0
*/
function twitchpress_remove_uninstallation_options() {

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
function twitchpress_remove_extensions() {
    // Include the array of known extensions.
    include_once( 'extensions.php' );
      
    foreach( twitchpress_extensions_array() as $extensions_group_key => $extensions_group_array ) {
        foreach( $extensions_group_array as $extension_name => $extension_array ) {
            deactivate_plugins( $extension_name, true );
            uninstall_plugin( $extension_name );
        }
    }     
}

/**
* Remove all user data created by the core plugin.
* 
* @version 1.0
*/
function twitchpress_remove_user_data() {
    // Include the array of known user meta keys.
    include_once( 'meta.php' );
    
    foreach( twitchpress_meta_array() as $metakey_group_key => $metakey_group_array ) {
        foreach( $metakey_group_array as $metakey => $metakey_array ) {
            delete_option( $metakey );
        }
    }
}

/**
* Remove media created by TwitchPress. 
* 
* @version 1.0
*/
function twitchpress_remove_media() {
    
}

if( 'yes' == get_option( 'twitchpress_remove_options' ) ) { twitchpress_remove_options(); }
if( 'yes' == get_option( 'twitchpress_remove_feed_posts' ) ) { twitchpress_remove_feed_posts(); }
if( 'yes' == get_option( 'twitchpress_remove_database_tables' ) ) { twitchpress_remove_database_tables(); }
if( 'yes' == get_option( 'twitchpress_remove_extensions' ) ) { twitchpress_remove_extensions(); }
if( 'yes' == get_option( 'twitchpress_remove_user_data' ) ) { twitchpress_remove_user_data(); }
if( 'yes' == get_option( 'twitchpress_remove_media' ) ) { twitchpress_remove_media(); }

// Final action is removal of this group of options.
if( 'yes' == get_option( 'twitchpress_remove_uninstallation_options' ) ) { twitchpress_remove_uninstallation_options(); }
