<?php       
/**
 * TwitchPress Extension Installed
 * 
 * @author   Ryan Bayne
 * @category Configuration
 * @package  TwitchPress/Core
 * @since    2.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Extension_Installer' ) ) : 

/**
 * TwitchPress_Extension_Installer installs plugins directly from WordPress.org
 */
class TwitchPress_Extension_Installer {      
                   
    public static function init() {       
        // Reduce this operation to administrators only...
        add_action( 'twitchpress_plugin_background_installer', array( __CLASS__, 'background_installer' ), 10, 2 );
    }

    /**
     * Install a plugin from .org in the background via a cron job (used by installer - opt in).
     * 
     * @param string $plugin_to_install_id
     * @param array $plugin_to_install
     * @since 1.2.7
     * 
     * @version 3.0
     */
    public static function background_installer( $plugin_to_install_id, $plugin_to_install ) {
        // Explicitly clear the event.
        wp_clear_scheduled_hook( 'twitchpress_plugin_background_installer', func_get_args() );
          
        if ( ! empty( $plugin_to_install['repo-slug'] ) ) {
            
            // Requires some core WP files.
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
            require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

            WP_Filesystem();

            $skin              = new Automatic_Upgrader_Skin;
            $upgrader          = new WP_Upgrader( $skin );
            $plugin_slug       = $plugin_to_install['repo-slug'];
            $installed_plugins = array_map( twitchpress_format_plugin_slug( $plugin_slug ), array_keys( get_plugins() ) );
            $plugin            = $plugin_slug . '/' . $plugin_slug . '.php';
            $installed         = false;
            $activate          = false;

            // See if the plugin is installed already
            if ( in_array( $plugin_to_install['repo-slug'], $installed_plugins ) ) {
                $installed = true;
                $activate  = ! is_plugin_active( $plugin );
            }

            // Install this thing!
            if ( ! $installed ) {
                // Suppress feedback
                ob_start();

                try {
                    $plugin_information = plugins_api( 'plugin_information', array(
                        'slug'   => $plugin_to_install['repo-slug'],
                        'fields' => array(
                            'short_description' => false,
                            'sections'          => false,
                            'requires'          => false,
                            'rating'            => false,
                            'ratings'           => false,
                            'downloaded'        => false,
                            'last_updated'      => false,
                            'added'             => false,
                            'tags'              => false,
                            'homepage'          => false,
                            'donate_link'       => false,
                            'author_profile'    => false,
                            'author'            => false,
                        ),
                    ) );

                    if ( is_wp_error( $plugin_information ) ) {
                        throw new Exception( $plugin_information->get_error_message() );
                    }

                    $package  = $plugin_information->download_link;
                    $download = $upgrader->download_package( $package );

                    if ( is_wp_error( $download ) ) {
                        throw new Exception( $download->get_error_message() );
                    }

                    $working_dir = $upgrader->unpack_package( $download, true );

                    if ( is_wp_error( $working_dir ) ) {
                        throw new Exception( $working_dir->get_error_message() );
                    }

                    $result = $upgrader->install_package( array(
                        'source'                      => $working_dir,
                        'destination'                 => WP_PLUGIN_DIR,
                        'clear_destination'           => false,
                        'abort_if_destination_exists' => false,
                        'clear_working'               => true,
                        'hook_extra'                  => array(
                            'type'   => 'plugin',
                            'action' => 'install',
                        ),
                    ) );

                    if ( is_wp_error( $result ) ) {
                        throw new Exception( $result->get_error_message() );
                    }

                    $activate = true;

                } catch ( Exception $e ) {
                    TwitchPress_Admin_Notices::add_custom_notice(
                        $plugin_to_install_id . '_install_error',
                        sprintf(
                            __( '%1$s could not be installed (%2$s). <a href="%3$s">Please install it manually by clicking here.</a>', 'twitchpress' ),
                            $plugin_to_install['name'],
                            $e->getMessage(),
                            esc_url( admin_url( 'index.php?wc-install-plugin-redirect=' . $plugin_to_install['repo-slug'] ) )
                        )
                    );
                }

                // Discard feedback
                ob_end_clean();
            }

            wp_clean_plugins_cache();

            // Activate this thing
            if ( $activate ) {
                try {
                    $result = activate_plugin( $plugin );

                    if ( is_wp_error( $result ) ) {
                        throw new Exception( $result->get_error_message() );
                    }
                } catch ( Exception $e ) {
                    TwitchPress_Admin_Notices::add_custom_notice(
                        $plugin_to_install_id . '_install_error',
                        sprintf(
                            __( '%1$s was installed but could not be activated. <a href="%2$s">Please activate it manually by clicking here.</a>', 'twitchpress' ),
                            $plugin_to_install['name'],
                            admin_url( 'plugins.php' )
                        )
                    );
                }
            }
        }
    }                     

}

endif;
                       
TwitchPress_Extension_Installer::init();