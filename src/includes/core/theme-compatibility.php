<?php

/**
 * Achievements Theme Compatibility
 *
 * What follows is an attempt at intercepting the natural page load process
 * to replace the_content() with the appropriate Achievements content.
 *
 * To do this, Achievements does several direct manipulations of global variables
 * and forces them to do what they are not supposed to be doing.
 *
 * Many Bothans died to bring us this information.	
 *
 * @package Achievements
 * @subpackage ThemeCompatibility
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Theme compatibility base class
 *
 * This is only intended to be extended and is included here as a basic guide for future Theme Packs to use.
 *
 * @since Achievements (3.0)
 */
class DPA_Theme_Compat {
	/**
	 * Consisting of arrays in this format:
	 *
	 * array(
	 *     'dir'     => Path to theme
	 *     'id'      => ID of the theme (should be unique)
	 *     'name'    => Name of the theme (should match style.css)
	 *     'url'     => URL to theme
	 *     'version' => Theme version for cache busting scripts and styling
	 * );
	 * @var array 
	 */
	private $_data = array();

	/**
	 * Pass the $properties to the object on creation.
	 *
	 * @param array $properties
	 * @since Achievements (3.0)
	 */
	public function __construct( array $properties = array() ) {
		$this->_data = $properties;
	}

	/**
	 * Set a theme's property.
	 *
	 * @param string $property
	 * @param mixed $value
	 * @return mixed
	 * @since Achievements (3.0)
	 */
	public function __set( $property, $value ) {
		return $this->_data[$property] = $value;
	}

	/**
	 * Get a theme's property.
	 *
	 * @param string $property
	 * @return mixed
	 * @since Achievements (3.0)
	 */
	public function __get( $property ) {
		return array_key_exists( $property, $this->_data ) ? $this->_data[$property] : '';
	}
}

/**
 * Setup the default theme compat theme
 *
 * @param string $theme Optional
 * @since Achievements (3.0)
 */
function dpa_setup_theme_compat( $theme = '' ) {
	// Make sure theme package is available, set to default if not
	if ( ! isset( achievements()->theme_compat->packages[$theme] ) || ! is_a( achievements()->theme_compat->packages[$theme], 'DPA_Theme_Compat' ) )
		$theme = 'default';

	// Set the active theme compat theme
	achievements()->theme_compat->theme = achievements()->theme_compat->packages[$theme];
}

/**
 * Gets the ID of the Achievements compatible theme used in the event of the
 * currently active WordPress theme not explicitly supporting Achievements.
 * This can be filtered or set manually. Tricky theme authors can override the
 * default and include their own Achievements compatibility layers for their themes.
 *
 * @return string
 * @since Achievements (3.0)
 */
function dpa_get_theme_compat_id() {
	return apply_filters( 'dpa_get_theme_compat_id', achievements()->theme_compat->theme->id );
}

/**
 * Gets the name of the Achievements compatible theme used in the event of the
 * currently active WordPress theme not explicitly supporting Achievements.
 * This can be filtered or set manually. Tricky theme authors can override the
 * default and include their own Achievements compatibility layers for their themes.
 *
 * @return string
 * @since Achievements (3.0)
 */
function dpa_get_theme_compat_name() {
	return apply_filters( 'dpa_get_theme_compat_name', achievements()->theme_compat->theme->name );
}

/**
 * Gets the version of the Achievements compatible theme used in the event of the
 * currently active WordPress theme not explicitly supporting Achievements.
 * This can be filtered or set manually. Tricky theme authors can override the
 * default and include their own Achievements compatibility layers for their themes.
 *
 * @return string
 * @since Achievements (3.0)
 */
function dpa_get_theme_compat_version() {
	return apply_filters( 'dpa_get_theme_compat_version', achievements()->theme_compat->theme->version );
}

/**
 * Gets the directory path to the Achievements compatible theme used in the event of the
 * currently active WordPress theme not explicitly supporting Achievements.
 * This can be filtered or set manually. Tricky theme authors can override the
 * default and include their own Achievements compatibility layers for their themes.
 *
 * @return string
 * @since Achievements (3.0)
 */
function dpa_get_theme_compat_dir() {
	return apply_filters( 'dpa_get_theme_compat_dir', achievements()->theme_compat->theme->dir );
}

/**
 * Gets the URL to the Achievements compatible theme used in the event of the
 * currently active WordPress theme not explicitly supporting Achievements.
 * This can be filtered or set manually. Tricky theme authors can override the
 * default and include their own Achievements compatibility layers for their themes.
 *
 * @return string
 * @since Achievements (3.0)
 */
function dpa_get_theme_compat_url() {
	return apply_filters( 'dpa_get_theme_compat_url', achievements()->theme_compat->theme->url );
}

/**
 * Gets true/false if page is currently inside theme compatibility
 *
 * @since Achievements (3.0)
 * @return bool
 */
function dpa_is_theme_compat_active() {
	if ( empty( achievements()->theme_compat->active ) )
		return false;

	return achievements()->theme_compat->active;
}

/**
 * Set if page is currently inside theme compatibility
 *
 * @since Achievements (3.0)
 * @param bool $set Optional. Defaults to true.
 */
function dpa_set_theme_compat_active( $set = true ) {
	achievements()->theme_compat->active = $set;
}

/**
 * Set the theme compat templates global
 *
 * Stash possible template files for the current query. Useful if plugins want
 * to override them or to see what files are being scanned for inclusion.
 *
 * @param array $templates Optional
 * @return array Returns $templates
 * @since Achievements (3.0)
 */
function dpa_set_theme_compat_templates( $templates = array() ) {
	achievements()->theme_compat->templates = $templates;

	return achievements()->theme_compat->templates;
}

/**
 * Set the theme compat template global
 *
 * Stash the template file for the current query. Useful if plugins want
 * to override it or see what file is being included.
 *
 * @param string $template Optional
 * @return string Returns $template
 * @since Achievements (3.0)
 */
function dpa_set_theme_compat_template( $template = '' ) {
	achievements()->theme_compat->template = $template;

	return achievements()->theme_compat->template;
}

/**
 * Set the theme compat original_template global
 *
 * Stash the original template file for the current query. Useful for checking
 * if Achievements was able to find a more appropriate template.
 *
 * @param string $template Optional
 * @return string Returns $template
 * @since Achievements (3.0)
 */
function dpa_set_theme_compat_original_template( $template = '' ) {
	achievements()->theme_compat->original_template = $template;

	return achievements()->theme_compat->original_template;
}

/**
 * Returns true if theme compatibility is using the original template for this page.
 * e.g. when we failed to find a more appropriate template.
 *
 * @param string $template Optional
 * @return bool
 * @since Achievements (3.0)
 */
function dpa_is_theme_compat_original_template( $template = '' ) {
	if ( empty( achievements()->theme_compat->original_template ) )
		return false;

	return achievements()->theme_compat->original_template === $template;
}

/**
 * Register a new Achievements theme package to the active theme packages array
 *
 * @param array|DPA_Theme_Compat $theme Optional. Accept an array to create a DPA_Theme_Compat object from, or an actual object.
 * @param bool $override Optional. Defaults to true. If false, and a package with the same ID is already registered, then don't override it.
 * @since Achievements (3.0)
 */
function dpa_register_theme_package( $theme = array(), $override = true ) {
	// Create new DPA_Theme_Compat object from the $theme argument
	if ( is_array( $theme ) )
		$theme = new DPA_Theme_Compat( $theme );

	// Bail if $theme isn't a proper object
	if ( ! is_a( $theme, 'DPA_Theme_Compat' ) )
		return;

	// Only override if the flag is set and not previously registered
	if ( empty( achievements()->theme_compat->packages[$theme->id] ) || true === $override ) {
		achievements()->theme_compat->packages[$theme->id] = $theme;
	}
}

/**
 * This fun little function fills up some WordPress globals with dummy data to
 * stop your average page template from complaining about it missing.
 *
 * @global WP_Query $wp_query
 * @global object $post
 * @param array $args Optional
 * @since Achievements (3.0)
 */
function dpa_theme_compat_reset_post( $args = array() ) {
	global $wp_query, $post;

	// Switch defaults if post is set
	if ( isset( $wp_query->post ) ) {
		$dummy = dpa_parse_args( $args, array(
			'comment_count'         => $wp_query->post->comment_count,
			'comment_status'        => $wp_query->post->comment_status,
			'filter'                => $wp_query->post->filter,
			'guid'                  => $wp_query->post->guid,
			'ID'                    => $wp_query->post->ID,
			'menu_order'            => $wp_query->post->menu_order,
			'pinged'                => $wp_query->post->pinged,
			'ping_status'           => $wp_query->post->ping_status,
			'post_author'           => $wp_query->post->post_author,
			'post_content'          => $wp_query->post->post_content,
			'post_content_filtered' => $wp_query->post->post_content_filtered,
			'post_date'             => $wp_query->post->post_date,
			'post_date_gmt'         => $wp_query->post->post_date_gmt,
			'post_excerpt'          => $wp_query->post->post_excerpt,
			'post_mime_type'        => $wp_query->post->post_mime_type,
			'post_modified'         => $wp_query->post->post_modified,
			'post_modified_gmt'     => $wp_query->post->post_modified_gmt,
			'post_name'             => $wp_query->post->post_name,
			'post_parent'           => $wp_query->post->post_parent,
			'post_password'         => $wp_query->post->post_password,
			'post_status'           => $wp_query->post->post_status,
			'post_title'            => $wp_query->post->post_title,
			'post_type'             => $wp_query->post->post_type,
			'to_ping'               => $wp_query->post->to_ping,

			'is_404'                => false,
			'is_archive'            => false,
			'is_page'               => false,
			'is_single'             => false,
			'is_tax'                => false,
		), 'theme_compat_reset_post' );

	} else {
		$dummy = dpa_parse_args( $args, array(
			'comment_count'         => 0,
			'comment_status'        => 'closed',
			'filter'                => 'raw',
			'ID'                    => -9999,
			'guid'                  => '',
			'menu_order'            => 0,
			'pinged'                => '',
			'ping_status'           => '',
			'post_author'           => 0,
			'post_content'          => '',
			'post_content_filtered' => '',
			'post_date'             => 0,
			'post_date_gmt'         => 0,
			'post_excerpt'          => '',
			'post_mime_type'        => '',
			'post_modified'         => 0,
			'post_modified_gmt'     => 0,
			'post_name'             => '',
			'post_parent'           => 0,
			'post_password'         => '',
			'post_status'           => 'publish',
			'post_title'            => '',
			'post_type'             => 'page',
			'to_ping'               => '',

			'is_404'                => false,
			'is_archive'            => false,
			'is_page'               => false,
			'is_single'             => false,
			'is_tax'                => false,
		), 'theme_compat_reset_post' );
	}

	// Bail if dummy post is empty
	if ( empty( $dummy ) )
		return;

	// Set the $post global
	$post = new WP_Post( (object) $dummy );

	// Copy the new post global into the main $wp_query
	$wp_query->post  = $post;
	$wp_query->posts = array( $post );

	// Prevent comments form from appearing
	$wp_query->post_count = 1;
	$wp_query->is_404     = $dummy['is_404'];
	$wp_query->is_page    = $dummy['is_page'];
	$wp_query->is_single  = $dummy['is_single'];
	$wp_query->is_archive = $dummy['is_archive'];
	$wp_query->is_tax     = $dummy['is_tax'];

	unset( $dummy );

	// If we are resetting a post, we are in theme compat
	dpa_set_theme_compat_active( true );
}

/**
 * Reset main query vars and filter 'the_content' to output an Achievements template part as needed.
 *
 * @param string $template Optional
 * @since Achievements (3.0)
 */
function dpa_template_include_theme_compat( $template = '' ) {

	// Bail if a root template was already found. This prevents unintended recursive filtering of 'the_content'.
	if ( dpa_is_template_included() )
		return $template;

	// Bail if shortcodes are unset somehow
	if ( ! is_a( achievements()->shortcodes, 'DPA_Shortcodes' ) )
		return $template;

	// Achievements archive
	if ( dpa_is_achievement_archive() ) {

		// Page exists where this archive should be
		$page = dpa_get_page_by_path( dpa_get_root_slug() );

		// Should we replace the content...
		if ( empty( $page->post_content ) ) {
			$new_content = achievements()->shortcodes->display_achievements_index(); 

		// ...or use the existing page content?
		} else {
			$new_content = apply_filters( 'the_content', $page->post_content );
		}

		// Should we replace the title...
		if ( empty( $page->post_title ) ) {
			$new_title = dpa_get_achievement_archive_title();

		// ...or use the existing page title?
		} else {
			$new_title = apply_filters( 'the_title', $page->post_title );
		}

		dpa_theme_compat_reset_post( array(
			'comment_status' => 'closed',
			'ID'             => ! empty( $page->ID ) ? $page->ID : 0,
			'is_archive'     => true,
			'post_author'    => 0,
			'post_content'   => $new_content,
			'post_date'      => 0,
			'post_status'    => 'publish',
			'post_title'     => $new_title,
			'post_type'      => dpa_get_achievement_post_type(),
		) );

	// Single Achievement
	} elseif ( dpa_is_single_achievement() ) {
		dpa_theme_compat_reset_post( array(
			'comment_status' => 'closed',
			'ID'             => dpa_get_achievement_id(),
			'is_single'      => true,
			'post_author'    => dpa_get_achievement_author_id(),
			'post_content'   => achievements()->shortcodes->display_achievement( array( 'id' => dpa_get_achievement_id() ) ),
			'post_date'      => 0,
			'post_status'    => 'publish',
			'post_title'     => dpa_get_achievement_title(),
			'post_type'      => dpa_get_achievement_post_type(),
		) );

	// Single user's achievements template
	} elseif ( dpa_is_single_user_achievements() ) {
		dpa_theme_compat_reset_post( array(
			'comment_status' => 'closed',
			'ID'             => 0,
			'is_archive'     => true,
			'post_author'    => 0,
			'post_content'   => achievements()->shortcodes->display_user_achievements(),
			'post_date'      => 0,
			'post_status'    => 'publish',
			'post_title'     => sprintf( _x( "%s's achievements", 'possesive noun', 'dpa' ), get_the_author_meta( 'display_name', dpa_get_displayed_user_id() ) ),
			'post_type'      => dpa_get_achievement_post_type(),
		) );
	}

	/**
	 * Bail if the template already matches an Achievements template. This includes
	 * archive-* and single-* WordPress post_type matches (allowing themes to use the
	 * expected format) as well as all other Achievements-specific template files.
	 */
	if ( dpa_is_template_included() ) {
		return $template;

	/**
	 * If we are relying on Achievements' built-in theme compatibility to load
	 * the proper content, we need to intercept the_content, replace the
	 * output, and display ours instead.
	 *
	 * To do this, we first remove all filters from 'the_content' and hook
	 * our own function into it, which runs a series of checks to determine
	 * the context, and then uses the built in shortcodes to output the
	 * correct results from inside an output buffer.
	 *
	 * Uses dpa_get_theme_compat_templates() to provide fall-backs that
	 * should be coded without superfluous mark-up and logic (prev/next
	 * navigation, comments, date/time, etc...)
	 * 
	 * Hook into the 'dpa_get_achievements_template' to override the array of
	 * possible templates, or 'dpa_achievements_template' to override the result.
	 */
	} elseif ( dpa_is_theme_compat_active() ) {
		dpa_remove_all_filters( 'the_content' );

		$template = dpa_get_theme_compat_templates();
	}

	return apply_filters( 'dpa_template_include_theme_compat', $template );
}


/**
 * Helpers
 */

/** 
 * Are we replacing the_content ?
 * 
 * @since Achievevements (3.4)
 * @return bool 
 */ 
function dpa_do_theme_compat() {
	$retval = ! dpa_is_template_included() && in_the_loop() && dpa_is_theme_compat_active();
	return apply_filters( 'dpa_do_theme_compat', (bool) $retval );
}

/**
 * Remove the canonical redirect to allow pretty pagination
 *
 * @global unknown $wp_rewrite
 * @param string $redirect_url Redirect url
 * @return bool|string False if it's an achievement archive or post, and on the first page of pagination, otherwise the redirect url.
 * @since Achievements (3.0)
 */
function dpa_redirect_canonical( $redirect_url ) {
	global $wp_rewrite;

	// Canonical is for the beautiful
	if ( $wp_rewrite->using_permalinks() ) {

		// If viewing beyond page 1 of several
		if ( 1 < dpa_get_paged() ) {

			// On a single achievement
			if ( dpa_is_single_achievement() ) {
				$redirect_url = false;

			// ...and any single anything else...
			} elseif ( is_page() || is_singular() ) {
				$redirect_url = false;
			}
		}
	}

	return $redirect_url;
}

/** Filters *******************************************************************/

/**
 * Removes all filters from a WordPress filter, and stashes them in achievements()
 * in the event they need to be restored later.
 *
 * @global array $merged_filters
 * @global WP_Filter $wp_filter
 * @param string $tag
 * @param int $priority Optional
 * @since Achievements (3.0)
 */
function dpa_remove_all_filters( $tag, $priority = 0 ) {
	global $merged_filters, $wp_filter;

	// Filters exist
	if ( isset( $wp_filter[$tag] ) ) {

		// Filters exist in this priority
		if ( ! empty( $priority ) && isset( $wp_filter[$tag][$priority] ) ) {

			// Store filters in a backup
			achievements()->filters->wp_filter[$tag][$priority] = $wp_filter[$tag][$priority];

			// Unset the filters
			unset( $wp_filter[$tag][$priority] );

		// Priority is empty
		} else {

			// Store filters in a backup
			achievements()->filters->wp_filter[$tag] = $wp_filter[$tag];

			// Unset the filters
			unset( $wp_filter[$tag] );
		}
	}

	// Check merged filters
	if ( isset( $merged_filters[$tag] ) ) {

		// Store filters in a backup
		achievements()->filters->merged_filters[$tag] = $merged_filters[$tag];

		// Unset the filters
		unset( $merged_filters[$tag] );
	}
}

/**
 * Restores filters from achievements() that were removed using dpa_remove_all_filters()
 *
 * @global array $merged_filters
 * @global WP_Filter $wp_filter
 * @param string $tag
 * @param int $priority
 * @since Achievements (3.0)
 */
function dpa_restore_all_filters( $tag, $priority = false ) {
	global $merged_filters, $wp_filter;

	// Filters exist
	if ( isset( achievements()->filters->wp_filter[$tag] ) ) {

		// Filters exist in this priority
		if ( ! empty( $priority ) && isset( achievements()->filters->wp_filter[$tag][$priority] ) ) {

			// Restore filter
			$wp_filter[$tag][$priority] = achievements()->filters->wp_filter[$tag][$priority];

			// Clear out our stash
			unset( achievements()->filters->wp_filter[$tag][$priority] );

		// Priority is empty
		} else {

			// Restore filter
			$wp_filter[$tag] = achievements()->filters->wp_filter[$tag];

			// Clear out our stash
			unset( achievements()->filters->wp_filter[$tag] );
		}
	}

	if ( isset( achievements()->filters->merged_filters[$tag] ) ) {

		// Restore filter
		$merged_filters[$tag] = achievements()->filters->merged_filters[$tag];

		// Clear out our stash
		unset( achievements()->filters->merged_filters[$tag] );
	}
}
