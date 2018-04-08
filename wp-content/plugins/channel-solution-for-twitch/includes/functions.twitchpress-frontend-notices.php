<?php
/**
 * TwitchPress - Frontend Notices 
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
    add_filter( 'the_title', 'twitchpress_display_frontend_notices_the_title' );
}

function twitchpress_display_frontend_notices_the_title( $content ) {
    if( !isset( $_GET['twitchpress_notice'] ) || !is_string( $_GET['twitchpress_notice'] ) ) { return; }
    elseif( !isset( $_GET['twitchpress_title'] ) || !is_string( $_GET['twitchpress_title'] ) ) { return; }
    elseif( !isset( $_GET['twitchpress_info'] ) || !is_string( $_GET['twitchpress_info'] ) ) { return; }

    // Remove the action calling this function once it's run, to prevent it running elsewhere.
    remove_action( 'post_updated', 'twitchpress_display_frontend_notices_the_title', 11 );

    $content = "
    <div class='twitchpress-frontend-message'>
        <h2>" . esc_html( $_GET['twitchpress_title'] ) . "</h2>
        <p>" . esc_html( $_GET['twitchpress_info'] ) . "</p>
    </div>\n\n" . $content;
    
    return $content;
}