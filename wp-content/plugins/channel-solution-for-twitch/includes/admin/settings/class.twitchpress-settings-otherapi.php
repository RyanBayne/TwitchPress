<?php
/**
* TwitchPress Other API Settings
* 
* This is the settings view for any API other than Twitch.tv
* 
* @author Ryan Bayne
* @category Users
* @package TwitchPress/Settings/Other API
* @version 1.0
*/

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' );

if ( ! class_exists( 'TwitchPress_Settings_OtherAPI' ) ) :

class TwitchPress_Settings_OtherAPI extends TwitchPress_Settings_Page {
    
    private $sections_array = array();
    
    private $api_array = array(
        'twitter',
        'youtube',
        'steam',
        'facebook',
        'deepbot',
        'streamtip',
        'discord',
        'streamlabs'
    );

    /**
    * Constructor
    * 
    * @version 1.0    
    */
    public function __construct() {
        $this->id    = 'otherapi';
        $this->label = __( 'Other API', 'twitchpress' );

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
            'default'    => __( 'API Switches', 'twitchpress' ),
            'twitter'    => __( 'Twitter', 'twitchpress' ),
            'youtube'    => __( 'YouTube', 'twitchpress' ),
            'steam'      => __( 'Steam', 'twitchpress' ),
            'facebook'   => __( 'Facebook', 'twitchpress' ),
            'deepbot'    => __( 'DeepBot', 'twitchpress' ),
            'streamtip'  => __( 'Streamtip', 'twitchpress' ),
            'discord'    => __( 'Discord', 'twitchpress' ),
            'streamlabs' => __( 'Streamlabs', 'twitchpress' ),
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
     * @version 1.0
     */
    public function save() {
        global $current_section;
        $settings = $this->get_settings( $current_section );
        
        // Process application configuration submission. 
        if( $service = $this->is_application_being_saved() ) {
            $this->update_application( $service );         
        }

        TwitchPress_Admin_Settings::save_fields( $settings );
    } 
    
    /**
    * Store application credentials.
    * 
    * @param mixed $service
    * 
    * @version 1.0
    */
    public function update_application( $service ) {
        
        // We need all application credentials else display error. 
        if( !isset( $_POST['twitchpress_api_redirect_uri_' . $service] ) ) {
            TwitchPress_Admin_Settings::add_error( __( 'You have not entered the redirect URL setup in your application.', 'twitchpress' ) );
            return;    
        }    
        
        if( !isset( $_POST['twitchpress_api_id_' . $service] ) || empty( $_POST['twitchpress_api_id_' . $service] ) ) {
            TwitchPress_Admin_Settings::add_error( __( 'You have not entered the ID generated when creating your application.', 'twitchpress' ) );
            return;
        }    
        
        if( !isset( $_POST['twitchpress_api_secret_' . $service] ) ) {
            TwitchPress_Admin_Settings::add_error( __( 'You have not entered the secret generated when you created your application.', 'twitchpress' ) );
            return;
        }    
        
        // Validate URL string is an actual URL. 
        
        // Validate ID (no special characters allowed) 
        
        // Validate (no special characters allowed)
        
        $url = $_POST['twitchpress_api_redirect_uri_' . $service];
        $id = $_POST['twitchpress_api_id_' . $service];
        $secret = $_POST['twitchpress_api_secret_' . $service];
        
        // The All API library will start an oAuth2 if required.  
        $all_api = new TWITCHPRESS_All_API();
        $all_api->application_being_updated( $service, $url, $id, $secret );
    }
    
    /** 
    * Determines if user is saving an application on any of the 
    * application forms spread over multiple sections. 
    * 
    * @returns string API lowercase slug. 
    * @returns boolean false if
    * 
    * @version 1.0
    */
    public function is_application_being_saved() { 
        if( isset( $_POST ) && isset( $_GET['section'] ) && in_array( $_GET['section'], $this->api_array ) ) 
        {
            return $_GET['section']; 
        }
        return false;
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

            $settings = apply_filters( 'twitchpress_otherapi_switches_settings', array(
            
                array(
                    'title' => __( 'Other API Switches', 'twitchpress' ),
                    'type'  => 'title',
                    'desc'  => __( 'Switches for API services other than the Twitch API. Please consider how secure your site is before configuring access to many API.', 'twitchpress' ),
                    'id'    => 'otherapiswitches_settings'
                ),

                // Twitter
                array(
                    'title'         => __( 'Twitter API', 'twitchpress' ),
                    'desc'          => __( 'Activate Twitter Services.', 'twitchpress' ),
                    'id'            => 'twitchpress_switch_twitter_api_services',
                    'type'          => 'checkbox',
                    'default'       => 'no',
                    'checkboxgroup' => 'start',
                    'autoload'      => false,
                ),

                array(
                    'desc'            => __( 'Log Tweeting Activity', 'twitchpress' ),
                    'id'              => 'twitchpress_switch_twitter_api_logs',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
               
                // YouTube
                array(
                    'title'         => __( 'YouTube API', 'twitchpress' ),
                    'desc'          => __( 'Activate YouTube Services.', 'twitchpress' ),
                    'id'            => 'twitchpress_switch_youtube_api_services',
                    'type'          => 'checkbox',
                    'default'       => 'no',
                    'checkboxgroup' => 'start',
                    'autoload'      => false,
                ),

                array(
                    'desc'            => __( 'Log YouTube API Activity', 'twitchpress' ),
                    'id'              => 'twitchpress_switch_youtube_api_logs',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                // Steam
                array(
                    'title'         => __( 'Steam API', 'twitchpress' ),
                    'desc'          => __( 'Activate Steam API Services.', 'twitchpress' ),
                    'id'            => 'twitchpress_switch_steam_api_services',
                    'type'          => 'checkbox',
                    'default'       => 'no',
                    'checkboxgroup' => 'start',
                    'autoload'      => false,
                ),

                array(
                    'desc'            => __( 'Log Steam API Activity', 'twitchpress' ),
                    'id'              => 'twitchpress_switch_steam_api_logs',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                                
                // Facebook 
                array(
                    'title'         => __( 'Facebook API', 'twitchpress' ),
                    'desc'          => __( 'Activate Facebook API Services.', 'twitchpress' ),
                    'id'            => 'twitchpress_switch_facebook_api_services',
                    'type'          => 'checkbox',
                    'default'       => 'no',
                    'checkboxgroup' => 'start',
                    'autoload'      => false,
                ),

                array(
                    'desc'            => __( 'Log Facebook API Activity', 'twitchpress' ),
                    'id'              => 'twitchpress_switch_facebook_api_logs',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
              
                // Streamtip
                array(
                    'title'         => __( 'Streamtip API', 'twitchpress' ),
                    'desc'          => __( 'Activate Streamtip Services.', 'twitchpress' ),
                    'id'            => 'twitchpress_switch_streamtip_api_services',
                    'type'          => 'checkbox',
                    'default'       => 'no',
                    'checkboxgroup' => 'start',
                    'autoload'      => false,
                ),

                array(
                    'desc'            => __( 'Log Streamtip API Activity', 'twitchpress' ),
                    'id'              => 'twitchpress_switch_streamtip_api_logs',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                                 
                // Discord
                array(
                    'title'         => __( 'Discord API', 'twitchpress' ),
                    'desc'          => __( 'Activate Discord Services.', 'twitchpress' ),
                    'id'            => 'twitchpress_switch_discord_api_services',
                    'type'          => 'checkbox',
                    'default'       => 'no',
                    'checkboxgroup' => 'start',
                    'autoload'      => false,
                ),

                array(
                    'desc'            => __( 'Log Discord API Activity', 'twitchpress' ),
                    'id'              => 'twitchpress_switch_discord_api_logs',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                                 
                // Streamlabs 
                array(
                    'title'         => __( 'Streamlabs API', 'twitchpress' ),
                    'desc'          => __( 'Activate Streamlabs Services.', 'twitchpress' ),
                    'id'            => 'twitchpress_switch_streamlabs_api_services',
                    'type'          => 'checkbox',
                    'default'       => 'no',
                    'checkboxgroup' => 'start',
                    'autoload'      => false,
                ),

                array(
                    'desc'            => __( 'Log Streamlabs API Activity', 'twitchpress' ),
                    'id'              => 'twitchpress_switch_streamlabs_api_logs',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                                                                                                   
                array(
                    'type' => 'sectionend',
                    'id'   => 'otherapiswitches_settings'
                ),     
            
            ));
            
        // Pair public services with roles and capabilities.
        } elseif( 'twitter' == $current_section ) {
            
            $title = "Twitter"; 
            $settings = apply_filters( 'twitchpress_twitter_application_settings', $this->application_inputs( $title, $current_section ) );
                                           
        } elseif( 'youtube' == $current_section ) {
            
            $title = "YouTube"; 
            $settings = apply_filters( 'twitchpress_youtube_application_settings', $this->application_inputs( $title, $current_section ) );
            
        } elseif( 'steam' == $current_section ) {
            
            $title = "Steam"; 
            $settings = apply_filters( 'twitchpress_steam_application_settings', $this->application_inputs( $title, $current_section ) );
            
        } elseif( 'facebook' == $current_section ) {
            
            $title = "Facebook"; 
            $settings = apply_filters( 'twitchpress_facebook_application_settings', $this->application_inputs( $title, $current_section ) );

        } elseif( 'deepbot' == $current_section ) {
            
            $title = "DeepBot"; 
            $settings = apply_filters( 'twitchpress_deepbot_application_settings', $this->application_inputs( $title, $current_section ) );
            
        } elseif( 'streamtip' == $current_section ) {
            
            $title = "Streamtip"; 
            $settings = apply_filters( 'twitchpress_streamtip_application_settings', $this->application_inputs( $title, $current_section ) );
            
        } elseif( 'discord' == $current_section ) {
            
            $title = "Discord"; 
            $settings = apply_filters( 'twitchpress_discord_application_settings', $this->application_inputs( $title, $current_section ) );
            
        } elseif( 'streamlabs' == $current_section ) {
            
            $title = "Streamlabs"; 
            $settings = apply_filters( 'twitchpress_streamlabs_application_settings', $this->application_inputs( $title, $current_section ) );
            
        }

        return apply_filters( 'twitchpress_get_settings_' . $this->id, $settings, $current_section );
    }
    
    public function application_inputs( $title, $lower ) {
        return array(
            array(
                'title' => $title . __( ' API Switches', 'twitchpress' ),
                'type'  => 'title',
                'desc'  => sprintf( __( 'Application settings for the %s API.', 'twitchpress' ), $title ),
                'id'    => $lower . '_api_application_settings'
            ),

            array(
                'id'              => 'twitchpress_otherapi_application_saving',
                'default'         => '',
                'autoload'        => false,
                'type'            => 'hidden',
            ),
            
            array(
                'title'           => __( 'Redirect URL', 'twitchpress' ),
                'desc'            => __( 'Redirect URL', 'twitchpress' ),
                'id'              => 'twitchpress_api_redirect_uri_' . $lower,
                'default'         => '',
                'autoload'        => false,
                'type'            => 'text',
            ),

            array(
                'title'           => __( 'Application ID', 'twitchpress' ),
                'desc'            => __( 'Your applications public ID.', 'twitchpress' ),
                'id'              => 'twitchpress_api_id_' . $lower,
                'default'         => '',
                'type'            => 'text',
                'autoload'        => false,
            ),

            array(
                'title'           => __( 'Application Secret', 'twitchpress' ),
                'desc'            => __( 'Keep this value hidden at all times.', 'twitchpress' ),
                'id'              => 'twitchpress_api_secret_' . $lower,
                'default'         => '',
                'type'            => 'password',
                'autoload'        => false,
            ),
                  
            array(
                'type' => 'sectionend',
                'id'   => $lower . '_api_application_settings'
            ),
        );   
    }
}
    
endif;

return new TwitchPress_Settings_OtherAPI();
