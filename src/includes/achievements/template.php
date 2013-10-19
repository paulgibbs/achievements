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
 * 'ach_event'             - string   - Loads achievements for a specific event. Matches a slug from the dpa_event tax. Default is empty.
 * 'ach_populate_progress' - bool|int - Populate a user/users' progress for the results.
 *                                    - bool: True - uses the logged in user (default). False - don't fetch progress.
 *                                    - int: pass a user ID (single user).
 * 'ach_progress_status'   - array    - array: Post status IDs for the Progress post type.
 *
 * @param array|string $args All the arguments supported by {@link WP_Query}, and some more.
 * @return bool Returns true if the query has any results to loop over
 * @since Achievements (1.0)
 */
function dpa_has_achievements( $args = array() ) {
	// If multisite and running network-wide, switch_to_blog to the data store site
	if ( is_multisite() && dpa_is_running_networkwide() )
		switch_to_blog( DPA_DATA_STORE );

	$default_post_parent     = dpa_is_single_achievement()       ? dpa_get_achievement_id()              : 'any';	
	$default_progress_status = dpa_is_single_user_achievements() ? array( dpa_get_unlocked_status_id() ) : array( dpa_get_locked_status_id(), dpa_get_unlocked_status_id() );

	$defaults = array(
		// Standard WP_Query params
		'ignore_sticky_posts'   => true,                                                       // Ignored sticky posts
		'order'                 => 'ASC',                                                      // 'ASC', 'DESC
		'orderby'               => 'title',                                                    // 'meta_value', 'author', 'date', 'title', 'modified', 'parent', rand'
		'max_num_pages'         => false,                                                      // Maximum number of pages to show
		'paged'                 => dpa_get_paged(),                                            // Page number
		'perm'                  => 'readable',                                                 // Query var value to provide statuses
		'post_parent'           => $default_post_parent,                                       // Post parent
		'post_type'             => dpa_get_achievement_post_type(),                            // Only retrieve achievement posts
		'posts_per_page'        => dpa_get_achievements_per_page(),                            // Achievements per page
		'ach_progress_status'   => $default_progress_status,                                   // On single user achievement page, default to only showing unlocked achievements
		's'                     => ! empty( $_GET['dpa'] ) ? wp_unslash( $_GET['dpa'] ) : '',  // Achievements search

		// Achievements params
 		'ach_event'             => '',                                                         // Load achievements for a specific event
		'ach_populate_progress' => false,                                                      // Progress post type: populate user progress for the results.
	);

	// Load achievements for a specific event
	if ( ! empty( $args['ach_event'] ) ) {

		$args['tax_query'] = array(
			array(
				'field'    => 'slug',
				'taxonomy' => dpa_get_event_tax_id(),
				'terms'    => $args['ach_event'],
			)
		);

		unset( $args['ach_event'] );
	}

	$args = dpa_parse_args( $args, $defaults, 'has_achievements' );
	extract( $args );

	// Run the query
	achievements()->achievement_query = new WP_Query( $args );

	// User to popular progress for
	$progress_user_ids = false;

	if ( isset( $args['ach_populate_progress'] ) ) {
		if ( true === $args['ach_populate_progress'] ) {
			if ( dpa_is_single_user_achievements() ) {
				$progress_user_ids = dpa_get_displayed_user_id();

			} elseif ( is_user_logged_in() ) {
				$progress_user_ids = get_current_user_id();
			}

		} else {
			$progress_user_ids = (int) $args['ach_populate_progress'];
		}
	}

	// If no limit to posts per page, set it to the current post_count
	if ( -1 === $posts_per_page )
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
			'next_text' => is_rtl() ? '&larr;' : '&rarr;',
			'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
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
			'no_found_rows'  => true,                          // Disable SQL_CALC_FOUND_ROWS (used for pagination queries)
			'post_parent'    => $progress_post_ids,            // Fetch progress posts with parent_id matching these
			'post_status'    => $args['ach_progress_status'],  // Get posts in these post statuses
			'posts_per_page' => -1,                            // No pagination
		);

		// Run the query
		dpa_has_progress( $progress_args );
	}

	// If multisite and running network-wide, undo the switch_to_blog
	if ( is_multisite() && dpa_is_running_networkwide() )
		restore_current_blog();

	return apply_filters( 'dpa_has_achievements', achievements()->achievement_query->have_posts() );
}

/**
 * Whether there are more achievements available in the loop
 *
 * @return bool True if posts are in the loop
 * @since Achievements (2.0)
 */
function dpa_achievements() {
	$have_posts = achievements()->achievement_query->have_posts();

	// Reset the post data when finished
	if ( empty( $have_posts ) )
		wp_reset_postdata();

	return $have_posts;
}

/**
 * Iterate the post index in the loop. Retrieves the next post, sets up the post, sets the 'in the loop' property to true.
 *
 * @return bool
 * @since Achievements (1.0)
 */
function dpa_the_achievement() {
	return achievements()->achievement_query->the_post();
}

/**
 * Retrieve the ID of the current item in the achievement loop.
 *
 * @return int
 * @since Achievements (3.0)
 */
function dpa_get_the_achievement_ID() {
	return achievements()->achievement_query->post->ID;
}

/**
 * Output the achievement archive title
 *
 * @param string $title Optional. Default text to use as title
 * @since Achievements (3.0)
 */
function dpa_achievement_archive_title( $title = '' ) {
	echo dpa_get_achievement_archive_title( $title );
}
	/**
	 * Return the achievement archive title
	 *
	 * @param string $title Optional. Default text to use as title
	 * @return string The achievement archive title
	 * @since Achievements (3.0)
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
 * @since Achievements (3.0)
 */
function dpa_achievement_title( $achievement_id = 0 ) {
	echo dpa_get_achievement_title( $achievement_id );
}
	/**
	 * Return the title of the achievement
	 *
	 * @param int $achievement_id Optional. Achievement ID to get title of.
	 * @return string Title of achievement
	 * @since Achievements (3.0)
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
 * @since Achievements (3.0)
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
	 * @since Achievements (3.0)
	 */
	function dpa_get_achievement_permalink( $achievement_id = 0, $redirect_to = '' ) {
		$achievement_id = dpa_get_achievement_id( $achievement_id );

		// Maybe the redirect address
		if ( ! empty( $redirect_to ) )
			$achievement_permalink = esc_url_raw( $redirect_to );

		// Otherwise use the achievement permalink
		else
			$achievement_permalink = get_permalink( $achievement_id );

		return apply_filters( 'dpa_get_achievement_permalink', $achievement_permalink, $achievement_id );
	}

/**
 * Output the achievement ID
 *
 * @param int $achievement_id Optional
 * @see dpa_get_achievement_id()
 * @since Achievements (1.0)
 */
function dpa_achievement_id( $achievement_id = 0 ) {
	echo dpa_get_achievement_id( $achievement_id );
}
	/**
	 * Return the achievement ID
	 *
	 * @global WP_Query $wp_query
	 * @param int $achievement_id Optional
	 * @return int The achievement ID
	 * @since Achievements (1.0)
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
 * @since Achievements (3.0)
 */
function dpa_achievement_author_id( $achievement_id = 0 ) {
	echo dpa_get_achievement_author_id( $achievement_id );
}
	/**
	 * Return the author ID of the achievement
	 *
	 * @param int $achievement_id Optional. Achievement ID
	 * @return int User ID
	 * @since Achievements (3.0)
	 */
	function dpa_get_achievement_author_id( $achievement_id = 0 ) {
		$achievement_id = dpa_get_achievement_id( $achievement_id );
		$author_id      = get_post_field( 'post_author', $achievement_id );

		return (int) apply_filters( 'dpa_get_achievement_author_id', (int) $author_id, $achievement_id );
	}

/**
 * Output the content of the achievement
 *
 * @param int $achievement_id Optional. Achievement ID
 * @since Achievements (3.0)
 */
function dpa_achievement_content( $achievement_id = 0 ) {
	echo dpa_get_achievement_content( $achievement_id );
}
	/**
	 * Return the content of the achievement
	 *
	 * @param int $achievement_id Optional. Achievement ID
	 * @return string Content of the achievement post
	 * @since Achievements (3.0)
	 */
	function dpa_get_achievement_content( $achievement_id = 0 ) {
		$achievement_id = dpa_get_achievement_id( $achievement_id );

		// Check if password is required
		if ( post_password_required( $achievement_id ) )
			return get_the_password_form();

		$content = get_post_field( 'post_content', $achievement_id );

		/**
		 * Juggle the do_shortcode filter to prevent Achievements' shortcodes being run on the excerpt.
		 * This is important because otherwise some shortcodes affect the achievements() global in bad
		 * ways (i.e. the breadcrumb shortcode breaks pagination).
		 */
		remove_filter( 'dpa_get_achievement_content', 'do_shortcode', 26 );
		$retval = apply_filters( 'dpa_get_achievement_content', $content, $achievement_id );
		add_filter( 'dpa_get_achievement_content', 'do_shortcode', 26 );

		return $retval;
	}

/**
 * Output the points value of the achievement.
 *
 * @param int $achievement_id Optional. Achievement ID
 * @since Achievements (1.0)
 */
function dpa_achievement_points( $achievement_id = 0 ) {
	echo number_format_i18n( dpa_get_achievement_points( $achievement_id ) );
}
	/**
	 * Return the points value of the achievement.
	 *
	 * @param int $achievement_id Optional. Achievement ID
	 * @return int
	 * @since Achievements (2.0)
	 */
	function dpa_get_achievement_points( $achievement_id = 0 ) {
		$achievement_id = dpa_get_achievement_id( $achievement_id );
		$points         = (int) get_post_meta( $achievement_id, '_dpa_points', true );

		return apply_filters( 'dpa_get_achievement_points', $points, $achievement_id );
	}

/**
 * Output the target value of the achievement. Only used for event-type achievements.
 * The target is the number of times that an achievement's events must occur before the achievement is unlocked.
 *
 * @param int $achievement_id Optional. Achievement ID
 * @since Achievements (3.0)
 */
function dpa_achievement_target( $achievement_id = 0 ) {
	echo number_format_i18n( dpa_get_achievement_target( $achievement_id ) );
}
	/**
	 * Return the points value of the achievement. Only used for event-type achievements.
	 * The target is the number of times that an achievement's events must occur before the achievement is unlocked.
	 *
	 * @param int $achievement_id Optional. Achievement ID
	 * @return int
	 * @since Achievements (3.0)
	 */
	function dpa_get_achievement_target( $achievement_id = 0 ) {
		$achievement_id = dpa_get_achievement_id( $achievement_id );
		$target         = (int) get_post_meta( $achievement_id, '_dpa_target', true );

		return apply_filters( 'dpa_get_achievement_target', $target, $achievement_id );
	}

/**
 * Output the redemption code for this achievement.
 *
 * @param int $achievement_id Optional. Achievement ID
 * @since Achievements (3.1)
 */
function dpa_achievement_redemption_code( $achievement_id = 0 ) {
	echo number_format_i18n( dpa_get_achievement_redemption_code( $achievement_id ) );
}
	/**
	 * Return the redemption code for this achievement.
	 *
	 * @param int $achievement_id Optional. Achievement ID
	 * @return string
	 * @since Achievements (3.1)
	 */
	function dpa_get_achievement_redemption_code( $achievement_id = 0 ) {
		$achievement_id = dpa_get_achievement_id( $achievement_id );
		$target         = get_post_meta( $achievement_id, '_dpa_redemption_code', true );

		return apply_filters( 'dpa_get_achievement_redemption_code', $target, $achievement_id );
	}

/**
 * Output the excerpt of the achievement
 *
 * @param int $achievement_id Optional. Achievement ID
 * @param int $length Optional. Length of the excerpt. Defaults to 200 letters
 * @since Achievements (3.0)
 */
function dpa_achievement_excerpt( $achievement_id = 0, $length = 200 ) {
	echo dpa_get_achievement_excerpt( $achievement_id, $length );
}
	/**
	 * Return the excerpt of the achievement
	 *
	 * @param int $achievement_id Optional. Achievement ID.
	 * @param int $length Optional. Length of the excerpt. Defaults to 200 letters
	 * @return string Achievement excerpt
	 * @since Achievements (3.0)
	 * @todo Don't cut off part of a word; go to the nearest space
	 */
	function dpa_get_achievement_excerpt( $achievement_id = 0, $length = 200 ) {
		$achievement_id = dpa_get_achievement_id( $achievement_id );
		$excerpt        = get_post_field( 'post_excerpt', $achievement_id );
		$length         = (int) $length;

		// If you don't specify an excerpt when creating an achievement, we'll use the post content.
		if ( empty( $excerpt ) )
			$excerpt = dpa_get_achievement_content( $achievement_id );

		// Check the length of the excerpt
		$excerpt = trim( strip_tags( $excerpt ) );

		// Multibyte support
		if ( function_exists( 'mb_strlen' ) )
			$excerpt_length = mb_strlen( $excerpt );
		else
			$excerpt_length = strlen( $excerpt );

		if ( ! empty( $length ) && ( $excerpt_length > $length ) ) {
			// Trim the excerpt
			$excerpt = substr( $excerpt, 0, $length - 1 );

			// Build a "go here to read more" link
			// translators: first param is post permalink, second param is the "more" text.
			$more_link = sprintf( __( '&hellip; (<a href="%1$s">%2$s</a>)', 'dpa' ),
				esc_attr( dpa_get_achievement_permalink( $achievement_id ) ),
				_x( 'more', 'Excerpt - click here to see more of the post', 'dpa' )
			);
			$more_link = apply_filters( 'dpa_get_achievement_excerpt_more_link', $more_link, $achievement_id, $length );
			$excerpt  .= $more_link;
		}

		return apply_filters( 'dpa_get_achievement_excerpt', $excerpt, $achievement_id, $length );
	}

/**
 * Output the post date and time of an achievement
 *
 * @param int $achievement_id Optional. Achievement ID.
 * @param bool $humanise Optional. Humanise output using time_since. Defaults to false.
 * @param bool $gmt Optional. Use GMT.
 * @since Achievements (3.0)
 */
function dpa_achievement_post_date( $achievement_id = 0, $humanise = false, $gmt = false ) {
	echo dpa_get_achievement_post_date( $achievement_id, $humanise, $gmt );
}
	/**
	 * Return the post date and time of an achievement
	 *
	 * @param int $achievement_id Optional. Achievement ID.
	 * @param bool $humanise Optional. Humanise output using time_since. Defaults to false.
	 * @param bool $gmt Optional. Use GMT.
	 * @return string
	 * @since Achievements (3.0)
	 */
	function dpa_get_achievement_post_date( $achievement_id = 0, $humanise = false, $gmt = false ) {
		$achievement_id = dpa_get_achievement_id( $achievement_id );
		
		// 4 days, 4 hours ago
		if ( $humanise ) {
			$gmt_s  = ! empty( $gmt ) ? 'G' : 'U';
			$date   = get_post_time( $gmt_s, $gmt, $achievement_id );
			$time   = false; // For filter below
			$result = dpa_get_time_since( $date );

		// August 22, 2012 at 5:55 pm
		} else {
			$date   = get_post_time( get_option( 'date_format' ), $gmt, $achievement_id, true );
			$time   = get_post_time( get_option( 'time_format' ), $gmt, $achievement_id, true );
			$result = sprintf( _x( '%1$s at %2$s', '[date] at [time]', 'dpa' ), $date, $time );
		}

		return apply_filters( 'dpa_get_achievement_post_date', $result, $achievement_id, $humanise, $gmt, $date, $time );
	}

/**
 * Output a fancy description of the achievements on the site, including the
 * number of public and hidden achievements.
 *
 * @param array $args Optional. Arguments passed to alter output
 * @since Achievements (3.0)
 */
function dpa_achievements_index_description( $args = '' ) {
	echo dpa_get_achievements_index_description( $args );
}
	/**
	 * Return a fancy description of the achievements on the site, including the
	 * number of public and hidden achievements, and the username and avatar of
	 * the most recent person who unlocked the achievement.
	 *
	 * @param mixed $args This function supports these arguments:
	 *  - before: Before the text
	 *  - after: After the text
	 *  - size: Size of the avatar
	 * @return string Fancy description
	 * @since Achievements (3.0)
	 */
	function dpa_get_achievements_index_description( $args = '' ) {
		$defaults = array(
			'after'     => '</p></div>',
			'before'    => '<div class="dpa-template-notice info"><p class="dpa-achievements-description">',
			'size'      => 14,
		);
		$r = dpa_parse_args( $args, $defaults, 'get_achievements_index_description' );
		extract( $r );

		// Get count of total achievements
		$achievement_count = dpa_get_total_achievement_count();
		$achievement_text  = sprintf( _n( '%s achievement', '%s achievements', $achievement_count, 'dpa' ), number_format_i18n( $achievement_count ) );

		// Get data on the most recent unlocked achievement
		$recent_achievement_id = dpa_stats_get_last_achievement_id();
		$recent_user_id        = dpa_stats_get_last_achievement_user_id();

		if ( ! empty( $recent_user_id ) && ! empty( $recent_achievement_id ) ) {

			// Check user ID is still valid
			$user = get_userdata( $recent_user_id );
			if ( ! empty( $user ) && dpa_is_user_active( $user ) ) {

				// Check achievement ID is valid
				$achievement = get_post( $recent_achievement_id );
				if ( ! empty( $achievement ) && 'publish' === $achievement->post_status ) {

					// Combine all the things to build the output text
					$retstr = sprintf(
						__( 'This site has %1$s, and the last unlocked was <a href="%2$s">%3$s</a> by %4$s.', 'dpa' ),
						$achievement_text,
						get_permalink( $achievement->ID ),
						apply_filters( 'dpa_get_achievement_title', $achievement->post_title, $achievement->ID ),
						dpa_get_user_avatar_link( array(
							'size'    => $size,
							'user_id' => $user->ID,
						) )
					);
				}
			}
		}

		// If we haven't set a more specific description, fall back to the default.
		if ( ! isset( $retstr ) )
			$retstr = sprintf( __( 'This site has %1$s.', 'dpa' ), $achievement_text );

		$retstr = $before . $retstr . $after;
		return apply_filters( 'dpa_get_achievements_index_description', $retstr, $args );
	}

/**
 * Output the row class of an achievement
 *
 * @param int $achievement_id Optional. Achievement ID
 * @param array $classes Optional. Extra classes you can pass when calling this function 
 * @since Achievements (3.0)
 */
function dpa_achievement_class( $achievement_id = 0, $classes = array() ) {
	echo dpa_get_achievement_class( $achievement_id, $classes );
}
	/**
	 * Return the row class of an achievement
	 *
	 * @param int $achievement_id Optional. Achievement ID
	 * @param array $classes Optional. Extra classes you can pass when calling this function 
	 * @return string Row class of an achievement
	 * @since Achievements (3.0)
	 */
	function dpa_get_achievement_class( $achievement_id = 0, $classes = array() ) {
		$achievement_id = dpa_get_achievement_id( $achievement_id );
		$classes        = (array) $classes;
		$count          = isset( achievements()->achievement_query->current_post ) ? achievements()->achievement_query->current_post : 1;

		// If we've only one post in the loop, don't both with odd and even.
		if ( count( achievements()->achievement_query->posts ) > 1 )
			$classes[] = ( (int) $count % 2 ) ? 'even' : 'odd';
		else
			$classes[] = 'dpa-single-achievement';

		$classes[] = 'user-id-' . dpa_get_achievement_author_id( $achievement_id );
		$classes   = apply_filters( 'dpa_get_achievement_class', $classes, $achievement_id );

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
 * Output the featured image of the achievement
 *
 * @author Mike Bronner <mike.bronner@gmail.com>
 * @param int $achievement_id Optional. Achievement ID
 * @param string $size Optional. Can be one of: post-thumbnail*, thumbnail, medium, large, full.
 * @param string|array $attr Optional. Query string or array of attributes; see get_the_post_thumbnail().
 * @see dpa_get_achievement_title()
 * @since Achievements (3.3)
 */
function dpa_achievement_image( $achievement_id = 0, $size = 'post-thumbnail', $attr = '' ) {
	echo dpa_get_achievement_image( $achievement_id, $size );
}

/**
 * Output the featured image of the achievement
 *
 * @author Mike Bronner <mike.bronner@gmail.com>
 * @param int $achievement_id Optional. Achievement ID
 * @param string $size Optional. Can be one of: post-thumbnail*, thumbnail, medium, large, full.
 * @param string|array $attr Optional. Query string or array of attributes; see get_the_post_thumbnail().
 * @return string HTML <img> tag
 * @since Achievements (3.3)
 */
function dpa_get_achievement_image( $achievement_id = 0, $size = 'post-thumbnail', $attr = '' ) {
	$achievement_id = dpa_get_achievement_id( $achievement_id );
	$image          = get_the_post_thumbnail( $achievement_id, $size, $attr );

	return apply_filters( 'dpa_get_achievement_image', $image, $achievement_id, $size, $attr );
}

/**
 * Displays achievement notices
 *
 * @since Achievements (3.0)
 */
function dpa_achievement_notices() {
	// Bail if not viewing an achievement
	if ( ! dpa_is_single_achievement() )
		return;

	$notice_text = '';

	// Filter notice text and bail if empty
	$notice_text = apply_filters( 'dpa_achievement_notices', $notice_text, dpa_get_achievement_id() );
	if ( empty( $notice_text ) )
		return;

	dpa_add_error( 'achievement_notice', $notice_text, 'message' );
}


/**
 * Achievement pagination
 */

/**
 * Output the pagination count
 *
 * @since Achievements (3.0)
 */
function dpa_achievement_pagination_count() {
	echo dpa_get_achievement_pagination_count();
}
	/**
	 * Return the pagination count
	 *
	 * @return string Achievement pagination count
	 * @since Achievements (3.0)
	 */
	function dpa_get_achievement_pagination_count() {
		if ( ! is_a( achievements()->achievement_query, 'WP_Query' ) )
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
 * @since Achievements (3.0)
 */
function dpa_achievement_pagination_links() {
	echo dpa_get_achievement_pagination_links();
}
	/**
	 * Return pagination links
	 *
	 * @return string Pagination links
	 * @since Achievements (3.0)
	 */
	function dpa_get_achievement_pagination_links() {
		if ( ! is_a( achievements()->achievement_query, 'WP_Query' ) )
			return '';

		return apply_filters( 'dpa_get_achievement_pagination_links', achievements()->achievement_query->pagination_links );
	}
