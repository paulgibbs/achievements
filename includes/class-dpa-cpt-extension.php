<?php
/**
 * Base class for adding support for your plugins to Achievements that will
 * implement post type actions.
 *
 * To add support for your plugin to Achievements, you need to create a new
 * class derived from either {@link DPA_Extension} or {@link DPA_CPT_Extension}.
 *
 * @package Achievements
 * @see {@link DPA_Extension}
 * @subpackage CoreClasses
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

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