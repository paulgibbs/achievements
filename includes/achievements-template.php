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

	// Extract the query variables
	extract( $args );

	// Load achievements for a specific event
	if ( ! empty( $args['ach_event'] ) ) {

		$args['tax_query'] = array(
			'field'    => 'slug',
			'taxonomy' => dpa_get_event_tax_id(),
			'terms'    => $args['ach_event'],
		);
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

	// If no limit to posts per page, set it to the current post_count
	if ( -1 == $posts_per_page )
		$posts_per_page = achievements()->achievement_query->post_count;

	// Add pagination values to query object
	achievements()->achievement_query->posts_per_page = $posts_per_page;
	achievements()->achievement_query->paged          = $paged;

	// Only add pagination if query returned results
	if ( ( (int) achievements()->achievement_query->post_count || (int) achievements()->achievement_query->found_posts ) && (int) achievements()->achievement_query->posts_per_page ) {

		// Limit the number of achievements shown based on maximum allowed pages
		if ( ( ! empty( $max_num_pages ) ) && achievements()->achievement_query->found_posts > achievements()->achievement_query->max_num_pages * achievements()->achievement_query->post_count )
			achievements()->achievement_query->found_posts = achievements()->achievement_query->max_num_pages * achievements()->achievement_query->post_count;

		// If pretty permalinks are enabled, make our pagination pretty
		if ( $GLOBALS['wp_rewrite']->using_permalinks() ) {

			// Page or single post
			if ( is_page() || is_single() )
				$base = get_permalink();

			// Achievements archive
			elseif ( dpa_is_achievement_archive() )
				$base = dpa_get_achievements_url();

			// Default
			else
				$base = get_permalink( $post_parent );

			// Use pagination base
			$base = trailingslashit( $base ) . user_trailingslashit( $GLOBALS['wp_rewrite']->pagination_base . '/%#%/' );

		// Unpretty pagination
		} else {
			$base = add_query_arg( 'paged', '%#%' );
		}

		// Pagination settings with filter
		$achievement_pagination = apply_filters( 'dpa_achievement_pagination', array(
			'base'      => $base,
			'current'   => (int) achievements()->achievement_query->paged,
			'format'    => '',
			'mid_size'  => 1,
			'next_text' => '&rarr;',
			'prev_text' => '&larr;',
			'total'     => ( $posts_per_page == achievements()->achievement_query->found_posts ) ? 1 : ceil( (int) achievements()->achievement_query->found_posts / (int) $posts_per_page ),
		) );

		// Add pagination to query object
		achievements()->achievement_query->pagination_links = paginate_links( $achievement_pagination );

		// Remove first page from pagination
		achievements()->achievement_query->pagination_links = str_replace( $GLOBALS['wp_rewrite']->pagination_base . "/1/'", "'", achievements()->achievement_query->pagination_links );
	}

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
 * Output the permanent link to the achievement in the achievement loop
 *
 * @param int $achievement_id Optional. Achievement ID
 * @param string $redirect_to Optional
 * @since 3.0
 */
function dpa_achievement_permalink( $achievement_id = 0, $redirect_to = '' ) {
	echo dpa_get_achievement_permalink( $achievement_id, $redirect_to );
}
	/**
	 * Return the permanent link to the topic
	 *
	 * @param int $achievement_id Optional. Achievement ID
	 * @param string $redirect_to Optional
	 * @return string
	 * @since 3.0
	 */
	function dpa_get_achievement_permalink( $achievement_id = 0, $redirect_to = '' ) {
		$achievement_id = dpa_get_achievement_id( $achievement_id );

		// Maybe the redirect address
		if ( ! empty( $redirect_to ) )
			$achievement_permalink = esc_url_raw( $redirect_to );

		// Otherwise use the topic permalink
		else
			$achievement_permalink = get_permalink( $achievement_id );

		return apply_filters( 'dpa_get_achievement_permalink', $achievement_permalink, $achievement_id );
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
 * Output the row class of an achievement
 *
 * @param int $achievement_id Optional. Achievement ID
 * @since 3.0
 */
function dpa_achievement_class( $achievement_id = 0 ) {
	echo dpa_get_achievement_class( $achievement_id );
}
	/**
	 * Return the row class of an achievement
	 *
	 * @param int $achievement_id Optional. Achievement ID
	 * @return string Row class of an achievement
	 * @since 3.0
	 */
	function dpa_get_achievement_class( $achievement_id = 0 ) {
		$achievement_id = dpa_get_achievement_id( $achievement_id );
		$count          = isset( achievements()->achievement_query->current_post ) ? achievements()->achievement_query->current_post : 1;

		$classes   = array();
		$classes[] = ( (int) $count % 2 ) ? 'even' : 'odd';
		$classes[] = 'user-id-' . dpa_get_achievement_author_id( $achievement_id );
		$classes   = get_post_class( array_filter( $classes ), $achievement_id );
		$classes   = apply_filters( 'dpa_get_achievement_class', $classes, $achievement_id );

		$retval  = 'class="' . join( ' ', $classes ) . '"';
		return $retval;
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


/**
 * Topic Pagination
 */

/**
 * Output the pagination count
 *
 * @since 3.0
 */
function dpa_achievement_pagination_count() {
	echo dpa_get_achievement_pagination_count();
}
	/**
	 * Return the pagination count
	 *
	 * @return string Achievement pagination count
	 * @since 3.0
	 */
	function dpa_get_achievement_pagination_count() {
		if ( empty( achievements()->achievement_query ) )
			return;

		// Set pagination values
		$start_num = intval( ( achievements()->achievement_query->paged - 1 ) * achievements()->achievement_query->posts_per_page ) + 1;
		$from_num  = number_format_i18n( $start_num );
		$to_num    = number_format_i18n( ( $start_num + ( achievements()->achievement_query->posts_per_page - 1 ) > achievements()->achievement_query->found_posts ) ? achievements()->achievement_query->found_posts : $start_num + ( achievements()->achievement_query->posts_per_page - 1 ) );
		$total_int = (int) ! empty( achievements()->achievement_query->found_posts ) ? achievements()->achievement_query->found_posts : achievements()->achievement_query->post_count;
		$total     = number_format_i18n( $total_int );

		// Several achievements within a single page
		if ( empty( $to_num ) ) {
			$retstr = sprintf( _n( 'Viewing %1$s achievement', 'Viewing %1$s achievements', $total_int, 'dpa' ), $total );

		// Several achievements with several pages
		} else {
			$retstr = sprintf( _n( 'Viewing achievement %2$s (of %4$s total)', 'Viewing %1$s achievements - %2$s through %3$s (of %4$s total)', $total_int, 'dpa' ), achievements()->achievement_query->post_count, $from_num, $to_num, $total );
		}

		return apply_filters( 'dpa_get_achievement_pagination_count', $retstr );
	}

/**
 * Output pagination links
 *
 * @since 3.0
 */
function dpa_achievement_pagination_links() {
	echo dpa_get_achievement_pagination_links();
}
	/**
	 * Return pagination links
	 *
	 * @return string Pagination links
	 * @since 3.0
	 */
	function dpa_get_achievement_pagination_links() {
		if ( empty( achievements()->achievement_query ) )
			return;

		return apply_filters( 'dpa_get_achievement_pagination_links', achievements()->achievement_query->pagination_links );
	}
