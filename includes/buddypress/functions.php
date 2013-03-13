<?php
/**
 * Integrates Achievements into BuddyPress
 *
 * @package Achievements
 * @subpackage BuddyPressFunctions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

 /**
 * This function is hooked to the "bp_include" action from BuddyPress.
 * We use it to record that BuddyPress is active on the site, and include other files
 * that are dependant on BuddyPress being present.
 *
 * @since Achievements (3.0)
 */
function dpa_bp_loaded() {
	achievements()->integrate_into_buddypress = bp_is_active( 'xprofile' );

	// Bail if in maintenance mode
	if ( ! buddypress() || buddypress()->maintenance_mode )
		return;

	// Load Achievements component for BuddyPress
	require( achievements()->includes_dir . 'class-dpa-buddypress-component.php' );
	achievements()->buddypress = new DPA_BuddyPress_Component();

	do_action( 'dpa_bp_loaded' );
}
