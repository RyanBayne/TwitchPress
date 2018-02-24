<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
        
/**
 * BugNet for WordPress - Tracing Handler
 *
 * Use to store and track longterm traces. 
 *
 * @author   Ryan Bayne
 * @category Handlers
 * @package  BugNet/Handlers
 * @since    1.0
 */
class BugNet_Handler_Tracing {
    
    /**
    * Adds another entry to the trace. Stored in transient cache until 
    * end_trace() is called and then the trace is stored permanently.
    * 
    * @param mixed $tag
    * @param mixed $args
    * @param mixed $info
    * @package integer $transient_life_seconds default 24 hours (84600)
    * 
    * @version 1.0
    */
    public function do_trace( $tag, $args, $info, $transient_life_seconds = 84600 ) {   

        // Add $tag to a transient that only stores trace tags.
        // We used this to get traces which are also stored in their own transients.  
        $this->update_trace_list( $tag, 'add' );

        // Get possible entries already made as part of a series.
        $trans = $this->get_trace_transient_temporary( $tag );
        
        // Delete existing transient in order to set it again.
        if( !empty( $trans ) ) 
        {
            delete_transient( 'bugnet_' . $tag );    
        }
        else
        {
            // Initialize the new transient value that will be used to generate reports.
            $trans = array( 'automaticonly' => $args['automaticonly'],
                            'traceargs'     => $args, 
                            'traceentries'  => array() 
            );
        }
        
        // Add new entry to traceentries.
        // We should have traceargs already in main array by this point.
        $trans['traceentries'][] = $info;
                
        $this->set_trace_transient( $tag, $trans, $transient_life_seconds );             
    }     
    
    public function uninstall_tracing() {
        $this->delete_all_trace_cache_data();
    }
    
    /**
    * Sets an individual trace in WP transients. 
    * 
    * @param mixed $tag
    * @param mixed $transient_value
    * @param mixed $transient_life_seconds
    * 
    * @version 1.2
    */
    public function set_trace_transient( $tag, $transient_value, $transient_life_seconds = null ) {
        global $bugnet;
        if( !$transient_life_seconds ){ $transient_life_seconds = $bugnet->config->individual_trace_cache_life; }
        set_transient( 'bugnet_' . $tag, $transient_value, $transient_life_seconds );    
    }
    
    public function get_trace_transient_temporary( $tag ) {
        return get_transient( 'bugnet_' . $tag );    
    } 
    
    public function delete_temporary_trace( $tag ) {
        
        delete_transient( 'bugnet_' . $tag  );    
        
        // Also remove the trace from the trace list. 
        $this->update_trace_list( $tag, 'remove' );
    }    

    /**
    * Called as late as possible to process all traces that do not have
    * a controlled ending i.e. there is no deliberate call to end a trace
    * and process it at the moment we want. 
    * 
    * By default all traces that do not have the "automaticonly" value 
    * set and boolean true will be processed at the end of a page request.
    * 
    * @version 1.0
    */
    public function automatically_process_traces() {
        $traces = $this->get_trace_list();

        if( !is_array( $traces ) ){ return; }
         
        foreach( $traces as $tag => $time ) {

            $trace_transient = $this->get_trace_transient_temporary( $tag );
 
            $this->end_trace( $tag ); 
        }    
    }
    
    /**
    * Deletes the two main caches and an infinite number of temporary
    * individual caches. 
    * 
    * @version 1.0
    */
    public function delete_all_trace_cache_data() {
        
        // Remove individual traces first. 
        $traces = $this->get_trace_list();
        if( is_array( $traces ) ){
            foreach( $traces as $tag => $time ) {
               $this->delete_temporary_trace( $tag );
            }        
        }
        
        // Delete the trace list itself. 
        delete_transient( 'bugnet_trace_list' );
        
        // Delete the longterm transient storage where finished transients go. 
        delete_transient( 'bugnet_traces' );
    }   
     
    /**
    * Calls do_trace() and then end() to force output now
    * rather than waiting until the WordPress footer is loaded. 
    * 
    * @param mixed $tag
    * @param mixed $args
    * @param mixed $data
    */
    public function end_trace( $tag, $automatic = true ) {

        $trace_array = $this->get_trace_transient_temporary( $tag );

        if( !$trace_array ) { return; }
         
        // Avoid auto processing complex traces which continue after redirects or multiple step UI procedures. 
        if( isset( $trace_array['automaticonly'] ) && true == $trace_array['automaticonly'] ){ return; }
        
        // Insert data to permament records, start with main trace file. 
        if( true === $trace_array['traceargs']['maintracefile'] )
        {
            $this->update_main_tracefile( $trace_array );
        }
        
        // Individual Trace File
        if( true === $trace_array['traceargs']['newtracefile'] )
        {
            // TODO: write trace to a new file stored in traces folder.    
        }
        
        // Database Insert (BugNet custom table)
        if( true === $trace_array['traceargs']['wpdb'] ) 
        {
            // TODO: insert to BugNet custom table.    
        }
        
        // Transient Cache
        if( true === $trace_array['traceargs']['cache'] )
        {
            $this->update_longterm_transient_traces( $trace_array ); 
        }    
    
        $this->delete_temporary_trace( $tag );         
    }   
    
    /**
    * Add or remove a new item to the trace list. 
    * 
    * @version 1.0
    */
    public function update_trace_list( $tag, $task = 'add' ) {
        $trans = get_transient( 'bugnet_trace_list' );
        
        // If requested to remove a trace entry but none exist at all.
        if( $task == 'remove' && !$trans ) 
        {
            return false;
        }
        
        // If the transient has not been initialized yet. 
        if( !is_array( $trans ) ) {
            $trans = array();
        }
        
        delete_transient( 'bugnet_trace_list' );
        
        if( $task == 'add' && !isset( $trans[ $tag ] ) ) {

            // Tag is a string for sure. 
            $trans[ $tag ] = time();

            $this->set_trace_list_transient( $trans );
            return true;
        }
        elseif( $task == 'remove' && isset( $trans[ $tag ] ) )
        {
            unset( $trans[ $tag ] );
            $this->set_trace_list_transient( $trans );    
        }
    }   
    
    /**
    * Sets the transient holding the trace list.
    * 
    * @version 1.0
    */
    public function set_trace_list_transient( $value ) {
        set_transient( 'bugnet_trace_list', $value, 84600 );    
    }

    /**
    * Processes a request to update the main trace file with the
    * entire trace stored in transient. 
    * 
    * @param mixed $trace_array
    * 
    * @returns boolean false if nothing written else true
    * 
    * @version 1.0
    */
    public function update_main_tracefile( $trace_array ) {
        // Confirm directory and main trace file exists, else create them.
        if( !$this->does_main_trace_file_exist() ) {
            if( !$this->does_trace_dir_exist() ) {
                $result = $this->create_trace_files_directory();
                if( !$result ) { return false; }
            }
            $result = $this->create_main_trace_file();
            if( !$result ){ return false; }    
        }
        
        // Get pointer for appending new line to in the main file.
        $pointer = $this->open_main_trace_file( 'a' );
        
        if( !$pointer ){ return false; }
        
        $csv_array = $this->build_trace_file_rows_array( $trace_array );

        foreach( $csv_array as $key => $entry_array ) {
            fputcsv( $pointer, $entry_array );
        }
        
        @fclose( $pointer );

        return false;            
    }   
    
    public function build_trace_file_rows_array( $trace_array ) {
        $rows_array = array();

        foreach( $trace_array['traceentries'] as $key => $entry ) {
            
            $next_entry = array();
            $next_entry[] = $trace_array['traceargs']['time'];
            $next_entry[] = $trace_array['traceargs']['tag'];
            $next_entry[] = $entry['line'];
            $next_entry[] = $entry['function'];
            $next_entry[] = $entry['class'];
            $next_entry[] = $entry['file'];
            $next_entry[] = $entry['message'];
            
            $rows_array[] = $next_entry;    
        }   
        
        return $rows_array;
    }
     
    /**
    * Get the array of traces in a transient. We used it to retrieve individual
    * traces which are also stored in transient.
    * 
    * @version 1.0
    */
    public function get_trace_list() {
        $trace_list_array = get_transient( 'bugnet_trace_list' ); 
        return $trace_list_array;   
    }
    
    public function write_to_daily_file() { 
        // TODO: write to todays trace file. 
    }
    
    /**
    * Open the main trace file and return file pointer.
    * 
    * @returns false on fail or file pointer object. 
    * 
    * @param mixed $mode
    * 
    * @version 1.0
    */
    public function open_main_trace_file( $mode = 'a' ) {
        $path = $this->get_main_trace_file_path();
        return fopen( $this->get_main_trace_file_path(), $mode );
    }
    
    /**
    * Establishes the path for the main trace file.
    * 
    * @returns string
    * 
    * @version 1.0
    */
    public function get_traces_directory() {
        return WP_CONTENT_DIR . '/bugnet/traces/';    
    }  
      
    /**
    * Establishes the full name for a giving file i.e. path included.
    * 
    * @returns string
    * 
    * @version 1.0
    */
    public function get_main_trace_file_path() {
        return $this->get_traces_directory() . 'maintrace.csv';    
    }
    
    public function does_main_trace_file_exist() {
        return file_exists( $this->get_main_trace_file_path() );           
    }
    
    public function create_main_trace_file() {
    
        // Ensure the traces directory exists. 
        if ( !$this->does_trace_dir_exist() ) {
            $result = $this->create_trace_files_directory();
        }
        
        // Opening the file is how we create a file in PHP. 
        $this->open_main_trace_file( 'w' );
    }

    public function create_daily_trace_files_directory() {
        // TODO: Create wp-content/bugnet/tracing/daily/
    }
    
    public function does_trace_dir_exist() {
        return file_exists( $this->get_traces_directory() );    
    }
    
    public function create_trace_files_directory() {
        return mkdir( $this->get_traces_directory(), 0777, true );
    }
    
    public function delete_trace_files_directory() {  
        // TODO: Delete wp-content/bugnet/tracing/
    }
    
    /**
    * Establishes a filename based on year and the day of the year.
    * 
    * i.e. dailytrace_2017_236.txt
    * 
    * @version 1.0
    */
    public function todays_filename() {
        $dayofyear = date('z') + 1;
        $year = date('Y');
        return "dailytrace_{$year}_{$dayofyear}.csv";
    }
    
    /**
    * Checks if todays trace file has already been created.
    * 
    * @returns boolean the value of file_exists() is returned which is true or false.
    * 
    * @version 1.0
    */
    public function does_daily_trace_file_exist() {
        return file_exists( WP_CONTENT_DIR . "/bugnet/traces/daily/" . $this->todays_filename() );
    }
    
    /**
    * Adds a finished trace to the longterm transient
    * that holds all finished traces if the service is
    * active. 
    * 
    * @param mixed $trace_array
    * 
    * @version 1.0
    */
    public function update_longterm_transient_traces( $trace_array ) {    
        $current = get_transient( 'bugnet_traces' );
        delete_transient( 'bugnet_traces' );
        if( !is_array( $current ) ) { $current = array(); }
        
        // Store the $trace_array in a way that allows instances of the same trace
        // to be matched but also identified as a seperate event. 
        $tag = $trace_array['traceargs']['tag'];
        $eventid = $trace_array['traceargs']['eventid'];
        
        // Story the original 
        $current[ $tag ][ $eventid ]['entries']       = $trace_array['traceentries'];    
        $current[ $tag ][ $eventid ]['time']          = $trace_array['traceargs']['time'];    
        $current[ $tag ][ $eventid ]['automaticonly'] = $trace_array['traceargs']['automaticonly'];    
        $current[ $tag ][ $eventid ]['maintracefile'] = $trace_array['traceargs']['maintracefile'];    
        $current[ $tag ][ $eventid ]['newtracefile']  = $trace_array['traceargs']['newtracefile'];    
        $current[ $tag ][ $eventid ]['wpdb']          = $trace_array['traceargs']['wpdb'];    
        $current[ $tag ][ $eventid ]['cache']         = $trace_array['traceargs']['cache'];    
        
        set_transient( 'bugnet_traces', $current, 84600 );       
    }
    
    public function get_longterm_transient_traces() {
        $trace_array = get_transient( 'bugnet_traces' );
        if( !is_array( $trace_array ) ) {
            $trace_array = array();
        }
        return $trace_array;    
    }
}