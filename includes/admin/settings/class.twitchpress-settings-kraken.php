<?php
/**
 * TwitchPress Permissions Settings
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Admin/Settings
 * @version  1.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' );

if ( ! class_exists( 'TwitchPress_Settings_Kraken' ) ) :

/**
 * TwitchPress_Settings_Sections.
 */
class TwitchPress_Settings_Kraken extends TwitchPress_Settings_Page {

    /**
     * Constructor.
     */
    public function __construct() {

        $this->id    = 'kraken';
        $this->label = __( 'Twitch API', 'twitchpress' );

        add_filter( 'twitchpress_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
        add_action( 'twitchpress_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'twitchpress_settings_save_' . $this->id, array( $this, 'save' ) );
        add_action( 'twitchpress_sections_' . $this->id, array( $this, 'output_sections' ) );
    }

    /**
     * Get sections.
     *
     * @return array
     */
    public function get_sections() {

        $sections = array(
            'default'           => __( 'Permissions Scope', 'twitchpress' ),
            'entermaincredentials'  => __( 'Enter Main Credentials', 'twitchpress' ),
        );

        return apply_filters( 'twitchpress_get_sections_' . $this->id, $sections );
    }

    /**
     * Output the settings.
     */
    public function output() {
        global $current_section;

        $settings = $this->get_settings( $current_section );

        TwitchPress_Admin_Settings::output_fields( $settings );
    }

    /**
     * Save settings.
     */
    public function save() {
        global $current_section;
        $settings = $this->get_settings( $current_section );
        TwitchPress_Admin_Settings::save_fields( $settings );
        // Attempt to create a Twitch session on the assumption all credentials are ready.
        $kraken = new TWITCHPRESS_Kraken_API();
        $kraken->start_twitch_session_admin( 'main' );
    }

    /**
     * Get settings array.
     *
     * @return array
     */
    public function get_settings( $current_section = '' ) {
        $settings = array();
               
        if ( 'entermaincredentials' == $current_section ) {

            $settings = apply_filters( 'twitchpress_entermaincredentials_settings', array(
            
                array(
                    'title' => __( 'Enter Main Twitch API Application', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'This is the form for entering your main developer application. When you submit the form for the first time, you will go through the oAuth2 procedure. If a code already exists and it is still valid, the procedure will be shorter. When you arrive back on this screen, the token field should be populated and you should be able to make calls to Kraken.', 'twitchpress' ),
                    'id'     => 'mainapplicationcredentials'
                ),

                array(
                    'title'           => __( 'Main Channel', 'twitchpress' ),
                    'desc'            => __( 'Add the channel that the developer application has been created in.', 'twitchpress' ),
                    'id'              => 'twitchpress_main_channel_name',
                    'default'         => '',
                    'type'            => 'text',
                ),

                array(
                    'title'           => __( 'Main Channel ID', 'twitchpress' ),
                    'desc'            => __( 'Main channel ID is currently only set by TwitchPress to confirm oAuth2 credentials are correct.', 'twitchpress' ),
                    'id'              => 'twitchpress_main_channel_id',
                    'default'         => '',
                    'type'            => 'text',
                    'custom_attributes' => array( 'disabled' => 'disabled' ),
                ),
                
                array(
                    'title'           => __( 'Redirect URL', 'twitchpress' ),
                    'desc'            => __( 'Redirect URL', 'twitchpress' ),
                    'id'              => 'twitchpress_main_redirect_uri',
                    'default'         => '',
                    'autoload'        => false,
                    'type'            => 'text',
                ),

                array(
                    'title'           => __( 'Client ID', 'twitchpress' ),
                    'desc'            => __( 'Your applications public ID.', 'twitchpress' ),
                    'id'              => 'twitchpress_main_client_id',
                    'default'         => '',
                    'type'            => 'text',
                    'autoload'        => false,
                ),

                array(
                    'title'           => __( 'Client Secret', 'twitchpress' ),
                    'desc'            => __( 'Keep this value hidden at all times.', 'twitchpress' ),
                    'id'              => 'twitchpress_main_client_secret',
                    'default'         => '',
                    'type'            => 'password',
                    'autoload'        => false,
                ),

                array(
                    'title'           => __( 'Code', 'twitchpress' ),
                    'desc'            => __( 'Created by Kraken only.', 'twitchpress' ),
                    'id'              => 'twitchpress_main_code',
                    'default'         => '',
                    'type'            => 'password',
                    'autoload'        => false,
                    'custom_attributes' => array( 'disabled' => 'disabled' ),
                ),

                array(
                    'title'           => __( 'Token', 'twitchpress' ),
                    'desc'            => __( 'Created by Kraken only.', 'twitchpress' ),
                    'id'              => 'twitchpress_main_token',
                    'default'         => '',
                    'type'            => 'password',
                    'autoload'        => false,
                    'custom_attributes' => array( 'disabled' => 'disabled' ),
                ),

                array(
                    'type'     => 'sectionend',
                    'id'     => 'mainapplicationcredentials'
                )

            ));
            
        // Domain to Twitch API permission Options
        } elseif ( 'default' == $current_section ) {
            
            $default = 'no';
            
            $settings = apply_filters( 'twitchpress_permissions_scope_settings', array(
 
                array(
                    'title' => __( 'Global Scope', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'Scopes act like permissions. You set the scope of required permissions for a visitor to fully use your service. The visitor will see the list of scopes when they are sent to Twitch.tv (through oAuth2) to give your site permissions. Please learn and understand all scopes. You should only select the ones your service requires.', 'twitchpress' ),
                    'id'     => 'global_scope_options',
                ),

                array(
                    'title'           => __( 'Select Acceptable Scopes', 'twitchpress' ),
                    'desc'            => __( 'user_read: Read access to non-public user information, such as email address.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_user_read',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'start',
                    'show_if_checked' => 'option',
                ),

                array(
                    'desc'            => __( 'user_blocks_edit: Ability to ignore or unignore on behalf of a user.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_user_blocks_edit',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'user_blocks_read: Read access to a user\'s list of ignored users.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_user_blocks_read',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'user_follows_edit: Access to manage a user\'s followed channels.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_user_follows_edit',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'channel_read: Read access to non-public channel information, including email address and stream key.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_channel_read',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'channel_editor: Write access to channel metadata (game, status, etc).', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_channel_editor',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'channel_commercial: Access to trigger commercials on channel.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_channel_commercial',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'channel_stream: Ability to reset a channel\'s stream key.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_channel_stream',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'channel_subscriptions: Read access to all subscribers to your channel.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_channel_subscriptions',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'user_subscriptions: Read access to subscriptions of a user.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_user_subscriptions',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'channel_check_subscription: Read access to check if a user is subscribed to your channel.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_channel_check_subscription',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'chat_login: Ability to log into chat and send messages.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_chat_login',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
              
                array(
                    'desc'            => __( 'channel_feed_read: Ability to view to a channel feed.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_channel_feed_read',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'channel_feed_edit: Ability to add posts and reactions to a channel feed."', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_channel_feed_edit',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'communities_edit: Manage a user’s communities."', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_communities_edit',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'communities_moderate: Manage community moderators."', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_communities_moderate',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'collections_edit: Manage a user’s collections (of videos)."', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_collections_edit',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'channel_feed_edit: Turn on Viewer Heartbeat Service ability to record user data."', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_viewing_activity_read',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'end',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
 
                array(
                    'type'     => 'sectionend',
                    'id'     => 'global_scope_options'
                ),

                // VISITOR SCOPES
                array(
                    'title' => __( 'Visitor Scopes', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'These are the permissions users will be asked to accept when using Twitch to login and register.', 'twitchpress' ),
                    'id'     => 'visitor_scope_options',
                ),

                array(
                    'title'           => __( 'Select Acceptable Scopes', 'twitchpress' ),
                    'desc'            => __( 'user_read: Read access to non-public user information, such as email address.', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_user_read',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'start',
                    'show_if_checked' => 'option',
                ),

                array(
                    'desc'            => __( 'user_blocks_edit: Ability to ignore or unignore on behalf of a user.', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_user_blocks_edit',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'user_blocks_read: Read access to a user\'s list of ignored users.', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_user_blocks_read',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'user_follows_edit: Access to manage a user\'s followed channels.', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_user_follows_edit',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'channel_read: Read access to non-public channel information, including email address and stream key.', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_channel_read',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'channel_editor: Write access to channel metadata (game, status, etc).', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_channel_editor',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'channel_commercial: Access to trigger commercials on channel.', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_channel_commercial',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'channel_stream: Ability to reset a channel\'s stream key.', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_channel_stream',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'channel_subscriptions: Read access to all subscribers to your channel.', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_channel_subscriptions',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'user_subscriptions: Read access to subscriptions of a user.', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_user_subscriptions',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'channel_check_subscription: Read access to check if a user is subscribed to your channel.', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_channel_check_subscription',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'chat_login: Ability to log into chat and send messages.', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_chat_login',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
              
                array(
                    'desc'            => __( 'channel_feed_read: Ability to view to a channel feed.', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_channel_feed_read',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'channel_feed_edit: Ability to add posts and reactions to a channel feed."', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_channel_feed_edit',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'communities_edit: Manage a user’s communities."', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_communities_edit',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'communities_moderate: Manage community moderators."', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_communities_moderate',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'collections_edit: Manage a user’s collections (of videos)."', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_collections_edit',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'channel_feed_edit: Turn on Viewer Heartbeat Service ability to record user data."', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_viewing_activity_read',
                    'default'         => $default,
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'end',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
 
                array(
                    'type'     => 'sectionend',
                    'id'     => 'visitor_scope_options'
                ),

            ));
        }

        return apply_filters( 'twitchpress_get_settings_' . $this->id, $settings, $current_section );
    }
}

endif;

return new TwitchPress_Settings_Kraken();