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
        
            //'login'              => __( 'Login', 'twitchpress' ),
            //'registration'  => __( 'Registration', 'twitchpress' ),

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
     */
    public function get_settings( $current_section = '' ) {
        
        $sections_array = self::get_sections();
        
        $display_section = null;
        
        $settings = array();
                     
        if( !$current_section ) { 
            $display_section = array_keys($sections_array)[0];
        } else {
            $display_section = $current_section;
        }

        if ( 'login' == $display_section ) {

            $settings = apply_filters( 'twitchpress_user_login_settings', array(
            
                array(
                    'title' => __( 'Login Settings', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'This is the form for entering your main developer application. When you submit the form for the first time, you will go through the oAuth2 procedure. If a code already exists and it is still valid, the procedure will be shorter. When you arrive back on this screen, the token field should be populated and you should be able to make calls to Kraken.', 'twitchpress' ),
                    'id'     => 'usersloginsettings'
                ),

                array(
                    'desc'            => __( 'Checkbox One', 'twitchpress' ),
                    'id'              => 'loginsettingscheckbox1',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                                    
                array(
                    'type'     => 'sectionend',
                    'id'     => 'usersloginsettings'
                )

            ));
            
        // Domain to Twitch API permission Options
        } elseif( 'registration' == $display_section ) {
            $settings = apply_filters( 'twitchpress_user_registration_settings', array(
 
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
        }
  
        return apply_filters( 'twitchpress_get_settings_' . $this->id, $settings, $current_section );
    }
}
    
endif;

return new TwitchPress_Settings_Users();
