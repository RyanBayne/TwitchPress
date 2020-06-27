<?php       
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Deactivate' ) ) : 

// Include parent settings class, we use its methods to cycle through options.
include_once( TWITCHPRESS_PLUGIN_DIR_PATH . '/includes/admin/class.twitchpress-admin-settings.php' );

/**
 * TwitchPress - Uninstallation class.
 * 
 * @author   Ryan Bayne
 * @category Configuration
 * @package  TwitchPress/Core
 * @since    2.0
 */
class TwitchPress_Deactivate {
    
    /**
    * Called when Deactive is clicked on the Plugins view. 
    * 
    * This is not the uninstallation but some level of cleanup can be run here. 
    * 
    * @version 1.0
    */
    public static function deactivate() {
        
    }
      
}

endif;
