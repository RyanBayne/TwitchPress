<?php
/**
 * TwitchPress Feeds Settings
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'TwitchPress_Settings_Feeds' ) ) :

/**
 * TwitchPress_Settings_Example.
 */
class TwitchPress_Settings_Feeds extends TwitchPress_Settings_Page {

    /**
     * Constructor.
     */
    public function __construct() {

        $this->id    = 'feeds';
        $this->label = __( 'Channel Feeds', 'twitchpress' );

        add_filter( 'twitchpress_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
        add_action( 'twitchpress_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'twitchpress_settings_save_' . $this->id, array( $this, 'save' ) );
    }

    /**
     * Get settings array.
     *
     * @return array
     */
    public function get_settings() {

        $settings = apply_filters( 'twitchpress_feeds_settings', array(

            array( 'title' => __( 'Default Feed Sync Options', 'twitchpress' ), 'type' => 'title', 'desc' => 'Control feed sync activity for your entire site. These sync settings are not forced, they are defaults. If you provide a public service, you can allow users to setup their own sync and ignore defaults.', 'id' => 'defaultfeedsyncoptions' ),

            array(
                'title'   => __( 'New Twitch Posts', 'twitchpress' ),
                'desc'    => __( 'Get posts from Twitch channels and create new WP posts with the content.', 'twitchpress' ),
                'id'      => 'twitchpress_new_channeltowp',
                'default' => 'no',
                'type'    => 'checkbox'
            ),

            array(
                'title'   => __( 'New WordPress Posts', 'twitchpress' ),
                'desc'    => __( 'Share new WP posts on Twitch channel feed.', 'twitchpress' ),
                'id'      => 'twitchpress_new_wptochannel',
                'default' => 'no',
                'type'    => 'checkbox'
            ),
            
            array(
                'title'   => __( 'Update WP Posts', 'twitchpress' ),
                'desc'    => __( 'Detect changes to Twitch posts and update their matching WP post.', 'twitchpress' ),
                'id'      => 'twitchpress_update_channeltowp',
                'default' => 'no',
                'type'    => 'checkbox'
            ),
            
            array(
                'title'   => __( 'Update Twitch Posts', 'twitchpress' ),
                'desc'    => __( 'Update a channels posts when their matching WP post changes.', 'twitchpress' ),
                'id'      => 'twitchpress_update_wptochannel',
                'default' => 'no',
                'type'    => 'checkbox'
            ),
    
            array(
                'title'   => __( 'Apply Prepend Value', 'twitchpress' ),
                'desc'    => __( 'Use to switch on or off the prepend value below.', 'twitchpress' ),
                'id'      => 'twitchpress_apply_prepend_value_all_posts',
                'default' => 'no',
                'type'    => 'checkbox'
            ),
                
            array(
                'title'    => __( 'Prepend Value', 'twitchpress' ),
                'desc'     => 'Add a value to the beginning of every outgoing post i.e. let Twitch users know that the post was originally published on this website.',
                'id'       => 'twitchpress_prepend_value_all_posts',
                'default'  => __( '', 'twitchpress' ),
                'type'     => 'textarea',
                'css'     => 'width:350px; height: 65px;',
                'autoload' => false
            ),    
            
            array(
                'title'   => __( 'Apply Appending Value', 'twitchpress' ),
                'desc'    => __( 'Use to switch on or off the appending value below.', 'twitchpress' ),
                'id'      => 'twitchpress_apply_appending_value_all_posts',
                'default' => 'no',
                'type'    => 'checkbox'
            ),
                
            array(
                'title'    => __( 'Appending Value', 'twitchpress' ),
                'desc'     => 'Add a value to the end of every outgoing post i.e. add a link for users to continue reading post on this website.',
                'id'       => 'twitchpress_appending_value_all_posts',
                'default'  => __( '', 'twitchpress' ),
                'type'     => 'textarea',
                'css'     => 'width:350px; height: 65px;',
                'autoload' => false
            ),

            array( 'type' => 'sectionend', 'id' => 'defaultfeedsyncoptions'),

            array( 'title' => __( 'Feed Sync Limits', 'twitchpress' ), 'type' => 'title', 'desc' => __( 'These are globally enforced settings that limit feed sync activity. They can be used to prevent flooding and to reduce specific activity during live testing.', 'twitchpress' ), 'id' => 'feedsynclimits' ),

            array(
                'title'   => __( 'Apply Feed Sync Limits', 'twitchpress' ),
                'desc'    => __( 'You can switch all limits on or off with this one checkbox.', 'twitchpress' ),
                'id'      => 'twitchpress_apply_feed_sync_limits',
                'default' => 'no',
                'type'    => 'checkbox'
            ),

            array(
                'title'    => __( 'Sites Hourly Jobs Limit', 'twitchpress' ),
                'desc'     => __( 'This is the hourly limit for all feed sync jobs performed by Twitch.', 'twitchpress' ),
                'id'       => 'twitchpress_feed_sync_limit_hourly',
                'css'      => 'width:60px;',
                'default'  => '2',
                'desc_tip' =>  true,
                'type'     => 'number',
                'custom_attributes' => array(
                    'min'  => 0,
                    'step' => 10
                )
            ),

            array(
                'title'    => __( 'Channel Daily Jobs Limit', 'twitchpress' ),
                'desc'     => __( 'Limit how many posts a single channel can create on this site.', 'twitchpress' ),
                'id'       => 'twitchpress_feed_sync_channel_limit_daily',
                'css'      => 'width:60px;',
                'default'  => '2',
                'desc_tip' =>  true,
                'type'     => 'number',
                'custom_attributes' => array(
                    'min'  => 0,
                    'step' => 1
                )
            ),
            
            array( 'type' => 'sectionend', 'id' => 'feedsynclimits' )

        ) );

        return apply_filters( 'twitchpress_get_settings_' . $this->id, $settings );
    }

    /**
     * Output a colour picker input box.
     *
     * @param mixed $name
     * @param string $id
     * @param mixed $value
     * @param string $desc (default: '')
     */
    public function color_picker( $name, $id, $value, $desc = '' ) {
        echo '<div class="color_box">' . twitchpress_help_tip( $desc ) . '
            <input name="' . esc_attr( $id ). '" id="' . esc_attr( $id ) . '" type="text" value="' . esc_attr( $value ) . '" class="colorpick" /> <div id="colorPickerDiv_' . esc_attr( $id ) . '" class="colorpickdiv"></div>
        </div>';
    }

    /**
     * Save settings.
     */
    public function save() {
        $settings = $this->get_settings();

        TwitchPress_Admin_Settings::save_fields( $settings );
    }

}

endif;

return new TwitchPress_Settings_Feeds();