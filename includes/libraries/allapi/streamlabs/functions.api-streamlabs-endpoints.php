<?php
/**
 * Streamlabs API Endpoints for TwitchPress.
 * 
 * This data is used to create a layer of endpoint validation and 
 * support development tools, debugging and interface information. 
 * 
 * I would also like to track changes to the API here by keeping
 * old endpoints as Deprecated. 
 *
 * @link https://dev.streamlabs.com/
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress Streamlabs API
 * @version     1.0.0
 */

/**
* Return all endpoints.
*  
* @version 1.0
*/
function twitchpress_streamlabs_endpoints() {
    return array(
        'users'          => twitchpress_streamlabs_endpoints_users(),
        'donations'      => twitchpress_streamlabs_endpoints_donations(),
        'alerts'         => twitchpress_streamlabs_endpoints_alerts(),
        'points'         => twitchpress_streamlabs_endpoints_points(),
        'alert_profiles' => twitchpress_streamlabs_endpoints_alert_profiles(),
        'credits'        => twitchpress_streamlabs_endpoints_credits(),
        'jar'            => twitchpress_streamlabs_endpoints_jar(),
        'wheel'          => twitchpress_streamlabs_endpoints_wheel(),
    );
}

/**
* Users Endpoints
* 
* @version 1.0
*/
function twitchpress_streamlabs_endpoints_users() {
    $endpoints = array();

    // user
    $endpoints[] = array( 
        'name'     => __( 'User (get)', 'twitchpress' ),
        'type'     => __( 'GET', 'twitchpress' ),
        'desc'     => __( 'Retrieve a single use using their access token.', 'twitchpress' ),
        'slug'     => '/user',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#user',
        'required' => array( 'access_token' => __( 'Access token allows you to access a users Streamlabs data.', 'twitchpress-streamlabs' ) ),
    );
            
    return $endpoints;   
}

/**
* Donations endpoints.
* 
* @version 1.0
*/
function twitchpress_streamlabs_endpoints_donations() {
    $endpoints = array();

    // get donations
    $endpoints[] = array( 
        'name'     => __( 'Get Donations', 'twitchpress' ),
        'scope'    => 'donations.read',
        'type'     => __( 'GET', 'twitchpress' ),
        'desc'     => __( 'Fetch donations for the authenticated user. Results are ordered by creation date, descending.', 'twitchpress' ),
        'slug'     => '/donations',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#donations',
        'required' => array( 'access_token' => __( 'Access token allows you to access a users Streamlabs data.', 'twitchpress-streamlabs' ) ),
        'optional' => array( 'limit'        => __( 'Limit allows you to limit the number of results output', 'twitchpress-streamlabs' ),
                             'before'       => __( 'The before value is your donation id.', 'twitchpress-streamlabs' ),
                             'after'        => __( 'The after value is your donation id.', 'twitchpress-streamlabs' ),
                             'currency'     => __( 'The desired currency code. If empty, each record will be in the originating currency.', 'twitchpress-streamlabs' ),
                             'limit'        => __( 'If verified is set to 1, response will only include verified donations from paypal, credit card, skrill and unitpay, if it is set to 0 response will only include streamer added donations from My Donations page, do not pass this field if you want to include both.', 'twitchpress-streamlabs' ),
        ),
    );

    // create donation
    $endpoints[] = array( 
        'name'     => __( 'Create Donation', 'twitchpress' ),
        'scope'    => 'donations.create',
        'type'     => __( 'POST', 'twitchpress' ),
        'desc'     => __( 'Create a donation for the authenticated user.', 'twitchpress' ),
        'slug'     => '/donations',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#donations-1',
        'required' => array( 'name'         => __( 'The name of the donor.', 'twitchpress-streamlabs' ),
                             'identifier'   => __( 'An identifier for this donor, which is used to group donations with the same donor. For example, if you create more than one donation with the same identifier, they will be grouped together as if they came from the same donor. Typically this is best suited as an email address, or a unique hash.', 'twitchpress-streamlabs' ),
                             'amount'       => __( 'The amount of this donation.', 'twitchpress-streamlabs' ),
                             'currency'     => __( 'The 3 letter currency code for this donation. Must be one of the supported currency codes.', 'twitchpress-streamlabs' ),
                             'access_token' => __( 'Access token allows you to access users\' Streamlabs data.', 'twitchpress-streamlabs' ) ),
        'optional' => array( 'message'      => __( 'The message from the donor.', 'twitchpress-streamlabs' ), 
                             'created_at'   => __( 'A timestamp that identifies when this donation was made. If left blank, it will default to now.', 'twitchpress-streamlabs' ),
                             'skip_alert'   => __( 'Set it to "yes" if you need to skip the alert.', 'twitchpress-streamlabs' ) ),
    );
            
    return $endpoints;   
}

/**
* Alerts endpoints.
* 
* @version 1.0
*/
function twitchpress_streamlabs_endpoints_alerts() {
    $endpoints = array();

    // trigger alert
    $endpoints[] = array( 
        'name'     => __( 'Trigger Alert', 'twitchpress' ),
        'scope'    => 'alerts.create',
        'type'     => __( 'POST', 'twitchpress' ),
        'desc'     => __( 'Trigger a custom alert for the authenticated user.', 'twitchpress' ),
        'slug'     => 'alerts',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#alerts',
        'required' => array( 'access_token' => __( 'Access token allows you to access a users\' Streamlabs data.', 'twitchpress-streamlabs' ),
                             'type' => __( 'This parameter determines which alert box this alert will show up in, and thus should be one of the following: follow, subscription, donation, or host.', 'twitchpress-streamlabs' ), 
        ),
        'optional' => array( 'image_href' => __( 'The href pointing to an image resource to play when this alert shows. If an empty string is supplied, no image will be displayed.', 'twitchpress-streamlabs' ),
                             'sound_href' => __( 'The href pointing to a sound resource to play when this alert shows. If an empty string is supplied, no sound will be played.', 'twitchpress-streamlabs' ),
                             'message'    => __( 'The message to show with this alert. If not supplied, no message will be shown. Surround special tokens with s, for example: This is my special* alert!', 'twitchpress-streamlabs' ),
                             'duration'   => __( 'How many seconds this alert should be displayed. Value should be in milliseconds.Ex: 1000 for 1 second.', 'twitchpress-streamlabs' ),
                             'special_text_color'   => __( 'EThe color to use for special tokens. Must be a valid CSS color string.MPTY', 'twitchpress-streamlabs' ),
        ),
    );

    // alerts skip
    $endpoints[] = array( 
        'name'     => __( 'Skip', 'twitchpress' ),
        'scope'    => 'alerts.write',
        'type'     => __( 'POST', 'twitchpress' ),
        'desc'     => __( '', 'twitchpress' ),
        'slug'     => '/alerts/skip',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#alertsskip',
        'required' => array( 'access_token' => __( 'Access token allows you to access a users\' Streamlabs data..', 'twitchpress-streamlabs' ) ),
    );
    
    // alerts mute volume
    $endpoints[] = array( 
        'name'     => __( 'Mute Volume', 'twitchpress' ),
        'scope'    => 'alerts.write',
        'type'     => __( 'POST', 'twitchpress' ),
        'desc'     => __( 'EMPTY', 'twitchpress' ),
        'slug'     => '/alerts/mute_volume',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#alertsmute_volume',
        'required' => array( 'access_token' => __( 'Access token allows you to access a users\' Streamlabs data..', 'twitchpress-streamlabs' ) ),
    );

    // alerts unmute volume
    $endpoints[] = array( 
        'name'     => __( 'Unmute Volume', 'twitchpress' ),
        'scope'    => 'alerts.write',
        'type'     => __( 'POST', 'twitchpress' ),
        'desc'     => __( '', 'twitchpress' ),
        'slug'     => '/alerts/unmute_volume',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#alertsmute_volume',
        'required' => array( 'access_token' => __( 'Access token allows you to access a users\' Streamlabs data.', 'twitchpress-streamlabs' ) ),
    );

    // alerts pause queue
    $endpoints[] = array( 
        'name'     => __( 'Pause Queue', 'twitchpress' ),
        'scope'    => 'alerts.write',
        'type'     => __( 'POST', 'twitchpress' ),
        'desc'     => __( '', 'twitchpress' ),
        'slug'     => '/alerts/pause_queue',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#alertspause_queue',
        'required' => array( 'access_token' => __( 'Access token allows you to access a users\' Streamlabs data.', 'twitchpress-streamlabs' ) ),
    );

    // alert unpause queue
    $endpoints[] = array( 
        'name'     => __( 'Unpause Queue', 'twitchpress' ),
        'scope'    => 'alerts.write',
        'type'     => __( 'POST', 'twitchpress' ),
        'desc'     => __( '', 'twitchpress' ),
        'slug'     => '/alerts/unpause_queue',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#alertsunpause_queue',
        'required' => array( 'access_token' => __( 'Access token allows you to access a users\' Streamlabs data.', 'twitchpress-streamlabs' ) ),
    );

    // send test alert
    $endpoints[] = array( 
        'name'     => __( 'Send Test Alert', 'twitchpress' ),
        'scope'    => 'alerts.write',
        'type'     => __( 'POST', 'twitchpress' ),
        'desc'     => __( '', 'twitchpress' ),
        'slug'     => '/alerts/send_test_alert',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#alertssend_test_alert',
        'required' => array( 'access_token' => __( 'Access token allows you to access a users\' Streamlabs data.', 'twitchpress-streamlabs' ) ),
    );

    // show video
    $endpoints[] = array( 
        'name'     => __( 'Show Video', 'twitchpress' ),
        'type'     => __( 'POST', 'twitchpress' ),
        'desc'     => __( '', 'twitchpress' ),
        'slug'     => '/alerts/show_video',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#alertsshow_video',
        'required' => array( 'access_token' => __( 'Access token allows you to access a users\' Streamlabs data.', 'twitchpress-streamlabs' ) ),
    );

    // hide video
    $endpoints[] = array( 
        'name'     => __( 'Hide Video', 'twitchpress' ),
        'type'     => __( 'POST', 'twitchpress' ),
        'desc'     => __( '', 'twitchpress' ),
        'slug'     => '/alerts/hide_video',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#alertshide_video',
        'required' => array( 'access_token' => __( 'Access token allows you to access a users\' Streamlabs data.', 'twitchpress-streamlabs' ) ),
    );

    return $endpoints;   
}

/**
* Points endpoints. 
* 
* @version 1.0
*/
function twitchpress_streamlabs_endpoints_points() {
    $endpoints = array();

    // points
    $endpoints[] = array( 
        'name'     => __( 'Get Points', 'twitchpress' ),
        'scope'    => 'points.read',
        'type'     => __( 'GET', 'twitchpress' ),
        'desc'     => __( '', 'twitchpress' ),
        'slug'     => '/points/points',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#points',
        'required' => array( 'Access token allows you to access a users\' Streamlabs data.' => __( 'Access token allows you to access a users\' Streamlabs data.', 'twitchpress-streamlabs' ),
                             'username' => __( 'Username of the user.', 'twitchpress-streamlabs' ),
                             'channel' => __( 'Channel name e.g. iddqd.', 'twitchpress-streamlabs' ), )
    );
    
    // subtract
    $endpoints[] = array( 
        'name'     => __( 'Substract Points', 'twitchpress' ),
        'scope'    => 'points.write',
        'type'     => __( 'POST', 'twitchpress' ),
        'desc'     => __( '', 'twitchpress' ),
        'slug'     => '/points/subtract',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#pointssubtract',
        'required' => array( 'access_token' => __( 'Access token allows you to access a users\' Streamlabs data..', 'twitchpress-streamlabs' ),
                             'username' => __( 'Username of the user.', 'twitchpress-streamlabs' ),
                             'channel' => __( 'Channel name e.g. iddqd.', 'twitchpress-streamlabs' ),
                             'points' => __( 'The points you want to subtract from the user.', 'twitchpress-streamlabs' ), )
    );
    
    // import
    $endpoints[] = array( 
        'name'     => __( 'Import Points', 'twitchpress' ),
        'scope'    => 'points.write',        
        'type'     => __( 'POST', 'twitchpress' ),
        'desc'     => __( '', 'twitchpress' ),
        'slug'     => '/points/import',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#pointsimport',
        'required' => array( 'access_token' => __( 'Access token allows you to access a users\' Streamlabs data.', 'twitchpress-streamlabs' ),
                             'channel'      => __( 'Channel name e.g. iddqd.', 'twitchpress-streamlabs' ),
                             'users'        => __( 'Users[username]=points e.g. users[username1]=10&users[username2]=20.', 'twitchpress-streamlabs' ) )
    );
    
    // group_get_points
    $endpoints[] = array( 
        'name'     => __( 'Get Many Users Points', 'twitchpress' ),
        'type'     => __( 'GET', 'twitchpress' ),
        'desc'     => __( '', 'twitchpress' ),
        'slug'     => '/points/group_get_points',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#pointsgroup_get_points',
        'required' => array( 
            'access_token' => __( 'Access token allows you to access a users\' Streamlabs data.', 'twitchpress-streamlabs' ),
            'channel'      => __( 'Channel name e.g. iddqd.', 'twitchpress-streamlabs' ),
            'usernames'    => __( 'An array of usernames e.g. usernames[]=user1&usernames[]=user2.', 'twitchpress-streamlabs' ), 
        )
    );
    
    // group_subtract_points
    $endpoints[] = array( 
        'name'     => __( 'Subtract Many Users Points', 'twitchpress' ),
        'type'     => __( 'POST', 'twitchpress' ),
        'desc'     => __( '', 'twitchpress' ),
        'slug'     => '/points/group_subtract_points',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#pointsgroup_subtract_points',
        'required' => array( 
            'access_token' => __( 'Access token allows you to access a users\' Streamlabs data.', 'twitchpress-streamlabs' ),
            'channel'      => __( 'Channel name e.g. iddqd.', 'twitchpress-streamlabs' ),
            'users'        => __( 'Users[username]=points e.g. users[username1]=10&users[username2]=20.', 'twitchpress-streamlabs' ) 
        )
    );
    
    // add_to_all
    $endpoints[] = array( 
        'name'     => __( 'Add Points to All', 'twitchpress' ),
        'scope'    => 'points.write',        
        'type'     => __( 'POST', 'twitchpress' ),
        'desc'     => __( '', 'twitchpress' ),
        'slug'     => '/points/add_to_all',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#pointsadd_to_all',
        'required' => array( 
            'access_token' => __( 'Access token allows you to access a users\' Streamlabs data.', 'twitchpress-streamlabs' ),
            'channel' => __( 'Channel name e.g. iddqd.', 'twitchpress-streamlabs' ),
            'value' => __( 'Points to be added.', 'twitchpress-streamlabs' ) 
        )
    );
    
    // user_points
    $endpoints[] = array( 
        'name'     => __( 'Get User Points', 'twitchpress' ),
        'type'     => __( 'GET', 'twitchpress' ),
        'desc'     => __( '', 'twitchpress' ),
        'slug'     => '/points/user_points',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#pointsuser_points',
        'required' => array( 'access_token' => __( 'Access token allows you to access a users\' Streamlabs data.', 'twitchpress-streamlabs' ) ),
        'optional' => array(
            'username' => __( 'Partial username. Search [Name] ankh to find all users that have the letters "ankh" in their name.', 'twitchpress-streamlabs' ),
            'sort' => __( 'The value of sort can be username/points/time_watched . time_watched is in seconds.', 'twitchpress-streamlabs' ),
            'order' => __( 'asc/desc.', 'twitchpress-streamlabs' ),
            'limit' => __( 'The quantity of the users you will get. should be in range 1-100.', 'twitchpress-streamlabs' ),
            'page' => __( 'Which page we are on.', 'twitchpress-streamlabs' ),
        ),
    );
    
    // user_point_edit
    $endpoints[] = array( 
        'name'     => __( 'Edit User Points', 'twitchpress' ),
        'type'     => __( 'POST', 'twitchpress' ),
        'desc'     => __( '', 'twitchpress' ),
        'slug'     => '/points/user_point_edit',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#pointsuser_point_edit',
        'required' => array( 
            'access_token' => __( 'Access token allows you to access a users\' Streamlabs data.', 'twitchpress-streamlabs' ),
            'username'     => __( 'The name of the user who is in the streamer\'s channel.', 'twitchpress-streamlabs' ),
            'points'       => __( 'Points that will be set to the user.', 'twitchpress-streamlabs' ),
        )
    );
    
    // reset
    $endpoints[] = array( 
        'name'     => __( 'Reset Points', 'twitchpress' ),
        'type'     => __( 'POST', 'twitchpress' ),
        'desc'     => __( '', 'twitchpress' ),
        'slug'     => '/points/reset',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#pointsreset',
        'required' => array( 'access_token' => __( 'Access token allows you to access a users\' Streamlabs data..', 'twitchpress-streamlabs' ) )
    );
            
    return $endpoints;   
}

/**
* Streamlabs alert profiles endpoints. 
* 
* @version 1.0
*/
function twitchpress_streamlabs_endpoints_alert_profiles() {
    $endpoints = array();

    // get
    $endpoints[] = array( 
        'name'     => __( 'Get Alert Profiles', 'twitchpress' ),
        'scope'    => 'profiles.write',
        'type'     => __( 'GET', 'twitchpress' ),
        'desc'     => __( '', 'twitchpress' ),
        'slug'     => '/alert_profiles/get',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#alert_profilesget',
        'required' => array( 'access_token' => __( 'Access token allows you to access a users\' Streamlabs data.', 'twitchpress-streamlabs' ) )
    );

    // alert_profiles/activate
    $endpoints[] = array( 
        'name'     => __( 'Activate Alert Profile', 'twitchpress' ),
        'scope'    => 'profiles.write',
        'type'     => __( 'POST', 'twitchpress' ),
        'desc'     => __( '', 'twitchpress' ),
        'slug'     => '/alert_profiles/activate',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#alert_profilesactivate',
        'required' => array( 
            'access_token' => __( 'Access token allows you to access a users\' Streamlabs data.', 'twitchpress-streamlabs' ),
            'id' => __( 'Alert profile id.', 'twitchpress-streamlabs' ) 
        )
    );
            
    return $endpoints;   
}

/**
* Streamlabs credit endpoints.
* 
* @version 1.0
*/
function twitchpress_streamlabs_endpoints_credits() {
    $endpoints = array();

    // credits/roll
    $endpoints[] = array( 
        'name'     => __( 'Credits Roll', 'twitchpress' ),
        'scope'    => 'credits.write',
        'type'     => __( 'POST', 'twitchpress' ),
        'desc'     => __( '', 'twitchpress' ),
        'slug'     => '/credits/roll',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#creditsroll',
        'required' => array( 'access_token' => __( 'Access token allows you to access a users\' Streamlabs data.', 'twitchpress-streamlabs' ) ),
    );
            
    return $endpoints;   
}

/**
* Streamlabs jar endpoints. 
* 
* @version 1.0
*/
function twitchpress_streamlabs_endpoints_jar() {
    $endpoints = array();

    // jar/empty
    $endpoints[] = array( 
        'name'     => __( 'Empty Jar', 'twitchpress' ),
        'scope'    => 'jar.write',
        'type'     => __( 'POST', 'twitchpress' ),
        'desc'     => __( '', 'twitchpress' ),
        'slug'     => '/jar/empty',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#jarempty',
        'required' => array( 'access_token' => __( 'Access token allows you to access a users\' Streamlabs data.', 'twitchpress-streamlabs' ) ),
    );
            
    return $endpoints;   
}

/**
* Streamlabs wheel endpoints. 
* 
* @version 1.0
*/
function twitchpress_streamlabs_endpoints_wheel() {
    $endpoints = array();

    // wheel/spin
    $endpoints[] = array( 
        'name'     => __( 'Spin Wheel', 'twitchpress' ),
        'scope'    => 'wheel.write',
        'type'     => __( 'POST', 'twitchpress' ),
        'desc'     => __( '', 'twitchpress' ),
        'slug'     => '/wheel/spin',
        'doc'      => 'https://dev.streamlabs.com/v1.0/reference#wheelspin',
        'required' => array( 'access_token' => __( 'Access token allows you to access a users\' Streamlabs data.', 'twitchpress-streamlabs' ) )
    );
            
    return $endpoints;   
}