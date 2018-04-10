<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Filter to allow whitelisted IP to access the wp-admin login
 *
 * @param $allowed
 *
 * @return int
 */
function um_whitelisted_wpadmin_access( $allowed ) {
	$ips = UM()->options()->get( 'wpadmin_allow_ips' );
		
	if ( !$ips )
		return $allowed;
		
	$ips = array_map("rtrim", explode("\n", $ips));
	$user_ip = um_user_ip();

	if ( in_array( $user_ip, $ips ) )
		$allowed = 1;
		
	return $allowed;
}
add_filter( 'um_whitelisted_wpadmin_access', 'um_whitelisted_wpadmin_access' );


/**
 * Filter to customize errors
 *
 * @param $message
 *
 * @return string
 */
function um_custom_wp_err_messages( $message ) {

	if ( isset( $_REQUEST['err'] ) && !empty( $_REQUEST['err'] ) ) {
		switch( $_REQUEST['err'] ) {
			case 'blocked_email':
				$err = __('This email address has been blocked.','ultimate-member');
				break;
			case 'blocked_ip':
				$err = __('Your IP address has been blocked.','ultimate-member');
				break;
		}
	}

	if ( isset( $err ) ) {
		$message = '<div class="login" id="login_error">'.$err.'</div>';
	}

	return $message;
}
add_filter( 'login_message', 'um_custom_wp_err_messages' );


/**
 * Check for blocked ip
 *
 * @param $user
 * @param $username
 * @param $password
 *
 * @return mixed
 */
function um_wp_form_errors_hook_ip_test( $user, $username, $password ) {
	if ( ! empty( $username ) ) {
		/**
		 * UM hook
		 *
		 * @type action
		 * @title um_submit_form_errors_hook__blockedips
		 * @description Hook that runs after user reset their password
		 * @input_vars
		 * [{"var":"$args","type":"array","desc":"Form data"}]
		 * @change_log
		 * ["Since: 2.0"]
		 * @usage add_action( 'um_submit_form_errors_hook__blockedips', 'function_name', 10, 1 );
		 * @example
		 * <?php
		 * add_action( 'um_submit_form_errors_hook__blockedips', 'my_submit_form_errors_hook__blockedips', 10, 1 );
		 * function my_submit_form_errors_hook__blockedips( $args ) {
		 *     // your code here
		 * }
		 * ?>
		 */
		do_action( "um_submit_form_errors_hook__blockedips", $args = array() );
		/**
		 * UM hook
		 *
		 * @type action
		 * @title um_submit_form_errors_hook__blockedemails
		 * @description Hook that runs after user reset their password
		 * @input_vars
		 * [{"var":"$args","type":"array","desc":"Form data"}]
		 * @change_log
		 * ["Since: 2.0"]
		 * @usage add_action( 'um_submit_form_errors_hook__blockedemails', 'function_name', 10, 1 );
		 * @example
		 * <?php
		 * add_action( 'um_submit_form_errors_hook__blockedemails', 'my_submit_form_errors_hook__blockedemails', 10, 1 );
		 * function my_submit_form_errors_hook__blockedemails( $args ) {
		 *     // your code here
		 * }
		 * ?>
		 */
		do_action( "um_submit_form_errors_hook__blockedemails", $args = array( 'username' => $username ) );
			
	}

	return $user;
}
add_filter( 'authenticate', 'um_wp_form_errors_hook_ip_test', 10, 3 );


/**
 * Login checks thru the wordpress admin login
 *
 * @param $user
 * @param $username
 * @param $password
 *
 * @return WP_Error|WP_User
 */
function um_wp_form_errors_hook_logincheck( $user, $username, $password ) {

	do_action( 'wp_authenticate_username_password_before', $user, $username, $password );

	if ( isset( $user->ID ) ) {

		um_fetch_user( $user->ID );
		$status = um_user('account_status');

		switch( $status ) {
			case 'inactive':
				return new WP_Error( $status, __('Your account has been disabled.','ultimate-member') );
				break;
			case 'awaiting_admin_review':
				return new WP_Error( $status, __('Your account has not been approved yet.','ultimate-member') );
				break;
			case 'awaiting_email_confirmation':
				return new WP_Error( $status, __('Your account is awaiting e-mail verification.','ultimate-member') );
				break;
			case 'rejected':
				return new WP_Error( $status, __('Your membership request has been rejected.','ultimate-member') );
				break;
		}

	}

	return wp_authenticate_username_password( $user, $username, $password );

}
add_filter( 'authenticate', 'um_wp_form_errors_hook_logincheck', 50, 3 );