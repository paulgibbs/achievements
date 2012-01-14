<?php
/**
 * Common/helper functions
 *
 * @package Achievements
 * @subpackage Functions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Output the Achievements version
 *
 * @since 3.0
 */
function dpa_version() {
	echo dpa_get_version();
}
	/**
	 * Return the Achievements version
	 *
	 * @since 3.0
	 * @global achievements $achievements Main Achievements object
	 * @return string The Achievements version
	 */
	function dpa_get_version() {
		global $achievements;
		return $achievements->version;
	}

/**
 * Output the Achievements database version
 *
 * @uses dpa_get_version() To get the Achievements DB version
 */
function dpa_db_version() {
	echo dpa_get_db_version();
}
	/**
	 * Return the Achievements database version
	 *
	 * @since 3.0
	 * @global achievements $achievements Main Achievements object
	 * @return string The Achievements version
	 */
	function dpa_get_db_version() {
		global $achievements;
		return $achievements->db_version;
	}


/**
 * Errors
 */

/**
 * Adds an error message to later be output in the theme
 *
 * @global achievements $achievements Main Achievements object
 * @param string $code Unique code for the error message
 * @param string $message Translated error message
 * @param string $data Any additional data passed with the error message
 * @since 3.0
 */
function dpa_add_error( $code = '', $message = '', $data = '' ) {
	global $achievements;
	$achievements->errors->add( $code, $message, $data );
}

/**
 * Check if error messages exist in queue
 *
 * @global achievements $achievements Main Achievements object
 * @since 3.0
 */
function dpa_has_errors() {
	global $achievements;

	// Assume no errors
	$has_errors = false;

	// Check for errors
	if ( $achievements->errors->get_error_codes() )
		$has_errors = true;

	return apply_filters( 'dpa_has_errors', $has_errors, $achievements->errors );
}


/**
 * Users
 */

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
 * Check if the current post type belongs to Achievements
 *
 * @return bool
 * @since 3.0
 */
function dpa_is_custom_post_type() {
	// Current post type
	$post_type = get_post_type();

	// Achievements' post types
	$achievements_post_types = array(
		dpa_get_achievement_post_type(),
	);

	// Viewing one of Achievements' post types
	if ( in_array( $post_type, $achievements_post_types ) )
		return true;

	return false;
}

/**
 * Output the unique id of the custom post type for achievements
 *
 * @since 3.0
 * @uses dpa_get_achievement_post_type() To get the forum post type
 */
function dpa_achievement_post_type() {
	echo dpa_get_achievement_post_type();
}
	/**
	 * Return the unique id of the custom post type for achievements
	 *
	 * @global achievements $achievements Main Achievements object
	 * @return string The unique forum post type id
	 * @since 3.0
	 */
	function dpa_get_achievement_post_type() {
		global $achievements;
		return apply_filters( 'dpa_get_achievement_post_type', $achievements->achievement_post_type );
	}
?>