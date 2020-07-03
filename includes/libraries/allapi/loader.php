<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Include main class...
include_once( 'class.all-api.php' );

/* Options are currently bypassed pending switches for all services */

// Discord
if( !get_option( 'twitchpress_discord' ) ) { 
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/libraries/allapi/discord/class.api-discord.php' );
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/libraries/allapi/discord/class.api-discord-listener.php' );
}

// Patron.com 

// Steam 

// StreamElements

// Streamlabs
if( !get_option( 'twitchpress_streamlabs' ) ) {                                                         
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/libraries/allapi/streamlabs/functions.api-streamlabs.php' );
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/libraries/allapi/streamlabs/class.api-streamlabs.php' );
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/libraries/allapi/streamlabs/class.api-streamlabs-listener.php' );
}

// Twitch.tv 

// Twitter

// YouTube
if( !get_option( 'twitchpress_youtube' ) ) { 
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/libraries/allapi/youtube/functions.api-youtube.php' );   
}