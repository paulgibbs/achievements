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