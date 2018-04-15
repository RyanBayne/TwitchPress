<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class.wp-list-table.php' );
}

/**
 * TwitchPress_ListTable_Daily_Logs.
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Views
 * @version     1.0.0
 */
class TwitchPress_ListTable_Daily_Logs extends WP_List_Table {

    /**
     * Max items.
     *
     * @var int
     */
    protected $max_items;

    public $items = array();
    
    /**
     * Constructor.
     */
    public function __construct() {

        parent::__construct( array(
            'singular'  => __( 'Log Entry', 'wpseed' ),
            'plural'    => __( 'Log Entries', 'wpseed' ),
            'ajax'      => false
        ) );
        
        // Apply default items to the $items object.
        $this->default_items();
    }

    /**
    * Setup default items. 
    * 
    * This is not required and was only implemented for demonstration purposes. 
    * 
    * @version 1.2
    */
    public function default_items() {
        global $bugnet;
        $entry_counter = 0;// Acts as temporary ID for data that does not have one. 
        
        // Get data for GET requests. 
        $bugnet_logfiles_handler = new BugNet_Handler_LogFiles();
        $log_file_path = $bugnet_logfiles_handler->get_daily_log_path();
        
        if( file_exists( $log_file_path ) ) 
        {
            $file = new SplFileObject( $log_file_path );
            $file->setFlags(SplFileObject::READ_CSV);

            // Loop on individual trace entries. 
            foreach( $file as $row ) {
                
                // Sometimes the lasts row is empty so avoid treating it as an item. 
                if( !isset( $row[1] ) ) { continue; } 
                
                ++$entry_counter;
                
                $this->items[]['entry_number'] = $entry_counter; 

                // Get the new array key we just created. 
                end( $this->items);
                $new_key = key( $this->items );

                $this->items[$new_key] = $row;  
                
                $this->items = array_reverse( $this->items );       
            }
        }
    }
    
    /**
     * No items found text.
     */
    public function no_items() {
        _e( 'No items found.', 'wpseed' );
    }

    /**
     * Don't need this.
     *
     * @param string $position
     */
    public function display_tablenav( $position ) {

        if ( $position != 'top' ) {
            parent::display_tablenav( $position );
        }
    }

    /**
     * Output the report.
     */
    public function output_result() {

        $this->prepare_items();
        echo '<div id="poststuff" class="twitchpress-tablelist-wide">';
        $this->display();
        echo '</div>';
    }

    /**
     * Get column value.
     *
     * @param mixed $item
     * @param string $column_name   
     * 
     * @version 1.0
     */
    public function column_default( $item, $column_name ) {
        
        switch( $column_name ) {

            case 'time' :
            
                $time_passed = human_time_diff( $item[0], time() );
                echo sprintf( __( '%s ago', 'twitchpress' ), $time_passed );         

            break;

            case 'function' :
                echo $item[1];
            break;

            case 'message' :
                echo $item[2];
            break;             
        }
    }

    /**
     * Get columns.
     *
     * @return array
     */
    public function get_columns() {

        $columns = array(
            'time'     => __( 'Date/Time', 'twitchpress' ),
            'function' => __( 'function', 'twitchpress' ),
            'message'  => __( 'Message', 'twitchpress' ),
        );
            
        return $columns;
    }

    /**
     * Prepare customer list items.
     */
    public function prepare_items() {

        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
        $current_page          = absint( $this->get_pagenum() );
        $per_page              = apply_filters( 'twitchpress_listtable_dailylogs_items_per_page', 20 );

        $this->get_items( $current_page, $per_page );

        /**
         * Pagination.
         */
        $this->set_pagination_args( array(
            'total_items' => $this->max_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $this->max_items / $per_page )
        ) );
    }
}