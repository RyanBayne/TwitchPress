<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( !class_exists( 'TwitchPress_Feeds' ) ) :

/**
 * TwitchPress Class for handling channel feed services.
 * 
 * This does not register custom post types. 
 * 
 * @class    TwitchPress_Feeds
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Core
 * @version  1.0.0
 */
class TwitchPress_Feeds {

    public function __construct() {      
    
    }  
  
    public static function init() {         
        add_action( 'save_post', array( __CLASS__, 'publish_to_feed' ), 2, 3 );
    }
    
    /**
    * Shares post to Twitch. 
    * 
    * When a twitchfeed post is published, this action is called and shares the
    * authored content with Twitch.tv channel feeds.
    * 
    * @author Ryan Bayne
    * @version 2.0
    * 
    * @param mixed $ID post ID
    * @param mixed $post the post object
    */
    public static function publish_to_feed( $ID, $post, $update = false ) {
        global $bugnet; 
        
        // Avoid sharing during an AUTO_SAVE
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }     

        if( !current_user_can( 'administrator' ) ) {
            $bugnet->log( __FUNCTION__, sprintf( __( 'Share to feed rejected for post ID: %s because user does not have administrator role.', 'twitchpress' ), $ID ), array( 'level' => 100 ), true, false );
            return false;
        }
        
        // Administrators need to activate sharing for each post_type.
        if( !twitchpress_is_posttype_shareable( $post->post_type ) ) {
            $bugnet->log( __FUNCTION__, sprintf( __( 'Share to feed rejected for post ID: %s because post_type: %s is not shareable.', 'twitchpress' ), $ID, $post->post_type ), array( 'level' => 100 ), true, false );
            return false;
        }
        
        // If post-type is not twitchfeed, ensure manual share checkbox was selected. 
        if( !isset( $_POST['twitchpress_share_post_option'] ) ) {
            $bugnet->log( __FUNCTION__, sprintf( __( 'Share to feed rejected for post ID: %s because user did not select the share to Twitch option.', 'twitchpress' ), $ID, $post->post_type ), array( 'level' => 100 ), true, false );
            return false;  
        }
        
        // Only share posts that are published. 
        if( 'publish' !== $post->post_status ) {
            $bugnet->log( __FUNCTION__, sprintf( __( 'Share to feed rejected for post ID: %s because post status is %s', 'twitchpress' ), $ID, $post->post_status ), array( 'level' => 100 ), true, false );
            return false;
        }
        
        // Ensure post content is not empty i.e. user only edited post and WP auto saved.
        if( empty( $post->post_content ) ) {
            $bugnet->log( __FUNCTION__, sprintf( __( 'Share to feed rejected for post ID: %s because post_content is empty', 'twitchpress' ), $ID ) );
            return false;
        }
        
        // Has the post already been shared - since 26th Sep 2017.
        if( get_option( '_twitchpress_shared' ) == 'yes' ) {
            $bugnet->log( __FUNCTION__, sprintf( __( 'Share to feed rejected for post ID: %s because the post has already been shared.', 'twitchpress' ), $ID ), array( 'level' => 100 ), true, false );
            return false;                        
        }        
        
        // Has the post already been shared - pre 26th Sep 2017.
        if( get_option( 'twitchpress_feed_item_id' ) ) {
            $bugnet->log( __FUNCTION__, sprintf( __( 'Share to feed rejected for post ID: %s because the post was shared to a channel feed.', 'twitchpress' ), $ID ), array( 'level' => 100 ), true, false );
            return false;                        
        }
        
        $bugnet->log( __FUNCTION__, sprintf( __( 'Share to feed requested for post ID: %s and is Post-Type: %s', 'twitchpress' ), $ID, $post->post_type ), array( 'level' => 200 ), true, false );
        
        // Wake the Kraken up! 
        $kraken = new TWITCHPRESS_Kraken5_Calls();
        
        // Enhance post content using prepend and append values.
        $original_content = array( 'content' => $post->post_content );
        
        $enhanced_content = twitchpress_prepare_post_to_feed_content( $original_content );
        
        $result = $kraken->postFeedPost( $enhanced_content, array() );
        
        // Kraken returns a status of "200" on success. 
        if( '200' == $result ) 
        {
            $bugnet->log( __FUNCTION__, sprintf( __( 'Share to feed successful for post ID: %s', 'twitchpress' ), $ID ) );
        
            // Indicate post shared, could be to any network. 
            add_post_meta( $ID, '_twitchpress_shared', 'yes' );
                    
            // We now need to get the Twitch feed item and store its ID in post meta.
            // Lets assume another post was published at the same time.
            $feed_items = $kraken->getFeedPosts( $kraken->get_main_channel_id(), 3 );

            // Remove items that were posted more than 30 seconds ago. 
            $total = 0;
            if( $feed_items ) {
                foreach( $feed_items as $item_id => $item_array ) {
                    $timestamp = twitchpress_convert_created_at_to_timestamp( $item_array['created_at'] );
                    $in_thirty = $timestamp + 15;
                    if( time() > $in_thirty ) {
                        unset( $feed_items[ $item_id ] );
                    }       
                }    
                
                // If we have a single Twitch feed item than it's probably the one we just posted, right?!
                if( count( $feed_items ) == 1 ) {
                    
                    // Add the channel feed item ID to our post and they become married. 
                    add_post_meta( $ID, 'twitchpress_feed_item_id', $item_id );
                        
                } else { 
                    // If left with 2 or more items, attempt to match content but
                    // we would need a version of the WP content that has no HTML, maybe!    
                }
            }
        } 
        else 
        {
            // Display a notice indicating a likely failure to update feed with new post. 
            $bugnet->log( __FUNCTION__, sprintf( __( 'Share to feed failed for post ID: %s', 'twitchpress' ), $ID ) );
        }   
        
        unset( $kraken, $result );
    }
}  

endif;

TwitchPress_Feeds::init();