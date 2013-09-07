<?php
/**
 * Achievements user options
 *
 * @package Achievements
 * @subpackage UserOptions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get the default user options and their values
 *
 * @return array
 * @since Achievements (3.0)
 */
function dpa_get_default_user_options() {
	return apply_filters( 'dpa_get_default_user_options', array(
		'_dpa_last_unlocked'  => 0,        // ID of the last achievement this user unlocked (per site or network)
		'_dpa_unlocked_count' => 0,        // How many achievements this user has unlocked (per site or network)
		'_dpa_points'         => 0,        // How many points this user has (per site or network)
		'_dpa_notifications'  => array(),  // User notifications (per site or network)
	) );
}

/**
 * Add default user options
 *
 * This is destructive, so existing Achievements user options will be overridden.
 *
 * @param int $user_id Optional; defaults to current user
 * @since Achievements (3.0)
 */
function dpa_add_user_options( $user_id = 0 ) {
	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = get_current_user_id();

	// No user, bail out
	if ( empty( $user_id ) )
		return;

	// As Achievements can run independently (as well as sitewide) on a multisite, decide where to store the user option
	$store_global = is_multisite() && dpa_is_running_networkwide();

	// Add default options
	foreach ( array_keys( dpa_get_default_user_options() ) as $key => $value )
		update_user_option( $user_id, $key, $value, $store_global );

	// Allow previously activated plugins to append their own user options.
	do_action( 'dpa_add_user_options', $user_id );
}

/**
 * Delete default user options
 *
 * Hooked to dpa_uninstall, it is only called once when Achievements is uninstalled.
 * This is destructive, so existing Achievements user options will be destroyed.
 *
 * @param int $user_id Optional; defaults to current user
 * @since Achievements (3.0)
 */
function dpa_delete_user_options( $user_id = 0 ) {
	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = get_current_user_id();

	// No user, bail out
	if ( empty( $user_id ) )
		return;

	// Delete default options (both per site and per network)
	foreach ( array_keys( dpa_get_default_user_options() ) as $key => $value ) {
		delete_user_option( $user_id, $key, false );
		delete_user_option( $user_id, $key, true );
	}

	// Allow previously activated plugins to append their own options.
	do_action( 'dpa_delete_user_options', $user_id );
}

/**
 * Add filters to each Achievement option and allow them to be overloaded from
 * inside the achievements()->options array.
 *
 * @since Achievements (3.0)
 */
function dpa_setup_user_option_filters() {
	// Add filters to each Achievements option
	foreach ( array_keys( dpa_get_default_user_options() ) as $key => $value )
		add_filter( 'get_user_option_' . $key, 'dpa_filter_get_user_option', 10, 3 );

	// Allow previously activated plugins to append their own options.
	do_action( 'dpa_setup_user_option_filters' );
}

/**
 * Filter default options and allow them to be overloaded from inside the
 * achievements()->user_options array.
 *
 * @since Achievements (3.0)
 * @param bool $value Optional. Fallback value if none found (default is false).
 * @param string $option Optional. Option name
 * @param WP_User $user Optional. User to get option for
 * @return mixed false if not overloaded, mixed if set
 */
function dpa_filter_get_user_option( $value = false, $option = '', $user = null ) {
	// Check the options global for preset value
	if ( isset( $user->ID ) && isset( achievements()->user_options[$user->ID] ) && ! empty( achievements()->user_options[$user->ID][$option] ) )
		$value = achievements()->user_options[$user->ID][$option];

	return $value;
}


/**
 * _dpa_unlocked_count option - user's unlocked achievements count
 */

/**
 * Update a user's unlocked achievement count
 *
 * @param int $user_id Optional. User ID to update. Optional, defaults to current logged in user.
 * @param int $new_value Optional. The new value.
 * @return bool False if no user or failure, true if successful
 * @since Achievements (3.0)
 */
function dpa_update_user_unlocked_count( $user_id = 0, $new_value = 0 ) {
	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = get_current_user_id();

	// No user, bail out
	if ( empty( $user_id ) )
		return false;

	// As Achievements can run independently (as well as sitewide) on a multisite, decide where to store the user option
	$store_global = is_multisite() && dpa_is_running_networkwide();

	$new_value = apply_filters( 'dpa_update_user_unlocked_count', $new_value, $user_id );
	return update_user_option( $user_id, '_dpa_unlocked_count', absint( $new_value ), $store_global );
}

/**
 * Output the total of how many achievements that this user has unlocked
 *
 * @param int $user_id Optional. User ID to retrieve value for
 * @since Achievements (3.0)
 */
function dpa_user_unlocked_count( $user_id = 0 ) {
	echo number_format_i18n( dpa_get_user_unlocked_count( $user_id ) );
}

	/**
	 * Return the total of how many achievements that this user has unlocked
	 *
	 * @param int $user_id Optional. User ID to retrieve value for
	 * @return mixed False if no user, option value otherwise.
	 * @since Achievements (3.0)
	 */
	function dpa_get_user_unlocked_count( $user_id = 0 ) {
		// Default to current user
		if ( empty( $user_id ) && is_user_logged_in() )
			$user_id = get_current_user_id();

		// No user, bail out
		if ( empty( $user_id ) )
			return false;

		$value = get_user_option( '_dpa_unlocked_count', $user_id );
		return absint( apply_filters( 'dpa_get_user_unlocked_count', $value, $user_id ) );
	}


/**
 * _dpa_points option - this user's points total
 */

/**
 * Update a user's points total
 *
 * @param int $new_value Optional. The new value
 * @param int $user_id User ID to update. Optional, defaults to current logged in user.
 * @return bool False if no user or failure, true if successful
 * @since Achievements (3.0)
 */
function dpa_update_user_points( $new_value = 0, $user_id = 0 ) {
	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = get_current_user_id();

	// No user, bail out
	if ( empty( $user_id ) )
		return false;

	// As Achievements can run independently (as well as sitewide) on a multisite, decide where to store the user option
	$store_global = is_multisite() && dpa_is_running_networkwide();

	$new_value = apply_filters( 'dpa_update_user_points', $new_value, $user_id );
	$retval    = update_user_option( $user_id, '_dpa_points', (int) $new_value, $store_global );

	// Effectively clears the cache for leaderboard results
	wp_cache_set( 'last_changed', microtime(), 'achievements_leaderboard' );

	return $retval;
}

/**
 * Output the user's points total
 *
 * @param int $user_id Optional. User ID to retrieve value for
 * @since Achievements (3.0)
 */
function dpa_user_points( $user_id = 0 ) {
	echo number_format_i18n( dpa_get_user_points( $user_id ) );
}

	/**
	 * Return the user's points total
	 *
	 * @param int $user_id Optional. User ID to retrieve value for
	 * @return mixed False if no user, option value otherwise (int).
	 * @since Achievements (3.0)
	 */
	function dpa_get_user_points( $user_id = 0 ) {
		// Default to current user
		if ( empty( $user_id ) && is_user_logged_in() )
			$user_id = get_current_user_id();

		// No user, bail out
		if ( empty( $user_id ) )
			return false;

		$value = get_user_option( '_dpa_points', $user_id );
		return (int) apply_filters( 'dpa_get_user_points', $value, $user_id );
	}


/**
 * _dpa_notifications option - this user's notifications
 */

/**
 * Update a user's notifications
 *
 * @param array $notifications Optional. The new value
 * @param int $user_id User ID to update. Optional, defaults to current logged in user.
 * @return bool False if no user or failure, true if successful
 * @since Achievements (3.0)
 */
function dpa_update_user_notifications( $notifications = array(), $user_id = 0 ) {

	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = get_current_user_id();

	// No user, bail out
	if ( empty( $user_id ) )
		return false;

	// As Achievements can run independently (as well as sitewide) on a multisite, decide where to store the user option
	$store_global = is_multisite() && dpa_is_running_networkwide();

	$notifications = (array) apply_filters( 'dpa_update_user_notifications', $notifications, $user_id );
	$new_values    = array();

	// Prevent people filtering in array keys that aren't unsigned integers
	foreach ( $notifications as $ID => $value )
		$new_values[absint( $ID )] = $value;

	if ( isset( $new_values[0] ) )
		unset( $new_values[0] );

	return update_user_option( $user_id, '_dpa_notifications', $new_values, $store_global );
}

/**
 * Return the user's notifications
 *
 * @param int $user_id Optional. User ID to retrieve value for
 * @return array
 * @since Achievements (3.0)
 */
function dpa_get_user_notifications( $user_id = 0 ) {
	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = get_current_user_id();

	// No user, bail out
	if ( empty( $user_id ) )
		return array();

	// Get notifications for this user
	$value = get_user_option( '_dpa_notifications', $user_id );
	if ( empty( $value ) )
		return array();

	return (array) apply_filters( 'dpa_get_user_notifications', $value, $user_id );
}


/**
 * _dpa_last_unlocked option - ID of user's last unlocked achievement
 */

/**
 * Update the ID of the last achievement this user unlocked
 *
 * @param int $user_id Optional. User ID to update. Optional, defaults to current logged in user.
 * @param int $new_value Optional. The new value.
 * @return bool False if no user or failure, true if successful
 * @since Achievements (3.0)
 */
function dpa_update_user_last_unlocked( $user_id = 0, $new_value = 0 ) {
	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = get_current_user_id();

	// No user, bail out
	if ( empty( $user_id ) )
		return false;

	// As Achievements can run independently (as well as sitewide) on a multisite, decide where to store the user option
	$store_global = is_multisite() && dpa_is_running_networkwide();

	$new_value = apply_filters( 'dpa_update_user_last_unlocked', $new_value, $user_id );
	return update_user_option( $user_id, '_dpa_last_unlocked', (int) $new_value, $store_global );
}

/**
 * Output the ID of the last achievement this user unlocked
 *
 * @param int $user_id Optional. User ID to retrieve value for
 * @since Achievements (3.0)
 */
function dpa_user_last_unlocked( $user_id = 0 ) {
	echo number_format_i18n( dpa_get_user_last_unlocked( $user_id ) );
}

	/**
	 * Return the ID of the last achievement this user unlocked
	 *
	 * @param int $user_id Optional. User ID to retrieve value for
	 * @return mixed False if no user, option value otherwise (int).
	 * @since Achievements (3.0)
	 */
	function dpa_get_user_last_unlocked( $user_id = 0 ) {
		// Default to current user
		if ( empty( $user_id ) && is_user_logged_in() )
			$user_id = get_current_user_id();

		// No user, bail out
		if ( empty( $user_id ) )
			return false;

		$value = get_user_option( '_dpa_last_unlocked', $user_id );
		return (int) apply_filters( 'dpa_get_user_last_unlocked', $value, $user_id );
	}
