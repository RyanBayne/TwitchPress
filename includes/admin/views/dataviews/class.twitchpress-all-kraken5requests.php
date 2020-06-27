<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'TwitchPress_ListTable_Krakencalls' ) ) {
    require_once( 'class.twitchpress-listtable-krakencalls.php' );
}

/**
 * TwitchPress_ListTable_Demo_BugNet usually showing ALL items without filter.
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
class TwitchPress_DataView_All_Kraken5Requests extends TwitchPress_ListTable_Krakencalls {
   
    public $checkbox_column = true;
    
    /**
     * No items found text.
     */
    public function no_items() {
        _e( 'No applicable items found.', 'twitchpress' );
    }

    /**
     * Filter or add to the main data result established in the parent class.
     * Only return the items that apply to this sub view. 
     * 
     * This includes processing bulk actions but there are many approaches
     * you could take. If you want to apply a filter that does not delete
     * records for example. Then simply move the process_bulk_actions()
     * before everything else. 
     * 
     * See parent::query_items() for where we establish the first $items->object. 
     * That approach can also be adapted or removed.
     *
     * @param int $current_page
     * @param int $per_page
     */
    public function get_items( $current_page, $per_page ) {
        global $wpdb;
        
        // Filter $this->items to create a dataset suitable for this view.
        //$this->items     = array();  
        
        $this->process_bulk_actions();        
    }
    
    /**
    * Adds a column of checkboxes for use with bulk actions.
    */
    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="exampleitem[]" value="%s" />', $item['headerone']
        );    
    }    
    
    /**
    * Add options to the bulk actions menu.
    */
    public function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }

    /**
    * Process bulk actions. This can also be done in the parent
    * 
    * Thank you to the WordPress addict Ralf912 for the answer that built my method.
    * 
    * @link http://wordpress.stackexchange.com/users/16589/ralf912
    * 
    * Here is the question and answer.
    * 
    * @link http://wordpress.stackexchange.com/questions/76374/wp-list-tables-bulk-actions 
    */
    public function process_bulk_actions() {
                
        if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

            $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
            $action = 'bulk-' . $this->_args['plural'];

            if ( ! wp_verify_nonce( $nonce, $action ) )
                wp_die( 'Nope! Security check for a bulk action has failed!' );
        }

        $action = $this->current_action();

        // This is the top (or the only) bulk action if there is no bottom menu.
        $action = ( isset( $_POST['action'] ) ) ?
            filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRIPPED ) : 'default_top_bulk_action';

        // This is the bottom (or second) bulk action if there are two bulk action menus.
        $action2 = ( isset( $_POST['action2'] ) ) ? 
            filter_input( INPUT_POST, 'action2', FILTER_SANITIZE_STRIPPED ) : 'default_bottom_bulk_action';
        
        switch ( $action ) {

            case 'delete':
                // This demonstrates how deletion might appear once processing is finished.
                if( isset( $_POST['exampleitem'] ) ) {
                    foreach( $_POST['exampleitem'] as $key => $item ) {
                        unset( $this->items[ $key ] );
                    }
                }
                break;

            case 'save':
                wp_die( 'Save something' );
                break;

            default:
                // do nothing or something else
                return;
                break;
        }
        
        switch ( $action2 ) {}

    }
}