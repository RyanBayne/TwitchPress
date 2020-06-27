<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bugnet_includes() {
    require_once( plugin_basename( '/class.bugnet-configuration.php' ) );

    //if( get_option( 'bugnet_apimonitoring' ) ) { 
        require_once( plugin_basename( '/class.bugnet-apimonitoring.php' ) );
        //$this->api_net = new BugNet_API_Net();    
    //}
}

function bugnet_insert_issue( $type, $outcome, $name, $title, $reason, $line, $function, $file ) {
    global $wpdb;
    return twitchpress_db_insert( $wpdb->bugnet_issues, 
        array( 
            'type'        => $type, 
            'outcome'     => $outcome, 
            'name'        => $name,
            'title'       => $title,
            'reason'      => $reason,
            'line'        => $line,
            'function'    => $function,
            'file'        => $file
        ) 
    );     
}
               
function bugnet_new_trace( $code, $name = __FUNCTION__, $record_limit = 1 ) {
    if( 'yes' !== get_option( 'bugnet_activate_tracing' ) ) { return; }
    global $wpdb;
    $back_trace = debug_backtrace( false, 1 );

    if( $record_limit < 2 ) 
    {
        bugnet_delete_trace( $code );           
    }
    else
    {
        
    }

    $wpdb->insert(
        $wpdb->prefix . "bugnet_tracing",
        array( 'name' => $name, 'code' => $code, 'line' => $back_trace[0]['line'], 'function' => $back_trace[0]['function'] )
    ); 
                                    
    bugnet_add_trace_meta( $code, 'trace_id', $wpdb->insert_id );
    bugnet_add_trace_meta( $code, 'limit', $record_limit );
    bugnet_add_trace_meta( $code, 'file', $back_trace[0]['file'] );       
    bugnet_add_trace_meta( $code, 'plugin', TWITCHPRESS_PLUGIN_BASENAME );
    bugnet_add_trace_meta( $code, 'version', TWITCHPRESS_VERSION );
    bugnet_add_trace_meta( $code, 'request', TWITCHPRESS_REQUEST_KEY );        
}

/**
* Delete a trace based on the dev generated code...
* 
* @param mixed $code
* 
* @version 1.0
*/
function bugnet_delete_trace( $code ) {
    global $wpdb;
    $wpdb->delete( $wpdb->prefix . 'bugnet_tracing', array( 'code' => $code ) );
    $wpdb->delete( $wpdb->prefix . 'bugnet_tracing_meta', array( 'code' => $code ) );    
}

/**
* Add a meta value to a trace...
* 
* @version 1.0
*/
function bugnet_add_trace_meta( $code, $meta_key, $meta_value ) {
    if( 'yes' !== get_option( 'bugnet_activate_tracing' ) ) { return; }
    global $wpdb;
    
    $wpdb->insert(
        $wpdb->prefix . "bugnet_tracing_meta",
        array(  
            'meta_key'   => $meta_key, 
            'meta_value' => $meta_value, 
            'code'       => $code,
            'request'    => TWITCHPRESS_REQUEST_KEY 
        )
    );        
}

function bugnet_add_trace_steps( $code, $description ) {
    if( 'yes' !== get_option( 'bugnet_activate_tracing' ) ) { return; }
    global $wpdb;
    
    $back_trace = debug_backtrace( false, 1 );

    $wpdb->insert(
        $wpdb->prefix . "bugnet_tracing_steps",
        array(  
            'code'        => $code,
            'request'     => TWITCHPRESS_REQUEST_KEY,
            'description' => $description,
            'microtime'   => microtime( true ),
            'line'        => $back_trace[0]['line'],
            'function'    => $back_trace[0]['function']
        )
    );        
}

function bugnet_get_traces( $condition = null, $orderby = null, $select = '*', $limit = '', $object = 'OBJECT' ){
    global $wpdb;
    return twitchpress_db_selectorderby( $wpdb->bugnet_tracing, $condition, $orderby, $select, $limit, $object );
}
                              
function bugnet_get_trace_by_trace_id( $trace_id ) {
    global $wpdb;
    return twitchpress_db_selectrow( $wpdb->bugnet_tracing, 'id = ' . $trace_id, '*' );
}

function bugnet_get_trace_meta( $code ) {
    global $wpdb;
    return twitchpress_db_selectorderby( $wpdb->bugnet_tracing_meta, 'code = "' . $code . '"', null, '*', null );    
}

function bugnet_get_trace_steps( $code ) {
    global $wpdb;
    return twitchpress_db_selectorderby( $wpdb->bugnet_tracing_steps, 'code = "' . $code . '"', null, '*', null );    
}