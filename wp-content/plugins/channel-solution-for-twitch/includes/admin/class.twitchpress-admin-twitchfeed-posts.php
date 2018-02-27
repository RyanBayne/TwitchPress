<?php
/**
 * TwitchPress Admin class for custom post type: twitchfeed
 * 
 * This does not register the post type. That is handled by TwitchPress_Post_types.
 * 
 * This class is used to add postboxes, register actions, filters etc.
 * 
 * @class    TwitchPress_Admin
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( !class_exists( 'TwitchPress_Custom_Posts_TwitchFeed' ) ) :

class TwitchPress_Custom_Posts_TwitchFeed {

    public function __construct() {      
    
    }  
  
    public static function init() {         
        add_action( 'publish_twitchfeed', array( __CLASS__, 'tweetfeed_post_published' ), 10, 2 );
    }
    
    /**
    * Shares post to Twitch. 
    * 
    * When a twitchfeed post is published, this action is called and shares the
    * authored content with Twitch.tv channel feeds.
    * 
    * @param mixed $ID post ID
    * @param mixed $post the post object
    * 
    * @version 2.0
    */
    public static function tweetfeed_post_published( $ID, $post ) {
        
        /*
            Currently works for administrators only.
            Currently uses the main Twitch account by default.
        */   

        if( !current_user_can( 'activate_plugins' ) ) {
            return false;
        }
        
        $kraken = new TWITCHPRESS_Twitch_API_Calls();
        $send = array( 'content' => $post->post_content );
        $result = $kraken->postFeedPost( $send, array() );
        
        // Kraken returns a status of "200" on success. 
        if( '200' == $result ) {
            
            // We now need to get the Twitch feed item and store its ID in post meta.
            // Lets assume another post was published at the same time.
            $feed_items = $kraken->getFeedPosts( $kraken->twitch_channel_id, 3 );

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
                    add_post_meta( $ID, 'twitchpress_feed_item_id' );
                        
                } else { 
                    // If left with 2 or more items, attempt to match content but
                    // we would need a version of the WP content that has no HTML, maybe!    
                }
            }

        } else {
            
            // Display a notice indicating a likely failure to update feed with new post. 
             
        }   
        
        unset( $kraken, $result );
    }
}  

endif;

TwitchPress_Custom_Posts_TwitchFeed::init();