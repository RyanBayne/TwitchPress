<?php
/**
 * TwitchPress - Frontend output functions. 
 *
 * @author   Ryan Bayne
 * @category User Interface
 * @package  TwitchPress/Notices
 * @since    1.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}                    

add_action( 'wp_head', 'twitchpress_display_frontend_notices', 10 );
function twitchpress_display_frontend_notices() {
    add_filter( 'the_content', 'twitchpress_display_frontend_notices_the_content' );
}

function twitchpress_frontend_notice_types() {
    return array( 'error', 'success', 'warning', 'info' );
}
                       
function twitchpress_display_frontend_notices_the_content( $post_content ) {  
    global $GLOBALS;
                                 
    if( !isset( $_GET['twitchpress_notice'] ) || !is_string( $_GET['twitchpress_notice'] ) ) { return $post_content; }
    elseif( !isset( $_GET['source'] ) || !is_string( $_GET['source'] ) ) { return $post_content; }
    elseif( !isset( $_GET['key'] ) || !is_string( $_GET['key'] ) ) { return $post_content; }

    $the_message = $GLOBALS['twitchpress']->public_notices->get_message_by_id( $_GET['source'], $_GET['key'] );
                                      
    $content = "
    <div class='twitchpress-frontend-message'>
        <h2>" . esc_html( $the_message[ 'title'] ) . "</h2>
        <p>" . esc_html( $the_message[ 'info'] ) . "</p>
    </div>\n\n" . $post_content;
    
    // Remove the action calling this function once it's run, to prevent it running elsewhere.
    remove_filter( 'the_content', 'twitchpress_display_frontend_notices_the_content', 5 );
    remove_filter( 'post_updated', 'twitchpress_display_frontend_notices_the_content', 5 );
    
    return $content;
}