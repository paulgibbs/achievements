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
