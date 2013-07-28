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
 * When an achievement is unlocked, give the points to the user.
 *
 * @param object $achievement_obj The Achievement object to send a notification for.
 * @param int $user_id ID of the user who unlocked the achievement.
 * @param int $progress_id The Progress object's ID.
 * @since Achievements (3.0)
 */
function dpa_send_points( $achievement_obj, $user_id, $progress_id ) {
	// Let other plugins easily bypass sending points.
	if ( ! apply_filters( 'dpa_maybe_send_points', true, $achievement_obj, $user_id, $progress_id ) )
		return;

	// Get the user's current total points plus the point value for the unlocked achievement
	$points = dpa_get_user_points( $user_id ) + dpa_get_achievement_points( $achievement_obj->ID );
	$points = apply_filters( 'dpa_send_points_value', $points, $achievement_obj, $user_id, $progress_id );

	// Give points to user
	dpa_update_user_points( $points, $user_id );

	// Allow other things to happen after the user's points have been updated
	do_action( 'dpa_send_points', $achievement_obj, $user_id, $progress_id, $points );
}

/**
 * When an achievement is unlocked by a user, update various stats relating to the user.
 *
 * @param object $achievement_obj The Achievement object.
 * @param int $user_id ID of the user who unlocked the achievement.
 * @param int $progress_id The Progress object's ID.
 * @since Achievements (3.0)
 */
function dpa_update_user_stats( $achievement_obj, $user_id, $progress_id ) {
	// Let other plugins easily bypass updating user's stats
	if ( ! apply_filters( 'dpa_maybe_update_user_stats', true, $achievement_obj, $user_id, $progress_id ) )
		return;

	// Increment the user's current unlocked count
	$new_unlock_count = dpa_get_user_unlocked_count( $user_id ) + 1;
	$new_unlock_count = apply_filters( 'dpa_update_user_stats_value', $new_unlock_count, $achievement_obj, $user_id, $progress_id );

	// Update user's unlocked count
	dpa_update_user_unlocked_count( $user_id, $new_unlock_count );

	// Store the ID of the unlocked achievement for this user
	dpa_update_user_last_unlocked( $user_id, $achievement_obj->ID );

	// Allow other things to happen after the user's stats have been updated
	do_action( 'dpa_update_user_stats', $achievement_obj, $user_id, $progress_id, $new_unlock_count );
}

/**
 * Return the user ID whose profile we are in.
 * 
 * If BP integration is enabled, this will return bp_displayed_user_id().
 * If BP integration is not enabled, this will return get_queried_object()->ID.
 * 
 * This function should be used in conjunction with dpa_is_single_user_achievements().
 * 
 * @return int|false Returns user ID; if we aren't looking at a user's profile, return false.
 * @since Achievements (3.2)
 */
function dpa_get_displayed_user_id() {
	$retval = get_queried_object()->ID;

	if ( dpa_integrate_into_buddypress() && function_exists( 'bp_displayed_user_id' ) )
		$retval = bp_displayed_user_id();

	return apply_filters( 'dpa_get_displayed_user_id', $retval );
}

/**
 * Get the current state of the leaderboard, sorted by users' karma points.
 *
 * @param array $args Optional. Associatve array of optional arguments.
 * @since Achievements (3.4)
 */
function dpa_get_leaderboard( array $args = array() ) {
	global $wpdb;

	$defaults = array(
		'paged'          => dpa_get_paged(),                       // Page number
		'posts_per_page' => dpa_get_leaderboard_items_per_page(),  // Users per page
		'user_id'        => 0,                                     // Get details for a specific user if non-zero; pass an array of ints for >1 user.
	);

	$args = dpa_parse_args( $args, $defaults, 'get_leaderboard' );

	// No, we're not allowing infinite results. This is always a bad idea.
	if ( (int) $args['posts_per_page'] < 1 )
		$args['posts_per_page'] = dpa_get_leaderboard_items_per_page();

	$points_key = "{$wpdb->prefix}_dpa_points";
	$num_users  = ( $args['user_id'] !== 0 ) ? count( (array) $args['user_id'] ) : 0;


	/**
	 * Build the SQL
	 */

	// 1) Get all meta_values for everyone's "_dpa_points" keys as integers, and order descending.
	$subquery = $wpdb->prepare(
		"SELECT GROUP_CONCAT(DISTINCT ranking.meta_value ORDER BY CONVERT(ranking.meta_value, signed) DESC)
		FROM {$wpdb->usermeta} AS ranking
		WHERE ranking.meta_key = %s",
	$points_key );

	// I would add "LIMIT {$num_users}", but the MySQL docs say: "if LIMIT occurs within a subquery and also is applied in the outer query, the outermost LIMIT takes precedence".
	if ( $num_users > 0 )
		$subquery .= ' AND ranking.user_id IN (' . implode( ',', wp_parse_id_list( (array) $args['user_id'] ) ) . ')';


	// 2) Get each user's rank, user ID, and meta_value.
	$query = $wpdb->prepare(
		"SELECT SQL_CALC_FOUND_ROWS FIND_IN_SET( karma.meta_value, ({$subquery}) ) as rank, ID, karma.meta_value
		FROM {$wpdb->users} AS person
		INNER JOIN {$wpdb->usermeta} as karma ON person.id = karma.user_id AND karma.meta_key = %s",
	$points_key );

	/**
	 * Sort users correctly even if some of them don't have any karma points.
	 * `ORDER BY... rank` causes a table sort because usermeta has no index on meta_value, so only add this if we need it.
	 */
	if ( $num_users !== 1 )
		$query .= " ORDER BY CASE WHEN rank IS NULL THEN 1 ELSE 0 END, rank";

	// Handle pagination
	$offset  = ( (int) $args['paged'] - 1 ) * (int) $args['posts_per_page'];
	$query  .= $wpdb->prepare( ' LIMIT %d, %d', $offset, $args['posts_per_page'] );


	/**
	 * See if we already have this query in cache
	 */

	$last_changed = wp_cache_get( 'last_changed', 'achievements_leaderboard' );
	if ( ! $last_changed ) {
		$last_changed = microtime();
		wp_cache_set( 'last_changed', $last_changed, 'achievements_leaderboard' );
	}

	$cache_key = 'get_leaderboard:' . md5( serialize( $query ) ) . ":$last_changed";
	if ( $cache = wp_cache_get( $cache_key, 'achievements_leaderboard' ) )
		return $cache;
	// djpaultodo: what are we storing in cache here? what do we need to do to hte data?


	/**
	 * Run the query and cache results
	 */
	$results       = $wpdb->get_results( $query );
	$results_found = $wpdb->get_var( 'SELECT FOUND_ROWS()' );

	$results = array(
		'results' => $results,
		'total'   => (int) $results_found,
	);

	wp_cache_set( $cache_key, $results, 'achievements_leaderboard' );
}