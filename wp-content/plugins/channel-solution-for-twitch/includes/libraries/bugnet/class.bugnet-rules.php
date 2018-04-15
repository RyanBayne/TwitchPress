<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BugNet for WordPress - Rules
 *
 * Rules/Event Rules/Custom Error Rules/Triggers
 * 
 * Errors are passed through the rules class and if a rule
 * matches the error it then triggers specified/custom treatment.
 *
 * @author   Ryan Bayne
 * @category Core
 * @package  BugNet
 * @since    1.0
 */
class BugNet_Rules {
    
    public function query_rules() {
        # TODO: Query the BugNet table for a matching rule
    }
    
    public function apply_rule() {
        # TODO: Do what the rule request. 
    }
    
    public function install_rules_table() {
        # TODO: Create a new database table for storing rules.
    }
        
}