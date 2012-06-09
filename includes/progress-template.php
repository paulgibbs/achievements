<?php
/**
 * Achievement Progress post type template tags
 *
 * If you try to use an Progress post type template loops outside of the Achievement
 * post type template loop, you will need to implement your own swtich_to_blog and
 * wp_reset_postdata() handling if running in a multisite and in a
 * dpa_is_running_networkwide() configuration. Otherwise the data won't be fetched
 * from the appropriate site.
 *
 * @package Achievements
 * @subpackage ProgressTemplate
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The Progress post type loop.
 *
 * Only for use inside a dpa_has_achievements() template loop.
 *
 * @param array|string $args All the arguments supported by {@link WP_Query}, and some more.
 * @return bool Returns true if the query has any results to loop over
 * @since 3.0
 */
function dpa_has_progress( $args = array() ) {
	$defaults = array(
		// @todo Maybe re-implement order/orderby once I decided how the Progress post type is going to be used in more detail
		//'order'          => 'ASC',                          // 'ASC', 'DESC
		//'orderby'        => 'title',                        // 'meta_value', 'author', 'date', 'title', 'modified', 'parent', rand'

		'max_num_pages'  => false,                          // Maximum number of pages to show
		'paged'          => dpa_get_paged(),                // Page number
		'post_status'    => array(                          // Get posts in the locked / unlocked status by default.
		                      dpa_get_locked_status_id(),
		                      dpa_get_unlocked_status_id(),
		                    ),
		'post_type'      => dpa_get_progress_post_type(),   // Only retrieve progress posts
		'posts_per_page' => dpa_get_progresses_per_page(),  // Progresses per page
		's'              => '',                             // No search
	);
	$args = wp_parse_args( $args, $defaults );

	// Run the query
	achievements()->progress_query = new WP_Query( $args );

	return apply_filters( 'dpa_has_progress', achievements()->progress_query->have_posts() );
}

/**
 * Whether there are more achievement progresses available in the loop. Is progresses a word?
 *
 * @since 3.0
 * @return bool True if posts are in the loop
 */
function dpa_progress() {
	return achievements()->progress_query->have_posts();
}

/**
 * Iterate the post index in the loop. Retrieves the next post, sets up the post, sets the 'in the loop' property to true.
 *
 * @since 3.0
 */
function dpa_the_progress() {
	return achievements()->progress_query->the_post();
}