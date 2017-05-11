<?php
/**
 * TwitchPress Permissions Settings
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Admin/Settings
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'TwitchPress_Settings_Permissions' ) ) :

/**
 * TwitchPress_Settings_Sections.
 */
class TwitchPress_Settings_Permissions extends TwitchPress_Settings_Page {

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
            ''              => __( 'Permissions Scope', 'twitchpress' ),
            'maincredentials'       => __( 'Main Credentials', 'twitchpress' ),
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
        // Attempt to create a Twitch session on the assumpetion all credentials are ready.
        $kraken = new TWITCHPRESS_Kraken5_Interface();
        $kraken->start_twitch_session_admin( 'main' );
    }

    /**
     * Get settings array.
     *
     * @return array
     */
    public function get_settings( $current_section = '' ) {
        if ( 'maincredentials' == $current_section ) {

            $settings = apply_filters( 'twitchpress_sectionb_settings', array(
            
                array(
                    'title' => __( 'Main Twitch API Application', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'This is the form for entering your main developer application. When you submit the form, you will go through the oAuth2 procedure. If a code already exists and it is still valid, the procedure will be shorter. When you arrive back on this screen, the token field should be populated and you should be able to make calls to Kraken.', 'twitchpress' ),
                    'id'     => 'mainapplicationcredentials'
                ),

                array(
                    'title'           => __( 'Main Channel', 'twitchpress' ),
                    'desc'            => __( 'Add the channel that the developer application has been created in.', 'twitchpress' ),
                    'id'              => 'twitchpress_main_channel',
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
        } else {
            $settings = apply_filters( 'twitchpress_permissions_scope_settings', array(
 
                array(
                    'title' => __( 'Global Scope', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => 'The Twitch API offers us the ability to set the scope of access that allow a service to have. This works on an individual basis when authorization a service to access our channel. The options below allow you to reduce the possible scope offered to your users. This allows you to prevent users giving your site permissions that your service will never require. This is just a small security step. If in doubt please leave all boxes checked and do not be concerned.',
                    'id'     => 'global_scope_options',
                ),

                array(
                    'title'           => __( 'Select Acceptable Scopes', 'twitchpress' ),
                    'desc'            => __( 'user_read: Read access to non-public user information, such as email address.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_user_read',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'start',
                    'show_if_checked' => 'option',
                ),

                array(
                    'desc'            => __( 'user_blocks_edit: Ability to ignore or unignore on behalf of a user.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_user_blocks_edit',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'user_blocks_read: Read access to a user\'s list of ignored users.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_user_blocks_read',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'user_follows_edit: Access to manage a user\'s followed channels.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_user_follows_edit',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'channel_read: Read access to non-public channel information, including email address and stream key.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_channel_read',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'channel_editor: Write access to channel metadata (game, status, etc).', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_channel_editor',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'channel_commercial: Access to trigger commercials on channel.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_channel_commercial',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'channel_stream: Ability to reset a channel\'s stream key.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_channel_stream',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'channel_subscriptions: Read access to all subscribers to your channel.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_channel_subscriptions',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'user_subscriptions: Read access to subscriptions of a user.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_user_subscriptions',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'channel_check_subscription: Read access to check if a user is subscribed to your channel.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_channel_check_subscription',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'chat_login: Ability to log into chat and send messages.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_chat_login',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
              
                array(
                    'desc'            => __( 'channel_feed_read: Ability to view to a channel feed.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_channel_feed_read',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'channel_feed_edit: Ability to add posts and reactions to a channel feed."', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_channel_feed_edit',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'communities_edit: Manage a user’s communities."', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_communities_edit',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'communities_moderate: Manage community moderators."', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_communities_moderate',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'collections_edit: Manage a user’s collections (of videos)."', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_collections_edit',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'channel_feed_edit: Turn on Viewer Heartbeat Service ability to record user data."', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_viewing_activity_read',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'end',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
 
                array(
                    'type'     => 'sectionend',
                    'id'     => 'global_scope_options'
                ),

            ));
        }

        return apply_filters( 'twitchpress_get_settings_' . $this->id, $settings, $current_section );
    }
}

endif;

return new TwitchPress_Settings_Permissions();