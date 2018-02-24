=== TwitchPress Sync Extension ===
Contributors: Ryan Bayne
Donate link: https://www.patreon.com/zypherevolved
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Tags: Twitch, TwitchPress, Twitch.tv, Twitch Login, Twitch Register, Twitch Registration, Twitch User, Twitch Visitor
Requires at least: 4.4
Tested up to: 4.9
Stable tag: 1.3.2
Minimum TwitchPress version: 1.7.1
Requires PHP: 5.6
                        
Syncronize the data your TwitchPress system needs to run properly.
                       
== Description ==

This is an extension for the TwitchPress plugin. The core TwitchPress plugin
offers channel feed features. Extensions allow us to build a bigger system 

This TwitchPress extension focuses on syncronizing any Twitch.tv data your
WordPress site needs. The plugin will only attempt to syncronize data that is
actually needed by your custom TwitchPress system. Administrators also have
some control over what values are stored.  

Extensions should be integrated with the TwitchPress Sync Extension when
they require data that changes frequently i.e. username and email address
does not change frequently so we would not perform frequent calls to import
that data but subscription related data is different because it can change
monthly. 

Integrating plugins with this extensions will minimize what needs to be
created in the new plugin. 

= Links =                                                                
*   <a href="https://twitchpress.wordpress.com" title="">Blog</a>
*   <a href="https://github.com/RyanBayne/TwitchPress-sync-extension" title="">GitHub</a>       
*   <a href="https://twitter.com/ryan_r_bayne" title="Follow the projects Tweets.">Developers Twitter</a>     
*   <a href="https://twitter.com/twitchpress" title="Follow the projects Tweets.">Plugins Twitter</a>     
*   <a href="https://www.twitch.tv/zypherevolved" title="Follow my Twitch channel.">Authors Twitch</a>     
*   <a href="https://discord.gg/NaRB3wE" title="Chat about TwitchPress on Discord.">Discord Chat</a>          
*   <a href="https://www.patreon.com/zypherevolved" title="">Patreon Donations</a>     
*   <a href="https://www.paypal.me/zypherevolved" title="">PayPal Donations</a>       

= Features List = 

* Offers different approaches to maintaining different Twitch data.
* Provides developers with a standard approach to importing Twitch data that all extensions can use.
* Free and supported, because great people like you donate. 
* Twitch subscription sync when user logs into WP. Membership plugins can use the data to alter WP users membership level.

== Installation ==

1. Method 1: Move folder inside the .zip file into the "wp-content/plugins/" directory if your website is stored locally. Then upload the new plugin folder using your FTP program.
1. Method 2: Use your hosting control panels file manager to upload the plugin folder (not the .zip, only the folder inside it) to the "wp-content/plugins/" directory.
1. Method 3: In your WordPress admin click on Plugins then click on Add New. You can search for your plugin there and perform the installation easily. This method does not apply to premium plugins.

== Frequently Asked Questions ==

= Can I hire you to customize the plugin for me? =
Yes you can pay the plugin author to improve the plugin to suit your needs. Many improvements will be done free so
post your requirements on the plugins forum first. 

== Screenshots ==


== Languages ==


== Upgrade Notice ==

No special upgrade instructions this time. 

== Changelog ==
= 1.3.3 NOT RELEASED = 
* NEW - Added button to Profile view for manual Twitch data sync.
* DEV - sync_user_on_login() no longer calls sync_user() with "ignore delay" set to true, to prevent flooding by a bot login out and in constantly.
* DEV - sync_user_on_login() no longer passed true for notice output to prevent sudden notices appearing unrelated to login.
* DEV - As above for sync_user_on_viewing_profile(), no notices on going to profile and no longer ignores delay as an anti-flood measure. 
* DEV - twitch_subscription_status_save() no longer ignores the sync_user() delay but it allows notice output due to this running when profile is updated.
* DEV - tool_sync_all_users() now calls sync_user() with ignore delay set to true and notice output false, previously these parameters were omitted. 

= 1.3.2 = 
* FIX - User ID missing in twitchpress_sync_currentusers_twitchsub_mainchannel(). 
* FIX - User ID missing in is_users_sync_due().

= 1.3.1 = 
* DONE - Changed scopes array from "um" to "sync" in update_system_scopes_status(). 
* DEV  - $sync_user_flood_delay changed from 120 seconds to 3600 seconds to ensure systematic syncing is not too busy. Override available for manual sync requests.
* DEV  - Systematic syncing hook changed from wp_admin to shutdown so we can be a bit more constant - this is pending a CRON approach. 
* DEV  - sync_channel_subscribers() delay increased from 122 to 1800 seconds. 

= 1.3.0 =
* DEVS - sync_user_subscription_main_channel() no longer avoids processing administrators. 
*  FIX - Added a second method for syncing user sub. 
* DEVS - User sync delay increased from 30 seconds to 120 seconds - $sync_user_flood_delay in main file. 
*  FIX - Getting all subscribers for a channel is now done using the main channel token and code rather than application token. 

= 1.2.3 = 
* DEV - New scopes() function for holding the scopes needed by this extension.

= 1.2.1 =
* DEV - User profile will no longer display the users code. 
* DEV - User profile will no longer display the users token. 
* DEV - sync_user_subscription_main_channel() no longer creates notice strings that were not used in the intended BugNet trace. 
* DEV - sync_user_subscription_main_channel() now handles "null" return from Twitch.tv (possible cause of 500 error).

= 1.2.0 =
* FIX - Renamed TWITCHPRESS_Kraken5_Calls to TWITCHPRESS_Kraken_Calls in preparation for Twitch API version 6.

= 1.0.18 = 
* NEW - Channel subscriber systematic syncing is now set to off (value = no) by default.

= 1.0.17 =
* NEW - Subscription package name now shows in users profile i.e. "Channel Subscription (mr_woodchuck) - $24.99 Sub"

= 1.0.16 = 
* NEW - Import all subscribers for the main channel (data stored in Twitch Posts because user ID is the same as channel ID)

= 1.0.15 = 
* NEW - Twitch User ID is now displayed in WP profile. 

= 1.0.14 = 
* NEW - Twitch User ID is now displayed in WP profile. 

= 1.0.13 = 
* FIX - Previous released add a bug relating to subscription syncing trying to log with a WP_Error object as string.
* NEW - User profile now displays subscription status and plan. The status value is set first before establishing plan. 
* DEV - More tracing added to subscription plan arguments.
* DEV - Arguments in sync_user_subscription_main_channel() now clearer and handle WP_Error better. 

= 1.0.12 =
* FIX - Subscription sync bug found.
* DEV - New argument added to subscription processing.
* DEV - More logging is being done. 

= 1.0.11 = 
* DEV - More channel side methods have replaced user side methods for obtaining users subscription plan.

= 1.0.10 - 
* DEV - Subscription check is done from channel side and no longer requires permission from user. 

= 1.0.9 = 
* DEV - Twitch details are available on users profile and user edit view.

= 1.0.8 =
* FIX - Users plan was not being stored properly .

= 1.0.7 =
* DEV - sync_user() now has option to bypass the delay.
* DEV - Tool added for syncing all users. 

= 1.0.6 = 
* DEV - Changed wp_login sync action priority from 10 to 1. 
* DEV - Hooking into profile_personal_options to trigger syncing - basically when user views their WP profile. 
* DEV - Changed approach to preventing administrators being downgraded.
* DEV - Adds a tool to the core plugins Quick Tools table. 

= 1.0.5 =
* DEV - Special values can now be added to redirect URL to aid in debugging.  

= 1.0.4 =
* DEV - sync_user_subscription_main_channel() now returns if user ID is 1 just in-case the account gets a special role other than administrator.

= 1.0.3 =
* FIX - install() and deactivate() changed to static methods as required by registration hooks. 

= 1.0.2 =
* DEV - Loading is now controlled by core TwitchPress action hooks.
* DEV - register_activation_hook() moved outside of loading class. 
* DEV - register_deactivation_hook() moved outside of loading class.
 
= 1.0.1 =
* DEV - Improved subscription data syncing.
* FIX - Corrected two do_action() that allow membership/role plugins to react to a change in Twitch subscription.

= 1.0.0 =
* DEV - BETA Released July 2017

== Version Numbers and Updating ==

Explanation of versioning used by myself Ryan Bayne. The versioning scheme I use is called "Semantic Versioning 2.0.0" and more
information about it can be found at http://semver.org/ 

These are the rules followed to increase the TwitchPress plugin version number. Given a version number MAJOR.MINOR.PATCH, increment the:

MAJOR version when you make incompatible API changes,
MINOR version when you add functionality in a backwards-compatible manner, and
PATCH version when you make backwards-compatible bug fixes.
Additional labels for pre-release and build metadata are available as extensions to the MAJOR.MINOR.PATCH format.



                  