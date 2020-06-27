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
class TwitchPress_ListTable_BugNet_Issues extends WP_List_Table {

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
            'singular'  => __( 'Issue', 'twitchpress' ),
            'plural'    => __( 'Issues', 'twitchpress' ),
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
        if( !get_option( 'bugnet_version' ) ) 
        {
            return;    
        }
        
        global $wpdb;
        $issues = twitchpress_db_selectwherearray( $wpdb->bugnet_issues );

        // Loop on individual trace entries. 
        $entry_counter = 0;// Acts as temporary ID for data that does not have one. 
        foreach( $issues as $issue ) {
 
            ++$entry_counter;
            
            $this->items[]['entry_number'] = $entry_counter; 

            // Get the new array key we just created. 
            end( $this->items);
            $new_key = key( $this->items );

            $this->items[$new_key] = $issue;  
            
            $this->items = array_reverse( $this->items );       
        }
    }
    
    /**
     * No items found text.
     */
    public function no_items() {
        _e( 'No items found.', 'twitchpress' );
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
        if( !get_option( 'bugnet_version' ) ) 
        {
            $message = __( 'BugNet has not been installed from the Settings area.', 'twitchpress' );
            echo '<div id="message" class="error inline"><p><strong>' . $message . '</strong></p></div>';    
        }
        else
        {
            $this->prepare_items();
            echo '<div id="poststuff" class="twitchpress-tablelist-wide">';
            $this->display();
            echo '</div>';
        }
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
            case 'id' :
                echo $item['id'];
            break;
            case 'type' :
                echo $item['type'];
            break;
            case 'time' :
                $time_passed = human_time_diff( strtotime( $item['timestamp'] ), time() );
                echo sprintf( __( '%s ago', 'twitchpress' ), $time_passed );         
            break;  
            case 'name' :
                echo $item['name']; 
            break;
            case 'title' :
                echo $item['title'];
            break;
            case 'reason' :
                echo $item['reason'];
            break;
            case 'line' :
                echo $item['line'];
            break; 
            case 'function' :
                echo $item['function'];
            break;
            case 'file' :
                echo $item['file'];
            break;
            case 'outcome' :
                echo $item['outcome'];
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
            'id'       => __( 'ID', 'twitchpress' ),
            'type'     => __( 'Type', 'twitchpress' ),
            'time'     => __( 'Date/Time', 'twitchpress' ),
            'name'     => __( 'Name', 'twitchpress' ),
            'title'    => __( 'Title', 'twitchpress' ),
            'reason'   => __( 'Reason', 'twitchpress' ),
            'line'     => __( 'Line', 'twitchpress' ),
            'function' => __( 'Function', 'twitchpress' ),
            //'file'     => __( 'File', 'twitchpress' ),
            'outcome'  => __( 'Outcome', 'twitchpress' ),
        );
            
        return $columns;
    }

    /**
     * Prepare customer list items.
     */
    public function prepare_items() {

        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
        $current_page          = absint( $this->get_pagenum() );
        $per_page              = apply_filters( 'twitchpress_listtable_bugnet_issues_items_per_page', 20 );

        $this->get_items( $current_page, $per_page );

        // Pagination...
        $this->set_pagination_args( array(
            'total_items' => $this->max_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $this->max_items / $per_page )
        ) );
    }
}