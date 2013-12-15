/**
 * Achievements core JS
 *
 * @author Paul Gibbs <paul@byotos.com>
 */

/* jshint undef: true, unused: true */
/* global jQuery, wp, _ */


/**
 * Achievements' main JS object.
 *
 * @type {Object}
 */
var achievements = {
	/**
	 * Achievements' heartbeat object.
	 *
	 * @type {Achievements_Heartbeat}
	 */
	heartbeat: null,

	/**
	 * Fetches a template by ID.
	 *
	 * Copied from WordPress' wp.template() method in wp-includes/js/wp-util.js.
	 *
	 * @param {String} ID Name of the template to render
	 * @param {Object} data Data received from the server
	 * @return {String} HTML
	 */
	template: function (ID, data) {
		return _.memoize(function () {
			var compiled,
			options = {
				evaluate:    /<#([\s\S]+?)#>/g,
				interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
				escape:      /\{\{([^\}]+?)\}\}(?!\})/g,
				variable:    'data'
			};

			return function () {
				compiled = compiled || _.template( jQuery( '#tmpl-' + ID ).html(), null, options );
				return compiled( data );
			};
		});
	}
};


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

		/**
		 * Render new notifications to the screen
		 *
		 * @param {object} data Data received from the server
		 */
		function showNotifications(data) {
			var notifications = $(document.createDocumentFragment()),
			panel = $('#dpa-toaster');

			// Grab the rendered markup for each achievement
			_.each(data, function(achievement) {
				notifications.append(achievements.template('achievements-item', achievement));
			});

			// If our wrapper doesn't exist yet, create it
			if (panel.length < 1) {
				var wrapper = $(document.createDocumentFragment());
				wrapper.append(achievements.template('achievements-wrapper'));
				$('body').append(wrapper);

				panel = $('#dpa-toaster');
			}

			// Add rendered notifications to the panel
			notifications.insertAfter(panel.children(':first-child'));
			panel.fadeIn(200);
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

			// If nothing in the response for Achievements, bail out
			if ( ! ( 'achievements' in data ) ) {
				return;
			}

			// Fade the notifications window out after 7 seconds
			window.setTimeout(function () {
				var panel = $('#dpa-toaster');

				panel.fadeOut(100, function () {
					$(this).children('li').remove();
				});
			}, 7000);

			showNotifications(data.achievements);
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

			// Immediately check for achievements.
			wp.heartbeat.connectNow();
		});
	};

	achievements.heartbeat = new Achievements_Heartbeat();
}(jQuery));