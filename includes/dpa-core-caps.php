<?php
/**
 * Achievements Capabilities
 *
 * @package Achievements
 * @subpackage Capabilities
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Adds capabilities to WordPress user roles.
 *
 * This is called on plugin activation.
 *
 * @since 3.0
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
 * @since 3.0
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
 * @since 3.0
 */
function dpa_map_meta_caps( $caps, $cap, $user_id, $args ) {
	switch ( $cap ) {

		// Reading
		case 'read_achievement' :
			if ( $post = get_post( $args[0] ) ) {
				$caps      = array();
				$post_type = get_post_type_object( $post->post_type );

				if ( 'published' == $post->post_status )
					$caps[] = 'read';
				else
					$caps[] = $post_type->cap->read_private_posts;
			}

			break;

		// Editing
		case 'edit_achievements' :
			// Add do_not_allow cap if user is spam or deleted
			if ( ! dpa_is_user_active( $user_id ) )
				$caps = array( 'do_not_allow' );

			break;

		case 'edit_achievement' :
			if ( $post = get_post( $args[0] ) ) {
				$caps      = array();
				$post_type = get_post_type_object( $post->post_type );

				// Add 'do_not_allow' cap if user is spam or deleted
				if ( ! dpa_is_user_active( $user_id ) )
					$caps[] = 'do_not_allow';

				// Map to edit_posts
				elseif ( (int) $user_id == (int) $post->post_author )
					$caps[] = $post_type->cap->edit_posts;

				// Map to edit_others_posts
				else
					$caps[] = $post_type->cap->edit_others_posts;
			}

			break;

		// Deleting
		case 'delete_achievement' :
			if ( $post = get_post( $args[0] ) ) {
				$caps      = array();
				$post_type = get_post_type_object( $post->post_type );

				// Add 'do_not_allow' cap if user is spam or deleted
				if ( ! dpa_is_user_active( $user_id ) )
					$caps[] = 'do_not_allow';

				// Map to delete_posts
				elseif ( (int) $user_id == (int) $post->post_author )
					$caps[] = $post_type->cap->delete_posts;

				// Map to delete_others_posts
				else
					$caps[] = $post_type->cap->delete_others_posts;
			}

			break;
	}

	return apply_filters( 'dpa_map_meta_caps', $caps, $cap, $user_id, $args );
}

/**
 * Return forum capabilities (mapped to meta caps)
 *
 * @return array Forum capabilities
 * @since 3.0
 */
function dpa_get_achievement_caps() {
	$caps = array(
		'delete_others_posts' => 'delete_others_achievements',
		'delete_posts'        => 'delete_achievements',
		'edit_posts'          => 'edit_achievements',
		'edit_others_posts'   => 'edit_others_achievements',
		'publish_posts'       => 'publish_achievements',
		'read_private_posts'  => 'read_private_achievements',
	);

	return apply_filters( 'dpa_get_achievement_caps', $caps );
}

/**
 * Return achievement action capabilities (mapped to meta caps)
 *
 * @return array Topic tag capabilities
 * @since 3.0
 */
function dpa_get_action_caps() {
	$caps = array(
		'assign_terms' => 'assign_actions',
		'delete_terms' => 'delete_actions',
		'edit_terms'   => 'edit_actions',
		'manage_terms' => 'manage_actions',
	);

	return apply_filters( 'dpa_get_action_caps', $caps );
}


/**
 * Returns an array of capabilities based on the role that is being requested.
 *
 * @since 3.0
 *
 * @param string $role Optional. Defaults to The role to load caps for
 * @return array Capabilities for $role
 */
function dpa_get_caps_for_role( $role = '' ) {
	switch ( $role ) {

		// Administrator
		case 'administrator' :
			$caps = array(
				// Achievements caps
				'delete_achievements',
				'delete_others_achievements',
				'edit_achievements',
				'edit_others_achievements',
				'publish_achievements',
				'read_private_achievements',

				// Actions tax caps
				'assign_actions',
				'delete_actions',
				'edit_actions',
				'manage_actions',

				// Misc
				'view_trash',
			);

			break;

		// WordPress Core Roles
		case 'editor' :
			$caps = array(
				// Achievements caps
				'delete_achievements',
				'edit_achievements',
				'publish_achievements',

				// Actions tax caps
				'assign_actions',
			);

			break;

		case 'author'      :
		case 'contributor' :
		case 'subscriber'  :
		default            :
			$caps = array();

			break;
	}

	return apply_filters( 'dpa_get_caps_for_role', $caps, $role );
}
?>