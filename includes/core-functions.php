<?php
/**
 * Achievements core functions
 *
 * The first sections consists of functions directly related to the core achievement logic.
 *
 * @package Achievements
 * @subpackage CoreFunctions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Achievement actions are stored as a custom taxonomy. This function queries that taxonomy to find items,
 * and then using the items' slugs (which are the name of a WordPress action), registers a handler action
 * in Achievements. The user needs to be logged in for this to hapen.
 *
 * Posts in trash are returned by get_terms(), even if hide_empty is set. We double-check the post status
 * before we actually give the award.
 *
 * This function is invoked on every page load, but get_terms() provides built-in caching, so we don't
 * have to worry about that.
 *
 * @since 3.0
 */
function dpa_register_events() {
	// Only do things if the user is logged in
	if ( ! is_user_logged_in() )
		return;

	// Get all valid events from the event taxononmy. A valid event is one associated with a post type.
	$events = get_terms( achievements()->event_tax_id, array( 'hide_empty' => true )  );
	if ( is_wp_error( $events ) )
		return;

	$events = wp_list_pluck( (array) $events, 'slug' );
	$events = apply_filters( 'dpa_register_events', $events );

	// For each event, add a handler function to the action.
	foreach ( (array) $events as $event )
		add_action( $event, 'dpa_handle_event', 12, 10 );  // Priority 12 in case object modified by other plugins
}

/**
 * Implements the Achievement actions and unlocks if criteria met.
 *
 * @param string $name Action name
 * @param array $func_args Optional; action's arguments, from func_get_args().
 * @see dpa_register_events()
 * @since 3.0
 */
function dpa_handle_event() {
	// Look at the current_filter to find out what action has occured
	$event_name = current_filter();
	$func_args  = func_get_args();

	// Let other plugins do things before anything happens
	do_action( 'dpa_before_handle_event', $event_name, $func_args );

	// Allow other plugins to bail out early
	$event_name = apply_filters( 'dpa_handle_event_name', $event_name, $func_args );
	if ( false === $event_name )
		return;

	// This filter allows the user ID to be updated (e.g. for draft posts which are then published by someone else)
	$user_id = absint( apply_filters( 'dpa_handle_event_user_id', get_current_user_id(), $event_name, $func_args ) );
	if ( ! $user_id )
		return;

	// Find achievements that are associated with the $event_name taxonomy
	$args = array(
		'ach_event'             => $event_name,  // Get posts in the event taxonomy matching the event name
		'ach_populate_progress' => $user_id,     // Fetch Progress posts for this user ID
		'no_found_rows'         => true,         // Disable SQL_CALC_FOUND_ROWS
		'posts_per_page'        => -1,           // No pagination
		's'                     => '',           // Stop sneaky people running searches on this query
	);

	// Loop through achievements found
	if ( dpa_has_achievements( $args ) ) {

		while ( dpa_achievements() ) {
			dpa_the_achievement();

			/**
			 * Check the achievement post is published.
			 *
			 * get_terms() in dpa_register_events() can retrieve taxonomies which are
			 * associated only with posts in the trash. We only want to process
			 * 'active' achievements (post_status = published).
			 */
			if ( 'publish' != achievements()->achievement_query->post->post_status )
				continue;

			// Let other plugins do things before we maybe_unlock_achievement
			do_action( 'dpa_handle_event', $event_name, $func_args, $user_id );

			// Allow plugins to stop any more processing for this achievement
			if ( false === apply_filters( 'dpa_handle_event_maybe_unlock_achievement', true, $event_name, $func_args, $user_id ) )
				continue;

			// Look in the progress posts and match against a post_parent which is the same as the current achievement.
			$post = wp_filter_object_list( achievements()->progress_query->posts, array( 'post_parent' => dpa_get_the_achievement_ID() ), 'and', 'post_status' );

			// If $user_id does not have any progress for this achievement, or some progress has been made.
			if ( empty( $post ) || dpa_get_locked_status_id() == $post[0] )
				dpa_maybe_unlock_achievement();
		}
	}

	// Everything is done. Let other plugins do other things.
	do_action( 'dpa_after_handle_event', $event_name, $func_args, $user_id );
}

function dpa_maybe_unlock_achievement() {
}

/**
 * Below this point consists of functions not directly related to the core achievement logic.
 */

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
	 * @return string The Achievements version
	 */
	function dpa_get_version() {
		return achievements()->version;
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
	 * @return string The Achievements version
	 */
	function dpa_get_db_version() {
		return achievements()->db_version;
	}

/**
 * Return the locked (achievement) post status ID
 *
 * @return string
 * @since 3.0
 */
function dpa_get_locked_status_id() {
	return achievements()->locked_status_id;
}

/**
 * Return the unlocked (achievement progress) post status ID
 *
 * @return string
 * @since 3.0
 */
function dpa_get_unlocked_status_id() {
	return achievements()->unlocked_status_id;
}


/**
 * Errors
 */

/**
 * Adds an error message to later be output in the theme
 *
 * @param string $code Unique code for the error message
 * @param string $message Translated error message
 * @param string $data Any additional data passed with the error message
 * @since 3.0
 */
function dpa_add_error( $code = '', $message = '', $data = '' ) {
	achievements()->errors->add( $code, $message, $data );
}

/**
 * Check if error messages exist in queue
 *
 * @since 3.0
 */
function dpa_has_errors() {
	// Assume no errors
	$has_errors = false;

	// Check for errors
	if ( achievements()->errors->get_error_codes() )
		$has_errors = true;

	return apply_filters( 'dpa_has_errors', $has_errors, achievements()->errors );
}