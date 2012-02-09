<?php
/**
 * Achievements Admin screen functions
 *
 * Common or post type/taxonomy-specific customisations.
 *
 * @package Achievements
 * @subpackage Administration
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Filter sample permalinks so that certain languages display properly.
 *
 * @param string $post_link Custom post type permalink
 * @param object $post Post data object
 * @param bool $leavename Optional, defaults to false. Whether to keep post name or page name.
 * @param bool $sample Optional, defaults to false. Is it a sample permalink.
 * @return string The custom post type permalink
 * @since 3.0
 */
function dpa_filter_sample_permalink( $post_link, $post, $leavename, $sample ) {
	// Bail if not on an admin page and not getting a sample permalink
	if ( ! empty( $sample ) && is_admin() && dpa_is_custom_post_type() )
		return urldecode( $post_link );

	// Return post link
	return $post_link;
}

/**
 * Placeholder function to return a list of 3rd party plugins that are
 * supported by Achievements. Likely to change as plugin structure is
 * developed.
 *
 * Returned array consists of nested arrays and objects, one for each
 * supported plugin. Structure is:
 *
 * ['plugin_ID']->name         *        Plugin name
 * ['plugin_ID']->description  *        Plugin description
 * ['plugin_ID']->wporg_url             wporg/extends URL page for this plugin
 * ['plugin_ID']->image->large          URL to plugin image (large size)
 *
 * Properties marked with an asterisk are auto-updated periodically from wporg.
 *
 * @global achievements $achievements Main Achievements object
 * @return array See function description for structure of returned array
 * @since 1.0
 * @todo Figure out how to handle 3rd party plugins adding their own support for Achievements.
 */
function dpa_get_supported_plugins() {
	global $achievements;

	// If we can't communicate with wporg, fall back to this set of data for the plugins
	$plugins_fallback = array(
		'bbpress'               => array( 'name' => __( 'bbPress', 'dpa' ), 'description' => __( "bbPress is forum software with a twist from the creators of WordPress", 'dpa' ) ),
		'bp-gtm-system'         => array( 'name' => __( 'BP GTM System', 'dpa' ), 'description' => __( "BP GTM System will turn your site into a developer center, where tasks, projects, discussions, categories and tags will help you maintain products.", 'dpa' ) ),
		'buddypress'            => array( 'name' => __( 'BuddyPress', 'dpa' ), 'description' => __( "Social networking in a box. Build a social network for your company, school, sports team or niche community.", 'dpa' ) ),
		'buddypress-courseware' => array( 'name' => __( 'BuddyPress ScholarPress Courseware', 'dpa' ), 'description' => __( "A Learning Management System for BuddyPress", 'dpa' ) ),
		'buddypress-docs'       => array( 'name' => __( 'BuddyPress Docs', 'dpa' ), 'description' => __( "Adds collaborative Docs to BuddyPress.", 'dpa' ) ),
		'buddystream'           => array( 'name' => __( 'BuddyStream', 'dpa' ), 'description' => __( "BuddyStream is a BuddyPress plugin that will synchronize all of your favorite Social Networks to the BuddyPress activity stream.", 'dpa' ) ),
		'invite-anyone'         => array( 'name' => __( 'Invite Anyone', 'dpa' ), 'description' => __( "Makes BuddyPress's invitation features more powerful.", 'dpa' ) ),
		'wp-e-commerce'         => array( 'name' => __( 'WP e-Commerce', 'dpa' ), 'description' => __( "WP e-Commerce is a free WordPress Shopping Cart Plugin that lets customers buy your products, services and digital downloads online.", 'dpa' ) ),
	);

	$plugins = array();
	require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

	// Fetch information for each plugin
	foreach ( $plugins_fallback as $slug => $plugin_data ) {
		// See if we have this data in cache
		$data = get_transient( 'dpa_plugin_' . $slug );

		// Not cached, so get data
		if ( ! $data ) {
			// Build fallback before querying wporg
			$plugin               = new stdClass;
			$plugin->name         = $plugin_data['name'];
			$plugin->description  = $plugin_data['description'];
			$plugin->image->large = 'http://placehold.it/772x250';  // $achievements->plugin_dir . 'images/' . urldecode( $slug ) . '.png';
			$plugin->wporg_url    = esc_url( 'http://wordpress.org/extend/plugins/' . $slug );

			// Query wporg
			$wporg = plugins_api( 'plugin_information', array( 'slug' => $slug, 'fields' => array( 'short_description' => true ), ) );

			// Run a query to allow third parties to filter results from wporg
			$wporg = apply_filters( 'dpa_get_supported_plugins_wporg', $wporg, $slug, $plugin_data );

			// Overwrite the fallback data with new values from wporg
			if ( ! is_wp_error( $wporg ) ) {
				$plugin->name        = $wporg->name;
				$plugin->description = $wporg->short_description;
			}

			// Cache data for 72 hours
			set_transient( 'dpa_plugin_' . $slug, $plugin, 60 * 60 * 24 * 3 );
		}

		$plugins[] = $data;
	}
	
	return apply_filters( 'dpa_get_supported_plugins', $plugins );
}
?>