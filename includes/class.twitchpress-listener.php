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
    * @version 1.0
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
        
        // Start of TwitchPress administrator only requests.
        self::developertoolbar_uninstall_settings();        
        // End of TwitchPress adminstrator only requests.            
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
            $user_has_permission = TwitchPress_Uninstall::uninstall_options(); 
                   
            if( !$user_has_permission ) {
                TwitchPress_Admin_Notices::add_wordpress_notice(
                    'devtoolbaruninstallednotices',
                    'error',
                    true,
                    __( 'No Permission', 'twitchpress' ),
                    __( 'You do not have the permissions (WP capabilities) required to uninstall all options.', 'twitchpress' ) 
                );
                return false;                
            }  
            
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