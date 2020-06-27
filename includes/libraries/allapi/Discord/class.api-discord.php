<?php
/**
 * Discord API Class for TwitchPress
 *
 * @link https://dev.streamlabs.com/
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Streamlabs Extension
 * @version  1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( !class_exists( 'TWITCHPRESS_All_API' ) ) { return; }

if( !class_exists( 'TwitchPress_Discord_API' ) ) :

class TwitchPress_Discord_API extends TWITCHPRESS_All_API {    
    
    public $discord_app_ready = false; /* changes to true when application credentials are all present */
    public $response = null; /* Discord server response to call */
    public $decompress = true; /* Set to false if you do not want the body to be decompressed */
    
    // Application credentials...
    protected $discord_app_id     = null;
    protected $discord_app_secret = null;
    protected $discord_app_uri    = null; 
    protected $discord_app_code   = null;
    protected $discord_app_token  = null;
    protected $discord_app_scope  = array();
    
    protected $url = 'https://discord.com/api/oauth2/';
    public $version = '1.0';// API version
    
    /**
    * Store user data here during a procedure to help avoid 
    * repeating database queries.  
    * 
    * @var mixed
    */
    public $users = array();
        
    public function __construct( $profile = 'default' ){      
        parent::__construct( $profile );
        $this->set_application( $profile );
        $this->is_app_set();
        add_action( 'wp_loaded', array( $this, 'oauth_admin_listener' ), 1 );
    } 
    
    public function url() {
        return $this->url . TWITCHPRESS_VERSION . '/';
    }
                                                           
    public function set_application( $profile = 'default' ) {        
        $this->discord_app_uri    = get_option( 'twitchpress_allapi_discord_' . $profile . '_uri', null );
        $this->discord_app_id     = get_option( 'twitchpress_allapi_discord_' . $profile . '_id', null );
        $this->discord_app_secret = get_option( 'twitchpress_allapi_discord_' . $profile . '_secret', null );
        $this->discord_app_token  = get_option( 'twitchpress_allapi_discord_' . $profile . '_access_token', null );
        $this->discord_app_code   = get_option( 'twitchpress_allapi_discord_' . $profile . '_code', null );
    }
}

endif;