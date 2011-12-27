<?php
/**
 * Batman begins
 *
 * @author Paul Gibbs <paul@byotos.com>
 * @package Achievements
 * @subpackage loader
 */

/*
Plugin Name: Achievements
Plugin URI: http://achievementsapp.wordpress.com/
Description: Achievements gives your BuddyPress community fresh impetus by promoting and rewarding social interaction with challenges, badges and points.
Version: 3
Requires at least: WP 3.3, BuddyPress 1.6
Tested up to: WP 3.3, BuddyPress 1.6
License: General Public License version 3
Author: Paul Gibbs
Author URI: http://byotos.com/
Network: true
Domain Path: /languages/
Text Domain: dpa

"Achievements"
Copyright (C) 2009-12 Paul Gibbs

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License version 3 as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see http://www.gnu.org/licenses/.
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * DB version. Used during upgrade checks.
 *
 * @since 1.0
 */
define( 'ACHIEVEMENTS_DB_VERSION', 30 );

/**
 * Plugin version number.
 */
define( 'ACHIEVEMENTS_VERSION', '3.0' );

/**
 * Attach Achievements to WordPress
 *
 * Achievements uses its own internal actions to help aid in additional plugin
 * development, and to limit the amount of potential future code changes when
 * updates to WordPress occur.
 *
 * NOTE: All actions are hooked to priority 15; BuddyPress' equivalents,
 * as of v1.6, are hooked to 10. This lets us find out if BuddyPress is loaded
 * by the time we load.
 *
 * This file contains the actions and filters that are used throughout Achievements.
 * They are consolidated here to make searching for them easier, and to help
 * developers understand at a glance the order in which things occur.
 * See also admin.php.
 */
add_action( 'plugins_loaded', 'dpa_loaded', 15 );
add_action( 'init',           'dpa_init', 15 );
add_action( 'widgets_init',   'dpa_widgets_init', 15 );

/**
 * dpa_loaded - Attached to 'plugins_loaded' above
 *
 * Attach various loader actions to the dpa_loaded action.
 * The load order helps to execute code at the correct time.
 */
add_action( 'dpa_loaded', 'dpa_include', 6 );

/**
 * dpa_init - Attached to 'init' above
 *
 * Attach various initialisation actions to the dpa_init action.
 * The load order helps to execute code at the correct time.
 */
add_action( 'dpa_init', 'dpa_load_textdomain', 2 );
add_action( 'dpa_init', 'dpa_register_post_types', 10 );
add_action( 'dpa_init', 'dpa_register_taxonomies', 14 );
add_action( 'dpa_init', 'dpa_ready', 999 );

if ( is_admin() ) {
	add_action( 'admin_init', 'dpa_admin_init', 999 );
	// @todo Or network_admin_init(). Nick BP's code.
}

/**
 * dpa_ready - Attached to 'wp' above
 *
 * Attach various initialisation actions to the dpa_ready action.
 * The load order helps to execute code at the correct time.
 */


/**
 * The hooks
 */

/**
 * Initialise code
 *
 * @since 3.0
 */
function dpa_init() {
	do_action( 'dpa_init' );
}

/**
 * Attached to plugins_loaded
 *
 * @since 3.0
 */
function dpa_loaded() {
	do_action( 'dpa_loaded' );
}

/**
 * Register widgets
 *
 * @since 3.0
 */
function dpa_widgets_init() {
	do_action( 'dpa_widgets_init' );
}

/**
 * Attached to wp
 *
 * @since 3.0
 */
function dpa_ready() {
	do_action( 'dpa_ready' );
}

/**
 * Includes our files
 *
 * @since 2.0
 */
function dpa_include() {
	require( dirname( __FILE__ ) . '/helpers.php' );
	require( dirname( __FILE__ ) . '/core.php' );
	require( dirname( __FILE__ ) . '/filters.php' );

	if ( is_admin() ) {
		require( dirname( __FILE__ ) . '/admin.php' );

		// Install/upgrader
		if ( dpa_is_update() )
			require( dirname( __FILE__ ) . '/upgrade.php' );
	}
}

/**
 * Below this point exist hookable functions for advanced customisation
 * of the Achievements load/unload process. Have fun!
 */

if ( ! function_exists( 'dpa_activate' ) ) :
/**
 * Plugin activation hook. Overridable by re-defining dpa_activate() in your own plugin.
 *
 * @since 3.0
 */
function dpa_activate() {
	do_action( 'dpa_activate' );
}
register_activation_hook( 'achievements/achievements.php', 'dpa_activate' );
endif;

if ( ! function_exists( 'dpa_deactivate' ) ) :
/**
 * Plugin deactivation hook. Overridable by re-defining dpa_deactivate() in your own plugin.
 *
 * @since 3.0
 */
function dpa_deactivate() {
	do_action( 'dpa_deactivate' );
}
register_deactivation_hook( 'achievements/achievements.php', 'dpa_deactivate' );
endif;
?>