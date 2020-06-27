<?php
/**
 * TwitchPress Admin - Main Admin Class
 *
 * The primary for main add_action() and file includes during an administration side request. There is
 * also a functions.twitchpress-admin.php for functions strictly related to admin.  
 * 
 * Do not include files only meant for the frontside.
 * Do not queue scripts or css only meant for frontside. 
 * 
 * @class    TwitchPress_Admin
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * TwitchPress_Admin class.
 */
class TwitchPress_Admin {

    /**
     * Constructor.
     */
    public function __construct() {         
        add_action( 'init',               array( $this, 'includes_requiring_main_init' ), 1 );
        add_action( 'init',               array( $this, 'includes_requiring_admin_init' ), 1 );
        add_action( 'current_screen',     array( $this, 'conditional_includes' ) );
        add_action( 'admin_init',         array( $this, 'buffer' ), 1 );
        add_action( 'admin_init',         array( $this, 'admin_redirects' ) );
        add_action( 'admin_footer',       'twitchpress_print_js', 25 );
        add_filter( 'admin_footer_text',  array( $this, 'admin_footer_text' ), 1 );
        add_action( 'in_plugin_update_message-' . TWITCHPRESS_PLUGIN_URL, array( $this, 'in_plugin_update_message' ) );        
    }

    /**
     * Output buffering allows admin screens to make redirects later on.
     */
    public function buffer() {
        ob_start();
    }

    /**
     * Include any classes we need within admin...
     * 
     * @version 2.0
     */
    public function includes_requiring_main_init() {
        
        // Functions
        include_once( dirname( __FILE__ ) . '/functions.twitchpress-admin.php' );
        
        // Class
        include_once( dirname( __FILE__ ) . '/class.twitchpress-admin-menus.php' );
        include_once( dirname( __FILE__ ) . '/class.twitchpress-admin-notices.php' );
        include_once( dirname( __FILE__ ) . '/class.twitchpress-admin-assets.php' );
        include_once( dirname( __FILE__ ) . '/class.twitchpress-admin-help.php' );
        include_once( dirname( __FILE__ ) . '/class.twitchpress-admin-quicktools.php' );
        
        // Help Tabs
        if ( apply_filters( 'twitchpress_enable_admin_help_tab', true ) ) {
            include_once( dirname( __FILE__ ) . '/class.twitchpress-admin-help.php' );
        }
                
        // Setup/welcome
        if ( ! empty( $_GET['page'] ) ) {
            switch ( $_GET['page'] ) {
                case 'twitchpress-setup' :
                    include_once( dirname( __FILE__ ) . '/class.twitchpress-admin-setup-wizard.php' );
                break;
                case 'twitchpress-setup-youtube' :
                    include_once( dirname( __FILE__ ) . '/class.twitchpress-admin-setup-wizard-youtube.php' );
                break;
                 case 'twitchpress-traces' :
                    include_once( dirname( __FILE__ ) . '/views/dataviews/class.twitchpress-view-trace.php' );
                break;
            }
        }
    }

    /**
    * Include files that aren't needed on core init...
    * 
    * @version 1.0
    */
    public function includes_requiring_admin_init() {
        include_once( dirname( __FILE__ ) . '/class.twitchpress-admin-pointers.php' );        
    }
    
    /**
     * Include admin files conditionally based on specific page...
     * 
     * @version 2.0
     */
    public function conditional_includes() {

        if ( ! $screen = get_current_screen() ) {
            return;
        }

        switch ( $screen->id ) {
            case 'dashboard' :      
                include( 'class.twitchpress-admin-dashboard.php' );
            break;
            case 'twitchpress' :
            break;
            case 'users' :
            break;
            case 'user' :
            break;
            case 'profile' :
            break;
            case 'user-edit' :
            break;
            case 'twitchpress-settings' :
            break;
        }
    }

    /**
    * Displays an additional message within the core plugin-update notice...
    * 
    * @param mixed $args
    * 
    * @version 1.0
    */
    static function in_plugin_update_message( $args ) {
        $show_additional_notice = false;
        if ( isset( $args['new_version'] ) ) {
            $old_version_array = explode( '.', TWITCHPRESS_VERSION );
            $new_version_array = explode( '.', $args['new_version'] );

            if ( $old_version_array[0] < $new_version_array[0] ) {
                $show_additional_notice = true;
            } else {
                if ( $old_version_array[1] < $new_version_array[1] ) {
                    $show_additional_notice = true;
                }
            }

        }

        if ( $show_additional_notice ) {
            ob_start(); ?>

            <style type="text/css">
                .twitchpress_plugin_upgrade_notice {
                    font-weight: 400;
                    color: #fff;
                    background: #d53221;
                    padding: 1em;
                    margin: 9px 0;
                    display: block;
                    box-sizing: border-box;
                    -webkit-box-sizing: border-box;
                    -moz-box-sizing: border-box;
                }

                .twitchpress_plugin_upgrade_notice:before {
                    content: "\f348";
                    display: inline-block;
                    font: 400 18px/1 dashicons;
                    speak: none;
                    margin: 0 8px 0 -2px;
                    -webkit-font-smoothing: antialiased;
                    -moz-osx-font-smoothing: grayscale;
                    vertical-align: top;
                }
            </style>

            <span class="twitchpress_plugin_upgrade_notice">
                <?php printf( __( '%s is a major update - please backup of your site before updating.', 'twitch-press' ), $args['new_version'] ); ?>
            </span>

            <?php ob_get_flush();
        }  
    }
    
    /**
     * Handle redirects to setup/welcome page after install and updates.
     *
     * For setup wizard, transient must be present, the user must have access rights, and we must ignore the network/bulk plugin updaters.
     * 
     * @version 1.2
     */
    public function admin_redirects() {

        // Nonced plugin install redirects (whitelisted)
        if ( ! empty( $_GET['twitchpress-install-plugin-redirect'] ) ) {
            $plugin_slug = twitchpress_clean( $_GET['twitchpress-install-plugin-redirect'] );

            if ( current_user_can( 'install_plugins' ) && in_array( $plugin_slug, array( 'twitchpress-gateway-stripe' ) ) ) {
                $nonce = wp_create_nonce( 'install-plugin_' . $plugin_slug );
                $url   = self_admin_url( 'update.php?action=install-plugin&plugin=' . $plugin_slug . '&_wpnonce=' . $nonce );
            } else {
                $url = admin_url( 'plugin-install.php?tab=search&type=term&s=' . $plugin_slug );
            }
                  
            twitchpress_redirect_tracking( $url, __LINE__, __FUNCTION__ );          
            exit;
        }

        // Setup wizard redirect after plugin activation. 
        if ( get_transient( '_twitchpress_activation_redirect' ) ) {
            delete_transient( '_twitchpress_activation_redirect' );

            if ( ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], array( 'twitchpress-setup' ) ) ) || is_network_admin() || isset( $_GET['activate-multi'] ) || apply_filters( 'twitchpress_prevent_automatic_wizard_redirect', false ) ) {
                return;
            }

            // If the user needs to install, send them to the setup wizard
            if ( TwitchPress_Admin_Notices::has_notice( 'install' ) ) {
                $admin_url = admin_url( 'index.php?page=twitchpress-setup' );        
                twitchpress_redirect_tracking( $admin_url, __LINE__, __FUNCTION__ );
                exit;
            }
        }       
    }

    /**
     * Change the admin footer text on WordPress TwitchPress admin pages.
     */
    public function admin_footer_text( $footer_text ) {
        if ( ! current_user_can( 'manage_twitchpress' ) ) {
            return;
        }
        $current_screen = get_current_screen();
        $twitchpress_pages   = twitchpress_get_screen_ids();

        // Check to make sure we're on a TwitchPress admin page
        if ( isset( $current_screen->id ) && apply_filters( 'twitchpress_display_admin_footer_text', in_array( $current_screen->id, $twitchpress_pages ) ) ) {
            //$footer_text = __( 'Thank you for planting a WordPress TwitchPress. I recommend removing this footer message. This text is an example only.', 'twitchpress' );
        }

        return $footer_text;
    }
}

new TwitchPress_Admin();    
