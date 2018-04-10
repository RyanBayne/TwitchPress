<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * TwitchPress_Login_Shortcodes class
 *
 * @class       TwitchPress_Login_Shortcodes
 * @version     1.0.0
 * @package     TwitchPress Login Extension
 * @category    Class
 * @author      Ryan Bayne
 */
class TwitchPress_Login_Shortcodes {

    /**
     * Init shortcodes.
     */
    public static function init() {
        $shortcodes = array(
            'twitchpress_loginform' => __CLASS__ . '::loginform',
        );

        foreach ( $shortcodes as $shortcode => $function ) {
            add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
        }
    }
    
    public static function loginform() {
        
        /**
        * Display a Twitch login button here.
        * 
        * Requires the TwitchPress Login Extension to offer an independent class
        * which is now the next task. 
        */
    }
}
