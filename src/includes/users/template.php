<?php
/**
 * Achievements user and leaderboard template tags
 *
 * The leaderboard template loop has been implemented in a psuedo-WP_Query style; under the hood, it just iterates through dpa_get_leaderboard().
 * It's been done like this for consistency with the rest of Achievements' template loops, and will hopefully provide a more straightforward
 * upgrade path in the future if the internal implementation ever changes.
 *
 * @package Achievements
 * @subpackage UserTemplate
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Output the avatar link of a user
 *
 * @param array $args See dpa_get_user_avatar_link() documentation.
 * @since Achievements (3.0)
 */
function dpa_user_avatar_link( $args = array() ) {
	echo dpa_get_user_avatar_link( $args );
}
	/**
	 * Return the avatar link of a user
	 *
	 * @param array $args This function supports these arguments:
	 *  - int $size If we're showing an avatar, set it to this size
	 *  - string $type What type of link to return; either "avatar", "name", or "both", or "url".
	 *  - int $user_id The ID for the user.
	 * @return string
	 * @since Achievements (3.0)
	 */
	function dpa_get_user_avatar_link( $args = array() ) {
		$defaults = array(
			'size'    => 50,
			'type'    => 'both',
			'user_id' => 0,
		);
		$r = dpa_parse_args( $args, $defaults, 'get_user_avatar_link' );
		extract( $r );

		// Default to current user
		if ( empty( $user_id ) && is_user_logged_in() )
			$user_id = get_current_user_id();

		// Assemble some link bits
		$user_link = array();

		// BuddyPress
		if ( dpa_integrate_into_buddypress() ) {
			$user_url = user_trailingslashit( bp_core_get_user_domain( $user_id ) . dpa_get_authors_endpoint() );

		// WordPress
		} else {
			$user_url = user_trailingslashit( trailingslashit( get_author_posts_url( $user_id ) ) . dpa_get_authors_endpoint() );

			/**
			 * Multisite, running network-wide.
			 *
			 * When this function is used by the "unlocked achievement" popup, if multisite + running network-wide + and not subdomains,
			 * we'll have already done switch_to_blog( DPA_ROOT_BLOG ) by the time that this function is called. This makes inspecting
			 * the current site ID, and is_main_site(), both useless as the globals will have already been changed.
			 *
			 * We need to find out if the user is likely to be on the "main site" in this situation. so we can modify our link.
			 * The main site's author URLs are prefixed with "/blog". We do this by inspecting the _wp_switched_stack global.
			 *
			 * I think this solution might result in a wrong link in multi-network configuration, or if the main site has been set
			 * to something non-default, but these are edge-cases for now.
			 */
			if ( is_multisite() && ! is_subdomain_install() && dpa_is_running_networkwide() && DPA_DATA_STORE === 1 && ! empty( $GLOBALS['_wp_switched_stack'] ) ) {
				$last_element = count( $GLOBALS['_wp_switched_stack'] ) - 1;

				if ( isset( $GLOBALS['_wp_switched_stack'][$last_element] ) && $GLOBALS['_wp_switched_stack'][$last_element] != 1 )
					$user_url = str_replace( home_url(), home_url() . '/blog', $user_url );
			}
		}

		// Get avatar
		if ( 'avatar' === $type || 'both' === $type )
			$user_link[] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $user_url ), get_avatar( $user_id, $size ) );

		// Get display name
		if ( 'avatar' !== $type )
			$user_link[] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $user_url ), get_the_author_meta( 'display_name', $user_id ) );

		// Maybe return user URL only
		if ( 'url' === $type ) {
			$user_link = $user_url;

		// Otherwise piece together the link parts and return
		} else {
			$user_link = join( '&nbsp;', $user_link );
		}

		return apply_filters( 'dpa_get_user_avatar_link', $user_link, $args );
	}

/**
 * The leaderboard template loop.
 *
 * Doesn't use WP_Query, but the template loop and its data are structured in a vaguely similar
 * way to the dpa_has_achievements() and dpa_has_progress() loops (which do use WP_Query).
 *
 * @param array $args Optional. Associative array of optional arguments. See function for details.
 * @return bool Returns true if the query had any results to loop over
 * @since Achievements (3.4)
 */
function dpa_has_leaderboard( $args = array() ) {

	// If multisite and running network-wide, switch_to_blog to the data store site
	if ( is_multisite() && dpa_is_running_networkwide() )
		switch_to_blog( DPA_DATA_STORE );

	$defaults = array(
		'paged'          => dpa_get_leaderboard_paged(),           // Page number
		'posts_per_page' => dpa_get_leaderboard_items_per_page(),  // Users per page
		'user_ids'       => array(),                               // Get details for specific users; pass an array of ints.
	);

	$args = dpa_parse_args( $args, $defaults, 'has_leaderboard' );

	// Run the query
	achievements()->leaderboard_query = dpa_get_leaderboard( $args );

	// Only add pagination if query returned results
	if ( ( count( achievements()->leaderboard_query['results'] ) || achievements()->leaderboard_query['total'] ) && $args['posts_per_page'] ) {

		// If a top-level /leaderboard/ rewrite is ever added, we can make this use pretty pagination. Also see dpa_get_leaderboard_paged().
		$base = add_query_arg( 'leaderboard-page', '%#%' );

		// Pagination settings with filter
		$leaderboard_pagination = apply_filters( 'dpa_leaderboard_pagination', array(
			'base'      => $base,
			'current'   => $args['paged'],
			'format'    => '',
			'mid_size'  => 1,
			'next_text' => is_rtl() ? '&larr;' : '&rarr;',
			'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
			'total'     => ( (int) $args['posts_per_page'] === achievements()->leaderboard_query['total'] ) ? 1 : ceil( achievements()->leaderboard_query['total'] / (int) $args['posts_per_page'] ),
		) );

		achievements()->leaderboard_query['paged']            = (int) $args['paged'];
		achievements()->leaderboard_query['pagination_links'] = paginate_links( $leaderboard_pagination );
		achievements()->leaderboard_query['posts_per_page']   = (int) $args['posts_per_page'];
	}

	// If multisite and running network-wide, undo the switch_to_blog
	if ( is_multisite() && dpa_is_running_networkwide() )
		restore_current_blog();

	return apply_filters( 'dpa_has_leaderboard', ! empty( achievements()->leaderboard_query['results'] ) );
}

/**
 * Whether there are more items available in the leaderboard loop
 *
 * @return bool True if there are more items in the loop
 * @since Achievements (3.4)
 */
function dpa_leaderboard_has_users() {
	$item_count = count( achievements()->leaderboard_query['results'] );

	// Messy, messy...
	if ( ! isset( achievements()->leaderboard_query['current_item'] ) )
		achievements()->leaderboard_query['current_item'] = -1;

	if ( achievements()->leaderboard_query['current_item'] + 1 < $item_count )
		return true;

	// Do some cleaning up after the loop
	elseif ( achievements()->leaderboard_query['current_item'] + 1 === $item_count && $item_count > 0 )
		achievements()->leaderboard_query['current_item'] = -1;

	achievements()->leaderboard_query['in_the_loop'] = false;
	return false;
}

/**
 * Iterate the leaderboard user index in the loop. Retrieves the next item and sets the 'in the loop' property to true.
 *
 * @return object The next item in the leaderboard
 * @since Achievements (3.4)
 */
function dpa_the_leaderboard_user() {
	achievements()->leaderboard_query['current_item']++;
	achievements()->leaderboard_query['in_the_loop'] = true;

	return achievements()->leaderboard_query['results'][ achievements()->leaderboard_query['current_item'] ];
}

/**
 * Output the ID of the current user in the leaderboard
 *
 * @since Achievements (3.4)
 */
function dpa_leaderboard_user_id() {
	echo dpa_get_leaderboard_user_id();
}
	/**
	 * Return the ID of the current user in the leaderboard
	 *
	 * @return int User ID
	 * @since Achievements (3.4)
	 */
	function dpa_get_leaderboard_user_id() {
		$user_id = achievements()->leaderboard_query['results'][ achievements()->leaderboard_query['current_item'] ]->user_id;
		return (int) apply_filters( 'dpa_get_leaderboard_user_id', (int) $user_id );
	}

/**
 * Output the karma of the current user in the leaderboard
 *
 * @since Achievements (3.4)
 */
function dpa_leaderboard_user_karma() {
	echo number_format_i18n( dpa_get_leaderboard_user_karma() );
}
	/**
	 * Return the karma of the current user in the leaderboard
	 *
	 * @return int User's karma
	 * @since Achievements (3.4)
	 */
	function dpa_get_leaderboard_user_karma() {
		$karma = achievements()->leaderboard_query['results'][ achievements()->leaderboard_query['current_item'] ]->karma;
		return (int) apply_filters( 'dpa_get_leaderboard_user_karma', (int) $karma );
	}

/**
 * Output the position of the current user in the leaderboard
 *
 * It is possible that multiple users may share the same rank position (e.g. "tie for third place!").
 *
 * @since Achievements (3.4)
 */
function dpa_leaderboard_user_position() {
	echo number_format_i18n( dpa_get_leaderboard_user_position() );
}
	/**
	 * Return the rank position of the current user in the leaderboard
	 *
	 * It is possible that multiple users may share the same rank position (e.g. "tie for third place!").
	 *
	 * @return int User's rank
	 * @since Achievements (3.4)
	 */
	function dpa_get_leaderboard_user_position() {
		$rank = achievements()->leaderboard_query['results'][ achievements()->leaderboard_query['current_item'] ]->rank;
		return (int) apply_filters( 'dpa_get_leaderboard_user_position', (int) $rank );
	}

/**
 * Output the display nme of the current user in the leaderboard
 *
 * @since Achievements (3.4)
 */
function dpa_leaderboard_user_display_name() {
	echo esc_html( dpa_get_leaderboard_user_display_name() );
}
	/**
	 * Return the display nme of the current user in the leaderboard
	 *
	 * @return string User's display name
	 * @since Achievements (3.4)
	 */
	function dpa_get_leaderboard_user_display_name() {
		$display_name = achievements()->leaderboard_query['results'][ achievements()->leaderboard_query['current_item'] ]->display_name;

		// Use wpcore's get_the_author()'s filter for any other plugins that may filter the name in some special way.
		$display_name = apply_filters( 'the_author', $display_name );

		return apply_filters( 'dpa_get_leaderboard_user_display_name', $display_name );
	}

/**
 * Output the row class of the current user in the leaderboard
 *
 * @since Achievements (3.4)
 */
function dpa_leaderboard_user_class() {
	echo dpa_get_leaderboard_user_class();
}
	/**
	 * Return the row class of the current user in the leaderboard
	 *
	 * @return string Row class for the current user in the leaderboard
	 * @since Achievements (3.4)
	 */
	function dpa_get_leaderboard_user_class() {
		$classes = array();
		$count   = isset( achievements()->leaderboard_query['current_item'] ) ? achievements()->leaderboard_query['current_item'] : 1;

		$classes[] = ( (int) $count % 2 ) ? 'even' : 'odd';
		$classes[] = 'user-id-' . dpa_get_leaderboard_user_id();

		// Is the leaderboard user the current logged in user?
		if ( is_user_logged_in() && wp_get_current_user()->ID === dpa_get_leaderboard_user_id() )
			$classes[] = 'logged-in-user';

		$classes = apply_filters( 'dpa_get_leaderboard_user_class', $classes );
		$classes = array_map( 'sanitize_html_class', array_merge( $classes, array() ) );
		$classes = join( ' ', $classes );

		return 'class="' . esc_attr( $classes )  . '"';
	}


/**
 * Leaderboard pagination
 */

/**
 * Output the leaderboard pagination count
 *
 * @since Achievements (3.4)
 */
function dpa_leaderboard_pagination_count() {
	echo dpa_get_leaderboard_pagination_count();
}
	/**
	 * Return the pagination count
	 *
	 * As of 3.4, this function isn't used in Achievements core, and you should probably be careful about using it.
	 * The problem is that though there may be (for example) 20 total users on the leaderboard, this function is
	 * going to say "viewing 1-20 leaderboard positions", but if more than one of the users have the exact
	 * same karma points, they'll both share a rank, so the rank number of the last user in the leaderboard won't
	 * match the pagination count.
	 * 
	 * tl;dr This function creates pagination text for the users in the leaderboard text, not the distinct ranks.
	 * Don't use it.
	 *
	 * @return string Progress pagination count
	 * @since Achievements (3.4)
	 */
	function dpa_get_leaderboard_pagination_count() {
		if ( empty( achievements()->leaderboard_query ) )
			return '';

		$found_count = achievements()->leaderboard_query['total'];
		$item_count  = count( achievements()->leaderboard_query['results'] );

		// Set pagination values
		$start_num = intval( ( achievements()->leaderboard_query['paged'] - 1 ) * achievements()->leaderboard_query['posts_per_page'] ) + 1;
		$from_num  = number_format_i18n( $start_num );
		$to_num    = number_format_i18n( ( $start_num + ( achievements()->leaderboard_query['posts_per_page'] - 1 ) > $found_count ) ? $found_count : $start_num + ( achievements()->leaderboard_query['posts_per_page'] - 1 ) );
		$total_int = (int) ! empty( $found_count ) ? $found_count : $item_count;
		$total     = number_format_i18n( $total_int );

		// Several items within a single page
		if ( empty( $to_num ) ) {
			$retstr = sprintf( _n( 'Viewing %1$s user', 'Viewing %1$s users', $total_int, 'dpa' ), $total );

		// Several items with several pages
		} else {
			$retstr = sprintf( _n( 'Viewing users %2$s (of %4$s total)', 'Viewing %1$s users - %2$s through %3$s (of %4$s total)', $total_int, 'dpa' ), $item_count, $from_num, $to_num, $total );
		}

		return apply_filters( 'dpa_get_leaderboard_pagination_count', $retstr );
	}

/**
 * Output leaderboard pagination links
 *
 * @since Achievements (3.4)
 */
function dpa_leaderboard_pagination_links() {
	echo dpa_get_leaderboard_pagination_links();
}
	/**
	 * Return leaderboard pagination links
	 *
	 * @return string Pagination links
	 * @since Achievements (3.4)
	 */
	function dpa_get_leaderboard_pagination_links() {
		if ( empty( achievements()->leaderboard_query ) )
			return '';

		return apply_filters( 'dpa_get_leaderboard_pagination_links', achievements()->leaderboard_query['pagination_links'] );
	}
