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
