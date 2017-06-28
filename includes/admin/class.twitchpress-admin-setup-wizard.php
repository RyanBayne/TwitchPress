<?php
/**
 * Setup Wizard which completes installation of plugin. 
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Admin
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Admin_Setup_Wizard' ) ) :

/**
 * TwitchPress_Admin_Setup_Wizard Class 
 * 
 * Class originally created by ** Automattic ** and is the best approach to plugin
 * installation found if an author wants to treat the user and their site with
 * respect.
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Admin
 * @version     1.0.0
*/
class TwitchPress_Admin_Setup_Wizard {

    /** @var string Current Step */
    private $step   = '';

    /** @var array Steps for the setup wizard */
    private $steps  = array();

    /** @var boolean Is the wizard optional or required? */
    private $optional = false;

    /**
     * Hook in tabs.
     */
    public function __construct() {
        if ( apply_filters( 'twitchpress_enable_setup_wizard', true ) && current_user_can( 'manage_twitchpress' ) ) {
            add_action( 'admin_menu', array( $this, 'admin_menus' ) );
            add_action( 'admin_init', array( $this, 'setup_wizard' ) );
        } 
    }

    /**
     * Add admin menus/screens.
     */
    public function admin_menus() {
        add_dashboard_page( '', '', 'manage_options', 'twitchpress-setup', '' );
    }

    /**
     * Show the setup wizard.
     */
    public function setup_wizard() {
        if ( empty( $_GET['page'] ) || 'twitchpress-setup' !== $_GET['page'] ) {
            return;
        }
        $this->steps = array(
            'introduction' => array(
                'name'    =>  __( 'Introduction', 'twitchpress' ),
                'view'    => array( $this, 'twitchpress_setup_introduction' ),
                'handler' => ''
            ),
            'application' => array(
                'name'    =>  __( 'Application', 'twitchpress' ),
                'view'    => array( $this, 'twitchpress_setup_application' ),
                'handler' => array( $this, 'twitchpress_setup_application_save' )
            ),
            'folders' => array(
                'name'    =>  __( 'Files', 'twitchpress' ),
                'view'    => array( $this, 'twitchpress_setup_folders' ),
                'handler' => array( $this, 'twitchpress_setup_folders_save' )
            ),
            'database' => array(
                'name'    =>  __( 'Database', 'twitchpress' ),
                'view'    => array( $this, 'twitchpress_setup_database' ),
                'handler' => array( $this, 'twitchpress_setup_database_save' ),
            ), 
            'extensions' => array(
                'name'    =>  __( 'Extensions', 'twitchpress' ),
                'view'    => array( $this, 'twitchpress_setup_extensions' ),
                'handler' => array( $this, 'twitchpress_setup_extensions_save' ),
            ),                       
            'improvement' => array(
                'name'    =>  __( 'Feedback', 'twitchpress' ),
                'view'    => array( $this, 'twitchpress_setup_improvement' ),
                'handler' => array( $this, 'twitchpress_setup_improvement_save' ),
            ),
            'next_steps' => array(
                'name'    =>  __( 'Ready!', 'twitchpress' ),
                'view'    => array( $this, 'twitchpress_setup_ready' ),
                'handler' => ''
            )
        );
        $this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );
        $suffix     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        // Register scripts for the pretty extension presentation and selection.
        wp_register_script( 'jquery-blockui', TwitchPress()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
        wp_register_script( 'select2', TwitchPress()->plugin_url() . '/assets/js/select2/select2' . $suffix . '.js', array( 'jquery' ), '3.5.2' );
        wp_register_script( 'twitchpress-enhanced-select', TwitchPress()->plugin_url() . '/assets/js/admin/twitchpress-enhanced-select' . $suffix . '.js', array( 'jquery', 'select2' ), TWITCHPRESS_VERSION );
        
        // Queue CSS for the entire setup process.
        wp_enqueue_style( 'twitchpress_admin_styles', TwitchPress()->plugin_url() . '/assets/css/admin.css', array(), TWITCHPRESS_VERSION );
        wp_enqueue_style( 'twitchpress-setup', TwitchPress()->plugin_url() . '/assets/css/twitchpress-setup.css', array( 'dashicons', 'install' ), TWITCHPRESS_VERSION );
        wp_register_script( 'twitchpress-setup', TwitchPress()->plugin_url() . '/assets/js/admin/twitchpress-setup.min.js', array( 'jquery', 'twitchpress-enhanced-select', 'jquery-blockui' ), TWITCHPRESS_VERSION );

        if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
            call_user_func( $this->steps[ $this->step ]['handler'] );
        }
    
        ob_start();
        $this->setup_wizard_header();
        $this->setup_wizard_steps();
        $this->setup_wizard_content();
        $this->setup_wizard_footer();
        exit;
    }

    public function get_next_step_link() {
        $keys = array_keys( $this->steps );
        return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ) ) + 1 ] );
    }

    /**
     * Setup Wizard Header.
     */
    public function setup_wizard_header() {        
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta name="viewport" content="width=device-width" />
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title><?php _e( 'WordPress TwitchPress &rsaquo; Setup Wizard', 'twitchpress' ); ?></title>
            <?php wp_print_scripts( 'twitchpress-setup' ); ?>
            <?php do_action( 'admin_print_styles' ); ?>
            <?php do_action( 'admin_head' ); ?>
        </head>
        <body class="twitchpress-setup wp-core-ui">
            <h1 id="twitchpress-logo"><a href="<?php echo TWITCHPRESS_HOME;?>"><img src="<?php echo TwitchPress()->plugin_url(); ?>/assets/images/twitchpress_logo.png" alt="TwitchPress" /></a></h1>
        <?php
    }

    /**
     * Setup Wizard Footer.
     */
    public function setup_wizard_footer() {
        ?>
            <?php if ( 'next_steps' === $this->step ) : ?>
                <a class="twitchpress-return-to-dashboard" href="<?php echo esc_url( admin_url() ); ?>"><?php _e( 'Return to the WordPress Dashboard', 'twitchpress' ); ?></a>
            <?php endif; ?>
            </body>
        </html>
        <?php
    }

    /**
     * Output the steps.
     */
    public function setup_wizard_steps() {      
        $ouput_steps = $this->steps;
        array_shift( $ouput_steps );
        ?>
        <ol class="twitchpress-setup-steps">
            <?php foreach ( $ouput_steps as $step_key => $step ) : ?>
                <li class="<?php
                    if ( $step_key === $this->step ) {
                        echo 'active';
                    } elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
                        echo 'done';
                    }
                ?>"><?php echo esc_html( $step['name'] ); ?></li>
            <?php endforeach; ?>
        </ol>
        <?php
    }

    /**
     * Output the content for the current step.
     */
    public function setup_wizard_content() {           
        echo '<div class="twitchpress-setup-content">'; 
        
        if( !isset( $this->steps[ $this->step ]['view'] ) ) {
            ?><h1><?php _e( 'Invalid Step!', 'twitchpress' ); ?></h1><p><?php _e( 'You have attempted to visit a setup step that does not exist. I would like to know how this happened so that I can improve the plugin. Please tell me what you did before this message appeared. If you were just messing around, then stop it you naughty hacker!', 'twitchpress' ); ?></p><?php 
        } elseif( !method_exists( $this, $this->steps[ $this->step ]['view'][1] ) ) {
            ?><h1><?php _e( 'Something Has Gone Very Wrong!', 'twitchpress' ); ?></h1><p><?php _e( 'You have attempted to visit a step in the setup process that may not be ready yet! This should not have happened. Please report it to me.', 'twitchpress' ); ?></p><?php             
        } else {
            TwitchPress_Admin_Notices::output_custom_notices();
            call_user_func( $this->steps[ $this->step ]['view'] );
        }
        
        echo '</div>';
    }

    /**
     * Introduction step.
     */
    public function twitchpress_setup_introduction() { ?>
        <h1><?php _e( 'Setup TwitchPress', 'twitchpress' ); ?></h1>
        
        <?php if( $this->optional ) { ?>
        
        <p><?php _e( 'Thank you for choosing TwitchPress! The setup wizard will walk you through some essential settings and explain the changes being made to your blog. <strong>It’s completely optional and shouldn’t take longer than five minutes.</strong>', 'twitchpress' ); ?></p>
        <p><?php _e( 'No time right now? If you don’t want to go through the wizard, you can skip and return to the WordPress dashboard. You will be able to use the plugin but you might miss some features!', 'twitchpress' ); ?></p>
        <p class="twitchpress-setup-actions step">
            <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button-primary button button-large button-next"><?php _e( 'Let\'s Go!', 'twitchpress' ); ?></a>
            <a href="<?php echo esc_url( admin_url() ); ?>" class="button button-large"><?php _e( 'Not right now', 'twitchpress' ); ?></a>
        </p>
        
        <?php } else { ?> 
            
        <p><?php _e( 'Thank you for choosing TwitchPress! The setup wizard will walk you through some essential settings and explain the changes being made to your blog. <strong>It is required before you can use the plugin but it shouldn’t take longer than five minutes.</strong> You will be asked to enter your Twitch Application credentials. If you do not have time to do this right now. Please click on the "Not Right Now" button below.', 'twitchpress' ); ?></p>
                
        <h1><?php _e( 'Twitch Developer Services Agreement', 'twitchpress' ); ?></h1>

        <p><?php _e( 'By continuing to use the TwitchPress plugin you are agreeing to comply with the Twitch Developer Services Agreement and the GNU General Public License (version 3). You agree to be bound by them in the development of your WordPress site without any exceptions. If you do not agree to either licenses or understand them fully then please do not continue and seek advice.', 'twitchpress' ); ?></p>
        
        <form method="post">
            <p class="twitchpress-setup-actions step">
                <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button-primary button button-large button-next"><?php _e( 'Accept Agreement', 'twitchpress' ); ?></a>
                <a href="https://www.twitch.tv/p/developer-agreement" class="button button-large" target="_blank"><?php _e( 'Read Agreement', 'twitchpress' ); ?></a>
                <a href="<?php echo esc_url( admin_url() ); ?>" class="button button-large"><?php _e( 'Not right now', 'twitchpress' ); ?></a>
                <?php wp_nonce_field( 'twitchpress-setup' ); ?>
            </p>
        </form>        
                    
        <?php }
    }

    /**
     * Step requesting the user to accept the Twitch Developer Services Agreement
     * or avoid using the plugin.
     */
    public function twitchpress_setup_application() {?>
        <h1><?php _e( 'Enter Twitch Application Credentials', 'twitchpress' ); ?></h1>
        
        <p><?php _e( 'Although you\'re using a plugin I created. All responsibilities are yours. You will need to create a new Developer Application within your own Twitch account and enter it\'s credentials below. All use of the Twitch API by TwitchPress will be done through your own account. If you are acting on behalf of a business or team please take care when deciding which Twitch account to log into. You should use the Twitch account considered to be the official or main user.', 'twitchpress' ); ?></p>

        <h3><?php _e( 'The gray buttons at the bottom of the form will open a new tab. Please explore them to learn more and understand why this step is required.', 'twitchpress' ); ?></h3>
                                    
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="twitchpress_main_channel_name"><?php _e( 'Channel Name', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="text" id="twitchpress_main_channel_name" name="twitchpress_main_channel_name" class="input-text" value="<?php echo get_option( 'twitchpress_main_channel_name' );?>" />
                        <label for="twitchpress_main_channel_name"><?php _e( 'example: ZypheREvolved, StarCitizen, TESTSquadron', 'twitchpress' ); ?></label>
                    </td>
                </tr>               
                <tr>
                    <th scope="row"><label for="twitchpress_main_redirect_uri"><?php _e( 'Redirect URI', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="text" id="twitchpress_main_redirect_uri" name="twitchpress_main_redirect_uri" class="input-text" value="<?php echo get_option( 'twitchpress_main_redirect_uri' );?>" />
                        <label for="twitchpress_main_redirect_uri"><?php _e( 'example: http://www.yourdomain.pro/twitchservices', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_main_client_id"><?php _e( 'Client ID', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="text" id="twitchpress_main_client_id" name="twitchpress_main_client_id" class="input-text" value="<?php echo get_option( 'twitchpress_main_client_id' );?>" />
                        <label for="twitchpress_main_client_id"><?php _e( 'example: uo6dggojyb8d6soh92zknwmi5ej1q2', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_main_client_secret"><?php _e( 'Client Secret', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="password" id="twitchpress_main_client_secret" name="twitchpress_main_client_secret" class="input-text" value="<?php echo get_option( 'twitchpress_main_client_secret' );?>" />
                        <label for="twitchpress_main_client_secret"><?php _e( 'example: nyo51xcdrerl8z9m56w9w6wg', 'twitchpress' ); ?></label>
                    </td>
                </tr>
            </table>
            
            <h3><?php _e( 'Sitewide Permissions Scope', 'twitchpress' ); ?></h3>
            <p><?php _e( 'The permissions scope offered to you below, offers the opportunity to apply a sitewide restriction to all users. Your selections will determine what TwitchPress is permitted to do for any channel. In normal circumstances your selected scope would only apply to your own channel. In this case your selections will determine what TwitchPress abilities and features can be used. You can change the global scope on the plugins settings pages later.', 'twitchpress' ); ?></p>
             
            <table class="form-table">    
                <tr>
                    <th scope="row"><label for="twitchpress_scope_user_read"><?php _e( 'user_read', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_scope_user_read" name="twitchpress_scopes[]" class="input-checkbox" value="user_read" <?php checked( get_option( 'twitchpress_scope_user_read' ), 'yes', true ); ?> />
                        <label for="twitchpress_scope_user_read"><?php _e( 'Read access to non-public user information, such as email address.', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_scope_user_blocks_edit"><?php _e( 'user_blocks_edit', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_scope_user_blocks_edit" name="twitchpress_scopes[]" class="input-checkbox" value="user_blocks_edit" <?php checked( get_option( 'twitchpress_scope_user_blocks_edit' ), 'yes', true ); ?> />
                        <label for="twitchpress_scope_user_blocks_edit"><?php _e( 'Ability to ignore or unignore on behalf of a user.', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_scope_user_blocks_read"><?php _e( 'user_blocks_read', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_scope_user_blocks_read" name="twitchpress_scopes[]" class="input-checkbox" value="user_blocks_read" <?php checked( get_option( 'twitchpress_scope_user_blocks_read' ), 'yes', true ); ?> />
                        <label for="twitchpress_scope_user_blocks_read"><?php _e( 'Read access to a user\'s list of ignored users.', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_scope_user_follows_edit"><?php _e( 'user_follows_edit', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_scope_user_follows_edit" name="twitchpress_scopes[]" class="input-checkbox" value="user_follows_edit" <?php checked( get_option( 'twitchpress_scope_user_follows_edit' ), 'yes', true ); ?> />
                        <label for="twitchpress_scope_user_follows_edit"><?php _e( 'Access to manage a user\'s followed channels.', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_scope_channel_read"><?php _e( 'channel_read', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_scope_channel_read" name="twitchpress_scopes[]" class="input-checkbox" value="channel_read" <?php checked( get_option( 'twitchpress_scope_channel_read' ), 'yes', true ); ?> />
                        <label for="twitchpress_scope_channel_read"><?php _e( 'Read access to non-public channel information, including email address and stream key.', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_scope_channel_editor"><?php _e( 'channel_editor', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_scope_channel_editor" name="twitchpress_scopes[]" class="input-checkbox" value="channel_editor" <?php checked( get_option( 'twitchpress_scope_channel_editor' ), 'yes', true ); ?> />
                        <label for="twitchpress_scope_channel_editor"><?php _e( 'Write access to channel metadata (game, status, etc).', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_scope_channel_commercial"><?php _e( 'channel_commercial', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_scope_channel_commercial" name="twitchpress_scopes[]" class="input-checkbox" value="channel_commercial" <?php checked( get_option( 'twitchpress_scope_channel_commercial' ), 'yes', true ); ?> />
                        <label for="twitchpress_scope_channel_commercial"><?php _e( 'Access to trigger commercials on channel.', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_scope_channel_stream"><?php _e( 'channel_stream', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_scope_channel_stream" name="twitchpress_scopes[]" class="input-checkbox" value="channel_stream" <?php checked( get_option( 'twitchpress_scope_channel_stream' ), 'yes', true ); ?> />
                        <label for="twitchpress_scope_channel_stream"><?php _e( 'Ability to reset a channel\'s stream key.', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_scope_channel_subscriptions"><?php _e( 'channel_subscriptions', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_scope_channel_subscriptions" name="twitchpress_scopes[]" class="input-checkbox" value="channel_subscriptions" <?php checked( get_option( 'twitchpress_scope_channel_subscriptions' ), 'yes', true ); ?> />
                        <label for="twitchpress_scope_channel_subscriptions"><?php _e( 'Read access to all subscribers to your channel.', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_scope_user_subscriptions"><?php _e( 'user_subscriptions', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_scope_user_subscriptions" name="twitchpress_scopes[]" class="input-checkbox" value="user_subscriptions" <?php checked( get_option( 'twitchpress_scope_user_subscriptions' ), 'yes', true ); ?> />
                        <label for="twitchpress_scope_user_subscriptions"><?php _e( 'Read access to subscriptions of a user.', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_scope_channel_check_subscription"><?php _e( 'channel_check_subscription', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_scope_channel_check_subscription" name="twitchpress_scopes[]" class="input-checkbox" value="channel_check_subscription" <?php checked( get_option( 'twitchpress_scope_channel_check_subscription' ), 'yes', true ); ?> />
                        <label for="twitchpress_scope_channel_check_subscription"><?php _e( 'Read access to check if a user is subscribed to your channel.', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_scope_chat_login"><?php _e( 'chat_login', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_scope_chat_login" name="twitchpress_scopes[]" class="input-checkbox" value="chat_login" <?php checked( get_option( 'twitchpress_scope_chat_login' ), 'yes', true ); ?> />
                        <label for="twitchpress_scope_chat_login"><?php _e( 'Ability to log into chat and send messages.', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_scope_channel_feed_read"><?php _e( 'channel_feed_read', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_scope_channel_feed_read" name="twitchpress_scopes[]" class="input-checkbox" value="channel_feed_read" <?php checked( get_option( 'twitchpress_scope_channel_feed_read' ), 'yes', true ); ?> />
                        <label for="twitchpress_scope_channel_feed_read"><?php _e( 'Ability to view to a channel feed.', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_scope_channel_feed_edit"><?php _e( 'channel_feed_edit', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_scope_channel_feed_edit" name="twitchpress_scopes[]" class="input-checkbox" value="channel_feed_edit" <?php checked( get_option( 'twitchpress_scope_channel_feed_edit' ), 'yes', true ); ?> />
                        <label for="twitchpress_scope_channel_feed_edit"><?php _e( 'Ability to add posts and reactions to a channel feed.', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_scope_communities_edit"><?php _e( 'communities_edit', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_scope_communities_edit" name="twitchpress_scopes[]" class="input-checkbox" value="communities_edit" <?php checked( get_option( 'twitchpress_scope_communities_edit' ), 'yes', true ); ?> />
                        <label for="twitchpress_scope_communities_edit"><?php _e( 'Manage a user’s communities.', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_scope_communities_moderate"><?php _e( 'communities_moderate', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_scope_communities_moderate" name="twitchpress_scopes[]" class="input-checkbox" value="communities_moderate" <?php checked( get_option( 'twitchpress_scope_communities_moderate' ), 'yes', true ); ?> />
                        <label for="twitchpress_scope_communities_moderate"><?php _e( 'Manage community moderators.', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_scope_collections_edit"><?php _e( 'collections_edit', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_scope_collections_edit" name="twitchpress_scopes[]" class="input-checkbox" value="collections_edit" <?php checked( get_option( 'twitchpress_scope_collections_edit' ), 'yes', true ); ?> />
                        <label for="twitchpress_scope_collections_edit"><?php _e( 'Manage a user’s collections (of videos).', 'twitchpress' ); ?></label>
                    </td>
                </tr>     
                <tr>
                    <th scope="row"><label for="twitchpress_scope_viewing_activity_read"><?php _e( 'viewing_activity_read', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_scope_viewing_activity_read" name="twitchpress_scopes[]" class="input-checkbox" value="viewing_activity_read" <?php checked( get_option( 'twitchpress_scope_viewing_activity_read' ), 'yes', true ); ?> />
                        <label for="twitchpress_scope_viewing_activity_read"><?php _e( 'Turn on Viewer Heartbeat Service ability to record user data.', 'twitchpress' ); ?></label>
                    </td>
                </tr>
            </table>        
            <p class="twitchpress-setup-actions step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'twitchpress' ); ?>" name="save_step" />
                <a href="https://www.twitch.tv/kraken/oauth2/clients/new" class="button button-large" target="_blank"><?php _e( 'Create App', 'twitchpress' ); ?></a>                                
                <a href="https://www.twitch.tv/settings/connections" class="button button-large" target="_blank"><?php _e( 'Your Apps', 'twitchpress' ); ?></a>                
                <a href="https://dev.twitch.tv/docs/v5/guides/authentication/" class="button button-large" target="_blank"><?php _e( 'Help', 'twitchpress' ); ?></a>                
                <?php wp_nonce_field( 'twitchpress-setup' ); ?>
            </p>
        </form>
                
        <?php    
    }

    /**
     * Save Page Settings.
     */
    public function twitchpress_setup_application_save() {          
        check_admin_referer( 'twitchpress-setup' );
        
        // Sanitize $_POST values.
        $main_channel = sanitize_text_field( $_POST['twitchpress_main_channel_name'] );
        $redirect_uri = sanitize_text_field( $_POST['twitchpress_main_redirect_uri'] );
        $client_id = sanitize_text_field( $_POST['twitchpress_main_client_id'] );
        $client_secret = sanitize_text_field( $_POST['twitchpress_main_client_secret'] );

        if( empty( $main_channel ) || empty( $redirect_uri ) || empty( $client_id ) || empty( $client_secret ) ) {
            TwitchPress_Admin_Notices::add_custom_notice( 'wizardcredentialsincomplete', sprintf( __( 'You have not completed the Application Credentials part of this step. All four inputs need a value.'), esc_html( $main_channel ) ) );            
            return;
        }
        
        // Kraken requires one or more permissions in the scope. 
        if( !isset( $_POST['twitchpress_scopes'] ) ) {
            TwitchPress_Admin_Notices::add_custom_notice( 'wizardchanneldoesnotexist', sprintf( __( 'Please check the box for one or more permissions. Your permissions scope tells the Twitch API how much access to give. The less boxes you check the more Twitch.tv will restrict.'), esc_html( $main_channel ) ) );
            return;
        }
   
        // Delete options for scopes that are not in $_POST (not checked) and add those that are.
        $pre_credentials_kraken = new TWITCHPRESS_Kraken5_Interface();
        $all_scopes = $pre_credentials_kraken->scopes();
        foreach( $all_scopes as $scope => $scope_info ) {  
            if( in_array( $scope, $_POST['twitchpress_scopes'] ) ) {     
                update_option( 'twitchpress_scope_' . $scope, 'yes' );
            } else {                                       
                delete_option( 'twitchpress_scope_' . $scope );
            }
        }
 
        // Store the credentials.
        update_option( 'twitchpress_main_redirect_uri', $redirect_uri, true );
        update_option( 'twitchpress_main_client_id', $client_id, true );
        update_option( 'twitchpress_main_client_secret', $client_secret, true );
        update_option( 'twitchpress_main_channel_name', $main_channel, true );
                                        
        // Confirm the giving main channel is valid. 
        $kraken_calls_obj = new TWITCHPRESS_Kraken5_Calls();
        $user_objects = $kraken_calls_obj->get_users( $main_channel );
        
        if( !isset( $user_objects['users'][0]['_id'] ) ) {
            TwitchPress_Admin_Notices::add_custom_notice( 'wizardchanneldoesnotexist', __( '<strong>Channel Not Found:</strong> TwitchPress wants to avoid errors in future by ensuring what you typed is correct. So far it could not confirm your entered channel is correct. Please check the spelling of your channel and the status of Twitch. If your entered channel name is correct and Twitch is online, please report this message.', 'twitchpress' ) );      
            return;                         
        } 

        update_option( 'twitchpress_main_channel_id', $user_objects['users'][0]['_id'], true );
                
        TwitchPress_Admin_Notices::add_custom_notice( 'applicationcredentialssaved', __( 'Your application credentials have been stored. TwitchPress will now send you to Twitch.tv to authorize your account.' ) );
        
        // Send user to oAuth2 URL (they will be returned to the next step in the setup process)
        $post_credentials_kraken = new TWITCHPRESS_Kraken5_Interface();
        $state = array( 'redirectto' => '/wp-admin/index.php?page=twitchpress-setup&step=folders' );
        $oAuth2_URL = $post_credentials_kraken->generate_authorization_url_admin( $_POST['twitchpress_scopes'], $state );
        wp_redirect( $oAuth2_URL );
        exit;
    }

    /**
     * Folders and files step.
     */
    public function twitchpress_setup_folders() { 
        $upload_dir = wp_upload_dir();?>
        <h1><?php _e( 'Folders &amp; Files', 'twitchpress' ); ?></h1>
        
        <p><?php _e( 'These are the folders and files that have been created. Please try to avoid removing the folders and files you see in the list above.', 'twitchpress' ); ?></p>
                    
        <form method="post">
            <table class="twitchpress-setup-extensions" cellspacing="0">
                <thead>
                    <tr>
                        <th class="extension-name"><?php _e( 'Type', 'twitchpress' ); ?></th>
                        <th class="extension-description"><?php _e( 'Path', 'twitchpress' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="access-name"><?php _e( 'Folder', 'twitchpress' ); ?></td>
                        <td><?php echo $upload_dir['basedir'] . '/twitchpress_uploads'; ?></td>
                    </tr>
                    <tr>
                        <td class="access-name"><?php _e( 'Folder', 'twitchpress' ); ?></td>
                        <td><?php echo TWITCHPRESS_LOG_DIR; ?></td>
                    </tr>
                    <tr>
                        <td class="access-name"><?php _e( 'File', 'twitchpress' ); ?></td>
                        <td><?php echo TWITCHPRESS_LOG_DIR . '.htaccess'; ?></td>
                    </tr>
                    <tr>
                        <td class="access-name"><?php _e( 'File', 'twitchpress' ); ?></td>
                        <td><?php echo TWITCHPRESS_LOG_DIR . 'index.html'; ?></td>
                    </tr>
                </tbody>
            </table>
            
            <p class="twitchpress-setup-actions step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'twitchpress' ); ?>" name="save_step" />
                <?php wp_nonce_field( 'twitchpress-setup' ); ?>
            </p>
        </form>
        <?php
    }

    /**
     * Create folders and files.
     */
    public function twitchpress_setup_folders_save() {       
        check_admin_referer( 'twitchpress-setup' );
        wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    /**
     * Database changes overview step.
     */
    public function twitchpress_setup_database() {        
        ?>
        <h1><?php _e( 'Database Changes', 'twitchpress' ); ?></h1>
        <form method="post">
                        
            <p><?php _e( 'The plugin will not create or alter any database tables for this installation.', 'twitchpress' ); ?></p>

            <p class="twitchpress-setup-actions step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'twitchpress' ); ?>" name="save_step" />
                <?php wp_nonce_field( 'twitchpress-setup' ); ?>
            </p>
        </form>
        <?php
    }

    /**
     * Save shipping and tax options.
     */
    public function twitchpress_setup_database_save() {           
        check_admin_referer( 'twitchpress-setup' );
        wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    /**
     * Array of official and endorsed extensions.
     * 
     * @return array
     */
    protected function get_wizard_extensions() {       
        $gateways = array(
            'csv-2-post' => array(
                'name'        => __( 'CSV 2 POST', 'twitchpress' ),
                'description' => __( 'Import data for the purpose of mass publishing posts. Another plugin by Ryan Bayne.', 'twitchpress' ),
                'repo-slug'   => 'csv-2-post',
                'source'        => 'remote'
            ),  /*
            'stripe' => array(
                'name'        => __( 'Channel Solution for Twitch', 'twitchpress' ),
                'description' => __( 'A modern and robust wa.', 'twitchpress' ),
                'repo-slug'   => 'channel-solution-for-twitch',
                'source'        => 'remote',
            ),            
            'paypal' => array(
                'name'        => __( 'PayPal Standard', 'twitchpress' ),
                'description' => __( 'Accept payments via PayPal using account balance or credit card.', 'twitchpress' ),
                'settings'    => array(
                    'email' => array(
                        'label'       => __( 'PayPal email address', 'twitchpress' ),
                        'type'        => 'email',
                        'value'       => get_option( 'admin_email' ),
                        'placeholder' => __( 'PayPal email address', 'twitchpress' ),
                    ),
                ),
                'source'        => 'local'
            ),
            'cheque' => array(
                'name'        => _x( 'Check Payments', 'Check payment method', 'twitchpress' ),
                'description' => __( 'A simple offline gateway that lets you accept a check as method of payment.', 'twitchpress' ),
                'source'        => 'local'
            ),
            'bacs' => array(
                'name'        => __( 'Bank Transfer (BACS) Payments', 'twitchpress' ),
                'description' => __( 'A simple offline gateway that lets you accept BACS payment.', 'twitchpress' ),
                'source'        => 'local'
            ) */
        );

        return $gateways;
    }

    /**
     * Extensions selection step.
     * 
     * Both WordPress.org plugins and packaged plugins are offered.
     */
    public function twitchpress_setup_extensions() {
        $gateways = $this->get_wizard_extensions();?>
        
        <h1><?php _e( 'Extensions', 'twitchpress' ); ?></h1>   
        <p><?php _e( 'Normal WordPress plugins safely downloaded from wordpress.org website.', 'twitchpress' ); ?></p>
         
        <form method="post" class="twitchpress-wizard-plugin-extensions-form">
            
            <ul class="twitchpress-wizard-plugin-extensions">
                <?php foreach ( $gateways as $gateway_id => $gateway ) : ?>
                    <li class="twitchpress-wizard-extension twitchpress-wizard-extension-<?php echo esc_attr( $gateway_id ); ?>">
                        <div class="twitchpress-wizard-extension-enable">
                            <input type="checkbox" name="twitchpress-wizard-extension-<?php echo esc_attr( $gateway_id ); ?>-enabled" class="input-checkbox" value="yes" />
                            <label>
                                <?php echo esc_html( $gateway['name'] ); ?>
                            </label>
                        </div>
                        <div class="twitchpress-wizard-extension-description">
                            <?php echo wp_kses_post( wpautop( $gateway['description'] ) ); ?>
                        </div>
                        <?php if ( ! empty( $gateway['settings'] ) ) : ?>
                            <table class="form-table twitchpress-wizard-extension-settings">
                                <?php foreach ( $gateway['settings'] as $setting_id => $setting ) : ?>
                                    <tr>
                                        <th scope="row"><label for="<?php echo esc_attr( $gateway_id ); ?>_<?php echo esc_attr( $setting_id ); ?>"><?php echo esc_html( $setting['label'] ); ?>:</label></th>
                                        <td>
                                            <input
                                                type="<?php echo esc_attr( $setting['type'] ); ?>"
                                                id="<?php echo esc_attr( $gateway_id ); ?>_<?php echo esc_attr( $setting_id ); ?>"
                                                name="<?php echo esc_attr( $gateway_id ); ?>_<?php echo esc_attr( $setting_id ); ?>"
                                                class="input-text"
                                                value="<?php echo esc_attr( $setting['value'] ); ?>"
                                                placeholder="<?php echo esc_attr( $setting['placeholder'] ); ?>"
                                                />
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
           
            <p class="twitchpress-setup-actions step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'twitchpress' ); ?>" name="save_step" />
                <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-large button-next"><?php _e( 'Skip this step', 'twitchpress' ); ?></a>
                <?php wp_nonce_field( 'twitchpress-setup' ); ?>
            </p>
        </form>
        <?php
    }

    /**
     * Extensions installation and activation.
     * 
     * Both mini-extensions (single files stored in wp-content) and plugin-extensions
     * (plugins downloaded from wordpress.org) are handled by this step.
     */
    public function twitchpress_setup_extensions_save() {                  
        check_admin_referer( 'twitchpress-setup' );

        $gateways = $this->get_wizard_extensions();

        foreach ( $gateways as $gateway_id => $gateway ) {
            // If repo-slug is defined, download and install plugin from .org.
            if ( ! empty( $gateway['repo-slug'] ) && ! empty( $_POST[ 'twitchpress-wizard-extension-' . $gateway_id . '-enabled' ] ) ) {
                wp_schedule_single_event( time() + 10, 'twitchpress_plugin_background_installer', array( $gateway_id, $gateway ) );
            }

            $settings_key        = 'twitchpress_' . $gateway_id . '_settings';
            $settings            = array_filter( (array) get_option( $settings_key, array() ) );
            $settings['enabled'] = ! empty( $_POST[ 'twitchpress-wizard-extension-' . $gateway_id . '-enabled' ] ) ? 'yes' : 'no';

            if ( ! empty( $gateway['settings'] ) ) {
                foreach ( $gateway['settings'] as $setting_id => $setting ) {
                    $settings[ $setting_id ] = twitchpress_clean( $_POST[ $gateway_id . '_' . $setting_id ] );
                }
            }

            update_option( $settings_key, $settings );
        }

        wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    /**
     * Improvement program and feedback.
     */
    public function twitchpress_setup_improvement() { ?>
        <h1><?php _e( 'Improvement Program &amp; Feedback', 'twitchpress' ); ?></h1>
        <p><?php _e( 'Taking the time to provide constructive feedback and allowing the plugin to send none-sensitive data to me can be as valuable as a donation.', 'twitchpress' ); ?></p>
        
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="twitchpress_feedback_data"><?php _e( 'Allow none-sensitive information to be sent to Ryan Bayne?', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_feedback_data" <?php checked( get_option( 'twitchpress_feedback_data', '' ) !== 'disabled', true ); ?> name="twitchpress_feedback_data" class="input-checkbox" value="1" />
                        <label for="twitchpress_feedback_data"><?php _e( 'Yes, send configuration and logs only.', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_feedback_prompt"><?php _e( 'Allow the plugin to prompt you for feedback in the future?', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" <?php checked( get_option( 'twitchpress_feedback_prompt', 'no' ), 'yes' ); ?> id="twitchpress_feedback_prompt" name="twitchpress_feedback_prompt" class="input-checkbox" value="1" />
                        <label for="twitchpress_feedback_prompt"><?php _e( 'Yes, prompt me in a couple of months.', 'twitchpress' ); ?></label>
                    </td>
                </tr>
            </table>
            <p class="twitchpress-setup-actions step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'twitchpress' ); ?>" name="save_step" />
                <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-large button-next"><?php _e( 'Skip this step', 'twitchpress' ); ?></a>
                <?php wp_nonce_field( 'twitchpress-setup' ); ?>
            </p>
        </form>
        <?php
    }

    /**
     * Save improvement program and feedback.
     */
    public function twitchpress_setup_improvement_save() { 
        check_admin_referer( 'twitchpress-setup' );
        
        if( isset( $_POST['twitchpress_feedback_data'] ) ) {
            update_option( 'twitchpress_feedback_data', 1 );
        } else {
            delete_option( 'twitchpress_feedback_data' );
        }
        
        if( isset( $_POST['twitchpress_feedback_prompt'] ) ) {
            update_option( 'twitchpress_feedback_prompt', 1 );
        } else {
            delete_option( 'twitchpress_feedback_prompt' );
        }
        
        wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }
    
    public function twitchpress_setup_ready_actions() {
        // Stop showing notice inviting user to start the setup wizard. 
        TwitchPress_Admin_Notices::remove_notice( 'install' );   
    }    
    
    /**
     * Final step.
     */
    public function twitchpress_setup_ready() {
        $this->twitchpress_setup_ready_actions();?>
        <h1><?php _e( 'WordPress TwitchPress is Ready!', 'twitchpress' ); ?></h1>

        <div class="twitchpress-setup-next-steps">
            <div class="twitchpress-setup-next-steps-first">
                <h2><?php _e( 'Next Steps', 'twitchpress' ); ?></h2>
                <ul>
                    <li class="setup-thing"><a class="button button-primary button-large" href="<?php echo esc_url( admin_url( 'admin.php?page=twitchpress-settings' ) ); ?>"><?php _e( 'Go to Settings', 'twitchpress' ); ?></a></li>
                </ul>                                                                                                 
            </div>
            <div class="twitchpress-setup-next-steps-last">
            
                <h2><?php _e( 'Contact Ryan', 'twitchpress' ); ?></h2>
                
                <a href="https://ryanbayne.slack.com/threads/team/squeekycoder/"><?php _e( 'Slack', 'twitchpress' ); ?></a>
                <a href="https://join.skype.com/pJAjfxcbfHPN"><?php _e( 'Skype', 'twitchpress' ); ?></a>
                <a href="https://discord.gg/PcqNqNh"><?php _e( 'Discord', 'twitchpress' ); ?></a>
                <a href="https://twitter.com/Ryan_R_Bayne"><?php _e( 'Twitter', 'twitchpress' ); ?></a>
                <a href="https://plus.google.com/u/0/collection/oA85PE"><?php _e( 'Google+', 'twitchpress' ); ?></a>
  
            </div>
        </div>
        <?php
    }
}

endif;

new TwitchPress_Admin_Setup_Wizard();