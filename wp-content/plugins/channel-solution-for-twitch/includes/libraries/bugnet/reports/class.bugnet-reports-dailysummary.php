<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BugNet for WordPress - Daily Summary Report
 *
 * Produces a daily report in HTML for sending in email or publishing in
 * a private page or generating a PDF.
 *
 * @author   Ryan Bayne
 * @category Reports
 * @package  BugNet/Reports
 * @since    1.0
 */
class BugNet_Reports_DailySummary {
    
    public function __construct() {
        
        // Check if we are passed the days-end time. 
        
        // If we are passed days-end, check if summary was generated. 
    }   
    
    public function summary_email() {
        # TODO: Use email handler to send emails to administrators registered for this service.    
    }
    
    public function summary_txtfile() {
        # TODO: Use log file handler to generate daily summary log file (in a separate folder from normal logs)    
    }
    
    public function summary_custompost() {
        # TODO: create a new post for the BugNet post type and store summary as post content.     
    }
}