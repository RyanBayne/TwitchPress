<?php                 
/**
 * TwitchPress - WP Admin Dashboard Widget - My Channel  
 *
 * Displays content from the current users Twitch channel. 
 * 
 * @author   Ryan Bayne
 * @category WordPress Dashboard
 * @package  TwitchPress/Admin
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TwitchPress_Dashboard_MyChannel' ) ) :

class TwitchPress_Dashboard_MyChannel {
    
    var $widget_name         = 'mychannel';
    var $required_capability = 'activate_plugins';
    
    /**
     * Init dashboard widgets.
     */
    public function init() {           
        if ( !current_user_can( $this->required_capability ) ) {
            exit;
        }
        
        // Load the dashboard widget...
        //wp_add_dashboard_widget( 'twitchpress_widget_' . $this->widget_name, __( 'My Twitch Channel', 'twitchpress' ), array( $this, 'output' ) );
    }        

    /**
     * Function that outputs the contents of the dashboard widget
     * 
     * @version 1.0
     */
    public function output() {    
        $transient = 'twitchpress_dashboard_mychannel' . TWITCHPRESS_CURRENTUSERID;
        
        // Does the current user have a channel stored? 
        $twitch_id = twitchpress_get_user_twitchid_by_wpid( TWITCHPRESS_CURRENTUSERID );
        
        if( !$twitch_id ) {
            _e( 'Unable to retrieve your Twitch channel data until you add your Twitch ID to your account.', 'twitchpress' );
            return;
        }          
        
        $cache = get_transient( $transient );
        if( $cache) {
            echo $cache; 
        }
        
        $twitch_api_obj = new TWITCHPRESS_Twitch_API_Calls();
        
        $channel = $twitch_api_obj->getChannelObject( $twitch_id );  
        
        if( !$channel ) {
            _e( 'Unable to retrieve your Twitch channel data at this time.', 'twitchpress' );
            return;
        }
        
        $content = '';
        
        // $channel['mature'] bool
        $content .= '<h3>Status: ' . $channel['status'] . '<h3>';
        // ["broadcaster_language"]=>
        // ["broadcaster_software"]=>
        // $channel["display_name"]
        // ["game"]=>
        // ["language"]=>
        // ["_id"]=>
        // ["name"]=>
        // ["created_at"]=>
        // ["updated_at"]=>
        // ["partner"]=>
        // ["logo"]=>
        // ["video_banner"]=>
        // ["profile_banner"]=>
        // ["profile_banner_background_color"]=>
        // ["url"]=>
        $content .= '<p>Views: ' . $channel["views"] . '</p>';
        $content .= '<p>Followers: ' . $channel["followers"] . '</p>'; 
        // ["broadcaster_type"] string
        $content .= '<p>' . $channel["description"] . '</p>';
        // ["private_video"] bool
        // ["privacy_options_enabled"] bool
        
        $content = apply_filters( 'twitchpress_widget_mychannel', $content );      
        
        echo $content;
        
        delete_transient( $transient );
        set_transient( $transient, $content, 120 );
    }      
}

endif; 

$d = new TwitchPress_Dashboard_MyChannel();
$d->init();
unset($d);