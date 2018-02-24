<?php  
/**
 * TwitchPress - Primary Shortcode File
 *
 * Shortcode files are included here, loaded and registered so that they can be
 * detected by other plugins.  
 *
 * @author   Ryan Bayne
 * @category Shortcodes
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
   
/**
* Shortcode outputs a basic status for the giving channel. 
* 
* [twitchpress_channel_status_line channel_id=""]
* 
* @version 1.1
*/
function twitchpress_channel_status( $atts ) {   
    $html_output = null;
           
    $atts = shortcode_atts( array(             
            'channel_id'   => null,
            'channel_name' => null,
    ), $atts, 'twitchpress_channel_status' );
    
    // Establish channel ID
    if( $atts['channel_id'] === null && $atts['channel_name'] === null ) 
    {
        return '';      
    }   

    // Check cache. 
    if( $cache = get_transient( 'twitchpress_shortcode_channel_status' ) ) 
    {
        return $cache; 
    }
    
    $twitchpress = new TWITCHPRESS_Kraken_Calls();

    // Get channel ID using the channel/username. 
    if( $atts['channel_id'] === null && $atts['channel_name'] !== null )
    {
        $user_object = $twitchpress->get_users( $atts['channel_name'] ); 
        
        if( isset( $user_object['users'][0]['_id'] ) )
        {
            $channel_id = $user_object['users'][0]['_id'];
        }
        else
        {
            return '';  
        }
    }
    elseif( $atts['channel_id'] !== null )
    {
        $channel_id = $atts['channel_id'];
    }
    
    // Get the stream. 
    $channel_object = $twitchpress->getStreamObject( $channel_id );     

    // Build $html_output and cache it and then return it.
    if( $channel_object === null )
    {
        $html_output = '<p>' . __( 'Channel Offline', 'twitchpress' ) . '</p>';    
    } 
    else
    {                                  
        $html_output = '<p>' . __( 'Channel Live', 'twitchpress' ) . '</p>';  
    }
    
    set_transient( 'twitchpress_shortcode_channel_status', $html_output, 120 );
    
    return $html_output;
}
add_shortcode( 'twitchpress_channel_status', 'twitchpress_channel_status' );

/**
* Shortcode outputs a status line for the giving channel. 
* 
* @version 1.0
*/
function twitchpress_channel_status_line( $atts ) {          
    $atts = shortcode_atts( array(             
            'channel_id'   => null,
            'channel_name' => null,
    ), $atts, 'twitchpress_channel_status_line' );
    
    // Establish channel ID
    if( $atts['channel_id'] === null && $atts['channel_name'] === null ) 
    {
        return '';      
    }   

    // Check cache. 
    if( $cache = get_transient( 'twitchpress_shortcode_channel_status_line' ) ) 
    {
        return $cache; 
    }
    
    $twitchpress = new TWITCHPRESS_Kraken_Calls();

    // Get channel ID using the channel/username. 
    if( $atts['channel_id'] === null && $atts['channel_name'] !== null )
    {
        $user_object = $twitchpress->get_users( $atts['channel_name'] ); 
        
        if( isset( $user_object['users'][0]['_id'] ) )
        {
            $channel_id = $user_object['users'][0]['_id'];
        }
        else
        {
            return '';  
        }
    }
    elseif( $atts['channel_id'] !== null )
    {
        $channel_id = $atts['channel_id'];
    }
    
    // Get the stream. 
    $channel_object = $twitchpress->getStreamObject( $channel_id );     

    if( $channel_object === null )
    {
        $html_output = '<p>' . __( 'Channel Offline', 'twitchpress' ) . '</p>';   
    } 
    else
    {                                  
        $html_output = '<p>';        
        $html_output .= ' ' . esc_html( $channel_object['channel']['display_name'] ) . ' ';
        $html_output .= ' is playing ' . esc_html( $channel_object['game'] ) . ' ';
        $html_output .= ' ' . esc_html( $channel_object['stream_type'] ) . ' ';
        $html_output .= ' to ' . esc_html( $channel_object['viewers'] ) . ' viewers ';
        $html_output .= '</p>';
    }
    
    set_transient( 'twitchpress_shortcode_channel_status_line', $html_output, 120 );
    
    return $html_output;
}
add_shortcode( 'twitchpress_channel_status_line', 'twitchpress_channel_status_line' );

/**
* Shortcode outputs a status box with some extra information for the giving channel.
* 
* @version 1.0
*/
function twitchpress_channel_status_box( $atts ) {
    $atts = shortcode_atts( array(             
            'channel_id'   => null,
            'channel_name' => null,
    ), $atts, 'twitchpress_channel_status_box' );
    
    // Establish channel ID
    if( $atts['channel_id'] === null && $atts['channel_name'] === null ) 
    {
        return '';      
    }   

    // Check cache. 
    if( $cache = get_transient( 'twitchpress_shortcode_channel_status_box' ) ) 
    {
        return $cache; 
    }
    
    $twitchpress = new TWITCHPRESS_Kraken_Calls();

    // Get channel ID using the channel/username. 
    if( $atts['channel_id'] === null && $atts['channel_name'] !== null )
    {
        $user_object = $twitchpress->get_users( $atts['channel_name'] ); 
        
        if( isset( $user_object['users'][0]['_id'] ) )
        {
            $channel_id = $user_object['users'][0]['_id'];
        }
        else
        {
            return '';  
        }
    }
    elseif( $atts['channel_id'] !== null )
    {
        $channel_id = $atts['channel_id'];
    }
    
    // Get the stream. 
    $channel_object = $twitchpress->getStreamObject( $channel_id );     

    if( $channel_object === null )
    {
        $html_output = '<p>' . __( 'Channel Offline', 'twitchpress' ) . '</p>';    
    } 
    else
    {                                  
        $html_output = '<div>';
        $html_output .= 'Channel: ' . $channel_object['channel']['display_name'] . ' ';
        $html_output .= '<br />Game: ' . $channel_object['game'] . ' ';
        $html_output .= '<br />Viewers: ' . $channel_object['viewers'] . ' ';
        $html_output .= '<br />Stream Type: ' . $channel_object['stream_type'] . ' ';
        $html_output .= '<br />Views: ' . $channel_object['channel']['views'] . ' ';
        $html_output .= '<br />Followers: ' . $channel_object['channel']['followers'] . ' ';
        $html_output .= '</div>';
        
        return $html_output;
    }
    
    set_transient( 'twitchpress_shortcode_channel_status_box', $html_output, 120 );
    
    return $html_output;    
}
add_shortcode( 'twitchpress_channel_status_box', 'twitchpress_channel_status_box' );

/**
* Shortcode outputs an unordered list of channels with status.
* 
* @version 1.0
*/
function twitchpress_channels_status_list( $channels_array ) {
    
}
add_shortcode( 'twitchpress_channels_status_list', 'twitchpress_channels_status_list' );
