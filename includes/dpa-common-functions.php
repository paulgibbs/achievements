<?php
/**
 * Common/helper functions
 *
 * @package Achievements
 * @subpackage Functions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Output the Achievements version
 *
 * @since 3.0
 */
function dpa_version() {
	echo dpa_get_version();
}
	/**
	 * Return the Achievements version
	 *
	 * @since 3.0
	 * @global achievements $achievements Main Achievements object
	 * @return string The Achievements version
	 */
	function dpa_get_version() {
		global $achievements;
		return $achievements->version;
	}

/**
 * Output the Achievements database version
 *
 * @uses dpa_get_version() To get the Achievements DB version
 */
function dpa_db_version() {
	echo dpa_get_db_version();
}
	/**
	 * Return the Achievements database version
	 *
	 * @since 3.0
	 * @global achievements $achievements Main Achievements object
	 * @return string The Achievements version
	 */
	function dpa_get_db_version() {
		global $achievements;
		return $achievements->db_version;
	}


/**
 * Errors
 */

/**
 * Adds an error message to later be output in the theme
 *
 * @global achievements $achievements Main Achievements object
 * @param string $code Unique code for the error message
 * @param string $message Translated error message
 * @param string $data Any additional data passed with the error message
 * @since 3.0
 */
function dpa_add_error( $code = '', $message = '', $data = '' ) {
	global $achievements;
	$achievements->errors->add( $code, $message, $data );
}

/**
 * Check if error messages exist in queue
 *
 * @global achievements $achievements Main Achievements object
 * @since 3.0
 */
function dpa_has_errors() {
	global $achievements;

	// Assume no errors
	$has_errors = false;

	// Check for errors
	if ( $achievements->errors->get_error_codes() )
		$has_errors = true;

	return apply_filters( 'dpa_has_errors', $has_errors, $achievements->errors );
}


/**
 * Users
 */

/**
 * Checks if user is active
 * 
 * @param int $user_id The user ID to check
 * @return bool True if public, false if not
 * @since 3.0
 */
function dpa_is_user_active( $user_id = 0 ) {
	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = get_current_user_id();

	// No user to check
	if ( empty( $user_id ) )
		return false;

	// Check spam
	if ( dpa_is_user_spammer( $user_id ) )
		return false;

	// Check deleted
	if ( dpa_is_user_deleted( $user_id ) )
		return false;

	// Assume true if not spam or deleted
	return true;
}

/**
 * Checks if the user has been marked as a spammer.
 *
 * @param int $user_id int The ID for the user.
 * @return bool True if spammer, False if not.
 * @since 3.0
 */
function dpa_is_user_spammer( $user_id = 0 ) {
	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = get_current_user_id();

	// No user to check
	if ( empty( $user_id ) )
		return false;

	// Assume user is not spam
	$is_spammer = false;

	// Get user data
	$user = get_userdata( $user_id );

	// No user found
	if ( empty( $user ) ) {
		$is_spammer = false;

	// User found
	} else {

		// Check if spam
		if ( !empty( $user->spam ) )
			$is_spammer = true;

		if ( 1 == $user->user_status )
			$is_spammer = true;
	}

	return apply_filters( 'dpa_is_user_spammer', (bool) $is_spammer );
}

/**
 * Checks if the user has been marked as deleted.
 *
 * @param int $user_id int The ID for the user.
 * @return bool True if deleted, False if not.
 * @since 3.0
 */
function dpa_is_user_deleted( $user_id = 0 ) {
	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = get_current_user_id();

	// No user to check
	if ( empty( $user_id ) )
		return false;

	// Assume user is not deleted
	$is_deleted = false;

	// Get user data
	$user = get_userdata( $user_id );

	// No user found
	if ( empty( $user ) ) {
		$is_deleted = true;

	// User found
	} else {

		// Check if deleted
		if ( !empty( $user->deleted ) )
			$is_deleted = true;

		if ( 2 == $user->user_status )
			$is_deleted = true;
	}

	return apply_filters( 'dpa_is_user_deleted', (bool) $is_deleted );
}

/**
 * Check if the current post type belongs to Achievements
 *
 * @return bool
 * @since 3.0
 */
function dpa_is_custom_post_type() {
	// Current post type
	$post_type = get_post_type();

	// Achievements' post types
	$achievements_post_types = array(
		dpa_get_achievement_post_type(),
	);

	// Viewing one of Achievements' post types
	if ( in_array( $post_type, $achievements_post_types ) )
		return true;

	return false;
}

/**
 * Output the unique id of the custom post type for achievements
 *
 * @since 3.0
 * @uses dpa_get_achievement_post_type() To get the forum post type
 */
function dpa_achievement_post_type() {
	echo dpa_get_achievement_post_type();
}
	/**
	 * Return the unique id of the custom post type for achievements
	 *
	 * @global achievements $achievements Main Achievements object
	 * @return string The unique forum post type id
	 * @since 3.0
	 */
	function dpa_get_achievement_post_type() {
		global $achievements;
		return apply_filters( 'dpa_get_achievement_post_type', $achievements->achievement_post_type );
	}

/**
 * Pre-cache the supported plugins details when the plugin is activated for a
 * better first-time user experience.
 *
 * @since 3.0
 */
function dpa_precache_supported_plugins() {
	if ( ! is_admin() )
		return;

	/**
	 * Make a non-blocking GET request to the 'Supported Plugins' admin page,
	 * which will pre-cache the supported plugins list, ensuring a quick load
	 * for the first time the user really visits the page.
	 */
	wp_remote_get( admin_url( 'edit.php?post_type=dpa_achievements&page=achievements-plugins' ), array( 'blocking' => false, 'sslverify' => false ) );
}

/**
 * Placeholder function to return a list of 3rd party plugins that are
 * supported by Achievements. Likely to change as plugin structure is
 * developed.
 *
 * Returned array consists of nested arrays and objects, one for each
 * supported plugin. Structure is:
 *
 * ['plugin_ID']->contributors   *      Array of key/value pairs for each contributor (User name gravatar URL)
 * ['plugin_ID']->description    *      Plugin description
 * ['plugin_ID']->image->large          URL to plugin image (large size)
 * ['plugin_ID']->install_status *      Result from install_plugin_install_status(); is the plugin installed on the current site?
 * ['plugin_ID']->name           *      Plugin name
 * ['plugin_ID']->rating         *      1.0-5.0 plugin rating from wporg
 * ['plugin_ID']->rss_url               RSS news feed URL
 * ['plugin_ID']->slug                  Plugin slug
 * ['plugin_ID']->wporg_url             wporg/extends URL page for this plugin
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

	// Load required library
	require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

	// If we can't communicate with wporg, fall back to this set of data for the plugins
	$plugins_fallback = array(
		'bbpress'               => array( 'name' => __( 'bbPress', 'dpa' ), 'description' => __( "bbPress is forum software with a twist from the creators of WordPress", 'dpa' ), 'rss' => 'http://bbpress.org/blog/feed/', ),
		'bp-gtm-system'         => array( 'name' => __( 'BP GTM System', 'dpa' ), 'description' => __( "BP GTM System will turn your site into a developer center, where tasks, projects, discussions, categories and tags will help you maintain products.", 'dpa' ), 'rss' => 'http://feeds2.feedburner.com/Cosydalecom', ),
		'buddypress'            => array( 'name' => __( 'BuddyPress', 'dpa' ), 'description' => __( "Social networking in a box. Build a social network for your company, school, sports team or niche community.", 'dpa' ), 'rss' => 'http://buddypress.org/feed/', ),
		'buddypress-courseware' => array( 'name' => __( 'BuddyPress ScholarPress Courseware', 'dpa' ), 'description' => __( "A Learning Management System for BuddyPress", 'dpa' ), 'rss' => 'http://sushkov.wordpress.com/feed/,' ),
		'buddypress-docs'       => array( 'name' => __( 'BuddyPress Docs', 'dpa' ), 'description' => __( "Adds collaborative Docs to BuddyPress.", 'dpa' ), 'rss' => 'http://teleogistic.net/feed/', ),
		'buddypress-links'      => array( 'name' => __( 'BuddyPress Links', 'dpa' ), 'description' => __( "BuddyPress Links is a drop in link and rich media sharing component for BuddyPress 1.2.x.", 'dpa' ), 'rss' => 'http://community.presscrew.com/feed/', ),
		'buddystream'           => array( 'name' => __( 'BuddyStream', 'dpa' ), 'description' => __( "BuddyStream is a BuddyPress plugin that will synchronize all of your favorite Social Networks to the BuddyPress activity stream.", 'dpa' ), 'rss' => 'http://buddystream.net/feed', ),
		'invite-anyone'         => array( 'name' => __( 'Invite Anyone', 'dpa' ), 'description' => __( "Makes BuddyPress's invitation features more powerful.", 'dpa' ), 'rss' => 'http://teleogistic.net/feed/', ),
		'wp-e-commerce'         => array( 'name' => __( 'WP e-Commerce', 'dpa' ), 'description' => __( "WP e-Commerce is a free WordPress Shopping Cart Plugin that lets customers buy your products, services and digital downloads online.", 'dpa' ), 'rss' => 'http://getshopped.org/blog/feed', ),
	);

	$plugins = array();

	// Fetch information for each plugin
	foreach ( $plugins_fallback as $slug => $plugin_data ) {
		// See if we have this data in cache
		$data = get_transient( 'dpa_plugin_' . $slug );

		// Not cached, so get data
		if ( ! $data ) {
			// Build fallback before querying wporg
			$plugin                 = new stdClass;
			$plugin->contributors   = array();
			$plugin->description    = $plugin_data['description'];
			$plugin->image->large   = esc_url( $achievements->plugin_url . 'images/' . $slug . '.png' );
			$plugin->install_status = false;
			$plugin->install_url    = esc_url( admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=' . $slug . '&amp;TB_iframe=true&amp;width=600&amp;height=550' ) );
			$plugin->name           = $plugin_data['name'];
			$plugin->rating         = 0.0;
			$plugin->rss_url        = $plugin_data['rss'];
			$plugin->slug           = $slug;
			$plugin->wporg_url      = esc_url( 'http://wordpress.org/extend/plugins/' . $slug );

			// Query wporg
			$wporg = plugins_api( 'plugin_information', array( 'slug' => $slug, 'fields' => array( 'short_description' => true ), ) );

			// Run a query to allow third parties to filter results from wporg
			$wporg = apply_filters( 'dpa_get_supported_plugins_wporg', $wporg, $slug, $plugin_data );

			// Overwrite the fallback data with new values from wporg
			if ( ! is_wp_error( $wporg ) ) {
				$plugin->contributors   = (array) $wporg->contributors;
				$plugin->description    = $wporg->short_description;
				$plugin->install_status = install_plugin_install_status( $wporg );
				$plugin->name           = $wporg->name;
				$plugin->rating         = floatval( 0.05 * $wporg->rating );
			}

			// Convert wporg contributors data into profiles.wporg URL and Gravatar.
			$plugin->contributors = dpa_supported_plugins_get_contributor_data( $plugin->contributors );

			// Cache data for 72 hours
			set_transient( 'dpa_plugin_' . $slug, $plugin, 60 * 60 * 24 * 3 );

			$data = $plugin;
		}

		$plugins[] = $data;
	}

	return apply_filters( 'dpa_get_supported_plugins', $plugins );
}

/**
 * Get profiles.wporg URL and Gravatar from a plugin's raw contributors data, from plugins_api().
 *
 * @param array $contributors Raw data for a plugin's contributors, from plugins_api()
 * @return array Consists of nested arrays of [contributor name, profileswp.org URL, Gravatar URL]
 * @since 3.0
 * @todo Figure out how to handle 3rd party plugins adding their own support for Achievements.
 */
function dpa_supported_plugins_get_contributor_data( $raw_contributors ) {
	// We don't know email addresses for Gravatars, so use lookup table
	$people = array(
		'apeatling'             => 'http://www.gravatar.com/avatar/bb29d699b5cba218c313b61aa82249da?s=24&d=monsterid&r=g',
		'Blackphantom'          => 'http://www.gravatar.com/avatar/fa62da3fa8b3997be04448e7280dad29?s=24&d=monsterid&r=g',
		'boonebgorges'          => 'http://www.gravatar.com/avatar/9cf7c4541a582729a5fc7ae484786c0c?s=24&d=monsterid&r=g',
		'chexee'                => 'http://www.gravatar.com/avatar/0231c6b98cf90defe76bdad0c3c66acf?s=24&d=monsterid&r=g',
		'cuny-academic-commons' => 'http://www.gravatar.com/avatar/80c3fc801559bbc7111d5e3f56ac6a4c?s=24&d=monsterid&r=g',
		'DJPaul'                => 'http://www.gravatar.com/avatar/3bc9ab796299d67ce83dceb9554f75df?s=24&d=monsterid&r=g',
		'garyc40'               => 'http://www.gravatar.com/avatar/aea5ee57d1e882ad17e95c99265784d1?s=24&d=monsterid&r=g',
		'jeremyboggs'           => 'http://www.gravatar.com/avatar/2a062a10cb94152f4ab3daf569af54c3?s=24&d=monsterid&r=g',
		'jghazally'             => 'http://www.gravatar.com/avatar/e20e6b90f2f3862025ede5bf90b80a3d?s=24&d=monsterid&r=g',
		'johnjamesjacoby'       => 'http://www.gravatar.com/avatar/81ec16063d89b162d55efe72165c105f?s=24&d=monsterid&r=g',
		'matt'                  => 'http://www.gravatar.com/avatar/767fc9c115a1b989744c755db47feb60?s=24&d=monsterid&r=g',
		'MrMaz'                 => 'http://www.gravatar.com/avatar/a32efc5efefecb3fb1ef1149e23a077c?s=24&d=monsterid&r=g',
		'mufasa'                => 'http://www.gravatar.com/avatar/5ba89a2ce585864ce73cafa7e79d114c?s=24&d=monsterid&r=g',
		'mychelle'              => 'http://www.gravatar.com/avatar/da623a80bd7d7ded418c528a689520a3?s=24&d=monsterid&r=g',
		'slaFFik'               => 'http://www.gravatar.com/avatar/61fb07ede3247b63f19015f200b3eb2c?s=24&d=monsterid&r=g',
		'sushkov'               => 'http://www.gravatar.com/avatar/39639fde05c65fae440b775989e55006?s=24&d=monsterid&r=g',
		'valant'                => 'http://www.gravatar.com/avatar/bbbe17bf36f7df6f19c00f28f3614d4c?s=24&d=monsterid&r=g',
		'valentinas'            => 'http://www.gravatar.com/avatar/f8393ef67ed5144e15890d60eaa2289e?s=24&d=monsterid&r=g',
	);

	// Filter for 3rd party plugins
	$people = apply_filters( 'dpa_supported_plugins_get_contributors', $people );

	// Build array of contributors for the current plugin
	$contributors = array();
	foreach ( $raw_contributors as $contributor_slug => $contributor_profile ) {
		$contributors[$contributor_slug] = ! empty( $people[$contributor_slug] ) ? $people[$contributor_slug] : '';
	}

	return apply_filters( 'dpa_supported_plugins_get_contributor_data', $contributors, $raw_contributors, $people );
}
?>