<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BugNet for WordPress - Database Storage Handler
 *
 * Stores log entries in a custom database table.
 *
 * @author   Ryan Bayne
 * @category Handler
 * @package  BugNet/WPDB
 * @since    1.0
 */
class BugNet_Handler_WPDB {
    
    public function __construct() {
        # TODO: Add footer hook for cleaning database table every 24 hours. 
    }   
    
    public function insert_rows() {
        # TODO: loop and validate the giving data before attempting table insert.
        $this->insert_row();    
    }
    
    public function insert_row() {
        # TODO: insert a log entry in the bugnet database table.
    }
    
    public function cleanup() {
        # TODO: Delete irrelevent entries if table is getting too large. 
    }
    
    public function delete_rows() {
        
    }
    
    
}