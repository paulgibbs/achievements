<?php

/**
 * Achievements admin actions
 *
 * This file contains the actions that are used throughout Achievements admin. They
 * are consolidated here to make searching for them easier, and to help developers
 * understand at a glance the order in which things occur.
 *
 * @package Achievements
 * @subpackage AdminActions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Attach Achievements to WordPress
 *
 * Achievements uses its own internal actions to help aid in third-party plugin
 * development, and to limit the amount of potential future code changes when
 * updates to WordPress core occur.
 *
 * These actions exist to create the concept of 'plugin dependencies'. They
 * provide a safe way for plugins to execute code *only* when Achievements is
 * installed and activated, without needing to do complicated guesswork.
 */
add_action( 'admin_init',        'dpa_admin_init'                     );
add_action( 'admin_menu',        'dpa_admin_menu'                     );
add_action( 'admin_head',        'dpa_admin_head'                     );
//add_action( 'admin_notices',     'dpa_admin_notices'                 );
//add_action( 'custom_menu_order', 'dpa_admin_custom_menu_order'        );
//add_action( 'menu_order',        'dpa_admin_menu_order'               );
add_action( 'wpmu_new_blog',     'dpa_new_site',                10, 6 );

// Hook on to admin_init
add_action( 'dpa_admin_init', 'dpa_setup_updater',           999 );
add_action( 'dpa_admin_init', 'dpa_do_activation_redirect',  1   );
//add_action( 'dpa_admin_init', 'dpa_register_admin_settings'      );

// Initialize the admin area
add_action( 'dpa_init', 'dpa_admin_setup' );

// Activation
add_action( 'dpa_activation', 'dpa_add_caps',             2 );
add_action( 'dpa_activation', 'dpa_delete_rewrite_rules', 4 );

// Deactivation
add_action( 'dpa_deactivation', 'dpa_remove_caps',          2 );
add_action( 'dpa_deactivation', 'dpa_delete_rewrite_rules', 4 );

// New site created in multisite
add_action( 'dpa_new_site', 'dpa_add_caps',               4 );
add_action( 'dpa_new_site', 'dpa_create_initial_content', 6 );


/**
 * When a new site is created in a multisite installation, run the activation routine on that site.
 *
 * @param int $blog_id
 * @param int $user_id
 * @param string $domain
 * @param string $path
 * @param int $site_id
 * @param array() $meta
 * @since Achievements (3.0)
 */
function dpa_new_site( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

	// Bail if plugin has not been network activated 
	if ( ! is_plugin_active_for_network( achievements()->basename ) ) 
		return; 

	// Switch to the new blog
	switch_to_blog( $blog_id );

	// Do the Achievements activation routine
	do_action( 'dpa_new_site' );

	// restore original blog
	restore_current_blog();
}

// Sub-Actions

/**
 * Piggy back admin_init action
 *
 * @since Achievements (3.0)
 */
function dpa_admin_init() {
	do_action( 'dpa_admin_init' );
}

/**
 * Piggy back admin_menu action
 *
 * @since Achievements (3.0)
 */
function dpa_admin_menu() {
	do_action( 'dpa_admin_menu' );
}

/**
 * Piggy back admin_head action
 *
 * @since Achievements (3.0)
 */
function dpa_admin_head() {
	do_action( 'dpa_admin_head' );
}