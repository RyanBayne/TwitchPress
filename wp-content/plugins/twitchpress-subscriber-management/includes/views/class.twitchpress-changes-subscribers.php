<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * TwitchPress Data View for recent changes to Subscribers.   
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Views
 * @version     1.0.0
 */
class TwitchPress_DataView_Changes_Subscribers extends WP_List_Table {
   
    public $checkbox_column = true;

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
            'singular'  => __( 'Twitch Subscriber', 'wpseed' ),
            'plural'    => __( 'Twitch Subscribers', 'wpseed' ),
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
        global $wpdb;
        
        $entry_counter = 0;// Acts as temporary ID for data that does not have one. 
              
        $recent_changes = get_option( 'twitchpress_history' );
        
        $recent_changes = array();
        $recent_changes[] = array( 
            'wpuserid'      => '1', 
            'user_nicename' => 'ZypheREvolved',
            'new_plan'      => '3000',
            'old_plan'      => '1000',
            'change_time'   => time()
        );
  
        if( !$recent_changes ) { return array(); }
        
        // Loop on individual trace entries. 
        foreach( $recent_changes as $change ) {

            ++$entry_counter;
            
            // Create a new array.
            $this->items[]['entry_counter'] = $entry_counter; 

            // Get the new array key we just created. 
            end( $this->items);
            $new_key = key( $this->items );
 
            // Add the items data to the array we just created.
            $this->items[$new_key]['wpuserid']      = $change['wpuserid'];  
            $this->items[$new_key]['user_nicename'] = $change['user_nicename'];  
            $this->items[$new_key]['new_plan']      = $change['new_plan'];  
            $this->items[$new_key]['old_plan']      = $change['old_plan'];  
            $this->items[$new_key]['change_time']   = $change['change_time'];  
          
            $this->items = array_reverse( $this->items );       
        }
    }
    
    /**
     * No items found text.
     */
    public function no_items() {
        _e( 'No recent changes found.', 'wpseed' );
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
 
            case 'entry_counter' :
                echo $item['entry_counter'];   
            break;

            case 'wpuserid' :
                echo $item['wpuserid'];   
            break;

            case 'user_nicename' :
                echo $item['user_nicename'];   
            break;

            case 'new_plan' :
                echo $item['new_plan'];   
            break;

            case 'old_plan' :
                echo $item['old_plan'];   
            break;
            
            case 'change_time' :
                                       
                $time_passed = human_time_diff(  $item['change_time'] , time() );
                echo sprintf( __( '%s ago', 'twitchpress' ), $time_passed );         

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
            'entry_counter' => __( 'Query ID', 'twitchpress' ),        
            'wpuserid'      => __( 'WP User ID', 'twitchpress' ),
            'new_plan'      => __( 'New Plan', 'twitchpress' ),
            'old_plan'      => __( 'Old Plan', 'twitchpress' ),
            'change_time'   => __( 'Change Time', 'twitchpress' ),
        );
            
        return $columns;
    }

    /**
     * Prepare customer list items.
     */
    public function prepare_items() {

        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
        $current_page          = absint( $this->get_pagenum() );
        $per_page              = apply_filters( 'twitchpress_listtable_subscribers_items_per_page', 20 );

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