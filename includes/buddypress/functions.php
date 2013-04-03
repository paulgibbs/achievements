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
 * We use it to record that BuddyPress is active on the site, and include other files that are dependant on BuddyPress being present.
 *
 * Achievements integration requires the xProfile component to be active.
 * Activity stream integration is optional and requires that the Activity component is active.
 *
 * @since Achievements (3.0)
 */
function dpa_bp_loaded() {
	achievements()->integrate_into_buddypress = buddypress() && ! buddypress()->maintenance_mode && bp_is_active( 'xprofile' );
	if ( ! achievements()->integrate_into_buddypress )
		return;

	// Load Achievements component for BuddyPress
	require( achievements()->includes_dir . 'class-dpa-buddypress-component.php' );
	achievements()->buddypress = new DPA_BuddyPress_Component();

	do_action( 'dpa_bp_loaded' );
}

/**
 * Hook the "my achievements" template into BP's plugins template
 *
 * This function requires BuddyPress.
 *
 * @since Achievements (3.2)
 */
function dpa_bp_members_my_achievements() {

	// Clear pending notifications when visiting your user achievement page
	if ( dpa_is_user_active() && bp_is_my_profile() )
		dpa_update_user_notifications();

	add_action( 'bp_template_content', 'dpa_bp_members_my_achievements_content' );
	bp_core_load_template( apply_filters( 'dpa_bp_members_my_achievements', 'members/single/plugins' ) );
}

/**
 * Render the "my achievements" template part.
 * 
 * Used by BuddyPress' members/single/plugins.php template part via the 'bp_template_content' filter.
 *
 * @since Achievements (3.2)
 */
function dpa_bp_members_my_achievements_content() {
	echo achievements()->shortcodes->display_user_achievements();
}
