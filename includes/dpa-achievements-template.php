<?php
/**
 * Achievement post type template tags
 *
 * @package Achievements
 * @subpackage Template
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Output the unique id of the custom post type for achievements
 *
 * @since 3.0
 * @uses dpa_get_achievement_post_type() To get the forum post type
 */
function dpa_achievement_post_type() {
	echo dpa_get_achievement_post_type();
}
	/**
	 * Return the unique id of the custom post type for achievements
	 *
	 * @return string The unique forum post type id
	 * @since 3.0
	 */
	function dpa_get_achievement_post_type() {
		return apply_filters( 'dpa_get_achievement_post_type', achievements()->achievement_post_type );
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
 * Return the achievements per page setting
 *
 * @return int
 * @since 3.0
 */
function dpa_get_achievements_per_page() {
	$default = 15;

	// Get database option and cast as integer
	$per = $retval = (int) get_option( '_dpa_achievements_per_page', $default );

	// If return val is empty, set it to default
	if ( empty( $retval ) )
		$retval = $default;

	// Filter and return
	return (int) apply_filters( 'dpa_get_achievements_per_page', $retval, $per );
}

/**
 * The main achievement loop.
 *
 * @param array|string $args All the arguments supported by {@link WP_Query}
 * @return bool Returns true if the query has any results to loop over
 * @since 3.0
 */
function dpa_has_achievements( $args = '' ) {
	// Check if user can read hidden forums
	if ( current_user_can( 'read_hidden_forums' ) )
		$post_stati[] = bbp_get_hidden_status_id();

	// The default forum query for most circumstances
	$defaults = array (
		'order'          => 'ASC',                                                // 'ASC', 'DESC
		'orderby'        => 'title',                                              // 'meta_value', 'author', 'date', 'title', 'modified', 'parent', rand'
		'max_num_pages'  => false,                                                // Maximum number of pages to show
		'paged'          => dpa_get_paged(),                                      // Page number
		'post_status'    => 'publish',                                            // Published (active) achievements only
		'post_type'      => dpa_get_achievement_post_type(),                      // Only retrieve achievement posts
		'posts_per_page' => dpa_get_achievements_per_page(),                      // Achievements per page
		's'              => ! empty( $_REQUEST['dpa'] ) ? $_REQUEST['dpa'] : '',  // Achievements search
	);
	$achievement_filters = wp_parse_args( $args, $defaults );

	// Run the query
	achievements()->achievement_query = new WP_Query( $achievement_filters );

	return apply_filters( 'dpa_has_achievements', achievements()->achievement_query->have_posts(), achievements()->achievement_query );
}
?>