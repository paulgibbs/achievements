<?php
/**
 * Achievement Progress post type and post status functions.
 *
 * @package Achievements
 * @subpackage ProgressFunctions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Retrieves a list of progress posts matching criteria
 *
 * @param array|string $args All the arguments supported by {@link WP_Query}, and some more.
 * @return array Posts
 * @since Achievements (3.0)
 */
function dpa_get_progress( $args = array() ) {

	$defaults = array(
		// Standard WP_Query params
		'no_found_rows'  => true,                          // Disable SQL_CALC_FOUND_ROWS (used for pagination queries)
		'order'          => 'DESC',                        // 'ASC', 'DESC
		'orderby'        => 'date',                        // 'meta_value', 'author', 'date', 'title', 'modified', 'parent', 'rand'
		'post_type'      => dpa_get_progress_post_type(),  // Only retrieve progress posts
		'posts_per_page' => -1,                            // Progresses per page
	);

	$args     = dpa_parse_args( $args, $defaults, 'get_progress' );
	$progress = new WP_Query;

	return apply_filters( 'dpa_get_progress', $progress->query( $args ), $args );
}

/**
 * Output the unique id of the custom post type for achievement_progress
 *
 * @since Achievements (3.0)
 * @uses dpa_get_progress_post_type() To get the achievement_progress post type
 */
function dpa_achievement_progress_post_type() {
	echo dpa_get_progress_post_type();
}
	/**
	 * Return the unique id of the custom post type for achievement_progress
	 *
	 * @return string The unique post type id
	 * @since Achievements (3.0)
	 */
	function dpa_get_progress_post_type() {
		return apply_filters( 'dpa_get_progress_post_type', achievements()->achievement_progress_post_type );
	}

/**
 * Return the locked (achievement progress) post status ID
 *
 * @return string
 * @since Achievements (3.0)
 */
function dpa_get_locked_status_id() {
	return apply_filters( 'dpa_get_locked_status_id', achievements()->locked_status_id ) ;
}

/**
 * Return the unlocked (achievement progress) post status ID
 *
 * @return string
 * @since Achievements (3.0)
 */
function dpa_get_unlocked_status_id() {
	return apply_filters( 'dpa_get_unlocked_status_id', achievements()->unlocked_status_id );
}

/**
 * Delete a user's progress for an achievement. Essentially, un-reward the achievement for this user.
 *
 * @param int $achievement_id Achievement ID
 * @param int $user_id User ID
 * @since Achievements (3.0)
 */
function dpa_delete_achievement_progress( $achievement_id, $user_id ) {
	$achievement_id = dpa_get_achievement_id( $achievement_id );
	if ( empty( $achievement_id ) || ! dpa_is_achievement( $achievement_id ) )
		return;

	do_action( 'dpa_before_delete_achievement_progress', $achievement_id, $user_id, $progress_id  );

	dpa_has_progress( array(
		'ach_populate_achievements' => true,
		'author'                    => $user_id,
		'posts_per_page'            => -1,
	) );

	// Look in the progress posts and match against a post_parent that is the same as the desired achievement
	$progress = wp_filter_object_list( achievements()->progress_query->posts, array( 'post_parent' => $achievement_id ) );
	$progress = array_shift( $progress );

	if ( empty( $progress ) )
		return;

	$progress_id = apply_filters( 'dpa_delete_achievement_progress', $progress->ID, $achievement_id, $user_id );
	wp_delete_post( $progress_id, true );
}
