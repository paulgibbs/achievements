<?php
/**
 * Achievements core functions
 *
 * The architecture of Achievements is straightforward, making use of custom post types,
 * statuses, and taxonomies (no custom tables or SQL queries). Custom rewrite rules and
 * endpoints are used to register and display templates, but this file primarily takes
 * care of the core logic -- when a user does something, how does that trigger an event
 * and award an achievement?
 *
 * The achievement post type has a taxonomy called dpa_event. An "event" is any
 * do_action in WordPress. An achievement post is assigned a term from that taxonomy.
 *
 * On every page load, we grab all terms from the dpa_event taxonomy that have been
 * associated with a post. The dpa_handle_event() function is then registered with
 * those actions, and that's what lets us detect when something interesting happens.
 *
 * dpa_handle_event() makes a WP_Query query of the achievement post type, passing
 * the name of the current action (aka event) as the "tax_query" parameter. This is
 * because multiple achievements could use the same event and we need details of each
 * of those achievements. At this point, we know that the user has maybe unlocked an
 * achievement.
 *
 * The aptly named dpa_maybe_unlock_achievement() function takes over. An achievement
 * has a criteria of how many times an event has to occur (in post meta) for a user
 * before that achievement is unlocked. If the criteria has not been met, then a
 * record of the progress is stored in another custom post type, dpa_progress. If the
 * criteria was met, the dpa_progress post's status is changed to "unlocked".
 *
 * Each achievement has points (in post meta) and those are added to the user's score
 * (in user meta). The user is then made aware that they've unlocked an achievement.
 *
 * @package Achievements
 * @subpackage CoreFunctions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Check if any of the plugin extensions need to be set up or updated.
 *
 * If a new extension is found (technically, an extension with an un-registered 'ID'), the
 * actions that the extension supports will be automatically added to the dpa_get_event_tax_id() taxonomy.
 * 
 * If an extension is updated (by changing the 'version' property), its actions WILL NOT be automatically updated.
 * You will need to implement custom upgrade handling in the do_update() method to add, remove, or update taxonomy
 * data for the extension.
 *
 * @since Achievements (3.0)
 */
function dpa_maybe_update_extensions() {

	// If Achievements is being deactivated, bail out
	if ( dpa_is_deactivation( achievements()->basename ) )
		return;

	// Only do things if the user is active (logged in and not a spammer), in wp-admin, and is not doing an import.
	if ( ! dpa_is_user_active() || ! is_admin() || defined( 'WP_IMPORTING' ) && WP_IMPORTING )
		return;

	// Check user has the ability to edit and create terms
	if ( ! current_user_can( 'edit_achievement_events' ) )
		return;

	$orig_versions = $versions = dpa_get_extension_versions();

	foreach ( achievements()->extensions as $extension ) {
		// Extensions must inherit the DPA_Extension class
		if ( ! is_a( $extension, 'DPA_Extension' ) )
			continue;

		// If no old version in $versions, it's a new extension. Add its actions to the dpa_event taxonomy.
		$id = $extension->get_id();
		if ( ! isset( $versions[$id] ) ) {
			$actions = $extension->get_actions();

			// Add the actions to the dpa_event taxonomy
			foreach ( $actions as $action_name => $action_desc )
				wp_insert_term( $action_name, dpa_get_event_tax_id(), array( 'description' => $action_desc ) );

			// Record version
			$versions[$id] = $extension->get_version();

		// Check if an update is available.
		} elseif ( version_compare( $extension->get_version(), $versions[$id], '>' ) ) {
			$extension->do_update( $versions[$id] );
			$versions[$id] = $extension->get_version();
		}
	}

	// If $versions has changed, update the option in the database
	if ( $orig_versions != $versions )
		dpa_update_extension_versions( $versions );

	// Allow other plugins to run any update routines for their extension
	do_action( 'dpa_maybe_update_extensions', $orig_versions, $versions );
}

/**
 * Achievement actions are stored in a custom taxonomy. This function queries that taxonomy to find
 * items and, with those items' slugs (which are the name of a WordPress action), registers them to a
 * handler action that contains the next part of the main logic. The user needs to be logged in for
 * this to hapen.
 *
 * Posts in trash are returned by get_terms() even if hide_empty is set. We double-check the post status
 * before we actually give the award.
 *
 * This function is invoked on every page load but as get_terms() provides built-in caching, we don't
 * have to worry too much. For multisite with the network-wide option enabled, we store the events
 * in a global cache object to avoid a call to switch_to_blog.
 *
 * @since Achievements (3.0)
 */
function dpa_register_events() {

	// If Achievements is being deactivated, bail out
	if ( dpa_is_deactivation( achievements()->basename ) )
		return;

	// Only do things if the user is active (logged in and not a spammer) and is not doing an import.
	if ( ( defined( 'WP_IMPORTING' ) && WP_IMPORTING ) || ! apply_filters( 'dpa_maybe_register_events', dpa_is_user_active() ) )
		return;

	$events = false;

	// If multisite and running network-wide, see if the terms have previously been cached.
	if ( is_multisite() && dpa_is_running_networkwide() )
		$events = wp_cache_get( 'dpa_registered_events', 'achievements_events' );

	// No cache. Get events.
	if ( $events === false ) {

		// If multisite and running network-wide, switch_to_blog to the data store site
		if ( is_multisite() && dpa_is_running_networkwide() )
			switch_to_blog( DPA_DATA_STORE );

		// Get all valid events from the event taxononmy. A valid event is one associated with a post type.
		$events = get_terms( achievements()->event_tax_id, array( 'hide_empty' => true )  );

		// No items were found. Bail out.
		if ( is_wp_error( $events ) || empty( $events ) ) {

			// If multisite and running network-wide, undo the switch_to_blog
			if ( is_multisite() && dpa_is_running_networkwide() )
				restore_current_blog();

			return;

		// Items were found! If network-wide, cache the results and undo the switch_to_blog.
		} elseif ( is_multisite() && dpa_is_running_networkwide() ) {
			restore_current_blog();
			wp_cache_add( 'dpa_registered_events', $events, 'achievements_events' );
		}
	}

	// Get terms' slugs
	$events = wp_list_pluck( (array) $events, 'slug' );
	$events = array_unique( (array) apply_filters( 'dpa_filter_events', $events ) );

	// For each event, add a handler function to the action.
	foreach ( (array) $events as $event )
		add_action( $event, 'dpa_handle_event', 12, 10 );  // Priority 12 in case object modified by other plugins

	// Allow other plugins to register extra events
	do_action( 'dpa_register_events', $events );
}

/**
 * Implements the Achievement actions and unlocks if criteria met.
 *
 * @see dpa_register_events()
 * @since Achievements (3.0)
 */
function dpa_handle_event() {
	// Look at the current_filter to find out what action has occured
	$event_name = current_filter();
	$func_args  = func_get_args();

	// Let other plugins do things before anything happens
	do_action( 'dpa_before_handle_event', $event_name, $func_args );

	// Allow other plugins to change the name of the event being processed, or to bail out early
	$event_name = apply_filters( 'dpa_handle_event_name', $event_name, $func_args );
	if ( false === $event_name )
		return;

	/**
	 * Extensions using the DPA_CPT_Extension base class may not capture their generic CPT
	 * actions if that same action was used with by another extension with a different post
	 * type. As no achievement will ever be associated with a generic action, if we're about
	 * to query for a generic action, bail out.
	 */
	foreach ( achievements()->extensions as $extension ) {
		if ( ! is_a( $extension, 'DPA_CPT_Extension' ) )
			continue;

		// Is $event_name a generic CPT action?
		if ( in_array( $event_name, $extension->get_generic_cpt_actions( array() ) ) )
				return;
	}

	// This filter allows the user ID to be updated (e.g. for draft posts which are then published by someone else)
	$user_id = absint( apply_filters( 'dpa_handle_event_user_id', get_current_user_id(), $event_name, $func_args ) );
	if ( ! $user_id )
		return;

	// Only proceed if the specified user is active (logged in and not a spammer)
	if ( ! dpa_is_user_active( $user_id ) )
		return;

	// Only proceed if the specified user can create progress posts
	if ( ! user_can( $user_id, 'publish_achievement_progresses' ) )
		return;

	// Find achievements that are associated with the $event_name taxonomy
	$args = array(
		'ach_event'             => $event_name,  // Get posts in the event taxonomy matching the event name
		'ach_populate_progress' => $user_id,     // Fetch Progress posts for this user ID
		'no_found_rows'         => true,         // Disable SQL_CALC_FOUND_ROWS
		'nopaging'              => true,         // No pagination
		'post_status'           => 'any',        // We only want published/private achievements, but need to compensate (see below)
		'posts_per_page'        => -1,           // No pagination
		's'                     => '',           // Stop sneaky people running searches on this query
	);

	// If multisite and running network-wide, switch_to_blog to the data store site
	if ( is_multisite() && dpa_is_running_networkwide() )
		switch_to_blog( DPA_DATA_STORE );

	// Loop through achievements found
	if ( dpa_has_achievements( $args ) ) {

		while ( dpa_achievements() ) {
			dpa_the_achievement();

			// Check that the post status is published or privately published
			// We need to check this here to work around WP_Query not
			// constructing the query correctly with private
			if ( ! in_array( $GLOBALS['post']->post_status, array( 'publish', 'private' ) ) )
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
			if ( empty( $progress ) || dpa_get_unlocked_status_id() !== $progress->post_status )
				dpa_maybe_unlock_achievement( $user_id, false, $progress );
		}
	}

	// If multisite and running network-wide, undo the switch_to_blog
	if ( is_multisite() && dpa_is_running_networkwide() )
		restore_current_blog();

	achievements()->achievement_query = new WP_Query();
	achievements()->leaderboard_query = new ArrayObject();
	achievements()->progress_query    = new WP_Query();

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
 * @since Achievements (2.0)
 */
function dpa_maybe_unlock_achievement( $user_id, $skip_validation = '', $progress_obj = null, $achievement_obj = null ) {
	// Only proceed if the specified user is active (logged in and not a spammer)
	if ( ! dpa_is_user_active( $user_id ) )
		return;

	// Only proceed if the specified user can create progress posts
	if ( ! user_can( $user_id, 'publish_achievement_progresses' ) )
		return;

	// Default to current object in the achievement loop
	if ( empty( $achievement_obj ) )
		$achievement_obj = achievements()->achievement_query->post;

	// Default to progress object in the progress loop
	if ( empty( $progress_obj ) && ! empty( achievements()->progress_query->posts ) ) {
		$progress_obj = wp_filter_object_list( achievements()->progress_query->posts, array( 'post_parent' => $achievement_obj->ID ) );
		$progress_obj = array_shift( $progress_obj );
	}

	// Has the user already unlocked the achievement?
	if ( ! empty( $progress_obj ) && dpa_get_unlocked_status_id() === $progress_obj->post_status )
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

	// If the achievement does not have a target set, this is an award achievement.
	$achievement_target = dpa_get_achievement_target( $achievement_obj->ID );
	if ( $achievement_target ) {

		// Increment progress count
		$progress_args['post_content'] = apply_filters( 'dpa_maybe_unlock_achievement_progress_increment', 1 );

		if ( ! empty( $progress_obj ) )
			$progress_args['post_content'] = (int) $progress_args['post_content'] + (int) $progress_obj->post_content;
	}

	// Does the progress count now meet the achievement target?
	if ( 'skip_validation' === $skip_validation || ( $achievement_target && (int) $progress_args['post_content'] >= $achievement_target ) ) {

		// Yes. Unlock achievement.
		$progress_args['post_status'] = dpa_get_unlocked_status_id();

	// No, user needs to make more progress. Make sure the locked status is set correctly.
	} else {
		$progress_args['post_status'] = dpa_get_locked_status_id();
	}

	// Create or update the progress post
	$progress_id = wp_insert_post( $progress_args );

	// If the achievement was just unlocked, do stuff.
	if ( dpa_get_unlocked_status_id() === $progress_args['post_status'] ) {

		// Achievement was unlocked. Notifications and points updates are hooked to this function.
		do_action( 'dpa_unlock_achievement', $achievement_obj, $user_id, $progress_id );
	}
}
