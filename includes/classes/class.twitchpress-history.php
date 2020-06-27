<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( !class_exists( 'TwitchPress_History' ) ) :

/**
 * TwitchPress Class for establishing longterm history. Use the Traces
 * approach for short-term data storage that focuses on troubleshooting
 * procedures.
 * 
 * This class does not offer a UI.    
 * 
 * @class    TwitchPress_History
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Core
 * @version  1.0.0
 */
class TwitchPress_History {
    public $twitchpress_history = null;
    
    public static function init() {         

    }
    
    public function new_entry( $new_value, $old_value, $type, $reason, $wp_user_id = null ) {
        $history_array = $this->get_all_entries();
        if( !$history_array ) { $history_array = array(); }
        $history_array[] = array( 
            'new_value' => $new_value, 
            'old_value' => $old_value, 
            'change_time' => time(), 
            'wp_user_id' => $wp_user_id 
        );       
        $this->delete_all_entries();
        $this->set_all_entries( $history_array );
    }

    public function set_all_entries( $history_array ) {
        return set_transient( 'twitchpress_history', $history_array );    
    }
    
    public function delete_all_entries() {
        return delete_transient( 'twitchpress_history' );    
    }
    
    public function get_all_entries() {
        return get_transient( 'twitchpress_history' );
    }    

}  

endif;

TwitchPress_History::init();