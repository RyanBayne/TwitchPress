=== TwitchPress ===
Contributors: Ryan Bayne
Donate link: https://www.patreon.com/zypherevolved
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: Twitch, Twitch.tv, Twitch Feed, Twitch Channel, Twitch Team, Twitch Embed, Twitch Stream, Twitch Suite, Twitch Bot, Twitch Chat 
Requires at least: 4.4
Tested up to: 4.8
Stable tag: 1.2.4
                        
Launch your own Twitch services using the TwitchPress plugin for WordPress.
                       
== Description ==

TwitchPress BETA is an adaptable solution for the creation of a Twitch service that can do anything Kraken allows. 
Marry your WordPress gaming site with your Twitch channel in everyway possible using the plugins extension system
or create a site just to offer channel management services.

= Core Features =
The initial purpose of the free plugin is to share WP posts on Twitch channel feeds and collect feed updates
from Twitch.tv for publishing as new WordPress posts. All updates to the core will focus on improving this feature
and the plugins extension system. Using the extension system we can make TwitchPress and WP do anything possible
with the Twitch API. 

= New for 2017 =
The plugin is still very new. The core plugin is highly reliable. 
Free extensions are already being released but are also very new. 
Please test well before using on a live site. 
Please support the project, every share, like or contribution on GitHub
will drive the project forward.

= Links =                                                                
*   <a href="https://twitchpress.wordpress.com" title="">Blog</a>
*   <a href="https://github.com/RyanBayne/TwitchPress-Login-Extension" title="">GitHub</a>       
*   <a href="https://twitter.com/ryan_r_bayne" title="Follow the projects Tweets.">Developers Twitter</a>     
*   <a href="https://twitter.com/twitchpress" title="Follow the projects Tweets.">TwitchPress Twitter</a>     
*   <a href="https://www.twitch.tv/zypherevolved" title="Follow my Twitch channel.">Authors Twitch</a>     
*   <a href="https://discord.gg/NaRB3wE" title="Chat about TwitchPress on Discord.">Discord Chat</a>          
*   <a href="https://www.patreon.com/zypherevolved" title="">Patreon Donations</a>     
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

Please complete the Setup Wizard again. You will find it in the Help tab
on the plugins own settings page. 

== Changelog ==
= 1.2.4 = 
* FIX - Forwarding URL in start_twitch_session_admin() was causing a blank settings area in Twitch API tab. 
* DEV - class.twitchpress-settings-kraken.php improved to allow extension to add section. 
* DEV - New twitchpress_is_user_authorized() function makes it easier for all extensions to check the same user meta values for an authorized Twitch session.
* DEV - TwitchPress Sync Extension BETA added to list of plugins for quick install during Setup Wizard.
* DEV - As a temporary approach the sites official (default) Twitch account is applied as the WP users own account i.e. their personal use of the site will use that same account, their WP profile will display data from that channel.
* NEW - General settings page.
* NEW - Option to delete everything including data, when plugin is deleted. See Plugin Removal section in General Settings. 
* DEV - Now including background-process.php and async-request.php in the main file. 
* DEV - Plugin menu is loaded differently: in a manner that allows multiple post types to be added to it. 
* NEW - Channels custom post type. This will be a method of managing channels with the option of displaying them. 
* DEV - Channel feed sync to WordPress is much better with a more complex connection between posts, channels and owners. 
* DEV - Activating the channel to WordPress feed syncing will now create a new CRON job (scheduled task). 
* DEV - Removed option for updating Twitch feed entries pending further development.
* DEV - Removed option for updating WordPress posts when a Twitch feed item changes, pending further development. 
* DEV - A few lines removed from developertoolbar_uninstall_settings() whicch did not appear to have a purpose. 
* FIX - Function twitchpress_returning_url_nonced() now builds URL in a way that allows nonce check to pass. 
* DEV - Removed the mainviews folder and contents deleted as it was not yet in use.
* NEW - New Tools view - created using a WordPress core table for an approach that allows endless expansion. 
* DEV - Channel feed item ID is now queried straight after WordPress posts to Twitch and the item ID is stored in the original posts meta under "twitchpress_feed_item_id"

= 1.2.3 =
* DEV - TwitchPress_Settings_Permissions renamed to TwitchPress_Settings_Permissions
* DEV - Default footer message will no longer be displayed. 
* DEV - New settings views are being added, they will be hidden until active extensions require them. 
* DEV - Internationalizing (i18n) files added for the first time. 
* DEV - Updated scopes description to insist on strict selection of required scopes only.
* DEV - get_global_accepted_scopes() had a "twitch_scope" string changed to "twitchpress_scope".
* DEV - CSV 2 POST plugin removed from extensions list. 
* DEV - The first official extension "TwitchPress Login Extension" added to extensions list. 
* DEV - New function twitchpress_validate_code() applied to administrator_main_account_listener().
* DEV - Applied "$_SERVER['REQUEST_METHOD'] !== 'GET'" to $_GET listeners.
* DEV - Updated user-friendly descriptions in scopes(). 
* DEV - added "openid" scope to the scopes() function. 
* DEV - The $twitch_scopes variable has been updated with openid in TWITCHPRESS_Kraken5_Interface()
* DEV - Removed the getUserObject() function which has never been used and is no longer usable.

= 1.2.2 =
* DEV - Notices class improved to allow easier creation of all class (color) of notices. 
* DEV - Changed Senior Developer role (seniordeveloper) to TwitchPress Developer (twitchpressdeveloper)
* DEV - The code_twitchpress capability is no longer applied to administrators on installation.
* DEV - New twitchpressdevelopertoolbar capability added and is only assigned to the administrator with ID:1 and TwitchPress Developers.
* NEW - TwitchPress Developers toolbar now available. It will only show for users with "TwitchPress Developer" role or custom capability applied to an admin account.
* NEW - Toolbar option for uninstalling all TwitchPress options.
* DEV - Renamed twitchpress_main_channel to twitchpress_main_channel_name
* DEV - New option "twitchpress_main_channel_id"
* FIX - Channel does not exist situation reported 27th June fixed 28th June.
* DEV - Notice will now appear when API credentials are missing, encouraging user to complete the Setup Wizard. 
* DEV - Added esc_url() to second_level_configuration_options()
* DEV - Setup Wizard green styling replaced with purple. 

= 1.2.1 =
* FIX - Incorrect class name prevented plugin activation.  

= 1.2.0 =
* NEW - Setup wizard making installation clearer and more professional.
* NEW - Extensions system so developers can add features quickly.
* NEW - Settings pages now use the WordPress Options API.
* VER - Twitch API Kraken Version 5 now supported.

= When To Update = 

Browse the changes log and decide if an update is required. There is nothing wrong with skipping version if it does not
help you - look for security related changes or new features that could really benefit you. If you do not see any you may want
to avoid updating. If you decide to apply the new version - do so after you have backedup your entire WordPress installation 
(files and data). Files only or data only is not a suitable backup. Every WordPress installation is different and creates a different
environment for WTG Task Manager - possibly an environment that triggers faults with the new version of this software. This is common
in software development and it is why we need to make preparations that allow reversal of major changes to our website.

== Contributors ==
Donators, GitHub contributors and developers who support me when working on TwitchPress will be listed here. 

* IBurn36360 - Author of the main Twitch API class on GitHub.
* Automattic - The plugins initial design is massively based on their work.  
* Ignacio Cruz at WPMUDEV
* Ashley Rich (A5shleyRich)
* Igor Vaynberg
* M. Alsup
* Amir-Hossein Sobhi

== Version Numbers and Updating ==

Explanation of versioning used by myself Ryan Bayne. The versioning scheme I use is called "Semantic Versioning 2.0.0" and more
information about it can be found at http://semver.org/ 

These are the rules followed to increase the TwitchPress plugin version number. Given a version number MAJOR.MINOR.PATCH, increment the:

MAJOR version when you make incompatible API changes,
MINOR version when you add functionality in a backwards-compatible manner, and
PATCH version when you make backwards-compatible bug fixes.
Additional labels for pre-release and build metadata are available as extensions to the MAJOR.MINOR.PATCH format.



                  