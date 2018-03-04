<?php       
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Uninstall' ) ) : 

// Include parent settings class, we use its methods to cycle through options.
include_once( TWITCHPRESS_PLUGIN_DIR_PATH . '/includes/admin/class.twitchpress-admin-settings.php' );

/**
 * TwitchPress - Uninstallation class.
 * 
 * @author   Ryan Bayne
 * @category Configuration
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
class TwitchPress_Uninstall {
    
    public static function run() {
        
        //if( 'yes' == get_option( 'twitchpress_remove_options' ) ) { self::remove_options(); }
        //if( 'yes' == get_option( 'twitchpress_remove_feed_posts' ) ) { self::remove_feed_posts(); }
        //if( 'yes' == get_option( 'twitchpress_remove_database_tables' ) ) { self::remove_database_tables(); }
        //if( 'yes' == get_option( 'twitchpress_remove_extensions' ) ) { self::remove_extensions(); }
        //if( 'yes' == get_option( 'twitchpress_remove_user_data' ) ) { self::remove_user_data(); }
        if( 'yes' == get_option( 'twitchpress_remove_media' ) ) { self::remove_media(); }

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
    public static function remove_options() {
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
    
    /**
    * Remove feed posts (the core plugins custom post type)
    * 
    * @version 1.0
    */
    public static function remove_feed_posts() {}
    
    /**
    * Remove database tables created by the TwitchPress core.
    * 
    * @version 1.0 
    */
    public static function remove_database_tables() {}
    
    /**
    * Remove all TwitchPress extensions. 
    * 
    * @version 1.0
    */
    public static function remove_extensions() {}
    
    /**
    * Remove all user data created by the core plugin.
    * 
    * @version 1.0
    */
    public static function remove_user_data() {}
    
    /**
    * Remove media created by TwitchPress. 
    * 
    * @version 1.0
    */
    public static function remove_media() {
        do_action( 'twitchpress_test' );
    }

}

endif;
