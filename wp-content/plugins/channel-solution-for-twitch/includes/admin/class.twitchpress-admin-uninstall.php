<?php       
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Uninstall' ) ) : 

/**
 * TwitchPress - Uninstallation class.
 * 
 * @author   Ryan Bayne
 * @category Configuration
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
class TwitchPress_Uninstall {
    
    public static function removeall() {
        self::uninstall_options();
    }
    
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
        return;// Requires reworking as this method is outside of the plugins API.
        
        if( !current_user_can( 'activate_plugins' ) ) {
            return false;
        }
        
        // Remove the core options that do not exist as UI settings.
        delete_option( 'twitchpress_version' );
        
        // Uninstall registered user options (none core).
        // Include settings so that we can run through defaults
        include_once( TWITCHPRESS_PLUGIN_DIR_PATH  . 'includes/admin/class.twitchpress-admin-settings.php' );

        $settings = TwitchPress_Admin_Settings::get_settings_pages();

        foreach ( $settings as $section ) {
            if ( ! method_exists( $section, 'get_settings' ) ) {
                continue;
            }
            $subsections = array_unique( array_merge( array( '' ), array_keys( $section->get_sections() ) ) );

            foreach ( $subsections as $subsection ) {
                foreach ( $section->get_settings( $subsection ) as $value ) {
                    if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
                        $autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
                        delete_option( $value['id'] );
                    }
                }
            }
        }
        
        return true;// to indicate option deletion was done.
    }    
}

endif;
