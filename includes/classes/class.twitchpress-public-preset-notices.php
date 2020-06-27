<?php
/**
 * TwitchPress - Public Notice Management (does not handle output)
 *     
 * Do not confuse this files contents with the notices classes/functions which provide
 * the functionality for building and outputting notices. 
 * 
 * This file is purely for managing and accessing the the text.  
 *
 * @author   Ryan Bayne
 * @category User Interface
 * @package  TwitchPress/Notices
 * @since    1.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists( 'TwitchPress_Public_PreSet_Notices' ) ) :

/**
 * TwitchPress Class for accessing messages 
 * and registering new messages using extensions.
 * 
 * @class    TwitchPress_Public_PreSet_Notices
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/UI
 * @version  1.0.0
 */
class TwitchPress_Public_PreSet_Notices {
    public $message_array = array(); 
    
    public $types = array( 'success', 'warning', 'error', 'info' );
    
    public function __construct() {      
        $this->messages = $this->message_list(); 
                      
        // Apply filtering by extensions which need to add more messages to the array. 
        apply_filters( 'twitchpress_filter_public_notices_array', $this->messages );                           
    }

    /**
    * Get messages with as many filters as possible.
    * 
    * Allow this method to become complex.
    * 
    * @version 1.0
    */
    public function get_messages( $atts ) {
        $args = shortcode_atts( 
            array(
                'minimum_id'   => '0',
                'maximum_id'   => '99999',
                'ignore_types' => array(),
                'contains'     => '',
            ), 
            $atts
        ); 
        
        return $this->messages_array; 
    }
    
    /**
    * Get a message by TWITCHPRESS_PLUGIN_BASENAME and the array key for the
    * plugin/extension being queried. 
    * 
    * @param mixed $plugin
    * @param mixed $integer
    * 
    * @version 1.0
    */
    public function get_message_by_id( $plugin, $integer ) {
        return $this->messages[ $plugin ][ $integer ];    
    }
    
    /**
    * None strict search on ALL message values.
    * 
    * @version 1.0
    */
    public function get_message_by_search() {
        
    }
    
    public function get_message_by_title_strict() {
        
    }
    
    public function get_message_by_title_search() {
        
    }
    
    public function get_message_by_info_strict() {
        
    }
    
    public function get_message_by_info_search() {
        
    }

    /**
    * Get message type by integer. 
    * 
    * 0 = success
    * 1 = warning
    * 2 = error
    * 3 = info
    * 
    * @param mixed $integer
    * 
    * @version 1.0
    */
    public function get_type( $type_key ) {
        return $this->types[ $type_key ];
    }
    
    /**
    * List of the public notices available for applicable procedures.
    * 
    * TYPES
    * 0 = success
    * 1 = warning
    * 2 = error
    * 3 = info
    * 
    * @version 1.0
    */
    public function message_list() {      
        $messages_array = array();
        
        /* 0 = success, 1 = warning, 2 = error, 3 = info */
        $messages_array['twitchpress'][0] = array( 'type' => 0, 'title' => __( 'No Update Performed', 'twitchpress' ), 'info' => __( 'We already have the latest Twitch data from your account.', 'twitchpress' ) );

        // Login by Shortcode
        $messages_array['login'][0] = array( 'type' => 1, 'title' => __( 'Twitch.tv Reply', 'twitchpress' ), 'info' => __( 'Twitch said: %s', 'twitchpress' ) );        
        $messages_array['login'][1] = array( 'type' => 1, 'title' => __( 'Login Problem', 'twitchpress' ), 'info' => __( 'We could not established your original page when you attempted to login. Please try again and report this problem if it continues.', 'twitchpress' ) );
        $messages_array['login'][2] = array( 'type' => 1, 'title' => __( 'Twitch Code Missing', 'twitchpress' ), 'info' => __( 'Sorry, it appears Twitch.tv returned you without a code. Please try again and report this issue if it happens again.', 'twitchpress' ) );
        $messages_array['login'][3] = array( 'type' => 1, 'title' => __( 'Twitch Scope Missing', 'twitchpress' ), 'info' => __( 'Sorry, it appears Twitch.tv returned you without all the URL values required to complete your login request. Please try again and report this issue if it happens again.', 'twitchpress' ) );
        $messages_array['login'][4] = array( 'type' => 1, 'title' => __( 'Invalid Twitch Code', 'twitchpress' ), 'info' => __( 'Your request to login via Twitch has failed because the code return by Twitch appears invalid. Please try again or report the issue.', 'twitchpress' ) );
        $messages_array['login'][5] = array( 'type' => 1, 'title' => __( 'Invalid Twitch Token', 'twitchpress' ), 'info' => __( 'Cannot connect to Twitch because the giving token is invalid.', 'twitchpress' ) );
        $messages_array['login'][6] = array( 'type' => 1, 'title' => __( 'User Permission Required', 'twitchpress' ), 'info' => __( 'Permission to read user email has not been giving - cannot complete login.', 'twitchpress' ) );
        $messages_array['login'][7] = array( 'type' => 1, 'title' => __( 'Twitch Connection Issue', 'twitchpress' ), 'info' => __( 'Twitch could not confirm your credentials for us on this attempt, please try to login again.', 'twitchpress' ) );
        $messages_array['login'][8] = array( 'type' => 1, 'title' => __( 'Email Address Missing', 'twitchpress' ), 'info' => __( 'Twitch returned some of your account information but your email address was not included in the data.', 'twitchpress' ) );
        $messages_array['login'][9] = array( 'type' => 1, 'title' => __( 'Twitch Needs Verification', 'twitchpress' ), 'info' => __( 'Your request to login via Twitch was refused because your email address has not been verified by Twitch. You will need to verify your email through Twitch and then register on this site.', 'twitchpress' ) );
        $messages_array['login'][10] = array( 'type' => 1, 'title' => __( 'Duplicate Accounts', 'twitchpress' ), 'info' => __( 'Welcome back to this site. Your personal Twitch ID has been found linked to two or more accounts but neither of them contain the same email address found in your Twitch account. Please access your preferred account manually. Please also report this matter so we can consider deleting one of your accounts on this site.', 'twitchpress' ) );
        $messages_array['login'][11] = array( 'type' => 1, 'title' => __( 'Please Sign-Up', 'twitchpress' ), 'info' => __( 'This site does not allow automatic registration using Twitch. Please go to the registration page and create an account using the same email address as used in your Twitch account.', 'twitchpress' ) );
        $messages_array['login'][12] = array( 'type' => 1, 'title' => __( 'Existing Account Found', 'twitchpress' ), 'info' => __( 'There is an existing account with a similar Twitch username to your channel. Is it possible you have already created an account on this website? Please contact administration so we can secure the best public username. We know your Twitch brand is important to you.', 'twitchpress' ) );
        $messages_array['login'][13] = array( 'type' => 1, 'title' => __( 'Registration Problem', 'twitchpress' ), 'info' => __( 'TwitchPress attempted to create a new account but failed when using the following information.', 'twitchpress' ) );
        $messages_array['login'][14] = array( 'type' => 1, 'title' => __( 'Login Failure', 'twitchpress' ), 'info' => __( 'Please try to Connect with Twitch again and report this notice if it continues to appear.', 'twitchpress' ) );
        $messages_array['login'][15] = array( 'type' => 0, 'title' => __( 'Registration Complete', 'twitchpress' ), 'info' => __( 'You have been registered on this site using your Twitch login name but not your Twitch password. A password for this site will be sent to the email address used in your Twitch account.', 'twitchpress' ) );
        
        // Ultimate Member Extension (temporary until filter is applied)
        $messages_array['umextension'][0] = array( 'type' => 2, 'title' => __( 'No Subscription Plan', 'twitchpress' ), 'info' => __( 'You do not have a subscription plan for this sites main Twitch.tv channel. Your UM role has been set to the default.', 'twitchpress' ) );
        $messages_array['umextension'][1] = array( 'type' => 3, 'title' => __( 'Hello Administrator', 'twitchpress' ), 'info' => __( 'Your request must be rejected because you are an administrator. We cannot risk reducing your access.', 'twitchpress' ) );
        $messages_array['umextension'][2] = array( 'type' => 2, 'title' => __( 'Ultimate Member Role Invalid', 'twitchpress' ), 'info' => __( 'Sorry, the role value for subscription plan [%s] is invalid. This needs to be corrected in TwitchPress settings.', 'twitchpress' ) );
        $messages_array['umextension'][3] = array( 'type' => 0, 'title' => __( 'Ultimate Member Role Updated', 'twitchpress' ), 'info' => __( 'Your community role is now %s because your subscription plan is %s.', 'twitchpress' ) );
        
        // Streamlabs (temporary pending filter)
        $messages_array['officialstreamlabsextension'][0] = array( 'type' => 2, 'title' => __( 'No Update Performed', 'twitchpress' ), 'info' => __( 'We already have the latest Streamlabs data for you.', 'twitchpress' ) );
             
        return $messages_array;
    } 
}

endif;
