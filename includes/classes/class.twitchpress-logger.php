<?php
/**
 * TwitchPress - Primary Logging Interface
 *
 * This class is not final. There is a task in the projects Trello for 
 * version 2.0 which details a far better log and trace system.
 *
 * @author   Ryan Bayne
 * @category Core
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TwitchPress_Logger extends WordPressTwitchPress {

    /**
     * Stores open file _handles.
     *
     * @var array
     * @access private
     */
    private $_handles;

    /**
     * Constructor for the logger.
     */
    public function __construct() {
        //$this->_handles = array();
    }

    /**
     * Destructor.
     */
    public function __destruct() {
        
    }

    /**
     * Open log file for writing.
     *
     * @param string $handle
     * @param string $mode
     * @return bool success
     */
    protected function open( $handle, $mode = 'a' ) {
        return;
    }

    /**
     * Close a handle.
     *
     * @param string $handle
     * @return bool success
     */
    protected function close( $handle ) {
        return;
    }

    /**
     * Add a log entry to chosen file.
     *
     * @param string $handle
     * @param string $message
     *
     * @return bool
     */
    public function add( $handle, $message ) {
        return;
    }

    /**
     * Clear entries from chosen file.
     *
     * @param string $handle
     *
     * @return bool
     */
    public function clear( $handle ) {
        return;
    }
}
