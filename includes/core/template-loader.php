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
 * Listens to the 'template_include' filter and waits for any Achievements specific
 * template condition to be met. If one is met and the template file exists it will be used. 
 *
 * @param string $template Optional.
 * @return string The path to the template file that is being used
 * @see dpa_template_include_theme_compat()
 * @since Achievements (3.0)
 */
function dpa_template_include_theme_supports( $template = '' ) {
	// Single achievement
	if ( dpa_is_single_achievement() && ( $new_template = dpa_get_single_achievement_template() ) ) :

	// Achievement archive
	elseif ( dpa_is_achievement_archive() && ( $new_template = dpa_get_achievement_archive_template() ) ) :

	// User achievements page
	elseif ( dpa_is_single_user_achievements() && ( $new_template = dpa_get_single_user_achievements_template() ) ) :

	endif;

	// An Achievements template file was located, so override the WordPress template, and use it to switch off Achievements' theme compatibility.
	if ( ! empty( $new_template ) )
		$template = dpa_set_template_included( $new_template );

	return apply_filters( 'dpa_template_include_theme_supports', $template );
}

/**
 * Set the included template
 *
 * @param string|bool $template Template to load. Optional, defaults to false.
 * @return mixed False if empty. String of template name if template included.
 * @since Achievements (3.4)
 */
function dpa_set_template_included( $template = false ) {
	achievements()->theme_compat->achievements_template = $template;

	return achievements()->theme_compat->achievements_template;
}

/**
* Is an Achievements template being included?
*
* @return bool
* @since Achievements (3.4)
*/
function dpa_is_template_included() {
	return apply_filters( 'dpa_is_template_included', ! empty( achievements()->theme_compat->achievements_template ) );
}

/**
 * Attempt to load a custom Achievements functions file, similar to a theme's functions.php file.
 *
 * @global string $pagenow
 * @since Achievements (3.0)
 */
function dpa_load_theme_functions() {
	global $pagenow;

	// If Achievements is being deactivated, do not load any more files
	if ( dpa_is_deactivation() )
		return;

	if ( ! defined( 'WP_INSTALLING' ) || ( ! empty( $pagenow ) && ( 'wp-activate.php' !== $pagenow ) ) ) {
		dpa_locate_template( 'achievements-functions.php', true );

		if ( class_exists( 'DPA_Default' ) )
			achievements()->theme_functions = new DPA_Default();
	}
}


/**
 * Individual templates
 */

/**
 * Get the single achievement template
 *
 * @return string Path to template file
 * @since Achievements (3.0)
 */
function dpa_get_single_achievement_template() {
	$templates = array(
		'single-' . dpa_get_achievement_post_type() . '.php',  // Single achievement
	);

	return dpa_get_query_template( 'single_achievement', $templates );
}

/**
 * Get the achievement archive template
 *
 * @return string Path to template file
 * @since Achievements (3.0)
 */
function dpa_get_achievement_archive_template() {
	$templates = array(
		'archive-' . dpa_get_achievement_post_type() . '.php',  // Achievement archive
	);

	return dpa_get_query_template( 'achievement_archive', $templates );
}

/**
 * Get a single user's achievements template
 * 
 * @return string Path to template file
 * @since Achievements (3.0)
 */
function dpa_get_single_user_achievements_template() {
	$author = get_queried_object();

	$templates = array(
		"author-achievement-{$author->user_nicename}.php",
		"author-achievement-{$author->ID}.php",
		'author-achievement.php',
	);

	return dpa_get_query_template( 'single_user_achievements', $templates );
}

/**
 * Get the templates to use as the endpoint for Achievements template parts.
 *
 * The way this works is that we'll look in your theme for any of these files.
 * If we find one, we'll use it to build the output. For most themes this
 * will probably be page.php.
 *
 * The other options are there to let enterprising theme developers really make Achievements sing.
 *
 * @return string Path to template file
 * @since Achievements (3.0)
 */
function dpa_get_theme_compat_templates() {
	$templates = array(
		'plugin-achievements.php',  // https://core.trac.wordpress.org/ticket/20509
		'achievements.php',
		'generic.php',              // https://core.trac.wordpress.org/ticket/20509
		'page.php',
		'single.php',
		'index.php',
	);
	return dpa_get_query_template( 'achievements', $templates );
}