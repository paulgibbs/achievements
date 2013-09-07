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
 * @since Achievements (3.0)
 */
function dpa_init_wordpress_extension() {
	achievements()->extensions->wordpress = new DPA_WordPress_Extension;

	// Tell the world that the WordPress extension is ready
	do_action( 'dpa_init_wordpress_extension' );
}
add_action( 'dpa_ready', 'dpa_init_wordpress_extension' );

/**
 * Extension to add WordPress support to Achievements
 *
 * @since Achievements (3.0)
 */
class DPA_WordPress_Extension extends DPA_CPT_Extension {
	/**
	 * Constructor
	 *
	 * Sets up extension properties. See class phpdoc for details.
	 *
	 * @since Achievements (3.0)
	 */
	public function __construct() {
		$this->actions = array(
			'comment_post'               => __( 'A comment is written by the user.', 'dpa' ),
			'wordpress_draft_to_publish' => __( 'The user publishes a blog post.', 'dpa' ),
			'signup_finished'            => __( 'A new site is created by the user (multi-site only).', 'dpa' ),
			'trashed_post'               => __( 'The user trashes a blog post.', 'dpa' ),
			'user_register'              => __( 'A new user creates an account on your website.', 'dpa' ),
		);

		$this->generic_cpt_actions = array(
			'draft_to_publish',
			'future_to_publish',
			'new_to_publish',
			'pending_to_publish',
			'private_to_publish',
		);

		$this->contributors = array(
			array(
				'name'         => 'WordPress',
				'gravatar_url' => 'http://s.wordpress.org/about/images/logos/wordpress-logo-notext-rgb.png',
				'profile_url'  => network_admin_url( 'credits.php' ),
			),
		);

		$this->description     = __( 'WordPress started in 2003 with a single bit of code to enhance the typography of everyday writing and with fewer users than you can count on your fingers and toes. Since then it has grown to be the largest self-hosted blogging tool in the world, used on millions of sites and seen by tens of millions of people every day.', 'dpa' );
		$this->id              = 'wordpress';
		$this->image_url       = trailingslashit( achievements()->includes_url ) . 'admin/images/wordpress.jpg';
		$this->name            = __( 'WordPress', 'dpa' );
		$this->rss_url         = 'http://wordpress.org/news/feed/';
		$this->small_image_url = trailingslashit( achievements()->includes_url ) . 'admin/images/wordpress-small.jpg';
		$this->version         = 2;
		$this->wporg_url       = 'http://wordpress.org/about/';

		add_filter( 'dpa_filter_events',        array( $this, 'get_generic_cpt_actions' ), 1,  1 );
		add_filter( 'dpa_handle_event_name',    array( $this, 'event_name'              ), 10, 2 );
		add_filter( 'dpa_handle_event_user_id', array( $this, 'event_user_id'           ), 10, 3 );
	}

	/**
	 * Filters the event name which is currently being processed
	 *
	 * @param string $event_name Action name
	 * @param array $func_args Optional; action's arguments, from func_get_args().
	 * @return string|bool Action name or false to skip any further processing
	 * @since Achievements (3.0)
	 */
	function event_name( $event_name, $func_args ) {
		// Check we're dealing with the right type of event
		if ( ! in_array( $event_name, $this->generic_cpt_actions ) )
			return $event_name;

		// Only switch the event name for Posts
		if ( 'post' === $func_args[0]->post_type )
			return 'wordpress_draft_to_publish';

		// The event is a generic post type action which isn't handled by this extension. Bail out.
		else
			return $event_name;
	}

	/**
	 * For the comment_post and post publish events, swap the logged in user's ID
	 * for the post's author's ID. This is to support post moderation and publishing
	 * by other users.
	 *
	 * @param int $user_id
	 * @param string $action_name
	 * @param array $action_func_args The action's arguments from func_get_args().
	 * @return int|false New user ID or false to skip any further processing
	 * @since Achievements (3.0)
	 */
	public function event_user_id( $user_id, $action_name, $action_func_args ) {
		// Only deal with events added by this extension.
		if ( ! in_array( $action_name, array( 'comment_post', 'user_register', 'wordpress_draft_to_publish', ) ) )
			return $user_id;

		// New comment, check that the author isn't anonymous
		if ( 'comment_post' === $action_name ) {
			if ( ( ! $comment = get_comment( $action_func_args[0] ) ) || ! $comment->user_id )
				return $user_id;

			// Bail if comment isn't approved
			if ( 1 !== (int) $action_func_args[1]  )
				return false;

			// Return comment author ID
			return $comment->user_id;

		// New post, get the post author
		} elseif ( 'wordpress_draft_to_publish' === $action_name && 'post' === $action_func_args[0]->post_type ) {
			return $this->get_post_author( $user_id, $action_name, $action_func_args );

		// New user registration
		} elseif ( 'user_register' === $action_name ) {
			return $action_func_args[0];
		}
	}

	/**
	 * Update routine for this extension.
	 * 
	 * A future version of Achievements will likely handle extension updates automagically.
	 *
	 * @param string $current_version
	 * @since Achievements (3.4)
	 */
	public function do_update( $current_version ) {
		// Upgrading to v2 -- "user_register" is new. Add the action to the dpa_event taxonomy.
		if ( $current_version === 1 ) {
			wp_insert_term( 'user_register', dpa_get_event_tax_id(), array( 'description' => $this->actions['user_register'] ) );
		}
	}
}