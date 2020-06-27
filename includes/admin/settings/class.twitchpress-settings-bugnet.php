<?php
/**
 * TwitchPress BugNet Settings View
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress
 * @version  1.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' );

if ( ! class_exists( 'TwitchPress_Settings_BugNet' ) ) :

/**
 * TwitchPress_Settings_Sections.
 */
class TwitchPress_Settings_BugNet extends TwitchPress_Settings_Page {

    /**
     * Constructor.
     */
    public function __construct() {
        global $current_section;
        
        $this->id    = 'bugnet';
        $this->label = __( 'BugNet', 'twitchpress' );
        
        add_filter( 'twitchpress_settings_save_button_text', array( $this, 'custom_save_button' ), 1 );
        add_filter( 'twitchpress_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
        add_action( 'twitchpress_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'twitchpress_settings_save_' . $this->id, array( $this, 'save' ) );
        add_action( 'twitchpress_sections_' . $this->id, array( $this, 'output_sections' ) );
    }
    
    /**
    * Filter the save button text when on the BugNet tab and change it according
    * to BugNet installation state...
    * 
    * @param mixed $text original button text i.e. "Save changes"
    * 
    * @version 1.0
    */
    public function custom_save_button( $text ) {
        if( isset( $_GET['tab'] ) && $_GET['tab'] == 'bugnet' && !get_option( 'bugnet_version' ) )
        {
            return __( 'Install BugNet', 'twitchpress' );
        }
        return $text;
    }

    /**
     * Get sections.
     *
     * @return array
     */
    public function get_sections() {

        $sections = array(
            'default'  => __( 'General', 'twitchpress' ),
            'database' => __( 'Database', 'twitchpress' ),
        );

        return apply_filters( 'twitchpress_get_sections_' . $this->id, $sections );
    }

    /**
     * Output the settings.
     */
    public function output() {
        global $current_section;

        if( !get_option( 'bugnet_version' ) ) 
        {
            $settings = $this->get_settings( 'installation' );
            $message = __( 'BugNet has not been installed yet.', 'twitchpress' );
            echo '<div id="message" class="error inline"><p><strong>' . $message . '</strong></p></div>';
        }
        else
        {
            $settings = $this->get_settings( $current_section );    
        }
                                        
        TwitchPress_Admin_Settings::output_fields( $settings );
    }

    /**
     * Save settings...
     * 
     * @version 1.0
     */
    public function save() {
        
        require_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/libraries/bugnet/class.bugnet-install.php' );

        // First time installation by admin is required...
        if( isset( $_POST['bugnet_first_installation_request'] ) ) 
        {
            $settings = $this->get_settings( 'installation' );
            
            $install = new BugNet_Install();
            $install->installation_type = 'activation';
            $result = $install->install();
            
            TwitchPress_Admin_Settings::add_message( __( 'BugNet has been installed', 'twitchpress' ) );                
        }
        else
        {
            // Process normal submissions when initial installation has been done...
            global $current_section;
            $settings = $this->get_settings( $current_section ); 
          
            // Process installation of individual services...
            if( isset( $_POST['bugnet_activate_tracing'] ) ) {      
                $install = new BugNet_Install();
                $install->installation_type = 'tracing';
                $result = $install->install();
                unset($install);                   
            }
        }
    
        TwitchPress_Admin_Settings::save_fields( $settings );
    }

    /**
     * Get settings array.
     *
     * @return array
     * 
     * @version 2.0
     */
    public function get_settings( $current_section = 'default' ) {
        $settings = array();
                            
        if ( 'default' == $current_section ) {
            
            $settings = apply_filters( 'twitchpress_bugnet_settings', array(
            
                array(
                    'title' => __( 'BugNet Main Services', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => '',
                    'id'     => 'twitchpress_bugnet_main_service_switches',
                ),

                // MAIN SERVICE SWITCHES - these exist in two places in this file...
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

                // DISPLAY SWITCHES
                array(
                    'title' => __( 'Display Settings', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => '',
                    'id'     => 'twitchpress_bugnet_display_switches',
                ),

                array(
                    'desc'            => __( 'Dump Errors (footer)', 'twitchpress' ),
                    'id'              => 'twitchpress_displayerrors',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
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
                    'desc'            => __( 'Dump Filters (footer)', 'twitchpress' ),
                    'id'              => 'twitchpress_display_filters',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),
                
                array(
                    'desc'            => __( 'Dump Actions (footer)', 'twitchpress' ),
                    'id'              => 'twitchpress_display_actions',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),
                                    
                array(
                    'type' => 'sectionend',
                    'id'   => 'twitchpress_bugnet_display_switches'
                ),
                
                // TRANSIENT CACHE SWITCHES
                array(
                    'title' => __( 'Transient Cache Settings', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'Control how much data BugNet stores. Views that use transient caches to generate tables of data will instead use available data from the immediate page request. That is limiting but can still help.', 'twitchpress' ),
                    'id'     => 'twitchpress_bugnet_cache_switches',
                ),
                
                array(
                    'desc'            => __( 'Cache Action Hooks', 'twitchpress' ),
                    'id'              => 'twitchpress_bugnet_cache_action_hooks',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),
                                    
                array(
                    'type' => 'sectionend',
                    'id'   => 'twitchpress_bugnet_cache_switches'
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
                    'id'   => 'twitchpress_bugnet_level_switches'
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
                    'desc'            => __( 'Emails', 'twitchpress' ),
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
                    'id'              => 'bugnet_handlerswitch_tracing',
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
                    'title' => __( 'Report Switches', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'Reports generate snap-shot information and statistics. Reports are intended for long-term monitoring.', 'twitchpress' ),
                    'id'     => 'twitchpress_bugnet_report_switches',
                ),

                array(
                    'title'           => __( 'Activate/Disable Reports', 'twitchpress' ),
                    'desc'            => __( 'Daily Summary', 'twitchpress' ),
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
                    'type' => 'sectionend',
                    'id'   => 'twitchpress_bugnet_other_switches'
                ),

            ));
                
        } elseif ( 'installation' == $current_section ) {
                                                                 
            $about = __( 'BugNet is an error reporting and monitoring tool created by the author of TwitchPress.
            There are different levels of activity within BugNet to match your requirements. 
            Installation adds the minimum requirements to begin collecting none sensitive data that helps
            us troubleshoot issues.', 'twitchpress' );
            
            $bugnet_version = BUGNET_VERSION;
            
            $settings = apply_filters( 'twitchpress_bugnet_version_settings', array(
            
                array(
                    'title' => sprintf( __( 'BugNet Installation %s', 'twitchpress' ), BUGNET_VERSION ),
                    'type'  => 'title',
                    'desc'  => $about,
                    'id'    => 'bugnetinstallation'
                ),
                
                array(
                    'desc'            => __( 'name: the Twitch username can change.', 'twitchpress' ),
                    'id'              => 'bugnet_version',
                    'default'         => BUGNET_VERSION,
                    'type'            => 'hidden',
                ),
                
                array(
                    'desc'            => __( 'name: the Twitch username can change.', 'twitchpress' ),
                    'id'              => 'bugnet_first_installation_request',
                    'default'         => BUGNET_VERSION,
                    'type'            => 'hidden',
                ),
                
                // MAIN SERVICE SWITCHES - these exist in two places in this file...
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
                    'type'     => 'sectionend',
                    'id'     => 'bugnetinstallation'
                )

            ));
                  
        } 
                                   
        return apply_filters( 'twitchpress_get_settings_' . $this->id, $settings, $current_section );
    }
}

endif;

return new TwitchPress_Settings_BugNet();