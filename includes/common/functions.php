<?php
/**
 * Common functions
 *
 * Common functions are ones that are used by more than one component, like
 * achievements, achievement_progress, events taxonomy...
 *
 * @package Achievements
 * @subpackage CommonFunctions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Time/date
 */

/**
 * Output formatted time to display human readable time difference.
 *
 * @param string $older_date Unix timestamp from which the difference begins.
 * @param bool|string $newer_date Optional. Unix timestamp from which the difference ends. False for current time.
 * @param int $gmt Optional. Whether to use GMT timezone. Default is false. 
 * @since Achievements (3.0)
 */
function dpa_time_since( $older_date, $newer_date = false, $gmt = false ) {
	echo dpa_get_time_since( $older_date, $newer_date, $gmt );
}
	/**
	 * Return formatted time to display human readable time difference.
	 *
	 * @param string $older_date Unix timestamp from which the difference begins.
	 * @param bool|string $newer_date Optional. Unix timestamp from which the difference ends. False for current time.
	 * @param int $gmt Optional. Whether to use GMT timezone. Default is false. 
	 * @return string Formatted time
	 * @since Achievements (3.0)
	 */
	function dpa_get_time_since( $older_date, $newer_date = false, $gmt = false ) {		
		// Setup the strings
		$unknown_text   = apply_filters( 'dpa_time_since_unknown_text',   _x( 'sometime',  'time', 'dpa' ) );
		$right_now_text = apply_filters( 'dpa_time_since_right_now_text', _x( 'right now', 'time', 'dpa' ) );
		$ago_text       = apply_filters( 'dpa_time_since_ago_text',       _x( '%s ago',    'time', 'dpa' ) );

		// Array of time period chunks
		$chunks = array(
			array( 60 * 60 * 24 * 365 , __( 'year',   'dpa' ), __( 'years',   'dpa' ) ),
			array( 60 * 60 * 24 * 30 ,  __( 'month',  'dpa' ), __( 'months',  'dpa' ) ),
			array( 60 * 60 * 24 * 7,    __( 'week',   'dpa' ), __( 'weeks',   'dpa' ) ),
			array( 60 * 60 * 24 ,       __( 'day',    'dpa' ), __( 'days',    'dpa' ) ),
			array( 60 * 60 ,            __( 'hour',   'dpa' ), __( 'hours',   'dpa' ) ),
			array( 60 ,                 __( 'minute', 'dpa' ), __( 'minutes', 'dpa' ) ),
			array( 1,                   __( 'second', 'dpa' ), __( 'seconds', 'dpa' ) )
		);

		if ( ! empty( $older_date ) && ! is_numeric( $older_date ) ) {
			$time_chunks = explode( ':', str_replace( ' ', ':', $older_date ) );
			$date_chunks = explode( '-', str_replace( ' ', '-', $older_date ) );
			$older_date  = gmmktime( (int) $time_chunks[1], (int) $time_chunks[2], (int) $time_chunks[3], (int) $date_chunks[1], (int) $date_chunks[2], (int) $date_chunks[0] );
		}

		/**
		 * $newer_date will equal false if we want to know the time elapsed
		 * between a date and the current time. $newer_date will have a value if
		 * we want to work out time elapsed between two known dates.
		 */
		$newer_date = ( ! $newer_date ) ? strtotime( current_time( 'mysql', $gmt ) ) : $newer_date;

		// Difference in seconds
		$since = $newer_date - $older_date;

		// Something went wrong with date calculation and we ended up with a negative date.
		if ( 0 > $since ) {
			$output = $unknown_text;

		/**
		 * We only want to output two chunks of time here, eg:
		 *     x years, xx months
		 *     x days, xx hours
		 * so there's only two bits of calculation below:
		 */
		} else {

			// Step one: the first chunk
			for ( $i = 0, $j = count( $chunks ); $i < $j; ++$i ) {
				$seconds = $chunks[$i][0];

				// Finding the biggest chunk (if the chunk fits, break)
				$count = floor( $since / $seconds );
				if ( 0 != $count ) {
					break;
				}
			}

			// If $i iterates all the way to $j, then the event happened 0 seconds ago
			if ( ! isset( $chunks[$i] ) ) {
				$output = $right_now_text;

			} else {

				// Set output var
				$output = ( 1 == $count ) ? '1 '. $chunks[$i][1] : $count . ' ' . $chunks[$i][2];

				// Step two: the second chunk
				if ( $i + 2 < $j ) {
					$seconds2 = $chunks[$i + 1][0];
					$name2    = $chunks[$i + 1][1];
					$count2   = floor( ( $since - ( $seconds * $count ) ) / $seconds2 );

					// Add to output var
					if ( 0 != $count2 ) {
						$output .= ( 1 == $count2 ) ? _x( ',', 'Separator in time since', 'dpa' ) . ' 1 '. $name2 : _x( ',', 'Separator in time since', 'dpa' ) . ' ' . $count2 . ' ' . $chunks[$i + 1][2];
					}
				}

				// No output, so happened right now
				if ( ! (int) trim( $output ) ) {
					$output = $right_now_text;
				}
			}
		}

		// Append 'ago' to the end of time-since if not 'right now'
		if ( $output !== $right_now_text ) {
			$output = sprintf( $ago_text, $output );
		}

		return apply_filters( 'dpa_get_time_since', $output, $older_date, $newer_date );
	}


/**
 * Errors
 */

/**
 * Adds an error message to later be output in the theme
 *
 * @param string $code Unique code for the error message
 * @param string $message Translated error message
 * @param string $data Any additional data passed with the error message
 * @since Achievements (3.0)
 */
function dpa_add_error( $code = '', $message = '', $data = '' ) {
	achievements()->errors->add( $code, $message, $data );
}

/**
 * Check if error messages exist in queue
 *
 * @since Achievements (3.0)
 */
function dpa_has_errors() {
	$has_errors = achievements()->errors->get_error_codes() ? true : false; 

	return apply_filters( 'dpa_has_errors', $has_errors, achievements()->errors );
}


/**
 * Versions
 */

/**
 * Output the Achievements version
 *
 * @since Achievements (3.0)
 */
function dpa_version() {
	echo dpa_get_version();
}
	/**
	 * Return the Achievements version
	 *
	 * @since Achievements (3.0)
	 * @return string The Achievements version
	 */
	function dpa_get_version() {
		return achievements()->version;
	}

/**
 * Output the Achievements database version
 *
 * @uses dpa_get_version() To get the Achievements DB version
 */
function dpa_db_version() {
	echo dpa_get_db_version();
}
	/**
	 * Return the Achievements database version
	 *
	 * @since Achievements (3.0)
	 * @return string The Achievements version
	 */
	function dpa_get_db_version() {
		return achievements()->db_version;
	}

/**
 * Output the Achievements database version directly from the database
 *
 * @since Achievements (3.0)
 */
function dpa_db_version_raw() {
	echo dpa_get_db_version_raw();
}
	/**
	 * Return the Achievements database version directly from the database
	 *
	 * @return string The current Achievements version
	 * @since Achievements (3.0)
	 */
	function dpa_get_db_version_raw() {
		return get_option( '_dpa_db_version', '' );
	}


/**
 * Assorted "ifs" and "maybe do something" functions.
 */

/**
 * Tells Achievements whether to integrate with BuddyPress' profile component or not.
 *
 * @return
 * @since Achievements (3.2)
 */
function dpa_integrate_into_buddypress() {
	return apply_filters( 'dpa_integrate_into_buddypress', achievements()->integrate_into_buddypress );
}


/**
 * Returns true if WP_DEBUG is set.
 *
 * @return bool
 * @since Achievements (3.2)
 */
function dpa_is_developer_mode() {
	return apply_filters( 'dpa_is_developer_mode', defined( 'WP_DEBUG' ) && WP_DEBUG );
}


/**
 * Queries
 */

/**
 * Assist pagination by returning correct page number
 *
 * @return int Current page number
 * @since Achievements (3.0)
 */
function dpa_get_paged() {
	global $wp_query;

	// Check the query var
	if ( get_query_var( 'paged' ) ) {
		$paged = get_query_var( 'paged' );

	// Check query paged
	} elseif ( ! empty( $wp_query->query['paged'] ) ) {
		$paged = $wp_query->query['paged'];
	}

	// Paged found
	if ( ! empty( $paged ) )
		return (int) $paged;

	return 1;
}

/**
 * Assist pagination by returning correct page number for leaderboard pagination
 *
 * @return int Current page number
 * @since Achievements (3.4)
 * @todo If a top-level /leaderboard/ rewrite is ever added, we can make this properly use query vars.
 */
function dpa_get_leaderboard_paged() {
	return ( ! empty( $_GET['leaderboard-page'] ) ) ? (int) $_GET['leaderboard-page'] : 1;
}

/**
 * Hooked to both activated_plugin and deactivated_plugin, this function detects when BuddyPress has
 * been (de-)activated, and flushes the rewrite rules.
 *
 * This is done because we move the "my achievements" pages away from the author permalinks into the
 * BP user profiles (or vice-versa) depending on the new state of the plugin.
 *
 * @param string $plugin_basename PHP's basename() of the plugin file which is being (de-)activated.
 * @since Achievements (3.2)
 */
function dpa_check_buddypress_is_active( $plugin_basename ) {
	if ( strpos( 'buddypress/bp-loader.php', $plugin_basename ) === false )
		return;

	// Ah hah! Caught you red-handed.
	dpa_delete_rewrite_rules();
}

/**
 * Delete a site's rewrite rules so that they are automatically rebuilt on subsequent page load.
 *
 * @since Achievements (3.0)
 */
function dpa_delete_rewrite_rules() {
	delete_option( 'rewrite_rules' );
}

/**
 * Merge user defined arguments into defaults array.
 *
 * This function is used throughout Achievements to allow for either a string or array
 * to be merged into another array. It is identical to dpa_parse_args() except
 * it allows for arguments to be passively or aggressively filtered using the
 * optional $filter_key parameter.
 *
 * @param string|array $args Value to merge with $defaults
 * @param array $defaults Array that serves as the defaults.
 * @param string $filter_key String to key the filters from
 * @return array Merged user defined values with defaults.
 * @since Achievements (3.0)
 */
function dpa_parse_args( $args, $defaults = array(), $filter_key = '' ) {
	// Setup a temporary array from $args
	if ( is_object( $args ) )
		$r = get_object_vars( $args );
	elseif ( is_array( $args ) )
		$r =& $args;
	else
		wp_parse_str( $args, $r );

	// Passively filter the args before the parse
	if ( ! empty( $filter_key ) )
		$r = apply_filters( 'dpa_before_' . $filter_key . '_parse_args', $r );

	// Parse
	if ( is_array( $defaults ) && ! empty( $defaults ) )
		$r = array_merge( $defaults, $r );

	// Aggressively filter the args after the parse
	if ( ! empty( $filter_key ) )
		$r = apply_filters( 'dpa_after_' . $filter_key . '_parse_args', $r );

	return $r;
}

/**
 * Used to guess if page exists at requested path
 *
 * @param string $path Optional
 * @since Achievements (3.0)
 * @todo Do I need to worry about the plugin running sitewide in multisite?
 * @return mixed False if no page, Page object if true
 */
function dpa_get_page_by_path( $path = '' ) {
	$retval = false;

	// Path is not empty
	if ( ! empty( $path ) ) {

		// Pretty permalinks are on so path might exist
		if ( get_option( 'permalink_structure' ) )
			$retval = get_page_by_path( $path );
	}

	return apply_filters( 'dpa_get_page_by_path', $retval, $path );
}


/**
 * Nonces
 */

/**
 * Makes sure the user requested an action from another page on this site.
 *
 * To avoid security exploits within the theme.
 *
 * @param string $action Optional. Action nonce
 * @param string $query_arg Optional (defaults to _wpnonce). Where to look for nonce in $_REQUEST
 * @see https://bbpress.trac.wordpress.org/ticket/1863 for the background of the bbPress version of the function
 * @since Achievements (3.1)
 * @todo Support port number for non-multisite.
 */
function dpa_verify_nonce_request( $action = '', $query_arg = '_wpnonce' ) {

	// Parse home_url() into pieces to remove query strings, strange characters, and other funny things that plugins might to do to it.
	$parsed_home = parse_url( home_url( '/', ( is_ssl() ? 'https://' : 'http://' ) ) ); 

	if ( isset( $parsed_home['port'] ) )
		$parsed_host = $parsed_home['host'] . ':' . $parsed_home['port'];
	else
		$parsed_host = $parsed_home['host'];

	// Set the home URL for use in comparisons
	$home_url = trim( strtolower( $parsed_home['scheme'] . '://' . $parsed_host . $parsed_home['path'] ), '/' );

	// Maybe include the port, if it's included in home_url()
	if ( isset( $parsed_home['port'] ) )
		$request_host = $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'];
	else
		$request_host = $_SERVER['HTTP_HOST'];

	// Build the currently requested URL
	$scheme        = is_ssl() ? 'https://' : 'http://';
	$requested_url = strtolower( $scheme . $request_host . $_SERVER['REQUEST_URI'] );

	// Check the nonce
	$result = isset( $_REQUEST[$query_arg] ) ? wp_verify_nonce( $_REQUEST[$query_arg], $action ) : false;

	// Did the nonce check fail?
	if ( empty( $result ) || empty( $action ) || ( strpos( $requested_url, $home_url ) !== 0 ) )
		$result = false;

	do_action( 'dpa_verify_nonce_request', $action, $result );
	return $result;
}
