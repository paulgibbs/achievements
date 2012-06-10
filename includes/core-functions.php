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
			do_action( 'dpa_handle_event', $event_name, $func_args, $user_id, $args );

			// Allow plugins to stop any more processing for this achievement
			if ( false === apply_filters( 'dpa_handle_event_maybe_unlock_achievement', true, $event_name, $func_args, $user_id, $args ) )
				continue;

			// Look in the progress posts and match against a post_parent which is the same as the current achievement.
			$progress = wp_filter_object_list( achievements()->progress_query->posts, array( 'post_parent' => dpa_get_the_achievement_ID() ) );
			$progress = array_shift( $progress );

			// If the achievement hasn't already been unlocked, maybe_unlock_achievement.
			if ( empty( $progress ) || dpa_get_unlocked_status_id() != $progress->post_status )
				dpa_maybe_unlock_achievement( $user_id, false, $progress );
		}
	}

	unset( achievements()->achievement_query );
	unset( achievements()->progress_query    );

	// Everything's done. Let other plugins do things.
	do_action( 'dpa_after_handle_event', $event_name, $func_args, $user_id, $args );
}

/**
 * If the specified achievement's criteria has been met, we unlock the
 * achievement. Otherwise we record progress for the achievement for next time.
 *
 * $skip_validation is the second parameter for backpat with Achievements 2.x
 *
 * @param int     $user_id
 * @param string  $skip_validation  Optional. Set to "skip_validation" to skip Achievement validation (unlock achievement regardless of criteria).
 * @param object  $progress_obj     Optional. The Progress post object. Defaults to Progress object in the Progress loop.
 * @param object  $achievement_obj  Optional. The Achievement post object to maybe_unlock. Defaults to current object in Achievement loop.
 * @since 2.0
 */
function dpa_maybe_unlock_achievement( $user_id, $skip_validation = '', $progress_obj = null, $achievement_obj = null ) {
	// Default to current object in the achievement loop
	if ( empty( $achievement_obj ) )
		$achievement_obj = achievements()->achievement_query->post;

	// Default to progress object in the progress loop
	if ( empty( $progress_obj ) ) {
		$progress_obj = wp_filter_object_list( achievements()->progress_query->posts, array( 'post_parent' => $achievement_obj->ID ) );
		$progress_obj = array_shift( $progress_obj );
	}

	// Has the user already unlocked the achievement?
	if ( ! empty( $progress_obj ) && dpa_get_unlocked_status_id() == $progress_obj->post_status )
		return;

	// Prepare default values to create/update a progress post
	$progress_args = array(
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
		'post_author'    => $user_id,
		'post_parent'    => $achievement_obj->ID,
		'post_title'     => $achievement_obj->post_title,
		'post_type'      => dpa_get_progress_post_type(),
	);

	// If achievement already has some progress, grab the ID so we update the post later
	if ( ! empty( $progress_obj->ID ) )
		$progress_args['ID'] = $progress_obj->ID;

	// Does the achievement not have a target set or are we skipping validation?
	$achievement_target = (int) get_post_meta( $achievement_obj->ID, '_dpa_target', true );
	if ( empty( $achievement_target ) || 'skip_validation' === $skip_validation ) {

		// Unlock the achievement
		$progress_args['post_status'] = dpa_get_unlocked_status_id();


	// Does the achievement have a target set?
	} elseif ( ! empty( $achievement_target ) ) {

		// Increment progress count
		$progress_obj->content = (int) $progress_obj->content + apply_filters( 'dpa_maybe_unlock_achievement_progress_increment', 1 );

		// Does the progress count now meet the achievement target?
		if ( (int) $progress_obj->content >= $achievement_target ) {

			// Yes. Unlock achievement.
			$progress_args['post_status'] = dpa_get_unlocked_status_id();
		}
	}

	// Create or update the progress post
	$progress_id = wp_insert_post( $progress_args );

	// If the achievement was just unlocked, do stuff.
	if ( dpa_get_unlocked_status_id() == $progress_args['post_status'] ) {

		// Update user's points
		$points = dpa_get_user_points( $user_id ) + get_post_meta( $achievement_obj->ID, '_dpa_points', true );
		dpa_update_user_points( $user_id, $points );

		// Achievement was unlocked. Let other plugins do things.
		do_action( 'dpa_unlock_achievement', $achievement_obj, $user_id, $progress_obj, $progress_id );
	}
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