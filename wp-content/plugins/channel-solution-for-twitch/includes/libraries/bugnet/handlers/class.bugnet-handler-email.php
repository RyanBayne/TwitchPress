<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BugNet for WordPress - Email Handler
 *
 * Use to send emails to administrators registered to receive faults. 
 *
 * @author   Ryan Bayne
 * @category Handlers
 * @package  BugNet/Handlers
 * @since    1.0
 */
class BugNet_Handler_Email {
    
    public function __construct() {
        # TODO: Add footer hook to Check if daily log file is to be sent and has been sent. 
        
        # TODO: Add footer hook to Check if daily summary reports are to be sent and has been sent. 
        
        # TODO: Add footer hook to Check if there are new event snapshots and send them if they are meant to be sent.
        
        # TODO: Add footer hook to If trace reports are to be sent by email, check if there are news ones and if they need sent.
    }
    
    public function send_log_text() {
        # TODO: Get the contents of a giving log file and send it by email.     
    } 
    
    public function send_log_file() {
        # TODO: Attach the giving log file to email.         
    }
}