<?php 
/*
Plugin Name: TwitchPress Login Extension
Version: 1.3.4
Plugin URI: http://twitchpress.wordpress.com
Description: Social login and register on WordPress using Twitch.
Author: Ryan Bayne             
Author URI: http://ryanbayne.wordpress.com
Text Domain: twitchpress-login
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
 * Required minimums and constants
 */
define( 'TWITCHPRESS_LOGIN_VERSION', '1.3.4' );
define( 'TWITCHPRESS_LOGIN_MIN_PHP_VER', '5.6.0' );
define( 'TWITCHPRESS_LOGIN_MIN_TP_VER', '1.6.1' );
define( 'TWITCHPRESS_LOGIN_MAIN_FILE', __FILE__ );
define( 'TWITCHPRESS_LOGIN_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
define( 'TWITCHPRESS_LOGIN_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

if ( ! class_exists( 'TwitchPress_Login' ) ) :

    class TwitchPress_Login {
        /**
         * @var Singleton
         */
        private static $instance;        
        
        public $twitchpress_login_errors = array();
        
        /**
        * To avoid duplicating entire procedures but allow them to
        * output messages in different ways we set this variable.
        * 
        * Here are the optional values...
        * 1. wp-login.php   - messages will display as proper login errors. 
        * 2. shortcodewpdie - wp_die() will be used to output first encountered error.
        * 3. customtemplate - template with connect to twitch button and styled messages.
        * 
        * @var string
        */
        private $output_type = null;
        
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
        
        public static function install() {
            
        }
        
        public static function deactivate() {
            
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

            // Include Classes
            // i.e. require_once( plugin_basename( 'classes/class-wc-connect-logger.php' ) );
            
            // Create Class Objects
            // i.e. $logger                = new WC_Connect_Logger( new WC_Logger() );
            
            // Set Class Objects In Singleton
            // i.e. $this->set_logger( $logger );

            // SPL Autoloader Class
            include_once( 'includes/class.twitchpress-login-autoloader.php' );
            include_once( 'includes/class.twitchpress-custom-login-notices.php' );       
            include_once( 'includes/function.twitchpress-login-core.php' ); 
            
            // When doing admin_init load admin side dependencies.             
            add_action( 'admin_init', array( $this, 'load_admin_dependencies' ) );
        }

        public function load_admin_dependencies() {
            include_once( 'includes/class.twitchpress-login-uninstall.php' );     
        }
        
        /**
         * Hook plugin classes into WP/WC core.
         */
        public function attach_hooks() {

            add_action( 'plugins_loaded',        array( $this, 'init_filters' ), 1 );
            add_action( 'wp_enqueue_scripts',    array( $this, 'enqueue_public_css' ), 10 );
            add_action( 'init',                  array( $this, 'twitch_login_public_listener' ), 1 );
            add_action( 'init',                  array( $this, 'redirect_login_page' ), 1 );
            add_action( 'admin_init',            array( $this, 'load_admin_dependencies' ) );  
            
            // WordPress Login Form Approach - Adds button to core login form and processes with full integration.
            add_action( 'login_head',            array( $this, 'hide_login' ) );
            add_action( 'login_enqueue_scripts', array( $this, 'twitchpress_login_styles') );
            add_action( 'login_form',            array( $this, 'twitch_button_above'), 2 );
            add_action( 'login_form',            array( $this, 'twitch_button_below'), 2 );
        
            // Shortcode Approach - Output shortcode, process requires public listener,    
            add_shortcode( apply_filters( "twitchpress_connect_button_filter", 'twitchpress_connect_button' ), array( $this, 'shortcode_connect_button' ) );            
 
            // Filters
            $base = plugin_basename( __FILE__ );

            add_filter( 'login_errors',                            array( $this, 'login_change_errors'), 5, 1 );                                  
            add_filter( 'twitchpress_get_sections_users',          array( $this, 'settings_add_section_users' ) );
            add_filter( 'twitchpress_get_settings_users',          array( $this, 'settings_add_options_users' ) );
            add_filter( 'plugin_action_links_' . $base,            array( $this, 'plugin_action_links' ) ); 
            add_filter( 'twitchpress_update_system_scopes_status', array( $this, 'update_system_scopes_status' ), 2, 1 ); 
          
            do_action( 'twitchpress_login_loaded' );
        }
        
        /**
        * Add scopes information (usually from extensions) to the 
        * system scopes status which is used to tell us what scopes are
        * required for the current system.
        * 
        * @param mixed $new_array
        */
        public function update_system_scopes_status( $filtered_array ) {
            
            $scopes = array();
            
            // Scopes for admin only or main account functionality that is always used. 
            $scopes['admin']['twitchpress-login-extension']['required'] = array( 'user_read' );
            
            // Scopes for admin only or main account features that may not be used.
            $scopes['admin']['twitchpress-login-extension']['optional'] = array(); 
                        
            // Scopes for functionality that is always used. 
            $scopes['public']['twitchpress-login-extension']['required'] = array( 'user_read' );
            
            // Scopes for features that may not be used.
            $scopes['public']['twitchpress-login-extension']['optional'] = array(); 
                        
            return array_merge_recursive( $filtered_array, $scopes );      
        }
        
        /**
         * Define TwitchPress Login Constants.
         * 
         * @version 1.0
         */
        private function define_constants() {
            
            $upload_dir = wp_upload_dir();
            
            // Main (package) constants.
            if ( ! defined( 'TWITCHPRESS_LOGIN_ABSPATH' ) )       { define( 'TWITCHPRESS_LOGIN_ABSPATH', __FILE__ ); }
            if ( ! defined( 'TWITCHPRESS_LOGIN_BASENAME' ) )      { define( 'TWITCHPRESS_LOGIN_BASENAME', plugin_basename( __FILE__ ) ); }
            if ( ! defined( 'TWITCHPRESS_LOGIN_DIR_PATH' ) )      { define( 'TWITCHPRESS_LOGIN_DIR_PATH', plugin_dir_path( __FILE__ ) ); }
            if ( ! defined( 'TWITCHPRESS_LOGIN_PLUGIN_FILE' ) )   { define( 'TWITCHPRESS_LOGIN_PLUGIN_FILE', __FILE__ ); }
            if ( ! defined( 'TWITCHPRESS_SHOW_SETTINGS_USERS' ) ) { define( 'TWITCHPRESS_SHOW_SETTINGS_USERS', true ); }                  
        }  
        
        function enqueue_public_css() {
            // Main public.css file.
            wp_register_style( 'twitchpress-login-extension-public-styles', TwitchPress_Login::plugin_url() . '/assets/css/public.css' );            
            wp_enqueue_style( 'twitchpress-login-extension-public-styles', TwitchPress_Login::plugin_url() . '/assets/css/public.css' ); 
        }

        /**
        * Styles for login page hooked by login_enqueue_scripts
        * 
        * @version 1.0
        */
        public function twitchpress_login_styles() {
            wp_enqueue_script('jquery');
            wp_register_style( 'twitchpress_login_extension_styles', TwitchPress_Login::plugin_url() . '/assets/css/public.css' );
            wp_enqueue_style( 'twitchpress_login_extension_styles' );
        }
                 
        /**
        * Add a new section to the User settings tab.
        * 
        * Called by the twitchpress_get_sections_users() filter.
        * 
        * @param mixed $sections
        * 
        * @version 1.0
        */
        public function settings_add_section_users( $sections ) {  
            global $only_section;
            
            // We use this to apply this extensions settings as the default view...
            // i.e. when the tab is clicked and there is no "section" in URL. 
            if( empty( $sections ) ){ $only_section = true; } else { $only_section = false; }
            
            // Add sections to the User Settings tab. 
            $new_sections = array(
                'loginandregistration' => __( 'Login and Registration', 'twitchpress' ),
            );

            return array_merge( $sections, $new_sections );           
        }          
        
        /**
        * Add options to this extensions own settings section.
        * 
        * @param mixed $settings
        * 
        * @version 1.3
        */
        public function settings_add_options_users( $settings ) {
            global $current_section, $only_section;
            
            $new_settings = array();
            
            // This first section is default if there are no other sections at all.
            if ( 'loginandregistration' == $current_section || !$current_section && $only_section ) {
                
                $new_settings = apply_filters( 'twitchpress_loginextension_login_settings', array(
     
                    array(
                        'title' => __( 'Login', 'twitchpress-login' ),
                        'type'     => 'title',
                        'desc'     => __( 'These settings are offered by the TwitchPress Login Extension.', 'twitchpress-login' ),
                        'id'     => 'loginsettings',
                    ),

                    array(
                        'title'   => __( 'Login Page Type', 'twitchpress-login' ),
                        'desc'    => __( 'What type of login page have you setup?', 'twitchpress-login' ),
                        'id'      => 'twitchpress_login_loginpage_type',
                        'default' => 'both',
                        'type'    => 'radio',
                        'options' => array(
                            'default' => __( 'WP Login Form.', 'twitchpress-login' ),
                            'page'    => __( 'Custom Login Page', 'twitchpress-login' ),
                            'both'    => __( 'Mixed', 'twitchpress-login' ),
                        ),
                        'autoload'        => false,
                        'show_if_checked' => 'option',
                    ),

                    array(
                        'title'   => __( 'Twitch Button Position', 'twitchpress-login' ),
                        'desc'    => __( 'Select button position if using the WordPress login form.', 'twitchpress-login' ),
                        'id'      => 'twitchpress_login_loginpage_position',
                        'default' => 'above',
                        'type'    => 'radio',
                        'options' => array(
                            'above' => __( 'Above.', 'twitchpress-login' ),
                            'below' => __( 'Below', 'twitchpress-login' ),
                        ),
                        'autoload'        => false,
                        'show_if_checked' => 'option',
                    ),
                    
                    array(
                        'title'           => __( 'Display "Connect Using Twitch" Button', 'twitchpress-login' ),
                        'desc'            => __( 'Use Main Login Form', 'twitchpress-login' ),
                        'id'              => 'twitchpress_login_button',
                        'default'         => 'yes',
                        'type'            => 'checkbox',
                        'checkboxgroup'   => '',
                        'show_if_checked' => 'yes',
                        'autoload'        => false,
                    ),
                    
                    array(
                        'title'           => __( 'Require Twitch Login', 'twitchpress-login' ),
                        'desc'            => __( 'Twitch only login. Hides login fields on wp-login.php only.', 'twitchpress-login' ),
                        'id'              => 'twitchpress_login_requiretwitch',
                        'default'         => 'no',
                        'type'            => 'checkbox',
                        'checkboxgroup'   => '',
                        'show_if_checked' => 'yes',
                        'autoload'        => false,
                    ),
                    
                    array(
                        'title'           => __( 'Custom Page Only', 'twitchpress-login' ),
                        'desc'            => __( 'Redirect visitors away from wp-login.php to your custom page.', 'twitchpress-login' ),
                        'id'              => 'twitchpress_login_redirect_to_custom',
                        'default'         => 'no',
                        'type'            => 'checkbox',
                        'checkboxgroup'   => '',
                        'show_if_checked' => 'yes',
                        'autoload'        => false,
                    ),
                    
                    array(
                        'title'    => __( 'Custom Login Page', 'twitchpress-login' ),
                        'desc'     => __( 'Enter the page ID that displays your main login form.', 'twitchpress-login' ),
                        'id'       => 'twitchpress_login_mainform_page_id',
                        'css'      => 'width:75px;',
                        'default'  => '',
                        'type'     => 'text',
                    ),
                                                                   
                    array(
                        'title'    => __( 'Custom Logged-In Page', 'twitchpress-login' ),
                        'desc'     => __( 'Enter the page ID where you visitors to be redirected to once logged in.', 'twitchpress-login' ),
                        'id'       => 'twitchpress_login_loggedin_page_id',
                        'css'      => 'width:75px;',
                        'default'  => '',
                        'type'     => 'text',
                    ),
                    
                    array(
                        'title'    => __( 'Login Button Text', 'twitchpress-login' ),
                        'desc'     => __( 'Enter the text you would like to display on your Twitch button.', 'twitchpress-login' ),
                        'id'       => 'twitchpress_login_button_text',
                        'css'      => 'width:230px;',
                        'default'  => '',
                        'type'     => 'text',
                    ),
                     
                    array(
                        'type'     => 'sectionend',
                        'id'     => 'loginsettings'
                    ),
                    
                    array(
                        'title' => __( 'Registration', 'twitchpress-login' ),
                        'type'     => 'title',
                        'desc'     => __( '', 'twitchpress-login' ),
                        'id'     => 'registrationsettings',
                    ),
                                                            
                    array(
                        'desc'          => __( 'Registration Button: Display a Twitch button on the WordPress registration form.', 'twitchpress-login' ),
                        'id'            => 'twitchpress_registration_button',
                        'default'       => 'yes',
                        'type'          => 'checkbox',
                        'checkboxgroup' => '',
                        'autoload'      => false,
                    ),

                    array(
                        'desc'          => __( 'Force Registration: Force registration by Twitch only and hide WP registration form.', 'twitchpress-login' ),
                        'id'            => 'twitchpress_registration_twitchonly',
                        'default'       => 'no',
                        'type'          => 'checkbox',
                        'checkboxgroup' => '',
                        'autoload'      => false,
                    ),
                    
                    array(
                        'desc'          => __( 'Email Validation: Require a validated email address (validated by user through their Twitch account).', 'twitchpress-login' ),
                        'id'            => 'twitchpress_registration_requirevalidemail',
                        'default'       => 'yes',
                        'type'          => 'checkbox',
                        'checkboxgroup' => '',
                        'autoload'      => false,
                    ),
                  
                    array(
                        'type'     => 'sectionend',
                        'id'     => 'registrationsettings'
                    ),
                    
                    array(
                        'title' => __( 'Automatic Registration', 'twitchpress-login' ),
                        'type'     => 'title',
                        'desc'     => __( 'You can register a new user if the visitor attempts to login using the TwitchPress button provided and their Twitch details do not match an existing WordPress account. Users will be instantly logged in at the end of the procedure.', 'twitchpress-login' ),
                        'id'     => 'automaticregistrationsettings',
                    ), 
                    
                    array(
                        'desc'          => __( 'Register on Login.', 'twitchpress-login' ),
                        'id'            => 'twitchpress_automatic_registration',
                        'default'       => 'no',
                        'type'          => 'checkbox',
                        'checkboxgroup' => '',
                        'autoload'      => false,
                    ),
                  
                    array(
                        'type'     => 'sectionend',
                        'id'     => 'automaticregistrationsettings'
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
        * Change an existing login related error message by code.
        * 
        * Use login_message() to add the HTML for new messages. It's a bit of a hack!
        * 
        * @version 1.2
        */
        public function login_change_errors( $error ) {
            global $errors;

            $login_messages = new TwitchPress_Custom_Login_Messages();

            // This will currently only ever result in a single custom message.
            if( $login_messages->twitchpress_login_messages ) {                
                foreach( $login_messages->twitchpress_login_messages as $key => $error ) {
                    return $error['message'];
                } 
            }
            
            if( $errors ) {            
                $err_codes = $errors->get_error_codes();       
            
                // Invalid username.
                // Default: '<strong>ERROR</strong>: Invalid username. <a href="%s">Lost your password</a>?'
                if ( in_array( 'invalid_username', $err_codes ) ) {
                    $error = '<strong>ERROR</strong>: Invalid username.';
                }

                // Incorrect password.
                // Default: '<strong>ERROR</strong>: The password you entered for the username <strong>%1$s</strong> is incorrect. <a href="%2$s">Lost your password</a>?'
                if ( in_array( 'incorrect_password', $err_codes ) ) {
                    $error = '<strong>ERROR</strong>: The password you entered is incorrect.';
                }
            }
            
            return $error;
        } 
        
        /**
        * Outputs a Connect to Twitch button.
        * 
        * Shortcode tag: twitchpress_login_button
        * 
        * @version 2.0
        */
        public function shortcode_connect_button( $atts ) {
            global $wp, $wp_query;
                 
            if( is_user_logged_in() ) {
                return;
            }
            
            // Generate random number that will be return by Twitch.tv as state value and used to confirm cookie. 
            $random14 = twitchpress_random14();
            
            // Shortcode attributes.
            $atts = shortcode_atts( array(
                    'style'              => 0,
                    'text'               => $this->get_login_button_text(),
                    'loginpageid'        => apply_filters( 'twitchpress_loginext_shortcode_loginpageid', $wp_query->post->ID ),
                    'random14'           => $random14,
                    'successurl'         => null,// URL visitor is sent to on successful login. 
                    'wpmlapplysubdomain' => false,
                    'redirectto'         => null, // No longer used - use successurl. 
                ), $atts, 'twitchpress_connect_button' );
            
            // Do we need to prepend the current language to the success URL to maintain language.
            if( $atts['wpmlapplysubdomain'] == true || $atts['wpmlapplysubdomain'] == 'yes' ) 
            {
                if( function_exists( 'wpml_get_current_language' ) ) 
                {   
                    // Get the current users set language.          
                    $current_language = wpml_get_current_language();
                    
                    // Set the successurl, some backwards compatability offered by the old redirectto value.
                    if( $atts['successurl'] !== null ) 
                    {
                        $atts['successurl'] = 'https://' . $current_language . '.' . $atts['successurl'];
                    }
                    elseif( $atts['redirectto'] !== null  )
                    {
                        $atts['successurl'] = 'https://' . $current_language . '.' . $atts['redirectto'];    
                    }
                }
            }
                                       
            // Load Kraken.
            $kraken = new TWITCHPRESS_Twitch_API();

            // Lets make sure TwitchPress app is setup properly else do not display button/link.
            $is_app_set = $kraken->is_app_set();
            if( !$is_app_set ) {  
                return;
            }
            
            // Load the sites global scopes. 
            $kraken_permitted_scopes = $kraken->get_user_scopes();
            $states_array = array( 'random14'    => $atts['random14'], 
                                   'loginpageid' => $atts['loginpageid'],
                                   'view'        => 'post',
                                   'successurl'  => $atts['successurl'] );     
            
            // Generate the oAuth2 URL to Twitch.tv login.
            $authUrl = $kraken->generate_authorization_url( $kraken_permitted_scopes, $states_array );
            
            return self::connect_button_style_one( $authUrl, $atts['text'] );
        }  
            
        /**
        * Connect to Twitch button originally created for Ultimate Member plugin.
        *
        * @param mixed $authUrl
        * @param mixed $text
        */
        public static function connect_button_style_one( $authUrl, $text ) {      
              
            $button = '';
            $button .= '<div class="twitchpress-connect-button-one">';
            $button .= '<a href="' . $authUrl . '">' . esc_html( $text ) . '</a>';
            $button .= '</div>';
            
            return $button;  
        }     
             
        /**
        * Called by init action hook.
        * 
        * Detect Twitch login request by confirming all required parameters exist to proceed.
        * 
        * We ensure the visitor is back on the original login view before 
        * generating applicable output for that view. 
        *
        * @version 1.7
        */
        public function twitch_login_public_listener() {              
            global $bugnet;
            
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return;
            }
            
            if( defined( 'DOING_CRON' ) && DOING_CRON ) {
                return;    
            }        
            
            if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                return;    
            }
        
            // Ignore $_POST request. 
            if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
                return;
            }

            // Ignore a GET request if no state value is included.  
            if( !isset( $_GET['state'] ) ) { 
                return;
            }
            
            $state_code = $_GET['state'];

            // Start Trace - used to be much earlier until this function was re-written! 
            $bugnet->trace( 'twitchpressloginextensionlistener',__LINE__,__FUNCTION__,__FILE__,false, sprintf( __( 'Started TwitchPress Login Extension listener for state code: %s', 'twitchpress' ), $state_code ) );
                        
            // We require the local state value stored in transient. 
            if( !$transient_state = get_transient( 'twitchpress_oauth_' . $state_code ) ) { 
                // Trace
                $bugnet->trace( 'twitchpressloginextensionlistener',__LINE__,__FUNCTION__,__FILE__,false,__( 'Login listener ended on missing transient.', 'twitchpress' ),array(),true);
                return;
            }             
            
            // This procedure is not meant for anyone already logged into WordPress.
            if( is_user_logged_in() ) { 
                // Trace
                $bugnet->trace( 'twitchpressloginextensionlistener',__LINE__,__FUNCTION__,__FILE__,false,__( 'Login listener ended because user is already logged into WordPress.', 'twitchpress' ),array(),true);
                return;
            }             
            
            // If the login page type is "post" and not default, we need the page ID. 
            if( $transient_state['view'] == 'post' ) { 
                if( !$transient_state['loginpageid'] || !is_numeric( $transient_state['loginpageid'] ) ) { 
                    // Log
                    $bugnet->log_error( __FUNCTION__, sprintf( __( 'Listener ended on missing login page ID.', 'twitchpress' ), $_GET['error'] ), array(), true );
                    // Die
                    wp_die( __( 'After attempting to login using your Twitch account. This website could not establish where you begun the login process and return you there. This is a fault that needs to be reported. Please copy and send this message to the site owner.', 'twitchpress' ), __( 'Twitch Login Page Unknown', 'twitchpress' ) );
                }                           
            }
            
            // Prepare arguments for add_query_var() when redirecting. Cannot assume they are all set.
            $response_arguments = array( 'state' => $state_code );
            if( isset( $_GET['code'] ) ) { $response_arguments['code'] = $_GET['code']; }            
            if( isset( $_GET['scope'] ) ) { $response_arguments['scope'] = $_GET['scope']; }                     

            // If the $login_type = default we can do a view check and redirect early. 
            if( $transient_state['view'] == 'default' && !twitchpress_is_backend_login() ) {                
                $bugnet->trace( 'twitchpressloginextensionlistener',__LINE__,__FUNCTION__,__FILE__,false,__( 'Listener redirecting to default login page.', 'twitchpress' ));
                twitchpress_redirect_tracking( add_query_arg( $_GET, wp_login_url() ), __LINE__, __FUNCTION__ ); 
                exit;             
            }    

            // Did it all go terribly wrong in an even worse way?! 
            if( isset( $_GET['error'] ) ) { 
                // Log
                $bugnet->log_error( __FUNCTION__, sprintf( __( 'oAuth2 failure, returned error: %s', 'twitchpress' ), $_GET['error'] ), array(), true ); 
                // Die
                wp_die( __( 'Twitch.tv returned an error when attempting to login. This could be a temporary issue with the API. Please return to the login page and try again. If this message appears twice please report it.', 'twitchpress' ), __( 'Twitch Returned Error', 'twitchpress' ) );
            }        

            // we need CODE
            if( !isset( $_GET['code'] ) ) {
                // Trace
                $bugnet->trace( 'twitchpressloginextensionlistener',__LINE__,__FUNCTION__,__FILE__,false,__( 'Listener ended on missing $_GET[code].', 'twitchpress' ),array(),true);
                // Die
                wp_die( __( 'Sorry, it appears Twitch.tv returned you without a code. Please try again and report this issue if it happens again.', 'twitchpress' ), __( 'Twitch Login Request Ended', 'twitchpress' ) ); 
            }            
         
            // we need SCOPE
            if( !isset( $_GET['scope'] ) ) {
                // Trace
                $bugnet->trace( 'twitchpressloginextensionlistener',__LINE__,__FUNCTION__,__FILE__,false,__( 'Listener ended on missing $_GET[scope].', 'twitchpress' ),array(),true);
                // Die
                wp_die( __( 'Sorry, it appears Twitch.tv returned you without all the URL values required to complete your login request. Please try again and report this issue if it happens again.', 'twitchpress' ), __( 'Twitch Login Request Ended', 'twitchpress' ) ); 
            }
            
            // We now need to be on the login view, so we can start outputting normal user notices.
            if( $transient_state['view'] == 'post' ) {
                
                // Ensure visitor is on login page for output, we just need to send them to the permalink for the page.
                if( !isset( $_GET['twitchpress_sentto_login'] ) ) {    
                    
                    // Get pages permalink. 
                    $page_permalink = get_post_permalink( $transient_state['loginpageid'] );
                    
                    // Add our value to indicate we have done the redirect once already, avoid it twice. 
                    $page_permalink_plus = add_query_arg( array( 'twitchpress_sentto_login' => 1 ), $page_permalink );
                    
                    // Get current URL query arguments for adding to new URL. 
                    $final_url = add_query_arg( $_GET, $page_permalink_plus );
                    
                    // Log
                    $bugnet->log( __FUNCTION__, sprintf( __( 'Request to login using Twitch continuing. Visitor is being redirected to the login form (should only happen once): %s', 'twitchpress' ), $final_url ), array(), true, false );
                    
                    // Redirect
                    twitchpress_redirect_tracking( $final_url, __LINE__, __FUNCTION__ );
                    exit;
                }    
            } 

            $bugnet->trace( 'twitchpressloginextensionlistener',__LINE__,__FUNCTION__,__FILE__,false,__( 'Listener passed and calling process_login_using_twitch().', 'twitchpress' ),array(),true);

            // The request is Twitch oAuth2 related, lets try and auth visitor.
            $this->process_login_using_twitch( $state_code );
        }
        
        /**
        * Register (if required) then login the visitor if all security
        * checks are passed.
        * 
        * Assumes CODE, SCOPE and STATE exist in GET request.  
        * 
        * @version 2.0
        */
        private function process_login_using_twitch( $state_code ) { 
            global $bugnet;

            $bugnet->trace( 'twitchpressloginextensionlistener',__LINE__,__FUNCTION__,__FILE__,false,__( 'Request to process login has started.', 'twitchpress' ),array(),true);

            // Summon the Kraken! 
            $kraken = new TWITCHPRESS_Twitch_API_Calls();
            
            // Ensure code is ready.
            if( !twitchpress_validate_code( $_GET['code'] ) ) 
            { 
                $this->loginerror( __( 'Invalid Twitch Code'), 
                                   __( 'Your request to login via Twitch has failed because the code return by Twitch appears invalid. Please try again or report the issue.', 'twitchpress-login' ),
                                   null );                
                return;                   
            }
           
            // Generate a token, it is stored as user meta further down.
            $token_array = $kraken->request_user_access_token( $_GET['code'], __FUNCTION__ );   
 
            // Confirm token was returned.  
            if( !twitchpress_was_valid_token_returned( $token_array ) ) {   
                $this->loginerror( __( 'Invalid Token'), 
                                   __( 'Your request to login via Twitch could not be complete because the giving token is invalid.', 'twitchpress-login' ),
                                   null );                                  
                return;                         
            }
            
            // Get the visitors Twitch details.
            $twitch_user = $kraken->getUserObject_Authd( $token_array['access_token'], $_GET['code'] );
            if( is_wp_error( $twitch_user ) || $twitch_user == false ) { 
                $this->loginerror( __( 'Scope Permission Missing: user_read'), 
                                   __( 'Login by Twitch requires access to your email address using the "user_read" permission. This site is not setup to request it. Please report this problem to the site owner.', 'twitchpress-login' ),
                                   null ); 
            }
            
            // ['email] is required. 
            if( !isset( $twitch_user['email'] ) ) {        
                $this->loginerror( __( 'Email Address Missing'), 
                                   __( 'Twitch returned some of your account information but your email address was not included in the data.', 'twitchpress-login' ),
                                   null );                                                    
                return;                        
            }
            
            // Santization, this will happen again with WP core functions but lets take care.
            $twitch_user['email']        = sanitize_email( $twitch_user['email'] );
            $twitch_user['name']         = sanitize_user( $twitch_user['name'], true );
            $twitch_user['display_name'] = sanitize_text_field( $twitch_user['display_name'] );
            
            // ['email_verified] is required and must be bool(true) by default.
            if( 'yes' == get_option( 'twitchpress_registration_requirevalidemail' ) ) 
            {
                if( !isset( $twitch_user['email_verified'] ) || $twitch_user['email_verified'] !== true ) 
                {
                    $this->loginerror( __( 'Email Address Not Verified'), 
                                       __( 'Your request to login via Twitch was refused because your email address has not been verified by Twitch. You will need to verify your email through Twitch and then register on this site.', 'twitchpress-login' ),
                                       null 
                    );  
        
                    return;                                             
                } 
            }
            
            // We can log the user into WordPress if they have an existing account.
            $wp_user = get_user_by( 'email', $twitch_user['email'] );
            
            // If visitor does not exist in WP database by email check for Twitch history using "_id".
            if( $wp_user === false ) 
            {
                $args = array(
                    'meta_key'     => 'twitchpress_twitch_id',
                    'meta_value'   => $twitch_user['_id'],
                    'count_total'  => false,
                    'fields'       => 'all',
                ); 
                
                $get_users_results = get_users( $args ); 
                
                // We will not continue if there are more than one WP account with the same Twitch ID.     
                // This will be a very rare situation I think so we won't get too advanced on how to deal with it, yet! 
                if( isset( $get_users_results[1] ) ) 
                {
                    $this->loginerror( __( 'Duplicate Accounts Detected' ), 
                                       __( 'Welcome back to this site. Your personal Twitch ID has been found linked to two or more accounts but neither of them contain the same email address found in your Twitch account. Please access your preferred account manually. Please also report this matter so we can consider deleting one of your accounts on this site.' ),
                                       null 
                    );      
                        
                    $bugnet->trace( 'twitchpressloginextensionlistener',__LINE__,__FUNCTION__,__FILE__,true,__( 'Login denied. Duplicate account detected.', 'twitchpress' ) );
                                                  
                    return false;                            
                } 
                elseif( isset( $get_users_results[0] ) ) 
                {            
                    // A single user has been found with the Twitch "_id" associated with it.
                    // We will further marry the WP account to Twitch account.
                    update_user_meta( $get_users_results[0]->ID, 'twitchpress_twitch_id',     $twitch_user['_id'] );                    
                    update_user_meta( $get_users_results[0]->ID, 'twitchpress_email',         $twitch_user['email'] );
                    update_user_meta( $get_users_results[0]->ID, 'twitchpress_auth_time',     time() );
                    update_user_meta( $get_users_results[0]->ID, 'twitchpress_code',          sanitize_text_field( $_GET['code'] ) );
                    update_user_meta( $get_users_results[0]->ID, 'twitchpress_token',         $token_array['access_token'] );
                    update_user_meta( $get_users_results[0]->ID, 'twitchpress_token_refresh', $token_array['refresh_token'] );
                      
                    $bugnet->trace( 'twitchpressloginextensionlistener',__LINE__,__FUNCTION__,__FILE__,false,__( 'Login accepted. Existing user found using Twitch ID. Calling session setup procedure.', 'twitchpress' ) );
                                        
                    // Log the user in.
                    self::authenticate_login_by_twitch( $get_users_results[0]->ID, $twitch_user['name'], $state_code  );
                    
                    return;
                } 
            } 
            else 
            {
                // A single user has been found matching the Twitch email address.
                // We will further marry the WP account to Twitch account.
                update_user_meta( $wp_user->data->ID, 'twitchpress_twitch_id', $twitch_user['_id'] );
                update_user_meta( $wp_user->data->ID, 'twitchpress_auth_time', time() );
                update_user_meta( $wp_user->data->ID, 'twitchpress_code', sanitize_text_field( $_GET['code'] ) );
                update_user_meta( $wp_user->data->ID, 'twitchpress_token', $token_array['access_token'] );
                update_user_meta( $wp_user->data->ID, 'twitchpress_token_refresh', $token_array['refresh_token'] );
   
                $bugnet->trace( 'twitchpressloginextensionlistener',__LINE__,__FUNCTION__,__FILE__,false,__( 'Login accepted. Existing user found using Twitch email address. Calling session setup procedure.', 'twitchpress' ) );
    
                self::authenticate_login_by_twitch( $wp_user->data->ID, $wp_user->data->user_login, $state_code );

                return;
            }
            
            // If automatic registration is not on then we do nothing else.
            if( 'yes' !== get_option( 'twitchpress_automatic_registration' ) ) 
            {
                $this->loginerror( __( 'Manual Registration Required'), 
                                   __( 'This site does not allow automatic registration using Twitch. Please go to the registration page and create an account using the same email address as used in your Twitch account.' ),
                                   null );   
                                   
                $bugnet->trace( 'twitchpressloginextensionlistener',__LINE__,__FUNCTION__,__FILE__,true,__( 'No existing account found and automatic registration is off. Procedure ended.', 'twitchpress' ) );
                                                                
                return;                         
            }
            
            // Arriving here means no existing user by Twitch email or Twitch user ID so we create a WP account.
            $one = get_user_by( 'user_login', $twitch_user['name'] );
            $two = get_user_by( 'display_name', $twitch_user['name'] );
            $thr = get_user_by( 'user_nicename', $twitch_user['name'] );
            
            if( is_object( $one ) && !is_wp_error( $one ) || is_object( $two ) && !is_wp_error( $two ) || is_object( $thr ) && !is_wp_error( $thr )  )
            {
                $this->loginerror( __( 'Could Not Create WordPress Account'), 
                                   __( 'There is an existing account with a similar Twitch username to your channel. Is it possible you have already created an account on this website? Please contact administration so we can secure the best public username. We know your Twitch brand is important to you.', 'twitchpress-login' ),
                                   null );
                                   
                $bugnet->trace( 'twitchpressloginextensionlistener',__LINE__,__FUNCTION__,__FILE__,true,__( 'Registration or account pairing required because an existing WP user has a similar login or display name to the Twitch username.', 'twitchpress' ) );
                                            
                return;   
            }
            
            $user_url = 'http://twitch.tv/' . $twitch_user['name'];
            
            $password = wp_generate_password( 12, true );
            
            $new_user = array(
                'user_login'    =>  $twitch_user['name'],
                'display_name'  =>  $twitch_user['display_name'],
                'user_url'      =>  $user_url,
                'user_pass'     =>  $password, 
                'user_email'    =>  $twitch_user['email'] 
            );
          
            $user_id = wp_insert_user( $new_user ) ;

            if ( is_wp_error( $user_id ) ) 
            {
                $error_message_append = '
                <ul>
                    <li>Login: ' . $twitch_user['name'] . '</li>
                    <li>Display Name: ' . $twitch_user['display_name'] . '</li>
                    <li>User URL: ' . $user_url . '</li>
                    <li>Password: ' . $password . '</li>
                    <li>Email Address: ' . $twitch_user['email'] . '</li>
                </ul>
                <p>Please screenshot and report this notice.</p>';
                
                $this->loginerror( __( 'Could Not Create WordPress Account'), 
                                   __( 'TwitchPress attempted to create a new account but failed when using the
                                   following information.' . $error_message_append, 'twitchpress-login' ),
                                   null );  
                                   
                $bugnet->trace( 'twitchpressloginextensionlistener',__LINE__,__FUNCTION__,__FILE__,true,
                    __( 'Failed to create new WordPress user using data returned by Twitch.', 'twitchpress' )
                );
                                                                 
                return false;                        
            }      
                    
            // Store code and token in our new users meta.
            update_user_meta( $user_id, 'twitchpress_twitch_id', $twitch_user['_id'] );
            update_user_meta( $user_id, 'twitchpress_email', $twitch_user['email'] );
            update_user_meta( $user_id, 'twitchpress_auth_time', time() );
            update_user_meta( $user_id, 'twitchpress_code', sanitize_text_field( $_GET['code'] ) );
            update_user_meta( $user_id, 'twitchpress_token', $token_array['access_token'] );
            update_user_meta( $user_id, 'twitchpress_token_refresh', $token_array['refresh_token'] );

            do_action( 'twitchpress_login_inserted_new_user', $user_id );
 
            $bugnet->trace( 'twitchpressloginextensionlistener',__LINE__,__FUNCTION__,__FILE__,false,__( 'A new WordPress user has been created for the visitor. Now calling session setup method to log them in.', 'twitchpress' ));
                            
            self::authenticate_login_by_twitch( $user_id, $twitch_user['name'], $state_code );
        }               
        
        /**
        * Do the authentication part of a login for the current the visitor using the
        * their WordPress user_id. 
        * 
        * Was originally called by the authenticate hook but
        * we do not need to p9rocess WP core login values and so that approach has been
        * removed.
        * 
        * @param mixed $user_id
        * @returns boolean false if authentication rejected else does exit
        * 
        * @version 1.4
        */
        public function authenticate_login_by_twitch( $wp_user_id, $twitch_username, $state_code ) {      
            global $bugnet;

            // This method is only called when Twitch returns a code, scope and state else it shouldn't be called.
            // This method also assumes all security checks done, this line is just a small precaution. 
            if( isset( $_GET['code'] ) || isset( $_GET['scope'] ) || isset( $_GET['state'] ) ) {
                            
                $bugnet->log( __FUNCTION__, sprintf( __( 'Authenticating user with ID: %s', 'twitchpress-login' ), $wp_user_id ) );
                       
                $bugnet->trace( 'twitchpressloginextensionlistener',__LINE__,__FUNCTION__,__FILE__,true,__( 'Now creating auth cookie so that the visitor is logged into WordPress.', 'twitchpress' ));
                           
                // A bit of a hack to tell WordPress which user is the current one. 
                wp_set_current_user( $wp_user_id );
                
                // Set authorization for the current visitor for the now "current_user" account.
                wp_set_auth_cookie( $wp_user_id ); 

                // We need the user_object for the 2nd parameter of the wp_login action.
                $user_object = get_user_by( 'ID', $wp_user_id );
              
                // Do the wp_login action. First parameter [1] is username, second is ALL user data. 
                do_action( 'wp_login', $user_object->user_login, $user_object ); 
                
                // Do our own login_success and possibly redirect user to a custom logged-in page. 
                $this->login_success( $state_code );
                
                return true;    
            }
            
            $bugnet->log( __FUNCTION__, sprintf( __( 'Cannot authenticate user with ID: %s', 'twitchpress-login' ), $wp_user_id ) );
 
            return false;
        }
        
        /**
        * Last login access when the login was a success and auth has been
        * granted and setup by WordPress. 
        * 
        * @version 2.1
        */
        public function login_success( $state_code ) {
            global $bugnet, $current_user;
            
            $bugnet->log( __FUNCTION__, __( 'Login was a success and user will now be redirected.', 'twitchpress-login' ) );

            $transient = get_transient( 'twitchpress_oauth_' . $state_code );

            if( isset( $transient['successurl'] ) && is_string( $transient['successurl'] ) ) 
            {
                // Send user to custom set URL pass through shortcode.
                $bugnet->log( __FUNCTION__, sprintf( __( 'Redirecting user to "successurl": %s', 'twitchpress-login' ), $transient['successurl'] ) ); 
                twitchpress_redirect_tracking( $transient['successurl'], __LINE__, __FUNCTION__, __FILE__ );     
                exit;    
            }
            elseif( isset( $transient['redirectto'] ) && is_string( $transient['redirectto'] ) )
            {
                // Send user to custom set URL pass through shortcode. 
                $bugnet->log( __FUNCTION__, sprintf( __( 'Redirecting user to "redirectto": %s', 'twitchpress-login' ), $transient['successurl'] ) );
                twitchpress_redirect_tracking( $transient['redirectto'], __LINE__, __FUNCTION__, __FILE__ );     
                exit;       
            }
            else 
            {    
                // Check for a successful logged-in page set using page ID.
                $bugnet->log( __FUNCTION__, __( 'No successurl or redirectto url set. Now checking for page ID.', 'twitchpress-login' ) );
                $loggedin_page_id = get_option( 'twitchpress_login_loggedin_page_id', false );
                if( $loggedin_page_id !== false && is_numeric( $loggedin_page_id ) ) 
                {
                    $permalink = get_post_permalink( $loggedin_page_id );
                    
                    $bugnet->log( __FUNCTION__, sprintf( __( 'Page ID found for login success redirect.', 'twitchpress-login' ), $permalink ) );
                    
                    twitchpress_redirect_tracking( $permalink, __LINE__, __FUNCTION__, __FILE__ );
                    exit;                    
                }
                else
                {
                    $bugnet->log_error( __FUNCTION__, __( 'No login-success page setup. Sending user to WP profile view.', 'twitchpress-login' ), array(), true );

                    $profile = get_bloginfo('url') . '/wp-admin/profile.php';       

                    twitchpress_redirect_tracking( $profile, __LINE__, __FUNCTION__, __FILE__ );
                    exit;
                }
            }
        }
        
        /**
        * Use the TwitchPress Custom Login Notices class to generate a 
        * a new notice on the login scree
        * 
        * @param mixed $message
        * 
        * @version 2.0
        */
        function loginerror( $title, $message, $link = null ) {
            $login_messages = new TwitchPress_Custom_Login_Messages();
            $login_messages->add_error( $message );             
        }
                    
        /**
        * Generates notice for a refusal or failure.
        * 
        * @version 1.0
        */
        public static function oauth2_failure() {           
            
            if( !isset( $_GET['error'] ) ) {
                return;
            }

            $message = '<strong>' . __( 'Twitch Refused Request: ', 'twitchpress-login') . '</strong>';
            
            $message .= sprintf( __( 'the %s error was returned.'), $_GET['error'] );            
            
            if( isset( $_GET['description'] ) ) {
                $message .= ' ' . $_GET['description'] . '.';        
            }
            
            $login_notices = new TwitchPress_Custom_Login_Messages();
            $login_notices->add_error( $message );
            unset( $login_notices );
        }

        /**
        * Display the Twitch Login button below the WP login form.
        * 
        * @version 1.0
        */
        public function twitch_button_below() {
            // This is the top (above) position button.
            if( 'below' !== get_option( 'twitchpress_login_loginpage_position' ) ) { return; }
            
            $kraken = new TWITCHPRESS_Twitch_API();
            
            // Ensure Twitch app is setup to avoid pointless API calls.
            $is_app_set = $kraken->is_app_set();
            if( !$is_app_set ) { return; }
                        
            $kraken_permitted_scopes = $kraken->get_user_scopes();

            $states_array = array( 'random14' => twitchpress_random14(), 'view' => 'default' );

            $authUrl = $kraken->generate_authorization_url( $kraken_permitted_scopes, $states_array );
        
            echo "<h3 class='twitchpresslogin-or'>" . __( 'or' , 'twitchpress-login') . "</h3>";
            
            $this->button( $authUrl );
        }
        
        /**
        * Add a Twitch login button to the WordPress default login form. 
        * If the user has not registered it will also register them.
        * 
        * @version 1.5
        */
        public function twitch_button_above() {    
            
            // Ensure this button is activated.                                 
            if( 'yes' !== get_option( 'twitchpress_login_button' ) ) {
                return;
            }
            
            // Ensure user has set the login page type. 
            $type = get_option( 'twitchpress_login_loginpage_type', false );
            if( $type !== 'default' && $type !== 'both' ) {
                return;// Login page type must be "page". 
            }
            
            // This is the top (above) position button.
            if( 'above' !== get_option( 'twitchpress_login_loginpage_position' ) ) { return; }

            // Is auto login active? (sends visitors straight to Twitch oAuth2)
            $do_autologin = false;
            $temp_option_autologin = false;

            // Generate oAuth2 URL.
            $kraken = new TWITCHPRESS_Twitch_API();
            
            // Ensure Twitch app is setup to avoid pointless API calls.
            $is_app_set = $kraken->is_app_set();
            if( !$is_app_set ) {
                return;
            }
                        
            // The visitor will be asked to accept these scopes. 
            $kraken_permitted_scopes = $kraken->get_user_scopes();
   
            // States array is used to process visitor on returning from Twitch.tv.
            // We can use these values later in this function but they are more important to generate_authorization_url()
            $states_array = array( 'random14' => twitchpress_random14(), 'view' => 'default' );
            
            // Generate oAuth2 request URL.
            $authUrl = $kraken->generate_authorization_url( $kraken_permitted_scopes, $states_array );
        
            // Auto-in via Twitch - all traffic going to wp-login.php is wp_redirect() to an oAuth2 URL 
            if ( $temp_option_autologin ) {
                
                // Respect the option unless GET params mean we should remain on login page (e.g. ?loggedout=true)
                if (count($_GET) == (isset($_GET['redirect_to']) ? 1 : 0) 
                                        + (isset($_GET['reauth']) ? 1 : 0) 
                                        + (isset($_GET['action']) && $_GET['action']=='login' ? 1 : 0)) {
                    $do_autologin = true;
                }
                
                if (isset($_POST['log']) && isset($_POST['pwd'])) { // This was a WP username/password login attempt
                    $do_autologin = false;
                }
            }
            
            if ( $do_autologin ) {
                
                if ( !headers_sent() ) {

                    twitchpress_redirect_tracking( $authUrl, __LINE__, __FUNCTION__, __FILE__ );
                    exit;
                    
                } else { ?>
                
                    <p><b><?php printf( __( 'Redirecting to <a href="%s">%s</a>...' , 'twitchpress-login'), $authUrl, __( 'Login via Twitch', 'twitchpress' ) ); ?></b></p>
                    <script type="text/javascript">
                    window.location = "<?php echo $authUrl; ?>";
                    </script>
                    
                <?php 
                }
            }
            
            // Output the top positioned Twitch button.
            $this->button( $authUrl );
            ?>

            <script>
            jQuery(document).ready(function(){
                <?php ob_start(); /* Buffer javascript contents so we can run it through a filter */ ?>
                
                var loginform = jQuery('#loginform,#front-login-form');
                var googlelink = jQuery('div.twitchpresslogin');
                var poweredby = jQuery('div.twitchpresslogin-powered');

                    loginform.prepend("<h3 class='twitchpresslogin-or'><?php esc_html_e( 'or' , 'twitchpress-login'); ?></h3>");

                loginform.prepend(googlelink);

                <?php 
                    $fntxt = ob_get_clean(); 
                    echo apply_filters('twitchpress_login_form_readyjs', $fntxt);
                ?>
            });
            </script>
        
        <?php     
        }
        
        public function button( $authUrl ) {
            ?>
            <div class="twitchpresslogin">
                <a href="<?php echo $authUrl; ?>"><?php echo esc_html( $this->get_login_button_text() ); ?></a>
            </div>        
            <?php     
        }
        
        /**
        * Hides core username and password fields using CSS.
        * 
        * @version 1.0
        */
        public function hide_login() {
            
            if( 'yes' !== get_option( 'twitchpress_login_requiretwitch' ) ) { return; }            
            
            $style = '';

            $style .= '<style type="text/css">';

            $style .= 'body.login div#login form { padding: 20px 24px 18px }';
            $style .= 'body.login div#login div.twitchpress-connect-button-one { margin-bottom: 33px}';
            $style .= 'body.login div#login form#loginform p { display: none }';
            $style .= 'body.login div#login form#loginform p label { display: none }';
            $style .= 'body.login div#login form#loginform .twitchpresslogin-or { display: none }';
            $style .= 'body.login div#login form#loginform p.submit { display: none }';
            
            $style .= '</style>';
   
            /*
            body.login {}
            body.login div#login {}
            body.login div#login h1 {}
            body.login div#login h1 a {}
            body.login div#login form#loginform {}
            body.login div#login form#loginform p {}
            body.login div#login form#loginform p label {}
            body.login div#login form#loginform input {}
            body.login div#login form#loginform input#user_login {}
            body.login div#login form#loginform input#user_pass {}
            body.login div#login form#loginform p.forgetmenot {}
            body.login div#login form#loginform p.forgetmenot input#rememberme {}
            body.login div#login form#loginform p.submit {}
            body.login div#login form#loginform p.submit input#wp-submit {}
            body.login div#login p#nav {}
            body.login div#login p#nav a {}
            body.login div#login p#backtoblog {}
            body.login div#login p#backtoblog a {}
            */ 
 
            $style .= '</style>';

            echo $style; 
        }

        /**
        * Get the text for the public Twitch login button (link styled button and not a form)
        * 
        * @returns string
        * 
        * @version 2.0
        */
        protected function get_login_button_text() {
            $text = get_option( 'twitchpress_login_button_text', __( 'Login with Twitch', 'twitchpress-login' ) );
            if( !$text ) { $text = __( 'Twitch Login', 'twitchpress' ); }
            return apply_filters( 'twitchpress_login_button_text', $text );
        } 

        /**
        * Get the sites login URL with multisite and SSL considered.
        * 
        * @returns string URL with filter available
        * 
        * @version 1.0
        */
        protected function get_login_url() {                                 
            $login_url = wp_login_url();

            if ( is_multisite() ) {
                $login_url = network_site_url('wp-login.php');
            }                      

            if (force_ssl_admin() && strtolower(substr($login_url,0,7)) == 'http://') {
                $login_url = 'https://' . substr( $login_url, 7 );
            }

            return apply_filters( 'twitchpress_login_url', $login_url );
        } 
        
        /**
        * Force redirect default login wp-login.php
        * to a page with login shortcode.
        * 
        * @version 2.0
        */
        public function redirect_login_page() {
            global $bugnet; 
            
            $page_viewed = basename( esc_url( $_SERVER['REQUEST_URI'] ) );
            if ( $page_viewed !== "wp-login.php" ) {
                return;
            } 
                       
            if( 'yes' !== get_option( 'twitchpress_login_redirect_to_custom' ) ) {
                return;                
            }
            
            if( !$custom_login_id = get_option( 'twitchpress_login_mainform_page_id' ) ) {
                return;    
            }
            
            if( !is_numeric( $custom_login_id ) ) {
                $bugnet->log_error( __FUNCTION__, __( 'Your custom login page value is not numeric and must be a page ID to work.', 'twitchpress-login' ), array(), true );
                return;
            }
            
            $permalink = get_post_permalink( $custom_login_id );
            twitchpress_redirect_tracking( $permalink, __LINE__, __FUNCTION__, __FILE__ );
            exit;
        }

        /**
         * Modify the url returned by wp_registration_url().
         *
         * @return string page url with registration shortcode.
         */
        public function register_url_func() {
            if ( isset( $this->db_settings_data['set_registration_url'] ) ) {
                $reg_url = get_permalink( absint( $this->db_settings_data['set_registration_url'] ) );
                return $reg_url;
            }
        }

        /** force redirection of default registration to custom one */
        public function redirect_reg_page() {
            if ( isset( $this->db_settings_data['set_registration_url'] ) ) {

                $reg_url = get_permalink( absint( $this->db_settings_data['set_registration_url'] ) );

                $page_viewed = basename( esc_url( $_SERVER['REQUEST_URI'] ) );

                if ( $page_viewed == "wp-login.php?action=register" && $_SERVER['REQUEST_METHOD'] == 'GET' ) {
                    twitchpress_redirect_tracking( $reg_url, __LINE__, __FUNCTION__, __FILE__ );
                    exit;
                }
            }
        }
        
        /** Force redirection of default registration to the page with custom registration. */
        public function redirect_password_reset_page() {
            if ( isset( $this->db_settings_data['set_lost_password_url'] ) ) {

                $password_reset_url = get_permalink( absint( $this->db_settings_data['set_lost_password_url'] ) );

                $page_viewed = basename( esc_url( $_SERVER['REQUEST_URI'] ) );

                if ( $page_viewed == "wp-login.php?action=lostpassword" && $_SERVER['REQUEST_METHOD'] == 'GET' ) {
                    twitchpress_redirect_tracking( $password_reset_url, __LINE__, __FUNCTION__, __FILE__ );
                    exit;
                }
            }
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

    function TwitchPress_Login_Ext() {        
        return TwitchPress_Login::instance();
    }

    // Global for backwards compatibility.
    $GLOBALS['twitchpress-login'] = TwitchPress_Login_Ext(); 
}

// Activation and Deactivation hooks.
register_activation_hook( __FILE__, array( 'TwitchPress_Login', 'install' ) );
register_deactivation_hook( __FILE__, array( 'TwitchPress_Login', 'deactivate' ) );

