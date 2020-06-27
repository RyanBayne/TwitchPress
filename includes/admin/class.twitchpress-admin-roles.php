<?php
/**
 * Installation class only - both roles and capabilities are installed. 
 * 
 * When possible we will add roles and capabilities required for extensions to 
 * avoid duplicate capabilities that offer different levels of aaccess. 
 *
 * @class    TwitchPress_Roles
 * @version  1.0
 * @package  TwitchPress/ Classes
 * @category Class
 * @author   Ryan Bayne
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Roles_Capabilities_Installation' ) ) :

class TwitchPress_Roles_Capabilities_Installation {

    public function fullarray() {
        $array = array();
                  
        // Main Twitch Channel Editor 
        $array['twitchpress_role_main_channel_editor'] = array(
            'title' => __( 'Main Channel Editor', 'twitchpress' ),
            'desc'  => __( '', 'twitchpress' ),
            'caps'  => array(        
                'twitchpress_edit_stream_info'         => array( 'title' => __( 'Edit Stream Status', 'twitchpress' ), 'desc' => __( '', 'twitchpress' ) ),
                'twitchpress_run_commercials'          => array( 'title' => __( 'Run Commercials', 'twitchpress' ), 'desc' => __( '', 'twitchpress' ) ),
                'twitchpress_edit_video_info'          => array( 'title' => __( 'Edit Videos', 'twitchpress' ), 'desc' => __( '', 'twitchpress' ) ),
                'twitchpress_upload_videos'            => array( 'title' => __( 'Upload Videos', 'twitchpress' ), 'desc' => __( '', 'twitchpress' ) ),
                'twitchpress_create_events'            => array( 'title' => __( 'Create Events', 'twitchpress' ), 'desc' => __( '', 'twitchpress' ) ),
                'twitchpress_start_reruns'             => array( 'title' => __( 'Start Reruns', 'twitchpress' ), 'desc' => __( '', 'twitchpress' ) ),
                'twitchpress_start_premiers'           => array( 'title' => __( 'Star Premiers', 'twitchpress' ), 'desc' => __( '', 'twitchpress' ) ),
                'twitchpress_download_past_broadcasts' => array( 'title' => __( 'Download Past Broadcasts', 'twitchpress' ), 'desc' => __( '', 'twitchpress' ) ),
            )
        );
        $array['twitchpress_role_main_channel_editor'] = apply_filters( 'twitchpress_role_main_channel_editor', $array['twitchpress_role_main_channel_editor'] );
        
        // Main Twitch Channel Moderator 
        $array['twitchpress_role_main_channel_moderator'] = array(
            'title' => __( 'Main Channel Moderator', 'twitchpress' ),
            'desc'  => __( '', 'twitchpress' ),
            'caps'  => array(        
                'twitchpress_time_out_users'             => array( 'title' => __( 'Time Out Users', 'twitchpress' ), 'desc' => __( '', 'twitchpress' ) ),
                'twitchpress_ban_users'                  => array( 'title' => __( 'Ban Users', 'twitchpress' ), 'desc' => __( '', 'twitchpress' ) ),
                'twitchpress_control_slow_mode'          => array( 'title' => __( 'Control Slow Mode', 'twitchpress' ), 'desc' => __( '', 'twitchpress' ) ),
                'twitchpress_control_sub_chat_mode'      => array( 'title' => __( 'Control Chat Mod', 'twitchpress' ), 'desc' => __( '', 'twitchpress' ) ),
                'twitchpress_control_follower_chat_mode' => array( 'title' => __( 'Control Follower Chat Mode', 'twitchpress' ), 'desc' => __( '', 'twitchpress' ) ),
            )
        );
        $array['twitchpress_role_main_channel_moderator'] = apply_filters( 'twitchpress_role_main_channel_moderator', $array['twitchpress_role_main_channel_editor'] );
        
        // Main Twitch Channel VIP 
        $array['twitchpress_role_main_channel_vip'] = array(
            'title' => __( 'Main Channel Editor', 'twitchpress' ),
            'desc'  => __( '', 'twitchpress' ),
            'caps'  => array(
                'twitchpress_no_slow_mode'   => array( 'title' => __( 'Slow Mode Immunity', 'twitchpress' ), 'desc' => __( '', 'twitchpress' ) ),
                'twitchpress_sub_only'       => array( 'title' => __( 'Subscribers Only Access', 'twitchpress' ), 'desc' => __( '', 'twitchpress' ) ),
                'twitchpress_followers_only' => array( 'title' => __( 'Followers Only Access', 'twitchpress' ), 'desc' => __( '', 'twitchpress' ) ),
                'twitchpress_all_chat_rooms' => array( 'title' => __( 'All Chat Rooms', 'twitchpress' ), 'desc' => __( '', 'twitchpress' ) ),
                'twitchpress_chat_links'     => array( 'title' => __( 'Post Chat Links', 'twitchpress' ), 'desc' => __( '', 'twitchpress' ) ),
            )
        );
        $array['twitchpress_role_main_channel_vip'] = apply_filters( 'twitchpress_role_main_channel_vip', $array['twitchpress_role_main_channel_editor'] );
        
        // Main Channel Twitch Subscriber Plan 1000
        $array['twitchpress_role_subplan_1000'] = array(
            'title' => __( 'Level One Subscriber', 'twitchpress' ),
            'desc'  => __( '', 'twitchpress' ),
            'caps'  => array(        
                //'twitchpress_subplan_1000' => array( 'title' => __( '', 'twitchpress' ), 'desc' => __( '', 'twitchpress' ) ),
            )
        );
        $array['twitchpress_role_subplan_1000'] = apply_filters( 'twitchpress_role_subplan_1000', $array['twitchpress_role_main_channel_editor'] );
        
        // Main Channel Twitch Subscriber Plan 2000
        $array['twitchpress_role_subplan_2000'] = array(
            'title' => __( 'Level Two Subscriber', 'twitchpress' ),
            'desc'  => __( '', 'twitchpress' ),
            'caps'  => array(        
                //'twitchpress_subplan_2000' => array( 'title' => __( '', 'twitchpress' ), 'desc' => __( '', 'twitchpress' ) ),
            )
        );
        $array['twitchpress_role_subplan_2000'] = apply_filters( 'twitchpress_role_subplan_2000', $array['twitchpress_role_main_channel_editor'] );
        
        // Main Channel Twitch Subscriber Plan 3000
        $array['twitchpress_role_subplan_3000'] = array(
            'title' => __( 'Level Three Subscriber', 'twitchpress' ),
            'desc'  => __( '', 'twitchpress' ),
            'caps'  => array(        
                //'twitchpress_subplan_3000' => array( 'title' => __( '', 'twitchpress' ), 'desc' => __( '', 'twitchpress' ) ),
            )
        ); 
        $array['twitchpress_role_subplan_3000'] = apply_filters( 'twitchpress_role_subplan_3000', $array['twitchpress_role_main_channel_editor'] );
                
        return $array;
    }

    public function add_roles_and_capabilities() {
        $full_array = $this->fullarray();
                           
        foreach( $full_array as $role => $role_array ) 
        {
            $capabilities_array = array(); 
            
            foreach( $role_array['caps'] as $capability => $cap_array )
            {
                $capabilities_array[] = $capability;

                global $wp_roles;
                $wp_roles->add_cap( $role, $capability ); 
            }
            
            add_role( $role, $role_array['title'], $capabilities_array );
        }
    }
    
    public function remove_roles_and_capabilities() { 
        $full_array = $this->fullarray();
        
        foreach( $full_array as $role => $role_array )
        {
            foreach( $role_array['caps'] as $capability => $cap_array )
            {
                $capabilities_array[] = $capability;

                global $wp_roles;
                $wp_roles->remove_cap( $role, $capability ); 
            }
                        
            if( get_role( $role ) ){
                remove_role( $role );
            }
        }
    }

}

endif;