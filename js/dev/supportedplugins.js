(function($) {

$(document).ready(function() {

	// Grid view - switch to Detail view when a plugin is clicked
	$('#post-body-content > .grid a').on('click.achievements', function(event) {
		event.preventDefault();

		$('#post-body-content > .current').removeClass('current');
		$('#post-body-content > .detail').addClass('current');
	});

	// Zoom slider
	$('#dpa-toolbar-slider').on('change.achievements', function(event) {
		// wporg images are 772x250px
		var scaled_width = 7.72 * (this.value * 10);

		// Rescale each div to match the slider (20% increments)
		$('.grid .plugin').each(function(index, element) {
			$(element).css('width', scaled_width + 'px');
		});

		// Save multiplier to a cookie
		$.cookie( 'dpa_sp_zoom', this.value, {path: '/'} );
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

		// Save the new_view to a cookie
		$.cookie( 'dpa_sp_view', new_view, {path: '/'} );
	});
});

})(jQuery);