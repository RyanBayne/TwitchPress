<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class.wp-list-table.php' );
}

/**
 * TwitchPress_ListTable_Subscribers.
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Views
 * @version     1.0.0
 */
class TwitchPress_ListTable_Subscribers extends WP_List_Table {

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
        $entry_counter = 0;// Acts as temporary ID for data that does not have one. 
              
        $meta_key = 'twitchpress_sub_plan_' . twitchpress_get_main_channels_twitchid();

        global $wpdb;
        $users = $wpdb->get_results( 
            "SELECT {$wpdb->users}.*, {$wpdb->usermeta}.meta_value as roles FROM {$wpdb->users} 
            LEFT JOIN {$wpdb->usermeta} ON {$wpdb->users}.ID = {$wpdb->usermeta}.user_id
            WHERE {$wpdb->usermeta}.meta_key = '{$meta_key}'
            ORDER BY {$wpdb->users}.display_name"
        );

          echo '<pre>';
          var_dump($users);
          echo '</pre>';



        // Loop on individual trace entries. 
        foreach( $users as $user ) {

            ++$entry_counter;
            
            // Create a new array.
            $this->items[]['entry_counter'] = $entry_counter; 

            // Get the new array key we just created. 
            end( $this->items);
            $new_key = key( $this->items );

            // Add the items data to the array we just created.
            $this->items[$new_key]['wpuserid']        = $users[0]->data->ID; 
            $this->items[$new_key]['user_nicename']   = $users[0]->data->user_nicename; 
            $this->items[$new_key]['user_registered'] = $users[0]->data->user_registered; 
            $this->items[$new_key]['user_email']      = $users[0]->data->user_email; 
            $this->items[$new_key]['user_url']        = $users[0]->data->user_url; 
            $this->items[$new_key]['display_name']    = $users[0]->data->display_name;  
            $this->items[$new_key]['role']            = $users[0]->roles[0]; 
                        
            $this->items = array_reverse( $this->items );       
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
 
            case 'entry_counter' :
                echo $item['entry_counter'];   
            break;

            case 'wpuserid' :
                echo $item['wpuserid'];   
            break;

            case 'user_nicename' :
                echo $item['user_nicename'];   
            break;

            case 'display_name' :
                echo $item['display_name'];   
            break;

            case 'user_email' :
                echo $item['user_email'];   
            break;
            
            case 'user_registered' :
                                       
                $time_passed = human_time_diff(  strtotime( $item['user_registered'] ), time() );
                echo sprintf( __( '%s ago', 'twitchpress' ), $time_passed );         

            break;

            case 'role' :
                echo $item['role'];   
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
            'entry_counter'   => __( 'Query ID', 'twitchpress' ),        
            'wpuserid'        => __( 'WP User ID', 'twitchpress' ),
            'display_name'    => __( 'Username', 'twitchpress' ),
            'user_email'      => __( 'Email Address', 'twitchpress' ),
            'user_registered' => __( 'Registered', 'twitchpress' ),
            'role'            => __( 'Role', 'twitchpress' ),
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