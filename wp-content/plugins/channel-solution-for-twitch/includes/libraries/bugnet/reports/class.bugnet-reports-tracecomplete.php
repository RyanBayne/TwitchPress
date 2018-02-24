<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BugNet for WordPress - Generate a report when a Trace finishes.
 *
 * This report is generated when a trace finishes. A trace could be time
 * delayed and contain data from many requests or just a single request.
 *
 * @author   Ryan Bayne
 * @category Reports
 * @package  BugNet/Reports
 * @since    1.0
 */
class BugNet_Reports_TraceComplete {
    
    public function generate_trace_report() {
        # TODO: Compile a report using the giving transient trace data. 
    }
    
    public function email() {
        # TODO: Send trace details via email.         
    }
    
    public function text_file() {
        # TODO: Create a new text file with the trace details.    
    }
    
    public function restapi() {
        # TODO: Send a trace report by REST API to another WordPress. 
    }
}