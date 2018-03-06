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
* @version 1.0
*/
function twitchpress_remove_options() {
    return;// Requires reworking as this method is outside of the plugins API.
    
    if( !current_user_can( 'activate_plugins' ) ) {
        return false;
    }
    
    // Remove the core options that do not exist as UI settings.
    delete_option( 'twitchpress_version' );
    
    // Uninstall registered user options (none core).
    // Include settings so that we can run through defaults
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH  . 'includes/admin/class.twitchpress-admin-settings.php' );

    $settings = TwitchPress_Admin_Settings::get_settings_pages();

    foreach ( $settings as $section ) {
        if ( ! method_exists( $section, 'get_settings' ) ) {
            continue;
        }
        $subsections = array_unique( array_merge( array( '' ), array_keys( $section->get_sections() ) ) );

        foreach ( $subsections as $subsection ) {
            foreach ( $section->get_settings( $subsection ) as $value ) {
                if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
                    $autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
                    delete_option( $value['id'] );
                }
            }
        }
    }
    
    return true;// to indicate option deletion was done.
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
