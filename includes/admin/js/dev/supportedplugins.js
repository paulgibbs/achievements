/*! http://wordpress.org/extend/plugins/achievements/
*/
/*global Socialite*/
(function($) {

/**
 * When the "All Plugins" dropdown or live search box changes, go through the current
 * view, taking into account the type filter (installed, not installed, all plugins),
 * and hide/show content as appropriate.
 *
 * If [return] is hit when only one result is shown, jump to it and clear the search
 * critera.
 *
 * @param object event
 * @since Achievements (3.0)
 */
function dpa_update_filters(event) {
	event.preventDefault();
	event.stopImmediatePropagation();

	// Get current filter and search query
	var current_filter   = $('#dpa-toolbar-filter').val(),
	current_search_query = $('#dpa-toolbar-search').val(),
	item                 = null,
	object               = '',

	// Find out what view we're in. This controls what we need to search for in the DOM.
	current_view = $('#post-body-content > div').prop('class');
	if (current_view.indexOf('grid') >= 0) {
		current_view = 'grid';
		object       = '#post-body-content > .grid a';

	} else if (current_view.indexOf('list') >= 0) {
		current_view = 'list';
		object       = '#post-body-content > .list tbody tr';

	// @todo This isn't implemented for 3.0
	} else if (current_view.indexOf('detail') >= 0) {
		return;
	}

	// Go through the DOM elements and figure out filter visibility
	$(object).each(function() {
		item = $(this);

		// Show installed plugins
		if ("1" === current_filter) {
			if (item.hasClass('installed')) {
				item.addClass('showme');
			} else {
				item.addClass('hideme');
			}

		// Show available plugins
		} else if ("0" === current_filter) {
			if (item.hasClass('notinstalled')) {
				item.addClass('showme');
			} else {
				item.addClass('hideme');
			}

		// Show all
		} else {
			item.addClass('showme');
		}
	});

	// Now, go through the DOM elements and figure out search query visibilty (hideme trumps showme).
	$(object + ':not(.hideme)').each(function() {
		item = $(this);

		// Grid view - searches on 'alt' tags
		if ('grid' === current_view && item.children('img').prop('alt').search(new RegExp(current_search_query, 'i')) < 0 ||

		// List view - searches on plugin name column
		'list' === current_view && item.children('.name').text().search(new RegExp(current_search_query, 'i')) < 0 ||

		// Detail view - search on the LI classes.
		'detail' === current_view && item.prop('class').search(new RegExp(current_search_query, 'i')) < 0) {

			// No match
			item.removeClass('showme');
			item.addClass('hideme');
		}
	});

	// Finally, go through everything one last time and show/hide as appropriate
	$(object).each(function() {
		item = $(this);

		if (item.hasClass('showme')) {
			item.show();
		} else if ( item.hasClass('hideme')) {
			item.hide();
		}

		// Reset the visibility markers for future iterations
		item.removeClass('hideme').removeClass('showme');
	});
}

$(document).ready(function() {
	// Never submit the search field's form
	$('#dpa-toolbar').submit(function(event) {
		event.stopPropagation();
		event.preventDefault();

		// If only one plugin is visible in search results, jump to the detail view and select it.
		// Grid view
		if (1 === $('#post-body-content > .grid a:visible').size() ) {
			window.location = $('#post-body-content > .grid a:visible').prop('href');

		// List view
		} else if (1 === $('#post-body-content > .list tbody tr:visible').size() ) {
			window.location = $('#post-body-content > .list tbody tr:visible .plugin a').prop('href');
		}
	});

	// When the plugin picker changes on the Detail view, reload the page to show the new plugin
	$('#dpa-details-plugins').on('change.achievements', function() {
		window.location.search = 'post_type=achievement&page=achievements-plugins&filter=all&view=detail&plugin=' + $(this.options[this.selectedIndex]).data('plugin');
	});

	// "All Plugins" dropdown and live search box
	$('#dpa-toolbar-filter').on('change.achievements', dpa_update_filters);
	$('#dpa-toolbar-search').on('keyup.achievements',  dpa_update_filters);

	// Tablesorter
	$("#post-body-content .list table").tablesorter({
		headers: {
			0: { sorter: false },
			1: { sorter: false },
			3: { sorter: false }
		},
		textExtraction: function(node) {
			return node.innerHTML;
		}
	});
	$("#post-body-content .list table th").on('click.achievements', 'a', function(event) {
		event.preventDefault();
	});

	// Load the share tools
	Socialite.load($('#dpa-detail-contents h3'));
});

})(jQuery);