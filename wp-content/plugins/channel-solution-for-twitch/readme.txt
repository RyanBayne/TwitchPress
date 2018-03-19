=== TwitchPress ===
Contributors: Ryan Bayne
Donate link: https://www.patreon.com/zypherevolved
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Tags: Twitch, Twitch.tv, Twitch Feed, Twitch Channel, Twitch Team, Twitch Embed, Twitch Stream, Twitch Suite, Twitch Bot, Twitch Chat 
Requires at least: 4.7
Tested up to: 4.9
Stable tag: 2.0.0
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
*   <a href="https://www.patreon.com/zypherevolved" title="">Patreon Pledges</a>     
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
* DEVS - Script changes that developers need to be aware of. 
* TEXT - A note to translators that text has changed. 
* FIX - Fixed faults or faults still being investigated. 
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

New setup step added. Please open the Help tab and go to the Installation section. Click on the Authorize Main Channel button. 

== Changelog ==

= 2.0.0 NOT RELEASED = 
* DEV  - Constant TWITCHPRESS_API_NAME replaced with TWITCHPRESS_API_NAME. 
* DEV  - Twitch API files moved to new "libraries/twitch" directory.
* DEV  - Twitch API files renamed, "kraken" replaced with "twitch" for easier switching between versions.
* FIX  - Visitor Scopes checkboxes in Setup Wizard now populate and are saved. 
* DEV  - Class TWITCHPRESS_Kraken_API renamed to TWITCHPRESS_Twitch_API.
* DEV  - Class TWITCHPRESS_Kraken_Calls renamed to TWITCHPRESS_Twitch_API_Calls.
* DONE - Andsim added to endorsed channels in new Twitch API version 6 (Helix directory).
* DONE - GamingFroggie added to endorsed channels in new Twitch API version 6 (Helix directory).
* DONE - Scarecr0w12 added to endorsed channels in new Twitch API version 6 (Helix directory).
* DONE - ImChrisP added to endorsed channels in new Twitch API version 6 (Helix directory).
* DONE - theBatclam added to endorsed channels in new Twitch API version 6 (Helix directory).
* DONE - GideontheGreyFox added to endorsed channels in new Twitch API version 6 (Helix directory).
* DEV  - Typo 84600 in BugNet changed to 86400, would not cause a bug, just earlier expiry of transient caches. 
* DEV  - New option for removing all TwitchPress options during deletion of plugin (option key: twitchpress_remove_options)
* DEV  - New option for removing all feed posts during deletion of plugin (option key: twitchpress_remove_feed_posts)
* DEV  - New option for removing all TwitchPress database tables during deletion of plugin (option key: twitchpress_remove_database_tables)
* DEV  - New option for removing user data (user meta mainly) during deletion of plugin (option key: twitchpress_remove_user_data)
* DEV  - New option for removing all media generated by the TwitchPress system, during deletion of plugin (option key: twitchpress_remove_media)
* DEV  - uninstall.php has been improved.
* FIX  - Corrected wpseed_bugnet_handlerswitch_tracing by removing "wpseed_".
* DEV  - New options.php file contains arrays of all entries to the WP options table.
* DEV  - class.twitchpress-admin-uninstall.php has been removed and replaced with class.twitchpress-admin-deactivate.php 
* DEV  - New meta.php file contains arrays of meta keys used in the TwitchPress system. 
* DEV  - $twitch_wperror removed from the Twitch API library as it is not in use.
* DEV  - Streamlabs API endpoints added to All-API library.
* DEV  - Removed $twitch_call_id from Twitch API class as it is not in use.
* DEV  - Deepbot settings removed - extension on hold pending a strictly localhost only phase. 


= 1.7.4 = 
* DONE - Sync extension has been merged into this plugin.
* DEV  - Manual subscription sync tool function added to core tools class. 

= 1.7.3 = 
* DONE - Setup Wizard links updated on Application step to take users to more applicable pages. 
* DONE - Added new links to the top of the Setup Wizard Application step - just makes more sense! 
* DONE - Fixed broken link to the ZypheREvolved Twitch channel in Help tab.  
* DEV  - Added defaults to parameters in function add_wordpress_notice().
* DEV  - do_action( 'twitchpress_manualsubsync' ) added to visitor procedure for manual Twitch sub data sync.
* DONE - Improvement program step in Setup Wizard changed to "Options".
* DONE - Setup wizard now includes the authorising of the main channel when submitting Options step. 
* DONE - Final step in the Setup Wizard looks better after some text changes.  

= 1.7.2 = 
* FIX - Corrected variable name $functions to $function in the new twitchpress_is_sync_due() function.

= 1.7.1 = 
* DONE - Added new FAQ to Help tab. 
* DONE - Corrected text domain "appointments" to "twitchpress" in around 20 locations. 
* DONE - Prevented direct access to some files in the library directory including library.twitchbot.php for better security. 
* DONE - New functions for managing sync event delays added to core functions file. 

= 1.7.0 = 
* FIX - Authorization of main account was taking user to a broken URL.
* FIX - PHP 7 does not accept rand( 10000000000000, 99999999999999 ) so broken it down into two separate rand(). 
* FIX - Above changes fixes problem when authorizing main account and having missing credentials. 

= 1.6.5 = 
* DEVS - The $code value in class.kraken-api.php is no longer url escaped. 
* DEVS - oAuth part removed from Setup Wizard when submitting application credentials. 
* DEVS - twitchpress_setup_application_save() no longer stores channel ID as current users Twitch ID. 
* DEVS - Help tab has been updated to display User and App statuses with the permitted scope for each.
* DONE - Added textareas to the Result column of the API Requests table to compact rows. 
* DONE - API Requests time column now shows the time that has passed and now raw time() value. 
* DEVS - API calls for checking app token using check_application_token() will no longer be logged as it is too common. 
* DONE - Use of get_top_games() in Help section is now logged better by adding the using function. 
* DEVS - Status sections in Help tab are now cached for 120 seconds due to the increasing number of calls within the feature. 
* DEVS - checkUserSubscription() no longer defaults token to the application token despite being a user side request. 
* DEVS - $code parameter removed from checkUserSubscription() as is no longer in use. 
* DEVS - $code parameter removed from getUserSubscription() as it is no longer in use. 
* DONE - User Status section in the Help tab now displays subscription data. 
* BUGS - The is_user_subscribed_to_main_channel() function was using WordPress user ID where Twitch user ID should be used. 
* DEVS - Removed $code parameter from checkUserSubscription() 
* DONE - Removed the Change Log link in Help tab. There is no currently an external change log.
* DEVS - Changed multiple if to elseif in administrator_main_account_listener) to reduce the script time as currently multiple if are being checked in all situations. 
* DEVS - Credential related functions moved from functions.twitchpress-core.php to the new functions.twitchpress-credentials.php 
* DEVS - twitchpress_update_user_twitchid() no longer updates twitchpress_auth_time which has not been used as far as I can tell. 
* INFO - The new functions.twitchpress-credentials.php file intends to clear up some confusion with credential management. 
* DEVS - Added security check to the Setup Wizard (now requires user to have activate_plugins capability to enter the wizard). 
* DEVS - Renamed checkUserSubscription() to get_users_subscription_apicall()

= 1.6.4 = 
* BUGS - Introduced a bug to scope checkboxes in 1.6.3 

= 1.6.3 = 
* DEVS - Isset applied to display_name to avoid notice.
* DEVS - Changed die() to wp_die() in class.twitchpress-admin-settings.php function save().
* BUGS - Notice will now be displayed when saving General settings. 
* DEVS - Removed twitchpress_redirect_tracking() and exit() line from class.twitchpress-settings-general.php. 
* INFO - The redirect for refresh in general settings prevented notice output. No reason for the redirect/refresh. 
* DONE - Submitting the Sync Values view will no longer request a new application token which resulted in a notice. 
* DONE - Added twitchstatus.com link to the Status section in Help tab to encourage indepedent investigation. 
* DEVS - Changed scope checkboxes to a new input type that allows an icon to be displayed indicating required status. 
* INFO - Scope list now indicates which scopes are required with a tick and all others with a cross. 

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
* DEV - get-channel-subscribers() no longer uses add_query_args(). 
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



                  