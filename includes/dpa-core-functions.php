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
 * and then using that item's slug (which is the name of a WordPress action), registers a handler action
 * in Achievements.
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
	$events = get_terms( achievements()->event_tax_id, array( 'hide_empty' => true )  );
	if ( is_wp_error( $events ) )
		return;

	$events = wp_list_pluck( (array) $events, 'slug' );
	$events = apply_filters( 'dpa_register_events', $events );

	foreach ( (array) $events as $event )
		add_action( $event, 'dpa_handle_event', 12, 10 ); // Priority 12 in case object modified by other plugins
}

/**
 * Implements the Achievement actions, and unlocks if criteria met.
 *
 * @global int $blog_id Site ID (variable is from WordPress and hasn't been updated for 3.0; confusing name is confusing)
 * @global object $bp BuddyPress global settings
 * @param string $name Action name
 * @param array $func_args Optional; action's arguments, from func_get_args().
 * @see dpa_register_events()
 * @since 3.0
 */
function dpa_handle_event() {
	global $blog_id, $bp;

	// Look at the current_filter to find out what action/event has been called
	$event_name = current_filter();
	$func_args  = func_get_args();

	do_action( 'dpa_before_handle_event', $event_name, $func_args );

	// Allow plugins to bail out early
	if ( ! $event_name = apply_filters( 'dpa_handle_event', $event_name, $func_args ) )
		return;

	// This filter allows the user ID to be updated (e.g. for draft posts which are then published by someone else)
	$user_ids = apply_filters( 'dpa_handle_event_user_id', get_current_user_id(), $event_name, $func_args );
	if ( empty( $user_ids ) )
		return;

	// The 'dpa_handle_event_user_id' filter can return an array of user IDs.
	$user_ids = wp_parse_id_list( (array) $user_ids );

	// Execute each potential unlock for each user
	foreach( $user_ids as $user_id ) {
		if ( empty( $user_id ) )
			continue;

		// Find achievements that are associated with the $event_name taxonomy
		$args = array(
			'no_found_rows' => true,                 // Don't COUNT results if any LIMIT is set...
			                                         // This is probably the default behaviour since nopaging is set, but, just in case.

			'nopaging'      => true,                 // No pagination
			's'             => '',                   // Stop sneaky people running searches on this query
			'tax_query'     => array(                // Get posts in the event taxonomy
				'field'    => 'slug',
				'taxonomy' => dpa_get_event_tax_id(),
				'terms'    => $event_name,
			),
		);

		// If any achievements were found, go through each one.
		if ( dpa_has_achievements( $args ) ) {
			while ( dpa_achievements() ) {
				dpa_the_achievement();

				// Do stuff here
			}
		}
	}

	do_action( 'dpa_after_handle_event', $event_name, $func_args, $user_ids );
}


/*function dpa_handle_action( $name, $func_args=null, $type='' ) {
	foreach ( $user_ids as $user_id ) {
		if ( dpa_has_achievements( array( 'user_id' => $user_id, 'type' => 'active_by_action', 'action' => $name ) ) ) {
			while ( dpa_achievements() ) {
				dpa_the_achievement();

				$site_id = apply_filters( 'dpa_handle_action_site_id', dpa_get_achievement_site_id(), $name, $func_args, $type, $user_id );
				if ( false === $site_id )
					continue;

				$site_is_valid = false;
				if ( !is_multisite() || $site_id < 1 || $blog_id == $site_id )
					$site_is_valid = true;

				$group_is_valid = false;
				if ( dpa_get_achievement_group_id() < 1 || dpa_is_group_achievement_valid( $name, $func_args, $user_id ) )
					$group_is_valid = true;

				$site_is_valid = apply_filters( 'dpa_handle_action_site_is_valid', $site_is_valid, $name, $func_args, $type, $user_id );
				$group_is_valid = apply_filters( 'dpa_handle_action_group_is_valid', $group_is_valid, $name, $func_args, $type, $user_id );

				if ( $site_is_valid && $group_is_valid )
					dpa_maybe_unlock_achievement( $user_id );
			}
		}
	}
}*/


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
?>