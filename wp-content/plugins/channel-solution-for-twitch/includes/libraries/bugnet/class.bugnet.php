<?php
/**
 * BugNet for WordPress (library, not a plugin)
 * 
 * Add to existing plugins or themes or turn into a plugin on it's own.
 * OR just use my plugins and save time. 
 *
 * @author Ryan Bayne
 * @license GNU General Public License, Version 3
 * @copyright 2017 Ryan R. Bayne (SqueekyCoder@Gmail.com)
 * @version 0.1.1
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
 
if( !class_exists( 'BugNet' ) ) :
        
/**
 * BugNet for WordPress - Main class for loading BugNet
 *
 * @author   Ryan Bayne
 * @category Core
 * @package  BugNet/Core
 * @since    1.0
 */
class BugNet {
    
    /**
    * This will hold the WP_Error object. Use for traces when
    * each entry is within a single request i.e. ensure there are
    * no re-directs else initial trace entries will be lost.  
    * 
    * @var mixed
    */
    public $bugnet_wp_errors = null;
    
    public $log_directory = '';
    
    public function __construct() {
        global $bugnet_wp_errors;
        $bugnet_wp_errors = new WP_Error();
        
        // Load the configuration values.
        require_once( plugin_basename( '/class.bugnet-configuration.php' ) );
        $this->config = new BugNet_Configuration();
        
        // Load files both administration and public requests require.  
        $this->dependencies();
        
        // Hook into the final WP hook and process traces gathered in WP_Error. 
        add_action( 'shutdown', array( $this, 'automatically_process_traces' ) );
        
        // Store $wp_actions in our cache, there is a data view to examine the data.
        add_action( 'shutdown', array( $this, 'cache_wp_actions' ) );
    }    
    
    /**
    * Include dependent files and create globally depending objects.
    * 
    * @version 1.0
    */
    public function dependencies() {
        
        require_once( plugin_basename( '/class.bugnet-rules.php' ) );
        require_once( plugin_basename( '/handlers/class.bugnet-handler-email.php' ) );
        require_once( plugin_basename( '/handlers/class.bugnet-handler-logfiles.php' ) );
        require_once( plugin_basename( '/handlers/class.bugnet-handler-restapi.php' ) );
        require_once( plugin_basename( '/handlers/class.bugnet-handler-wpdb.php' ) );
        require_once( plugin_basename( '/handlers/class.bugnet-handler-tracing.php' ) );
        require_once( plugin_basename( '/notices/class.bugnet-notices-administratorpermanent.php' ) );
        require_once( plugin_basename( '/notices/class.bugnet-notices-administrators.php' ) );
        require_once( plugin_basename( '/notices/class.bugnet-notices-wpdieadministrators.php' ) );
        require_once( plugin_basename( '/notices/class.bugnet-notices-wpdiepublic.php' ) );
        require_once( plugin_basename( '/reports/class.bugnet-reports-dailysummary.php' ) );
        require_once( plugin_basename( '/reports/class.bugnet-reports-eventsnapshot.php' ) );
        require_once( plugin_basename( '/reports/class.bugnet-reports-tracecomplete.php' ) );
        
        $this->rules                           = new BugNet_Rules();
        $this->handler_emails                  = new BugNet_Handler_Email();
        $this->handler_logfiles                = new BugNet_Handler_LogFiles();
        $this->handler_restapi                 = new BugNet_Handler_RESTAPI();
        $this->handler_wpdb                    = new BugNet_Handler_WPDB();
        $this->handler_tracing                 = new BugNet_Handler_Tracing();
        $this->notices_administratorspermanent = new BugNet_Notices_AdministratorPermanent();
        $this->notices_administrators          = new BugNet_Notices_Administrators();
        $this->notices_wpdieadminsafe          = new BugNet_Notices_WPDIEAdministrators();
        $this->notices_wpdieadminvisitors      = new BugNet_Notices_WPDIEPublic();
        $this->reports_dailysummary            = new BugNet_Reports_DailySummary();
        $this->reports_eventsnapshot           = new BugNet_Reports_EventSnapshot();
        $this->reports_tracecomplete           = new BugNet_Reports_TraceComplete();
    }
 
    /**
     * React to an event that is usually unexpected and important. This is
     * like a more serious log and it does use the log() method itself. 
     * 
     * Place this method where you suspect something is going wrong or where
     * fault occurence would be a high priority. 
     * 
     * The entire library becomes available to the log entry i.e.
     * - It could be writtent ot the end of a daily log file.
     * - It could be displayed in an administrator only notice.
     * - It could be displayed in a public wp_die() page. 
     * - More options are available. 
     *
     * @param string $tag a unique identifier.
     * @param string $level One of the following:
     *     'emergency': System is unusable.
     *     'alert': Action must be taken immediately.
     *     'critical': Critical conditions.
     *     'error': Error conditions.
     *     'warning': Warning conditions.
     *     'notice': Normal but significant condition.
     *     'info': Informational messages.
     *     'debug': Debug-level messages.
     * @param string $line use __LINE__
     * @param string $file use __FILE__
     * @param string $title used to start report headers.
     * @param string $message Log message.
     * @param array $atts Optional. Additional information for log handlers.
     * 
     * @version 1.0
     */
    public function event( $tag, $level, $line, $file, $title, $message, $atts = array() ) {
        
        // Confirm administrator setting for this service is 'yes'. 
        if( $this->config->is_events_enabled !== 'yes' ) { return; }
        
        // Set our default arguments to meet criteria for the outputs we use. 
        $defaults = array(
            'support_email'        => false,   
            'transientseconds'     => 60,
        );
        
        // More default arguments. These activate outputs. 
        $output_defaults = array(
            'handler_emails'        => false,
            'handler_mainlogfile'   => false,
            'handler_restapi'       => false,
            'handler_wpdb'          => false,
            'notices_administrators'=> false,
            'notices_wpdieadmin'    => false,
            'notices_wpdiepublic'   => false,
            'reports_eventsnapshot' => true,            
        );

        $args = wp_parse_args( $atts, array_merge( $defaults, $output_defaults ) );
                
        // Was a valid level giving. 
        if( !$this->config->is_valid_level( $level ) ){
            $this->log( 'invalidevent', __( 'An invalid event type was used and needs to be corrected.', 'bugnet' ), array(), true );
            return;
        }
        
        // Is the event level active?
        if ( !$this->config->is_active_level( $level ) ) {
            return;
        }
        
        // Do some flood prevention. 
        if( !$this->is_event_allowed( $tag ) ) 
        {
            return;
        } 
        else 
        {
            // Create a shortlife transient used to prevent event output too soon.
            $this->create_shortlife_event_transient( $tag, $args['transientseconds'] );
        }
        
        // Always do a log based on the event.
        $this->log( $tag, $message, array(), true );

        // Do output starting with emails...
        if( $args['handler_emails'] === true ) {
            $this->handlers_emails->new_email();    
        }
                     
        if( $args['handler_mainlogfile'] === true ) {
            $this->handlers_logfiles->new_line_mainfile();    
        }
        
        if( $args['handler_restapi'] === true ) {
            $this->handlers_restapi->new_entry();
        }
        
        if( $args['handler_wpdb'] === true ) {
            $this->handlers_wpdb->insert();
        }
        
        if( $args['notices_administrators'] === true ) {
            $this->notices_administrators->create_notice();    
        }
        
        if( $args['notices_wpdieadmin'] === true ) {
            $this->notices_wpdieadmin->die();    
        }
        
        if( $args['notices_wpdiepublic'] === true ) {
            $this->notices_wpdiepublic->die();    
        }
        
        if( $args['reports_eventsnapshot'] === true ) {
            $this->reports_eventsnapshot->new();            
        }
    }
        
    /**
     * Add a simple log entry with optional handles to
     * build more detailed logs and share the logs i.e. to a central
     * WordPress database acting as the main site in a network of sites. 
     * 
     * @version 1.6
     */
    public function log( $tag, $message, $arguments = array(), $flood_prevention = true, $error = false ) {

        // Confirm administrator setting for this service is 'yes'. 
        if( $this->config->is_logging_enabled !== 'yes' ) { return; }
           
        // Set our default arguments to meet criteria for the outputs we use. 
        $defaults = array( 
            'systemlog'            => true,
            'dailylog'             => true,
            'restapi'              => false,
            'wpdb'                 => false,
            'line'                 => '',
            'function'             => '',
            'file'                 => '',
            'level'                => '100'
        );    
        $args = wp_parse_args( $arguments, $defaults );
          
        // Do not log if the giving level is not active.
        if( !$this->config->is_active_level( $args['level'] ) ) { return; }  
                    
        // Force a delay between the same log being reported. 
        if( !$this->is_log_allowed( $tag ) ) {
            return;
        }
        
        // Write entry to systen log if active.
        if( $args['systemlog'] === true ) {
            $this->handler_logfiles->system_log( $tag . ': ' . $message );
        }
        
        // Write entry to daily file if active. 
        if( $args['dailylog'] ) {
            $this->handler_logfiles->new_line_daily( $tag, $message, $args );    
        }
        
        // Queue a new entry in transient for sending via REST API.
        if( $args['restapi'] === true ) {
            $this->handlers->restapi->new_entry();
        }
        
        // Insert a new record into the BugNet database tables.
        if( $args['wpdb'] === true ) {
            $this->handlers->wpdb->insert();
        }
        
        // Return an error so that "return log()" can be used at the point of error.
        if( isset( $args['error'] ) && $args['error'] === true || $error === true ) {
            return new WP_Error( $tag, $message, $args );        
        }
    }   
    
    /**
    * Calls $this->log() and passes error parameter. 
    * 
    * @uses $this->log()
    * 
    * @param mixed $tag
    * @param mixed $message
    * @param mixed $atts
    * @param mixed $flood_prevention
    * 
    * @version 1.0
    */
    public function log_error( $tag, $message, $atts = array(), $flood_prevention = true ) {
        $this->log( $tag, $message, $atts, $flood_prevention, true  );    
    }
    
    /**
    * Start or continue or end trace. Trace entries are connected using
    * a unique $tag and the end of trace can be output using any active
    * methods. 
    * 
    * @param string $tag
    * @param string $line please use __LINE__
    * @param string $function please use __FUNCTION__
    * @param boolean $end true will trigger output then delete trace.
    * @param string $note optional note to help identify the step. 
    * @param mixed $atts
    * 
    * @version 1.0
    */
    public function trace( $tag, $line, $function, $file, $end, $message, $atts = array(), $error = false ) {
        // $log_error will return WP_Error and we can return it from the trace, allowing trace to everything.
        $wp_error = null; 
        
        $class = null;
        if( isset( $atts['class'] ) ) {
            $class = $atts['class'];
        }
        
        // Confirm administrator setting for this service is 'yes'. 
        if( $this->config->is_tracing_enabled !== 'yes' ) { return; }
                    
        // Set our default arguments. 
        $default_args = array(
                'tag'           => $tag,
                'time'          => time(),
                'eventid'       => wp_rand( 111111, 999999 ), // Seperates instances of the same trace.
                'automaticonly' => false, // true forces trace to require a controlled ending.
                'maintracefile' => false, // Log to the main trace text file.
                'newtracefile'  => false, // Log to a file for this trace. 
                'wpdb'          => false, // Log to the database.
                'cache'         => true,  // Log to transient cache. 
        );

        $args = wp_parse_args( $atts, $default_args );
        
        // Build a data array to make it easier to access later. 
        $info = array( 'line'     => $line, 
                       'function' => $function, 
                       'class'    => $class, 
                       'file'     => $file,
                       'message'  => $message,
                       'time'     => time(),
                       'eventid'  => $args['eventid'] 
        );

        $this->handler_tracing->do_trace( $tag, $args, $info ); 
        
        // We also log the trace to make it easier to confirm traces are or are not happening.
        if( $error )
        {
            $wp_error = $this->log_error( $tag, $message, $info, false );
        }
        else
        {
            $this->log( $tag, $message, $info, false );
        }
        
        // We can do a controlled ending of a trace.
        if( true === $end )
        {   
            // Ending the trace results in output.
            $this->handler_tracing->end_trace( $tag );                
        }                  

        // If this trace was also an error, a WP_Error() object will exist in $wp_error.
        if( $wp_error !== null ) { return $wp_error; }
    }
    
    /**
    * Use to establish a delay between duplicate log entries. This
    * is a measure to prevent abuse by flooding logs. 
    * 
    * @param mixed $tag
    * 
    * @version 1.0
    */
    public function is_log_allowed( $tag ) {
        $transient = get_transient( 'bugnet_log_' . $tag );
        if( $transient ) {
            return false;// Shortlife transient has not expired.
        }    
        return true;        
    }

    /**
    * Use to establish a delay between duplicate event entries. This
    * is a measure to prevent abuse by flooding active outputs. 
    * 
    * @param mixed $tag
    * 
    * @version 1.0
    */    
    public function is_event_allowed( $tag ) {
        $transient = get_transient( 'bugnet_event_' . $tag );
        if( $transient ) {
            return false;// Shortlife transient has not expired.
        }    
        return true;
    }
        
    /**
    * Create a transient used to force delays between the exact same 
    * event output. This is done based on the $tag alone.
    * 
    * @param mixed $tag
    * @param mixed $args
    * 
    * @version 1.0
    */
    public function create_shortlife_event_transient( $tag, $seconds = 120 ) {
        set_transient( 'bugnet_event_' . $tag, array( 'time' => time() ), $seconds );    
    }
    
    /**
    * Create a transient used to force delays been duplicate log entries.
    * This is done based on the tag alone and is a flood prevention ethod.
    * 
    * @param mixed $tag
    * @param mixed $seconds
    * 
    * @version 1.0
    */
    public function create_shortlife_log_transient( $tag, $seconds = 10 ) {
        set_transient( 'bugnet_log_' . $tag, array( 'time' => time() ), $seconds );    
    }
    
    /**
    * Processes BugNet traces stored in WP_Error by storing them
    * in files, database or transient.
    * 
    * This method is intended for the latest possible hook in WP: 'shutdown'
    * 
    * @version 1.0
    */
    public function automatically_process_traces() {
        $this->handler_tracing->automatically_process_traces();          
    }

    public static function is_system_logging_on(){
        return get_option( 'bugnet_systemlogging_switch', false );
    }
    
    /**
    * Cache the $wp_actions global for reviewing what actions ran during a request.
    * 
    * @version 1.0
    */
    public function cache_wp_actions() { 

        if( 'yes' !== get_option( 'twitchpress_bugnet_cache_action_hooks', 'no' ) ) { return; }
        
        global $wp_actions;        
           
        $cache = get_transient( 'bugnet_wpaction' );
    
        $cache[] = array( 
            'time'    => time(),
            'actions' => $wp_actions,
        );
        
        delete_transient( 'bugnet_wpaction' );
        set_transient( 'bugnet_wpaction', $cache ); 
        
        // Cleanup the cache if it's getting too large for purpose. 
        if( count( $cache ) > 50 ) { delete_transient( 'bugnet_wpaction' );} 
    }
}

endif;