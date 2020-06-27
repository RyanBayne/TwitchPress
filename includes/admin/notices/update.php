<?php
/**
 * Admin View: Notice - Update
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div id="message" class="updated twitchpress-message twitchpress-connect">
    <p><strong><?php _e( 'TwitchPress Data Update', 'twitchpress' ); ?></strong> &#8211; <?php _e( 'We need to update your store\'s database to the latest version.', 'twitchpress' ); ?></p>
    <p class="submit"><a href="<?php echo esc_url( add_query_arg( 'do_update_twitchpress', 'true', admin_url( 'admin.php?page=twitchpress' ) ) ); ?>" class="twitchpress-update-now button-primary"><?php _e( 'Run the updater', 'twitchpress' ); ?></a></p>
</div>
<script type="text/javascript">
    jQuery( '.twitchpress-update-now' ).click( 'click', function() {
        return window.confirm( '<?php echo esc_js( __( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'twitchpress' ) ); ?>' ); // jshint ignore:line
    });
</script>
