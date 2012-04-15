/*global Socialite*/
(function($) {

/**
 * When the "All Plugins" dropdown or live search box changes, go through the current
 * view, taking into account the type filter (installed, not installed, all plugins),
 * and hide/show content as appropriate.
 *
 * @param object event
 * @since 3.0
 */
function dpa_update_filters(event) {
	event.preventDefault();

	// Get current filter and search query
	var current_filter   = $('#dpa-toolbar-filter').val(),
	current_search_query = $('#dpa-toolbar-search').val(),
	item                 = null,
	object               = '',

	// Find out what view we're. This controls what we need to search for in the DOM.
	current_view = $('#post-body-content > .current').prop('class');
	if (current_view.indexOf('grid') >= 0) {
		current_view = 'grid';
		object       = '#post-body-content > .grid a';

	} else if (current_view.indexOf('list') >= 0) {
		current_view = 'list';
		object       = '#post-body-content > .list tbody tr';

	} else if (current_view.indexOf('detail') >= 0) {
		current_view = 'detail';
		object       = '#post-body-content > .detail > ul li';
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
			item.fadeOut();
		}

		// Reset the visibility markers for future iterations
		item.removeClass('hideme').removeClass('showme');
	});

	// Save current filter selection to a cookie
	$.cookie( 'dpa_sp_filter', current_filter, {path: '/'} );
}

/**
 * Switch current view to $new_view.
 *
 * Updates visible content and view tab state.
 *
 * @param string new_view
 * @param object event
 * @since 3.0
 */
function dpa_switch_view(new_view, event) {
	// Truncate whitespace
	new_view = $.trim( new_view );

	// Hide old view, show new view.
	$('#post-body-content > .current, #dpa-toolbar-views li.current').removeClass('current');
	$('#post-body-content > .' + new_view).addClass('current');
	$('#dpa-toolbar-views li a.' + new_view).parent().addClass('current');

	// Update the visible plugins for the search results and the installed/not installed/all plugins filters.
	dpa_update_filters(event);

	// Save the new_view to a cookie
	$.cookie( 'dpa_sp_view', new_view, {path: '/'} );
}

/**
 * Select a plugin in the detail view
 *
 * Updates visible content and plugin list selected item.
 *
 * @param jQuery new_plugin jQuery DOM object (<li> item from selection list)
 * @since 3.0
 */
function dpa_show_plugin(new_plugin) {
	var slug = new_plugin.prop('class');
	slug     = slug.substr(0, slug.indexOf(' '));

	// Mark new LI as selected
	$('#post-body-content > .detail > ul li').removeClass('current');
	new_plugin.addClass('current');

	// Show detail panel for the selected plugin
	$('#dpa-detail-contents > div').removeClass('current');
	$('#dpa-detail-contents .' + slug).addClass('current');

	// Save plugin slug to a cookie
	$.cookie( 'dpa_sp_lastplugin', slug, {path: '/'} );

	// Load the share tools
//	Socialite.load($('#dpa-detail-contents .current'));
}


$(document).ready(function() {
	// Detail view - update content when new plugin is clicked
	$('#post-body-content > .detail > ul li').on('click.achievements', function(event) {
		event.preventDefault();
		dpa_show_plugin($(this));
	});

	// List view - switch to Detail view when a plugin's logo is clicked
	$('#post-body-content > .list .plugin img').on('click.achievements', function(event) {
		event.preventDefault();

		dpa_switch_view('detail', event);
		dpa_show_plugin($('#post-body-content > .detail > ul li.' + $(this).prop('class')));
	});

	// Grid view - switch to Detail view when a plugin is clicked
	$('#post-body-content > .grid a').on('click.achievements', function(event) {
		event.preventDefault();

		dpa_switch_view('detail', event);
		dpa_show_plugin($('#post-body-content > .detail > ul li.' + $(this).children('img').prop('class')));
	});

	// Switch state of toolbar views, and update main display
	$('#dpa-toolbar-wrapper li a').on('click.achievements', function(event) {
		event.preventDefault();

		// Switch the view
		if ( !$(this).hasClass('current') ) {
			dpa_switch_view($(this).prop('class'), event);
		}
	});

	// "All Plugins" dropdown and live search box
	$('#dpa-toolbar-filter').on('change.achievements', dpa_update_filters);
	$('#dpa-toolbar-search').on('keyup.achievements',  dpa_update_filters);

	// Tablesorter
	$("#post-body-content .list table").tablesorter({
		headers: {
			0: { sorter: false },
			1: { sorter: false },
			4: { sorter: false }
		},
		textExtraction: function(node) {
			return node.innerHTML;
		}
	});
	$("#post-body-content .list table th a").on('click.achievements', function(event) {
		event.preventDefault();
	});

	// Load the share tools
	Socialite.load($('#dpa-detail-contents h3'));
});

})(jQuery);