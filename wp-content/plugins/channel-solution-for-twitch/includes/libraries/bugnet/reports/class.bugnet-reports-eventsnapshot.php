<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BugNet for WordPress - Event Snapshot
 *
 * This report details all available information at the time of a specified
 * event or standard error or log entry. 
 *
 * @author   Ryan Bayne
 * @category Reports
 * @package  BugNet/Reports
 * @since    1.0
 */
class BugNet_Reports_EventSnapShot {
    
    public function new_snapshot_report() {
        
    } 
    
    public function email() {
        # TODO: Send the report using the email handler.        
    }   
    
    public function logfile() {
        # TODO: Send the report using the logfile handler. 
    }
    
    public function restapi() {
        # TODO: Send the report using REST API handler. 
    }   

}