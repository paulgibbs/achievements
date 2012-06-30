<?php
/**
 * Extension for WordPress (core)
 *
 * This file extends Achievements to support actions from WordPress (core).
 *
 * @package Achievements
 * @subpackage ExtensionWordPress
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Extends Achievements to support actions from WordPress (core).
 *
 * @since 3.0
 */
function dpa_init_wordpress_extension() {
	achievements()->extensions->wordpress = new DPA_WordPress_Extension;
}
add_action( 'dpa_ready', 'dpa_init_wordpress_extension' );

class DPA_WordPress_Extension extends DPA_CPT_Extension {
	/**
	 * Constructor
	 *
	 * @since 3.0
	 */
	public function __construct() {
		add_action( 'dpa_handle_event_name',    array( $this, 'event_name' ),    10, 2 );
		add_action( 'dpa_handle_event_user_id', array( $this, 'event_user_id' ), 10, 3 );
	}

	/**
	 * A note about the "user publishes a post" event. The simple way would be to use the "publish post"
	 * action, but from that it's not efficent to find out if that post has already been published previously.
	 * Instead the WordPress extension uses "draft_to_publish", but there are other x_to_publish actions
	 * that might be used when a post is published (e.g. "pending_to_publish").
	 *
	 * If we added those in the WordPress extension we'd end up with four/five different types of
	 * "publish a post" events. That's a very poor user experience. Instead we manually add those other
	 * actions to the events stack in dpa_register_events() and use some trickery on the dpa_handle_event_name
	 * filter to change the action name to draft_to_publish when one of these actions are being processed.
	 *
	 * @param string $name Action name
	 * @param array $func_args Optional; action's arguments, from func_get_args().
	 * @return string|bool Action name or false to skip any further processing
	 * @see dpa_register_events()
	 * @since 3.0
	 */
	function event_name( $event_name, $func_args ) {
		// Check we're dealing with the right type of event
		if ( ! in_array( $event_name, array( 'future_to_publish', 'pending_to_publish', 'private_to_publish', ) ) ) {
			return $event_name;

		// Only switch the event name for Posts
		} elseif ( 'post' == $func_args[0]->post_type ) {
			return 'draft_to_publish';

		} else {
			return $event_name;
		}
	}

	/**
	 * For the comment_post and publish_post events, swap the logged in user's ID
	 * for the post's author's ID. This is to support post moderation and publishing
	 * by other users.
	 *
	 * @param int $user_id
	 * @param string $action_name
	 * @param array $action_func_args The action's arguments from func_get_args().
	 * @return int|false New user ID or false to skip any further processing
	 * @since 3.0
	 */
	protected function event_user_id( $user_id, $action_name, $action_func_args ) {
		// Only deal with events added by this extension.
		if ( ! in_array( $action_name, array( 'comment_post', 'draft_to_publish', ) ) )
			return $user_id;

		$new_user_id = $user_id;

		// New comment, check that the author isn't anonymous
		if ( 'comment_post' == $action_name ) {
			if ( ( ! $comment = get_comment( $action_func_args[0] ) ) || ! $comment->user_id )
				return $user_id;

			// Bail if comment isn't approved
			if ( 1 != $action_func_args[1]  )
				return false;

			// Return comment author ID
			return $comment->user_id;

		// New post, get the post author
		} elseif ( 'draft_to_publish' == $action_name && 'post' == $action_func_args[0]->post_type ) {
			return $this->get_post_author( $user_id, $action_name, $action_func_args );
		}
	}

	/**
	 * Returns details of actions from this plugin that Achievements can use.
	 *
	 * @return array
	 * @since 3.0
	 */
	public function get_actions() {
		return array(
			'comment_post'     => __( 'The user writes a comment.', 'dpa' ),
			'draft_to_publish' => __( 'The user publishes a blog post.', 'dpa' ),
			'signup_finished'  => __( 'The user creates a new site (multi-site only).', 'dpa' ),
			'trashed_post'     => __( 'The user trashes a blog post.', 'dpa' ),
		);
	}

	/**
	 * Returns nested array of key/value pairs for each contributor to this plugin (name, gravatar URL, profile URL).
	 *
	 * @return array
	 * @since 3.0
	 * @todo WordPress has far too many people who deserve to be listed here. Maybe link to wp-admin/credits.php?
	 */
	public function get_contributors() {
		return array();
	}

	/**
	 * Plugin description
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_description() {
		return __( 'WordPress started in 2003 with a single bit of code to enhance the typography of everyday writing and with fewer users than you can count on your fingers and toes. Since then it has grown to be the largest self-hosted blogging tool in the world, used on millions of sites and seen by tens of millions of people every day.', 'dpa' );
	}

	/**
	 * Absolute URL to plugin image.
	 *
	 * @return string
	 * @since 3.0
	 * @todo Add WordPress logo image
	 */
	public function get_image_url() {
		return 'http://placekitten.com/772/250';
	}

	/**
	 * Plugin name
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_name() {
		return __( 'WordPress', 'dpa' );
	}

	/**
	 * Absolute URL to a news RSS feed for this plugin. This may be your own website.
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_rss_url() {
		return 'http://wordpress.org/news/feed/';
	}

	/**
	 * Plugin identifier
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_id() {
		return 'WordPress';
	}

	/**
	 * Version number of your extension
	 *
	 * @return int
	 * @since 3.0
	 */
	public function get_version() {
		return 1;
	}

	/**
	 * Absolute URL to your plugin on WordPress.org
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_wporg_url() {
		return 'http://wordpress.org/about/';
	}
}