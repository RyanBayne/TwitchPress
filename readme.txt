=== TwitchPress ===
Contributors: Ryan Bayne
Donate link: https://www.patreon.com/twitchpress
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Tags: Twitch, Twitch.tv, Twitch Channel, Twitch Embed, Twitch Stream, Twitch API, TwitchPress
Requires at least: 5.2
Tested up to: 5.4
Stable tag: 3.5.0
Requires PHP: 5.6
                        
Unofficial Twitch.tv power-up for your WordPress! 
                       
== Description ==

Forge the ultimate tool in your quest to attract viewers using the power of
Twitch and WordPress combined. This is the ultimate recipe - fusing the purple 
Twitch API energy with the power of a Content Management Solution like WP. 

TwitchPress is unofficial and has not been endorsed by Twitch Interactive, Inc. Use of this plugin requires
the full understanding and acceptance of Twitch Interactive, Inc. terms of service.

= Links =                                                                
*   <a href="https://twitchpress.wordpress.com" title="">Blog</a>
*   <a href="https://github.com/RyanBayne/TwitchPress-Login-Extension" title="">GitHub</a>       
*   <a href="https://twitter.com/ryan_r_bayne" title="Follow the projects Tweets.">Developers Twitter</a>
*   <a href="https://twitter.com/twitchpress" title="Follow the projects Tweets.">TwitchPress Twitter</a>
*   <a href="https://www.twitch.tv/lolindark1" title="Follow my Twitch channel.">Authors Twitch</a>   
*   <a href="https://discord.gg/ScrhXPE" title="Chat about TwitchPress on Discord.">Discord Chat</a>     
*   <a href="https://www.patreon.com/twitchpress" title="">Patreon Pledges</a>     
*   <a href="https://www.paypal.me/zypherevolved" title="">PayPal Donations</a>       

= Features List = 
 
* Sign-In Via Twitch
* Various shortcodes 
* Registration Via Twitch
* Embed Live Streams
* Embed Live Chat
* Helix (API v6) supported

== Installation ==

1. Method 1: Move folder inside the .zip file into the "wp-content/plugins/" directory if your website is stored locally. Then upload the new plugin folder using your FTP program.
1. Method 2: Use your hosting control panels file manager to upload the plugin folder (not the .zip, only the folder inside it) to the "wp-content/plugins/" directory.
1. Method 3: In your WordPress admin click on Plugins then click on Add New. You can search for your plugin there and perform the installation easily. This method does not apply to premium plugins.

== Screenshots ==

1. Custom list of plugins for bulk installation and activation.
2. Example of how the WP admin is fully used. Help tab can be available on any page.
3. Security feature that helps to detect illegal entry of administrator accounts into the database.

== Languages ==

Translator needed to localize the Channel Solution for Twitch.


== Changelog == 

= 3.5.0 Released 24th June 2020 = 
* Faults Resolved
    - Fixed new bug regarding undefined constant making path to pro edition folder
    - Password field was still showing on WP login form when Twitch Only login activated
* Feature Improvements
    - Addded Google oAuth option under Other API 
    - Added new tool to begin testing the newely added YouTube API
    - Added option to redirect all logins to a custom "logged-in" page (no longer Twitch logins only)
    - Text removed from General Settings that said "Please use logging with care..."
    - New settings view for system switches
    - Some post types will only be available when the applicable system is activated
    - Latest post tools output improved (no longer a var_dump)
    - View and Edit links added to the tool for viewing the latest post
* Technical Notes
    - Giveaways system has progressed but still not ready to use
    - Added the Google v1-master API library to "libraries/allapi/youtube"
    - A big renderes pro edition unusable in 3.4.2 
    - Corrected multiple uses of "displayerrorsno" as a notice ID in tools 
    - Moved inclusion of functions.api-streamlabs.php from main functions.php to All-API loader.php
    - Login behaviour changed to redirect none Twitch users to the custom "logged-in" page
    - Content of functions.twitchpress-shortcodes.php moved to twitchpress/shortcodes.php and file deleted
    - Function twitchpress_get_sub_plan() can now take a null for channel ID and will default to main channel ID 
    - New "cache" attribute added to function twitchpress_shortcode_init() 
    - Commented core file require_once() lines with RFA (required for activation) as a step towards improving activation and loading times
* Configuration Advice
    - Normal plugin removal from WP plugins view now removes options and other data by default (can be turned off)
* Database Changes
    - None= 
    
= 3.4.3 Released 26th May 2020 = 
* Faults Resolved
    - BugNet main .css file now properly included
* Feature Improvements
    - Tool created for installation of pro-edition
    - Button added to Help tab for installation of pro-edition
* Technical Notes
    - Initial steps taking to turn pro-upgrade into an extension-plugin of it's own for easier updating
    - CSS path for channel list short-code was changed with a new constant
    - New "delete" value added to shortcodes for forcing cache deletion during development
* Configuration Advice
    - None
* Database Changes
    - None
    
= 3.4.2 Released 20th May 2020 = 
* Faults Resolved
    - The incomplete Perks post-type has been hidden to prevent confusion
    - Channel Status shortcode fixed (individual value version)
    - Shortcodes output fix that will make content building much easier now
* Feature Improvements
    - Kraken/Helix switch no longer warns user not to use Helix for live sites
    - Channel List shortcode styling improved a little
    - Some shortcode content will be stored in WP longer to prevent blank pages
    - DeepBot API support removed due to no official API
    - Added Patron API form in Settings
    - Added StreamElements API form in Settings
    - New tool for testing Streamlabs by outputting a random users loyalty-points
    - Removed discontinued extensions from Setup Wizard (only third-party integration/bridge plugins will be added there in future)
    - Avatars applied from Twitch logos are no longer over-sized
    - Perks post-type (not ready for live use) moved from pro-upgrade to core plugin (advanced features will be in pro-ugprade) 
* Technical Notes
    - Made changes to Streamlabs API that need plenty testing before live use
    - class.all-api.php had a lot done to it and is ready to use
    - A use of "$twitch_user->profile_image_url" replaced with "$twitch_user->logo" in class.twitchpress.login.php
    - Removed lines with twitchpress_update_user_meta_avatar() when updating an existing account in class.twitchpress.login.php
    - Created function twitchpress_twitch_logo_url() for storing users Twitch.tv logo URL in user-meta
    - New user meta key "twitchpress_twitch_logo_attachment_id" holds WP attachment ID for the original Twitch.tv logo for that user
    - Updated define_hard_constants() with changes focusing on links and removing unused constants 
    - Changed twitchpress_var_dump() to always wp_die() to reduce the risk of developers not knowing about a var_dump() happening in public
    - Replaced uses of get_user_by_login_name_without_email_address() with get_channel_id_by_name() (fixing bugs)
    - Added "expiry" value to some shortcodes (used for transient cache age in seconds)
    - Channel List shortcode CSS changed to stop forcing Twitch names onto a second line
    - Shortcode "refresh" value no longer used to set transient (cache) expiry (can now refresh sooner but keep older content as backup)
    - All custom post type files moved to new "posts" folder in core (pro and larger systems post-types will go there)
* Configuration Advice
    - None
* Database Changes
    - None
    
= 3.4.1 Released 4th May 2020 = 
* Faults Resolved
    - Fixed error on BugNet Issues caused when BugNet not yet installed
    - Fixed error on BugNet Traces caused when BugNet not yet installed
* Feature Improvements
    - None
* Technical Notes
    - None
* Configuration Advice
    - None
* Database Changes
    - None
    
= 3.4.0 Released 19th February 2020 =
* Faults Resolved
    - bugnet_add_trace_steps() undefined error fixed
* Feature Improvements
    - Wizard scope checkboxes now repopulate if forced to return to the form
    - New post type for giveaways - very early days for this system
* Technical Notes
    - Correct line 7 in class.twitchpress-posts-gate.php - wrong class name being used
* Configuration Advice
    - None
* Database Changes
    - Tables can be installed for supporting giveaways system 
    
= 3.3.5 Released 10th February 2020 =
* Faults Resolved
    - Login shortcode CSS no longer loads on every page
    - Pro channel list shortcode no longer causes error if zero members are online
* Feature Improvements
    - Pro channel list shortcode can now be ordered in different ways
    - Pro channel list shortcode can display online channels only or offline only
* Technical Notes
    - BugNet functions file no longer included in the construct of the main BugNet class                           
    - Pro channel list shortcode now uses a default stream-team set as constant and not in-line string
    - Some admin files now load using admin_init and no longer load for all requests (which was temporary)
    - Removed __construct() from some admin class where add_action() was inside
    - Some add_actions() now called at end of admin class files where object is created and destroyed
    - twitchpress_generate_authorization_url() is no longer called in TwitchPress_Admin_Help() construct
    - Sync user tool no longer runs on opening Tools views
    - Removed unused __get() from class WordPressTwitchPress()
    - Renamed twitchpress_get_channels_by_meta() to twitchpress_get_channel_posts_by_meta()
    - Removed line 170 from class.twitchpress-admin-settings.php due to a wp_enqueue_script() no longer required
* Configuration Advice
    - None
* Database Changes
    - None 
    
= 3.3.4 Released 12th Dec 2019 =
* Faults Resolved
    - None
* Feature Improvements
    - New tracing approach implemented (uses database instead of log files)
* Technical Notes
    - Function twitchpress_db_selectwherearray() changed for better error output after an issue reported
* Configuration Advice
    - Activate BugNet tracing (save BugNet settings page) to install tracing service database tables
* Database Changes
    - None 
    
= 3.3.3 Released 20th November 2019 =
* Faults Resolved
    - When a team reaches 101 or more users it no longer breaks the channel list shortcode
    - Data views are back
    - Tools views are back
* Feature Improvements
    - Channel lists now display icons
* Technical Notes
    - Blog link updated on final step of Setup Wizard (from personal blog to TwitchPress blog)
    - New "wpdialogue" folder in libraries directory - it will be a slow growing approach to notices to improve the current system
* Configuration Advice
    - None
* Database Changes
    - None    
    
= 3.3.2 Released 11th November 2019 =
* Faults Resolved
    - TTFB improved by reducing the number of API token calls during WP header loading which allows the page to load quicker 
* Feature Improvements
    - New action link appears on Plugins view titled "Pro Edition" when pro folder is present
* Technical Notes
    - Performance has been improved 
    - Uses of TwitchPress() replaced due to repeat __construct() usage possible causing performance loss
    - Work still ongoing to improve TTFB on the frontend - involves performing more calls nearer PHP shutdown (WP footer)
* Configuration Advice
    - None
* Database Changes
    - None
    
= 3.3.1 Released 5th November 2019 =
* Faults Resolved
    - None
* Feature Improvements
    - None
* Technical Notes
    - save_subscriber() in sync class replaced with function twitchpres_save_subscriber_post() 
    - None systematic data syncing is being separated from the systematic data sync class
    - New function twitchpress_sync_user_on_registration() added to automatic registration during shortcode login
* Configuration Advice
    - None
* Database Changes
    - None
    
= 3.3.0 Released 4th November 2019 =
* Faults Resolved
    - None
* Feature Improvements
    - Shortcode connect to twitch will send visitor to the original page if registered - this ensures friendly output actually happens
    - New Users Login and Registration option to prevent TwitchPress doing any redirects on login/registration
* Technical Notes
    - Default redirect location for shortcode login is the page displaying the connect to Twitch button
* Configuration Advice
    - None
* Database Changes
    - None
    
= 3.2.0 Released 4th November 2019 = 
* Faults Resolved
    - Login and Registration issued related to the transition from Kraken to Helix
    - Streamlabs token now updates in API settings
* Feature Improvements
    - None
* Technical Notes
    - Changed uses of Streamlabs token from "default_token" to "default_access_token"
* Configuration Advice
    - None
* Database Changes
    - None
    
= 3.1.1 Released 24th October = 
* Faults Resolved
    - Some admin notices were not being displayed
    - Frequent error released in 3.1.0
* Feature Improvements
    - Less notices need to be dismissed (less annoying notices)
* Technical Notes
    - BugNet does not run init() until user runs BugNet installation. 
    - Moved output_custom_notices() add_action call to the init() of the TwitchPress_Admin_Notices() class
    - Added self::remove_notice( $notice ) to output_custom_notices() and removed dismissable line from notice HTML
* Configuration Advice
    - None
* Database Changes
    - None

= 3.1.0 Released 22nd October 2019 = 
* Faults Resolved
    - UM plugin now detected
* Feature Improvements
    - BugNet section moved from General settings to a BugNet tab (plans for most settings)
    - BugNet now requires initial activation which installs new tables for collecting key monitoring data
* Technical Notes
    - Database work has begun for the first time and within the BugNet package
    - New listener and post processing approach being developed for Streamlabs admin oAuth
    - Streamlabs API is_app_set() now only returns boolean and not an array of missing app credentials.
* Configuration Advice
    - None
* Database Changes
    - Using BugNet may install tables depending on features used
    
3.0.2: Beta Released 15th September = 
* Faults Resolved
    - Undefined $transient (was never reported)
* Feature Improvements
    - None
* Technical Notes
    - Added a default style to connect_button_style_one()
* Configuration Advice
    - None
* Database Changes
    - None
    
= 3.0.1: Beta Released 15th September = 
* Faults Resolved
    - Uncaught Error: Call to undefined method TwitchPress_Twitch_API::is_app_set()
* Feature Improvements
    - None
* Technical Notes
    - None
* Configuration Advice
    - None
* Database Changes
    - None
    
= 3.0.0: Beta Released 14th September = 
* Bugfixes
    - Kraken no longer operates fully so switched plugins default to Helix (its time)
    - Corrected variable in new follower syncing 
    - Login using Twitch on WP core form has been fixed in this pro edition (same changes going to original edition)
    - New posts will no longer default to a locked state based on the "none" value in the meta menu 
    - Changed twitchfeed_rewrite_slug to twitchchannels_rewrite_slug in channels post type
    - Login bug fix related to new Helix endpoints incorrect use
* Feature Changes     
    - Services step in wizard makes it clearer that services are third-party and not part of the TwitchPress project
    - New installations new default to Twitch API version 6 (Helix) while version 5 now causes errors and is no longer supported
    - Development started on clips shortcode [twitchpress_shortcodes shortcode="clips_gallery" style="basic" type="latest" channel_name="Ninja"]      
    - New shortcode: [twitchpress_shortcodes shortcode="channel_list" type="team" team="test" style="shutters" refresh="60"]      
    - New Twitch subscriber gate features (requires much testing to confirm sub syncing works smoothly)
    - Any post/page can have a basic (more to come) follower requirement applied
    - Any post/page can have a basic (lots more to come) susbcriber requirement applied 
    - Removed feed tool from admin menu
    - Custom post type for channels is no longer "twitchchannels" and just "channels"
    - New shortcode for outputing live stream data - tested in working
    - Testing required on new shortcode for outputting bits leaderboard - see Discord
    - New clips shortcode
    - New bits leaderboard shortcode
    - New game shortcode
    - Improved CSS/Styling for shortcode login button
    - Improved CSS/Styling for WP login form button  
    - New public notice output for shortcode login     
* Technical Changes
    - HTML escaping (security) applied to TwitchPress_Shortcode_Channel_List()
    - Pro update approach applied with extended development going into a new "pro" folder
    - The new channel list shortcode loads far quicker due to multiple API calls being reduced to one
    - Function twitchpress_shortcode_init() moved to shortcodes.php file to make the design easier to understand
    - Standard user sync now includes follower (to main channel) status 
    - New set_accept_header() now sets the Accept header if not set in primary API method 
    - New advanced channel list shortcode has been started (a lot more to be added to it)    
    - Note: systems approach has been replaced with more class files
    - Moved login settings from login-by-shortcode.php to settings-users.php
    - Corrected text from "Feed channels list" to "Channels list" in custom post type
    - Added new "custom" folder to includes for custom post type classes
    - Moved post-type-perks.php to new "custom" folder
    - Class TwitchPress_Post_types() renamed to TwitchPress_Post_Type_Channel()
    - Renamed file post-typese.php to post-type-channels.php
    - Commented out a use of flush~rewrite~rules() in admin-settings.php
    - Commented out add_action() line calling flush~rewrite~rules in post-type-channels.php
    - Commented out add_action line calling flush~rewrite~rules in post-type-perks.php
    - Removed delete_option( 'twitchpress_visitor_scope_channel_feed_read' )
    - Removed delete_option( 'twitchpress_visitor_scope_channel_feed_edit' ) 
    - Removed delete_option( 'twitchpress_scope_channel_feed_edit' )
    - Removed delete_option( 'twitchpress_scope_channel_feed_read' )
    - Removed $arr[ 'twitchpress_serviceswitch_feeds_posttofeed' ] = array();
    - Removed $arr[ 'twitchpress_serviceswitch_feeds_scheduledposts' ] = array();
    - Removed $arr[ 'twitchpress_serviceswitch_feeds_prependappend' ] = array();
    - Removed $arr[ 'twitchpress_remove_feed_posts' ] = array();
    - Removed $arr[ 'twitchpress_apply_feed_sync_limits' ] = array();
    - Removed $arr[ 'twitchpress_feed_sync_limit_hourly' ] = array();
    - Removed $arr[ 'twitchpress_feed_sync_channel_limit_daily' ] = array();
    - Removed $arr[ 'twitchpress_shareable_posttype_twitchfeed' ] = array();
    - Deleted class.twitchpress-settings-feeds.php    
    - Removed function twitchpress_activate_channel_feedtowp_sync()
    - Removed function twitchpress_prepare_post_to_feed_content()
    - Removed line "'feed' => twitchpress_kraken_endpoints_feed()"
    - Removed function second_level_feed_tools()
    - Plugin registration hook moved to main file to improve installation procedure
    - Plugin deactivation hook moved to main file to improve uninstallation procedure    
    - Custom post type for channels is no longer "twitchchannels" and just "channels"
* Configuration
    - No changes
* Database
    - No changes

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



                  