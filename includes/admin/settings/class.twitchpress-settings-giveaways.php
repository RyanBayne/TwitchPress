<?php
/**
 * TwitchPress Giveaway Settings View
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress
 * @version  1.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' );

if ( ! class_exists( 'TwitchPress_Settings_Giveaways' ) ) :
                                  
class TwitchPress_Settings_Giveaways extends TwitchPress_Settings_Page {

    /**
     * Constructor.
     */
    public function __construct() {
        global $current_section;
        
        $this->id    = 'giveaways';
        $this->label = __( 'Giveaways', 'twitchpress' );
        
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
        if( isset( $_GET['tab'] ) && $_GET['tab'] == 'giveaways' && !get_option( 'twitchpress_giveaways_switch' ) ) {
            return __( 'Install Giveaways', 'twitchpress' );
        } return $text;
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
                                
        if( !get_option( 'twitchpress_giveaways_switch' ) || !get_option( 'twitchpress_giveaways_version' ) ) 
        {                                         
            echo '<div id="message" class="error inline"><p><strong>' . __( 'Giveaways System has not been installed yet.', 'twitchpress' ) . '</strong></p></div>';
            $settings = $this->get_settings( 'installation' );
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
        
        require_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'systems/giveaways/class.twitchpress-giveaways-install.php' );

        // First time installation by admin is required...
        if( isset( $_POST['giveaways_first_installation_request'] ) ) 
        {
            $settings = $this->get_settings( 'installation' );
            
            $install = new TwitchPress_Giveaways_Install();
            $install->installation_type = 'activation';
            $result = $install->install();
            
            TwitchPress_Admin_Settings::add_message( __( 'Giveaways has been installed', 'twitchpress' ) );                
        }
        else
        {
            // Process normal submissions when initial installation has been done...
            global $current_section;
            $settings = $this->get_settings( $current_section ); 
          
            // Process installation of individual services...
            if( isset( $_POST['giveaways_activate_tracing'] ) ) {      
                $install = new TwitchPress_Giveaways_Install();
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
            
            $settings = apply_filters( 'twitchpress_giveaways_settings', array(
            
                array(
                    'title' => __( 'Giveaways Main Services', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => '',
                    'id'     => 'twitchpress_giveaways_main_service_switches',
                ),              
                
                array(
                    'desc'            => __( 'Experimental Mode (testing only)', 'twitchpress' ),
                    'id'              => 'giveaway_activate_experimental',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),
   
                array(
                    'type' => 'sectionend',
                    'id'   => 'twitchpress_bugnet_main_service_switches'
                ),
                
            ));
                
        } elseif ( 'installation' == $current_section ) {
                                                                 
            $about = __( 'This plugin offers a Giveaways system. Installation is required before you can use this system.', 'twitchpress' );
            
            $settings = apply_filters( 'twitchpress_giveaways_settings', array(
            
                array(
                    'title' => __( 'Giveaways Installation', 'twitchpress' ),
                    'type'  => 'title',
                    'desc'  => $about,
                    'id'    => 'giveawaysinstallation'
                ),     
                         
                array(
                    'desc'            => __( '', 'twitchpress' ),
                    'id'              => 'twitchpress_giveaways_version',
                    'default'         => TWITCHPRESS_GIVEAWAYS_VERSION,
                    'type'            => 'hidden',
                ),
                
                array(
                    'desc'            => __( '', 'twitchpress' ),
                    'id'              => 'giveaways_first_installation_request',
                    'default'         => TWITCHPRESS_GIVEAWAYS_VERSION,
                    'type'            => 'hidden',
                ),
                        
                array(
                    'desc'            => __( 'Share giveaways to EGO Group', 'twitchpress' ),
                    'id'              => 'twitchpress_giveaways_shareego',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),
                                         
                array(
                    'type'     => 'sectionend',
                    'id'     => 'giveawaysinstallation'
                )

            ));
                  
        } 
                                   
        return apply_filters( 'twitchpress_get_settings_' . $this->id, $settings, $current_section );
    }
}

endif;

return new TwitchPress_Settings_Giveaways();