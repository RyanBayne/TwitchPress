<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'TwitchPress_Sync_Tools' ) ) :

/**
 * Tools are integrated with the core plugin
 * and displayed in the core plugins own Tools table.
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress
 * @version     1.0.0
 */
class TwitchPress_Sync_Tools extends TwitchPress_Sync {
    /**
    * Change to true and iterate through all methods for info.
    * 
    * @var mixed
    */
    public $return_tool_info = false;

    /**
    * Tool for syncing all users.
    * 
    * @param mixed $return_tool_info
    */
    public function tool_sync_all_users( $return_tool_info = true ) {
        /**
        * Description of values.
        * 
        * title       - give the tool a name.
        * description - describe what the tool does.
        * version     - tools must be versioned to give users warning
        * author      - we have to know who to come to for help with a tool
        * url         - link to a tutorial or other documentation
        * category    - a way to group tools
        * capability  - apply security using a core or custom capability
        * option      - add option name if configuration required to use tool
        */
        $tool_info = array(
            'title'       => __( 'Sync All Users', 'multitool' ),
            'description' => __( 'Import all WP users Twitch user data if not already done recently.', 'multitool' ),
            'version'     => '1.1',
            'author'      => 'Ryan Bayne',
            'plugin'      => 'Sync Extension',      
            'url'         => '',
            'category'    => 'users',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__
        );
        
        if( $return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }   
      
        // WP_User_Query - get all users who have a Twitch auth setup. 
        $args = array(    
            'meta_query' => array(
                array(
                    'key'     => 'twitchpress_code',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key'     => 'twitchpress_token',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key'     => 'twitchpress_sync_time',
                    'value'   => time(),
                    'compare' => '<'
                ),                
            )
        );

        // Create the WP_User_Query object
        $wp_user_query = new WP_User_Query( $args ); 
        $twitchers = $wp_user_query->get_results();
        if ( ! empty( $twitchers ) ) {

            foreach ( $twitchers as $next_user ) {
                $this->sync_user( $next_user->ID );
            }

        }    
        
        $notices = new TwitchPress_Admin_Notices();
        $notices->success( __( 'User Sync Finished', 'twitchpress-sync' ), __( 'Your request to import data from Twitch and update your WordPress users has been complete. Due to the technical level of this action it is not easy to generate a summary. Please see log entries for specifics.', 'twitchpress-sync' ) );   
    }

}

endif;
