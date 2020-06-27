<?php
/**
 * TwitchPress Shortcodes - output a list of links for visitors to sync data from all API.
 *
 * @author   Ryan Bayne
 * @category Shortcodes
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {    
    exit;
}
                                                        
add_shortcode( 'twitchpress_sync_buttons_public', 'twitchpress_shortcode_visitor_api_sync_buttons' );
                                                     
// Logged in action handlers. 
add_action( 'admin_post_twitchpress_manual_public_sync_twitch', 'twitchpress_manual_public_sync_twitch' );
add_action( 'admin_post_twitchpress_manual_public_sync_streamlabs', 'twitchpress_manual_public_sync_streamlabs' );
                                                                 
// Not logged in (not authenticated) handlers.
add_action( 'admin_post_nopriv_twitchpress_manual_public_sync_twitch', 'twitchpress_admin_post_nopriv_reject_sync' );
add_action( 'admin_post_nopriv_twitchpress_manual_public_sync_streamlabs', 'twitchpress_admin_post_nopriv_reject_sync' );
                                                          

/**
* List of buttons for manual sync for all services. This will initially server for testing
* and early builds until specific requirements are better understood. 
* 
* @param mixed $atts
* 
* @version 2.0
*/
function twitchpress_shortcode_visitor_api_sync_buttons( $atts ) {            
    global $post; 
                       
    // Ensure visitor is logged into WordPress. 
    if( !is_user_logged_in() ) {
        return '<p>' . __( 'You must be logged into WordPress to view the data sync buttons.', 'twitchpress' );
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
                        Data
                    </p>                        
                </th>
                <th> 
                    <p>
                        Action
                    </p>                        
                </th>                
            </tr>';
        
    $atts = shortcode_atts( array(             
            //'channel_id'   => null
    ), $atts, 'twitchpress_sync_buttons_public' );    
                          
    // Twitch
    if( class_exists( 'TWITCHPRESS_Twitch_API' ) && twitchpress_is_user_authorized( get_current_user_id() ) )
    {   
        $url = admin_url( 'admin-post.php?action=twitchpress_manual_public_sync_twitch' );

        $html_output .= '                
        <tr>
            <td>
                Twitch.tv
            </td>
            <td> 
                ' . __( 'All', 'twitchpress' ) . '                        
            </td>
            <td> 
                <a href="' . $url . '" class="button button-primary">Sync</a>                          
            </td>            
        </tr>';           
    }

    // Streamlabs 
    if( class_exists( 'TWITCHPRESS_Streamlabs_API' ) )
    {
        $url = admin_url( 'admin-post.php?action=manual_public_sync_streamlabs' );

        $html_output .= '                
        <tr>
            <td>
                Streamlabs.com
            </td>
            <td> 
                ' . __( 'All', 'twitchpress' ) . '                        
            </td>            
            <td>
                <a href="' . $url . '" class="button button-primary">Sync</a>               
            </td>            
        </tr>';                      
    }
    
    $html_output .= '            
        </tbody>
    </table>';
                          
    return $html_output;    
}

function twitchpress_manual_public_sync_twitch() {
    global $GLOBALS;

    // Track if anything at all was updated.
    $something_updated = false;                  
    $key = null;
    
    # TODO: call appropriate Twitch API method for processing a WP user Twitch.tv sync                
    
    // Redirect visitor back to original page and trigger pre-set notice to be displayed. 
    twitchpress_shortcode_procedure_redirect( 0, array(), array(), 'twitchpress' );
    exit;
}

function twitchpress_manual_public_sync_streamlabs( $values = array() ) {        
    global $GLOBALS;

    // Track if anything at all was updated.
    $something_updated = false;                  
    $key = null;
    
    # TODO: call appropriate Streamlabs API method for processing a WP user Twitch.tv sync    
    
    // Redirect visitor back to original page and trigger pre-set notice to be displayed. 
    twitchpress_shortcode_procedure_redirect( 0, array(), array(), 'officialstreamlabsextension' );
    exit;
}

function twitchpress_admin_post_nopriv_reject_sync() {
    wp_die( __( 'Please Login First', 'twitchpress' ), __( 'Please Login First', 'twitchpress' ));
    exit;
}