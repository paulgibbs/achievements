<?php
/**
 * Common template tags
 *
 * @package Achievements
 * @subpackage CommonTemplate
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Check if the current post type belongs to Achievements
 *
 * @return bool
 * @since 3.0
 */
function dpa_is_custom_post_type() {
	// Current post type
	$post_type = get_post_type();

	// Achievements' post types
	$achievements_post_types = array(
		dpa_get_achievement_post_type(),
		dpa_get_achievement_progress_post_type(),
	);

	// Viewing one of Achievements' post types
	if ( in_array( $post_type, $achievements_post_types ) )
		return true;

	return false;
}


/**
 * Query functions
 */

/**
 * Check the passed parameter against the current _dpa_query_name
 *
 * @return bool True if match, false if not
 * @since 3.0
 */
function dpa_is_query_name( $query_name )  {

	// No empties
	if ( empty( $query_name ) )
		return false;

	// Check if query var matches
	if ( dpa_get_query_name() == $query_name )
		return true;

	// No match
	return false;
}

/**
 * Get the '_dpa_query_name' setting
 *
 * @return string To return the query var value
 * @since 3.0
 */
function dpa_get_query_name() {
	return get_query_var( '_dpa_query_name' );
}
?>