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
 * If there is no raw DB version, this is the first installation
 *
 * @return bool True if update, False if not
 * @since 3.0
 */
function dpa_is_install() {
	return ! dpa_get_db_version_raw();
}

/**
 * Compare the Achievements version to the DB version to determine if updating
 *
 * @return bool True if update, False if not
 * @since 3.0
 */
function dpa_is_update() {
	return (bool) ( (int) dpa_get_db_version_raw() < (int) dpa_get_db_version() );
}

/**
 * Determine if Achievements is being activated
 *
 * @param string $basename Optional
 * @return bool True if activating Achievements, false if not
 * @since 3.0
 */
function dpa_is_activation( $basename = '' ) {
	$action = false;

	if ( ! empty( $_REQUEST['action'] ) && '-1' != $_REQUEST['action'] )
		$action = $_REQUEST['action'];
	elseif ( ! empty( $_REQUEST['action2'] ) && '-1' != $_REQUEST['action2'] )
		$action = $_REQUEST['action2'];

	// Bail if not activating
	if ( empty( $action ) || ! in_array( $action, array( 'activate', 'activate-selected' ) ) )
		return false;

	// The plugin(s) being activated
	if ( $action == 'activate' )
		$plugins = isset( $_GET['plugin'] ) ? array( $_GET['plugin'] ) : array();
	else
		$plugins = isset( $_POST['checked'] ) ? (array) $_POST['checked'] : array();

	// Set basename if empty
	if ( empty( $basename ) && ! empty( achievements()->basename ) )
		$basename = achievements()->basename;

	// Bail if no basename
	if ( empty( $basename ) )
		return false;

	// Is Achievements being activated?
	return in_array( $basename, $plugins );
}

/**
 * Determine if Achievements is being deactivated
 *
 * @param string $basename Optional
 * @return bool True if deactivating Achievements, false if not
 * @since 3.0
 */
function dpa_is_deactivation( $basename = '' ) {
	$action = false;

	if ( ! empty( $_REQUEST['action'] ) && '-1' != $_REQUEST['action'] )
		$action = $_REQUEST['action'];
	elseif ( ! empty( $_REQUEST['action2'] ) && '-1' != $_REQUEST['action2'] )
		$action = $_REQUEST['action2'];

	// Bail if not deactivating
	if ( empty( $action ) || ! in_array( $action, array( 'deactivate', 'deactivate-selected' ) ) )
		return false;

	// The plugin(s) being deactivated
	if ( $action == 'deactivate' )
		$plugins = isset( $_GET['plugin'] ) ? array( $_GET['plugin'] ) : array();
	else
		$plugins = isset( $_POST['checked'] ) ? (array) $_POST['checked'] : array();

	// Set basename if empty
	if ( empty( $basename ) && ! empty( achievements()->basename ) )
		$basename = achievements()->basename;

	// Bail if no basename
	if ( empty( $basename ) )
		return false;

	// Is Achievements being deactivated?
	return in_array( $basename, $plugins );
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
 * @todo This. Add a 'hello world' achievement.
 */
function dpa_create_initial_content( $args = array() ) {
}