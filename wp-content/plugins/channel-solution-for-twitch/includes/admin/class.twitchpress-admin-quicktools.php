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
 * @version     1.0.2
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
    
    public function url( $tool_name ) {
        $nonce = wp_create_nonce( 'tool_action' );        
        return admin_url( 'admin.php?page=twitchpress_tools&_wpnonce=' . $nonce . '&toolname=' . $tool_name );    
    }
    
    public function text_link_tools_view( $tool_name, $tool_title, $href ) {
        return '<a href="' . $this->url( $tool_name ) . '">' . $tool_title . '</a></p>';    
    }
        
    public function button_link_tools_view( $tool_name, $tool_title ) {
        return '<a href="' . $this->url( $tool_name ) . '" class="button button-primary">' . $tool_title . '</a></p>';    
    }
    
    /**
    * A template tool. Replace "templatetool_" in method name with "tool_".
    * 
    * @version 1.0 
    */
    public function templatetool_rename_this_function() {
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
    * Called by a button in the Help tab under Installation. 
    * 
    * This tool is to be run by the owner of the site and the main channel. 
    * The oAuth procedure will be complete and a user token generated.
    * The token and refresh token is stored as the main channel token for features.
    * 
    * The WP users ID is also stored to indicate a relationship between WP user and owner. 
    * 
    * @version 2.0
    */
    public function tool_authorize_main_channel() {
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
            'title'       => __( 'Authorize Main Channel', 'multitool' ),
            'description' => __( 'Only the site owner and owner of the main Twitch channel should use this tool. This tool will add permissions for more features to run i.e. getting the main channels subscribers.', 'multitool' ),
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
        
        // Create a Twitch API oAuth2 URL
        $post_credentials_kraken = new TWITCHPRESS_Kraken_API();
                                             
        $state = array( 'redirectto' => admin_url( 'admin.php?page=twitchpress' ),
                        'userrole'   => 'administrator',
                        'outputtype' => 'admin',
                        'reason'     => 'mainchannelsetup',
                        'function'   => __FUNCTION__
        );
        
        // Generate the oAuth URL that we will forward the user to. 
        $all_scopes = $post_credentials_kraken->twitch_scopes;
        $oAuth2_URL = $post_credentials_kraken->generate_authorization_url( $all_scopes, $state );

        wp_redirect( $oAuth2_URL );
        exit;
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

    /**
    * Tests the Twitch channel subscription update procedure
    * and generates extra output for debugging.
    * 
    * @version 1.0 
    */
    public function tool_debug_sub_update() {
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
            'title'       => __( 'Debug Sub Update', 'multitool' ),
            'description' => __( 'Gets the current users subscription details from the main channel and generates output for debugging problems.', 'multitool' ),
            'version'     => '1.0',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'developers',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__,
            'plugin'      => 'TwitchPress',
        );
        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }
        
        /*
            Run indepth subscription update attempt with extra output. 
        */

        // Setup Logging and Tracing
        global $bugnet;
        
        // Create Class Objects
        $kraken = new TWITCHPRESS_Kraken_Calls();

        // Build the output for the entire procedure. 
        $output = __( 'Debugging Subscription Update Procedure', 'twitchpress' );

        // This test only uses the current user. 
        $user_id = get_current_user_id();
        $user_info = get_userdata( $user_id );
        $output .= sprintf( __( '<p><strong>User ID: %s</strong></p>', 'twitchpress' ), $user_id );
        
        // Get the main channel Twitch ID.
        $channel_id = $kraken->get_main_channel_id();
        $output .= sprintf( __( '<p><strong>Main Channel ID: %s</strong></p>', 'twitchpress' ), $channel_id );
        
        // Get main account token.     
        $channel_token = $kraken->get_main_client_token();
        $output .= sprintf( __( '<p><strong>Main Token: %s</strong></p>', 'twitchpress' ), $channel_token );

        // Get main account code. 
        $channel_code = $kraken->get_main_client_code();
        $output .= sprintf( __( '<p><strong>Main Code: %s</strong></p>', 'twitchpress' ), $channel_code );

        // Setup a call name for tracing. 
        $kraken->twitch_call_name = __( 'Debug Update Subscription', 'twitchpress' );
        
        // Get current users Twitch ID.
        $users_twitch_id = get_user_meta( $user_id, 'twitchpress_twitch_id', true );
        $output .= sprintf( __( '<p><strong>Users Twitch ID: %s</strong></p>', 'twitchpress' ), $users_twitch_id );
        
        // Get possible existing sub plan from a earlier sub sync.
        $local_sub_plan = get_user_meta( $user_id, 'twitchpress_sub_plan_' . $kraken->get_main_channel_id(), true  );
        $output .= sprintf( __( '<p><strong>Existing Sub Plan Value: %s</strong></p>', 'twitchpress' ), $local_sub_plan );

        // Check channel subscription from channel side (does not require scope permission).
        $output .= sprintf( __( '<p><strong>Calling Twitch.tv for Subscription Details</strong></p>', 'twitchpress' ), $local_sub_plan );        
        $twitch_sub_response = $kraken->getChannelSubscription( $users_twitch_id, $channel_id, $channel_token, $channel_code );
                
        // If Twitch user is a subscriber to channel do_action() early here, maybe a simple thank you notice. 
        if( isset( $twitch_sub_response['error'] ) || $twitch_sub_response === null ) 
        {   

            // Prepare error code/status to improve log entry.
            $status = '';
            if( isset( $twitch_sub_response['status']) )
            {
                $status = $twitch_sub_response['status']; 
                $output .= sprintf( __( '<p><strong>Error Returned: %s</strong></p>', 'twitchpress' ), $status );                
            }             
            else
            {
                $status = 'Null';
                $output .= sprintf( __( '<p><strong>Null Value Returned by Twitch.tv</strong></p>', 'twitchpress' ), '' );        
            }
                         
            if( twitchpress_is_valid_sub_plan( $local_sub_plan ) ) 
            {   
                // Delete users subscription for the channel we are checking.    
                delete_user_meta( $user_id, 'twitchpress_sub_plan_' . $channel_id );  
                delete_user_meta( $user_id, 'twitchpress_sub_plan_name_' . $channel_id );  
                
                $output .= sprintf( __( '<p><strong>Removed Expired Sub Data</strong></p>', 'twitchpress' ), '' );

                TwitchPress_Admin_Notices::add_custom_notice( 'displayerrorsno', $output );            
                return;
            }
            else
            {
                // Invalid sub plan found in users data. 
                $output .= sprintf( __( '<p><strong>Sub Plan Invalid: %s</strong></p>', 'twitchpress' ), $local_sub_plan );        
            }       

            TwitchPress_Admin_Notices::add_custom_notice( 'displayerrorsno', $output );            
            return;
        }
        elseif( isset( $twitch_sub_response['sub_plan'] ) )
        {
            // Sub plan value returned. 
            $output .= sprintf( __( '<p><strong>Sub Plan Value Returned: </strong></p>', 'twitchpress' ), $twitch_sub_response['sub_plan'] );
            
            // Updated Sub Status
            update_user_meta( $user_id, 'twitchpress_substatus_mainchannel', true );
            $output .= sprintf( __( '<p><strong>Updated User Meta Sub Status to TRUE</strong></p>', 'twitchpress' ), '' );
                     
            if( !twitchpress_is_valid_sub_plan( $local_sub_plan ) ) 
            {      
                // User is being registered as a Twitch sub for the first time.
                update_user_meta( $user_id, 'twitchpress_sub_plan_' . $channel_id, $twitch_sub_response['sub_plan'] );
                update_user_meta( $user_id, 'twitchpress_sub_plan_name_' . $channel_id, $twitch_sub_response['sub_plan_name'] );
                
                $output .= sprintf( __( '<p><strong>New Twitch Subscriber Confirmed</strong></p>', 'twitchpress' ), '' );
                
                TwitchPress_Admin_Notices::add_custom_notice( 'displayerrorsno', $output );            
                return; 
            } 
            elseif( twitchpress_is_valid_sub_plan( $local_sub_plan ) ) 
            {  
                // User is not a newely detected subscriber and has sub history stored in WP, check for sub plan change. 
     
                if( $twitch_sub_response['sub_plan'] !== $local_sub_plan )
                { 
                    // User has changed their subscription plan and are still subscribing.
                    update_user_meta( $user_id, 'twitchpress_sub_plan_' . $channel_id, $twitch_sub_response['sub_plan'] );                        
                    update_user_meta( $user_id, 'twitchpress_sub_plan_name_' . $channel_id, $twitch_sub_response['sub_plan'] );                        
                    
                    $output .= sprintf( __( '<p><strong>Sub Plan Change Confirmed</strong></p>', 'twitchpress' ), '' );                }
                else
                {          
                    // User is subscribing to the same plan since last sync. 
                    $output .= sprintf( __( '<p><strong>Activate Subscription Unchanged</strong></p>', 'twitchpress' ), '' );
                }

                TwitchPress_Admin_Notices::add_custom_notice( 'displayerrorsno', $output );            
                return;
            } 
        }      

        $output .= sprintf( __( '<p><strong>Bad State</strong></p>', 'twitchpress' ), '' );
        
        TwitchPress_Admin_Notices::add_custom_notice( 'displayerrorsno', $output );            
        return; 
    }   
    
    public function tool_update_zyphers_sub() {

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
            'title'       => __( 'Update ZypheRs Sub', 'multitool' ),
            'description' => __( 'Update Zyphers sub using Twitch ID 120841817 for channel ID 20519306.', 'multitool' ),
            'version'     => '1.0',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'developers',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__,
            'plugin'      => 'TwitchPress',
        );
        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }
        
        /*
            Run indepth subscription update attempt with extra output. 
        */

        // Setup Logging and Tracing
        global $bugnet;
        
        // Create Class Objects
        $kraken = new TWITCHPRESS_Kraken_Calls();

        // Build the output for the entire procedure. 
        $output = __( 'Debugging Subscription Update Procedure', 'twitchpress' );

        // This test only uses the current user. 
        $user_id = get_current_user_id();
        $user_info = get_userdata( $user_id );
        $output .= sprintf( __( '<p><strong>User ID: %s</strong></p>', 'twitchpress' ), $user_id );
        
        // Get the main channel Twitch ID.
        $channel_id = $kraken->get_main_channel_id();
        $output .= sprintf( __( '<p><strong>Main Channel ID: %s</strong></p>', 'twitchpress' ), $channel_id );
        
        // Get main account token.     
        $channel_token = $kraken->get_main_client_token();
        $output .= sprintf( __( '<p><strong>Main Token: %s</strong></p>', 'twitchpress' ), $channel_token );

        // Get main account code. 
        $channel_code = $kraken->get_main_client_code();
        $output .= sprintf( __( '<p><strong>Main Code: %s</strong></p>', 'twitchpress' ), $channel_code );

        // Setup a call name for tracing. 
        $kraken->twitch_call_name = __( 'Debug Update Subscription', 'twitchpress' );
        
        // Get current users Twitch ID.
        $users_twitch_id = get_user_meta( $user_id, 'twitchpress_twitch_id', true );
        $output .= sprintf( __( '<p><strong>Users Twitch ID: %s</strong></p>', 'twitchpress' ), $users_twitch_id );
        
        // Get possible existing sub plan from a earlier sub sync.
        $local_sub_plan = get_user_meta( $user_id, 'twitchpress_sub_plan_' . $kraken->get_main_channel_id(), true  );
        $output .= sprintf( __( '<p><strong>Existing Sub Plan Value: %s</strong></p>', 'twitchpress' ), $local_sub_plan );

        // Check channel subscription from channel side (does not require scope permission).
        $output .= sprintf( __( '<p><strong>Calling Twitch.tv for Subscription Details</strong></p>', 'twitchpress' ), $local_sub_plan );        
        $twitch_sub_response = $kraken->getChannelSubscription( '120841817', '20519306', $channel_token, $channel_code );
                
        // If Twitch user is a subscriber to channel do_action() early here, maybe a simple thank you notice. 
        if( isset( $twitch_sub_response['error'] ) || $twitch_sub_response === null ) 
        {   

            // Prepare error code/status to improve log entry.
            $status = '';
            if( isset( $twitch_sub_response['status']) )
            {
                $status = $twitch_sub_response['status']; 
                $output .= sprintf( __( '<p><strong>Error Returned: %s</strong></p>', 'twitchpress' ), $status );                
            }             
            else
            {
                $status = 'Null';
                $output .= sprintf( __( '<p><strong>Null Value Returned by Twitch.tv</strong></p>', 'twitchpress' ), '' );        
            }
                         
            if( twitchpress_is_valid_sub_plan( $local_sub_plan ) ) 
            {   
                // Delete users subscription for the channel we are checking.    
                delete_user_meta( $user_id, 'twitchpress_sub_plan_' . $channel_id );  
                delete_user_meta( $user_id, 'twitchpress_sub_plan_name_' . $channel_id );  
                
                $output .= sprintf( __( '<p><strong>Removed Expired Sub Data</strong></p>', 'twitchpress' ), '' );

                TwitchPress_Admin_Notices::add_custom_notice( 'displayerrorsno', $output );            
                return;
            }
            else
            {
                // Invalid sub plan found in users data. 
                $output .= sprintf( __( '<p><strong>Sub Plan Invalid: %s</strong></p>', 'twitchpress' ), $local_sub_plan );        
            }       

            TwitchPress_Admin_Notices::add_custom_notice( 'displayerrorsno', $output );            
            return;
        }
        elseif( isset( $twitch_sub_response['sub_plan'] ) )
        {
            // Sub plan value returned. 
            $output .= sprintf( __( '<p><strong>Sub Plan Value Returned: </strong></p>', 'twitchpress' ), $twitch_sub_response['sub_plan'] );
            
            // Updated Sub Status
            update_user_meta( $user_id, 'twitchpress_substatus_mainchannel', true );
            $output .= sprintf( __( '<p><strong>Updated User Meta Sub Status to TRUE</strong></p>', 'twitchpress' ), '' );
                     
            if( !twitchpress_is_valid_sub_plan( $local_sub_plan ) ) 
            {      
                // User is being registered as a Twitch sub for the first time.
                update_user_meta( $user_id, 'twitchpress_sub_plan_' . $channel_id, $twitch_sub_response['sub_plan'] );
                update_user_meta( $user_id, 'twitchpress_sub_plan_name_' . $channel_id, $twitch_sub_response['sub_plan_name'] );
                
                $output .= sprintf( __( '<p><strong>New Twitch Subscriber Confirmed</strong></p>', 'twitchpress' ), '' );
                
                TwitchPress_Admin_Notices::add_custom_notice( 'displayerrorsno', $output );            
                return; 
            } 
            elseif( twitchpress_is_valid_sub_plan( $local_sub_plan ) ) 
            {  
                // User is not a newely detected subscriber and has sub history stored in WP, check for sub plan change. 
     
                if( $twitch_sub_response['sub_plan'] !== $local_sub_plan )
                { 
                    // User has changed their subscription plan and are still subscribing.
                    update_user_meta( $user_id, 'twitchpress_sub_plan_' . $channel_id, $twitch_sub_response['sub_plan'] );                        
                    update_user_meta( $user_id, 'twitchpress_sub_plan_name_' . $channel_id, $twitch_sub_response['sub_plan'] );                        
                    
                    $output .= sprintf( __( '<p><strong>Sub Plan Change Confirmed</strong></p>', 'twitchpress' ), '' );                }
                else
                {          
                    // User is subscribing to the same plan since last sync. 
                    $output .= sprintf( __( '<p><strong>Activate Subscription Unchanged</strong></p>', 'twitchpress' ), '' );
                }

                TwitchPress_Admin_Notices::add_custom_notice( 'displayerrorsno', $output );            
                return;
            } 
        }      

        $output .= sprintf( __( '<p><strong>Bad State</strong></p>', 'twitchpress' ), '' );
        
        TwitchPress_Admin_Notices::add_custom_notice( 'displayerrorsno', $output );            
        return;    
    }      

    /**
    * Button on user profile allows WP user to request subscription data
    * sync manually and get visual confirmation of the result.
    * 
    * @version 1.0 
    */
    public function tool_user_sync_twitch_sub_data() {
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
            'title'       => __( 'Update Twitch Subscription', 'multitool' ),
            'description' => __( 'Requests a single users subscription data from Twitch.tv and stores it in WordPress.', 'multitool' ),
            'version'     => '1.1',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'users',
            'capability'  => 'read',
            'option'      => null,
            'function'    => __FUNCTION__,
            'plugin'      => 'TwitchPress',
        );
        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }
        
        /*
            Your tools unique code goes here. Make it do something!
        */
        $sync_object = new TwitchPress_Systematic_Syncing();
        $sync_object->sync_user( get_current_user_id(), false, true, 'user' );
        
        do_action( 'twitchpress_manualsubsync' );
    }
    
    /**
    * Tool for syncing all users.
    * 
    * Originally in the Sync Extension before it was merged.
    * 
    * @param mixed $return_tool_info
    * 
    * @version 1.0
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
                $this->sync_user( $next_user->ID, true, false );
            }

        }    
        
        $notices = new TwitchPress_Admin_Notices();
        $notices->success( __( 'User Sync Finished', 'twitchpress-sync' ), __( 'Your request to import data from Twitch and update your WordPress users has been complete. Due to the technical level of this action it is not easy to generate a summary. Please see log entries for specifics.', 'twitchpress-sync' ) );   
    }    
}

endif;

$QuickTools = new TwitchPress_Tools();
$QuickTools->init();
unset($QuickTools);