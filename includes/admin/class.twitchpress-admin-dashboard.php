<?php                 
/**
 * TwitchPress - WP Admin Dashboard
 *
 * Custom dashboard widgets and functionality goes here.  
 *
 * @author   Ryan Bayne
 * @category WordPress Dashboard
 * @package  TwitchPress/Admin
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TwitchPress_Admin_Dashboard' ) ) :

/**
 * TwitchPress_Admin_Dashboard Class.
 */
class TwitchPress_Admin_Dashboard {
    
    public function __construct() {
        $this->dashboard_directory = TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/admin/dashboard/';
    }
    
    /**
     * Init entire range of dashboard widgets.
     */
    public function init() {           
        add_action( 'wp_dashboard_setup', array( $this, 'load_dashboard_widgets' ) );
    }        
    
    /**
    * Establish which dashboard widgets should be loaded and then require files.
    * 
    * @version 1.0
    */
    public function load_dashboard_widgets() {
        if ( current_user_can( 'activate_plugins' ) ) {
            include_once( $this->dashboard_directory . 'class.twitchpress-dashboard-mychannel.php' );    
        }
    }
    
}

endif;

$d = new TwitchPress_Admin_Dashboard();
$d->init();
unset($d);
