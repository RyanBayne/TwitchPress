<?php 
/**
 * TwitchPress Ultimate Member integration class.
 * 
 * @author   Ryan Bayne
 * @version 1.0
 */
               
// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' );

if ( ! class_exists( 'TwitchPress_Ultimate_Member' ) ) :

class TwitchPress_Ultimate_Member {

    public function init() {
        // Load files and register actions required before TwitchPress core inits.
        add_action( 'before_twitchpress_init', array( $this, 'pre_twitchpress_init' ) );      
    }

    /**
    * Do something before TwitchPress core initializes. 
    * 
    * @version 1.0
    */
    public function pre_twitchpress_init() {
        $this->load_global_dependencies();
                             
        add_action( 'twitchpress_init', array( $this, 'after_twitchpress_init' ) );
    }

    /**
    * Do something after TwitchPress core initializes.
    * 
    * @version 1.0
    */
    public function after_twitchpress_init() {     
        $this->attach_hooks();                   
    }

    /**
     * Load all plugin dependencies.
     * 
     * @version 1.0
     */
    public function load_global_dependencies() {
        require_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/shortcodes/shortcode-ultimate-member-updater.php' );
    }
               
    /**
     * Hooks
     * 
     * @version 2.5
     */
    private function attach_hooks() {   
        // Add sections to users tab. 
        add_filter( 'twitchpress_get_sections_users', array( $this, 'settings_add_section_users' ), 9 );
        
        // Add options to users section.
        add_filter( 'twitchpress_get_settings_users', array( $this, 'settings_add_options_users' ), 9 );
        
        // Add links to the plugins
        add_filter( 'twitchpress_update_system_scopes_status', array( $this, 'update_system_scopes_status' ), 1, 1 );  
        add_filter( 'twitchpress_filter_public_notices_array', array( $this, 'public_notices_array' ), 1, 1 );
           
        // Decide a users role when the sub data has been updated. 
        add_action( 'twitchpress_user_sub_sync_finished', array( $this, 'set_twitch_subscribers_um_role' ), 2, 1 );// Passes user ID.
                                
        // Hook into subscription sync functions and apply UM role...
        add_action( 'twitchpress_sync_new_twitch_subscriber', array( $this, 'set_twitch_subscribers_um_role' ), 5, 1 );// Passes user ID.
        add_action( 'twitchpress_sync_continuing_twitch_subscriber', array( $this, 'set_twitch_subscribers_um_role' ), 5, 1 );// Passes user ID.
        add_action( 'twitchpress_sync_discontinued_twitch_subscriber', array( $this, 'set_twitch_subscribers_um_role' ), 5, 1 );// Passes user ID.
        add_action( 'twitchpress_manualsubsync', array( $this, 'set_twitch_subscribers_um_role' ), 5, 1 );// Passes user ID.

        // Update the current users Twitch.tv subscription status for main channel...
        add_action( 'admin_init', array( $this, 'set_current_users_um_role_based_on_twitch_sub' ), 10, 1 );

        // Hook into edit profile requests with refresh(redirect) if subscription changes...
        // this runs near the end of the user profile editing screen in the admin menus...
        add_action( 'edit_user_profile', array( $this, 'set_twitch_subscribers_um_role' ), 5, 1 );
         
    }
    
    /**
    * Sync the current logged in user - hooked by wp_loaded
    * 
    * @version 1.0
    */
    public function set_current_users_um_role_based_on_twitch_sub() {    

        if( !is_user_logged_in() ) { return false; }
        
        if( !twitchpress_is_sync_due( __FILE__, __FUNCTION__, __LINE__, 120 ) ) { return; }
                
        // Avoid processing the owner of the main channel (might not be admin with ID 1)
        if( twitchpress_is_current_user_main_channel_owner() ) { return; }
        
        $this->set_twitch_subscribers_um_role( get_current_user_id() );    
    }   
         
    /**
    * This method assumes that the "twitchpress_sub_plan_[channelid]"
    * user meta value has been updated already. See core sync class and
    * subscribers extension.
    * 
    * @param mixed $user_id
    * @param mixed $channel_id
    * @param mixed $api_response
    * 
    * @version 3.0
    */
    public function set_twitch_subscribers_um_role( $wp_user_id ) {
        if( !twitchpress_is_sync_due( __FILE__, __FUNCTION__, __LINE__, 60 ) ) { return; }
        
        // Get the current filter to help us trace backwards from log entries. 
        $filter = current_filter();

        // edit_user_profile filter passed array
        if( 'edit_user_profile' == $filter ) 
        {
            // This hook actually passes a user object. 
            $wp_user_id = $wp_user_id->data->ID;                
        }
        
        // Avoid processing the main account or administrators so they are never downgraded. 
        $user_info = get_userdata( $wp_user_id );
        if( $wp_user_id === 1 || user_can( $wp_user_id, 'administrator' ) ) { return; }
        
        $next_role = null;

        // Establish which of the users roles are Twitch subscription related ones (paired with a sub plan through this extension). 
        $paired_roles_array = $this->get_subscription_plan_roles();
        
        $users_sub_paired_roles = array();// This should only hold one role, but we will plan for faults. 
          
        foreach( $paired_roles_array as $key => $sub_paired_role )
        {
            if( in_array( $sub_paired_role, $user_info->roles ) ) 
            {
                $users_sub_paired_roles[] = $sub_paired_role;    
            }    
        } 

        // Work with the main channel by default (is also Twitch user ID)
        $channel_id = twitchpress_get_main_channels_twitchid();
        
        // Get subscription plan from user meta for the giving channel (based on channel ID). 
        $sub_plan = get_user_meta( $wp_user_id, 'twitchpress_sub_plan_' . $channel_id, true );

        if( !$sub_plan ) 
        { 
            // User has no Twitch subscription, so apply default (none) role. 
            $next_role = get_option( 'twitchpress_um_subtorole_none', false );
        }    
        else
        {
            $option_string = 'twitchpress_um_subtorole_' . $sub_plan;
            
            // Get the UM role paired with the $sub_plan
            $next_role = get_option( $option_string, false );

            if( !$next_role )         
            {   
                // Apply default role - UM settings not setup or a mismatch in role names.
                $next_role = get_option( 'twitchpress_um_subtorole_none', false );
            }            
        }

        // Give the sub-plan paired WP role to the user. 
        $user = new WP_User( $wp_user_id );
        
        // Cleanup users existing subscription paired roles (should only be one but just in case we plan for more)
        foreach( $users_sub_paired_roles as $key => $role_name )
        {
            $user->remove_role( $role_name );    
        }

        // Add role
        $user->add_role( $next_role );

        // Log any change in history. 
        if( $current_role !== $next_role ) {
            $history_obj = new TwitchPress_History();
            $history_obj->new_entry( $next_role, $current_role, 'auto', __( '', 'twitchpress' ), $wp_user_id );    
        }           
    }
    
    /**
    * Add a new section to the User settings tab.
    * 
    * @param mixed $sections
    * 
    * @version 1.0
    */
    public function settings_add_section_users( $sections ) {  
        global $only_section;
                                   
        // We use this to apply this extensions settings as the default view...
        // i.e. when the tab is clicked and there is no "section" in URL. 
        if( empty( $sections ) ){ 
            $only_section = true;
        } else { 
            $only_section = false; 
        }
                    
        // Add sections to the User Settings tab. 
        $new_sections = array(
            'ultimatemember'  => __( 'UM Roles', 'twitchpress' ),
        );

        return array_merge( $sections, $new_sections );           
    }
    
    /**
    * Add options to this extensions own settings section.
    * 
    * @param mixed $settings
    * 
    * @version 1.0
    */
    public function settings_add_options_users( $settings ) {
        global $current_section, $only_section;
        
        $new_settings = array();
        
        // This first section is default if there are no other sections at all.
        if ( 'ultimatemember' == $current_section || !$current_section && $only_section ) {
            
            // Get Ultimate Member roles.  
            $um_roles = array();
            if( function_exists( 'um_get_roles' ) ) {
                $um_roles = um_get_roles();
            } 
                        
            $new_settings = apply_filters( 'twitchpress_ultimatemember_users_settings', array(
 
                array(
                    'title' => __( 'Subscription to Role Pairing', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'These options have been added by the TwitchPress UM extension. Pair your Twitch subscription plans to Ultimate Member roles.', 'twitchpress' ),
                    'id'     => 'subscriptionrolepairing',
                ),

                array(
                    'title'    => __( 'No Subscription', 'twitchpress' ),
                    'id'       => 'twitchpress_um_subtorole_none',
                    'css'      => 'min-width:300px;',
                    'default'  => 'menu_order',
                    'type'     => 'select',
                    'options'  => apply_filters( 'twitchpress_um_subtorole_none', $um_roles ),
                ),
                
                array(
                    'title'    => __( 'Prime', 'twitchpress' ),
                    'id'       => 'twitchpress_um_subtorole_prime',
                    'css'      => 'min-width:300px;',
                    'default'  => 'menu_order',
                    'type'     => 'select',
                    'options'  => apply_filters( 'twitchpress_um_subtorole_prime', $um_roles ),
                ),                    
                
                array(
                    'title'    => __( '$4.99', 'twitchpress' ),
                    'id'       => 'twitchpress_um_subtorole_1000',
                    'css'      => 'min-width:300px;',
                    'default'  => 'menu_order',
                    'type'     => 'select',
                    'options'  => apply_filters( 'twitchpress_um_subtorole_1000', $um_roles ),
                ),
                  
                array(
                    'title'    => __( '$9.99', 'twitchpress' ),
                    'id'       => 'twitchpress_um_subtorole_2000',
                    'css'      => 'min-width:300px;',
                    'default'  => 'menu_order',
                    'type'     => 'select',
                    'options'  => apply_filters( 'twitchpress_um_subtorole_2000', $um_roles ),
                ),
                  
                array(
                    'title'    => __( '$24.99', 'twitchpress' ),
                    'id'       => 'twitchpress_um_subtorole_3000',
                    'css'      => 'min-width:300px;',
                    'default'  => 'menu_order',
                    'type'     => 'select',
                    'options'  => apply_filters( 'twitchpress_um_subtorole_3000', $um_roles ),
                ),
                        
                array(
                    'type'     => 'sectionend',
                    'id'     => 'membershiprolepairing'
                ),

            ));   
            
        }
        
        return array_merge( $settings, $new_settings );         
    }

    /**
    * List of the public notices available for applicable procedures.
    * 
    * TYPES
    * 0 = success
    * 1 = warning
    * 2 = error
    * 3 = info
    * 
    * @version 1.0
    */
    public function public_notices_array( $notices_pre_filter ) {
        $messages_array = array();
  
        // 0 = success, 1 = warning, 2 = error, 3 = info
        $messages_array['umextension'][0] = array( 'type' => 2, 'title' => __( 'No Subscription Plan', 'twitchpress' ), 'info' => __( 'You do not have a subscription plan for this sites main Twitch.tv channel. Your UM role has been set to the default.', 'twitchpress' ) );
        $messages_array['umextension'][1] = array( 'type' => 3, 'title' => __( 'Hello Administrator', 'twitchpress' ), 'info' => __( 'Your request must be rejected because you are an administrator. We cannot risk reducing your access.', 'twitchpress' ) );
        $messages_array['umextension'][2] = array( 'type' => 2, 'title' => __( 'Ultimate Member Role Invalid', 'twitchpress' ), 'info' => __( 'Sorry, the role value for subscription plan [%s] is invalid. This needs to be corrected in TwitchPress settings.', 'twitchpress' ) );
        $messages_array['umextension'][3] = array( 'type' => 0, 'title' => __( 'Ultimate Member Role Updated', 'twitchpress' ), 'info' => __( 'Your community role is now %s because your subscription plan is %s.', 'twitchpress' ) );
        
        $notices_post_filter = array_merge( $notices_pre_filter, $messages_array );
        
        // Apply filtering by extensions which need to add more messages to the array. 
        return $notices_post_filter;  
    }       

    /**
    * Get an array of the roles that have been paired with Twitch subscription plans. 
    * 
    * @version 1.0
    */
    public function get_subscription_plan_roles() {
        $array = array();
        $array[] = get_option( 'twitchpress_um_subtorole_none' );
        $array[] = get_option( 'twitchpress_um_subtorole_prime' );
        $array[] = get_option( 'twitchpress_um_subtorole_1000' );
        $array[] = get_option( 'twitchpress_um_subtorole_2000' );
        $array[] = get_option( 'twitchpress_um_subtorole_3000' );
        return $array;
    }
                                                          
}
    
endif;    