<?php
/**
 * TwitchPress Login Extension - Core Functions

 * @author   Ryan Bayne
 * @category Core
 * @package  TwitchPress Login Extension
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !function_exists( 'twitchpress_login_error' ) ) {
    /**
    * Use the TwitchPress Custom Login Notices class to generate a 
    * a new notice on the login scree
    * 
    * @param mixed $message
    * 
    * @version 1.0
    */
    function twitchpress_login_error( $message ) {
        $login_messages = new TwitchPress_Custom_Login_Messages();
        $login_messages->add_error( $message );
        unset( $login_messages );                 
    }
}

if( !function_exists( 'twitchpress_is_backend_login' ) ) {
    function twitchpresS_is_backend_login(){
        $ABSPATH_MY = str_replace(array('\\','/'), DIRECTORY_SEPARATOR, ABSPATH);
        return ((in_array($ABSPATH_MY.'wp-login.php', get_included_files()) || in_array($ABSPATH_MY.'wp-register.php', get_included_files()) ) || $GLOBALS['pagenow'] === 'wp-login.php' || $_SERVER['PHP_SELF']== '/wp-login.php');
    }    
}

