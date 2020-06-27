<?php
/**
 * TwitchPress - Santization Functions
 *
 * @author   Ryan Bayne
 * @category Security
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sanitize taxonomy names. Slug format (no spaces, lowercase).
 *
 * urldecode is used to reverse munging of UTF8 characters.
 *
 * @param mixed $taxonomy
 * @return string
 */
function twitchpress_sanitize_taxonomy_name( $taxonomy ) {
    return apply_filters( 'sanitize_taxonomy_name', urldecode( sanitize_title( urldecode( $taxonomy ) ) ), $taxonomy );
}

/**
 * Sanitize permalink values before insertion into DB.
 *
 * Cannot use twitchpress_clean because it sometimes strips % chars and breaks the user's setting.
 *
 * @param  string $value
 * @return string
 */                                              
function twitchpress_sanitize_permalink( $value ) {       
    global $wpdb;

    $value = $wpdb->strip_invalid_text_for_column( $wpdb->options, 'option_value', $value );

    if ( is_wp_error( $value ) ) {
        $value = '';
    }

    $value = esc_url_raw( $value );
    $value = str_replace( 'http://', '', $value );
    return untrailingslashit( $value );
}

/**
 * Sanitize a string destined to be a tooltip.
 *
 * @param string $var
 * @return string
 */
function twitchpress_sanitize_tooltip( $var ) { 
    // Tooltips are encoded with htmlspecialchars to prevent XSS. Should not be used in conjunction with esc_attr()    
    return htmlspecialchars( wp_kses( html_entity_decode( $var ), array(
        'br'     => array(),
        'em'     => array(),
        'strong' => array(),
        'small'  => array(),
        'span'   => array(),
        'ul'     => array(),
        'li'     => array(),
        'ol'     => array(),
        'p'      => array(),
    ) ) );
}
