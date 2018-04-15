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
			'singular'  => __( 'Item', 'twitchpress' ),
			'plural'    => __( 'Items', 'twitchpress' ),
			'ajax'      => false
		) );
        
        // Apply default items to the $items object.
        $this->default_items();
	}

    /**
    * Setup default items. 
    * 
    * This is not required and was only implemented for demonstration purposes. 
    */
    public function default_items() {
        
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
		echo '<div id="poststuff" class="twitchpress-tables-wide">';
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
