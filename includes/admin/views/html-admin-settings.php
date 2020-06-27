<?php
/**
 * Admin View: Settings
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}                                       
?>
        
<div class="wrap twitchpress">
    <h1><?php _e( 'TwitchPress Settings', 'twitchpress' ); ?></h1>
    <form method="<?php echo esc_attr( apply_filters( 'twitchpress_settings_form_method_tab_' . $current_tab, 'post' ) ); ?>" id="mainform" action="" enctype="multipart/form-data">
        <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
            <?php
                foreach ( $tabs as $name => $label ) {
                    echo '<a href="' . admin_url( 'admin.php?page=twitchpress&tab=' . $name ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
                }
                do_action( 'twitchpress_settings_tabs' );
            ?>
        </nav>
        <h1 class="screen-reader-text"><?php echo esc_html( $tabs[ $current_tab ] ); ?></h1>
        <?php
            do_action( 'twitchpress_sections_' . $current_tab );

            self::show_messages();

            do_action( 'twitchpress_settings_' . $current_tab );
            do_action( 'twitchpress_settings_tabs_' . $current_tab ); // @deprecated hook
            
        ?>
        <p class="submit">
            <?php if ( empty( $GLOBALS['hide_save_button'] ) ) : ?>
                <input name="save" class="button-primary twitchpress-save-button" type="submit" value="<?php esc_attr_e( $save_button_text, 'twitchpress' ); ?>" />
            <?php endif; ?>
            <?php wp_nonce_field( 'twitchpress-settings' ); ?>
        </p>
    </form>
</div>