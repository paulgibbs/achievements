<?php
/**
 * User notification functions
 *
 * @package Achievements
 * @subpackage UserNotifications
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Sends a notification to a user when they unlock an achievement.
 *
 * @param object $achievement_obj The Achievement object to send a notification for.
 * @param int $user_id ID of the user who unlocked the achievement.
 * @param int $progress_id The Progress object's ID.
 * @since Achievements (3.0)
 */
function dpa_send_notification( $achievement_obj, $user_id, $progress_id ) {
	// Let other plugins easily bypass sending notifications.
	if ( ! apply_filters( 'dpa_maybe_send_notification', true, $achievement_obj, $user_id, $progress_id ) )
		return;

	// Create a notification for this user/achievement.
	dpa_new_notification( $user_id, $achievement_obj->ID );

	// Tell other plugins that we've just added a new notification
	do_action( 'dpa_send_notification', $achievement_obj, $user_id, $progress_id );
}

/**
 * Print any notifications for the current user to the page footer.
 * 
 * @since Achievements (3.0)
 */
function dpa_print_notifications() {
	// If user's not active or is inside the WordPress Admin, bail out.
	if ( ! dpa_is_user_active() || is_admin() || is_404() )
		return;

	// Get current notifications
	$achievements = array();
	$notifications = dpa_get_user_notifications();

	// If we're viewing an achievement, clear the notification
	if ( ! empty( $notifications ) && dpa_is_single_achievement() && isset( $notifications[get_the_ID()] ) ) {
		unset( $notifications[get_the_ID()] );

		// Save the user's notifications
		dpa_update_user_notifications( $notifications );
	}

	if ( empty( $notifications ) )
		return;

	// Display notifications
	echo achievements()->shortcodes->display_feedback_achievement_unlocked();
}

/**
 * Add a new notification for the specified user
 *
 * @param int $user_id int Optional. The ID for the user.
 * @param int $post_id int Optional. The post ID of the achievement to clear the notification for.
 * @since Achievements (3.0)
 */
function dpa_new_notification( $user_id = 0, $post_id = 0 ) {
	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = get_current_user_id();

	// Default to current post
	if ( empty( $post_id ) && is_single() )
		$post_id = get_the_ID();

	// No user or post ID to check
	if ( empty( $user_id ) || empty( $post_id ) )
		return;

	// Get existing notifications
	$notifications = dpa_get_user_notifications( $user_id );

	// Add the new notification: key = post ID, value = not used (for backpat reasons during 3.0 beta releases).
	$notifications[$post_id] = '';
	dpa_update_user_notifications( $notifications, $user_id );

	// Tell other plugins that we've just created a new notification
	do_action( 'dpa_new_notification', $user_id, $post_id );
}

/**
 * Clears any notifications for the specified user for the specified achievement.
 *
 * @param int $post_id int Optional. The post ID of the achievement to clear the notification for.
 * @param int $user_id int Optional. The ID for the user.
 * @since Achievements (3.0)
 */
function dpa_clear_notification( $post_id = 0, $user_id = 0 ) {
	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = get_current_user_id();

	// Default to current post
	if ( empty( $post_id ) && is_single() )
		$post_id = get_the_ID();

	// No user or post ID to check
	if ( empty( $user_id ) || empty( $post_id ) )
		return;

	// The notifications array is keyed by the achievement (post) ID.
	$notifications = dpa_get_user_notifications( $user_id );

	// Is there a notification to clear?
	if ( ! isset( $notifications[$post_id] ) )
		return;

	// Clear the notification
	unset( $notifications[$post_id] );
	dpa_update_user_notifications( $notifications, $user_id );

	// Tell other plugins that we've just cleared other plugins
	do_action( 'dpa_clear_notification', $post_id, $user_id );
}