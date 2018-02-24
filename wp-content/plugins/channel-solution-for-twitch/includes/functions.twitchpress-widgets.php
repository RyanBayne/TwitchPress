<?php
/**
 * TwitchPress - Primary Sidebar Widgets File
 *
 * @author   Ryan Bayne
 * @category Widgets
 * @package  TwitchPress/Widgets
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include widget classes.
//include_once( 'abstracts/abstract-twitchpress-widget.php' );

/**
 * Register Widgets.
 */
function twitchpress_register_widgets() {
    //register_widget( 'TwitchPress_Widget_Example' );
}
add_action( 'widgets_init', 'twitchpress_register_widgets' );