<?php
/**
 * TwitchPress Admin User oAuth for Streamlabs
 * 
 * Redirect admin user to admin-post.php?action=twitchpress_streamlabs_admin_oauth_listener
 *
 * @author   Ryan Bayne
 * @category Shortcodes
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {    
    exit;
}

function TwitchPress_Streamlabs_Admin_oAuth_Listener() {
            
    // Return when DOING none related things...
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
    if( defined( 'DOING_CRON' ) && DOING_CRON ) { return; }         
    if( defined( 'DOING_AJAX' ) && DOING_AJAX ) { return; }
              
    // This listener is for administrators only...  
    if( !is_user_logged_in() ) { return; }        
    if( !current_user_can( 'activate_plugins' ) ) { return; } 
    $wp_user_id = get_current_user_id();
     
    // Ensure $_GET values present for an API return...
    if( isset( $_GET['error'] ) ) { return; } 
    if( !isset( $_GET['code'] ) ) { return; }     
    if( !isset( $_GET['state'] ) ) { return; }    
    
    if( !$transient_state = get_transient( 'twitchpress_streamlabs_oauthstate_' . $_GET['state'] ) ) {      
        TwitchPress_Admin_Notices::add_custom_notice( 'streamlabs_admin_oauth_listener', __( 'Streamlabs Listener: No matching transient.', 'twitchpress' ) );
        return;
    }
          
    // Ensure the reason for this request is an attempt to set the main channels credentials...
    if( !isset( $transient_state['reason'] ) ) {
        TwitchPress_Admin_Notices::add_custom_notice( 'streamlabs_admin_oauth_listener', __( 'Streamlabs Listener: no reason for request.', 'twitchpress' ) );            
        return;
    }
          
    // Ensure we have the admin view or page the user needs to be sent to... 
    if( $transient_state['reason'] !== 'streamlabsextensionowneroauth2request' ) {         
        TwitchPress_Admin_Notices::add_custom_notice( 'streamlabs_admin_oauth_listener', __( 'Streamlabs Listener: reason rejected.', 'twitchpress' ) );
        return;    
    }        
         
    // Ensure we have the admin view or page the user needs to be sent to... 
    if( !isset( $transient_state['redirectto'] ) ) {         
        TwitchPress_Admin_Notices::add_custom_notice( 'streamlabs_admin_oauth_listener', __( 'Streamlabs Listener: "redirectto" value does not exist.', 'twitchpress' ) );
        return;    
    }  
          
    // Validate the code...
    if( !twitchpress_streamlabs_validate_code( $_GET['code'] ) ) {        
        TwitchPress_Admin_Notices::add_custom_notice( 'streamlabs_admin_oauth_listener', __( 'Streamlabs Listener: invalid code.', 'twitchpress' ) );
        return;
    }
      
    // Update the main Streamlab account details using the current admins credentials...
    twitchpress_streamlabs_update_main_code( $_GET['code'] );
    twitchpress_streamlabs_update_main_owner( $wp_user_id );        
      
    // Request a token on behalf of the main administrator (current user)...
    $streamlabs = new TWITCHPRESS_Streamlabs_API();
    $request_body = $streamlabs->api_request_token();
    if( $request_body === false ) 
    {
        TwitchPress_Admin_Notices::add_custom_notice( 'streamlabs_main_tokenrequest', __( 'The request for a Streamlabs access token has failed this time, please try again.') );
        return false;                
    }
    
    // Update main account credentials...
    twitchpress_streamlabs_update_main_access_token( $request_body->access_token );
    twitchpress_streamlabs_update_main_expires_in( $request_body->expires_in );
    twitchpress_streamlabs_update_main_refresh_token( $request_body->refresh_token );
    
    // Update current admin-users Streamlabs API credentials...
    twitchpress_streamlabs_update_user_code( $wp_user_id, $_GET['code'] );
    twitchpress_streamlabs_update_user_access_token( $wp_user_id, $request_body->access_token );
    twitchpress_streamlabs_update_user_expires_in( $wp_user_id, $request_body->expires_in );
    twitchpress_streamlabs_update_user_refresh_token( $wp_user_id, $request_body->refresh_token );

    // Token notice...
    TwitchPress_Admin_Notices::add_custom_notice( 'streamlabs_mainapplicationsetup', __( 'Streamlabs returned a token and you can now make calls to the Streamlabs API.') );
               
    // redirect to admin-post.php?action=twitchpress_streamlabs_admin_oauth_listener
    // Forward user to the custom destinaton i.e. where they were before oAuth2. 
    twitchpress_redirect_tracking( $transient_state['redirectto'], __LINE__, __FUNCTION__ );
    exit;
}
        
add_action( 'wp_loaded', 'TwitchPress_Streamlabs_Admin_oAuth_Listener', 1 );