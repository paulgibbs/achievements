<?php
/**
* Options
*
* This file is a bit empty right now but it will grow if/when config options
* are added to Achievements.
*
* @package Achievements
* @subpackage Options
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get the default site options and their values
 *
 * @return array Option names and values
 * @since 3.0
 */
function dpa_get_default_options() {
	$options = array(
		'_dpa_db_version' => '0',  // Initial DB version
	);

	return apply_filters( 'dpa_get_default_options', $options );
}

/**
 * Add default options to DB
 *
 * This is only called when the plugin is activated and is non-destructive,
 * so existing settings will not be overridden.
 *
 * @since 3.0
 */
function dpa_add_options() {
	$options = dpa_get_default_options();

	// Add default options
	foreach ( $options as $key => $value )
		add_option( $key, $value );

	// Run an action for other plugins
	do_action( 'dpa_add_options' );
}

/**
 * Delete default options
 *
 * Hooked to dpa_uninstall, it is only called when the plugin is uninstalled.
 * This is destructive, so existing settings will be destroyed.
 *
 * @since 3.0
 */
function dpa_delete_options() {
	$options = dpa_get_default_options();

	// Delete default options
	foreach ( $options as $key => $value )
		delete_option( $key );

	// Run an action for other plugins
	do_action( 'dpa_delete_options' );
}

/**
 * Add filters to each Achievements option and allow them to be overloaded
 * from inside the $achievements->options array.
 * 
 * @since 3.0
 */
function dpa_setup_option_filters() {
	$options = dpa_get_default_options();

	// Add filters to each option
	foreach ( $options as $key => $value )
		add_filter( 'pre_option_' . $key, 'dpa_pre_get_option' );

	// Run an action for other plugins
	do_action( 'dpa_setup_option_filters' );
}

/**
 * Filter default options and allow them to be overloaded from inside the
 * $achievements->options array.
 *
 * @global achievements $achievements Main Achievements object
 * @param bool $value
 * @return mixed
 * @since 3.0
 */
function dpa_pre_get_option( $value = false ) {
	global $achievements;

	// Get the name of the current filter so we can manipulate it, and remove the filter prefix
	$option = str_replace( 'pre_option_', '', current_filter() );

	// Check the options global for preset value
	if ( ! empty( $achievements->options[$option] ) )
		$value = $achievements->options[$option];

	return $value;
}
?>