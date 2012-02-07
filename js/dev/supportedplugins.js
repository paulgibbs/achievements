(function($) {

$(document).ready(function() {
	// Switch state of toolbar views, and update main display
	$('#dpa-toolbar-wrapper a').on('click.achievements', function(event) {
		event.preventDefault();

		// Don't change if this view already selected
		var btn = $(this), new_view = btn.attr('class');
		if (btn.hasClass('current')) {
			return;
		}

		// Update toolbar buttons
		btn.parent().parent().find('a').removeClass('current');
		btn.addClass('current');

		// Update main display
		$('#post-body-content > div').removeClass('current');
		$('#post-body-content > div.' + new_view).addClass('current');

		// @TODO: Save new_view to a cookie called "dpa_sp_view"
	});
});

})(jQuery);