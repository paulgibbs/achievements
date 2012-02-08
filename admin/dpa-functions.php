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
 * ['plugin_ID']->image->large          URL to plugin image (large size).
 *
 * Properties marked with an asterisk are auto-updated periodically from wporg.
 *
 * @return array See function description for structure of returned array
 * @since 1.0
 * @todo Figure out how to handle 3rd party plugins adding their own support for Achievements.
 */
function dpa_get_supported_plugins() {
	$plugins = array();

	// This is just for ease of development
	for ( $i = 0; $i < 20; $i++ ) {
		$plugin               = new stdClass;
		$plugin->name         = 'Plugin ' . $i;
		$plugin->description  = 'This is a description of plugin number ' . $i . '. It might be pretty awesome.';
		$plugin->wporg_url    = 'http://wordpress.org/extend/plugins/buddypress/';
		$plugin->image->large = 'http://placehold.it/772x250';

		$plugins[] = $plugin;
		unset( $plugin );
	}

	// @todo Implement wporg auto-update. Cache as transient (72hrs?).

	return apply_filters( 'dpa_get_supported_plugins', $plugins );
}
?>