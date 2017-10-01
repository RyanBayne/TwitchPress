<?php
/**
 * TwitchPress Quick Tools Class
 * 
 * Contains methods for each quick tool. 
 * 
 * Remember that the quicktools table might be displaying cached data.
 * 
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Admin
 * @version     1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'TwitchPress_Tools' ) ) :

/**
 * TwitchPress_Tools.
 * 
 * When making changes please remember that the quicktools 
 * table might be displaying cached data.
 * 
 * Append tool methods with "tool_". 
 */
class TwitchPress_Tools {
    /**
    * Change to true and iterate through all methods for info.
    * 
    * @var mixed
    */
    public $return_tool_info = false;
                  
    /**
    * Mainly for hooks. 
    */
    public static function init() {                
        add_action( 'admin_init', array( __CLASS__, 'admin_request_listener' )  );     
    }
    
    /**
    * Just a template tool. Replace "templatetool_" in method name
    * with "tool_".
    * 
    * @version 1.0 
    */
    public function templatetool_go_to_latest_publication() {
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
            'title'       => __( 'Tool Title', 'multitool' ),
            'description' => __( 'This is the tool description.', 'multitool' ),
            'version'     => '1.1',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'users',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__,
            'plugin'      => 'TwitchPress',
        );
        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }
        
        /*
            Your tools unique code goes here. Make it do something!
        */
    }
    
    /**
    * Listens for tools being used on the Quick Tools table view.
    * 
    * Hooked by "init" in the init() method.
    * 
    * If a tool needs to send the user elsewhere, handle it by forwarding
    * them using a method in this class. Ensuring a standard approach to
    * every tools security checks and validation.
    *
    * @version 1.1
    */
    public static function admin_request_listener() {    
        if( !isset( $_REQUEST['_wpnonce'] ) ) {
            return;
        }     
        
        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'tool_action' ) ) {
            return;
        } 
        
        if( !isset( $_GET['toolname'] ) ) {  
            return;
        }
        $tool_name = twitchpress_clean( $_GET['toolname'] );
             
        if( !method_exists( __CLASS__, $tool_name ) ) {       
            return;
        }    
        
        // Ensure the request is attempting to use an actual tool!
        if( substr( $tool_name, 0, 5 ) !== "tool_" ) { 
            return; 
        }
                
        $QuickTools = new TwitchPress_Tools();                 
        $QuickTools->return_tool_info = true;
        
        // Prepare an array for passing to the tool method.
        $tool_parameters_array = array();
        
        // Get the requested tools information for performing validation.
        eval( '$tool_info = $QuickTools->$tool_name( $tool_parameters_array );');
        
        if( !isset( $tool_info['capability'] ) ) {
            return;
        }
        
        if( !current_user_can( $tool_info['capability'] ) ) {
            return;
        }
        
        // Is this a tool with multiple possible actions? 
        if( isset( $tool_info['actions'] ) && is_array( $tool_info['actions'] ) ) {
            $action = twitchpress_clean( $_GET['action'] );
            if( !isset( $tool_info['actions'][ $action ] ) ) {
                return false;
            }   
            
            // Pass the specific action to the tools method.
            $tool_parameters_array['action'] = $action;
        }
        
        $QuickTools->return_tool_info = false;
        $QuickTools->$tool_name( $tool_parameters_array );
    }
    
    public function get_categories() {
        return $tool_categories = array( 'posts', 'users', 'comments', 'plugins', 'security', 'seo', 'social', 'integration' );    
    }
    
    /**
    * Display a list of the latest subscribers. A maximum of 100.
    * 
    * @version 1.1
    */
    public function tool_display_latest_subscribers() {
        $tool_info = array(
            'title'       => __( 'Display Latest Subscribers', 'twitchpress' ),
            'description' => __( 'Displays usernames and email addresses for the latest registered users.', 'twitchpress' ),
            'version'     => '1.1',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'users',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__
        );
        
        if( $this->return_tool_info ){ return $tool_info; }    
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }
        
        $blogusers = get_users( array( 'fields' => array( 'ID', 'display_name', 'user_email' ) ) );
        foreach ( $blogusers as $user ) {
            wp_die( '<p>ID: ' . esc_html( $user->ID ) . ' - Display Name: ' . esc_html( $user->display_name ) . ' - Email: ' . esc_html( $user->user_email ) . '</p>' );
        }
    }   
    
    /**
    * Sends the user to the latest wp_post (post,page,custom post types).
    * 
    * @version 1.1 
    */
    public function tool_go_to_latest_publication() {
        $tool_info = array(
            'title'       => 'View Latest Publication',
            'description' => __( 'Display information about the latest authored post including pages and custom post-types.', 'multitool' ),
            'version'     => '1.1',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'users',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__
        );
        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }

        $args = array(
            'numberposts' => 1,
            'orderby' => 'post_date',
            'order' => 'DESC',
            'post_type' => array( 'post', 'page' ),
            'post_status' => 'draft, publish, future, pending, private',
            'suppress_filters' => true
        );

        $recent_posts = wp_get_recent_posts( $args, ARRAY_A ); 
        
        echo '<pre>';
        var_dump( $recent_posts ); 
        echo '</pre>';       
    } 
    
    /**
    * Enable/Disabled error display.
    * 
    * @version 1.0 
    */
    public function tool_plugin_displayerrors( $tool_parameters_array ) {

        $tool_info = array(
            'title'       => __( 'Display Errors', 'twitchpress' ),
            'description' => __( 'A tool for developers that will display errors.', 'twitchpress' ),
            'version'     => '1.0',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'developers',
            'capability'  => 'activate_plugins',
            'option'      => 'displayerrors_activate',
            'actions'     => array( 
                'displayerrors'    => array( 'title' => __( 'Display Errors', 'twitchpress' ) ),
                'hideerrors' => array( 'title' => __( 'Hide Errors', 'twitchpress' ) ),
            ),
            'function'    => __FUNCTION__
        );
              
        if( $this->return_tool_info ){ return $tool_info; }     
              
        if( !current_user_can( $tool_info['capability'] ) ) { return; }
             
        if( !isset( $tool_parameters_array['action'] ) ) { return; }
                                        
        if( $tool_parameters_array['action'] == 'displayerrors' ) {     
            
            update_option( 'twitchpress_displayerrors', 'yes', true );
            TwitchPress_Admin_Notices::add_custom_notice( 'displayerrorsyes', 'Error display has been activated by the TwitchPress Display Errors tool. You can reverse this by going to the plugins menu, select Quick Tools, search for "Display Errors" and click on the Hide Errors button.', 'twitchpress' );
            twitchpress_redirect_tracking( admin_url( 'admin.php?page=twitchpress_tools' ), __LINE__, __FUNCTION__ );
            exit;
            
        } elseif( $tool_parameters_array['action'] == 'hideerrors' ) {                 
            
            delete_option( 'twitchpress_displayerrors' );
            TwitchPress_Admin_Notices::add_custom_notice( 'displayerrorsno', 'Error display has been disabled by the TwitchPress Display Errors tool.', 'twitchpress' );            
            twitchpress_redirect_tracking( admin_url( 'admin.php?page=twitchpress_tools' ), __LINE__, __FUNCTION__ );          
            exit;
            
        }
    } 
    
    /**
    * Delete all trace data created by BugNet
    * 
    * @version 1.0
    */
    public function tool_delete_all_trace_transients() {
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
            'title'       => __( 'Delete Cached Trace Data', 'multitool' ),
            'description' => __( 'Deletes all trace data generated by BugNet and stored in WordPress transient caches.', 'multitool' ),
            'version'     => '1.1',
            'author'      => 'Ryan Bayne',
            'plugin'      => 'TwitchPress',
            'url'         => '',
            'category'    => 'bugnet',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__
        );
        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }
        
        // Get list of individual traces. 
        global $bugnet;
        $transient_list = $bugnet->handler_tracing->delete_all_trace_cache_data();
        
        TwitchPress_Admin_Notices::add_custom_notice( 'displayerrorsno', __( 'All BugNet Tracing data has been deleted from caches.', 'twitchpress' ), 'twitchpress' );            
    }                                                
          
}

endif;

$QuickTools = new TwitchPress_Tools();
$QuickTools->init();
unset($QuickTools);