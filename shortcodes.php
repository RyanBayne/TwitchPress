<?php  
/**
 * TwitchPress - Include shortcode files here (added April 2018)
 *
 * There are some shortcodes that do not have their own file and are loaded in
 * another file pre-2018.
 *
 * @author   Ryan Bayne
 * @category Shortcodes
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
                                              
if ( ! defined( 'ABSPATH' ) ) {          
    exit;
}

// Shortcodes with individual files... 
include_once( 'includes/shortcodes/shortcode-sync-buttons-public.php' );   
include_once( 'includes/shortcodes/shortcode-follower-only-content.php' );   
      
/**
* Master shortcode function for calling actual content building methods...
* 
* @version 2.0
*/
function twitchpress_shortcode_init( $atts, $content = null ) {         
    global $post;
    
    // Apply defaults to all shortcodes (a standard accross the entire site)
    // Can trigger very different output if changed after page publication... 
    $atts = shortcode_atts( array(  
            'id'             => null,
            'shortcode'      => 'Missing',        
            'channel_id'     => null,
            'channel_name'   => null,
            'cache'          => true,
            'count'          => '10',
            'first'          => 10,
            'period'         => '',
            'started_at'     => '',
            'refresh'        => 120,  /* wait this time before fetching newer data */
            'expiry'         => 3600, /* maximum cache life, usually longer than refresh value */
            'broadcaster_id' => null, 
            'game_id'        => null, 
            'clip_id'        => null,
            'game_name'      => null,
            'type'           => null, 
            'style'          => null,
            'team'           => 'sutv',
            'value'          => null,
            'username'       => null,
            'to_id'          => null, 
            'from_id'        => null,
            'orderby'        => null,
            'display'        => 'all',
            'max'            => null,
            'min'            => null,
            'delete'         => false, /* set to true to delete cache, use in testing only */
    ), $atts, $atts['shortcode'] );
    
    // Caching will be disabled for $content wrapping shortcodes...
    if( $content ) { $atts['cache'] = true; }
    
    // Complete the name of requested shortcode method...
    $function_name = 'twitchpress_shortcode_' . $atts['shortcode'];

    // Establish channel ID when only the channel name has been provided...
    if( isset( $atts['channel_name'] ) && !isset( $atts['channel_id'] ) ) {
        $twitch_api = new TwitchPress_Twitch_API();
        $atts['channel_id'] = $twitch_api->get_channel_id_by_name( $atts['channel_name'] );
    }
    
    // Output buffer is required for this design...                 
    ob_start();
    
    // Return if cached HTML found...
    $cache_name = 'twitchpress_shortcode_' . $atts['shortcode'] . '_' . $post->ID;

    // Force deletion of cache on every request (meant for development/testing only)...
    if( $atts['delete'] ) { delete_transient( $cache_name ); }
    
    if( $atts['cache'] ){    
        $cache = get_transient( $cache_name );
        if( $cache && isset( $cache['time'] ) && isset( $cache['content'] ) ) {
            // If a refresh is not due then output the existing content...
            $refresh_due = $cache['time'] + $atts['refresh']; 
            if( $refresh_due < time() ) {        
                echo $cache['content'];
                return ob_get_clean();// return earlier due to existing cache of HTML!   
            }
        } 
    }
        
    // Build new HTML content by calling specific shortcode method...
    $built_content = $function_name( $atts, $content );
    
    // Shortcode function may return array with ['atts'] to modify behaviours...
    if( is_array( $built_content ) ) {
        $html = $built_content['html']; 
        $atts = $built_content['atts'];
    } else { 
        $html = $built_content;    
    }
    
    // Process procedural values for a user friendly output issues/faults...
    if( $html == 'prorequired' ) {
        // The shortcode belongs to the pro upgrade...
        return __( 'Pro Upgrade Required to display certain Twitch.tv content here. The TwitchPress Pro upgrade is issued to supports of the project.', 'twitchpress' );   
    }
        
    if( $atts['cache'] ){ 
        $new_cache_value = array( 'content' => $html, 'time' => time() );    
        set_transient( $cache_name, $new_cache_value, $atts['expiry'] ); 
    }
 
    echo $html;
        
    return ob_get_clean();    
}
add_shortcode( 'twitchpress_shortcodes', 'twitchpress_shortcode_init' );
                
/**
* Embeds the live stream and chat for a giving channel. 
* 
* @link https://dev.twitch.tv/docs/embed#embedding-everything-public-beta
* 
* @version 2.5 
*/
function twitchpress_shortcode_embed_everything( $atts ) {     
    
    // Shortcode attributes.
    $atts = shortcode_atts( array(          
        'channel'         => 'lolindark1', 
        'chat'            => 'default', // default|mobile
        'collection'      => '', // Example: https://embed.twitch.tv/?video=v124085610&collection=GMEgKwTQpRQwyA
        'height'          => 600, // 50%|Minimum: 400|Default: 480
        'theme'           => 'light', // light|dark
        'width'           => '100%'  // 80%|100%|Minimum: 340|Default: 940               
    ), $atts, 'twitchpress_embed_everything' );
    
    $atts['channel'] = str_replace( '”', '', $atts['channel'] );
    
    $parameters = json_encode( $atts );
              
    $html = '
    <!-- Add a placeholder for the Twitch embed -->
    <div id="twitchpress-embed-everything"></div>
    
    <!-- Load the Twitch embed script -->
    <script src="https://embed.twitch.tv/embed/v1.js"></script>
                    
    <script type="text/javascript">
      new Twitch.Embed("twitchpress-embed-everything", ' . $parameters . ');
    </script>';
    
    return apply_filters( 'twitchpress_shortcode_embed_everything', $html );
}     
add_shortcode( 'twitchpress_embed_everything', 'twitchpress_shortcode_embed_everything' );

function twitchpress_videos_shortcode( $atts ) {              
    $html_output = '';
    
    $atts = shortcode_atts( array(             
        'id'       => null,
        'user_id'  => null,
        'game_id'  => null,
        'after'    => null,
        'before'   => null,
        'first'    => null,
        'language' => null,
        'period'   => null,
        'sort'     => null,
        'type'     => null,
        'links'    => false
    ), $atts, 'twitchpress_video' );    
     
    $transient_code = $atts['id'] . $atts['user_id'] . $atts['game_id'];
    
    if( $cache = get_transient( 'twitchpress_video' . $transient_code ) ) {
       // return $cache;
    }
    
    // Get the stream. 
    $helix = new TWITCHPRESS_Twitch_API();
    
    $result = $helix->get_videos( $atts['id'], $atts['user_id'], $atts['game_id'] );
    
    if( $result ) 
    {
        if( $atts['links'] )
        {
            $html_output .= '<ol>'; 
            foreach( $result->data as $key => $item )
            {                          
                $html_output .= '<li>';
                $html_output .= '<a href="' . $item->url . '">' . $item->title . '</a>';         
                $html_output .= '</li>';        
            }
            $html_output .= '</ol>';
        }
        else
        {
            $html_output .= '<ol>'; 
            foreach( $result->data as $key => $item )
            {                          
                $html_output .= '<li>';
  
                $html_output .= '
                <iframe
                    src="https://player.twitch.tv/?video=' . $item->id . '&autoplay=false"
                    height="720"
                    width="1280"
                    frameborder="0"
                    scrolling="no"
                    allowfullscreen="true">
                </iframe>';   
                                      
                $html_output .= '</li>';        
            }
            $html_output .= '</ol>';                  
        }
    }

    set_transient( 'twitchpress_videos' . $transient_code, $html_output, 86400 );
    
    return $html_output;
}                                       
add_shortcode( 'twitchpress_videos', 'twitchpress_videos_shortcode' );
   
function twitchpress_get_top_games_list_shortcode( $atts ) {              
    $html_output = '';
    
    $atts = shortcode_atts( array(             
        'total'   => 10,
    ), $atts, 'twitchpress_get_top_games_list' );
    
    if( $cache = get_transient( 'twitchpress_get_top_games_list' . $atts['total'] ) ) {
        return $cache;
    }                 
    // Get the stream. 
    $helix = new TWITCHPRESS_Twitch_API();
    
    $result = $helix->get_top_games( null, null, $atts['total'] );
    
    if( $result && isset( $result->data[0] ) ) 
    {
        $html_output .= '<ol>'; 
        foreach( $result->data as $key => $game )
        {                          
            $html_output .= '<li>';
            $html_output .= $game->name;        
            $html_output .= '</li>';        
        }
        $html_output .= '</ol>';
    }

    set_transient( 'twitchpress_get_top_games_list' . $atts['total'], $html_output, 86400 );
    
    return $html_output;
}                                       
add_shortcode( 'twitchpress_get_top_games_list', 'twitchpress_get_top_games_list_shortcode' );

/**
* Shortcode outputs a basic status for the giving channel. 
* 
* [twitchpress_channel_status_line channel_id=""]
* 
* @version 1.1
*/
function twitchpress_channel_status_line_shortcode( $atts ) {  
    if( TWITCHPRESS_API_NAME == 'kraken' )
    {
        return twitchpress_channel_status_line_kraken( $atts );    
    }
    else
    {
        return twitchpress_channel_status_line_helix( $atts );    
    }
}
function twitchpress_channel_status_line_kraken( $atts ) {         
    $html_output = null;
           
    $atts = shortcode_atts( array(             
            'channel_id'   => null,
            'channel_name' => null,
            'offline'      => __( 'Channel Offline', 'twitchpress' ),
            'live'         => __( 'Channel Live', 'twitchpress' )
    ), $atts, 'twitchpress_channel_status' );
    
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

    $twitchpress = new TWITCHPRESS_Twitch_API_Calls();

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
        $html_output = '<p>' . $atts['offline'] . '</p>';    
    } 
    else
    {                                  
        $html_output = '<p>' . $atts['live'] . '</p>';  
    }
    
    set_transient( 'twitchpress_shortcode_channel_status_line', $html_output, 120 );
    
    return $html_output;
}
function twitchpress_channel_status_line_helix( $atts ) {      
    
    $atts = shortcode_atts( array(             
            'channel_id'   => null,
            'channel_name' => null,
            'offline'      => __( 'Channel Offline', 'twitchpress' ),
            'live'         => __( 'Channel Live', 'twitchpress' )            
    ), $atts, 'twitchpress_channel_status' );
    
    // If no channel ID or name is giving...
    if( !$atts['channel_id'] && !$atts['channel_name'] ) {
        return __( 'Channel status-line shortcode has not been setup!', 'twitchpress' );      
    } 
    
    // Establish an ID if we only have a channel name...  
    if( !$atts['channel_id'] && $atts['channel_name'] ) {              
        $helix = new TWITCHPRESS_Twitch_API();
        $result = $helix->get_channel_id_by_name( $atts['channel_name'] );
        if( isset( $result->data[0]->id ) ) {
            $channel_id = $result->data[0]->id;
        } else {
            $channel_id = null;
            $html_output = sprintf( __( 'Failed to retrieve channel ID %s', 'twitchpress' ), esc_html( $atts['channel_name'] ) );
        }                   
    } 
    
    // Use cached HTML if we have a channel ID...
    if( $channel_id ) {
        $cache = get_transient( 'twitchpress_channel_status_line_' . $channel_id );
        if( $cache ) {
            return $cache;
        }
    }                  

    // Get the stream. 
    if( !$helix ){ $helix = new TWITCHPRESS_Twitch_API(); }
         
    $result = $helix->get_stream_by_userid( $channel_id );     

    if( !$result || $result->type !== 'live' )
    {
        $html_output = '<p>' . $atts['offline'] . '</p>';    
    } 
    else
    {                                  
        $html_output = '<p>' . $atts['live'] . '</p>';  
    }
    
    set_transient( 'twitchpress_channel_status_line' . $channel_id, $html_output, 120 );
    
    return $html_output;
}
add_shortcode( 'twitchpress_channel_status_line', 'twitchpress_channel_status_line_shortcode' );

/**
* Shortcode outputs a status line for the giving channel. 
* 
* @version 2.0
*/
function twitchpress_channel_status_shortcode( $atts ) {      
    if( TWITCHPRESS_API_NAME == 'kraken' )
    {
        return twitchpress_channel_status_shortcode_kraken( $atts );    
    }
    else
    {
        return twitchpress_channel_status_shortcode_helix( $atts );    
    }
}
function twitchpress_channel_status_shortcode_kraken( $atts ) {
          
    $atts = shortcode_atts( array(             
            'channel_id'   => null,
            'channel_name' => null,
            'offline'      => __( 'Channel Offline', 'twitchpress' )
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
    
    $twitchpress = new TWITCHPRESS_Twitch_API_Calls();

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
        $html_output = '<p>' . $atts['offline'] . '</p>';   
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

/**
* put your comment there...
* 
* @param mixed $atts
* 
* @version 2.0
*/
function twitchpress_channel_status_shortcode_helix( $atts ) {       
    $atts = shortcode_atts( array(             
            'channel_id'   => null,
            'channel_name' => null,
            'offline'      => __( 'Channel Offline', 'twitchpress' )
    ), $atts, 'twitchpress_channel_status' );
    
    // If no channel ID or name is giving...
    if( !$atts['channel_id'] && !$atts['channel_name'] ) {
        return __( 'Channel status shortcode has not been setup!', 'twitchpress' );      
    } 
    
    // Establish an ID if we only have a channel name...  
    if( !$atts['channel_id'] && $atts['channel_name'] ) {              
        $helix = new TWITCHPRESS_Twitch_API();
        $result = $helix->get_channel_id_by_name( $atts['channel_name'] );
        if( isset( $result->data[0]->id ) ) {
            $channel_id = $result->data[0]->id;
        } else {
            $channel_id = null;
            $html_output = sprintf( __( 'Failed to retrieve channel ID %s', 'twitchpress' ), esc_html( $atts['channel_name'] ) );
        }                   
    } 
    
    // Use cached HTML if we have a channel ID...
    if( $channel_id ) {
        $cache = get_transient( 'twitchpress_channel_status_line_' . $channel_id );
        if( $cache ) {
            return $cache;
        }
    }                  

    // Get the stream. 
    if( !$helix ){ $helix = new TWITCHPRESS_Twitch_API(); }
         
    $result = $helix->get_stream_by_userid( $channel_id );     

    if( !$result || $result->type !== 'live' )
    {          
        $html_output = '<p>' . $atts['offline'] . '</p>';   
    } 
    else
    {                               
        $html_output = '<p>';        
        $html_output .= ' ' . esc_html( $result->user_name ) . ' ';
        $html_output .= ' is live with ' . esc_html( $result->viewer_count ) . ' viewers ';
        $html_output .= '</p>';
    }
    
    set_transient( 'twitchpress_channel_status' . $channel_id, $html_output, 120 );
    
    return $html_output;
}
add_shortcode( 'twitchpress_channel_status', 'twitchpress_channel_status_shortcode' );

/**
* Shortcode outputs a status box with some extra information for the giving channel.
* 
* @version 2.0
*/
function twitchpress_channel_status_box_shortcode( $atts ) {     
    if( TWITCHPRESS_API_NAME == 'kraken' )
    {
        return twitchpress_channel_status_box_shortcode_kraken( $atts );    
    }
    else
    {
        return twitchpress_channel_status_box_shortcode_helix( $atts );    
    }    
}
function twitchpress_channel_status_box_shortcode_kraken( $atts ) {       
    $atts = shortcode_atts( array(             
            'channel_id'   => null,
            'channel_name' => null,
            'offline'      => __( 'Channel Offline', 'twitchpress' )
    ), $atts, 'twitchpress_channel_status_box' );
    
    // Establish channel ID
    if( $atts['channel_id'] === null && $atts['channel_name'] === null ) 
    {
        return 'Shortcode has not been setup properly!';      
    }   

    // Check cache. 
    if( $cache = get_transient( 'twitchpress_shortcode_channel_status_box' ) ) 
    {
        return $cache; 
    }

    $twitchpress = new TWITCHPRESS_Twitch_API_Calls();
    
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
        $html_output = '<p>' . $atts['offline'] . '</p>';    
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
    }
    
    set_transient( 'twitchpress_channel_status_box', $html_output, 120 );
    
    return $html_output;    
}
function twitchpress_channel_status_box_shortcode_helix( $atts ) {       
    $atts = shortcode_atts( array(             
            'channel_id'   => null,
            'channel_name' => null,
            'offline'      => __( 'Channel Offline', 'twitchpress' )
    ), $atts, 'twitchpress_shortcode_channel_status_box' );
    
    // Establish channel ID
    if( $atts['channel_id'] === null && $atts['channel_name'] === null ) 
    {
        return 'Shortcode has not been setup properly!';      
    }   
    $helix = new TWITCHPRESS_Twitch_API();

    // Establish an ID if we only have a channel name...  
    if( !$atts['channel_id'] && $atts['channel_name'] ) {              
        $helix = new TWITCHPRESS_Twitch_API();
        $result = $helix->get_user_without_email_by_login_name( $atts['channel_name'] );
        if( isset( $result->data[0]->id ) ) {
            $channel_id = $result->data[0]->id;
        } else {
            $channel_id = null;
            return sprintf( __( 'Failed to retrieve channel ID %s', 'twitchpress' ), esc_html( $atts['channel_name'] ) );
        }                   
    } 

    // Use cached HTML if we have a channel ID...
    if( $channel_id ) {
        $cache = get_transient( 'twitchpress_channel_status_box' . $channel_id );
        if( $cache ) {
            return $cache;
        }
    }                  
                                
    // Get the stream. 
    if( !$helix ){ $helix = new TWITCHPRESS_Twitch_API(); }
         
    $result = $helix->get_stream_by_userid( $channel_id );     
                       
    if( !$result || $result->type !== 'live' )
    {
        $html_output = '<p>' . $atts['offline'] . '</p>';    
    } 
    else
    {                                                            
        $html_output = '<div>';
        $html_output .= 'Channel: ' . $result->user_name . ' ';
        $html_output .= '<br />Game: ' . $result->game_id . ' ';
        $html_output .= '<br />Viewers: ' . $result->viewer_count . ' ';
        $html_output .= '</div>';
    }
    
    set_transient( 'twitchpress_channel_status_box' . $channel_id, $html_output, 120 );
    
    return $html_output;    
}
add_shortcode( 'twitchpress_shortcode_channel_status_box', 'twitchpress_channel_status_box_shortcode' );
                                             
/**
* Shortcode outputs an unordered list of channels with status.
* 
* @version 1.0
*/
function twitchpress_channels_status_list_shortcode( $atts ) {       

}
add_shortcode( 'twitchpress_channels_status_list', 'twitchpress_channels_status_list_shortcode' );

/**
* Displays a list of buttons for initiating oAuth for each API.
* 
* @version 2.0
*/
function shortcode_visitor_api_services_buttons( $atts ) {         
    global $post; 
    
    // Ensure visitor is logged into WordPress. 
    if( !is_user_logged_in() ) {
        return '<p>' . __( 'You must be logged into WordPress to view the full contents of this page.', 'twitchpress' );
    }
    
    $html_output = '        
    <table class="form-table">
        <tbody>        
            <tr>
                <th>
                    <p>
                        Service
                    </p>
                </th>
                <th> 
                    <p>
                        Status
                    </p>                        
                </th>
                <th> 
                    <p>
                        Authorize
                    </p>                        
                </th>                
            </tr>';
        
    $permalink = get_post_permalink( $post->ID, true );
    
    $atts = shortcode_atts( array(             
            //'channel_id'   => null
    ), $atts, 'twitchpress_visitor_api_services_buttons' );    
                          
    // Twitch
    if( class_exists( 'TWITCHPRESS_Twitch_API' ) )
    {   
        $twitch_api = new TWITCHPRESS_Twitch_API();

        // Set the users current Twitch oAuth status. 
        $twitchpress_oauth_status = __( 'Not Setup', 'twitchpress' );
        if( twitchpress_is_user_authorized( get_current_user_id() ) )
        {
            $twitchpress_oauth_status = __( 'Ready', 'twitchpress' );
        }
        
        // Create a local API state. 
        $state = array( 'redirectto' => $permalink,
                        'userrole'   => 'visitor',
                        'outputtype' => 'public',
                        'reason'     => 'personaloauth',
                        'function'   => __FUNCTION__
        );  
                                                                      
        $url = twitchpress_generate_authorization_url( twitchpress_get_visitor_scopes(), $state );
        unset($twitch_api); 

        $html_output .= '                
        <tr>
            <td>
                Twitch.tv
            </td>
            <td> 
                ' . $twitchpress_oauth_status . '                        
            </td>
            <td> 
                <a href="' . $url . '" class="button button-primary">Setup</a>                          
            </td>            
        </tr>';           
    }

    // Streamlabs 
    if( class_exists( 'TWITCHPRESS_Streamlabs_API' ) )
    {
        $streamlabs_api = new TWITCHPRESS_Streamlabs_API();
        
        $state = array( 'redirectto' => $permalink,
                        'userrole'   => 'visitor',
                        'outputtype' => 'public',
                        'reason'     => 'personaloauth',
                        'function'   => __FUNCTION__
        );   
             
        // Set the users current Twitch oAuth status. 
        $streamlabs_oauth_status = __( 'Not Setup', 'twitchpress' );
        if( $streamlabs_api->is_user_ready( get_current_user_id() ) )
        {
            $streamlabs_oauth_status = __( 'Ready', 'twitchpress' );
        }
        
        $url = $streamlabs_api->oauth2_url_visitors( $state );
        unset($streamlabs_api); 

        $html_output .= '                
        <tr>
            <td>
                Streamlabs.com
            </td>
            <td> 
                ' . $streamlabs_oauth_status . '                        
            </td>            
            <td>
                <a href="' . $url . '" class="button button-primary">Setup</a>               
            </td>            
        </tr>';                      
    }
    
    $html_output .= '            
        </tbody>
    </table>';
                          
    return $html_output;    
}
add_shortcode( 'twitchpress_visitor_api_services_buttons', 'shortcode_visitor_api_services_buttons' );

/**
* Shortcode outputs the total viewers for a giving stream
* 
* @version 0.1
*/
function twitchpress_streams_totalviewers_shortcode( $atts ) {     
    if( TWITCHPRESS_API_NAME == 'kraken' )
    {
        return twitchpress_streams_totalviewers_shortcode_kraken( $atts );    
    }
    else
    {
        return twitchpress_streams_totalviewers_shortcode_helix( $atts );    
    }    
}
function twitchpress_streams_totalviewers_shortcode_kraken( $atts ) {       
    $atts = shortcode_atts( array(             
            'channel_id'   => null,
            'channel_name' => null,
            'offline'      => __( 'Stream Offline', 'twitchpress' )
    ), $atts, 'twitchpress_streams_totalviewers' );
    
    // Establish channel ID
    if( $atts['channel_id'] === null && $atts['channel_name'] === null ) 
    {
        return 'Shortcode has not been setup properly!';      
    }   

    // Check cache. 
    if( $cache = get_transient( 'twitchpress_shortcode_streams_totalviewers' ) ) 
    {
        return $cache; 
    }

    $twitchpress = new TWITCHPRESS_Twitch_API_Calls();
    
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
        $html_output = 0;    
    } 
    else
    {                                  
        $html_output = $channel_object['viewers'];
    }
    
    set_transient( 'twitchpress_shortcode_streams_totalviewers', $html_output, 120 );
    
    return $html_output;    
}                               
function twitchpress_streams_totalviewers_shortcode_helix( $atts ) {       
    $atts = shortcode_atts( array(             
            'channel_id'   => null,
            'channel_name' => null,
            'offline'      => __( 'Stream Offline', 'twitchpress' )
    ), $atts, 'twitchpress_shortcode_streams_totalviewers' );
    
    // Establish channel ID
    if( $atts['channel_id'] === null && $atts['channel_name'] === null ) 
    {
        return 'Shortcode has not been setup properly!';      
    }   
    $helix = new TWITCHPRESS_Twitch_API();

    // Establish an ID if we only have a channel name...  
    if( !$atts['channel_id'] && $atts['channel_name'] ) {              
        $result = $helix->get_user_without_email_by_login_name( $atts['channel_name'] );
        if( isset( $result->data[0]->id ) ) {
            $channel_id = $result->data[0]->id;
        } else {
            $channel_id = null;
            return sprintf( __( 'Failed to retrieve channel ID %s', 'twitchpress' ), esc_html( $atts['channel_name'] ) );
        }                   
    } 

    // Use cached HTML if we have a channel ID...
    if( $channel_id ) {
        $cache = get_transient( 'twitchpress_channel_totalviewers' . $channel_id );
        if( $cache ) {
            return $cache;
        }
    }                  
                                
    $result = $helix->get_stream_by_userid( $channel_id );     
                       
    if( !$result )
    {
        $html_output = 0;    
    } 
    else
    {              
        $html_output = $result->viewer_count; 
    }
    
    set_transient( 'twitchpress_streams_totalviewers' . $channel_id, $html_output, 120 );
    
    return $html_output;    
}
add_shortcode( 'twitchpress_shortcode_streams_totalviewers', 'twitchpress_streams_totalviewers_shortcode' );

/**
* Shortcode outputs a single channel value
* 
* @version 0.1
*/
function twitchpress_stream_data_shortcode( $atts ) {     
    if( TWITCHPRESS_API_NAME == 'kraken' )
    {
        return 'Helix only shortcode!';    
    }
    else
    {
        return twitchpress_stream_data_shortcode_helix( $atts );    
    }    
}
function twitchpress_stream_data_shortcode_helix( $atts ) {       
    $atts = shortcode_atts( array(             
            'channel_id'   => null,
            'channel_name' => null,
            'value'        => 'viewer_count',
            'offline'      => __( 'Stream Offline', 'twitchpress' ),
            'refresh'      => 120,
    ), $atts, 'twitchpress_shortcode_stream_data' );
    
    // Establish channel ID
    if( $atts['channel_id'] === null && $atts['channel_name'] === null ) 
    {
        return 'Shortcode has not been setup properly!';      
    }   
    $helix = new TWITCHPRESS_Twitch_API();

    // Establish an ID if we only have a channel name...  
    if( !$atts['channel_id'] && $atts['channel_name'] ) {              
        $result = $helix->get_user_without_email_by_login_name( $atts['channel_name'] );
        if( isset( $result->data[0]->id ) ) {
            $channel_id = $result->data[0]->id;
        } else {
            $channel_id = null;
            return sprintf( __( 'Failed to retrieve channel ID %s', 'twitchpress' ), esc_html( $atts['channel_name'] ) );
        }                   
    } 

    // Use cached HTML if we have a channel ID...
    if( $channel_id ) 
    {
        $cache = get_transient( 'twitchpress_stream_data' . $channel_id );
        if( $cache ) 
        {
            $result = $cache;
        } 
        else 
        {
            $result = $helix->get_stream_by_userid( $channel_id );        
        }
    }         
    
    // Storing the data in this shortcode and not generate html...         
    set_transient( 'twitchpress_stream_data' . $channel_id, $result, $atts['refresh'] );
    
    // If no result assume stream offline...
    if( !$result )
    {
        $html_output = $atts['offline'];    
    } 
    else
    {    
        $val = $atts['value'];
        
        // Access the giving value           
        $html_output = $result->$val; 
    }

    return $html_output;    
}
add_shortcode( 'twitchpress_shortcode_stream_data', 'twitchpress_stream_data_shortcode' );

/**
* Shortcode outputs an ordered list of bits donators...
* 
* @version 0.1
*/
function twitchpress_get_bits_leaderboard_shortcode( $atts ) {     
    if( TWITCHPRESS_API_NAME == 'kraken' )
    {
        return 'Helix only shortcode!';    
    }
    else
    {
        return twitchpress_get_bits_leaderboard_shortcode_helix( $atts );    
    }    
}
function twitchpress_get_bits_leaderboard_shortcode_helix( $atts ) {       
    $atts = shortcode_atts( array(             
            'channel_id'   => null,
            'channel_name' => null,
            'count'        => '10',
            'period'       => '',
            'started_at'   => '',
            'refresh'      => 120,
    ), $atts, 'twitchpress_shortcode_stream_data' );
    
    // Establish channel ID
    if( $atts['channel_id'] === null && $atts['channel_name'] === null ) 
    {
        return 'Shortcode has not been setup properly!';      
    } 
    
    // Call our good friend Twitch...  
    $helix = new TWITCHPRESS_Twitch_API();

    // Establish an ID if we only have a channel name (not the recommended approach but handy)...  
    if( !$atts['channel_id'] && $atts['channel_name'] ) {              
        $result = $helix->get_user_without_email_by_login_name( $atts['channel_name'] );
        if( isset( $result->data[0]->id ) ) {
            $atts['channel_id'] = $result->data[0]->id;
        } else {
            return sprintf( __( 'Failed to retrieve channel ID %s', 'twitchpress' ), esc_html( $atts['channel_name'] ) );
        }                   
    } 

    // Use cached HTML if we have a channel ID...
    if( $atts['channel_id'] ) {
        $cache = get_transient( 'twitchpress_get_bits_leaderboard' . $atts['channel_id'] );
        if( $cache ) {
            return $cache;
        } else {
            $result = $helix->get_bits_leaderboard( $atts['count'], $atts['period'], $atts['started_at'], $atts['channel_id'] );
        }
    }                  
    
    ob_start();

    $html_output = '';
    
    foreach( $result as $key => $something ) {
        $html_output .= '';    
    }
    
    echo $html_output;
    
    // Storing data and not the output...
    set_transient( 'twitchpress_get_bits_leaderboard' . $atts['channel_id'], $html_output, $atts['refresh'] );
    
    return ob_get_clean();   
}
add_shortcode( 'twitchpress_shortcode_get_bits_leaderboard', 'twitchpress_get_bits_leaderboard_shortcode' );

/**
* Outputs the bits leaderboard for a giving channel...
* 
* @param mixed $atts
* @version 1.0
*/
function twitchpress_shortcode_get_bits_leaderboard( $atts ) {
 
    $helix = new TWITCHPRESS_Twitch_API();
    
    $result = $helix->get_bits_leaderboard( $atts['count'], $atts['period'], $atts['started_at'], $atts['channel_id'] );
    
    if( !$result || !$result->data ) { return __( 'No bits', 'twitchpress' ); }
    
    $html_output = '<ol>';

    foreach( $result->data as $supporter ) {
        $html_output .= '<li>' . $supporter['user_name']. '</li>';    
    }

    return $html_output;    
}

/**
* Outs a list of clips...
* 
* @param mixed $atts
* @version 1.0
*/
function twitchpress_shortcode_get_clips( $atts ) {
    
    $helix = new TWITCHPRESS_Twitch_API();    
    
    $result = $helix->get_clips( $atts['broadcaster_id'], $atts['game_id'], $atts['clip_id'] );

    $html_output = '<ol>';

    foreach( $result->data as $key => $clip ) {
        $html_output .= '<li>' . $clip->url . '</li>';    
    }

    return $html_output;    
}

/**
* Outputs a single game...
* 
* @param mixed $atts
* @version 1.0
*/
function twitchpress_shortcode_get_game( $atts ) {
    $helix = new TWITCHPRESS_Twitch_API();    
    
    $result = $helix->get_games( $atts['game_id'], $atts['game_name'] );

    $html_output = '';
    $html_output .= 'Title: ' . $result->data[0]->name;
    $html_output .= '<br>ID: ' . $result->data[0]->id;
    $html_output .= '<br>Box Art URL: ' . $result->data[0]->box_art_url;
    
    return $html_output;      
}
          
function twitchpress_shortcode_subscription_status() {
    global $current_user_id;
    if( !$current_user_id ) {
        return __( 'Unknown', 'twitchpress' );
    }    
    $plan = twitchpress_get_sub_plan( $current_user_id, twitchpress_get_main_channels_twitchid() );
    if( !twitchpress_is_valid_sub_plan( $plan ) ) {
        return __( 'No Subscription', 'twitchpress' );
    }
    
    return __( 'Active Subscriber', 'twitchpress' );    
}

/**
* Use to create a subscription page within a user account area...
* 
* @version 1.0
*/
function twitchpress_shortcode_subscription_plan( $atts ) {
    global $current_user_id;
    if( !$current_user_id ) {
        return __( 'Unknown', 'twitchpress' );
    }
    $plan = twitchpress_get_sub_plan( $current_user_id, twitchpress_get_main_channels_twitchid() );
    if( !$plan ) {
        return __( 'No Subscription', 'twitchpress' );
    }
    
    return $plan;
}

function twitchpress_shortcode_tests() {

    ob_start();

    $html_output = '';
    
    $result = array();
      
    foreach( $result as $key => $something ) {
        $html_output .= '';    
    }
    
    echo $html_output;
    
    // Storing data and not the output...
    set_transient( 'twitchpress_get_bits_leaderboard' . $atts['channel_id'], $html_output, $atts['refresh'] );
    
    return ob_get_clean();    
}

/**
* Pro Upgrade Required 
* 
* Outputs a list of channels - this is the original approach to doing this
* and further shortcodes will be created to offer lists with default settings
* to make shortcode entry easier - otherwise this extensive shortcode and a range
* of attributes can be used to configure the output in many ways...
* 
* @param mixed $atts
* 
* @version 1.0
*/
function twitchpress_shortcode_channel_list( $atts ) {             
    if( TWITCHPRESS_PRO !== true ) { return 'prorequired'; }
    require_once( TWITCHPRESS_PRO_DIR_PATH . 'shortcodes/channellist/twitchpress-shortcode-channellist.php' );    
    $shortcode_object = new TwitchPress_Shortcode_Channel_List();
    $shortcode_object->atts = $atts;
    $shortcode_object->init();     
    return $shortcode_object->output();
}

/**
* Pro Upgrade Required
* 
* Outputs a gallery of clips with defaults starting with a gallery of thumbnail
* images only - from there we can configure additional HTML typical with a clips
* gallery.
* 
* @param mixed $atts
* 
* @version 1.0
*/
function twitchpress_shortcode_clips_gallery( $atts ) { 
    if( TWITCHPRESS_PRO !== true ) { return 'prorequired'; }
    require_once( TWITCHPRESS_PLUGIN_DIR_PATH . '/pro/shortcodes/clipsgallery/twitchpress-shortcode-clipsgallery.php' );    
    $shortcode_object = new TwitchPress_Shortcode_Clips_Gallery();
    $shortcode_object->atts = $atts;
    $shortcode_object->init();    
    return $shortcode_object->output();    
}

/**
* Pro Upgrade Required
* 
* Outputs raw Streamlabs data.
* 
* @param mixed $atts
* 
* @version 1.0
*/
function twitchpress_shortcode_streamlabs( $atts ) { 
    if( TWITCHPRESS_PRO !== true ) { return 'prorequired'; }
    require_once( TWITCHPRESS_PRO_DIR_PATH . 'shortcodes/streamlabs/twitchpress-shortcode-streamlabs.php' );    
    $shortcode_object = new TwitchPress_Shortcode_Streamlabs();
    $shortcode_object->atts = $atts;
    $shortcode_object->init();    
    return $shortcode_object->output();    
}

/**
* Pro Upgrade Required for this shortcode...
* 
* Outputs raw Streamlabs data.
* 
* @param mixed $atts
* 
* @version 1.0
*/
function twitchpress_shortcode_sub_only_content( $atts, $content = null ) { 
    if( TWITCHPRESS_PRO !== true ) { return 'prorequired'; }
    require_once( TWITCHPRESS_PRO_DIR_PATH . 'shortcodes/subcontent/twitchpress-shortcode-subcontent.php' );    
    $shortcode_object = new TwitchPress_Shortcode_Subscriber_Only_Content();
    $shortcode_object->atts = $atts;
    $shortcode_object->sub_only_content = $content;    
    return $shortcode_object->output();    
}