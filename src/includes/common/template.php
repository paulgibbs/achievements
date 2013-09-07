<?php
/**
 * Common template tags
 *
 * @package Achievements
 * @subpackage CommonTemplate
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * URLs
 */

/**
 * Ouput the achievements post type URL
 * 
 * @param string $path Additional path with leading slash
 * @since Achievements (3.0)
 */
function dpa_achievements_url( $path = '/' ) {
	echo dpa_get_achievements_url( $path );
}
	/**
	 * Return the achievements post type URL
	 * 
	 * @param string $path Additional path with leading slash
	 * @since Achievements (3.0)
	 */
	function dpa_get_achievements_url( $path = '/' ) {
		return home_url( dpa_get_root_slug() . $path );
	}


/**
 * Add-on Actions
 */

/**
 * Add our custom head action to wp_head
 *
 * @since Achievements (3.0)
 */
function dpa_head() {
	do_action( 'dpa_head' );
}

/**
 * Add our custom head action to wp_head
 *
 * @since Achievements (3.0)
 */
function dpa_footer() {
	do_action( 'dpa_footer' );
}


/**
 * "is_" functions
 */

/**
 * Check if current page is an achievement post type page.
 *
 * @param int $post_id Optional. Possible post_id to check
 * @return bool True if it's an achievement page, false if not
 * @since Achievements (3.0)
 */
function dpa_is_achievement( $post_id = 0 ) {
	$retval = false;

	// Supplied ID is an achievement
	if ( ! empty( $post_id ) && ( dpa_get_achievement_post_type() === get_post_type( $post_id ) ))
		$retval = true;

	return (bool) apply_filters( 'dpa_is_achievement', $retval, $post_id );
}

/**
 * Check if we are viewing an achievement post type archive page.
 *
 * @return bool
 * @since Achievements (3.0)
 */
function dpa_is_achievement_archive() {
	$retval = false;

	// Is achievement archive?
	if ( is_post_type_archive( dpa_get_achievement_post_type() ) || dpa_is_query_name( 'dpa_achievement_archive' ) )
		$retval = true;

	return (bool) apply_filters( 'dpa_is_achievement_archive', $retval );
}

/**
 * Check if we are viewing a single achievement page
 *
 * @return bool
 * @since Achievements (3.0)
 */
function dpa_is_single_achievement() {
	$retval = false;

	// Single and a match
	if ( is_singular( dpa_get_achievement_post_type() ) || dpa_is_query_name( 'dpa_single_achievement' ) )
		$retval = true;

	return (bool) apply_filters( 'dpa_is_single_achievement', $retval );
}

/**
 * Check if we are viewing a user's achievements page.
 *
 * @global WP_Query $wp_query
 * @return bool
 * @since Achievements (3.0)
 */
function dpa_is_single_user_achievements() {

	// Using BuddyPress user profiles
	if ( dpa_integrate_into_buddypress() ) {
		$retval = bp_is_user() && bp_is_current_component( dpa_get_authors_endpoint() ) && bp_is_current_action( 'all' );

	// Using WordPress' author page and the 'achievements' endpoint
	} else {
		global $wp_query;
		$retval = is_author() && isset( $wp_query->query_vars[dpa_get_authors_endpoint()] );
	}

	return (bool) apply_filters( 'dpa_is_single_user_achievements', $retval );
}

/**
 * Check if the current post type belongs to Achievements
 *
 * @param mixed $the_post Optional. Post object or post ID.
 * @return bool
 * @since Achievements (3.0)
 */
function dpa_is_custom_post_type( $the_post = false ) {
	$retval = false;

	// Viewing one of Achievements' post types
	if ( in_array( get_post_type( $the_post ), array( dpa_get_achievement_post_type(), dpa_get_progress_post_type(), ) ) )
		$retval = true;

	return (bool) apply_filters( 'dpa_is_custom_post_type', $retval, $the_post );
}

/**
 * Use the above is_() functions to output a body class for each possible scenario
 *
 * @param array $wp_classes
 * @param array $custom_classes Optional
 * @return array Body Classes
 * @since Achievements (3.0)
 */
function dpa_body_class( $wp_classes, $custom_classes = array() ) {
	$achievements_classes = array();

	// Archives
	if ( dpa_is_achievement_archive() )
		$achievements_classes[] = dpa_get_achievement_post_type() . '-archive';

	// Components
	if ( dpa_is_single_achievement() )
		$achievements_classes[] = dpa_get_achievement_post_type();

	// Does the user have any pending notifications?
	if ( dpa_user_has_notifications() )
		$achievements_classes[] = 'achievement-notifications';

	// Clean up

	// Add achievements class if we are on an Achievements page
	if ( ! empty( $achievements_classes ) )
		$achievements_classes[] = 'achievements';

	// Merge WP classes with Achievements classes
	$classes = array_merge( (array) $achievements_classes, (array) $wp_classes );

	// Remove any duplicates
	$classes = array_unique( $classes );

	return apply_filters( 'dpa_body_class', $classes, $achievements_classes, $wp_classes, $custom_classes );
}

/**
 * Use the above is_() functions to return if in any Achievements page
 *
 * @return bool In an Achievements page
 * @since Achievements (3.0)
 */
function is_achievements() {
	$retval = false;

	// Archives
	if ( dpa_is_achievement_archive() )
		$retval = true;

	// Components
	elseif ( dpa_is_single_achievement() )
		$retval = true;

	return (bool) apply_filters( 'is_achievements', $retval );
}


/**
 * Query functions
 */

/**
 * Check the passed parameter against the current _dpa_query_name
 *
 * @param string $name
 * @return bool True if match, false if not
 * @since Achievements (3.0)
 */
function dpa_is_query_name( $name )  {
	return (bool) ( dpa_get_query_name() === $name );
}

/**
 * Get the '_dpa_query_name' setting
 *
 * @return string To return the query var value
 * @since Achievements (3.0)
 */
function dpa_get_query_name() {
	return get_query_var( '_dpa_query_name' );
}

/**
 * Set the '_dpa_query_name' setting to $name
 *
 * @param string $name What to set the query var to
 * @since Achievements (3.0)
 */
function dpa_set_query_name( $name = '' )  {
	set_query_var( '_dpa_query_name', $name );
}

/**
 * Used to clear the '_dpa_query_name' setting
 *
 * @since Achievements (3.0)
 */
function dpa_reset_query_name() {
	dpa_set_query_name();
}


/**
 * Breadcrumbs
 */

/**
 * Output the page title as a breadcrumb
 *
 * @param array $args Optional. See dpa_get_breadcrumb()
 * @see dpa_get_breadcrumb()
 * @since Achievements (3.0)
 */
function dpa_title_breadcrumb( $args = array() ) {
	echo dpa_get_breadcrumb( $args );
}

/**
 * Output a breadcrumb
 *
 * @param array $args Optional. See dpa_get_breadcrumb()
 * @since Achievements (3.0)
 */
function dpa_breadcrumb( $args = array() ) {
	echo dpa_get_breadcrumb( $args );
}
	/**
	 * Return a breadcrumb ( achievement archive -> achievement -> [achievement, ...] )
	 *
	 * @param array $args Optional.
	 * @return string Breadcrumbs
	 * @since Achievements (3.0)
	 */
	function dpa_get_breadcrumb( $args = array() ) {
		// Turn off breadcrumbs
		if ( apply_filters( 'dpa_no_breadcrumb', is_front_page() ) )
			return;

		// Define variables
		$front_id         = $root_id                                 = 0;
		$ancestors        = $crumbs           = $tag_data            = array();
		$pre_root_text    = $pre_front_text   = $pre_current_text    = '';
		$pre_include_root = $pre_include_home = $pre_include_current = true;


		/**
		 * Home text
		 */

		// No custom home text
		if ( empty( $args['home_text'] ) ) {

			// Set home text to page title
			$front_id = get_option( 'page_on_front' );
			if ( ! empty( $front_id ) )
				$pre_front_text = get_the_title( $front_id );

			// Default to 'Home'
			else
				$pre_front_text = _x( 'Home', 'Home screen of the website', 'dpa' );
		}


		/**
		 * Root text
		 */

		// No custom root text
		if ( empty( $args['root_text'] ) ) {
			$page = dpa_get_page_by_path( dpa_get_root_slug() );
			if ( ! empty( $page ) )
				$root_id = $page->ID;

			$pre_root_text = dpa_get_achievement_archive_title();
		}


		/**
		 * Includes
		 */

		// Root slug is also the front page
		if ( ! empty( $front_id ) && ( $front_id === $root_id ) )
			$pre_include_root = false;

		// Don't show root if viewing achievement archive
		if ( dpa_is_achievement_archive() )
			$pre_include_root = false;

		// Don't show root if viewing page in place of achievement archive
		if ( ! empty( $root_id ) && ( ( is_single() || is_page() ) && ( $root_id === get_the_ID() ) ) )
			$pre_include_root = false;


		/**
		 * Current text
		 */

		// Achievement archive
		if ( dpa_is_achievement_archive() ) {
			$pre_current_text = dpa_get_achievement_archive_title();

		// Single achievement
		} elseif ( dpa_is_single_achievement() ) {
			$pre_current_text = dpa_get_achievement_title();

		// Single object of some type
		} else {
			$pre_current_text = get_the_title();
		}


		/**
		 * Parse args
		 */

		// Parse args
		$defaults = array(
			// HTML
			'before'          => '<div class="dpa-breadcrumb"><p>',
			'after'           => '</p></div>',

			// Separator
			'sep'             => is_rtl() ? _x( '&lsaquo;', 'HTML entity for left single angle quotes', 'dpa' ) : _x( '&rsaquo;', 'HTML entity for right single angle quotes', 'dpa' ),
			'pad_sep'         => 1,
			'sep_before'      => '<span class="dpa-breadcrumb-sep">',
			'sep_after'       => '</span>',

			// Crumbs
			'crumb_before'    => '',
			'crumb_after'     => '',

			// Home
			'include_home'    => $pre_include_home,
			'home_text'       => $pre_front_text,

			// Achievement root
			'include_root'    => $pre_include_root,
			'root_text'       => $pre_root_text,

			// Current
			'include_current' => $pre_include_current,
			'current_text'    => $pre_current_text,
			'current_before'  => '<span class="dpa-breadcrumb-current">',
			'current_after'   => '</span>',
		);
		$r = dpa_parse_args( $args, $defaults, 'get_breadcrumb' );
		extract( $r );


		/**
		 * Ancestors
		 */

		// Get post ancestors
		if ( is_singular() )
			$ancestors = array_reverse( get_post_ancestors( get_the_ID() ) );

		// Do we want to include a link to home?
		if ( ! empty( $include_home ) || empty( $home_text ) )
			$crumbs[] = '<a href="' . trailingslashit( home_url() ) . '" class="dpa-breadcrumb-home">' . $home_text . '</a>';

		// Do we want to include a link to the achievement root?
		if ( ! empty( $include_root ) || empty( $root_text ) ) {

			// Page exists at root slug path, so use its permalink
			$page = dpa_get_page_by_path( dpa_get_root_slug() );
			if ( ! empty( $page ) )
				$root_url = get_permalink( $page->ID );

			// Use the root slug
			else
				$root_url = get_post_type_archive_link( dpa_get_achievement_post_type() );

			// Add the breadcrumb
			$crumbs[] = '<a href="' . $root_url . '" class="dpa-breadcrumb-root">' . $root_text . '</a>';
		}

		// Ancestors exist
		if ( ! empty( $ancestors ) ) {

			// Loop through parents
			foreach( (array) $ancestors as $parent_id ) {

				// Parents
				$parent = get_post( $parent_id );

				// Skip parent if empty or error
				if ( empty( $parent ) || is_wp_error( $parent ) )
					continue;

				// Switch through post_type to ensure correct filters are applied
				switch ( $parent->post_type ) {
					// Achievement
					case dpa_get_achievement_post_type() :
						$crumbs[] = '<a href="' . dpa_get_achievement_permalink( $parent->ID ) . '" class="dpa-breadcrumb-achievement">' . dpa_get_achievement_title( $parent->ID ) . '</a>';
						break;

					// WordPress Post/Page/Other
					default :
						$crumbs[] = '<a href="' . get_permalink( $parent->ID ) . '" class="dpa-breadcrumb-item">' . get_the_title( $parent->ID ) . '</a>';
						break;
				}
			}
		}


		/**
		 * Current
		 */

		// Add current page to breadcrumb
		if ( ! empty( $include_current ) || empty( $current_text ) )
			$crumbs[] = $current_before . $current_text . $current_after;


		/**
		 * Separator
		 */

		// Wrap the separator in before/after before padding and filter
		if ( ! empty( $sep ) )
			$sep = $sep_before . $sep . $sep_after;

		// Pad the separator
		if ( ! empty( $pad_sep ) ) {
			$sep = str_pad( $sep, strlen( $sep ) + ( (int) $pad_sep * 2 ), ' ', STR_PAD_BOTH );

			if ( function_exists( 'mb_strlen' ) )
				$sep = str_pad( $sep, mb_strlen( $sep ) + ( (int) $r['pad_sep'] * 2 ), ' ', STR_PAD_BOTH );
			else
				$sep = str_pad( $sep, strlen( $sep ) + ( (int) $r['pad_sep'] * 2 ), ' ', STR_PAD_BOTH );
		}

		/**
		 * And -- eventually -- we're done.
		 */

		// Filter the separator and breadcrumb
		$sep    = apply_filters( 'dpa_breadcrumb_separator', $sep    );
		$crumbs = apply_filters( 'dpa_breadcrumbs',          $crumbs );

		// Build the trail
		$trail = ! empty( $crumbs ) ? ( $before . $crumb_before . implode( $sep . $crumb_after . $crumb_before , $crumbs ) . $crumb_after . $after ) : '';

		return apply_filters( 'dpa_get_breadcrumb', $trail, $crumbs, $r );
	}


/**
 * Errors & Messages
 */

/**
 * Display possible errors & messages inside a template file
 *
 * @since Achievements (3.0)
 */
function dpa_template_notices() {
	// Bail if no notices or errors
	if ( ! dpa_has_errors() )
		return;

	// Define local variable(s)
	$errors = $messages = array();

	// Loop through notices
	foreach ( achievements()->errors->get_error_codes() as $code ) {
		// Get notice severity
		$severity = achievements()->errors->get_error_data( $code );

		// Loop through notices and separate errors from messages
		foreach ( achievements()->errors->get_error_messages( $code ) as $error ) {
			if ( 'message' === $severity )
				$messages[] = $error;
			else
				$errors[] = $error;
		}
	}

	// Display errors first...
	if ( ! empty( $errors ) ) : ?>

		<div class="dpa-template-notice error">
			<p>
				<?php echo implode( "</p>\n<p>", $errors ); ?>
			</p>
		</div>

	<?php endif;

	// ...and messages last
	if ( ! empty( $messages ) ) : ?>

		<div class="dpa-template-notice">
			<p>
				<?php echo implode( "</p>\n<p>", $messages ); ?>
			</p>
		</div>

	<?php endif;
}


/**
 * Page title
 */

/**
 * Custom page title for Achievements pages
 *
 * @param string $title Optional. The title (not used).
 * @param string $sep Optional, default is '&raquo;'. How to separate each part within the page title.
 * @param string $seplocation Optional. Direction to display title, 'right'.
 * @return string The title
 * @since Achievements (3.0)
 */
function dpa_title( $title = '', $sep = '&raquo;', $seplocation = '' ) {
	$new_title = array();

	// Achievement archive
	if ( dpa_is_achievement_archive() ) {
		$new_title['text'] = dpa_get_achievement_archive_title();

	// Single achievement page
	} elseif ( dpa_is_single_achievement() ) {
		$new_title['text']   = dpa_get_achievement_title();
		$new_title['format'] = esc_attr__( 'Achievement: %s', 'dpa' );
	}

	$new_title = apply_filters( 'dpa_raw_title_array', $new_title ); 

	$new_title = dpa_parse_args( $new_title, array(
		'format' => '%s',
		'text'   => $title,
	), 'title' );

	// Get the formatted raw title
	$new_title = sprintf( $new_title['format'], $new_title['text'] );
	$new_title = apply_filters( 'dpa_raw_title', $new_title, $sep, $seplocation );

	// Compare new title with original title
	if ( $new_title === $title )
		return $title;

	// Temporary separator for accurate flipping, if necessary
	$t_sep  = '%WP_TITILE_SEP%';
	$prefix = '';

	if ( ! empty( $new_title ) )
		$prefix = " $sep ";

	// Separate on right, so reverse the order
	if ( 'right' === $seplocation ) {
		$new_title_array = explode( $t_sep, $new_title );
		$new_title_array = array_reverse( $new_title_array );
		$new_title       = implode( " $sep ", $new_title_array ) . $prefix;

	// Separate on left, do not reverse
	} else {
		$new_title_array = explode( $t_sep, $new_title );
		$new_title       = $prefix . implode( " $sep ", $new_title_array );
	}

	// Filter and return
	return apply_filters( 'dpa_title', $new_title, $sep, $seplocation );
}


/**
 * Forms
 */

/**
 * Output the required hidden form fields for redeeming an achievement
 *
 * @since Achievements (3.1)
 */
function dpa_redeem_achievement_form_fields() {
?>

	<input type="hidden" name="dpa_action" id="dpa_post_action" value="dpa-redeem-achievement" />
	<?php wp_nonce_field( 'dpa-redeem-achievement' );

}
