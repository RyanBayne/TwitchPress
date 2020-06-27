<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BugNet for WordPress - Uninstall
 *
 * Uninstall procedure to be called within a plugins own uninstallation procedure.
 *
 * @author   Ryan Bayne
 * @category Core
 * @package  BugNet
 * @since    1.0
 */
class BugNet_Uninstall {
    
    public function uninstall() {
        $this->delete_options();        
        $this->delete_tables();        
        $this->delete_logs();        
        $this->delete_reports();        
    }   
    
    public function delete_options() {
        # TODO: Use the settings view class to loop through all options and remove them. 
    } 
    
    public function delete_tables() {
        # TODO: Delete BugNet custom database tables. 
    }
    
    public function delete_logs() {
        # TODO: Delete BugNet own log files. 
    }
    
    public function delete_reports() {
        # TODO: Delete BugNet report files. 
    }
}