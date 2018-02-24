=== TwitchPress Login Extension ===
Contributors: Ryan Bayne
Donate link: https://www.patreon.com/ryanbayne
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Tags: Twitch, TwitchPress, Twitch.tv, Twitch Login, Twitch Register, Twitch Registration, Twitch User, Twitch Visitor
Requires at least: 4.4
Tested up to: 4.8
Stable tag: 1.3.3
Minimum TwitchPress version: 1.6.2
Requires PHP: 5.6
                        
Launch your own Twitch services using the TwitchPress plugin for WordPress.
                       
== Description ==

Add Twitch social login and registration to your TwitchPress service. This plugin acts as an extension
to TwitchPress and has no purpose on it's own. The required API class and configuration exists in the
core TwitchPress plugin. This helps to keep extensions simple. 

If you require more social login options and not just Twitch. You can install any social plugin and 
provide more options for your visitors. You can still use this extension to ensure that your users
profiles are populated with the values your TwitchPress service needs. 

= Links =                                                                
*   <a href="https://twitchpress.wordpress.com" title="">Blog</a>
*   <a href="https://github.com/RyanBayne/TwitchPress" title="">GitHub</a>       
*   <a href="https://twitter.com/ryan_r_bayne" title="Follow the projects Tweets.">Developers Twitter</a>     
*   <a href="https://twitter.com/twitchpress" title="Follow the projects Tweets.">Plugins Twitter</a>     
*   <a href="https://www.twitch.tv/zypherevolved" title="Follow my Twitch channel.">Authors Twitch</a>     
*   <a href="https://discord.gg/NaRB3wE" title="Chat about TwitchPress on Discord.">Discord Chat</a>          
*   <a href="https://www.patreon.com/ryanbayne" title="Patreon Donations">Patreon Donations</a>     
*   <a href="https://www.paypal.me/zypherevolved" title="PayPal Donations">PayPal Donations</a>       

= Features List = 

* Add a Twitch registration button to your WordPress standard registration form.
* Add a Twitch login button to your WordPress standard login form.

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

Translator needed.

== Upgrade Notice ==

No special upgrade instructions this time. 

== Changelog ==
= 1.3.4 NOT RELEASED = 
* DONE - Account creation error notice improved - now shows the values used to try and insert new user.
* DEV  - New hook "twitchpress_login_inserted_new_user" placed after wp_insert_user() and after meta update of Twitch account.

= 1.3.3 = 
* DEV - Now adds required scopes to the core for telling users what scopes are required for this plugin to operate.
 
= 1.3.2 = 
* FIX - Fixed object as array error when logging into with a new Twitch account in certain scenarios. 

== Version Numbers and Updating ==

Explanation of versioning used by myself Ryan Bayne. The versioning scheme I use is called "Semantic Versioning 2.0.0" and more
information about it can be found at http://semver.org/ 

These are the rules followed to increase the TwitchPress plugin version number. Given a version number MAJOR.MINOR.PATCH, increment the:

MAJOR version when you make incompatible API changes,
MINOR version when you add functionality in a backwards-compatible manner, and
PATCH version when you make backwards-compatible bug fixes.
Additional labels for pre-release and build metadata are available as extensions to the MAJOR.MINOR.PATCH format.



                  