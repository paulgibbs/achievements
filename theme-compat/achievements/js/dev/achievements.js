/*! http://wordpress.org/extend/plugins/achievements/ */
(function($) {

$(document).ready(function() {
	// Close button in notification panels
	$('#dpa-notifications').on('click.achievements', '.close', function() {
		$(this).parent().remove();
	});
});

})(jQuery);