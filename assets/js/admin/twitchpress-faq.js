jQuery( document).ready( function( $ ) {
    var selectedQuestion = '';

    function selectQuestion() {
        var q = $( '#' + $(this).val() );
        if ( selectedQuestion.length ) {
            selectedQuestion.hide();
        }
        q.show();
        selectedQuestion = q;
    }

    var faqAnswers = $('.faq-answer');
    var faqIndex = $('#faq-index');
    faqAnswers.hide();
    faqIndex.hide();

    var indexSelector = $('<select/>')
        .attr( 'id', 'question-selector' )
        .addClass( 'widefat' );
    var questions = faqIndex.find( 'li' );
    var advancedGroup = false;
    questions.each( function () {
        var self = $(this);
        var answer = self.data('answer');
        var text = self.text();
        var option;

        if ( answer === 39 ) {
            advancedGroup = $( '<optgroup />' )
                .attr( 'label', "<?php _e( 'Advanced: This part of FAQ requires some knowledge about HTML, PHP and/or WordPress coding.', 'appointments' ); ?>" );

            indexSelector.append( advancedGroup );
        }

        if ( answer !== '' && text !== '' ) {
            option = $( '<option/>' )
                .val( 'q' + answer )
                .text( text );
            if ( advancedGroup ) {
                advancedGroup.append( option );
            }
            else {
                indexSelector.append( option );
            }

        }

    });

    faqIndex.after( indexSelector );
    indexSelector.before(
        $('<label />')
            .attr( 'for', 'question-selector' )
            .text( "<?php _e( 'Select a question', 'appointments' ); ?>" )
            .addClass( 'screen-reader-text' )
    );

    indexSelector.change( selectQuestion );
});