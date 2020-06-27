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
 * 
 * @version 2.0
 */
class TwitchPress_Admin_Menus {
    var $slug = 'twitchpress';
    
    function primary_admin_menu() {
        $this->pagehook = add_menu_page( __('TwitchPress', $this->slug), __('TwitchPress', $this->slug), 'manage_options', $this->slug, array(&$this, 'settings_page'), 'dashicons-admin-users', '42.78578' );
        add_submenu_page( $this->slug, __('Settings', $this->slug), __('Settings', $this->slug), 'manage_options', $this->slug, array(&$this, 'settings_page') );
    }
    
    /**
    * Adds items to the plugins administration menu...
    * 
    * @version 2.0
    */
    function secondary_menu_items() {
        
        add_submenu_page( $this->slug, __('Channels', $this->slug), __('Channels', $this->slug), 'manage_options', 'edit.php?post_type=channels', '' );
        
        if( 'yes' == get_option( 'twitchpress_perks_switch' ) ) {
            add_submenu_page( $this->slug, __('Perks',    $this->slug), __('Perks',    $this->slug), 'manage_options', 'edit.php?post_type=perks', '' );
        }
        
        if( 'yes' == get_option( 'twitchpress_giveaways_switch' ) ) {
            add_submenu_page( $this->slug, __('Giveaways',$this->slug), __('Giveaways',$this->slug), 'manage_options', 'edit.php?post_type=giveaways', '' );
        }
        
        if( 'yes' == get_option( 'twitchpress_webhooks_switch' ) ) {
            add_submenu_page( $this->slug, __('Webhooks',    $this->slug), __('Webhooks',    $this->slug), 'manage_options', 'edit.php?post_type=webhooks', '' );        
        }
        
        if( 'yes' == get_option( 'twitchpress_subscribers_switch' ) ) {
            add_submenu_page( $this->slug, __('Subscribers', $this->slug), __('Subscribers', $this->slug), 'manage_options', 'twitchpress_subscribers', array( $this, 'subscribers_page' ) );           
        }
        
        if( 'yes' == get_option( 'twitchpress_bot_switch' ) ) {
            add_submenu_page( $this->slug, __('Bot',         $this->slug), __('Bot',         $this->slug), 'manage_options', 'twitchpress_bot', array( $this, 'bot_page' ) );
        }
        
        add_submenu_page( $this->slug, __('Tools',    $this->slug), __('Tools',    $this->slug), 'manage_options', 'twitchpress_tools', array( $this, 'tools_page' ) );
        add_submenu_page( $this->slug, __('Data',     $this->slug), __('Data',     $this->slug), 'manage_options', 'twitchpress_data', array( $this, 'data_page' ) );
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
    
    public function subscribers_page() {
        TwitchPress_Admin_Subscribers_Views::output();    
    }
    
    public function data_page() {
        TwitchPress_Admin_Data_Views::output();    
    }      

    public function funds_page() {
        TwitchPress_Admin_Funds_Views::output();    
    }  
    
    public function chat_page() {
        TwitchPress_Admin_Chat_Views::output();    
    }  

    public function activity_page() {
        TwitchPress_Admin_Activity_Views::output();    
    }  
    
    public function social_page() {
        TwitchPress_Admin_Social_Views::output();    
    }  
       
    public function media_page() {
        TwitchPress_Admin_Data_Views::output();    
    }       
    
    public function bot_page() {
        TwitchPress_Admin_Bot_Views::output();    
    }  
}

endif;

$class = new TwitchPress_Admin_Menus();

add_action('admin_menu', array( $class, 'primary_admin_menu'), 0);
add_action('admin_menu', array( $class, 'secondary_menu_items'), 1000); 

unset($class);