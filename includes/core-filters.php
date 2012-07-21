<?php
/**
 * Core filters
 *
 * This file contains the filters that are used throughout Achievements. They are
 * consolidated here to make searching for them easier, and to help developers
 * understand at a glance the order in which things occur.
 *
 * @package Achievements
 * @subpackage CoreFilters
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
add_filter( 'request',                 'dpa_request',            10    );
add_filter( 'template_include',        'dpa_template_include',   10    );
add_filter( 'wp_title',                'dpa_title',              10, 3 );
add_filter( 'body_class',              'dpa_body_class',         10, 2 );
add_filter( 'map_meta_cap',            'dpa_map_meta_caps',      10, 4 );
add_filter( 'allowed_themes',          'dpa_allowed_themes',     10    );
add_filter( 'redirect_canonical',      'dpa_redirect_canonical', 10    );
add_filter( 'login_redirect',          'dpa_redirect_login',     2,  3 );
add_filter( 'logout_url',              'dpa_logout_url',         2,  2 );

// Add post_parent__in to posts_where
add_filter( 'posts_where', 'dpa_query_post_parent__in', 10, 2 );

/**
 * Template Compatibility
 *
 * If you want to completely bypass this and manage your own custom Achievements
 * template hierarchy, start here by removing this filter and then look at how
 * dpa_template_include() works and do something similar. :)
 */
add_filter( 'dpa_template_include', 'dpa_template_include_theme_supports', 2, 1 );
add_filter( 'dpa_template_include', 'dpa_template_include_theme_compat',   4, 2 );

// Filter template locations
add_filter( 'dpa_get_template_part', 'dpa_add_template_locations' );


/**
 * Functions
 */

/**
 * Piggy back filter for WordPress' "request" filter
 *
 * @since 3.0
 * @param array $query_vars Optional
 * @return array
 */
function dpa_request( $query_vars = array() ) {
	return apply_filters( 'dpa_request', $query_vars );
}

/**
 * The main filter used for theme compatibility and displaying custom Achievements theme files.
 *
 * @since 3.0
 * @param string $template
 * @return string Template file to use
 */
function dpa_template_include( $template = '' ) {
	return apply_filters( 'dpa_template_include', $template );
}

/**
 * Generate Achievements-specific rewrite rules
 *
 * @since 3.0
 * @param WP_Rewrite $wp_rewrite
 */
function dpa_generate_rewrite_rules( $wp_rewrite ) {
	do_action_ref_array( 'dpa_generate_rewrite_rules', array( &$wp_rewrite ) );
}

/**
 * Filter the allowed themes list for Achievements-specific themes
 *
 * @since 3.0
 */
function dpa_allowed_themes( $themes ) {
	return apply_filters( 'dpa_allowed_themes', $themes );
}