<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
* This method assumes that the "twitchpress_sub_plan_[channelid]"
* user meta value has been updated already. 
* 
* The update would usually be done by the Sync Extension. We
* call this method to apply UM roles based on what is stored by
* Sync Extension.
* 
* @param mixed $user_id
* @param mixed $channel_id
* @param mixed $api_response
* 
* @version 2.3
*/
function set_twitch_subscribers_um_role( $wp_user_id ) {
    global $bugnet;

    // Get the current filter to help us trace backwards from log entries. 
    $filter = current_filter();
    $action = current_action();

    // edit_user_profile filter passed array
    if( 'edit_user_profile' == $filter ) 
    {
        // This hook actually passes a user object. 
        $wp_user_id = $wp_user_id->data->ID;                
    }
                      
    /*  Other filters and actions that call this method. 
        1. personal_options_update
        2. edit_user_profile_update
        3. twitchpress_sync_new_twitch_subscriber
        4. twitchpress_sync_continuing_twitch_subscriber
        5. twitchpress_sync_discontinued_twitch_subscriber
        6. twitchpress_login_inserted_new_user 
    */

    $channel_id = twitchpress_get_main_channels_twitchid();
    
    // Avoid processing the main account or administrators so they are never downgraded. 
    $user_info = get_userdata( $wp_user_id );
    if( $wp_user_id === 1 || user_can( $wp_user_id, 'administrator' ) ) { return; }
                
    // Get subscription plan from user meta for the giving channel (based on channel ID). 
    $sub_plan = get_user_meta( $wp_user_id, 'twitchpress_sub_plan_' . $channel_id, true );

    // Get possible current UM role. 
    $current_role = get_user_meta( $wp_user_id, 'role', true );
    
    // Do nothing if the users UM role is admin (it is not administrator for UM)
    if( $current_role == 'admin' || $current_role == 'administrator' ) { return; }

    if( !$sub_plan ) 
    { 
        // User has no Twitch subscription, so apply default (none) role. 
        $next_role = get_option( 'twitchpress_um_subtorole_none', false );
    }
    else
    {
        $option_string = 'twitchpress_um_subtorole_' . $sub_plan;
        
        // Get the UM role paired with the $sub_plan
        $next_role = get_option( $option_string, false );
                          
        if( !$next_role )         
        {   
            // UM settings have not been setup or there is somehow a mismatch (that should never happen though).
            $next_role = get_option( 'twitchpress_um_subtorole_none', false );
        }
        else
        {
            $bugnet->log( __FUNCTION__, sprintf( __( 'UM Extension role for [%s] is [%s]', 'twitchpress-um' ), $option_string, $role ), array(), true, false );                    
        }              
    }

    // Log any change in history. 
    if( $current_role !== $next_role ) {
        $history_obj = new TwitchPress_History();
        $history_obj->new_entry( $next_role, $current_role, 'auto', __( '', 'twitchpress-um' ), $wp_user_id );    
    }
    
    update_user_meta( $wp_user_id, 'role', $ongoing_role );           
}
        
if( !function_exists( 'twitchpress_is_request' ) ) {
    /**
     * What type of request is this?
     *
     * Functions and constants are WordPress core. This function will allow
     * you to avoid large operations or output at the wrong time.
     * 
     * @param  string $type admin, ajax, cron or frontend.
     * @return bool
     */
    function twitchpress_is_request( $type ) {
        switch ( $type ) {
            case 'admin' :
                return is_admin();
            case 'ajax' :
                return defined( 'DOING_AJAX' );
            case 'cron' :
                return defined( 'DOING_CRON' );
            case 'frontend' :
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
    } 
}