<?php
/**
 * Achievement Progress post type functions.
 *
 * @package Achievements
 * @subpackage ProgressFunctions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Output the unique id of the custom post type for achievement_progress
 *
 * @since 3.0
 * @uses dpa_get_achievement_progress_post_type() To get the achievement_progress post type
 */
function dpa_achievement_progress_post_type() {
	echo dpa_get_achievement_progress_post_type();
}
	/**
	 * Return the unique id of the custom post type for achievement_progress
	 *
	 * @return string The unique post type id
	 * @since 3.0
	 */
	function dpa_get_achievement_progress_post_type() {
		return apply_filters( 'dpa_get_achievement_progress_post_type', achievements()->achievement_progress_post_type );
	}