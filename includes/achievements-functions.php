<?php
/**
 * Achievement post type functions.
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