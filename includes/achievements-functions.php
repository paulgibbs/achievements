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
 * Output the unique id of the custom post type for achievement
 *
 * @since 3.0
 * @uses dpa_get_achievement_post_type() To get the achievement post type
 */
function dpa_achievement_post_type() {
	echo dpa_get_achievement_post_type();
}
	/**
	 * Return the unique id of the custom post type for achievement
	 *
	 * @return string The unique post type id
	 * @since 3.0
	 */
	function dpa_get_achievement_post_type() {
		return apply_filters( 'dpa_get_achievement_post_type', achievements()->achievement_post_type );
	}

/**
 * Output the id of the authors achievements endpoint
 *
 * @since 3.0
 * @uses dpa_get_authors_endpoint() To get the authors achievements endpoint
 */
function dpa_authors_endpoint() {
	echo dpa_get_authors_endpoint();
}
	/**
	 * Return the id of the authors achievements endpoint
	 *
	 * @return string The endpoint
	 * @since 3.0
	 */
	function dpa_get_authors_endpoint() {
		return apply_filters( 'dpa_get_authors_endpoint', achievements()->authors_endpoint );
	}

/**
 * Return the event taxonomy ID
 *
 * @since 3..0
 * @return string
 */
function dpa_get_event_tax_id() {
	return apply_filters( 'dpa_get_event_tax_id', achievements()->event_tax_id );
}

/**
 * Return the total count of the number of achievements
 *
 * @return int Total achievement count
 * @since 3.0
 */
function dpa_get_total_achievement_count() {
	$counts = wp_count_posts( dpa_get_achievement_post_type() );
	return apply_filters( 'dpa_get_total_achievement_count', (int) $counts->publish );
}

/**
 * When an achievement is unlocked, update various stats.
 *
 * @param object $achievement_obj The Achievement object.
 * @param int $user_id ID of the user who unlocked the achievement.
 * @param int $progress_id The Progress object's ID.
 * @since 3.0
 */
function dpa_update_achievement_stats( $achievement_obj, $user_id, $progress_id ) {
	// Update the 'last unlocked achievement' stats
	dpa_stats_update_last_achievement_id( $achievement_obj->ID );
	dpa_stats_update_last_achievement_user_id( $user_id );

	// Allow other plugins to update their own stats when an achievement is unlocked
	do_action( 'dpa_update_achievement_stats', $achievement_obj, $user_id, $progress_id );
}
