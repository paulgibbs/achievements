(function($) {

$(document).ready(function() {
	// Zoom slider
	$('#dpa-toolbar-slider').on('change.achievements', function(event) {
		var multiplier = this.value * 10;

		// Rescale each div to match the slider (10% increments)
		$('.grid .plugin').each(function(index, element) {

			// wporg images are 772x250px
			var scaled_height = 2.5  * multiplier,
			scaled_width      = 7.72 * multiplier;

			$(element).css('height', scaled_height + 'px' ).css('width', scaled_width + 'px' );
		});

		// @todo: Save multiplier to a cookie called "dpa_sp_zoom"
	});

	// Switch state of toolbar views, and update main display
	$('#dpa-toolbar-wrapper a').on('click.achievements', function(event) {
		event.preventDefault();

		// Don't change if this view already selected
		var btn = $(this), new_view = btn.prop('class');
		if (btn.hasClass('current')) {
			return;
		}

		// Update zoom slider
		if ('grid' === new_view) {
			$('.dpa-toolbar-slider').addClass('current');
		} else {
			$('.dpa-toolbar-slider').removeClass('current');
		}

		// Update toolbar buttons
		btn.parent().parent().find('a').removeClass('current');
		btn.addClass('current');

		// Update main display
		$('#post-body-content > div').removeClass('current');
		$('#post-body-content > div.' + new_view).addClass('current');

		// @todo: Save new_view to a cookie called "dpa_sp_view"
	});
});

})(jQuery);