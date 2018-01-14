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
               
if ( ! class_exists( 'TwitchPress_Admin_Data_Views' ) ) :
            
class TwitchPress_Admin_Data_Views {

    /**
     * Handles output of the main tables page in admin.
     */
    public static function output() {       
        $tabs              = self::get_tabs();
        $first_tab         = array_keys( $tabs );
        $current_tab       = ! empty( $_GET['tab'] ) ? sanitize_title( $_GET['tab'] ) : $first_tab[0];
        $current_tablelist = isset( $_GET['twitchpressview'] ) ? sanitize_title( $_GET['twitchpressview'] ) : current( array_keys( $tabs[ $current_tab ]['datatabviews'] ) );

        require_once( 'views/html-admin-data.php' );
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
        
        // BugNet Daily Log
        $tabviews['bugnet_dailylog_list_tables'] = array(
            'title'  => __( 'BugNet Daily Log', 'twitchpress' ),
            'datatabviews' => array(
                "all_dailylogs" => array(
                    'title'       => __( 'All Entries', 'twitchpress' ),
                    'description' => '',
                    'hide_title'  => true,
                    'callback'    => array( __CLASS__, 'get_maintabview' )
                )                   
            )
        );
             
        // BugNet Cached Traces
        $tabviews['bugnetcache_list_tables'] = array(
            'title'  => __( 'BugNet Cached Traces', 'twitchpress' ),
            'datatabviews' => array(
                "allcachetraces_bugnet" => array(
                    'title'       => __( 'All Traces', 'twitchpress' ),
                    'description' => '',
                    'hide_title'  => true,
                    'callback'    => array( __CLASS__, 'get_maintabview' )
                ),                    
                "todayscachetraces_bugnet" => array(
                    'title'       => __( 'Todays Traces', 'twitchpress' ),
                    'description' => '',
                    'hide_title'  => true,
                    'callback'    => array( __CLASS__, 'get_maintabview' )
                ),
                "lasthourcachetraces_bugnet" => array(
                    'title'       => __( 'Last Hour', 'twitchpress' ),
                    'description' => '',
                    'hide_title'  => true,
                    'callback'    => array( __CLASS__, 'get_maintabview' )
                ),
                "last10cachetraces_bugnet" => array(
                    'title'       => __( 'Last 10', 'twitchpress' ),
                    'description' => '',
                    'hide_title'  => true,
                    'callback'    => array( __CLASS__, 'get_maintabview' )
                ),
            )
        );        
        
        // API Requests (general request details)
        $tabviews['kraken5requests_list_tables'] = array(
            'title'  => __( 'API Requests', 'twitchpress' ),
            'datatabviews' => array(
                "all_kraken5requests" => array(
                    'title'       => __( 'All Requests', 'twitchpress' ),
                    'description' => '',
                    'hide_title'  => true,
                    'callback'    => array( __CLASS__, 'get_maintabview' )
                )/*, Use this to add a view category for quick filtering                   
                "get_kraken5requests" => array(
                    'title'       => __( 'Twitch Requests', 'twitchpress' ),
                    'description' => '',
                    'hide_title'  => true,
                    'callback'    => array( __CLASS__, 'get_maintabview' )
                ),*/
            )
        );       
         
        // API Results (Raw curl response)
        $tabviews['apiresponses_list_tables'] = array(
            'title'  => __( 'API Responses', 'twitchpress' ),
            'datatabviews' => array(
                "all_apiresponses" => array(
                    'title'       => __( 'All Responses', 'twitchpress' ),
                    'description' => '',
                    'hide_title'  => true,
                    'callback'    => array( __CLASS__, 'get_maintabview' )
                )
            )
        );      
           
        // API Errors 
        $tabviews['apierrors_list_tables'] = array(
            'title'  => __( 'API Errors', 'twitchpress' ),
            'datatabviews' => array(
                "all_apierrors" => array(
                    'title'       => __( 'All Errors', 'twitchpress' ),
                    'description' => '',
                    'hide_title'  => true,
                    'callback'    => array( __CLASS__, 'get_maintabview' )
                )
            )
        );
      
        $tabviews = apply_filters( 'twitchpress_admin_mainviews', $tabviews );

        return $tabviews;
    }

    /**
     * Get a specific table view from 'mainviews' subfolder.
     */
    public static function get_maintabview( $name_presan ) {     
        $name  = sanitize_title( str_replace( '_', '-', $name_presan ) );
        $class = 'TwitchPress_DataView_' . str_replace( '-', '_', $name );
       
        require_once( apply_filters( 'twitchpress_admin_dataviews_path', 'views/dataviews/class.twitchpress-' . $name . '.php', $name, $class ) );

        if ( ! class_exists( $class ) )
            return;

        $maintabs = new $class();      
        $maintabs->output_result();
    }
}

endif;
