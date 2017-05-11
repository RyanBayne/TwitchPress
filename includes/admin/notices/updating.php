<?php
/**
 * Admin View: Notice - Updating
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div id="message" class="updated twitchpress-message twitchpress-connect">
    <p><strong><?php _e( 'TwitchPress Data Update', 'twitchpress' ); ?></strong> &#8211; <?php _e( 'Your database is being updated in the background.', 'twitchpress' ); ?> <a href="<?php echo esc_url( add_query_arg( 'force_update_twitchpress', 'true', admin_url( 'admin.php?page=twitchpress-settings' ) ) ); ?>"><?php _e( 'Taking a while? Click here to run it now.', 'twitchpress' ); ?></a></p>
</div>
