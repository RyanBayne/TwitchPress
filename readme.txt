=== TwitchPress ===
Contributors: Ryan Bayne
Donate link: https://www.patreon.com/ryanbayne
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Tags: Twitch, Twitch.tv, Twitch Feed, Twitch Channel, Twitch Team, Twitch Embed, Twitch Stream, Twitch Suite, Twitch Bot, Twitch Chat 
Requires at least: 4.4
Tested up to: 4.9
Stable tag: 1.6.2
Requires PHP: 5.6
                        
Launch your own Twitch services using the TwitchPress plugin for WordPress.
                       
== Description ==

TwitchPress is an adaptable solution for the creation of a Twitch service that can do anything Twitch.tv allows. 
Marry your WordPress gaming site with your Twitch channel in everyway possible using the plugins extension system
or create a site that offersr channel management services to the public.

= Core Features =
The initial purpose of the free plugin is to share WP posts on Twitch channel feeds and collect feed updates
from Twitch.tv for publishing as new WordPress posts. All updates to the core will focus on improving this feature
and the plugins extension system. Using the extension system we can make TwitchPress and WP do anything possible
with the Twitch API. 

= Links =                                                                
*   <a href="https://twitchpress.wordpress.com" title="">Blog</a>
*   <a href="https://github.com/RyanBayne/TwitchPress-Login-Extension" title="">GitHub</a>       
*   <a href="https://twitter.com/ryan_r_bayne" title="Follow the projects Tweets.">Developers Twitter</a
*   <a href="https://twitter.com/twitchpress" title="Follow the projects Tweets.">TwitchPress Twitter</a>
*   <a href="https://www.twitch.tv/zypherevolved" title="Follow my Twitch channel.">Authors Twitch</a>   
*   <a href="https://discord.gg/NaRB3wE" title="Chat about TwitchPress on Discord.">Discord Chat</a>     
*   <a href="https://www.patreon.com/ryanbayne" title="">Patreon Pledges</a>     
*   <a href="https://www.paypal.me/zypherevolved" title="">PayPal Donations</a>       

= Features List = 
 
* Post content to Twitch feed.
* Get content from Twitch feed.
* Extension system to build Twitch suites. 
* Custom post type for Twitch posts. 
* Share standard posts to Twitch feeds.
* Fully supported. 
* Free and Premium levels of service. 
* Channel Status Indicator shortcode.
* Channel Status Line shortcode.
* Channel Status Box shortcode. 

= Features In Extensions = 

* Sign-In Via Twitch
* Registration Via Twitch
* Embed Live Streams
* Embed Live Chat
* Frequent data sync.
* Ultimate Member integration.

= Changelog Code = 
* DONE - Basic changes that do not require testing. 
* TEXT - A note to translators that text has changed. 
* BUGS - Fixed faults or faults still being investigated. 
* INFO - Helpful information regarding recent changes. 
* HELP - A request for help, usually explained on GitHub further. 

== Installation ==

1. Method 1: Move folder inside the .zip file into the "wp-content/plugins/" directory if your website is stored locally. Then upload the new plugin folder using your FTP program.
1. Method 2: Use your hosting control panels file manager to upload the plugin folder (not the .zip, only the folder inside it) to the "wp-content/plugins/" directory.
1. Method 3: In your WordPress admin click on Plugins then click on Add New. You can search for your plugin there and perform the installation easily. This method does not apply to premium plugins.

== Frequently Asked Questions ==

= Can I hire you to customize the plugin for me? =
Yes you can pay the plugin author to improve the plugin to suit your needs. Many improvements will be done free so
post your requirements on the plugins forum first. 

== Screenshots ==

1. Custom list of plugins for bulk installation and activation.
2. Example of how the WP admin is fully used. Help tab can be available on any page.
3. Security feature that helps to detect illegal entry of administrator accounts into the database.

== Languages ==

Translator needed to localize the Channel Solution for Twitch.

== Upgrade Notice ==

Please configure and submit the Permissions Scopes view to initiate new scope settings. 

== Changelog ==

= 1.6.3 NOT RELEASED - GENERAL STABILITY CHANGES = 
* DEVS - Isset applied to display_name to avoid notice.
* DEVS - Changed die() to wp_die() in class.twitchpress-admin-settings.php function save().
* BUGS - Notice will now be displayed when saving General settings. 
* DEVS - Removed twitchpress_redirect_tracking() and exit() line from class.twitchpress-settings-general.php. 
* INFO - The redirect for refresh in general settings prevented notice output. No reason for the redirect/refresh. 
* DONE - Submitting the Sync Values view will no longer request a new application token which resulted in a notice. 
* DONE - Added twitchstatus.com link to the Status section in Help tab to encourage indepedent investigation. 

= 1.6.2 = 
* DONE - Improved the Status section in Help tab. 
* TEXT - Changed "Invalid user token" to "Token has expired" to seem less like a fault. 
* DEVS - Removed 2nd of 2 parameters from postFeedPost() as it would never be used. 
* DEVS - publish_to_feed() now gets the current users token and passes it to postFeedPost(). 
* DEVS - postFeedPost() now requires a user token to be passed. 

= 1.6.1 = 
* FILE - Deleted class.twitchpress-admin-main-views.php (not in use).
* FILE - Delete "includes/admin/mainviews/" as it was never used.
* BUGS - User token problems fixed. 

= 1.6.0 = 
* DEV - Scope value removed from request_app_access_token()
* DEV - request_app_access_token() now updates the stored token.
* DEV - WP posts will be shareable by default now to avoid confusion. 
* FIX - Ripple of changes through Kraken 5 library to improve token handling. 
* DEV - Function generateToken() is now request_user_access_token(). 
* DEV - $code parameter removed from getChannelObject_Authd(). 
* DEV - getChannelObject_Authd() replaced with get_tokens_channel().
* DEV - twitchpress_prepare_scopes() adds + and not literal spaces.
* DEV - Status section of Help tab now performs more tests. 
* INFO - 400, 401 and 500 errors returned again but have been addressed.
* DEV - get_channel_subscribers() no longer uses add_query_args(). 
* DEV - Added new user meta "twitchpress_token_refresh". 
* DEV - Removed wp_setcookie_twitchoauth2_ongoing() (not in use or complete). 
* DEV - administrator_main_account_listener() now uses establish_user_token() instead of request_user_access_token().
* TEX - Changed "Channel Name" on Setup Wizard to "Main Channel Name".
* TEX - Changed "ZypheREvolved, StarCitizen, TESTSquadron" to "ZypheREvolved, StarCitizen, nookyyy". 


= When To Update = 

Browse the changes log and decide if an update is required. There is nothing wrong with skipping version if it does not
help you - look for security related changes or new features that could really benefit you. If you do not see any you may want
to avoid updating. If you decide to apply the new version - do so after you have backedup your entire WordPress installation 
(files and data). Files only or data only is not a suitable backup. Every WordPress installation is different and creates a different
environment for WTG Task Manager - possibly an environment that triggers faults with the new version of this software. This is common
in software development and it is why we need to make preparations that allow reversal of major changes to our website.

== Contributors ==
Donators, GitHub contributors and developers who support me when working on TwitchPress will be listed here. 

* nookyyy      - A popular Twitch.tv streamer who done half of the testing.
* IBurn36360   - Author of the main Twitch API class on GitHub.
* Automattic   - The plugins initial design is massively based on their work.  
* Ashley Rich  - I used a great class by Ashley (Username A5shleyRich).

== Version Numbers and Updating ==

Explanation of versioning used by myself Ryan Bayne. The versioning scheme I use is called "Semantic Versioning 2.0.0" and more
information about it can be found at http://semver.org/ 

These are the rules followed to increase the TwitchPress plugin version number. Given a version number MAJOR.MINOR.PATCH, increment the:

MAJOR version when you make incompatible API changes,
MINOR version when you add functionality in a backwards-compatible manner, and
PATCH version when you make backwards-compatible bug fixes.
Additional labels for pre-release and build metadata are available as extensions to the MAJOR.MINOR.PATCH format.



                  