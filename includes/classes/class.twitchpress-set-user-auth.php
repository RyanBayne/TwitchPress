<?php
/**
 * TwitchPress - Class sets the current users Twitch API oauth credentials.   
 * 
 * @author   Ryan Bayne
 * @category Scripts
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Set_User' ) ) :

class TwitchPress_Set_User {

    public $wp_user_id = null;
    
    function __construct() { 
        
    }
    
    function init() {
        add_action( 'wp_loaded', array( $this, 'set' ), 5 );    
    }
    
    function set() {     
        if( is_user_logged_in() ) {
            $this->wp_user_id = wp_get_current_user();    
        }       
    }
}

endif;

// Do normal registration of the class.
TwitchPress_Object_Registry::add( 'currentusertwitchauth', new TwitchPress_Set_User() );

// Now get the registered object so we can initialize it. 
// This is required due to the use of is_user_logged_in() and the order of loading.
$obj = TwitchPress_Object_Registry::get( 'currentusertwitchauth' );
$obj->init();

                                

