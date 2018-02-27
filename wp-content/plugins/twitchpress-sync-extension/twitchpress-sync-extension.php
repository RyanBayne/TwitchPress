<?php 
/*
Plugin Name: TwitchPress Sync Extension
Version: 1.3.3
Plugin URI: http://twitchpress.wordpress.com
Description: Twitch extension for syncing most Twitch.tv data within a TwitchPress system.
Author: Ryan Bayne
Author URI: http://ryanbayne.wordpress.com
Text Domain: twitchpress-sync
Domain Path: /languages
Copyright: © 2017 - 2018 Ryan Bayne
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
 
GPL v3 

This program is free software downloaded from WordPress.org: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. This means
it can be provided for the sole purpose of being developed further
and we do not promise it is ready for any one persons specific needs.
See the GNU General Public License for more details.

See <http://www.gnu.org/licenses/>.


    Planning to create a TwitchPress extension like this one?

    Step 1: Read WordPress.org plugin development guidelines
    https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/

    Step 2: Read the TwitchPress extension development guidelines.
    Full guide coming soon!
    
    
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
 * Required minimums and constants
 */
define( 'TWITCHPRESS_SYNC_VERSION', '1.3.3' );
define( 'TWITCHPRESS_SYNC_MIN_PHP_VER', '5.6.0' );
define( 'TWITCHPRESS_SYNC_MIN_TP_VER', '1.7.3' );
define( 'TWITCHPRESS_SYNC_MAIN_FILE', __FILE__ );
define( 'TWITCHPRESS_SYNC_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
define( 'TWITCHPRESS_SYNC_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

if ( ! class_exists( 'TwitchPress_Sync' ) ) :

    class TwitchPress_Sync {
        /**
         * @var Singleton
         */
        private static $instance;
        
        /**
        * @var integer - user sync flood delay.        
        */
        public $sync_user_flood_delay = 120;// seconds

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
        
        public function pre_twitchpress_init() {
            $this->load_dependencies();
            
            /**
                Do things here required before TwitchPress core plugin does init. 
            */
            
            add_action( 'twitchpress_init', array( $this, 'after_twitchpress_init' ) );
        }

        public function after_twitchpress_init() {
            $this->attach_hooks();    
        }

        /**
         * Load all plugin dependencies.
         */
        public function load_dependencies() {

            // When doing admin_init load admin side dependencies.             
            add_action( 'admin_init', array( $this, 'load_admin_dependencies' ) );
        }

        public function load_admin_dependencies() {
                 
        }
                       
        /**
         * Define TwitchPress Login Constants.
         * 
         * @version 1.0
         */
        private function define_constants() {

            if ( ! defined( 'TWITCHPRESS_SYNC_ABSPATH' ) )  { define( 'TWITCHPRESS_SYNC_ABSPATH', __FILE__ ); }
            if ( ! defined( 'TWITCHPRESS_SYNC_BASENAME' ) ) { define( 'TWITCHPRESS_SYNC_BASENAME', plugin_basename( __FILE__ ) ); }
            if ( ! defined( 'TWITCHPRESS_SYNC_DIR_PATH' ) ) { define( 'TWITCHPRESS_SYNC_DIR_PATH', plugin_dir_path( __FILE__ ) ); }
                  
        }  
        
        /**
         * Hook into actions and filters. This method is called
         * after the core plugin has loaded.
         * 
         * @version 1.2
         */
        private function attach_hooks() {
            // WP core action hooks.
            add_action( 'wp_login', array( $this, 'sync_user_on_login' ), 1, 2 );
            add_action( 'profile_personal_options', array( $this, 'sync_user_on_viewing_profile' ) );
            add_action( 'admin_init', array( $this, 'admin_listener' ), 1 );
                   
            // Systematic syncing will perform constant data updating with delays to prevent flooding.
            add_action( 'wp_loaded', array( $this, 'systematic_syncing' ), 1 );

            // Custom action hooks. 
            add_action( 'twitchpress_sync_currentusers_twitchsub_mainchannel', 'twitchpress_sync_currentusers_twitchsub_mainchannel', 5 );
            
            // User Profile
            add_action( 'show_user_profile',        array( $this, 'twitch_subscription_status_show' ) );
            add_action( 'edit_user_profile',        array( $this, 'twitch_subscription_status_edit' ) );
            add_action( 'personal_options_update',  array( $this, 'twitch_subscription_status_save' ) );
            add_action( 'edit_user_profile_update', array( $this, 'twitch_subscription_status_save' ) );
                
            // Filters
            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
            add_filter( 'twitchpress_get_sections_kraken', array( $this, 'settings_add_section_kraken' ), 10 );
            add_filter( 'twitchpress_get_settings_kraken', array( $this, 'settings_add_options_kraken' ), 10 );
            add_filter( 'twitchpress-tools-query', array( $this, 'filter_quick_tools' ), 1, 1 );
            
            do_action( 'twitchpress_sync_loaded' );
        }
        
        /**
        * A main channel sync method.
        * 
        * Do hook when you need to ensure that a current logged in users
        * Twitch subscription data has been updated else update it. 
        * 
        * @version 2.0
        */
        public function twitchpress_sync_currentusers_twitchsub_mainchannel() {    
            // Hook should only be called within a method that involves a
            // logged in user but we will make sure. 
            if( !is_user_logged_in() ) { return; } 
                                         
            // Avoid processing the owner of the main channel (might not be admin with ID 1)
            if( twitchpress_is_current_user_main_channel_owner() ) { return; }
                       
            $this->sync_user( get_current_user_id(), false, false, 'user' );  
        }
        
        /**
        * Using load balancing limits and caching we trigger automated
        * processing constantly. This is a temporary approach until
        * background processing with reliance on WP CRON is established. 
        * 
        * @version 1.0
        */
        public function systematic_syncing() {          
            // Apply a short delay on all syncing activity. 
            if( !twitchpress_is_sync_due( __LINE__, __FUNCTION__, __LINE__, 60 ) )
            {
                return;        
            }
              
            // Run sync on all of the main channel subscribers, with offset to manage larger numbers.
            add_action( 'shutdown', array( $this, 'sync_channel_subscribers' ), 10 );    
            
            // Sync the current logged in visitors twitch sub data for the main channel. 
            add_action( 'shutdown', array( $this, 'twitchpress_sync_currentusers_twitchsub_mainchannel' ), 10 );    
        }
        
        /**
        * Syncs the main channels subscribers systematically. 
        * 
        * 1. Gets the main channels subscribers.
        * 2. Creates a channel post for each subscriber. 
        * 3. Post meta includes all possible information.
        * 4. We pair channel post with user when they login using Twitch. 
        * 
        * @version 2.0
        */
        public function sync_channel_subscribers() {
            global $bugnet;
            
            if( get_option( 'twitchpress_sync_switch_channel_subscribers' ) !== 'yes' ) {return;}
            
            // This option is only generated here. 
            $option = get_option( 'twitchpress_sync_job_channel_subscribers' );
            if( !is_array( $option ) ) 
            { 
                $option = array(
                    'delay' => 1800,
                    'last_time' => 666,// 666 has no importance, placeholder only
                    'subscribers' => 0,
                    'subs_limit'  => 5, 
                ); 
            }
            
            // Load Kraken and set credentials for the app + channel.  
            $kraken = new TWITCHPRESS_Twitch_API_Calls();
            $id = $kraken->get_main_channel_id();// Right now everything defaults to the main channel.

            $earliest_time = $option['last_time'] + $option['delay'];
            
            if( $earliest_time > time() ) { return false; } else { $option['last_time'] = time(); }
            
            $bugnet->log( __FUNCTION__, __( 'Channel subscribers sync is due and has begun.', 'twitchpress-sync' ) );  
                        
            // Establish an offset within a continuing job. 
            if( !isset( $option['offset'] ) || !is_numeric( $option['offset'] ) ) { $option['offset'] = 0; }
            
            // Update the jobs option early to ensure no error later stops timers being stored. 
            update_option( 'twitchpress_sync_job_channel_subscribers', $option, false );
        
            $token = twitchpress_get_main_channels_token();
            
            $code = twitchpress_get_main_channels_code(); 
              
            $subscribers = $kraken->get_channel_subscribers( $id, $option['subs_limit'], $option['offset'] , 'asc', $token, $code );

            if( $subscribers !== null && isset( $subscribers['subscriptions'] ) )
            {
                if( isset( $subscribers['_total'] ) && $subscribers['_total'] < $option['subs_limit'] ) 
                {
                    $bugnet->log( __FUNCTION__, sprintf( __( 'Kraken request includes %s subscribers and they are the last to be imported.', 'twitchpress-sync' ), $subscribers['_total'] ) );
                    
                    // Then we have reached the last subscriber for the channel, requiring the job to be restarted. 
                    $option['offset'] = 0;
                    $option['jobs'] = $option['jobs'] + 1;// Counts how many times we do the entire process.
                    
                    // Store last count to help any troubleshooting that needs done in future. 
                    $option['lasttotal'] = isset( $subscribers['_total'] );
                }

                if( is_array($subscribers['subscriptions']  ) )
                {
                    $new_subscribers = 0;
                    
                    // Get or create a channel post based on this subscriber. 
                    foreach( $subscribers['subscriptions'] as $key => $sub ) 
                    {
                        $bugnet->log( __FUNCTION__, sprintf( __( 'Twitch user ID %s has been returned in request and about to be stored as a Twitch Channel post.', 'twitchpress-sync' ), $sub['users']['_id'] ) );
                        
                        // Subscribers are stored as channel posts, subs can get their own page on the site.
                        if( $this->save_subscriber( $sub ) )
                        {  
                            ++$new_subscribers;               
                        }   
                        else
                        {
                            // False indicates existing post was updated.
                        }    
                    }   
                    
                    $option['subscribers'] = $option['subscribers'] + $new_subscribers;
                }
            }
            else
            {
                $bugnet->log( __FUNCTION__, __( 'Subscribers sync request returned nothing.', 'twitchpress-sync' ) );
            }
            
            // Update the jobs option. 
            update_option( 'twitchpress_sync_job_channel_subscribers', $option, false );
            
            unset($kraken);               
        } 
        
        /**
        * Updates an existing subscriber in the form of a channel post
        * or creates a new channel post and stores Twitch users details
        * in post meta. 
        * 
        * @returns boolean as indicator of insert or update of post (true if new post is created else false) 
        * 
        * @param mixed $sub
        * 
        * @version 1.0
        */
        public function save_subscriber( $sub ) {
         
            // Check for existing twitchchannels post.    
            $args = array(
                'post_type'  => 'twitchchannels',
                'meta_query' => array(
                    array(
                        'key'   => 'twitch_user_id',
                        'value' => $sub['user']['_id'],
                    )
                )
            );
            $postslist = get_posts( $args );
                   
            if( !empty( $postslist ) && is_array( $postslist ) )
            {   
                $bugnet->log( __FUNCTION__, sprintf( __( 'Twitch user ID %s already has a Channel post. This has no relation to their WordPress user status.', 'twitchpress-sync' ), $sub['users']['_id'] ) );
               
                if( isset( $postslist[0]->ID ) ) 
                {
                    update_post_meta( $postslist[0]->ID, 'twitch_sub_id',            $sub['_id'] );
                    update_post_meta( $postslist[0]->ID, 'twitch_sub_created_at',    $sub['created_at'] );
                    update_post_meta( $postslist[0]->ID, 'twitch_sub_plan',          $sub['sub_plan'] );
                    update_post_meta( $postslist[0]->ID, 'twitch_sub_plan_name',     $sub['sub_plan_name'] );
                    update_post_meta( $postslist[0]->ID, 'twitch_user_created_at',   $sub['user']['created_at'] );
                    update_post_meta( $postslist[0]->ID, 'twitch_user_display_name', $sub['user']['display_name'] );
                    update_post_meta( $postslist[0]->ID, 'twitch_user_name',         $sub['user']['name'] );
                    update_post_meta( $postslist[0]->ID, 'twitch_user_type',         $sub['user']['type'] );
                    update_post_meta( $postslist[0]->ID, 'twitch_user_updated_at',   $sub['user']['updated_at'] );       
                }
                
                return false;
            }    
            
            // Insert a new channel based on the users data
            $postarr = array(
                'post_author'  => 1,
                'post_content' => $sub['user']['bio'],                                                    
                'post_title' => $sub['user']['display_name'],                           
                'post_excerpt' => $sub['user']['bio'],                           
                'post_status' => 'publish',                             
                'post_type' => 'twitchchannels',                                                                 
            ); 

            $post_id = wp_insert_post( $postarr, true );
            
            if( is_wp_error( $post_id ) ) 
            {
                $bugnet->log( __FUNCTION__, sprintf( __( 'WordPress failed to create a new Twitch Channel post for Twitch subscriber with user ID %s.', 'twitchpress-sync' ), $sub['users']['_id'] ) );
                return false;
            }    

            $bugnet->log( __FUNCTION__, sprintf( __( 'WordPress created a new Twitch Channel post with ID %s for Twitch subscriber with user ID %s.', 'twitchpress-sync' ), $post_id, $sub['users']['_id'] ) );
        
            add_post_meta( $post_id, 'twitch_user_id',           $sub['user']['_id'] );  
            add_post_meta( $post_id, 'twitch_sub_id',            $sub['_id'] );
            add_post_meta( $post_id, 'twitch_sub_created_at',    $sub['created_at'] );
            add_post_meta( $post_id, 'twitch_sub_plan',          $sub['sub_plan'] );
            add_post_meta( $post_id, 'twitch_sub_plan_name',     $sub['sub_plan_name'] );
            add_post_meta( $post_id, 'twitch_user_created_at',   $sub['user']['created_at'] );
            add_post_meta( $post_id, 'twitch_user_display_name', $sub['user']['display_name'] );
            add_post_meta( $post_id, 'twitch_user_name',         $sub['user']['name'] );
            add_post_meta( $post_id, 'twitch_user_type',         $sub['user']['type'] );
            add_post_meta( $post_id, 'twitch_user_updated_at',   $sub['user']['updated_at'] );
            
            return true;
        }
        
        /**
        * Called by register_activation_hook()
        * 
        * @version 1.0
        */
        public static function install() {
            
        }   
        
        /**
        * Called by register_deactivation_hook()
        * 
        * Do not confuse with uninstallation. This runs when plugin is 
        * disabled which might be temporary. 
        * 
        * @version 1.0
        */
        public static function deactivate() {
            
        }   

        /**
        * Add scopes information (usually from extensions) to the 
        * system scopes status which is used to tell us what scopes are
        * required for the current system.
        * 
        * @param mixed $new_array
        * 
        * @version 2.0
        */
        public function update_system_scopes_status( $filtered_array ) {
            
            $scopes = array();
            
            /*
               Not used because sync extension is to be merged with the core. 
               Sync extension technically does not have its own required roles.
               Only the services that require sync have required roles. 
            */
            
            // Scopes for admin only or main account functionality that is always used. 
            $scopes['admin']['twitchpress-sync-extension']['required'] = array();
            
            // Scopes for admin only or main account features that may not be used.
            $scopes['admin']['twitchpress-sync-extension']['optional'] = array(); 
                        
            // Scopes for functionality that is always used. 
            $scopes['public']['twitchpress-sync-extension']['required'] = array();
            
            // Scopes for features that may not be used.
            $scopes['public']['twitchpress-sync-extension']['optional'] = array(); 
                        
            return array_merge( $filtered_array, $scopes );      
        }
              
        public function admin_listener() {
 
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
            
            // Include the extensions tool class. 
            require_once( $this->plugin_path() . '/includes/class.twitchpress-sync-tools.php' );
            $twitchpress_sync_tools = new TwitchPress_Sync_Tools();
                             
            if( !method_exists( $twitchpress_sync_tools, $tool_name ) ) {       
                return;
            }    
            
            // Ensure the request is attempting to use an actual tool!
            if( substr( $tool_name, 0, 5 ) !== "tool_" ) { 
                return; 
            }
                                    
            // Prepare an array for passing to the tool method.
            $tool_parameters_array = array();
            
            // Get the requested tools information for performing validation.
            $twitchpress_sync_tools->return_tool_info = true;
            eval( '$tool_info = $twitchpress_sync_tools->$tool_name( $tool_parameters_array );');
            
            // We require a capability to ensure security hasn't been totally forgotten.
            if( !isset( $tool_info['capability'] ) ) {
                return;
            }
            
            // Ensure the user has the capability to run this tool.
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
            
            $twitchpress_sync_tools->return_tool_info = false;
            $twitchpress_sync_tools->$tool_name( $tool_parameters_array );            
        }
      
        /**
      * Filters TwitchPress tools array. Mainly for adding new tools. 
      * 
      * @param mixed $tools_array
      * @param mixed $return_tool_info
      * 
      * @version 1.0
      */
        public function filter_quick_tools( $tools_array, $return_tool_info = true ) {
            
            // Include the extensions tool class. 
            require_once( $this->plugin_path() . '/includes/class.twitchpress-sync-tools.php' );
            $twitchpress_sync_tools = new TwitchPress_Sync_Tools();
                        
            /*
                TODO: this approach needs to be improved by reading all
                functions with tool_ in class.twitchpress-sync-tools.php
                and avoid having to update this method each time a new
                tool is added. 
            
            */
            
            $tools_array[] = $twitchpress_sync_tools->tool_sync_all_users( $return_tool_info );   
            return $tools_array;             
        }
          
        /**
        * Styles for login page hooked by login_enqueue_scripts
        * 
        * @version 1.0
        */
        public function twitchpress_login_styles() {

        }
        
        /**
        * Add a new section to the Kraken tab (titled Twitch API).
        * 
        * @param mixed $sections
        * 
        * @version 1.0
        */
        public function settings_add_section_kraken( $sections ) {  
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
                'syncvalues'  => __( 'Sync Values', 'twitchpress' ),
            );

            return array_merge( $sections, $new_sections );           
        }
        
        /**
        * Add options to the kraken (Twitch API) settings tab. 
        * 
        * @param mixed $settings
        * 
        * @version 1.2
        */
        public function settings_add_options_kraken( $settings ) {
            global $current_section, $only_section;
            
            $new_settings = array();
            
            // This first section is default if there are no other sections at all.
            if ( 'syncvalues' == $current_section || !$current_section && $only_section ) {
                $new_settings = apply_filters( 'twitchpress_syncvalues_kraken_settings', array(
     
                    array(
                        'title' => __( 'Activate Syncronizing: Grouped Data', 'twitchpress-sync' ),
                        'type'  => 'title',
                        'desc'  => __( 'Select the data groups and purposes your site will need to operate the services you plan to offer. A group will store and update more than one value.', 'twitchpress-sync' ),
                        'id'    => 'syncgroupedvaluesettings',
                    ),

                    array(
                        'desc'            => __( 'Subscribers - import all of your channels subscribers and use the data to improve subscriber experience. This will create Twitch Channel posts and those posts double as a way to manage followers, subscribers, moderators etc.', 'twitchpress' ),
                        'id'              => 'twitchpress_sync_switch_channel_subscribers',
                        'default'         => 'no',
                        'type'            => 'checkbox',
                        'checkboxgroup'   => 'start',
                        'show_if_checked' => 'option',
                    ),
                    
                    /*
                    array( 
                        'desc'            => __( 'Import all subscribers for building a subscriber aware website."', 'twitchpress' ),
                        'id'              => 'twitchpress_sync_switch_channel_subscribers',
                        'default'         => 'yes',
                        'type'            => 'checkbox',
                        'checkboxgroup'   => '',
                        'show_if_checked' => 'yes',
                        'autoload'        => false,
                    ),

                    array(
                        'desc'            => __( 'partnered: used by services that monitor a visitors partner status."', 'twitchpress' ),
                        'id'              => 'twitchpress_sync_user_partnered',
                        'default'         => 'yes',
                        'type'            => 'checkbox',
                        'checkboxgroup'   => 'end',
                        'show_if_checked' => 'yes',
                        'autoload'        => false,
                    ),
                    */
                                                      
                    array(
                        'type'     => 'sectionend',
                        'id'     => 'syncgroupedvaluesettings'
                    ),                    
                    
                    array(
                        'title' => __( 'Activate Syncronizing: Individual Values', 'twitchpress-sync' ),
                        'type'  => 'title',
                        'desc'  => __( 'The Twitch API returns groups of data for many calls, that cannot be avoided. What can be avoided is storing all the data Twitch returns. If you need a single value from each channel/user to operate a feature and want to avoid storing large amounts of Twitch data that has no used to you. Use the options below to configure TwitchPress to extract the values you need and ignore the rest when making requests to the Twitch API.', 'twitchpress-sync' ),
                        'id'    => 'syncvaluesettings',
                    ),

                    array(
                        'desc'            => __( 'name: the Twitch username can change.', 'twitchpress' ),
                        'id'              => 'twitchpress_sync_user_name',
                        'default'         => 'yes',
                        'type'            => 'checkbox',
                        'checkboxgroup'   => 'start',
                        'show_if_checked' => 'option',
                    ),
                    
                    array(
                        'desc'            => __( 'sub_plan: keep the Twitch subscription plan updated and control WordPress membership levels.', 'twitchpress' ),
                        'id'              => 'twitchpress_sync_user_sub_plan',
                        'default'         => 'yes',
                        'type'            => 'checkbox',
                        'checkboxgroup'   => '',
                        'show_if_checked' => 'yes',
                        'autoload'        => false,
                    ),
                    
                    array(
                        'desc'            => __( 'partnered: used by services that monitor a visitors partner status."', 'twitchpress' ),
                        'id'              => 'twitchpress_sync_user_partnered',
                        'default'         => 'yes',
                        'type'            => 'checkbox',
                        'checkboxgroup'   => 'end',
                        'show_if_checked' => 'yes',
                        'autoload'        => false,
                    ),
                                                      
                    array(
                        'type'     => 'sectionend',
                        'id'     => 'syncvaluesettings'
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

        /**
        * Syncronize the visitors Twitch data when they login.  
        * 
        * @version 1.2
        */
        public function sync_user_on_login( $user_login, $user ) {  
            global $bugnet;
                           
            $bugnet->trace( 'twitchpresssyncuser',
                __LINE__,
                __FUNCTION__,
                __FILE__,
                false,
                sprintf( __( 'User %s has logged in. Beginning procedure to query their Twitch data and update their WP account.', 'twitchpress' ), $user->ID )
            );
            
            $this->sync_user( $user->data->ID, false, false );    
        }

        /**
        * Hooked by profile_personal_options and syncs user data. 
        * 
        * @version 1.2
        */
        public function sync_user_on_viewing_profile( $user ) {    
            $this->sync_user( $user->ID, false, false );    
        }        
         
        /**
        * Syncronize the visitors Twitch data.
        * 
        * Hooked to login and profile visit. 
        * Also called in a custom action for running the user sync when it matters.   
        * 
        * @version 2.1
        */
        public function sync_user( $wp_user_id, $ignore_delay = false, $output_notice = false, $side = 'user' ) {  
            global $bugnet;
                      
            // Ensure the giving user is due a sync.   
            if( false === $ignore_delay ) {                  
                if( !$this->is_users_sync_due( $wp_user_id ) ) { 
                                
                    $bugnet->trace( 'twitchpresssyncuser',
                        __LINE__,
                        __FUNCTION__,
                        __FILE__,
                        true,
                        sprintf( __( 'User %s is not due for an update. Try again soon.', 'twitchpress' ), $wp_user_id )
                    );  
                  
                    if( $output_notice ) 
                    {
                        TwitchPress_Admin_Notices::add_wordpress_notice( 'twitchpresssyncusernotdue', 'info', false, __( 'TwitchPress', 'twitchpress' ), __( 'Your subscription data was requested not long ago. Please wait a few minutes before trying again.', 'twitchpress' ) );
                    }
                    
                    return false; 
                }
            }
                        
            update_user_meta( $wp_user_id, 'twitchpress_sync_time', time() );
            
            /*   Call individual methods here to sync specific data.  */
            
            // Subscription Syncing - We can choose to sync from user side or channel side. 
            if( $side == 'user' )
            {
                // Twitch Subscription Sync
                $this->user_sub_sync( $wp_user_id, $output_notice );    
            }
            elseif( $side == 'channel' )
            {
                // Twitch Subscription Sync
                $this->main_channel_sub_sync( $wp_user_id, $output_notice );    
            }           
        }

        /**
        * Checks if the giving user is due a full sync.
        * 
        * @param mixed $user_id
        * 
        * @version 1.0
        */
        public function is_users_sync_due( $wp_user_id ) {
            $time = get_user_meta( $wp_user_id, 'twitchpress_sync_time', true );
            if( !$time ) { $time = 0; }         
            $earliest_time = $this->sync_user_flood_delay + $time;
            if( $earliest_time > time() ) {  return false; }
            return true;
        }
        
        /**
        * Sync subscription data from Twitch to WP
        * 
        * @param mixed $wp_user_id
        * @param mixed $notice_output
        * 
        * @version 1.0
        */
        public function user_sub_sync( $wp_user_id, $output_notice = false ){       
            global $bugnet;
 
            $kraken = new TWITCHPRESS_Twitch_API_Calls();

            $twitch_user_id = twitchpress_get_user_twitchid_by_wpid( $wp_user_id );    
            $twitch_channel_id = twitchpress_get_main_channels_twitchid();
            $twitch_user_token = twitchpress_get_user_token( $wp_user_id );
            
            // Get the full subscription object.
            $twitch_sub_response = $kraken->getUserSubscription( $twitch_user_id, $twitch_channel_id, $twitch_user_token );
 
            // Get possible existing sub plan from a earlier sub sync.
            $local_sub_plan = get_user_meta( $wp_user_id, 'twitchpress_sub_plan_' . $twitch_channel_id, true  );
            
            if( isset( $twitch_sub_response['error'] ) || $twitch_sub_response === null ) 
            {   
                // Prepare error code/status to improve log entry.
                $status = '';
                if( isset( $twitch_sub_response['status']) )
                {
                    $status = $twitch_sub_response['status'];   
                }             
                else
                {
                    $status = 'Null';
                }

                if( twitchpress_is_valid_sub_plan( $local_sub_plan ) ) 
                {      
                    // Remove the sub plan value to ensure there is no mistake when it comes to user access.
                    delete_user_meta( $wp_user_id, 'twitchpress_sub_plan_' . $twitch_channel_id );  
                    delete_user_meta( $wp_user_id, 'twitchpress_sub_plan_name_' . $twitch_channel_id );  
    
                    if( $output_notice ) 
                    {
                        TwitchPress_Admin_Notices::add_wordpress_notice( 'usersubsyncnosubresponse', 'warning', false, 
                        __( 'Subscription Ended', 'twitchpress' ), 
                        __( 'The response from Twitch.tv indicates that a previous subscription to the sites main channel was discontinued. Subscriber perks on this website will also be discontinued.', 'twitchpress' ) );
                    }
                    
                    return;
                }       

                if( $output_notice ) 
                {
                    TwitchPress_Admin_Notices::add_wordpress_notice( 'usersubsyncnosubresponse', 'info', false, 
                    __( 'Not Subscribing', 'twitchpress' ), 
                    __( 'The response from Twitch.tv indicates that you are not currently subscribing to this sites main channel.', 'twitchpress' ) );
                }
                                    
                return;
            }
            elseif( isset( $twitch_sub_response['sub_plan'] ) )
            {
                // The visitor is a subscriber to the main channel. 
                // The sub status is boolean only.
                update_user_meta( $wp_user_id, 'twitchpress_substatus_mainchannel', true );
            
                if( !twitchpress_is_valid_sub_plan( $local_sub_plan ) ) 
                {      
                    // User is being registered as a Twitch sub for the first time.
                    update_user_meta( $wp_user_id, 'twitchpress_sub_plan_' . $twitch_channel_id, $twitch_sub_response['sub_plan'] );
                    update_user_meta( $wp_user_id, 'twitchpress_sub_plan_name_' . $twitch_channel_id, $twitch_sub_response['sub_plan_name'] );
                    
                    if( $output_notice ) 
                    {
                        TwitchPress_Admin_Notices::add_wordpress_notice( 'usersubsyncnosubresponse', 'success', false, 
                        __( 'New Subscriber', 'twitchpress' ), 
                        __( 'You\'re subscription has been confirmed and your support is greatly appreciated. You now have access to subscriber perks on this site.', 'twitchpress' ) );
                    }
                    
                    return;
                } 
                elseif( twitchpress_is_valid_sub_plan( $local_sub_plan ) ) 
                {  
                    // User is not a newely detected subscriber and has sub history stored in WP, check for sub plan change. 
                    if( $twitch_sub_response['sub_plan'] !== $local_sub_plan )
                    { 
                        // User has changed their subscription plan and are still subscribing.
                        update_user_meta( $wp_user_id, 'twitchpress_sub_plan_' . $twitch_channel_id, $twitch_sub_response['sub_plan'] );                        
                        update_user_meta( $wp_user_id, 'twitchpress_sub_plan_name_' . $twitch_channel_id, $twitch_sub_response['sub_plan'] );    
                        
                        if( $output_notice ) 
                        {
                            TwitchPress_Admin_Notices::add_wordpress_notice( 'usersubsyncnosubresponse', 'success', false, 
                            __( 'Continuing Subscriber', 'twitchpress' ), 
                            __( 'Your existing subscription has been confirmed as unchanged and your continued support is greatly appreciated.', 'twitchpress' ) );
                        }                                       
                    }
                    else
                    {          
                        if( $output_notice ) 
                        {
                            TwitchPress_Admin_Notices::add_wordpress_notice( 'usersubsyncnosubresponse', 'success', false, 
                            __( 'Subscription Updated', 'twitchpress' ), 
                            __( 'Your existing subscription has been updated due to a change in your plan. You\'re continued support is greatly appreciated.', 'twitchpress' ) );
                        }            
                    }

                    return;
                } 
            }      
        }   
             
        /**
        * Saves the users Twitch subscription status for the main channel.
        * 
        * @returns true if a change is made and false if no change to sub status has been made.
        * 
        * @version 2.2
        */
        private function main_channel_sub_sync( $user_id, $output_notice = false ) {
            global $bugnet;
         
            // Does the giving user subscribe to the main channel?
            $kraken = new TWITCHPRESS_Twitch_API_Calls();
            $channel_id = twitchpress_get_main_channels_twitchid();
            $channel_token = twitchpress_get_main_channels_token();

            // Setup a call name for tracing. 
            $kraken->twitch_call_name = __( 'Sync Users Subscription', 'twitchpress' );
            
            $users_twitch_id = get_user_meta( $user_id, 'twitchpress_twitch_id', true );
            
            // Check channel subscription from channel side (does not require scope permission)
            $twitch_sub_response = $kraken->getChannelSubscription( $users_twitch_id, $channel_id, $channel_token );

            // Get possible existing sub plan from a earlier sub sync.
            $local_sub_plan = get_user_meta( $user_id, 'twitchpress_sub_plan_' . $kraken->get_main_channel_id(), true  );
            
            // If Twitch user is a subscriber to channel do_action() early here, maybe a simple thank you notice. 
            if( isset( $twitch_sub_response['error'] ) || $twitch_sub_response === null ) 
            {   
                // Prepare error code/status to improve log entry.
                $status = '';
                if( isset( $twitch_sub_response['status']) )
                {
                    $status = $twitch_sub_response['status'];   
                }             
                else
                {
                    $status = 'Null';
                }
                
                $bugnet->trace( 'twitchpresssyncuser',__LINE__,__FUNCTION__,__FILE__,true,sprintf( __( 'Twitch Error Status %s: get subscription data for Twitch user %s on channel %s.', 'twitchpress' ), $status, $users_twitch_id, $channel_id ), array(), false);
                
                if( twitchpress_is_valid_sub_plan( $local_sub_plan ) ) 
                {      
                    // User did have a sub but we assume they have cancelled. 
                    $bugnet->trace( 'twitchpresssyncuser',__LINE__,__FUNCTION__,__FILE__,true,sprintf( __( 'User %s has cancelled their subscription to the main channel.', 'twitchpress' ), $user_id ));
                    
                    // Remove the sub plan value to ensure there is no mistake when it comes to user access.
                    delete_user_meta( $user_id, 'twitchpress_sub_plan_' . $channel_id );  
                    delete_user_meta( $user_id, 'twitchpress_sub_plan_name_' . $channel_id );  
                    
                    do_action( 'twitchpress_sync_discontinued_twitch_subscriber', $user_id, $channel_id );

                    return;
                }       

                // Arriving here means no active Twitch sub and no local history of a sub.
                do_action( 'twitchpress_sync_never_a_twitch_subscriber', $user_id, $channel_id );

                return;
            }
            elseif( isset( $twitch_sub_response['sub_plan'] ) )
            {
                // The visitor is a subscriber to the main channel. 
                // The sub status is boolean only.
                update_user_meta( $user_id, 'twitchpress_substatus_mainchannel', true );
                
                // Actions should rely on the twitchpress_substatus_mainchannel option only as others are updated later.
                do_action( 'twitchpress_sync_user_subscribes_to_channel', array( $users_twitch_id, $channel_id ) );    
       
                $bugnet->trace( 'twitchpresssyncuser',__LINE__,__FUNCTION__,__FILE__,false,sprintf( __( 'User %s is a subscriber. TwitchPress Sync will now attempt to establish the subscription plan.', 'twitchpress' ), $user_id ));
                                    
                if( !twitchpress_is_valid_sub_plan( $local_sub_plan ) ) 
                {      
                    // User is being registered as a Twitch sub for the first time.
                    update_user_meta( $user_id, 'twitchpress_sub_plan_' . $channel_id, $twitch_sub_response['sub_plan'] );
                    update_user_meta( $user_id, 'twitchpress_sub_plan_name_' . $channel_id, $twitch_sub_response['sub_plan_name'] );
                    
                    $bugnet->trace( 'twitchpresssyncuser',__LINE__,__FUNCTION__,__FILE__,false,sprintf( __( 'User %s is now subscribing to the sites main channel and they selected the %s plan.', 'twitchpress' ), $user_id, $plan ));
                    
                    do_action( 'twitchpress_sync_new_twitch_subscriber', $user_id, $channel_id, $twitch_sub_response['sub_plan'] );                
                
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
                        
                        $bugnet->trace( 'twitchpresssyncuser',__LINE__,__FUNCTION__,__FILE__,false,sprintf( __( 'User %s has changed their sub plan from %s to %s.', 'twitchpress' ), $user_id, $local_sub_plan, $twitch_sub_response['sub_plan'] ) );                        
                    }
                    else
                    {          
                        // User is subscribing to the same plan since last sync. 
                        $bugnet->trace( 'twitchpresssyncuser',__LINE__,__FUNCTION__,__FILE__,false,sprintf( __( 'User %s has not changed their sub plan which is %s.', 'twitchpress' ), $user_id, $local_sub_plan ));                        
                    }
 
                    do_action( 'twitchpress_sync_continuing_twitch_subscriber', $user_id, $channel_id );

                    return;
                } 
            }      

            // Trace + WP_Error in one. 
            return $bugnet->trace( 'twitchpresssyncuser',__LINE__,__FUNCTION__,__FILE__,false,sprintf( __( 'Bad state on subscription sync request. Twitch returned unknown value. Report!', 'twitchpress' ), $user_id ),array(),true);        
        }                                   
    
        /**
        * Adds subscription information to user profile: /wp-admin/profile.php 
        * 
        * @param mixed $user
        * 
        * @version 1.0
        */
        public function twitch_subscription_status_show( $user ) {
            ?>
            <h2><?php _e('Twitch Details','twitchpress-sync') ?></h2>
            <p><?php _e('This information is being added by the TwitchPress system.','twitchpress-sync') ?></p>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th>
                            <label for="something"><?php _e( 'Twitch ID', 'twitchpress-sync'); ?></label>
                        </th>
                        <td>
                            <?php echo get_user_meta( $user->ID, 'twitchpress_twitch_id', true ); ?>
                        </td>
                    </tr>                
                    <tr>
                        <th>
                            <label for="something"><?php _e( 'Subscription Status', 'twitchpress-sync'); ?></label>
                        </th>
                        <td>
                            <?php $this->display_users_subscription_status( $user->ID ); ?>
                        </td>
                    </tr>                                        
                    <tr>
                        <th>
                            <label for="something"><?php _e( 'Subscription Name', 'twitchpress-sync'); ?></label>
                        </th>
                        <td>
                            <?php $this->display_users_subscription_plan_name( $user->ID ); ?>
                        </td>
                    </tr>                    
                    <tr>
                        <th>
                            <label for="something"><?php _e( 'Subscription Plan', 'twitchpress-sync'); ?></label>
                        </th>
                        <td>
                            <?php $this->display_users_subscription_plan( $user->ID ); ?>
                        </td>
                    </tr>                    
                    <tr>
                        <th>
                            <label for="something"><?php _e( 'Update Date', 'twitchpress-sync'); ?></label>
                        </th>
                        <td>                
                            <?php $this->display_users_last_twitch_to_wp_sync_date( $user->ID ); ?>
                        </td>
                    </tr>                    
                    <tr>
                        <th>
                            <label for="something"><?php _e( 'Update Time', 'twitchpress-sync'); ?></label>
                        </th>
                        <td>                
                            <?php $this->display_users_last_twitch_to_wp_sync_date( $user->ID, true ); ?>
                        </td>
                    </tr>    
                    <tr>
                        <th>
                            <label for="something"><?php _e( 'Twitch oAuth2 Status', 'twitchpress-sync'); ?></label>
                        </th>
                        <td>                
                            <?php $this->display_users_twitch_authorisation_status( $user->ID ); ?>
                        </td>
                    </tr>   
                    <tr>
                        <th>
                            <label for="something"><?php _e( 'Code', 'twitchpress-sync'); ?></label>
                        </th>
                        <td>                
                            <?php if( get_user_meta( $user->ID, 'twitchpress_code', true ) ) { _e( 'Code Set', 'twitchpress-sync' ); } ?>
                        </td>
                    </tr>   
                    <tr>
                        <th>
                            <label for="something"><?php _e( 'Token', 'twitchpress-sync'); ?></label>
                        </th>
                        <td>                
                            <?php if( get_user_meta( $user->ID, 'twitchpress_token', true ) ) { _e( 'Token Is Saved', 'twitchpress-sync' ); }else{ _e( 'No User Token', 'twitchpress-sync' ); } ?>
                        </td>
                    </tr>   
                    <tr>
                        <th>
                            <label for="something"><?php _e( 'Twitch Tools', 'twitchpress-sync'); ?></label>
                        </th>
                        <td>
                            <?php 
                            $nonce = wp_create_nonce( 'tool_action' );
                            
                            $profile_url = admin_url( 'profile.php?_wpnonce=' . $nonce . '&toolname=tool_user_sync_twitch_sub_data' );    

                            echo '<a href="' . $profile_url . '" class="button button-primary">' . __( 'Update Twitch Subscription', 'twitchpress' ) . '</a></p>';                        
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php             
        }
        
        /**
        * Displays fields on wp-admin/user-edit.php?user_id=1
        * 
        * @param mixed $user
        * 
        * @version 1.2
        */
        public function twitch_subscription_status_edit( $user ) {
            ?>
            <h2><?php _e('Twitch Information','twitchpress-sync') ?></h2>
            <p><?php _e('This information is being displayed by TwitchPress.','twitchpress-sync') ?></p>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th>
                            <label for="something"><?php _e( 'Twitch ID', 'twitchpress-sync'); ?></label>
                        </th>
                        <td>
                            <?php echo get_user_meta( $user->ID, 'twitchpress_twitch_id', true ); ?>
                        </td>
                    </tr>                
                    <tr>
                        <th>
                            <label for="something"><?php _e( 'Subscription Status', 'twitchpress-sync'); ?></label>
                        </th>
                        <td>
                            <?php $this->display_users_subscription_status( $user->ID ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="something"><?php _e( 'Subscription Name', 'twitchpress-sync'); ?></label>
                        </th>
                        <td>
                            <?php $this->display_users_subscription_plan_name( $user->ID ); ?>
                        </td>
                    </tr>                                        
                    <tr>
                        <th>
                            <label for="something"><?php _e( 'Subscription Plan', 'twitchpress-sync'); ?></label>
                        </th>
                        <td>
                            <?php $this->display_users_subscription_plan( $user->ID ); ?>
                        </td>
                    </tr>                    
                    <tr>
                        <th>
                            <label for="something"><?php _e( 'Update Date', 'twitchpress-sync'); ?></label>
                        </th>
                        <td>                
                            <?php $this->display_users_last_twitch_to_wp_sync_date( $user->ID ); ?>
                        </td>
                    </tr>                    
                    <tr>
                        <th>
                            <label for="something"><?php _e( 'Update Time', 'twitchpress-sync'); ?></label>
                        </th>
                        <td>                
                            <?php $this->display_users_last_twitch_to_wp_sync_date( $user->ID, true ); ?>
                        </td>
                    </tr>   
                    <tr>
                        <th>
                            <label for="something"><?php _e( 'Twitch oAuth2 Status', 'twitchpress-sync'); ?></label>
                        </th>
                        <td>                
                            <?php $this->display_users_twitch_authorisation_status( $user->ID ); ?>
                        </td>
                    </tr>   
                    <tr>
                        <th>
                            <label for="something"><?php _e( 'Code', 'twitchpress-sync'); ?></label>
                        </th>
                        <td>                
                            <?php echo get_user_meta( $user->ID, 'twitchpress_code', true ); ?>
                        </td>
                    </tr>   
                    <tr>
                        <th>
                            <label for="something"><?php _e( 'Token', 'twitchpress-sync'); ?></label>
                        </th>
                        <td>                
                            <?php if( get_user_meta( $user->ID, 'twitchpress_token', true ) ) { _e( 'Token Is Saved', 'twitchpress-sync' ); }else{ _e( 'No User Token', 'twitchpress-sync' ); } ?>
                        </td>
                    </tr>
                    
                </tbody>
            </table>
            <?php  
            do_action( 'twtichpress_sync_user_profile_section' );         
        }
        
        /**
        * Calls $this->sync_user() 
        * 
        * Hooked by personal_options_update() and edit_user_profile_update()
        * 
        * @uses sync_user
        * @param mixed $user_id
        */
        public function twitch_subscription_status_save( $user_id ) {
            $this->sync_user( $user_id, false, true );   
        }
                
        /**
        * Outputs giving users scription status for the main channel. 
        * 
        * @param mixed $user_id
        * 
        * @version 2.0
        */
        public function display_users_subscription_status( $user_id ) {
            $output = '';

            $status = get_user_meta( $user_id, 'twitchpress_substatus_mainchannel', true );
            
            if( $status == true )
            {
                $output = __( 'Subscribed', 'twitchpress' );                
            }
            else
            {
                $output = __( 'Not Subscribed', 'twitchpress' );
            }
            
            echo esc_html( $output );    
        }
        
        /**
        * Outputs giving users scription plan for the main channel. 
        * 
        * @param mixed $user_id
        * 
        * @version 1.0
        */
        public function display_users_subscription_plan( $user_id ) {
            $output = '';
            $channel_id = twitchpress_get_main_channels_twitchid();
       
            $plan = get_user_meta( $user_id, 'twitchpress_sub_plan_' . $channel_id, true );
            
            if( $plan !== '' && is_string( $plan ) )
            {
                $output = $plan;                
            }
            else
            {
                $output = __( 'None', 'twitchpress' );
            }
            
            echo esc_html( $output );    
        }  
              
        /**
        * Outputs giving users scription package name for the main channel. 
        * 
        * @param mixed $user_id
        * 
        * @version 1.0
        */
        public function display_users_subscription_plan_name( $user_id ) {
            $output = '';
            $channel_id = twitchpress_get_main_channels_twitchid();
       
            $plan = get_user_meta( $user_id, 'twitchpress_sub_plan_name_' . $channel_id, true );
            
            if( $plan !== '' && is_string( $plan ) )
            {
                $output = $plan;                
            }
            else
            {
                $output = __( 'None', 'twitchpress' );
            }
            
            echo esc_html( $output );    
        }
        
        /**
        * Outputs the giving users last sync date and time. 
        * 
        * @param mixed $user_id
        * 
        * @version 1.0
        */
        public function display_users_last_twitch_to_wp_sync_date( $user_id, $ago = false ) {
            $output = __( 'Waiting - Please Click Update', 'twitchpress-sync' );
            
            $time = get_user_meta( $user_id, 'twitchpress_sync_time', true );
            
            if( !$time ) 
            { 
                $output = __( 'Never Updated - Please Click Update', 'twitchpress-sync' ); 
            }
            else
            {   
                if( $ago ) 
                {   
                    $output = human_time_diff( $time, time() );
                }
                else
                {
                    $output = date( 'F j, Y g:i a', $time );
                }
            }
            
            echo $output;
        }                   
        
        /**
        * Outputs use friendly status of twitch authorisation. 
        *         
        * @param mixed $user_id
        * 
        * @version 1.2
        */
        public function display_users_twitch_authorisation_status( $user_id ) {

            $code = get_user_meta( $user_id, 'twitchpress_code', true );
            $token = get_user_meta( $user_id, 'twitchpress_token', true );
            
            if( !$code && !$token)
            {
                echo __( 'No Twitch Authorisation Setup', 'twitchpress-sync' );
                return;
            }
            elseif( !$code )
            {
                echo __( 'No Code', 'twitchpress-sync' );
                return;
            }
            else
            {   
                echo __( 'Ready', 'twitchpress-sync' );
                return;
                
                /*   TODO: If scopes allow it, make a call and test the token and code. 
                // Make a call using users token and code, confirming validity.                 
                $kraken = new TWITCHPRESS_Twitch_API_Calls();
                $sub_call_result = $kraken->chat_generateToken( $token, $code );
                if( !$sub_call_result ) 
                {
                    echo __( 'New Token Required - Please logout then login using Twitch', 'twitchpress-sync' );
                    return;
                }
                */
            }

        }
                            
    }
    
endif;    

if( !function_exists( 'TwitchPress_Sync_Ext' ) ) {

    function TwitchPress_Sync_Ext() {        
        return TwitchPress_Sync::instance();
    }

    // Global for backwards compatibility.
    $GLOBALS['twitchpress-sync'] = TwitchPress_Sync_Ext(); 
}

register_activation_hook( __FILE__, array( 'TwitchPress_Sync', 'install' ) );
register_deactivation_hook( __FILE__, array( 'TwitchPress_Sync', 'deactivate' ) );
