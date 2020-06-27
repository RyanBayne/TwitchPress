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
			'singular'  => __( 'Trace', 'twitchpress' ),
			'plural'    => __( 'Traces', 'twitchpress' ),
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
        if( !get_option( 'bugnet_version' ) ) {return;}
        
        global $wpdb;

        //$all_traces = twitchpress_db_selectwherearray( $wpdb->bugnet_tracing, );
        $all_traces = bugnet_get_traces( null, null, '*', 50, ARRAY_A );
        
        if( !$all_traces ) { return false; }
                  
        $entry_counter = 0;
  
        foreach( $all_traces as $tag => $entry ) {

            ++$entry_counter;
            
            $items[]['entry_number'] = $entry_counter; 
            
            // Get the new array key we just created.
            end($items);
            $new_key = key($items);

            $items[$new_key]['id'] = $entry['id'];
            $items[$new_key]['name'] = $entry['name'];
            $items[$new_key]['line'] = $entry['line'];
            $items[$new_key]['function'] = $entry['function'];
            $items[$new_key]['code'] = $entry['code'];// Dev created code also applied to meta
            $items[$new_key]['time'] = $entry['timestamp'];
        }
   
        return $items;
    }
    
	/**
	 * No items found text.
	 */
	public function no_items() {
		_e( 'No traces were found.', 'twitchpress' );
	}

	/**
	 * Don't need this.
	 *
	 * @param string $position
	 */
	public function display_tablenav( $position ) {
        if( !get_option( 'bugnet_version' ) ) {return;}
        
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
            return;
        }
        
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
                $time_passed = human_time_diff( strtotime( $item['time'] ), time() );
                echo sprintf( __( '%s ago', 'twitchpress' ), $time_passed );
            break;
            
            case 'name' :
                echo $item['name'];
            break;
            
			case 'line' :
				echo $item['line'];
			break;

			case 'function' :
				echo $item['function'];
			break;

            case 'code' :
                echo $item['code'];
            break;
            
            case 'link' :

                $url   = self_admin_url( 'admin.php?page=twitchpress-traces&trace_id=' . $item['id'] );   
                echo '<a href="' . $url . '" class="button button-primary">' . __( 'View', 'twitchpress' ) . '</a>';
     
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
            'time'     => __( 'Time', 'twitchpress' ),
            'name'     => __( 'Name', 'twitchpress' ),           
			'line'     => __( 'Line', 'twitchpress' ),
			'function' => __( 'Function', 'twitchpress' ),
            'code'     => __( 'Code', 'twitchpress' ),
            'link'     => __( 'Link', 'twitchpress' ),
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
