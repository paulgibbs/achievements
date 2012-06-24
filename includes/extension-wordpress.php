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
//	achievements()->extensions->wordpress = new DPA_WordPress_Extension;
}
add_action( 'dpa_ready', 'dpa_init_wordpress_extension' );

class DPA_WordPress_Extension extends DPA_CPT_Extension {
	/**
	 * Constructor
	 *
	 * Register actions to change the user ID for specific event type
	 *
	 * @since 3.0
	 */
	public function __construct() {
		add_action( 'dpa_handle_event_user_id', array( $this, 'user_id' ), 10, 3 );
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
	protected function user_id( $user_id, $action_name, $action_func_args ) {
		// Only deal with events added by this extension.
		if ( ! in_array( $action_name, array( 'comment_post', 'publish_post', ) ) )
			return $user_id;

		$new_user_id = $user_id;

		// New comment, check that the author isn't anonymous
		if ( 'comment_post' == $action_name ) {
			if ( ( ! $comment = get_comment( $action_func_args[0] ) ) || ! $comment->user_id )
				return $user_id;

			// Bail if comment isn't approved
			if ( 1 != $action_func_args[1]  )
				return false;

		// New post, get the post author
		} elseif ( 'publish_post' == $action_name ) {
			$new_user_id = $this->modify_user_id_for_post( $user_id, $action_name, $action_func_args  );
		}

		// Filter for 3rd party plugins
		return apply_filters( 'dpa_wordpress_extension_user_id', $new_user_id, $user_id, $action_name, $action_func_args );
	}

	/**
	 * Returns details of actions from this plugin that Achievements can use.
	 *
	 * @return array
	 * @since 3.0
	 */
	public function get_actions() {
		return array(
			'comment_post'    => __( 'The user writes a comment on a post or page.', 'dpa' ),
			'publish_post'    => __( 'The user publishes a blog post.', 'dpa' ),
			'signup_finished' => __( 'The user creates a new site (multi-site only).', 'dpa' ),
			'trashed_post'    => __( 'The user trashes a blog post.', 'dpa' ),
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