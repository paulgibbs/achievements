<?php

/**
 * Achievements template loader
 *
 * @package Achievements
 * @subpackage TemplateLoader
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Possibly intercept the template being loaded
 *
 * Listens to the 'template_include' filter and waits for any Achieveemtns. specific
 * template condition to be met. If one is met and the template file exists it will be used. 
 *
 * @param string $template Optional.
 * @return string The path to the template file that is being used
 * @see dpa_template_include_theme_compat()
 * @since 3.0
 */
function dpa_template_include_theme_supports( $template = '' ) {
	// Single achievement
	if ( dpa_is_single_achievement() && ( $new_template = dpa_get_single_achievement_template() ) ) :

	// Achievement archive
	elseif ( dpa_is_achievement_archive() && ( $new_template = dpa_get_achievement_archive_template() ) ) :
	endif;

	// If template file exists
	if ( ! empty( $new_template ) ) {
		// Override the WordPress template with an Achievements template
		$template = $new_template;

		// See dpa_template_include_theme_compat()
		achievements()->theme_compat->achievements_template = true;
	}

	return apply_filters( 'dpa_template_include_theme_supports', $template );
}

/**
 * Attempt to load a custom bbPress functions file, similar to a theme's functions.php file.
 *
 * @global string $pagenow
 * @since 3.0
 */
function dpa_load_theme_functions() {
	global $pagenow;

	if ( ! defined( 'WP_INSTALLING' ) || ( ! empty( $pagenow ) && ( 'wp-activate.php' !== $pagenow ) ) )
		dpa_locate_template( 'achievements-functions.php', true );
}


/**
 * Individual templates
 */

/**
 * Get the single forum template
 *
 * @return string Path to template file
 * @since 3.0
 */
function dpa_get_single_achievement_template() {
	$templates = array(
		'single-' . dpa_get_achievement_post_type() . '.php',  // Single achievement
	);

	return dpa_get_query_template( 'single_achievement', $templates );
}

/**
 * Get the forum archive template
 *
 * @return string Path to template file
 * @since 3.0
 */
function dpa_get_achievement_archive_template() {
	$templates = array(
		'archive-' . dpa_get_achievement_post_type() . '.php',  // Achievement archive
	);

	return dpa_get_query_template( 'achievement_archive', $templates );
}

/**
 * Get the templates to use as the endpoint for Achievements template parts
 *
 * @return string Path to template file
 * @since 3.0
 */
function dpa_get_theme_compat_templates() {
	$templates = array(
		'plugin-achievements.php',
		'achievements.php',
		'achievement.php',
		'generic.php',
		'page.php',
		'single.php',
		'index.php',
	);
	return dpa_get_query_template( 'achievements', $templates );
}