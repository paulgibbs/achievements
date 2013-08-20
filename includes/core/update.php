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
 * @since Achievements (3.0)
 */
function dpa_is_install() {
	return ! dpa_get_db_version_raw();
}

/**
 * Compare the Achievements version to the DB version to determine if updating
 *
 * @return bool True if update, False if not
 * @since Achievements (3.0)
 */
function dpa_is_update() {
	return (bool) ( (int) dpa_get_db_version_raw() < (int) dpa_get_db_version() );
}

/**
 * Determine if Achievements is being activated
 *
 * @param string $basename Optional
 * @return bool True if activating Achievements, false if not
 * @since Achievements (3.0)
 */
function dpa_is_activation( $basename = '' ) {
	global $pagenow;

	// Bail if not in admin/plugins
	if ( ! is_admin() || 'plugins.php' !== $pagenow )
		return false;

	$action = false;

	if ( ! empty( $_REQUEST['action'] ) && '-1' !== $_REQUEST['action'] )
		$action = $_REQUEST['action'];
	elseif ( ! empty( $_REQUEST['action2'] ) && '-1' !== $_REQUEST['action2'] )
		$action = $_REQUEST['action2'];

	// Bail if not activating
	if ( empty( $action ) || ! in_array( $action, array( 'activate', 'activate-selected' ) ) )
		return false;

	// The plugin(s) being activated
	if ( $action === 'activate' )
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
 * @since Achievements (3.0)
 */
function dpa_is_deactivation( $basename = '' ) {
	global $pagenow;

	// Bail if not in admin/plugins
	if ( ! is_admin() || 'plugins.php' !== $pagenow )
		return false;

	$action = false;

	if ( ! empty( $_REQUEST['action'] ) && '-1' !== $_REQUEST['action'] )
		$action = $_REQUEST['action'];
	elseif ( ! empty( $_REQUEST['action2'] ) && '-1' !== $_REQUEST['action2'] )
		$action = $_REQUEST['action2'];

	// Bail if not deactivating
	if ( empty( $action ) || ! in_array( $action, array( 'deactivate', 'deactivate-selected' ) ) )
		return false;

	// The plugin(s) being deactivated
	if ( $action === 'deactivate' )
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
 * @since Achievements (3.0)
 */
function dpa_version_bump() {
	$db_version = dpa_get_db_version();
	update_option( '_dpa_db_version', $db_version );
}

/**
 * Set up Achievements' updater
 *
 * @since Achievements (3.0)
 */
function dpa_setup_updater() {
	// Bail if no update needed
	if ( ! dpa_is_update() )
		return;

	// Call the automated updater
	dpa_version_updater();
}

/**
 * Achievements' version updater looks at what the current database version is and
 * runs whatever other code is needed.
 *
 * This is most-often used when the data schema changes, but should also be used
 * to correct issues with Achievements meta-data silently on software update.
 *
 * @since Achievements (3.0)
 */
function dpa_version_updater() {
	// Get the raw database version
	$raw_db_version = (int) dpa_get_db_version_raw();

	// Chill; there's nothing to do for now!

	// Bump the version
	dpa_version_bump();

	// Delete rewrite rules to force a flush
	dpa_delete_rewrite_rules();
}

/**
 * Create initial content on plugin activation or on a new site (in multisite).
 *
 * @param array $args Array of arguments to override default values
 * @since Achievements (3.0)
 */
function dpa_create_initial_content( $args = array() ) {
}

/**
 * Redirect user to Achievements's "What's New" page on activation
 *
 * @since Achievements (3.4)
 */
function dpa_add_activation_redirect() {

	// Bail if activating from network, or bulk.
	if ( isset( $_GET['activate-multi'] ) )
		return;

	// Record that this is a new installation, so we show the right welcome message
	if ( dpa_is_install() )
		set_transient( '_dpa_is_new_install', true, 30 );

	// Add the transient to redirect
	set_transient( '_dpa_activation_redirect', true, 30 );
}
