/*! http://wordpress.org/extend/plugins/achievements/ */
(function($) {

/**
 * Creates a notification using jQuery noty.
 *
 * @param string message The text to show in the notification
 * @param string url The link to the achievement page for this notification
 * @since 3.0
 * @todo Migrate notification to Web Notifications when spec matures
 */
function dpa_send_notification(message, url) {
	/*global noty, DPA_Notifications*/
	noty({
		buttons:      false,
		layout:       'topCenter',
		dismissQueue: true,
		text:         message,
		type:         'success'
	});
}

$(document).ready(function() {

	// Notifications
	if ('function' === typeof noty && 'undefined' !== typeof DPA_Notifications) {
		for (var note in DPA_Notifications) {
			if (DPA_Notifications.hasOwnProperty(note)) {
				dpa_send_notification(DPA_Notifications[note].message, DPA_Notifications[note].url);
			}
		}
	}

});

})(jQuery);