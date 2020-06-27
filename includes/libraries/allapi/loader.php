<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Include main class...
require( 'class.all-api.php' );

/* Options are currently bypassed pending switches for all services */

// Discord
if( !get_option( 'twitchpress_discord' ) ) { 
    require( 'discord/class.api-discord.php' );
    require( 'discord/class.api-discord-listener.php' );
}

// Patron.com 

// Steam 

// StreamElements

// Streamlabs
if( !get_option( 'twitchpress_streamlabs' ) ) {                                                         
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/libraries/allapi/streamlabs/functions.api-streamlabs.php' );
    require( 'streamlabs/class.api-streamlabs.php' );
    require( 'streamlabs/class.api-streamlabs-listener.php' );
}

// Twitch.tv 

// Twitter

// YouTube
if( !get_option( 'twitchpress_youtube' ) ) { 
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/libraries/allapi/youtube/functions.api-youtube.php' );   
}