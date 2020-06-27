<?php
/**
 * TwitchPress - Admin Only Functions
 *
 * This file will only be included during an admin request. Use a file
 * like functions.twitchpress-core.php if your function is meant for the frontend.   
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Admin
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Generate the complete nonce string, from the nonce base, the action 
 * and an item, e.g. twitchpress_delete_table_3.
 *
 * @since 1.0.0
 *
 * @param string      $action Action for which the nonce is needed.
 * @param string|bool $item   Optional. Item for which the action will be performed, like "table".
 * @return string The resulting nonce string.
 */
function twitchpress_nonce_prepend( $action, $item = false ) {
    $nonce = "twitchpress_{$action}";
    if ( $item ) {
        $nonce .= "_{$item}";
    }
    return $nonce;
}

/**
 * Get all WordPress TwitchPress screen ids.
 *
 * @return array
 * 
 * @version 1.3
 */
function twitchpress_get_screen_ids() {

    $screen_ids = array(
        'twitchpress_page_twitchpress_tools',
        'toplevel_page_twitchpress',
        'channels',
        'edit-channels',
        'twitchpress_page_twitchpress_data'
    );

    return apply_filters( 'twitchpress_screen_ids', $screen_ids );
}

/**
* Creates a new twitchchannel post. after passing 
* 
* Does strict checks to ensure that the channel has not already been entered.
* 
* @return integer post ID if a twitchchannel post is inserted.
* @return boolean false if wp_error on inserting channel.
* @returns boolean false if post already exists with the giving channel_ID.
* 
* @param integer $channel_id
* @param string $channel_name
* @param boolean $validated passing true will bypass a call to Kraken to validate channel.
* 
* @version 1.1
*/
function twitchpress_insert_channel( $channel_id, $channel_name, $validated = false ) {
    
    // Ensure the channel ID is not already linked to a channel post.  
    $does_channel_id_exist = twitchpress_channelid_in_postmeta( $channel_id );
    if( $does_channel_id_exist ) {   
        return false;    
    }
   
    // Ensure post slug does not already exist.
    $post_name = sanitize_title( $channel_name );
    $post_name_exists = twitchpress_does_post_name_exist( $post_name );                
    if( $post_name_exists ) {   
        return false;
    }
                                           
    // Create a new channel post.
    $post = array(
        'post_author' => 1,
        'post_title' => $channel_name,
        'post_name'  => $post_name,
        'post_status' => 'draft',
        'post_type' => 'channels',
    );
    
    $post_id = wp_insert_post( $post, true );
    
    if( is_wp_error( $post_id ) ) {     
        return false;
    }
    
    // Add Twitch channel ID to the post as a permanent pairing. 
    add_post_meta( $post_id, 'twitchpress_channel_id', $channel_id );
    
    return $post_id;
}