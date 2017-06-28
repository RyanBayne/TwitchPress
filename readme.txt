=== Plugin Name ===
Contributors: Ryan Bayne
Donate link: https://www.patreon.com/zypherevolved
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: Twitch, Twitch.tv, Unofficial, Twitch Feed, Twitch Channel, Twitch Channel Feeds
Requires at least: 4.4
Tested up to: 4.8
Stable tag: 1.2.2
                        
Launch your own Twitch services using the TwitchPress plugin for WordPress.
                       
== Description ==

TwitchPress is an adaptable solution for the creation of a Twitch service. 
Marry your WordPress gaming site with your Twitch channel in everyway possible using the plugins extension system
or create a site just to offer channel management services.

= Core Features =
The initial purpose of the free plugin is to share WP posts on Twitch channel feeds and collect feed updates
from Twitch.tv for publishing as new WordPress posts. All updates to the core will focus on improving this feature
and the plugins extension system. Using the extension system we can make TwitchPress and WP do anything possible
with the Twitch API. 

= New for 2017 =
The plugin is still very new. The core plugin is highly reliable. Free extensions are still being developed and tested.
Premium extensions are not being developed yet although I have already made two privates ones.

= Links =                                                                
*   <a href="https://twitchpress.wordpress.com" title="">Blog</a>
*   <a href="https://github.com/RyanBayne/TwitchPress" title="">GitHub</a>       
*   <a href="https://twitter.com/zypherevolved" title="Follow the projects Tweets.">Authors Twitter</a>     
*   <a href="https://twitter.com/twitchpress" title="Follow the projects Tweets.">Plugins Twitter</a>     
*   <a href="https://www.twitch.tv/zypherevolved" title="Follow my Twitch channel.">Authors Twitch</a>     
*   <a href="https://discord.gg/NaRB3wE" title="Chat about TwitchPress on Discord.">Discord Chat</a>          
*   <a href="https://www.patreon.com/zypherevolved" title="">Patreon Donations</a>     
*   <a href="https://www.paypal.me/zypherevolved" title="">PayPal Donations</a>       

= Features List = 

1. Twitch API - Authorization Code Flow
1. Authorize a main Twitch account to handle schedule events.
1. Smart shortcodes that find live streams and embedding them.
1. Schedule system for automating API and IRC actions while you sleep.
1. Post content from WordPress to Twitch channel feed.
1. Coming Next: Import feed posts from Twitch into WordPress!

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

No special upgrade instructions this time. Just remember to 
backup your site files and database.

== Changelog ==
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



                  