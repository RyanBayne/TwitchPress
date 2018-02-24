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
            'bugnet'  => __( 'BugNet', 'twitchpress' ),

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
            
        // Domain to Twitch API permission Options.
        } elseif( 'removal' == $current_section ) {
            
            $settings = apply_filters( 'twitchpress_general_removal_settings', array(
 
                array(
                    'title' => __( 'Plugin Removal Settings', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'By default most plugins will not remove everything when the plugin is deleted. If you need all options, custom tables, custom files and other data to be removed when you delete the plugin. Then check the box below, else please leave this option alone.', 'twitchpress' ),
                    'id'     => 'pluginremovalsettings',
                ),
            
                array(
                    'desc'            => __( 'Removal All TwitchPress Data and Files (including extensions)', 'twitchpress' ),
                    'id'              => 'twitchpress_removeall',
                    'default'         => 'no',
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
        
        // Advanced settings for developers only.
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
                    'id'              => 'twitchress_redirect_tracking_switch',
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
 
        // BugNet library settings
        } elseif( 'bugnet' == $current_section ) {
            
            $settings = apply_filters( 'twitchpress_general_bugnet_settings', array(
             
                array(
                    'title' => __( 'BugNet Controls', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => '',
                    'id'     => 'twitchpress_bugnet_main_service_switches',
                ),

                // MAIN SERVICE SWITCHES
                array(
                    'desc'            => __( 'Activate Events Service', 'twitchpress' ),
                    'id'              => 'bugnet_activate_events',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),

                array(
                    'desc'            => __( 'Activate Logging Service', 'twitchpress' ),
                    'id'              => 'bugnet_activate_log',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),

                array(
                    'desc'            => __( 'Activate Tracing Service ', 'twitchpress' ),
                    'id'              => 'bugnet_activate_tracing',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'end',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'twitchpress_bugnet_main_service_switches'
                ),

                // LEVEL SWITCHES
                array(
                    'title' => __( 'Level Switches', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'You can disable entire levels of debugging to reduce BugNet activity.', 'twitchpress' ),
                    'id'     => 'twitchpress_bugnet_level_switches',
                ),

                array(
                    'title'           => __( 'Activate/Disable Levels', 'twitchpress' ),
                    'desc'            => __( 'Emergency Level Events', 'twitchpress' ),
                    'id'              => 'bugnet_levelswitch_emergency',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'start',
                    'show_if_checked' => 'option',
                    'autoload'        => true,
                ),

                array(
                    'desc'            => __( 'Serious Alerts', 'twitchpress' ),
                    'id'              => 'bugnet_levelswitch_alert',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),

                array(
                    'desc'            => __( 'Critical Faults', 'twitchpress' ),
                    'id'              => 'bugnet_levelswitch_critical',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),

                array(
                    'desc'            => __( 'Errors', 'twitchpress' ),
                    'id'              => 'bugnet_levelswitch_error',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),

                array(
                    'desc'            => __( 'Important Warnings', 'twitchpress' ),
                    'id'              => 'bugnet_levelswitch_warning',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),

                array(
                    'desc'            => __( 'Helpful Notices', 'twitchpress' ),
                    'id'              => 'bugnet_levelswitch_notice',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'end',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'wpseed_bugnet_level_switches'
                ),
                
                // HANDLER SWITCHES
                array(
                    'title' => __( 'Handler Switches', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'Handles are services used to collect and store debugging data. It also includes emails and does not include reports.', 'twitchpress' ),
                    'id'     => 'twitchpress_bugnet_handler_switches',
                ),

                array(
                    'title'           => __( 'Activate/Disable Handlers', 'twitchpress' ),
                    'desc'            => __( 'Emails', 'wpseed' ),
                    'id'              => 'bugnet_handlerswitch_email',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'start',
                    'show_if_checked' => 'option',
                    'autoload'        => true,
                ),

                array(
                    'desc'            => __( 'Log Files', 'twitchpress' ),
                    'id'              => 'bugnet_handlerswitch_logfiles',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),

                array(
                    'desc'            => __( 'REST API', 'twitchpress' ),
                    'id'              => 'bugnet_handlerswitch_restapi',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),

                array(
                    'desc'            => __( 'Tracing', 'twitchpress' ),
                    'id'              => 'wpseed_bugnet_handlerswitch_tracing',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),

                array(
                    'desc'            => __( 'Database', 'twitchpress' ),
                    'id'              => 'bugnet_handlerswitch_wpdb',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'end',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'twitchpress_bugnet_handler_switches'
                ),
                
                // REPORT SWITCHES
                array(
                    'title' => __( 'Report Switches', 'wpseed' ),
                    'type'     => 'title',
                    'desc'     => __( 'Reports generate information and statistics for none developers. Reports can also create documents.', 'twitchpress' ),
                    'id'     => 'twitchpress_bugnet_report_switches',
                ),

                array(
                    'title'           => __( 'Activate/Disable Reports', 'twitchpress' ),
                    'desc'            => __( 'Daily Summary', 'wpseed' ),
                    'id'              => 'bugnet_reportsswitch_dailysummary',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'start',
                    'show_if_checked' => 'option',
                    'autoload'        => true,
                ),

                array(
                    'desc'            => __( 'Event Snapshot', 'twitchpress' ),
                    'id'              => 'bugnet_reportsswitch_eventsnapshot',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),

                array(
                    'desc'            => __( 'Trace Complete', 'twitchpress' ),
                    'id'              => 'bugnet_reportsswitch_tracecomplete',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'end',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'twitchpress_bugnet_report_switches'
                ),

                // MISC SWITCHES
                array(
                    'title' => __( 'Other Settings', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => '',
                    'id'     => 'twitchpress_bugnet_other_switches',
                ),

                array(
                    'desc'            => __( 'System Logging i.e. using php_error() and logging to default server log.', 'twitchpress' ),
                    'id'              => 'bugnet_systemlogging_switch',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'start',
                    'show_if_checked' => 'option',
                    'autoload'        => true,
                ),
                
                array(
                    'title'    => __( 'Single User Error Dump', 'twitchpress-login' ),
                    'desc'     => __( 'Enter a WP user ID to limit error dumping to that user.', 'twitchpress-login' ),
                    'id'       => 'bugnet_error_dump_user_id',
                    'css'      => 'width:75px;',
                    'default'  => '1',
                    'type'     => 'text',
                ),
                    
                array(
                    'type' => 'sectionend',
                    'id'   => 'twitchpress_bugnet_other_switches'
                ),
                                    
            ));            
        }
  
        return apply_filters( 'twitchpress_get_settings_' . $this->id, $settings, $current_section );
    }
}
    
endif;

return new TwitchPress_Settings_General();
          
     

 
    