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
            'default'               => __( 'Permissions Scope', 'twitchpress' ),
            'entermaincredentials'  => __( 'Enter Main Credentials', 'twitchpress' ),
            'general'               => __( 'General Options', 'twitchpress' ),
            'syncvalues'            => __( 'Sync Values', 'twitchpress' ),
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
    }

    /**
     * Get settings array.
     *
     * @return array
     * 
     * @version 2.0
     */
    public function get_settings( $current_section = '' ) {
        $settings = array();
                           
        if ( 'general' == $current_section ) {
            
            $settings = apply_filters( 'twitchpress_generalapi_settings', array(
            
                array(
                    'title' => __( 'Twitch API Options', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'Mostly miscellanous options for the Twitch API.', 'twitchpress' ),
                    'id'     => 'twitchapigeneraloptions'
                ),

                array(
                    'title'   => __( 'Twitch API Version', 'twitchpress' ),
                    'desc'    => __( 'Switch with care and fully test used features.', 'twitchpress' ),
                    'id'      => 'twitchpress_apiversion',
                    'default' => '6',
                    'type'    => 'radio',
                    'options' => array(
                        '5' => __( 'Kraken (v5)', 'twitchpress' ),
                        '6' => __( 'Helix (v6)', 'twitchpress' ),
                    ),
                    'autoload'        => true,
                    'show_if_checked' => 'option',
                ),
                                     
                array(
                    'type'     => 'sectionend',
                    'id'     => 'twitchapigeneraloptions'
                )

            ));
                
        } elseif ( 'entermaincredentials' == $current_section ) {

            $settings = apply_filters( 'twitchpress_entermaincredentials_settings', array(
            
                array(
                    'title' => __( 'Enter Main Twitch API Application', 'twitchpress' ),
                    'type'  => 'title',
                    'desc'  => __( 'This is the form for entering your main developer application. When you submit the form for the first time, you will go through the oAuth2 procedure. If a code already exists and it is still valid, the procedure will be shorter. When you arrive back on this screen, the token field should be populated and you should be able to make calls to Kraken.', 'twitchpress' ),
                    'id'    => 'mainapplicationcredentials'
                ),

                array(
                    'title'           => __( 'Main Channel', 'twitchpress' ),
                    'desc'            => __( 'Add the channel that the developer application has been created in.', 'twitchpress' ),
                    'id'              => 'twitchpress_main_channels_name',
                    'default'         => '',
                    'type'            => 'text',
                ),

                array(
                    'title'             => __( 'Main Channel ID', 'twitchpress' ),
                    'desc'              => __( 'Main channel ID is currently only set by TwitchPress to confirm oAuth2 credentials are correct.', 'twitchpress' ),
                    'id'                => 'twitchpress_main_channels_id',
                    'default'           => '',
                    'type'              => 'text',
                    'custom_attributes' => array( 'disabled' => 'disabled' ),
                ),
                
                array(
                    'title'           => __( 'Redirect URL', 'twitchpress' ),
                    'desc'            => __( 'Redirect URL', 'twitchpress' ),
                    'id'              => 'twitchpress_app_redirect',
                    'default'         => '',
                    'autoload'        => false,
                    'type'            => 'text',
                ),

                array(
                    'title'           => __( 'Client/App ID', 'twitchpress' ),
                    'desc'            => __( 'Your applications public ID.', 'twitchpress' ),
                    'id'              => 'twitchpress_app_id',
                    'default'         => '',
                    'type'            => 'text',
                    'autoload'        => false,
                ),

                array(
                    'title'           => __( 'Client/App Secret', 'twitchpress' ),
                    'desc'            => __( 'Keep this value hidden at all times.', 'twitchpress' ),
                    'id'              => 'twitchpress_app_secret',
                    'default'         => '',
                    'type'            => 'password',
                    'autoload'        => false,
                ),

                array(
                    'title'           => __( 'Code', 'twitchpress' ),
                    'desc'            => __( 'Created by Twitch.tv only.', 'twitchpress' ),
                    'id'              => 'twitchpress_main_channels_code',
                    'default'         => '',
                    'type'            => 'password',
                    'autoload'        => false,
                    'custom_attributes' => array( 'disabled' => 'disabled' ),
                ),

                array(
                    'title'           => __( 'Token', 'twitchpress' ),
                    'desc'            => __( 'Created by Twitch.tv only.', 'twitchpress' ),
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
                    'desc'     => __( 'A scope with <span class="dashicons dashicons-yes"></span> indicates that it is required and <span class="dashicons dashicons-no"></span> suggests it is not. Scopes are a type of permission. You set the scope of required permissions for a visitor to fully use your service. The visitor will see the list of scopes when they are sent to Twitch.tv (through oAuth2) to give your site permissions. Please learn and understand all scopes. You should only select the ones your service requires.', 'twitchpress' ),
                    'id'     => 'global_scope_options',
                ),

                array(
                    'title'           => __( 'Select Acceptable Scopes', 'twitchpress' ),
                    'desc'            => __( 'user_read: Read access to non-public user information, such as email address.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_user_read',
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => 'start',
                    'show_if_checked' => 'option',
                    'scope'           => 'user_read',
                ),

                array(
                    'desc'            => __( 'user_blocks_edit: Ability to ignore or unignore on behalf of a user.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_user_blocks_edit',
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'user_blocks_edit',
                ),

                array(
                    'desc'            => __( 'user_blocks_read: Read access to a user\'s list of ignored users.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_user_blocks_read',
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'user_blocks_read',
                ),

                array(
                    'desc'            => __( 'user_follows_edit: Access to manage a user\'s followed channels.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_user_follows_edit',
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'user_follows_edit',
                ),

                array(
                    'desc'            => __( 'channel_read: Read access to non-public channel information, including email address and stream key.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_channel_read',
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'channel_read',
                ),

                array(
                    'desc'            => __( 'channel_editor: Write access to channel metadata (game, status, etc).', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_channel_editor',
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'channel_editor',
                ),

                array(
                    'desc'            => __( 'channel_commercial: Access to trigger commercials on channel.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_channel_commercial',
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'channel_commercial',
                ),

                array(
                    'desc'            => __( 'channel_stream: Ability to reset a channel\'s stream key.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_channel_stream',
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'channel_stream',
                ),
                
                array(
                    'desc'            => __( 'channel_subscriptions: Read access to all subscribers to your channel.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_channel_subscriptions',
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'channel_subscriptions',
                ),
                
                array(
                    'desc'            => __( 'user_subscriptions: Read access to subscriptions of a user.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_user_subscriptions',
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'user_subscriptions',
                ),
                
                array(
                    'desc'            => __( 'channel_check_subscription: Read access to check if a user is subscribed to your channel.', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_channel_check_subscription',
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'channel_check_subscription',
                ),
              
                array(
                    'desc'            => __( 'communities_edit: Manage a user’s communities."', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_communities_edit',
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'communities_edit',
                ),
                
                array(
                    'desc'            => __( 'communities_moderate: Manage community moderators."', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_communities_moderate',
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'communities_moderate',
                ),
                
                array(
                    'desc'            => __( 'collections_edit: Manage a user’s collections (of videos)."', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_collections_edit',
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'collections_edit',
                ),

                array(
                    'desc'            => __( 'analytics:read:extensions: View analytics data for your extensions."', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_analytics_read_extensions',
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'analytics_read_extensions',
                ),
                
                array(
                    'desc'            => __( 'analytics:read:games: View analytics data for your games."', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_analytics_read_games',
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'analytics_read_games',
                ),
                
                array(
                    'desc'            => __( 'bits:read: View Bits information for your channel."', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_bits_read',
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'bits_read',
                ),
                
                array(
                    'desc'            => __( 'clips:edit: Manage a clip object."', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_clips_edit',
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'clips_edit',
                ),
                
                array(
                    'desc'            => __( 'user:edit: Manage a user object."', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_user_edit',
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'user_edit',
                ),
                
                array(
                    'desc'            => __( 'user:edit:broadcast: Edit your channel’s broadcast configuration, including extension configuration."', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_user_edit_broadcast',
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'user_edit_broadcast',
                ),

                array(
                    'desc'            => __( 'user:read:broadcast: View your broadcasting configuration, including extension configurations."', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_user_read_broadcast',
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'user_read_broadcast',
                ),
     
                array(
                    'desc'            => __( 'user:read:email: Read authorized user’s email address."', 'twitchpress' ),
                    'id'              => 'twitchpress_scope_user_read_email',
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'user_read_email',
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
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => 'start',
                    'show_if_checked' => 'option',
                    'scope'           => 'user_read',
                ),

                array(
                    'desc'            => __( 'user_blocks_edit: Ability to ignore or unignore on behalf of a user.', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_user_blocks_edit',
                    'default'         => $default,
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'user_blocks_edit',
                ),

                array(
                    'desc'            => __( 'user_blocks_read: Read access to a user\'s list of ignored users.', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_user_blocks_read',
                    'default'         => $default,
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'user_blocks_read',
                ),

                array(
                    'desc'            => __( 'user_follows_edit: Access to manage a user\'s followed channels.', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_user_follows_edit',
                    'default'         => $default,
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'user_follows_edit',
                ),

                array(
                    'desc'            => __( 'channel_read: Read access to non-public channel information, including email address and stream key.', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_channel_read',
                    'default'         => $default,
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'channel_read',
                ),

                array(
                    'desc'            => __( 'channel_editor: Write access to channel metadata (game, status, etc).', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_channel_editor',
                    'default'         => $default,
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'channel_editor',
                ),

                array(
                    'desc'            => __( 'channel_commercial: Access to trigger commercials on channel.', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_channel_commercial',
                    'default'         => $default,
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'channel_commercial',
                ),

                array(
                    'desc'            => __( 'channel_stream: Ability to reset a channel\'s stream key.', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_channel_stream',
                    'default'         => $default,
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'channel_stream',
                ),
                
                array(
                    'desc'            => __( 'channel_subscriptions: Read access to all subscribers to your channel.', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_channel_subscriptions',
                    'default'         => $default,
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'channel_subscriptions',
                ),
                
                array(
                    'desc'            => __( 'user_subscriptions: Read access to subscriptions of a user.', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_user_subscriptions',
                    'default'         => $default,
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'user_subscriptions',
                ),
                
                array(
                    'desc'            => __( 'channel_check_subscription: Read access to check if a user is subscribed to your channel.', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_channel_check_subscription',
                    'default'         => $default,
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'channel_check_subscription',
                ),
                              
                array(
                    'desc'            => __( 'communities_edit: Manage a user’s communities."', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_communities_edit',
                    'default'         => $default,
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'communities_edit',
                ),
                
                array(
                    'desc'            => __( 'communities_moderate: Manage community moderators."', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_communities_moderate',
                    'default'         => $default,
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'communities_moderate',
                ),
                
                array(
                    'desc'            => __( 'collections_edit: Manage a user’s collections (of videos)."', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_collections_edit',
                    'default'         => $default,
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'collections_edit',
                ),
 
                array(
                    'desc'            => __( 'analytics:read:extensions: View analytics data for your extensions."', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_analytics_read_extensions',
                    'default'         => $default,
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'analytics_read_extensions',
                ),
                
                array(
                    'desc'            => __( 'analytics:read:games: View analytics data for your games."', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_analytics_read_games',
                    'default'         => $default,
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'analytics_read_games',
                ),
                
                array(
                    'desc'            => __( 'bits:read: View Bits information for your channel."', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_bits_read',
                    'default'         => $default,
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'bits_read',
                ),
                
                array(
                    'desc'            => __( 'clips:edit: Manage a clip object."', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_clips_edit',
                    'default'         => $default,
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'clips_edit',
                ),
                
                array(
                    'desc'            => __( 'user:edit: Manage a user object."', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_user_edit',
                    'default'         => $default,
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'user_edit',
                ),
                
                array(
                    'desc'            => __( 'user:edit:broadcast: Edit your channel’s broadcast configuration, including extension configuration."', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_user_edit_broadcast',
                    'default'         => $default,
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'user_edit_broadcast',
                ),

                array(
                    'desc'            => __( 'user:read:broadcast: View your broadcasting configuration, including extension configurations."', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_user_read_broadcast',
                    'default'         => $default,
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'user_read_broadcast',
                ),
     
                array(
                    'desc'            => __( 'user:read:email: Read authorized user’s email address."', 'twitchpress' ),
                    'id'              => 'twitchpress_visitor_scope_user_read_email',
                    'default'         => $default,
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => 'user_read_email',
                ),

                array(
                    'type'     => 'sectionend',
                    'id'     => 'visitor_scope_options'
                ),

            ));
        } elseif ( 'syncvalues' == $current_section ) {
            $settings = apply_filters( 'twitchpress_syncvalues_kraken_settings', array(
 
                array(
                    'title' => __( 'Activate Syncronizing: Grouped Data', 'twitchpress-sync' ),
                    'type'  => 'title',
                    'desc'  => __( 'Select the data groups and purposes your site will need to operate the services you plan to offer. A group will store and update more than one value.', 'twitchpress-sync' ),
                    'id'    => 'syncgroupedvaluesettings',
                ),

                array(
                    'desc'            => __( 'Subscribers - import all of your channels subscribers and use the data to improve subscriber experience. This will create a post for each subscriber which can be used by admin only or displayed to the public as part of your services.', 'twitchpress' ),
                    'id'              => 'twitchpress_sync_switch_channel_subscribers',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'start',
                    'show_if_checked' => 'option',
                ),
                
                /*
                array( 
                    'desc'            => __( 'Import all subscribers for building a subscriber aware website."', 'twitchpress' ),
                    'id'              => 'twitchpress_sync_switch_channel_subscribers',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'partnered: used by services that monitor a visitors partner status."', 'twitchpress' ),
                    'id'              => 'twitchpress_sync_user_partnered',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'end',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                */
                                                  
                array(
                    'type'     => 'sectionend',
                    'id'     => 'syncgroupedvaluesettings'
                ),                    
                
                array(
                    'title' => __( 'Activate Syncronizing: Individual Values', 'twitchpress-sync' ),
                    'type'  => 'title',
                    'desc'  => __( 'The Twitch API returns groups of data for many calls, that cannot be avoided. What can be avoided is storing all the data Twitch returns. If you need a single value from each channel/user to operate a feature and want to avoid storing large amounts of Twitch data that has no used to you. Use the options below to configure TwitchPress to extract the values you need and ignore the rest when making requests to the Twitch API.', 'twitchpress-sync' ),
                    'id'    => 'syncvaluesettings',
                ),

                array(
                    'desc'            => __( 'name: the Twitch username can change.', 'twitchpress' ),
                    'id'              => 'twitchpress_sync_user_name',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'start',
                    'show_if_checked' => 'option',
                ),
                
                array(
                    'desc'            => __( 'sub_plan: keep the Twitch subscription plan updated and control WordPress membership levels.', 'twitchpress' ),
                    'id'              => 'twitchpress_sync_user_sub_plan',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'partnered: used by services that monitor a visitors partner status."', 'twitchpress' ),
                    'id'              => 'twitchpress_sync_user_partnered',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'end',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                                                  
                array(
                    'type'     => 'sectionend',
                    'id'     => 'syncvaluesettings'
                ),

            ));   
        }        

        return apply_filters( 'twitchpress_get_settings_' . $this->id, $settings, $current_section );
    }
}

endif;

return new TwitchPress_Settings_Kraken();