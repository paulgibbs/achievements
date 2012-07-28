<?php
/**
 * User functions
 *
 * @package Achievements
 * @subpackage UserFunctions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Checks if user is active
 * 
 * @param int $user_id Optional. The user ID to check
 * @return bool True if public, false if not
 * @since 3.0
 */
function dpa_is_user_active( $user_id = 0 ) {
	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = get_current_user_id();

	// No user to check
	if ( empty( $user_id ) )
		return false;

	// Check spam
	if ( dpa_is_user_spammer( $user_id ) )
		return false;

	// Check deleted
	if ( dpa_is_user_deleted( $user_id ) )
		return false;

	// Assume true if not spam or deleted
	return true;
}

/**
 * Checks if the user has been marked as a spammer.
 *
 * @param int $user_id int Optional. The ID for the user.
 * @return bool True if spammer, False if not.
 * @since 3.0
 */
function dpa_is_user_spammer( $user_id = 0 ) {
	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = get_current_user_id();

	// No user to check
	if ( empty( $user_id ) )
		return false;

	// Assume user is not spam
	$is_spammer = false;

	// Get user data
	$user = get_userdata( $user_id );

	// No user found
	if ( empty( $user ) ) {
		$is_spammer = false;

	// User found
	} else {

		// Check if spam
		if ( ! empty( $user->spam ) )
			$is_spammer = true;

		if ( 1 == $user->user_status )
			$is_spammer = true;
	}

	return apply_filters( 'dpa_is_user_spammer', (bool) $is_spammer );
}

/**
 * Mark a user's stuff as spam (or just delete it) when the user is marked as spam
 *
 * @param int $user_id Optional. User ID to spam.
 * @since 3.0
 */
function dpa_make_spam_user( $user_id = 0 ) {
	// Bail if no user ID
	if ( empty( $user_id ) )
		return;

	// Bail if user ID is super admin
	if ( is_super_admin( $user_id ) )
		return;

	// @todo Do stuff here
}

/**
 * Mark a user's stuff as ham when the user is marked as not a spammer
 *
 * @param int $user_id Optional. User ID to unspam.
 * @since 3.0
 */
function dpa_make_ham_user( $user_id = 0 ) {
	// Bail if no user ID
	if ( empty( $user_id ) )
		return;

	// Bail if user ID is super admin
	if ( is_super_admin( $user_id ) )
		return;

	// Do stuff here
}

/**
 * Checks if the user has been marked as deleted.
 *
 * @param int $user_id int Optional. The ID for the user.
 * @return bool True if deleted, False if not.
 * @since 3.0
 */
function dpa_is_user_deleted( $user_id = 0 ) {
	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = get_current_user_id();

	// No user to check
	if ( empty( $user_id ) )
		return false;

	// Assume user is not deleted
	$is_deleted = false;

	// Get user data
	$user = get_userdata( $user_id );

	// No user found
	if ( empty( $user ) ) {
		$is_deleted = true;

	// User found
	} else {

		// Check if deleted
		if ( ! empty( $user->deleted ) )
			$is_deleted = true;

		if ( 2 == $user->user_status )
			$is_deleted = true;
	}

	return apply_filters( 'dpa_is_user_deleted', (bool) $is_deleted );
}


/**
 * User notifications
 */

/**
 * Add a new notification for the specified user
 *
 * @param int $user_id int Optional. The ID for the user.
 * @param int $post_id int Optional. The post ID of the achievement to clear the notification for.
 * @since 3.0
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

	// Run an action for third-party plugins before we add the notification
	do_action( 'dpa_new_notification', $user_id, $post_id );

	// Get current notifications; the array is keyed by the achievement (post) ID.
	$notifications = dpa_get_user_notifications( $user_id );

	// Add the new notification - value is the timestamp in GMT
	$notifications[$post_id] = current_time( 'mysql', true );
	dpa_update_user_notifications( $notifications, $user_id );
}

/**
 * Clears any notifications for the specified user for the specified achievement.
 *
 * @param int $post_id int Optional. The post ID of the achievement to clear the notification for.
 * @param int $user_id int Optional. The ID for the user.
 * @since 3.0
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

	// Run an action for third-party plugins before we clear the notification
	do_action( 'dpa_clear_notification', $post_id, $user_id );

	// Clear the notification
	unset( $notifications[$post_id] );
	dpa_update_user_notifications( $notifications, $user_id );
}