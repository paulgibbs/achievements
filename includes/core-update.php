<?php

/**
 * Achievements Updater
 *
 * @package Achievements
 * @subpackage CoreUpdate
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Compare the Achievements version to the DB version to determine if updating
 *
 * @return bool True if update, False if not
 * @since 3.0
 */
function dpa_is_update() {
	// Current DB version of this site (per site in a multisite network)
	$current_db   = get_option( '_dpa_db_version' );
	$current_live = dpa_get_db_version();

	// Compare versions (cast as int and bool to be safe)
	$is_update = (bool) ( (int) $current_db < (int) $current_live );

	return $is_update;
}

/**
 * Determine if Achievements is being activated
 *
 * @return bool True if activating Achievements, false if not
 * @since 3.0
 */
function dpa_is_activation( $basename = '' ) {
	// Baif if action or plugin are empty, or not activating
	if ( empty( $_GET['action'] ) || empty( $_GET['plugin'] ) || 'activate' !== $_GET['action'] )
		return false;

	// Get the plugin being activated
	$plugin = isset( $_GET['plugin'] ) ? $_GET['plugin'] : '';

	// Set basename if empty
	if ( empty( $basename ) && ! empty( achievements()->basename ) )
		$basename = achievements()->basename;

	// Bail if no basename
	if ( empty( $basename ) )
		return false;

	// Bail if plugin is not Achievements
	if ( $basename !== $_GET['plugin'] )
		return false;

	return true;
}

/**
 * Determine if Achievements is being deactivated
 *
 * @return bool True if deactivating Achievements, false if not
 * @since 3.0
 */
function dpa_is_deactivation( $basename = '' ) {
	// Baif if action or plugin are empty, or not deactivating
	if ( empty( $_GET['action'] ) || empty( $_GET['plugin'] ) || 'deactivate' !== $_GET['action'] )
		return false;

	// Get the plugin being deactivated
	$plugin = isset( $_GET['plugin'] ) ? $_GET['plugin'] : '';

	// Set basename if empty
	if ( empty( $basename ) && ! empty( achievements()->basename ) )
		$basename = achievements()->basename;

	// Bail if no basename
	if ( empty( $basename ) )
		return false;

	// Bail if plugin is not Achievements
	if ( $basename !== $plugin )
		return false;

	return true;
}

/**
 * Update Achievements to the latest version
 *
 * @since 3.0
 */
function dpa_version_bump() {
	$db_version = dpa_get_db_version();
	update_option( '_dpa_db_version', $db_version );
}

/**
 * Set up Achievements' updater
 *
 * @since 3.0
 */
function dpa_setup_updater() {
	// Are we running an outdated version of Achievements?
	if ( dpa_is_update() ) {

		// Bump the version
		dpa_version_bump();

		// Run the deactivation function to wipe roles, caps, and rewrite rules
		dpa_deactivation();

		// Run the activation function to reset roles, caps, and rewrite rules
		dpa_activation();
	}
}

/**
 * Create initial content on plugin activation or on a new site (in multisite).
 *
 * @param array $args Array of arguments to override default values
 * @since 3.0
 * @todo This.
 */
function dpa_create_initial_content( $args = array() ) {
}