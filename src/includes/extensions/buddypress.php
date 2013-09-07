<?php
/**
 * Extension for BuddyPress
 *
 * This file extends Achievements to support actions from BuddyPress
 *
 * @package Achievements
 * @subpackage ExtensionBuddyPress
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Extends Achievements to support actions from BuddyPress.
 *
 * @since Achievements (3.0)
 */
function dpa_init_buddypress_extension() {
	achievements()->extensions->buddypress = new DPA_BuddyPress_Extension;

	// Tell the world that the BuddyPress extension is ready
	do_action( 'dpa_init_buddypress_extension' );
}
add_action( 'dpa_ready', 'dpa_init_buddypress_extension' );

/**
 * Extension to add BuddyPress support to Achievements
 *
 * @since Achievements (3.0)
 */
class DPA_BuddyPress_Extension extends DPA_Extension {

	/**
	 * Constructor
	 *
	 * Sets up extension properties. See class phpdoc for details.
	 *
	 * @since Achievements (3.0)
	 */
	public function __construct() {

		$this->actions = array(
			'bp_activity_add_user_favorite'    => __( 'The user marks an item in an activity stream as a favourite.', 'dpa' ),
			'bp_activity_comment_posted'       => __( 'The user replies to an item in an activity stream.', 'dpa' ),
			'bp_activity_posted_update'        => __( 'The user writes an activity update message.', 'dpa' ),
			'bp_activity_remove_user_favorite' => __( 'The user un-favourites an item in their activity stream.', 'dpa' ),
			'bp_core_activated_user'           => __( 'A new user activates their account on your website.', 'dpa' ),
			'bp_groups_posted_update'          => __( "The user writes a message in a group&#8217;s activity stream.", 'dpa' ),
			'friends_friendship_accepted'      => __( 'The user accepts a friendship request from someone.', 'dpa' ),
			'friends_friendship_deleted'       => __( 'The user cancels a friendship.', 'dpa' ),
			'friends_friendship_rejected'      => __( 'The user rejects a friendship request from someone.', 'dpa' ),
			'friends_friendship_requested'     => __( 'The user sends a friendship request to someone.', 'dpa' ),
			'groups_banned_member'             => __( 'The user bans a member from a group.', 'dpa' ),
			'groups_group_create_complete'     => __( 'The user creates a group.', 'dpa' ),
			'groups_delete_group'              => __( 'The user deletes a group.', 'dpa' ),
			'groups_demoted_member'            => __( 'The user demotes a group member from moderator or administrator status.', 'dpa' ),
			'groups_invite_user'               => __( 'The user invites someone to join a group.', 'dpa' ),
			'groups_join_group'                => __( 'The user joins a group.', 'dpa' ),
			'groups_leave_group'               => __( 'The user leaves a group.', 'dpa' ),
			'groups_promote_member'            => __( 'The user is promoted to a moderator or an administrator in a group.', 'dpa' ),
			'groups_promoted_member'           => __( 'The user promotes a group member to moderator or administrator status.', 'dpa' ),
			'groups_unbanned_member'           => __( 'The user unbans a member from a group.', 'dpa' ),
			'messages_delete_thread'           => __( 'The user deletes a private message.', 'dpa' ),
			'messages_message_sent'            => __( 'The user sends a new private message or replies to an existing one.', 'dpa' ),
			'xprofile_avatar_uploaded'         => __( "The user changes their profile&#8217;s avatar.", 'dpa' ),
			'xprofile_updated_profile'         => __( 'The user updates their profile information.', 'dpa' ),
		);

		$this->contributors = array(
			array(
				'name'         => 'John James Jacoby',
				'gravatar_url' => 'http://www.gravatar.com/avatar/81ec16063d89b162d55efe72165c105f',
				'profile_url'  => 'http://profiles.wordpress.org/johnjamesjacoby/',
			),
			array(
				'name'         => 'Paul Gibbs',
				'gravatar_url' => 'http://www.gravatar.com/avatar/3bc9ab796299d67ce83dceb9554f75df',
				'profile_url'  => 'http://profiles.wordpress.org/DJPaul/',
			),
			array(
				'name'         => 'Boone Gorges',
				'gravatar_url' => 'http://www.gravatar.com/avatar/9cf7c4541a582729a5fc7ae484786c0c',
				'profile_url'  => 'http://profiles.wordpress.org/boonebgorges/',
			),
			array(
				'name'         => 'Raymond Hoh',
				'gravatar_url' => 'http://www.gravatar.com/avatar/3bfa556a62b5bfac1012b6ba5f42ebfa',
				'profile_url'  => 'http://profiles.wordpress.org/r-a-y/',
			),
		);

		$this->description     = __( 'Social networking in a box. Build a social network for your company, school, sports team or niche community.', 'dpa' );
		$this->id              = 'buddypress';
		$this->image_url       = trailingslashit( achievements()->includes_url ) . 'admin/images/buddypress.png';
		$this->name            = __( 'BuddyPress', 'dpa' );
		$this->rss_url         = 'http://buddypress.org/blog/feed/';
		$this->small_image_url = trailingslashit( achievements()->includes_url ) . 'admin/images/buddypress-small.png';
		$this->version         = 1;
		$this->wporg_url       = 'http://wordpress.org/plugins/buddypress/';

		add_filter( 'dpa_handle_event_user_id', array( $this, 'event_user_id' ), 10, 3 );
	}

	/**
 	 * For some of the actions, get the user ID from the function arguments.
	 *
	 * @param int $user_id
	 * @param string $action_name
	 * @param array $action_func_args The action's arguments from func_get_args().
	 * @return int|false New user ID or false to skip any further processing
	 * @since Achievements (3.0)
	 */
	public function event_user_id( $user_id, $action_name, $action_func_args ) {
		// Only deal with events added by this extension.
		if ( ! in_array( $action_name, array( 'bp_core_activated_user', 'groups_demote_member', 'groups_promote_member', ) ) )
			return $user_id;

		// A new user activates their account on your website
		if ( 'bp_core_activated_user' === $action_name ) {
			$user_id = $action_func_args[0];

		// The user is demoted from being a moderator or an administrator in a group
		} elseif ( 'groups_demote_member' === $action_name ) {
			$user_id = $action_func_args[1];

		// The user is promoted to a moderator or an administrator in a group
		} elseif ( 'groups_promote_member' === $action_name ) {
			$user_id = $action_func_args[1];
		}

		return (int) $user_id;
	}

	/**
	 * Sets the bp_loaded property if BuddyPress is loaded (DEPRECATED).
	 *
	 * @deprecated Achievements (3.2)
	 * @param bool $bp_is_loaded
	 * @since Achievements (3.0)
	 */
	public function set_bp_loaded( $bp_is_loaded ) {
		_deprecated_function( __FUNCTION__, 'Achievements (3.2)' );
	}

	/**
	 * Returns true if BuddyPress is loaded (DEPRECATED).
	 *
	 * @deprecated Achievements (3.2)
	 * @return bool
	 * @since Achievements (3.0)
	 */
	public function is_bp_loaded() {
		_deprecated_function( __FUNCTION__, 'Achievements (3.2)' );
		return false;
	}
}