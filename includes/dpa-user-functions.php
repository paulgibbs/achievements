<?php
/**
 * User functions
 *
 * @package Achievements
 * @subpackage Functions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Checks if user is active
 * 
 * @param int $user_id The user ID to check
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
 * @param int $user_id int The ID for the user.
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
		if ( !empty( $user->spam ) )
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

	// Do stuff here
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
 * @param int $user_id int The ID for the user.
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
		if ( !empty( $user->deleted ) )
			$is_deleted = true;

		if ( 2 == $user->user_status )
			$is_deleted = true;
	}

	return apply_filters( 'dpa_is_user_deleted', (bool) $is_deleted );
}

/**
 * Changes the specified user's points total
 *
 * @param int $new_points This can be a negative or a positive integer
 * @param int $user_id Optional; defaults to current user
 * @since 3.0
 */
function dpa_update_user_points( $new_points, $user_id = 0 ) {
	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = get_current_user_id();

	// No user, bail out
	if ( empty( $user_id ) )
		return;

	// Build the meta key
	$site_id  = dpa_get_site_id_for_user_meta();
	$meta_key = 'dpa_points_' . $site_id;

	// Fetch the points from the user meta and update
	$points = get_user_meta( $user_id, $meta_key, true );
	$points = apply_filters( 'dpa_update_user_points', $points + $new_points, $user_id, $site_id );
	update_user_meta( $user_id, $meta_key, $points );

	do_action( 'dpa_update_user_points', $new_points, $user_id, $site_id );
}

/**
 * Gets the specified user's points total
 *
 * @param int $user_id Optional; defaults to current user
 * @return int User's points
 * @since 3.0
 */
function dpa_get_user_points( $user_id = 0 ) {
	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = get_current_user_id();

	// No user, bail out
	if ( empty( $user_id ) )
		return;

	// Build the meta key
	$site_id  = dpa_get_site_id_for_user_meta();
	$meta_key = 'dpa_points_' . $site_id;

	// Fetch the user's points and return
	$retval = get_user_meta( $user_id, $meta_key, true );
	return apply_filters( 'dpa_get_user_points', $retval, $user_id, $site_id );
}

/**
 * As Achievements can run independantly (as well as sitewide) on a multisite
 * installation, the user meta key for the user's points total has a site ID suffix.
 * This function takes care of figuring out what site ID we should be using for this.
 *
 * You shouldn't have to use this function unless you are adding new functionality to
 * Achievements, which would likely include additional storage in the user meta tables.
 *
 * @return int
 * @since 3.0
 */
function dpa_get_site_id_for_user_meta() {
	// If multisite and running network-wide, switch_to_blog to the data store site
	$site_id = ( is_multisite() && dpa_is_running_networkwide() ) ? DPA_DATA_STORE : get_current_blog_id();

	return apply_filters( 'dpa_get_site_id_for_user_meta', $site_id );
}

/**
 * Gets the total number of achievements that the specified user has unlocked
 *
 * @param int $user_id Optional; defaults to current user
 * @return int
 * @since 3.0
 */
function dpa_get_user_unlocked_achievements_count( $user_id = 0 ) {
	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = get_current_user_id();

	// No user, bail out
	if ( empty( $user_id ) )
		return;

	// Build the meta key
	$site_id  = dpa_get_site_id_for_user_meta();
	$meta_key = 'dpa_unlocked_count_' . $site_id;

	// Fetch the user's points and return
	$retval = get_user_meta( $user_id, $meta_key, true );
	return apply_filters( 'dpa_get_user_unlocked_achievements_count', $retval, $user_id, $site_id );
}
?>