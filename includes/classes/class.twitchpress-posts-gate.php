<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TwitchPress_Posts_Gate' ) ) :

/**
 * Applies a restriction on post content until a reason is found to
 * display it to the current user...
 *
 * @class     TwitchPress_Post_Type_Perks
 * @version   1.0.0
 * @package   TwitchPress
 * @category  Class
 * @author    Ryan Bayne
 */
class TwitchPress_Posts_Gate {
    public function init() {
        add_filter( 'the_content', array( __CLASS__, 'gate' ), 20 );
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_custom_boxes' ) );
        add_action( 'save_post', array( __CLASS__, 'save_twitchpress_post_gate_options' ) );        
    }  
    
    /**
    * Hides content if subscription/follower status is not met
    * 
    * @param mixed $original_content
    * 
    * @version 2.0
    */
    static function gate( $original_content ) {
        global $post, $current_user;

        $sub_status = TwitchPress_Current_User::subscription_status();
        $follower_status = TwitchPress_Current_User::follower();
        
        // User must meet the minimum requirement to view post contents...
        $gate_requirement_minimum = get_post_meta( $post->ID, '_twitchpress_post_gate_minimum', true );
        
        if( !$gate_requirement_minimum || $gate_requirement_minimum == 'notlocked' ) { return $original_content; }
                                        
        if( $gate_requirement_minimum == 'follower' && $follower_status ) 
        {
            return $original_content;
        }
        elseif( $gate_requirement_minimum == 'subscriber' && $sub_status )
        {
            return $original_content;
        }
        
        // We should not arrive here but just in-case...
        return self::gate_locked_notice( $gate_requirement_minimum );      
    }
    
    /**
    * Displays a locked-out notice when the vistor has not unlocked the post...
    * 
    * @version 1.0
    */
    static function gate_locked_notice( $requirement ) {
        $ing = __( 'Following', 'twitchpress' ); 
        if( $requirement == 'subscriber' ) { $ing = __( 'subscribing', 'twitchpress' ); }
        return '<h3>' . __( 'Locked Content', 'twitchpress' ) . '</h3>
        <p>' . sprintf( __( 'You have found a %s only post. Please ensure you are logged in and %s to my Twitch channel to unlock this secret content.', 'twitchpress' ), $requirement, $ing ) . '</p>';  
    }

    /**
    * Add all custom meta boxes. 
    * 
    * @version 1.0
    */
    public static function add_custom_boxes() {
        add_meta_box(
            'twitchpress_post_gate_options', // Unique ID
            __( 'TwitchPress Gate', 'twitchpress' ),  
            array( __CLASS__, 'html_twitchpress_post_gate_options' )
        );
    }
    
    /**
    * Options for locking post content.
    * 
    * @param mixed $post
    * 
    * @version 1.0
    */
    public static function html_twitchpress_post_gate_options($post) {
        $value = get_post_meta($post->ID, '_twitchpress_post_gate_minimum', true);
        ?>
        <label for="_twitchpress_post_gate_minimum"><?php _e( 'Minimum requirement to view post content...', 'twitchpress' ); ?></label>
        <select name="_twitchpress_post_gate_minimum" id="_twitchpress_post_gate_minimum" class="postbox">
            <option value="notlocked">Select requirement...</option>
            <option value="notlocked" <?php selected($value, 'notlocked'); ?>>Not Locked</option>
            <option value="follower" <?php selected($value, 'follower'); ?>>Twitch Follower</option>
            <option value="subscriber" <?php selected($value, 'subscriber'); ?>>Twitch Subscriber</option>
        </select>
        <?php
    }
         
    /**
    * Saves and processes gate options.
    * 
    * @param mixed $post_id
    * 
    * @version 1.0
    */
    public static function save_twitchpress_post_gate_options( $post_id ){
    
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }                        

        if ( array_key_exists( '_twitchpress_post_gate_minimum', $_POST ) ) {
            update_post_meta(
                $post_id,
                '_twitchpress_post_gate_minimum',
                $_POST['_twitchpress_post_gate_minimum']
            );
        }
        
        return $post_id;
    }
}

endif;