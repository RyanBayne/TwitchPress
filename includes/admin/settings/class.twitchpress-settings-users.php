<?php
/**
* TwitchPress User Settings
* 
* @author Ryan Bayne
* @category Users
* @package TwitchPress/Settings/Users
* @version 1.0
*/

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' );

if ( ! class_exists( 'TwitchPress_Settings_Users' ) ) :

class TwitchPress_Settings_Users extends TwitchPress_Settings_Page {
    
    private $sections_array = array();
    
    /**
    * Constructor
    * 
    * @version 1.0    
    */
    public function __construct() {

        $this->id    = 'users';
        $this->label = __( 'Users', 'twitchpress' );

        add_filter( 'twitchpress_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
        add_action( 'twitchpress_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'twitchpress_settings_save_' . $this->id, array( $this, 'save' ) );
        add_action( 'twitchpress_sections_' . $this->id, array( $this, 'output_sections' ) );
        
    }
    
    /**
    * Get sections.
    * 
    * @return array
    * 
    * @version 1.0
    */
    public function get_sections() {
        
        // Can leave this array empty and the first extensions first section...
        // will become the default view. Only use this if core plugin
        // needs settings on this tab. 
        $this->sections_array = array(
            'default'              => __( 'Service Switches', 'twitchpress' ),
            'loginandregistration' => __( 'Login and Registration', 'twitchpress' ),
        );
        
        return apply_filters( 'twitchpress_get_sections_' . $this->id, $this->sections_array );
    }
    
    /**
    * Output the settings.
    */
    public function output() {
        global $current_section;
        $settings = $this->get_settings( $current_section );
        TwitchPress_Admin_Settings::output_fields( $settings );
    }
    
    /**
     * Save settings.
     */
    public function save() {
        global $current_section;
        $settings = $this->get_settings( $current_section );
        TwitchPress_Admin_Settings::save_fields( $settings );
    }  
    
    /**
     * Get settings array.
     *
     * @return array
     * 
     * @version 2.0
     */
    public function get_settings( $current_section = '' ) {
        $settings = array();
        
        // Switch public services on and off easily/quickly.
        if ( 'default' == $current_section ) {
            
            $settings = apply_filters( 'twitchpress_user_publicserviceswitches_settings', array(
            
                array(
                    'title' => __( 'Service Switches', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'Main controls for public services. Take great care if your service is live and busy as each switch can cause disruption to your subscribers. These settings do not affect administrator access or automated services setup by administrators.', 'twitchpress' ),
                    'id'     => 'publicserviceswitches_settings'
                ),
               
                array(
                    'title'         => __( 'Channel Profiles', 'twitchpress' ),
                    'desc'          => __( 'Take Ownership', 'twitchpress' ),
                    'id'            => 'twitchpress_serviceswitch_channels_takeownership',
                    'type'          => 'checkbox',
                    'default'       => 'no',
                    'checkboxgroup' => 'start',
                    'autoload'      => false,
                ),
                
                array(
                    'desc'            => __( 'Edit Channel Post Content', 'twitchpress' ),
                    'id'              => 'twitchpress_serviceswitch_channels_editcontent',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                                
                array(
                    'desc'          => __( 'Control Chat Display', 'twitchpress' ),
                    'id'            => 'twitchpress_serviceswitch_channels_controlchatdisplay',
                    'type'          => 'checkbox',
                    'default'       => 'yes',
                    'checkboxgroup' => 'end',
                    'autoload'      => false,
                ),
                                                                                   
                array(
                    'type'     => 'sectionend',
                    'id'     => 'publicserviceswitches_settings'
                ),     
            
            ));
            
        // Pair public services with roles and capabilities.
        } elseif( 'publicservicepermissions' == $current_section ) {
            
            return;// REMOVE WHEN SECTION READY
                
            $settings = apply_filters( 'twitchpress_user_publicservicepermissions_settings', array(
 
                array(
                    'title' => __( 'Registraton Settings', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => 'The.',
                    'id'     => 'usersregisrationsettings',
                ),
            
                array(
                    'desc'            => __( 'Checkbox Two', 'twitchpress' ),
                    'id'              => 'loginsettingscheckbox2',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ), 
                
                array(
                    'type'     => 'sectionend',
                    'id'     => 'usersregisrationsettings'
                ),

            ));
        } elseif ( 'loginandregistration' == $current_section ) {
            
            $settings = apply_filters( 'twitchpress_loginextension_login_settings', array(
 
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
                    'title'           => __( 'No Redirects', 'twitchpress-login' ),
                    'desc'            => __( 'Do not redirect on login success.', 'twitchpress-login' ),
                    'id'              => 'twitchpress_login_prevent_redirect',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'title'           => __( 'Redirect All Logins', 'twitchpress-login' ),
                    'desc'            => __( 'Redirect none Twitch oAuth logins (admin excluded).', 'twitchpress-login' ),
                    'id'              => 'twitchpress_login_redirect_all',
                    'default'         => 'yes',
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
    
        return apply_filters( 'twitchpress_get_settings_' . $this->id, $settings, $current_section );
    }
}
    
endif;

return new TwitchPress_Settings_Users();
