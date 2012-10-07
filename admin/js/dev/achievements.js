/*! http://wordpress.org/extend/plugins/achievements/ */
(function($) {

$(document).ready(function() {

	// If editing an existing award, hide the event boxes. @todo Do this with CSS.
	if ( 'award' === $( 'input[name="dpa_type"]:checked' ).prop('value') ) {
		setTimeout( function() { $( '#dpa_event_chzn, .dpa-target' ).hide(); }, 10 );
	}

	// Make the event select box magical
	$( '#dpa-event' ).chosen();

	// Toggle the 'pick event' select box when the 'event' type is selected
	$( '#dpa-type-award, #dpa-type-event' ).change( function() {
		if ( 'event' === $(this).prop( 'value' ) ) {
			$( '#dpa_event_chzn, .dpa-target' ).show();
		} else {
			$( '#dpa_event_chzn, .dpa-target' ).fadeOut( 200 );
		}
	} );

});

})(jQuery);