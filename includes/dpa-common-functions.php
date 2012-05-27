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

	// Make sure to not paginate widget queries
	if ( ! dpa_is_query_name( 'dpa_widget' ) ) {
		$paged = 0;

		// Check the query var
		if ( get_query_var( 'paged' ) ) {
			$paged = get_query_var( 'paged' );

		// Check query paged
		} elseif ( ! empty( $wp_query->query['paged'] ) ) {
			$paged = $wp_query->query['paged'];
		}

		// Paged found
		if ( ! empty( $paged ) )
			return (int) $paged;
	}

	// Default to first page
	return 1;
}
?>