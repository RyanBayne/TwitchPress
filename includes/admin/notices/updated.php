<?php
/**
 * Admin View: Notice - Updated
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div id="message" class="updated twitchpress-message twitchpress-connect">
    <a class="twitchpress-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'twitchpress-hide-notice', 'update', remove_query_arg( 'do_update_twitchpress' ) ), 'twitchpress_hide_notices_nonce', '_twitchpress_notice_nonce' ) ); ?>"><?php _e( 'Dismiss', 'twitchpress' ); ?></a>

    <p><?php _e( 'TwitchPress data update complete. Thank you for updating to the latest version!', 'twitchpress' ); ?></p>
</div>
