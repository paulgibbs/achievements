<?php
/**
 * Base class for adding support for your plugin to Achievements.
 *
 * To add support for your plugin to Achievements, you need to create a new
 * class derived from either {@link DPA_Extension} or {@link DPA_CPT_Extension}.
 *
 * In a function hooked to the 'dpa_ready' action, instantiate your class and
 * store it in the main achievements object, e.g.
 *
 * achievements()->extensions->your_plugin = new Your_DPA_Extension_Class();
 *
 * We need to add the actions you are supporting into the dpa_event taxonomy.
 * Achievements will take care of initial "installation" for you, so you'll only
 * need to implement any update logic in your {@link DPA_Extension::do_update}
 * method as/when required.
 *
 * That's all. Achievements takes care of everything else.
 *
 * @package Achievements
 * @subpackage CoreClasses
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add support to Achievements for your plugin using this class. It's used to
 * store information about the plugin and actions that you are adding support for.
 *
 * The objects that you store in achievements()->extensions need to be derived
 * from this class.
 *
 * If the action which you are adding support for is a WordPress core custom
 * post type action, use {@link DPA_CPT_Extension} rather than this class.
 *
 * @since 3.0
 */
abstract class DPA_Extension {
	/**
	 * Implement an update routine for your extension.
	 *
	 * Achievements adds your actions into the dpa_event taxonomy if it has no
	 * record of it in the "_dpa_extension_versions" site option. If the option already
	 * has a version number recorded, Achievements compares that to the value from
	 * {@link self::get_version}. If the extension reports a higher version number,
	 * then this method will be called.
	 *
	 * @since 3.0
	 */
	public function do_update( $current_version ) {
	}

	/**
	 * Returns details of actions from this plugin that Achievements can use.
	 * Note that you still have to add these into the dpa_event taxonomy yourself.
	 *
	 * You should return an array with these key/value pairs:
	 *
	 * array(
	 *   'action_name' => 'description',
	 *
	 *   // For example
	 *   'publish_post' => __( 'The user publishes a post or page.', 'your_plugin' ),
	 *   'trashed_post' => __( 'The user trashes a post or page.',   'your_plugin' ),
	 * )
	 *
	 * @return array
	 * @since 3.0
	 */
	abstract public function get_actions();

	/**
	 * Returns nested array of key/value pairs for each contributor to this plugin (name, gravatar URL, profile URL).
	 *
	 * You should return an array with these key/value pairs:
	 *
	 * array(
	 *   array(
	 *     'name'         => '',
	 *     'gravatar_url' => '',
	 *     'profile_url'  => '',
	 *  ),
	 *
	 *   // For example
	 *   array(
	 *     'name'         => 'Paul Gibbs',
	 *     'gravatar_url' => 'http://www.gravatar.com/avatar/3bc9ab796299d67ce83dceb9554f75df',
	 *     'profile_url'  => 'http://profiles.wordpress.org/DJPaul'
	 *   ),
	 * )
	 *
	 * @return array
	 * @since 3.0
	 */
	abstract public function get_contributors();

	/**
	 * Plugin description
	 *
	 * @return string
	 * @since 3.0
	 */
	abstract public function get_description();

	/**
	 * Absolute URL to plugin image.
	 *
	 * MUST be local (e.g. on user's own site, rather than linking to your own site).
	 *
	 * @return string
	 * @since 3.0
	 */
	abstract public function get_image_url();

	/**
	 * Plugin name
	 *
	 * @return string
	 * @since 3.0
	 */
	abstract public function get_name();

	/**
	 * Absolute URL to a news RSS feed for this plugin. This may be your own website.
	 *
	 * @return string
	 * @since 3.0
	 */
	abstract public function get_rss_url();

	/**
	 * Plugin identifier
	 *
	 * A unique string representing your plugin. This is used for keying indexes
	 * and is also output on elements' class property in the templates/
	 *
	 * @return string
	 * @since 3.0
	 */
	abstract public function get_id();

	/**
	 * Version number
	 *
	 * Return an integer representing the version of your extension. This is used
	 * internally to detect if we need to run any installation or update routine
	 * for your plugin. For example, you might add a new action to support in a
	 * second version of your extension.
	 *
	 * The implementation of any updating handling is down to you.
	 *
	 * @return int
	 * @since 3.0
	 */
	abstract public function get_version();

	/**
	 * Absolute URL to your plugin on WordPress.org
	 *
	 * @return string
	 * @since 3.0
	 */
	abstract public function get_wporg_url();
}