<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class.wp-list-table.php' );
}

/**
 * TwitchPress_ListTable_Stock.
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Admin/MainViews
 * @version     1.0.0
 */
class TwitchPress_ListTable_Demo extends WP_List_Table {

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
			'singular'  => __( 'Stock', 'twitchpress' ),
			'plural'    => __( 'Stock', 'twitchpress' ),
			'ajax'      => false
		) );
        
        // Perform query or set default items.
        $this->query_items();
	}

    /**
    * Setup default items. 
    * 
    * This is not required and was only implemented for demonstration purposes. 
    */
    public function query_items() {
        
        $this->items[0]['headerone']    = 'Alpha';
        $this->items[0]['headertwo']    = 'Bravo';
        $this->items[0]['headerthree']  = 'Charlie';
        $this->items[0]['headerfour']   = 'Delta';
        
        $this->items[1]['headerone']    = 'Cats';
        $this->items[1]['headertwo']    = 'Dogs';
        $this->items[1]['headerthree']  = 'Rabbits';
        $this->items[1]['headerfour']   = 'Dinosaurs';
        
        $this->items[2]['headerone']    = 'Steak';
        $this->items[2]['headertwo']    = 'Sausages';
        $this->items[2]['headerthree']  = 'Bacon';
        $this->items[2]['headerfour']   = 'Burger';
        
        $this->items[3]['headerone']    = 'Potato';
        $this->items[3]['headertwo']    = 'Raddish';
        $this->items[3]['headerthree']  = 'Lettuce';
        $this->items[3]['headerfour']   = 'Onion';    
    }
    
	/**
	 * No items found text.
	 */
	public function no_items() {
		_e( 'No items found.', 'twitchpress' );
	}

	/**
	 * Control what table navigation is displayed.
	 *
	 * @param string $position
	 */
	public function display_tablenav( $position ) {
        // Avoid displaying the top navigation and make tidier space for sub view links.
		if ( $position != 'top' ) {
			parent::display_tablenav( $position );
		}
	}

	/**
	 * Output the report.
	 */
	public function output_result() {

		$this->prepare_items();
		echo '<div id="poststuff" class="twitchpress-tables-wide">';
        echo '<form id="twitchpress-list-table-form-demo" method="post">';
		$this->display();
        echo '<form>';
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

			case 'headerone' :
				echo $item['headerone'];
			break;

			case 'headertwo' :
				echo $item['headertwo'];
			break;

			case 'headerthree' :
				echo $item['headerthree'];
			break;

			case 'headerfour' :
				echo $item['headerfour'];
			break;

		}
	}
    
    /**
    * Adds a column of checkboxes for use with bulk actions.
    */
    public function column_cb( $item ) {
        // The display is controlled within the sub view files. 
    }    

	/**
	 * Get columns.
	 *
	 * @return array
	 */
	public function get_columns() {
        
		$columns = array(
            'headerone'      => __( 'Header One', 'twitchpress' ),
			'headertwo'       => __( 'Header Two', 'twitchpress' ),
			'headerthree'  => __( 'Header Three', 'twitchpress' ),
			'headerfour' => __( 'Header Four', 'twitchpress' ),
		);

        // Sub view might offer bulk actions and require checkbox column. 
        if( isset( $this->checkbox_column ) && $this->checkbox_column === true ) {
            $cb = array( 'cb' => __( '<input type="checkbox" />', 'twitchpress' ) );
            $columns = array_merge( $cb, $columns );    
        }
        
		return $columns;
	}

	/**
	 * Prepare customer list items further. Does not get the items. It only
     * prepares them for the specific views presentation configuration i.e. pagination.
	 */
	public function prepare_items() {

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		$current_page          = absint( $this->get_pagenum() );
		$per_page              = apply_filters( 'twitchpress_admin_list_table_demo_items_per_page', 20 );

		$this->get_items( $current_page, $per_page );

        // Process bulk actions.
        //$this->process_bulk_action();
              
		/**
		 * Pagination.
		 */
		$this->set_pagination_args( array(
			'total_items' => $this->max_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $this->max_items / $per_page )
		) );
	}
    
    /**
    * Process bulk actions selected in two possible menus. 
    */
    public function process_bulk_actions() {
        // Processing is handled by each sub file.     
    }
}
