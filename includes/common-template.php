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
 * @param mixed $the_post Optional. Post object or post ID.
 * @return bool
 * @since 3.0
 */
function dpa_is_custom_post_type( $the_post = false ) {
	$retval = false;

	// Viewing one of Achievements' post types
	if ( in_array( get_post_type( $the_post ), array( dpa_get_achievement_post_type(), dpa_get_progress_post_type(), ) ) )
		$retval = true;

	return (bool) apply_filters( 'dpa_is_custom_post_type', $retval, $the_post );
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
	return (bool) ( dpa_get_query_name() == $name );
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