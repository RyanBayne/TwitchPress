<?php
/**
 * Admin Views Default Structure 
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}    
                        
?>
<div class="wrap twitchpress">

    <?php            
    // Establish Title
    $title = '';
    if( !isset( $_GET['listtable'] ) ) {
        // User did not click sub view so use first sub view data.
        $title = array_values($tabs[ $current_tab ]['toolstabviews'])[0]['title'];
    } else {
        $title = $tabs[ $current_tab ]['toolstabviews'][ $_GET['twitchpressview'] ]['title'];    
    }
    
    echo '<h1>TwitchPress: ' . esc_html( $title ) . '</h1>'; 
    ?>
    
    <!-- TABS -->
    <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
        <?php
            foreach ( $tabs as $key => $report_group ) {
                echo '<a href="' . admin_url( 'admin.php?page=twitchpress_tools&tab=' . urlencode( $key ) ) . '" class="nav-tab ';
                if ( $current_tab == $key ) {
                    echo 'nav-tab-active';
                }
                echo '">' . esc_html( $report_group[ 'title' ] ) . '</a>';
            }

            do_action( 'twitchpress_toolsview_tabs' );
        ?>
    </nav>
    
    
    <?php if ( sizeof( $tabs[ $current_tab ]['toolstabviews'] ) > 1 ) { ?>
        <!-- SUB VIEWS (within selected tab) -->
        <ul class="subsubsub">
            <li><?php

                $links = array();

                foreach ( $tabs[ $current_tab ]['toolstabviews'] as $key => $tab ) {

                    $link = '<a href="admin.php?page=twitchpress&tab=' . urlencode( $current_tab ) . '&amp;seedview=' . urlencode( $key ) . '" class="';
  
                    if ( $key == $current_tablelist ) {
                        $link .= 'current';
                    }

                    $link .= '">' . $tab['title'] . '</a>';

                    $links[] = $link;

                }

                echo implode( ' | </li><li>', $links );

            ?></li>
        </ul>
        <br class="clear" />
        <?php
    }

    if ( isset( $tabs[ $current_tab ][ 'toolstabviews' ][ $current_tablelist ] ) ) {

        $tabs = $tabs[ $current_tab ][ 'toolstabviews' ][ $current_tablelist ];

        if ( ! isset( $tabs['hide_title'] ) || $tabs['hide_title'] != true ) {
            echo '<h1>' . esc_html( $tabs['title'] ) . '</h1>';
        } else {
            echo '<h1 class="screen-reader-text">' . esc_html( $tabs['title'] ) . '</h1>';
        }

        if ( $tabs['description'] ) {
            echo '<p>' . $tabs['description'] . '</p>';
        }

        if ( $tabs['callback'] && ( is_callable( $tabs['callback'] ) ) ) {
            call_user_func( $tabs['callback'], $current_tablelist );
        }
    }
    ?>
</div>
