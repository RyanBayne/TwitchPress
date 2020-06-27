<?php
/**
* TwitchPress General Settings
*
* @author Ryan Bayne
* @category settings
* @package TwitchPress/Settings/General
* @version 1.0
*/

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' ); 

if ( ! class_exists ( 'TwitchPress_Settings_General' ) ) :

class TwitchPress_Settings_General extends TwitchPress_Settings_Page {

    private $sections_array = array ();
 
    /**
    * Constructor
    * 
    * @version 1.0  
    */
    public function __construct()  {

        $this->id  = 'general'; 
        $this->label = __( 'General', 'twitchpress' );

        add_filter( 'twitchpress_settings_tabs_array',        array( $this, 'add_settings_page' ), 20 );
        add_action( 'twitchpress_settings_' . $this->id,      array( $this, 'output' ) );
        add_action( 'twitchpress_settings_save_' . $this->id, array( $this, 'save' ) );
        add_action( 'twitchpress_sections_' . $this->id,      array( $this, 'output_sections' ) );

    }
 
    /**
    * Get sections.
    * 
    * @return array
    * 
    * @version 1.0
    */
    public function get_sections() {
        
        // Add more sections to the settings tab.
        $this->sections_array = array(
        
            'default'   => __( 'General', 'twitchpress' ), 
            'removal'   => __( 'Plugin Removal', 'twitchpress' ),
            'advanced'  => __( 'Advanced', 'twitchpress' ),
            'systems'   => __( 'Systems', 'twitchpress' ),

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
     * 
     * @version 1.2
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
     * @version 1.0
     */
    public function get_settings( $current_section = 'default' ) {
        $settings = array(); 
        
        if ( 'default' == $current_section ) {

            $settings = apply_filters( 'twitchpress_general_settings', array(

                array(
                    'title' => __( 'General Settings', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'You can support development by opting into the improvement program. It does not send sensitive data. The plugin can also request feedback occasionally, this is rare to avoid harassing you or users. Data collected is send to Ryan Bayne.', 'twitchpress' ),
                    'id'     => 'generalsettings'
                ),

                array(
                    'desc'            => __( 'Send Usage Data', 'twitchpress' ),
                    'id'              => 'twitchpress_feedback_data',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'Allow Feedback Prompts', 'twitchpress' ),
                    'id'              => 'twitchpress_feedback_prompt',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                                    
                array(
                    'type'     => 'sectionend',
                    'id'     => 'generalsettings'
                )

            ));
            
        // Domain to Twitch API permission Options.
        } elseif( 'removal' == $current_section ) {
            
            $settings = apply_filters( 'twitchpress_general_removal_settings', array(
 
                array(
                    'title' => __( 'Plugin Removal Settings', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'What should the TwitchPress core plugin remove when being deleted?', 'twitchpress' ),
                    'id'     => 'pluginremovalsettings',
                ),
            
                array(
                    'desc'            => __( 'Delete Options', 'twitchpress' ),
                    'id'              => 'twitchpress_remove_options',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),    

                array(
                    'desc'            => __( 'Delete Database Tables', 'twitchpress' ),
                    'id'              => 'twitchpress_remove_database_tables',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),        
            
                array(
                    'desc'            => __( 'Delete User Data', 'twitchpress' ),
                    'id'              => 'twitchpress_remove_user_data',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),    
            
                array(
                    'desc'            => __( 'Delete Media', 'twitchpress' ),
                    'id'              => 'twitchpress_remove_media',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),    
                
                array(
                    'type'     => 'sectionend',
                    'id'     => 'pluginremovalsettings'
                ),

            ));
        
         // Advanced settings for developers only...
        } elseif( 'advanced' == $current_section ) {
            
            $settings = apply_filters( 'twitchpress_general_advanced_settings', array(
 
                array(
                    'title' => __( 'Advanced Settings', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'Use with care. Some settings are meant for development environments (not live sites).', 'twitchpress' ),
                    'id'     => 'advancedsettings',
                ),
            
                array(
                    'desc'            => __( 'Display Errors', 'twitchpress' ),
                    'id'              => 'twitchpress_displayerrors',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),  
                          
                array(
                    'desc'            => __( 'Activate Redirect Tracking', 'twitchpress' ),
                    'id'              => 'twitchpress_redirect_tracking_switch',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),
                                        
                array(
                    'type'     => 'sectionend',
                    'id'     => 'advancedsettings'
                ),

            ));

        } elseif( 'systems' == $current_section ) {
            
            $settings = apply_filters( 'twitchpress_general_systems_settings', array(
 
                array(
                    'title' => __( 'System Switches', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'You can enable/disable multiple systems at once here (with care). Please visit system specific Settings views that become available on activation and run first-time installation.', 'twitchpress' ),
                    'id'     => 'systemsettings',
                ),
            
                array(
                    'desc'            => __( 'Giveaways and Raffle System', 'twitchpress' ),
                    'id'              => 'twitchpress_giveaways_switch',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),  
                            
                array(
                    'desc'            => __( 'Perks System', 'twitchpress' ),
                    'id'              => 'twitchpress_perks_switch',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),  
                                            
                array(
                    'type'     => 'sectionend',
                    'id'     => 'systemsettings'
                ),

            ));

        }
  
        return apply_filters( 'twitchpress_get_settings_' . $this->id, $settings, $current_section );
    }
}
    
endif;

return new TwitchPress_Settings_General();
          
     

 
    