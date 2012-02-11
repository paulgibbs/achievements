(function($) {

/**
 * Switch current view to $new_view.
 *
 * Updates visible content and view tab state. Hides zoom slider if appropriate.
 *
 * @param string new_view
 * @since 3.0
 */
function dpa_switch_view(new_view) {
	// Hide old view, show new view.
	$('#post-body-content > .current, #dpa-toolbar-views a.current').removeClass('current');
	$('#post-body-content > .' + new_view + ', #dpa-toolbar-views li a.' + new_view).addClass('current');

	// Update zoom slider
	if ('grid' === new_view) {
		$('.dpa-toolbar-slider').addClass('current');
	} else {
		$('.dpa-toolbar-slider').removeClass('current');
	}

	// Save the new_view to a cookie
	$.cookie( 'dpa_sp_view', new_view, {path: '/'} );
}

$(document).ready(function() {

	// List view - switch to Detail view when a plugin's logo is clicked
	$('#post-body-content > .list .plugin img').on('click.achievements', function(event) {
		event.preventDefault();
		dpa_switch_view('detail');
	});

	// Grid view - switch to Detail view when a plugin is clicked
	$('#post-body-content > .grid a').on('click.achievements', function(event) {
		event.preventDefault();
		dpa_switch_view('detail');
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

		// Switch the view
		dpa_switch_view(new_view);
	});

	// Search box
	$('#dpa-toolbar-search').on('keyup.achievements', function(event) {
		event.preventDefault();

		// Get query
		var query = $(this).val(), filter = '';

		// We filter on different content depending on which view we're in
		var current_view = $('#post-body-content > .current').prop('class');
		if (current_view.indexOf('grid') >= 0) {
			current_view = 'grid';
			filter       = '#post-body-content > .grid img';

		} else if (current_view.indexOf('list') >= 0) {
			current_view = 'list';
			filter       = '#post-body-content > .list table .name';

		} else if (current_view.indexOf('detail') >= 0) {
			current_view = 'detail';
			filter       = '@todo This.';
		}

		// Do the actual filter
		$(filter).each(function() {
			var item = $(this);

			// Grid view - searches on 'alt' tags
			if ('grid' === current_view) {
				if (item.prop('alt').search(new RegExp(query, 'i')) < 0) {  // No match
					item.fadeOut();
				} else {
					item.show();
				}

			// List view - searches on plugin name column
			} else if ('list' === current_view) {
				if (item.text().search(new RegExp(query, 'i')) < 0) {  // No match
					item.parent().fadeOut();
				} else {
					item.parent().show();
				}

			// Detail view - @todo This.
			} else if ('detail' === current_view) {
			}

		});

	});
});

})(jQuery);