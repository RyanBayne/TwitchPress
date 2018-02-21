<?php       
/**
 * TwitchPress Login Extension - Uninstallation class.
 * 
 * @author   Ryan Bayne
 * @category Configuration
 * @package  TwitchPress Login Extension/Configuration
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Login_Uninstall' ) ) : 

/**
 * TwitchPress_Login_Uninstall Class.
 */
class TwitchPress_Login_Uninstall {
    /**
    * Called when Deactive is clicked on the Plugins view. 
    * 
    * This is not the uninstallation but some level of cleanup can be run here. 
    * 
    * @version 1.0
    */
    public static function deactivate() {
        
    }
    
    /**
    * Uninstall all of the plugins options without any care. Meant as a developer tool and 
    * a part of 100% uninstallation.
    * 
    * @version 1.0
    */
    public static function uninstall_options() {

    }    
}

endif;