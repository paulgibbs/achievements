<?php
/**
 * Admin screens
 *
 * @author Paul Gibbs <paul@byotos.com>
 * @package Achievements
 * @subpackage admin
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Setup Achievements admin
 *
 * @since 3.0
 * @uses DPA_Admin
 */
function dpa_admin_init() {
	/**
	 * As suggested by bbPress, run the updater late on 'dpa_admin_init' to
	 * ensure that all alterations to the permalink structure have taken place.
	 */
	if ( dpa_do_update() )
		add_action( 'dpa_admin_init', 'dpa_setup_updater', 999 );

	add_submenu_page( 'edit.php?post_type=dpa_achievements', 'somethign', 'Supported Plugins', 'manage_options', 'myslug' );

	// Run an action to allow plugins to hook in
	do_action( 'dpa_admin_init' );
}
?>