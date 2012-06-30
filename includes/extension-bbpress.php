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
		//add_action( 'dpa_handle_event_user_id', array( $this, 'event_user_id' ), 10, 3 );
	}

	/**
	 * For some actions from bbPress, get the user ID from the function arguments.
	 *
	 * @param int $user_id
	 * @param string $action_name
	 * @param array $action_func_args The action's arguments from func_get_args().
	 * @return int|false New user ID or false to skip any further processing
	 * @since 3.0
	 */
	protected function event_user_id( $user_id, $action_name, $action_func_args ) {
		// Only deal with events added by this extension.
		if ( ! in_array( $action_name, array( '', '', ) ) )
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
			'bbp_deleted_forum'    => __( 'A forum is permanently deleted by the user', 'dpa' ),
			'bbp_edit_forum'       => __( "A forum's settings are changed by the user", 'dpa' ),
			'bbp_new_forum'        => __( 'The user creates a new forum', 'dpa' ),
			'bbp_trashed_forum'    => __( 'The user puts a forum into the trash', 'dpa' ),
			'bbp_untrashed_forum'  => __( 'The user restores a forum from the trash', 'dpa' ),

			// Topic management
			'bbp_merged_topic'     => __( 'Separate topics are merged together by a user', 'dpa' ),
			'bbp_post_split_topic' => __( 'An existing topic is split into seperate threads by a user', 'dpa' ),

			// Topic
			'bbp_deleted_topic'   => __( 'The user permanently deletes a topic', 'dpa' ),
			'bbp_sticked_topic'   => __( 'The user marks a topic as a sticky', 'dpa' ),
			'bbp_trashed_topic'   => __( 'The user trashes a topic', 'dpa' ),
			'bbp_unsticked_topic' => __( 'The user unstickies a topic', 'dpa' ),
			'bbp_untrashed_topic' => __( 'The user restores a topic from the trash', 'dpa' ),

			// Reply
			'bbp_deleted_reply'   => __( 'The user permanently deletes a reply', 'dpa' ),
			'bbp_trashed_reply'   => __( 'The user trashes a reply', 'dpa' ),
			'bbp_untrashed_reply' => __( 'The user restores a reply from the trash', 'dpa' ),
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