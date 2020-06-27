<?php
/**
 * Twitch API Version 6 Endpoints
 * 
 * This data is used to create a layer of endpoint validation and 
 * support development tools or just debugging feedback. 
 * 
 * I would also like to track changes to the API here by keeping
 * old endpoints as Deprecated. 
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress Helix Library
 * @version     1.0
 */
 
function twitchpress_helix_endpoints() { 
    return array(
        //'bits'        => twitchpress_helix_endpoints_bits(),
        //'feed'        => twitchpress_helix_endpoints_feed(), 
        //'channels'    => twitchpress_helix_endpoints_channels(),
        //'chat'        => twitchpress_helix_endpoints_chat(),
        //'clips'       => twitchpress_helix_endpoints_clips(),
        //'collections' => twitchpress_helix_endpoints_collections(),
        //'communities' => twitchpress_helix_endpoints_communities(),
        //'games'       => twitchpress_helix_endpoints_games(),
        //'ingests'     => twitchpress_helix_endpoints_ingests(),
        //'search'      => twitchpress_helix_endpoints_search(),
        //'steams'      => twitchpress_helix_endpoints_streams(),
        //'teams'       => twitchpress_helix_endpoint_teams(),
        //'users'       => twitchpress_helix_endpoint_users(),
        //'video'       => twitchpress_helix_endpoint_video(),
    );
}

function twitchpress_helix_endpoints_bits() {
    $endpoints = array();

    // Get Cheermotes
    $endpoints[] = array( 
        'name'     => __( 'Get Cheermotes', 'twitchpress' ),
        'desc'     => __( 'Retrieves the list of available cheermotes, animated emotes to which viewers can assign bits, to cheer in chat. The cheermotes returned are available throughout Twitch, in all bits-enabled channels.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/bits/action',
        'doc'      => 'https://dev.twitch.tv/docs/v5/reference/bits#get-cheermotes',
        'optional' => array( 'channel_id' => __( 'If this is specified, the cheermote for this channel is included in the response (if the channel owner has uploaded a channel-specific cheermote).', 'twitchpress' ) )
    );
    
    return $endpoints;    
}

function twitchpress_helix_endpoints_channels() {
    $endpoints = array();

    // Get Channel
    $endpoints[] = array(
        'name'     => __( 'Get Channel', 'twitchpress' ),
        'desc'     => __( 'Gets a channel object based on the OAuth token provided. Get Channel returns more data than Get Channel by ID because Get Channel is privileged.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/channel',
        'doc'      => ''
    );

    // Get Channel by ID
    $endpoints[] = array(
        'name'     => __( 'Get Channel by ID', 'twitchpress' ),
        'desc'     => __( 'Gets a specified channel object.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/channels/<channel ID',
        'doc'      => ''
    );

    // Update Channel
    $endpoints[] = array(
        'name'     => __( 'Update Channel', 'twitchpress' ),
        'desc'     => __( 'Updates specified properties of a specified channel. In the request, the new properties can be specified as a JSON object or a form-encoded representation', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/channels/<channel ID',
        'doc'      => ''
    );

    // Get Channel Editors
    $endpoints[] = array(
        'name'     => __( 'Get Channel Editors', 'twitchpress' ),
        'desc'     => __( 'Gets a list of users who are editors for a specified channel.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/channels/<channel ID>/editors',
        'doc'      => ''
    );

    // Get Channel Followers
    $endpoints[] = array(
        'name'     => __( 'Get Channel Followers', 'twitchpress' ),
        'desc'     => __( 'Gets a list of users who follow a specified channel, sorted by the date when they started following the channel (newest first, unless specified otherwise).', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/channels/<channel ID>/follows',
        'doc'      => ''
    );

    // Get Channel Teams
    $endpoints[] = array(
        'name'     => __( 'Get Channel Teams', 'twitchpress' ),
        'desc'     => __( 'Gets a list of teams to which a specified channel belongs.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/channels/<channel ID>/teams',
        'doc'      => ''
    );

    // Get Channel Subscribers
    $endpoints[] = array(
        'name'     => __( 'Get Channel Subscribers', 'twitchpress' ),
        'desc'     => __( 'Gets a list of users subscribed to a specified channel, sorted by the date when they subscribed.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/channels/<channel ID>/subscriptions',
        'doc'      => ''
    );

    // Check Channel Subscription by User
    $endpoints[] = array(
        'name'     => __( 'Check Channel Subscription by User', 'twitchpress' ),
        'desc'     => __( 'Checks if a specified channel has a specified user subscribed to it. Intended for use by channel owners. Returns a subscription object which includes the user if that user is subscribed. Requires authentication for the channel.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/channels/<channel ID>/subscriptions/<user ID',
        'doc'      => ''
    );

    // Get Channel Videos
    $endpoints[] = array(
        'name'     => __( 'Get Channel Videos', 'twitchpress' ),
        'desc'     => __( 'Gets a list of VODs (Video on Demand) from a specified channel.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/channels/<channel ID>/videos',
        'doc'      => ''
    );

    // Start Channel Commercial
    $endpoints[] = array(
        'name'     => __( 'Start Channel Commercial', 'twitchpress' ),
        'desc'     => __( 'Starts a commercial (advertisement) on a specified channel. This is valid only for channels that are Twitch partners. You cannot start a commercial more often than once every 8 minutes. There is an error response (422 Unprocessable Entity) if an invalid length is specified, an attempt is made to start a commercial less than 8 minutes after the previous commercial, or the specified channel is not a Twitch partner.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/channels/<channel ID>/commercial',
        'doc'      => ''
    );

    // Reset Channel Stream Key
    $endpoints[] = array(
        'name'     => __( 'Reset Channel Stream Key', 'twitchpress' ),
        'desc'     => __( 'Deletes the stream key for a specified channel. Once it is deleted, the stream key is automatically reset. A stream key (also known as authorization key) uniquely identifies a stream. Each broadcast uses an RTMP URL that includes the stream key. Stream keys are assigned by Twitch.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/channels/<channel ID>/stream_key',
        'doc'      => ''
    );

    // Get Channel Communities
    $endpoints[] = array(
        'name'     => __( 'Get Channel Communities', 'twitchpress' ),
        'desc'     => __( 'Gets the communities for a specified channel. Note: This replaces Get Channel Community, which returned only one community and will be deprecated.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/channels/<channel ID>/communities',
        'doc'      => ''
    );

    // Set Channel Communities
    $endpoints[] = array(
        'name'     => __( 'Set Channel Communities', 'twitchpress' ),
        'desc'     => __( 'Sets a specified channel to be in up to three specified communities. Note: This replaces Set Channel Community, which set only one community and will be deprecated.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/channels/<channel ID>/communities',
        'doc'      => ''
    );

    // Delete Channel from Communities
    $endpoints[] = array(
        'name'     => __( 'Delete Channel from Communities', 'twitchpress' ),
        'desc'     => __( 'Deletes a specified channel from its communities. Note: This replaces Delete Channel from Community, which acted on only one community and will be deprecated.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/channels/<channel ID>/community',
        'doc'      => ''
    );
    
    return $endpoints;    
}

function twitchpress_helix_endpoints_chat() {
    $endpoints = array();

    // Get Chat Badges by Channel
    $endpoints[] = array(
        'name'     => __( 'Get Chat Badges by Channel', 'twitchpress' ),
        'desc'     => __( 'Gets a list of badges that can be used in chat for a specified channel.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/chat/<channel ID>/badges',
        'doc'      => ''
    );

    // Get Chat Emoticons by Set
    $endpoints[] = array(
        'name'     => __( 'Get Chat Emoticons by Set', 'twitchpress' ),
        'desc'     => __( 'Gets all chat emoticons (not including their images) in one or more specified sets. If no set is specified, all chat emoticons are returned.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/chat/emoticon_images',
        'doc'      => ''
    );

    // Get All Chat Emoticons
    $endpoints[] = array(
        'name'     => __( 'Get All Chat Emoticons', 'twitchpress' ),
        'desc'     => __( 'Gets all chat emoticons (including their images).', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/chat/emoticons',
        'doc'      => ''
    );
    
    return $endpoints;    
}

function twitchpress_helix_endpoints_clips() {
    $endpoints = array();

    // Get Clip
    $endpoints[] = array(
        'name'     => __( 'Get Clip', 'twitchpress' ),
        'desc'     => __( 'Gets details about a specified clip. Clips are referenced by a globally unique string called a slug.', 'twitchpress' ),
        'url'      => '',
        'doc'      => ''
    );

    // Get Top Clips
    $endpoints[] = array(
        'name'     => __( 'Get Top Clips', 'twitchpress' ),
        'desc'     => __( 'Gets the top clips which meet a specified set of parameters.', 'twitchpress' ),
        'url'      => '',
        'doc'      => ''
    );

    // Get Followed Clips
    $endpoints[] = array(
        'name'     => __( 'Get Followed Clips', 'twitchpress' ),
        'desc'     => __( 'Gets the top clips for the games followed by a specified user.', 'twitchpress' ),
        'url'      => '',
        'doc'      => ''
    );
     
    return $endpoints;    
}

function twitchpress_helix_endpoints_collections() {
    $endpoints = array();

    // Get Collection Metadata
    $endpoints[] = array(
        'name'     => __( 'Get Collection Metadata', 'twitchpress' ),
        'desc'     => __( 'Gets summary information about a specified collection. This does not return the collection items (videos).', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/collections/<collection ID>',
        'doc'      => ''
    );

    // Get Collection
    $endpoints[] = array(
        'name'     => __( 'Get Collection', 'twitchpress' ),
        'desc'     => __( 'Gets all items (videos) in a specified collection. For each video in the collection, this returns a collection item ID and other information. Collection item IDs are unique (only) within the collection.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/collections/<collection ID>/items',
        'doc'      => ''
    );

    // Get Collections by Channel
    $endpoints[] = array(
        'name'     => __( 'Get Collections by Channel', 'twitchpress' ),
        'desc'     => __( 'Gets all collections owned by a specified channel. Collections are sorted by update date, with the most recently updated first.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/channels/<channel ID>/collections',
        'doc'      => ''
    );

    // Create Collection
    $endpoints[] = array(
        'name'     => __( 'Create Collection', 'twitchpress' ),
        'desc'     => __( 'Creates a new collection owned by a specified channel. The user identified by the OAuth token must be the owner or an editor of the specified channel. The collection’s title is provided as a required parameter in the request body, in JSON format. A collection is directly related to a channel: broadcasters can create a collection of videos only from their channels. A user can own at most 100 collections.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/channels/<channel ID>/collections',
        'doc'      => ''
    );

    // Update Collection
    $endpoints[] = array(
        'name'     => __( 'Update Collection', 'twitchpress' ),
        'desc'     => __( 'Updates the title of a specified collection. The new title is provided as a required parameter in the request body, in JSON format.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/collections/<collection ID>',
        'doc'      => ''
    );

    // Create Collection Thumbnail
    $endpoints[] = array(
        'name'     => __( 'Create Collection Thumbnail', 'twitchpress' ),
        'desc'     => __( 'Adds the thumbnail of a specified collection item as the thumbnail for the specified collection. The collection item – a video which must already be in the collection – is specified in a required parameter in the request body, in JSON format. The collection item is specified with a collection item ID returned by Get Collection.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/collections/<collection ID>/thumbnail',
        'doc'      => ''
    );

    // Delete Collection
    $endpoints[] = array(
        'name'     => __( 'Delete Collection', 'twitchpress' ),
        'desc'     => __( 'Deletes a specified collection.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/collections/<collection ID>',
        'doc'      => ''
    );

    // Add Item to Collection
    $endpoints[] = array(
        'name'     => __( 'Add Item to Collection', 'twitchpress' ),
        'desc'     => __( 'Adds a specified video to a specified collection. The video ID and type are specified as required parameters in the request body, in JSON format. The item ID is a video ID (not a collection item ID), and the type must be “video.”', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/collections/<collection ID>/items',
        'doc'      => ''
    );

    // Delete Item from Collection
    $endpoints[] = array(
        'name'     => __( 'Delete Item from Collection', 'twitchpress' ),
        'desc'     => __( 'Deletes a specified collection item from a specified collection, if it exists. The collection item is specified with a collection item ID returned by Get Collection.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/collections/<collection ID>/items/<collection item ID',
        'doc'      => ''
    );

    // Move Item within Collection
    $endpoints[] = array(
        'name'     => __( 'Move Item within Collection', 'twitchpress' ),
        'desc'     => __( 'Moves a specified collection item to a different position within a collection. The collection item is specified with a collection item ID returned by Get Collection. The position is specified by a required parameter in the request body, in JSON format.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/collections/<collection ID>/items/<collection item ID>',
        'doc'      => ''
    );
       
    return $endpoints;    
}

function twitchpress_helix_endpoints_communities() {
    $endpoints = array();


    // Get Community by Name
    $endpoints[] = array(
        'name'     => __( 'Get Community by Name', 'twitchpress' ),
        'desc'     => __( 'Gets a specified community. The name of the community is specified in a required query-string parameter. It must be 3-25 characters.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/communities?name=<community name',
        'doc'      => ''
    );

    // Get Community by ID
    $endpoints[] = array(
        'name'     => __( 'Get Community by ID', 'twitchpress' ),
        'desc'     => __( 'Gets a specified community.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/communities/<community ID',
        'doc'      => ''
    );

    // Update Community
    $endpoints[] = array(
        'name'     => __( 'Update Community', 'twitchpress' ),
        'desc'     => __( 'Updates a specified community.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/communities/<community ID',
        'doc'      => ''
    );

    // Get Top Communities
    $endpoints[] = array(
        'name'     => __( 'Get Top Communities', 'twitchpress' ),
        'desc'     => __( 'Gets the top communities by viewer count.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/communities/top',
        'doc'      => ''
    );

    // Get Community Banned Users
    $endpoints[] = array(
        'name'     => __( 'Get Community Banned Users', 'twitchpress' ),
        'desc'     => __( 'Gets a list of banned users for a specified community.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/communities/<community ID>/bans',
        'doc'      => ''
    );

    // Ban Community User
    $endpoints[] = array(
        'name'     => __( 'Ban Community User', 'twitchpress' ),
        'desc'     => __( '    Adds a specified user to the ban list of a specified community.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/communities/<community ID>/bans/<user ID',
        'doc'      => ''
    );

    // Un-Ban Community User
    $endpoints[] = array(
        'name'     => __( 'Un-Ban Community User', 'twitchpress' ),
        'desc'     => __( 'Deletes a specified user from the ban list of a specified community.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/communities/<community ID>/bans/<user ID',
        'doc'      => ''
    );

    // Create Community Avatar Image
    $endpoints[] = array(
        'name'     => __( 'Create Community Avatar Image', 'twitchpress' ),
        'desc'     => __( 'Adds a specified image as the avatar of a specified community.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/communities/<community ID>/images/avata',
        'doc'      => ''
    );

    // Delete Community Avatar Image
    $endpoints[] = array(
        'name'     => __( 'Delete Community Avatar Image', 'twitchpress' ),
        'desc'     => __( 'Deletes the avatar image of a specified community.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/communities/<community ID>/images/avatar',
        'doc'      => ''
    );
        
    // Create Community Cover Image
    $endpoints[] = array(
        'name'     => __( 'Create Community Cover Image', 'twitchpress' ),
        'desc'     => __( 'Adds a specified image as the cover image of a specified community.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/communities/<community ID>/images/cover',
        'doc'      => ''
    );
        
    // Delete Community Cover Image
    $endpoints[] = array(
        'name'     => __( 'Delete Community Cover Image', 'twitchpress' ),
        'desc'     => __( 'Deletes the cover image of a specified community.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/communities/<community ID>/images/cove',
        'doc'      => ''
    );
        
    // Get Community Moderators
    $endpoints[] = array(
        'name'     => __( 'Get Community Moderators', 'twitchpress' ),
        'desc'     => __( 'Gets a list of moderators of a specified community.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/communities/<community ID>/moderators',
        'doc'      => ''
    );
        
    // Add Community Moderator
    $endpoints[] = array(
        'name'     => __( 'Add Community Moderator', 'twitchpress' ),
        'desc'     => __( 'Adds a specified user to the list of moderators of a specified community.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/communities/<community ID>/moderators/<user ID>',
        'doc'      => ''
    );
        
    // Delete Community Moderator
    $endpoints[] = array(
        'name'     => __( 'Delete Community Moderator', 'twitchpress' ),
        'desc'     => __( 'Deletes a specified user from the list of moderators of a specified community.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/communities/<community ID>/moderators/<user ID>',
        'doc'      => ''
    );
        
    // Get Community Permissions
    $endpoints[] = array(
        'name'     => __( 'Get Community Permissions', 'twitchpress' ),
        'desc'     => __( 'Gets a list of actions users can perform in a specified community.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/communities/<community ID>/permission',
        'doc'      => ''
    );
        
    // Report Community Violation    
    $endpoints[] = array(
        'name'     => __( 'Report Community Violation', 'twitchpress' ),
        'desc'     => __( 'Reports a specified channel for violating the rules of a specified community.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/communities/<community ID>/report_channel',
        'doc'      => ''
    );
        
    // Get Community Timed-Out Users
    $endpoints[] = array(
        'name'     => __( 'Get Community Timed-Out Users', 'twitchpress' ),
        'desc'     => __( 'Gets a list of users who are timed out in a specified community.', 'twitchpress' ),
        'url'      => ' https://api.twitch.tv/helix/communities/<community ID>/timeouts',
        'doc'      => ''
    );
        
    // Add Community Timed-Out User
    $endpoints[] = array(
        'name'     => __( 'Add Community Timed-Out User', 'twitchpress' ),
        'desc'     => __( 'Adds a specified user to the timeout list of a specified community.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/communities/<community ID>/timeouts/<user ID>',
        'doc'      => ''
    );
        
    // Delete Community Timed-Out User
    $endpoints[] = array(
        'name'     => __( 'Delete Community Timed-Out User', 'twitchpress' ),
        'desc'     => __( 'Deletes a specified user from the timeout list of a specified community.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/communities/<community ID>/timeouts/<user ID>',
        'doc'      => ''
    );
        
    return $endpoints;    
}

function twitchpress_helix_endpoints_games() {
    $endpoints = array();

    // Get Top Games
    $endpoints[] = array(
        'name'     => __( 'Get Top Games', 'twitchpress' ),
        'desc'     => __( 'Gets games sorted by number of current viewers on Twitch, most popular first.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/games/top',
        'doc'      => ''
    );
        
    return $endpoints;    
}

function twitchpress_helix_endpoints_ingests() {
    $endpoints = array();

    // Get Ingest Server List
    $endpoints[] = array(
        'name'     => __( 'Get Ingest Server List', 'twitchpress' ),
        'desc'     => __( 'Gets a list of ingest servers.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/ingests',
        'doc'      => ''
    );
        
    return $endpoints;    
}

function twitchpress_helix_endpoints_search() {
    $endpoints = array();

    // Search Channels
    $endpoints[] = array(
        'name'     => __( 'Search Channels', 'twitchpress' ),
        'desc'     => __( 'Searches for channels based on a specified query parameter. A channel is returned if the query parameter is matched entirely or partially, in the channel description or game name.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/search/channels?query=<URL encoded search query>',
        'doc'      => ''
    );

    // Search Games
    $endpoints[] = array(
        'name'     => __( 'Search Games', 'twitchpress' ),
        'desc'     => __( 'Searches for games based on a specified query parameter. A game is returned if the query parameter is matched entirely or partially, in the game name.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/search/games?query=<URL encoded search query',
        'doc'      => ''
    );

    // Search Streams
    $endpoints[] = array(
        'name'     => __( 'Search Streams', 'twitchpress' ),
        'desc'     => __( 'Searches for streams based on a specified query parameter. A stream is returned if the query parameter is matched entirely or partially, in the channel description or game name.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/search/streams?query=<URL encoded search query',
        'doc'      => ''
    );
        
    return $endpoints;    
}

function twitchpress_helix_endpoints_streams() {
    $endpoints = array();

    // Get Stream by User
    $endpoints[] = array(
        'name'     => __( 'Get Stream by User', 'twitchpress' ),
        'desc'     => __( 'Gets stream information (the stream object) for a specified user.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/streams/<channel ID>',
        'doc'      => ''
    );

    // Get Live Streams
    $endpoints[] = array(
        'name'     => __( 'Get Live Streams', 'twitchpress' ),
        'desc'     => __( 'Gets a list of live streams.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/streams/',
        'doc'      => ''
    );

    // Get Streams Summary
    $endpoints[] = array(
        'name'     => __( 'Get Streams Summary', 'twitchpress' ),
        'desc'     => __( 'Gets a summary of live streams.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/streams/summary',
        'doc'      => ''
    );

    // Get Featured Streams
    $endpoints[] = array(
        'name'     => __( 'Get Featured Streams', 'twitchpress' ),
        'desc'     => __( 'Gets a list of all featured live streams.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/streams/featured',
        'doc'      => ''
    );

    // Get Followed Streams
    $endpoints[] = array(
        'name'     => __( 'Get Followed Streams', 'twitchpress' ),
        'desc'     => __( 'Gets a list of online streams a user is following, based on a specified OAuth token.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/streams/followed',
        'doc'      => ''
    );
    
    return $endpoints;    
}

function twitchpress_helix_endpoint_teams() {
    $endpoints = array();

    // Get All Teams
    $endpoints[] = array(
        'name'     => __( 'Get All Teams', 'twitchpress' ),
        'desc'     => __( 'Gets all active teams.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/teams',
        'doc'      => ''
    );

    // Get Team
    $endpoints[] = array(
        'name'     => __( 'Get Team', 'twitchpress' ),
        'desc'     => __( 'Gets a specified team object.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/teams',
        'doc'      => ''
    );
        
    return $endpoints;    
}

function twitchpress_helix_endpoint_users() {
    $endpoints = array();

    // Get User
    $endpoints[] = array(
        'name'     => __( 'Get User', 'twitchpress' ),
        'desc'     => __( 'Gets a user object based on the OAuth token provided. Get User returns more data than Get User by ID, because Get User is privileged.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/user',
        'doc'      => ''
    );

    // Get User by ID
    $endpoints[] = array(
        'name'     => __( 'Get User by ID', 'twitchpress' ),
        'desc'     => __( 'Gets a specified user object.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/users/<user ID>',
        'doc'      => ''
    );

    // Get Users
    $endpoints[] = array(
        'name'     => __( 'Get Users', 'twitchpress' ),
        'desc'     => __( 'Gets the user objects for the specified Twitch login names (up to 100). If a specified user’s Twitch-registered email address is not verified, null is returned for that user.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/users?login=<user IDs>',
        'doc'      => ''
    );

    // Get User Emotes
    $endpoints[] = array(
        'name'     => __( 'Get User Emotes', 'twitchpress' ),
        'desc'     => __( 'Gets a list of the emojis and emoticons that the specified user can use in chat. These are both the globally available ones and the channel-specific ones (which can be accessed by any user subscribed to the channel).', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/users/<user ID>/emotes',
        'doc'      => ''
    );

    // Check User Subscription by Channel
    $endpoints[] = array(
        'name'     => __( 'Check User Subscription by Channel', 'twitchpress' ),
        'desc'     => __( 'Checks if a specified user is subscribed to a specified channel. Intended for viewers. There is an error response (422 Unprocessable Entity) if the channel does not have a subscription program.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/users/<user ID>/subscriptions/<channel ID>',
        'doc'      => ''
    );

    // Get User Follows
    $endpoints[] = array(
        'name'     => __( 'Get User Follows', 'twitchpress' ),
        'desc'     => __( 'Gets a list of all channels followed by a specified user, sorted by the date when they started following each channel.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/users/<user ID>/follows/channels',
        'doc'      => ''
    );

    // Check User Follows by Channel
    $endpoints[] = array(
        'name'     => __( 'Check User Follows by Channel', 'twitchpress' ),
        'desc'     => __( 'Checks if a specified user follows a specified channel. If the user is following the channel, a follow object is returned.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/users/<user ID>/follows/channels/<channel ID>',
        'doc'      => ''
    );

    // Follow Channel
    $endpoints[] = array(
        'name'     => __( 'Follow Channel', 'twitchpress' ),
        'desc'     => __( 'Adds a specified user to the followers of a specified channel. There is an error response (422 Unprocessable Entity) if the channel could not be followed.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/users/<user ID>/follows/channels/<channel ID>',
        'doc'      => ''
    );

    // Unfollow Channel
    $endpoints[] = array(
        'name'     => __( 'Unfollow Channel', 'twitchpress' ),
        'desc'     => __( 'Deletes a specified user from the followers of a specified channel.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/users/<user ID>/follows/channels/<channel ID>',
        'doc'      => ''
    );

    // Get User Block List
    $endpoints[] = array(
        'name'     => __( 'Get User Block List', 'twitchpress' ),
        'desc'     => __( 'Gets a specified user\'s block list.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/users/<user ID>/blocks',
        'doc'      => ''
    );

    // Block User
    $endpoints[] = array(
        'name'     => __( 'Block User', 'twitchpress' ),
        'desc'     => __( 'Blocks a user; that is, adds a specified target user to the blocks list of a specified source user.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/users/<source user ID>/blocks/<target user ID>',
        'doc'      => ''
    );

    // Unblock User
    $endpoints[] = array(
        'name'     => __( 'Unblock User', 'twitchpress' ),
        'desc'     => __( 'Unblocks a user; that is, deletes a specified target user from the blocks list of a specified source user. There is an error if the target user is not on the source user\'s block list (404 Not Found) or the delete failed (422 Unprocessable Entity).', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/users/<source user ID>/blocks/<target user ID>',
        'doc'      => ''
    );

    // Create User Connection to Viewer Heartbeat Service (VHS)
    $endpoints[] = array(
        'name'     => __( 'Create User Connection to Viewer Heartbeat Service (VHS)', 'twitchpress' ),
        'desc'     => __( 'Creates a connection between a user (an authenticated Twitch user, linked to a game user) and VHS, and starts returning the user’s VHS data in each heartbeat. The game user is specified by a required identifier parameter.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/user/vhs',
        'doc'      => ''
    );

    // Check User Connection to Viewer Heartbeat Service (VHS)
    $endpoints[] = array(
        'name'     => __( 'Check User Connection to Viewer Heartbeat Service (VHS)', 'twitchpress' ),
        'desc'     => __( 'Checks whether an authenticated Twitch user is connected to VHS. If a connection to the service exists for the specified user, the linked game user’s ID is returned; otherwise, an HTTP 404 response is returned.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/user/vhs',
        'doc'      => ''
    );

    // Delete User Connection to Viewer Heartbeat Service (VHS)
    $endpoints[] = array(
        'name'     => __( 'Delete User Connection to Viewer Heartbeat Service (VHS)', 'twitchpress' ),
        'desc'     => __( 'Deletes the connection between an authenticated Twitch user and VHS.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/user/vhs',
        'doc'      => ''
    );
    
    return $endpoints;    
}

function twitchpress_helix_endpoint_video() {
    $endpoints = array();

    // Get Video
    $endpoints[] = array(
        'name'     => __( 'Get Video', 'twitchpress' ),
        'desc'     => __( 'Gets a specified video object.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/videos/<video ID>',
        'doc'      => ''
    );

    // Get Top Videos
    $endpoints[] = array(
        'name'     => __( 'Get Top Videos', 'twitchpress' ),
        'desc'     => __( 'Gets the top videos based on viewcount, optionally filtered by game or time period.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/videos/top',
        'doc'      => ''
    );

    // Get Followed Videos
    $endpoints[] = array(
        'name'     => __( 'Get Followed Videos', 'twitchpress' ),
        'desc'     => __( 'Gets the videos from channels the user is following based on the OAuth token provided.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/videos/followed',
        'doc'      => ''
    );

    // Create Video
    $endpoints[] = array(
        'name'     => __( 'Create Video', 'twitchpress' ),
        'desc'     => __( 'Creates a new video in a specified channel.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/videos?channel_id=<channel ID>&title=<video title>',
        'doc'      => ''
    );
    
    // Upload Video Part
    $endpoints[] = array(
        'name'     => __( 'Upload Video Part', 'twitchpress' ),
        'desc'     => __( 'Uploads part of a video. Each part of a video is uploaded with a separate request.', 'twitchpress' ),
        'url'      => 'https://uploads.twitch.tv/upload/<video ID>?part=<number>&upload_token=<token>',
        'doc'      => ''
    );
    
    // Complete Video Upload
    $endpoints[] = array(
        'name'     => __( 'Complete Video Upload', 'twitchpress' ),
        'desc'     => __( 'After you upload all the parts of a video, you complete the upload process with this endpoint.', 'twitchpress' ),
        'url'      => 'https://uploads.twitch.tv/upload/<video ID>/complete?upload_token=<token>',
        'doc'      => ''
    );

    // Update Video
    $endpoints[] = array(
        'name'     => __( 'Update Video', 'twitchpress' ),
        'desc'     => __( 'Updates information about a specified video that was already created.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/videos/<video ID>',
        'doc'      => ''
    );

    // Delete Video
    $endpoints[] = array(
        'name'     => __( 'Delete Video', 'twitchpress' ),
        'desc'     => __( 'Deletes a specified video. Any type can be deleted.', 'twitchpress' ),
        'url'      => 'https://api.twitch.tv/helix/videos/<video ID>',
        'doc'      => ''
    );
    
    return $endpoints;    
}
