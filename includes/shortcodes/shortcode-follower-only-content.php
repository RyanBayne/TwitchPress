<?php
/**
 * TwitchPress Follower-Only Shortcode 
 * 
 * This is a wrapper shortcode that will hide content unless the visitor is logged 
 * into WP and is a follower on the main or giving channel.
 *
 * @author   Ryan Bayne
 * @category Shortcodes
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {    
    exit;
}
                                                        
add_shortcode( 'twitchpress_followers_only', 'twitchpress_shortcode_follower_only_content' );
                                                  
/**
* Follower-only content shortcode...
* 
* @param mixed $atts
* 
* @version 1.0
*/
function twitchpress_shortcode_follower_only_content( $atts, $content = null  ) {            
    $html_output = ''; 
    
    // Visitor must be logged into the blog...
    if( !is_user_logged_in() ) { 
        $html_output .= '<p>' . __( 'Unlock more content here by following my Twitch channel and login into this site...', 'twitchpress' ) . '</p>'; 
        return $html_output; 
    }
    
    // Visitor must be following the main channel...
    if( !twitchpress_is_user_following( get_current_user_id() ) ) {
        $html_output .= '<p>' . __( 'Unlock hidden content on this page by following my Twitch channel...', 'twitchpress' ) . '</p>';
        return $html_output;    
    }   
    
    // Final - prepare $content for output...
    $html_output = $content;
               
    return $html_output;    
}
          