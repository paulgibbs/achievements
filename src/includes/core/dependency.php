<?php
/**
 * Plugin Dependency
 *
 * The purpose of the following actions is to mimic the behaviour of something
 * called 'plugin dependency' which enables a plugin to have plugins of their
 * own in a safe and reliable way.
 *
 * This is done in BuddyPress and bbPress. We do this by mirroring existing
 * WordPress actions in many places allowing dependant plugins to hook into
 * the Achievements specific ones, thus guaranteeing proper code execution.
 *
 * @package Achievements
 * @subpackage CoreDependency
 */

/**
 * Activation Actions
 */

/**
 * Runs on plugin activation
 *
 * @since Achievements (3.0)
 */
function dpa_activation() {
	do_action( 'dpa_activation' );
}

/**
 * Runs on plugin deactivation
 *
 * @since Achievements (3.0)
 */
function dpa_deactivation() {
	do_action( 'dpa_deactivation' );
}

/**
 * Runs when uninstalling the plugin
 *
 * @since Achievements (3.0)
 */
function dpa_uninstall() {
	do_action( 'dpa_uninstall' );
}


/**
 * Main Actions
 */

/**
 * Main action responsible for constants, globals, and includes
 *
 * @since Achievements (3.0)
 */
function dpa_loaded() {
	do_action( 'dpa_loaded' );
}

/**
 * Set up constants
 *
 * @since Achievements (3.0)
 */
function dpa_constants() {
	do_action( 'dpa_constants' );
}

/**
 * Set up globals BEFORE includes
 *
 * @since Achievements (3.0)
 */
function dpa_bootstrap_globals() {
	do_action( 'dpa_bootstrap_globals' );
}

/**
 * Include files
 *
 * @since Achievements (3.0)
 */
function dpa_includes() {
	do_action( 'dpa_includes' );
}

/**
 * Set up globals AFTER includes
 *
 * @since Achievements (1.0)
 */
function dpa_setup_globals() {
	do_action( 'dpa_setup_globals' );
}

/**
 * Initialise any code after everything has been loaded
 *
 * @since Achievements (2.0)
 */
function dpa_init() {
	do_action( 'dpa_init' );
}

/** 
 * Register any objects before anything is initialised.
 * 
 * @since Achievements (3.0)
 */ 
function dpa_register() { 
	do_action( 'dpa_register' );
}

/**
 * Initialise widgets
 *
 * @since Achievements (3.0)
 */
function dpa_widgets_init() {
	do_action( 'dpa_widgets_init' );
}

/**
 * Setup the currently logged-in user
 *
 * @since Achievements (3.0)
 */
function dpa_setup_current_user() {
	do_action( 'dpa_setup_current_user' );
}

/**
 * Supplemental Actions
 */

/**
 * Load translations for current language
 *
 * @since Achievements (1.0)
 */
function dpa_load_textdomain() {
	do_action( 'dpa_load_textdomain' );
}

/**
 * Set up the post types
 *
 * @since Achievements (3.0)
 */
function dpa_register_post_types() {
	do_action( 'dpa_register_post_types' );
}

/**
 * Set up the post statuses
 *
 * @since Achievements (3.0)
 */
function dpa_register_post_statuses() {
	do_action( 'dpa_register_post_statuses' );
}

/**
 * Register the built-in taxonomies
 *
 * @since Achievements (3.0)
 */
function dpa_register_taxonomies() {
	do_action( 'dpa_register_taxonomies' );
}

/**
 * Register custom endpoints
 *
 * @since Achievements (3.0)
 */
function dpa_register_endpoints() {
	do_action( 'dpa_register_endpoints' );
}

/**
 * Register the Achievements shortcodes
 *
 * @since Achievements (3.0)
 */
function dpa_register_shortcodes() {
	do_action( 'dpa_register_shortcodes' );
}

/**
 * Enqueue CSS and JS
 *
 * @since Achievements (3.0)
 */
function dpa_enqueue_scripts() {
	do_action( 'dpa_enqueue_scripts' );
}

/**
 * Add Toolbar support
 *
 * @since Achievements (3.0)
 */
function dpa_admin_bar_menu() {
	do_action( 'dpa_admin_bar_menu' );
}

/**
 * Add custom image sizes for cropping
 *
 * @since Achievements (3.3)
 */
function dpa_register_image_sizes() {
	do_action( 'dpa_register_image_sizes' );
}

/**
 * Everything's loaded and ready to go!
 *
 * @since Achievements (3.0)
 */
function dpa_ready() {
	do_action( 'dpa_ready' );
}


/**
 * Theme Permissions
 */

/**
 * The main action used for redirecting Achievements theme actions that are not
 * permitted by the current_user.
 *
 * @since Achievements (3.0)
 */
function dpa_template_redirect() {
	do_action( 'dpa_template_redirect' );
}


/**
 * Theme Helpers
 */

/**
 * The main action used for executing code before the theme has been setup
 *
 * @since Achievements (3.0)
 */
function dpa_register_theme_packages() {
	do_action( 'dpa_register_theme_packages' );
}

/**
 * The main action used for executing code before the theme has been setup
 *
 * @since Achievements (3.0)
 */
function dpa_setup_theme() {
	do_action( 'dpa_setup_theme' );
}

/**
 * The main action used for executing code after the theme has been setup
 *
 * @since Achievements (3.0)
 */
function dpa_after_setup_theme() {
	do_action( 'dpa_after_setup_theme' );
}

/**
 * The main action used for handling theme-side POST requests
 *
 * @since Achievements (3.1)
 */
function dpa_post_request() {
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) || defined( 'DOING_AJAX' ) && DOING_AJAX || is_admin() )
		return;

	if ( empty( $_POST['dpa_action'] ) )
		return;

	do_action( 'dpa_post_request', $_POST['dpa_action'] );
}

/**
 * Filter the plugin locale and domain.
 *
 * @param string $locale Optional
 * @param string $domain Optionl
 * @since Achievements (3.0)
 */
function dpa_plugin_locale( $locale = '', $domain = '' ) {

	// Only filter the dpa text domain
	if ( achievements()->domain !== $domain )
		return $locale;

	return apply_filters( 'dpa_plugin_locale', $locale, $domain );
}


/**
 * Filters
 */

/**
 * Piggy back filter for WordPress' "request" filter
 *
 * @since Achievements (3.0)
 * @param array $query_vars Optional
 * @return array
 */
function dpa_request( $query_vars = array() ) {
	return apply_filters( 'dpa_request', $query_vars );
}

/**
 * The main filter used for theme compatibility and displaying custom Achievements theme files.
 *
 * @since Achievements (3.0)
 * @param string $template
 * @return string Template file to use
 */
function dpa_template_include( $template = '' ) {
	return apply_filters( 'dpa_template_include', $template );
}

/**
 * Generate Achievements-specific rewrite rules
 *
 * @since Achievements (3.0)
 * @param WP_Rewrite $wp_rewrite
 */
function dpa_generate_rewrite_rules( $wp_rewrite ) {
	do_action_ref_array( 'dpa_generate_rewrite_rules', array( &$wp_rewrite ) );
}
