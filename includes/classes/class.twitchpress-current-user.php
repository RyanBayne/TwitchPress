<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists( 'TwitchPress_Current_User_Setter' ) ) :

/**
 * Add and sync values to WP core $current_user...    
 * 
 * @author   Ryan Bayne
 * @category Scripts
 * @package  TwitchPress
 * @since    1.0.0
 */
class TwitchPress_Current_User_Setter {
    public static function init() {
        add_action( 'init', array( __CLASS__, 'twitch' ), 5 ); 
    }   
    
    public static function twitch() {
        global $current_user;
        
        // Subscriber status...
        $current_user->__set( 'twitchpress_substatus_mainchannel', twitchpress_get_users_subscription_status( $current_user->ID ) );

        // Subscription plan...
        $current_user->__set( 'twitch_subscription_plan', twitchpress_get_users_sub_plan_name( $current_user->ID ) );
    
        // Follower Status...
        $current_user->__set( 'twitch_follower', twitchpress_get_users_follower_status( $current_user->ID ) );
    }
}

endif;


if( !class_exists( 'TwitchPress_Current_User' ) ) :

/**
 * Returns values from $current_user rather than user meta...    
 * 
 * @author   Ryan Bayne
 * @category Scripts
 * @package  TwitchPress
 * @since    1.0.0
 */
class TwitchPress_Current_User {
    public static function subscription_status( $channel = 'main' ) {
        global $current_user;
        return $current_user->__get( 'twitchpress_substatus_' . $channel . 'channel' );
    }
    
    public static function subscription_plan() {
        global $current_user;
        return $current_user->__get( 'twitch_subscription_plan' );
    }
    
    public static function follower() {
        global $current_user;
        return $current_user->__get( 'twitch_follower' );
    }
}         

endif;
