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

    public $api_array = array(
        'google',
        'streamlabs',
        'twitter',
        'youtube', 
        'discord',
        'steam',
        'streamelements',
        'patron'
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
            'default'        => __( 'API Switches', 'twitchpress' ),
            'google'         => __( 'Google oAuth', 'twitchpress' ),
            'streamlabs'     => __( 'Streamlabs', 'twitchpress' ),
            'twitter'        => __( 'Twitter', 'twitchpress'),
            'youtube'        => __( 'YouTube', 'twitchpress' ),
            'discord'        => __( 'Discord', 'twitchpress' ),
            'steam'          => __( 'Steam', 'twitchpress' ),
            'streamelements' => __( 'StreamElements', 'twitchpress' ),
            'patron'         => __( 'Patron', 'twitchpress' ),
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
     * @version 2.0
     */
    public function save() {
        global $current_section;
        $settings = $this->get_settings( $current_section );
        
        TwitchPress_Admin_Settings::save_fields( $settings );
        
        // Run procedures for reacting to new application credentials.
        if( $service = $this->is_application_being_saved() ) {
            $this->update_application( $service );         
        }        
    } 
    
    /**
    * Store application credentials.
    * 
    * @param mixed $service
    * 
    * @version 1.1
    */
    public function update_application( $service ) {

        // We need all application credentials else display error. 
        if( !isset( $_POST[ 'twitchpress_allapi_' . $service . '_default_uri' ] ) ) {
            TwitchPress_Admin_Settings::add_error( __( 'You have not entered the redirect URL.', 'twitchpress' ) );
            return;    
        }    
        
        if( !isset( $_POST[ 'twitchpress_allapi_' . $service . '_default_id' ] ) || empty( $_POST[ 'twitchpress_allapi_' . $service . '_default_id' ] ) ) {
            TwitchPress_Admin_Settings::add_error( __( 'You have not entered the ID for your application.', 'twitchpress' ) );
            return;
        }    
        
        if( !isset( $_POST[ 'twitchpress_allapi_' . $service . '_default_secret' ] ) ) {
            TwitchPress_Admin_Settings::add_error( __( 'You have not entered the secret value for your application.', 'twitchpress' ) );
            return;
        }    

        $uri    = esc_url_raw( $_POST[ 'twitchpress_allapi_' . $service . '_default_uri' ] );
        $key    = sanitize_key( $_POST[ 'twitchpress_allapi_' . $service . '_default_id' ] );
        $secret = sanitize_key( $_POST[ 'twitchpress_allapi_' . $service . '_default_secret' ] );
                
        // The All API library will start an oAuth2 if required.  
        $this->application_being_updated( $service, $uri, $key, $secret );
    }
    
    /**
    * Carrying out post application update procedures. 
    * 
    * @param mixed $service
    * @param mixed $url
    * @param mixed $key
    * @param mixed $secret
    * 
    * @version 1.0
    */
    public function application_being_updated( $service, $url, $key, $secret ) {     
                                          
        switch ( $service ) {
           case 'google':
             break; 
           case 'streamlabs':        
                $streamlabs = new TWITCHPRESS_Streamlabs_API();     
                $redirect_to = admin_url( 'admin.php?page=twitchpress&tab=otherapi&section=streamlabs' );    
                $streamlabs->do_application_being_updated( $redirect_to, $key, $secret );
             break;
           
           case 'twitter':
        
             break;
           
           case 'youtube':
        
             break;
           
           case 'steam':
        
             break;
           
           case 'deepbot':
        
             break;
           
           case 'discord':

             break;
           
           case 'streamelements':
        
             break;
             
           case 'patron':
        
             break;
        
           default:
                // Allow extensions to add_action() and run custom procedures for updating app credentials...
                do_action( 'twitchpress_allapi_application_update_' . $service, $service, $url, $key, $secret );
             break;
        }   
                    
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
                    'desc'  => __( 'Switches for API services other than Twitch.', 'twitchpress' ),
                    'id'    => 'otherapiswitches_settings'
                ),
                
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
                    'default'         => 'no',
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
            
        } elseif( 'discord' == $current_section ) {
            
            $title = "Discord"; 
            $settings = apply_filters( 'twitchpress_discord_application_settings', $this->application_inputs( $title, $current_section ) );
            
        } elseif( 'streamlabs' == $current_section ) {
            
            $title = "Streamlabs"; 
            $settings = apply_filters( 'twitchpress_streamlabs_application_settings', $this->application_inputs( $title, $current_section ) );

        } elseif( 'streamelements' == $current_section ) {
            
            $title = "StreamElements"; 
            $settings = apply_filters( 'twitchpress_streamelements_application_settings', $this->application_inputs( $title, $current_section ) );

        } elseif( 'google' == $current_section ) {
            
            $title = "Google oAuth"; 
            $settings = apply_filters( 'twitchpress_google_application_settings', $this->application_inputs( $title, $current_section ) );
            
        }
        
        return apply_filters( 'twitchpress_get_settings_' . $this->id, $settings, $current_section );
    }
    
    public function application_inputs( $title, $service ) {
        $service = strtolower( $service );
        $fields = array(
            array(
                'title' => $title . __( ' API Settings', 'twitchpress' ),
                'type'  => 'title',
                'desc'  => sprintf( __( 'Application settings for the %s API.', 'twitchpress' ), $title ),
                'id'    => $service . '_api_application_settings'
            ),

            array(
                'id'              => 'twitchpress_otherapi_application_saving',
                'default'         => '',
                'autoload'        => false,
                'type'            => 'hidden',
            ),
            
            array(
                'title'           => __( 'Redirect URI', 'twitchpress' ),
                'desc'            => __( 'Redirect URI', 'twitchpress' ),
                'id'              => 'twitchpress_allapi_' . $service . '_default_uri',
                'default'         => '',
                'autoload'        => false,
                'type'            => 'text',
            ),

            array(
                'title'           => __( 'Client ID', 'twitchpress' ),
                'desc'            => __( 'Your applications public ID.', 'twitchpress' ),
                'id'              => 'twitchpress_allapi_' . $service . '_default_id',
                'default'         => '',
                'type'            => 'text',
                'autoload'        => false,
            ),

            array(
                'title'           => __( 'Client Secret', 'twitchpress' ),
                'desc'            => __( 'Keep this value hidden at all times.', 'twitchpress' ),
                'id'              => 'twitchpress_allapi_' . $service . '_default_secret',
                'default'         => '',
                'type'            => 'password',
                'autoload'        => false,
            ),
            
            array(
                'title'           => __( 'Client Token', 'twitchpress' ),
                'desc'            => __( 'Also known as an Access Token and must be kept secret.', 'twitchpress' ),
                'id'              => 'twitchpress_allapi_' . $service . '_default_access_token',
                'default'         => '',
                'type'            => 'password',
                'autoload'        => false,
            ),
        );
        
        // Add fields for specific services...
        if( $service == 'twitter' ) {
            
            $token_secret_field = array(
                'title'           => __( 'Access Token Secret', 'twitchpress' ),
                'desc'            => __( 'Token secrets are used to regenerate the token.', 'twitchpress' ),
                'id'              => 'twitchpress_allapi_' . $service . '_default_access_token_secret',
                'default'         => '',
                'type'            => 'password',
                'autoload'        => false,
            );
            
            $fields = array_merge( $fields, array($token_secret_field) );     
        }   
        
        // Add final section ID value required by WP...
        $final_value = array(
                'type' => 'sectionend',
                'id'   => $service . '_api_application_settings' );
                        
        return array_merge( $fields, array($final_value) );
    }
}
    
endif;

return new TwitchPress_Settings_OtherAPI();
