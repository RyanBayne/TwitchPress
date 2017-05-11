<?php
/**
 * TwitchPress - Toolbars Class by Ryan Bayne
 *
 * Add menus to the admin toolbar, front and backend.  
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Toolbars
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}  

if( !class_exists( 'TwitchPress_Toolbars' ) ) :

class TwitchPress_Toolbars {
    
    public function __construct() {
        add_action( 'wp_before_admin_bar_render', array( $this, 'admin_only_toolbars' ) );                
    }   
    
    public function admin_only_toolbars() {
        if( !current_user_can( 'activate_plugins' ) ) return;
        
        // Even administrators need protected from the power of some tools
        if( !current_user_can( 'seniordeveloper' ) ) return;
        include_once( 'class.twitchpress-toolbar-developers.php' );
    } 
}

endif;

return new TwitchPress_Toolbars();