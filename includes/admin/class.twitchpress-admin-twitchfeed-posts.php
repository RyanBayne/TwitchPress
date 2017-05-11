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

/**
 * TwitchPress_Custom_Posts_TwitchFeed class.
 */
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
    * @author Ryan Bayne
    * @version 1.0
    * 
    * @param mixed $ID
    * @param mixed $post 
    */
    public static function tweetfeed_post_published( $ID, $post ) {
        
        /*
            Currently works for administrators only.
            Currently uses the main Twitch account only.
        */   

        $kraken = new TWITCHPRESS_Kraken5_Calls();
        $send = array( 'content' => $post->post_content );
        $result = $kraken->postFeedPost( $send, array() );   
    }
}  

endif;

TwitchPress_Custom_Posts_TwitchFeed::init();
?>
