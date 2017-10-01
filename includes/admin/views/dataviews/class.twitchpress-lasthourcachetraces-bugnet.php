<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'TwitchPress_ListTable_Demo_BugNet' ) ) {
    require_once( 'class.twitchpress-listtable-demo-bugnet.php' );
}

/**
 * TwitchPress_DataView_LastHourCacheTraces_BugNet.
 * 
 * This is one of multiple classes that extends a parent class which builds
 * the table. This approach essentially splits a table into common views just as if
 * a search criteria was entered.  
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Views
 * @version     1.0.0
 */
class TwitchPress_DataView_LastHourCacheTraces_BugNet extends TwitchPress_ListTable_Demo_BugNet {

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
        unset($this->items[0],$this->items[2],$this->items[3]);          
    }
}