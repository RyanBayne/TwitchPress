<?php
/**
 * Twitter API for the WordPress Twitch system known as TwitchPress. 
 *
 * PHP version 5.6
 *
 * @category API Libraries
 * @package  TwitchPress Twitter
 * @author   Ryan Bayne <squeekycoder@gmail.com>
 * @version  1.0.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' );
 
// Make sure we meet our dependency requirements
if (!extension_loaded('curl')) trigger_error('cURL is not currently installed on your server, please install cURL if your wish to use Twitter API services in TwitchPress.');
if (!extension_loaded('json')) trigger_error('PECL JSON or pear JSON is not installed, please install either PECL JSON or compile pear JSON if you wish to use Twitter API services in TwitchPress.');

class Twitter_API                             
{
    /**
     * @var string
     */
    private $oauth_access_token;

    /**
     * @var string
     */
    private $oauth_access_token_secret;

    /**
     * @var string
     */
    private $consumer_key;

    /**
     * @var string
     */
    private $consumer_secret;

    /**
     * @var array
     */
    private $postfields;

    /**
     * @var string
     */
    private $getfield;

    /**
     * @var mixed
     */
    protected $oauth;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $requestMethod;

    /**
     * The HTTP status code from the previous request
     *
     * @var int
     */
    protected $httpStatusCode;

    /**
     * Create the API access object. Requires an array of settings::
     * oauth access token, oauth access token secret, consumer key, consumer secret
     * These are all available by creating your own application on dev.twitter.com
     * Requires the cURL library
     *
     * @throws \InvalidArgumentException When incomplete settings parameters are provided
     *
     * @param array $settings
     * 
     * @version 2.0
     */
    public function __construct(array $settings)
    {
        if (!isset($settings['oauth_access_token'])
            || !isset($settings['oauth_access_token_secret'])
            || !isset($settings['consumer_key'])
            || !isset($settings['consumer_secret']))
        {
            throw new InvalidArgumentException('Incomplete settings passed to TwitterAPIExchange');
        }

        $this->oauth_access_token = $settings['oauth_access_token'];
        $this->oauth_access_token_secret = $settings['oauth_access_token_secret'];
        $this->consumer_key = $settings['consumer_key'];
        $this->consumer_secret = $settings['consumer_secret'];
    }

    public function error_codes() {

        // Error Titles
        $titles = array(
            '3'   => __('Invalid Coordinates','twitchpress'),
            '13'  => __('No IP address Location','twitchpress'),
            '17'  => __('No User','twitchpress'),
            '32'  => __('No Authentication','twitchpress'),
            '34'  => __('No Page','twitchpress'),
            '36'  => __('Spam Report Rejected','twitchpress'),
            '44'  => __('attachment_url Invalid','twitchpress'),
            '50'  => __('User Not Found','twitchpress'),
            '63'  => __('User Is Suspended','twitchpress'),
            '64'  => __('Account Suspended','twitchpress'),
            '68'  => __('REST API v1 Inactive','twitchpress'),
            '87'  => __('Not Permitted','twitchpress'),
            '88'  => __('Rate Limit Exceeded','twitchpress'),
            '89'  => __('Bad Token','twitchpress'),
            '92'  => __('SSL Required','twitchpress'),
            '93'  => __('No Delete Permission','twitchpress'),
            '99'  => __('Unknown Credentials','twitchpress'),
            '120' => __('Value Too Long','twitchpress'),
            '130' => __('Over Capacity','twitchpress'),
            '131' => __('Internal Error','twitchpress'),
            '135' => __('Could Not Authenticate','twitchpress'),
            '144' => __('No Status','twitchpress'),
            '150' => __('Message Denied','twitchpress'),
            '151' => __('Message Failed','twitchpress'),
            '160' => __('Already Following','twitchpress'),
            '161' => __('Limit Reached','twitchpress'),
            '179' => __('Status Hidden','twitchpress'),
            '185' => __('Daily Limit Reached','twitchpress'),
            '187' => __('Duplicate Status','twitchpress'),
            '205' => __('Cannot Report','twitchpress'),
            '215' => __('Bad Authentication','twitchpress'),
            '220' => __('Resource Denied','twitchpress'),
            '226' => __('Automated Request Detected','twitchpress'),
            '231' => __('Verify Login','twitchpress'),
            '251' => __('Endpoint Retired','twitchpress'),
            '261' => __('Write Actions Denied','twitchpress'),
            '271' => __('Mute Denied','twitchpress'),
            '272' => __('No Muting','twitchpress'),
            '323' => __('Animated GIF Denied','twitchpress'),
            '324' => __('Invalid Media ID','twitchpress'),
            '325' => __('No Media ID','twitchpress'),
            '326' => __('Account Locked','twitchpress'),
            '354' => __('Oversized Message','twitchpress'),
            '385' => __('No Reply Target','twitchpress'),
            '386' => __('Too Many Attachments','twitchpress')
        );

        $descriptions = array(
            '3'   => __('Corresponds with HTTP 400. The coordinates provided as parameters were not valid for the request.','twitchpress'),
            '13'  => __('No location associated with the specified IP address. Corresponds with HTTP 404. It was not possible to derive a location for the IP address provided as a parameter on the geo search request.','twitchpress'),
            '17'  => __('No user matches for specified terms. Corresponds with HTTP 404. It was not possible to find a user profile matching the parameters specified.','twitchpress'),
            '32'  => __('Corresponds with HTTP 401. There was an issue with the authentication data for the request.','twitchpress'),
            '34'  => __('Corresponds with HTTP 404. The specified resource was not found.','twitchpress'),
            '36'  => __('You cannot report yourself for spam. Corresponds with HTTP 403. You cannot use your own user ID in a report spam call.','twitchpress'),
            '44'  => __('Corresponds with HTTP 400. The URL value provided is not a URL that can be attached to this Tweet.','twitchpress'),
            '50'  => __('Corresponds with HTTP 404. The user is not found.','twitchpress'),
            '63'  => __('User has been suspended. Corresponds with HTTP 403 The user account has been suspended and information cannot be retrieved.','twitchpress'),
            '64'  => __('Your account is suspended and is not permitted to access this feature. Corresponds with HTTP 403. The access token being used belongs to a suspended user.','twitchpress'),
            '68'  => __('The Twitter REST API v1 is no longer active. Please migrate to API v1.1. Corresponds to a HTTP request to a retired v1-era URL.','twitchpress'),
            '87'  => __('Client is not permitted to perform this action. Corresponds with HTTP 403. The endpoint called is not a permitted URL.','twitchpress'),
            '88'  => __('Rate limit exceeded. The request limit for this resource has been reached for the current rate limit window.','twitchpress'),
            '89'  => __('Invalid or expired token. The access token used in the request is incorrect or has expired.','twitchpress'),
            '92'  => __('SSL is required. Only SSL connections are allowed in the API. Update the request to a secure connection. See how to connect using TLS','twitchpress'),
            '93'  => __('This application is not allowed to access or delete your direct messages. Corresponds with HTTP 403. The OAuth token does not provide access to Direct Messages.','twitchpress'),
            '99'  => __('Unable to verify your credentials. Corresponds with HTTP 403. The OAuth credentials cannot be validated. Check that the token is still valid.','twitchpress'),
            '120' => __('Account update failed: value is too long (maximum is nn characters). Corresponds with HTTP 403. Thrown when one of the values passed to the update_profile.json endpoint exceeds the maximum value currently permitted for that field. The error message will specify the allowable maximum number of nn characters.','twitchpress'),
            '130' => __('Over capacity. Corresponds with HTTP 503. Twitter is temporarily over capacity.','twitchpress'),
            '131' => __('Internal error. Corresponds with HTTP 500. An unknown internal error occurred.','twitchpress'),
            '135' => __('Could not authenticate you. Corresponds with HTTP 401. Timestamp out of bounds (often caused by a clock drift when authenticating - check your system clock.','twitchpress'),
            '144' => __('No status found with that ID. Corresponds with HTTP 404. The requested Tweet ID is not found (if it existed, it was probably deleted','twitchpress'),
            '150' => __('You cannot send messages to users who are not following you. Corresponds with HTTP 403. Sending a Direct Message failed.','twitchpress'),
            '151' => __('There was an error sending your message: Corresponds with HTTP 403. Sending a Direct Message failed. The reason value will provide more information.','twitchpress'),
            '160' => __('You\'ve already requested to follow user. Corresponds with HTTP 403. This was a duplicated follow request and a previous request was not yet acknowleged.','twitchpress'),
            '161' => __('You are unable to follow more people at this time. Corresponds with HTTP 403. Thrown when a user cannot follow another user due to some kind of limit','twitchpress'),
            '179' => __('Sorry, you are not authorized to see this status. Corresponds with HTTP 403. Thrown when a Tweet cannot be viewed by the authenticating user, usually due to the Tweet’s author having protected their Tweets.','twitchpress'),
            '185' => __('User is over daily status update limit. Corresponds with HTTP 403. Thrown when a Tweet cannot be posted due to the user having no allowance remaining to post. Despite the text in the error message indicating that this error is only thrown when a daily limit is reached, this error will be thrown whenever a posting limitation has been reached. Posting allowances have roaming windows of time of unspecified duration.','twitchpress'),
            '187' => __('Status is a duplicate. The status text has already been Tweeted by the authenticated account.','twitchpress'),
            '205' => __('You are over the limit for spam reports. Corresponds with HTTP 403. The account limit for reporting spam has been reached. Try again later.','twitchpress'),
            '215' => __('Bad authentication data. Corresponds with HTTP 400. The method requires authentication but it was not presented or was wholly invalid.','twitchpress'),
            '220' => __('Your credentials do not allow access to this resource. Corresponds with HTTP 403. The authentication token in use is restricted and cannot access the requested resource.','twitchpress'),
            '226' => __('This request looks like it might be automated. To protect our users from spam and other malicious activity, we can’t complete this action right now.    We constantly monitor and adjust our filters to block spam and malicious activity on the Twitter platform. These systems are tuned in real-time. If you get this response our systems have flagged the Tweet or DM as possibly fitting this profile. If you feel that the Tweet or DM you attempted to create was flagged in error, please report the details around that to us by filing a ticket at https://support.twitter.com/forms/platform.','twitchpress'),
            '231' => __('User must verify login. Returned as a challenge in xAuth when the user has login verification enabled on their account and needs to be directed to twitter.com to generate a temporary password. Note that xAuth is no longer an available option for authentication on the API.','twitchpress'),
            '251' => __('This endpoint has been retired and should not be used. Corresponds to a HTTP request to a retired URL.','twitchpress'),
            '261' => __('Application cannot perform write actions. Corresponds with HTTP 403. Thrown when the application is restricted from POST, PUT, or DELETE actions. Check the information on your application dashboard. See How to appeal application suspension and other disciplinary actions.','twitchpress'),
            '271' => __('You can’t mute yourself. Corresponds with HTTP 403. The authenticated user account cannot mute itself.','twitchpress'),
            '272' => __('You are not muting the specified user. Corresponds with HTTP 403. The authenticated user account is not muting the account a call is attempting to unmute.','twitchpress'),
            '323' => __('Animated GIFs are not allowed when uploading multiple images. Corresponds with HTTP 400. Only one animated GIF is allowed to be attached to a single Tweet.','twitchpress'),
            '324' => __('The validation of media ids failed. Corresponds with HTTP 400. There was a problem with the media ID submitted with the Tweet.','twitchpress'),
            '325' => __('A media id was not found. Corresponds with HTTP 400. The media ID attached to the Tweet was not found.','twitchpress'),
            '326' => __('To protect our users from spam and other malicious activity, this account is temporarily locked. Corresponds with HTTP 403. The user should log in to https://twitter.com to unlock their account before the user token can be used.','twitchpress'),
            '354' => __('The text of your direct message is over the max character limit. Corresponds with HTTP 403. The message size exceeds the number of characters permitted in a Direct Message.','twitchpress'),
            '385' => __('You attempted to reply to a tweet that is deleted or not visible to you. Corresponds with HTTP 403. A reply can only be sent with reference to an existing public Tweet.','twitchpress'),
            '386' => __('The Tweet exceeds the number of allowed attachment types. Corresponds with HTTP 403. A Tweet is limited to a single attachment resource (media, Quote Tweet, etc.)','twitchpress'),
        );  

        return array( 'titles' => $titles, 'description' => $descriptions );
    }
    
    /**
    * Default Rate Limits Array (all endpoints)
    * 
    * The API rate limits described in this table refer to (read) endpoints.
    * 
    * Note that endpoints not listed in the chart default to 15 requests per allotted user. All request windows are 15 minutes in length.
    * 
    * For POST (create and delete) operations, refer to Twitter’s Account Limits support page in order to understand the daily limits that apply on a per-user basis.
    * 
    * APP RATE LIMITS: GET application/rate_limit_status
    * This endpoints can be used to get application specific rate limits and may be used later in this method. 
    * 
    * @version 1.0
    */
    public function rate_limits() {
        
        $rate_limits = array();

        /*
        * Endpoint Limits
        * 1. Resource Family
        * 2. Requests/Window (user auth)
        * 3. Requests/Window (app auth)
        */
        
        $rate_limits['account/verify_credentials']      = array( 'application', 75,  0   );
        $rate_limits['application/rate_limit_status']   = array( 'application', 180, 180 );
        $rate_limits['favorites/list']                  = array( 'favorites',   75,  75  );
        $rate_limits['followers/ids']                   = array( 'followers',   15,  15  );
        $rate_limits['followers/list']                  = array( 'followers',   15,  15  );
        $rate_limits['friends/ids']                     = array( 'friends',     15,  15  );
        $rate_limits['friends/list']                    = array( 'friends',     15,  15  );
        $rate_limits['friendships/show']                = array( 'friendships', 180, 15  );
        $rate_limits['geo/id/:place_id']                = array( 'geo',         75,  0   );
        $rate_limits['help/configuration']              = array( 'help',        15,  15  );
        $rate_limits['help/languages']                  = array( 'help',        15,  15  );
        $rate_limits['help/privacy']                    = array( 'help',        15,  15  );
        $rate_limits['help/tos']                        = array( 'help',        15,  15  );
        $rate_limits['lists/list']                      = array( 'lists',       15,  15  );
        $rate_limits['lists/members']                   = array( 'lists',       900, 75  );
        $rate_limits['lists/members/show']              = array( 'lists',       15,  15  );
        $rate_limits['lists/memberships']               = array( 'lists',       75,  75  );
        $rate_limits['lists/ownerships']                = array( 'lists',       15,  15  );
        $rate_limits['lists/show']                      = array( 'lists',       75,  75  );
        $rate_limits['lists/statuses']                  = array( 'lists',       900, 900 );
        $rate_limits['lists/subscribers']               = array( 'lists',       180, 15  );
        $rate_limits['lists/subscribers/show']          = array( 'lists',       15,  15  );
        $rate_limits['lists/subscriptions']             = array( 'lists',       15,  15  );
        $rate_limits['search/tweets']                   = array( 'search',      180, 450 );
        $rate_limits['statuses/lookup']                 = array( 'statuses',    900, 300 );
        $rate_limits['statuses/mentions_timeline']      = array( 'statuses',    75,  0   );
        $rate_limits['statuses/retweeters/ids']         = array( 'statuses',    75,  300 );
        $rate_limits['statuses/retweets_of_me']         = array( 'statuses',    75,  0   );
        $rate_limits['statuses/retweets/:id']           = array( 'statuses',    75,  300 );
        $rate_limits['statuses/show/:id']               = array( 'statuses',    900, 900 );
        $rate_limits['statuses/user_timeline']          = array( 'statuses',    900, 1500);
        $rate_limits['trends/available']                = array( 'trends',      75,  75  );
        $rate_limits['trends/closest']                  = array( 'trends',      75,  75  );
        $rate_limits['trends/place']                    = array( 'trends',      75,  75  );
        $rate_limits['users/lookup']                    = array( 'users',       900, 300 );
        $rate_limits['users/search']                    = array( 'users',       900, 0   );
        $rate_limits['users/show']                      = array( 'users',       900, 900 );
        $rate_limits['users/suggestions']               = array( 'users',       15,  15  );
        $rate_limits['users/suggestions/:slug']         = array( 'users',       15,  15  );
        $rate_limits['users/suggestions/:slug/members'] = array( 'users',       15,  15  );
        
        return $rate_limits;
    }
    
    /**
     * Set postfields array, example: array('screen_name' => 'J7mbo')
     *
     * @param array $array Array of parameters to send to API
     *
     * @throws \Exception When you are trying to set both get and post fields
     *
     * @return TwitterAPIExchange Instance of self for method chaining
     */
    public function setPostfields(array $array)
    {
        if (!is_null($this->getGetfield()))
        {
            throw new Exception('You can only choose get OR post fields (post fields include put).');
        }

        if (isset($array['status']) && substr($array['status'], 0, 1) === '@')
        {
            $array['status'] = sprintf("\0%s", $array['status']);
        }

        foreach ($array as $key => &$value)
        {
            if (is_bool($value))
            {
                $value = ($value === true) ? 'true' : 'false';
            }
        }

        $this->postfields = $array;

        // rebuild oAuth
        if (isset($this->oauth['oauth_signature']))
        {
            $this->buildOauth($this->url, $this->requestMethod);
        }

        return $this;
    }

    /**
     * Set getfield string, example: '?screen_name=J7mbo'
     *
     * @param string $string Get key and value pairs as string
     *
     * @throws \Exception
     *
     * @return \TwitterAPIExchange Instance of self for method chaining
     */
    public function setGetfield($string)
    {
        if (!is_null($this->getPostfields()))
        {
            throw new Exception('You can only choose get OR post / post fields.');
        }

        $getfields = preg_replace('/^\?/', '', explode('&', $string));
        $params = array();

        foreach ($getfields as $field)
        {
            if ($field !== '')
            {
                list($key, $value) = explode('=', $field);
                $params[$key] = $value;
            }
        }

        $this->getfield = '?' . http_build_query($params, '', '&');

        return $this;
    }

    /**
     * Get getfield string (simple getter)
     *
     * @return string $this->getfields
     */
    public function getGetfield()
    {
        return $this->getfield;
    }

    /**
     * Get postfields array (simple getter)
     *
     * @return array $this->postfields
     */
    public function getPostfields()
    {
        return $this->postfields;
    }

    /**
     * Build the Oauth object using params set in construct and additionals
     * passed to this method. For v1.1, see: https://dev.twitter.com/docs/api/1.1
     *
     * @param string $url           The API url to use. Example: https://api.twitter.com/1.1/search/tweets.json
     * @param string $requestMethod Either POST or 
     *
     * @throws \Exception
     *
     * @return \TwitterAPIExchange Instance of self for method chaining
     */
    public function buildOauth($url, $requestMethod)
    {
        if ( !in_array(strtolower($requestMethod), array('post', 'get', 'put', 'delete')))
        {
            throw new Exception('Request method must be either POST,  or PUT or DELETE');
        }

        $consumer_key              = $this->consumer_key;
        $consumer_secret           = $this->consumer_secret;
        $oauth_access_token        = $this->oauth_access_token;
        $oauth_access_token_secret = $this->oauth_access_token_secret;

        $oauth = array(
            'oauth_consumer_key' => $consumer_key,
            'oauth_nonce' => time(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token' => $oauth_access_token,
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0'
        );

        $getfield = $this->getGetfield();

        if (!is_null($getfield))
        {
            $getfields = str_replace('?', '', explode('&', $getfield));

            foreach ($getfields as $g)
            {
                $split = explode('=', $g);

                /** In case a null is passed through **/
                if (isset($split[1]))
                {
                    $oauth[$split[0]] = urldecode($split[1]);
                }
            }
        }

        $postfields = $this->getPostfields();

        if (!is_null($postfields)) {
            foreach ($postfields as $key => $value) {
                $oauth[$key] = $value;
            }
        }

        $base_info = $this->buildBaseString($url, $requestMethod, $oauth);
        $composite_key = rawurlencode($consumer_secret) . '&' . rawurlencode($oauth_access_token_secret);
        $oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
        $oauth['oauth_signature'] = $oauth_signature;

        $this->url           = $url;
        $this->requestMethod = $requestMethod;
        $this->oauth         = $oauth;

        return $this;
    }

    /**
     * Perform the actual data retrieval from the API
     *
     * @param boolean $return      If true, returns data. This is left in for backward compatibility reasons
     * @param array   $curlOptions Additional Curl options for this request
     *
     * @throws \Exception
     *
     * @return string json If $return param is true, returns json data.
     */
    public function performRequest($return = true, $curlOptions = array())
    {
        if (!is_bool($return))
        {
            throw new Exception('performRequest parameter must be true or false');
        }

        $header =  array($this->buildAuthorizationHeader($this->oauth), 'Expect:');

        $getfield = $this->getGetfield();
        $postfields = $this->getPostfields();

        if (in_array(strtolower($this->requestMethod), array('put', 'delete')))
        {
            $curlOptions[CURLOPT_CUSTOMREQUEST] = $this->requestMethod;
        }

        $options = $curlOptions + array(
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_HEADER => false,
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        );

        if (!is_null($postfields))
        {
            $options[CURLOPT_POSTFIELDS] = http_build_query($postfields, '', '&');
        }
        else
        {
            if ($getfield !== '')
            {
                $options[CURLOPT_URL] .= $getfield;
            }
        }

        $feed = curl_init();
        curl_setopt_array($feed, $options);
        $json = curl_exec($feed);

        $this->httpStatusCode = curl_getinfo($feed, CURLINFO_HTTP_CODE);

        if (($error = curl_error($feed)) !== '')
        {
            curl_close($feed);

            throw new \Exception($error);
        }

        curl_close($feed);

        return $json;
    }

    /**
     * Private method to generate the base string used by cURL
     *
     * @param string $baseURI
     * @param string $method
     * @param array  $params
     *
     * @return string Built base string
     */
    private function buildBaseString($baseURI, $method, $params)
    {
        $return = array();
        ksort($params);

        foreach($params as $key => $value)
        {
            $return[] = rawurlencode($key) . '=' . rawurlencode($value);
        }

        return $method . "&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $return));
    }

    /**
     * Private method to generate authorization header used by cURL
     *
     * @param array $oauth Array of oauth data generated by buildOauth()
     *
     * @return string $return Header used by cURL for request
     */
    private function buildAuthorizationHeader(array $oauth)
    {
        $return = 'Authorization: OAuth ';
        $values = array();

        foreach($oauth as $key => $value)
        {
            if (in_array($key, array('oauth_consumer_key', 'oauth_nonce', 'oauth_signature',
                'oauth_signature_method', 'oauth_timestamp', 'oauth_token', 'oauth_version'))) {
                $values[] = "$key=\"" . rawurlencode($value) . "\"";
            }
        }

        $return .= implode(', ', $values);
        return $return;
    }

    /**
     * Helper method to perform our request
     *
     * @param string $url
     * @param string $method
     * @param string $data
     * @param array  $curlOptions
     *
     * @throws \Exception
     *
     * @return string The json response from the server
     */
    public function request($url, $method = 'get', $data = null, $curlOptions = array())
    {
        if (strtolower($method) === 'get')
        {
            $this->setGetfield($data);
        }
        else
        {
            $this->setPostfields($data);
        }

        return $this->buildOauth($url, $method)->performRequest(true, $curlOptions);
    }

    /**
     * Get the HTTP status code for the previous request
     *
     * @return integer
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }
}