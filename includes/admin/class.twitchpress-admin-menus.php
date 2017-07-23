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
        $this->slug = 'twitchpress';
        
        add_action('admin_menu', array( &$this, 'primary_admin_menu'), 0);
        add_action('admin_menu', array( &$this, 'secondary_menu_items'), 1000);        
    }
    
    function primary_admin_menu() {
        $this->pagehook = add_menu_page( __('TwitchPress', $this->slug), __('TwitchPress', $this->slug), 'manage_options', $this->slug, array(&$this, 'settings_page'), 'dashicons-admin-users', '42.78578');
        add_submenu_page( $this->slug, __('Settings', $this->slug), __('Settings', $this->slug), 'manage_options', $this->slug, array(&$this, 'settings_page') );

    }
    
    function secondary_menu_items() {
        
        add_submenu_page( $this->slug, __('Feed Posts', $this->slug), __('Feed Posts', $this->slug), 'manage_options', 'edit.php?post_type=twitchfeed', '', '' );
        add_submenu_page( $this->slug, __('Channels',   $this->slug), __('Channels',   $this->slug), 'manage_options', 'edit.php?post_type=twitchchannels', '', '' );
   
    }

    /**
     * Init the settings page.
     */
    public function settings_page() {    
        TwitchPress_Admin_Settings::output();
    }
      
}

endif;

return new TwitchPress_Admin_Menus();