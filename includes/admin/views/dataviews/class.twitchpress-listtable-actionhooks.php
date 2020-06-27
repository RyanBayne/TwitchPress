<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class.wp-list-table.php' );
}

/**
 * List table for viewing action hook data by BugNet.
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Views
 * @version     1.0
 */
class TwitchPress_ListTable_ActionHooks extends WP_List_Table {

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
            'singular'  => __( 'Action', 'twitchpress' ),
            'plural'    => __( 'Actions', 'twitchpress' ),
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
        $entry_counter = 0;// Acts as temporary ID for data that does not have one. 
        
        // Get data for GET requests. 
        $action_history = get_transient( 'bugnet_wpaction' );
        if( empty( $action_history ) || !is_array( $action_history ) ) {
            $action_history = array();            
        } 

        // Loop on individual trace entries. 
        foreach( $action_history as $key => $entry ) 
        {
            // Extract the TwitchPress hooks. 
            $twitchpress_actions = array();
            foreach( $entry['actions'] as $key => $hook_priority ) 
            {                                  
                if( stristr( $key, 'twitch' ) ) 
                {
                    $twitchpress_actions[] = $key;    
                }    
            }   
                                    
            // Count number of items entered into the table.
            ++$entry_counter;// to check the order of items prior to searches or user sorting.
            $this->items[] = array( 
                'entry_number' => $entry_counter,
                'time'         => $entry['time'],
                'actions'      => $entry['actions'], 
                'twitchpress_actions'      => $twitchpress_actions 
            );
                       
        }
    }
    
    /**
     * No items found text.
     */
    public function no_items() {
        if( 'yes' !== get_option( 'twitchpress_bugnet_cache_action_hooks' ) )
        {
            _e( 'Action hook caching has not been activated in the BugNet settings.');
            return;    
        }
        
        _e( 'No applicable items found.', 'twitchpress' );
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

                $time_passed = human_time_diff( $item['time'], time() );
                echo sprintf( __( '%s ago', 'twitchpress' ), $time_passed );         
              
            break;      
  
            case 'actions' :
                echo '<textarea rows="4" cols="80">' . print_r( $item['actions'], true ) . '</textarea>';
            break;            
            
            case 'twitchpress_actions' :
                echo '<textarea rows="4" cols="80">' . print_r( $item['twitchpress_actions'], true ) . '</textarea>';
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
            'time'    => __( 'Date/Time', 'twitchpress' ),
            'actions' => __( 'Action Hooks', 'twitchpress' ),
            'twitchpress_actions' => __( 'TwitchPress Hooks', 'twitchpress' ),
        );
            
        return $columns;
    }

    /**
     * Prepare customer list items.
     */
    public function prepare_items() {

        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
        $current_page          = absint( $this->get_pagenum() );
        $per_page              = apply_filters( 'twitchpress_listtable_actionhooks_items_per_page', 20 );

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