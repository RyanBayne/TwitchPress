<?php
/**
 * Twitter API Endpoints.
 * 
 * This data is used to create a layer of endpoint validation and 
 * support development tools or just debugging feedback. 
 * 
 * I would also like to track changes to the API here by keeping
 * old endpoints as depreciated. 
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress Twitter Library
 * @version     1.0.0
 */
 
function twitchpress_twitter_endpoints() {
    return array(
        'authentication' => twitchpress_twitter_endpoints_authentication(),
        'subscribe'      => twitchpress_twitter_endpoints_subscribe(),
        'account'        => twitchpress_twitter_endpoints_account(), 
    );
}

function twitchpress_twitter_endpoints_() {
    $endpoints = array();

    // 
    $endpoints[] = array( 
        'name'     => __( '', 'twitchpress' ),
        'desc'     => __( '', 'twitchpress' ),
        'url'      => '',
        'type'     => '',
    );
    
    return $endpoints;
}
    
function twitchpress_twitter_endpoints_authentication() {
    $endpoints = array();

    // Access Token
    $endpoints[] = array( 
        'name'     => __( 'Access Token', 'twitchpress' ),
        'desc'     => __( 'Allows a Consumer application to exchange the OAuth Request Token for an OAuth Access Token. This method fulfills Section 6.3 of the OAuth 1.0 authentication flow.', 'twitchpress' ),
        'url'      => 'https://api.twitter.com/oauth/access_token',
        'type'     => 'post'
    );

    // Authenticate
    $endpoints[] = array( 
        'name'     => __( 'Authenticate', 'twitchpress' ),
        'desc'     => __( 'Allows a Consumer application to use an OAuth request_token to request user authorization.', 'twitchpress' ),
        'url'      => 'https://api.twitter.com/oauth/authenticate',
        'type'     => 'get',
    );
    
    // Authorize
    $endpoints[] = array( 
        'name'     => __( 'Authorize', 'twitchpress' ),
        'desc'     => __( 'Allows a Consumer application to use an OAuth Request Token to request user authorization.', 'twitchpress' ),
        'url'      => 'https://api.twitter.com/oauth/authorize',
        'type'     => 'get',
    );
    
    // Invalidate Token
    $endpoints[] = array( 
        'name'     => __( 'Invalidate Token', 'twitchpress' ),
        'desc'     => __( 'Allows a registered application to revoke an issued OAuth 2 Bearer Token by presenting its client credentials. Once a Bearer Token has been invalidated, new creation attempts will yield a different Bearer Token and usage of the invalidated token will no longer be allowed.', 'twitchpress' ),
        'url'      => 'https://api.twitter.com/oauth2/invalidate_token',
        'type'     => 'post',
    );
    
    // Request Token
    $endpoints[] = array( 
        'name'     => __( 'Request Token', 'twitchpress' ),
        'desc'     => __( 'Allows a Consumer application to obtain an OAuth Request Token to request user authorization. ', 'twitchpress' ),
        'url'      => 'https://api.twitter.com/oauth/request_token',
        'type'     => 'post',
    );
    
    // Token
    $endpoints[] = array( 
        'name'     => __( 'Token', 'twitchpress' ),
        'desc'     => __( 'Allows a registered application to obtain an OAuth 2 Bearer Token, which can be used to make API requests on an application’s own behalf, without a user context. This is called Application-only authentication.', 'twitchpress' ),
        'url'      => 'https://api.twitter.com/oauth2/token',
        'type'     => 'post',
    );
        
    return $endpoints;
}

function twitchpress_twitter_endpoints_subscribe() {
    $endpoints = array();

    // Delete Subscription
    $endpoints[] = array( 
        'name'     => __( 'Delete Subscription', 'twitchpress' ),
        'desc'     => __( 'Deactivates subscription for the provided user context and app. After deactivation, all DM events for the requesting user will no longer be sent to the webhook URL.', 'twitchpress' ),
        'url'      => 'https://api.twitter.com/1.1/account_activity/webhooks/:webhook_id/subscriptions.json',
        'type'     => 'delete',
    );

    // Delete Webook Configuration
    $endpoints[] = array( 
        'name'     => __( 'Delete Webook Configuration', 'twitchpress' ),
        'desc'     => __( 'Removes the webhook from the provided application’s configuration. The webhook ID can be accessed by making a call to GET /1.1/account_activity/webhooks.', 'twitchpress' ),
        'url'      => 'https://api.twitter.com/1.1/account_activity/webhooks/:webhook_id.json',
        'type'     => 'delete',
    );

    // Get Subscriptions
    $endpoints[] = array( 
        'name'     => __( 'Get Subscriptions', 'twitchpress' ),
        'desc'     => __( 'Provides a way to determine if a webhook configuration is subscribed to the provided user’s Direct Messages. If the provided user context has an active subscription with the provided app, returns 204 OK. If the response code is not 204, then the user does not have an active subscription. See HTTP Response code and error messages below for details.', 'twitchpress' ),
        'url'      => 'https://api.twitter.com/1.1/account_activity/webhooks/:webhook_id/subscriptions.json',
        'type'     => 'get',
    );

    // Get Webhook Config
    $endpoints[] = array( 
        'name'     => __( 'Get Webhook Configuration', 'twitchpress' ),
        'desc'     => __( 'Returns all URLs and their statuses for the given app. Currently, only one webhook URL can be registered to an application.', 'twitchpress' ),
        'url'      => 'https://api.twitter.com/1.1/account_activity/webhooks.json',
        'type'     => 'get',
    );

    // New Subscription
    $endpoints[] = array( 
        'name'     => __( 'New Subscription', 'twitchpress' ),
        'desc'     => __( 'Subscribes the provided app to events for the provided user context. When subscribed, all DM events for the provided user will be sent to the app’s webhook via POST request.', 'twitchpress' ),
        'url'      => 'https://api.twitter.com/1.1/account_activity/webhooks/:webhook_id/subscriptions.json',
        'type'     => 'post',
    );

    // New Webhook Config
    $endpoints[] = array( 
        'name'     => __( 'New Webhook Configuration', 'twitchpress' ),
        'desc'     => __( 'Registers a new webhook URL for the given application context. The URL will be validated via CRC request before saving. In case the validation fails, an error is returned. Only one webhook URL can be registered to an application.', 'twitchpress' ),
        'url'      => 'https://api.twitter.com/1.1/account_activity/webhooks.json',
        'type'     => 'post',
    );

    // Validate Webhook Config
    $endpoints[] = array( 
        'name'     => __( 'Validate Webhook Config', 'twitchpress' ),
        'desc'     => __( 'Triggers the challenge response check (CRC) for the given webhook’s URL. If the check is successful, returns 204 and reenables the webhook by setting its status to valid.', 'twitchpress' ),
        'url'      => 'https://api.twitter.com/1.1/account_activity/webhooks/:webhook_id.json',
        'type'     => 'put',
    );

    // Site Stream
    $endpoints[] = array( 
        'name'     => __( 'Site Stream', 'twitchpress' ),
        'desc'     => __( 'Streams messages for a set of users.', 'twitchpress' ),
        'url'      => 'https://sitestream.twitter.com/1.1/site.json',
        'type'     => 'get',
    );

    // User Stream
    $endpoints[] = array( 
        'name'     => __( 'User Stream', 'twitchpress' ),
        'desc'     => __( 'Streams messages for a single user.', 'twitchpress' ),
        'url'      => 'https://userstream.twitter.com/1.1/user.json',
        'type'     => 'get',
    );
    
    return $endpoints;
}

function twitchpress_twitter_endpoints_account() {
    $endpoints = array();

    // Get Account Settings
    $endpoints[] = array( 
        'name'     => __( 'Get Account Settings', 'twitchpress' ),
        'desc'     => __( 'Returns settings (including current trend, geo and sleep time information) for the authenticating user.', 'twitchpress' ),
        'url'      => 'https://api.twitter.com/1.1/account/settings.json',
        'type'     => 'get',
    );

    // Get Account Verify Credentials
    $endpoints[] = array( 
        'name'     => __( 'Get Account Verify Credentials', 'twitchpress' ),
        'desc'     => __( 'Returns an HTTP 200 OK response code and a representation of the requesting user if authentication was successful; returns a 401 status code and an error message if not. Use this method to test if supplied user credentials are valid.', 'twitchpress' ),
        'url'      => 'https://api.twitter.com/1.1/account/verify_credentials.json',
        'type'     => 'get',
    );

    // Get Users Profile Banner
    $endpoints[] = array( 
        'name'     => __( 'Get Users Profile Banner', 'twitchpress' ),
        'desc'     => __( 'Returns a map of the available size variations of the specified user’s profile banner. If the user has not uploaded a profile banner, a HTTP 404 will be served instead. This method can be used instead of string manipulation on the profile_banner_url returned in user objects as described in Profile Images and Banners.', 'twitchpress' ),
        'url'      => 'https://api.twitter.com/1.1/users/profile_banner.json',
        'type'     => 'get',
    );

    // Remove Profile Banner
    $endpoints[] = array( 
        'name'     => __( 'Remove Profile Banner', 'twitchpress' ),
        'desc'     => __( 'Removes the uploaded profile banner for the authenticating user. Returns HTTP 200 upon success.', 'twitchpress' ),
        'url'      => 'https://api.twitter.com/1.1/account/remove_profile_banner.json',
        'type'     => 'post',
    );

    // Post Account Settings
    $endpoints[] = array( 
        'name'     => __( 'Post Account Settings', 'twitchpress' ),
        'desc'     => __( 'Updates the authenticating user’s settings.', 'twitchpress' ),
        'url'      => 'https://api.twitter.com/1.1/account/settings.json',
        'type'     => 'post',
    );
        
    return $endpoints;
}
          