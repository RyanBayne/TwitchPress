<?php
/**
 * TwitchPress Listener for $_GET requests.
 * 
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Toolbars
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}  

if( !class_exists( 'TwitchPress_Listener' ) ) :

class TwitchPress_Listener {  
    public function __construct() {
        add_action( 'wp_loaded', array( $this, 'GET_requests_listener' ) );
    }
         
    /**
    * Call methods for processing requests after all of the common
    * security checks have been done for the request your making.
    * 
    * @version 1.2
    */
    public function GET_requests_listener() {
        if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
            return;
        }

        if( !isset( $_GET['twitchpressaction'] ) ) {
            return;    
        }

        if( !isset( $_REQUEST['_wpnonce'] ) ) {
            return;    
        }
        
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        
        if( defined( 'DOING_CRON' ) && DOING_CRON ) {
            return;    
        }        
        
        if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;    
        }
       
        // Start of public able requests. 
        
        // End of public able requests. 
        
        if( !is_user_logged_in() ) {       
            return;
        }        
        
        if( !user_can( TWITCHPRESS_CURRENTUSERID, 'activate_plugins' ) ) {  
            return;    
        }

        // Developer Toolbar Actions - First call 
        self::developertoolbar_admin_actions();                   
    } 
    
    /**
    * Runs method called by a request made using the Developer Toolbar.
    * 
    * @version 1.0
    */
    private static function developertoolbar_admin_actions() {
        
        if( !isset( $_GET['twitchpressaction'] ) ) { 
            return; 
        }

        // Varify Nonce
        if( ! wp_verify_nonce( $_REQUEST['_wpnonce'], $_GET['twitchpressaction'] ) ) {
            return;    
        }

        switch ( $_GET['twitchpressaction'] ) {
            
           case 'twitchpressuninstalloptions':
                self::developertoolbar_uninstall_settings();
             break;
           case 'twitchpresssyncmainfeedtowp':
                self::developertoolbar_sync_main_channel_feed_to_wp();
             break;
        }       
    }
    
    /**
    * Remove all settings from the Developer Toolbar.
    * 
    * @version 1.1
    */
    public static function developertoolbar_uninstall_settings() {
        // Security is done already but we need safeguards should the method be called elsewhere.
        if( !user_can( TWITCHPRESS_CURRENTUSERID, 'activate_plugins' ) ) {  
            return;    
        }
                               
        $nonce = $_REQUEST['_wpnonce'];
        if ( wp_verify_nonce( $nonce, 'twitchpressuninstalloptions' ) ) {
            //TwitchPress_Uninstall::uninstall_options(); 
   
            TwitchPress_Admin_Notices::add_wordpress_notice(
                'devtoolbaruninstallednotices',
                'success',
                true,
                __( 'Options Removed', 'twitchpress' ),
                __( 'TwitchPress options have been deleted and the plugin will need some configuration to begin using it.', 'twitchpress' ) 
            );
        }  
    }          
}   

endif;

return new TwitchPress_Listener();        