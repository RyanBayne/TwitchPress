<?php
/**
 * TwitchPress DeepBot API Endpoints.
 * 
 * This data is used to create a layer of endpoint validation and 
 * support development tools or just debugging feedback. 
 * 
 * I would also like to track changes to the API here by keeping
 * old endpoints as depreciated. 
 *
 * @link https://github.com/DeepBot-API/client-websocket
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress DeepBot API
 * @version     1.0.0
 */
 
function twitchpress_deepbot_endpoints() {
    return array(
        'users'    => twitchpress_deepbot_endpoints_users,
        'commands' => twitchpress_deepbot_endpoints_commands,
    );
}

function twitchpress_deepbot_endpoints_users() {
    $endpoints = array();

    // get_user
    $endpoints[] = array( 
        'name'     => __( 'Get User', 'twitchpress' ),
        'desc'     => __( 'Get a single user by username.', 'twitchpress' ),
        'slug'     => 'get_user',
        'url'      => '',
        'doc'      => 'https://github.com/DeepBot-API/client-websocket#apiget_useruser',
        'required' => array( 'user' => __( 'Username, string.', 'twitchpress-deepbot' ) ),
        'optional' => array(),
    );

    // get_users
    $endpoints[] = array( 
        'name'     => __( 'Get Users', 'twitchpress' ),
        'desc'     => __( 'Get multiple users by username.', 'twitchpress' ),
        'slug'     => 'get_users',
        'url'      => '',
        'doc'      => 'https://github.com/DeepBot-API/client-websocket#apiget_usersoffsetlimit',
        'required' => array(),
        'optional' => array( 'offset' => __( 'optional. (Default = 0)', 'twitchpress-deepbot' ),
                             'limit'  => __( 'optional.(Default = 100)', 'twitchpress-deepbot' ) )
    );

    // get_top_users
    $endpoints[] = array( 
        'name'     => __( 'Get Top Users', 'twitchpress' ),
        'desc'     => __( 'Top users sorted by decending order of points.', 'twitchpress' ),
        'slug'     => 'get_top_users',
        'url'      => '',
        'doc'      => 'https://github.com/DeepBot-API/client-websocket#apiget_top_usersoffsetlimit',
        'required' => array(),
        'optional' => array( 'offset' => __( 'optional. (Default = 0)', 'twitchpress-deepbot' ),
                             'limit'  => __( 'optional.(Default = 100)', 'twitchpress-deepbot' ) )
    );

    // get_users_count
    $endpoints[] = array( 
        'name'     => __( 'Get Users Count', 'twitchpress' ),
        'desc'     => __( 'Get total current users.', 'twitchpress' ),
        'slug'     => 'get_users_count',
        'url'      => '',
        'doc'      => 'https://github.com/DeepBot-API/client-websocket#apiget_users_count',
        'required' => array(),
        'optional' => array()
    );

    // get_points
    $endpoints[] = array( 
        'name'     => __( 'Get User Points', 'twitchpress' ),
        'desc'     => __( 'Get a single specific users points.', 'twitchpress' ),
        'slug'     => 'get_points',
        'url'      => '',
        'doc'      => 'https://github.com/DeepBot-API/client-websocket#apiget_pointsuser',
        'required' => array( 'user' => __( 'Username, string.', 'twitchpress-deepbot' ) ),
        'optional' => array()
    );

    // get_hours
    $endpoints[] = array( 
        'name'     => __( 'Get Hours', 'twitchpress' ),
        'desc'     => __( 'Get a single specific users hours.', 'twitchpress' ),
        'slug'     => 'get_hours',
        'url'      => '',
        'doc'      => 'https://github.com/DeepBot-API/client-websocket#apiget_hoursuser',
        'required' => array( 'user' => __( 'Username, string.', 'twitchpress-deepbot' ) ),
        'optional' => array()
    );

    // get_rank
    $endpoints[] = array( 
        'name'     => __( 'Get Rank', 'twitchpress' ),
        'desc'     => __( 'Get a single specific users rank.', 'twitchpress' ),
        'slug'     => 'get_rank',
        'url'      => '',
        'doc'      => 'https://github.com/DeepBot-API/client-websocket#apiget_rankuser',
        'required' => array(),
        'optional' => array()
    );
    
    return $endpoints;   
}

function twitchpress_deepbot_endpoints_commands() {
    $endpoints = array();
    return $endpoints;    
}