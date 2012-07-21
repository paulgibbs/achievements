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

/**
 * Set the '_dpa_query_name' setting to $name
 *
 * @param string $name What to set the query var to
 * @since 3.0
 */
function dpa_set_query_name( $name = '' )  {
	set_query_var( '_dpa_query_name', $name );
}

/**
 * Used to clear the '_dpa_query_name' setting
 *
 * @since 3.0
 */
function dpa_reset_query_name() {
	dpa_set_query_name();
}


/**
 * Views
 */

/**
 * Output the view id
 *
 * @param string $view Optional. View id
 * @since 3.0
 */
function dpa_view_id( $view = '' ) {
	echo dpa_get_view_id( $view );
}

	/**
	 * Get the view id
	 *
	 * If a view id is supplied, that is used. Otherwise the 'dpa_view' query var is checked for.
	 *
	 * @param string $view Optional. View id.
	 * @return bool|string ID on success, false on failure
	 * @since 3.0
	 */
	function dpa_get_view_id( $view = '' ) {
		$view = ! empty( $view ) ? sanitize_title( $view ) : get_query_var( 'dpa_view' );

		if ( array_key_exists( $view, achievements()->views ) )
			return $view;

		return false;
	}

/**
 * Output the view name aka title
 *
 * @param string $view Optional. View id
 * @since 3.0
 */
function dpa_view_title( $view = '' ) {
	echo dpa_get_view_title( $view );
}

	/**
	 * Get the view name aka title
	 *
	 * If a view id is supplied, that is used. Otherwise the bbp_view
	 * query var is checked for.
	 *
	 * @since 3.0
	 *
	 * @param string $view Optional. View id
	 * @return bool|string Title on success, false on failure
	 */
	function dpa_get_view_title( $view = '' ) {
		$view = dpa_get_view_id( $view );
		if ( empty( $view ) )
			return false;

		return achievements()->views[$view]['title'];
	}

/**
 * Output the view url
 *
 * @param string $view Optional. View id
 * @since 3.0
 */
function dpa_view_url( $view = false ) {
	echo dpa_get_view_url( $view );
}
	/**
	 * Return the view url
	 *
	 * @global unknown $wp_rewrite
	 * @param string $view Optional. View id
	 * @return string View url (or home url if the view was not found)
	 * @since 3.0
	 */
	function dpa_get_view_url( $view = false ) {
		global $wp_rewrite;

		$view = dpa_get_view_id( $view );
		if ( empty( $view ) )
			return home_url();

		// Pretty permalinks
		if ( $wp_rewrite->using_permalinks() ) {
			$url = $wp_rewrite->root . dpa_get_view_slug() . '/' . $view;
			$url = home_url( user_trailingslashit( $url ) );

		// Unpretty permalinks
		} else {
			$url = add_query_arg( array( 'dpa_view' => $view ), home_url( '/' ) );
		}

		return apply_filters( 'dpa_get_view_url', $url, $view );
	}


/**
 * Add-on Actions
 */

/**
 * Add our custom head action to wp_head
 *
 * @since 3.0
 */
function dpa_head() {
	do_action( 'dpa_head' );
}

/**
 * Add our custom head action to wp_head
 *
 * @since 3.0
 */
function dpa_footer() {
	do_action( 'dpa_footer' );
}