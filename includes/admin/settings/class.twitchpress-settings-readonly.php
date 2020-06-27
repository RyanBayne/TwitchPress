<?php
/**
* TwitchPress Read Only Settings (not shown on interface at all)
*
* This class aids installation and managment of WordPress option values that
* are readonly but we could allow developers to see a view of them for tests.
* 
* @author Ryan Bayne
* @category settings
* @package TwitchPress
* @version 1.0
*/

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' ); 

if ( ! class_exists ( 'TwitchPress_Settings_Readonly' ) ) :

class TwitchPress_Settings_Readonly extends TwitchPress_Settings_Page {

    private $sections_array = array ();
 
    /**
    * Constructor
    * 
    * @version 1.0  
    */
    public function __construct()  {

        $this->id  = 'readonly'; 
        $this->label = __( 'Read Only', 'twitchpress' );

        if( !current_user_can( 'twitchpress_developer' ) ) {
            add_filter( 'twitchpress_settings_tabs_array',        array( $this, 'add_settings_page' ), 20 );
            add_action( 'twitchpress_settings_' . $this->id,      array( $this, 'output' ) );
            add_action( 'twitchpress_settings_save_' . $this->id, array( $this, 'save' ) );
            add_action( 'twitchpress_sections_' . $this->id,      array( $this, 'output_sections' ) );
        }
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
            //'twitchapi'   => __( 'Twitch API', 'twitchpress' ),
            //'advanced'  => __( 'Advanced', 'twitchpress' ),
            //'bugnet'    => __( 'BugNet', 'twitchpress' ),

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

            $settings = apply_filters( 'twitchpress_general_readonly_settings', array(

                array(
                    'title' => __( 'General Read-Only Settings', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'These are read-only settings that should only be changed by a developer during testing.', 'twitchpress' ),
                    'id'     => 'generalreadonlysettings'
                ),

                array(
                    'title'             => __( 'Twitch API Call Count', 'twitchpress' ),
                    'desc'              => __( 'Used as a call-ID for troubleshooting purposes.', 'twitchpress' ),                    
                    'id'                => 'twitchpress_twitchapi_call_count',
                    'default'           => 0,
                    'type'              => 'text',
                    'autoload'          => true,
                    'custom_attributes' => array( 'readonly' => 'readonly' ),
                ),                  
                array(
                    'type'     => 'sectionend',
                    'id'     => 'generalreadonlysettings'
                )

            ));

        }
  
        return apply_filters( 'twitchpress_get_settings_' . $this->id, $settings, $current_section );
    }
}
    
endif;

return new TwitchPress_Settings_Readonly();
          
     

 
    