<?php
/**
 * TwitchPress Streamlabs Shortcode
 * 
 * This is a single shortcode offering access to all available Streamlabs data
 * for raw output. See other shortcodes for more technical output.
 * 
 * @author Ryan Bayne  
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Shortcode_Streamlabs' ) ) :

class TwitchPress_Shortcode_Streamlabs {
    
    var $atts = null;
    var $output = null;
    var $api_response = null;
    
    public function init() {
        $this->load_streamlabs_api();
        $this->get_data(); 
    }
    
    public function load_streamlabs_api() {
        $this->streamlabs_api = new TWITCHPRESS_Streamlabs_API( 'default' );
        
        if( !$this->streamlabs_api->is_app_set() ) {
            $this->output = __( 'Streamlabs data was meant to appear here but the API has not been setup.', 'twitchpress' );
            return;
        }
        
        // This content should be replaced unless there is a fault...
        $this->output = __( 'Streamlabs API has been loaded.', 'twitchpress' );
    }
    
    public function get_data() {
        switch ( $this->atts['value'] ) {
           case 'points':
                $streamlabs_api = new TwitchPress_Streamlabs_API(); 
                $response = $streamlabs_api->api_get_users_points( $this->atts['username'] );
             break;
           default:
                $streamlabs_api = new TwitchPress_Streamlabs_API(); 
                $streamlabs_access_token = twitchpress_streamlabs_get_user_access_token( get_current_user_id() ); 
                $api_response = $streamlabs_api->api_get_user_by_access_token( $streamlabs_access_token );
             break;             
        }            
    }

    public function output() {
        return $this->output;
    }
}

endif;
