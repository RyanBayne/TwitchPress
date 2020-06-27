<?php
/**
 * TwitchPress Shortcode and admin-post processing for displaying raffle entry button...
 *
 * @author   Ryan Bayne
 * @category Shortcodes
 * @package  TwitchPress/Core
 * @since    1.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {    
    exit;
}
                                                        
add_shortcode( 'twitchpress_raffle_entry_button', 'twitchpress_shortcode_raffle_entry_button' );
                                                     
// Logged in action handlers... 
add_action( 'admin_post_twitchpress_raffle_entry', 'twitchpress_admin_post_handler_raffle_entry' );
                                                               
// Not logged in (not authenticated) handlers...
add_action( 'admin_post_nopriv_twitchpress_raffle_entry_button', 'twitchpress_admin_post_handler_nopriv_reject' );                      

/**
* Outputs a single button for entering raffle...
* 
* @uses admin-post.php 
* 
* @param mixed $atts
* 
* @version 1.0
*/
function twitchpress_shortcode_raffle_entry_button( $atts ) {            
    global $post; 

    $atts = shortcode_atts( array(             
            //'channel_id'   => null
    ), $atts, 'twitchpress_raffle_entry_button' );    
                          
    $url = admin_url( 'admin-post.php?action=twitchpress_raffle_entry' );
    return '<a href="' . $url . '" class="button button-primary">' . __( 'Enter Raffle','twitchpress' ) . '</a>';                      
}

function twitchpress_admin_post_handler_raffle_entry() {
    twitchpress_giveaways_entry(); 
    exit;
}

function twitchpress_admin_post_handler_nopriv_reject() {
    wp_die( __( 'Please Login First', 'twitchpress' ), __( 'Please Login First', 'twitchpress' ));
    exit;
}