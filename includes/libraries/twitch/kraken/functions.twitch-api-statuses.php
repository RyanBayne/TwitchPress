<?php
/**
 * Kraken5 for WordPress functions.
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     Kraken5
 * @version     1.0.0
 */

/**
* Get HTTP Status codes array or the type for a giving code.
* 
* @link https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
* 
* @version 1.0
*/
function twitchpress_kraken_httpstatus_groups( $status = null ) {
    $group_meanings = array(
        '1' => __( 'Informational responses.', 'twitchpress' ), 
        '2' => __( 'Success.', 'twitchpress' ), 
        '3' => __( 'Redirection.', 'twitchpress' ), 
        '4' => __( 'Client errors.', 'twitchpress' ), 
        '5' => __( 'Server errors.', 'twitchpress' ), 
    );
    
    if( !$status ) { return $group_meanings; }
    
    $group_number = substr( $status, 1);
    
    if( !is_numeric( $group_number ) ) 
    {
        return __( 'Group-number must be numeric.', 'twitchpress' );
        
    }    
    elseif( !isset( $group_meanings[ $group_number ] ) ) 
    {
        return __( 'Invalid group number returned by substr().', 'twitchpress' );
    }
    
    return $group_meanings[ $group_number ];
}

/**
* A list of HTTP status with default meaning taking from Wikipedia
* and where possible there are other interpretations to explain 
* a Twitch API (Kraken) result within the context of Kraken.
* 
* @link https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
* 
* @version 2.2
*/
function twitchpress_kraken_httpstatuses( $requested_status = null, $requested_meaning = 'short' ) {
    $httpstatus = array();
    
    $httpstatus[100]['short'] = __( 'Continue', 'twitchpress' );     
    $httpstatus[100]['wiki'] = __( "The server has received the request headers and the client should proceed to send the request body (in the case of a request for which a body needs to be sent; for example, a POST request). Sending a large request body to a server after a request has been rejected for inappropriate headers would be inefficient. To have a server check the request's headers, a client must send Expect: 100-continue as a header in its initial request and receive a 100 Continue status code in response before sending the body. The response 417 Expectation Failed indicates the request should not be continued.", 'twitchpress' );
    
    $httpstatus[101]['short'] = __( 'Switching Protocols', 'twitchpress' );
    $httpstatus[101]['wiki'] = __( "The requester has asked the server to switch protocols and the server has agreed to do so.", 'twitchpress' );
    
    $httpstatus[102]['short'] = __( 'Processing', 'twitchpress' );
    $httpstatus[102]['wiki'] = __( "A WebDAV request may contain many sub-requests involving file operations, requiring a long time to complete the request. This code indicates that the server has received and is processing the request, but no response is available yet. This prevents the client from timing out and assuming the request was lost.", 'twitchpress' );
    
    $httpstatus[103]['short'] = __( 'Checkpoint', 'twitchpress' );
    $httpstatus[103]['wiki'] = __( "Used in the resumable requests proposal to resume aborted PUT or POST requests.", 'twitchpress' );
    
    $httpstatus[200]['short'] = __( 'OK', 'twitchpress' );
    $httpstatus[200]['wiki'] = __( "Standard response for successful HTTP requests. The actual response will depend on the request method used. In a GET request, the response will contain an entity corresponding to the requested resource. In a POST request, the response will contain an entity describing or containing the result of the action.", 'twitchpress' );
    
    $httpstatus[201]['short'] = __( 'Created', 'twitchpress' );
    $httpstatus[201]['wiki'] = __( "The request has been fulfilled, resulting in the creation of a new resource.", 'twitchpress' );
    
    $httpstatus[202]['short'] = __( 'Accepted', 'twitchpress' );
    $httpstatus[202]['wiki'] = __( "The request has been accepted for processing, but the processing has not been completed. The request might or might not be eventually acted upon, and may be disallowed when processing occurs.", 'twitchpress' );
    
    $httpstatus[203]['short'] = __( 'Non-Authoritative Information', 'twitchpress' );
    $httpstatus[203]['wiki'] = __( "The server is a transforming proxy (e.g. a Web accelerator) that received a 200 OK from its origin, but is returning a modified version of the origin's response.", 'twitchpress' );
    
    $httpstatus[204]['short'] = __( 'No Content', 'twitchpress' );
    $httpstatus[204]['wiki'] = __( "The server successfully processed the request and is not returning any content.", 'twitchpress' );
    
    $httpstatus[205]['short'] = __( 'Reset Content', 'twitchpress' );
    $httpstatus[205]['wiki'] = __( "The server successfully processed the request, but is not returning any content. Unlike a 204 response, this response requires that the requester reset the document view.", 'twitchpress' );
    
    $httpstatus[206]['short'] = __( 'Partial Content', 'twitchpress' );
    $httpstatus[206]['wiki'] = __( "The server is delivering only part of the resource (byte serving) due to a range header sent by the client. The range header is used by HTTP clients to enable resuming of interrupted downloads, or split a download into multiple simultaneous streams.", 'twitchpress' );
    
    $httpstatus[207]['short'] = __( 'Multi-Status', 'twitchpress' );
    $httpstatus[207]['wiki'] = __( "The message body that follows is an XML message and can contain a number of separate response codes, depending on how many sub-requests were made.", 'twitchpress' );
    
    $httpstatus[208]['short'] = __( 'Already Reported', 'twitchpress' );
    $httpstatus[208]['wiki'] = __( "The members of a DAV binding have already been enumerated in a preceding part of the (multistatus) response, and are not being included again.", 'twitchpress' );
    
    $httpstatus[226]['short'] = __( 'IM Used', 'twitchpress' );
    $httpstatus[226]['wiki'] = __( "The server has fulfilled a request for the resource, and the response is a representation of the result of one or more instance-manipulations applied to the current instance.", 'twitchpress' );
    
    $httpstatus[300]['short'] = __( 'Multiple Choices', 'twitchpress' );
    $httpstatus[300]['wiki'] = __( "Indicates multiple options for the resource from which the client may choose (via agent-driven content negotiation). For example, this code could be used to present multiple video format options, to list files with different filename extensions, or to suggest word-sense disambiguation.", 'twitchpress' );
    
    $httpstatus[301]['short'] = __( 'Moved Permanently', 'twitchpress' );
    $httpstatus[301]['wiki'] = __( "This and all future requests should be directed to the given URI.", 'twitchpress' );
    
    $httpstatus[302]['short'] = __( 'Found', 'twitchpress' );
    $httpstatus[302]['wiki'] = __( "This is an example of industry practice contradicting the standard. The HTTP/1.0 specification (RFC 1945) required the client to perform a temporary redirect (the original describing phrase was \"Moved Temporarily\"), but popular browsers implemented 302 with the functionality of a 303 See Other. Therefore, HTTP/1.1 added status codes 303 and 307 to distinguish between the two behaviours. However, some Web applications and frameworks use the 302 status code as if it were the 303.", 'twitchpress' );
    
    $httpstatus[303]['short'] = __( 'See Other', 'twitchpress' );
    $httpstatus[303]['wiki'] = __( "The response to the request can be found under another URI using a GET method. When received in response to a POST (or PUT/DELETE), the client should presume that the server has received the data and should issue a redirect with a separate GET message.", 'twitchpress' );
    
    $httpstatus[304]['short'] = __( 'Not Modified', 'twitchpress' );
    $httpstatus[304]['wiki'] = __( "Indicates that the resource has not been modified since the version specified by the request headers If-Modified-Since or If-None-Match. In such case, there is no need to retransmit the resource since the client still has a previously-downloaded copy.", 'twitchpress' );
    
    $httpstatus[305]['short'] = __( 'Use Proxy', 'twitchpress' );
    $httpstatus[305]['wiki'] = __( "The requested resource is available only through a proxy, the address for which is provided in the response. Many HTTP clients (such as Mozilla[25] and Internet Explorer) do not correctly handle responses with this status code, primarily for security reasons.", 'twitchpress' );
    
    $httpstatus[306]['short'] = __( 'Switch Proxy', 'twitchpress' );
    $httpstatus[306]['wiki'] = __( "No longer used. Originally meant subsequent requests should use the specified proxy.", 'twitchpress' );
    
    $httpstatus[307]['short'] = __( 'Temporary Redirect', 'twitchpress' );
    $httpstatus[307]['wiki'] = __( "In this case, the request should be repeated with another URI; however, future requests should still use the original URI. In contrast to how 302 was historically implemented, the request method is not allowed to be changed when reissuing the original request. For example, a POST request should be repeated using another POST request.", 'twitchpress' );
    
    $httpstatus[308]['short'] = __( 'Permanent Redirect', 'twitchpress' );
    $httpstatus[308]['wiki'] = __( "The request and all future requests should be repeated using another URI. 307 and 308 parallel the behaviors of 302 and 301, but do not allow the HTTP method to change. So, for example, submitting a form to a permanently redirected resource may continue smoothly.", 'twitchpress' );
    
    $httpstatus[404]['short'] = __( 'error on Wikipedia', 'twitchpress' );
    $httpstatus[404]['wiki'] = __( "This class of status code is intended for situations in which the error seems to have been caused by the client. Except when responding to a HEAD request, the server should include an entity containing an explanation of the error situation, and whether it is a temporary or permanent condition. These status codes are applicable to any request method. User agents should display any included entity to the user.", 'twitchpress' );
    
    $httpstatus[400]['short'] = __( 'Bad Request', 'twitchpress' );
    $httpstatus[400]['wiki'] = __( "Request Not Valid. Something is wrong with the request due to an apparent client error e.g. malformed request syntax, size too large, invalid request message framing, or deceptive request routing.", 'twitchpress' );
    
    $httpstatus[401]['short'] = __( 'Unauthorized', 'twitchpress' );
    $httpstatus[401]['wiki'] = __( "The OAuth token does not have the correct scope or does not have the required permission on behalf of the specified user.", 'twitchpress' );
    
    $httpstatus[402]['short'] = __( 'Payment Required', 'twitchpress' );
    $httpstatus[402]['wiki'] = __( "Reserved for future use. The original intention was that this code might be used as part of some form of digital cash or micropayment scheme, as proposed for example by GNU Taler, but that has not yet happened, and this code is not usually used. Google Developers API uses this status if a particular developer has exceeded the daily limit on requests.", 'twitchpress' );
    
    $httpstatus[403]['short'] = __( 'Forbidden', 'twitchpress' );
    $httpstatus[403]['wiki'] = __( "Forbidden. This usually indicates that authentication was provided, but the authenticated user is not permitted to perform the requested operation. For example, a user who is not a partner might have tried to start a commercial.", 'twitchpress' );
    
    $httpstatus[404]['short'] = __( 'Not Found', 'twitchpress' );
    $httpstatus[404]['wiki'] = __( "The requested resource could not be found but may be available in the future. Subsequent requests by the client are permissible. For example, the channel, user, or relationship could not be found.", 'twitchpress' );
    
    $httpstatus[405]['short'] = __( 'Method Not Allowed', 'twitchpress' );
    $httpstatus[405]['wiki'] = __( "A request method is not supported for the requested resource; for example, a GET request on a form that requires data to be presented via POST, or a PUT request on a read-only resource.", 'twitchpress' );
    
    $httpstatus[406]['short'] = __( 'Not Acceptable', 'twitchpress' );
    $httpstatus[406]['wiki'] = __( "The requested resource is capable of generating only content not acceptable according to the Accept headers sent in the request. See Content negotiation.", 'twitchpress' );
    
    $httpstatus[407]['short'] = __( 'Proxy Authentication Required', 'twitchpress' );
    $httpstatus[407]['wiki'] = __( "The client must first authenticate itself with the proxy.", 'twitchpress' );
    
    $httpstatus[408]['short'] = __( 'Request Timeout', 'twitchpress' );
    $httpstatus[408]['wiki'] = __( "The server timed out waiting for the request. According to HTTP specifications: The client did not produce a request within the time that the server was prepared to wait. The client MAY repeat the request without modifications at any later time.", 'twitchpress' );
    
    $httpstatus[410]['short'] = __( 'Gone', 'twitchpress' );
    $httpstatus[410]['wiki'] = __( "Indicates that the resource requested is no longer available and will not be available again. This should be used when a resource has been intentionally removed and the resource should be purged. Upon receiving a 410 status code, the client should not request the resource in the future. Clients such as search engines should remove the resource from their indices. Most use cases do not require clients and search engines to purge the resource, and a \"404 Not Found\" may be used instead.", 'twitchpress' );
    
    $httpstatus[411]['short'] = __( 'Length Required', 'twitchpress' );
    $httpstatus[411]['wiki'] = __( "The request did not specify the length of its content, which is required by the requested resource.", 'twitchpress' );
    
    $httpstatus[412]['short'] = __( 'Precondition Failed', 'twitchpress' );
    $httpstatus[412]['wiki'] = __( "The server does not meet one of the preconditions that the requester put on the request.", 'twitchpress' );
    
    $httpstatus[413]['short'] = __( 'Payload Too Large', 'twitchpress' );
    $httpstatus[413]['wiki'] = __( "The request is larger than the server is willing or able to process. Previously called \"Request Entity Too Large\".", 'twitchpress' );
    
    $httpstatus[414]['short'] = __( 'URI Too Long', 'twitchpress' );
    $httpstatus[414]['wiki'] = __( "The URI provided was too long for the server to process. Often the result of too much data being encoded as a query-string of a GET request, in which case it should be converted to a POST request. Called \"Request-URI Too Long\" previously.", 'twitchpress' );
    
    $httpstatus[415]['short'] = __( 'Unsupported Media Type', 'twitchpress' ); 
    $httpstatus[415]['wiki'] = __( "The request entity has a media type which the server or resource does not support. For example, the client uploads an image as image/svg+xml, but the server requires that images use a different format.", 'twitchpress' );
    
    $httpstatus[416]['short'] = __( 'Range Not Satisfiable', 'twitchpress' );
    $httpstatus[416]['wiki'] = __( "The client has asked for a portion of the file (byte serving), but the server cannot supply that portion. For example, if the client asked for a part of the file that lies beyond the end of the file.[46] Called \"Requested Range Not Satisfiable\" previously.", 'twitchpress' );
    
    $httpstatus[417]['short'] = __( 'Expectation Failed', 'twitchpress' );
    $httpstatus[417]['wiki'] = __( "The server cannot meet the requirements of the Expect request-header field.", 'twitchpress' );
    
    $httpstatus[418]['short'] = __( 'I\'m a teapot', 'twitchpress' );
    $httpstatus[418]['wiki'] = __( "This code was defined in 1998 as one of the traditional IETF April Fools' jokes, in RFC 2324, Hyper Text Coffee Pot Control Protocol, and is not expected to be implemented by actual HTTP servers. The RFC specifies this code should be returned by teapots requested to brew coffee. This HTTP status is used as an Easter egg in some websites, including Google.com.", 'twitchpress' );
    
    $httpstatus[421]['short'] = __( 'Misdirected Request', 'twitchpress' );
    $httpstatus[421]['wiki'] = __( "The request was directed at a server that is not able to produce a response. (for example because of a connection reuse)", 'twitchpress' );
    
    $httpstatus[422]['short'] = __( 'Unprocessable Entity', 'twitchpress' );
    $httpstatus[422]['wiki'] = __( "For example, for a user subscription endpoint, the specified channel does not have a subscription program.", 'twitchpress' );
    
    $httpstatus[423]['short'] = __( 'Locked', 'twitchpress' );
    $httpstatus[423]['wiki'] = __( "The resource that is being accessed is locked.", 'twitchpress' );
    
    $httpstatus[424]['short'] = __( 'Failed Dependency', 'twitchpress' );
    $httpstatus[424]['wiki'] = __( "The request failed due to failure of a previous request (e.g., a PROPPATCH).", 'twitchpress' );
    
    $httpstatus[426]['short'] = __( 'Upgrade Required', 'twitchpress' );
    $httpstatus[426]['wiki'] = __( "The client should switch to a different protocol such as TLS/1.0, given in the Upgrade header field.", 'twitchpress' );
    
    $httpstatus[428]['short'] = __( 'Precondition Required', 'twitchpress' );
    $httpstatus[428]['wiki'] = __( "The origin server requires the request to be conditional. Intended to prevent the 'lost update' problem, where a client GETs a resource's state, modifies it, and PUTs it back to the server, when meanwhile a third party has modified the state on the server, leading to a conflict.", 'twitchpress' );
    
    $httpstatus[429]['short'] = __( 'Too Many Requests', 'twitchpress' );
    $httpstatus[429]['wiki'] = __( "The user has sent too many requests in a given amount of time. Improve rate limiting for the causing feature.", 'twitchpress' );
    
    $httpstatus[431]['short'] = __( 'Request Header Fields Too Large', 'twitchpress' );
    $httpstatus[431]['wiki'] = __( "The server is unwilling to process the request because either an individual header field, or all the header fields collectively, are too large.", 'twitchpress' );
    
    $httpstatus[451]['short'] = __( 'Unavailable For Legal Reasons', 'twitchpress' );
    $httpstatus[451]['wiki'] = __( "A server operator has received a legal demand to deny access to a resource or to a set of resources that includes the requested resource.[55] The code 451 was chosen as a reference to the novel Fahrenheit 451.", 'twitchpress' );
    
    $httpstatus[420]['short'] = __( 'Method Failure (Spring Framework)', 'twitchpress' );
    $httpstatus[420]['wiki'] = __( "A deprecated response used by the Spring Framework when a method has failed.", 'twitchpress' );
    
    $httpstatus[440]['short'] = __( 'Login Time-out', 'twitchpress' );
    $httpstatus[440]['wiki'] = __( "The client's session has expired and must log in again.", 'twitchpress' );
    
    $httpstatus[449]['short'] = __( 'Retry With ', 'twitchpress' );
    $httpstatus[449]['wiki'] = __( "The server cannot honour the request because the user has not provided the required information.", 'twitchpress' );
    
    $httpstatus[451]['short'] = __( 'Redirect', 'twitchpress' );
    $httpstatus[451]['wiki'] = __( "Used in Exchange ActiveSync when either a more efficient server is available or the server cannot access the users' mailbox. The client is expected to re-run the HTTP AutoDiscover operation to find a more appropriate server.", 'twitchpress' );
    
    $httpstatus[444]['short'] = __( 'No Response', 'twitchpress' );
    $httpstatus[444]['wiki'] = __( "Used to indicate that the server has returned no information to the client and closed the connection.", 'twitchpress' );
    
    $httpstatus[495]['short'] = __( 'SSL Certificate Error', 'twitchpress' );
    $httpstatus[495]['wiki'] = __( "An expansion of the 400 Bad Request response code, used when the client has provided an invalid client certificate.", 'twitchpress' );
    
    $httpstatus[496]['short'] = __( 'SSL Certificate Required', 'twitchpress' );
    $httpstatus[496]['wiki'] = __( "An expansion of the 400 Bad Request response code, used when a client certificate is required but not provided.", 'twitchpress' );
    
    $httpstatus[497]['short'] = __( 'HTTP Request Sent to HTTPS Port', 'twitchpress' );
    $httpstatus[497]['wiki'] = __( "An expansion of the 400 Bad Request response code, used when the client has made a HTTP request to a port listening for HTTPS requests.", 'twitchpress' );
    
    $httpstatus[498]['short'] = __( 'Invalid Token (Esri)', 'twitchpress' );
    $httpstatus[498]['wiki'] = __( "Returned by ArcGIS for Server. Code 498 indicates an expired or otherwise invalid token.", 'twitchpress' );
    
    $httpstatus[499]['short'] = __( 'Client Closed Request', 'twitchpress' );
    $httpstatus[499]['wiki'] = __( "Used when the client has closed the request before the server could send a response.", 'twitchpress' );

    $httpstatus[500]['short'] = __( 'Internal Server Error', 'twitchpress' );
    $httpstatus[500]['wiki'] = __( "A generic error message, given when an unexpected condition was encountered and no more specific message is suitable.", 'twitchpress' );
    
    $httpstatus[501]['short'] = __( 'Not Implemented', 'twitchpress' );
    $httpstatus[501]['wiki'] = __( "The server either does not recognize the request method, or it lacks the ability to fulfil the request. Usually this implies future availability (e.g., a new feature of a web-service API).", 'twitchpress' );
    
    $httpstatus[502]['short'] = __( 'Bad Gateway', 'twitchpress' );
    $httpstatus[502]['wiki'] = __( "The server was acting as a gateway or proxy and received an invalid response from the upstream server.", 'twitchpress' );
    
    $httpstatus[503]['short'] = __( 'Service Unavailable', 'twitchpress' );
    $httpstatus[503]['wiki'] = __( "For example, the status of a game or ingest server cannot be retrieved.", 'twitchpress' );
    
    $httpstatus[504]['short'] = __( 'Gateway Timeout', 'twitchpress' );
    $httpstatus[504]['wiki'] = __( "The server was acting as a gateway or proxy and did not receive a timely response from the upstream server.", 'twitchpress' );
    
    $httpstatus[505]['short'] = __( 'HTTP Version Not Supported', 'twitchpress' );
    $httpstatus[505]['wiki'] = __( "The server does not support the HTTP protocol version used in the request.", 'twitchpress' );
   
    $httpstatus[506]['short'] = __( 'Variant Also Negotiates', 'twitchpress' );
    $httpstatus[506]['wiki'] = __( "Transparent content negotiation for the request results in a circular reference.", 'twitchpress' );
    
    $httpstatus[507]['short'] = __( 'Insufficient Storage', 'twitchpress' );
    $httpstatus[507]['wiki'] = __( "The server is unable to store the representation needed to complete the request.", 'twitchpress' );
    
    $httpstatus[508]['short'] = __( 'Loop Detected', 'twitchpress' );
    $httpstatus[508]['wiki'] = __( "The server detected an infinite loop while processing the request (sent in lieu of 208 Already Reported).", 'twitchpress' );
    
    $httpstatus[509]['short'] = __( 'Bandwidth Limit Exceeded (Apache Web Server/cPanel)', 'twitchpress' );
    $httpstatus[509]['wiki'] = __( "The server has exceeded the bandwidth specified by the server administrator; this is often used by shared hosting providers to limit the bandwidth of customers.", 'twitchpress' );
        
    $httpstatus[510]['short'] = __( 'Not Extended', 'twitchpress' );
    $httpstatus[510]['wiki'] = __( "Further extensions to the request are required for the server to fulfil it.", 'twitchpress' );
    
    $httpstatus[511]['short'] = __( 'Network Authentication Required (RFC 6585)', 'twitchpress' );
    $httpstatus[511]['wiki'] = __( "The client needs to authenticate to gain network access. Intended for use by intercepting proxies used to control access to the network (e.g., \"captive portals\" used to require agreement to Terms of Service before granting full Internet access via a Wi-Fi hotspot).", 'twitchpress' );

    $httpstatus[520]['short'] = __( 'Unknown Error', 'twitchpress' );
    $httpstatus[520]['wiki'] = __( "The 520 error is used as a \"catch-all response for when the origin server returns something unexpected\", listing connection resets, large headers, and empty or invalid responses as common triggers.", 'twitchpress' );
    
    $httpstatus[521]['short'] = __( 'Web Server Is Down', 'twitchpress' );
    $httpstatus[521]['wiki'] = __( "The origin server has refused the connection from Cloudflare.", 'twitchpress' );
    
    $httpstatus[522]['short'] = __( 'Connection Timed Out', 'twitchpress' );
    $httpstatus[522]['wiki'] = __( "Cloudflare could not negotiate a TCP handshake with the origin server.", 'twitchpress' );
    
    $httpstatus[523]['short'] = __( 'Origin Is Unreachable', 'twitchpress' );
    $httpstatus[523]['wiki'] = __( "Cloudflare could not reach the origin server; for example, if the DNS records for the origin server are incorrect.", 'twitchpress' );
    
    $httpstatus[524]['short'] = __( 'A Timeout Occurred', 'twitchpress' );
    $httpstatus[524]['wiki'] = __( "Cloudflare was able to complete a TCP connection to the origin server, but did not receive a timely HTTP response.", 'twitchpress' );
    
    $httpstatus[525]['short'] = __( 'SSL Handshake Failed', 'twitchpress' ); 
    $httpstatus[525]['wiki'] = __( "Cloudflare could not negotiate a SSL/TLS handshake with the origin server.", 'twitchpress' );
    
    $httpstatus[526]['short'] = __( 'Invalid SSL Certificate', 'twitchpress' );
    $httpstatus[526]['wiki'] = __( "Cloudflare could not validate the SSL/TLS certificate that the origin server presented.", 'twitchpress' );
    
    $httpstatus[527]['short'] = __( 'Railgun Error', 'twitchpress' );
    $httpstatus[527]['wiki'] = __( "Error 527 indicates that the request timed out or failed after the WAN connection had been established.", 'twitchpress' );
    
    $httpstatus[530]['short'] = __( 'Site is frozen', 'twitchpress' );
    $httpstatus[530]['wiki'] = __( "Used by the Pantheon web platform to indicate a site that has been frozen due to inactivity.", 'twitchpress' );

    $httpstatus[598]['short'] = __( 'Network read timeout error', 'twitchpress' );
    $httpstatus[598]['wiki'] = __( "Used by some HTTP proxies to signal a network read timeout behind the proxy to a client in front of the proxy.", 'twitchpress' );
      
    if( !isset( $httpstatus[ $requested_status ] ) ) 
    {
        return false;
    }   
    elseif( !isset( $httpstatus[ $requested_status ][ $requested_meaning ] ) )
    {
        // Attempt to get another meaning and reduce errors. 
        if( $requested_meaning == 'wiki' && isset( $httpstatus[ $requested_status ][ 'wiki' ] ) )
        {
            return $httpstatus[ $requested_status ][ 'wiki' ]; 
        }
        elseif( $requested_meaning == 'twitch' && isset( $httpstatus[ $requested_status ][ 'twitch' ] ) )
        {
            return $httpstatus[ $requested_status ][ 'twitch' ];
        }
        
        return __( 'The request status description does not exist.', 'twitchpress' );        
    }

    return $httpstatus[ $requested_status ][ $requested_meaning ];
}
