<?php

/**
 * Base interface and class for adding support for your plugin to Achievements.
 *
 * @package Achievements
 * @subpackage CoreClasses
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The objects that you store in achievements()->extends->your_plugin needs to
 * implement this interface.
 *
 * @since 3.0
 */
interface DPA_Plugin_Interface {
	/**
	 * Returns array of key/value pairs for each contributor to this plugin (user name, gravatar URL, profile URL)
	 *
	 * @return array e.g. array[0] = array( 'username' => 'Paul Gibbs', 'gravatar' => 'http://www.gravatar.com/avatar/3bc9ab796299d67ce83dceb9554f75df', 'profile' => 'http://profiles.wordpress.org/DJPaul' )
	 * @since 3.0
	 */
	public function get_contributors();

	/**
	 * Plugin description
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_description();

	/**
	 * URL to plugin image.
	 *
	 * SHOULD be local (e.g. on user's own site, rather than linking to your own site).
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_image_url();

	/**
	 * Plugin name
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_name();

	/**
	 * URL to a news RSS feed for this plugin. This may be the author's own website.
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_rss_url();

	/**
	 * Plugin slug
	 *
	 * A unique string representing your plugin. This is used for keying indexes
	 * and is also output on elements' class property in the wp-admin screens.
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_slug();

	/**
	 * URL to your plugin on WordPress.org
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_wporg_url();
}
