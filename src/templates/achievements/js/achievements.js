/**
 * Achievements core JS
 *
 * @author Paul Gibbs <paul@byotos.com>
 */

/* jshint undef: true, unused: true */
/* global jQuery, _, wp */


/**
 * Achievements JS object
 *
 * @type {Object}
 */
var achievements = {
	heartbeat: null
};

/*
	wp.template = _.memoize(function ( id ) {
		var compiled,
			options = {
				evaluate:    /<#([\s\S]+?)#>/g,
				interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
				escape:      /\{\{([^\}]+?)\}\}(?!\})/g,
				variable:    'data'
			};

		return function ( data ) {
			compiled = compiled || _.template( $( '#tmpl-' + id ).html(), null, options );
			return compiled( data );
		};
	});
*/
//wp-check-locked-posts


(function ($) {
	/**
	 * Achievements' object for heartbeat communication with WordPress
	 *
	 * @class Achievements' object for heartbeat communication with WordPress
	 * @property {boolean} isUserLoggedIn Does WordPress think the current user is logged in?
	 * @property {boolean} isWindowVisible According to the Page Visibility API, is the current window visible?
	 * @property {string} visibilityChangeEvent Helper for the name of the browser's "visibilitychange" event, due to browser prefixing.
	 * @property {string} visibilityChangeProperty Helper for the name of the browser's "document.hidden" property, due to browser prefixing.
	 */
	var Achievements_Heartbeat = function () {
		var isUserLoggedIn = true,
		isWindowVisible = true,
		visibilityChangeEvent = '',
		visibilityChangeProperty = '';

		// Page Visibility API helpers - http://goo.gl/vIqmlf
		if (typeof document.hidden !== 'undefined') {
			visibilityChangeEvent    = 'visibilitychange';
			visibilityChangeProperty = 'hidden';
		} else if (typeof document.mozHidden !== 'undefined') {
			visibilityChangeEvent    = 'mozvisibilitychange';
			visibilityChangeProperty = 'mozHidden';
		} else if (typeof document.msHidden !== 'undefined') {
			visibilityChangeEvent    = 'msvisibilitychange';
			visibilityChangeProperty = 'msHidden';
		} else if (typeof document.webkitHidden !== 'undefined') {
			visibilityChangeEvent    = 'webkitvisibilitychange';
			visibilityChangeProperty = 'webkitHidden';
		}

 
		// Misc helper functions

		/**
		 * Does WordPress think that the current user is logged in?
		 *
		 * @returns {boolean}
		 */
		this.isUserLoggedIn = function () {
			return isUserLoggedIn;
		};

		/**
		 * Is the current window visible or not?
		 *
		 * Helper for the Page Visibility API - http://goo.gl/vIqmlf
		 *
		 * @returns {boolean}
		 */
		this.isWindowVisible = function () {
			return isWindowVisible;
		};

		/**
		 * Uses the Page Visibility API to set the isWindowVisible variable
		 *
		 * Page Visibility API - http://goo.gl/vIqmlf
		 */
		function visibilityChanged() {
			isWindowVisible = (document[visibilityChangeProperty] === false);
		}


		// WP Heartbeat API implementation

		/**
		 * When we receive a heartbeat from WordPress.
		 *
		 * @param {Event} e Event object
		 * @param {Object} data Data received from the server
		 */
		function tick(e, data) {
			// Record if the user is logged in or not
			isUserLoggedIn = ('wp-auth-check' in data && data['wp-auth-check'] === true);
		}

		/**
		 * Prepare data to send back to WP in the reply heartbeat.
		 *
		 * @param {Event} e Event object
		 * @param {Object} data Data received from the server
		 */
		function send(e, data) {
			// User must be logged in and the current window must be visible
			if (!isUserLoggedIn || !isWindowVisible) {
				return;
			}

			// If something has already queued up data to send back to WordPress, bail out
			if (wp.heartbeat.isQueued('achievements')) {
				return;
			}

			// djpaultodo: don't ping if the popup is still open

			// We want to recieve any new notifications in the next heartbeat
			data['achievements'] = { type: 'notifications' };
		}

		/**
		 * DOM on-ready event handler.
		 *
		 * Hook into events from WordPress' heartbeat API.
		 */
		$(document).ready(function () {
			$(document).on('heartbeat-tick.achievements', tick)
			.on('heartbeat-send.achievements', send)
			.on(visibilityChangeEvent + '.achievements', visibilityChanged);
		});
	};

	achievements.heartbeat = new Achievements_Heartbeat();
}(jQuery));