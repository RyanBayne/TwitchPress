<?php       
/**
 * TwitchPress - Installation
 *
 * Installation of post types, taxonomies, database tables, options etc. 
 *
 * @author   Ryan Bayne
 * @category Configuration
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Install' ) ) : 

/**
 * TwitchPress_Install Class.
 */
class TwitchPress_Install { 
              
    public static function init() {

    }

    /**
     * Install TwitchPress by Ryan Bayne.
     */
    public static function install() {
        global $wpdb;

        if ( ! defined( 'TWITCHPRESS_LOGIN_INSTALLING' ) ) {
            define( 'TWITCHPRESS_LOGIN_INSTALLING', true );
        }

        self::create_options();

        self::update_package_version();
                                     
        // Flush rules after install
        flush_rewrite_rules();
         
        // Trigger action
        do_action( 'twitchpress_installed' );
    }
    
    private static function update_package_version() {
        delete_option( 'twitchpress_loginextension_version' );
        update_option( 'twitchpress_loginextension_version', TWITCHPRESS_LOGIN_VERSION );
    }
        
    /**
     * Default options.
     *
     * Sets up the default options used on the settings page.
     */
    private static function create_options() {

    }                     
}

endif;

TwitchPress_Install::init();