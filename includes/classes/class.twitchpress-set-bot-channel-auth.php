<?php
/**
 * TwitchPress - Twitch API credentials for the bot channel and it's owner are set here.  
 * 
 * @author   Ryan Bayne
 * @category Scripts
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Set_Bot_Channel_Auth' ) ) :

class TwitchPress_Set_Bot_Channel_Auth {
    
    public $bot_channels_code = null;
    public $bot_channels_wpowner_id = null;
    public $bot_channels_token = null;
    public $bot_channels_refresh = null; 
    public $bot_channels_scopes = null;
    public $bot_channels_name = null;
    public $bot_channels_id = null;
    public $bot_channels_postid = null;
    
    function __construct() {
        $this->set();    
    }
    
    function set() {                                                    
        $this->bot_channels_code        = get_option( 'twitchpress_bot_channels_code', 0 );
        $this->bot_channels_wpowner_id  = get_option( 'twitchpress_bot_channels_wpowner_id', 0 );
        $this->bot_channels_token       = get_option( 'twitchpress_bot_channels_token', 0 );
        $this->bot_channels_refresh     = get_option( 'twitchpress_bot_channels_refresh', 0 ); 
        $this->bot_channels_scopes      = get_option( 'twitchpress_bot_channels_scopes', 0 );
        $this->bot_channels_name        = get_option( 'twitchpress_bot_channels_name', 0 );
        $this->bot_channels_id          = get_option( 'twitchpress_bot_channels_id', 0 );        
        $this->bot_channels_postid      = get_option( 'twitchpress_bot_channels_postid', 0 );        
    }
    
    function get( $value = null ) {
        if( $value ) {
            return eval( '$this->bot_channel_$value' );
        }
        return array(
            'code'      => $this->bot_channels_code,
            'wpownerid' => $this->bot_channels_wpowner_id,
            'token'     => $this->bot_channels_token,
            'refresh'   => $this->bot_channels_refresh,
            'scopes'    => $this->bot_channels_scopes,
            'name'      => $this->bot_channels_name,
            'id'        => $this->bot_channels_id,
            'postid'    => $this->bot_channels_postid
        );
    }

}

endif;

TwitchPress_Object_Registry::add( 'botchannelauth', new TwitchPress_Set_Bot_Channel_Auth() );