<?php 
/*
Plugin Name: TwitchPress UM Extension
Version: 1.2.4
Plugin URI: http://twitchpress.wordpress.com
Description: Integrate the Ultimate Member and TwitchPress plugins.
Author: Ryan Bayne
Author URI: http://ryanbayne.wordpress.com
Text Domain: twitchpress-um
Domain Path: /languages
Copyright: © 2017 - 2018 Ryan Bayne
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' );

/**
 * Check if TwitchPress is active, else avoid activation.
 **/
if ( !in_array( 'channel-solution-for-twitch/twitchpress.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return;
}

/**
* Check if Ultimate MEmber is active, else avoid activation.
*/
if ( !in_array( 'ultimate-member/index.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return;
}

/**
 * Required minimums and constants
 */
define( 'TWITCHPRESS_UM_VERSION', '1.2.4' );
define( 'TWITCHPRESS_UM_MIN_PHP_VER', '5.6.0' );
define( 'TWITCHPRESS_UM_MIN_TP_VER', '1.7.1' );
define( 'TWITCHPRESS_UM_MAIN_FILE', __FILE__ );
define( 'TWITCHPRESS_UM_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
define( 'TWITCHPRESS_UM_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

if ( ! class_exists( 'TwitchPress_UM' ) ) :

    class TwitchPress_UM {
        /**
         * @var Singleton
         */
        private static $instance;        

        /**
         * Get a *Singleton* instance of this class.
         *
         * @return Singleton The *Singleton* instance.
         * 
         * @version 1.0
         */
        public static function instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }
            return self::$instance;
            
        } 
        
        /**
         * Private clone method to prevent cloning of the instance of the
         * *Singleton* instance.
         *
         * @return void
         */
        private function __clone() {}

        /**
         * Private unserialize method to prevent unserializing of the *Singleton*
         * instance.
         *
         * @return void
         */
        private function __wakeup() {}    
        
        /**
         * Protected constructor to prevent creating a new instance of the
         * *Singleton* via the `new` operator from outside of this class.
         */
        protected function __construct() {

            $this->define_constants();
            
            // Load files and register actions required before TwitchPress core inits.
            add_action( 'before_twitchpress_init', array( $this, 'pre_twitchpress_init' ) );      
        }

        /**
         * Define TwitchPress Login Constants.
         * 
         * @version 1.0
         */
        private function define_constants() {
            
            $upload_dir = wp_upload_dir();
            
            // Main (package) constants.
            if ( ! defined( 'TWITCHPRESS_UM_ABSPATH' ) )  { define( 'TWITCHPRESS_UM_ABSPATH', __FILE__ ); }
            if ( ! defined( 'TWITCHPRESS_UM_BASENAME' ) ) { define( 'TWITCHPRESS_UM_BASENAME', plugin_basename( __FILE__ ) ); }
            if ( ! defined( 'TWITCHPRESS_UM_DIR_PATH' ) ) { define( 'TWITCHPRESS_UM_DIR_PATH', plugin_dir_path( __FILE__ ) ); }
            
            // Constants for force hidden views to been seen for this plugin.
            if ( ! defined( 'TWITCHPRESS_SHOW_SETTINGS_USERS' ) )    { define( 'TWITCHPRESS_SHOW_SETTINGS_USERS', true ); }
            if ( ! defined( 'TWITCHPRESS_SHOW_SETTINGS_BOT' ) )      { define( 'TWITCHPRESS_SHOW_SETTINGS_BOT', true ); }
            if ( ! defined( 'TWITCHPRESS_SHOW_SETTINGS_CHAT' ) )     { define( 'TWITCHPRESS_SHOW_SETTINGS_CHAT', true ); }
            if ( ! defined( 'TWITCHPRESS_SHOW_SETTINGS_JUKEBOX' ) )  { define( 'TWITCHPRESS_SHOW_SETTINGS_JUKEBOX', true ); }
            if ( ! defined( 'TWITCHPRESS_SHOW_SETTINGS_GAMES' ) )    { define( 'TWITCHPRESS_SHOW_SETTINGS_GAMES', true ); }
            if ( ! defined( 'TWITCHPRESS_SHOW_SETTINGS_COMMANDS' ) ) { define( 'TWITCHPRESS_SHOW_SETTINGS_COMMANDS', true ); }
            if ( ! defined( 'TWITCHPRESS_SHOW_SETTINGS_CONTENT' ) )  { define( 'TWITCHPRESS_SHOW_SETTINGS_CONTENT', true ); }      
        }  
        
        /**
        * Do something before TwitchPress core initializes. 
        * 
        * @version 1.0
        */
        public function pre_twitchpress_init() {
            $this->load_dependencies();
            
            /**
                Do things here required before TwitchPress core plugin does init. 
            */
            
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
        public function load_dependencies() {

            include_once( 'functions.twitchpress-um-core.php' );
            
            // When doing admin_init load admin side dependencies.             
            add_action( 'admin_init', array( $this, 'load_admin_dependencies' ) );
        }
        
        /**
        * Call by add_action( 'admin_init ) in load_dependencies().
        * 
        * @version 1.0
        */
        public function load_admin_dependencies() {
             
        }
                   
        /**
         * Hooks
         * 
         * @version 2.0
         */
        private function attach_hooks() {

            // Filters
            add_filter( 'twitchpress_get_sections_users', array( $this, 'settings_add_section_users' ), 50 );
            add_filter( 'twitchpress_get_settings_users', array( $this, 'settings_add_options_users' ), 50 );
            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) ); 
            add_filter( 'twitchpress_update_system_scopes_status', array( $this, 'update_system_scopes_status' ), 1, 1 ); 

            // Decide a users role when the sub data has been updated. 
            add_action( 'twitchpress_user_sub_sync_finished', array( $this, 'set_twitch_subscribers_um_role' ), 2, 1 );// Passes user ID.
                                    
            // Apply UM role based on users Twitch plan data if it is available.  
            //add_action( 'edit_user_profile', array( $this, 'set_twitch_subscribers_um_role' ), 5, 1 );// Passes user object. 
            //add_action( 'personal_options_update', array( $this, 'set_twitch_subscribers_um_role' ), 5, 1 );// Passes user ID.
            //add_action( 'edit_user_profile_update', array( $this, 'set_twitch_subscribers_um_role' ), 5, 1 );// Passes user ID.
            //add_action( 'twitchpress_sync_new_twitch_subscriber', array( $this, 'set_twitch_subscribers_um_role' ), 5, 1 );// Passes user ID.
            //add_action( 'twitchpress_sync_continuing_twitch_subscriber', array( $this, 'set_twitch_subscribers_um_role' ), 5, 1 );// Passes user ID.
            //add_action( 'twitchpress_sync_discontinued_twitch_subscriber', array( $this, 'set_twitch_subscribers_um_role' ), 5, 1 );// Passes user ID.
            //add_action( 'twitchpress_manualsubsync', array( $this, 'set_twitch_subscribers_um_role' ), 5, 1 );// Passes user ID.

            // Systematic actions. 
            //add_action( 'wp_loaded', array( $this, 'set_current_users_um_role_based_on_twitch_sub' ), 5, 1 );
        }

        public static function install() {
            
        }
        
        public static function deactivate() { 
            
        }

        /**
        * Add scopes information (usually from extensions) to the 
        * system scopes status which is used to tell us what scopes are
        * required for the current system.
        * 
        * @param mixed $new_array
        * 
        * @version 1.0
        */
        public function update_system_scopes_status( $filtered_array ) {
            $scopes = array();
            
            // Scopes for admin only or main account functionality that is always used. 
            $scopes['admin']['twitchpress-um-extension']['required'] = array( 'channel_subscriptions', 'channel_check_subscription' );
            
            // Scopes for admin only or main account features that may not be used.
            $scopes['admin']['twitchpress-um-extension']['optional'] = array(); 
                        
            // Scopes for functionality that is always used. 
            $scopes['public']['twitchpress-um-extension']['required'] = array();
            
            // Scopes for features that may not be used.
            $scopes['public']['twitchpress-um-extension']['optional'] = array(); 
                        
            return array_merge_recursive( $filtered_array, $scopes );  
        }
        
        /**
        * Sync the current logged in user - hooked by wp_loaded
        * 
        * @version 1.0
        */
        public function set_current_users_um_role_based_on_twitch_sub() {
            if( !is_user_logged_in() ) { return false; }
            
            if( !twitchpress_is_sync_due( __FILE__, __FUNCTION__, __LINE__, 500 ) ) { return; }
            
            // Avoid processing the owner of the main channel (might not be admin with ID 1)
            if( twitchpress_is_current_user_main_channel_owner() ) { return; }
            
            $this->set_twitch_subscribers_um_role( get_current_user_id() );    
        }   
             
        /**
        * This method assumes that the "twitchpress_sub_plan_[channelid]"
        * user meta value has been updated already. 
        * 
        * The update would usually be done by the Sync Extension. We
        * call this method to apply UM roles based on what is stored by
        * Sync Extension.
        * 
        * @param mixed $user_id
        * @param mixed $channel_id
        * @param mixed $api_response
        * 
        * @version 2.3
        */
        public function set_twitch_subscribers_um_role( $user_id ) {
            global $ultimatemember, $bugnet;
            
            $return_value = null;

            // Get the current filter to help us trace backwards from log entries. 
            $filter = current_filter();
            $action = current_action();

            // edit_user_profile filter passed array
            if( 'edit_user_profile' == $filter ) 
            {
                // This hook actually passes a user object. 
                $user_id = $user_id->data->ID;                
            }
            
            /*  Other filters and actions that call this method. 
                1. personal_options_update
                2. edit_user_profile_update
                3. twitchpress_sync_new_twitch_subscriber
                4. twitchpress_sync_continuing_twitch_subscriber
                5. twitchpress_sync_discontinued_twitch_subscriber
                6. twitchpress_login_inserted_new_user 
            */

            $channel_id = twitchpress_get_main_channels_twitchid();
            
            // Avoid processing the main account or administrators so they are never downgraded. 
            $user_info = get_userdata( $user_id );
            if( $user_id === 1 || user_can( $user_id, 'administrator' ) ) { return; }
                        
            // Get subscription plan from user meta for the giving channel (based on channel ID). 
            $sub_plan = get_user_meta( $user_id, 'twitchpress_sub_plan_' . $channel_id, true );

            // Get possible current UM role. 
            $current_role = get_user_meta( $user_id, 'role', true );
            
            // Do nothing if the users UM role is admin (it is not administrator for UM)
            if( $current_role == 'admin' || $current_role == 'administrator' ) { return; }
            
            
            
$bugnet->log( __FUNCTION__, sprintf( __( 'UM Extension - Channel ID [%s].', 'twitchpress' ), $channel_id ), array(), true, false );  

$bugnet->log( __FUNCTION__, sprintf( __( 'UM Extension - User ID [%s].', 'twitchpress' ), $user_id ), array(), true, false );  

$bugnet->log( __FUNCTION__, sprintf( __( 'UM Extension - Sub Plan from user meta [%s].', 'twitchpress' ), $sub_plan ), array(), true, false );  

$bugnet->log( __FUNCTION__, sprintf( __( 'UM Extension - Filter [%s].', 'twitchpress' ), $filter ), array(), true, false );  

$bugnet->log( __FUNCTION__, sprintf( __( 'UM Extension - Action [%s].', 'twitchpress' ), $action ), array(), true, false ); 
 
$bugnet->log( __FUNCTION__, sprintf( __( 'UM Extension - Current role [%s].', 'twitchpress' ), $current_role ), array(), true, false );  


            
            if( !$sub_plan ) 
            { 
                // User has no Twitch subscription, so apply default (none) role. 
                $role = get_option( 'twitchpress_um_subtorole_none', false );
$bugnet->log( __FUNCTION__, sprintf( __( 'UM Extension - user [%s] does not have a Twitch subscription to the main channel.', 'twitchpress' ), $user_id ), array(), true, false );  
            }
            else
            {
                $option_string = 'twitchpress_um_subtorole_' . $sub_plan;
                
$bugnet->log( __FUNCTION__, sprintf( __( 'UM Extension - user [%s] has a Twitch subscription to the main channel. Getting and applying the matching UM role.', 'twitchpress' ), $user_id ), array(), true, false );  
                
                // Get the UM role paired with the $sub_plan
                $role = get_option( $option_string, false );
                
$bugnet->log( __FUNCTION__, sprintf( __( 'UM Extension - New Role [%s].', 'twitchpress' ), $role ), array(), true, false );  
                                
                if( !$role )         
                {   
                    // UM settings have not been setup or there is somehow a mismatch (that should never happen though).
$bugnet->log( __FUNCTION__, sprintf( __( 'UM Extension attempted to update WP user [%s] with an Ultimate Member role but the subscription plan stored in user meta does not have a matching option key. Expected option key: [%s]', 'twitchpress-um' ), $user_id, $option_string ), array(), false );
                    $role = get_option( 'twitchpress_um_subtorole_none', false );
                }
                else
                {
$bugnet->log( __FUNCTION__, sprintf( __( 'UM Extension role for [%s] is [%s]', 'twitchpress-um' ), $option_string, $role ), array(), true, false );                    
                }              
            }

$bugnet->log( __FUNCTION__, sprintf( __( 'UM Extension role update for user ID [%s] to [%s]', 'twitchpress-um' ), $user_id, $role ), array(), true, false );                    

            update_user_meta( $user_id, 'role', $role );           
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
                'ultimatemember'  => __( 'UM Roles', 'twitchpress-um' ),
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
                $um_roles = um_get_roles();
                            
                $new_settings = apply_filters( 'twitchpress_ultimatemember_users_settings', array(
     
                    array(
                        'title' => __( 'Subscription to Role Pairing', 'twitchpress-um' ),
                        'type'     => 'title',
                        'desc'     => __( 'These options have been added by the TwitchPress UM extension. Pair your Twitch subscription plans to Ultimate Member roles.', 'twitchpress-um' ),
                        'id'     => 'subscriptionrolepairing',
                    ),

                    array(
                        'title'    => __( 'No Subscription', 'twitchpress-um' ),
                        'id'       => 'twitchpress_um_subtorole_none',
                        'css'      => 'min-width:300px;',
                        'default'  => 'menu_order',
                        'type'     => 'select',
                        'options'  => apply_filters( 'twitchpress_um_subtorole_none', $um_roles ),
                    ),
                    
                    array(
                        'title'    => __( 'Prime', 'twitchpress-um' ),
                        'id'       => 'twitchpress_um_subtorole_prime',
                        'css'      => 'min-width:300px;',
                        'default'  => 'menu_order',
                        'type'     => 'select',
                        'options'  => apply_filters( 'twitchpress_um_subtorole_prime', $um_roles ),
                    ),                    
                    
                    array(
                        'title'    => __( '$4.99', 'twitchpress-um' ),
                        'id'       => 'twitchpress_um_subtorole_1000',
                        'css'      => 'min-width:300px;',
                        'default'  => 'menu_order',
                        'type'     => 'select',
                        'options'  => apply_filters( 'twitchpress_um_subtorole_1000', $um_roles ),
                    ),
                      
                    array(
                        'title'    => __( '$9.99', 'twitchpress-um' ),
                        'id'       => 'twitchpress_um_subtorole_2000',
                        'css'      => 'min-width:300px;',
                        'default'  => 'menu_order',
                        'type'     => 'select',
                        'options'  => apply_filters( 'twitchpress_um_subtorole_2000', $um_roles ),
                    ),
                      
                    array(
                        'title'    => __( '$24.99', 'twitchpress-um' ),
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
         * Adds plugin action links
         *
         * @since 1.0.0
         */
        public function plugin_action_links( $links ) {
            $plugin_links = array(

            );
            return array_merge( $plugin_links, $links );
        }        

        /**
         * Get the plugin url.
         * @return string
         */
        public function plugin_url() {                
            return untrailingslashit( plugins_url( '/', __FILE__ ) );
        }

        /**
         * Get the plugin path.
         * @return string
         */
        public function plugin_path() {              
            return untrailingslashit( plugin_dir_path( __FILE__ ) );
        }                                                         
    }
    
endif;    

if( !function_exists( 'TwitchPress_UM_Ext' ) ) {

    function TwitchPress_UM_Ext() {        
        return TwitchPress_UM::instance();
    }

    // Global for backwards compatibility.
    $GLOBALS['twitchpress-um'] = TwitchPress_UM_Ext(); 
}

// Activation and Deactivation hooks.
register_activation_hook( __FILE__, array( 'TwitchPress_UM', 'install' ) );
register_deactivation_hook( __FILE__, array( 'TwitchPress_UM', 'deactivate' ) );