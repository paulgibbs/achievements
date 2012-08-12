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
 * $ach_event             - string                - Loads achievements for a specific event. Matches a slug from the dpa_event tax. Default is empty.
 * $ach_populate_progress - bool|array|int|string - Populate a user/users' progress for the results.
 *                                                - bool: True - uses the logged in user (default). False - don't fetch progress.
 *                                                - int: pass a user ID (single user).
 *                                                - array: array of integers (multiple users' IDs).
 *                                                - string: "all" (get all users), or a comma-separated list of user IDs (multiple users).
 * $ach_progress_status   - array                 - array: Post status IDs for the Progress post type.
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
		'ach_populate_progress' => true,                                                 // Progress post type: populate user(s) progress for the results. See function's phpdoc for full description.
		'ach_progress_status'   => array(                                                // Progress post type: get posts in the locked / unlocked status by default.
		                             dpa_get_locked_status_id(),
		                             dpa_get_unlocked_status_id(),
		                           ),
	);
	$args              = dpa_parse_args( $args, $defaults );
	$progress_user_ids = false;

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

	// Populate user(s) progress for the results.
	if ( ! empty( $args['ach_populate_progress'] ) ) {
		if ( true === $args['ach_populate_progress'] && is_user_logged_in() ) {
			$progress_user_ids = achievements()->current_user->ID;
		} elseif ( is_string( $args['ach_populate_progress'] ) && 'all' === $args['ach_populate_progress'] ) {
			$progress_user_ids = false;
		} else {
			$progress_user_ids = wp_parse_id_list( (array) $args['ach_populate_progress'] );
			$progress_user_ids = implode( ',', $progress_user_ids );
		}

	}

	// Run the query
	achievements()->achievement_query = new WP_Query( $args );

	// Populate extra progress information for the achievements
	if ( ! empty( $progress_user_ids ) && achievements()->achievement_query->have_posts() ) {
		$progress_post_ids = wp_list_pluck( (array) achievements()->achievement_query->posts, 'ID' );

		// Args for progress query
		$progress_args = array(
			'author'         => $progress_user_ids,            // Posts belonging to these author(s)
			'no_found_rows'  => true,                          // Disable SQL_CALC_FOUND_ROWS
			'post_parent'    => $progress_post_ids,            // Fetch progress posts with parent_id matching these
			'post_status'    => $args['ach_progress_status'],  // Get posts in these post statuses
			'posts_per_page' => -1,                            // No pagination
		);

		// Run the query
		dpa_has_progress( $progress_args );
	}

	return apply_filters( 'dpa_has_achievements', achievements()->achievement_query->have_posts() );
}

/**
 * Whether there are more achievements available in the loop
 *
 * @return bool True if posts are in the loop
 * @since 3.0
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
 * @return bool
 * @since 3.0
 */
function dpa_the_achievement() {
	return achievements()->achievement_query->the_post();
}

/**
 * Retrieve the ID of the current item in the achievement loop.
 *
 * @return int
 * @since 3.0
 */
function dpa_get_the_achievement_ID() {
	return achievements()->achievement_query->post->ID;
}

/**
 * Output the achievement archive title
 *
 * @param string $title Optional. Default text to use as title
 * @since 3.0
 */
function dpa_achievement_archive_title( $title = '' ) {
	echo dpa_get_achievement_archive_title( $title );
}
	/**
	 * Return the achievement archive title
	 *
	 * @param string $title Optional. Default text to use as title
	 * @return string The achievement archive title
	 * @since 3.0
	 */
	function dpa_get_achievement_archive_title( $title = '' ) {
		// If no title was passed
		if ( empty( $title ) ) {

			// Set root text to page title
			$page = dpa_get_page_by_path( dpa_get_root_slug() );
			if ( ! empty( $page ) ) {
				$title = get_the_title( $page->ID );

			// Default to achievement post type name label
			} else {
				$pto   = get_post_type_object( dpa_get_achievement_post_type() );
				$title = $pto->labels->name;
			}
		}

		return apply_filters( 'dpa_get_achievement_archive_title', $title );
	}

/**
 * Output the title of the achievement
 *
 * @param int $achievement_id Optional. Achievement ID
 * @see dpa_get_achievement_title()
 * @since 3.0
 */
function dpa_achievement_title( $achievement_id = 0 ) {
	echo dpa_get_achievement_title( $achievement_id );
}
	/**
	 * Return the title of the achievement
	 *
	 * @param int $achievement_id Optional. Achievement ID to get title of.
	 * @return string Title of achievement
	 * @since 3.0
	 */
	function dpa_get_achievement_title( $achievement_id = 0 ) {
		$achievement_id = dpa_get_achievement_ID( $achievement_id );
		$title          = get_the_title( $achievement_id );

		return apply_filters( 'dpa_get_achievement_title', $title, $achievement_id );
	}

/**
 * Output the achievement ID
 *
 * @param int $achievement_id Optional
 * @see dpa_get_achievement_id()
 * @since 3.0
 */
function dpa_achievement_id( $achievement_id = 0 ) {
	echo dpa_get_achievement_id( $achievement_id );
}
	/**
	 * Return the achievement ID
	 *
	 * @param int $achievement_id Optional
	 * @return int The achievement ID
	 * @since 3.0
	 */
	function dpa_get_achievement_id( $achievement_id = 0 ) {
		global $wp_query;

		// Easy empty checking
		if ( ! empty( $achievement_id ) && is_numeric( $achievement_id ) )
			$the_achievement_id = $achievement_id;

		// Currently inside an achievement loop
		elseif ( ! empty( achievements()->achievement_query->in_the_loop ) && isset( achievements()->achievement_query->post->ID ) )
			$the_achievement_id = achievements()->achievement_query->post->ID;

		// Currently viewing an achievement
		elseif ( dpa_is_single_achievement() && ! empty( achievements()->current_achievement_id ) )
			$the_achievement_id = achievements()->current_achievement_id;

		// Currently viewing an achievement
		elseif ( dpa_is_single_achievement() && isset( $wp_query->post->ID ) )
			$the_achievement_id = $wp_query->post->ID;

		else
			$the_achievement_id = 0;

		return (int) apply_filters( 'dpa_get_achievement_id', (int) $the_achievement_id, $achievement_id );
	}

/**
 * Output the author ID of the achievement
 *
 * @param int $achievement_id Optional. Achievement ID
 * @since 3.0
 */
function dpa_achievement_author_id( $achievement_id = 0 ) {
	echo dpa_get_achievement_author_id( $achievement_id );
}
	/**
	 * Return the author ID of the achievement
	 *
	 * @param int $achievement_id Optional. Achievement ID
	 * @return string Author of achievement
	 * @since 3.0
	 */
	function dpa_get_achievement_author_id( $achievement_id = 0 ) {
		$achievement_id = dpa_get_achievement_id( $achievement_id );
		$author_id      = get_post_field( 'post_author', $achievement_id );

		return (int) apply_filters( 'dpa_get_achievement_author_id', (int) $author_id, $achievement_id );
	}

/**
 * Displays achievement notices
 *
 * @since 3.0
 */
function dpa_achievement_notices() {
	// Bail if not viewing an achievement
	if ( ! dpa_is_single_achievement() )
		return;

	// @todo Maybe add "locked"/"unlocked" achievement notices here
	$notice_text = '';

	// Filter notice text and bail if empty
	$notice_text = apply_filters( 'dpa_achievement_notices', $notice_text, dpa_get_achievement_id() );
	if ( empty( $notice_text ) )
		return;

	dpa_add_error( 'achievement_notice', $notice_text, 'message' );
}