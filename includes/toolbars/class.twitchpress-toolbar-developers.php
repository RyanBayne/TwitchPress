<?php
/**
 * TwitchPress - Developer Toolbar
 *
 * The developer toolbar requires the "seniordeveloper" custom capability. The
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
        if( !current_user_can( 'seniordeveloper' ) ) return false;
        $this->init(); 
    }    
    
    private function init() {
        global $wp_admin_bar, $twitchpress_settings;  
        
        // Top Level/Level One
        $args = array(
            'id'     => 'twitchpress-toolbarmenu-developers',
            'title'  => __( 'TwitchPress Developers', 'text_domain' ),          
        );
        $wp_admin_bar->add_menu( $args );
        
            // Group - Debug Tools
            $args = array(
                'id'     => 'twitchpress-toolbarmenu-debugtools',
                'parent' => 'twitchpress-toolbarmenu-developers',
                'title'  => __( 'Debug Tools', 'text_domain' ), 
                'meta'   => array( 'class' => 'first-toolbar-group' )         
            );        
            $wp_admin_bar->add_menu( $args );

                // error display switch        
                $href = wp_nonce_url( admin_url() . 'admin.php?page=' . $_GET['page'] . '&twitchpressaction=' . 'debugmodeswitch'  . '', 'debugmodeswitch' );
                if( !isset( $twitchpress_settings['displayerrors'] ) || $twitchpress_settings['displayerrors'] !== true ) 
                {
                    $error_display_title = __( 'Hide Errors', 'twitchpress' );
                } 
                else 
                {
                    $error_display_title = __( 'Display Errors', 'twitchpress' );
                }
                $args = array(
                    'id'     => 'twitchpress-toolbarmenu-errordisplay',
                    'parent' => 'twitchpress-toolbarmenu-debugtools',
                    'title'  => $error_display_title,
                    'href'   => $href,            
                );
                $wp_admin_bar->add_menu( $args );    
    }
    
}   

endif;

return new TwitchPress_Admin_Toolbar_Developers();
