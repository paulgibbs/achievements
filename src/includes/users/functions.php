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
 * If you try to use this function, you will need to implement your own switch_to_blog() and wp_reset_postdata() handling if running in a multisite
 * and in a dpa_is_running_networkwide() configuration, otherwise the data won't be fetched from the appropriate site.
 *
 * This function accept a 'user_ids' parameter in the $argument, which accepts an array of user IDs.
 * It is only useful if you want to create a leaderboard that only contains the specified users; for example,
 * you and your friends could have your own mini-league, or in BuddyPress, each Group could have its own leaderboard.
 *
 * It is totally useless if you're trying to find the position for one or more specific users in the *overall* leaderboard.
 *
 * @param array $args Optional. Associative array of optional arguments. See function for details.
 * @return array|bool If no results, false. Otherwise, an associative array: array('results' => array([0] => array('rank' => int, 'user_id' => int, 'karma' => int, 'display_name' => string), ...), 'total' => int).
 * @since Achievements (3.4)
 */
function dpa_get_leaderboard( array $args = array() ) {
	global $wpdb;

	$defaults = array(
		'paged'           => dpa_get_leaderboard_paged(),           // Page number
		'populate_extras' => true,                                  // Whether to fetch users' display names. If you just want user IDs, set this to false.
		'posts_per_page'  => dpa_get_leaderboard_items_per_page(),  // Users per page
		'user_ids'        => array(),                               // Get details for specific users; pass an array of ints.
	);

	$args       = dpa_parse_args( $args, $defaults, 'get_leaderboard' );
	$points_key = "{$wpdb->prefix}_dpa_points";
	$num_users  = empty( $args['user_ids'] ) ? 0 : count( (array) $args['user_ids'] );

	// No, we're not allowing infinite results. This is always a bad idea.
	if ( (int) $args['posts_per_page'] < 1 )
		$args['posts_per_page'] = dpa_get_leaderboard_items_per_page();

	// We use this later to help get/set the object cache
	$last_changed = wp_cache_get( 'last_changed', 'achievements_leaderboard' );
	if ( $last_changed === false ) {
		$last_changed = microtime();
		wp_cache_add( 'last_changed', $last_changed, 'achievements_leaderboard' );
	}


	/**
	 * 1) Get all the distinct values of the _dpa_points keys from the usermeta table.
	 *
	 * We do the SELECT DISTINCT and sorting in PHP because meta_value is not indexed; this would cause use MySQL to use a temp table.
	 */
	$points_query = $wpdb->prepare(
		"SELECT meta_value
		FROM {$wpdb->usermeta}
		WHERE meta_key = %s",
	$points_key );

	if ( $num_users > 0 )
		$points_query .= $wpdb->prepare( ' AND user_id IN (' . implode( ',', wp_parse_id_list( (array) $args['user_ids'] ) ) . ') LIMIT %d', $num_users );

	// Only query if not in cache
	$points_cache_key = 'get_leaderboard_points' . md5( serialize( $points_query ) ) . ":$last_changed";
	$points           = wp_cache_get( $points_cache_key, 'achievements_leaderboard_ids' );

	if ( $points === false ) {
		$points = $wpdb->get_col( $points_query );
		wp_cache_add( $points_cache_key, $points, 'achievements_leaderboard_ids' );
	}

	if ( empty( $points ) ) {

		// If points is empty, no-one has any karma, so bail out.
		return array(
			'results' => array(),
			'total'   => 0,
		);
	}

	/**
	 * Can't use wp_parse_id_list() here because that casts the values to unsigned ints.
	 * The leaderboard might contain users with negative karma point totals.
	 */
	$points = array_unique( array_map( 'intval', $points ) );

	rsort( $points, SORT_NUMERIC );     // Sort descending for FIND_IN_SET
	$points = implode( ',', $points );  // Format for FIND_IN_SET

	/**
	 * 2a) Start building the SQL to get each user's rank, user ID, and points total.
	 */
	$query = $wpdb->prepare(
		"SELECT SQL_CALC_FOUND_ROWS FIND_IN_SET( karma.meta_value, %s ) as rank, ID as user_id, karma.meta_value as karma
		FROM {$wpdb->users} AS person
		INNER JOIN {$wpdb->usermeta} as karma ON person.ID = karma.user_id AND karma.meta_key = %s",
	$points,
	$points_key );

	/**
	 * 2b) Sort users correctly even if some of them don't have any karma points.
	 *
	 * `ORDER BY... rank` causes a filesort because usermeta has no index on meta_value :(
	 */
	$query .= ' ORDER BY CASE WHEN rank IS NULL THEN 1 ELSE 0 END, rank';

	/**
	 * 2c) Handle pagination
	 */
	$offset  = ( (int) $args['paged'] - 1 ) * (int) $args['posts_per_page'];
	$query  .= $wpdb->prepare( ' LIMIT %d, %d', $offset, $args['posts_per_page'] );


	/**
	 * 3) Run the query and cache results
	 */
	$cache_key = 'get_leaderboard:' . md5( serialize( $query ) ) . ":$last_changed";
	$results   = wp_cache_get( $cache_key, 'achievements_leaderboard' );

	// Only query if not in cache
	if ( $results === false ) {
		$results       = $wpdb->get_results( $query );
		$results_found = $wpdb->get_var( 'SELECT FOUND_ROWS()' );

		// All the returned values should be ints, not strings, so cast them here.
		foreach ( $results as $result ) {
			foreach ( $result as &$value )
				$value = (int) $value;
		}

		$results = array(
			'results' => $results,
			'total'   => (int) $results_found,
		);

		wp_cache_add( $cache_key, $results, 'achievements_leaderboard' );
	}


	/**
	 * 4) Maybe get users' display names
	 */
	if ( $args['populate_extras'] ) {
		$users = get_users( array(
			'fields'  => array( 'ID', 'display_name' ),
			'include' => wp_list_pluck( $results['results'], 'user_id' ),
		) );

		// For now, handle any cached user IDs for spammers or deleted users by setting a blank display name.
		foreach ( $results['results'] as &$leaderboard_user )
			$leaderboard_user->display_name = '';

		foreach ( $users as $user ) {
			foreach ( $results['results'] as &$leaderboard_user ) {
				if ( (int) $user->ID === $leaderboard_user->user_id ) {
					$leaderboard_user->display_name = $user->display_name;
					break;
				}
			}
		}
	}

	// Why an ArrayObject? See http://stackoverflow.com/questions/10454779/php-indirect-modification-of-overloaded-property
	return apply_filters( 'dpa_get_leaderboard', new ArrayObject( $results ), $defaults, $args, $points_cache_key, $cache_key );
}