=== TwitchPress ===
Contributors: Ryan Bayne
Donate link: https://www.patreon.com/ryanbayne
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Tags: Twitch, Twitch.tv, Twitch Feed, Twitch Channel, Twitch Team, Twitch Embed, Twitch Stream, Twitch Suite, Twitch Bot, Twitch Chat 
Requires at least: 4.4
Tested up to: 4.8
Stable tag: 1.5.1
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
* Fully supported. 
* Free and Premium levels of service. 

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
= 1.5.1 = 
* DEV - BugNet activation switch removed. Individual handler switches should be used to reduce logging activity. 
* DEV - Deleted debug class file after migrating functions to core files. 
* DEV - Error dumps are now done in footer to prevent header output errors.
* DEV - Final step in setup wizard updated with links and "Contact Ryan" changed to "Support". 
* DEV - Example redirect URL in setup wizard is now based on the actual site domain using get_site_url(). 

= 1.5.0 = 
* FIX - If BugNet is deactivated it causes error in the Twitch API because the library is not included within the core file if BugNet switched off. 

= 1.4.3 = 
* DEV - Changed "Create a Post" button to "Post to Twitch" for clarity on post type. 
* DEV - HTTPDStatus log entries will only happen if Twitch returns something other than a 200 code. 
* NEW - Can now share any post type. 
* DEV - BugNet log entries are now level 100 by default meaning this level alone can be deactivated. Allows focus on higher level log entries.
* FIX - Error on daily log view when no logging has happened for the day resulting in no log file yet. 

= 1.4.2 = 
* DEV - Added new var_dump_twitchpress() function which only dumps when specified user is making the request. 
* DEV - Add new wp_die_twitchpress() function which only works if specific user makes request. 

= 1.4.1 = 
* NEW - Setting for BugNet for entry of user ID. Errors will only be displayed for the entered user. 

= 1.4.0 =
* FIX - Uninstallation class is now included in the uninstall.php file fixing uninstall error. 
* DEV - Added TwitchPress UM Extension to the extensions list in Setup Wizard. 
* NEW - Option added to disable system logging in BugNet when performing any logging to the plugins own log files. 
* DEV - twitchpress_redirect_tracking() now always adds a new value to the URL as a measure to prevent looping. 
* DEV - twitchpress_redirect_tracking() refuses redirect if "twitchpressredirected=1" is already in URL.
* DEV - System log entries are not prepended with "TwitchPress: " and "TwitchPress Error: ". 

= 1.3.18 = 
* DEV - Logging greatly increased in Kraken5 (Twitch API library for WordPress) - using BugNet for WordPress
* DEV - All generateError() replaced with BugNet logging which will increase logging for most API calls. 
* NEW - Daily Log tab in Data area displays a more constant history than traces which are primarily designed for investigations.
* FIX - Subscription query now relies on a returned array and not a status which was causing problems.
* DEV - Removed get_subscribers_plan() from Kraken library as it is no longer needed. 

= 1.3.17 =
* DEV - Now displaying URL on TWitch API Requests table.

= 1.3.16 =
* DEV - New core function for validating Twitch sub plan string. 

= 1.3.15 = 
* FIX - Unknown bug broke the Kraken5 library and required rollback. 
    
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



                  