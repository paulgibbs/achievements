<?php
/**
 * Common functions
 *
 * Common functions are ones that are used by more than one component, like
 * achievements, achievement_progress, events taxonomy...
 *
 * @package Achievements
 * @subpackage CommonFunctions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Assist pagination by returning correct page number
 *
 * @global WP_Query $wp_query
 * @return int Current page number
 * @since 3.0
 */
function dpa_get_paged() {
	global $wp_query;

	// Check the query var
	if ( get_query_var( 'paged' ) ) {
		$paged = get_query_var( 'paged' );

	// Check query paged
	} elseif ( !empty( $wp_query->query['paged'] ) ) {
		$paged = $wp_query->query['paged'];
	}

	// Paged found
	if ( !empty( $paged ) )
		return (int) $paged;

	// Default to first page
	return 1;
}

/**
 * Provides post_parent__in and __not_in support.
 *
 * @global WP $wp
 * @global WPDB $wpdb
 * @param string $where
 * @param WP_Query $object
 * @return string
 * @see http://core.trac.wordpress.org/ticket/13927/
 * @since 3.0
 */
function dpa_query_post_parent__in( $where, $object = null ) {
	global $wp, $wpdb;

	// Noop if WP core supports this already
	if ( in_array( 'post_parent__in', $wp->private_query_vars ) )
		return $where;

	// Other plugins or themes might implement something like this. Check for known implementations.
	if ( function_exists( 'bbp_query_post_parent__in' ) || class_exists( 'Ideation_Gallery_Sidebar' ) )
		return $where;

	// Bail if no WP_Query object passed
	if ( empty( $object ) )
		return $where;

	// Only 1 post_parent so return $where
	if ( is_numeric( $object->query_vars['post_parent'] ) )
		return $where;

	// Including specific post_parent's
	if ( ! empty( $object->query_vars['post_parent__in'] ) ) {
		$ids    = implode( ',', array_map( 'absint', $object->query_vars['post_parent__in'] ) );
		$where .= " AND $wpdb->posts.post_parent IN ($ids)";

	// Excluding specific post_parent's
	} elseif ( ! empty( $object->query_vars['post_parent__not_in'] ) ) {
		$ids    = implode( ',', array_map( 'absint', $object->query_vars['post_parent__not_in'] ) );
		$where .= " AND $wpdb->posts.post_parent NOT IN ($ids)";
	}

	// Return possibly modified $where
	return $where;
}

/**
 * Return the view's query arguments
 *
 * @param string $view View name
 * @return array Query arguments
 * @since 3.0
 */
function dpa_get_view_query_args( $view ) {
	$view   = dpa_get_view_id( $view );
	$retval = ! empty( $view ) ? achievements()->views[$view]['query'] : false;

	return apply_filters( 'dpa_get_view_query_args', $retval, $view );
}

/**
 * Adds an error message to later be output in the theme
 *
 * @param string $code Unique code for the error message
 * @param string $message Translated error message
 * @param string $data Any additional data passed with the error message
 * @since 3.0
 */
function dpa_add_error( $code = '', $message = '', $data = '' ) {
	achievements()->errors->add( $code, $message, $data );
}

/**
 * Check if error messages exist in queue
 *
 * @since 3.0
 */
function dpa_has_errors() {
	// Assume no errors
	$has_errors = false;

	// Check for errors
	if ( achievements()->errors->get_error_codes() )
		$has_errors = true;

	return apply_filters( 'dpa_has_errors', $has_errors, achievements()->errors );
}

/**
 * Output the Achievements version
 *
 * @since 3.0
 */
function dpa_version() {
	echo dpa_get_version();
}
	/**
	 * Return the Achievements version
	 *
	 * @since 3.0
	 * @return string The Achievements version
	 */
	function dpa_get_version() {
		return achievements()->version;
	}

/**
 * Output the Achievements database version
 *
 * @uses dpa_get_version() To get the Achievements DB version
 */
function dpa_db_version() {
	echo dpa_get_db_version();
}
	/**
	 * Return the Achievements database version
	 *
	 * @since 3.0
	 * @return string The Achievements version
	 */
	function dpa_get_db_version() {
		return achievements()->db_version;
	}


/**
 * Rewrite IDs
 */

/**
 * Return the unique ID for achievement view rewrite rules
 *
 * @return string
 * @since 3.0
 */
function dpa_get_view_rewrite_id() {
	return achievements()->view_id;
}