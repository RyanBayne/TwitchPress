<?php
/**
 * TwitchPress $_POST processing for developer-toolbar requests!
 *
 * @author   Ryan Bayne
 * @category Shortcodes
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {    
    exit;
}

add_action( 'admin_post_twitchpress_api_version_switch', 'twitchpress_api_version_switch' );
  
function twitchpress_api_version_switch() {

    // Only users with the twitchpress_developer capability will be allowed to do this...
    if( !current_user_can( 'twitchpressdevelopertoolbar' ) ) 
    {      
        TwitchPress_Admin_Notices::add_wordpress_notice(
            'devtoolbar_twitchapiswitch_notice',
            'warning',
            false,
            __( 'No Permission', 'twitchpress' ),
            __( 'You do not have the TwitchPress Developer capability for this action. That permission must be added to your WordPress account first.', 'twitchpress' ) 
        );

        wp_redirect();
        exit;                      
    }
    
    if( TWITCHPRESS_API_NAME == 'kraken' )
    {
        update_option( 'twitchpress_apiversion', 6 );
        $version = 6;
        $name = 'Helix';        
    }
    elseif( TWITCHPRESS_API_NAME == 'helix' )
    {
        update_option( 'twitchpress_apiversion', 5 );
        $version = 5;
        $name = 'Kraken';    
    }

    TwitchPress_Admin_Notices::add_wordpress_notice(
        'devtoolbar_twitchapiswitch_notice',
        'success',
        false,
        __( 'Twitch API Version Changed', 'twitchpress' ),
        sprintf( __( 'You changed the Twitch API version to %d (%s)', 'twitchpress' ), $version, $name ) 
    );
        
    wp_redirect( wp_get_referer() );
    exit;    
}

add_action( 'admin_post_twitchpress_beta_testing_switch', 'twitchpress_beta_testing_switch' );
  
function twitchpress_beta_testing_switch() {

    // Only users with the twitchpress_developer capability will be allowed to do this...
    if( !current_user_can( 'twitchpressdevelopertoolbar' ) ) 
    {      
        TwitchPress_Admin_Notices::add_wordpress_notice(
            'devtoolbar_beta_testing_nopermission_notice',
            'warning',
            false,
            __( 'Request Rejected', 'twitchpress' ),
            __( 'You do not have the wp-capability (TwitchPress Developer) for this action.', 'twitchpress' ) 
        );

        wp_redirect();
        exit;                      
    }
    
    $beta_testing_switch = get_option( 'twitchpress_beta_testing' );
    
    if( $beta_testing_switch )
    {
        update_option( 'twitchpress_beta_testing', 0 );    
        TwitchPress_Admin_Notices::add_wordpress_notice(
            'devtoolbar_beta_testing_disabled_notice',
            'success',
            false,
            __( 'TwitchPress Beta Testing Disabled', 'twitchpress' ),
            __( 'Beta testing has been turned off. Some features might be hidden and others may operate differently.', 'twitchpress' ) 
        );        
    }
    else
    {
        update_option( 'twitchpress_beta_testing', 1 );
        TwitchPress_Admin_Notices::add_wordpress_notice(
            'devtoolbar_beta_testing_activated_notice',
            'success',
            false,
            __( 'TwitchPress Beta Testing Enabled', 'twitchpress' ),
            __( 'You activated beta testing for TwitchPress. Please disable if you have not checked this versions risk-level and you are on a live site.', 'twitchpress' ) 
        ); 
    }
        
    wp_redirect( wp_get_referer() );
    exit;    
}