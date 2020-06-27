<?php
/**
* TwitchPress notice layout styled like WordPress core: is not dismissable
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div id="message" class="updated twitchpress-message">
    <?php echo wp_kses_post( wpautop( $notice_html ) ); ?>
</div>
