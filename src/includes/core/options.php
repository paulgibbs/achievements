<?php
/**
* Achievement options
*
* @package Achievements
* @subpackage CoreOptions
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add default options to DB
 *
 * This is only called when the plugin is activated and is non-destructive,
 * so existing settings will not be overridden.
 *
 * @since Achievements (3.0)
 */
function dpa_add_options() {
	$options = dpa_get_default_options();

	// Add default options
	foreach ( $options as $key => $value )
		add_option( $key, $value );

	// Let other plugins add any extra options
	do_action( 'dpa_add_options' );
}

/**
 * Delete default options
 *
 * Hooked to dpa_uninstall, it is only called when the plugin is uninstalled.
 * This is destructive, so existing settings will be destroyed.
 *
 * @since Achievements (3.0)
 */
function dpa_delete_options() {
	// Delete default options
	foreach ( array_keys( dpa_get_default_options() ) as $key )
		delete_option( $key );

	// Let other plugins delete any extra options which they've added
	do_action( 'dpa_delete_options' );
}

/**
 * Add filters to each Achievements option and allow them to be overloaded
 * from inside the achievements()->options array.
 * 
 * @since Achievements (3.0)
 */
function dpa_setup_option_filters() {
	// Add filters to each option
	foreach ( array_keys( dpa_get_default_options() ) as $key )
		add_filter( 'pre_option_' . $key, 'dpa_pre_get_option' );

	// Let other plugins add their own option filters
	do_action( 'dpa_setup_option_filters' );
}

/**
 * Filter default options and allow them to be overloaded from inside the
 * achievements()->options array.
 *
 * @param string $value
 * @return mixed
 * @since Achievements (3.0)
 */
function dpa_pre_get_option( $value = '' ) {
	// Get the name of the current filter so we can manipulate it, and remove the filter prefix
	$option = str_replace( 'pre_option_', '', current_filter() );

	// Check the options global for preset value
	if ( isset( achievements()->options[$option] ) )
		$value = achievements()->options[$option];

	return $value;
}


/**
 * General settings
 */

/**
 * Get the current theme package ID
 *
 * @param string $default Optional. Default value 'default'
 * @return string ID of the subtheme
 * @since Achievements (3.0)
 */
function dpa_get_theme_package_id( $default = 'default' ) {
	return apply_filters( 'dpa_get_theme_package_id', get_option( '_dpa_theme_package_id', $default ) );
}

/**
 * Numeric settings
 */

/**
 * Return the achievements per page setting
 *
 * @return int
 * @since Achievements (3.0)
 */
function dpa_get_achievements_per_page() {
	$default = 15;

	// Get database option and cast as integer
	$per = $retval = (int) get_option( '_dpa_achievements_per_page', $default );

	// If return val is empty, set it to default
	if ( empty( $retval ) )
		$retval = $default;

	// Filter and return
	return (int) apply_filters( 'dpa_get_achievements_per_page', $retval, $per );
}

/**
 * Return the achievements per RSS page setting
 *
 * @return int
 * @since Achievements (3.0)
 */
function dpa_get_achievements_per_rss_page() {
	$default = 25;

	// Get database option and cast as integer
	$per = $retval = (int) get_option( '_dpa_achievements_per_rss_page', $default );

	// If return val is empty, set it to default
	if ( empty( $retval ) )
		$retval = $default;

	// Filter and return
	return (int) apply_filters( 'dpa_get_achievements_per_rss_page', $retval, $per );
}

/**
 * Return the progresses per page setting
 *
 * @return int
 * @since Achievements (3.0)
 */
function dpa_get_progresses_per_page() {
	$default = 15;

	// Get database option and cast as integer
	$per = $retval = (int) get_option( '_dpa_progresses_per_page', $default );

	// If return val is empty, set it to default
	if ( empty( $retval ) )
		$retval = $default;

	// Filter and return
	return (int) apply_filters( 'dpa_get_progresses_per_page', $retval, $per );
}

/**
 * Return the progresses per RSS page setting
 *
 * @return int
 * @since Achievements (3.0)
 */
function dpa_get_progresses_per_rss_page() {
	$default = 25;

	// Get database option and cast as integer
	$per = $retval = (int) get_option( '_dpa_progresses_per_rss_page', $default );

	// If return val is empty, set it to default
	if ( empty( $retval ) )
		$retval = $default;

	// Filter and return
	return (int) apply_filters( 'dpa_get_progresses_per_rss_page', $retval, $per );
}

/**
 * Return the leaderboard items per page setting
 *
 * @return int
 * @since Achievements (3.4)
 */
function dpa_get_leaderboard_items_per_page() {
	$default = 12;
	$per     = $retval = (int) get_option( '_dpa_leaderboard_per_page', $default );

	if ( empty( $retval ) )
		$retval = $default;

	return (int) apply_filters( 'dpa_get_leaderboard_items_per_page', $retval, $per );
}


/**
 * Boolean functions (aka is this thing on?)
 */


/**
 * Slug functions
 */

/**
 * Return the root slug
 *
 * @param string $default Optional; defaults to 'achievements'.
 * @return string
 * @since Achievements (3.0)
 */
function dpa_get_root_slug( $default = 'achievements' ) {
	return apply_filters( 'dpa_get_root_slug', get_option( '_dpa_root_slug', $default ) );
}

/**
 * Return the singular root slug
 *
 * @param string $default Optional; defaults to 'achievement'.
 * @return string
 * @since Achievements (3.4)
 */
function dpa_get_singular_root_slug( $default = 'achievement' ) {
	return apply_filters( 'dpa_get_singular_root_slug', get_option( '_dpa_singular_root_slug', $default ) );
}

/**
 * Return the achievement post type slug
 *
 * This is just a wrapper function for dpa_get_root_slug() right now.
 *
 * @return string
 * @since Achievements (3.0)
 */
function dpa_get_achievement_slug() {
	return apply_filters( 'dpa_get_achievement_slug', dpa_get_root_slug() );
}


/**
 * Extension functions
 */

/**
 * Return the _dpa_extension_versions option, which is an associative array that
 * extensions can use to store a version number.
 *
 * The format is ['my_plugin' => '1.0']
 *
 * @return array
 * @since Achievements (3.0)
 */
function dpa_get_extension_versions() {
	// If running network-wide, use the site options table
	if ( is_multisite() && dpa_is_running_networkwide() )
		$retval = get_site_option( '_dpa_extension_versions', array() );
	else
		$retval = get_option( '_dpa_extension_versions', array() );

	return apply_filters( 'dpa_get_extension_versions', $retval );
}

/**
 * Update the _dpa_extension_versions option.
 *
 * @param array $new_value
 * @return array
 * @see dpa_get_extension_versions()
 * @since Achievements (3.0)
 */
function dpa_update_extension_versions( $new_value ) {
	// If running network-wide, use the site options table
	if ( is_multisite() && dpa_is_running_networkwide() )
		update_site_option( '_dpa_extension_versions', $new_value );
	else
		update_option( '_dpa_extension_versions', $new_value );
}


/**
 * Stats functions
 */

/**
 * Return the ID of the last unlocked achievement
 *
 * @return int
 * @since Achievements (3.0)
 */
function dpa_stats_get_last_achievement_id() {
	$id = get_option( '_dpa_stats_last_achievement_id', 0 );

	return (int) apply_filters( 'dpa_stats_get_last_achievement_id', $id );
}

/**
 * Return the ID of the user who unlocked the last achievement
 *
 * @return int
 * @since Achievements (3.0)
 */
function dpa_stats_get_last_achievement_user_id() {
	$user_id = get_option( '_dpa_stats_last_achievement_user_id', 0 );

	return (int) apply_filters( 'dpa_stats_get_last_achievement_user_id', $user_id );
}

/**
 * Set the ID of the last unlocked achievement
 *
 * @param int $achievement_id
 * @since Achievements (3.0)
 */
function dpa_stats_update_last_achievement_id( $achievement_id ) {
	$achievement_id = apply_filters( 'dpa_stats_update_last_achievement_id', $achievement_id );

	update_option( '_dpa_stats_last_achievement_id', $achievement_id );
}

/**
 * Set the ID of the user who unlocked the last achievement
 *
 * @param int $user_id
 * @since Achievements (3.0)
 */
function dpa_stats_update_last_achievement_user_id( $user_id ) {
	$user_id = apply_filters( 'dpa_stats_update_last_achievement_user_id', $user_id );

	update_option( '_dpa_stats_last_achievement_user_id', $user_id );
}