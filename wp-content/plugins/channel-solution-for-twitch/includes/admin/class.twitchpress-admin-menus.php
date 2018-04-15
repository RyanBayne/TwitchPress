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
        
        add_submenu_page( $this->slug, __('Feed Posts',  $this->slug), __('Feed Posts',  $this->slug), 'manage_options', 'edit.php?post_type=twitchfeed', '' );
        add_submenu_page( $this->slug, __('Channels',    $this->slug), __('Channels',    $this->slug), 'manage_options', 'edit.php?post_type=twitchchannels', '' );
        add_submenu_page( $this->slug, __('Tools',       $this->slug), __('Tools',       $this->slug), 'manage_options', 'twitchpress_tools', array( $this, 'tools_page' ) );
        add_submenu_page( $this->slug, __('Data',        $this->slug), __('Data',        $this->slug), 'manage_options', 'twitchpress_data', array( $this, 'data_page' ) );

        // PayPal, Patreon, Streamtip
        if( defined( 'TWITCHPRESS_MENU_FINANCE' ) ) {
            add_submenu_page( $this->slug, __('Finance', $this->slug), __('Finance', $this->slug), 'manage_options', 'twitchpress_finance', array( $this, 'finance_page' ) );        
        }
        
        // Bots, Commands
        if( defined( 'TWITCHPRESS_MENU_CHAT' ) ) {
            add_submenu_page( $this->slug, __('Chat', $this->slug), __('Chat', $this->slug), 'manage_options', 'twitchpress_chat', array( $this, 'chat_page' ) );        
        }
        
        // Administrator, Visitor, Viewers, Moderators, Subscribers
        if( defined( 'TWITCHPRESS_MENU_ACTIVITY' ) ) {
            add_submenu_page( $this->slug, __('Activity', $this->slug), __('Activity', $this->slug), 'manage_options', 'twitchpress_activity', array( $this, 'activity_page' ) );        
        }
        
        // XBox, Steam, Twitter, Reddit, Facebook        
        if( defined( 'TWITCHPRESS_MENU_SOCIAL' ) ) {
            add_submenu_page( $this->slug, __('Social', $this->slug), __('Social', $this->slug), 'manage_options', 'twitchpress_social', array( $this, 'social_page' ) );        
        }
        
        // YouTube, XBox, Steam, Facebook
        if( defined( 'TWITCHPRESS_MENU_MEDIA' ) ) {
            add_submenu_page( $this->slug, __('Media', $this->slug), __('Media', $this->slug), 'manage_options', 'twitchpress_media', array( $this, 'media_page' ) );                    
        }
    }

    /**
     * Init the settings page.
     */
    public function settings_page() {    
        TwitchPress_Admin_Settings::output();
    }
    
    public function tools_page() {
        TwitchPress_Admin_Tools_Views::output();    
    }
    
    public function data_page() {
        TwitchPress_Admin_Data_Views::output();    
    }      

    public function finance_page() {
        TwitchPress_Admin_Data_Views::output();    
    }  
    
    public function chat_page() {
        TwitchPress_Admin_Data_Views::output();    
    }  

    public function activity_page() {
        TwitchPress_Admin_Data_Views::output();    
    }  
    
    public function social_page() {
        TwitchPress_Admin_Data_Views::output();    
    }  
       
    public function media_page() {
        TwitchPress_Admin_Data_Views::output();    
    }  
}

endif;

return new TwitchPress_Admin_Menus();