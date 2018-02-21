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
        
            'default' => __( 'Service Switches', 'twitchpress' ),
            //'publicservicepermissions'  => __( 'Service Permissions', 'twitchpress' ),

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
                    'title'         => __( 'Channel Feed Services', 'twitchpress' ),
                    'desc'          => __( 'Post to Feed', 'twitchpress' ),
                    'id'            => 'twitchpress_serviceswitch_feeds_posttofeed',
                    'type'          => 'checkbox',
                    'default'       => 'no',
                    'checkboxgroup' => 'start',
                    'autoload'      => false,
                ),

                array(
                    'desc'            => __( 'Allow Scheduled Posts', 'twitchpress' ),
                    'id'              => 'twitchpress_serviceswitch_feeds_scheduledposts',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                                
                array(
                    'desc'          => __( 'Customize Prepend/Append Values', 'twitchpress' ),
                    'id'            => 'twitchpress_serviceswitch_feeds_prependappend',
                    'type'          => 'checkbox',
                    'default'       => 'yes',
                    'checkboxgroup' => 'end',
                    'autoload'      => false,
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
        }
  
        return apply_filters( 'twitchpress_get_settings_' . $this->id, $settings, $current_section );
    }
}
    
endif;

return new TwitchPress_Settings_Users();
