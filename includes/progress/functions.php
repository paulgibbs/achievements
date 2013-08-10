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
 * If you try to use this function, you will need to implement your own switch_to_blog and wp_reset_postdata() handling if running in a multisite
 * and in a dpa_is_running_networkwide() configuration, otherwise the data won't be fetched from the appropriate site.
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

		'post_status'    => array(                         // Locked/unlocked post statuses
			dpa_get_locked_status_id(),
			dpa_get_unlocked_status_id(),
		),
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

	$progress_id = dpa_get_progress( array(
		'author'        => $user_id,
		'fields'        => 'ids',
		'no_found_rows' => true,
		'nopaging'      => true,
		'numberposts'   => 1,
		'post_parent'   => $achievement_id,
	) );

	if ( empty( $progress_id ) )
		return;

	$progress_id = apply_filters( 'dpa_delete_achievement_progress', array_pop( $progress_id ), $achievement_id, $user_id );
	do_action( 'dpa_before_delete_achievement_progress', $progress_id, $achievement_id, $user_id );

	wp_delete_post( $progress_id, true );

	// Check that the delete achievement isn't in the user's pending notifications
	$notifications = dpa_get_user_notifications( $user_id );
	if ( isset( $notifications[$achievement_id] ) )
		unset( $notifications[$achievement_id]);

	// Update the user's notifications in case we cleared any above
	dpa_update_user_notifications( $notifications, $user_id );

	// Decrease user unlocked count
	dpa_update_user_unlocked_count( $user_id, dpa_get_user_unlocked_count( $user_id ) - 1 );

	/**
	 * If the progress was linked to an achievement that is the same achievement that is stored in
	 * this user's "last unlocked" meta, then clear the "last unlocked" meta, too.
	 */
	if ( (int) dpa_get_user_last_unlocked( $user_id ) === $achievement_id )
		dpa_update_user_last_unlocked( $user_id, 0 );

	do_action( 'dpa_after_delete_achievement_progress', $progress_id, $achievement_id, $user_id );
}
