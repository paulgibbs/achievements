<?php
/**
 * Achievement post type template tags
 *
 * @package Achievements
 * @subpackage AchievementsTemplate
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The achievement post type loop.
 *
 * Most of the values that $args can accept are documented in {@link WP_Query}. The custom
 * values added by Achievements are as follows:
 *
 * $ach_event             - string - Loads achievements for a specific event. Matches a slug from the dpa_event tax. Default is empty.
 * $ach_populate_progress - bool   - Populate logged in user's progress for the results. Default is true.
 * 
 * @param array|string $args All the arguments supported by {@link WP_Query}, and some more.
 * @return bool Returns true if the query has any results to loop over
 * @since 3.0
 */
function dpa_has_achievements( $args = array() ) {
	// If multisite and running network-wide, switch_to_blog to the data store site
	if ( is_multisite() && dpa_is_running_networkwide() )
		switch_to_blog( DPA_DATA_STORE );

	// The default achievement query for most circumstances
	$defaults = array(
		// Standard WP_Query params
		'order'                 => 'ASC',                                                // 'ASC', 'DESC
		'orderby'               => 'title',                                              // 'meta_value', 'author', 'date', 'title', 'modified', 'parent', rand'
		'max_num_pages'         => false,                                                // Maximum number of pages to show
		'paged'                 => dpa_get_paged(),                                      // Page number
		'post_status'           => 'publish',                                            // Published (active) achievements only
		'post_type'             => dpa_get_achievement_post_type(),                      // Only retrieve achievement posts
		'posts_per_page'        => dpa_get_achievements_per_page(),                      // Achievements per page
		's'                     => ! empty( $_REQUEST['dpa'] ) ? $_REQUEST['dpa'] : '',  // Achievements search

		// Achievements params
		'ach_event'             => '',                                                   // Load achievements for a specific event
		'ach_populate_progress' => true,                                                 // Populate logged in user's progress for the results
	);
	$args = wp_parse_args( $args, $defaults );


	/**
	 * Handle Achievements params
	 */

	// Load achievements for a specific event
	if ( isset( $args['ach_event'] ) ) {
		if ( ! empty( $args['ach_event']) ) {

			$args['tax_query'] = array(
				'field'    => 'slug',
				'taxonomy' => dpa_get_event_tax_id(),
				'terms'    => $args['ach_event'],
			);
		}

		unset( $args['ach_event'] );
	}

	// Run the query
	achievements()->achievement_query = new WP_Query( $args );

	// Grab extra data for the achievements (e.g. has the current user unlocked it)
	if ( $args['ach_populate_progress'] && achievements()->achievement_query->have_posts() && is_user_loggedin() ) {
		$progress_post_ids = wp_list_pluck( (array) achievements()->achievement_query->posts, 'ID' );

		// Args for progress query
		$progress_args = array(
			'author'         => achievements()->current_user->ID;      // Current user
			'max_num_pages'  => false,                                 // Maximum number of pages to show
			'no_found_rows'  => true,                                  // Disable SQL_CALC_FOUND_ROWS
			'post_parent'    => $progress_post_ids,                    // Fetch progress posts with parent_id matching these
			'post_status'    => 'publish',                             // @todo Post statuses for locked/unlocked
			'post_type'      => dpa_achievement_progress_post_type(),  // Only retrieve progress posts
			'posts_per_page' => -1,                                    // No pagination
			's'              => '',                                    // No search
		);

		// Run the query
		achievements()->progress_query = new WP_Query( $progress_args );
	}

	return apply_filters( 'dpa_has_achievements', achievements()->achievement_query->have_posts() );
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