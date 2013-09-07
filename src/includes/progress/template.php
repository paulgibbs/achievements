<?php
/**
 * Achievement Progress post type template tags
 *
 * If you try to use an Progress post type template loops outside of the Achievement
 * post type template loop, you will need to implement your own switch_to_blog and
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
 * @param array|string $args All the arguments supported by {@link WP_Query}, and some more.
 * @return bool Returns true if the query has any results to loop over
 * @since Achievements (3.0)
 */
function dpa_has_progress( $args = array() ) {
	// If multisite and running network-wide, switch_to_blog to the data store site
	if ( is_multisite() && dpa_is_running_networkwide() )
		switch_to_blog( DPA_DATA_STORE );

	$defaults = array(
		'ignore_sticky_posts' => true,                          // Ignored sticky posts
		'max_num_pages'       => false,                         // Maximum number of pages to show
		'order'               => 'DESC',                        // 'ASC', 'DESC
		'orderby'             => 'date',                        // 'meta_value', 'author', 'date', 'title', 'modified', 'parent', 'rand'
		'paged'               => dpa_get_paged(),               // Page number
		'post_status'         => dpa_get_unlocked_status_id(),  // Get posts in the unlocked status by default.
		'post_type'           => dpa_get_progress_post_type(),  // Only retrieve progress posts
		's'                   => '',                            // No search


		// Conditional defaults

		// If on a user's achievements page, use that author's user ID.
		'author'         => dpa_is_single_user_achievements() ? dpa_get_displayed_user_id() : null,

		// If on single achievement page, use that post's ID. 
		'post_parent'    => dpa_is_single_achievement() ? dpa_get_achievement_id() : null,

		// If on a single achievement page, don't paginate progresses.
		//'posts_per_page' => dpa_is_single_achievement() ? -1 : dpa_get_progresses_per_page(),
		// @todo Above commented out for 3.1; see https://github.com/paulgibbs/achievements/issues/70 for details
		'posts_per_page' => -1,

		// If on a user's achievements page, fetch the achievements if we haven't got them already
		'ach_populate_achievements' => dpa_is_single_user_achievements() && is_a( achievements()->achievement_query, 'WP_Query' ) && empty( achievements()->achievement_query->request ),
	);

	$args = dpa_parse_args( $args, $defaults, 'has_progress' );

	// Run the query
	achievements()->progress_query = new WP_Query( $args );

	// If no limit to posts per page, set it to the current post_count
	if ( -1 === (int) $args['posts_per_page'] )
		$args['posts_per_page'] = achievements()->progress_query->post_count;

	// Add pagination values to query object
	achievements()->progress_query->posts_per_page = $args['posts_per_page'];
	achievements()->progress_query->paged          = $args['paged'];

	// Only add pagination if query returned results
	if ( ( (int) achievements()->progress_query->post_count || (int) achievements()->progress_query->found_posts ) && (int) achievements()->progress_query->posts_per_page ) {

		// Limit the number of achievements shown based on maximum allowed pages
		if ( ( ! empty( $args['max_num_pages'] ) ) && achievements()->progress_query->found_posts > achievements()->progress_query->max_num_pages * achievements()->progress_query->post_count )
			achievements()->progress_query->found_posts = achievements()->progress_query->max_num_pages * achievements()->progress_query->post_count;

		// If pretty permalinks are enabled, make our pagination pretty
		if ( $GLOBALS['wp_rewrite']->using_permalinks() ) {

			// Page or single post
			if ( is_page() || is_single() )
				$base = get_permalink();

			// User achievements page
			elseif ( dpa_is_single_user_achievements() )
				$base = dpa_get_user_avatar_link( array( 'type' => 'url', 'user_id' => dpa_get_displayed_user_id() ) );

			// Default
			else
				$base = get_permalink( $args['post_parent'] );

			// Use pagination base
			$base = trailingslashit( $base ) . user_trailingslashit( $GLOBALS['wp_rewrite']->pagination_base . '/%#%/' );

		// Unpretty pagination
		} else {
			$base = add_query_arg( 'paged', '%#%' );
		}

		// Pagination settings with filter
		$progress_pagination = apply_filters( 'dpa_progress_pagination', array(
			'base'      => $base,
			'current'   => (int) achievements()->progress_query->paged,
			'format'    => '',
			'mid_size'  => 1,
			'next_text' => is_rtl() ? '&larr;' : '&rarr;',
			'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
			'total'     => ( $args['posts_per_page'] == achievements()->progress_query->found_posts ) ? 1 : ceil( (int) achievements()->progress_query->found_posts / (int) $args['posts_per_page'] ),
		) );

		// Add pagination to query object
		achievements()->progress_query->pagination_links = paginate_links( $progress_pagination );

		// Remove first page from pagination
		achievements()->progress_query->pagination_links = str_replace( $GLOBALS['wp_rewrite']->pagination_base . "/1/'", "'", achievements()->progress_query->pagination_links );
	}

	// If on a user's achievements page, we need to fetch the achievements
	if ( $args['ach_populate_achievements'] && achievements()->progress_query->have_posts() ) {
		$achievement_ids = wp_list_pluck( (array) achievements()->progress_query->posts, 'post_parent' );

		$achievement_args = array(
			'order'          => $args['order'],   // Add order criterium to make sure achievements are ordered in the same fashion as progress items
			'orderby'        => $args['orderby'], // Add orderby fields to make sure achievements display in the same order as the progress items
			'post__in'       => $achievement_ids, // Only get achievements that relate to the progressses we've got.
			'posts_per_page' => -1,               // No pagination
		);

		// Run the query
		dpa_has_achievements( $achievement_args );
	}

	// If multisite and running network-wide, undo the switch_to_blog
	if ( is_multisite() && dpa_is_running_networkwide() )
		restore_current_blog();

	return apply_filters( 'dpa_has_progress', achievements()->progress_query->have_posts() );
}

/**
 * Whether there are more achievement progresses available in the loop. Is progresses a word?
 *
 * @since Achievements (3.0)
 * @return bool True if posts are in the loop
 */
function dpa_progress() {
	$have_posts = achievements()->progress_query->have_posts();

	// Reset the post data when finished
	if ( empty( $have_posts ) )
		wp_reset_postdata();

	return $have_posts;
}

/**
 * Iterate the post index in the loop. Retrieves the next post, sets up the post, sets the 'in the loop' property to true.
 *
 * @since Achievements (3.0)
 */
function dpa_the_progress() {
	return achievements()->progress_query->the_post();
}

/**
 * Output the achievement progress ID
 *
 * @param int $progress_id Optional
 * @see dpa_get_achievement_id()
 * @since Achievements (3.0)
 */
function dpa_progress_id( $progress_id = 0 ) {
	echo dpa_get_progress_id( $progress_id );
}
	/**
	 * Return the achievement progress ID
	 *
	 * @param int $progress_id Optional
	 * @return int The achievement progress ID
	 * @since Achievements (3.0)
	 */
	function dpa_get_progress_id( $progress_id = 0 ) {
		// Easy empty checking
		if ( ! empty( $progress_id ) && is_numeric( $progress_id ) )
			$the_progress_id = $progress_id;

		// Currently inside an achievement loop
		elseif ( ! empty( achievements()->progress_query->in_the_loop ) && isset( achievements()->progress_query->post->ID ) )
			$the_progress_id = achievements()->progress_query->post->ID;

		else
			$the_progress_id = 0;

		return (int) apply_filters( 'dpa_get_progress_id', (int) $the_progress_id, $progress_id );
	}

/**
 * Output the user ID of the person who made this achievement progress
 *
 * @param int $progress_id Optional. Progress ID
 * @since Achievements (3.0)
 */
function dpa_progress_author_id( $progress_id = 0 ) {
	echo dpa_get_progress_author_id( $progress_id );
}
	/**
	 * Return the user ID of the person who made this achievement progress
	 *
	 * @param int $progress_id Optional. Progress ID
	 * @return int User ID
	 * @since Achievements (3.0)
	 */
	function dpa_get_progress_author_id( $progress_id = 0 ) {
		$progress_id = dpa_get_progress_id( $progress_id );
		$author_id   = get_post_field( 'post_author', $progress_id );

		return (int) apply_filters( 'dpa_get_progress_author_id', (int) $author_id, $progress_id );
	}

/**
 * Output the post date and time that a user made progress on an achievement
 *
 * @param int $progress_id Optional. Progress ID.
 * @param bool $humanise Optional. Humanise output using time_since. Defaults to true.
 * @param bool $gmt Optional. Use GMT.
 * @since Achievements (3.0)
 */
function dpa_progress_date( $progress_id = 0, $humanise = true, $gmt = false ) {
	echo dpa_get_progress_date( $progress_id, $humanise, $gmt );
}
	/**
	 * Return the post date and time that a user made progress on an achievement
	 *
	 * @param int $progress_id Optional. Progress ID.
	 * @param bool $humanise Optional. Humanise output using time_since. Defaults to true.
	 * @param bool $gmt Optional. Use GMT.
	 * @return string
	 * @since Achievements (3.0)
	 */
	function dpa_get_progress_date( $progress_id = 0, $humanise = true, $gmt = false ) {
		$progress_id = dpa_get_progress_id( $progress_id );
		
		// 4 days, 4 hours ago
		if ( $humanise ) {
			$gmt_s  = ! empty( $gmt ) ? 'G' : 'U';
			$date   = get_post_time( $gmt_s, $gmt, $progress_id );
			$time   = false; // For filter below
			$result = dpa_get_time_since( $date );

		// August 22, 2012 at 5:55 pm
		} else {
			$date   = get_post_time( get_option( 'date_format' ), $gmt, $progress_id, true );
			$time   = get_post_time( get_option( 'time_format' ), $gmt, $progress_id, true );
			$result = sprintf( _x( '%1$s at %2$s', '[date] at [time]', 'dpa' ), $date, $time );
		}

		return apply_filters( 'dpa_get_progress_date', $result, $progress_id, $humanise, $gmt, $date, $time );
	}

/**
 * Output the avatar link of the user who the achievement progress belongs to.
 *
 * @param array $args See dpa_get_user_avatar_link() documentation.
 * @since Achievements (3.0)
 */
function dpa_progress_user_avatar( $args = array() ) {
	echo dpa_get_progress_user_avatar( $args );
}
	/**
	 * Return the avatar link of the user who the achievement progress belongs to.
	 *
	 * @param array $args See dpa_get_user_avatar_link() documentation.
	 * @return string
	 * @since Achievements (3.0)
	 */
	function dpa_get_progress_user_avatar( $args = array() ) {
		$defaults = array(
			'type'    => 'avatar',
			'user_id' => dpa_get_progress_author_id(),
		);
		$r = dpa_parse_args( $args, $defaults, 'get_progress_user_avatar' );
		extract( $r );

		// Get the user's avatar link
		$avatar = dpa_user_avatar_link( array(
			'type'    => $type,
			'user_id' => $user_id,
		) );

		return apply_filters( 'dpa_get_progress_user_avatar', $avatar, $args );
	}

/**
 * Output a link to the profile of the user who the achievement progress belongs to.
 *
 * @param array $args See dpa_get_user_avatar_link() documentation.
 * @since Achievements (3.0)
 */
function dpa_progress_user_link( $args = array() ) {
	echo dpa_get_progress_user_link( $args );
}
	/**
	 * Return a link to the profile of the user who the achievement progress belongs to.
	 *
	 * @param array $args See dpa_get_user_avatar_link() documentation.
	 * @return string
	 * @since Achievements (3.0)
	 */
	function dpa_get_progress_user_link( $args = array() ) {
		$defaults = array(
			'type'    => 'name',
			'user_id' => dpa_get_progress_author_id(),
		);
		$r = dpa_parse_args( $args, $defaults, 'get_progress_user_link' );
		extract( $r );

		// Get the user's avatar link
		$link = dpa_user_avatar_link( array(
			'type'    => $type,
			'user_id' => $user_id,
		) );

		return apply_filters( 'dpa_get_progress_user_link', $link, $args );
	}

/**
 * Output the row class of an achievement progress object
 *
 * @param int $progress_id Optional. Progress ID
 * @param array $classes Optional. Extra classes you can pass when calling this function 
 * @since Achievements (3.0)
 */
function dpa_progress_class( $progress_id = 0, $classes = array() ) {
	echo dpa_get_progress_class( $progress_id, $classes );
}
	/**
	 * Return the row class of an achievement progress object
	 *
	 * @param int $progress_id Optional. Progress ID
	 * @param array $classes Optional. Extra classes you can pass when calling this function 
	 * @return string Row class of an achievement progress object
	 * @since Achievements (3.0)
	 */
	function dpa_get_progress_class( $progress_id = 0, $classes = array() ) {
		$progress_id = dpa_get_progress_id( $progress_id, $classes );
		$classes     = (array) $classes;
		$count       = isset( achievements()->progress_query->current_post ) ? achievements()->progress_query->current_post : 1;

		// If we've only one post in the loop, don't both with odd and even.
		if ( $count > 1 )
			$classes[] = ( (int) $count % 2 ) ? 'even' : 'odd';
		else
			$classes[] = 'dpa-single-progress';

		// Does this progress belong to the logged in user?
		if ( is_user_logged_in() && wp_get_current_user()->ID === dpa_get_progress_author_id( $progress_id ) )
			$classes[] = 'logged-in-user';

		$classes[] = 'user-id-' . dpa_get_progress_author_id( $progress_id );
		$classes   = apply_filters( 'dpa_get_progress_class', $classes, $progress_id );

		// Remove hentry as Achievements isn't hAtom compliant.
		foreach ( $classes as &$class ) {
			if ( 'hentry' === $class )
				$class = '';
		}

		$classes = array_map( 'sanitize_html_class', array_merge( $classes, array() ) );
		$classes = join( ' ', $classes );

		return 'class="' . esc_attr( $classes )  . '"';
	}

/**
 * Has the current achievement in the progress loop been unlocked by the current user?
 * 
 * The "current" user refers to the user in the dpa_has_achievements() loop, which is not necessarily
 * the currently-logged in user.
 *
 * @param int $achievement_id Optional. Achievement ID to check.
 * @return bool True if achievement has been unlocked
 * @since Achievements (3.0)
 */
function dpa_is_achievement_unlocked( $achievement_id = 0 ) {
	$achievement_id = dpa_get_achievement_id( $achievement_id );

	// Look in the progress posts and match the achievement against a post_parent
	$progress = wp_filter_object_list( achievements()->progress_query->posts, array( 'post_parent' => $achievement_id ) );
	$progress = array_shift( $progress );

	$retval = ( ! empty( $progress ) && dpa_get_unlocked_status_id() === $progress->post_status );
	return apply_filters( 'dpa_is_achievement_unlocked', $retval, $achievement_id, $progress ); 
}


/**
 * Achievement Progress pagination
 */

/**
 * Output the pagination count
 *
 * @since Achievements (3.0)
 */
function dpa_progress_pagination_count() {
	echo dpa_get_progress_pagination_count();
}
	/**
	 * Return the pagination count
	 *
	 * @return string Progress pagination count
	 * @since Achievements (3.0)
	 */
	function dpa_get_progress_pagination_count() {
		if ( ! is_a( achievements()->progress_query, 'WP_Query' ) )
			return;

		// Set pagination values
		$start_num = intval( ( achievements()->progress_query->paged - 1 ) * achievements()->progress_query->posts_per_page ) + 1;
		$from_num  = number_format_i18n( $start_num );
		$to_num    = number_format_i18n( ( $start_num + ( achievements()->progress_query->posts_per_page - 1 ) > achievements()->progress_query->found_posts ) ? achievements()->progress_query->found_posts : $start_num + ( achievements()->progress_query->posts_per_page - 1 ) );
		$total_int = (int) ! empty( achievements()->progress_query->found_posts ) ? achievements()->progress_query->found_posts : achievements()->progress_query->post_count;
		$total     = number_format_i18n( $total_int );

		// Several achievements within a single page
		if ( empty( $to_num ) ) {
			$retstr = sprintf( _n( 'Viewing %1$s achievement progress', "Viewing %1\$s achievements&rsquo; progress", $total_int, 'dpa' ), $total );

		// Several achievements with several pages
		} else {
			$retstr = sprintf( _n( 'Viewing achievement progress %2$s (of %4$s total)', 'Viewing %1$s achievements&rsquo; progress - %2$s through %3$s (of %4$s total)', $total_int, 'dpa' ), achievements()->progress_query->post_count, $from_num, $to_num, $total );
		}

		return apply_filters( 'dpa_get_progress_pagination_count', $retstr );
	}

/**
 * Output pagination links
 *
 * @since Achievements (3.1)
 */
function dpa_progress_pagination_links() {
	echo dpa_get_progress_pagination_links();
}
	/**
	 * Return pagination links
	 *
	 * @return string Pagination links
	 * @since Achievements (3.1)
	 */
	function dpa_get_progress_pagination_links() {
		if ( ! is_a( achievements()->progress_query, 'WP_Query' ) )
			return '';

		return apply_filters( 'dpa_get_progress_pagination_links', achievements()->progress_query->pagination_links );
	}
