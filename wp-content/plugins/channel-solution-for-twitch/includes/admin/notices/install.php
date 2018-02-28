<?php
/**
 * Admin View: Notice - Install with wizard start button.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div id="message" class="updated twitchpress-message twitchpress-connect">
    <p><?php _e( '<strong>Welcome to WordPress TwitchPress</strong> &#8211; You&lsquo;re almost ready to begin using the plugin.', 'twitchpress' ); ?></p>
    <p class="submit"><a href="<?php echo esc_url( admin_url( 'admin.php?page=twitchpress-setup' ) ); ?>" class="button-primary"><?php _e( 'Run the Setup Wizard', 'twitchpress' ); ?></a> <a class="button-secondary skip" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'twitchpress-hide-notice', 'install' ), 'twitchpress_hide_notices_nonce', '_twitchpress_notice_nonce' ) ); ?>"><?php _e( 'Skip Setup', 'twitchpress' ); ?></a></p>
</div>
