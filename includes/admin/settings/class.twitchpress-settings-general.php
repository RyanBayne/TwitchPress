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
        
            'general'       => __( 'General', 'twitchpress' ),
            'removal'  => __( 'Plugin Removal', 'twitchpress' ),

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

        // Establish a section to display rather than have a default.
        $sections_array = self::get_sections(); 
        $display_section = null;
        $settings = array();
                     
        if( !$current_section ) { 
            $display_section = array_keys($sections_array)[0];
        } else {
            $display_section = $current_section;
        }

        if ( 'general' == $display_section ) {

            $settings = apply_filters( 'twitchpress_general_settings', array(

                array(
                    'title' => __( 'Improvement Program', 'twitchpress' ),
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
            
        // Domain to Twitch API permission Options
        } elseif( 'removal' == $display_section ) {
            
            $settings = apply_filters( 'twitchpress_general_removal_settings', array(
 
                array(
                    'title' => __( 'Plugin Removal Settings', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'By default most plugins will not remove everything when the plugin is deleted. If you need all options, custom tables, custom files and other data to be removed when you delete the plugin. Then check the box below, else please leave this option alone.', 'twitchpress' ),
                    'id'     => 'pluginremovalsettings',
                ),
            
                array(
                    'desc'            => __( 'Removal Everything on Plugin Deletion', 'twitchpress' ),
                    'id'              => 'twitchpress_removeall',
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
        }
  
        return apply_filters( 'twitchpress_get_settings_' . $this->id, $settings, $current_section );
    }
}
    
endif;

return new TwitchPress_Settings_General();
          
     

 
    