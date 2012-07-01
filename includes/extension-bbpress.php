<?php
/**
 * Extension for bbPress
 *
 * This file extends Achievements to support actions from bbPress.
 *
 * @package Achievements
 * @subpackage ExtensionbbPress
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Extends Achievements to support actions from bbPress
 *
 * @since 3.0
 */
function dpa_init_bbpress_extension() {
	achievements()->extensions->bbpress = new DPA_bbPress_Extension;

	// Run an action for other plugins
	do_action( 'dpa_init_bbpress_extension' );
}
add_action( 'dpa_ready', 'dpa_init_bbpress_extension' );

/**
 * Extension to add bbPress support to Achievements
 *
 * @since 3.0
 */
class DPA_bbPress_Extension extends DPA_CPT_Extension {
	/**
	 * Constructor
	 *
	 * @since 3.0
	 */
	public function __construct() {
		parent::__construct();

		// Filter the user ID
		add_filter( 'dpa_handle_event_user_id', array( $this, 'event_user_id' ),   10, 3 );
	}

	/**
	 * Add generic post type actions to the list of events that Achievements will listen for.
	 *
	 * @param array $events
 	 * @since 3.0
 	 */
 	public function get_generic_cpt_actions( $events ) {
 		$more_events = array(
 			// Public
 			'draft_to_publish',
 			'future_to_publish',
 			'pending_to_publish',
 			'private_to_publish',
 			'spam_to_publish',
 		);

 		return array_merge( $events, $more_events );
	}

	/**
	 * Filters the event name which is currently being processed
	 *
	 * @param string $name Action name
	 * @param array $func_args Optional; action's arguments, from func_get_args().
	 * @return string|bool Action name or false to skip any further processing
	 * @since 3.0
	 */
	function event_name( $event_name, $func_args ) {
		// Check we're dealing with the right type of event
		if ( ! in_array( $event_name, array(
 			'draft_to_publish',
 			'future_to_publish',
 			'pending_to_publish',
 			'private_to_publish',
 			'spam_to_publish',
		) ) )
			return $event_name;

		// Switch the event name for Replies
		if ( 'reply' == $func_args[0]->post_type )
			return 'bbpress_reply_draft_to_publish';

		// Switch the event name for Topics
		elseif ( 'topic' == $func_args[0]->post_type )
			return 'bbpress_topic_draft_to_publish';

		// The event is a generic post type action which isn't handled by this extension. Bail out.
		else
			return $event_name;
	}

	/**
	 * For some actions from bbPress, get the user ID from the Post's author.
	 *
	 * @param int $user_id
	 * @param string $action_name
	 * @param array $action_func_args The action's arguments from func_get_args().
	 * @return int|false New user ID or false to skip any further processing
	 * @since 3.0
	 */
	public function event_user_id( $user_id, $action_name, $action_func_args ) {
		// Only deal with events added by this extension.
		if ( ! in_array( $action_name, array( 'bbpress_reply_draft_to_publish', 'bbpress_topic_draft_to_publish', ) ) )
			return $user_id;

		// New Reply or Topic, get the post author
		if ( in_array( $action_func_args[0]->post_type, array( 'reply', 'topic', ) ) )
			return $this->get_post_author( $user_id, $action_name, $action_func_args );
		else
			return $user_id;
	}

	/**
	 * Returns details of actions from this plugin that Achievements can use.
	 *
	 * @return array
	 * @since 3.0
	 */
	public function get_actions() {
		return array(
			// Forum
			'bbp_deleted_forum'   => __( 'A forum is permanently deleted by the user', 'dpa' ),
			'bbp_edit_forum'      => __( "A forum's settings are changed by the user", 'dpa' ),
			'bbp_new_forum'       => __( 'The user creates a new forum', 'dpa' ),
			'bbp_trashed_forum'   => __( 'The user puts a forum into the trash', 'dpa' ),
			'bbp_untrashed_forum' => __( 'The user restores a forum from the trash', 'dpa' ),

			// Topic management
			'bbp_closed_topic'     => __( 'The user closes a topic.', 'dpa' ),
			'bbp_merged_topic'     => __( 'Separate topics are merged together by a user', 'dpa' ),
			'bbp_opened_topic'     => __( 'The user opens a topic for new replies', 'dpa' ),
			'bbp_post_split_topic' => __( 'An existing topic is split into seperate threads by a user', 'dpa' ),

			// Topic
			'bbp_deleted_topic'              => __( 'The user permanently deletes a topic', 'dpa' ),
			'bbp_sticked_topic'              => __( 'The user marks a topic as a sticky', 'dpa' ),
			'bbp_trashed_topic'              => __( 'The user trashes a topic', 'dpa' ),
			'bbp_unsticked_topic'            => __( 'The user unstickies a topic', 'dpa' ),
			'bbp_untrashed_topic'            => __( 'The user restores a topic from the trash', 'dpa' ),
			'bbpress_topic_draft_to_publish' => __( 'The user creates a new topic.', 'dpa' ),

			// Reply
			'bbp_deleted_reply'              => __( 'The user permanently deletes a reply', 'dpa' ),
			'bbp_trashed_reply'              => __( 'The user trashes a reply', 'dpa' ),
			'bbp_untrashed_reply'            => __( 'The user restores a reply from the trash', 'dpa' ),
			'bbpress_reply_draft_to_publish' => __( 'The user replies to a topic.', 'dpa' ),
		);
	}

	/**
	 * Returns nested array of key/value pairs for each contributor to this plugin (name, gravatar URL, profile URL).
	 *
	 * @return array
	 * @since 3.0
	 */
	public function get_contributors() {
		return array(
			array(
				'name'         => 'Matt',
				'gravatar_url' => 'http://www.gravatar.com/avatar/767fc9c115a1b989744c755db47feb60',
				'profile_url'  => 'http://profiles.wordpress.org/matt/',
			),
			array(
				'name'         => 'John James Jacoby',
				'gravatar_url' => 'http://www.gravatar.com/avatar/81ec16063d89b162d55efe72165c105f',
				'profile_url'  => 'http://profiles.wordpress.org/johnjamesjacoby/',
			),
		);
	}

	/**
	 * Plugin description
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_description() {
		return __( 'bbPress is forum software with a twist from the creators of WordPress.', 'dpa' );
	}

	/**
	 * Absolute URL to plugin image.
	 *
	 * @return string
	 * @since 3.0
	 * @todo Add bbPress logo image
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
		return __( 'bbPress', 'dpa' );
	}

	/**
	 * Absolute URL to a news RSS feed for this plugin. This may be your own website.
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_rss_url() {
		return 'http://bbpress.org/blog/feed/';
	}

	/**
	 * Plugin identifier
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_id() {
		return 'bbPress';
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
		return 'http://wordpress.org/extend/plugins/bbpress/';
	}
}