<?php  
/**
 * TwitchPress - Pointers
 *
 * Manage multiple step tutorial like process using WP core points.  
 *
 * @author   Ryan Bayne
 * @category Support
 * @package  TwitchPress/Admin
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
                      
if ( ! class_exists( 'TwitchPress_Admin_Pointers' ) ) :

/**
 * TwitchPress_Admin_Pointers Class.
 */
class TwitchPress_Admin_Pointers {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_enqueue_scripts', array( $this, 'setup_pointers_for_screen' ) );
    }

    /**
     * Setup pointers for screen.
     */
    public function setup_pointers_for_screen() {    
        if ( ! $screen = get_current_screen() ) {
            return;
        }
             
        switch ( $screen->id ) {
            case 'plugins_page_twitchpress' :
                $this->create_tables_tutorial();
            break;
        }
    }

    /**
     * Pointers example.
     */
    public function create_tables_tutorial() {
        if ( ! isset( $_GET['twitchpresstutorial'] ) || ! current_user_can( 'manage_options' ) ) {
            return;
        }                            
           
        // These pointers will chain - they will not be shown at once.
        $pointers = array(
            'pointers' => array(
                'thefirst' => array(
                    'target'       => "#contextual-help-link",
                    'next'         => 'thesecond',
                    'next_trigger' => array(
                        'target' => '#contextual-help-link',
                        'event'  => 'input'
                    ),
                    'options'      => array(
                        'content'  =>     '<h3>' . esc_html__( 'Help and Information Tab', 'twitchpress' ) . '</h3>' .
                                        '<p>' . esc_html__( 'Over here is your first step to getting help. You will also find information about supporting the project as a developer or donator.', 'twitchpress' ) . '</p>',
                        'position' => array(
                            'edge'  => 'top',
                            'align' => 'right'
                        )
                    )
                ),
                'thesecond' => array(
                    'target'       => "#contextual-help-link",
                    'next'         => 'the-next-pointer',
                    'next_trigger' => array(),
                    'options'      => array(
                        'content'  =>     '<h3>' . esc_html__( 'Second', 'twitchpress' ) . '</h3>' .
                                        '<p>' . esc_html__( 'Second content.', 'twitchpress' ) . '</p>',
                        'position' => array(
                            'edge'  => 'bottom',
                            'align' => 'middle'
                        )
                    )
                )                            
            )
        );

        $this->enqueue_pointers( $pointers );
    }

    /**
     * Enqueue pointers and add script to page.
     * @param array $pointers
     */
    public function enqueue_pointers( $pointers ) {          
        $pointers = wp_json_encode( $pointers );
        wp_enqueue_style( 'wp-pointer' );
        wp_enqueue_script( 'wp-pointer' );
        twitchpress_enqueue_js( "
            jQuery( function( $ ) {
                var twitchpress_pointers = {$pointers};

                setTimeout( init_twitchpress_pointers, 800 );

                function init_twitchpress_pointers() {
                    $.each( twitchpress_pointers.pointers, function( i ) {
                        show_twitchpress_pointer( i );
                        return false;
                    });
                }

                function show_twitchpress_pointer( id ) {
                    var pointer = twitchpress_pointers.pointers[ id ];
                    var options = $.extend( pointer.options, {
                        close: function() {
                            if ( pointer.next ) {
                                show_twitchpress_pointer( pointer.next );
                            }
                        }
                    } );
                    var this_pointer = $( pointer.target ).pointer( options );
                    this_pointer.pointer( 'open' );

                    if ( pointer.next_trigger ) {
                        $( pointer.next_trigger.target ).on( pointer.next_trigger.event, function() {
                            setTimeout( function() { this_pointer.pointer( 'close' ); }, 400 );
                        });
                    }
                }
            });
        " );
    }
}

endif;

new TwitchPress_Admin_Pointers();