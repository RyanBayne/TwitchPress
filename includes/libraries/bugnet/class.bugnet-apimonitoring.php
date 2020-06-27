<?php
/**
 * BugNet API Monitoring (API Net for short)...
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress
 * @version  1.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' );

if ( ! class_exists( 'BugNet_API_Net' ) ) :

class BugNet_API_Net {
    
    var $issue_id = null;
    
    public function report_call( $api_name, $outcome, $endpoint, $reason, $title, $meta_values = array() ) {
        
        $dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,1);

        // Insert to issues table and generate an ID for using in issues_meta table...
        $this->issue_id = bugnet_insert_issue( 'api', $outcome, $api_name, $title, $reason, $dbt[0]['line'], $dbt[0]['function'], $dbt[0]['file'] );
                              
        // Now insert the full call to complete the new issue...
        if( !empty( $meta_values ) ){ $this->insert_call_meta( $meta_values ); }
    }
    
    public function insert_call_meta( $meta_values = array() ) {
        global $wpdb;
        foreach( $meta_values as $meta_key => $meta_value ) {
            $wpdb->insert(
                $wpdb->prefix . "bugnet_issues_meta",
                array( 'issue_id' => $this->issue_id, 'meta_key' => maybe_serialize( $meta_key ), 'meta_value' => maybe_serialize( $meta_value ) )
            );    
        }            
    }   
}

endif;