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
    wp_die( __( 'Uninstallation file incorrectly requested for the TwitchPress plugin.', 'twitchpress' ) );
}
                                                             
if( 'yes' == get_option( 'twitchpress_remove_database_tables' ) ) { twitchpress_remove_database_tables(); }
if( 'yes' == get_option( 'twitchpress_remove_extensions' ) ) { twitchpress_remove_extensions(); }
if( 'yes' == get_option( 'twitchpress_remove_user_data' ) ) { twitchpress_remove_user_data(); }
if( 'yes' == get_option( 'twitchpress_remove_media' ) ) { twitchpress_remove_media(); }
if( 'yes' == get_option( 'twitchpress_remove_roles' ) ) { twitchpress_remove_roles(); }

// The plan is to offer different levels of uninstallation to make testing and re-configuration easier...
//if( 'yes' == get_option( 'twitchpress_remove_options' ) ) { twitchpress_remove_options_surgically(); }
if( 'yes' == get_option( 'twitchpress_remove_options' ) ) { twitchpress_remove_options(); }

/**
* Uninstall all of the plugins options with care! 
* 
* @version 2.0
*/
function twitchpress_remove_options() {      
    
    /*  Add this approach when working on uninstallation and improving cleanup...
    // Include settings so that we can run through defaults
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/admin/class.twitchpress-admin-settings.php' );
    $settings = TwitchPress_Admin_Settings::get_settings_pages();

    foreach ( $settings as $section ) {
        if ( !method_exists( $section, 'get_settings' ) ) {
            continue;
        }
        
        $subsections = array_unique( array_merge( array( '' ), array_keys( $section->get_sections() ) ) );

        foreach ( $subsections as $subsection ) {
            foreach ( $section->get_settings( $subsection ) as $value ) {
                if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
                    $autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
                    add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
                }
            }
        }
    } */
    
    delete_option( 'twitchpress_admin_notices' );
    delete_option( 'twitchpress_admin_notice_missingvaluesofferwizard' );
    delete_option( 'twitchpress_allapi_id_streamlabs' );
    delete_option( 'twitchpress_allapi_redirect_uri_streamlabs' );
    delete_option( 'twitchpress_allapi_secret_streamlabs' );
    delete_option( 'twitchpress_allapi_streamlabs_default_key' );
    delete_option( 'twitchpress_allapi_streamlabs_default_secret' );
    delete_option( 'twitchpress_allapi_streamlabs_default_uri' );
    delete_option( 'twitchpress_automatic_registration' );
    delete_option( 'twitchpress_bugnet_cache_action_hooks' );
    delete_option( 'twitchpress_display_actions' );
    delete_option( 'twitchpress_display_filters' );
    delete_option( 'twitchpress_login_button' );
    delete_option( 'twitchpress_login_button_text' );
    delete_option( 'twitchpress_login_loggedin_page_id' );
    delete_option( 'twitchpress_login_loginpage_position' );
    delete_option( 'twitchpress_login_loginpage_type' );
    delete_option( 'twitchpress_login_mainform_page_id' );
    delete_option( 'twitchpress_login_redirect_to_custom' );
    delete_option( 'twitchpress_login_requiretwitch' );
    delete_option( 'twitchpress_main_channels_refresh_token' );
    delete_option( 'twitchpress_registration_button' );
    delete_option( 'twitchpress_registration_requirevalidemail' );
    delete_option( 'twitchpress_registration_twitchonly' );
    delete_option( 'twitchpress_remove_database_tables' );
    delete_option( 'twitchpress_remove_extensions' );
    delete_option( 'twitchpress_remove_media' );
    delete_option( 'twitchpress_remove_options' );
    delete_option( 'twitchpress_remove_roles' );
    delete_option( 'twitchpress_remove_user_data' );
    delete_option( 'twitchpress_scope_analytics_read_extensions' );
    delete_option( 'twitchpress_scope_analytics_read_games' );
    delete_option( 'twitchpress_scope_bits_read' );
    delete_option( 'twitchpress_scope_chat_edit' );
    delete_option( 'twitchpress_scope_chat_read' );
    delete_option( 'twitchpress_scope_clips_edit' );
    delete_option( 'twitchpress_scope_user_edit' );
    delete_option( 'twitchpress_scope_user_edit_broadcast' );
    delete_option( 'twitchpress_scope_user_read_broadcast' );
    delete_option( 'twitchpress_scope_user_read_email' );
    delete_option( 'twitchpress_sync_timing' );
    delete_option( 'twitchpress_twitchapi_call_count' );
    delete_option( 'twitchpress_twitchpress-embed-everything_settings' );
    delete_option( 'twitchpress_twitchpress-login-extension_settings' );
    delete_option( 'twitchpress_twitchpress-subscriber-management_settings' );
    delete_option( 'twitchpress_twitchpress-sync-extension_settings' );
    delete_option( 'twitchpress_twitchpress-um-extension_settings' );
    delete_option( 'twitchpress_visitor_scope_analytics_read_extensions' );
    delete_option( 'twitchpress_visitor_scope_analytics_read_games' );
    delete_option( 'twitchpress_visitor_scope_bits_read' );
    delete_option( 'twitchpress_visitor_scope_channel_check_subscription' );
    delete_option( 'twitchpress_visitor_scope_channel_commercial' );
    delete_option( 'twitchpress_visitor_scope_channel_editor' );
    delete_option( 'twitchpress_visitor_scope_channel_read' );
    delete_option( 'twitchpress_visitor_scope_channel_stream' );
    delete_option( 'twitchpress_visitor_scope_channel_subscriptions' );
    delete_option( 'twitchpress_visitor_scope_chat_edit' );
    delete_option( 'twitchpress_visitor_scope_chat_read' );
    delete_option( 'twitchpress_visitor_scope_clips_edit' );
    delete_option( 'twitchpress_visitor_scope_collections_edit' );
    delete_option( 'twitchpress_visitor_scope_communities_edit' );
    delete_option( 'twitchpress_visitor_scope_communities_moderate' );
    delete_option( 'twitchpress_visitor_scope_openid' );
    delete_option( 'twitchpress_visitor_scope_user_blocks_edit' );
    delete_option( 'twitchpress_visitor_scope_user_blocks_read' );
    delete_option( 'twitchpress_visitor_scope_user_edit' );
    delete_option( 'twitchpress_visitor_scope_user_edit_broadcast' );
    delete_option( 'twitchpress_visitor_scope_user_follows_edit' );
    delete_option( 'twitchpress_visitor_scope_user_read' );
    delete_option( 'twitchpress_visitor_scope_user_read_broadcast' );
    delete_option( 'twitchpress_visitor_scope_user_read_email' );
    delete_option( 'twitchpress_visitor_scope_user_subscriptions' );
    delete_option( 'twitchpress_visitor_scope_viewing_activity_read' );
    
    // Deprecated September 2019 (Sandbox feature removed)
    delete_option( 'twitchress_sandbox_mode_falsereturns_switch' );
    delete_option( 'twitchress_sandbox_mode_generator_switch' );
    delete_option( 'twitchress_sandbox_mode_switch' );
}    

/**
* Remove database tables created by the TwitchPress core.
* 
* @version 1.0 
*/
function twitchpress_remove_database_tables() {/* no tables yet */}

/**
* Remove all TwitchPress extensions. 
* 
* @version 1.0
*/
function twitchpress_remove_extensions() {      
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

/**
 * Remove all roles and all custom capabilities added to 
 * both custom roles and core roles.
 * 
 * @version 1.0
 */
function twitchpress_remove_roles() {
    global $wp_roles;

    if ( ! class_exists( 'WP_Roles' ) ) {
        return;
    }

    if ( ! isset( $wp_roles ) ) {
        $wp_roles = new WP_Roles();
    }

    $capabilities = twitchpress_get_core_capabilities();
    $capabilities = array_merge( $capabilities, twitchpress_get_developer_capabilities() );
    
    foreach ( $capabilities as $cap_group ) {
        foreach ( $cap_group as $cap ) {
            $wp_roles->remove_cap( 'twitchpressdeveloper', $cap );
        }
    }

    remove_role( 'twitchpressdeveloper' );
}
