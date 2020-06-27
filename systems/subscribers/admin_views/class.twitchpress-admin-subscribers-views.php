<?php
/**
 * TwitchPress Admin Table Views
 *
 * @author      TwitchPress
 * @category    Admin
 * @package     WPSeed/Admin
 * @version     2.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
               
if ( ! class_exists( 'TwitchPress_Admin_Subscribers_Views' ) ) :
            
class TwitchPress_Admin_Subscribers_Views {

    /**
     * Handles output of the main tables page in admin.
     */
    public static function output() {       
        $tabs              = self::get_tabs();
        $first_tab         = array_keys( $tabs );
        $current_tab       = ! empty( $_GET['tab'] ) ? sanitize_title( $_GET['tab'] ) : $first_tab[0];
        $current_tablelist = isset( $_GET['twitchpressview'] ) ? sanitize_title( $_GET['twitchpressview'] ) : current( array_keys( $tabs[ $current_tab ]['subscriberstabviews'] ) );

        require_once( 'html-admin-subscribers.php' );
    }

    /**
     * Returns the definitions for the tables to show in admin.
     *
     * @return array
     * 
     * @version 2.0
     */
    public static function get_tabs() {
        $tabviews = array();
        
        // All subscribers table.
        $tabviews['subscribers_all_list_tables'] = array(
            'title'  => __( 'All Subscribers', 'twitchpress' ),
            'subscriberstabviews' => array(
                "all_subscribers" => array(
                    'title'       => __( 'All Entries', 'twitchpress' ),
                    'description' => '',
                    'hide_title'  => true,
                    'callback'    => array( __CLASS__, 'get_subscriberstabview' )
                )                   
            )
        );   
             
        // Recent Subscription Changes
        $tabviews['subscribers_changes_list_tables'] = array(
            'title'  => __( 'Recent Subscription Changes', 'twitchpress' ),
            'subscriberstabviews' => array(
                "changes_subscribers" => array(
                    'title'       => __( 'Recent Subscription Changes', 'twitchpress' ),
                    'description' => '',
                    'hide_title'  => true,
                    'callback'    => array( __CLASS__, 'get_subscriberstabview' )
                )                   
            )
        );
      
        $tabviews = apply_filters( 'twitchpress_admin_subscribersviews', $tabviews );

        return $tabviews;
    }

    /**
     * Get a specific table view from 'mainviews' subfolder.
     */
    public static function get_subscriberstabview( $name_presan ) {     
        $name  = sanitize_title( str_replace( '_', '-', $name_presan ) );
        $class = 'TwitchPress_DataView_' . str_replace( '-', '_', $name );
       
        require_once( apply_filters( 'twitchpress_admin_subscribersviews_path', 'class.twitchpress-' . $name . '.php', $name, $class ) );

        if ( ! class_exists( $class ) )
            return;

        $maintabs = new $class();      
        $maintabs->output_result();
    }
}

endif;
