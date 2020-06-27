<?php
/**
 * YouTube API setup wizard (using a Google API profile that contains the YouTube service). 
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Admin
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_YouTube_Setup_Wizard' ) ) :

/**
 * TwitchPress_YouTube_Setup_Wizard Class 
 * 
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Admin
 * @version     1.0
*/
class TwitchPress_Admin_Setup_Wizard_YouTube {

    /** @var string Current Step */
    private $step = '';

    /** @var array Steps for the setup wizard */
    private $steps = array();

    /** @var boolean Is the wizard optional or required? */
    private $optional = false;

    /**
     * Hook in tabs.
     */
    public function __construct() {
        
        if ( apply_filters( 'twitchpress_enable_setup_wizard', true ) && current_user_can( 'activate_plugins' ) ) {
            add_action( 'admin_menu', array( $this, 'admin_menus' ) );
            add_action( 'admin_init', array( $this, 'setup_wizard_youtube' ) );
        } 
    }

    /**
     * Add admin menus/screens.
     */
    public function admin_menus() {
        add_dashboard_page( '', '', 'manage_options', 'twitchpress-setup-youtube', '' );
    }

    /**
     * Show the setup wizard.
     * 
     * @version 1.0
     */
    public function setup_wizard_youtube() {
        if ( empty( $_GET['page'] ) || 'twitchpress-setup-youtube' !== $_GET['page'] ) {
            return;
        }
        
        // Ensure install related notices no longer show. 
        TwitchPress_Admin_Notices::remove_notice( 'installyoutube' );
        TwitchPress_Admin_Notices::remove_notice( 'missingvaluesofferwizardyoutube' );

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
            
                    /*
                    'samples' => array(
                        'name'    =>  __( 'Sample Data', 'twitchpress' ),
                        'view'    => array( $this, 'twitchpress_setup_samples' ),
                        'handler' => array( $this, 'twitchpress_setup_samples_save' )
                    ),
                    'examples' => array(
                        'name'    =>  __( 'Create Example Pages', 'twitchpress' ),
                        'view'    => array( $this, 'twitchpress_setup_examples' ),
                        'handler' => array( $this, 'twitchpress_setup_examples_save' ),
                    ), 
                    'shortcodes' => array(
                        'name'    =>  __( 'Example Shortcodes', 'twitchpress' ),
                        'view'    => array( $this, 'twitchpress_setup_shortcodes' ),
                        'handler' => array( $this, 'twitchpress_setup_shortcodes_save' ),
                    ),
                    */
                                               
            'next_steps' => array(
                'name'    =>  __( 'Ready!', 'twitchpress' ),
                'view'    => array( $this, 'twitchpress_setup_ready' ),
                'handler' => ''
            )
        );
        
        $this->steps = apply_filters( 'twitchpress_wizard_menu', $this->steps );
        
        $this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        // Register scripts for the pretty extension presentation and selection.
        wp_register_script( 'jquery-blockui', TWITCHPRESS_PLUGIN_URL . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
        wp_register_script( 'select2', TWITCHPRESS_PLUGIN_URL . '/assets/js/select2/select2' . $suffix . '.js', array( 'jquery' ), '3.5.2' );
        wp_register_script( 'twitchpress-enhanced-select', TWITCHPRESS_PLUGIN_URL . '/assets/js/admin/twitchpress-enhanced-select' . $suffix . '.js', array( 'jquery', 'select2' ), TWITCHPRESS_VERSION );
        
        // Queue CSS for the entire setup process.
        wp_enqueue_style( 'twitchpress_admin_styles', TWITCHPRESS_PLUGIN_URL . '/assets/css/admin.css', array(), TWITCHPRESS_VERSION );
        wp_enqueue_style( 'twitchpress-setup', TWITCHPRESS_PLUGIN_URL . '/assets/css/twitchpress-setup.css', array( 'dashicons', 'install' ), TWITCHPRESS_VERSION );
        wp_register_script( 'twitchpress-setup', TWITCHPRESS_PLUGIN_URL . '/assets/js/admin/twitchpress-setup.min.js', array( 'jquery', 'twitchpress-enhanced-select', 'jquery-blockui' ), TWITCHPRESS_VERSION );

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
            <title><?php _e( 'WordPress TwitchPress &rsaquo; YouTube Setup Wizard', 'twitchpress' ); ?></title>
            <?php wp_print_scripts( 'twitchpress-setup' ); ?>
            <?php do_action( 'admin_print_styles' ); ?>
            <?php do_action( 'admin_head' ); ?>
        </head>
        <body class="twitchpress-setup wp-core-ui">
            <h1 id="twitchpress-logo"><a href="<?php echo TWITCHPRESS_HOME;?>"><img src="<?php echo TWITCHPRESS_PLUGIN_URL; ?>/assets/images/twitchpress_logo.png" alt="TwitchPress" /></a></h1>
        <?php
    }

    /**
     * Setup Wizard Footer.
     */
    public function setup_wizard_footer() { 
        if ( 'next_steps' === $this->step ) : ?>
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
     * 
     * @version 2.0
     */
    public function setup_wizard_content() {           
        echo '<div class="twitchpress-setup-content">'; 
        
        if( !isset( $this->steps[ $this->step ]['view'] ) ) {
            ?><h1><?php _e( 'Invalid Step!', 'twitchpress' ); ?></h1><p><?php _e( 'You tried to visit a setup step that does not exist. Please report this to help improve the plugin.', 'twitchpress' ); ?></p><?php 
        } elseif( !method_exists( $this, $this->steps[ $this->step ]['view'][1] ) ) {
            ?><h1><?php _e( 'Something Has Gone Very Wrong!', 'twitchpress' ); ?></h1><p><?php _e( 'A wizard step is missing! Please report it to improve the plugin.', 'twitchpress' ); ?></p><?php             
        } elseif( !current_user_can( 'activate_plugins' ) ) {
            ?><h1><?php _e( 'Administrators Only', 'twitchpress' ); ?></h1><p><?php _e( 'Only administrators can complete the installation of TwitchPress.', 'twitchpress' ); ?></p><?php                         
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
        <h1><?php _e( 'Setup YouTube Web Services API', 'twitchpress' ); ?></h1>
                
        <p><?php _e( 'Use this wizard to add Google API credentails created specifically for YouTube. Please take some
        time to understand your responsibilities by reading Google API Terms of Service first.', 'twitchpress' ); ?></p>
        
        <form method="post">
            <p class="twitchpress-setup-actions step">
                <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button-primary button button-large button-next"><?php _e( 'Accept Terms', 'twitchpress' ); ?></a>
                <a href="https://developers.google.com/terms" class="button button-large" target="_blank"><?php _e( 'Read Terms', 'twitchpress' ); ?></a>
                <a href="<?php echo esc_url( admin_url() ); ?>" class="button button-large"><?php _e( 'Not right now', 'twitchpress' ); ?></a>
                <?php wp_nonce_field( 'twitchpress-setup-youtube' ); ?>
            </p>
        </form>        
                    
        <?php 
    }

    /**
     * Step requesting the user to accept the Twitch Developer Services Agreement
     * or avoid using the plugin.
     * 
     * @version 5.0
     */
    public function twitchpress_setup_application() {?>
        <h1><?php _e( 'Enter Google API Application Credentials', 'twitchpress' ); ?></h1>
        
        <p><?php _e( 'Enter a set of Google API credentials that have the YouTube service permitted. This setup will not put you through oAuth2 
        at this time (pending further development). This plugin will not offer a button for visitors to login/register using their Google account.', 'twitchpress' ); ?></p>

        <h3><?php _e( 'Support buttons will open new tabs...', 'twitchpress' ); ?></h3>
              
        <p class="twitchpress-setup-actions step">
            <a href="https://console.developers.google.com/apis/credentials" class="button button-large" target="_blank"><?php _e( 'Manage Apps', 'twitchpress' ); ?></a>                                                                
            <a href="https://discord.gg/ScrhXPE" class="button button-large" target="_blank"><?php _e( 'Chat Help (Discord)', 'twitchpress' ); ?></a>                
        </p>
                                                
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="twitchpress_main_youtube_name"><?php _e( 'Main Channel Name', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="text" id="twitchpress_main_youtube_name" name="twitchpress_main_youtube_name" class="input-text" value="<?php echo esc_html( get_option( 'twitchpress_main_youtube_name' ) );?>" />
                        <label for="twitchpress_main_youtube_name"><?php _e( 'example: LOLinDark1, StarCitizen, nookyyy', 'twitchpress' ); ?></label>
                    </td>
                </tr>                
                <tr>
                    <th scope="row"><label for="twitchpress_main_youtube_id"><?php _e( 'Main Channel ID', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="text" id="twitchpress_main_youtube_id" name="twitchpress_main_youtube_id" class="input-text" value="<?php echo esc_html( get_option( 'twitchpress_main_youtube_id' ) );?>" />
                        <label for="twitchpress_main_youtube_id"><?php _e( 'example: LOLinDark1, StarCitizen, nookyyy', 'twitchpress' ); ?></label>
                    </td>
                </tr>               
                <tr>
                    <th scope="row"><label for="twitchpress_allapi_youtube_default_uri"><?php _e( 'App Redirect URI', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="text" id="twitchpress_allapi_youtube_default_uri" name="twitchpress_app_redirect_youtube" class="input-text" value="<?php echo get_option( 'twitchpress_allapi_youtube_default_uri' );?>" />
                        <label for="twitchpress_allapi_youtube_default_uri"><?php echo __( 'example: ', 'twitchpress' ) . get_site_url(); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_allapi_youtube_default_id"><?php _e( 'Client/App ID', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="text" id="twitchpress_allapi_youtube_default_id" name="twitchpress_allapi_youtube_default_id" class="input-text" value="<?php echo get_option( 'twitchpress_allapi_youtube_default_id' );?>" />
                        <label for="twitchpress_allapi_youtube_default_id"><?php _e( 'example: uo6dggojyb8d6soh92zknwmi5ej1q2', 'twitchpress' ); ?></label>
                    </td>
                </tr>
            </table>

            <p class="twitchpress-setup-actions step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'twitchpress' ); ?>" name="save_step" />
                <?php wp_nonce_field( 'twitchpress-setup' ); ?>
            </p>
        </form>
                
        <?php    
    }

    /**
     * Save application settings and then forwards user to kraken oauth2.
     * 
     * @version 3.1
     */
    public function twitchpress_setup_application_save() {          
        check_admin_referer( 'twitchpress-setup-youtube' );
        
        // Sanitize $_POST values.
        $main_channel_name  = sanitize_text_field( $_POST['twitchpress_main_youtube_name'] );
        $main_channel_id    = sanitize_text_field( $_POST['twitchpress_main_youtube_id'] );
        $redirect_uri       = sanitize_text_field( $_POST['twitchpress_allapi_youtube_default_uri'] );
        $app_id             = sanitize_text_field( $_POST['twitchpress_allapi_youtube_default_id'] );
        $app_secret         = sanitize_text_field( $_POST['twitchpress_allapi_youtube_default_secret'] );
 
        // Store Main Channel Name
        if( empty( $main_channel_name ) ) {
            TwitchPress_Admin_Notices::add_custom_notice( 'wizardcredentialsincompleteyoutube', sprintf( __( 'Application credentails are missing. All four inputs need a value.'), esc_html( $main_channel_name ) ) );            
            return;
        } else {
            update_option( 'twitchpress_main_youtube_name',  $main_channel_name,  true );            
        }
        
        // Store Main Channel ID
        if( empty( $main_channel_id ) ) {
            TwitchPress_Admin_Notices::add_custom_notice( 'wizardcredentialsincompleteyoutube', sprintf( __( 'Application credentails are missing. All four inputs need a value.'), esc_html( $main_channel_name ) ) );            
            return;
        } else {
            update_option( 'twitchpress_main_youtube_id',  $main_channel_id,  true );            
        }
        
        // Store Redirect URI
        if( empty( $redirect_uri ) ) {
            TwitchPress_Admin_Notices::add_custom_notice( 'wizardcredentialsincompleteyoutube', sprintf( __( 'Application credentails are missing. All four inputs need a value.'), esc_html( $main_channel ) ) );            
            return;
        } else {
            update_option( 'twitchpress_allapi_youtube_default_uri',  trim( $redirect_uri ),  true );
        }        
        
        // Store App ID
        if( empty( $app_id ) ) {
            TwitchPress_Admin_Notices::add_custom_notice( 'wizardcredentialsincompleteyoutube', sprintf( __( 'Application credentails are missing. All four inputs need a value.'), esc_html( $main_channel ) ) );            
            return;
        } else {
            update_option( 'twitchpress_allapi_youtube_default_id', $app_id, true );
        }        
        
        // Store App Secret
        if( empty( $app_secret ) ) {
            TwitchPress_Admin_Notices::add_custom_notice( 'wizardcredentialsincompleteyoutube', sprintf( __( 'Application credentails are missing. All four inputs need a value.'), esc_html( $main_channel ) ) );            
            return;
        } else {
            update_option( 'twitchpress_allapi_youtube_default_secret', $app_secret, true );
        }
 
        update_option( 'twitchpress_main_channel_name_youtube', $main_channel_name, true );
        update_option( 'twitchpress_main_channel_id_youtube', $main_channel_id, true );

        // Get the main channels dedicated post for storing information with it...
        $existing_channelpost_id = twitchpress_get_channel_post( twitchpress_get_main_channels_twitchid() );
                
        // Insert a new twitchchannel post if one does not already exist.
        if( !$existing_channelpost_id ) 
        {
            TwitchPress_Admin_Notices::add_custom_notice( 'mainpostfailedtoinsert', __( 'Your dedicated post for storing information with could not be found. The post is created by the main Setup Wizard for configuring Twitch and must be done first.' ) );      
            return;
        } 
        else 
        {
            $post_id = $existing_channelpost_id;
        } 
              
        // Confirm storage of application and that oAuth2 is next.        
        TwitchPress_Admin_Notices::add_custom_notice( 'applicationcredentialssaved', __( 'Your Google API credentials have been stored and your WordPress site is ready to get YouTube data.' ) );
                      
        check_admin_referer( 'twitchpress-setup-youtube' );
        wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }
    
    /**
     * Final step.
     * 
     * @version 2.0
     */
    public function twitchpress_setup_ready() {
        
        $this->twitchpress_setup_ready_actions();?>

        <div class="twitchpress-setup-next-steps">
            <div class="twitchpress-setup-next-steps-first">
                <h2><?php _e( 'TwitchPress System Ready!', 'twitchpress' ); ?></h2>
                <ul>
                    <li class="setup-thing"><a class="button button-primary button-large" href="<?php echo esc_url( admin_url( 'admin.php?page=twitchpress' ) ); ?>"><?php _e( 'Go to Settings', 'twitchpress' ); ?></a></li>
                </ul>                                                                                                 
            </div>
            <div class="twitchpress-setup-next-steps-last">
            
                <h2><?php _e( 'Need some support?', 'twitchpress' ); ?></h2>
                                                           
                <a href="<?php echo TWITCHPRESS_GITHUB; ?>"><?php _e( 'GitHub', 'twitchpress' ); ?></a>
                <a href="<?php echo TWITCHPRESS_DISCORD; ?>"><?php _e( 'Discord', 'twitchpress' ); ?></a>
                <a href="<?php echo TWITCHPRESS_TWITTER; ?>"><?php _e( 'Twitter', 'twitchpress' ); ?></a>
                <a href="<?php echo TWITCHPRESS_HOME; ?>"><?php _e( 'Blog', 'twitchpress' ); ?></a>
                <a href="<?php echo TWITCHPRESS_AUTHOR_DONATE; ?>"><?php _e( 'Patreon', 'twitchpress' ); ?></a>
     
            </div>
        </div>
        <?php
    }
}

endif;

// This file is conditionally included...
new TwitchPress_Admin_Setup_Wizard_YouTube();