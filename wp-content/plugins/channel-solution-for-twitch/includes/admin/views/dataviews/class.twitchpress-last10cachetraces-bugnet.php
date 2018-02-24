<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'TwitchPress_ListTable_Stuff' ) ) {
    require_once( 'class.twitchpress-listtable-demo-bugnet.php' );
}

/**
 * TwitchPress_DataView_Last10CacheTraces_BugNet.
 * 
 * This is one of multiple classes that extends a parent class which builds
 * the table. This approach essentially splits a table into common views just as if
 * a search criteria was entered.  
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     WPSeed/Admin
 * @version     1.0.0
 */
class TwitchPress_DataView_Last10CacheTraces_BugNet extends TwitchPress_ListTable_Demo_BugNet {

    /**
     * No items found text.
     */
    public function no_items() {
        _e( 'No applicable items found.', 'wpseed' );
    }

    /**
     * Filter the main data result and only return the items that apply
     * to this report.
     *
     * @param int $current_page
     * @param int $per_page
     */
    public function get_items( $current_page, $per_page ) {
        global $wpdb;
                
        // Filter $this->items to create a dataset suitable for this view.
        unset($this->items[0],$this->items[1]); 
        
        // Order by sorting columns.
        if( isset( $_GET['orderby'] ) && isset( $_GET['order'] ) ) {
            switch ( $_GET['orderby'] ) {
                case 'headerone':
                
                    foreach ( $this->items as $key => $row ) {
                        $headerone[$key] = $row['headerone'];
                    }

                    if( $_GET['order'] == 'asc' ) {
                        array_multisort( $headerone, SORT_ASC, $this->items );    
                    } else {
                        array_multisort( $headerone, SORT_DESC, $this->items );
                    }
         
                break;
                case 'headertwo':

                break;
                case 'headerthree':

                break;
                case 'headerfour':
                
                break;
            }            
        }                 
    }
    
    public function get_sortable_columns() {
        $sortable_columns = array(
            'headerone'  => array( 'headerone',false )
        );
        return $sortable_columns;
    }
}