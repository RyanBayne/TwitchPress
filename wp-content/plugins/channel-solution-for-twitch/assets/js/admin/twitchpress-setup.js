/*global twitchpress_setup_params */
jQuery( function( $ ) {

    $( '.button-next' ).on( 'click', function() {
        $('.twitchpress-setup-content').block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });
        return true;
    } );

    $( '.twitchpress-wizard-plugin-extensions' ).on( 'change', '.twitchpress-wizard-extension-enable input', function() {
        if ( $( this ).is( ':checked' ) ) {
            $( this ).closest( 'li' ).addClass( 'checked' );
        } else {
            $( this ).closest( 'li' ).removeClass( 'checked' );
        }
    } );

    $( '.twitchpress-wizard-plugin-extensions' ).on( 'click', 'li.twitchpress-wizard-extension', function() {
        var $enabled = $( this ).find( '.twitchpress-wizard-extension-enable input' );

        $enabled.prop( 'checked', ! $enabled.prop( 'checked' ) ).change();
    } );

    $( '.twitchpress-wizard-plugin-extensions' ).on( 'click', 'li.twitchpress-wizard-extension table, li.twitchpress-wizard-extension a', function( e ) {
        e.stopPropagation();
    } );
} );
