<?php
/**
 * TwitchPress Ajax Event Handler.
 *                           
 * @package  TwitchPress/Core
 * @category Ajax
 * @author   Ryan Bayne
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class TwitchPress_AJAX {

    /**
     * Hook in ajax handlers.
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'define_ajax' ), 0 );
        add_action( 'template_redirect', array( __CLASS__, 'do_twitchpress_ajax' ), 0 );
        self::add_ajax_events();
    }

    /**
     * Get TwitchPress Ajax Endpoint.
     * @param  string $request Optional
     * @return string
     */
    public static function get_endpoint( $request = '' ) {
        return esc_url_raw( apply_filters( 'twitchpress_ajax_get_endpoint', add_query_arg( 'twitchpress-ajax', $request, remove_query_arg( array( 'remove_item', 'add-to-cart', 'added-to-cart' ) ) ), $request ) );
    }

    /**
     * Set TwitchPress AJAX constant and headers.
     */
    public static function define_ajax() {
        if ( ! empty( $_GET['twitchpress-ajax'] ) ) {
            if ( ! defined( 'DOING_AJAX' ) ) {
                define( 'DOING_AJAX', true );
            }
            if ( ! defined( 'TWITCHPRESS_DOING_AJAX' ) ) {
                define( 'TWITCHPRESS_DOING_AJAX', true );
            }
            // Turn off display_errors during AJAX events to prevent malformed JSON
            if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
                @ini_set( 'display_errors', 0 );
            }
            $GLOBALS['wpdb']->hide_errors();
        }
    }

    /**
     * Send headers for TwitchPress Ajax Requests
     */
    private static function twitchpress_ajax_headers() {
        send_origin_headers();
        @header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
        @header( 'X-Robots-Tag: noindex' );
        send_nosniff_header();
        nocache_headers();
        status_header( 200 );
    }

    /**
     * Check for TwitchPress Ajax request and fire action.
     */
    public static function do_twitchpress_ajax() {
        global $wp_query;

        if ( ! empty( $_GET['twitchpress-ajax'] ) ) {
            $wp_query->set( 'twitchpress-ajax', sanitize_text_field( $_GET['twitchpress-ajax'] ) );
        }

        if ( $action = $wp_query->get( 'twitchpress-ajax' ) ) {
            self::twitchpress_ajax_headers();
            do_action( 'twitchpress_ajax_' . sanitize_text_field( $action ) );
            die();
        }
    }

    /**
     * Hook in methods - uses WordPress ajax handlers (admin-ajax).
     */
    public static function add_ajax_events() {
        // twitchpress_EVENT => nopriv
        $ajax_events = array();

        foreach ( $ajax_events as $ajax_event => $nopriv ) {
            add_action( 'wp_ajax_twitchpress_' . $ajax_event, array( __CLASS__, $ajax_event ) );

            if ( $nopriv ) {
                add_action( 'wp_ajax_nopriv_twitchpress_' . $ajax_event, array( __CLASS__, $ajax_event ) );

                // TwitchPress AJAX can be used for frontend ajax requests
                add_action( 'twitchpress_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
            }
        }
    }
}

TwitchPress_AJAX::init();
