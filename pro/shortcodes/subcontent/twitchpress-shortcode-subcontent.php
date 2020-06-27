<?php
/**
 * TwitchPress Shortcode for locking Subscriber Only Content
 * 
 * @author Ryan Bayne  
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Shortcode_Subscriber_Only_Content' ) ) :

/**
* TwitchPress Shortcode Class
* 
* @version 1.0
*/
class TwitchPress_Shortcode_Subscriber_Only_Content {
    
    var $atts = null;
    var $sub_only_content = null;
    var $output = null;

    public function gate() {
        // Visitor must be logged into the blog...
        if( !is_user_logged_in() ) { 
            echo '<p>' . __( 'See the hidden content for this page by subscribing to my Twitch.tv channel and logging into this site...', 'twitchpress' ) . '</p>';
            return; 
        }
        
        // Visitor must be subscribing to the main channel...
        if( !twitchpress_is_user_subscribing( get_current_user_id() ) ) {
            echo '<p>' . __( 'Subscriber only content has been hidden here, please subscriber to my Twitch.tv channel to unlock it...', 'twitchpress' ) . '</p>';
            return;
        }   
        
        echo $this->sub_only_content;
    }

    public function output() {
        ob_start(); 
        $this->gate();
        return ob_get_clean();
    }
}

endif;
