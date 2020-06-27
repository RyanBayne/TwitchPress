<?php
/**
 * Adds notices to WP official login form
 * 
 * @author   Ryan Bayne
 * @category User Interface
 * @package  TwitchPress Login Extension
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists( 'TwitchPress_Custom_Login_Messages') ) :

class TwitchPress_Custom_Login_Messages {
    public $twitchpress_login_messages = array();    
    
    public function __construct() {
        add_filter( 'login_message', array( $this, 'build_messages'), 5 );        
    }
    
    /**
    * Adds new error to custom array which we output above the
    * login form in a custom way. It's all a hack but clean enough.
    * 
    * @param mixed $message
    * 
    * @version 1.0
    */
    public function add_error( $message ) {     
        $this->twitchpress_login_messages[] = array( 
            'type' => 'error', 
            'message' => $message 
        );
        
        update_option( 'twitchpress_login_messages', $this->twitchpress_login_messages );
    }
    
    /**
    * Build the block of HTML notices that will be output
    * above login form.
    * 
    * @version 1.1
    */
    public function build_messages( $message ) {
       
        if( $this->twitchpress_login_messages ) {                
            foreach( $this->twitchpress_login_messages as $key => $error ) {
                if( $error['type'] == 'info' ) {
                    $message .= "<p class='message'>" . esc_html( $error['message'] ) . "</p>";        
                } elseif( $error['type'] == 'error' ) {
                    $message .= '<div id="login_error">' . esc_html( $error['message'] )  . '</div>';
                }
            } 
        }
        
        return $message;
    }    
}           

endif;