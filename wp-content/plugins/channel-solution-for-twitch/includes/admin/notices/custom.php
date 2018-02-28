<?php
/**
 * Admin View: Custom Notices
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div id="message" class="updated twitchpress-message">
    <a class="twitchpress-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'twitchpress-hide-notice', $notice ), 'twitchpress_hide_notices_nonce', '_twitchpress_notice_nonce' ) ); ?>"><?php _e( 'Dismiss', 'twitchpress' ); ?></a>
    <?php echo wp_kses_post( wpautop( $notice_html ) ); ?>
</div>
