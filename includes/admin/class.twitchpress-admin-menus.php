<?php
/**
 * TwitchPress - Plugin Menus
 *
 * Maintain plugins admin menu and tab-menus here.  
 *
 * @author   Ryan Bayne
 * @category User Interface
 * @package  TwitchPress/Admin
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TwitchPress_Admin_Menus' ) ) :

/**
 * TwitchPress_Admin_Menus Class.
 */
class TwitchPress_Admin_Menus {

    /**
     * Hook in tabs.
     */
    public function __construct() {
        //add_action( 'admin_menu', array( $this, 'data_menu' ), 9 );
        add_action( 'admin_menu', array( $this, 'settings_menu' ), 100 );
    }

    /**
     * Add menu items.
     */
    public function data_menu() {
        $settings_page = add_submenu_page( 'edit.php?post_type=twitchfeed', __( 'TwitchPress Data Views', 'twitchpress' ),  __( 'Data Views', 'twitchpress' ) , 'activate_plugins', 'twitchpress-data', array( $this, 'data_page' ) ); 
    }

    /**
     * Add settings menu item to the existing Settings menu.
     */
    public function settings_menu() {
        $settings_page = add_submenu_page( 'edit.php?post_type=twitchfeed', __( 'TwitchPress Settings', 'twitchpress' ),  __( 'Settings', 'twitchpress' ) , 'activate_plugins', 'twitchpress-settings', array( $this, 'settings_page' ) ); 
        add_action( 'load-' . $settings_page, array( $this, 'settings_page_init' ) );
    }
        
    /**
    * Init the main page. 
    */
    public function data_page() { 
        TwitchPress_Admin_Main_Views::output(); 
    }
        
    /**
     * Init the settings page.
     */
    public function settings_page() {    
        TwitchPress_Admin_Settings::output();
    }
    
    /**
     * Loads settings into memory for use within this view.
     */
    public function settings_page_init() {

    }
      
}

endif;

return new TwitchPress_Admin_Menus();