/*! http://wordpress.org/extend/plugins/achievements/ */
(function($){
$(document).ready(function() {

	// If click is outside the notifications popup, close it.
	$('body.achievement-notifications').click(function() {
		$('#dpa-notifications-wrapper').remove();
	});

	$('#dpa-notifications').click(function(event){
		event.stopPropagation();
	});

});
})(jQuery);
