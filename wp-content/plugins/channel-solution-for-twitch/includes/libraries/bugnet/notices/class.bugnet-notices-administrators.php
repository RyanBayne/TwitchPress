<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BugNet for WordPress - Standard Administrator only Notices
 *
 * Generate administrator only notices. 
 *
 * @author   Ryan Bayne
 * @category Core
 * @package  BugNet
 * @since    1.0
 */
class BugNet_Notices_Administrators {
    
    public $notices = array();
    
    public function __construct() {
        add_action( 'admin_notices', array( $this, 'print_notices' ) );
    }
    
    public function print_notices() {
        # TODO: If config requires it print notices here.    
    }
    
    public function return_notices() {
        # TODO: If config requires it return notices here, for the plugin or theme to handle.
    }
}