<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class.wp-list-table.php' );
}

/**
 * TwitchPress_ListTable_Demo_BugNet.
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Views
 * @version     1.0.0
 */
class TwitchPress_ListTable_Demo_BugNet extends WP_List_Table {

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
			'singular'  => __( 'Item', 'wpseed' ),
			'plural'    => __( 'Items', 'wpseed' ),
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
    * @version 1.0
    */
    public function default_items() {
        $this->items = $this->get_traces();
    }
    
    public function get_traces() {
        global $bugnet;

        $all_traces = $bugnet->handler_tracing->get_longterm_transient_traces();
       
        $entry_counter = 0;
  
        foreach( $all_traces as $tag => $in_tag_events ) {
         
            // Loop on the separate events for the current trace. 
            foreach( $in_tag_events as $event_id => $event_array ) {
                 
                // Loop on individual trace entries. 
                foreach( $event_array['entries'] as $key => $entry ) {
 
                    ++$entry_counter;
                    
                    $items[]['entry_number'] = $entry_counter; 
                    
                    // Get the new array key we just created.
                    end($items);
                    $new_key = key($items);

                    // PHP __LINE__ integer. 
                    $items[$new_key]['line'] = $entry['line'];
                    
                    // PHP __FUNCTION__
                    $items[$new_key]['function'] = $entry['function'];
                    
                    // PHP __CLASS__
                    $items[$new_key]['class'] = $entry['class'];
                
                    // PHP __FILE__ string.
                    $items[$new_key]['file'] = $entry['file'];
                    
                    // Entry message string.
                    $items[$new_key]['message'] = $entry['message'];
                    
                    // Time of entry example: int 1503562573
                    $items[$new_key]['time'] = $entry['time'];
                    
                    // Event identification (not entry) example: int 199273                               
                    $items[$new_key]['eventid'] = $event_id;
               
                    // Limit the displayed entries. 
                    if( $entry_counter > 49 ) {
                        return $items;
                    }               
                }              
         
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
	 */
	public function column_default( $item, $column_name ) {

		switch( $column_name ) {
            case 'time' :
                $time_passed = human_time_diff( $item['time'], time() );
                echo sprintf( __( '%s ago', 'twitchpress' ), $time_passed );
            break;
            
            case 'message' :
                echo $item['message'];
            break;
            
			case 'line' :
				echo $item['line'];
			break;

			case 'function' :
				echo $item['function'];
			break;

			case 'class' :
				echo $item['class'];
			break;

            case 'file' :
                echo $item['file'];
            break;

            case 'eventid' :
                echo $item['eventid'];
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
            'time'     => __( 'Time', 'wpseed' ),
            'message'  => __( 'Message', 'wpseed' ),           
			'line'     => __( 'Line', 'wpseed' ),
			'function' => __( 'Function', 'wpseed' ),
			'class'    => __( 'Class', 'wpseed' ),
            'file'     => __( 'File', 'wpseed' ),
            'eventid'  => __( 'Event ID', 'wpseed' ),
		);

		return $columns;
	}

	/**
	 * Prepare customer list items.
	 */
	public function prepare_items() {

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		$current_page          = absint( $this->get_pagenum() );
		$per_page              = apply_filters( 'twitchpress_admin_list_table_demo_items_per_page', 20 );

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
