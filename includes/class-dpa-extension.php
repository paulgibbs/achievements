<?php
/**
 * Base interface and class for adding support for your plugin to Achievements.
 *
 * To add support for your plugin to Achievements, you need to create a new
 * class derived from either {@link DPA_Extension} or {@link DPA_CPT_Extension}.
 *
 * Your class will need to contain some installation logic to check if your
 * actions already exist in the dpa_event taxonomy; if they haven't, you need
 * to add them. Check out the supported plugins that come bundled with
 * Achievements for examples of how to do this.
 *
 * In a function hooked to the 'dpa_ready' action, instantiate your class and
 * store it in the main achievements object, e.g.
 *
 * achievements()->extend->your_plugin = new Your_DPA_Extension_Class();
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
 * to store information about the plugin and actions that you are adding
 * support for.
 *
 * The objects that you store in achievements()->extends need to be derived
 * from this class.
 *
 * If the action which you are adding support for is a WordPress core custom
 * post type action, use {@link DPA_CPT_Extension} rather than this class.
 *
 * @since 3.0
 */
abstract class DPA_Extension {
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
	 *     'profile_url   => 'http://profiles.wordpress.org/DJPaul'
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
	 * Plugin slug
	 *
	 * A unique string representing your plugin. This is used for keying indexes
	 * and is also output on elements' class property in the wp-admin screens.
	 *
	 * @return string
	 * @since 3.0
	 */
	abstract public function get_slug();

	/**
	 * Absolute URL to your plugin on WordPress.org
	 *
	 * @return string
	 * @since 3.0
	 */
	abstract public function get_wporg_url();
}

/**
 * Adding support to Achievements for your plugin can be a little complicated if
 * the event is a built-in WordPress action for a post types. This abstract
 * class gives you a starting point to more easily add support for such plugins.
 * Otherwise, use {@link DPA_Extension}.
 *
 * @since 3.0
 */
abstract class DPA_CPT_Extension extends DPA_Extension {
	/**
	 * For actions that are in WordPress core and handle post types, update the
	 * user ID from the logged in user to the post author's ID (e.g. for draft
	 * posts which are then published by another user).
	 *
	 * In your class you need to add_filter this method to 'dpa_handle_event_user_id'.
	 * In your implementation you must check that $event_name matches the name of the
	 * action that your plugin implements.
	 *
	 * This method assumes that $func_args[0] is the Post object.
	 *
	 * @param int    $user_id    Logged in user's ID
	 * @param string $event_name Name of the event
	 * @param array  $func_args  Arguments that were passed to the action
	 * @return int New user ID
	 * @since 3.0
	 */
	public function modify_user_id_for_post( $user_id, $event_name, $func_args ) {
		$post = $func_args[0];

		if ( ! empty( $post->post_author ) )
			return (int) $post->post_author;
		else
			return $user_id;
	}
}