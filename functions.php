<?php        
/**
* TwitchPress Core Functions
*/
   
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



add_action( 'activated_plugin', 'save_error_wpse_24278', 10, 2 );

function save_error_wpse_24278( $plugin, $network_wide )
{
    file_put_contents( 
        WP_CONTENT_DIR. '/error_activation.html', 
        ob_get_contents() 
    );
    //update_option( 'plugin_error',  ob_get_contents() );
}



require_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'install.php' );
include_once( plugin_basename( 'integration.php' ) );                 
include_once( plugin_basename( 'includes/functions/functions.twitchpress-get.php' ) );                
include_once( plugin_basename( 'includes/functions/functions.twitchpress-database.php' ) );                

function twitchpress_is_backend_login(){
    $ABSPATH_MY = str_replace(array('\\','/'), DIRECTORY_SEPARATOR, ABSPATH);
    return ((in_array($ABSPATH_MY.'wp-login.php', get_included_files()) || in_array($ABSPATH_MY.'wp-register.php', get_included_files()) ) || $GLOBALS['pagenow'] === 'wp-login.php' || $_SERVER['PHP_SELF']== '/wp-login.php');
}

/**
* Used with WP core login form...
* 
* @param mixed $message
* 
* @version 1.0
*/
function twitchpress_login_error( $message ) {
    $login_messages = new TwitchPress_Custom_Login_Messages();
    $login_messages->add_error( $message );
    unset( $login_messages );                 
}


/**
* Applies a custom slug using an external source i.e. Twitch channel logo. This
* approaches ensures that the avatar is always the current one on Twitch without
* needing an update process. 
* 
* @param mixed $avatar
* @param mixed $id_or_email
* @param mixed $size
* @param mixed $default
* @param mixed $alt
* 
* @version 2.0
*/
function twitchpress_slug_get_avatar( $avatar, $id_or_email, $size, $default, $alt = '' ) {
                                                                  
    //If is email, try and find user ID...
    if( !is_numeric( $id_or_email ) ){
        $user = get_user_by( 'email', $id_or_email );
        if( $user ){
            $id_or_email = $user->ID;
        }
    }

    //If still no user ID, return...
    if( !is_numeric( $id_or_email ) ){     
        return $avatar;
    }
        
    //Find URL of saved avatar in user meta...
    $saved = get_user_meta( $id_or_email, 'twitchpress_avatar_url', true );  
     
    //check if it is a URL
    if( filter_var( $saved, FILTER_VALIDATE_URL ) ) {
        //return saved image
        return sprintf( '<img src="%s?s=%" alt="%s" width="%s" height="%s" />', esc_url( $saved ), $size, esc_attr( $alt ), $size, $size );
    }

    //return normal
    return $avatar;

}
add_filter( 'get_avatar', 'twitchpress_slug_get_avatar', 10, 5 );

/**
* Updates user avatar with a 48x48 image with cropping possible...
* 
* @param mixed $wp_user_id
* @param mixed $url
* 
* @version 2.0
* 
* @deprecated
*/
function twitchpress_update_user_meta_avatar( $wp_user_id, $url ) {     
    if( isset( $url ) && $url )
    {           
        return update_user_meta( $wp_user_id, 'twitchpress_avatar_url', $url );                    
    }    
    return false;
}

/**
* Updates user avatar with a 48x48 image with cropping possible...
* 
* @param mixed $wp_user_id
* @param mixed $url
* 
* @version 2.0
*/
function twitchpress_update_user_meta_twitch_logourl( $wp_user_id, $url ) {     
    if( isset( $url ) && $url )
    {   
        return update_user_meta( $wp_user_id, 'twitchpress_twitch_logo_url', $url );                    
    }    
    return false;
}

function twitchpress_save_twitch_logo( $wp_user_id, $url, $display_name, $username ) { 
    $attachment = array(
        'post_title'     => 'Logo Owned By ' . $display_name,
        'post_content'   => 'Copied from Twitch.tv when owner registered on site using Twitch oAuth2.',
        'post_slug'      => 'twitch-logo-' . $username,
        'post_status'    => 'private'
    );
                                                
    $attachment_id = wp_insert_attachment( $attachment, $url, null, true );
    if( $attachment_id !== WP_Error ) {
        // Store new attachment ID in users meta...
        update_user_meta( $wp_user_id, 'twitchpress_twitch_logo_attachment_id', $attachment_id );  
        
        // Improve the attachments meta data...
        $attach_data = wp_generate_attachment_metadata( $attachment_id, $url );
        wp_update_attachment_metadata( $attachment_id, $attach_data );              
    } 
    
    return $attachment_id; 
}

/**
* Redirect during shortcode processing, 
* with parameters for displaying a front-end notice.
* 
* @param mixed $message_source is the plugin name i.e. "core" or "subscribermanagement" or "loginextension" etc
* @param mixed $message_key
* 
* @version 1.0
*/
function twitchpress_shortcode_procedure_redirect( $message_key, $title_values_array = array(), $info_values_array = array(), $message_source = 'twitchpress' ) {
    
    // Store values array in shortlife transient and use when generating output.
    set_transient( 'twitchpress_shortcode_' . $message_source . $message_key, 
        array( 'title_values' => $title_values_array, 'info_values' => $info_values_array ), 120 );
    
    wp_redirect( add_query_arg( array(
        'twitchpress_notice' => time(),
        'key'                => $message_key,        
        'source'             => $message_source,
    ), wp_get_referer() ) );
    exit;    
}

/**
 * Get slug from path
 * @param  string $key
 * @return string
 */
function twitchpress_format_plugin_slug( $key ) {
    $slug = explode( '/', $key );
    $slug = explode( '.', end( $slug ) );
    return $slug[0];
}

/**
 * Get custom capabilities for this package. These are assigned to
 * all administrators and are available for applying to moderator
 * level users.
 * 
 * Caps are assigned during installation or reset.
 *
 * @return array
 * 
 * @version 1.0
 */
function twitchpress_get_core_capabilities() {
    $capabilities = array();

    $capabilities['core'] = array(
        'manage_twitchpress',
    );

    return $capabilities;
}
    
/**
* Returns an array of scopes with user-friendly form input labels and descriptions.
* 
* @author Ryan R. Bayne
* @version 2.0
*/
function twitchpress_scopes( $scope_only = false ) {

    $scope = array(
        'channel_check_subscription' => array(),
        'channel_commercial'         => array(),
        'channel_editor'             => array(),
        'channel_read'               => array(),
        'channel_stream'             => array(),
        'channel_subscriptions'      => array(),
        'collections_edit'           => array(),
        'communities_edit'           => array(),
        'communities_moderate'       => array(),
        'user_blocks_edit'           => array(),
        'user_blocks_read'           => array(),
        'user_follows_edit'          => array(),
        'user_read'                  => array(),
        'user_subscriptions'         => array(),
        'viewing_activity_read'      => array(),
        'openid'                     => array(),           /* New Helix Scopes */
        'analytics:read:extensions'  => array(),// View analytics data for your extensions.
        'analytics:read:games'       => array(),// View analytics data for your games.
        'bits:read'                  => array(),// View Bits information for your channel.
        'clips:edit'                 => array(),// Manage a clip object.
        'user:edit'                  => array(),// Manage a user object.
        'user:edit:broadcast'        => array(),// Edit your channel’s broadcast configuration, including extension configuration. (This scope implies user:read:broadcast capability.)
        'user:read:broadcast'        => array(),// View your broadcasting configuration, including extension configurations.
        'user:read:email'            => array(),// Read authorized user’s email address. 
        'chat:read'                  => array(), 
        'chat:edit'                  => array(),               
    );

    // We can return scopes without additional information.
    if( $scope_only ) { return array_keys( $scope ); }
              
    // Add form input labels for use in form input labels. 
    $scope['user_read']['label']                  = __( 'General Account Details', 'twitchpress' );
    $scope['user_blocks_edit']['label']           = __( 'Ignore Users', 'twitchpress' );
    $scope['user_blocks_read']['label']           = __( 'Get Ignored Users', 'twitchpress' );
    $scope['user_follows_edit']['label']          = __( 'Follow Users', 'twitchpress' );
    $scope['channel_read']['label']               = __( 'Get Channel Data', 'twitchpress' );
    $scope['channel_editor']['label']             = __( 'Edit Channel', 'twitchpress' );
    $scope['channel_commercial']['label']         = __( 'Trigger Commercials', 'twitchpress' );
    $scope['channel_stream']['label']             = __( 'Reset Stream Key', 'twitchpress' );
    $scope['channel_subscriptions']['label']      = __( 'Get Your Subscribers', 'twitchpress' );
    $scope['user_subscriptions']['label']         = __( 'Get Your Subscriptions', 'twitchpress' );
    $scope['channel_check_subscription']['label'] = __( 'Check Viewers Subscription', 'twitchpress' );
    $scope['communities_edit']['label']           = __( 'Manage Users Communities', 'twitchpress' );
    $scope['communities_moderate']['label']       = __( 'Manage Community Moderators', 'twitchpress' );
    $scope['collections_edit']['label']           = __( 'Manage Video Collections', 'twitchpress' );
    $scope['viewing_activity_read']['label']      = __( 'Viewer Heartbeat Service', 'twitchpress' );
    $scope['openid']['label']                     = __( 'OpenID Connect Service', 'twitchpress' );    
    $scope['analytics:read:extensions']['label']  = __( 'New Helix Scope', 'twitchpress' );
    $scope['analytics:read:games']['label']       = __( 'New Helix Scope', 'twitchpress' );
    $scope['bits:read']['label']                  = __( 'New Helix Scope', 'twitchpress' );
    $scope['clips:edit']['label']                 = __( 'New Helix Scope', 'twitchpress' );
    $scope['user:edit']['label']                  = __( 'New Helix Scope', 'twitchpress' );      
    $scope['user:edit:broadcast']['label']        = __( 'New Helix Scope', 'twitchpress' );
    $scope['user:read:broadcast']['label']        = __( 'New Helix Scope', 'twitchpress' );
    $scope['user:read:email']['label']            = __( 'New Helix Scope', 'twitchpress' );
    $scope['chat:read']['label']                  = __( 'New Helix Scope', 'twitchpress' );                    
    $scope['chat:edit']['label']                  = __( 'New Helix Scope', 'twitchpress' );                    
    
    // Add official api descriptions - copied from official API documention.
    $scope['user_read']['apidesc']                  = __( 'Read access to non-public user information, such as email address.', 'twitchpress' );
    $scope['user_blocks_edit']['apidesc']           = __( 'Ability to ignore or unignore on behalf of a user.', 'twitchpress' );
    $scope['user_blocks_read']['apidesc']           = __( 'Read access to a user’s list of ignored users.', 'twitchpress' );
    $scope['user_follows_edit']['apidesc']          = __( 'Access to manage a user’s followed channels.', 'twitchpress' );
    $scope['channel_read']['apidesc']               = __( 'Read access to non-public channel information, including email address and stream key.', 'twitchpress' );
    $scope['channel_editor']['apidesc']             = __( 'Write access to channel metadata (game, status, etc).', 'twitchpress' );
    $scope['channel_commercial']['apidesc']         = __( 'Access to trigger commercials on channel.', 'twitchpress' );
    $scope['channel_stream']['apidesc']             = __( 'Ability to reset a channel’s stream key.', 'twitchpress' );
    $scope['channel_subscriptions']['apidesc']      = __( 'Read access to all subscribers to your channel.', 'twitchpress' );
    $scope['user_subscriptions']['apidesc']         = __( 'Read access to subscriptions of a user.', 'twitchpress' );
    $scope['channel_check_subscription']['apidesc'] = __( 'Read access to check if a user is subscribed to your channel.', 'twitchpress' );
    $scope['communities_edit']['apidesc']           = __( 'Manage a user’s communities.', 'twitchpress' );
    $scope['communities_moderate']['apidesc']       = __( 'Manage community moderators.', 'twitchpress' );
    $scope['collections_edit']['apidesc']           = __( 'Manage a user’s collections (of videos).', 'twitchpress' );
    $scope['viewing_activity_read']['apidesc']      = __( 'Turn on Viewer Heartbeat Service ability to record user data.', 'twitchpress' );
    $scope['openid']['apidesc']                     = __( 'Use OpenID Connect authentication.', 'twitchpress' );    
    $scope['analytics:read:extensions']['apidesc']  = __( 'New Helix Scope', 'twitchpress' );
    $scope['analytics:read:games']['apidesc']       = __( 'New Helix Scope', 'twitchpress' );
    $scope['bits:read']['apidesc']                  = __( 'New Helix Scope', 'twitchpress' );
    $scope['clips:edit']['apidesc']                 = __( 'New Helix Scope', 'twitchpress' );
    $scope['user:edit']['apidesc']                  = __( 'New Helix Scope', 'twitchpress' );
    $scope['user:edit:broadcast']['apidesc']        = __( 'New Helix Scope', 'twitchpress' );
    $scope['user:read:broadcast']['apidesc']        = __( 'New Helix Scope', 'twitchpress' );
    $scope['user:read:email']['apidesc']            = __( 'New Helix Scope', 'twitchpress' );
    $scope['chat:read']['apidesc']                  = __( 'New Helix Scope', 'twitchpress' );
    $scope['chat:edit']['apidesc']                  = __( 'New Helix Scope', 'twitchpress' );
            
    // Add user-friendly descriptions.
    $scope['user_read']['userdesc']                  = __( 'Get email address.', 'twitchpress' );
    $scope['user_blocks_edit']['userdesc']           = __( 'Ability to ignore or unignore other users.', 'twitchpress' );
    $scope['user_blocks_read']['userdesc']           = __( 'Access to your list of ignored users.', 'twitchpress' );
    $scope['user_follows_edit']['userdesc']          = __( 'Permission to manage your followed channels.', 'twitchpress' );
    $scope['channel_read']['userdesc']               = __( 'Read your non-public channel information. Including email address and stream key.', 'twitchpress' );
    $scope['channel_editor']['userdesc']             = __( 'Ability to update meta data like game, status, etc.', 'twitchpress' );
    $scope['channel_commercial']['userdesc']         = __( 'Access to trigger commercials on channel.', 'twitchpress' );
    $scope['channel_stream']['userdesc']             = __( 'Ability to reset your channel’s stream key.', 'twitchpress' );
    $scope['channel_subscriptions']['userdesc']      = __( 'Read access to all subscribers to your channel.', 'twitchpress' );
    $scope['user_subscriptions']['userdesc']         = __( 'Permission to get your subscriptions.', 'twitchpress' );
    $scope['channel_check_subscription']['userdesc'] = __( 'Read access to check if a user is subscribed to your channel.', 'twitchpress' );
    $scope['communities_edit']['userdesc']           = __( 'Manage your user’s communities.', 'twitchpress' );
    $scope['communities_moderate']['userdesc']       = __( 'Manage your community moderators.', 'twitchpress' );
    $scope['collections_edit']['userdesc']           = __( 'Manage your collections (of videos).', 'twitchpress' );
    $scope['viewing_activity_read']['userdesc']      = __( 'Turn on Viewer Heartbeat Service to record your user data.', 'twitchpress' );
    $scope['openid']['userdesc']                     = __( 'Allow your OpenID Connect for authentication on this site.', 'twitchpress' );
    $scope['analytics:read:extensions']['userdesc']  = __( 'New Helix Scope', 'twitchpress' );
    $scope['analytics:read:games']['userdesc']       = __( 'New Helix Scope', 'twitchpress' );
    $scope['bits:read']['userdesc']                  = __( 'New Helix Scope', 'twitchpress' );
    $scope['clips:edit']['userdesc']                 = __( 'New Helix Scope', 'twitchpress' );
    $scope['user:edit']['userdesc']                  = __( 'New Helix Scope', 'twitchpress' );
    $scope['user:edit:broadcast']['userdesc']        = __( 'New Helix Scope', 'twitchpress' );
    $scope['user:read:broadcast']['userdesc']        = __( 'New Helix Scope', 'twitchpress' );
    $scope['user:read:email']['userdesc']            = __( 'New Helix Scope', 'twitchpress' );
    $scope['chat:read']['userdesc']                  = __( 'New Helix Scope', 'twitchpress' );
    $scope['chat:edit']['userdesc']                  = __( 'New Helix Scope', 'twitchpress' );
              
    return $scope;  
}   
    
######################################################################
#                                                                    #
#                              USER                                  #
#                                                                    #
######################################################################

/**
* Checks if the giving user has Twitch API credentials.
* 
* @returns boolean false if no credentials else true
* 
* @param mixed $wp_user_id
* 
* @version 2.5
*/
function twitchpress_is_user_authorized( int $wp_user_id )  
{ 
    if( !get_user_meta( $wp_user_id, 'twitchpress_code', false ) ) {
        return false;
    }    
    if( !get_user_meta( $wp_user_id, 'twitchpress_token', false ) ) {
        return false;
    }    
    return true;
}

/**
* Gets a giving users Twitch credentials from user meta and if no user
* is giving defaults to the current logged in user. 
* 
* @returns mixed array if user has credentials else false.
* @param mixed $user_id
* 
* @version 2.0
*/
function twitchpress_get_user_twitch_credentials( int $user_id ) 
{
    if( !$user_id ) {
        return false;
    } 
    
    if( !$code = twitchpress_get_user_code( $user_id ) ) {  
        return false;
    }
    
    if( !$token = twitchpress_get_user_token( $user_id ) ) {  
        return false;
    }

    return array(
        'code'  => $code,
        'token' => $token
    );
}

/**
* Updates user code and token for Twitch.tv API.
* 
* We always store the Twitch user ID that the code and token matches. This
* will help to avoid mismatched data.
* 
* @param integer $wp_user_id
* @param string $code
* @param string $token
* 
* @version 1.0
*/
function twitchpress_update_user_oauth( int $wp_user_id, string $code, string $token, int $twitch_user_id ) {
    twitchpress_update_user_code( $wp_user_id, $code );
    twitchpress_update_user_token( $wp_user_id, $token ); 
    twitchpress_update_user_twitchid( $wp_user_id, $twitch_user_id );     
}

function twitchpress_update_user_bot_oauth( int $wp_user_id, string $code, string $token, int $twitch_user_id ) {
    twitchpress_update_user_bot_code( $wp_user_id, $code );
    twitchpress_update_user_bot_token( $wp_user_id, $token ); 
    twitchpress_update_user_bot_twitchid( $wp_user_id, $twitch_user_id );     
}

function twitchpress_get_user_twitchid_by_wpid( $user_id ) {
    return get_user_meta( $user_id, 'twitchpress_twitch_id', true );
}

function twitchpress_get_user_bot_twitchid_by_wpid( $user_id ) {
    return get_user_meta( $user_id, 'twitchpress_twitch_bot_id', true );
}

/**
* Update users Twitch ID (in Kraken version 5 user ID and channel ID are the same).
* 
* @param integer $user_id
* @param integer $twitch_user_id
* 
* @version 1.0
*/
function twitchpress_update_user_twitchid( $user_id, $twitch_user_id ) {
    update_user_meta( $user_id, 'twitchpress_twitch_id', $twitch_user_id );    
}

function twitchpress_update_user_bot_twitchid( $user_id, $twitch_user_id ) {
    update_user_meta( $user_id, 'twitchpress_twitch_bot_id', $twitch_user_id );    
}

function twitchpress_get_user_code( $user_id ) {
    return get_user_meta( $user_id, 'twitchpress_code', true );    
}

function twitchpress_get_user_bot_code( $user_id ) {
    return get_user_meta( $user_id, 'twitchpress_bot_code', true );    
}

/**
* Update giving users oauth2 code.
* 
* @param mixed $user_id
* @param mixed $code
* 
* @version 1.0
*/
function twitchpress_update_user_code( $user_id, $code ) { 
    update_user_meta( $user_id, 'twitchpress_auth_time', time() );
    update_user_meta( $user_id, 'twitchpress_code', $code );    
}

function twitchpress_update_user_bot_code( $user_id, $code ) { 
    update_user_meta( $user_id, 'twitchpress_bot_auth_time', time() );
    update_user_meta( $user_id, 'twitchpress_bot_code', $code );    
}

function twitchpress_get_user_token( $user_id ) {
    return get_user_meta( $user_id, 'twitchpress_token', true );    
}

function twitchpress_get_user_bot_token( $user_id ) {
    return get_user_meta( $user_id, 'twitchpress_bot_token', true );    
}

/**
* Update users oauth2 token.
* 
* @param mixed $user_id
* @param mixed $token
* 
* @version 1.0
*/
function twitchpress_update_user_token( $user_id, $token ) { 
    update_user_meta( $user_id, 'twitchpress_auth_time', time() );
    update_user_meta( $user_id, 'twitchpress_token', $token );    
}

function twitchpress_update_user_bot_token( $user_id, $token ) { 
    update_user_meta( $user_id, 'twitchpress_bot_auth_time', time() );
    update_user_meta( $user_id, 'twitchpress_bot_token', $token );    
}

function twitchpress_get_users_token_scopes( $user_id ) {
    return get_user_meta( $user_id, 'twitchpress_token_scope', true );    
}
 
/**
* Get the token_refresh string for extending a session. 
* 
* @param integer $user_id
* @param boolean $single
* 
* @version 1.0
*/
function twitchpress_get_user_token_refresh( $user_id, $single = true ) {
    return get_user_meta( $user_id, 'twitchpress_token_refresh', $single );
}

function twitchpress_get_user_bot_token_refresh( $user_id, $single = true ) {
    return get_user_meta( $user_id, 'twitchpress_bot_token_refresh', $single );
}

/**
* Update users oauth2 token_refresh string.
* 
* @param integer $user_id
* @param boolean $token
* 
* @version 1.0
*/
function twitchpress_update_user_token_refresh( $user_id, $token ) { 
    update_user_meta( $user_id, 'twitchpress_token_refresh', $token );    
}

function twitchpress_update_user_bot_token_refresh( $user_id, $token ) { 
    update_user_meta( $user_id, 'twitchpress_bot_token_refresh', $token );    
}

/**
* Get the giving users Twitch subscription plan for the giving or main channel...
* 
* @param mixed $wp_user_id
* @param mixed $twitch_channel_id
* 
* @version 2.0
*/
function twitchpress_get_sub_plan( $wp_user_id, $twitch_channel_id = null ) {
    if( !$twitch_channel_id ) { $twitch_channel_id = twitchpress_get_main_channels_twitchid(); }
    return get_user_meta( $wp_user_id, 'twitchpress_sub_plan_' . $twitch_channel_id, true  );    
}

function twitchpress_get_bot_sub_plan( $wp_user_id, $twitch_channel_id ) {
    return get_user_meta( $wp_user_id, 'twitchpress_bot_sub_plan_' . $twitch_channel_id, true  );    
}

######################################################################
#                                                                    #
#                        MAIN CHANNEL [GET]                          #
#                                                                    #
######################################################################

/**
* Get the main channel name.
* This is entered by the key holder during the setup wizard.
* 
* @version 2.0
*/
function twitchpress_get_main_channels_name() {
    $obj = TwitchPress_Object_Registry::get( 'mainchannelauth' );
    return isset( $obj->main_channels_name ) ? $obj->main_channels_name : null; 
}

/**
* Get the main/default/official channel ID for the WP site.
* 
* @version 2.0
*/
function twitchpress_get_main_channels_twitchid() {
    $obj = TwitchPress_Object_Registry::get( 'mainchannelauth' );
    return isset( $obj->main_channels_id ) ? $obj->main_channels_id : null;  
}

/**
* Get the channels token which is the same value as the channel owners token but this
* can make it easier to obtain that value outside of a user based procedure.
* 
* @version 2.0 
*/
function twitchpress_get_main_channels_token() {
    $obj = TwitchPress_Object_Registry::get( 'mainchannelauth' );
    return isset( $obj->main_channels_token ) ? $obj->main_channels_token : null;
}

/**
* Get the main channels code which is the same as the channel owners code. 
* 
* @version 2.0
*/
function twitchpress_get_main_channels_code() {
    $obj = TwitchPress_Object_Registry::get( 'mainchannelauth' );
    return isset( $obj->main_channels_code ) ? $obj->main_channels_code : null;
}

/**
* Returns the WordPress ID of the main channel owner.
* This is added to the database during the plugin Setup Wizard.
* 
* @version 2.0
*/
function twitchpress_get_main_channels_wpowner_id() {
    $obj = TwitchPress_Object_Registry::get( 'mainchannelauth' );
    return isset( $obj->main_channels_wpowner_id ) ? $obj->main_channels_wpowner_id : null;
}

function twitchpress_get_main_channels_refresh() {
    $obj = TwitchPress_Object_Registry::get( 'mainchannelauth' );
    return isset( $obj->main_channels_refresh ) ? $obj->main_channels_refresh : null;
}

/**
* Get the scopes that the channel owner agreed to. The value is also stored in user-meta.
* 
* @version 1.0
*/
function twitchpress_get_main_channels_scopes() {
    $obj = TwitchPress_Object_Registry::get( 'mainchannelauth' );
    return isset( $obj->main_channels_scopes ) ? $obj->main_channels_scopes : null;
}

/**
* Get the main/default/official channels related post ID.
* 
* @version 1.0
*/
function twitchpress_get_main_channels_postid() {
    $obj = TwitchPress_Object_Registry::get( 'mainchannelauth' );
    return isset( $obj->main_channels_postid ) ? $obj->main_channels_postid : null;
}

######################################################################
#                                                                    #
#                        MAIN CHANNEL [UPDATE]                       #
#                                                                    #
######################################################################

function twitchpress_update_main_channels_code( $new_code ) {
    $new_code = sanitize_key( $new_code );
    update_option( 'twitchpress_main_channels_code', sanitize_key( $new_code ), false ); 
    return TwitchPress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_code', $new_code );
}

function twitchpress_update_main_channels_wpowner_id( $wp_user_id ) {
    $new_code = sanitize_key( $wp_user_id );
    update_option( 'twitchpress_main_channels_wpowner_id', sanitize_key( $wp_user_id ), false ); 
    return TwitchPress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_wpowner_id', $wp_user_id );
}

function twitchpress_update_main_channels_token( $new_token ) { 
    $new_code = sanitize_key( $new_token );
    update_option( 'twitchpress_main_channels_token', sanitize_key( $new_token ), false ); 
    return TwitchPress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_token', $new_token );
}

/**
* Updates main channels refresh token in options table and object registry.
* 
* @param mixed $new_refresh_token
* 
* @version 2.0
*/
function twitchpress_update_main_channels_refresh_token( $new_refresh_token ) {
    $new_code = sanitize_key( $new_refresh_token );
    update_option( 'twitchpress_main_channels_refresh_token', sanitize_key( $new_refresh_token ), false ); 
    return TwitchPress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_refresh_token', $new_refresh_token );
}

/**
* Updates main channels accepted scopes in relation to the owner/admins accepted
* scopes during authorization. Storing them as the channels scopes is a simplier
* way to obtain the data. 
* 
* Updates option table and object registry.
* 
* @param mixed $new_main_channels_scopes
* 
* @version 2.0
*/
function twitchpress_update_main_channels_scopes( $new_main_channels_scopes ) {
    $new_code = $new_main_channels_scopes;
    update_option( 'twitchpress_main_channels_scopes', $new_main_channels_scopes, false ); 
    return TwitchPress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_scopes', $new_main_channels_scopes );
}

/**
* Updates option table and object registry with new main channel name.
* 
* @param mixed $new_main_channels_name
* 
* @version 2.0
*/
function twitchpress_update_main_channels_name( $new_main_channels_name ) {
    $new_code = sanitize_key( $new_main_channels_name );
    update_option( 'twitchpress_main_channels_name', sanitize_key( $new_main_channels_name ), false ); 
    return TwitchPress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_name', $new_main_channels_name );
}

/**
* Updates option table and object registry with new main channel (twitch)ID.
* 
* @param mixed $new_main_channels_id
* 
* @version 2.0
*/
function twitchpress_update_main_channels_id( $new_main_channels_id ) {
    $new_code = sanitize_key( $new_main_channels_id );
    update_option( 'twitchpress_main_channels_id', sanitize_key( $new_main_channels_id ), false ); 
    return TwitchPress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_id', $new_main_channels_id );
}

/**
* Updates option table and object registry with new main channel post ID.
* 
* @param mixed $new_main_channels_postid
* 
* @version 2.0
*/
function twitchpress_update_main_channels_postid( $new_main_channels_postid ) {
    $new_code = sanitize_key( $new_main_channels_postid );
    update_option( 'twitchpress_main_channels_postid', sanitize_key( $new_main_channels_postid ), false ); 
    return TwitchPress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_postid', $new_main_channels_postid );
}              
             
######################################################################
#                                                                    #
#                        APPLICATION [GET]                           #
#                                                                    #
######################################################################
         
function twitchpress_get_app_id() {
    $obj = TwitchPress_Object_Registry::get( 'twitchapp' );
    return isset( $obj->app_id ) ? $obj->app_id : null;
}          

function twitchpress_get_app_secret() {
    $obj = TwitchPress_Object_Registry::get( 'twitchapp' );
    return isset( $obj->app_secret ) ? $obj->app_secret : null;    
}   

function twitchpress_get_main_client_token() {   
    $obj = TwitchPress_Object_Registry::get( 'twitchapp' );
    return isset( $obj->app_token ) ? $obj->app_token : null;
}  

function twitchpress_get_app_redirect() {
    $obj = TwitchPress_Object_Registry::get( 'twitchapp' );
    return isset( $obj->app_redirect ) ? $obj->app_redirect : null; 
}
                   
function twitchpress_get_app_token() {
    $obj = TwitchPress_Object_Registry::get( 'twitchapp' );
    return isset( $obj->app_token ) ? $obj->app_token : null;    
}

function twitchpress_get_app_token_scopes() {
    $obj = TwitchPress_Object_Registry::get( 'twitchapp' );
    return isset( $obj->app_scopes ) ? $obj->app_scopes : null;    
}

function twitchpress_get_app_token_expiry() {
    $obj = TwitchPress_Object_Registry::get( 'twitchapp' );
    return isset( $obj->token_expiry ) ? $obj->token_expiry : null;    
}

######################################################################
#                                                                    #
#                         BOT CHANNEL [GET]                          #
#                                                                    #
######################################################################

/**
* Get the bot channel name.
* 
* @version 2.0
*/
function twitchpress_get_bot_channels_name() {
    $obj = TwitchPress_Object_Registry::get( 'botchannelauth' );
    return isset( $obj->bot_channels_name ) ? $obj->bot_channels_name : null; 
}

/**
* Get the bot channel ID for the WP site.
* 
* @version 2.0
*/
function twitchpress_get_bot_channels_twitchid() {
    $obj = TwitchPress_Object_Registry::get( 'botchannelauth' );
    return isset( $obj->bot_channels_id ) ? $obj->bot_channels_id : null;  
}

/**
* Get the bot channels token which is the same value as the channel owners token but this
* can make it easier to obtain that value outside of a user based procedure.
* 
* @version 2.0 
*/
function twitchpress_get_bot_channels_token() {
    $obj = TwitchPress_Object_Registry::get( 'botchannelauth' );
    return isset( $obj->bot_channels_token ) ? $obj->bot_channels_token : null;
}

/**
* Get the bot channels code which is the same as the channel owners code. 
* 
* @version 2.0
*/
function twitchpress_get_bot_channels_code() {
    $obj = TwitchPress_Object_Registry::get( 'botchannelauth' );
    return isset( $obj->bot_channels_code ) ? $obj->bot_channels_code : null;
}

/**
* Returns the WordPress ID of the bot channel owner.
* This is added to the database during the plugin Setup Wizard.
* 
* @version 2.0
*/
function twitchpress_get_bot_channels_wpowner_id() {
    $obj = TwitchPress_Object_Registry::get( 'botchannelauth' );
    return isset( $obj->bot_channels_wpowner_id ) ? $obj->bot_channels_wpowner_id : null;
}

function twitchpress_get_bot_channels_refresh() {
    $obj = TwitchPress_Object_Registry::get( 'botchannelauth' );
    return isset( $obj->bot_channels_refresh ) ? $obj->bot_channels_refresh : null;
}

/**
* Get the scopes that the channel owner agreed to. The value is also stored in user-meta.
* 
* @version 1.0
*/
function twitchpress_get_bot_channels_scopes() {
    $obj = TwitchPress_Object_Registry::get( 'botchannelauth' );
    return isset( $obj->bot_channels_scopes ) ? $obj->bot_channels_scopes : null;
}

/**
* Get the main/default/official channels related post ID.
* 
* @version 1.0
*/
function twitchpress_get_bot_channels_postid() {
    $obj = TwitchPress_Object_Registry::get( 'botchannelauth' );
    return isset( $obj->bot_channels_postid ) ? $obj->bot_channels_postid : null;
}

######################################################################
#                                                                    #
#                        BOT CHANNEL [UPDATE]                       #
#                                                                    #
######################################################################

function twitchpress_update_bot_channels_code( $new_code ) {
    $new_code = sanitize_key( $new_code );
    update_option( 'twitchpress_bot_channels_code', sanitize_key( $new_code ), false ); 
    return TwitchPress_Object_Registry::update_var( 'botchannelauth', 'bot_channels_code', $new_code );
}

function twitchpress_update_bot_channels_wpowner_id( $wp_user_id ) {
    $new_code = sanitize_key( $wp_user_id );
    update_option( 'twitchpress_bot_channels_wpowner_id', sanitize_key( $wp_user_id ), false ); 
    return TwitchPress_Object_Registry::update_var( 'botchannelauth', 'bot_channels_wpowner_id', $wp_user_id );
}

function twitchpress_update_bot_channels_token( $new_token ) { 
    $new_code = sanitize_key( $new_token );
    update_option( 'twitchpress_bot_channels_token', sanitize_key( $new_token ), false ); 
    return TwitchPress_Object_Registry::update_var( 'botchannelauth', 'bot_channels_token', $new_token );
}

/**
* Updates bot channels refresh token in options table and object registry.
* 
* @param mixed $new_refresh_token
* 
* @version 2.0
*/
function twitchpress_update_bot_channels_refresh_token( $new_refresh_token ) {
    $new_code = sanitize_key( $new_refresh_token );
    update_option( 'twitchpress_bot_channels_refresh_token', sanitize_key( $new_refresh_token ), false ); 
    return TwitchPress_Object_Registry::update_var( 'botchannelauth', 'bot_channels_refresh_token', $new_refresh_token );
}

/**
* Updates bot channels accepted scopes in relation to the owner/admins accepted
* scopes during authorization. Storing them as the channels scopes is a simplier
* way to obtain the data. 
* 
* Updates option table and object registry.
* 
* @param mixed $new_bot_channels_scopes
* 
* @version 2.0
*/
function twitchpress_update_bot_channels_scopes( $new_bot_channels_scopes ) {
    $new_code = $new_bot_channels_scopes;
    update_option( 'twitchpress_bot_channels_scopes', $new_bot_channels_scopes, false ); 
    return TwitchPress_Object_Registry::update_var( 'botchannelauth', 'bot_channels_scopes', $new_bot_channels_scopes );
}

/**
* Updates option table and object registry with new bot channel name.
* 
* @param mixed $new_bot_channels_name
* 
* @version 2.0
*/
function twitchpress_update_bot_channels_name( $new_bot_channels_name ) {
    $new_code = sanitize_key( $new_bot_channels_name );
    update_option( 'twitchpress_bot_channels_name', sanitize_key( $new_bot_channels_name ), false ); 
    return TwitchPress_Object_Registry::update_var( 'botchannelauth', 'bot_channels_name', $new_bot_channels_name );
}

/**
* Updates option table and object registry with new bot channel (twitch)ID.
* 
* @param mixed $new_bot_channels_id
* 
* @version 2.0
*/
function twitchpress_update_bot_channels_id( $new_bot_channels_id ) {
    $new_code = sanitize_key( $new_bot_channels_id );
    update_option( 'twitchpress_bot_channels_id', sanitize_key( $new_bot_channels_id ), false ); 
    return TwitchPress_Object_Registry::update_var( 'botchannelauth', 'bot_channels_id', $new_bot_channels_id );
}

/**
* Updates option table and object registry with new bot channel post ID.
* 
* @param mixed $new_bot_channels_postid
* 
* @version 2.0
*/
function twitchpress_update_bot_channels_postid( $new_bot_channels_postid ) {
    $new_code = sanitize_key( $new_bot_channels_postid );
    update_option( 'twitchpress_bot_channels_postid', sanitize_key( $new_bot_channels_postid ), false ); 
    return TwitchPress_Object_Registry::update_var( 'botchannelauth', 'bot_channels_postid', $new_bot_channels_postid );
}        

######################################################################
#                                                                    #
#                      APPLICATION [UPDATE]                          #
#                                                                    #
######################################################################

function twitchpress_update_app_id( $new_app_id ) {
    update_option( 'twitchpress_app_id', $new_app_id, true );
    return TwitchPress_Object_Registry::update_var( 'twitchapp', 'app_id', $new_app_id );    
}

function twitchpress_update_app_secret( $new_app_secret ) {
    update_option( 'twitchpress_app_secret', $new_app_secret, true );
    return TwitchPress_Object_Registry::update_var( 'twitchapp', 'app_secret', $new_app_secret );    
}

function twitchpress_update_app_redirect( $new_app_redirect ) {
    update_option( 'twitchpress_app_redirect', $new_app_redirect, true );
    return TwitchPress_Object_Registry::update_var( 'twitchapp', 'app_redirect', $new_app_redirect );    
}

function twitchpress_update_app_token( $new_app_token ) {
    update_option( 'twitchpress_app_token', $new_app_token, true );
    return TwitchPress_Object_Registry::update_var( 'twitchapp', 'app_token', $new_app_token );    
}

function twitchpress_update_app_token_expiry( $new_app_token_expiry ) {
    update_option( 'twitchpress_app_expiry', $new_app_token_expiry, true );
    return TwitchPress_Object_Registry::update_var( 'twitchapp', 'app_expiry', $new_app_token_expiry );    
}

function twitchpress_update_app_token_scopes( $new_app_scopes ) {
    update_option( 'twitchpress_app_scopes', $new_app_scopes, true );
    return TwitchPress_Object_Registry::update_var( 'twitchapp', 'app_scopes', $new_app_scopes );    
}
                        
/**
* Gets the required visitor scope setup by administrator.
* 
* @version 3.0
*/
function twitchpress_get_visitor_scopes() {
    $visitor_scopes = array();
    
    $all_scopes = twitchpress_scopes( true );

    foreach( $all_scopes as $key => $scope ) {
        if( get_option( 'twitchpress_visitor_scope_' . str_replace( ':', '_', $scope ) ) == 'yes' ) {
            $visitor_scopes[] = $scope;
        }
    }       

    return $visitor_scopes;        
} 

/**
* Each scope is stored in an individual option. Use this method when
* an array of them is required. 
* 
* Usually when a scope name exists in options, it is an accepted scope. We will
* not assume it though. 
* 
* @version 3.0
*/
function twitchpress_get_global_accepted_scopes() {
    $global_accepted_scopes = array();

    foreach( twitchpress_scopes( true ) as $key => $scope ) {
        if( get_option( 'twitchpress_scope_' . str_replace( ':', '_', $scope ) ) == 'yes' ) {
            $global_accepted_scopes[] = $scope;
        }
    }       
   
    return $global_accepted_scopes;
}

/**
* Confirms if the $scope has been permitted for the
* $side the call applies to.
* 
* Should be called at the beginning of most calls methods. 
* 
* The $function is passed to aid debugging. 
* 
* @param mixed $scope
* @param mixed $side
* @param mixed $function
* 
* @version 3.0
*/
function twitchpress_confirm_scope( $scope, $side = 'channel', $function = null ) {

    // Confirm $scope is a real Twitch API permission. 
    if( !array_key_exists( $scope, twitchpress_scopes() ) ) {
        return __( 'Twitch API request is using an invalid scope. See %s()', 'twitchpress' );
    }    
    
    // Check applicable $side array scope.
    switch ( $side ) {
       case 'user':
            if( !in_array( $scope, twitchpress_get_visitor_scopes() ) ) { 
                ### error 
                return sprintf( __( 'TwitchPress requires visitor scope: %s for function %s()', 'twitchpress' ), $scope, $function ); }
         break;           
       case 'channel':
            if( !in_array( $scope, twitchpress_get_global_accepted_scopes() ) ) { 
                ### error
                return sprintf( __( 'TwitchPress scope %s was not permitted by administration and is required by %s().', 'twitchpress' ), $scope, $function ); }
         break;         
       case 'both':
            // This measure is temporary, to avoid faults, until we confirm which $side some calls apply to. 
            if( !in_array( $scope, twitchpress_get_global_accepted_scopes() ) &&
                    !in_array( $scope, twitchpress_get_visitor_scopes() ) ) { 
                        ### error 
                        return __( 'A Kraken5 call requires a scope that has not been permitted.', 'twitchpress' ); 
            }
         break;
    }
    
    // Arriving here means the scope is valid and was found. 
    return true;
}

/**
* Generate an oAuth2 Twitch API URL for an administrator only. The procedure
* for public visitors will use different methods for total clarity when it comes to
* security. 
* 
* @author Ryan Bayne
* @version 6.0
* 
* @param array $permitted_scopes
* @param array $state_array
*/
function twitchpress_generate_authorization_url( $permitted_scopes, $local_state ) {

    // Scope value will be a random code that can be matched to a transient on return.
    if( !isset( $local_state['random14'] ) ) { $local_state['random14'] = twitchpress_random14();}
    
    // Primary request handler - value is checked on return from Twitch.tv
    set_transient( 'twitchpress_oauth_' . $local_state['random14'], $local_state, 6000 );

    $scope = twitchpress_prepare_scopes( $permitted_scopes, true );
          
    // Build Twitch.tv oauth2 URL...
    $url = 'https://id.twitch.tv/oauth2/authorize?' .
        'response_type=code' . '&' .
        'client_id=' . twitchpress_get_app_id() . '&' .
        'redirect_uri=' . get_option( 'twitchpress_app_redirect', 'Redirect Value Not Set In WordPress' ) . '&' .
        'scope=' . $scope . '&' .
        'state=' . $local_state['random14'];

    return $url;       
}

/**
 * is_ajax - Returns true when the page is loaded via ajax.
 * 
 * The DOING_AJAX constant is set by WordPress.
 * 
 * @return bool
 */
function twitchpress_is_ajax() {          
    return defined( 'DOING_AJAX' );
}
    
/**
* Check if the home URL (stored during WordPress installation) is HTTPS. 
* If it is, we don't need to do things such as 'force ssl'.
*
* @return bool
*/
function twitchpress_is_https() {      
    return false !== strstr( get_option( 'home' ), 'https:' );
}

/**
* Determine if on the dashboard page. 
* 
* $current_screen is not set early enough for calling in some actions. So use this
* function instead.
*/
function twitchpress_is_dashboard() {      
    global $pagenow;
    // method one: check $pagenow value which could be "index.php" and that means the dashboard
    if( isset( $pagenow ) && $pagenow == 'index.php' ) { return true; }
    // method two: should $pagenow not be set, check the server value
    return strstr( $this->PHP->currenturl(), 'wp-admin/index.php' );
}

/**
* Use to check for Ajax or XMLRPC request. Use this function to avoid
* running none urgent tasks during existing operations and demanding requests.
*/
function twitchpress_is_background_process() {   
    if ( ( 'wp-login.php' === basename( $_SERVER['SCRIPT_FILENAME'] ) )
        || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
        || ( defined( 'DOING_CRON' ) && DOING_CRON )
        || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            return true;
    }
               
    return false;
}

/**
 * Output any queued javascript code in the footer.
 */
function twitchpress_print_js() {
    global $twitchpress_queued_js;

    if ( ! empty( $twitchpress_queued_js ) ) {
        // Sanitize.
        $twitchpress_queued_js = wp_check_invalid_utf8( $twitchpress_queued_js );
        $twitchpress_queued_js = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", $twitchpress_queued_js );
        $twitchpress_queued_js = str_replace( "\r", '', $twitchpress_queued_js );

        $js = "<!-- TwitchPress JavaScript -->\n<script type=\"text/javascript\">\njQuery(function($) { $twitchpress_queued_js });\n</script>\n";

        /**
         * twitchpress_queued_js filter.
         *
         * @since 2.6.0
         * @param string $js JavaScript code.
         */
        echo apply_filters( 'twitchpress_queued_js', $js );

        unset( $twitchpress_queued_js );
    }
}

/**
 * Display a WordPress TwitchPress help tip.
 *
 * @since  2.5.0
 *
 * @param  string $tip        Help tip text
 * @param  bool   $allow_html Allow sanitized HTML if true or escape
 * @return string
 * 
 * @version 2.0
 */
function twitchpress_help_tip( $tip, $allow_html = false ) {
    if ( $allow_html ) {
        $tip = twitchpress_sanitize_tooltip( $tip );
    } else {
        $tip = esc_attr( $tip );
    }

    return '<span class="twitchpress-help-tip" data-tip="' . $tip . '"></span>';
}

/**
 * Queue some JavaScript code to be output in the footer.
 *
 * @param string $code
 */
function twitchpress_enqueue_js( $code ) {
    global $twitchpress_queued_js;

    if ( empty( $twitchpress_queued_js ) ) {
        $twitchpress_queued_js = '';
    }

    $twitchpress_queued_js .= "\n" . $code . "\n";
}

/**
 * Get permalink settings for TwitchPress independent of the user locale.
 *
 * @since  1.0.0
 * @return array
 * 
 * @version 2.0
 */
function twitchpress_get_permalink_structure() {
    if ( function_exists( 'switch_to_locale' ) && did_action( 'admin_init' ) ) {
        switch_to_locale( get_locale() );
    }
                      
    $permalinks = wp_parse_args( (array) get_option( 'twitchpress_permalinks', array() ), array(
        'twitchpress_base'       => '',
        'category_base'          => '',
        'tag_base'               => '',
        'attribute_base'         => '',
        'use_verbose_page_rules' => false,
    ) );

    // Ensure rewrite slugs are set.
    $permalinks['channels_rewrite_slug']  = untrailingslashit( empty( $permalinks['channel_base'] )   ? _x( 'twitchchannels',            'slug', 'twitchpress' ) : $permalinks['twitchchannel_base'] );
    $permalinks['perks_rewrite_slug']     = untrailingslashit( empty( $permalinks['perks_base'] )     ? _x( 'perks',                     'slug', 'twitchpress' ) : $permalinks['twitchperks_base'] );
    $permalinks['giveaways_rewrite_slug'] = untrailingslashit( empty( $permalinks['giveaways_base'] ) ? _x( 'giveaways',                 'slug', 'twitchpress' ) : $permalinks['twitchgiveaways_base'] );
    $permalinks['attribute_rewrite_slug'] = untrailingslashit( empty( $permalinks['attribute_base'] ) ? '' : $permalinks['attribute_base'] );

    if ( function_exists( 'restore_current_locale' ) && did_action( 'admin_init' ) ) {
        restore_current_locale();
    }
    return $permalinks;
}

/**
* Log a PHP error with extra information. Bypasses any WP configuration.

* Common Use: twitchpress_error( 'DEEPTRACE', 0, null, null, __LINE__, __FUNCTION__, __CLASS__, time() );
* 
* @version 1.2
* 
* @param string $message
* @param int $message_type 0=PHP logger|1=Email|2=Deprecated|3=Append to file|4=SAPI logging handler
* @param string $destination
* @param string $extra_headers
* @param mixed $line
* @param mixed $function
* @param mixed $class
* @param mixed $time
*/
function twitchpress_error( $message, $message_type = 0, $destination = null, $extra_headers = null, $line = null, $function = null, $class = null, $time = null ) {
    $error = 'TwitchPress Plugin: ';
    $error .= $message;
    $error .= ' (get squeekycoder@gmail.com)';
    
    // Add extra information. 
    if( $line != null || $function != null || $class != null || $time != null )
    {
        if( $line )
        {
            $error .= ' Line: ' . $line;
        }    
        
        if( $function )
        {
            $error .= ' Function: ' . $function;
        }
        
        if( $class )
        {
            $error .= ' Class: ' . $class;    
        }
        
        if( $time )
        {
            $error .= ' Time: ' . $time;
        }
    }

    return error_log( $error, $message_type, $destination, $extra_headers );
}

/**
* Create a nonced URL for returning to the current page.
* 
* @param mixed $new_parameters_array
* 
* @version 1.2
*/
function twitchpress_returning_url_nonced( $new_parameters_array, $action, $specified_url = null  ) {
    $url = add_query_arg( $new_parameters_array, $specified_url );
    
    $url = wp_nonce_url( $url, $action );
    
    return $url;
} 

/**
 * What type of request is this?
 *
 * Functions and constants are WordPress core. This function will allow
 * you to avoid large operations or output at the wrong time.
 * 
 * @param  string $type admin, ajax, cron or frontend.
 * @return bool
 */
function twitchpress_is_request( $type ) {
    switch ( $type ) {
        case 'admin' :
            return is_admin();
        case 'ajax' :
            return defined( 'DOING_AJAX' );
        case 'cron' :
            return defined( 'DOING_CRON' );
        case 'frontend' :
            return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
    }
} 

/**
* Validate the value passed as a $_GET['code'] prior to using it.
* 
* @return boolean false if not valid else true
* 
* @version 1.0
*/
function twitchpress_validate_code( $code ) {
    if( strlen ( $code ) !== 30  ) {
        return false;
    }           
    
    if( !ctype_alnum( $code ) ) {
        return false;
    }
    
    return true;
}      

/**
* Validate a token value.
* 
* @return boolean false if not valid else true
* 
* @version 1.0
*/
function twitchpress_validate_token( $token ) {
    if( strlen ( $token ) !== 30  ) {    
        return false;
    }           
    
    if( !ctype_alnum( $token ) ) {      
        return false;
    }
         
    return true;
}    

/**
* Determines if the value returned by generateToken() is a token or not.
* 
* Does not check if the token is valid as this is intended for use straight
* after a token is generated. 
* 
* @returns boolean true if the value appears normal.
* 
* @version 1.0
*/
function twitchpress_was_valid_token_returned( $returned_value ){
                                             
    if( !array( $returned_value ) ) {        
        return false;
    }
    
    if( !isset( $returned_value['access_token'] ) ) {
        return false;
    }

    if( !twitchpress_validate_token( $returned_value['access_token'] ) ) {
        return false;
    }
    
    return true;
}                     

/**
* A helix function for confirming valid access has been granted through a token...
* 
* @returns boolean true if the value appears normal.
* 
* @version 1.0
*/
function twitchpress_was_valid_token_returned_from_helix( $token_obj ){
                                         
    if( !is_object( $token_obj ) ) {     
        return false;
    }
    
    if( !isset( $token_obj->access_token ) ) {     
        return false;
    }

    if( !twitchpress_validate_token( $token_obj->access_token ) ) {      
        return false;
    }
    
    return true;
}
      
/**
* Schedule an event for syncing feed posts into WP.
* 
* @version 1.0
*/
function twitchpress_schedule_sync_channel_to_wp() {
    wp_schedule_event(
        time() + 2,
        3600,
        'twitchpress_sync_feed_to_wp'
    );    
}

/**
* Queries the custom post type 'twitchchannels' and returns post ID's that
* have a specific meta key and specific meta value.
* 
* @version 1.0
*/
function twitchpress_get_channel_posts_by_meta( $post_meta_key, $post_meta_value, $limit = 100 ) {
    // args to query for your key
    $args = array(
        'post_type' => 'channels',
        'meta_query' => array(
            array(
                'key' => $post_meta_key,
                'value' => $post_meta_value
            )
        ),
        'fields' => 'ids'
    );
    
    // perform the query
    $query = new WP_Query( $args );
  
    if ( !empty( $query->posts ) ) {     
        return true;
    }

    return false;    
}

/**
* Check if giving post name (slug) already exists in wp_posts.
* 
* @param mixed $post_name
* 
* @version 1.0
*/
function twitchpress_does_post_name_exist( $post_name ) {
    global $wpdb;
    $result = $wpdb->get_var( $wpdb->prepare( "SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = '%s'", $post_name ), 'ARRAY_A' );
    if( $result ) {
        return true;
    } else {
        return false;
    }
}

/**
* Checks if a channel ID exists in post meta for custom post type "twitchchannels"
* 
* @returns boolean true if the Twitch channel ID already exists in post meta.
*  
* @param mixed $channel_id
* 
* @version 1.0
*/
function twitchpress_channelid_in_postmeta( $channel_id ) {
    // args to query for your key
    $args = array(
        'post_type' => 'channels',
        'meta_query' => array(
            array(
                'key' => 'twitchpress_channel_id',
                'value' => $channel_id
            )
        ),
        'fields' => 'ids'
    );
    
    // perform the query
    $query = new WP_Query( $args );
  
    if ( !empty( $query->posts ) ) {     
        return true;
    }

    return false;
}

/**
* Converts "2016-11-29T15:52:27Z" format into a timestamp. 
* 
* @param mixed $date_time_string
* 
* @version 1.0
*/
function twitchpress_convert_created_at_to_timestamp( $date_time_string ) {  
    return date_timestamp_get( date_create( $date_time_string ) );      
}

/**
* Gets a channel post 
* 
* @param mixed $channel_id
*/
function twitchpress_get_channel_post( $channel_id ) {
    // args to query for your key
    $args = array(
        'post_type' => 'channels',
        'meta_query' => array(
            array(
                'key' => 'twitchpress_channel_id',
                'value' => $channel_id
            )
        ),
    );
    
    // perform the query
    $query = new WP_Query( $args );
                            
    if ( !empty( $query->posts ) ) {     
        return $query->posts[0]->ID;
    }

    return false;     
}

/**
* Checks if the giving post type is one that
* has been permitted for sharing to Twitch channel feeds.
* 
* @version 1.0
* 
* @param string $post_type
*/
function twitchpress_is_posttype_shareable( $post_type ) {
    if( get_option( 'twitchpress_shareable_posttype_' . $post_type ) ) {
        return true;
    }
    return false;
}

/**
* Handles redirects with log entries and added arguments to URL for 
* easy visual monitoring.
* 
* @param mixed $url
* @param mixed $line
* @param mixed $function
* @param mixed $file
* 
* @version 2.0
*/
function twitchpress_redirect_tracking( $url, $line, $function, $file = '', $safe = false ) {

    $redirect_counter = 1;
    
    // Refuse the redirect and log if twitchpressredirected=2 in giving $url. 
    if( strstr( $url, 'twitchpressredirected=1' ) ) 
    {
        ++$redirect_counter;
    }
    elseif( strstr( $url, 'twitchpressredirected=2' ) )
    {
        return;
    }
                                
    // Tracking adds more values to help trace where redirect was requested. 
    if( get_option( 'twitchpress_redirect_tracking_switch' ) == 'yes' ) 
    {
        $url = add_query_arg( array( 'redirected-line' => $line, 'redirected-function' => $function ), esc_url_raw( $url ) );
    }    
    
    if( $safe ) 
    {
        wp_safe_redirect( add_query_arg( array( 'twitchpressredirected' => $redirect_counter ), $url ) );
        exit;
    }  
    
    // Add twitchpressredirected to show that the URL has had a redirect. 
    // If it ever becomes normal to redirect again, we can increase the integer.
    wp_redirect( add_query_arg( array( 'twitchpressredirected' => $redirect_counter ), $url ) );
    exit;
}

/**
* Determines if giving value is a valid Twitch subscription plan. 
* 
* @param mixed $value
* 
* @returns boolean true if the $value is valid.
* 
* @version 1.0
*/
function twitchpress_is_valid_sub_plan( $value ){
    $sub_plans = array( 'prime', 1000, 2000, 3000 );
    if( !is_string( $value ) && !is_numeric( $value ) ){ return false;}
    if( is_string( $value ) ){ $value = strtolower( $value ); }
    if( in_array( $value, $sub_plans ) ) { return true;}
    return false;
}

/**
* Generates a random 14 character string.
* 
* @version 2.0
*/
function twitchpress_random14(){ 
    return rand( 10000000, 99999999 ) . rand( 100000, 999999 );   
}

/**
* Dump the giving value but only if the current user is allowed to see dumps. 
* 
* @param mixed $var
* 
* @version 2.0
*/
function var_dump_twitchpress( $var ) {       
    if( !function_exists( 'wp_get_current_user' ) ) { return null; }
    if( !twitchpress_are_errors_allowed() ) { return false; }
    echo '<pre>'; var_dump( $var ); echo '</pre>';
}

/**
* Like twitchpress_var_dump() but always requires an administrator and does not
* need to be removed before version release.
* 
* @param mixed $var
* @param mixed $function
* @param mixed $line
* @param mixed $file
* @param mixed $wp_die
* 
* @version 0.1
*/
function twitchpress_var_dump_safer( $var  ) {
                                    
    if( !twitchpress_are_errors_allowed() ){ return false; }
    
    $bt = debug_backtrace();
    
    $header = '<h2>Developer Information</h2><ul>';
                                    
    if( $bt[0]['function'] ) {
        $header .= '<li>Function: ' . $bt[0]['function'] . '</li>'; 
    }
    if( $bt[0]['line'] ) {
        $header .= '<li>Line: ' . $bt[0]['line'] . '</li>';    
    }
    if( $bt[0]['file'] ) {
        $header .= '<li>File: ' . $bt[0]['file'] . '</li></ul>';    
    }

    echo '<pre>'; var_dump( $header ); echo '</pre>';   
    echo '<pre>'; var_dump( $var ); echo '</pre>';   
    
    wp_die(); 
}

/**
* The original var_dump() with some formatting and settings control.
* 
* Not as safe as twitchpress_var_dump_safer() because it does not require an
* administrator however it does allow the use of a specific user only which
* will usually be an administrator. 
* 
* If that setting is not in use then even a visitor can see the dump - required
* for front-end testing when not writing to file (the recommended approach)
* 
* @param mixed $var
* @param mixed $wp_die
* 
* @version 2.0
*/
function twitchpress_var_dump( $var, $wp_die = false  ) {
    
    $bt = debug_backtrace();
    
    $header = '<h2>Developer Information</h2><ul>';
                                    
    if( $bt[0]['function'] ) {
        $header .= '<li>Function: ' . $bt[0]['function'] . '</li>'; 
    }
    if( $bt[0]['line'] ) {
        $header .= '<li>Line: ' . $bt[0]['line'] . '</li>';    
    }
    if( $bt[0]['file'] ) {
        $header .= '<li>File: ' . $bt[0]['file'] . '</li></ul>';    
    }

    $whitelist = array(
        '127.0.0.1',
        '::1'
    );

    if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
        return;
    }

    echo '<pre>'; var_dump( $header ); echo '</pre>';   
    echo '<pre>'; var_dump( $var ); echo '</pre>';   
    
    wp_die(); 
}

function wp_die_twitchpress( $html ) {
    if( !twitchpress_are_errors_allowed() ){ return; }
    wp_die( esc_html( $html ) ); 
}

/**
* Checks if the current user is permitted to view 
* error dumps for the entire blog.
* 
* Assumes the BugNet library.
* 
* @version 1.0
*/
function twitchpress_are_errors_allowed() {
                                                 
    if( twitchpress_is_background_process() ) {  
        return false; 
    }    
               
    if( !get_option( 'twitchpress_displayerrors' ) || get_option( 'twitchpress_displayerrors' ) !== 'yes' ) {
      return false;
    }

    // We can bypass the protection to display errors for a specified user.
    if( 'BYPASS' == get_option( 'bugnet_error_dump_user_id') ) {  
       return true;    
    } 
    
    // A value of ADMIN allows anyone with "activate_plugins" permission to see errors.
    if( !current_user_can( 'activate_plugins' ) ) {    
       return false;
    }  
    elseif( 'ADMIN' == get_option( 'bugnet_error_dump_user_id') ) { 
       return true;    
    }

    // Match current users ID to the entered ID which restricts error display to a single user.
    if( get_current_user_id() != get_option( 'bugnet_error_dump_user_id') ) {  
       return false;    
    } 

    return true;
}

/**
* Adds spaces between each scope as required by the Twitch API. 
* 
* @param mixed $scopes_array
* @param mixed $for_url
* 
* @version 1.5
*/
function twitchpress_prepare_scopes( array $scopes_array ) {
        $scopes_string = '';

        foreach ( $scopes_array as $s ){

            $scopes_string .= $s . '+';
        }

        $prepped_scopes = rtrim( $scopes_string, '+' );
        
        return $prepped_scopes;
}

function twitchpress_scopecheckbox_required_icon( $scope ){
    global $system_scopes_status;
 
    $required = false; 
    
    // Do not assume every extension has set this global properly. 
    if( !is_array( $system_scopes_status ) || empty( $system_scopes_status ) ) { return ''; }
    
    // Check if $scope is required for the admins main account. 
    foreach( $system_scopes_status['admin'] as $extension_slug => $scope_information )
    {
        if( in_array( $scope, $scope_information['required'] ) ) { $required = true; break; }                      
    }    
    
    if( $required ) 
    {
        $icon = '<span class="dashicons dashicons-yes"></span>';
    }
    else
    {
        $icon = '<span class="dashicons dashicons-no"></span>';
    }
    
    return $icon;
}

function twitchpress_scopecheckboxpublic_required_icon( $scope ){
    global $system_scopes_status;
                 
    $required = false; 
    
    // Do not assume every extension has set this global properly. 
    if( !is_array( $system_scopes_status ) || empty( $system_scopes_status ) ) { return ''; }

    // Check if $scope is required for visitors accounts. 
    foreach( $system_scopes_status['public'] as $extension_slug => $scope_information )
    {
        if( in_array( $scope, $scope_information['required'] ) ) { $required = true; break; }     
    }

    if( $required ) 
    {
        $icon = '<span class="dashicons dashicons-yes"></span>';
    }
    else
    {
        $icon = '<span class="dashicons dashicons-no"></span>';
    }
    
    return $icon;
}

/**
* Get a Twitch users Twitch ID.
* 
* @version 1.0
* 
* @return integer from Twitch user object or false if failure detected.
*/
function twitchpress_get_user_twitchid( $twitch_username ) {
    if( TWITCHPRESS_API_NAME == 'kraken' ) 
    {
        $kraken = new TWITCHPRESS_Twitch_API_Calls();
        $user_object = $kraken->get_users( $twitch_username );
        
        if( isset( $user_object['users'][0]['_id'] ) && is_numeric( $user_object['users'][0]['_id'] ) ) {
            return $user_object['users'][0]['_id'];
        } else {                                 
            return false;
        }        
    }
    else
    {   
        $helix = new TwitchPress_Twitch_API();
        $user_object = $helix->get_users( null, $twitch_username ); 
           
        if( isset( $user_object['users'][0]['_id'] ) && is_numeric( $user_object['users'][0]['_id'] ) ) {
            return $user_object['users'][0]['_id'];
        } else {                                 
            return false;
        }        
    }
}

/**
* CSS for API Requests table.
* 
* @version 1.0
*/
function twitchpress_css_listtable_apirequests() {
    if( !isset( $_GET['page'] ) ) { return; }
    if( !isset( $_GET['tab'] ) ) { return; }
    if( $_GET['page'] !== 'twitchpress_data' ) { return; }
    if( $_GET['tab'] !== 'kraken5requests_list_tables' ) { return; }
    
    echo '<style type="text/css">';
    echo '.wp-list-table .column-time { width: 10%; }';
    echo '.wp-list-table .column-function { width: 20%; }';
    echo '.wp-list-table .column-header { width: 30%; }';
    echo '.wp-list-table .column-url { width: 20%; }';
    echo '</style>';
    
}
add_action('admin_head', 'twitchpress_css_listtable_apirequests');

/**
* CSS for API Errors table.
* 
* @version 1.0
*/
function twitchpress_css_listtable_apiresponses() {
    if( !isset( $_GET['page'] ) ) { return; }
    if( !isset( $_GET['tab'] ) ) { return; }
    if( $_GET['page'] !== 'twitchpress_data' ) { return; }
    if( $_GET['tab'] !== 'apiresponses_list_tables' ) { return; }
    
    echo '<style type="text/css">';
    echo '.wp-list-table .column-time { width: 10%; }';
    echo '.wp-list-table .column-httpdstatus { width: 10%; }';
    echo '.wp-list-table .column-function { width: 20%; }';
    echo '.wp-list-table .column-error_no { width: 10%; }';
    echo '.wp-list-table .column-result { width: 50%; }';
    echo '</style>';
    
}
add_action('admin_head', 'twitchpress_css_listtable_apiresponses');

/**
* CSS for API Errors table.
* 
* @version 1.0
*/
function twitchpress_css_listtable_apierrors() {
    if( !isset( $_GET['page'] ) ) { return; }
    if( !isset( $_GET['tab'] ) ) { return; }
    if( $_GET['page'] !== 'twitchpress_data' ) { return; }
    if( $_GET['tab'] !== 'apierrors_list_tables' ) { return; }
    
    echo '<style type="text/css">';
    echo '.wp-list-table .column-time { width: 10%; }';
    echo '.wp-list-table .column-function { width: 20%; }';
    echo '.wp-list-table .column-error_string { width: 30%; }';
    echo '.wp-list-table .column-error_no { width: 10%; }';
    echo '.wp-list-table .column-curl_url { width: 40%; }';
    echo '</style>';
    
}
add_action('admin_head', 'twitchpress_css_listtable_apierrors');

/**
* Get the sync timing array which holds delays for top level sync activity.
* 
* This option avoids having to creation options per service at the top level
* but if needed services can have additional options to control individual
* processes.
* 
* @version 1.0
*/
function twitchpress_get_sync_timing() {
    $sync_timing_array = get_option( 'twitchpress_sync_timing' );
    if( !$sync_timing_array || !is_array( $sync_timing_array ) ) { return array(); }
    return $sync_timing_array;
}

function twitchpress_update_sync_timing( $sync_timing_array ) {
    update_option( 'twitchpress_sync_timing', $sync_timing_array, false );    
}

/**
* Add a new sync time for a giving procedure. 
* 
* @param mixed $file
* @param mixed $function
* @param mixed $line
* @param mixed $delay
* 
* @version 1.0
*/
function twitchpress_add_sync_timing( $file, $function, $line, $delay ) {
    $sync_timing_array = twitchpress_get_sync_timing();
    $sync_timing_array[$file][$function][$line]['delay'] = $delay;
    $sync_timing_array[$file][$function][$line]['time'] = time();
    twitchpress_update_sync_timing( $sync_timing_array );    
}

/**
* A standard method for establishing time delay and if a giving method is
* due to run. Use this within any function/method to end it early. 
* 
* Sets new time() if due to make it easier to manage delays within procedures. 
* 
* @param mixed $function
* @param mixed $line
* @param mixed $file
* @param mixed $delay
* 
* @returns boolean true if delay has passed already else false.
* 
* @version 2.0
*/
function twitchpress_is_sync_due( $file, $function, $line, $delay ) {
    $sync_timing_array = twitchpress_get_sync_timing();
    
    // Init the delay for the first time
    if( !isset( $sync_timing_array[$file][$function][$line] ) )
    {
        twitchpress_add_sync_timing( $file, $function, $line, $delay );
        return true;    
    }    
    else
    {
        $last_time = $sync_timing_array[$file][$function][$line]['time'];
        $soonest_time = $last_time + $delay;
        if( $soonest_time > time() ) 
        {
            $sync_timing_array[$file][$function][$line]['delay'] = $delay;
            $sync_timing_array[$file][$function][$line]['time'] = time();
            twitchpress_update_sync_timing( $sync_timing_array );
            return true;    
        }   
        
        // Not enough time has passed since the last event. 
        return false;
    }
}

function twitchpress_flood_protector(  $file, $function, $line, $delay ) {
    twitchpress_is_sync_due( $file, $function, $line, $delay );    
}

/**
* Determines if the current logged in user is also the owner of the main channel.
* 
* @version 2.0
*/
function twitchpress_is_current_user_main_channel_owner( $user_id = null ) {
    if( !$user_id )
    {
        $user_id = get_current_user_id();
    }
    
    // Avoid processing the owner of the main channel (might not be admin with ID 1)
    if( twitchpress_get_main_channels_wpowner_id() == $user_id ) { return true; }
    return false;    
}

/**
* Returns the user meta value for the last time their Twitch data (as a whole)
* was synced with WordPress. Do not use this when dealing with individual
* values such as the users Twitch subscription to the main channel. 
* 
* @returns integer time set using time() or false/null. 
* @version 1.0
*/
function twitchpress_get_user_sync_time( $user_id ) {
    return get_user_meta( $user_id, 'twitchpress_sync_time', true );
}

/**
* Use when handling the WordPress current authenticated user. 
* 
* This function is used to determine if the current user needs to be
* processed within the function or should the procedure be ended early. 
* 
* @version 1.0
*/
function twitchpress_is_current_user_sync_due( $function, $line, $file, $seconds ) {
    return twitchpress_is_users_sync_due( TWITCHPRESS_CURRENTUSERID, $function, $line, $file, $seconds );
}

/**
* Not the same as twitchpress_get_user_sync_time() which is a basic timer for
* when syncing large amounts of data. 
* 
* This function can be used when making calls to the API for smaller amounts of data
* and so allows anti-flooding in more locations while not using a single value to
* block all requests.
* 
* @param mixed $wp_user_id
* @param mixed $function
* @param mixed $line
* @param mixed $file
* @param mixed $seconds
* 
* @version 2.0
*/
function twitchpress_is_users_sync_due( $wp_user_id, $function, $line, $file, $seconds ) {
  
    $sync_data = twitchpress_get_current_user_sync_transient();
    if( isset( $sync_data[ $wp_user_id ][$function][$line][$file] ) )
    {
        $earliest_time = $sync_data[ $wp_user_id ][$function][$line][$file] + $seconds; 
        
        if( $earliest_time < time() )
        {
            $sync_data[ $wp_user_id ][$function][$line][$file] = time();
            
            twitchpress_update_current_user_sync_transient( $sync_data );
            
            return true;// because $seconds have passed since the last sync! 
        }    
        else
        {
            return false;// because $seconds have not yet passed since the last sync!
        }
    }
    else
    {   
        $sync_data[ $wp_user_id ][$function][$line][$file] = time();
        twitchpress_update_current_user_sync_transient( $sync_data );
        return true;    
    }
}

function twitchpress_update_current_user_sync_transient( $sync_data ) {
    delete_transient( 'twitchpress_current_user_syncing_' . date('Ymd') );
    set_transient( 'twitchpress_current_user_syncing_' . date('Ymd'), $sync_data, 3600 );    
}

/**
* A daily transient is created for storing some user related sync data. Rather than
* make many user meta data values we maintain one array and can add it to the object
* registry after it's initial use. 
* 
* @returns boolean false if no data has been set before else returns array
* 
* @version 1.0 
*/
function twitchpress_get_current_user_sync_transient() {
    $transient_name = 'twitchpress_current_user_syncing_' . date('Ymd');

    // Get or set transient array of user sync times...
    $trans_val = get_transient( $transient_name );
    if( $trans_val && !is_array( $trans_val ) ) 
    {
        twitchpress_update_current_user_sync_transient( array() );
        return false; 
    }
    
    return $trans_val;
}

/**
* Creates a unique transient named based on API request.
* 
* @param mixed $endpoint
* @param mixed $originating_function
* @param mixed $origination_line
* 
* @version 2.0
*/
function twitchpress_encode_transient_name( $endpoint, $originating_function, $origination_line ) {
    return base64_encode( serialize( array( $endpoint, $originating_function, $origination_line ) ) );   
}

function twitchpress_get_call_count() {
    return get_option( 'twitchpress_twitchapi_call_count' );
}

function twitchpress_get_new_call_id() {
    $old_call_count = twitchpress_get_call_count();
    $new_call_count = $old_call_count + 1;
    update_option( 'twitchpress_twitchapi_call_count', $new_call_count, true );
    return $new_call_count;       
}

function twitchpress_login_prevent_redirect() {
    if( 'yes' == get_option( 'twitchpress_login_prevent_redirect' ) ) {
        return true;
    }
    return false;
}

/**
* Will create or update a Twitch subscribers personal post...
* 
* @returns boolean as indicator of insert or update of post (true if new post is created else false) 
* 
* @param mixed $sub
* 
* @version 1.0
*/
function twitchpress_save_subscribers_post( $sub ) {

    // Check for existing twitchchannels post.    
    $args = array(
        'post_type'  => 'channels',
        'meta_query' => array(
            array(
                'key'   => 'twitch_user_id',
                'value' => $sub['user']['_id'],
            )
        )
    );
    $postslist = get_posts( $args );
           
    if( !empty( $postslist ) && is_array( $postslist ) )
    {   
        if( isset( $postslist[0]->ID ) ) 
        {
            update_post_meta( $postslist[0]->ID, 'twitch_sub_id',            $sub['_id'] );
            update_post_meta( $postslist[0]->ID, 'twitch_sub_created_at',    $sub['created_at'] );
            update_post_meta( $postslist[0]->ID, 'twitch_sub_plan',          $sub['sub_plan'] );
            update_post_meta( $postslist[0]->ID, 'twitch_sub_plan_name',     $sub['sub_plan_name'] );
            update_post_meta( $postslist[0]->ID, 'twitch_user_created_at',   $sub['user']['created_at'] );
            update_post_meta( $postslist[0]->ID, 'twitch_user_display_name', $sub['user']['display_name'] );
            update_post_meta( $postslist[0]->ID, 'twitch_user_name',         $sub['user']['name'] );
            update_post_meta( $postslist[0]->ID, 'twitch_user_type',         $sub['user']['type'] );
            update_post_meta( $postslist[0]->ID, 'twitch_user_updated_at',   $sub['user']['updated_at'] );       
        }
        
        return false;
    }    
    
    // Insert a new channel based on the users data
    $postarr = array(
        'post_author'  => 1,
        'post_content' => $sub['user']['bio'],                                                    
        'post_title' => $sub['user']['display_name'],                           
        'post_excerpt' => $sub['user']['bio'],                           
        'post_status' => 'publish',                             
        'post_type' => 'channels',                                                                 
    ); 

    $post_id = wp_insert_post( $postarr, true );
    
    if( is_wp_error( $post_id ) ) 
    {
        return false;
    }    

    add_post_meta( $post_id, 'twitch_user_id',           $sub['user']['_id'] );  
        add_post_meta( $post_id, 'twitch_sub_id',            $sub['_id'] );
        add_post_meta( $post_id, 'twitch_sub_created_at',    $sub['created_at'] );
        add_post_meta( $post_id, 'twitch_sub_plan',          $sub['sub_plan'] );
        add_post_meta( $post_id, 'twitch_sub_plan_name',     $sub['sub_plan_name'] );
        add_post_meta( $post_id, 'twitch_user_created_at',   $sub['user']['created_at'] );
        add_post_meta( $post_id, 'twitch_user_display_name', $sub['user']['display_name'] );
        add_post_meta( $post_id, 'twitch_user_name',         $sub['user']['name'] );
        add_post_meta( $post_id, 'twitch_user_type',         $sub['user']['type'] );
        add_post_meta( $post_id, 'twitch_user_updated_at',   $sub['user']['updated_at'] );
        
        return true;
}

/**
* Channel side request for subscription data.
* 
* @returns true if a change is made and false if no change to sub status has been made.
* 
* @version 1.0
*/
function twitchpress_main_channel_sub_sync_helix( $wp_user_id, $output_notice = false ) {
    $helix = new TWITCHPRESS_Twitch_API();  
        
    $channel_id = twitchpress_get_main_channels_twitchid();
    $channel_token = twitchpress_get_main_channels_token();

    // Setup a call name for tracing. 
    $helix->twitch_call_name = __( 'Sync Users Subscription', 'twitchpress' );

    $users_twitch_id = get_user_meta( $wp_user_id, 'twitchpress_twitch_id', true );

    // Check channel subscription from channel side (does not require scope permission)
    $twitch_sub_response = $helix->get_broadcasters_subscribers( $channel_id, $users_twitch_id );      
    
    // Get possible existing sub plan from an earlier sub sync...
    $local_sub_plan = get_user_meta( $wp_user_id, 'twitchpress_sub_plan_' . $channel_id, true  );
    
    // No subscription - update WP to match Twitch.tv sub state...
    if( isset( $twitch_sub_response['error'] ) || $twitch_sub_response === null ) 
    {               
        // Avoid the do_action() if subplan isn't actually valid...
        if( twitchpress_is_valid_sub_plan( $local_sub_plan ) ) 
        {      
            // Remove the sub plan value to ensure there is no mistake when it comes to user access.
            delete_user_meta( $wp_user_id, 'twitchpress_sub_plan_' . $channel_id );  
            delete_user_meta( $wp_user_id, 'twitchpress_sub_plan_name_' . $channel_id );  
            
            do_action( 'twitchpress_sync_discontinued_twitch_subscriber', $wp_user_id, $channel_id );

            return;
        }       

        // Arriving here means no active Twitch sub and no local history of a sub.
        do_action( 'twitchpress_sync_never_a_twitch_subscriber', $wp_user_id, $channel_id );

        return;
    }
    elseif( isset( $twitch_sub_response->tier ) )
    {
        // The visitor is a subscriber to the main channel. 
        // The sub status is boolean only.
        update_user_meta( $wp_user_id, 'twitchpress_substatus_mainchannel', true );
        
        // Actions should rely on the twitchpress_substatus_mainchannel option only as others are updated later.
        do_action( 'twitchpress_sync_user_subscribes_to_channel', array( $users_twitch_id, $channel_id ) );    
         
        if( !twitchpress_is_valid_sub_plan( $local_sub_plan ) ) 
        {      
            // User is being registered as a Twitch sub for the first time.
            update_user_meta( $wp_user_id, 'twitchpress_sub_plan_' . $channel_id, $twitch_sub_response->tier );
            update_user_meta( $wp_user_id, 'twitchpress_sub_plan_name_' . $channel_id, $twitch_sub_response->plan_name );

            do_action( 'twitchpress_sync_new_twitch_subscriber', $wp_user_id, $channel_id, $twitch_sub_response->tier );                
        
            return;
        } 
        elseif( twitchpress_is_valid_sub_plan( $local_sub_plan ) ) 
        {  
            // User is not a newely detected subscriber and has sub history stored in WP, check for sub plan change. 
 
            if( $twitch_sub_response->tier !== $local_sub_plan )
            { 
                // User has changed their subscription plan and are still subscribing.
                update_user_meta( $wp_user_id, 'twitchpress_sub_plan_' . $channel_id, $twitch_sub_response->tier );                        
                update_user_meta( $wp_user_id, 'twitchpress_sub_plan_name_' . $channel_id, $twitch_sub_response->tier );                        
            }

            do_action( 'twitchpress_sync_continuing_twitch_subscriber', $wp_user_id, $channel_id );

            return;
        } 
    }  
}

/**
* Apply during WP user registration to immediatly get their Twitch info...
* 
* @param mixed $wp_user_id
* 
* @version 1.0
*/
function twitchpress_sync_user_on_registration( $wp_user_id ) {
    $sync = new TwitchPress_Systematic_Syncing();

    // Begin tracing to
    bugnet_new_trace( 'twitchsubonreg', 'Twitch Sub Status', false, 50 );
         
    // Now begin syncing the newly registered user user...
    $sync->sync_user( $wp_user_id, true, false, 'user' );    
}
          
/**
* Get Twitch app overall status from object registry...
*             
* @version 1.0
*/
function twitchpress_get_app_status() {
    $obj = TwitchPress_Object_Registry::get( 'twitchapp');
    return isset( $obj->app_status ) ? $obj->app_status : null;
}

function twitchpress_get_default_stream_team() {
    return TWITCHPRESS_STREAM_TEAM;
}

/**
* Check if giving user is following the main/default or giving channel...
* 
* @param mixed $wp_user_id
* 
* @version 1.0
*/
function twitchpress_is_user_following( $wp_user_id, $channel = 'main' ) {
    return get_user_meta( $wp_user_id, 'twitchpress_following_' . $channel, true );
}

/**
* Check if user is subscribing or not...
* 
* @returns boolean
* 
* @param mixed $wp_user_id
* @param mixed $channel
* 
* @version 1.0
*/
function twitchpress_is_user_subscribing( $wp_user_id, $channel = 'main' ) {
    if( twitchpress_get_sub_plan( $wp_user_id ) ) { return true; }
    return false; 
}