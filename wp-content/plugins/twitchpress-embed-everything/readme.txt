=== TwitchPress Embed Everything ===
Contributors: Ryan Bayne
Donate link: https://www.patreon.com/zypherevolved
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Tags: Twitch, TwitchPress, Twitch.tv, TwitchPress Extension, Twitch Embed, Embed Everything, Embed Twitch Stream, Embed Twitch Chat
Requires at least: 4.4
Tested up to: 4.9
Stable tag: 1.2.0
Minimum core version: 1.6.1
Requires PHP: 5.6
                        
Embed a Twitch channel using this extension for the TwitchPress plugin.
                       
== Description ==

Add the Embed Everything plugin to your blog after installing the core TwitchPress plugin
called Channel Solution for Twitch. The core plugin provides a platform to build an advanced
Twitch system that can enhance how we use Twitch iframe and API features. 

This plugin focuses on a feature provided on the Twitch.tv developer site called Embed Everything. 
The feature is still a beta in October 2017 and for now this extension should be treated as a beta.  

= Links =                                                            

*   <a href="https://dev.twitch.tv/docs/embed#embedding-everything-public-beta" title="">Twitch Dev Docs</a>    
*   <a href="https://twitchpress.wordpress.com" title="">TwitchPress Blog</a>
*   <a href="https://github.com/RyanBayne/TwitchPress" title="">GitHub</a>       
*   <a href="https://twitter.com/ryan_r_bayne" title="Follow the projects Tweets.">Developers Twitter</a>     
*   <a href="https://twitter.com/twitchpress" title="Follow the projects Tweets.">Plugins Twitter</a>     
*   <a href="https://www.twitch.tv/zypherevolved" title="Follow my Twitch channel.">Authors Twitch</a>     
*   <a href="https://discord.gg/NaRB3wE" title="Chat about TwitchPress on Discord.">Discord Chat</a>          
*   <a href="https://www.patreon.com/zypherevolved" title="">Patreon Donations</a>     
*   <a href="https://www.paypal.me/zypherevolved" title="">PayPal Donations</a>       

= Features List = 

* Embed Twitch Live Video
* Embed Twitch Live Chat
* Sign In Button
* Follow Button
* Subscribe Button 

== Installation ==

1. Requires the core TwitchPress plugin to be installed first. It is officially called "Channel Solution for Twitch".
1. Method 1: Move folder inside the .zip file into the "wp-content/plugins/" directory if your website is stored locally. Then upload the new plugin folder using your FTP program.
1. Method 2: Use your hosting control panels file manager to upload the plugin folder (not the .zip, only the folder inside it) to the "wp-content/plugins/" directory.
1. Method 3: In your WordPress admin click on Plugins then click on Add New. You can search for your plugin there and perform the installation easily. This method does not apply to premium plugins.

== Frequently Asked Questions ==

= How do I display my live Twitch stream in a page? 
Add this shortcode "[twitchpress_embed_everything channel="ZypheREvolved" height="1000"]" to any page or post to begin displaying live video and chat from the
Twitch.tv site. The shortcode has parameters including "channel", "width, "height" and more. 

= Can I hire you to customize the plugin for me? =
Yes you can pay the plugin author to improve the plugin to suit your needs. Many improvements will be done free so
post your requirements on the plugins forum first. 

== Screenshots ==


== Languages ==

Translator needed to localize our Channel Solution for Twitch: TwitchPress, and it's extensions.

== Upgrade Notice ==

No special upgrade instructions this time.

== Changelog ==
= 1.2.0 = 
*  FIX - Shortcode [twitchpress-embed-everything] now returns content rather than echoing it which positions the content better. 
*  DEV - New method added for updating core system required scopes to tell admin which scopes are required. 

= 1.1.1 = 
* DEV - New scopes() function for holding the scopes needed by this extension. 

= 1.1.0 = 
* FIX - Corrected constant names which used "SYNC" and should have used "EMBED"

= 1.0.0 =
* DEV - Released 20th October 2017

== Version Numbers and Updating ==

Explanation of versioning used by myself Ryan Bayne. The versioning scheme I use is called "Semantic Versioning 2.0.0" and more
information about it can be found at http://semver.org/ 

These are the rules followed to increase the TwitchPress plugin version number. Given a version number MAJOR.MINOR.PATCH, increment the:

MAJOR version when you make incompatible API changes,
MINOR version when you add functionality in a backwards-compatible manner, and
PATCH version when you make backwards-compatible bug fixes.
Additional labels for pre-release and build metadata are available as extensions to the MAJOR.MINOR.PATCH format.



                  