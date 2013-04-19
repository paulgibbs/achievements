<?php
/**
 * Achievement post type, endpoint, Event taxonomy, and other utility functions.
 *
 * @package Achievements
 * @subpackage AchievementsFunctions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Retrieves a list of achievement posts matching criteria
 *
 * Most of the values that $args can accept are documented in {@link WP_Query}. The custom values added by Achievements are as follows:
 * 'ach_event' - string - Loads achievements for a specific event. Matches a slug from the dpa_event tax. Default is empty.
 *
 * @param array|string $args All the arguments supported by {@link WP_Query}, and some more.
 * @return array Posts
 * @since Achievements (3.0)
 */
function dpa_get_achievements( $args = array() ) {

	$defaults = array(
		// Standard WP_Query params
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,                             // Disable SQL_CALC_FOUND_ROWS (used for pagination queries)
		'order'               => 'ASC',                            // 'ASC', 'DESC
		'orderby'             => 'title',                          // 'meta_value', 'author', 'date', 'title', 'modified', 'parent', rand'
		'post_type'           => dpa_get_achievement_post_type(),  // Only retrieve achievement posts
		'posts_per_page'      => -1,                               // Achievements per page

		// Achievements params
 		'ach_event'           => '',                               // Load achievements for a specific event
	);

	// Load achievements for a specific event
	if ( ! empty( $args['ach_event'] ) ) {

		$args['tax_query'] = array(
			array(
				'field'    => 'slug',
				'taxonomy' => dpa_get_event_tax_id(),
				'terms'    => $args['ach_event'],
			)
		);

		unset( $args['ach_event'] );
	}

	$args         = dpa_parse_args( $args, $defaults, 'get_achievements' );
	$achievements = new WP_Query;

	return apply_filters( 'dpa_get_achievements', $achievements->query( $args ), $args );
}

/**
 * Output the unique id of the custom post type for achievement
 *
 * @since Achievements (3.0)
 * @uses dpa_get_achievement_post_type() To get the achievement post type
 */
function dpa_achievement_post_type() {
	echo dpa_get_achievement_post_type();
}
	/**
	 * Return the unique id of the custom post type for achievement
	 *
	 * @return string The unique post type id
	 * @since Achievements (3.0)
	 */
	function dpa_get_achievement_post_type() {
		return apply_filters( 'dpa_get_achievement_post_type', achievements()->achievement_post_type );
	}

/**
 * Output the id of the authors achievements endpoint
 *
 * @since Achievements (3.0)
 * @uses dpa_get_authors_endpoint() To get the authors achievements endpoint
 */
function dpa_authors_endpoint() {
	echo dpa_get_authors_endpoint();
}
	/**
	 * Return the id of the authors achievements endpoint
	 *
	 * @return string The endpoint
	 * @since Achievements (3.0)
	 */
	function dpa_get_authors_endpoint() {
		return apply_filters( 'dpa_get_authors_endpoint', achievements()->authors_endpoint );
	}

/**
 * Return the event taxonomy ID
 *
 * @since Achievements (3.0)
 * @return string
 */
function dpa_get_event_tax_id() {
	return apply_filters( 'dpa_get_event_tax_id', achievements()->event_tax_id );
}

/**
 * Return the total count of the number of achievements
 *
 * @return int Total achievement count
 * @since Achievements (3.0)
 */
function dpa_get_total_achievement_count() {
	$counts = wp_count_posts( dpa_get_achievement_post_type() );
	return apply_filters( 'dpa_get_total_achievement_count', (int) $counts->publish );
}

/**
 * When an achievement is unlocked, update various stats relating to the achievement.
 *
 * @param object $achievement_obj The Achievement object.
 * @param int $user_id ID of the user who unlocked the achievement.
 * @param int $progress_id The Progress object's ID.
 * @since Achievements (3.0)
 */
function dpa_update_achievement_stats( $achievement_obj, $user_id, $progress_id ) {
	// Update the 'last unlocked achievement' stats
	dpa_stats_update_last_achievement_id( $achievement_obj->ID );
	dpa_stats_update_last_achievement_user_id( $user_id );

	// Allow other plugins to update their own stats when an achievement is unlocked
	do_action( 'dpa_update_achievement_stats', $achievement_obj, $user_id, $progress_id );
}

/**
 * Returns details of all events from the event taxonomy, and groups the events by extension.
 *
 * This is used in the new/edit post type screen, but can be used anywhere where you need to
 * show a list of all events grouped by the extension which provides them.
 *
 * @return array
 * @since Achievements (3.0)
 */
function dpa_get_all_events_details() {
	$temp_events = array();

	// Get all events from the event taxonomy and sort them by the plugin which provides them
	$events = get_terms( achievements()->event_tax_id, array( 'hide_empty' => false ) );

	foreach ( $events as $event ) {

		// Find out which plugin provides this event
		foreach ( achievements()->extensions as $extension ) {
			if ( ! is_a( $extension, 'DPA_Extension' ) )
				continue;

			// If this extension contains this event
			if ( array_key_exists( $event->name, $extension->get_actions() )) {
				if ( ! isset( $temp_events[$extension->get_name()] ) )
					$temp_events[$extension->get_name()] = array();

				// Store term description and ID
				$temp_events[$extension->get_name()][] = array( 'description' => $event->description, 'id' => $event->term_id );
				break;
			}
		}

	}
	$events = $temp_events;

	return apply_filters( 'dpa_get_all_events_details', $events );
}

/**
 * Called before a post is deleted; if an achievement post, we tidy up any related Progress posts.
 * 
 * This function is supplemental to the actual achievement deletion which is handled by WordPress core API functions.
 * It is used to clean up after an achievement that is being deleted.
 *
 * @param int $post_id Optional; post ID that is being deleted.
 * @since Achievements (3.0)
 */
function dpa_before_achievement_deleted( $post_id = 0 ) {
	$post_id = dpa_get_achievement_id( $post_id );
	if ( empty( $post_id ) || ! dpa_is_achievement( $post_id ) )
		return;

	do_action( 'dpa_before_achievement_deleted', $post_id );

	// An achievement is being permanently deleted, so any related Progress posts have to go, too.
	$progress = new WP_Query( array(
		'fields'           => 'id=>parent',
		'nopaging'         => true,
		'post_parent'      => $post_id,
		'post_status'      => array( dpa_get_locked_status_id(), dpa_get_unlocked_status_id() ),
		'post_type'        => dpa_get_progress_post_type(),
		'posts_per_page'   => -1,
		'suppress_filters' => true,
	) );

	if ( empty( $progress ) )
		return;

	foreach ( $progress->posts as $post ) 
		wp_delete_post( $post->ID, true );
}

/**
 * Handles the redeem achievement form submission.
 * 
 * Finds any achievements with the specific redemption code, and if the user hasn't already unlocked
 * that achievement, it's awarded to the user.
 *
 * @param string $action Optional. If 'dpa-redeem-achievement', handle the form submission.
 * @since Achievements (3.1)
 */
function dpa_form_redeem_achievement( $action = '' ) {
	if ( 'dpa-redeem-achievement' !== $action || ! dpa_is_user_active() )
		return;

	// Check required form values are present
	$redemption_code = isset( $_POST['dpa_code'] ) ? strip_tags( stripslashes( $_POST['dpa_code'] ) ) : '';
	$redemption_code = apply_filters( 'dpa_form_redeem_achievement_code', $redemption_code );

	if ( empty( $redemption_code ) || ! dpa_verify_nonce_request( 'dpa-redeem-achievement' ) )
		return;

	// If multisite and running network-wide, switch_to_blog to the data store site
	if ( is_multisite() && dpa_is_running_networkwide() )
		switch_to_blog( DPA_DATA_STORE );

	// Find achievements that match the same redemption code
	$achievements = dpa_get_achievements( array(
		'meta_key'   => '_dpa_redemption_code',
		'meta_value' => $redemption_code,
	) );

	// Bail out early if no achievements found
	if ( empty( $achievements ) ) {
		dpa_add_error( 'dpa_redeem_achievement_nonce', __( 'That code was invalid. Try again!', 'dpa' ) );

		// If multisite and running network-wide, undo the switch_to_blog
		if ( is_multisite() && dpa_is_running_networkwide() )
			restore_current_blog();

		return;
	}

	$existing_progress = dpa_get_progress( array(
		'author' => get_current_user_id(),
	) );

	foreach ( $achievements as $achievement_obj ) {
		$progress_obj = array();

		// If we have existing progress, pass that to dpa_maybe_unlock_achievement().
		foreach ( $existing_progress as $progress ) {
			if ( $achievement_obj->ID === $progress->post_parent ) {

				// If the user has already unlocked this achievement, don't give it to them again.
				if ( dpa_get_unlocked_status_id() == $progress->post_status )
					$progress_obj = false;
				else
					$progress_obj = $progress;
	
				break;
			}
		}

		if ( false !== $progress_obj )
			dpa_maybe_unlock_achievement( get_current_user_id(), 'skip_validation', $progress_obj, $achievement_obj );
	}

	// If multisite and running network-wide, undo the switch_to_blog
	if ( is_multisite() && dpa_is_running_networkwide() )
		restore_current_blog();
}

/**
 * Returns either the ranking for the current user (single row result) or for all users (limited to the current page, if not overriden).
 * 
 *
 * @param bool $show_current_user Optional. If true will get the ranking for the current user only. Default is false.
 * @param int $offset Optional. If set, will start the query from a given offset record. Default is to use normal pagination offset.
 * @param int $posts_per_page Optional. If set, will change the number of records returned per page. Default is Wordpress default value.
 * 
 * @return array. Two-dimensional array is returned, array["restults"] holds search results, while array["total_number_of_pages"] holds the max number of pages which can be used for pagination links.
 * @since Achievements (3.2.2)
 * @author Mike Bronner <mike.bronner@gmail.com>
 */
function dpa_get_leaderboard_rankings($show_current_user = false, $offset = null, $posts_per_page = null)
{
	global $wpdb;
	
	if ($show_current_user)
	{
		get_currentuserinfo();
	}
	if (null === $posts_per_page)
	{
		$posts_per_page = intval(get_query_var('posts_per_page'));
	}
	$db_prefix = $wpdb->base_prefix;
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $posts_per_page = intval(get_query_var('posts_per_page'));
    if (null === $offset)
    {
	    $offset = ($paged - 1) * $posts_per_page;
	}
	$leaderboard_query = "SELECT SQL_CALC_FOUND_ROWS
				person.*
				,nick.meta_value AS nickname
				,SUM(karma.meta_value) AS total_karma
				,FIND_IN_SET(karma.meta_value, (SELECT  GROUP_CONCAT(DISTINCT ranking.meta_value ORDER BY ranking.meta_value  DESC) FROM konb_usermeta AS ranking WHERE ranking.meta_key = 'konb__dpa_points')) as rank
			FROM " . $db_prefix . "users AS person
			LEFT JOIN " . $db_prefix . "usermeta as nick
				ON person.id = nick.user_id
				AND nick.meta_key = 'nickname'
			LEFT JOIN " . $db_prefix . "usermeta as karma
				ON person.id = karma.user_id
				AND karma.meta_key = '" . $db_prefix . "_dpa_points'
		WHERE 1 = 1";
	if ($show_current_user)
	{
		$leaderboard_query .= "
			AND ID = " . $current_user->ID;
	}
	$leaderboard_query .= "
		GROUP BY nick.meta_value
			,person.ID
		ORDER BY total_karma DESC
			,person.user_registered ASC";
	if ($show_current_user)
	{
		$leaderboard_query .= "
		LIMIT 0, 1;";
	}
	else
	{
		$leaderboard_query .= "
		LIMIT " . $offset . ", " . $posts_per_page . ";";
	}
	$leaderboard["results"] = $wpdb->get_results($wpdb->prepare($leaderboard_query, null));
	$sql_posts_total = $wpdb->get_var( "SELECT FOUND_ROWS();" );
    $leaderboard["total_number_of_pages"] = ceil($sql_posts_total / $posts_per_page);

    return $leaderboard;
}
