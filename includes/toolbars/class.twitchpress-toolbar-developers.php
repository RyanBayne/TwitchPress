<?php
/**
 * TwitchPress - Developer Toolbar
 *
 * The developer toolbar requires the "twitchpressdevelopertoolbar" custom capability. The
 * toolbar allows actions not all key holders should be giving access to. The
 * menu is intended for developers to already have access to a range of
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Toolbars
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}  

if( !class_exists( 'TwitchPress_Admin_Toolbar_Developers' ) ) :

class TwitchPress_Admin_Toolbar_Developers {
    public function __construct() {
        // This is a precaution as the same check is done when including the file.
        if( !current_user_can( 'twitchpressdevelopertoolbar' ) ) {
            return false;
        }
        
        // Initialize the toolbar.
        $this->init(); 
    }    
    
    /**
    * Calls a method per group of items.
    * 
    * @version 1.2
    */
    private function init() {
        global $wp_admin_bar;  

        self::parent_level();
        self::second_level_configuration_options();        
        self::second_level_feed_tools();   
             
    }

    private static function parent_level() {
        global $wp_admin_bar;   
        
        // Top Level/Level One
        $args = array(
            'id'     => 'twitchpress-toolbarmenu-developers',
            'title'  => __( 'TwitchPress Developers', 'text_domain' ),          
        );
        $wp_admin_bar->add_menu( $args );        
    }
    
    /**
    * Adds a group of configuration options i.e. uninstall. 
    * 
    * @version 1.0
    */
    private static function second_level_configuration_options() {
        global $wp_admin_bar;
        
        // Group - Configuration Options
        $args = array(
            'id'     => 'twitchpress-toolbarmenu-configurationoptions',
            'parent' => 'twitchpress-toolbarmenu-developers',
            'title'  => __( 'Configuration Options', 'text_domain' ), 
            'meta'   => array( 'class' => 'second-toolbar-group' )         
        );        
        $wp_admin_bar->add_menu( $args );        
            
            // Item - reinstall plugin options.
            $thisaction = 'twitchpressuninstalloptions';     
        
            $href = twitchpress_returning_url_nonced( array( 'twitchpressaction' => $thisaction ), $thisaction, $_SERVER['REQUEST_URI'] );
                        
            $args = array(
                'id'     => 'twitchpress-toolbarmenu-uninstallsettings',
                'parent' => 'twitchpress-toolbarmenu-configurationoptions',
                'title'  => __( 'Un-Install Settings', 'twitchpress' ),
                'href'   => esc_url( $href ),            
            );
            
            $wp_admin_bar->add_menu( $args );  
    }    
    
    /**
    * Adds tools for testing feed actions.
    * 
    * @version 1.1
    */
    private static function second_level_feed_tools() {
        global $wp_admin_bar;
        
        // Group - Feed tools.
        $args = array(
            'id'     => 'twitchpress-toolbarmenu-feedtools',
            'parent' => 'twitchpress-toolbarmenu-developers',
            'title'  => __( 'Feed Tools', 'text_domain' ), 
            'meta'   => array( 'class' => 'second-toolbar-group' )         
        );        
        $wp_admin_bar->add_menu( $args );        
            
            // Item - Sync main channel feed entries to WordPress. 
            $thisaction = 'twitchpresssyncmainfeedtowp';     
        
            $href = twitchpress_returning_url_nonced( array( 'twitchpressaction' => $thisaction ), $thisaction, $_SERVER['REQUEST_URI'] );
            
            $args = array(
                'id'     => 'twitchpress-toolbarmenu-syncmainfeedtowp',
                'parent' => 'twitchpress-toolbarmenu-feedtools',
                'title'  => __( 'Sync Main Feed - Twitch to WP', 'twitchpress' ),
                'href'   => esc_url( $href ),            
            );
            
            $wp_admin_bar->add_menu( $args );  
    }
}   

endif;

return new TwitchPress_Admin_Toolbar_Developers();
