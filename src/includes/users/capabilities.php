<?php
/**
 * User capability functions
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
 * @since Achievements (3.0)
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
 * @param int|WP_User $user_id int Optional. The ID for the user, or a WP_User object.
 * @return bool True if spammer, False if not.
 * @since Achievements (3.0)
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
	if ( is_a( $user_id, 'WP_User' ) )
		$user = $user_id;
	else
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
 * @return bool True if user is spammed, false if they weren't
 * @since Achievements (3.0)
 */
function dpa_make_spam_user( $user_id = 0 ) {
	// Bail if no user ID
	if ( empty( $user_id ) )
		return false;

	// Bail if user ID is super admin
	if ( is_super_admin( $user_id ) )
		return false;

	return true;
}

/**
 * Mark a user's stuff as ham when the user is marked as not a spammer
 *
 * @param int $user_id Optional. User ID to unspam.
 * @return bool True if user is spammed, false if they weren't
 * @since Achievements (3.0)
 */
function dpa_make_ham_user( $user_id = 0 ) {
	// Bail if no user ID
	if ( empty( $user_id ) )
		return false;

	// Bail if user ID is super admin
	if ( is_super_admin( $user_id ) )
		return false;

	return true;
}

/**
 * Checks if the user has been marked as deleted.
 *
 * @param int|WP_User $user_id int Optional. The ID for the user, or a WP_User object.
 * @return bool True if deleted, False if not.
 * @since Achievements (3.0)
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
	if ( is_a( $user_id, 'WP_User' ) )
		$user = $user_id;
	else
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
