<?php
/**
* Basic array of all known options with multiple uses planned.
* 
* @version 1.0
*/
function twitchpress_options_array() {

    return array(
        'misc'     => twitchpress_options_misc(),
        'api'      => twitchpress_options_twitch_api(),
        'switch'   => twitchpress_options_switch(),
        'otherapi' => twitchpress_options_otherapi(),
        'scope'    => twitchpress_options_scope(),
        'bugnet'   => twitchpress_options_bugnet(),
    );   
     
}

function twitchpress_options_misc() {
    $arr = array();

    // Get Cheermotes
    $arr[ 'twitchpress_admin_notices' ] = array();
    $arr[ 'twitchpress_displayerrors' ] = array();
    $arr[ 'twitchpress_db_version' ] = array();
    $arr[ 'twitchpress_feedback_data' ] = array();
    $arr[ 'twitchpress_feedback_prompt' ] = array();
    $arr[ 'twitchpress_login_messages' ] = array();
    $arr[ 'twitchpress_removeall' ] = array();
    $arr[ 'twitchpress_remove_options' ] = array();
    $arr[ 'twitchpress_remove_feed_posts' ] = array();
    $arr[ 'twitchpress_remove_database_tables' ] = array();
    $arr[ 'twitchpress_remove_extensions' ] = array();
    $arr[ 'twitchpress_remove_user_data' ] = array();
    $arr[ 'twitchpress_remove_media' ] = array();
    $arr[ 'twitchpress_sync_job_channel_subscribers' ] = array();
    $arr[ 'twitchpress_sync_timing' ] = array();
    $arr[ 'twitchpress_version' ] = array();
    $arr[ 'twitchpress_feedback_data' ] = array();
    $arr[ 'twitchpress_feedback_prompt' ] = array();
    $arr[ 'twitchpress_displayerrors' ] = array();
    $arr[ 'twitchress_redirect_tracking_switch' ] = array();
    $arr[ 'twitchpress_new_channeltowp' ] = array();
    $arr[ 'twitchpress_new_wptochannel' ] = array();
    $arr[ 'twitchpress_apply_prepend_value_all_posts' ] = array();
    $arr[ 'twitchpress_prepend_value_all_posts' ] = array();
    $arr[ 'twitchpress_apply_appending_value_all_posts' ] = array();
    $arr[ 'twitchpress_appending_value_all_posts' ] = array();
    $arr[ 'twitchpress_apply_feed_sync_limits' ] = array();
    $arr[ 'twitchpress_feed_sync_limit_hourly' ] = array();
    $arr[ 'twitchpress_feed_sync_channel_limit_daily' ] = array();
    $arr[ 'twitchpress_shareable_posttype_twitchfeed' ] = array();
    $arr[ 'twitchpress_shareable_posttype_post' ] = array();
    $arr[ 'twitchpress_shareable_posttype_page' ] = array();

    return $arr;        
}

function twitchpress_options_twitch_api() {
    
    $arr = array();

    // Get Cheermotes
    $arr[ 'twitchpress_app_id' ] = array();
    $arr[ 'twitchpress_app_secret' ] = array();
    $arr[ 'twitchpress_app_redirect' ] = array();
    $arr[ 'twitchpress_app_token' ] = array();
    $arr[ 'twitchpress_app_token_scopes' ] = array();
    $arr[ 'twitchpress_main_channels_code' ] = array();
    $arr[ 'twitchpress_main_channels_wpowner_id' ] = array();
    $arr[ 'twitchpress_main_channels_token' ] = array();
    $arr[ 'twitchpress_main_channels_refresh' ] = array();
    $arr[ 'twitchpress_main_channels_scopes' ] = array();
    $arr[ 'twitchpress_main_channel_name' ] = array();
    $arr[ 'twitchpress_main_channel_id' ] = array();
    $arr[ 'twitchpress_main_client_secret' ] = array();
    $arr[ 'twitchpress_main_client_id' ] = array();
    $arr[ 'twitchpress_main_redirect_uri' ] = array();

    return $arr;

}

function twitchpress_options_switch() {
    
    $arr = array();

    // Get Cheermotes
    $arr[ 'twitchpress_admin_notices' ] = array();
    $arr[ 'twitchpress_switch_twitter_api_services' ] = array();
    $arr[ 'twitchpress_switch_twitter_api_logs' ] = array();
    $arr[ 'twitchpress_switch_youtube_api_services' ] = array();
    $arr[ 'twitchpress_switch_youtube_api_logs' ] = array();
    $arr[ 'twitchpress_switch_steam_api_services' ] = array();
    $arr[ 'twitchpress_switch_steam_api_logs' ] = array();
    $arr[ 'twitchpress_switch_facebook_api_services' ] = array();
    $arr[ 'twitchpress_switch_facebook_api_logs' ] = array();
    $arr[ 'twitchpress_switch_deepbot_api_services' ] = array();
    $arr[ 'twitchpress_switch_deepbot_api_logs' ] = array();
    $arr[ 'twitchpress_switch_streamtip_api_services' ] = array();
    $arr[ 'twitchpress_switch_streamtip_api_logs' ] = array();
    $arr[ 'twitchpress_switch_discord_api_services' ] = array();
    $arr[ 'twitchpress_switch_discord_api_logs' ] = array();
    $arr[ 'twitchpress_switch_streamlabs_api_services' ] = array();
    $arr[ 'twitchpress_switch_streamlabs_api_logs' ] = array();
    $arr[ 'twitchpress_serviceswitch_feeds_posttofeed' ] = array();
    $arr[ 'twitchpress_serviceswitch_feeds_scheduledposts' ] = array();
    $arr[ 'twitchpress_serviceswitch_feeds_prependappend' ] = array();
    $arr[ 'twitchpress_serviceswitch_channels_takeownership' ] = array();
    $arr[ 'twitchpress_serviceswitch_channels_editcontent' ] = array();
    $arr[ 'twitchpress_serviceswitch_channels_controlchatdisplay' ] = array();

    return $arr;
    
}

function twitchpress_options_otherapi() {

    $arr = array();

    // Get Cheermotes
    $arr[ 'twitchpress_otherapi_application_saving' ] = array();
    $arr[ 'twitchpress_api_redirect_uri_twitter' ] = array();
    $arr[ 'twitchpress_api_id_twitter' ] = array();
    $arr[ 'twitchpress_api_secret_twitter' ] = array();
    $arr[ 'twitchpress_otherapi_application_saving' ] = array();
    $arr[ 'twitchpress_api_redirect_uri_youtube' ] = array();
    $arr[ 'twitchpress_api_id_youtube' ] = array();
    $arr[ 'twitchpress_api_secret_youtube' ] = array();
    $arr[ 'twitchpress_otherapi_application_saving' ] = array();
    $arr[ 'twitchpress_api_redirect_uri_steam' ] = array();
    $arr[ 'twitchpress_api_id_steam' ] = array();
    $arr[ 'twitchpress_api_secret_steam' ] = array();
    $arr[ 'twitchpress_otherapi_application_saving' ] = array();
    $arr[ 'twitchpress_api_redirect_uri_facebook' ] = array();
    $arr[ 'twitchpress_api_id_facebook' ] = array();
    $arr[ 'twitchpress_api_secret_facebook' ] = array();
    $arr[ 'twitchpress_otherapi_application_saving' ] = array();
    $arr[ 'twitchpress_api_redirect_uri_deepbot' ] = array();
    $arr[ 'twitchpress_api_id_deepbot' ] = array();
    $arr[ 'twitchpress_api_secret_deepbot' ] = array();
    $arr[ 'twitchpress_otherapi_application_saving' ] = array();
    $arr[ 'twitchpress_api_redirect_uri_streamtip' ] = array();
    $arr[ 'twitchpress_api_id_streamtip' ] = array();
    $arr[ 'twitchpress_api_secret_streamtip' ] = array();
    $arr[ 'twitchpress_otherapi_application_saving' ] = array();
    $arr[ 'twitchpress_api_redirect_uri_discord' ] = array();
    $arr[ 'twitchpress_api_id_discord' ] = array();
    $arr[ 'twitchpress_api_secret_discord' ] = array();
    $arr[ 'twitchpress_otherapi_application_saving' ] = array();
    $arr[ 'twitchpress_api_redirect_uri_streamlabs' ] = array();
    $arr[ 'twitchpress_api_id_streamlabs' ] = array();
    $arr[ 'twitchpress_api_secret_streamlabs' ] = array();

    return $arr;
      
}

function twitchpress_options_scope() {
    
    $arr = array();

    $arr[ 'twitchpress_scope_channel_check_subscription' ] = array();
    $arr[ 'twitchpress_scope_channel_commercial' ] = array();
    $arr[ 'twitchpress_scope_channel_editor' ] = array();
    $arr[ 'twitchpress_scope_channel_feed_edit' ] = array();
    $arr[ 'twitchpress_scope_channel_feed_read' ] = array();
    $arr[ 'twitchpress_scope_channel_read' ] = array();
    $arr[ 'twitchpress_scope_channel_stream' ] = array();
    $arr[ 'twitchpress_scope_channel_subscriptions' ] = array();
    $arr[ 'twitchpress_scope_chat_login' ] = array();
    $arr[ 'twitchpress_scope_collections_edit' ] = array();
    $arr[ 'twitchpress_scope_communities_edit' ] = array();
    $arr[ 'twitchpress_scope_communities_moderate' ] = array();
    $arr[ 'twitchpress_scope_user_blocks_edit' ] = array();
    $arr[ 'twitchpress_scope_user_blocks_read' ] = array();
    $arr[ 'twitchpress_scope_user_follows_edit' ] = array();
    $arr[ 'twitchpress_scope_user_read' ] = array();
    $arr[ 'twitchpress_scope_user_subscriptions' ] = array();
    $arr[ 'twitchpress_scope_viewing_activity_read' ] = array();
    $arr[ 'twitchpress_scope_openid' ] = array();
    
    return $arr;

}

function twitchpress_options_bugnet() {

    $arr = array();

    $arr[ 'bugnet_activate_events' ] = array();
    $arr[ 'bugnet_activate_log' ] = array();
    $arr[ 'bugnet_activate_tracing' ] = array();
    $arr[ 'bugnet_levelswitch_emergency' ] = array();
    $arr[ 'bugnet_levelswitch_alert' ] = array();
    $arr[ 'bugnet_levelswitch_critical' ] = array();
    $arr[ 'bugnet_levelswitch_error' ] = array();
    $arr[ 'bugnet_levelswitch_warning' ] = array();
    $arr[ 'bugnet_levelswitch_notice' ] = array();
    $arr[ 'bugnet_handlerswitch_email' ] = array();
    $arr[ 'bugnet_handlerswitch_logfiles' ] = array();
    $arr[ 'bugnet_handlerswitch_restapi' ] = array();
    $arr[ 'bugnet_handlerswitch_tracing' ] = array();
    $arr[ 'bugnet_handlerswitch_wpdb' ] = array();
    $arr[ 'bugnet_reportsswitch_dailysummary' ] = array();
    $arr[ 'bugnet_reportsswitch_eventsnapshot' ] = array();
    $arr[ 'bugnet_reportsswitch_tracecomplete' ] = array();
    $arr[ 'bugnet_systemlogging_switch' ] = array();
    $arr[ 'bugnet_error_dump_user_id' ] = array();

    return $arr;
 
}