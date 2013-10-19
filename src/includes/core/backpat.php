<?php
/**
 * Backwards compatibility
 *
 * Whenever a theme compat. template is deprecated in Achievements, often we need
 * to make sure that it continues to work for sites who have customised that template
 * with previous versions of Achievements.
 * 
 * This file contains functions that help with that.
 *
 * @package Achievements
 * @subpackage BackwardsCompatibilty
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Does the old-style notification template file exist in the current theme?
 *
 * The template was removed in version 3.5 and was replaced with the heartbeat-powered "live notifications" system.
 * This function has no filters on purpose because it's meant to only run once, as quickly as possible.
 *
 * @return bool
 * @since Achievements (3.5)
 */
function dpa_deprecated_notification_template_exists() {
	static $retval = null;

	if ( $retval !== null )
		return $retval;

	$template_locations = dpa_add_template_locations( array( '/feedback-achievement-unlocked.php' ) );
	$retval             = false;

	foreach ( $template_locations as $template_name ) {

		// Try to find the deprecated template. Check child theme first.
		if ( file_exists( get_stylesheet_directory() . "/$template_name" ) ) {
			$retval = true;
			break;

		// Check parent theme last.
		} elseif ( file_exists( get_template_directory() . "/$template_name" ) ) {
			$retval = true;
			break;
		}
	}

	return $retval;
}

/**
 * Backwards compatibility with pre-3.5: print the old-style notifications for the current user to the page footer.
 * 
 * Notifications were overhauled in version 3.5 and were replaced with the heartbeat-powered "live notifications" system.
 * This function used to be called "dpa_print_notifications".
 *
 * @deprecated Achievements (3.5)
 * @since Achievements (3.0)
 */
function dpa_deprecated_print_notifications() {

	// If user's not active or is inside the WordPress Admin, bail out.
	if ( ! dpa_is_user_active() || is_admin() || is_404() || ! dpa_user_has_notifications() )
		return;

	// Get current notifications
	$achievements  = array();
	$notifications = dpa_get_user_notifications();

	if ( empty( $notifications ) )
		return;

	echo achievements()->shortcodes->display_feedback_achievement_unlocked();
}

/**
 * Backwards compatibility with pre-3.5; enqueue the old-style CSS/JS for notifications.
 *
 * These scripts were removed from the default theme compatiility pack in version 3.5 with
 * the introduction of the heartbeat-powered "live notifications" system.
 *
 * @since Achievements (3.5)
 */
function dpa_deprecated_enqueue_notification_styles_and_scripts() {

	// If user's not active or is inside the WordPress Admin, bail out.
	if ( ! dpa_is_user_active() || is_admin() || is_404() )
		return;

	$location = achievements()->includes_url . 'backpat/3.5/';
	$rtl      = is_rtl() ? '-rtl' : '';
	$file     = "notifications{$rtl}.css";

	wp_enqueue_style( 'dpa-default-notifications', $location . $file, array(), dpa_get_theme_compat_version(), 'screen' );
	wp_enqueue_script( 'dpa-default-notifications-javascript', "{$location}notifications.js", array( 'jquery' ), dpa_get_theme_compat_version(), true );
}
