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
class TwitchPress_Subscribers_Menus extends TwitchPress_Admin_Menus {
    
    /**
     * Hook in tabs.
     */
    public function __construct() {
        define( 'TWITCHPRESS_MENU_SUBSCRIBERS', true );        
    } 
}

endif;

return new TwitchPress_Subscribers_Menus();