<?php
/**
 * Achievements Capabilities
 *
 * @package Achievements
 * @subpackage CoreCapabilities
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Adds capabilities to WordPress user roles.
 *
 * This is called on plugin activation.
 *
 * @since Achievements (3.0)
 */
function dpa_add_caps() {
	global $wp_roles;

	// Load roles if not set
	if ( ! isset( $wp_roles ) )
		$wp_roles = new WP_Roles();

	// Loop through available roles
	foreach( $wp_roles->roles as $role => $details ) {

		// Load this role
		$this_role = get_role( $role );

		// Loop through available caps for this role and add them
		foreach ( dpa_get_caps_for_role( $role ) as $cap ) {
			$this_role->add_cap( $cap );
		}
	}

	do_action( 'dpa_add_caps' );
}

/**
 * Removes capabilities from WordPress user roles.
 *
 * This is called on plugin deactivation.
 *
 * @since Achievements (3.0)
 */
function dpa_remove_caps() {
	global $wp_roles;

	// Load roles if not set
	if ( ! isset( $wp_roles ) )
		$wp_roles = new WP_Roles();

	// Loop through available roles
	foreach( $wp_roles->roles as $role => $details ) {

		// Load this role
		$this_role = get_role( $role );

		// Loop through caps for this role and remove them
		foreach ( dpa_get_caps_for_role( $role ) as $cap ) {
			$this_role->remove_cap( $cap );
		}
	}

	do_action( 'dpa_remove_caps' );
}

/**
 * Maps 'achievements' post type caps to built-in WordPress caps
 *
 * @param array $caps Capabilities for meta capability
 * @param string $cap Capability name
 * @param int $user_id User id
 * @param mixed $args Arguments
 * @return array Actual capabilities for meta capability
 * @since Achievements (3.0)
 */
function dpa_map_meta_caps( $caps, $cap, $user_id, $args ) {
	switch ( $cap ) {

		// Reading
		case 'read_achievement'          :
		case 'read_achievement_progress' :
			$post = get_post( $args[0] );
			if ( ! empty( $post ) ) {

				$caps      = array();
				$post_type = get_post_type_object( $post->post_type );

				// Public post
				if ( 'publish' === $post->post_status )
					$caps[] = 'read';

				// User is author so allow read
				elseif ( (int) $user_id === (int) $post->post_author )
					$caps[] = 'read';

				else
					$caps[] = $post_type->cap->read_private_posts;
			}

			break;

		case 'read_achievements':
			// Add do_not_allow cap if user is spam or deleted
			if ( ! dpa_is_user_active( $user_id ) )
				$caps = array( 'do_not_allow' );		

			break;


		// Publishing
		case 'publish_achievements'           :
		case 'publish_achievement_progresses' :
			// Add do_not_allow cap if user is spam or deleted
			if ( ! dpa_is_user_active( $user_id ) )
				$caps = array( 'do_not_allow' );

			break;


		// Editing
		case 'edit_achievements'           :
		case 'edit_achievement_progresses' :
			// Add do_not_allow cap if user is spam or deleted
			if ( ! dpa_is_user_active( $user_id ) )
				$caps = array( 'do_not_allow' );

			break;

		case 'edit_achievement' :
		case 'edit_achievement_progress' :
			$_post = get_post( $args[0] );
			if ( ! empty( $_post ) ) {

				// Get caps for post type object
				$post_type = get_post_type_object( $_post->post_type );
				$caps      = array();

				// Add 'do_not_allow' cap if user is spam or deleted
				if ( ! dpa_is_user_active( $user_id ) ) {
					$caps[] = 'do_not_allow';

				// User is author so allow edit
				} elseif ( (int) $user_id === (int) $_post->post_author ) {
					$caps[] = $post_type->cap->edit_posts;

				// Unknown, so map to edit_others_posts
				} else {
					$caps[] = $post_type->cap->edit_others_posts;
				}
			}

			break;


		// Deleting
		case 'delete_achievement'          :
		case 'delete_achievement_progress' :
			$_post = get_post( $args[0] );
			if ( ! empty( $_post ) ) {

				// Get caps for post type object
				$post_type = get_post_type_object( $_post->post_type );
				$caps      = array();

				// Add 'do_not_allow' cap if user is spam or deleted
				if ( ! dpa_is_user_active( $user_id ) ) {
					$caps[] = 'do_not_allow';

				// User is author so allow to delete
				} elseif ( (int) $user_id === (int) $_post->post_author ) {
					$caps[] = $post_type->cap->delete_posts;

				// Unknown so map to delete_others_posts
				} else {
					$caps[] = $post_type->cap->delete_others_posts;
				}
			}

			break;
	}

	return apply_filters( 'dpa_map_meta_caps', $caps, $cap, $user_id, $args );
}

/**
 * Return achievement post type capabilities (mapped to meta caps)
 *
 * @return array achievement post type capabilities
 * @since Achievements (3.0)
 */
function dpa_get_achievement_caps() {
	$caps = array(
		'delete_others_posts' => 'delete_others_achievements',
		'delete_posts'        => 'delete_achievements',
		'edit_posts'          => 'edit_achievements',
		'edit_others_posts'   => 'edit_others_achievements',
		'publish_posts'       => 'publish_achievements',
		'read_posts'          => 'read_achievements',
		'read_private_posts'  => 'read_private_achievements',
	);

	return apply_filters( 'dpa_get_achievement_caps', $caps );
}

/**
 * Return achievement_progress post type capabilities (mapped to meta caps)
 *
 * @return array achievement_progress post type capabilities
 * @since Achievements (3.0)
 */
function dpa_get_achievement_progress_caps() {
	$caps = array(
		'delete_others_posts' => 'delete_others_achievement_progresses',
		'delete_posts'        => 'delete_achievement_progresses',
		'edit_posts'          => 'edit_achievement_progresses',
		'edit_others_posts'   => 'edit_others_achievement_progresses',
		'publish_posts'       => 'publish_achievement_progresses',
		'read_private_posts'  => 'read_private_achievement_progresses',
	);

	return apply_filters( 'dpa_get_achievement_progress_caps', $caps );
}

/**
 * Return achievement action capabilities (mapped to meta caps)
 *
 * @return array Topic tag capabilities
 * @since Achievements (3.0)
 */
function dpa_get_event_caps() {
	$caps = array(
		'assign_terms' => 'assign_achievement_events',
		'delete_terms' => 'delete_achievement_events',
		'edit_terms'   => 'edit_achievement_events',
		'manage_terms' => 'manage_achievement_events',
	);

	return apply_filters( 'dpa_get_event_caps', $caps );
}


/**
 * Returns an array of capabilities based on the role that is being requested.
 *
 * @since Achievements (3.0)
 *
 * @param string $role Optional. Defaults to The role to load caps for
 * @return array Capabilities for $role
 */
function dpa_get_caps_for_role( $role = '' ) {
	switch ( $role ) {

		// Administrator
		case 'administrator' :
			$caps = array(
				// Achievement caps
				'delete_achievements',
				'delete_others_achievements',
				'edit_achievements',
				'edit_others_achievements',
				'publish_achievements',
				'read_private_achievements',
				'read_achievements',

				// Achievement progress caps
				'delete_achievement_progresses',
				'delete_others_achievement_progresses',
				'edit_achievement_progresses',
				'edit_others_achievement_progresses',
				'publish_achievement_progresses',
				'read_private_achievement_progresses',

				// Event tax caps
				'assign_achievement_events',
				'delete_achievement_events',
				'edit_achievement_events',
				'manage_achievement_events',
			);

			break;

		// WordPress Core Roles
		case 'editor' :
			$caps = array(
				// Achievements caps
				'delete_achievements',
				'edit_achievements',
				'publish_achievements',
				'read_private_achievements',
				'read_achievements',

				// Achievement progress caps
				'delete_achievement_progresses',
				'delete_others_achievement_progresses',
				'edit_achievement_progresses',
				'edit_others_achievement_progresses',
				'publish_achievement_progresses',
				'read_private_achievement_progresses',

				// Event tax caps
				'assign_achievement_events',
			);

			break;

		case 'author'      :
		case 'contributor' :
		case 'subscriber'  :
		default            :
			$caps = array(
				// Achievement progress caps
				'delete_achievement_progresses',
				'edit_achievement_progresses',
				'publish_achievement_progresses',
				'read_achievements',
			);

			break;
	}

	return apply_filters( 'dpa_get_caps_for_role', $caps, $role );
}

/**
 * Can the current user see a specific UI element?
 * 
 * Used when registering post types and taxonomies to decide if 'show_ui' should
 * be set to true or false. Also used for fine-grained control over which admin
 * sections are visible under what conditions.
 *
 * This function is in core/caps.php rather than in /admin/ so that it
 * can be used during the dpa_register_post_types action.
 *
 * @param string $component Optional; defaults to no value. Component to check UI visibility for.
 * @return bool
 * @since Achievements (3.0)
 */
function dpa_current_user_can_see( $component = '' ) {
	$retval = false;

	// Which component are we checking UI visibility for?
	switch ( $component ) {
		// Everywhere

		case dpa_get_achievement_post_type() :  // Achievements
			$retval = current_user_can( 'edit_achievements' );
			break;


		// Admin setions

		default :  // Anything else
			// @todo Use DPA_Admin->minimum_capability
			$retval = current_user_can( 'manage_options' );
			break;
	}

	return (bool) apply_filters( 'dpa_current_user_can_see', (bool) $retval, $component );
}