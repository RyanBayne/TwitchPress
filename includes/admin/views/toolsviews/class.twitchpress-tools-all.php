<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'TwitchPress_ListTable_Tools' ) ) {
    require_once( 'class.twitchpress-listtable-tools.php' );
}

/**
 * TwitchPress_QuickTools_All  
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Admin
 * @version     1.0.0
 */
class TwitchPress_Tools_All extends TwitchPress_ListTable_Tools {

    /**
     * No items found text.
     */
    public function no_items() {
        _e( 'No tools were found which must be a fault. Please report this message.', 'twitchpress' );
    }

    /**
     * Filter the main data result and only return the items that apply
     * to this report.
     *
     * @param int $current_page
     * @param int $per_page
     */
    public function get_items( $current_page, $per_page ) {         
    }
}