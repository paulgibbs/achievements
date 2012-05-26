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
 * The achievement post type loop.
 *
 * @param array|string $args All the arguments supported by {@link WP_Query}
 * @return bool Returns true if the query has any results to loop over
 * @since 3.0
 */
function dpa_has_achievements( $args = '' ) {
	// If multisite and running network-wide, switch_to_blog to the data store site
	if ( is_multisite() && dpa_is_running_networkwide() )
		switch_to_blog( DPA_DATA_STORE );

	// The default achievement query for most circumstances
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

/**
 * Whether there are more achievements available in the loop
 *
 * @since 3.0
 * @return bool True if posts are in the loop
 */
function dpa_achievements() {
	$have_posts = achievements()->achievement_query->have_posts();

	// Reset the post data when finished
	if ( empty( $have_posts ) ) {
		wp_reset_postdata();

		// If multisite and running network-wide, undo the switch_to_blog
		if ( is_multisite() && dpa_is_running_networkwide() )
			restore_current_blog();
	}

	return $have_posts;
}

/**
 * Iterate the post index in the loop. Retrieves the next post, sets up the post, sets the 'in the loop' property to true.
 *
 * @since 3.0
 */
function dpa_the_achievement() {
	return achievements()->achievement_query->the_post();
}
?>