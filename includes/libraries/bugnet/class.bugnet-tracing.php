<?php
/**
 * BugNet tracing.
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress
 * @version  1.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' );

if ( ! class_exists( 'BugNet_Tracing' ) ) :

class BugNet_Tracing {
    
    var $trace_code = null;
    var $trace_id = null;
    var $meta_keys = array(
        'description'
    );
    
    /**
    * Create a new trace in the bugnet_traces table...
    * 
    * @param mixed $name
    * @param mixed $overwrite
    * 
    * @version 1.0
    */
    public function new_trace( $code, $name = __FUNCTION__, $overwrite = true ) {         
        global $wpdb;
        $this->trace_code = $code;
        
        $back_trace = debug_backtrace( false, 1 );

        if( $overwrite ) {
            $this->delete_trace( $this->trace_code );           
        }

        $wpdb->insert(
            $wpdb->prefix . "bugnet_tracing",
            array( 'name' => $name, 'code' => $this->trace_code )
        ); 
        
        // Make database insert ID available globally...
        $this->trace_id = $wpdb->insert_id;

        bugnet_add_trace_meta( $this->trace_code, 'overwrite', $overwrite );
        bugnet_add_trace_meta( $this->trace_code, 'file', $back_trace[0]['file'] );
        bugnet_add_trace_meta( $this->trace_code, 'line', $back_trace[0]['line'] );       
        bugnet_add_trace_meta( $this->trace_code, 'function', $back_trace[0]['function'] );
        bugnet_add_trace_meta( $this->trace_code, 'plugin', TWITCHPRESS_PLUGIN_BASENAME );
        bugnet_add_trace_meta( $this->trace_code, 'version', TWITCHPRESS_VERSION );
        //$this->add_meta( 'request', $request_ID );
    }
    
    /**
    * Delete a trace based on the dev generated code...
    * 
    * @param mixed $code
    * 
    * @version 1.0
    */
    public function delete_trace() {
        global $wpdb;
        $wpdb->delete( $wpdb->prefix . 'bugnet_tracing', array( 'code' => $this->trace_code ) );
        $wpdb->delete( $wpdb->prefix . 'bugnet_tracing_meta', array( 'code' => $this->trace_code ) );    
    }
}

endif;